<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCurrentInventoryViewTable extends Migration
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
        Schema::dropIfExists('v_current_inventory');
    }

    private function dropView()
    {
        return <<<SQL
            DROP VIEW IF EXISTS `v_current_inventory`;
        SQL;
    }

    private function createView()
    {
        return <<<SQL
            CREATE VIEW `v_current_inventory` AS
            SELECT `picking_area_logs`.`trans_date`, `picking_area_logs`.`location`, `picking_area_logs`.`sku`, `picking_area_logs`.`storage_box`, `picking_area_logs`.`subtotal`
            FROM `picking_area_logs` INNER JOIN (
                SELECT `location`, `sku`, MAX(`trans_date`) As `trans_date` FROM `picking_area_logs` GROUP BY `location`, `sku`
            ) As `tmp` ON `picking_area_logs`.`location` = `tmp`.`location` AND `picking_area_logs`.`sku` = `tmp`.`sku` AND `picking_area_logs`.`trans_date` = `tmp`.`trans_date`
        SQL;
    }
}
