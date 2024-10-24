<?php

namespace App\Models\StorageBox;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageBox extends Model
{
    use HasFactory;

    // 物料綁定
    const BOUND = "bound";
    // 入庫
    const STORAGE = "storage";

    protected $table = 'storage_boxes';

    protected $guarded = [];

    protected $fillable = [
        'prefix',
        'barcode',
        'factory_id',
        'warehouse_id',
        'location',
        'sku',
        'initial_quantity',
        'is_empty',
        'status',
        'bound_material_at',
        'bound_location_at',
        'bound_picking_area_at'
    ];
}
