<?php

namespace App\Http\Controllers;

use App\Jobs\B2BInventoryDebitJob;
use App\Services\B2B\DeleteB2BInputService;
use App\Services\B2B\GetB2BInputService;
use App\Services\B2B\UpsertB2BInputService;
use App\Services\B2B\GetB2BInputListService;
use App\Services\B2B\UpdateQuantityService;
use Illuminate\Http\Request;

class B2BController extends Controller
{
    public function deleteb2bInput(Request $request, int $inputId)
    {
        app(DeleteB2BInputService::class)
            ->setInputId($inputId)
            ->exec();

        return response()->json([
            'inputId' => $inputId
        ]);
    }

    public function getb2bInput(Request $request, int $inputId)
    {
        $input = app(GetB2BInputService::class)
            ->setInputId($inputId)
            ->exec();

        return response()->json(is_null($input) ? [] : $input);
    }

    public function getb2bInputList(Request $request)
    {
        $list = app(GetB2BInputListService::class)
            ->setPayload($request->all())
            ->exec();

        return response()->json(count($list) === 0 ? [] : $list);
    }

    public function upsertb2bInput(Request $request)
    {
        $input = app(UpsertB2BInputService::class)
            ->setPayload($request->all())
            ->exec();

        return response()->json($input);
    }

    public function updateQuantity(Request $request)
    {
        return app(UpdateQuantityService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function inventoryDebit(Request $request)
    {
        dispatch(new B2BInventoryDebitJob($request->all()))
            ->onQueue('wms-b2b-inventory-debit');

        return response()->json([
            'ok' => true
        ]);
    }
}
