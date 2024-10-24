<?php

namespace App\Http\Controllers\PickingArea;

use App\Http\Controllers\Controller;
use App\Services\PickingArea\Refill\AddRefillRecordService;
use App\Services\PickingArea\Refill\BindACLocationService;
use App\Services\PickingArea\Refill\BindLocationService;
use App\Services\PickingArea\Refill\DeleteRefillRecordService;
use App\Services\PickingArea\Refill\GenerateAAZoneRefillListService;
use App\Services\PickingArea\Refill\GetACLocationService;
use App\Services\PickingArea\Refill\GetLocationService;
use App\Services\PickingArea\Refill\GetPendingRefillRecordService;
use App\Services\PickingArea\RolloverRecordService;
use App\Services\PickingArea\DownloadRolloverRecordService;
use App\Services\PickingArea\Refill\GetUnCompleteRefillRecord;
use App\Services\PickingArea\Refill\UpdataACMNLocationService;
use App\Services\PickingArea\Refill\UpdateRefillRecordService;
use App\Services\PickingArea\RolloverSkuService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RefillController extends Controller
{
    public function addRecord(Request $request)
    {
        return app(AddRefillRecordService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function bindLocation(Request $request)
    {
        return app(BindLocationService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function bindACLocation(Request $request)
    {
        return app(BindACLocationService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function deleteRecord(Request $request, int $recordId)
    {
        return app(DeleteRefillRecordService::class)
            ->setRecordId($recordId)
            ->exec();
    }

    public function downloadAAZoneRefillList(Request $request)
    {
        $fileName = app(GenerateAAZoneRefillListService::class)
            ->exec();

        $csvFile = file_get_contents(storage_path("slack/" . $fileName));
        return (new Response($csvFile, 200))
            ->header('Content-Type', 'text/csv');
    }

    public function getLocation(Request $request, string $storageBox)
    {
        return app(GetLocationService::class)
            ->setStorageBox($storageBox)
            ->exec();
    }

    public function getACLocation(Request $request, string $storageBox)
    {
        return app(GetACLocationService::class)
            ->setStorageBox($storageBox)
            ->exec();
    }

    public function getRecord(Request $request)
    {
        return app(GetPendingRefillRecordService::class)
            ->exec();
    }

    public function updateRecord(Request $request)
    {
        return app(UpdateRefillRecordService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function getRolloverSku(Request $request , string $ean)
    {
        return app(RolloverSkuService::class)
            ->setSku($ean)
            ->exec();
    }

    public function rolloverRecord(Request $request)
    {
        return app(RolloverRecordService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function dowloadRolloverFile(Request $request)
    {
        $fileName = app(DownloadRolloverRecordService::class)
            ->exec();
        return ($fileName);
    }

    public function updateACMNLocation(Request $request)
    {
        return app(UpdataACMNLocationService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function getRefillRecord(Request $request)
    {
        return app(GetUnCompleteRefillRecord::class)
            ->exec();
    }
}
