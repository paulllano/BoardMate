<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column doesn't exist before adding it
        // This migration is redundant since the column already exists in create_deleted_payments_table
        // but we keep it for safety in case it was run separately before
        if (!Schema::hasColumn('deleted_payments', 'boarding_house_id')) {
            Schema::table('deleted_payments', function (Blueprint $table) {
                $table->unsignedBigInteger('boarding_house_id')->nullable()->after('contract_id');
                $table->index('boarding_house_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deleted_payments', function (Blueprint $table) {
            $table->dropIndex(['boarding_house_id']);
            $table->dropColumn('boarding_house_id');
        });
    }
};
