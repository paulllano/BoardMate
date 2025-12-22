<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $data = [];

        // Known login for testing
        $data[] = [
            'name' => 'BoardMate Owner',
            'email' => 'admin@boardmate.com',
            'phone' => '09123456789',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Create at least 5 admins total to support other seeders
        for ($i=0; $i<4; $i++) {
            $data[] = [
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'password' => bcrypt('password'),
                'role' => fake()->randomElement(['staff', 'owner']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('admins')->insert($data);
    }
}
