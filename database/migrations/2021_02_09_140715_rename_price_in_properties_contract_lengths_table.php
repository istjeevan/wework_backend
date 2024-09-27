<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenamePriceInPropertiesContractLengthsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('properties_contract_lengths', function (Blueprint $table) {
            $table->renameColumn('price', 'percent');
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
        Schema::table('properties_contract_lengths', function (Blueprint $table) {
            $table->renameColumn('percent', 'price');
            $table->dropColumn('is_default');
        });
    }
}
