<?php

namespace App\Services\PickingArea;

use App\Exports\RolloverRecordExport;
use App\Services\AppService;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class DownloadRolloverRecordService extends AppService
{



    public function __construct()
    {
    }

    public function exec()
    {
        $timestamp = Carbon::now()->format('YmdHis');
        $fileName = 'RolloverRecord_' . $timestamp . ".xlsx";
        $rolloverRecordExport = new RolloverRecordExport();
        return Excel::download($rolloverRecordExport, $fileName);
    }
}
