<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateNewSkuLaunchViewTable extends Migration
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
        Schema::dropIfExists('v_new_sku_launch');
    }

    private function dropView(): string
    {
        return <<<SQL
            DROP VIEW IF EXISTS `v_new_sku_launch`;
        SQL;
    }

    private function createView(): string
    {
        return <<<SQL
            CREATE VIEW `v_new_sku_launch` As
            select distinct `i`.`material_sku` AS `material_sku`,`i`.`location` AS `location`,`i`.`storage_box` AS `storage_box`,`i`.`material_name` AS `material_name` from (`v_inventory_items` `i` left join `v_picking_items` `p` on((`i`.`material_sku` = `p`.`material_sku`))) where (`p`.`material_sku` is null) order by `i`.`material_sku`

        SQL;
    }
}
