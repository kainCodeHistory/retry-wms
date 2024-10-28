<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Material\GetMaterialService;

use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function get(Request $request, string $sku)
    {
        return app(GetMaterialService::class)
            ->setSku($sku)
            ->exec();
    }

}
