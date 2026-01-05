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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('experience_id')->constrained('experiences')->onDelete('cascade');
            $table->boolean('notify_on_price_drop')->default(false);
            $table->boolean('notify_on_availability')->default(false);
            $table->boolean('notify_on_new_reviews')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            
            // Contrainte unique : un utilisateur ne peut ajouter une expérience qu'une seule fois
            $table->unique(['user_id', 'experience_id'], 'user_experience_unique');
            
            $table->index('user_id');
            $table->index('experience_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
