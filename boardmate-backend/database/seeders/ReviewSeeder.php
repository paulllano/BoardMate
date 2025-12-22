<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing boarding house and boarder IDs
        $boardingHouseIds = \App\Models\BoardingHouse::pluck('id')->toArray();
        $boarderIds = \App\Models\Boarder::pluck('id')->toArray();
        
        if (empty($boardingHouseIds) || empty($boarderIds)) {
            \Log::warning('No boarding houses or boarders found. Please seed them first.');
            return;
        }

        $data = [];

        // Create reviews - prefer boarders who have contracts with boarding houses
        $contracts = \App\Models\Contract::with('boarder', 'boardingHouse')->get();
        
        // Create reviews from contracts (more realistic)
        foreach ($contracts->take(15) as $contract) {
            if ($contract->boarder && $contract->boardingHouse) {
                $data[] = [
                    'boarding_house_id' => $contract->boarding_house_id,
                    'boarder_id' => $contract->boarder_id,
                    'rating' => rand(3, 5), // Most reviews are positive
                    'comment' => fake()->paragraph(2),
                    'is_anonymous' => fake()->boolean(20), // 20% chance of being anonymous
                    'created_at' => fake()->dateTimeBetween($contract->start_date, 'now'),
                    'updated_at' => now(),
                ];
            }
        }

        // Add a few more random reviews
        for ($i = 0; $i < 5; $i++) {
            $data[] = [
                'boarding_house_id' => $boardingHouseIds[array_rand($boardingHouseIds)],
                'boarder_id' => $boarderIds[array_rand($boarderIds)],
                'rating' => rand(1, 5),
                'comment' => fake()->paragraph(2),
                'is_anonymous' => fake()->boolean(20),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('reviews')->insert($data);
    }
}
