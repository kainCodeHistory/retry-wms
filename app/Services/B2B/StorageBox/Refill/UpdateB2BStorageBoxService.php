<?php

namespace App\Services\PickingArea\Refill;

use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Repositories\TransactionRepository;
use App\Services\AppService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UpdateB2BStorageBoxService extends AppService
{

    protected $storageBoxItemRepository;
    protected $transactionRepository;
    protected $payload;

    public function __construct(StorageBoxItemRepository $storageBoxItemRepository ,TransactionRepository $transactionRepository)
    {
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
                'quantity' => 'required|numeric|min:1',
                'inputStorageBox' => 'required|string',
                'outputStorageBox' => 'required|string',
            ],
            [
                'quantity.required' => '補料數量必須有值。',
                'inputStorageBox.required' => '轉入貨箱條碼必須有值。',
                'outputStorageBox.required' => '轉出貨箱條碼必須有值。'
            ]
        );

        try {
            $inputStorageBoxItem = $this->storageBoxItemRepository->search([
                'storage_box' => $this->payload['inputStorageBox']
            ])->first();

            if (is_null($inputStorageBoxItem)) {
                throw new ValidationException("此貨箱 (" . $this->payload['inputStorageBox'] . ') 無綁定物料。');
            }

            $outputStorageBoxItem = $this->storageBoxItemRepository->search([
                'storage_box' => $this->payload['outputStorageBox']
            ])->first();

            if (is_null($outputStorageBoxItem)) {
                throw new ValidationException("此貨箱 (" . $this->payload['outputStorageBox'] . ') 無綁定物料。');
            }

            if ($inputStorageBoxItem->material_sku !== $outputStorageBoxItem->material_sku) {
                throw new ValidationException( "兩箱ＳＫＵ不相同");
            }

            DB::beginTransaction();


            $this->storageBoxItemRepository->update(
                $inputStorageBoxItem->id,
                [
                    'quantity' => $inputStorageBoxItem->quantity + (int)$this->payload['quantity']
                ]
            );
            $this->storageBoxItemRepository->update(
                $outputStorageBoxItem->id,
                [
                    'quantity' => $outputStorageBoxItem->quantity - (int)$this->payload['quantity']
                ]
            );

            $inputStorageBox = $inputStorageBoxItem->storageBox;
            $this->transactionRepository->create([
                'warehouse_id' => $inputStorageBox->warehouse_id,
                'location' => $inputStorageBox->location,
                'storage_box' => $inputStorageBoxItem->storage_box,
                'material_id' => $inputStorageBoxItem->material_id,
                'material_sku' => $inputStorageBoxItem->material_sku,
                'batch_no' => $inputStorageBoxItem->batch_no,
                'quantity' => (int)$this->payload['quantity'],
                'in_out' => 'input',
                'event' => 'refill_input',
                'event_key' => $inputStorageBoxItem->quantity,
                'user' => Auth::user()->id
            ]);

            $outputStorageBox = $outputStorageBoxItem->storageBox;
            $this->transactionRepository->create([
                'warehouse_id' => $outputStorageBox->warehouse_id,
                'location' => $outputStorageBox->location,
                'storage_box' => $outputStorageBoxItem->storage_box,
                'material_id' => $outputStorageBoxItem->material_id,
                'material_sku' => $outputStorageBoxItem->material_sku,
                'batch_no' => $outputStorageBoxItem->batch_no,
                'quantity' => (int)$this->payload['quantity'],
                'in_out' => 'output',
                'event' => 'refill_output',
                'event_key' => $outputStorageBoxItem->quantity,
                'user' => Auth::user()->id
            ]);
            DB::commit();
            return [
                'inputStorageBox' => $this->payload['inputStorageBox'],
                'newInputQuantity' => $inputStorageBoxItem->quantity + (int)$this->payload['quantity'],
                'newOutputQuantity' => $outputStorageBoxItem->quantity - (int)$this->payload['quantity'],
                'hasError' => false,
                'errorMessage' => ''
            ];
        } catch (Exception $ex) {
            DB::rollBack();

            return [
                'storageBox' => $this->payload['inputStorageBox'],
                'adjustQuantity' => $this->payload['inputQuantity'],
                'hasError' => true,
                'errorMessage' => $ex->getMessage()
            ];
        }
    }
}
