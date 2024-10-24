<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserNameColumnToB2BStockLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('b2b_stock_logs', function (Blueprint $table) {
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
        Schema::table('b2b_stock_logs', function (Blueprint $table) {
            //
            $table->dropColumn([
                'user_name'
            ]);
        });
    }
}
