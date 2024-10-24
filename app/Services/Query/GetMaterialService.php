<?php

namespace App\Services\Query;

use App\Repositories\StorageBox\StorageBoxRepository;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Services\AppService;

class GetMaterialService extends AppService
{
    private $barcode;

    private $storageBoxRepository;
    private $storageBoxItemRepository;

    public function __construct(StorageBoxRepository $storageBoxRepository, StorageBoxItemRepository $storageBoxItemRepository)
    {
        $this->storageBoxRepository = $storageBoxRepository;
        $this->storageBoxItemRepository = $storageBoxItemRepository;
    }

    public function setBarcode(string $barcode)
    {
        $this->barcode = $barcode;
        return $this;
    }

    public function exec()
    {
        $storageBoxItems = $this->storageBoxItemRepository->search(['storage_box' => $this->barcode]);
        $storageZone = config('storageBoxZone.storage');
        $floor = (array_values($storageZone['3F']));
        $targetStorageBox = null;
        if (count($storageBoxItems) === 0) {
            $storageBoxes = $this->storageBoxRepository->getStorageBoxesByLocation($this->barcode);

            if (count($storageBoxes) > 0) {
                $targetStorageBox = $storageBoxes->get(0);
                $storageBoxItems = $this->storageBoxItemRepository->search(['storage_box' => $targetStorageBox->barcode]);
            }
        } else {
            $targetStorageBox = $storageBoxItems->get(0)->storageBox;
        }

        if (count($storageBoxItems) > 0) {
            $location = $targetStorageBox->location;
            $materialSku = $storageBoxItems->get(0)->material_sku;
            $boxBarcode = $targetStorageBox->barcode;
            $storageBoxItems = $this->storageBoxItemRepository->getItemsDetails($location, $materialSku, $boxBarcode,$floor);


            $locations = $this->storageBoxItemRepository->getSkuDetails($materialSku,$floor);

            if (count($storageBoxItems)) {
                $storageBoxItem = $storageBoxItems->get(0);

                return [
                    'skuDetail' => $locations,
                    'storageDetail' => [$storageBoxItem]
                ];
            } else {
                return [
                    'skuDetail' => [],
                    'storageDetail' => []
                ];
            }

        } else {
            return [
                'skuDetail' => [],
                'storageDetail' => []
            ];
        }
    }
}
