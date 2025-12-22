<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change column to VARCHAR temporarily to allow data updates
        DB::statement("ALTER TABLE `payments` MODIFY COLUMN `status` VARCHAR(20) DEFAULT 'Pending'");
        
        // Update existing data to match new enum values
        DB::table('payments')
            ->where('status', 'Pending')
            ->update(['status' => 'pending']);
        
        DB::table('payments')
            ->where('status', 'Paid')
            ->update(['status' => 'completed']);
        
        DB::table('payments')
            ->where('status', 'Failed')
            ->update(['status' => 'failed']);

        // Now change back to ENUM with the new values
        DB::statement("ALTER TABLE `payments` MODIFY COLUMN `status` ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert existing data back to old enum values
        DB::table('payments')
            ->where('status', 'pending')
            ->update(['status' => 'Pending']);
        
        DB::table('payments')
            ->where('status', 'completed')
            ->update(['status' => 'Paid']);
        
        DB::table('payments')
            ->where('status', 'failed')
            ->update(['status' => 'Failed']);
        
        DB::table('payments')
            ->where('status', 'cancelled')
            ->update(['status' => 'Failed']); // Map cancelled to Failed as fallback

        // Revert the enum column
        DB::statement("ALTER TABLE `payments` MODIFY COLUMN `status` ENUM('Pending', 'Paid', 'Failed') DEFAULT 'Pending'");
    }
};

