<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickedBatchItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('picked_batch_items', function (Blueprint $table) {
            $table->id();
            $table->string('batch_key', 36);
            $table->date('picked_date');
            $table->string('check_sku', 30);
            $table->string('location', 20);
            $table->tinyInteger('quantity')->default(0);
            $table->boolean("is_debited")->default(false);
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
        Schema::dropIfExists('picked_batch_items');
    }
}
