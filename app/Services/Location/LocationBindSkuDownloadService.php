<?php

namespace App\Services\Location;

use App\Exports\SkuBindLocationExport;
use App\Imports\LocationsImport;
use App\Repositories\StorageBox\PickingItemRepository;
use App\Services\AppService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class LocationBindSkuDownloadService extends AppService
{
    public $filePath;

    protected $pickingItemRepository;

    public function __construct(PickingItemRepository $pickingItemRepository)
    {
        $this->pickingItemRepository = $pickingItemRepository;
    }

    public function setFilePath(string $filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function exec()
    {
        try {
            $skuImport = new LocationsImport();
            Excel::import($skuImport, $this->filePath);

            $items = [];
            foreach ($skuImport->sheets as $sheet) {
                foreach ($sheet['sheetData'] as $row) {
                    $sku = $this->pickingItemRepository->search([
                        'material_sku' => $row['sku'],
                        'warehouse_id' => 1
                    ]);
                    if (count($sku) > 0) {
                        $items[] = [
                            'items' => $row['items'],
                            'material_sku' => $row['sku'],
                            'quantity' => $row['quantity'],
                            'box' => $row['box'],
                            'grid' => $row['grid'],
                            'location' => $sku->get(0)->location
                        ];
                    }
                }
            }
        } catch (\Exception $ex) {
            throw ValidationException::withMessages(['input' => $ex->getMessage()]);
        }
        $timestamp = Carbon::now()->format('YmdHis');
        $fileName = 'sku_bind_location' . $timestamp . ".xlsx";
        $skuBindLocationExport = new SkuBindLocationExport($items);
        return Excel::download($skuBindLocationExport, $fileName);
    }
}
