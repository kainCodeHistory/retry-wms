<?php

namespace App\Services\PickingArea\Refill;

use App\Repositories\StorageBox\InventoryItemRepository;
use App\Repositories\PickingArea\RefillRepository;
use App\Services\AppService;
use Exception;
use Illuminate\Validation\ValidationException;

class UpdateRefillRecordService extends AppService
{
    protected $payload;
    protected $refillRepository;
    protected $inventoryItemRepository;

    public function __construct(InventoryItemRepository $inventoryItemRepository, RefillRepository $refillRepository)
    {
        $this->refillRepository = $refillRepository;
        $this->inventoryItemRepository = $inventoryItemRepository;
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
                'id' => 'required|numeric',
                'outputQuantity' => 'required|numeric|min:1',
                'storageBox' => 'required|string'
            ],
            [
                'id.required' => '無補料紀錄 ID。',
                'outputQuantity.required' => '補料數量必須有值。',
                'storageBox.required' => '貨箱條碼必須有值。'
            ]
        );

        try {
            $refill = $this->refillRepository->findOrFail($this->payload['id']);

            $inventoryItem = $this->inventoryItemRepository->search([
                'storage_box' => $this->payload['storageBox']
            ])->get(0);

            $this->refillRepository->update(
                $refill->id,
                [
                    'repl_warehouse_id' => $inventoryItem->warehouse_id,
                    'repl_location' => $inventoryItem->location,
                    'repl_storage_box' => $inventoryItem->storage_box,
                    'repl_quantity' => $inventoryItem->quantity,
                    'status' => 'processing'
                ]
            );

            return [
                'id' => $refill->id,
                'quantity' => $this->payload['outputQuantity']
            ];
        } catch (Exception $ex) {
            throw ValidationException::withMessages(['refill' => $ex->getMessage()]);
        }
    }
}
