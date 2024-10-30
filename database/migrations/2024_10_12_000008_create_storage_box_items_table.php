<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStorageBoxItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storage_box_items', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->bigInteger('storage_box_id')->unsigned();
            $table->foreign('storage_box_id')->references('id')->on('storage_boxes');
            $table->string('storage_box', 20)->default('');

            $table->bigInteger('material_id')->unsigned();
            $table->foreign('material_id')->references('id')->on('materials');
            $table->string('material_sku', 20)->default('')->index();
            $table->string('material_name', 200)->default('');
            $table->string('batch_no', 20)->default('');

            $table->integer('quantity')->default(0);

            $table->timestamps();

            $table->unique(['storage_box_id', 'material_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('storage_box_items');
    }
}
