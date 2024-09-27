<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('properties')){
            Schema::table('properties', function (Blueprint $table) {
                if (Schema::hasColumn('properties', 'thumbnail_image')){
                    $table->string('thumbnail_image')->nullable()->change();
                }

                if (Schema::hasColumn('properties', 'terms_and_condition_file')){
                    $table->string('terms_and_condition_file')->nullable()->change();
                }
            });
        }
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
