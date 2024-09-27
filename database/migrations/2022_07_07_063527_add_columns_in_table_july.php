<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInTableJuly extends Migration
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
                if (!Schema::hasColumn('properties', 'state')){
                    $table->string('state')->nullable()->after('location');
                }
            });
        }

        if(Schema::hasTable('contract_lengths')){
            Schema::table('contract_lengths', function (Blueprint $table) {
                if (!Schema::hasColumn('contract_lengths', 'uuid')){
                    $table->string('uuid')->nullable()->after('id');
                }
            });
        }

        if(Schema::hasTable('amenities')){
            Schema::table('amenities', function (Blueprint $table) {
                if (!Schema::hasColumn('amenities', 'uuid')){
                    $table->string('uuid')->nullable()->after('id');
                }
            });
        }

        if(Schema::hasTable('layout_designs')){
            Schema::table('layout_designs', function (Blueprint $table) {
                if (!Schema::hasColumn('layout_designs', 'uuid')){
                    $table->string('uuid')->nullable()->after('id');
                }
            });
        }

        if(Schema::hasTable('near_by_amenities')){
            Schema::table('near_by_amenities', function (Blueprint $table) {
                if (!Schema::hasColumn('near_by_amenities', 'uuid')){
                    $table->string('uuid')->nullable()->after('id');
                }
            });
        }

        if(Schema::hasTable('additional_options')){
            Schema::table('additional_options', function (Blueprint $table) {
                if (!Schema::hasColumn('additional_options', 'uuid')){
                    $table->string('uuid')->nullable()->after('id');
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
        Schema::table('table_july', function (Blueprint $table) {
            //
        });
    }
}
