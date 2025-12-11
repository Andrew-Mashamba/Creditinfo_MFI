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
        Schema::create('mandatory_savings_tracking', function (Blueprint $table) {
            $table->id();
            $table->string('client_number')->nullable(); // Member/client number
            $table->string('account_number')->nullable(); // Mandatory savings account number
            $table->integer('year')->nullable(); // Year for the payment
            $table->integer('month')->nullable(); // Month for the payment (1-12)
            $table->decimal('required_amount', 15, 2)->nullable(); // Required amount for this month
            $table->decimal('paid_amount', 15, 2)->default(0)->nullable(); // Amount actually paid
            $table->decimal('balance', 15, 2)->nullable(); // Outstanding balance (required - paid)
            $table->enum('status', ['PAID', 'PARTIAL', 'UNPAID', 'OVERDUE'])->default('UNPAID')->nullable();
            $table->date('due_date')->nullable(); // Due date for the payment
            $table->date('paid_date')->nullable(); // Date when payment was made
            $table->integer('months_in_arrears')->default(0)->nullable(); // Number of months in arrears
            $table->decimal('total_arrears', 15, 2)->default(0)->nullable(); // Total arrears amount
            $table->text('notes')->nullable(); // Additional notes
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['client_number', 'year', 'month']);
            $table->index(['status', 'due_date']);
            $table->index(['account_number', 'year', 'month']);
            $table->index('due_date');
        });

        Schema::create('mandatory_savings_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('client_number')->nullable(); // Member/client number
            $table->string('account_number')->nullable(); // Mandatory savings account number
            $table->integer('year')->nullable(); // Year for the payment
            $table->integer('month')->nullable(); // Month for the payment
            $table->enum('notification_type', ['FIRST_REMINDER', 'SECOND_REMINDER', 'FINAL_REMINDER', 'OVERDUE_NOTICE'])->nullable();
            $table->enum('notification_method', ['SMS', 'EMAIL', 'SYSTEM'])->default('SYSTEM')->nullable();
            $table->text('message')->nullable(); // Notification message
            $table->enum('status', ['PENDING', 'SENT', 'FAILED'])->default('PENDING')->nullable();
            $table->timestamp('sent_at')->nullable(); // When notification was sent
            $table->timestamp('scheduled_at')->nullable(); // When notification should be sent
            $table->json('metadata')->nullable(); // Additional data (SMS/Email details)
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['client_number', 'year', 'month']);
            $table->index(['status', 'scheduled_at']);
            $table->index('notification_type');
        });

        Schema::create('mandatory_savings_settings', function (Blueprint $table) {
            $table->id();
            $table->string('institution_id')->default('1')->nullable(); // Institution ID (default 1)
            $table->string('mandatory_savings_account')->nullable(); // Account number for mandatory savings
            $table->decimal('monthly_amount', 15, 2)->nullable(); // Monthly required amount
            $table->integer('due_day')->default(5)->nullable(); // Day of month when payment is due
            $table->integer('grace_period_days')->default(5)->nullable(); // Grace period after due date
            $table->boolean('enable_notifications')->default(true)->nullable();
            $table->integer('first_reminder_days')->default(7)->nullable(); // Days before due date for first reminder
            $table->integer('second_reminder_days')->default(3)->nullable(); // Days before due date for second reminder
            $table->integer('final_reminder_days')->default(1)->nullable(); // Days before due date for final reminder
            $table->boolean('enable_sms_notifications')->default(false)->nullable();
            $table->boolean('enable_email_notifications')->default(false)->nullable();
            $table->text('sms_template')->nullable(); // SMS template
            $table->text('email_template')->nullable(); // Email template
            $table->json('additional_settings')->nullable(); // Additional configuration
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mandatory_savings_notifications');
        Schema::dropIfExists('mandatory_savings_tracking');
        Schema::dropIfExists('mandatory_savings_settings');
    }
}; 