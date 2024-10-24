<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StoragesInventoryExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */


    public function headings(): array
    {
        return [
            'SKU',
            '品名',
            '批號',
            '數量',
            '儲位',
            '箱號'
        ];
    }

    public function title(): string
    {
        return '總庫存匯出檔';
    }

    public function array(): array
    {
        $allRawMaterials = DB::select("SELECT material_sku,material_name,batch_no,quantity,storage_boxes.location,storage_box
                                       FROM `storage_box_items`
                                       LEFT JOIN `storage_boxes` ON storage_box_items.storage_box = storage_boxes.barcode ");
        $allRawMaterials = collect($allRawMaterials)->toArray();

        if (count($allRawMaterials) > 0) {

            return $allRawMaterials;
        } else {
            return [];
        }
    }
}
