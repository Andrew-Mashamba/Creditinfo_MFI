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
        Schema::create('transaction_reversals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->string('reversal_reference', 50)->unique()->index();
            $table->text('reason');
            $table->foreignId('reversed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_automatic')->default(false);
            $table->enum('status', [
                'pending',
                'processing', 
                'completed',
                'failed',
                'dead_letter'
            ])->default('pending')->index();
            
            // Correlation and tracking
            $table->uuid('correlation_id')->index();
            $table->integer('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            
            // External service details
            $table->string('external_reference', 100)->nullable()->index();
            $table->string('external_transaction_id', 100)->nullable();
            $table->json('external_request_payload')->nullable();
            $table->json('external_response_payload')->nullable();
            $table->string('external_status_code', 10)->nullable();
            $table->text('external_status_message')->nullable();
            
            // Error tracking
            $table->string('error_code', 50)->nullable()->index();
            $table->text('error_message')->nullable();
            
            // Timestamps
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Metadata for additional data
            $table->json('metadata')->nullable();
            
            // Indexes for performance
            $table->index(['status', 'created_at']);
            $table->index(['external_reference', 'status']);
            $table->index(['reversed_by', 'created_at']);
            $table->index(['is_automatic', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_reversals');
    }
}; 