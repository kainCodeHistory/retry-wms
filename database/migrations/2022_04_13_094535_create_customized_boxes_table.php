<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomizedBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customized_boxes', function (Blueprint $table) {

            $table->id();
            $table->string('barcode', 20); // 箱號
            $table->bigInteger('allocate_quantity')->unsigned()->default(0);
            $table->string('carrier', 20)->default('');
            $table->boolean('is_multiple')->default(false);
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
        Schema::dropIfExists('customized_boxes');
    }
}
