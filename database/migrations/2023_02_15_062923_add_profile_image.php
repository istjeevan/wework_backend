<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProfileImage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try{      
            if(Schema::hasTable('users')){
                Schema::table('users', function (Blueprint $table) {
                    if (!Schema::hasColumn('users', 'profile_image')){
                        $table->string('profile_image')->nullable();
                    }
                });
            }
        } catch(\Exception $e){
            echo "something went wrong";
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            if (Schema::hasTable('users')) {
                Schema::table('users', function (Blueprint $table) {
                    if(Schema::hasColumn('users','profile_image')){
                        $table->dropColumn('profile_image');
                    }
                });
            }
        } catch(\Exception $e){
            echo "something went wrong";
            \Log::info($e);
        }
    }
}
