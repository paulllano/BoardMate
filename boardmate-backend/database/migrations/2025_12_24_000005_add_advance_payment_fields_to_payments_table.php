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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('application_id')->nullable()->constrained('applications')->onDelete('set null')->after('contract_id');
            $table->boolean('is_advance_payment')->default(false)->after('payment_type');
            $table->boolean('used_as_credit')->default(false)->after('is_advance_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
            $table->dropColumn(['application_id', 'is_advance_payment', 'used_as_credit']);
        });
    }
};
