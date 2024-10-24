<?php

namespace App\Services\PickingArea\Refill;

use App\Repositories\PickingArea\RefillRepository;
use App\Repositories\StorageBox\InventoryItemRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;

class GetUnCompleteRefillRecord extends AppService
{
    protected $rareStorageBox;

    protected $refillRepository;
    protected $inventoryItemRepository;

    public function __construct(RefillRepository $refillRepository, InventoryItemRepository $inventoryItemRepository)
    {
        $this->refillRepository = $refillRepository;
        $this->inventoryItemRepository = $inventoryItemRepository;
    }

    public function exec()
    {
        $records = $this->refillRepository->getProcessingStorageBox('replace');

        if (count($records) === 0) {
            throw ValidationException::withMessages(['refill' => '目前無在途補料作業。']);
        }


        return  $records->toArray();
    }
}
