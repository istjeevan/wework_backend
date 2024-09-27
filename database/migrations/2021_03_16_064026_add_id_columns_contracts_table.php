<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdColumnsContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	Schema::table('contracts', function($table) {
            $table->integer('contract_length_id');
            $table->integer('layout_id');
            $table->float('cost_per_person', 8, 2);
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
            $table->dropColumn('contract_length_id');
            $table->dropColumn('layout_id');
            $table->dropColumn('cost_per_person');
        });
    }
}
