<?php

namespace App\Services\PickingArea\Refill;

use App\Models\Transaction;
use App\Repositories\LocationRepository;
use App\Repositories\PickingArea\RefillRepository;
use App\Repositories\StorageBox\PickingItemRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Repositories\StorageItemRepository;
use App\Repositories\TransactionRepository;
use App\Services\AppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Libs\ShippingServer\ShippingServerService;

class BindLocationService extends AppService
{
    protected $payload;
    protected $locationRepository;
    protected $pickingItemRepository;
    protected $refillRepository;
    protected $storageBoxRepository;
    protected $storageBoxItemRepository;
    protected $storageItemRepository;
    protected $transactionRepository;

    public function __construct(
        LocationRepository $locationRepository,
        PickingItemRepository $pickingItemRepository,
        RefillRepository $refillRepository,
        StorageBoxRepository $storageBoxRepository,
        StorageBoxItemRepository $storageBoxItemRepository,
        TransactionRepository $transactionRepository,
        StorageItemRepository $storageItemRepository
    ) {
        $this->locationRepository = $locationRepository;
        $this->refillRepository = $refillRepository;
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
                'storageBox' => 'required|string'
            ],
            [
                'location.required' => '儲位條碼必須有值。',
                'storageBox.required' => '儲位必須有值。'
            ]
        );

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

        $refill = $this->refillRepository->search([
            'location' => $location->barcode,
            'status' => 'processing'
        ])->first();

        if (is_null($refill)) {
            throw ValidationException::withMessages(['location' => '此儲位目前無有效的補料紀錄 (' . $this->payload['location'] . ')。']);
        }

        $storageBoxItem = $this->storageBoxItemRepository->search([
            'storage_box' => $this->payload['storageBox']
        ])->first();

        if (is_null($storageBoxItem)) {
            throw ValidationException::withMessages(['storageBox' => '此貨箱 (' . $this->payload['storageBox'] . '目前無綁定物料。']);
        }

        if ((int)$storageBoxItem->material_id !== (int)$storageItem->material_id) {
            throw ValidationException::withMessages(['location' => '此貨箱的SKU (' . $storageBoxItem->material_sku . ') 與此儲位預設的 SKU (' . $storageItem->material_sku . ') 不一致。']);
        }

        try {
            DB::beginTransaction();

            // 計算解除綁定的貨箱數量
            $adjustQuantity = 0;
            if (isset($this->payload['releaseBoxes']) && is_array($this->payload['releaseBoxes'])) {
                foreach ($this->payload['releaseBoxes'] as $releaseBox) {
                    $releaseItem = $this->storageBoxItemRepository->search([
                        'storage_box' => $releaseBox
                    ])->first();

                    if (!is_null($releaseItem)) {
                        $this->addTransaction($refill, $releaseBox, $releaseItem->quantity, 'output', Transaction::REFILL_OUTPUT);
                        if ($releaseItem->quantity !== 0) {
                            $adjustQuantity += $releaseItem->quantity;
                        }
                        $this->storageBoxItemRepository->reset($releaseBox);
                    }

                    $this->storageBoxRepository->reset($releaseBox);
                }
            }

            $inputStorageBox = $this->storageBoxRepository->search([
                'barcode' => $refill->repl_storage_box
            ])->get(0);
            // 設定貨箱儲位
            $this->storageBoxRepository->update(
                $inputStorageBox->id,
                [
                    'warehouse_id' => $refill->warehouse_id,
                    'location' => $refill->location,
                ]
            );

            $this->storageBoxRepository->updateBoundPickingAreaTimestamp($inputStorageBox->id, Carbon::now());

            $this->addTransaction($refill, $inputStorageBox->barcode, $refill->repl_quantity, 'input', Transaction::REFILL_INPUT);

            $this->refillRepository->update(
                $refill->id,
                [
                    'status' => 'completed'
                ]
            );

            // 重新計算數量
            // 若調整數量為負，先忽略
            if ($adjustQuantity > 0) {
                if (str_starts_with($location->barcode, 'AA')) {
                    $pickingItems = $this->pickingItemRepository->search([
                        'location' => $location->barcode
                    ]);

                    if (count($pickingItems) > 1) {
                        // 雙箱區有兩箱,修改另外一箱的數量
                        $anotherPickingItem = $pickingItems->filter(function ($item) use ($inputStorageBox) {
                            return $item->storage_box !== $inputStorageBox->barcode;
                        });

                        $anotherPickingItem = $anotherPickingItem->first();
                        $this->storageBoxItemRepository->updateQuantityWithStorageBox($anotherPickingItem->storage_box, $anotherPickingItem->quantity + $adjustQuantity);
                    } else {
                        // 雙箱區只剩一箱 === 補料上架的料箱
                        $this->setStoreageBoxQuantity($storageBoxItem->id, $storageBoxItem->quantity + $adjustQuantity);
                    }
                } else {
                    // 單箱區
                    $this->setStoreageBoxQuantity($storageBoxItem->id, $storageBoxItem->quantity + $adjustQuantity);
                }
            }

            // 更新 Shipping Server - Picking Area Inventory
            // app(ShippingServerService::class)
            //     ->upsertPickingAreaInventory($refill->material_sku, Transaction::REFILL_INPUT, $location->barcode, $location->priority, $refill->repl_quantity);

            DB::commit();

            $refill = $this->refillRepository->findOrFail($refill->id);

            return [
                'id' => $refill->id,
                'status' => $refill->status
            ];
        } catch (\Exception $ex) {
            DB::rollBack();

            throw ValidationException::withMessages(['location' => $ex->getMessage()]);
        }
    }

    private function addTransaction($refill, string $storageBox, int $quantity, string $inOut, string $event)
    {
        $this->transactionRepository->create([
            'warehouse_id' => $refill->warehouse_id,
            'location' => $refill->location,
            'storage_box' => $storageBox,
            'material_id' => $refill->material_id,
            'material_sku' => $refill->material_sku,
            'batch_no' => '',
            'quantity' => $quantity,
            'in_out' => $inOut,
            'event' => $event,
            'event_key' => $refill->id,
            'user' => Auth::user()->id
        ]);
    }

    private function setStoreageBoxQuantity(int $storageBoxItemId, int $quantity)
    {
        $this->storageBoxItemRepository->update(
            $storageBoxItemId,
            [
                'quantity' => $quantity
            ]
        );
    }
}
