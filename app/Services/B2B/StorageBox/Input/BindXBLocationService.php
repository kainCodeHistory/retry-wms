<?php

namespace App\Services\B2B\StorageBox\Input;

use App\Models\B2BStockLog;
use App\Models\StorageBox\StorageBox;
use App\Models\Transaction;
use App\Repositories\LocationRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Repositories\TransactionRepository;
use App\Services\AppService;
use App\Services\B2BStock\UpdateB2BStockService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BindXBLocationService extends AppService
{
    protected $locationRepository;
    protected $materialRepository;
    protected $storageBoxRepository;
    protected $storageBoxItemRepository;
    protected $transactionRepository;
    protected $payload;

    public function __construct(LocationRepository $locationRepository, MaterialRepository $materialRepository, StorageBoxRepository $storageBoxRepository, StorageBoxItemRepository $storageBoxItemRepository, TransactionRepository $transactionRepository)
    {
        $this->locationRepository = $locationRepository;
        $this->materialRepository = $materialRepository;
        $this->storageBoxRepository = $storageBoxRepository;
        $this->storageBoxItemRepository = $storageBoxItemRepository;
        $this->transactionRepository = $transactionRepository;
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

        if (substr($this->payload['location'], 0, 2) !== 'XB') {
            throw ValidationException::withMessages(['location' => '請掃描 XB 區儲位 (' . $this->payload['location'] . ')。']);
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


        if (is_null($location)) {
            throw ValidationException::withMessages(['location' => '無此儲位 (' . $this->payload['location'] . ')。']);
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
                    'bound_material_at' => Carbon::now()
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
                'event' => Transaction::STORAGE_BOX_INPUT,
                'user' => Auth::user()->id
            ]);

            app(UpdateB2BStockService::class)
                ->setPayload([
                    'items' => [
                        [
                            'sku' =>  $material->sku,
                            'quantity' => (int)$this->payload['quantity'],
                            'event' => B2BStockLog::STOCK_INPUT,
                            'eventKey' => $storageBox->barcode,
                            'note' => ''
                        ]
                    ]
                ])
                ->exec();

            DB::commit();

            return $transaction;
        } catch (Exception $ex) {
            throw ValidationException::withMessages(['location' => $ex->getMessage()]);
            DB::rollBack();
        }
    }
}
