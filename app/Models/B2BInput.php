<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class B2BInput extends Model
{
    use HasFactory;

    protected $table = 'b2b_inputs';

    protected $guarded = [];
}
