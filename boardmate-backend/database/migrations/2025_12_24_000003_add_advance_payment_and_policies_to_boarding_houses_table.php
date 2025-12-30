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
        Schema::table('boarding_houses', function (Blueprint $table) {
            $table->decimal('advance_payment_amount', 10, 2)->default(0)->after('gender_preference');
            $table->text('policies')->nullable()->after('advance_payment_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boarding_houses', function (Blueprint $table) {
            $table->dropColumn(['advance_payment_amount', 'policies']);
        });
    }
};
