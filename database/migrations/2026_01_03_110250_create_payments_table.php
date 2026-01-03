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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('traveler_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded', 'partially_refunded', 'cancelled'])->default('pending');
            $table->enum('type', ['booking', 'refund', 'commission', 'transfer'])->default('booking');
            $table->decimal('amount', 10, 2);
            $table->decimal('commission_amount', 10, 2)->default(0); // Commission plateforme
            $table->decimal('provider_amount', 10, 2)->default(0); // Montant pour le prestataire
            $table->string('currency', 3)->default('XOF');
            $table->string('payment_method')->nullable(); // stripe, paypal, mobile_money, etc.
            $table->string('payment_gateway')->nullable(); // Nom du gateway
            $table->string('transaction_id')->unique()->nullable();
            $table->string('gateway_reference')->nullable();
            $table->enum('gateway_status', ['pending', 'success', 'failed', 'cancelled'])->nullable();
            $table->text('gateway_response')->nullable(); // Réponse JSON du gateway
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('transferred_at')->nullable(); // Date de transfert au prestataire
            $table->text('failure_reason')->nullable();
            $table->text('refund_reason')->nullable();
            $table->json('metadata')->nullable(); // Données supplémentaires
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('booking_id');
            $table->index('traveler_id');
            $table->index('provider_id');
            $table->index('status');
            $table->index('type');
            $table->index('transaction_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
