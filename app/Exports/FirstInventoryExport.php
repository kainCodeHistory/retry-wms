<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FirstInventoryExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
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
            '倉別',
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
        return '初盤匯出檔';
    }

    public function array(): array
    {
        $first = DB::select("SELECT warehouses.name,inventory.material_sku,inventory.material_name,inventory.batch_no,inventory.first_quantity,inventory.location,inventory.storage_box
                             FROM `inventory`
                             Left  JOIN warehouses on  inventory.warehouse_id = warehouses.id
                             UNION ALL
                             SELECT warehouses.name,extra_raw_materials.relied_sku, `materials`.display_name, inventory.batch_no, inventory.first_quantity, inventory.location, inventory.storage_box
                             FROM `inventory`
                             INNER JOIN `extra_raw_materials` ON `inventory`.`material_sku` = `extra_raw_materials`.`sku`
                             INNER JOIN `materials` ON `extra_raw_materials`.`sku` = `materials`.`sku`
                             Left  JOIN warehouses on inventory.warehouse_id = warehouses.id ");
        $first = collect($first)->toArray();

        if (count($first) > 0) {

            return $first;
        } else {
            return [];
        }
    }
}
