<?php

namespace App\Services\Query;


use App\Repositories\MaterialRepository;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Services\AppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GetB2BLocationService extends AppService
{
    private $eanSku;

    private $materialRepository;
    private $storageBoxItemRepository;
    private $storageBoxRepository;

    public function __construct( MaterialRepository $materialRepository,StorageBoxItemRepository $storageBoxItemRepository , StorageBoxRepository $storageBoxRepository)
    {
      
        $this->materialRepository = $materialRepository;
        $this->storageBoxItemRepository = $storageBoxItemRepository;
        $this->storageBoxRepository = $storageBoxRepository;
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
       
        $storageBoxes = $this->storageBoxItemRepository->getLocations($material->sku)->all();

        $defaultLocations = $this->storageBoxRepository->search([
            'sku' => $material->sku
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
