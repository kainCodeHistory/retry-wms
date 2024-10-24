<?php

namespace App\Http\Controllers;

use App\Jobs\B2BInventoryDebitJob;
use App\Services\B2B\AddB2BInputService;
use App\Services\B2B\B2BCheckInventoryService;
use App\Services\B2B\B2BInventoryService;
use App\Services\B2B\B2BPickedItemService;
use App\Services\B2B\DeleteB2B5FInputService;
use App\Services\B2B\GetB2B5FInputService;
use App\Services\B2B\UpsertB2B5FInputService;
use App\Services\B2B\ExportB2BInputService;
use App\Services\B2B\DownloadPickedItemRecordService;
use App\Services\B2B\FixB2BPickedItemService;
use App\Services\B2B\GetB2B5FInputListService;
use App\Services\B2B\GetB2B5FInventoryService;
use App\Services\B2B\GetB2BFirstInventoryService;
use App\Services\B2B\SearchB2BPickedItemsService;
use App\Services\B2B\UpdateQuantityService;
use App\Services\B2B\DeleteB2BInputService;
use App\Services\B2B\GetB2BInputListService;
use Illuminate\Http\Request;

class B2BController extends Controller
{
    /**
     * 製造 - 成品倉入庫
     */
    public function addInput(Request $request)
    {
        $inputId = app(AddB2BInputService::class)
            ->setPayload($request->all())
            ->exec();

        return response()->json([
            'ok' => true,
            'inputId' => $inputId
        ]);
    }

    public function deleteInput(Request $request, int $inputId)
    {
        app(DeleteB2BInputService::class)
            ->setInputId($inputId)
            ->exec();

        return response()->json([
            'ok' => true
        ]);
    }

    public function getInputList(Request $request)
    {
        $list = app(GetB2BInputListService::class)
            ->setPayload($request->all())
            ->exec();

        return response()->json($list);
    }

    public function exportB2bInput(Request $request, string $workingDay)
    {
        $file = app(ExportB2BInputService::class)
            ->setWorkingDay($workingDay)
            ->exec();

        return $file;
    }

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

    public function get5FInventory(Request $request, string $sku)
    {
        return app(GetB2B5FInventoryService::class)
            ->setSku($sku)
            ->exec();
    }
    public function b2bInventory(Request $request)
    {
        return app(B2BInventoryService::class)
            ->setPayload($request->all())
            ->exec();
    }
    public function getb2bFirstInventory(Request $request, string $sku)
    {
        return app(GetB2BFirstInventoryService::class)
            ->setSku($sku)
            ->exec();
    }
    public function b2bCheckInventory(Request $request)
    {
        return app(B2BCheckInventoryService::class)
            ->setPayload($request->all())
            ->exec();
    }
    public function addB2bPickedItem(Request $request)
    {
        return app(B2BPickedItemService::class)
            ->setPayload($request->all())
            ->exec();
    }
    public function updateQuantity(Request $request)
    {
        return app(UpdateQuantityService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function searchPickedItemsRecord(Request $request)
    {
        return app(SearchB2BPickedItemsService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function fixQuantity(Request $request)
    {
        return app(FixB2BPickedItemService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function dowloadPickedItemRecordFile(Request $request, $start_date, $end_date, $sku)
    {
        $fileName = app(DownloadPickedItemRecordService::class)
            ->setStartDate($start_date)
            ->setEndDate($end_date)
            ->setSku($sku)
            ->exec();
        return ($fileName);
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
