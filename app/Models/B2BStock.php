<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class B2BStock extends Model
{
    use HasFactory;

    protected $table = 'b2b_stock';

    protected $fillable = [
        'sku',
        'total_quantity'
    ];
}
