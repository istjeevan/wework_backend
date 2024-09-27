<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInTable extends Migration
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
                $table->string('pincode')->nullable()->after('location');
            });
        }

        if(Schema::hasTable('contract_offer')){
            Schema::table('contract_offer', function (Blueprint $table) {
                $table->string('phone_no')->nullable()->after('contract_id');
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
        Schema::table('properties', function (Blueprint $table) {
            $table->string('pincode');
        });

        Schema::table('contract_offer', function (Blueprint $table) {
            $table->string('phone_no');
        });
    }
}
