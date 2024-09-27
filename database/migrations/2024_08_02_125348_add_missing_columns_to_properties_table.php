<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->integer('min_price')->nullable(); // Add this line
            $table->integer('max_price')->nullable(); // Add this line
            $table->integer('max_length')->nullable(); // Add this line
            $table->integer('min_length')->nullable(); // Add this line
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
            $table->dropColumn('min_price');
            $table->dropColumn('max_price');
            $table->dropColumn('max_length');
            $table->dropColumn('min_length');
        });
    }
}
