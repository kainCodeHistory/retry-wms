<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class B2BPickedItem extends Model
{
    use HasFactory;

    protected $table = 'b2b_picked_items';
    protected $fillable = [
        'batch_key',
        'picked_date',
        'sku',
        'total_list',
        'order_number',
        'quantity',
        'employee_no'
    ];
}
