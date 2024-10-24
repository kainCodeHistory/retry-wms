<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CheckInventoryExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
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
        return '複盤匯出檔';
    }

    public function array(): array
    {
        $check = DB::select("SELECT warehouses.name,inventory.material_sku,inventory.material_name,inventory.batch_no,inventory.check_quantity,inventory.location,inventory.storage_box
                             FROM `inventory`
                             LEFT JOIN warehouses ON  inventory.warehouse_id = warehouses.id
                             UNION ALL
                             SELECT warehouses.name,extra_raw_materials.relied_sku, `materials`.display_name, inventory.batch_no, inventory.check_quantity, inventory.location, inventory.storage_box
                             FROM `inventory`
                             INNER JOIN `extra_raw_materials` ON `inventory`.`material_sku` = `extra_raw_materials`.`sku`
                             INNER JOIN `materials` ON `extra_raw_materials`.`sku` = `materials`.`sku`
                             LEFT JOIN warehouses ON inventory.warehouse_id = warehouses.id ");
        $check = collect($check)->toArray();

        if (count($check) > 0) {

            return $check;
        } else {
            return [];
        }
    }
}
