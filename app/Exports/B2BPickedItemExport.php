<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class B2BPickedItemExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    private $endTime;
    private $startTime;
    private $sku;

    public function __construct($sku, $startTime, $endTime)
    {
        $this->sku = $sku;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function headings(): array
    {
        return [
            '日期',
            '總表單號',
            '訂單編號',
            'SKU',
            '數量',
            '修正數量',
            '使用者',
            '建立時間'
        ];
    }

    public function title(): string
    {
        return 'B2B撿料記錄';
    }

    public function array(): array
    {

        $inputs = DB::select("SELECT `picked_date`,`total_list`, `order_number`, `sku`, `quantity`, `fixed_quantity`,`employee_no`, DATE_ADD(`created_at`, interval 8 hour) As `created_at`
                              FROM `b2b_picked_items`
                              where `sku` = '$this->sku'
                              and `picked_date` between '$this->startTime' and '$this->endTime' ");

        if (count($inputs) > 0) {
            return $inputs;
        } else {
            return [];
        }
    }
}
