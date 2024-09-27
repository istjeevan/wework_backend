<?php

use App\Models\ContractLengths;
use Illuminate\Database\Seeder;

class ContractLengthsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('contract_lengths')->delete();
        DB::table('contract_lengths')->insert(
            array(
                
                array(
                    'uuid' => ContractLengths::createUuid(),
                    'length' => 12
                ),
                array(
                    'uuid' => ContractLengths::createUuid(),
                    'length' => 6
                ),
                array(
                    'uuid' => ContractLengths::createUuid(),
                    'length' => 2
                ),
                array(
                    'uuid' => ContractLengths::createUuid(),
                    'length' => 1
                )
            )
        );
    }
}
