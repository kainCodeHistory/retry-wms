<?php

namespace App\Http\Controllers;

use App\Services\Inventory\CreateInventoryService;
use App\Services\Inventory\XinfuBindStorageBoxService;
use App\Services\Inventory\GetInventoryBoxService;
use App\Services\Inventory\FirstInventoryService;
use App\Services\Inventory\GetFirstInventoryService;
use App\Services\Inventory\CheckInventoryService;
use App\Services\Inventory\DownloadFirstInventoryService;
use App\Services\Inventory\DownloadCheckInventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function create(Request $request)
    {
        return app(CreateInventoryService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function getBox(Request $request, string $Box, string $Warehouse)
    {
        return app(GetInventoryBoxService::class)
            ->setBox($Box)
            ->setWarehouse($Warehouse)
            ->exec();
    }

    public function getFirstRecord(Request $request, string $Box)
    {
        return app(GetFirstInventoryService::class)
            ->setBox($Box)
            ->exec();
    }

    public function firstInventory(Request $request)
    {
        return app(FirstInventoryService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function checkInventory(Request $request)
    {
        return app(CheckInventoryService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function dowloadFirstInventoryFile(Request $request)
    {
        $fileName = app(DownloadFirstInventoryService::class)
            ->exec();
        return ($fileName);
    }

    public function dowloadCheckInventoryFile(Request $request)
    {
        $fileName = app(DownloadCheckInventoryService::class)
            ->exec();
        return ($fileName);
    }
}
