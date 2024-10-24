<?php

namespace App\Services\PickingArea\Refill;

use App\Repositories\LocationRepository;
use App\Repositories\StorageBox\PickingItemRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;

class GetACLocationService extends AppService
{
    protected $storageBox;
    protected $locationRepository;
    protected $pickingItemRepository;

    public function __construct(LocationRepository $locationRepository, PickingItemRepository $pickingItemRepository)
    {
        $this->locationRepository = $locationRepository;
        $this->pickingItemRepository = $pickingItemRepository;
    }

    public function setStorageBox(string $storageBox)
    {
        $this->storageBox = $storageBox;
        return $this;
    }

    public function exec()
    {
        $pickingItem = $this->pickingItemRepository->search([
            'storage_box' => $this->storageBox
        ]);

        if (count($pickingItem) === 0 || str_starts_with($pickingItem->get(0)->location,'XB')) {
            $location = $this->locationRepository->search([
                'default_storage_box' => $this->storageBox
            ]);

            if (count($location) === 0) {
                throw ValidationException::withMessages(['location' => '此儲位尚未綁定罕見品箱。']);
            } else {
                $location = $location->get(0);

                return [
                    'storageBox' => $location->default_storage_box,
                    'location' => $location->barcode,
                    'sku' => '',
                    'materialName' => '',
                    'batchNo' => '',
                    'quantity' => 0
                ];
            }
        } else {
            $pickingItem = $pickingItem->get(0);
            return [
                'storageBox' => $pickingItem->storage_box,
                'location' => $pickingItem->location,
                'sku' => $pickingItem->material_sku,
                'materialName' => $pickingItem->material_name,
                'batchNo' => $pickingItem->batch_no,
                'quantity' => $pickingItem->quantity
            ];
        }
    }
}
