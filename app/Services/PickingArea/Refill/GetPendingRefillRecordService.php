<?php

namespace App\Services\PickingArea\Refill;

use App\Repositories\PickingArea\RefillRepository;
use App\Repositories\StorageBox\InventoryItemRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;

class GetPendingRefillRecordService extends AppService
{
    protected $rareStorageBox;

    protected $refillRepository;
    protected $inventoryItemRepository;

    public function __construct(RefillRepository $refillRepository, InventoryItemRepository $inventoryItemRepository)
    {
        $this->refillRepository = $refillRepository;
        $this->inventoryItemRepository = $inventoryItemRepository;
    }

    public function exec()
    {
        $records = $this->refillRepository->getPendingStorageBox('replace');

        if (count($records) === 0) {
            throw ValidationException::withMessages(['refill' => '目前無補料作業。']);
        }
        $record = $records->get(0);

        $inventoryItems = $this->inventoryItemRepository->getAvailableInventoryItems($record->material_id);

        if (count($inventoryItems) === 0) {
            $this->refillRepository->update(
                $record->id,
                [
                    'status' => 'aborted'
                ]
            );
            throw ValidationException::withMessages(['refill' => '此SKU (' . $record->material_sku . ') 已無料可補，作業中止。']);
        }

        return [
            'id' => $record->id,
            'sku' => $inventoryItems->get(0)->material_sku,
            'name' => $inventoryItems->get(0)->material_name,
            'locations' => $inventoryItems->map(function ($inventoryItem) {
                return [
                    'warehouse' => $inventoryItem->warehouse_name,
                    'location' => $inventoryItem->location,
                    'storageBox' => $inventoryItem->storage_box,
                    'quantity' => $inventoryItem->quantity,
                    'batchNo' => $inventoryItem->batch_no,
                    'boundAt' => $inventoryItem->bound_at
                ];
            })->toArray()
        ];
    }
}
