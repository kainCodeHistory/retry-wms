<?php

namespace App\Repositories;

use App\Models\StorageItem;

class StorageItemRepository extends BaseRepository
{
    protected $model = StorageItem::class;
    public function updateLocations(string $location, array $payload)
    {
        return $this->model::where('location', $location)->update($payload);
    }
    public function getLocations(string $sku)
    {
        return $this->model::where('material_sku', $sku)->get();
    }
    public function get3FPickingLocations(string $sku)
    {
        return $this->model::where('material_sku', $sku)->where('location','not like', 'X%')->get();
    }
    public function get5FPickingLocations(string $sku)
    {
        return $this->model::where('material_sku', $sku)->where('location','like', 'X%')->get();
    }


}
