<?php

namespace App\Services\Location;

use App\Repositories\LocationRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\StorageItemRepository;
use Illuminate\Validation\ValidationException;
use App\Services\AppService;
use Illuminate\Support\Facades\DB;
use Exception;

use Illuminate\Support\Facades\Log;

class LocationUpdateService extends AppService
{

    protected $locationRepository;
    protected $message;

    public function __construct(LocationRepository $locationRepository, MaterialRepository $materialRepository, StorageItemRepository $storageItemRepository)
    {
        $this->locationRepository = $locationRepository;
        $this->materialRepository = $materialRepository;
        $this->storageItemRepository = $storageItemRepository;
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
        return $this;
    }

    public function exec()
    {
        $this->validate(
            $this->payload,
            [
                'location' => 'required|string',
            ],
            [
                'location.required' => '儲位必須有值。',
            ]
        );

        $Sku = $this->payload['sku'] === null ? '' : $this->payload['sku'];
        $oldSku = $this->payload['oldSku'];
        if ($Sku === '') {
            $material = null;
        } else {
            $material = $this->materialRepository->search(['sku' => $Sku]);
            if (count($material) === 0) {
                throw ValidationException::withMessages(['input' => "無此 SKU (" . $Sku . ')。']);
            } else {
                $material = $material->get(0);
            }
        }
        $location = $this->locationRepository->search(['barcode' => $this->payload['location']]);
        if (count($location) === 0) {
            throw ValidationException::withMessages(['input' => "無此 儲位 (" . $this->payload['location'] . ')。']);
        } else {
            $location = $location->get(0)->id;
        }
        if ($Sku !== '') {
            $this->storageItemRepository->updateLocations($this->payload['location'], ['material_id' => $material->id, 'material_name' => $material->display_name, 'material_sku' => $Sku, 'location_id' => $location, 'location' => $this->payload['location']]);
        } else {
            try {
                DB::beginTransaction();
                DB::statement("DELETE FROM storage_items WHERE `location` = ? and `material_sku` = ?", [$this->payload['location'], $oldSku]);
                DB::commit();
            } catch (Exception $ex) {
                DB::rollBack();
            }
        }
        $message = "更改成功。";

        return [
            'editMessage' => $message
        ];
    }
}
