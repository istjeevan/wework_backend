<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsDefaultInPropertiesLayoutDesignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('properties_layout_designs', function (Blueprint $table) {
            $table->boolean('is_default')->default(0)->comment('use 1 for default value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('properties_layout_designs', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
}
