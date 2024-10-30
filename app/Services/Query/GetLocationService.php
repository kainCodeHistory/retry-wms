<?php

namespace App\Services\Query;


use App\Repositories\MaterialRepository;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Repositories\StorageItemRepository;
use App\Services\AppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GetLocationService extends AppService
{
    private $eanSku;

    private $storageItemRepository;
    private $materialRepository;
    private $storageBoxItemRepository;

    public function __construct(StorageItemRepository $storageItemRepository, MaterialRepository $materialRepository,StorageBoxItemRepository $storageBoxItemRepository)
    {
        $this->storageItemRepository = $storageItemRepository;
        $this->materialRepository = $materialRepository;
        $this->storageBoxItemRepository = $storageBoxItemRepository;
    }

    public function setEanSku(string $eanSku)
    {
        $this->eanSku = $eanSku;
        return $this;
    }

    public function exec()
    {
        $material = $this->materialRepository->getMaterialByEanOrSku($this->eanSku);

        if (count($material) === 0) {
            throw ValidationException::withMessages(['eanSku' => '查無此料號 (' . $this->eanSku . ')。']);
        }
        $material = $material->get(0);
        $storageZone = config('storageBoxZone.storage');
        $floor = (array_values($storageZone['3F']));

        $storageBoxes = $this->storageBoxItemRepository->getLocations($material->sku ,$floor);

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
