<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(AmenitiesSeeder::class);
        $this->call(ContractLengthsSeeder::class);
        $this->call(PropertyAmenitiesSeeder::class);
        $this->call(PropertyContractLengthsSeeder::class);
        $this->call(PropertySeeder::class);
        $this->call(LayoutDesignSeeder::class);
        $this->call(PropertyImagesSeeder::class);
    }
}
