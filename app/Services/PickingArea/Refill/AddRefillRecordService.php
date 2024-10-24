<?php

namespace App\Services\PickingArea\Refill;

use App\Repositories\PickingArea\RefillRepository;
use App\Repositories\StorageBox\InventoryItemRepository;
use App\Repositories\StorageBox\PickingItemRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;

class AddRefillRecordService extends AppService
{
    protected $payload;
    protected $refillRepository;
    protected $inventoryItemRepository;
    protected $pickingItemRepository;

    public function __construct(RefillRepository $refillRepository, PickingItemRepository $pickingItemRepository, InventoryItemRepository $inventoryItemRepository)
    {
        $this->refillRepository = $refillRepository;
        $this->pickingItemRepository = $pickingItemRepository;
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
                'location' => 'required|string|exists:locations,barcode'
            ],
            [
                'location.required' => '儲位必須有值。',
                'location.exists' => '無此儲位 (' . $this->payload['location'] . ')。'
            ]
        );

        $pickingItems = $this->pickingItemRepository->search([
            'location' => $this->payload['location']
        ]);

        if (count($pickingItems) === 0) {
            throw ValidationException::withMessages(['location' => '此儲位無綁定貨箱 (' . $this->payload['location'] . ') 。']);
        }

        $inventoryItems = $this->inventoryItemRepository->getAvailableInventoryItems($pickingItems->get(0)->material_id);
        if (count($inventoryItems) === 0) {
            throw ValidationException::withMessages(['location' => '此儲位料號無料可補( ' . $pickingItems->get(0)->material_sku . ')。']);
        }

        $refill = $this->refillRepository->getRefillRecordsByLocation($this->payload['location']);

        if (count($refill) === 0) {
            $refill = $this->refillRepository->create([
                'material_id' => $pickingItems->get(0)->material_id,
                'material_sku' => $pickingItems->get(0)->material_sku,
                'warehouse_id' => $pickingItems->get(0)->warehouse_id,
                'location' => $pickingItems->get(0)->location,
                'storage_boxes' => json_encode($pickingItems->pluck('storage_box')->toArray()),
                'quantity' => 0,
                'fill_type' => 'replace',
                'status' => 'pending'
            ]);
        } else {
            $refill = $refill->get(0);

            if ($refill->status === 'processing') {
                throw ValidationException::withMessages(['location' => '此儲位已排定補料作業 (' . $this->payload['location'] . ')。']);
            }
        }

        return [
            'id' => $refill->id,
            'sku' => $pickingItems->get(0)->material_sku,
            'name' => $pickingItems->get(0)->material_name,
            'location' => $pickingItems->get(0)->location,
            'storageBoxes' => $pickingItems->pluck('storage_box')->toArray(),
            'fillType' => $refill->fill_type,
        ];
    }
}
