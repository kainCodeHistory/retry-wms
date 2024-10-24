<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateB2bPickedItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b2b_picked_items', function (Blueprint $table) {
            $table->id();
            $table->string('batch_key', 36);
            $table->date('picked_date');
            $table->string('sku', 30);
            $table->string('total_list', 30)->default('');
            $table->string('order_number', 30)->default('');
            $table->tinyInteger('quantity')->default(0);
            $table->tinyInteger('fixed_quantity')->default(0);
            $table->string('employee_no', 30);
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
        Schema::dropIfExists('b2b_picked_items');
    }
}
