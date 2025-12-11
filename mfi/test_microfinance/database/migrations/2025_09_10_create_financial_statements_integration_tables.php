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
        // Financial Reporting Periods
        if (!Schema::hasTable('financial_periods')) {
            Schema::create('financial_periods', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month')->nullable();
            $table->integer('quarter')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('period_type', ['annual', 'quarterly', 'monthly']);
            $table->enum('status', ['draft', 'in_progress', 'closed', 'published'])->default('draft');
            $table->boolean('is_audited')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->bigInteger('closed_by')->nullable();
            $table->timestamps();
            
            $table->unique(['year', 'period_type', 'quarter', 'month']);
            $table->index(['year', 'status']);
        });
        }

        // Financial Statement Snapshots
        if (!Schema::hasTable('financial_statement_snapshots')) {
            Schema::create('financial_statement_snapshots', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('financial_period_id');
            $table->enum('statement_type', [
                'balance_sheet',
                'income_statement', 
                'cash_flow',
                'equity_changes',
                'trial_balance'
            ]);
            $table->json('data'); // Stores complete statement data
            $table->string('version')->default('1.0');
            $table->enum('status', ['draft', 'reviewed', 'approved', 'published'])->default('draft');
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('financial_period_id')->references('id')->on('financial_periods');
            $table->index(['financial_period_id', 'statement_type']);
        });
        }

        // Financial Statement Line Items - for detailed tracking
        if (!Schema::hasTable('financial_statement_items')) {
            Schema::create('financial_statement_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('financial_period_id');
            $table->string('account_number', 50);
            $table->string('account_name', 255);
            $table->enum('statement_type', [
                'balance_sheet',
                'income_statement',
                'cash_flow', 
                'equity_changes'
            ]);
            $table->enum('classification', [
                'current_asset',
                'non_current_asset',
                'current_liability',
                'non_current_liability',
                'equity',
                'revenue',
                'expense',
                'operating_activity',
                'investing_activity',
                'financing_activity'
            ]);
            $table->decimal('amount', 20, 2);
            $table->decimal('previous_period_amount', 20, 2)->nullable();
            $table->decimal('variance_amount', 20, 2)->nullable();
            $table->decimal('variance_percentage', 10, 2)->nullable();
            $table->integer('display_order')->default(0);
            $table->integer('indent_level')->default(0);
            $table->boolean('is_subtotal')->default(false);
            $table->boolean('is_total')->default(false);
            $table->json('metadata')->nullable(); // Additional data like notes, references
            $table->timestamps();
            
            $table->foreign('financial_period_id')->references('id')->on('financial_periods');
            $table->index(['financial_period_id', 'statement_type', 'classification']);
            $table->index(['account_number', 'financial_period_id']);
        });
        }

        // Financial Ratios & KPIs
        if (!Schema::hasTable('financial_ratios')) {
            Schema::create('financial_ratios', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('financial_period_id');
            $table->string('ratio_category'); // liquidity, solvency, profitability, efficiency
            $table->string('ratio_name');
            $table->decimal('value', 20, 4);
            $table->string('formula')->nullable();
            $table->decimal('benchmark_value', 20, 4)->nullable();
            $table->enum('trend', ['improving', 'stable', 'declining'])->nullable();
            $table->text('interpretation')->nullable();
            $table->timestamps();
            
            $table->foreign('financial_period_id')->references('id')->on('financial_periods');
            $table->index(['financial_period_id', 'ratio_category']);
        });
        }

        // Notes to Financial Statements
        if (!Schema::hasTable('financial_statement_notes')) {
            Schema::create('financial_statement_notes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('financial_period_id');
            $table->integer('note_number');
            $table->string('note_title');
            $table->enum('note_type', [
                'accounting_policy',
                'account_breakdown',
                'disclosure',
                'contingency',
                'subsequent_event',
                'related_party',
                'segment_info'
            ]);
            $table->text('content');
            $table->json('related_accounts')->nullable(); // Array of account numbers
            $table->json('breakdown_data')->nullable(); // Detailed breakdown if applicable
            $table->boolean('is_mandatory')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->foreign('financial_period_id')->references('id')->on('financial_periods');
            $table->unique(['financial_period_id', 'note_number']);
        });
        }

        // Statement Relationships & Cross-References
        if (!Schema::hasTable('statement_relationships')) {
            Schema::create('statement_relationships', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('financial_period_id');
            $table->enum('source_statement', [
                'income_statement',
                'balance_sheet',
                'cash_flow',
                'equity_changes'
            ]);
            $table->string('source_item'); // e.g., 'net_income'
            $table->enum('target_statement', [
                'income_statement',
                'balance_sheet', 
                'cash_flow',
                'equity_changes'
            ]);
            $table->string('target_item'); // e.g., 'retained_earnings'
            $table->enum('relationship_type', [
                'flows_to',        // Income Statement -> Balance Sheet
                'derives_from',    // Balance Sheet -> Cash Flow
                'reconciles_with', // Cash Flow -> Balance Sheet
                'equals',          // Direct equality
                'comprises_of'     // Breakdown relationship
            ]);
            $table->text('description')->nullable();
            $table->decimal('amount', 20, 2)->nullable();
            $table->timestamps();
            
            $table->foreign('financial_period_id')->references('id')->on('financial_periods');
            $table->index(['financial_period_id', 'source_statement', 'target_statement']);
        });
        }

        // Consolidation Entries for Group Reporting
        if (!Schema::hasTable('consolidation_entries')) {
            Schema::create('consolidation_entries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('financial_period_id');
            $table->string('entity_code')->nullable(); // For multi-entity consolidation
            $table->string('entity_name')->nullable();
            $table->enum('entry_type', [
                'elimination',
                'adjustment',
                'reclassification',
                'translation'
            ]);
            $table->string('debit_account', 50);
            $table->string('credit_account', 50);
            $table->decimal('amount', 20, 2);
            $table->text('description');
            $table->string('reference_number')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('financial_period_id')->references('id')->on('financial_periods');
            $table->index(['financial_period_id', 'entry_type']);
        });
        }

        // Audit Trail for Financial Statements
        if (!Schema::hasTable('financial_statement_audit_trail')) {
            Schema::create('financial_statement_audit_trail', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('financial_period_id');
            $table->string('statement_type');
            $table->string('action'); // created, modified, approved, published
            $table->json('changes')->nullable(); // What was changed
            $table->bigInteger('user_id');
            $table->string('user_name');
            $table->text('comments')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->foreign('financial_period_id')->references('id')->on('financial_periods');
            $table->index(['financial_period_id', 'statement_type']);
        });
        }

        // Financial Statement Templates
        if (!Schema::hasTable('financial_statement_templates')) {
            Schema::create('financial_statement_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');
            $table->enum('statement_type', [
                'balance_sheet',
                'income_statement',
                'cash_flow',
                'equity_changes',
                'notes'
            ]);
            $table->json('structure'); // JSON structure defining the template
            $table->json('account_mappings'); // Maps accounts to line items
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('compliance_standard')->nullable(); // IFRS, GAAP, etc.
            $table->timestamps();
            
            $table->index(['statement_type', 'is_active']);
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_statement_templates');
        Schema::dropIfExists('financial_statement_audit_trail');
        Schema::dropIfExists('consolidation_entries');
        Schema::dropIfExists('statement_relationships');
        Schema::dropIfExists('financial_statement_notes');
        Schema::dropIfExists('financial_ratios');
        Schema::dropIfExists('financial_statement_items');
        Schema::dropIfExists('financial_statement_snapshots');
        Schema::dropIfExists('financial_periods');
    }
};