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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BindLocationService extends AppService
{
    protected $payload;
    protected $locationRepository;
    protected $materialRepository;
    protected $storageBoxRepository;
    protected $transactionRepository;
    protected $storageBoxItemRepository;

    public function __construct(
        LocationRepository $locationRepository,
        MaterialRepository $materialRepository,
        StorageBoxRepository $storageBoxRepository,
        TransactionRepository $transactionRepository,
        StorageBoxItemRepository $storageBoxItemRepository
    ) {
        $this->locationRepository = $locationRepository;
        $this->materialRepository = $materialRepository;
        $this->storageBoxRepository = $storageBoxRepository;
        $this->transactionRepository = $transactionRepository;
        $this->storageBoxItemRepository = $storageBoxItemRepository;
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

        $storageBoxes = $this->storageBoxRepository->getStorageBoxByStatus($this->payload['storageBox'], [StorageBox::BOUND, StorageBox::STORAGE])->all();
        if (is_null($storageBoxes)) {
            throw ValidationException::withMessages(['box' => '無貨箱無綁定物料紀錄 (' . $this->payload['storageBox'] . ')。']);
        }
        foreach ($storageBoxes as  $storageBox) {



            $location = $this->locationRepository->search([
                'barcode' => $this->payload['location']
            ])->first();


            if (is_null($location)) {
                throw ValidationException::withMessages(['location' => '無此儲位 (' . $this->payload['location'] . ')。']);
            }

            $prefix = substr($location->barcode, 0, 2);

            if ($prefix === 'XB') {
                throw ValidationException::withMessages(['location' => '無法綁定此區 (XB) 儲位 (' . $location->barcode . '）。']);
            }
            $countBoxItemType = $this->storageBoxItemRepository->search(['storage_box_id' => $storageBox->id])->count();
            if ($countBoxItemType === 2 && $prefix === 'XA') {
                throw ValidationException::withMessages(['location' => '此儲位 (' . $this->payload['location'] . ')只能存放箱內料號唯一種的箱子。']);
            }

            try {
                DB::beginTransaction();

                $warehouse = $location->warehouse;

                if ($storageBox->status === StorageBox::BOUND) {

                    // 製造室 -> 入庫
                    $this->storageBoxRepository->update(
                        $storageBox->id,
                        [
                            'warehouse_id' => $warehouse->id,
                            'location' => $location->barcode,
                            'is_empty' => false,
                            'status' => StorageBox::STORAGE,
                            'bound_location_at' => Carbon::now()
                        ]
                    );

                    $tx = $this->transactionRepository->create([
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

                    app(UpdateB2BStockService::class)
                        ->setPayload([
                            'items' => [
                                [
                                    'sku' => $storageBox->material_sku,
                                    'quantity' => $storageBox->quantity,
                                    'event' => B2BStockLog::STOCK_INPUT,
                                    'eventKey' => $storageBox->barcode,
                                    'note' => ''
                                ]
                            ]
                        ])
                        ->exec();

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

                    $tx = $this->transactionRepository->create([
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
                }

                DB::commit();


            } catch (\Exception $ex) {
                DB::rollBack();

                throw ValidationException::withMessages(['box' => $ex->getMessage()]);
            }

        }
        return [
            'transId' => $tx->id
        ];
    }
}
