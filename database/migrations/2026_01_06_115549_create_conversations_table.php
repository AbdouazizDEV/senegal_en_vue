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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('traveler_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('experience_id')->nullable()->constrained('experiences')->onDelete('set null');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->string('subject')->nullable();
            $table->enum('status', ['active', 'archived', 'blocked'])->default('active');
            $table->timestamp('last_message_at')->nullable();
            $table->integer('unread_count_traveler')->default(0);
            $table->integer('unread_count_provider')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('traveler_id');
            $table->index('provider_id');
            $table->index('experience_id');
            $table->index('booking_id');
            $table->index('status');
            $table->index('last_message_at');
            
            // Un voyageur ne peut avoir qu'une seule conversation active avec un prestataire pour une expérience/booking
            $table->unique(['traveler_id', 'provider_id', 'experience_id'], 'unique_traveler_provider_experience');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
