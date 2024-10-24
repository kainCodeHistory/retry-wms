<?php

namespace App\Console\Commands;

use App\Repositories\PickedBatchRepository;
use App\Repositories\PickedBatchItemRepository;
use App\Repositories\TransactionRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear picked_batches, picked_batch_items, transactions data';

    protected $pickedBatchRepository;
    protected $pickedBatchItemRepository;
    protected $transactionRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        PickedBatchRepository $pickedBatchRepository,
        PickedBatchItemRepository $pickedBatchItemRepository,
        TransactionRepository $transactionRepository
    ) {
        parent::__construct();

        $this->pickedBatchRepository = $pickedBatchRepository;
        $this->pickedBatchItemRepository = $pickedBatchItemRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            DB::beginTransaction();

            $days = config('app.reserveDays.pickedBatches', 15);
            $reserveDate = Carbon::now()->subDays($days)->format('Y-m-d');

            DB::statement("DELETE FROM picked_batches WHERE picked_date < ?", [$reserveDate]);
            DB::statement("DELETE FROM picked_batch_items WHERE picked_date < ?", [$reserveDate]);

            $days = config('app.reserveDays.transactions', 100);
            $reserveDate = Carbon::now()->subDays($days)->format('Y-m-d');

            DB::statement("DELETE FROM transactions WHERE created_at < ?", [$reserveDate]);

            DB::commit();

            Log::info(sprintf("Clear data: %s", Carbon::now()->format('Y-m-d')));
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error(sprintf("Clear data failed: %s", $ex->getMessage()));
        }

        return 0;
    }
}
