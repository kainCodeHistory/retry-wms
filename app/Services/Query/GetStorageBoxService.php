<?php

namespace App\Services\Query;

use App\Repositories\LocationRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Repositories\StorageItemRepository;
use App\Services\AppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GetStorageBoxService extends AppService
{
    private $storageBox;

    private $storageItemRepository;
    private $materialRepository;
    private $storageBoxRepository;
    private $storageBoxItemRepository;

    public function __construct(StorageItemRepository $storageItemRepository, MaterialRepository $materialRepository, StorageBoxRepository $storageBoxRepository,StorageBoxItemRepository $storageBoxItemRepository)
    {
        $this->storageItemRepository = $storageItemRepository;
        $this->materialRepository = $materialRepository;
        $this->storageBoxRepository = $storageBoxRepository;
        $this->storageBoxItemRepository = $storageBoxItemRepository;
    }

    public function setStorageBox(string $storageBox)
    {
        $this->storageBox = $storageBox;
        return $this;
    }

    public function exec()
    {
        $storageBoxMaterial = $this->storageBoxItemRepository->search(['storage_box' => $this->storageBox]);
        if (count($storageBoxMaterial) === 0) {
            throw ValidationException::withMessages(['storageBox' => '查無此箱號 (' . $this->storageBox . ')。']);
        }
        $material = $this->materialRepository->getMaterialByEanOrSku($storageBoxMaterial->get(0)->material_sku);

        if (count($material) === 0) {
            throw ValidationException::withMessages(['eanSku' => '查無此料號 (' . $storageBoxMaterial->get(0)->material_sku . ')。']);
        }
        $material = $material->get(0);
        $storageBox = $this->storageBoxRepository->search([
            'barcode' => $this->storageBox
        ])->first();
        $prefix =  $storageBox->prefix;

        $storageZone = config('storageBoxZone.storage');
        $floor = (array_values($storageZone['3F']));

        if (in_array($prefix, $floor)) {
            $storageBoxes = $this->storageBoxItemRepository->getLocations($material->sku ,$floor);
        } else {
            $storageBoxes = $this->storageBoxItemRepository->getLocations($material->sku ,$floor);
        }


        $defaultLocations = $this->storageItemRepository->search([
            'material_id' => $material->id
        ])->pluck('location');


        return [
            'sku' => $material->sku,
            'productTitle' => $material->display_name,
            'checkSku' => $material->checkSku,
            'ean' => $material->ean,
            'storageBoxes' => $storageBoxes,
            'defaultLocations' => $defaultLocations
        ];
    }
}
