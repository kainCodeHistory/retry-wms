<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateB2bInputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b2b_inputs', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date')->nullable();

            $table->bigInteger('material_id')->unsigned();
            $table->foreign('material_id')->references('id')->on('materials');

            $table->string('material_sku', 30)->default('');
            $table->string('product_title', 200)->default('');
            $table->integer('quantity')->default(0);

            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('user', 20)->default('');
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
        Schema::dropIfExists('b2b_inputs');
    }
}
