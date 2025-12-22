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
        Schema::table('deleted_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('boarding_house_id')->nullable()->after('contract_id');
            $table->index('boarding_house_id');
        });
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
