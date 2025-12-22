<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
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
        $services = ['WiFi', 'Laundry', 'Meals', 'Parking', 'Cleaning', 'Security', 'Maintenance'];

        // Create 2-3 services per boarding house
        foreach ($boardingHouseIds as $houseId) {
            $serviceCount = rand(2, 3);
            for ($i=0; $i<$serviceCount; $i++) {
                $serviceName = $services[array_rand($services)];
                $data[] = [
                    'boarding_house_id' => $houseId,
                    'service_name' => $serviceName,
                    'name' => $serviceName . ' Service',
                    'description' => fake()->paragraph(2),
                    'price' => rand(100, 2000),
                    'category' => fake()->randomElement(['cleaning', 'maintenance', 'security', 'utilities', 'food', 'transportation', 'other']),
                    'availability' => fake()->randomElement(['available', 'unavailable', 'limited']),
                    'is_recurring' => fake()->boolean(70), // 70% chance of being recurring
                    'notes' => fake()->sentence(5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('services')->insert($data);
    }
}
