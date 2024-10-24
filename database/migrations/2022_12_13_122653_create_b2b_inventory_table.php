<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateB2BInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b2b_inventory', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();

            $table->bigInteger('material_id')->unsigned()->nullable();
            $table->foreign('material_id')->references('id')->on('materials');
            $table->string('material_sku', 20)->default('');
            $table->string('material_name', 200)->default('');
            $table->smallInteger('first_quantity')->default(0);
            $table->smallInteger('check_quantity')->default(0);
            $table->enum('status', ['first_inventory', 'check_inventory', 'non_inventory'])->default('non_inventory');


            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');

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
        Schema::dropIfExists('b2b_inventory');
    }
}
