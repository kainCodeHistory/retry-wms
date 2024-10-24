<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoxAndEanToB2bInputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('b2b_inputs', function (Blueprint $table) {
            $table->string('box', 20)->default('')->after('transaction_date')->unique();
            $table->string('ean', 20)->default('')->after('material_sku');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('b2b_inputs', function (Blueprint $table) {
            $table->dropColumn(['box', 'ean']);
        });
    }
}
