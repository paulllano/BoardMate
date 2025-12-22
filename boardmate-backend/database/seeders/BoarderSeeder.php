<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoarderSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing boarding house IDs
        $boardingHouseIds = \App\Models\BoardingHouse::pluck('id')->toArray();
        
        if (empty($boardingHouseIds)) {
            \Log::warning('No boarding houses found. Please seed boarding houses first.');
            return;
        }

        $data = [];

        // Known boarder for testing
        $data[] = [
            'boarding_house_id' => null,
            'name' => 'Sample Boarder',
            'email' => 'boarder@boardmate.com',
            'phone' => '09999999999',
            'age' => 22,
            'contact' => '09999999999',
            'date_of_birth' => '2003-01-01',
            'address' => 'Sample Address',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Create 20 boarders, some with boarding houses, some without
        for ($i=0; $i<20; $i++) {   
            $data[] = [
                'boarding_house_id' => fake()->boolean(70) ? $boardingHouseIds[array_rand($boardingHouseIds)] : null, // 70% chance of having a boarding house
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'age' => rand(18, 30),
                'contact' => fake()->phoneNumber(),
                'date_of_birth' => fake()->date('Y-m-d', '-18 years'), // At least 18 years old
                'address' => fake()->streetAddress() . ', ' . fake()->city(),
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('boarders')->insert($data);
    }
}
