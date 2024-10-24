<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropRawMaterialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists("extra_raw_materials");
        Schema::dropIfExists("raw_material_box_items");
        Schema::dropIfExists("raw_material_groups");
        Schema::dropIfExists("raw_material_input");
        Schema::dropIfExists("raw_material_labels");
        Schema::dropIfExists("suppliers");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
