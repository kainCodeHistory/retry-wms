<?php

namespace App\Services\StorageBox\Input;

use App\Models\StorageBox\StorageBox;
use App\Models\Transaction;
use App\Repositories\MaterialRepository;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Repositories\StorageItemRepository;
use App\Repositories\TransactionRepository;
use App\Services\AppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class BindMaterialService extends AppService
{
    protected $payload;

    protected $materialRepository;
    protected $storageBoxItemRepository;
    protected $storageBoxRepository;
    protected $storageItemRepository;
    protected $transactionRepository;

    public function __construct(
        StorageBoxRepository $storageBoxRepository,
        StorageBoxItemRepository $storageBoxItemRepository,
        MaterialRepository $materialRepository,
        TransactionRepository $transactionRepository,
        StorageItemRepository $storageItemRepository
    ) {
        $this->storageBoxRepository = $storageBoxRepository;
        $this->storageBoxItemRepository = $storageBoxItemRepository;
        $this->materialRepository = $materialRepository;
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
                'storageBox' => 'required|string',
                'sku' => 'required|string',
                'quantity' => 'required|numeric|min:1'
            ],
            [
                'storageBox.required' => '貨箱條碼必須有值。',
                'sku.required' => 'SKU 必須有值。',
                'quantity.required' => '數量必須有值。',
                'quantity.min' => '數量必須大於 0。'
            ]
        );

        $storageBox = $this->storageBoxRepository->search([
            'barcode' => $this->payload['storageBox']
        ])->first();

        if (is_null($storageBox)) {
            throw ValidationException::withMessages(['storageBox' => '無此貨箱條碼 (' . $this->payload['storageBox'] . ')。']);
        }

        $material = $this->materialRepository->search([
            'sku' => $this->payload['sku']
        ])->first();

        if (is_null($material) === 0) {
            throw ValidationException::withMessages(['sku' => '無此 SKU (' . $this->payload['sku'] . ')。']);
        }

        $location = $this->storageItemRepository->search([
            'material_sku' => $this->payload['sku']
        ])->first();

        if (is_null($location)) {
            throw ValidationException::withMessages(['sku' => '無此 SKU (' . $this->payload['sku'] . ') 儲位設定。']);
        }

        $alreadyBindStorageBox = $this->storageBoxItemRepository->search(['storage_box' => $this->payload['storageBox']])->first();
        if (!is_null($alreadyBindStorageBox)) {
            throw ValidationException::withMessages(['storageBox' => '此貨箱已綁定物料 (' . $alreadyBindStorageBox->material_sku  . ')。']);
        }

        try {
            DB::beginTransaction();

            $this->storageBoxItemRepository->reset($storageBox->barcode);
            $this->storageBoxItemRepository->create([
                'storage_box_id' => $storageBox->id,
                'storage_box' => $storageBox->barcode,
                'material_id' => $material->id,
                'material_sku' => $material->sku,
                'material_name' => $material->display_name,
                'batch_no' => empty($this->payload['batchNo']) ? '' : $this->payload['batchNo'],
                'quantity' => (int)$this->payload['quantity']
            ]);

            $this->storageBoxRepository->update($storageBox->id, [
                'warehouse_id' => null,
                'location' => '',
                'is_empty' => 0,
                'status' => StorageBox::BOUND,
                'sku' => $material->sku,
                'initial_quantity' => (int)$this->payload['quantity'],
                'bound_material_at' => Carbon::now()
            ]);

            $this->transactionRepository->create([
                'warehouse_id' => null,
                'location' => '',
                'storage_box' => $storageBox->barcode,
                'material_id' => $material->id,
                'material_sku' => $material->sku,
                'batch_no' => empty($this->payload['batchNo']) ? '' : $this->payload['batchNo'],
                'quantity' => (int)$this->payload['quantity'],
                'in_out' => 'input',
                'event' => Transaction::ITEM_BOUND,
                'event_key' => '',
                'user' => Auth::user()->id
            ]);

            DB::commit();

            return [
                'box' => $storageBox->barcode,
                'sku' => $material->sku,
                'quantity' => $this->payload['quantity'],
                'status' => StorageBox::BOUND
            ];
        } catch (\Exception $ex) {
            DB::rollBack();
            throw ValidationException::withMessages(['box' => $ex->getMessage()]);
        }
    }
}
