<?php

namespace App\Services\B2B;

use App\Repositories\B2BInventoryRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;

class GetB2BFirstInventoryService extends AppService
{
    protected $box;

    public function __construct(B2BInventoryRepository $b2binventoryRepository)
    {
        $this->b2binventoryRepository = $b2binventoryRepository;
    }

    public function setSku(string $sku)
    {
        $this->sku = $sku;
        return $this;
    }

    public function exec()
    {
        $has_error =false;
        $check_quantity =0;
        $sku = $this->b2binventoryRepository->search([
            'material_sku' => $this->sku
        ]);
        $check = $this->b2binventoryRepository->search([
            'material_sku' => $this->sku,
            'status' => 'check_inventory'
        ]);
        if (count($sku) === 0) {
            throw ValidationException::withMessages(['sku' => '此料號'.$this->sku.'尚未初盤']);
        } else {
            if (count($check)!==0){
                $has_error =true;
                $check_quantity = $check->get(0)->check_quantity;
            }
            $sku = $sku->get(0);
            return [
                'has_error'=>$has_error,
                'checkQuantity' => $check_quantity,
                'sku' => $sku->material_sku,
                'materialName' => $sku->material_name,
                'firstQuantity' => $sku->first_quantity
            ];
        }
    }
}
