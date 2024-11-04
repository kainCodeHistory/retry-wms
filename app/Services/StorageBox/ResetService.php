<?php

namespace App\Services\StorageBox;

use App\Models\Transaction;
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
    protected $storageBoxRepository;
    protected $storageBoxItemRepository;
    protected $transactionRepository;

    public function __construct(
        StorageBoxRepository $storageBoxRepository,
        StorageBoxItemRepository $storageBoxItemRepository,
        TransactionRepository $transactionRepository
    ) {
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
