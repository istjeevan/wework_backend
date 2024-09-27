<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameEmailPhonenumberImageFieldInPropertyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('manager_name')->after('default_contract_length')->nullable();
            $table->string('manager_email')->after('manager_name')->nullable();
            $table->string('manager_phone_number')->after('manager_email')->nullable();
            $table->longText('manager_image')->after('manager_phone_number')->nullable();
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
            $table->dropColumn('manager_name');
            $table->dropColumn('manager_email');
            $table->dropColumn('manager_phone_number');
            $table->dropColumn('manager_image');
        });
    }
}
