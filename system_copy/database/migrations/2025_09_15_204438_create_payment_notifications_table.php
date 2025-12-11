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
        Schema::create('payment_notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['upcoming_payment', 'overdue_payment', 'payment_reminder']);
            $table->enum('category', ['payable', 'receivable']);
            $table->unsignedBigInteger('reference_id'); // ID of the payable or receivable
            $table->string('reference_type'); // 'trade_payables' or 'trade_receivables'
            $table->string('vendor_or_customer_name');
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->integer('days_until_due')->nullable(); // Negative for overdue
            $table->text('description')->nullable();
            $table->json('recipients')->nullable(); // Email addresses that were notified
            $table->enum('notification_status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->unsignedBigInteger('read_by')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('type');
            $table->index('category');
            $table->index('notification_status');
            $table->index('is_read');
            $table->index('due_date');
            $table->index(['reference_id', 'reference_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_notifications');
    }
};