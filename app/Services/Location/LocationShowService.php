<?php

namespace App\Services\Location;

use App\Repositories\LocationRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\StorageItemRepository;
use Illuminate\Validation\ValidationException;
use App\Services\AppService;
use Illuminate\Support\Facades\DB;


class LocationShowService extends AppService
{

    protected $locationRepository;

    public function __construct(LocationRepository $locationRepository, MaterialRepository $materialRepository,StorageItemRepository $storageItemRepository)
    {
        $this->locationRepository = $locationRepository;
        $this->materialRepository = $materialRepository;
        $this->storageItemRepository = $storageItemRepository;
    }

    public function setSku(string $sku)
    {
        $this->sku = $sku;
        return $this;
    }




    public function exec()
    {
        $locations = $this->storageItemRepository->getLocations($this->sku);

        $material = $this->materialRepository->findMaterialBySku($this->sku);
        if (count($material) === 0) {
            throw ValidationException::withMessages(['input' => "查無此 SKU (" . $this->sku . ')。']);
        } else {
            if (count($locations) === 0) {
                $emptyLocations = DB::select("select barcode from locations where barcode not in (select location from storage_items)and barcode not like 'B%'");
                $emptyLocations = collect($emptyLocations)->toArray();
                return [
                    'records' => $emptyLocations
                ];
            } else {
                return [
                    'records' => $locations
                ];
            }
        }
    }
}
