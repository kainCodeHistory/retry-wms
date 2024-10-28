<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Query\GetB2BLocationService;
use App\Services\Query\GetB2BMaterialService;
use App\Services\Query\GetB2BStorageBoxService;
use App\Services\Query\GetBindTimeService;
use App\Services\Query\GetLocationService;
use App\Services\Query\GetMaterialService;
use App\Services\Query\GetStorageBoxService;
use Illuminate\Http\Request;

class QueryController extends Controller
{
    public function getLocations(Request $request, string $eanSku)
    {
        return app(GetLocationService::class)
            ->setEanSku($eanSku)
            ->exec();
    }

    public function getMaterial(Request $request, string $barcode)
    {
        return app(GetMaterialService::class)
            ->setBarcode($barcode)
            ->exec();
    }

    public function getBindTimes(Request $request, string $Sku)
    {
        return app(GetBindTimeService::class)
            ->setSku($Sku)
            ->exec();
    }

    public function getStorageBoxes(Request $request, string $storageBox)
    {
        return app(GetStorageBoxService::class)
            ->setStorageBox($storageBox)
            ->exec();
    }
    public function getB2BLocations(Request $request, string $eanSku)
    {
        return app(GetB2BLocationService::class)
            ->setEanSku($eanSku)
            ->exec();
    }
    public function getB2BMaterial(Request $request, string $barcode)
    {
        return app(GetB2BMaterialService::class)
            ->setBarcode($barcode)
            ->exec();
    }
    public function getB2BStorageBox(Request $request, string $barcode)
    {
        return app(GetB2BStorageBoxService::class)
            ->setBarcode($barcode)
            ->exec();
    }
}
