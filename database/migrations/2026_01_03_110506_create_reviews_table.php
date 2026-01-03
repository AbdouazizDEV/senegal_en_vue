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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('experience_id')->constrained('experiences')->onDelete('cascade');
            $table->foreignId('traveler_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating')->unsigned(); // 1-5
            $table->string('title')->nullable();
            $table->text('comment');
            $table->enum('status', ['pending', 'approved', 'rejected', 'reported'])->default('pending');
            $table->boolean('is_verified')->default(false); // Avis vérifié (réservation confirmée)
            $table->boolean('is_featured')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->json('images')->nullable(); // Photos jointes
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('booking_id');
            $table->index('experience_id');
            $table->index('traveler_id');
            $table->index('provider_id');
            $table->index('status');
            $table->index('rating');
            $table->index('created_at');
            
            // Un voyageur ne peut laisser qu'un seul avis par réservation
            $table->unique(['booking_id', 'traveler_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
