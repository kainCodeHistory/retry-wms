<?php

namespace App\Services\B2B;

ini_set('memory_limit', '256M');

use App\Exports\B2BInputExport;
use App\Services\AppService;
use Maatwebsite\Excel\Facades\Excel;

class ExportB2BInputService extends AppService
{
    private $workingDay;

    public function setWorkingDay(string $workingDay)
    {
        $this->workingDay = $workingDay;
        return $this;
    }

    public function exec()
    {
        $fileName = sprintf("b2b_%s.xlsx", $this->workingDay);
        return Excel::download(new B2BInputExport($this->workingDay), $fileName);
    }
}
