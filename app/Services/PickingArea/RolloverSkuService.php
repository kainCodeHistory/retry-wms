<?php

namespace App\Services\PickingArea;

use Illuminate\Validation\ValidationException;
use App\Repositories\MaterialRepository;
use App\Services\AppService;
class RolloverSkuService extends AppService
{

    protected $materialRepository;

    public function __construct( MaterialRepository $materialRepository)
    {
        $this->materialRepository = $materialRepository;
    }

    public function setSku(string $ean)
    {
        $this->ean = $ean;
        return $this;
    }

    public function exec()
    {
        $ean = $this->materialRepository->search([
            'ean' => $this->ean
        ]);

        if (count($ean) === 0) {
            throw ValidationException::withMessages(['sku' => '查無此SKU']);
        } else {
            $sku = $ean->get(0)->sku;

            return [
                'sku' => $sku,
            ];
        }
    }
}
