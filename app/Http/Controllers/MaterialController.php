<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\UpsertAlertCheckPointJob;
use App\Jobs\UpsertMaterialJob;
use App\Services\Material\CheckLocationService;
use App\Services\Material\GetBomService;
use App\Services\Material\GetMaterialService;
use App\Services\Material\GetStorageBoxService;

use Illuminate\Http\Request;

class MaterialController extends Controller
{
    //撿料車抓箱號
    public function checkLocation(Request $request, string $location, string $storageBox)
    {
        return app(CheckLocationService::class)
            ->setLocation($location)
            ->setStorageBox($storageBox)
            ->exec();
    }

    public function getBoms(Request $request)
    {
        return app(GetBomService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function getStorageBoxes(Request $request)
    {
        return app(GetStorageBoxService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function get(Request $request, string $sku)
    {
        return app(GetMaterialService::class)
            ->setSku($sku)
            ->exec();
    }

}
