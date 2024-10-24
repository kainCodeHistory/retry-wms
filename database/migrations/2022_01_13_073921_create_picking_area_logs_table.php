<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickingAreaLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('picking_area_logs', function (Blueprint $table) {
            $table->id();
            $table->date('trans_date');
            $table->string('location', 20)->default('');
            $table->string('sku', 20)->default('');
            $table->string('storage_box', 10)->default('');
            $table->integer('input_quantity')->default(0);
            $table->integer('output_quantity')->default(0);
            $table->integer('subtotal')->default(0);
            $table->timestamp('bound_at');
            $table->timestamps();

            $table->unique(['trans_date', 'location', 'sku', 'storage_box'], 'unique_picking_area_logs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('picking_area_logs');
    }
}
