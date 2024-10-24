<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPickingAreaInventoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:pickingAreaInventory {server} {--az}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync picking_area_inventory';

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
        $server = $this->argument("server");
        $az = $this->option("az");

        DB::connection($server)->statement("TRUNCATE TABLE `picking_area_inventory`");
        DB::connection($server)->statement("TRUNCATE TABLE `b2b_picking_area_inventory`");
        $sql = <<<SQL
            SELECT `picking_area`.`location`, `picking_area`.`material_sku`, IFNULL(`picking_area`.`priority`, 0) As `priority`, IFNULL(`reserve_area`.`subtotal`, 0) as `reserve_quantity`, IFNULL( `picking_area`.`subtotal`,0) as`subtotal`
            FROM (
                SELECT `storage_items`.`location`, `storage_items`.`material_sku`, `locations`.`priority`, SUM(`storage_box_items`.`quantity`) As `subtotal`
                FROM `storage_items` Left JOIN `storage_box_items` ON `storage_items`.`material_sku` = `storage_box_items`.`material_sku`
                LEFT JOIN `locations` ON `storage_items`.`location` = `locations`.`barcode`
                GROUP BY `storage_items`.`location`, `storage_items`.`material_sku`, `locations`.`priority`
            ) As `picking_area` LEFT JOIN (
                SELECT `storage_box_items`.`material_sku`, SUM(`storage_box_items`.`quantity`) As `subtotal`
                FROM `storage_boxes` INNER JOIN `storage_box_items` ON `storage_boxes`.`id` = `storage_box_items`.`storage_box_id`
                WHERE `storage_boxes`.`is_empty` = 0 AND `storage_boxes`.`warehouse_id` = 2
                GROUP BY `storage_box_items`.`material_sku`
            ) As `reserve_area` ON `picking_area`.`material_sku` = `reserve_area`.`material_sku`
        SQL;
        $items = DB::select($sql);

        foreach ($items as $item) {
            DB::connection($server)->statement("INSERT INTO `picking_area_inventory` (`check_sku`, `location`, `location_priority`, `quantity`, `picked_quantity`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, 0, NOW(), NOW())", [
                $item->material_sku,
                $item->location,
                $item->priority,
                $item->subtotal
            ]);
        }

        if ($az) {
            $sql = <<<SQL
                SELECT `shipment_items`.`sku`, `shipment_items`.`check_sku`, `shipment_items`.`locations`, SUM(`shipment_items`.`quantity`) As `quantity`
                FROM `shipment_items` INNER JOIN `shipments` ON `shipment_items`.`shipment_id` = `shipments`.`id`
                WHERE `shipments`.`status` = 'received_label' AND `shipments`.`is_cancelled` = 0 AND `shipment_items`.`locations` LIKE '%AZ%' GROUP BY `shipment_items`.`sku`, `shipment_items`.`check_sku`, `shipment_items`.`locations`
            SQL;
            $azItems = DB::connection($server)->select($sql);

            if (count($azItems) > 0) {
                collect($azItems)->groupBy('locations')->map(function ($azItems, $locations) use ($server) {
                    $location = json_decode($locations)[0];

                    foreach ($azItems as $azItem) {
                        $checkSkuSql = <<<SQL
                        SELECT * FROM `picking_area_inventory` WHERE `check_sku` = ?
                    SQL;
                        $getCheckSKu = DB::connection($server)->select($checkSkuSql, [$azItem->check_sku]);
                        if (count($getCheckSKu) > 0) {
                            continue;
                        }

                        DB::connection($server)->statement("INSERT INTO `picking_area_inventory` (`check_sku`, `location`, `location_priority`, `quantity`, `picked_quantity`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, 0, NOW(), NOW())", [
                            $azItem->check_sku,
                            $location,
                            config('app.other.az_location_priority'),
                            $azItem->quantity
                        ]);
                    }
                });
            }
        }
        //TODO B2B
        //寫入shipping_server b2b_stock/b2b_picking_area_inventory相關
        //     $sql1 = <<<SQL
        //     SELECT distinct `locations`.`barcode` AS `location`, `storage_box_items`.`material_sku`, `locations`.`priority`
        //         FROM `storage_box_items` LEFT JOIN `storage_boxes` ON `storage_box_items`.`storage_box` = `storage_boxes`.`barcode`
        //         LEFT JOIN `locations` ON `storage_boxes`.`location` = `locations`.`barcode` where `storage_boxes`.`warehouse_id` = 4
        // SQL;
        //     $items = DB::select($sql1);

        //     foreach ($items as $item) {
        //         DB::connection($server)->statement("INSERT INTO `b2b_picking_area_inventory` (`sku`, `location`, `location_priority`, `created_at`, `updated_at`) VALUES (?,?, ?, NOW(), NOW())", [
        //             $item->material_sku,
        //             $item->location,
        //             $item->priority
        //         ]);
        //     }

        return 0;
    }
}
