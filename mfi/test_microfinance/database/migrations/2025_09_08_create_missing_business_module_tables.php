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
        // Trade Receivables table (if not exists)
        if (!Schema::hasTable('trade_receivables')) {
            Schema::create('trade_receivables', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->string('customer_name');
                $table->string('customer_id')->nullable();
                $table->decimal('amount', 20, 2);
                $table->decimal('paid_amount', 20, 2)->default(0);
                $table->decimal('balance', 20, 2);
                $table->date('invoice_date');
                $table->date('due_date');
                $table->integer('aging_days')->default(0);
                $table->enum('aging_category', ['current', '30_days', '60_days', '90_days', 'over_90_days'])->default('current');
                $table->decimal('provision_amount', 20, 2)->default(0);
                $table->decimal('provision_percentage', 10, 2)->default(0);
                $table->string('account_number')->nullable();
                $table->text('description')->nullable();
                $table->enum('status', ['pending', 'partial', 'paid', 'overdue', 'written_off'])->default('pending');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                
                $table->index('customer_id');
                $table->index('due_date');
                $table->index('status');
                $table->index('account_number');
            });
        }

        // PPE Assets table (if not exists)
        if (!Schema::hasTable('ppe_assets')) {
            Schema::create('ppe_assets', function (Blueprint $table) {
                $table->id();
                $table->string('asset_code')->unique();
                $table->string('asset_name');
                $table->enum('asset_category', ['land', 'buildings', 'machinery', 'vehicles', 'furniture', 'equipment', 'computers', 'other']);
                $table->decimal('cost', 20, 2);
                $table->date('acquisition_date');
                $table->integer('useful_life_years');
                $table->decimal('salvage_value', 20, 2)->default(0);
                $table->enum('depreciation_method', ['straight_line', 'declining_balance', 'sum_of_years'])->default('straight_line');
                $table->decimal('accumulated_depreciation', 20, 2)->default(0);
                $table->decimal('net_book_value', 20, 2);
                $table->string('location')->nullable();
                $table->string('responsible_person')->nullable();
                $table->string('account_number')->nullable();
                $table->enum('status', ['active', 'disposed', 'written_off', 'under_maintenance'])->default('active');
                $table->date('disposal_date')->nullable();
                $table->decimal('disposal_amount', 20, 2)->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                
                $table->index('asset_category');
                $table->index('status');
                $table->index('account_number');
            });
        }

        // Prepaid Expenses table (if not exists)
        if (!Schema::hasTable('prepaid_expenses')) {
            Schema::create('prepaid_expenses', function (Blueprint $table) {
                $table->id();
                $table->string('expense_code')->unique();
                $table->string('expense_name');
                $table->enum('expense_type', ['insurance', 'rent', 'subscriptions', 'licenses', 'maintenance', 'other']);
                $table->decimal('total_amount', 20, 2);
                $table->decimal('monthly_expense', 20, 2);
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('total_months');
                $table->integer('months_expensed')->default(0);
                $table->decimal('amount_expensed', 20, 2)->default(0);
                $table->decimal('remaining_balance', 20, 2);
                $table->string('vendor_name')->nullable();
                $table->string('reference_number')->nullable();
                $table->string('account_number')->nullable();
                $table->text('description')->nullable();
                $table->enum('status', ['active', 'fully_expensed', 'cancelled'])->default('active');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                
                $table->index('expense_type');
                $table->index('status');
                $table->index('account_number');
            });
        }

        // Trade Payables table (if not exists)
        if (!Schema::hasTable('trade_payables')) {
            Schema::create('trade_payables', function (Blueprint $table) {
                $table->id();
                $table->string('bill_number')->unique();
                $table->string('vendor_name');
                $table->string('vendor_id')->nullable();
                $table->decimal('amount', 20, 2);
                $table->decimal('paid_amount', 20, 2)->default(0);
                $table->decimal('balance', 20, 2);
                $table->date('bill_date');
                $table->date('due_date');
                $table->integer('payment_terms')->default(30); // Days
                $table->string('purchase_order_number')->nullable();
                $table->string('account_number')->nullable();
                $table->text('description')->nullable();
                $table->enum('status', ['pending', 'partial', 'paid', 'overdue', 'disputed'])->default('pending');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                
                $table->index('vendor_id');
                $table->index('due_date');
                $table->index('status');
                $table->index('account_number');
            });
        }

        // Creditors table (if not exists) - for other types of creditors beyond trade
        if (!Schema::hasTable('creditors')) {
            Schema::create('creditors', function (Blueprint $table) {
                $table->id();
                $table->string('creditor_code')->unique();
                $table->string('creditor_name');
                $table->enum('creditor_type', ['loan', 'lease', 'mortgage', 'bonds', 'other']);
                $table->decimal('principal_amount', 20, 2);
                $table->decimal('interest_rate', 10, 4)->nullable();
                $table->decimal('outstanding_amount', 20, 2);
                $table->date('start_date');
                $table->date('maturity_date')->nullable();
                $table->string('payment_frequency')->nullable(); // monthly, quarterly, etc
                $table->decimal('payment_amount', 20, 2)->nullable();
                $table->string('collateral')->nullable();
                $table->string('account_number')->nullable();
                $table->text('terms_conditions')->nullable();
                $table->enum('status', ['active', 'paid_off', 'defaulted', 'restructured'])->default('active');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                
                $table->index('creditor_type');
                $table->index('status');
                $table->index('account_number');
            });
        }

        // Other Income table (if not exists)
        if (!Schema::hasTable('other_income')) {
            Schema::create('other_income', function (Blueprint $table) {
                $table->id();
                $table->string('income_code')->unique();
                $table->date('income_date');
                $table->string('income_category');
                $table->string('income_source');
                $table->decimal('amount', 20, 2);
                $table->decimal('tax_amount', 20, 2)->default(0);
                $table->decimal('net_amount', 20, 2);
                $table->string('currency', 3)->default('TZS');
                $table->string('payment_method');
                $table->string('reference_number')->nullable();
                $table->string('receipt_number')->nullable();
                $table->string('received_from')->nullable();
                $table->unsignedBigInteger('bank_account_id')->nullable();
                $table->unsignedBigInteger('income_account_id')->nullable();
                $table->string('account_number')->nullable();
                $table->text('description')->nullable();
                $table->string('receipt_attachment')->nullable();
                $table->json('supporting_documents')->nullable();
                $table->boolean('recurring')->default(false);
                $table->string('recurring_frequency')->nullable();
                $table->date('recurring_end_date')->nullable();
                $table->enum('status', ['received', 'pending', 'cancelled'])->default('received');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                
                $table->index('income_category');
                $table->index('income_date');
                $table->index('status');
                $table->index('account_number');
            });
        }

        // Financial Insurance table (if not exists)
        if (!Schema::hasTable('financial_insurance')) {
            Schema::create('financial_insurance', function (Blueprint $table) {
                $table->id();
                $table->string('policy_number')->unique();
                $table->string('insurance_type');
                $table->string('insurer_name');
                $table->string('insurer_contact')->nullable();
                $table->string('coverage_type')->nullable();
                $table->decimal('coverage_amount', 20, 2);
                $table->decimal('premium_amount', 20, 2);
                $table->decimal('annual_premium', 20, 2)->nullable();
                $table->string('premium_frequency');
                $table->date('policy_start_date');
                $table->date('policy_end_date');
                $table->string('insured_entity');
                $table->string('insured_entity_id')->nullable();
                $table->string('beneficiary')->nullable();
                $table->decimal('deductible', 20, 2)->default(0);
                $table->decimal('copayment_percentage', 10, 2)->default(0);
                $table->string('policy_document')->nullable();
                $table->string('account_number')->nullable();
                $table->text('notes')->nullable();
                $table->enum('status', ['active', 'expired', 'cancelled', 'claimed'])->default('active');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                
                $table->index('insurance_type');
                $table->index('status');
                $table->index('policy_end_date');
                $table->index('account_number');
            });
        }

        // Insurance Claims table
        if (!Schema::hasTable('insurance_claims')) {
            Schema::create('insurance_claims', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('insurance_id');
                $table->string('claim_number')->unique();
                $table->date('claim_date');
                $table->decimal('claim_amount', 20, 2);
                $table->string('claim_reason');
                $table->enum('claim_status', ['pending', 'approved', 'rejected', 'paid', 'partially_paid'])->default('pending');
                $table->decimal('approved_amount', 20, 2)->nullable();
                $table->decimal('settlement_amount', 20, 2)->nullable();
                $table->date('settlement_date')->nullable();
                $table->string('account_number')->nullable();
                $table->text('notes')->nullable();
                $table->json('supporting_documents')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
                
                $table->foreign('insurance_id')->references('id')->on('financial_insurance');
                $table->index('claim_status');
                $table->index('claim_date');
                $table->index('account_number');
            });
        }

        // Collections from Receivables table
        if (!Schema::hasTable('receivable_collections')) {
            Schema::create('receivable_collections', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('receivable_id');
                $table->string('collection_number')->unique();
                $table->date('collection_date');
                $table->decimal('amount_collected', 20, 2);
                $table->string('payment_method');
                $table->string('reference_number')->nullable();
                $table->string('bank_account_id')->nullable();
                $table->string('account_number')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('collected_by')->nullable();
                $table->timestamps();
                
                $table->foreign('receivable_id')->references('id')->on('trade_receivables');
                $table->index('collection_date');
                $table->index('account_number');
            });
        }

        // Payments to Payables/Creditors table
        if (!Schema::hasTable('payable_payments')) {
            Schema::create('payable_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payable_id')->nullable();
                $table->unsignedBigInteger('creditor_id')->nullable();
                $table->string('payment_number')->unique();
                $table->date('payment_date');
                $table->decimal('amount_paid', 20, 2);
                $table->string('payment_method');
                $table->string('reference_number')->nullable();
                $table->string('bank_account_id')->nullable();
                $table->string('account_number')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
                
                $table->index('payment_date');
                $table->index('account_number');
            });
        }

        // PPE Transactions (purchases/disposals)
        if (!Schema::hasTable('ppe_transactions')) {
            Schema::create('ppe_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ppe_asset_id')->nullable();
                $table->enum('transaction_type', ['purchase', 'disposal', 'revaluation', 'impairment', 'depreciation']);
                $table->date('transaction_date');
                $table->decimal('amount', 20, 2);
                $table->string('reference_number')->nullable();
                $table->string('account_number')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                
                $table->foreign('ppe_asset_id')->references('id')->on('ppe_assets');
                $table->index('transaction_type');
                $table->index('transaction_date');
                $table->index('account_number');
            });
        }

        // Investment Transactions (purchases/redemptions)
        if (!Schema::hasTable('investment_transactions')) {
            Schema::create('investment_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('investment_id')->nullable();
                $table->enum('transaction_type', ['purchase', 'sale', 'dividend', 'interest', 'maturity', 'rollover']);
                $table->date('transaction_date');
                $table->decimal('amount', 20, 2);
                $table->decimal('units', 20, 4)->nullable();
                $table->decimal('price_per_unit', 20, 4)->nullable();
                $table->string('reference_number')->nullable();
                $table->string('account_number')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                
                $table->index('transaction_type');
                $table->index('transaction_date');
                $table->index('account_number');
            });
        }

        // Borrowings and Repayments
        if (!Schema::hasTable('borrowing_transactions')) {
            Schema::create('borrowing_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('creditor_id')->nullable();
                $table->enum('transaction_type', ['borrowing', 'repayment', 'interest_payment', 'refinancing']);
                $table->date('transaction_date');
                $table->decimal('principal_amount', 20, 2)->nullable();
                $table->decimal('interest_amount', 20, 2)->nullable();
                $table->decimal('total_amount', 20, 2);
                $table->string('reference_number')->nullable();
                $table->string('account_number')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                
                $table->foreign('creditor_id')->references('id')->on('creditors');
                $table->index('transaction_type');
                $table->index('transaction_date');
                $table->index('account_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowing_transactions');
        Schema::dropIfExists('investment_transactions');
        Schema::dropIfExists('ppe_transactions');
        Schema::dropIfExists('payable_payments');
        Schema::dropIfExists('receivable_collections');
        Schema::dropIfExists('insurance_claims');
        Schema::dropIfExists('financial_insurance');
        Schema::dropIfExists('other_income');
        Schema::dropIfExists('creditors');
        Schema::dropIfExists('trade_payables');
        Schema::dropIfExists('prepaid_expenses');
        Schema::dropIfExists('ppe_assets');
        Schema::dropIfExists('trade_receivables');
    }
};