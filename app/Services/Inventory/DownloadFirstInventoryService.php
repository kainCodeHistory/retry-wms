<?php

namespace App\Services\Inventory;

use App\Services\AppService;
use App\Exports\FirstInventoryExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class DownloadFirstInventoryService extends AppService
{

    protected $locationRepository;

    public function __construct()
    {
    }

    public function exec()
    {
        $timestamp = Carbon::now()->format('YmdHis');
        $fileName = 'First_Inventory_' . $timestamp . ".xlsx";
        $firstInventoryExport = new FirstInventoryExport();
        return Excel::download($firstInventoryExport, $fileName);
    }
}
