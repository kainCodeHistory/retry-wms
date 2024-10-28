<?php

namespace App\Http\Controllers\StorageBox;

use App\Http\Controllers\Controller;
use App\Services\StorageBox\Input\CheckStorageBoxService;
use App\Services\StorageBox\Input\GetBindingService;

use Illuminate\Http\Request;

class InputController extends Controller
{
    public function checkStorageBox(Request $request, string $storageBox)
    {
        return app(CheckStorageBoxService::class)
            ->setStorageBox($storageBox)
            ->exec();
    }


    public function getBinding(Request $request, string $storageBox)
    {
        return app(GetBindingService::class)
            ->setStorageBox($storageBox)
            ->exec();
    }

}
