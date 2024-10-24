<?php

namespace App\Services\StorageBox;

use App\Repositories\StorageBox\StorageBoxRepository;
use App\Services\AppService;

class PrintLabelService extends AppService
{
    protected $payload;

    protected $storageBoxRepository;

    public function __construct(StorageBoxRepository $storageBoxRepository)
    {
        $this->storageBoxRepository = $storageBoxRepository;
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
                'b2cAddNewStorageBox' => 'required|int',
                'b2bAddNewCartonLabel' => 'required|int'
            ],
            [
                'b2cAddNewStorageBox.required' => '值必須為數字。',
                'b2bAddNewCartonLabel.required' => '值必須為數字。'
            ]
        );
        $b2cStorageBox = $this->payload['b2cAddNewStorageBox'];
        $b2bStorageBox = $this->payload['b2bAddNewCartonLabel'];

        if ($b2cStorageBox > 0) {
            $this->addNewStorage($b2cStorageBox, 'F');
        }
        if ($b2bStorageBox > 0) {
            $this->addNewStorage($b2bStorageBox, 'K');
        }



        return [
             'quantity' => (int)$b2cStorageBox+$b2bStorageBox

        ];
    }
    public function addNewStorage($quantity, $prefix)
    {
        $device = $this->storageBoxRepository->getMaxStorageBox($prefix)->last();
        $padSize = strlen($device->barcode) - 1;
        $values = substr($device->barcode, 1);
        for ($k = 1; $k <= $quantity; $k++) {

            $barcode = $prefix . substr(str_pad('', $padSize, '0') . ((int)$values + $k), -$padSize);

            $this->storageBoxRepository->create([
                'factory_id' => 1,
                'warehouse_id' => null,
                'prefix' => $prefix,
                'barcode' => $barcode,
                'location' => '',
                'is_empty' => 1
            ]);
        }
    }
}
