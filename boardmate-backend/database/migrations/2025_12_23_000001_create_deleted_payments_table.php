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
        Schema::create('deleted_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_payment_id');
            $table->foreignId('contract_id')->nullable()->constrained('contracts')->onDelete('set null');
            $table->unsignedBigInteger('boarding_house_id')->nullable(); // Store directly for filtering even when contract is deleted
            $table->foreignId('boarder_id')->nullable()->constrained('boarders')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('method')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('status');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->string('payment_type')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('deleted_at');
            $table->timestamps();
            
            // Index for faster lookups
            $table->index('original_payment_id');
            $table->index('boarding_house_id');
            $table->index('deleted_by');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deleted_payments');
    }
};
