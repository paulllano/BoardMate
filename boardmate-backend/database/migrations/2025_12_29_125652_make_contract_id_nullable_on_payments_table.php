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
        // Drop the foreign key constraint first
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['contract_id']);
        });
        
        // Make contract_id nullable using raw SQL
        DB::statement('ALTER TABLE `payments` MODIFY COLUMN `contract_id` BIGINT UNSIGNED NULL');
        
        // Re-add the foreign key constraint with nullable support
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraint
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['contract_id']);
        });
        
        // Make contract_id required again (only if no null values exist)
        // Note: This will fail if there are NULL values in contract_id
        DB::statement('ALTER TABLE `payments` MODIFY COLUMN `contract_id` BIGINT UNSIGNED NOT NULL');
        
        // Re-add the foreign key constraint
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
        });
    }
};
