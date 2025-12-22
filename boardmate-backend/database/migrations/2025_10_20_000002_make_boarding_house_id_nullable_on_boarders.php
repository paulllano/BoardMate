<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK first (name can vary, use schema inspection)
        Schema::table('boarders', function (Blueprint $table) {
            // Some DBs require dropping the constraint explicitly; wrap in try/catch via DB::statement
        });

        // Try common FK name; ignore failures
        try {
            DB::statement('ALTER TABLE `boarders` DROP FOREIGN KEY `boarders_boarding_house_id_foreign`');
        } catch (\Throwable $e) {
            // ignore if not exists
        }

        Schema::table('boarders', function (Blueprint $table) {
            $table->unsignedBigInteger('boarding_house_id')->nullable()->change();
        });

        // Re-add FK but allow nulls
        Schema::table('boarders', function (Blueprint $table) {
            $table->foreign('boarding_house_id')->references('id')->on('boarding_houses')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Reverse: make it not nullable again
        try {
            DB::statement('ALTER TABLE `boarders` DROP FOREIGN KEY `boarders_boarding_house_id_foreign`');
        } catch (\Throwable $e) {
        }

        Schema::table('boarders', function (Blueprint $table) {
            $table->unsignedBigInteger('boarding_house_id')->nullable(false)->change();
        });

        Schema::table('boarders', function (Blueprint $table) {
            $table->foreign('boarding_house_id')->references('id')->on('boarding_houses')->onDelete('cascade');
        });
    }
};


