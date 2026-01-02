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
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->string('slug')->unique();
            $table->enum('type', ['activity', 'tour', 'workshop', 'event', 'accommodation', 'restaurant'])->default('activity');
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'suspended', 'reported'])->default('draft');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('XOF');
            $table->integer('duration_minutes')->nullable();
            $table->integer('max_participants')->nullable();
            $table->integer('min_participants')->default(1);
            $table->json('images')->nullable(); // Array of Cloudinary URLs
            $table->json('location')->nullable(); // {address, city, region, coordinates: {lat, lng}}
            $table->json('schedule')->nullable(); // Availability schedule
            $table->json('tags')->nullable(); // Array of tags
            $table->json('amenities')->nullable(); // Array of amenities
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('bookings_count')->default(0);
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('reviews_count')->default(0);
            $table->text('rejection_reason')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('provider_id');
            $table->index('status');
            $table->index('type');
            $table->index('slug');
            $table->index('is_featured');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
