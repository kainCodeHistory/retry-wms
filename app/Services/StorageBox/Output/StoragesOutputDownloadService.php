<?php

namespace App\Services\StorageBox\Output;

use App\Services\AppService;
use App\Exports\StoragesOutputExport;
use App\Repositories\StorageBox\StorageBoxRepository;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class StoragesOutputDownloadService extends AppService
{



    public function __construct(StorageBoxRepository $storageBoxRepository)
    {
        $this->storageBoxRepository = $storageBoxRepository;
    }

    public function setStartTime(string $startTime)
    {
        $this->startTime = $startTime;
        return $this;
    }
    public function setEndTime(string $EndTime)
    {
        $this->EndTime = $EndTime;
        return $this;
    }


    public function exec()
    {
        $timestamp = Carbon::now()->format('YmdHis');
        $fileName = 'Output_Storages_' . $timestamp . ".xlsx";
        $storagesOutputExport = new StoragesOutputExport($this->startTime,$this->EndTime);
        return Excel::download($storagesOutputExport, $fileName);
    }
}
