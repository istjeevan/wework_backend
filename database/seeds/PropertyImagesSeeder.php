<?php

use Illuminate\Database\Seeder;

class PropertyImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('properties_images')->delete();
        DB::table('properties_images')->insert(
            array(
                
                array(
                    'property_id' => 1,
                    'image_name' => '200a.jpg'
                ),
                array(
                    'property_id' => 1,
                    'image_name' => '200b.jpg'
                ),
                array(
                    'property_id' => 1,
                    'image_name' => '200c.jpg'
                ),
                array(
                    'property_id' => 1,
                    'image_name' => '200d.jpg'
                ),
                array(
                    'property_id' => 1,
                    'image_name' => '200e.jpg'
                ),
                array(
                    'property_id' => 1,
                    'image_name' => '200_final.png'
                ),
                array(
                    'property_id' => 1,
                    'image_name' => '210200-Copy.jpg'
                ),
                array(
                    'property_id' => 2,
                    'image_name' => '210.png'
                ),
                array(
                    'property_id' => 2,
                    'image_name' => '210a.jpg'
                ),
                array(
                    'property_id' => 2,
                    'image_name' => '210b.jpg'
                ),
                array(
                    'property_id' => 2,
                    'image_name' => '210c.jpg'
                ),
                array(
                    'property_id' => 2,
                    'image_name' => '210e.jpg'
                ),
                array(
                    'property_id' => 2,
                    'image_name' => '210f.jpg'
                ),
                array(
                    'property_id' => 2,
                    'image_name' => '210g.jpg'
                ),
                array(
                    'property_id' => 2,
                    'image_name' => '210h.jpg'
                ),
                array(
                    'property_id' => 2,
                    'image_name' => '210200.jpg'
                ),
                array(
                    'property_id' => 3,
                    'image_name' => '210h.jpg'
                ),
                array(
                    'property_id' => 3,
                    'image_name' => '210200.jpg'
                ),
                array(
                    'property_id' => 4,
                    'image_name' => '220a.jpg'
                ),
                array(
                    'property_id' => 4,
                    'image_name' => '220b.jpg'
                ),
                array(
                    'property_id' => 4,
                    'image_name' => '220c.jpg'
                ),
                array(
                    'property_id' => 4,
                    'image_name' => '220_final.png'
                ),
                array(
                    'property_id' => 5,
                    'image_name' => '270a.jpg'
                ),
                array(
                    'property_id' => 5,
                    'image_name' => '270b.jpg'
                ),
                array(
                    'property_id' => 5,
                    'image_name' => '270c.jpg'
                ),
                array(
                    'property_id' => 5,
                    'image_name' => '270d.jpg'
                ),
                array(
                    'property_id' => 5,
                    'image_name' => '270e.jpg'
                ),
                array(
                    'property_id' => 5,
                    'image_name' => '270f.jpg'
                ),
                array(
                    'property_id' => 5,
                    'image_name' => 'Suite_270_8200SF.jpg'
                ),
                array(
                    'property_id' => 6,
                    'image_name' => '270e.jpg'
                ),
                array(
                    'property_id' => 6,
                    'image_name' => '270f.jpg'
                ),
                array(
                    'property_id' => 6,
                    'image_name' => 'Suite_270_8200SF.jpg'
                ),
                array(
                    'property_id' => 7,
                    'image_name' => '270e.jpg'
                ),
                array(
                    'property_id' => 7,
                    'image_name' => '270f.jpg'
                ),
                array(
                    'property_id' => 7,
                    'image_name' => 'Suite_270_8200SF.jpg'
                ),
                array(
                    'property_id' => 8,
                    'image_name' => '270e.jpg'
                ),
                array(
                    'property_id' => 8,
                    'image_name' => '270f.jpg'
                ),
                array(
                    'property_id' => 8,
                    'image_name' => 'Suite_270_8200SF.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300_326.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300a.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300b.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300c.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300d.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300e.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300f.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300_floor_plan.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300g.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300h.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300i.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300j.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300k.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300l.jpg'
                ),
                array(
                    'property_id' => 9,
                    'image_name' => '300m.jpg'
                ),

            )
        );
    }
}
