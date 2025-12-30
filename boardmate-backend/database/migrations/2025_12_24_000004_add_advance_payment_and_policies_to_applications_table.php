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
            $table->foreignId('advance_payment_id')->nullable()->constrained('payments')->onDelete('set null')->after('boarding_house_id');
            $table->boolean('policies_accepted')->default(false)->after('transfer_rejected_at');
            $table->timestamp('policies_accepted_at')->nullable()->after('policies_accepted');
            $table->text('policies_text')->nullable()->after('policies_accepted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['advance_payment_id']);
            $table->dropColumn(['advance_payment_id', 'policies_accepted', 'policies_accepted_at', 'policies_text']);
        });
    }
};
