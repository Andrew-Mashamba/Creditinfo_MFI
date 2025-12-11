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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            
            // Process tracking
            $table->uuid('process_id')->nullable()->index();
            
            // Recipient information
            $table->string('recipient_type')->nullable(); // Class name of recipient
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            
            // Notification details
            $table->string('notification_type')->nullable(); // member_registration, loan_disbursement, etc.
            $table->string('channel')->nullable(); // email, sms, system
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'failed_permanently'])->default('pending');
            
            // Error tracking
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();
            
            // Retry tracking
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            
            // Timestamps
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            
            // Response data
            $table->json('response_data')->nullable();
            
            // Control numbers and payment info
            $table->json('control_numbers')->nullable();
            $table->string('payment_link')->nullable();
            
            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['recipient_type', 'recipient_id']);
            $table->index(['notification_type', 'status']);
            $table->index(['process_id', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
