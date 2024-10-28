<?php

namespace App\Services\B2B;

use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Repositories\TransactionRepository;
use App\Services\AppService;
use App\Services\B2BStock\UpdateB2BStockService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Libs\ShippingServer\ShippingServerService;

use Illuminate\Support\Facades\Auth;

class UpdateQuantityService extends AppService
{
    protected $payload;

    protected $storageBoxItemRepository;

    protected $shippingServerService;
    protected $transactionRepository;
    protected $storageBoxRepository;

    public function __construct(StorageBoxItemRepository $storageBoxItemRepository, TransactionRepository $transactionRepository, ShippingServerService $shippingServerService, StorageBoxRepository $storageBoxRepository)
    {
        $this->storageBoxItemRepository = $storageBoxItemRepository;
        $this->transactionRepository = $transactionRepository;
        $this->storageBoxRepository = $storageBoxRepository;
        $this->shippingServerService = $shippingServerService;
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
                'adjustQuantity' => 'required|integer',
                'event' => 'required|string',
                'storageBox' => 'required|string',
                'sku'=>'required|string'
            ],
            [
                'adjustQuantity.required' => '調整數量/復歸數量為必填項目。',
                'event.required' => '調整原因為必填項目。',
                'storageBox.required' => '貨箱條碼為必填項目。',
                'sku.required' => 'SKU必須有值'
            ]
        );
        $storageBoxItem = $this->storageBoxItemRepository->search([
            'storage_box' => $this->payload['storageBox'],
            'material_sku' =>$this->payload['sku']
        ])->first();

        if (is_null($storageBoxItem)) {
            throw new ValidationException("此貨箱 (" . $this->payload['storageBox'] . ') 無綁定物料。');
        }

        $storageBox = $storageBoxItem->storageBox;

        $storageBox = $this->storageBoxRepository->search(['barcode' => $storageBoxItem->storage_box])->first();

        try {


            DB::beginTransaction();
            $adjustQuantity = (int)$this->payload['adjustQuantity'];
            $event = strtolower(trim($this->payload['event']));
            $note = empty($this->payload['note']) ? '' : $this->payload['note'];
            $inputOutput = 'input';

            $subtotal = 0;

            $payload = [];
            if ($event === 'adjust') {

                $payload = [
                    'items' => [
                        [
                            'sku' => $storageBoxItem->material_sku,
                            'quantity' => $adjustQuantity - $storageBoxItem->quantity,
                            'event' => 'adjust',
                            'eventKey' => $storageBoxItem->storage_box,
                            'note' => $note
                        ]
                    ]
                ];
                $subtotal = $adjustQuantity;
            } else if ($event ===  "transfer_output") {

                $inputOutput = 'output';
                $payload = [
                    'items' => [
                        [
                            'sku' => $storageBoxItem->material_sku,
                            'quantity' => $adjustQuantity,
                            'event' =>  "transfer_output",
                            'eventKey' => $storageBoxItem->storage_box,
                            'note' => $note
                        ]
                    ]
                ];
                $subtotal = $storageBoxItem->quantity - $adjustQuantity;
            } else {


                $payload = [
                    'items' => [
                        [
                            'sku' => $storageBoxItem->material_sku,
                            'quantity' => $adjustQuantity,
                            'event' => $event,
                            'eventKey' => $storageBoxItem->storage_box,
                            'note' => $note
                        ]
                    ]
                ];
                $subtotal =  $storageBoxItem->quantity + $adjustQuantity;
            }

            $this->transactionRepository->create([
                'warehouse_id' => $storageBox->warehouse_id,
                'location' => $storageBox->location,
                'storage_box' => $storageBoxItem->storage_box,
                'material_id' => $storageBoxItem->material_id,
                'material_sku' => $storageBoxItem->material_sku,
                'batch_no' => $storageBoxItem->batch_no,
                'quantity' => $adjustQuantity,
                'in_out' => $inputOutput,
                'event' => $event,
                'event_key' => $note,
                'user' => Auth::user()->id
            ]);

            $this->storageBoxItemRepository->update( $storageBoxItem->id,['quantity' => $subtotal]);

            app(UpdateB2BStockService::class)
                ->setPayload($payload)
                ->exec();


            DB::commit();

            return [
                'storageBox' => $this->payload['storageBox'],
                'event' => $event,
                'quantity' => $subtotal,
                'hasError' => false,
                'errorMessage' => ''
            ];
        } catch (Exception $ex) {
            DB::rollBack();

            return [
                'storageBox' => $this->payload['storageBox'],
                'event' => $this->payload['event'],
                'adjustQuantity' => $this->payload['adjustQuantity'],
                'hasError' => true,
                'errorMessage' => $ex->getMessage()
            ];
        }
    }
}
