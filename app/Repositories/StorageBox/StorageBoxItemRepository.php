<?php

namespace App\Repositories\StorageBox;

use App\Models\StorageBox\StorageBoxItem;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class StorageBoxItemRepository extends BaseRepository
{
    protected $model = StorageBoxItem::class;

    public function reset(string $storageBox)
    {
        return $this->model::where('storage_box', $storageBox)->delete();
    }
    public function getItemsDetails(string $location, string $sku, string $storageBox,array $prefix)
    {
        return $this->model::select('storage_boxes.barcode', 'storage_boxes.location', 'storage_boxes.is_empty', 'storage_box_items.material_id', 'storage_box_items.material_sku', 'storage_box_items.material_name', 'storage_box_items.batch_no', 'storage_box_items.quantity', 'materials.ean')
            ->leftjoin('storage_boxes', 'storage_boxes.id', '=', 'storage_box_items.storage_box_id')
            ->leftjoin('materials', 'storage_box_items.material_id', '=', 'materials.id')
            ->where('storage_boxes.location', $location)
            ->whereIn('storage_boxes.prefix', $prefix)
            ->where('storage_box_items.material_sku', $sku)
            ->where('storage_boxes.barcode', $storageBox)
            ->orderby('storage_boxes.location')
            ->get();
    }

    public function getSkuDetails(string $sku,array $prefix)
    {
        return $this->model::select('storage_boxes.barcode', 'storage_boxes.location','storage_box_items.quantity')
            ->leftjoin('storage_boxes', 'storage_boxes.id', '=', 'storage_box_items.storage_box_id')
            ->where('storage_box_items.material_sku', $sku)
            ->whereIn('storage_boxes.prefix', $prefix)
            ->orderby('storage_boxes.location')
            ->get();
    }

    public function updateQuantityWithStorageBox(string $storageBox, int $quantity)
    {
        return $this->model::where('storage_box', $storageBox)
            ->update([
                'quantity' => $quantity
            ]);
    }


    public function getLocations(string $sku)
    {
        return $this->model::select('storage_boxes.id', 'storage_boxes.warehouse_id', 'storage_boxes.location','storage_box_items.storage_box', 'storage_box_items.batch_no', 'storage_box_items.quantity',DB::Raw('warehouses.name As warehouse'))
            ->leftjoin('storage_boxes', 'storage_boxes.id', '=', 'storage_box_items.storage_box_id')
            ->leftjoin('warehouses', 'warehouses.id', '=', 'storage_boxes.warehouse_id')
            ->where('storage_box_items.material_sku', $sku)
            ->orderby('storage_boxes.warehouse_id')
            ->orderby( 'storage_boxes.location')
            ->get();
    }


}
