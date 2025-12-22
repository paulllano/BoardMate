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
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('transfer_approved_by_previous_admin')->nullable()->after('reviewed_by')->constrained('admins')->onDelete('set null');
            $table->timestamp('transfer_approved_at')->nullable()->after('transfer_approved_by_previous_admin');
            $table->timestamp('transfer_rejected_at')->nullable()->after('transfer_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['transfer_approved_by_previous_admin']);
            $table->dropColumn(['transfer_approved_by_previous_admin', 'transfer_approved_at', 'transfer_rejected_at']);
        });
    }
};
