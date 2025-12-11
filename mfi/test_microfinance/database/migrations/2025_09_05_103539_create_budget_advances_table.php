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
        Schema::create('budget_advances', function (Blueprint $table) {
            $table->id();
            $table->string('advance_number')->unique();
            $table->foreignId('budget_id')->constrained('budget_managements');
            $table->integer('from_period'); // Period borrowing from
            $table->integer('from_year');
            $table->integer('to_period'); // Period borrowing to
            $table->integer('to_year');
            $table->decimal('advance_amount', 20, 2);
            $table->decimal('repaid_amount', 20, 2)->default(0);
            $table->decimal('outstanding_amount', 20, 2);
            $table->text('reason');
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'REPAID', 'PARTIAL_REPAID', 'CANCELLED'])->default('PENDING');
            $table->date('due_date')->nullable();
            $table->enum('repayment_method', ['AUTOMATIC', 'MANUAL', 'DEDUCTION'])->default('AUTOMATIC');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['budget_id', 'status']);
            $table->index(['from_period', 'from_year']);
            $table->index(['to_period', 'to_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_advances');
    }
};