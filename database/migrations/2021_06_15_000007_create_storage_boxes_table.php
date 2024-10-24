<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStorageBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storage_boxes', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();

            $table->string('prefix', 1)->default('A');
            $table->string('barcode', 20)->unique(); // 載具代號

            $table->bigInteger('factory_id')->unsigned();
            $table->foreign('factory_id')->references('id')->on('factories');

            $table->bigInteger('warehouse_id')->unsigned()->nullable();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->string('location', 20)->default(''); // 儲位代碼
            $table->string('status', 20)->default(''); // 狀態
            $table->boolean('is_empty')->default(true);
            $table->timestamps();

            $table->unique(['barcode', 'location']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('storage_boxes');
    }
}
