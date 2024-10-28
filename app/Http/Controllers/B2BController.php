<?php

namespace App\Http\Controllers;

use App\Jobs\B2BInventoryDebitJob;
use App\Services\B2B\DeleteB2B5FInputService;
use App\Services\B2B\GetB2B5FInputService;
use App\Services\B2B\UpsertB2B5FInputService;
use App\Services\B2B\GetB2B5FInputListService;
use App\Services\B2B\UpdateQuantityService;
use Illuminate\Http\Request;

class B2BController extends Controller
{
    public function delete5FInput(Request $request, int $inputId)
    {
        app(DeleteB2B5FInputService::class)
            ->setInputId($inputId)
            ->exec();

        return response()->json([
            'inputId' => $inputId
        ]);
    }

    public function get5FInput(Request $request, int $inputId)
    {
        $input = app(GetB2B5FInputService::class)
            ->setInputId($inputId)
            ->exec();

        return response()->json(is_null($input) ? [] : $input);
    }

    public function get5FInputList(Request $request)
    {
        $list = app(GetB2B5FInputListService::class)
            ->setPayload($request->all())
            ->exec();

        return response()->json(count($list) === 0 ? [] : $list);
    }

    public function upsert5FInput(Request $request)
    {
        $input = app(UpsertB2B5FInputService::class)
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
