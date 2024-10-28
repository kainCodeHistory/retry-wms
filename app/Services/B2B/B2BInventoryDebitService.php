<?php

namespace App\Services\B2B;

use App\Models\B2BStockLog;
use App\Repositories\B2BPickedItemRepository;
use App\Repositories\B2BStockRepository;
use App\Services\AppService;
use App\Services\B2BStock\UpdateB2BStockService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class B2BInventoryDebitService extends AppService
{
    protected $b2bPickedItemRepository;
    protected $b2bStockRepository;

    protected $payload;

    /**
     * Batch Key
     * @var string
     */
    protected $batchKey;

    public function __construct(
        B2BPickedItemRepository $b2bPickedItemRepository,
        B2BStockRepository $b2bStockRepository

    ) {
        $this->b2bPickedItemRepository = $b2bPickedItemRepository;
        $this->b2bStockRepository = $b2bStockRepository;
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;

        $this->saveBatches();

        return $this;
    }

    public function exec()
    {
        try {
            DB::beginTransaction();

            $pickedItems = $this->b2bPickedItemRepository->search([
                'batch_key' => $this->batchKey,
                'is_debited' => 0
            ]);
            $debitedItems = [];
            foreach ($pickedItems as $pickedItem) {

                $payload = [
                    'items' => [
                        [
                            'sku' => $pickedItem->sku,
                            'quantity' => $pickedItem->quantity,
                            'event' => B2BStockLog::ITEM_PICK,
                            'eventKey' => $this->batchKey,
                            'note' => ''
                        ]
                    ]
                ];

                app(UpdateB2BStockService::class)
                    ->setPayload($payload)
                    ->exec();

                array_push($debitedItems, $pickedItem->id);
            }

            if (count($debitedItems) > 0) {
                $this->b2bPickedItemRepository->updateMany($debitedItems, [
                    'is_debited' => 1
                ]);
            }


            DB::commit();

            Log::info("Inventory debit: " . $this->batchKey);
        } catch (Exception $ex) {
            DB::rollBack();

            Log::error("Inventory debit failed: " . $this->batchKey . ", errorMessage: " . $ex->getMessage());
        }
    }

    public function saveBatches()
    {
        $batchIds = $this->payload['batchIds'];
        if ($batchIds === '') {
            $this->batchKey = '';
            return;
        }
        sort($batchIds);

        $this->batchKey = implode("-", $batchIds);

        $pickedBatch = $this->b2bPickedItemRepository->search([
            'batch_key' => $this->batchKey
        ])->first();


        if (!is_null($pickedBatch)) {
            if ((int)$pickedBatch->is_debited === 0) {
                Log::warning(sprintf("Duplicate batch key: %s", $this->batchKey));
                return;
            }
        }

        try {
            $sysTZ = config('app.timezone', 'UTC');
            $workingDay = Carbon::now($sysTZ)->timezone('Asia/Taipei');

            DB::beginTransaction();

            $pickedItems = [];

            foreach ($this->payload['pickedItems'] as $pickedItem) {
                array_push($pickedItems, [
                    'batch_key' => $this->batchKey,
                    'picked_date' => $workingDay->toDateString(),
                    'sku' => $pickedItem['sku'],
                    'quantity' => (int)$pickedItem['quantity'],
                    'is_debited' => 0,
                    'employee_no' => $pickedItem['employeeNo'],
                    'created_at' => $workingDay->format('Y-m-d H:i:s'),
                    'updated_at' => $workingDay->format('Y-m-d H:i:s')
                ]);
            }

            $this->b2bPickedItemRepository->createMany($pickedItems);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();

            if (is_null($pickedBatch)) {
                $this->b2bPickedItemRepository->create([
                    'batch_key' => $this->batchKey,
                    'picked_date' => $workingDay->toDateString(),
                    'sku' => $pickedItem['sku'],
                    'is_debited' => 0,
                    'employee_no' => $pickedItem['employeeNo'],
                    'created_at' => $workingDay->format('Y-m-d H:i:s'),
                    'updated_at' => $workingDay->format('Y-m-d H:i:s')
                ]);
            }

           
        }
    }
}
