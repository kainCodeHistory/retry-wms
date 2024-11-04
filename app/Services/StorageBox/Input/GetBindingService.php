<?php

namespace App\Services\StorageBox\Input;

use App\Models\StorageBox\StorageBox;
use App\Repositories\LocationRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;

class GetBindingService extends AppService
{
    protected $storageBox;

    protected $inventoryItemRepository;
    protected $locationRepository;
    protected $storageBoxRepository;

    public function __construct(
        LocationRepository $locationRepository,
        StorageBoxRepository $storageBoxRepository
    ) {
        $this->locationRepository = $locationRepository;
        $this->storageBoxRepository = $storageBoxRepository;
    }

    public function setStorageBox(string $storageBox)
    {
        $this->storageBox = $storageBox;
        return $this;
    }

    public function exec()
    {
        $box = $this->storageBoxRepository->getStorageBoxByStatus($this->storageBox, [StorageBox::BOUND, StorageBox::STORAGE])->first();
        if (is_null($box)) {
            throw ValidationException::withMessages(['storageBox' => '此貨箱沒有綁定物料 (' . $this->storageBox . ')。']);
        }
       

        
            // 預備倉儲位
            $location = $this->storageBoxRepository->getSuggestLocations($box->material_sku)->first();

            return [
                'location' => is_null($location) ? 'BZ-01-01' : $location->location,
                'pickLocation' => $this->storageBoxRepository->getSuggestLocations($box->material_sku)
                    ->pluck('location')
                    
            ];
        
    }
}
