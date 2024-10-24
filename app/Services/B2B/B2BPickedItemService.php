<?php

namespace App\Services\B2B;

use App\Jobs\B2BInventoryDebitJob;
use App\Models\B2BStockLog;
use App\Repositories\B2BPickedItemRepository;
use App\Repositories\MaterialRepository;
use App\Services\AppService;
use App\Services\B2BStock\UpdateB2BStockService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class B2BPickedItemService extends AppService
{
    protected $box;
    protected $materialRepository;
    protected $payload;
    protected $b2bPickedItemRepository;
    protected $workingDay;

    public function __construct(MaterialRepository $materialRepository, B2BPickedItemRepository $b2bPickedItemRepository)
    {
        $this->materialRepository = $materialRepository;
        $this->b2bPickedItemRepository = $b2bPickedItemRepository;
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
        return $this;
    }

    public function exec()
    {
        $sysTZ = config('app.timezone', 'UTC');
        $workingDay = Carbon::now($sysTZ);
        $this->validate(
            $this->payload,
            [
                'sku' => 'required|string',
                'employee' => 'required|string',
                'orderList' => 'required|string',
                'quantity' => 'required|integer|min:1'
            ],
            [
                'sku.required' => 'SKU必須有值。',
                'employee.required' => '工號必須有值。',
                'orderList.required' => '總表單號必須有值。',
                'quantity.required' => '數量必須有值。',
                'quantity.min' => '數量必須大於 0。'
            ]
        );

        try {
            DB::beginTransaction();
            $material = $this->materialRepository->search([
                'sku' => $this->payload['sku']
            ]);

            $batchKey = $this->payload['batchKey'] ?? '';
            $orderNumber = $this->payload['orderNumber'] ?? '';
            if (count($material) > 0) {
                $this->b2bPickedItemRepository->create([
                    'batch_key' => $batchKey,
                    'picked_date' => $workingDay->toDateString(),
                    'sku' => $this->payload['sku'],
                    'total_list' => $this->payload['orderList'],
                    'order_number' => $orderNumber,
                    'quantity' => (int)$this->payload['quantity'],
                    'employee_no' => $this->payload['employee'],
                    'created_at' => $workingDay->format('Y-m-d H:i:s'),
                    'updated_at' => $workingDay->format('Y-m-d H:i:s')
                ]);

                $this->payload['batchIds'] = $batchKey;

                dispatch(new B2BInventoryDebitJob($this->payload))
                    ->onQueue('wms-b2b-inventory-debit');
                DB::commit();
            } else {
                throw ValidationException::withMessages(['messages' => '料號不存在']);
            }


            return [
                'sku' => $this->payload['sku'],
                'quantity' =>  $this->payload['quantity']
            ];
        } catch (Exception $ex) {
            throw ValidationException::withMessages(['location' => $ex->getMessage()]);
            DB::rollBack();
        }
    }
}
