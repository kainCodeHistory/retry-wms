<?php

namespace App\Services\StorageBox;

use App\Models\Transaction;
use App\Repositories\PickingArea\RefillRepository;
use App\Repositories\StorageBox\PickingItemRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Repositories\TransactionRepository;
use App\Services\AppService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResetService extends AppService
{
    protected $payload;
    protected $pickingItemRepository;
    protected $refillRepository;
    protected $storageBoxRepository;
    protected $storageBoxItemRepository;
    protected $transactionRepository;

    public function __construct(
        PickingItemRepository $pickingItemRepository,
        RefillRepository $refillRepository,
        StorageBoxRepository $storageBoxRepository,
        StorageBoxItemRepository $storageBoxItemRepository,
        TransactionRepository $transactionRepository
    ) {
        $this->pickingItemRepository = $pickingItemRepository;
        $this->refillRepository = $refillRepository;
        $this->storageBoxRepository = $storageBoxRepository;
        $this->storageBoxItemRepository = $storageBoxItemRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
        return $this;
    }

    public function exec()
    {
        $this->validate(
            $this->payload,
            [
                'storageBox' => 'required|string'
            ],
            [
                'storageBox.required' => '貨箱條碼必須有值。'
            ]
        );

        $storageBox = $this->storageBoxRepository->search([
            'barcode' => $this->payload['storageBox']
        ])->first();

        if (is_null($storageBox)) {
            throw ValidationException::withMessages(['storageBox' => '無此貨箱條碼 (' . $this->payload['storageBox'] . ')。']);
        }

        try {
            DB::beginTransaction();

            $storageBoxItem = $this->storageBoxItemRepository->search([
                'storage_box' => $this->payload['storageBox']
            ])->first();

            if (!is_null($storageBoxItem)) {
                //負數要扣到第二箱
                if (str_starts_with($storageBox->location, 'AA') || str_starts_with($storageBox->location, 'MN-07')) {
                    $pickingItems = $this->pickingItemRepository->search([
                        'location' => $storageBox->location
                    ]);

                    if (count($pickingItems) > 1) {
                        $anotherPickingItem = $pickingItems->filter(function ($pickingItem) use ($storageBox) {
                            return $pickingItem->storage_box !== $storageBox->barcode;
                        })->first();

                        $this->storageBoxItemRepository->updateQuantityWithStorageBox($anotherPickingItem->storage_box, $anotherPickingItem->quantity + $storageBoxItem->quantity);
                    }
                }


                $this->transactionRepository->create([
                    'warehouse_id' => $storageBox->warehouse_id,
                    'location' => $storageBox->location,
                    'storage_box' => $storageBox->barcode,
                    'material_id' => $storageBoxItem->material_id,
                    'material_sku' => $storageBoxItem->material_sku,
                    'batch_no' => $storageBoxItem->batch_no,
                    'quantity' => $storageBoxItem->quantity,
                    'in_out' => 'output',
                    'event' => Transaction::STORAGE_BOX_RESET,
                    'user' => Auth::user()->id
                ]);

                $this->storageBoxItemRepository->delete($storageBoxItem->id);
            }

            if (!str_starts_with($storageBox->location, 'AC') || !str_starts_with($storageBox->location, 'MN-01')) {
                $this->storageBoxRepository->reset($storageBox->barcode);
            }

            // 移除補料紀錄
            $refills = $this->refillRepository->search([
                'repl_storage_box' => $storageBox->barcode
            ]);

            if (count($refills) > 0) {
                $this->refillRepository->deleteMany($refills->pluck('id')->toArray());
            }

            DB::commit();

            return [
                'storageBox' => $storageBox->barcode
            ];
        } catch (\Exception $ex) {
            DB::rollBack();

            throw ValidationException::withMessages(['storageBox' => $ex->getMessage()]);
        }
    }
}
