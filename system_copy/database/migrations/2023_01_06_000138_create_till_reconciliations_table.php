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
        Schema::create('till_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('till_id')->constrained('tills')->onDelete('cascade');
            $table->foreignId('teller_id')->constrained('tellers')->onDelete('cascade');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('reconciliation_date');
            $table->decimal('opening_balance', 15, 2);
            $table->decimal('closing_balance_system', 15, 2); // System calculated balance
            $table->decimal('closing_balance_actual', 15, 2); // Physically counted balance
            $table->decimal('total_deposits', 15, 2)->default(0.00);
            $table->decimal('total_withdrawals', 15, 2)->default(0.00);
            $table->decimal('total_transfers_in', 15, 2)->default(0.00);
            $table->decimal('total_transfers_out', 15, 2)->default(0.00);
            $table->decimal('variance', 15, 2)->default(0.00); // Difference between system and actual
            $table->json('denomination_breakdown')->nullable(); // Physical cash count
            $table->integer('transaction_count')->default(0);
            $table->enum('status', ['balanced', 'over', 'short', 'pending_approval', 'approved'])->default('pending_approval');
            $table->text('variance_explanation')->nullable();
            $table->text('supervisor_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['till_id', 'reconciliation_date']);
            $table->index(['reconciliation_date', 'status']);
            $table->index('teller_id');
            $table->index('supervisor_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('till_reconciliations');
    }
};
