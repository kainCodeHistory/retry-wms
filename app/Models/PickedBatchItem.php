<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickedBatchItem extends Model
{
    use HasFactory;

    protected $table = 'picked_batch_items';

    protected $guarded = [];
}
