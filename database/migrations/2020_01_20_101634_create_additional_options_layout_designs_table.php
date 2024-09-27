<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalOptionsLayoutDesignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_options_layout_designs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('additional_options_id');
            $table->boolean ('basic');
            $table->boolean('standard');
            $table->boolean('premium');

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
        Schema::dropIfExists('additional_options_layout_designs');
    }
}
