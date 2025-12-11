<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add ECL fields to loan_loss_provisions table if they don't exist
        if (!Schema::hasColumn('loan_loss_provisions', 'ecl_stage')) {
            Schema::table('loan_loss_provisions', function (Blueprint $table) {
                $table->integer('ecl_stage')->nullable()->after('loan_classification');
                $table->decimal('pd_rate', 5, 4)->nullable()->after('provision_rate')->comment('Probability of Default');
                $table->decimal('lgd_rate', 5, 4)->nullable()->after('pd_rate')->comment('Loss Given Default');
                $table->decimal('ead_amount', 15, 2)->nullable()->after('lgd_rate')->comment('Exposure at Default');
                $table->string('calculation_method', 50)->nullable()->after('ead_amount');
                $table->string('economic_scenario', 20)->nullable()->after('calculation_method');
                $table->boolean('posted_to_gl')->default(false)->after('economic_scenario');
                $table->datetime('gl_posting_date')->nullable()->after('posted_to_gl');
                $table->unsignedBigInteger('journal_entry_id')->nullable()->after('gl_posting_date');
                $table->boolean('is_reversed')->default(false)->after('journal_entry_id');
                $table->datetime('reversal_date')->nullable()->after('is_reversed');
                $table->unsignedBigInteger('reversal_journal_id')->nullable()->after('reversal_date');
                
                $table->index(['provision_date', 'ecl_stage']);
                $table->index(['posted_to_gl', 'provision_date']);
            });
        }
        
        // Create provision summaries table
        if (!Schema::hasTable('provision_summaries')) {
            Schema::create('provision_summaries', function (Blueprint $table) {
                $table->id();
                $table->date('provision_date');
                $table->string('calculation_method', 50);
                $table->integer('total_loans');
                $table->decimal('total_exposure', 20, 2);
                $table->decimal('total_provisions', 20, 2);
                $table->decimal('provision_coverage', 5, 2);
                
                // Stage 1 statistics
                $table->integer('stage1_count')->default(0);
                $table->decimal('stage1_exposure', 20, 2)->default(0);
                $table->decimal('stage1_provision', 20, 2)->default(0);
                
                // Stage 2 statistics
                $table->integer('stage2_count')->default(0);
                $table->decimal('stage2_exposure', 20, 2)->default(0);
                $table->decimal('stage2_provision', 20, 2)->default(0);
                
                // Stage 3 statistics
                $table->integer('stage3_count')->default(0);
                $table->decimal('stage3_exposure', 20, 2)->default(0);
                $table->decimal('stage3_provision', 20, 2)->default(0);
                
                $table->string('economic_scenario', 20)->nullable();
                $table->boolean('forward_looking_applied')->default(false);
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->index('provision_date');
                $table->unique(['provision_date', 'calculation_method']);
            });
        }
        
        // Create loan restructures table for SICR tracking
        if (!Schema::hasTable('loan_restructures')) {
            Schema::create('loan_restructures', function (Blueprint $table) {
                $table->id();
                $table->string('loan_id')->index();
                $table->date('restructure_date');
                $table->string('restructure_type'); // 'term_extension', 'rate_reduction', 'payment_holiday', 'other'
                $table->text('restructure_reason');
                $table->decimal('original_amount', 15, 2);
                $table->decimal('restructured_amount', 15, 2);
                $table->integer('original_term_months');
                $table->integer('new_term_months');
                $table->decimal('original_rate', 5, 2);
                $table->decimal('new_rate', 5, 2);
                $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->datetime('approved_date')->nullable();
                $table->timestamps();
                
                $table->index(['loan_id', 'status']);
            });
        }
        
        // Create loan commitments table for undrawn amounts
        if (!Schema::hasTable('loan_commitments')) {
            Schema::create('loan_commitments', function (Blueprint $table) {
                $table->id();
                $table->string('loan_id')->index();
                $table->string('commitment_type'); // 'overdraft', 'credit_line', 'letter_of_credit'
                $table->decimal('approved_amount', 15, 2);
                $table->decimal('drawn_amount', 15, 2)->default(0);
                $table->decimal('available_amount', 15, 2);
                $table->date('commitment_date');
                $table->date('expiry_date');
                $table->string('status')->default('active'); // 'active', 'expired', 'cancelled'
                $table->timestamps();
                
                $table->index(['loan_id', 'status']);
            });
        }
        
        // Skip loan_provision_settings modifications as it's handled in separate migration
        
        // Create journal entries table if not exists
        if (!Schema::hasTable('journal_entries')) {
            Schema::create('journal_entries', function (Blueprint $table) {
                $table->id();
                $table->string('reference_no')->unique();
                $table->date('transaction_date');
                $table->text('description');
                $table->decimal('total_amount', 20, 2);
                $table->boolean('is_reversal')->default(false);
                $table->unsignedBigInteger('reversed_journal_id')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->index('transaction_date');
                $table->index('reference_no');
            });
        }
        
        // Create journal entry lines table if not exists
        if (!Schema::hasTable('journal_entry_lines')) {
            Schema::create('journal_entry_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('journal_entry_id');
                $table->string('account_code');
                $table->string('account_name');
                $table->decimal('debit', 20, 2)->default(0);
                $table->decimal('credit', 20, 2)->default(0);
                $table->string('description')->nullable();
                $table->timestamps();
                
                $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->onDelete('cascade');
                $table->index(['journal_entry_id', 'account_code']);
            });
        }
        
        // Skip inserting default provision settings as it's handled in separate migration
        
        // Add days_in_arrears_at_closure to loans table if not exists
        if (!Schema::hasColumn('loans', 'days_in_arrears_at_closure')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->integer('days_in_arrears_at_closure')->nullable()->after('closure_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns from loan_loss_provisions
        if (Schema::hasColumn('loan_loss_provisions', 'ecl_stage')) {
            Schema::table('loan_loss_provisions', function (Blueprint $table) {
                $table->dropColumn([
                    'ecl_stage', 'pd_rate', 'lgd_rate', 'ead_amount', 
                    'calculation_method', 'economic_scenario', 'posted_to_gl',
                    'gl_posting_date', 'journal_entry_id', 'is_reversed',
                    'reversal_date', 'reversal_journal_id'
                ]);
            });
        }
        
        // Drop new tables
        Schema::dropIfExists('provision_summaries');
        Schema::dropIfExists('loan_restructures');
        Schema::dropIfExists('loan_commitments');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        
        // Remove stage threshold fields and provision column from loan_provision_settings
        if (Schema::hasColumn('loan_provision_settings', 'stage1_days')) {
            Schema::table('loan_provision_settings', function (Blueprint $table) {
                $table->dropColumn(['stage1_days', 'stage2_days', 'stage3_days']);
            });
        }
        
        if (Schema::hasColumn('loan_provision_settings', 'provision')) {
            Schema::table('loan_provision_settings', function (Blueprint $table) {
                $table->dropColumn(['provision', 'rate', 'created_at', 'updated_at']);
            });
        }
        
        // Remove days_in_arrears_at_closure from loans
        if (Schema::hasColumn('loans', 'days_in_arrears_at_closure')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->dropColumn('days_in_arrears_at_closure');
            });
        }
    }
};