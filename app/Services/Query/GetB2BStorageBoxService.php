<?php

namespace App\Services\Query;

use App\Repositories\StorageBox\StorageBoxRepository;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Services\AppService;

class GetB2BStorageBoxService extends AppService
{
    private $barcode;

    private $storageBoxRepository;
    private $storageBoxItemRepository;

    public function __construct(StorageBoxRepository $storageBoxRepository, StorageBoxItemRepository $storageBoxItemRepository)
    {
        $this->storageBoxRepository = $storageBoxRepository;
        $this->storageBoxItemRepository = $storageBoxItemRepository;
    }

    public function setBarcode(string $barcode)
    {
        $this->barcode = $barcode;
        return $this;
    }

    public function exec()
    {
        $storageBoxes = $this->storageBoxRepository->getStorageBoxesByLocation($this->barcode);

        if (count($storageBoxes)) {

            return [
                'storageDetail' => [$storageBoxes->toArray()]
            ];
        } else {
            return [
                'storageDetail' => []
            ];
        }
    }
}
