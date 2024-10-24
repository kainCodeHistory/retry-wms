<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();

            $table->bigInteger('warehouse_id')->unsigned()->nullable(); // 倉庫代號
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->string('location', 20)->default('')->index(); // 儲位代號
            $table->string('storage_box', 20)->default('')->index();

            $table->bigInteger('material_id')->unsigned();
            $table->foreign('material_id')->references('id')->on('materials');
            $table->string('material_sku', 20)->index(); // 料號

            $table->string('batch_no', 20)->default('');
            $table->integer('quantity'); // 交易數量
            $table->enum('in_out', ['input', 'output'])->default('input'); // inbound or outbound
            $table->string('event', 20)->default(''); // 事件
            $table->string('event_key', 200)->default(''); // 事件說明

            $table->string('user', 20)->default(''); // 執行人員
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
        Schema::dropIfExists('transactions');
    }
}
