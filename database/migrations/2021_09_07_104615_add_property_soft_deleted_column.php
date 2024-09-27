<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPropertySoftDeletedColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('soft_deleted')->default(0);
        });

        Schema::table('properties_additional_options', function (Blueprint $table) {
            $table->boolean('soft_deleted')->default(0);
        });

        Schema::table('properties_amenities', function (Blueprint $table) {
            $table->boolean('soft_deleted')->default(0);
        });

        Schema::table('properties_contract_lengths', function (Blueprint $table) {
            $table->boolean('soft_deleted')->default(0);
        });

        Schema::table('properties_images', function (Blueprint $table) {
            $table->boolean('soft_deleted')->default(0);
        });

        Schema::table('properties_layout_designs', function (Blueprint $table) {
            $table->boolean('soft_deleted')->default(0);
        });

        Schema::table('properties_near_by_amenities', function (Blueprint $table) {
            $table->boolean('soft_deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('properties', function($table) {
            $table->dropColumn('soft_deleted');
        });

        Schema::table('properties_additional_options', function (Blueprint $table) {
            $table->dropColumn('soft_deleted');
        });

        Schema::table('properties_amenities', function (Blueprint $table) {
            $table->dropColumn('soft_deleted');
        });

        Schema::table('properties_contract_lengths', function (Blueprint $table) {
            $table->dropColumn('soft_deleted');
        });

        Schema::table('properties_images', function (Blueprint $table) {
            $table->dropColumn('soft_deleted');
        });

        Schema::table('properties_layout_designs', function (Blueprint $table) {
            $table->dropColumn('soft_deleted');
        });

        Schema::table('properties_near_by_amenities', function (Blueprint $table) {
            $table->dropColumn('soft_deleted');
        });
    }
}
