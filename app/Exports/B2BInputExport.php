<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class B2BInputExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    private $workingDay;

    public function __construct(string $workingDay)
    {
        $this->workingDay = $workingDay;
    }

    public function headings(): array
    {
        return [
            '日期',
            '紙箱條碼',
            'SKU',
            '物料名稱',
            'EAN',
            '數量',
            '使用者',
            '建立時間'
        ];
    }

    public function title(): string
    {
        return sprintf("b2b_%s", $this->workingDay);
    }

    public function array(): array
    {
        $inputs = DB::select("SELECT `b2b_stock_logs`.`working_day`, `b2b_stock_logs`.`event_key`, `b2b_stock_logs`.`sku`, `materials`.`display_name`, CONCAT(' ', `materials`.`ean`) As `ean`, `b2b_stock_logs`.`quantity`, `b2b_stock_logs`.`user_name`, DATE_FORMAT(`b2b_stock_logs`.`created_at`, '%Y-%m-%d %H:%i:%s') As `created_at`
                              FROM `b2b_stock_logs`
                              left join `materials` on materials.sku = b2b_stock_logs.sku
                              WHERE `b2b_stock_logs`.`working_day` = ?
                              and `b2b_stock_logs`.`event` = 'stock_input'
                              ORDER BY `b2b_stock_logs`.`created_at`", [$this->workingDay]);

        if (count($inputs) > 0) {
            return $inputs;
        } else {
            return [];
        }
    }
}
