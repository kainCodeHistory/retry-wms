<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateB2b5fInputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b2b_5f_inputs', function (Blueprint $table) {
            $table->id();
            $table->date('manufacturing_date');
            $table->integer('item_number')->default(0);

            $table->bigInteger('material_id')->unsigned();
            $table->foreign('material_id')->references('id')->on('materials');

            $table->string('material_sku', 30)->default('');
            $table->string('ean', 20)->default('');
            $table->string('product_title', 200)->default('');
            $table->integer('quantity')->default(0);
            $table->string('note', 200)->nullable();

            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('user_name', 30)->default('');

            $table->boolean('is_deleted')->default(0);
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
        Schema::dropIfExists('b2b_5f_inputs');
    }
}
