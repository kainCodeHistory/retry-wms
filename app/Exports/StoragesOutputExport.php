<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StoragesOutputExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $start_time;
    protected $end_time;
    protected $startTime;
    protected $endTime;

    public function __construct($startTime, $endTime)
    {
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function headings(): array
    {
        return [
            'SKU',
            '品名',
            '批號',
            '數量',
            '儲位',
            '箱號',
            '時間',
            '人員'
        ];
    }

    public function title(): string
    {
        return '特定區間庫存匯出檔';
    }

    public function array(): array
    {
        $start_time = date('Y-m-d H:i', strtotime($this->startTime));
        $end_time = date('Y-m-d H:i', strtotime($this->endTime));
        $storages = DB::select("SELECT transactions.material_sku,materials.display_name,transactions.batch_no,transactions.quantity,transactions.location,transactions.storage_box,transactions.updated_at,users.name
                                FROM `transactions`
                                LEFT JOIN `users` ON transactions.user =users.id
                                LEFT JOIN `materials` ON transactions.material_sku =materials.check_sku || transactions.material_sku =materials.sku
                                WHERE  date_format(transactions.updated_at,'%Y-%m-%d %H:%i') BETWEEN '$start_time' AND '$end_time' && transactions.event= 'storage_box_output' ");
        $storages = collect($storages)->toArray();

        if (count($storages) > 0) {

            return $storages;
        } else {
            return [];
        }
    }
}
