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
        Schema::create('travelbook_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('traveler_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('experience_id')->nullable()->constrained('experiences')->onDelete('set null');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->string('title');
            $table->text('content');
            $table->date('entry_date');
            $table->string('location')->nullable(); // Lieu de l'entrée
            $table->json('location_details')->nullable(); // {address, city, region, coordinates: {lat, lng}}
            $table->json('photos')->nullable(); // Array of Cloudinary URLs
            $table->json('tags')->nullable(); // Array of tags
            $table->enum('visibility', ['private', 'friends', 'public'])->default('private');
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->json('metadata')->nullable(); // Additional data (weather, mood, etc.)
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('traveler_id');
            $table->index('experience_id');
            $table->index('booking_id');
            $table->index('entry_date');
            $table->index('visibility');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travelbook_entries');
    }
};
