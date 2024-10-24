<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Storage Event List
     */
    // 重設
    const STORAGE_BOX_RESET = "storage_box_reset";
    // 製造轉入庫
    const STORAGE_BOX_INPUT = "storage_box_input";
    // 清空儲位
    const RESET_LOCATION = "reset_location";
    // B 區補料上架
    const REFILL_INPUT = "refill_input";
    // B 區補料下架
    const REFILL_OUTPUT = "refill_output";
    // 物料已綁箱
    const ITEM_BOUND = "item_bound";
    // 調整儲位
    const ADJUST_LOCATION = "adjust_location";

    protected $table = 'transactions';

    protected $guarded = [];
    protected $fillable = [
        'warehouse_id',
        'location',
        'storage_box',
        'material_id',
        'material_sku',
        'batch_no',
        'quantity',
        'in_out',
        'event',
        'event_key',
        'user'
    ];
}
