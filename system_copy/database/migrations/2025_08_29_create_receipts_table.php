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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('member_number')->nullable();
            $table->string('member_name')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('TZS');
            $table->string('payment_method', 50)->nullable();
            $table->string('depositor_name')->nullable();
            $table->text('narration')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->unsignedBigInteger('branch')->nullable();
            $table->string('transaction_type', 100)->nullable();
            $table->enum('status', ['GENERATED', 'PRINTED', 'CANCELLED'])->default('GENERATED');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('receipt_number');
            $table->index('member_number');
            $table->index('transaction_id');
            $table->index('account_id');
            $table->index('status');
            $table->index('generated_at');
            $table->index(['member_number', 'generated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};