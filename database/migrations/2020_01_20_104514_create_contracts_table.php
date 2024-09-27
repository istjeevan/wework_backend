<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('property_id');
            $table->string('user_id');
            $table->date('start_date');
            $table->string('contract_length');
            $table->string('layout');
            $table->string('capacity');
            $table->boolean('signed')->default(0);
            $table->boolean('approved')->default(0);
            $table->boolean('first_rent')->default(0);
            $table->boolean('last_rent')->default(0);
            $table->boolean('add_on_cost')->default(0);
            $table->boolean('materials_ordered')->default(0);
            $table->boolean('assembly_started')->default(0);
            $table->boolean('setup_completed')->default(0);
            $table->boolean('send_via_fedex')->default(0);
            $table->boolean('arrived_in_mail')->default(0);
            $table->boolean('is_contract_done')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contracts');
    }
}
