<?php

namespace App\Http\Controllers\B2BCRUD;

use App\Http\Controllers\Controller;
use App\Services\B2B\UpdateRefillLocationService;
use Illuminate\Http\Request;

class B2BRefillController extends Controller
{
    public function updateLocationQuantity(Request $request)
    {
        return app(UpdateRefillLocationService::class)
            ->setPayload($request->all())
            ->exec();
    }
}
