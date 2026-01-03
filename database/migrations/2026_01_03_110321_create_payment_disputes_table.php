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
        Schema::create('payment_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->foreignId('initiated_by')->constrained('users')->onDelete('cascade');
            $table->enum('reason', ['unauthorized', 'duplicate', 'fraud', 'product_not_received', 'product_unacceptable', 'other'])->default('other');
            $table->text('description');
            $table->enum('status', ['open', 'under_review', 'resolved', 'closed', 'won', 'lost'])->default('open');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->enum('resolution_type', ['refund', 'chargeback', 'no_action'])->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->json('evidence')->nullable(); // Preuves, documents
            $table->timestamps();
            
            $table->index('payment_id');
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
        Schema::dropIfExists('payment_disputes');
    }
};
