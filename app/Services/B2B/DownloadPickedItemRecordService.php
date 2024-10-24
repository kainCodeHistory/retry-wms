<?php

namespace App\Services\B2B;

use App\Exports\B2BPickedItemExport;
use App\Services\AppService;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class DownloadPickedItemRecordService extends AppService
{
    protected $startDate;
    protected $endDate;
    protected $sku;


    public function __construct()
    {
    }

    public function setStartDate(string $startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }
    public function setEndDate(string $endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }
    public function setSku(string $sku)
    {
        $this->sku = $sku;
        return $this;
    }


    public function exec()
    {

        $sku = $this->sku;
        $startTime =$this->startDate;
        $endTime =  $this->endDate;

        $timestamp = Carbon::now()->format('YmdHis');
        $fileName = 'B2B_picked_record_' . $timestamp . ".xlsx";
        $b2bPickedRecordExport = new B2BPickedItemExport($sku,$startTime,$endTime );
        return Excel::download($b2bPickedRecordExport, $fileName);
    }
}
