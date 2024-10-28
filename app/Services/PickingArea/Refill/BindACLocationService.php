<?php

namespace App\Services\PickingArea\Refill;

use App\Models\StorageBox\StorageBox;
use App\Models\Transaction;
use App\Repositories\LocationRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\StorageBox\PickingItemRepository;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Repositories\StorageItemRepository;
use App\Repositories\TransactionRepository;
use App\Services\AppService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BindACLocationService extends AppService
{
    protected $locationRepository;
    protected $materialRepository;
    protected $pickingItemRepository;
    protected $storageBoxRepository;
    protected $storageBoxItemRepository;
    protected $storageItemRepository;
    protected $transactionRepository;

    private $payload;

    public function __construct(LocationRepository $locationRepository, MaterialRepository $materialRepository, PickingItemRepository $pickingItemRepository, StorageBoxRepository $storageBoxRepository, StorageBoxItemRepository $storageBoxItemRepository, TransactionRepository $transactionRepository,StorageItemRepository $storageItemRepository)
    {
        $this->locationRepository = $locationRepository;
        $this->materialRepository = $materialRepository;
        $this->pickingItemRepository = $pickingItemRepository;
        $this->storageBoxRepository = $storageBoxRepository;
        $this->storageBoxItemRepository = $storageBoxItemRepository;
        $this->transactionRepository = $transactionRepository;
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
                'quantity' => 'required|integer|min:1',
                'sku' => 'required|string',
                'storageBox' => 'required|string',
            ],
            [
                'location.required' => '儲位條碼必須有值。',
                'quantity.required' => '數量必須有值。',
                'quantity.min' => '數量必須大於 0。',
                'sku.required' => 'SKU 必須有值。',
                'storageBox.required' => '貨箱條碼必須有值。',
            ]
        );

        if (substr($this->payload['location'], 0, 2) !== 'AC' && substr($this->payload['location'], 0, 2) !== 'MN') {
            throw ValidationException::withMessages(['location' => '請掃描 AC/MN 區儲位 (' . $this->payload['location'] . ')。']);
        }

        $material = $this->materialRepository->search([
            'sku' => $this->payload['sku']
        ])->first();

        if (is_null($material)) {
            throw ValidationException::withMessages(['sku' => '無此物料 (' . $this->payload['sku'] . ')。']);
        }

        $storageBox = $this->storageBoxRepository->search([
            'barcode' => $this->payload['storageBox']
        ])->first();

        if (is_null($storageBox)) {
            throw ValidationException::withMessages(['storageBox' => '無此罕見品箱 (' . $this->payload['storageBox'] . ')。']);
        }

        $location = $this->locationRepository->search([
            'barcode' => $this->payload['location']
        ])->first();

        $storageItem = $this->storageItemRepository->search([
            'location' => $this->payload['location']
        ])->first();

        if (is_null($location)) {
            throw ValidationException::withMessages(['location' => '無此儲位 (' . $this->payload['location'] . ')。']);
        }

        if (is_null($storageItem)) {
            throw ValidationException::withMessages(['location' => '此儲位無預設綁定物料設定 (' . $this->payload['location'] . ')。']);
        }

        if ($material->id !== (int)$storageItem->material_id) {
            throw ValidationException::withMessages(['location' => '此貨箱的SKU (' . $material->sku . ') 與此儲位預設的 SKU (' . $storageItem->material_sku . ') 不一致。']);
        }

        try {
            DB::beginTransaction();

            $this->storageBoxItemRepository->create([
                'storage_box_id' => $storageBox->id,
                'storage_box' => $storageBox->barcode,
                'material_id' => $material->id,
                'material_sku' => $material->sku,
                'material_name' => $material->display_name,
                'batch_no' => empty($this->payload['batchNo']) ? '' : $this->payload['batchNo'],
                'quantity' => (int)$this->payload['quantity']
            ]);

            $this->storageBoxRepository->update(
                $storageBox->id,
                [
                    'warehouse_id' => $location->warehouse_id,
                    'location' => $location->barcode,
                    'is_empty' => 0,
                    'status' => StorageBox::STORAGE,
                    'sku' => $material->sku,
                    'initial_quantity' => (int)$this->payload['quantity'],
                    'bound_material_at' => Carbon::now(),
                    'bound_location_at' => Carbon::now(),
                    'bound_picking_area_at' => Carbon::now()
                ]
            );

            $transaction = $this->transactionRepository->create([
                'warehouse_id' => $location->warehouse_id,
                'location' => $location->barcode,
                'storage_box' => $storageBox->barcode,
                'material_id' => $material->id,
                'material_sku' => $material->sku,
                'batch_no' => empty($this->payload['batchNo']) ? '' : $this->payload['batchNo'],
                'quantity' => (int)$this->payload['quantity'],
                'in_out' => 'input',
                'event' => Transaction::REFILL_INPUT,
                'user' => Auth::user()->id
            ]);

            DB::commit();

            return $transaction;
        } catch (Exception $ex) {
            throw ValidationException::withMessages(['location' => $ex->getMessage()]);
            DB::rollBack();
        }
    }
}
