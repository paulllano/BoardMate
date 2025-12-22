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
        Schema::create('deleted_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_contract_id');
            $table->foreignId('boarder_id')->nullable()->constrained('boarders')->onDelete('set null');
            $table->foreignId('boarding_house_id')->nullable()->constrained('boarding_houses')->onDelete('set null');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status');
            $table->decimal('rent_amount', 10, 2)->default(0);
            $table->foreignId('deleted_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('deleted_at');
            $table->timestamps();
            
            // Index for faster lookups
            $table->index('original_contract_id');
            $table->index('deleted_by');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deleted_contracts');
    }
};
