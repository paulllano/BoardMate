<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Boarding Houses
        Schema::create('boarding_houses', function (Blueprint $table) {
            $table->id(); // PK
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->string('name');
            $table->string('address');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Boarders
        Schema::create('boarders', function (Blueprint $table) {
            $table->id(); // PK
            $table->foreignId('boarding_house_id')->constrained('boarding_houses')->onDelete('cascade');
            $table->string('name');
            $table->integer('age');
            $table->string('contact');
            $table->timestamps();
        });

        // Services
        Schema::create('services', function (Blueprint $table) {
            $table->id(); // PK
            $table->foreignId('boarding_house_id')->constrained('boarding_houses')->onDelete('cascade');
            $table->string('service_name');
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });

        // Contracts
        Schema::create('contracts', function (Blueprint $table) {
            $table->id(); // PK
            $table->foreignId('boarder_id')->constrained('boarders')->onDelete('cascade');
            $table->foreignId('boarding_house_id')->constrained('boarding_houses')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['Pending', 'Active', 'Completed', 'Cancelled'])->default('Pending');
            $table->timestamps();
        });
        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // PK
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->enum('method', ['Cash', 'GCash']);
            $table->enum('status', ['Pending', 'Paid', 'Failed'])->default('Pending');
            $table->timestamps();
        });

        // Reviews
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boarding_house_id')->constrained('boarding_houses')->onDelete('cascade');
            $table->foreignId('boarder_id')->constrained('boarders')->onDelete('cascade');
            $table->unsignedTinyInteger('rating'); // 1–5 stars
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('services');
        Schema::dropIfExists('boarders');
        Schema::dropIfExists('boarding_houses');
    }
};
