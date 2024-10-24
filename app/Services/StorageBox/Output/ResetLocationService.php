<?php

namespace App\Services\StorageBox\Output;

use App\Models\Transaction;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Repositories\TransactionRepository;
use App\Services\AppService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResetLocationService extends AppService
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

        $storageBoxItem = $this->storageBoxItemRepository->search([
            'storage_box_id' => $storageBox->id
        ])->first();

        if (is_null($storageBoxItem) === 0) {
            throw ValidationException::withMessages(['storageBox' => '此貨箱無綁定物料 (' . $storageBox->barcode . ')。']);
        }

        try {
            DB::beginTransaction();

            $this->transactionRepository->create([
                'warehouse_id' => $storageBox->warehouse_id,
                'location' => $storageBox->location,
                'storage_box' => $storageBox->barcode,
                'material_id' => $storageBoxItem->material_id,
                'material_sku' => $storageBoxItem->material_sku,
                'batch_no' => $storageBoxItem->batch_no,
                'quantity' => $storageBoxItem->quantity,
                'in_out' => 'output',
                'event' => Transaction::RESET_LOCATION,
                'event_key' => '',
                'user' => Auth::user()->id
            ]);

            $this->storageBoxRepository->update(
                $storageBox->id,
                [
                    'warehouse_id' => null,
                    'location' => '',
                    'is_empty' => 0
                ]
            );

            DB::commit();

            return [
                'storageBox' => $storageBox->barcode,
                'location' => ''
            ];
        } catch (Exception $ex) {
            DB::rollBack();

            throw ValidationException::withMessages(['storageBox' => $ex->getMessage()]);
        }
    }
}
