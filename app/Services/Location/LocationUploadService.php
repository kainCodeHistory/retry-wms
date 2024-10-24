<?php

namespace App\Services\Location;

use App\Imports\LocationsImport;
use App\Repositories\LocationRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\StorageItemRepository;
use App\Services\AppService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class LocationUploadService extends AppService
{
    public $filePath;

    protected $storageItemRepository;
    protected $materialRepository;
    protected $locationRepository;

    public function __construct(StorageItemRepository $storageItemRepository, MaterialRepository $materialRepository, LocationRepository $locationRepository)
    {
        $this->storageItemRepository = $storageItemRepository;
        $this->materialRepository = $materialRepository;
        $this->locationRepository = $locationRepository;
    }

    public function setFilePath(string $filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function exec()
    {
        try {
            $locationImport = new LocationsImport();
            Excel::import($locationImport, $this->filePath);


            DB::beginTransaction();


            $totalCount = 0;


            foreach ($locationImport->sheets as $sheet) {
                foreach ($sheet['sheetData'] as $row) {
                    $barcode = $this->storageItemRepository->search([
                        'location' => $row['barcode'],
                    ]);


                    $sku = $row['default_material_sku'] === null ? '' : $row['default_material_sku'];

                    if ($sku === '') {
                        $material = null;
                    } else {
                        $material = $this->materialRepository->search(['sku' => $sku]);
                        if (count($material) === 0) {
                            throw new Exception("無此 SKU (" . $sku . ')。');
                        } else {
                            $material = $material->get(0);
                        }
                    }
                    $location = $this->locationRepository->search(['barcode' => $row['barcode']]);
                    if (count($location) === 0) {
                        throw ValidationException::withMessages(['input' => "無此 儲位 (" . $row['barcode'] . ')。']);
                    } else {
                        $locationId = $location->get(0)->id;
                    }
                    if (count($barcode) > 0) {
                        $barcode = $barcode->get(0);

                        if ($barcode->material_sku === $sku && $barcode->location === $row['barcode']) {
                            continue;
                        } else if ($sku === '') {
                            DB::statement("DELETE FROM storage_items WHERE location = ? ", [$row['barcode']]);
                            $totalCount += 1;
                        }
                    } else {

                        if ($sku !== '') {
                            DB::statement("DELETE FROM storage_items WHERE location = ? ", [$row['barcode']]);
                            $totalCount += 1;
                            $this->storageItemRepository->create([
                                'material_id' => $material->id,
                                'material_name' => $material->display_name,
                                'material_sku' => $sku,
                                'location_id' => $locationId,
                                'location' => $row['barcode']
                            ]);
                        }

                    }
                }
            }


            DB::commit();

            return [
                'labelCount' => $totalCount
            ];
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex);
            throw ValidationException::withMessages(['input' => $ex->getMessage()]);
        }
    }
}
