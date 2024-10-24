<?php

namespace App\Console\Commands;

use App\Repositories\B2CStockRepository;
use App\Repositories\B2CStockLogRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InitializeB2CStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'initialize:b2c-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize B2C inventory logs';

    private $b2cStockRepository;
    private $b2cStockLogRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        B2CStockRepository $b2cStockRepository,
        B2CStockLogRepository $b2cStockLogRepository
    )
    {
        parent::__construct();
        $this->b2cStockRepository = $b2cStockRepository;
        $this->b2cStockLogRepository = $b2cStockLogRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $workingDay = Carbon::now();

        DB::statement("TRUNCATE TABLE `b2c_stock`");

        $sql = <<<SQL
        SELECT `storage_box_items`.`material_sku`, SUM(`storage_box_items`.`quantity`) As `subtotal`
        FROM `storage_boxes` INNER JOIN `storage_box_items` ON `storage_boxes`.`id` = `storage_box_items`.`storage_box_id`
        WHERE `storage_boxes`.`warehouse_id` IN (1, 2)
        GROUP BY `storage_box_items`.`material_sku`
        SQL;

        $stock = [];
        $logs = [];
        $rows = DB::select($sql);

        foreach ($rows as $row) {
            array_push($stock, [
                'sku' => $row->material_sku,
                'total_quantity' => $row->subtotal,
                'created_at' => $workingDay,
                'updated_at' => $workingDay
            ]);

            array_push($logs, [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $row->material_sku,
                'quantity' => 0,
                'balance' => $row->subtotal,
                'event' => 'initial',
                'event_key' => '',
                'created_at' => $workingDay,
                'updated_at' => $workingDay
            ]);
        }

        $this->b2cStockRepository->createMany($stock);
        $this->b2cStockLogRepository->createMany($logs);

        return 0;
    }
}
