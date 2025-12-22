<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardingHouseSeeder extends Seeder
{
    public function run(): void
    {
        // Get all existing admins first
        $adminIds = \App\Models\Admin::pluck('id')->toArray();
        
        if (empty($adminIds)) {
            \Log::warning('No admins found. Please seed admins first.');
            return;
        }

        $data = [];

        for ($i=0; $i<10; $i++) {
            $data[] = [
                'admin_id' => $adminIds[array_rand($adminIds)], // Use existing admin IDs
                'name' => \Illuminate\Support\Str::title(fake()->words(2, true)) . ' Boarding House',
                'address' => fake()->streetAddress() . ', ' . fake()->city() . ', ' . fake()->state() . ' ' . fake()->postcode(),
                'description' => fake()->paragraph(2),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('boarding_houses')->insert($data);
    }
}
