<?php

namespace App\Services\StorageBox\Output;

use App\Models\B2CStockLog;
use App\Repositories\PickedBatchRepository;
use App\Repositories\PickedBatchItemRepository;
use App\Repositories\StorageBox\PickingItemRepository;
use App\Repositories\StorageBox\StorageBoxItemRepository;
use App\Services\AppService;
use App\Services\B2CStock\UpdateB2CStockService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Libs\Slack\SlackService;

class InventoryDebitService extends AppService
{
    protected $pickedBatchRepository;
    protected $pickedBatchItemRepository;
    protected $pickingItemRepository;
    protected $storageBoxItemRepository;

    protected $payload;

    /**
     * Batch Key
     * @var string
     */
    protected $batchKey;

    public function __construct(
        PickedBatchRepository $pickedBatchRepository,
        PickedBatchItemRepository $pickedBatchItemRepository,
        PickingItemRepository $pickingItemRepository,
        StorageBoxItemRepository $storageBoxItemRepository
    ) {
        $this->pickedBatchRepository = $pickedBatchRepository;
        $this->pickedBatchItemRepository = $pickedBatchItemRepository;
        $this->pickingItemRepository = $pickingItemRepository;
        $this->storageBoxItemRepository = $storageBoxItemRepository;
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

            $pickedItems = $this->pickedBatchItemRepository->search([
                'batch_key' => $this->batchKey
            ]);

            $debitedItems = [];
            foreach ($pickedItems as $pickedItem) {
                // AZ 不需扣帳
                if (str_starts_with($pickedItem->location, 'AZ')) {
                    continue;
                }

                // 單箱扣庫暫停運作
                // $flag = $this->runDebit(
                //     $pickedItem->check_sku,
                //     $pickedItem->location,
                //     $pickedItem->quantity
                // );

                // if ($flag === 1) {
                $payload = [
                    'items' => [
                        [
                            'sku' => $pickedItem->check_sku,
                            'quantity' => $pickedItem->quantity,
                            'event' => B2CStockLog::ITEM_PICK,
                            'eventKey' => $this->batchKey,
                            'note' => ''
                        ]
                    ]
                ];

                app(UpdateB2CStockService::class)
                    ->setPayload($payload)
                    ->exec();

                array_push($debitedItems, $pickedItem->id);
                // }
            }

            if (count($debitedItems) > 0) {
                $this->pickedBatchItemRepository->updateMany($debitedItems, [
                    'is_debited' => 1
                ]);
            }

            $this->pickedBatchRepository->setRedo($this->batchKey, 0);

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
        sort($batchIds);

        $this->batchKey = implode("-", $batchIds);

        $pickedBatch = $this->pickedBatchRepository->search([
            'batch_key' => $this->batchKey
        ])->first();


        if (!is_null($pickedBatch)) {
            if ((int)$pickedBatch->redo === 0) {
                Log::warning(sprintf("Duplicate batch key: %s", $this->batchKey));
                return;
            }
        }

        try {
            $sysTZ = config('app.timezone', 'UTC');
            $workingDay = Carbon::now($sysTZ)->timezone('Asia/Taipei');

            DB::beginTransaction();

            if (is_null($pickedBatch)) {
                $this->pickedBatchRepository->create([
                    'batch_key' => $this->batchKey,
                    'picked_date' => $workingDay->toDateString(),
                    'redo' => 0,
                    'created_at' => $workingDay->format('Y-m-d H:i:s'),
                    'updated_at' => $workingDay->format('Y-m-d H:i:s')
                ]);
            }

            $pickedItems = [];
            foreach ($this->payload['pickedItems'] as $pickedItem) {
                array_push($pickedItems, [
                    'batch_key' => $this->batchKey,
                    'picked_date' => $workingDay->toDateString(),
                    'check_sku' => $pickedItem['check_sku'],
                    'location' => $pickedItem['location'],
                    'quantity' => (int)$pickedItem['quantity'],
                    'is_debited' => 0,
                    'created_at' => $workingDay->format('Y-m-d H:i:s'),
                    'updated_at' => $workingDay->format('Y-m-d H:i:s')
                ]);
            }

            $this->pickedBatchItemRepository->createMany($pickedItems);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();

            if (is_null($pickedBatch)) {
                $this->pickedBatchRepository->create([
                    'batch_key' => $this->batchKey,
                    'picked_date' => $workingDay->toDateString(),
                    'redo' => 1,
                    'created_at' => $workingDay->format('Y-m-d H:i:s'),
                    'updated_at' => $workingDay->format('Y-m-d H:i:s')
                ]);
            }

            $message = sprintf("WMS 扣帳失敗，batch key: %s。", $this->batchKey);
            app(SlackService::class)
                ->sendMessage(config('app.slack.channel.nxl_notification'), $message);
        }
    }

    public function runDebit(string $checkSku, string $location, int $quantity): int
    {
        $pickingItems = $this->pickingItemRepository->getAvailablePickingItems($checkSku, $location);

        if (count($pickingItems) > 0) {
            $subtotal = $quantity;
            while ($subtotal > 0) {
                $storageBox = $pickingItems->shift();

                if ($storageBox->quantity > 0) {
                    if ($storageBox->quantity >= $subtotal) {
                        $this->subtractQuantity($storageBox->storage_box, $checkSku, $subtotal);
                        $subtotal = 0;
                    } else {
                        $this->subtractQuantity($storageBox->storage_box, $checkSku, $storageBox->quantity);
                        $subtotal -= $storageBox->quantity;
                    }
                }

                if ($subtotal === 0) {
                    return 1;
                }

                if (count($pickingItems) === 0 && $subtotal > 0) {
                    $this->subtractQuantity($storageBox->storage_box, $checkSku, $subtotal);

                    $message = sprintf(<<<SLACK
                        WMS 負庫存
                        料號： %s
                        儲位： %s
                        箱號： %s
                        數量： %s
                    SLACK, $checkSku, $location, $storageBox->storage_box, 0 - $subtotal);

                    app(SlackService::class)
                        ->sendMessage(config('app.slack.channel.nxl_notification'), $message);

                    $subtotal = 0;
                    return 1;
                }
            }
        } else {
            $message = sprintf(<<<SLACK
                WMS 扣帳失敗（儲位異動）
                料號： %s
                儲位： %s
                應扣數量： %s
            SLACK, $checkSku, $location, $quantity);

            app(SlackService::class)
                ->sendMessage(config('app.slack.channel.nxl_notification'), $message);
        }

        return 0;
    }

    private function subtractQuantity(string $storageBox, string $checkSku, int $quantity)
    {
        $sql = sprintf(<<<SQL
        UPDATE `storage_box_items`
        SET `quantity` = `quantity` - %s
        WHERE `storage_box` = ? AND `material_sku` = ?
        SQL, $quantity);

        DB::statement($sql, [$storageBox, $checkSku]);
    }
}
