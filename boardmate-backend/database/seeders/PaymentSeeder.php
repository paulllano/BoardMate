<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing contract IDs with their boarder IDs
        $contracts = \App\Models\Contract::with('boarder')->get();
        
        if ($contracts->isEmpty()) {
            \Log::warning('No contracts found. Please seed contracts first.');
            return;
        }

        $data = [];

        // Create payments for existing contracts
        foreach ($contracts as $contract) {
            // Create 1-3 payments per contract
            $paymentCount = rand(1, 3);
            for ($i=0; $i<$paymentCount; $i++) {
                $method = fake()->randomElement(['Cash', 'GCash']);
                $status = fake()->randomElement(['pending', 'completed', 'failed', 'cancelled']);
                
                // Generate reference number based on payment method
                $referenceNumber = null;
                if ($method === 'GCash') {
                    // GCash format: GC + 10 digits (e.g., GC1234567890)
                    $referenceNumber = 'GC' . fake()->numerify('##########');
                } else {
                    // Cash format: CASH + date + random (e.g., CASH202501201234)
                    $referenceNumber = 'CASH' . fake()->date('Ymd') . fake()->numerify('####');
                }
                
                $data[] = [
                    'contract_id' => $contract->id,
                    'boarder_id' => $contract->boarder_id,
                    'amount' => rand(1000, min(5000, $contract->rent_amount)),
                    'payment_date' => fake()->dateTimeBetween($contract->start_date, 'now')->format('Y-m-d'),
                    'method' => $method,
                    'status' => $status,
                    'payment_method' => $method === 'GCash' ? 'GCash' : 'Cash',
                    'reference_number' => $referenceNumber,
                    'payment_type' => fake()->randomElement(['full', 'partial']),
                    'notes' => fake()->boolean(30) ? fake()->sentence() : null, // 30% chance of having notes
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('payments')->insert($data);
    }
}
