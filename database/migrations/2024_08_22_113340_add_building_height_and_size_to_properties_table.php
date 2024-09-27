<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBuildingHeightAndSizeToPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            // Adding new columns
            $table->string('building_height')->nullable();
            $table->integer('building_size')->nullable(); // Add this without change()
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            // Removing the columns
            $table->dropColumn('building_height');
            $table->dropColumn('building_size');
        });
    }
}
