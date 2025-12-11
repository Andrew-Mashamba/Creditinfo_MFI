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
        Schema::create('loan_notification_schedule', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id', 50)->index();
            $table->string('client_number', 50)->nullable();
            $table->string('notification_type', 50); // upcoming_3days, upcoming_2days, upcoming_1day, due_day, arrears_5, arrears_10, etc
            $table->date('scheduled_date');
            $table->date('due_date')->nullable();
            $table->decimal('amount_due', 20, 2)->default(0);
            $table->decimal('outstanding_amount', 20, 2)->default(0);
            $table->integer('days_before_due')->nullable(); // For upcoming reminders: 3, 2, 1
            $table->integer('days_in_arrears')->nullable(); // For arrears notifications
            $table->string('channel', 20)->default('sms'); // sms, email, both
            $table->string('status', 20)->default('pending'); // pending, sent, failed, cancelled
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('delivery_status', 50)->nullable();
            $table->text('message_content')->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('external_reference', 100)->nullable(); // SMS gateway reference
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('restrict');
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['loan_id', 'notification_type', 'scheduled_date']);
            $table->index(['scheduled_date', 'status']);
            $table->index(['notification_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_notification_schedule');
    }
};
