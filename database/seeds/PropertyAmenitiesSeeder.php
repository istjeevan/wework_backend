<?php

use Illuminate\Database\Seeder;

class PropertyAmenitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('properties_amenities')->delete();
        DB::table('properties_amenities')->insert(
            array(
                
                array(
                    'property_id' => 1,
                    'amenity_id' => 1
                ),
                array(
                    'property_id' => 1,
                    'amenity_id' => 2
                ),
                array(
                    'property_id' => 1,
                    'amenity_id' => 3
                ),
                array(
                    'property_id' => 1,
                    'amenity_id' => 4
                ),
                array(
                    'property_id' => 1,
                    'amenity_id' => 5
                ),
                array(
                    'property_id' => 1,
                    'amenity_id' => 6
                ),
                array(
                    'property_id' => 2,
                    'amenity_id' => 1
                ),
                array(
                    'property_id' => 2,
                    'amenity_id' => 2
                ),
                array(
                    'property_id' => 2,
                    'amenity_id' => 3
                ),
                array(
                    'property_id' => 2,
                    'amenity_id' => 4
                ),
                array(
                    'property_id' => 2,
                    'amenity_id' => 5
                ),
                array(
                    'property_id' => 2,
                    'amenity_id' => 6
                ),
                array(
                    'property_id' => 3,
                    'amenity_id' => 1
                ),
                array(
                    'property_id' => 3,
                    'amenity_id' => 2
                ),
                array(
                    'property_id' => 3,
                    'amenity_id' => 3
                ),
                array(
                    'property_id' => 3,
                    'amenity_id' => 4
                ),
                array(
                    'property_id' => 3,
                    'amenity_id' => 5
                ),
                array(
                    'property_id' => 3,
                    'amenity_id' => 6
                ),
                array(
                    'property_id' => 4,
                    'amenity_id' => 1
                ),
                array(
                    'property_id' => 4,
                    'amenity_id' => 2
                ),
                array(
                    'property_id' => 4,
                    'amenity_id' => 3
                ),
                array(
                    'property_id' => 4,
                    'amenity_id' => 4
                ),
                array(
                    'property_id' => 4,
                    'amenity_id' => 5
                ),
                array(
                    'property_id' => 4,
                    'amenity_id' => 6
                ),
                array(
                    'property_id' => 5,
                    'amenity_id' => 1
                ),
                array(
                    'property_id' => 5,
                    'amenity_id' => 2
                ),
                array(
                    'property_id' => 5,
                    'amenity_id' => 3
                ),
                array(
                    'property_id' => 5,
                    'amenity_id' => 4
                ),
                array(
                    'property_id' => 5,
                    'amenity_id' => 5
                ),
                array(
                    'property_id' => 5,
                    'amenity_id' => 6
                ),
                array(
                    'property_id' => 6,
                    'amenity_id' => 1
                ),
                array(
                    'property_id' => 6,
                    'amenity_id' => 2
                ),
                array(
                    'property_id' => 6,
                    'amenity_id' => 3
                ),
                array(
                    'property_id' => 6,
                    'amenity_id' => 4
                ),
                array(
                    'property_id' => 6,
                    'amenity_id' => 5
                ),
                array(
                    'property_id' => 6,
                    'amenity_id' => 6
                ),
                array(
                    'property_id' => 7,
                    'amenity_id' => 1
                ),
                array(
                    'property_id' => 7,
                    'amenity_id' => 2
                ),
                array(
                    'property_id' => 7,
                    'amenity_id' => 3
                ),
                array(
                    'property_id' => 7,
                    'amenity_id' => 4
                ),
                array(
                    'property_id' => 7,
                    'amenity_id' => 5
                ),
                array(
                    'property_id' => 7,
                    'amenity_id' => 6
                ),
                array(
                    'property_id' => 8,
                    'amenity_id' => 1
                ),
                array(
                    'property_id' => 8,
                    'amenity_id' => 2
                ),
                array(
                    'property_id' => 8,
                    'amenity_id' => 3
                ),
                array(
                    'property_id' => 8,
                    'amenity_id' => 4
                ),
                array(
                    'property_id' => 8,
                    'amenity_id' => 5
                ),
                array(
                    'property_id' => 8,
                    'amenity_id' => 6
                ),
                array(
                    'property_id' => 9,
                    'amenity_id' => 1
                ),
                array(
                    'property_id' => 9,
                    'amenity_id' => 2
                ),
                array(
                    'property_id' => 9,
                    'amenity_id' => 3
                ),
                array(
                    'property_id' => 9,
                    'amenity_id' => 4
                ),
                array(
                    'property_id' => 9,
                    'amenity_id' => 5
                ),
                array(
                    'property_id' => 9,
                    'amenity_id' => 6
                )
            )
        );
    }
}
