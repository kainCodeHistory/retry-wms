<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SkuBindLocationExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $items;
    public function __construct($items)
    {
        $this->items = $items;
    }

    public function headings(): array
    {
        return [
            'items',
            'sku',
            'quantity',
            'box',
            'grid',
            'picking_location',
        ];
    }

    public function title(): string
    {
        return '復歸檔';
    }

    public function array(): array
    {
        $items = $this->items;
        if (count($items) > 0) {
            return $items;
        } else {
            return [];
        }
    }
}
