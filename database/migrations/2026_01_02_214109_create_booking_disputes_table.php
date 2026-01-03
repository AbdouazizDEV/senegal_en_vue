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
        Schema::create('booking_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('initiated_by')->constrained('users')->onDelete('cascade');
            $table->enum('reason', ['service_not_provided', 'quality_issue', 'cancellation_dispute', 'payment_issue', 'other'])->default('other');
            $table->text('description');
            $table->enum('status', ['open', 'in_review', 'resolved', 'closed'])->default('open');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->enum('resolution_type', ['refund', 'partial_refund', 'credit', 'no_action'])->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->json('evidence')->nullable(); // Photos, documents, etc.
            $table->timestamps();
            
            $table->index('booking_id');
            $table->index('initiated_by');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_disputes');
    }
};
