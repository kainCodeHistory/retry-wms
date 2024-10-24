<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_settings', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->bigInteger('factory_id')->unsigned();
            $table->foreign('factory_id')->references('id')->on('factories');
            $table->string('device_type', 10)->default('Box');
            $table->string('device_name', 20)->default('');
            $table->string('tag', 10)->default('');
            $table->string('prefix', 1)->default('');
            $table->integer('strpad_length')->default(0);
            $table->integer('value')->default(0);
            $table->string('label_template', 20)->default('');
            $table->boolean('is_storage_box')->default(false);
            $table->timestamps();

            $table->unique(['factory_id', 'device_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_settings');
    }
}
