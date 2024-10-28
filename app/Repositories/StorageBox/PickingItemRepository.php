<?php

namespace App\Repositories\StorageBox;

use App\Models\StorageBox\PickingItem;
use App\Repositories\BaseRepository;

class PickingItemRepository extends BaseRepository
{
    protected $model = PickingItem::class;

    public function getPickingItems(array $skus)
    {
        return PickingItem::whereIn('material_sku', $skus)->select('material_sku', 'location', 'storage_box')->get();
    }

}
