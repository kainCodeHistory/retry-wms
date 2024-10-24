<?php

namespace App\Http\Controllers\LocationCRUD;

use App\Http\Controllers\Controller;
use App\Services\Location\LocationShowService;
use App\Services\Location\LocationUpdateService;
use Illuminate\Http\Request;


class SingleController extends Controller
 {
    public function getLocations(Request $request, string $Sku)
    {
        return app(LocationShowService::class)
            ->setSku($Sku)
            ->exec();
    }

    public function editLocations(Request $request)
    {
        return app(LocationUpdateService::class)
            ->setPayload($request->all())
            ->exec();
    }

 }
