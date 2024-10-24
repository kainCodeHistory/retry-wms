<?php

namespace App\Services\Material;

use App\Repositories\MaterialRepository;
use App\Services\AppService;

class GetMaterialService extends AppService
{
    protected $eanSku;

    protected $materialRepository;

    public function __construct(MaterialRepository $materialRepository)
    {
        $this->materialRepository = $materialRepository;
    }

    public function setSku(string $eanSku)
    {
        $this->eanSku = $eanSku;
        return $this;
    }

    public function exec()
    {

        $material = $this->materialRepository->getMaterialByEanOrSku($this->eanSku)->first();

        if (!is_null($material)) {
            return [
                'ean' => $material->ean,
                'sku' =>  $material->sku,
                'name' => $material->display_name
            ];
        } else {
            return [
                'ean' => '',
                'sku' => '',
                'name' => '無此 SKU (' . $this->eanSku . ')。'
            ];
        }
    }
}
