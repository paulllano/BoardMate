<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boarders', function (Blueprint $table) {
            if (!Schema::hasColumn('boarders', 'password')) {
                $table->string('password')->nullable()->after('address');
            }
            if (!Schema::hasColumn('boarders', 'remember_token')) {
                $table->rememberToken();
            }
        });
    }

    public function down(): void
    {
        Schema::table('boarders', function (Blueprint $table) {
            if (Schema::hasColumn('boarders', 'password')) {
                $table->dropColumn('password');
            }
            if (Schema::hasColumn('boarders', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
        });
    }
};


