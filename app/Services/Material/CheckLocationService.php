<?php

namespace App\Services\Material;

use App\Services\AppService;
use App\Repositories\StorageBox\PickingItemRepository;

class CheckLocationService extends AppService
{
    protected $location;
    protected $storageBox;
    protected $pickingItemRepository;

    public function __construct(PickingItemRepository $pickingItemRepository)
    {
        $this->pickingItemRepository = $pickingItemRepository;
    }

    public function setLocation(string $location)
    {
        $this->location = $location;
        return $this;
    }

    public function setStorageBox(string $storageBox)
    {
        $this->storageBox = $storageBox;
        return $this;
    }

    public function exec()
    {
        $storageBoxes = $this->pickingItemRepository->search([
            'location' => $this->location
        ]);
        $barcodes = $storageBoxes->pluck('storage_box')->toArray();

        if (in_array($this->storageBox, $barcodes)) {
            return [
                'status' => true,
                'sku' => $storageBoxes->get(0)->material_sku,
                'location' => $this->location,
                'storageBoxes' => $barcodes
            ];
        } else {
            return [
                'status' => false,
                'sku' => '',
                'location' => $this->location,
                'storageBoxes' => []
            ];
        }
    }
}
