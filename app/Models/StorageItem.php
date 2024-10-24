<?php

namespace App\Models;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageItem extends Model
{
    use HasFactory;

    protected $table = 'storage_items';

    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo(Location::class, 'loaction_id', 'id');
    }
}
