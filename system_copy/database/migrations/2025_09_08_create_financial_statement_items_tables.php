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
        // Statement of Financial Position Items
        Schema::create('statement_financial_position_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->enum('category', ['assets', 'liabilities', 'equity']);
            $table->enum('sub_category', [
                // Assets
                'current_assets', 'non_current_assets', 'property_plant_equipment', 
                'intangible_assets', 'investments', 'biological_assets',
                // Liabilities
                'current_liabilities', 'non_current_liabilities', 'provisions',
                'deferred_tax_liabilities', 'contingent_liabilities',
                // Equity
                'share_capital', 'retained_earnings', 'reserves', 'non_controlling_interests'
            ]);
            $table->string('account_number')->nullable();
            $table->decimal('amount', 20, 2)->default(0);
            $table->decimal('previous_year_amount', 20, 2)->default(0);
            $table->text('description')->nullable();
            $table->json('breakdown')->nullable(); // Detailed breakdown of the item
            $table->integer('display_order')->default(0);
            $table->boolean('is_calculated')->default(false); // For totals and subtotals
            $table->string('calculation_formula')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('reporting_date');
            $table->string('reporting_period'); // e.g., '2024-Q1', '2024-FY'
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['category', 'sub_category']);
            $table->index('reporting_date');
            $table->index('reporting_period');
            $table->index('account_number');
        });

        // Statement of Comprehensive Income Items
        Schema::create('statement_comprehensive_income_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->enum('category', ['revenue', 'expenses', 'other_comprehensive_income']);
            $table->enum('sub_category', [
                // Revenue
                'operating_revenue', 'interest_income', 'fee_income', 'other_income',
                // Expenses
                'operating_expenses', 'administrative_expenses', 'finance_costs',
                'depreciation_amortization', 'provision_expenses', 'tax_expenses',
                // Other Comprehensive Income
                'revaluation_gains', 'foreign_exchange_differences', 'fair_value_changes'
            ]);
            $table->string('account_number')->nullable();
            $table->decimal('current_period_amount', 20, 2)->default(0);
            $table->decimal('previous_period_amount', 20, 2)->default(0);
            $table->decimal('year_to_date_amount', 20, 2)->default(0);
            $table->decimal('budget_amount', 20, 2)->default(0);
            $table->decimal('variance_amount', 20, 2)->default(0);
            $table->decimal('variance_percentage', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->json('monthly_breakdown')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_calculated')->default(false);
            $table->string('calculation_formula')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->string('reporting_period');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['category', 'sub_category']);
            $table->index(['period_start_date', 'period_end_date']);
            $table->index('reporting_period');
            $table->index('account_number');
        });

        // Statement of Cash Flows Items
        Schema::create('statement_cash_flows_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->enum('category', ['operating', 'investing', 'financing']);
            $table->enum('sub_category', [
                // Operating Activities
                'cash_receipts_customers', 'cash_paid_suppliers', 'cash_paid_employees',
                'interest_received', 'interest_paid', 'taxes_paid', 'other_operating',
                // Investing Activities
                'purchase_ppe', 'proceeds_ppe_disposal', 'purchase_investments',
                'proceeds_investments', 'dividends_received', 'other_investing',
                // Financing Activities
                'proceeds_borrowings', 'repayment_borrowings', 'proceeds_share_issue',
                'dividends_paid', 'lease_payments', 'other_financing'
            ]);
            $table->string('account_number')->nullable();
            $table->decimal('amount', 20, 2)->default(0);
            $table->decimal('previous_period_amount', 20, 2)->default(0);
            $table->enum('cash_flow_type', ['inflow', 'outflow']);
            $table->text('description')->nullable();
            $table->json('transaction_details')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_calculated')->default(false);
            $table->string('calculation_formula')->nullable();
            $table->enum('method', ['direct', 'indirect'])->default('direct');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->string('reporting_period');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['category', 'sub_category']);
            $table->index(['period_start_date', 'period_end_date']);
            $table->index('reporting_period');
            $table->index('cash_flow_type');
            $table->index('account_number');
        });

        // Statement of Changes in Equity Items
        Schema::create('statement_changes_equity_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->enum('category', [
                'share_capital', 'share_premium', 'retained_earnings', 
                'revaluation_reserve', 'translation_reserve', 'other_reserves'
            ]);
            $table->enum('transaction_type', [
                'opening_balance', 'profit_loss', 'other_comprehensive_income',
                'dividends', 'share_issue', 'share_buyback', 'transfers', 'closing_balance'
            ]);
            $table->string('account_number')->nullable();
            $table->decimal('amount', 20, 2)->default(0);
            $table->decimal('share_capital_amount', 20, 2)->default(0);
            $table->decimal('share_premium_amount', 20, 2)->default(0);
            $table->decimal('retained_earnings_amount', 20, 2)->default(0);
            $table->decimal('reserves_amount', 20, 2)->default(0);
            $table->decimal('total_amount', 20, 2)->default(0);
            $table->text('description')->nullable();
            $table->json('details')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_calculated')->default(false);
            $table->string('calculation_formula')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('transaction_date');
            $table->string('reporting_period');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index('category');
            $table->index('transaction_type');
            $table->index('transaction_date');
            $table->index('reporting_period');
            $table->index('account_number');
        });

        // Notes to Financial Statements Items
        Schema::create('notes_to_accounts_items', function (Blueprint $table) {
            $table->id();
            $table->string('note_number'); // e.g., 'Note 1', 'Note 2.1'
            $table->string('note_title');
            $table->enum('note_category', [
                'accounting_policies', 'critical_judgments', 'key_estimates',
                'segment_information', 'revenue_breakdown', 'expense_breakdown',
                'asset_details', 'liability_details', 'equity_details',
                'related_party', 'contingencies', 'events_after_reporting',
                'financial_instruments', 'risk_management', 'other_disclosures'
            ]);
            $table->string('related_statement')->nullable(); // Which statement this note relates to
            $table->string('related_account_number')->nullable();
            $table->text('note_content');
            $table->json('supporting_data')->nullable(); // Tables, calculations, etc.
            $table->json('references')->nullable(); // References to other notes or standards
            $table->integer('display_order')->default(0);
            $table->enum('disclosure_required', ['mandatory', 'voluntary'])->default('voluntary');
            $table->string('accounting_standard')->nullable(); // e.g., 'IFRS 15', 'IAS 16'
            $table->enum('status', ['draft', 'reviewed', 'approved', 'published'])->default('draft');
            $table->string('reporting_period');
            $table->date('reporting_date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index('note_category');
            $table->index('related_statement');
            $table->index('reporting_period');
            $table->index('status');
            $table->index('related_account_number');
        });

        // Comparative Income and Expense Analysis
        Schema::create('comparative_income_expense_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->enum('type', ['income', 'expense']);
            $table->string('category');
            $table->string('account_number')->nullable();
            $table->decimal('current_month', 20, 2)->default(0);
            $table->decimal('previous_month', 20, 2)->default(0);
            $table->decimal('month_variance', 20, 2)->default(0);
            $table->decimal('month_variance_percentage', 10, 2)->default(0);
            $table->decimal('current_quarter', 20, 2)->default(0);
            $table->decimal('previous_quarter', 20, 2)->default(0);
            $table->decimal('quarter_variance', 20, 2)->default(0);
            $table->decimal('quarter_variance_percentage', 10, 2)->default(0);
            $table->decimal('current_year', 20, 2)->default(0);
            $table->decimal('previous_year', 20, 2)->default(0);
            $table->decimal('year_variance', 20, 2)->default(0);
            $table->decimal('year_variance_percentage', 10, 2)->default(0);
            $table->decimal('budget_amount', 20, 2)->default(0);
            $table->decimal('budget_variance', 20, 2)->default(0);
            $table->decimal('budget_variance_percentage', 10, 2)->default(0);
            $table->text('variance_explanation')->nullable();
            $table->json('monthly_trend')->nullable();
            $table->integer('display_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('reporting_period');
            $table->date('reporting_date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('category');
            $table->index('reporting_period');
            $table->index('account_number');
        });

        // Financial Statement Versions (for tracking changes and approvals)
        Schema::create('financial_statement_versions', function (Blueprint $table) {
            $table->id();
            $table->string('statement_type'); // 'position', 'income', 'cash_flow', 'equity', 'notes'
            $table->string('version_number');
            $table->string('reporting_period');
            $table->date('reporting_date');
            $table->enum('status', ['draft', 'under_review', 'approved', 'published', 'archived'])->default('draft');
            $table->json('statement_data'); // Complete snapshot of the statement
            $table->text('changes_summary')->nullable();
            $table->text('review_comments')->nullable();
            $table->text('approval_comments')->nullable();
            $table->unsignedBigInteger('prepared_by')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index(['statement_type', 'reporting_period']);
            $table->index('status');
            $table->index('version_number');
        });

        // Audit trail for all financial statement changes
        Schema::create('financial_statement_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->unsignedBigInteger('record_id');
            $table->string('action'); // 'create', 'update', 'delete', 'approve', 'publish'
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at');
            
            $table->index(['table_name', 'record_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_statement_audit_logs');
        Schema::dropIfExists('financial_statement_versions');
        Schema::dropIfExists('comparative_income_expense_items');
        Schema::dropIfExists('notes_to_accounts_items');
        Schema::dropIfExists('statement_changes_equity_items');
        Schema::dropIfExists('statement_cash_flows_items');
        Schema::dropIfExists('statement_comprehensive_income_items');
        Schema::dropIfExists('statement_financial_position_items');
    }
};