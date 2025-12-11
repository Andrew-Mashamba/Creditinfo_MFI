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
        Schema::create('till_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 100)->unique();
            $table->foreignId('till_id')->constrained('tills')->onDelete('cascade');
            $table->foreignId('teller_id')->constrained('tellers')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->enum('type', ['deposit', 'withdrawal', 'transfer', 'opening_balance', 'closing_balance', 'adjustment']);
            $table->enum('transaction_type', ['cash_in', 'cash_out', 'neutral']); // For balance calculations
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('account_number', 50)->nullable(); // Related member account
            $table->text('description');
            $table->json('denomination_breakdown')->nullable(); // For cash transactions
            $table->string('receipt_number', 100)->nullable();
            $table->enum('status', ['pending', 'completed', 'reversed', 'cancelled'])->default('completed');
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();
            
            $table->index(['till_id', 'created_at']);
            $table->index(['type', 'status']);
            $table->index('teller_id');
            $table->index('client_id');
            $table->index('account_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('till_transactions');
    }
};
