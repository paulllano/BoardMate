<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            BoardingHouseSeeder::class,
            BoarderSeeder::class,
            ServiceSeeder::class,
            ContractSeeder::class,
            PaymentSeeder::class,
            ReviewSeeder::class,
        ]);
    }

}
