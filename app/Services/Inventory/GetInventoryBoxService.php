<?php

namespace App\Services\Inventory;

use App\Repositories\InventoryRepository;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;

class GetInventoryBoxService extends AppService
{
    protected $box;
    protected $has_error;

    public function __construct(StorageBoxItemRepository $storageBoxItemRepository, InventoryRepository $inventoryRepository)
    {
        $this->storageBoxItemRepository = $storageBoxItemRepository;
        $this->inventoryRepository = $inventoryRepository;
    }

    public function setBox(string $Box)
    {
        $this->box = $Box;
        return $this;
    }
    public function setWarehouse(string $warehouse)
    {
        $this->warehouse = $warehouse;
        return $this;
    }

    public function exec()
    {
        $has_error = false;
        $first_quantity = 0;
        $inventory = $this->inventoryRepository->search([
            'storage_box' => $this->box
        ]);
        if (count($inventory) !== 0) {

            $has_error = true;
            $first_quantity = $inventory->get(0)->first_quantity;
        }

        $boxItem = $this->storageBoxItemRepository->getStorages($this->box);

        if (count($boxItem) === 0) {
            throw ValidationException::withMessages(['box' => '查無此箱箱號']);
        } else {
            $boxItem = $boxItem->get(0);
            $box = $boxItem->storage_box;
        }

        return [
            'has_error' => $has_error,
            'alreadyBox' => '此箱號已盤點過',
            'firstQuantity' => $first_quantity,
            'box' => $box,
            'location' => $boxItem->location,
            'sku' => $boxItem->material_sku,
            'materialName' => $boxItem->material_name,
            'warehouse' => (int)($this->warehouse),
            'quantity' => $boxItem->quantity
        ];
    }
}
