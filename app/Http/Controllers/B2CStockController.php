<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\B2CStock\AdjustStockService;
use App\Services\B2CStock\GetSkuListService;
use App\Services\B2CStock\GetSkuLogWithDateService;
use App\Services\B2CStock\GetStockService;
use Illuminate\Http\Request;

class B2CStockController extends Controller
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

    public function adjustStock(Request $request, string $eanSku)
    {
        $payload = array_merge(
            $request->all(),
            [
                'ean_sku' => $eanSku
            ]
        );

        app(AdjustStockService::class)
            ->setPayload($payload)
            ->exec();

        return [
            'ok' => true
        ];
    }

    public function getStock(Request $request, string $eanSku)
    {
        $payload = app(GetStockService::class)
            ->setEanSku($eanSku)
            ->exec();

        if (is_null($payload)) {
            return response(sprintf("無此 EAN/SKU (%s)", $eanSku), 404);
        } else {
            return $payload;
        }
    }
}
