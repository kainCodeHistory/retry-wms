<?php

namespace App\Http\Controllers\StorageBox;

use App\Http\Controllers\Controller;
use App\Services\StorageBox\Input\BindMaterialService;
use App\Services\StorageBox\Input\BindLocationService;
use App\Services\StorageBox\Input\CheckStorageBoxService;
use App\Services\StorageBox\Input\GetBindingService;
use App\Services\StorageBox\UpdateQuantityService;

use Illuminate\Http\Request;

class InputController extends Controller
{
    public function checkStorageBox(Request $request, string $storageBox)
    {
        return app(CheckStorageBoxService::class)
            ->setStorageBox($storageBox)
            ->exec();
    }

    public function bindMaterial(Request $request)
    {
        return app(BindMaterialService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function bindLocation(Request $request)
    {
        return app(BindLocationService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function getBinding(Request $request, string $storageBox)
    {
        return app(GetBindingService::class)
            ->setStorageBox($storageBox)
            ->exec();
    }

    public function updateQuantity(Request $request)
    {
        return app(UpdateQuantityService::class)
            ->setPayload($request->all())
            ->exec();
    }
}
