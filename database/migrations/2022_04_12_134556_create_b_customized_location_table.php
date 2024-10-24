<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBCustomizedLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_customized_location', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('source_id');
            $table->bigInteger('bundle_id')->nullable();
            $table->bigInteger('source_item_id');
            $table->bigInteger('shipment_items_count');
            $table->string('box',10)->default('');
            $table->string('carrier',20)->default('');

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
        Schema::dropIfExists('b_customized_location');
    }
}
