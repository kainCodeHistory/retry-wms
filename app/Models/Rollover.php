<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rollover extends Model
{
    use HasFactory;

    protected $table = 'rollover';

    protected $guarded = [];
}
