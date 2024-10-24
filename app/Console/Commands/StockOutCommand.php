<?php

namespace App\Console\Commands;

use App\Jobs\InventoryDebitJob;
use App\Repositories\PickedBatchRepository;
use App\Repositories\PickedBatchItemRepository;
use App\Services\StorageBox\Output\InventoryDebitService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Libs\ShippingServer\ShippingServerService;

class StockOutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '補扣庫存';

    protected $pickedBatchRepository;
    protected $pickedBatchItemRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        PickedBatchRepository $pickedBatchRepository,
        PickedBatchItemRepository $pickedBatchItemRepository
    )
    {
        parent::__construct();

        $this->pickedBatchRepository = $pickedBatchRepository;
        $this->pickedBatchItemRepository = $pickedBatchItemRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $stockOutItems = $this->pickedBatchItemRepository->getStockOutItems();

        if (count($stockOutItems) > 0) {
            try {
                DB::beginTransaction();

                $stockOutItemIds = [];
                foreach ($stockOutItems as $stockOutItem) {
                    $flag = app(InventoryDebitService::class)
                        ->runDebit($stockOutItem->check_sku, $stockOutItem->location, (int)$stockOutItem->quantity);

                    if ($flag === 1) {
                        array_push($stockOutItemIds, $stockOutItem->id);
                    }
                }

                $this->pickedBatchItemRepository->updateMany(
                    $stockOutItemIds,
                    [
                        'is_debited' => 1
                    ]
                );

                DB::commit();

                Log::info(sprintf("[stock:out] %s items %s。", count($stockOutItemIds), json_encode($stockOutItemIds)));
            } catch (Exception $ex) {
                DB::rollBack();

                Log::warning(sprintf("stock:out] failed, message: %s", $ex->getMessage()));
            }
        }

        $redoBatches = $this->pickedBatchRepository->search([
            'redo' => 1
        ]);

        if (count($redoBatches) > 0) {
            foreach ($redoBatches as $redoBatch) {
                $payload = app(ShippingServerService::class)
                    ->getPickedItemsByBatchKey($redoBatch->batch_key);


                dispatch(new InventoryDebitJob($payload))
                    ->onQueue('wms-inventory-debit');
            }
        }

        return 0;
    }
}
