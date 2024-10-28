<?php

namespace App\Repositories;

use App\Models\StorageItem;

class StorageItemRepository extends BaseRepository
{
    protected $model = StorageItem::class;
   
    public function getLocations(string $sku)
    {
        return $this->model::where('material_sku', $sku)->get();
    }
    public function get5FPickingLocations(string $sku)
    {
        return $this->model::where('material_sku', $sku)->where('location','like', 'X%')->get();
    }


}
