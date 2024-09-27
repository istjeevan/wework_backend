<?php

use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('properties')->delete();
        DB::table('properties')->insert(
            array(
                
                array(
                    'location' => '3600 136th Place SE,Bellevue,98006,USA',
                    'price' => 20900,
                    'capacity' => 73,
                    'area' => 5500,
                    'floors' => 1,
                    'latitude' => '47.578470',
                    'longitude' => '-122.153290',
                    'thumbnail_image' => '200_final.png',
                    'terms_and_condition_file' => 'p1.pdf',
                    'available_from' => '2021-02-01',
                    'is_available' => 0,
                    'created_at' => now(),
                    'default_contract_length' => 1
                ),
                array(
                    'location' => '3600 136th Place SE,Bellevue,98006,USA',
                    'price' => 28000,
                    'capacity' => 95,
                    'area' => 7500,
                    'floors' => 2,
                    'latitude' => '47.578470',
                    'longitude' => '-122.153290',
                    'thumbnail_image' => '210.png',
                    'terms_and_condition_file' => 'p1.pdf',
                    'available_from' => '2020-11-01',
                    'is_available' => 1,
                    'created_at' => now(),
                    'default_contract_length' => 1
                ),
                array(
                    'location' => '3600 136th Place SE,Bellevue,98006,USA',
                    'price' => 48900,
                    'capacity' => 170,
                    'area' => 13000,
                    'floors' => 2,
                    'latitude' => '47.578470',
                    'longitude' => '-122.153290',
                    'thumbnail_image' => '200_final.png',
                    'terms_and_condition_file' => 'p1.pdf',
                    'available_from' => '2020-11-01',
                    'is_available' => 0,
                    'created_at' => now(),
                    'default_contract_length' => 1
                ),
                array(
                    'location' => '3600 136th Place SE,Bellevue,98006,USA',
                    'price' => 13000,
                    'capacity' => 35,
                    'area' => 3400,
                    'floors' => 1,
                    'latitude' => '47.578470',
                    'longitude' => '-122.153290',
                    'thumbnail_image' => '220a.jpg',
                    'terms_and_condition_file' => 'p1.pdf',
                    'available_from' => now(),
                    'is_available' => 1,
                    'created_at' => now(),
                    'default_contract_length' => 1
                ),
                array(
                    'location' => '3600 136th Place SE,Bellevue,98006,USA',
                    'price' => 31200,
                    'capacity' => 105,
                    'area' => 8200,
                    'floors' => 1,
                    'latitude' => '47.578470',
                    'longitude' => '-122.153290',
                    'thumbnail_image' => '270a.jpg',
                    'terms_and_condition_file' => 'p1.pdf',
                    'available_from' => '2020-11-01',
                    'is_available' => 1,
                    'created_at' => now(),
                    'default_contract_length' => 1
                ),
                array(
                    'location' => '3600 136th Place SE,Bellevue,98006,USA',
                    'price' => 7900,
                    'capacity' => 35,
                    'area' => 1200,
                    'floors' => 1,
                    'latitude' => '47.578470',
                    'longitude' => '-122.153290',
                    'thumbnail_image' => '300_326.jpg',
                    'terms_and_condition_file' => 'p1.pdf',
                    'available_from' => '2020-11-01',
                    'is_available' => 1,
                    'created_at' => now(),
                    'default_contract_length' => 1
                ),
                array(
                    'location' => '3600 136th Place SE,Bellevue,98006,USA',
                    'price' => 2000,
                    'capacity' => 5,
                    'area' => 180,
                    'floors' => 1,
                    'latitude' => '47.578470',
                    'longitude' => '-122.153290',
                    'thumbnail_image' => '300_326.jpg',
                    'terms_and_condition_file' => 'p1.pdf',
                    'available_from' => '2020-11-01',
                    'is_available' => 1,
                    'created_at' => now(),
                    'default_contract_length' => 1
                ),
                array(
                    'location' => '3600 136th Place SE,Bellevue,98006,USA',
                    'price' => 250,
                    'capacity' => 1,
                    'area' => 100,
                    'floors' => 1,
                    'latitude' => '47.578470',
                    'longitude' => '-122.153290',
                    'thumbnail_image' => '300_326.jpg',
                    'terms_and_condition_file' => 'p1.pdf',
                    'available_from' => '2020-11-01',
                    'is_available' => 1,
                    'created_at' => now(),
                    'default_contract_length' => 1
                ),
                array(
                    'location' => '3600 136th Place SE,Bellevue,98006,USA',
                    'price' => 21000,
                    'capacity' => 75,
                    'area' => 6000,
                    'floors' => 1,
                    'latitude' => '47.578470',
                    'longitude' => '-122.153290',
                    'thumbnail_image' => '300_326.jpg',
                    'terms_and_condition_file' => 'p1.pdf',
                    'available_from' => '2020-11-01',
                    'is_available' => 1,
                    'created_at' => now(),
                    'default_contract_length' => 1
                )

            )
        );
    }
}
