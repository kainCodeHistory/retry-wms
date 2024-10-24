<?php

namespace App\Models\StorageBox;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickingItem extends Model
{
    use HasFactory;

    protected $table = 'v_picking_items';

    protected $guarded = [];
}
