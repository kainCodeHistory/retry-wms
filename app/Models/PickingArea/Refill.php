<?php

namespace App\Models\PickingArea;

use App\Models\Material;
use App\Models\Warehouse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refill extends Model
{
    use HasFactory;

    protected $table = 'picking_area_refill';

    protected $guarded = [];

    public function repl_warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
