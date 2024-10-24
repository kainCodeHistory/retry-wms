<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RolloverRecordExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */



    public function __construct()
    {
    }

    public function headings(): array
    {
        return [
            '時間',
            'SKU',
            '數量',
            '備註',
        ];
    }

    public function title(): string
    {
        return '成品轉倉紀錄';
    }

    public function array(): array
    {
        $rollover = DB::select("SELECT date(created_at),sku,quantity,note
                                FROM `rollover`
                                WHERE DATE(created_at) = CURDATE()");
        $rollover = collect($rollover)->toArray();

        if (count($rollover) > 0) {

            return $rollover;
        } else {
            return [];
        }
    }
}
