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
            $table->foreignId('boarder_id')->nullable()->after('contract_id')->constrained('boarders')->onDelete('cascade');
            $table->string('payment_method')->nullable()->after('method');
            $table->string('reference_number')->nullable()->after('payment_method');
            $table->text('notes')->nullable()->after('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['boarder_id', 'payment_method', 'reference_number', 'notes']);
        });
    }
};
