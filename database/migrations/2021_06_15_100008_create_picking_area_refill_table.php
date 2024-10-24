<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickingAreaRefillTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('picking_area_refill', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();

            $table->bigInteger('material_id')->unsigned();
            $table->foreign('material_id')->references('id')->on('materials');
            $table->string('material_sku', 20)->default('');

            $table->bigInteger('warehouse_id')->unsigned();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->string('location', 20)->default('');
            $table->string('storage_boxes', 30)->default('[]');
            $table->integer('quantity')->default(0);

            $table->enum('fill_type', ['replace', 'fill'])->default('replace');

            $table->bigInteger('repl_warehouse_id')->unsigned()->nullable();
            $table->foreign('repl_warehouse_id')->references('id')->on('warehouses');
            $table->string('repl_location', 20)->default('');
            $table->string('repl_storage_box', 10)->default('');
            $table->integer('repl_quantity')->default(0);

            $table->enum('status', ['pending', 'processing', 'completed', 'aborted'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('picking_area_refill');
    }
}
