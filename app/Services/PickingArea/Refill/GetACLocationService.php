<?php

namespace App\Services\PickingArea\Refill;

use App\Repositories\LocationRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;

class GetACLocationService extends AppService
{
    protected $storageBox;
    protected $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    public function setStorageBox(string $storageBox)
    {
        $this->storageBox = $storageBox;
        return $this;
    }

    public function exec()
    {
            $location = $this->locationRepository->search([
                'default_storage_box' => $this->storageBox
            ]);

            if (count($location) === 0) {
                throw ValidationException::withMessages(['location' => '此儲位尚未綁定罕見品箱。']);
            } else {
                $location = $location->get(0);

                return [
                    'storageBox' => $location->default_storage_box,
                    'location' => $location->barcode,
                    'sku' => '',
                    'materialName' => '',
                    'batchNo' => '',
                    'quantity' => 0
                ];
            }
        }
}
