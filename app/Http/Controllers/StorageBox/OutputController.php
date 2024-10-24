<?php

namespace App\Http\Controllers\StorageBox;

use App\Http\Controllers\Controller;
use App\Jobs\InventoryDebitJob;
use App\Services\StorageBox\Output\ResetLocationService;
use App\Services\StorageBox\Output\StoragesInventoryDownloadService;
use App\Services\StorageBox\Output\StoragesOutputDownloadService;
use App\Services\StorageBox\ResetService;

use Illuminate\Http\Request;

class OutputController extends Controller
{
    public function reset(Request $request)
    {
        return app(ResetService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function resetLocation(Request $request)
    {
        return app(ResetLocationService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function dowloadFile(Request $request)
    {
        $fileName = app(StoragesInventoryDownloadService::class)
            ->exec();
        return ($fileName);
    }

    public function dowloadFileByTime(Request $request, string $startTime, string $endTime){
        $file = app(StoragesOutputDownloadService::class)
            ->setStartTime($startTime)
            ->setEndTime($endTime)
            ->exec();
        return ($file);
    }

    public function inventoryDebit(Request $request)
    {
        dispatch(new InventoryDebitJob($request->all()))
            ->onQueue('wms-inventory-debit');

        return response()->json([
                'ok' => true
        ]);
    }
}
