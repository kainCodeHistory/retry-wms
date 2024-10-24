<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSkuAndInitialQuantityToStorageBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('storage_boxes', function (Blueprint $table) {
            $table->string('sku', 30)->default('')->after('location');
            $table->integer('initial_quantity')->default(0)->after('sku');
            $table->timestamp('bound_material_at')->nullable();
            $table->timestamp('bound_location_at')->nullable();
            $table->timestamp('bound_picking_area_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('storage_boxes', function (Blueprint $table) {
            $table->dropColumn([
                'sku',
                'initial_quantity',
                'bound_material_at',
                'bound_location_at',
                'bound_picking_area_at'
            ]);
        });
    }
}
