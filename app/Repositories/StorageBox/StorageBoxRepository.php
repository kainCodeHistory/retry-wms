<?php

namespace App\Repositories\StorageBox;

use App\Models\StorageBox\StorageBox;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class StorageBoxRepository extends BaseRepository
{
    protected $model = StorageBox::class;

    public function reset(string $barcode)
    {
        $storageBox = $this->search([
            'barcode' => $barcode
        ])->get(0);

        return $this->update(
            $storageBox->id,
            [
                'warehouse_id' => null,
                'location' => '',
                'is_empty' => 1,
                'status' => '',
                'sku' => '',
                'initial_quantity' => 0,
                'bound_material_at' => null,
                'bound_location_at' => null,
                'bound_picking_area_at' => null
            ]
        );
    }

    public function getStorageBoxesByLocation(string $location)
    {
        return StorageBox::where('location', $location)
            ->orderBy('updated_at')
            ->get();
    }

    public function getStorageBoxByStatus(string $barcode, array $statusList)
    {
        return DB::table('storage_boxes')
            ->join('storage_box_items', 'storage_boxes.id', '=', 'storage_box_items.storage_box_id')
            ->where('storage_boxes.barcode', '=', $barcode)
            ->whereIn('storage_boxes.status', $statusList)
            ->select(
                'storage_boxes.id',
                'storage_boxes.prefix',
                'storage_boxes.barcode',
                'storage_boxes.status',
                'storage_box_items.material_id',
                'storage_box_items.material_sku',
                'storage_box_items.batch_no',
                'storage_box_items.quantity'
            )
            ->get();
    }
}
