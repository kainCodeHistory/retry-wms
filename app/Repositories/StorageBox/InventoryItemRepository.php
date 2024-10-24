<?php

namespace App\Repositories\StorageBox;

use Illuminate\Support\Facades\DB;
use App\Models\StorageBox\InventoryItem;
use App\Repositories\BaseRepository;

class InventoryItemRepository extends BaseRepository
{
    protected $model = InventoryItem::class;

    public function getAvailableInventoryItems(int $materialId)
    {
        return InventoryItem::where('material_id', $materialId)->orderBy('bound_at')->orderBy('quantity')->get();
    }
    public function getSuggestLocations(string $sku)
    {
        return InventoryItem::select('location', DB::Raw('COUNT(storage_box) As cnt'))->where('material_sku', $sku)->groupBy('location')->orderBy('cnt', 'DESC')->get();
    }
}
