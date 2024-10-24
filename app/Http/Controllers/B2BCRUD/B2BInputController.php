<?php

namespace App\Http\Controllers\B2BCRUD;

use App\Http\Controllers\Controller;
use App\Services\B2B\StorageBox\Input\BindLocationService;
use App\Services\B2B\StorageBox\Input\BindPickingBoxService;
use App\Services\B2B\StorageBox\Input\BindXBLocationService;
use Illuminate\Http\Request;

class B2BInputController extends Controller
{
    public function bindPickingBox(Request $request)
    {
        return app(BindPickingBoxService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function bindLocation(Request $request)
    {
        return app(BindLocationService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function bindXBLocation(Request $request)
    {
        return app(BindXBLocationService::class)
            ->setPayload($request->all())
            ->exec();
    }
}
