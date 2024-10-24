<?php

namespace App\Services\Inventory;

use App\Services\AppService;
use App\Exports\CheckInventoryExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class DownloadCheckInventoryService extends AppService
{



    public function __construct()
    {

    }

    public function exec()
    {
        $timestamp = Carbon::now()->format('YmdHis');
        $fileName = 'Check_Inventory_' . $timestamp . ".xlsx";
        $checkInventoryExport = new CheckInventoryExport();
        return Excel::download($checkInventoryExport, $fileName);
    }
}
