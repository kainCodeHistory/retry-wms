<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('sku', 20)->unique();// 料號
            $table->string('display_name', 200)->default(''); // 物料簡稱 (工作指示站用)
            $table->string('full_name', 200)->default(''); // 完整物料名稱
            $table->string('check_sku', 20)->default(''); //原料號
            $table->boolean('check_for_leash')->default(true);
            $table->string('ean', 13)->default('');//撿貨掃的條碼
            $table->string('upc', 12)->default('');//Amazon
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
        Schema::dropIfExists('materials');
    }
}
