<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LocationsExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */


    public function headings(): array
    {
        return [
            'barcode',
            'default_storage_box',
            'default_material_sku',
            'display_name'
        ];
    }

    public function title(): string
    {
        return 'barcodeSetting';
    }

    public function array(): array
    {
        $allLocations = DB::select("SELECT locations.barcode,locations.default_storage_box,storage_items.material_sku,storage_items.material_name
                                    FROM locations
                                    LEFT JOIN storage_items ON locations.id = storage_items.location_id
                                    WHERE locations.barcode LIKE 'A%'
                                    OR locations.barcode LIKE 'M%' ");
        $allLocations = collect($allLocations)->toArray();

        if (count($allLocations) > 0) {

            return $allLocations;
        } else {
            return [];
        }
    }
}
