<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickedBatch extends Model
{
    use HasFactory;

    protected $table = 'picked_batches';

    protected $guarded = [];
}
