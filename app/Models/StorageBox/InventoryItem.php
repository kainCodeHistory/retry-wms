<?php

namespace App\Models\StorageBox;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $table = 'v_inventory_items';

    protected $guarded = [];
}
