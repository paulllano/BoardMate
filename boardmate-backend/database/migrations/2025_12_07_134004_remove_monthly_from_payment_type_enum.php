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
        // MySQL doesn't support direct enum modification, so we need to alter the column
        // First, update any existing 'monthly' values to 'partial'
        DB::table('payments')
            ->where('payment_type', 'monthly')
            ->update(['payment_type' => 'partial']);
        
        // Then alter the column to remove 'monthly' from the enum
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_type ENUM('full', 'partial') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore 'monthly' to the enum
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_type ENUM('full', 'monthly', 'partial') NULL");
    }
};
