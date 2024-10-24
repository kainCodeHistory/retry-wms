<?php

namespace App\Models\StorageBox;

use App\Models\StorageBox\StorageBox;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageBoxItem extends Model
{
    use HasFactory;

    protected $table = 'storage_box_items';

    protected $guarded = [];

    public function storageBox()
    {
        return $this->belongsTo(StorageBox::class);
    }
}
