<?php

use App\Models\LayoutDesigns;
use Illuminate\Database\Seeder;

class LayoutDesignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('layout_designs')->delete();
        DB::table('layout_designs')->insert(
            array(
                
                array(
                    'uuid' => LayoutDesigns::createUuid(),
                    'name' => 'Basic'
                ),
                array(
                    'uuid' => LayoutDesigns::createUuid(),
                    'name' => 'Standard'
                ),
                array(
                    'uuid' => LayoutDesigns::createUuid(),
                    'name' => 'Premium'
                )
            )
        );
    }
}
