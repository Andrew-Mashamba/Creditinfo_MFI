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
        Schema::create('loan_penalty_accruals', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id', 50)->index();
            $table->string('loan_account_number', 50)->nullable();
            $table->string('client_number', 50)->nullable();
            $table->date('accrual_date');
            $table->decimal('outstanding_amount', 20, 2)->default(0); // Amount penalty is calculated on
            $table->integer('days_in_arrears')->default(0);
            $table->decimal('penalty_rate', 8, 4)->default(0); // Daily or monthly rate
            $table->string('rate_type', 20)->default('monthly'); // monthly, daily, yearly
            $table->decimal('daily_penalty', 20, 4)->default(0); // Calculated daily penalty
            $table->decimal('cumulative_penalty', 20, 2)->default(0); // Running total
            $table->decimal('penalty_paid', 20, 2)->default(0); // Amount already paid
            $table->decimal('penalty_balance', 20, 2)->default(0); // Outstanding penalty
            $table->string('loan_classification', 30)->nullable(); // CURRENT, WATCH, SUBSTANDARD, DOUBTFUL, LOSS
            $table->boolean('posted_to_gl')->default(false);
            $table->string('journal_reference', 50)->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('restrict');
            $table->timestamps();

            // Unique constraint to prevent duplicate accruals
            $table->unique(['loan_id', 'accrual_date']);

            // Indexes for reporting
            $table->index(['accrual_date', 'posted_to_gl']);
            $table->index(['year', 'month']);
            $table->index(['loan_classification']);
        });

        // Summary table for daily penalty accrual totals
        Schema::create('loan_penalty_accrual_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('accrual_date')->unique();
            $table->integer('total_loans_processed')->default(0);
            $table->integer('loans_with_penalty')->default(0);
            $table->decimal('total_penalty_accrued', 20, 2)->default(0);
            $table->decimal('total_penalty_collected', 20, 2)->default(0);
            $table->decimal('total_penalty_outstanding', 20, 2)->default(0);
            $table->integer('journal_entries_created')->default(0);
            $table->string('status', 20)->default('completed'); // running, completed, failed
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_penalty_accrual_summaries');
        Schema::dropIfExists('loan_penalty_accruals');
    }
};
