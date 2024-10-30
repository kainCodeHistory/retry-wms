<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class B2bStockLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('b2b_stock_logs', function (Blueprint $table) {
            $table->id();

            $table->date('working_day');
            $table->string('sku', 30)->default('');
            $table->integer('quantity')->default(0);
            $table->integer('balance')->default(0);
            $table->string('event', 30)->default('');
            $table->string('event_key', 50)->default('');
            $table->string('note', 100)->default('');
            $table->string('user_name', 30)->default('');

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
        //
        Schema::dropIfExists('b2b_stock_logs');
    }
}
