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
        Schema::create('discovery_preferences', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->unique();
            $table->json('preferred_types')->nullable(); // ['tour', 'activity', 'workshop', etc.]
            $table->json('preferred_regions')->nullable(); // ['Dakar', 'Saint-Louis', etc.]
            $table->json('preferred_tags')->nullable(); // ['culture', 'nature', 'histoire', etc.]
            $table->decimal('min_price', 10, 2)->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->integer('min_duration_minutes')->nullable();
            $table->integer('max_duration_minutes')->nullable();
            $table->integer('preferred_participants')->nullable();
            $table->json('budget_range')->nullable(); // ['low', 'medium', 'high']
            $table->json('interests')->nullable(); // Centres d'intérêt supplémentaires
            $table->boolean('prefer_featured')->default(false);
            $table->boolean('prefer_eco_friendly')->default(false);
            $table->boolean('prefer_certified_providers')->default(false);
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discovery_preferences');
    }
};
