<?php

namespace App\Http\Controllers\PickingArea;

use App\Http\Controllers\Controller;
use App\Services\PickingArea\Refill\BindACLocationService;
use App\Services\PickingArea\Refill\GetACLocationService;
use Illuminate\Http\Request;

class RefillController extends Controller
{
    
    public function bindACLocation(Request $request)
    {
        return app(BindACLocationService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function getACLocation(Request $request, string $storageBox)
    {
        return app(GetACLocationService::class)
            ->setStorageBox($storageBox)
            ->exec();
    }

}
