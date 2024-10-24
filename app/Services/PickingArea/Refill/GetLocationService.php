<?php

namespace App\Services\PickingArea\Refill;

use App\Repositories\PickingArea\RefillRepository;
use App\Repositories\StorageBox\PickingItemRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;

class GetLocationService extends AppService
{
    protected $storageBox;
    protected $pickingItemRepository;
    protected $refillRepository;

    public function __construct(PickingItemRepository $pickingItemRepository, RefillRepository $refillRepository)
    {
        $this->pickingItemRepository = $pickingItemRepository;
        $this->refillRepository = $refillRepository;
    }

    public function setStorageBox(string $storageBox)
    {
        $this->storageBox = $storageBox;
        return $this;
    }

    public function exec()
    {
        $refill = $this->refillRepository->getLocationByStorageBox($this->storageBox, 'processing');

        if (count($refill) === 0) {
            throw ValidationException::withMessages(['refill' => '此貨箱 (' . $this->storageBox . ') 無補料作業紀錄。']);
        }
        $refill = $refill->get(0);

        $boxes = $this->pickingItemRepository->search([
            'location' => $refill->location
        ])->map(function($pickingItem) {
            return [
                'barcode' => $pickingItem->storage_box,
                'release' => false
            ];
        })->toArray();

        return [
            'boxes' => $boxes,
            'id' => $refill->id,
            'designatedLocation' => $refill->location
        ];
    }
}
