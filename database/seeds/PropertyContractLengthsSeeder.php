<?php

use Illuminate\Database\Seeder;

class PropertyContractLengthsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('properties_contract_lengths')->truncate();
        DB::table('properties_contract_lengths')->insert(
            array(

                array(
                    'property_id' => 1,
                    'contract_length_id' => 1,
                    'percent' => 12
                ),
                array(
                    'property_id' => 1,
                    'contract_length_id' => 2,
                    'percent' => 12
                ),
                array(
                    'property_id' => 1,
                    'contract_length_id' => 3,
                    'percent' => 12
                ),
                array(
                    'property_id' => 1,
                    'contract_length_id' => 4,
                    'percent' => 12
                ),
                array(
                    'property_id' => 2,
                    'contract_length_id' => 1,
                    'percent' => 12
                ),
                array(
                    'property_id' => 2,
                    'contract_length_id' => 2,
                    'percent' => 12
                ),
                array(
                    'property_id' => 2,
                    'contract_length_id' => 3,
                    'percent' => 12
                ),
                array(
                    'property_id' => 2,
                    'contract_length_id' => 4,
                    'percent' => 12
                ),
                array(
                    'property_id' => 3,
                    'contract_length_id' => 1,
                    'percent' => 12
                ),
                array(
                    'property_id' => 3,
                    'contract_length_id' => 2,
                    'percent' => 12
                ),
                array(
                    'property_id' => 3,
                    'contract_length_id' => 3,
                    'percent' => 12
                ),
                array(
                    'property_id' => 3,
                    'contract_length_id' => 4,
                    'percent' => 12
                ),
                array(
                    'property_id' => 4,
                    'contract_length_id' => 1,
                    'percent' => 12
                ),
                array(
                    'property_id' => 4,
                    'contract_length_id' => 2,
                    'percent' => 12
                ),
                array(
                    'property_id' => 4,
                    'contract_length_id' => 3,
                    'percent' => 12
                ),
                array(
                    'property_id' => 4,
                    'contract_length_id' => 4,
                    'percent' => 12
                ),
                array(
                    'property_id' => 5,
                    'contract_length_id' => 1,
                    'percent' => 12
                ),
                array(
                    'property_id' => 5,
                    'contract_length_id' => 2,
                    'percent' => 12
                ),
                array(
                    'property_id' => 5,
                    'contract_length_id' => 3,
                    'percent' => 12
                ),
                array(
                    'property_id' => 5,
                    'contract_length_id' => 4,
                    'percent' => 12
                ),
                array(
                    'property_id' => 6,
                    'contract_length_id' => 1,
                    'percent' => 12
                ),
                array(
                    'property_id' => 6,
                    'contract_length_id' => 2,
                    'percent' => 12
                ),
                array(
                    'property_id' => 6,
                    'contract_length_id' => 3,
                    'percent' => 12
                ),
                array(
                    'property_id' => 6,
                    'contract_length_id' => 4,
                    'percent' => 12
                ),
                array(
                    'property_id' => 7,
                    'contract_length_id' => 1,
                    'percent' => 12
                ),
                array(
                    'property_id' => 7,
                    'contract_length_id' => 2,
                    'percent' => 12
                ),
                array(
                    'property_id' => 7,
                    'contract_length_id' => 3,
                    'percent' => 12
                ),
                array(
                    'property_id' => 7,
                    'contract_length_id' => 4,
                    'percent' => 12
                ),
                array(
                    'property_id' => 8,
                    'contract_length_id' => 1,
                    'percent' => 12
                ),
                array(
                    'property_id' => 8,
                    'contract_length_id' => 2,
                    'percent' => 12
                ),
                array(
                    'property_id' => 8,
                    'contract_length_id' => 3,
                    'percent' => 12
                ),
                array(
                    'property_id' => 8,
                    'contract_length_id' => 4,
                    'percent' => 12
                ),
                array(
                    'property_id' => 9,
                    'contract_length_id' => 1,
                    'percent' => 12
                ),
                array(
                    'property_id' => 9,
                    'contract_length_id' => 2,
                    'percent' => 12
                ),
                array(
                    'property_id' => 9,
                    'contract_length_id' => 3,
                    'percent' => 12
                ),
                array(
                    'property_id' => 9,
                    'contract_length_id' => 4,
                    'percent' => 12
                )
            )
        );
    }
}
