<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\B2BStock\GetSkuListService;
use App\Services\B2BStock\GetSkuLogWithDateService;
use Illuminate\Http\Request;

class B2BStockController extends Controller
{
    public function skuList(Request $request)
    {
        return app(GetSkuListService::class)
            ->exec();
    }

    public function getSkuDate(Request $request, string $sku)
    {
        return app(GetSkuLogWithDateService::class)
            ->setSku($sku)
            ->setPayload($request->all())
            ->exec();
    }


}
