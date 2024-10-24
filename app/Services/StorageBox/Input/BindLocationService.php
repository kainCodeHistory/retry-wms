<?php

namespace App\Services\StorageBox\Input;

use App\Models\B2CStockLog;
use App\Models\StorageBox\StorageBox;
use App\Models\Transaction;
use App\Repositories\LocationRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\StorageBox\PickingItemRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Repositories\StorageItemRepository;
use App\Repositories\TransactionRepository;
use App\Services\AppService;
use App\Services\B2CStock\UpdateB2CStockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Libs\ShippingServer\ShippingServerService;

class BindLocationService extends AppService
{
    protected $payload;
    protected $locationRepository;
    protected $materialRepository;
    protected $pickingItemRepository;
    protected $storageBoxRepository;
    protected $transactionRepository;
    protected $storageItemRepository;

    public function __construct(
        LocationRepository $locationRepository,
        MaterialRepository $materialRepository,
        PickingItemRepository $pickingItemRepository,
        StorageBoxRepository $storageBoxRepository,
        TransactionRepository $transactionRepository,
        StorageItemRepository $storageItemRepository
    ) {
        $this->locationRepository = $locationRepository;
        $this->materialRepository = $materialRepository;
        $this->pickingItemRepository = $pickingItemRepository;
        $this->storageBoxRepository = $storageBoxRepository;
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
                'storageBox' => 'required|string',
            ],
            [
                'location.required' => '儲位必須有值。',
                'storageBox.required' => '貨箱條碼必須有值。',
            ]
        );

        $storageBox = $this->storageBoxRepository->getStorageBoxByStatus($this->payload['storageBox'], [StorageBox::BOUND, StorageBox::STORAGE])->first();
        if (is_null($storageBox)) {
            throw ValidationException::withMessages(['box' => '無貨箱無綁定物料紀錄 (' . $this->payload['storageBox'] . ')。']);
        }

        $prefix = $storageBox->prefix;
        $storageZone = config('storageBoxZone.storage');
        $floor = (array_values($storageZone['3F']));
        if (!in_array($prefix, $floor)) {
            throw ValidationException::withMessages(['box' => '此貨箱只能綁定在B2B倉 (' . $this->payload['storageBox'] . ')。']);
        }

        $alreadyStorageBox = $this->storageBoxRepository->getAlreadyBindBox($this->payload['storageBox'])->first();
        if (!is_null($alreadyStorageBox)) {
            throw ValidationException::withMessages(['box' => '此貨箱已綁定在 (' .  $alreadyStorageBox->location . ')。']);
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

        $prefix = substr($location->barcode, 0, 2);

        if ($prefix === 'AA' || $prefix === 'AB' || $prefix === 'MN') {
            $pickingItems = $this->pickingItemRepository->search([
                'location' => $location->barcode
            ]);

            if (count($pickingItems) > 0) {
                if ($prefix === 'AA' || $prefix === 'MN') {
                    if (count($pickingItems) > 1) {
                        throw ValidationException::withMessages(['location' => '此儲位最多只能綁定兩個貨箱 (' . $location->barcode . ')。']);
                    }
                } else {
                    throw ValidationException::withMessages(['location' => '此儲位最多只能綁定一個貨箱 (' . $location->barcode . ')。']);
                }
            }

            if (is_null($storageItem)) {
                throw ValidationException::withMessages(['location' => '此儲位無預設綁定物料設定 (' . $this->payload['location'] . ')。']);
            }

            if ($storageBox->material_sku !== $storageItem->material_sku) {
                throw ValidationException::withMessages(['location' => '此貨箱 SKU (' . $storageBox->material_sku . ') 與此儲位預設的 SKU (' . $storageItem->material_sku . ') 不一致。']);
            }
        } else if ($prefix === 'AC') {
            throw ValidationException::withMessages(['location' => '無法綁定此區 (AC) 儲位 (' . $location->barcode . '）。']);
        }

        try {
            DB::beginTransaction();

            $warehouse = $location->warehouse;

            if ($storageBox->status === StorageBox::BOUND) {
                // 製造室 -> 入庫
                $payload = [
                    'warehouse_id' => $warehouse->id,
                    'location' => $location->barcode,
                    'is_empty' => false,
                    'status' => StorageBox::STORAGE,
                    'bound_location_at' => Carbon::now()
                ];

                // 檢料倉 設定綁定時間
                if ($warehouse->id === 1) {
                    $payload['bound_picking_area_at'] = Carbon::now();
                }

                $this->storageBoxRepository->update(
                    $storageBox->id,
                    $payload
                );

                app(UpdateB2CStockService::class)
                    ->setPayload([
                        'items' => [
                            [
                                'sku' => $storageBox->material_sku,
                                'quantity' => $storageBox->quantity,
                                'event' => B2CStockLog::STOCK_INPUT,
                                'eventKey' => $storageBox->barcode,
                                'note' => ''
                            ]
                        ]
                    ])
                    ->exec();

                $this->transactionRepository->create([
                    'warehouse_id' => $warehouse->id,
                    'location' => $location->barcode,
                    'storage_box' => $storageBox->barcode,
                    'material_id' => $storageBox->material_id,
                    'material_sku' => $storageBox->material_sku,
                    'batch_no' => $storageBox->batch_no,
                    'quantity' => $storageBox->quantity,
                    'in_out' => 'input',
                    'event' => Transaction::STORAGE_BOX_INPUT,
                    'event_key' => '',
                    'user' => Auth::user()->id
                ]);

                // app(ShippingServerService::class)
                //     ->upsertPickingAreaInventory($storageBox->material_sku, Transaction::STORAGE_BOX_INPUT, $location->barcode, $location->priority, $storageBox->quantity);
            } else {
                // 儲位重設
                $this->storageBoxRepository->update(
                    $storageBox->id,
                    [
                        'warehouse_id' => $warehouse->id,
                        'location' => $location->barcode,
                        'is_empty' => false
                    ]
                );

                if ($warehouse->id === 1) {
                    $this->storageBoxRepository->updateBoundPickingAreaTimestamp($storageBox->id, Carbon::now());
                }

                $this->transactionRepository->create([
                    'warehouse_id' => $warehouse->id,
                    'location' => $location->barcode,
                    'storage_box' => $storageBox->barcode,
                    'material_id' => $storageBox->material_id,
                    'material_sku' => $storageBox->material_sku,
                    'batch_no' => $storageBox->batch_no,
                    'quantity' => $storageBox->quantity,
                    'in_out' => 'input',
                    'event' => Transaction::ADJUST_LOCATION,
                    'event_key' => '',
                    'user' => Auth::user()->id
                ]);

                if (!str_starts_with($location->barcode, "B")) {
                    // app(ShippingServerService::class)
                    //     ->upsertPickingAreaInventory($storageBox->material_sku, Transaction::ADJUST_LOCATION, $location->barcode, $location->priority, $storageBox->quantity);
                }
            }

            DB::commit();

            return [
                'ok' => true
            ];
        } catch (\Exception $ex) {
            DB::rollBack();

            throw ValidationException::withMessages(['box' => $ex->getMessage()]);
        }
    }
}
