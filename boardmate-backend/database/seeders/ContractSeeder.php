<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing boarder and boarding house IDs
        $boarderIds = \App\Models\Boarder::pluck('id')->toArray();
        $boardingHouseIds = \App\Models\BoardingHouse::pluck('id')->toArray();
        
        if (empty($boarderIds) || empty($boardingHouseIds)) {
            \Log::warning('No boarders or boarding houses found. Please seed them first.');
            return;
        }

        $data = [];

        // Create contracts for boarders who have boarding houses
        $boardersWithHouses = \App\Models\Boarder::whereNotNull('boarding_house_id')->pluck('id')->toArray();
        
        // Create contracts for boarders with houses
        foreach ($boardersWithHouses as $boarderId) {
            $boarder = \App\Models\Boarder::find($boarderId);
            if ($boarder && $boarder->boarding_house_id) {
                $start = fake()->dateTimeBetween('-1 year', 'now');
                $end = (clone $start)->modify('+' . rand(6, 12) . ' months');

                $data[] = [
                    'boarder_id' => $boarderId,
                    'boarding_house_id' => $boarder->boarding_house_id,
                    'start_date' => $start->format('Y-m-d'),
                    'end_date' => $end->format('Y-m-d'),
                    'status' => fake()->randomElement(['Pending', 'Active', 'Completed', 'Cancelled']),
                    'rent_amount' => rand(3000, 15000),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Add a few more random contracts
        for ($i=0; $i<5; $i++) {
            $start = fake()->dateTimeBetween('-6 months', 'now');
            $end = (clone $start)->modify('+' . rand(6, 12) . ' months');

            $data[] = [
                'boarder_id' => $boarderIds[array_rand($boarderIds)],
                'boarding_house_id' => $boardingHouseIds[array_rand($boardingHouseIds)],
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'status' => fake()->randomElement(['Pending', 'Active', 'Completed', 'Cancelled']),
                'rent_amount' => rand(3000, 15000),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('contracts')->insert($data);
    }
}
