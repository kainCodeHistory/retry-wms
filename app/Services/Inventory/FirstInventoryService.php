<?php

namespace App\Services\Inventory;

use App\Repositories\InventoryRepository;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Repositories\WarehouseRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class FirstInventoryService extends AppService
{
    protected $box;
    protected $warehouse_id;

    public function __construct(StorageBoxItemRepository $storageBoxItemRepository, InventoryRepository $inventoryRepository, StorageBoxRepository $storageBoxRepository, WarehouseRepository $warehouseRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
        $this->storageBoxItemRepository = $storageBoxItemRepository;
        $this->storageBoxRepository = $storageBoxRepository;
        $this->warehouseRepository = $warehouseRepository;
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
                'box' => 'required|string',
                'warehouse' => 'required|string'
            ],
            [
                'box.required' => '貨箱條碼必須有值。',
                'firstQuantity.required' => '數量必須有值。'
            ]
        );

        $box = $this->storageBoxItemRepository->getStorages($this->payload['box']);
        $box = $box->get(0);
        $warehouse = $this->storageBoxRepository->search([
            'barcode' => $this->payload['box']
        ])->get(0);
        $warehouseId = $warehouse->warehouse_id;

        try {
            DB::beginTransaction();
            $inventory = $this->inventoryRepository->search([
                'storage_box' => $this->payload['box']
            ]);
            if (count($inventory) === 0) {
                $this->inventoryRepository->create([
                    'warehouse_id' => $warehouseId,
                    'location' => $box->location,
                    'storage_box' => $this->payload['box'],
                    'material_id' => $box->material_id,
                    'material_sku' => $box->material_sku,
                    'material_name' => $box->material_name,
                    'batch_no' => $box->batch_no,
                    'first_quantity' => (int)$this->payload['firstQuantity'],
                    'status' => 'first_inventory',
                    'user_id' => Auth::user()->id
                ]);
            } else {
                $this->inventoryRepository->update(
                    $inventory->get(0)->id,
                    [
                        'first_quantity' => (int)$this->payload['firstQuantity'],
                        'user_id' => Auth::user()->id
                    ]
                );
            }

            DB::commit();

            return [
                'box' => $this->payload['box'],
                'warehouse' => $warehouseId,
                'quantity' =>  $this->payload['firstQuantity']
            ];
        } catch (Exception $ex) {
            throw ValidationException::withMessages(['location' => $ex->getMessage()]);
            DB::rollBack();
        }
    }
}
