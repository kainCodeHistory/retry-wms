<?php

namespace App\Imports;


use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\BeforeSheet;

class LocationsImport implements ToArray, WithEvents, WithHeadingRow
{
    private $sheetName = '';
    private $sheetCount = 0;
    private $isVisible = false;
    public $sheets;

    public function __construct()
    {
        $this->sheets = [];
    }

    public function array(array $array)
    {
        if ($this->isVisible) {
            $this->sheets[] = [
                'sheetIndex' => $this->sheetCount,
                'sheetName' => $this->sheetName,
                'sheetData' => $array
            ];
            $this->sheetCount += 1;
        }
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $currentSheet = $event->sheet->getDelegate();
                $this->isVisible = $currentSheet->getSheetState() === 'visible';
                $this->sheetName = $currentSheet->getTitle();

                $highestColumnString = $currentSheet->getHighestDataColumn();
                $highestRow = $currentSheet->getHighestDataRow();
                $currentSheet->getStyle('A1:' . $highestColumnString . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            }
        ];
    }

    public function headingRow(): int
    {
        return 1;
    }
}
