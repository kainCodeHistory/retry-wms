<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePickingItemsViewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement($this->dropView());
        DB::statement($this->createView());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v_picking_items');
    }

    private function dropView(): string
    {
        return <<<SQL
            DROP VIEW IF EXISTS `v_picking_items`;
        SQL;
    }

    private function createView(): string
    {
        return <<<SQL
            CREATE VIEW `v_picking_items` As
            SELECT `storage_boxes`.`barcode` as `storage_box`, `storage_boxes`.`warehouse_id`, `warehouses`.`name` as `warehouse_name`, `storage_boxes`.`location`,
                `storage_box_items`.`material_id`, `storage_box_items`.`material_sku`, `storage_box_items`.`material_name`, `storage_box_items`.`batch_no`, `storage_box_items`.`quantity`, DATE(`storage_box_items`.`created_at`) As `bound_at`
            FROM `storage_boxes` INNER JOIN `storage_box_items` ON `storage_boxes`.`id` = `storage_box_items`.`storage_box_id`
                INNER JOIN `warehouses` ON `storage_boxes`.`warehouse_id` = `warehouses`.`id` AND `warehouses`.`tt_code` = 'ED02' AND `warehouses`.`is_picking_area` = 1
            WHERE `storage_boxes`.`is_empty` = 0
        SQL;
    }
}
