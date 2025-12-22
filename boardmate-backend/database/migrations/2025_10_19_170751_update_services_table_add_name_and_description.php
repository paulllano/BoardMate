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
        Schema::table('services', function (Blueprint $table) {
            $table->string('name')->after('service_name');
            $table->text('description')->nullable()->after('name');
            $table->string('category')->nullable()->after('description');
            $table->string('provider')->nullable()->after('category');
            $table->string('contact_info')->nullable()->after('provider');
            $table->enum('availability', ['available', 'unavailable', 'limited'])->default('available')->after('contact_info');
            $table->boolean('is_recurring')->default(false)->after('availability');
            $table->text('notes')->nullable()->after('is_recurring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['name', 'description', 'category', 'provider', 'contact_info', 'availability', 'is_recurring', 'notes']);
        });
    }
};
