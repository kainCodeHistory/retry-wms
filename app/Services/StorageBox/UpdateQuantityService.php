<?php

namespace App\Services\StorageBox;

use App\Models\B2CStockLog;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Services\AppService;
use App\Services\B2CStock\UpdateB2CStockService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Libs\ShippingServer\ShippingServerService;

class UpdateQuantityService extends AppService
{
    protected $payload;

    protected $storageBoxItemRepository;

    protected $shippingServerService;
    protected $storageBoxRepository;

    public function __construct(StorageBoxItemRepository $storageBoxItemRepository, ShippingServerService $shippingServerService, StorageBoxRepository $storageBoxRepository)
    {
        $this->storageBoxItemRepository = $storageBoxItemRepository;
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
                'storageBox' => 'required|string'
            ],
            [
                'adjustQuantity.required' => '調整數量/復歸數量為必填項目。',
                'event.required' => '調整原因為必填項目。',
                'storageBox.required' => '貨箱條碼為必填項目。'
            ]
        );
        $storageBoxItem = $this->storageBoxItemRepository->search([
            'storage_box' => $this->payload['storageBox']
        ])->first();

        if (is_null($storageBoxItem)) {
            throw new ValidationException("此貨箱 (" . $this->payload['storageBox'] . ') 無綁定物料。');
        }

        $storageBox = $storageBoxItem->storageBox;
        $storageZone = config('storageBoxZone.storage');
        $floor = (array_values($storageZone['3F']));
        $storageBox = $this->storageBoxRepository->search(['barcode' => $storageBoxItem->storage_box])->first();
        if (!is_null($storageBox)) {
            $prefix =  $storageBox->prefix;

            if (!in_array($prefix, $floor)) {
                throw ValidationException::withMessages(['box' => '此貨箱只能在B2C倉 (' . $this->payload['storageBox'] . ')使用。']);
            }
        }

        try {


            DB::beginTransaction();
            $adjustQuantity = (int)$this->payload['adjustQuantity'];
            $event = strtolower(trim($this->payload['event']));
            $note = empty($this->payload['note']) ? '' : $this->payload['note'];

            $subtotal = 0;

            $payload = [];
            if ($event === B2CStockLog::ADJUST) {
                //單箱扣庫暫停運作
                // $this->updateQuantity($storageBoxItem->id, $adjustQuantity);

                $payload = [
                    'items' => [
                        [
                            'sku' => $storageBoxItem->material_sku,
                            'quantity' => $adjustQuantity - $storageBoxItem->quantity,
                            'event' => B2CStockLog::ADJUST,
                            'eventKey' => $storageBoxItem->storage_box,
                            'note' => $note
                        ]
                    ]
                ];
                $subtotal = $adjustQuantity - $storageBoxItem->quantity;
            } else if ($event === B2CStockLog::TRANSFER_OUTPUT) {
                //單箱扣庫暫停運作
                //  $this->updateQuantity($storageBoxItem->id, $storageBoxItem->quantity - $adjustQuantity);

                $payload = [
                    'items' => [
                        [
                            'sku' => $storageBoxItem->material_sku,
                            'quantity' => $adjustQuantity,
                            'event' => B2CStockLog::TRANSFER_OUTPUT,
                            'eventKey' => $storageBoxItem->storage_box,
                            'note' => $note
                        ]
                    ]
                ];
                $subtotal = $storageBoxItem->quantity - $adjustQuantity;
            } else {
                //單箱扣庫暫停運作
                //  $this->updateQuantity($storageBoxItem->id, $storageBoxItem->quantity + $adjustQuantity);

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
                $subtotal = $storageBoxItem->quantity + $adjustQuantity;
            }



            if ($event !== B2CStockLog::ADJUST) {
                app(UpdateB2CStockService::class)
                ->setPayload($payload)
                ->exec();
                // $this->shippingServerService->upsertPickingAreaInventory(
                //     $storageBoxItem->material_sku,
                //     $event,
                //     $storageBox->location,
                //     0,
                // //     $adjustQuantity
                // );
            }

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

    private function updateQuantity(int $storageBoxItemId, int $subtotal)
    {
        $this->storageBoxItemRepository->update(
            $storageBoxItemId,
            [
                'quantity' => $subtotal
            ]
        );
    }
}
