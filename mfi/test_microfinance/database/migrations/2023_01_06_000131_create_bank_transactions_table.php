<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('analysis_sessions')->onDelete('cascade');
            
            // Transaction details
            $table->date('transaction_date');
            $table->date('value_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('narration');
            $table->decimal('withdrawal_amount', 20, 2)->default(0);
            $table->decimal('deposit_amount', 20, 2)->default(0);
            $table->decimal('balance', 20, 2)->nullable();
            
            // Reconciliation fields
            $table->foreignId('matched_transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->enum('reconciliation_status', ['unreconciled', 'matched', 'partial', 'reconciled'])->default('unreconciled');
            $table->decimal('match_confidence', 5, 2)->nullable(); // 0-100 confidence score
            $table->text('reconciliation_notes')->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->string('reconciled_by')->nullable();
            
            // Bank-specific fields
            $table->string('branch')->nullable();
            $table->string('transaction_type')->nullable();
            $table->json('raw_data')->nullable(); // Store original parsed data
            
            $table->timestamps();
            
            $table->index(['session_id', 'transaction_date']);
            $table->index(['reconciliation_status', 'transaction_date']);
            $table->index(['matched_transaction_id']);
            $table->index(['withdrawal_amount', 'deposit_amount']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_transactions');
    }
};
