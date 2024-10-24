<?php

namespace App\Services\StorageBox\Input;

use App\Models\StorageBox\StorageBox;
use App\Repositories\LocationRepository;
use App\Repositories\StorageBox\InventoryItemRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Repositories\StorageItemRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;

class GetBindingService extends AppService
{
    protected $storageBox;

    protected $inventoryItemRepository;
    protected $locationRepository;
    protected $storageBoxRepository;
    protected $storageItemRepository;

    public function __construct(
        InventoryItemRepository $inventoryItemRepository,
        LocationRepository $locationRepository,
        StorageBoxRepository $storageBoxRepository,
        StorageItemRepository $storageItemRepository
    ) {
        $this->inventoryItemRepository = $inventoryItemRepository;
        $this->locationRepository = $locationRepository;
        $this->storageBoxRepository = $storageBoxRepository;
        $this->storageItemRepository = $storageItemRepository;
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
        $prefix = $box->prefix;
        $storageZone = config('storageBoxZone.storage');
        $floor = (array_values($storageZone['3F']));

        if (in_array($prefix, $floor)) {
            // 預備倉儲位
            $location = $this->inventoryItemRepository->getSuggestLocations($box->material_sku)->first();

            return [
                'location' => is_null($location) ? 'BZ-01-01' : $location->location,
                'pickLocation' => $this->storageItemRepository->get3FPickingLocations($box->material_sku)
                    ->pluck('location')
                    
            ];
        } else {
            return [
                'pickLocation' => $this->storageItemRepository->get5FPickingLocations($box->material_sku)
                    ->pluck('location')
                    
            ];
        }
    }
}
