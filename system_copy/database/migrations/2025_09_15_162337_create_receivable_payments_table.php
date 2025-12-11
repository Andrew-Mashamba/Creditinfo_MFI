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
        Schema::create('receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receivable_id');
            $table->date('payment_date');
            $table->decimal('amount', 20, 2);
            $table->string('payment_method', 50)->default('bank_transfer');
            $table->string('reference_number')->nullable();
            $table->string('payment_account_id')->nullable(); // Bank account used
            $table->decimal('bank_charges', 20, 2)->default(0);
            $table->decimal('discount_amount', 20, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('transaction_reference')->nullable(); // GL reference
            $table->enum('status', ['pending', 'completed', 'reversed', 'failed'])->default('completed');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('receivable_id');
            $table->index('payment_date');
            $table->index('reference_number');
            $table->index('status');
            
            // Foreign key
            $table->foreign('receivable_id')->references('id')->on('trade_receivables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivable_payments');
    }
};