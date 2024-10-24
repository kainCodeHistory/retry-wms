<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncStorageBoxBoundTimestampCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storageBox:timestamp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新B2C貨箱綁定時間';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // 更新 SKU
        $sql = <<<SQL
            UPDATE `storage_boxes` INNER JOIN `storage_box_items` ON `storage_boxes`.`id` = `storage_box_items`.`storage_box_id`
            SET `storage_boxes`.`sku` = `storage_box_items`.`material_sku`
            WHERE `storage_boxes`.`warehouse_id` IN (1, 2)
        SQL;
        DB::statement($sql);

        // 更新 initial_quantity 及 bound_material_at
        $sql = sprintf(<<<SQL
            UPDATE `storage_boxes` INNER JOIN (
                SELECT `storage_boxes`.`id`, `storage_boxes`.`barcode`, `transactions`.`material_sku`, `transactions`.`quantity`, MAX(`transactions`.`created_at`) As `bound_material_at`
                FROM `storage_boxes` INNER JOIN `transactions` ON `storage_boxes`.`barcode` = `transactions`.`storage_box` AND `storage_boxes`.`sku` = `transactions`.`material_sku`
                WHERE `storage_boxes`.`warehouse_id` IN (1, 2)
                AND `transactions`.`event` = '%s'
                GROUP BY `storage_boxes`.`id`, `storage_boxes`.`barcode`, `transactions`.`material_sku`, `transactions`.`quantity`
            ) As `tmp` ON `storage_boxes`.`id` = `tmp`.`id`
            SET `storage_boxes`.`initial_quantity` = `tmp`.`quantity`,
                `storage_boxes`.`bound_material_at` = `tmp`.`bound_material_at`
        SQL, Transaction::ITEM_BOUND);
        DB::statement($sql);

        // 更新 bound_location_at
        $sql = sprintf(<<<SQL
            UPDATE `storage_boxes` INNER JOIN (
                SELECT `storage_boxes`.`id`, `storage_boxes`.`barcode`, `storage_boxes`.`sku`, MIN(`transactions`.`created_at`) As `bound_location_at`
                FROM `storage_boxes` INNER JOIN `transactions` ON `storage_boxes`.`barcode` = `transactions`.`storage_box` AND `storage_boxes`.`sku` = `transactions`.`material_sku`
                WHERE `storage_boxes`.`warehouse_id` IN (1, 2)
                AND `transactions`.`event` = '%s'
                AND `transactions`.`warehouse_id` IN (1, 2)
                GROUP BY `storage_boxes`.`id`, `storage_boxes`.`barcode`, `storage_boxes`.`sku`
            ) As `tmp` ON `storage_boxes`.`id` = `tmp`.`id`
            SET `storage_boxes`.`bound_location_at` = `tmp`.`bound_location_at`
        SQL, Transaction::STORAGE_BOX_INPUT);
        DB::statement($sql);

        // 更新 bound_picking_area_at
        $sql = sprintf(<<<SQL
            UPDATE `storage_boxes` INNER JOIN (
                SELECT `storage_boxes`.`id`, `storage_boxes`.`barcode`, `storage_boxes`.`sku`, MIN(`transactions`.`created_at`) As `bound_picking_area_at`
                FROM `storage_boxes` INNER JOIN `transactions` ON `storage_boxes`.`barcode` = `transactions`.`storage_box` AND `storage_boxes`.`sku` = `transactions`.`material_sku`
                WHERE `storage_boxes`.`warehouse_id` IN (1, 2)
                AND `transactions`.`event` IN ('%s', '%s', '%s')
                AND `transactions`.`warehouse_id` = 1
                GROUP BY `storage_boxes`.`id`, `storage_boxes`.`barcode`, `storage_boxes`.`sku`
            ) As `tmp` ON `storage_boxes`.`id` = `tmp`.`id`
            SET `storage_boxes`.`bound_picking_area_at` = `tmp`.`bound_picking_area_at`
        SQL, Transaction::STORAGE_BOX_INPUT, Transaction::ADJUST_LOCATION, Transaction::REFILL_INPUT);
        DB::statement($sql);
        return 0;
    }
}
