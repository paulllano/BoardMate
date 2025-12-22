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
        Schema::table('boarders', function (Blueprint $table) {
            if (!Schema::hasColumn('boarders', 'email')) {
                $table->string('email')->nullable()->after('name');
            }
            if (!Schema::hasColumn('boarders', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('boarders', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('boarders', 'address')) {
                $table->text('address')->nullable()->after('date_of_birth');
            }
        });
        
        // Update existing boarders with fake email addresses if they don't have one
        DB::table('boarders')->whereNull('email')->orWhere('email', '')->update([
            'email' => DB::raw('CONCAT("boarder", id, "@example.com")')
        ]);
        
        // Add unique constraint to email if it doesn't exist
        if (!Schema::hasIndex('boarders', 'boarders_email_unique')) {
            Schema::table('boarders', function (Blueprint $table) {
                $table->unique('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boarders', function (Blueprint $table) {
            if (Schema::hasColumn('boarders', 'email')) {
                $table->dropUnique('boarders_email_unique');
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('boarders', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('boarders', 'date_of_birth')) {
                $table->dropColumn('date_of_birth');
            }
            if (Schema::hasColumn('boarders', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};
