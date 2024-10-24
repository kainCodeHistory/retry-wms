<?php

namespace App\Services\Location;

use App\Repositories\LocationRepository;
use App\Services\AppService;
use App\Exports\LocationsExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class LocationDownloadService extends AppService
{

    protected $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }




    public function exec()
    {
        $timestamp = Carbon::now()->format('YmdHis');
        $fileName = 'locaiotn_' . $timestamp . ".xlsx";
        $locationExport = new LocationsExport();
        return Excel::download($locationExport, $fileName);
    }
}
