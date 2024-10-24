<?php

namespace App\Services\B2B;

use App\Repositories\B2BInventoryRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;

class GetB2B5FInventoryService extends AppService
{
    protected $box;

    public function __construct(B2BInventoryRepository $b2bInventoryRepository)
    {
        $this->b2bInventoryRepository = $b2bInventoryRepository;
    }

    public function setSku(string $sku)
    {
        $this->sku = $sku;
        return $this;
    }



    public function exec()
    {
        $has_error = false;
        $inventory = $this->b2bInventoryRepository->search([
            'material_sku' => $this->sku
        ]);
        if (count($inventory) !== 0) {

            $has_error = true;
            $quantity = $inventory->get(0)->first_quantity;

            return [
                'has_error' => $has_error,
                'alreadyBox' => '此箱號已盤點過',
                'quantity' => $quantity,
                'sku' => $inventory->get(0)->material_sku,
                'materialName' => $inventory->get(0)->material_name
            ];
        }else{
            return [
                'has_error' => $has_error,
                'alreadyBox' => '此箱號已盤點過',
                'quantity' => '',
                'sku' => '',
                'materialName' => ''
            ];

        }


    }
}
