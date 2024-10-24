<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserNameColumnToB2cStockLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('b2c_stock_logs', function (Blueprint $table) {
            //
            $table->string('user_name', 30)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('b2c_stock_logs', function (Blueprint $table) {
            //
            $table->dropColumn([
                'user_name'
            ]);
        });
    }
}
