<?php

namespace App\Services\Inventory;

use App\Repositories\InventoryRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;

class GetFirstInventoryService extends AppService
{
    protected $box;

    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    public function setBox(string $Box)
    {
        $this->box = $Box;
        return $this;
    }

    public function exec()
    {
        $has_error =false;
        $check_quantity =0;
        $boxItem = $this->inventoryRepository->search([
            'storage_box' => $this->box
        ]);
        $check = $this->inventoryRepository->search([
            'storage_box' => $this->box,
            'status' => 'check_inventory'
        ]);
        if (count($boxItem) === 0) {
            throw ValidationException::withMessages(['box' => '此箱號尚未初盤']);
        } else {
            if (count($check)!==0){
                $has_error =true;
                $check_quantity = $boxItem->get(0)->check_quantity;
            }
            $boxItem = $boxItem->get(0);
            return [
                'has_error'=>$has_error,
                'checkQuantity' => $check_quantity,
                'box' => $boxItem->storage_box,
                'location' => $boxItem->location,
                'sku' => $boxItem->material_sku,
                'materialName' => $boxItem->material_name,
                'firstQuantity' => $boxItem->first_quantity
            ];
        }
    }
}
