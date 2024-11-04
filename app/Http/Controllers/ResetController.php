<?php

namespace App\Http\Controllers;

use App\Services\StorageBox\ResetService;
use Illuminate\Http\Request;

class ResetController extends Controller
{
    //
    public function resetStorageBox(Request $request)
    {
        return app(ResetService::class)
            ->setPayload($request->all())
            ->exec();
    }
}
