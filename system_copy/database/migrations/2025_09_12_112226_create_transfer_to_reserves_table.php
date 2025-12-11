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
        Schema::create('transfer_to_reserves', function (Blueprint $table) {
            $table->id();
            
            // Transfer identification
            $table->string('transfer_reference')->unique();
            $table->enum('transfer_type', [
                'STATUTORY_RESERVE',
                'GENERAL_RESERVE',
                'SPECIAL_RESERVE',
                'REGULATORY_CAPITAL_RESERVE',
                'CONTINGENCY_RESERVE',
                'CAPITAL_REDEMPTION_RESERVE',
                'REVALUATION_RESERVE',
                'OTHER_RESERVE'
            ]);
            
            // Source and destination accounts
            $table->string('source_account_number');
            $table->string('source_account_name');
            $table->string('destination_reserve_account_number');
            $table->string('destination_reserve_account_name');
            
            // Amount and period
            $table->decimal('amount', 20, 2);
            $table->string('currency', 3)->default('TZS');
            $table->integer('financial_year');
            $table->integer('financial_month')->nullable();
            $table->integer('financial_quarter')->nullable();
            
            // Transfer details
            $table->date('transfer_date');
            $table->text('narration');
            $table->text('reason_for_transfer')->nullable();
            
            // Percentage or fixed amount
            $table->enum('calculation_method', ['PERCENTAGE', 'FIXED_AMOUNT'])->default('FIXED_AMOUNT');
            $table->decimal('percentage_of_profit', 5, 2)->nullable(); // If percentage based
            $table->decimal('base_amount', 20, 2)->nullable(); // Amount used for percentage calculation
            
            // Approval workflow
            $table->enum('status', [
                'DRAFT',
                'PENDING_APPROVAL',
                'APPROVED',
                'POSTED',
                'REVERSED',
                'REJECTED'
            ])->default('DRAFT');
            
            $table->bigInteger('initiated_by')->nullable();
            $table->string('initiated_by_name')->nullable();
            $table->timestamp('initiated_at')->nullable();
            
            $table->bigInteger('approved_by')->nullable();
            $table->string('approved_by_name')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            $table->bigInteger('posted_by')->nullable();
            $table->string('posted_by_name')->nullable();
            $table->timestamp('posted_at')->nullable();
            
            $table->bigInteger('reversed_by')->nullable();
            $table->string('reversed_by_name')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            
            $table->bigInteger('rejected_by')->nullable();
            $table->string('rejected_by_name')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // GL posting references
            $table->string('gl_entry_reference')->nullable();
            $table->boolean('posted_to_gl')->default(false);
            $table->timestamp('gl_posting_date')->nullable();
            
            // Compliance and regulatory
            $table->boolean('is_statutory_requirement')->default(false);
            $table->string('regulatory_reference')->nullable();
            $table->decimal('minimum_required_amount', 20, 2)->nullable();
            $table->boolean('meets_regulatory_requirement')->default(true);
            
            // Audit trail
            $table->json('metadata')->nullable();
            $table->string('session_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            // Soft deletes and timestamps
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('transfer_reference');
            $table->index('source_account_number');
            $table->index('destination_reserve_account_number');
            $table->index('financial_year');
            $table->index('status');
            $table->index('transfer_date');
            $table->index(['financial_year', 'transfer_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_to_reserves');
    }
};