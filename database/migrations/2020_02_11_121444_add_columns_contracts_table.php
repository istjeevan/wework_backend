<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contracts', function($table) {
            $table->float('contract_length_price', 8, 2)->nullable();
            $table->float('layout_price', 8, 2)->nullable();
            $table->float('final_price', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contracts', function($table) {
            $table->dropColumn('contract_length_price');
            $table->dropColumn('layout_price');
        });
    }
}
