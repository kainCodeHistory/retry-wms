<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();

            $table->date('picked_date');
            $table->string('sku', 20)->default('');
            $table->string('location')->default('');
            $table->string('storage_box1', 10)->default('');
            $table->integer('quantity1')->default(0);
            $table->string('storage_box2', 10)->default('');
            $table->integer('quantity2')->default(0);
            $table->integer('total_quantity')->default(0);
            $table->integer('reset_quantity')->default(0);
            $table->integer('input_quantity')->default(0); // item_return, transfer_input
            $table->integer('output_quantity')->default(0); // transfer_output
            $table->integer('picked_quantity')->default(0);

            $table->timestamps();

            $table->unique(['picked_date', 'sku', 'location'], 'inventory_logs_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_logs');
    }
}
