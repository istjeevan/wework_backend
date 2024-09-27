<?php

use App\Models\Amenities;
use Illuminate\Database\Seeder;

class AmenitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('amenities')->delete();
        DB::table('amenities')->insert(
            array(
                
                array(
                    'uuid' => Amenities::createUuid(),
                    'name' => 'WiFi',
                    'icon_name' => 'wifi'
                ),
                array(
                    'uuid' => Amenities::createUuid(),
                    'name' => 'Facilities',
                    'icon_name' => 'cogs'
                ),
                array(
                    'uuid' => Amenities::createUuid(),
                    'name' => 'Kitchen',
                    'icon_name' => 'spoon'
                ),
                array(
                    'uuid' => Amenities::createUuid(),
                    'name' => 'Parking',
                    'icon_name' => 'car'
                ),
                array(
                    'uuid' => Amenities::createUuid(),
                    'name' => 'Furnished',
                    'icon_name' => 'bed'
                ),
                array(
                    'uuid' => Amenities::createUuid(),
                    'name' => '24/7 Access',
                    'icon_name' => 'building'
                )
            )
        );
    }
}
