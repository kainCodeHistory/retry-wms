<?php

namespace App\Services\StorageBox\Output;

use App\Services\AppService;
use App\Exports\StoragesInventoryExport;
use App\Repositories\MaterialRepository;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class StoragesInventoryDownloadService extends AppService
{


    public function __construct(MaterialRepository $materialRepository)
    {
        $this->materialRepository = $materialRepository;
    }




    public function exec()
    {
        $timestamp = Carbon::now()->format('YmdHis');
        $fileName = 'All_Storages_' . $timestamp . ".xlsx";
        $storagesInventoryExport = new StoragesInventoryExport();
        return Excel::download($storagesInventoryExport, $fileName);
    }
}
