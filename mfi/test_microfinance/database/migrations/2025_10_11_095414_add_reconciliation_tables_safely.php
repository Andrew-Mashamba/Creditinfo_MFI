<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PRODUCTION SAFE: Only adds columns/tables if they don't exist
     *
     * @return void
     */
    public function up()
    {
        // 1. Add missing fields to analysis_sessions (serves as recon_session)
        if (Schema::hasTable('analysis_sessions')) {
            Schema::table('analysis_sessions', function (Blueprint $table) {
                if (!Schema::hasColumn('analysis_sessions', 'account_name')) {
                    $table->string('account_name')->nullable()->after('id');
                }
                if (!Schema::hasColumn('analysis_sessions', 'account_number')) {
                    $table->string('account_number')->nullable()->after('account_name');
                }
                if (!Schema::hasColumn('analysis_sessions', 'bank')) {
                    $table->string('bank')->nullable()->after('account_number');
                }
                if (!Schema::hasColumn('analysis_sessions', 'bank_account_id')) {
                    $table->unsignedBigInteger('bank_account_id')->nullable()->after('bank');
                }
                if (!Schema::hasColumn('analysis_sessions', 'mirror_account_number')) {
                    $table->string('mirror_account_number')->nullable()->after('bank_account_id')
                        ->comment('Internal GL account number that mirrors this bank account');
                }
                if (!Schema::hasColumn('analysis_sessions', 'statement_period')) {
                    $table->string('statement_period')->nullable()->after('mirror_account_number');
                }
                if (!Schema::hasColumn('analysis_sessions', 'status')) {
                    $table->enum('status', ['pending', 'processing', 'completed', 'failed'])
                        ->default('pending')->after('statement_period');
                }
                if (!Schema::hasColumn('analysis_sessions', 'total_transactions')) {
                    $table->integer('total_transactions')->default(0)->after('status');
                }
                if (!Schema::hasColumn('analysis_sessions', 'matched_count')) {
                    $table->integer('matched_count')->default(0)->after('total_transactions');
                }
                if (!Schema::hasColumn('analysis_sessions', 'unmatched_count')) {
                    $table->integer('unmatched_count')->default(0)->after('matched_count');
                }
                if (!Schema::hasColumn('analysis_sessions', 'reconciliation_summary')) {
                    $table->json('reconciliation_summary')->nullable()->after('unmatched_count');
                }
                if (!Schema::hasColumn('analysis_sessions', 'file_path')) {
                    $table->string('file_path')->nullable()->after('reconciliation_summary');
                }
                if (!Schema::hasColumn('analysis_sessions', 'uploaded_by')) {
                    $table->unsignedBigInteger('uploaded_by')->nullable()->after('file_path');
                }
                if (!Schema::hasColumn('analysis_sessions', 'reconciled_by')) {
                    $table->unsignedBigInteger('reconciled_by')->nullable()->after('uploaded_by');
                }
                if (!Schema::hasColumn('analysis_sessions', 'reconciled_at')) {
                    $table->timestamp('reconciled_at')->nullable()->after('reconciled_by');
                }
            });

            // Add foreign keys if they don't exist
            if (Schema::hasColumn('analysis_sessions', 'bank_account_id')) {
                $foreignKeys = \DB::select("
                    SELECT constraint_name
                    FROM information_schema.table_constraints
                    WHERE table_name = 'analysis_sessions'
                    AND constraint_type = 'FOREIGN KEY'
                    AND constraint_name = 'analysis_sessions_bank_account_id_foreign'
                ");

                if (empty($foreignKeys) && Schema::hasTable('bank_accounts')) {
                    Schema::table('analysis_sessions', function (Blueprint $table) {
                        $table->foreign('bank_account_id')
                            ->references('id')
                            ->on('bank_accounts')
                            ->onDelete('set null');
                    });
                }
            }

            // Add index on status for faster queries
            $indexes = \DB::select("
                SELECT indexname
                FROM pg_indexes
                WHERE tablename = 'analysis_sessions'
                AND indexname = 'analysis_sessions_status_index'
            ");

            if (empty($indexes)) {
                Schema::table('analysis_sessions', function (Blueprint $table) {
                    $table->index('status');
                });
            }
        }

        // 2. Ensure bank_transactions table has all needed fields (it already has most)
        if (Schema::hasTable('bank_transactions')) {
            Schema::table('bank_transactions', function (Blueprint $table) {
                // Add any additional fields that might be missing
                if (!Schema::hasColumn('bank_transactions', 'recon_match_type')) {
                    $table->enum('recon_match_type', ['auto', 'manual', 'partial', 'none'])
                        ->default('none')->after('reconciliation_status')
                        ->comment('Type of reconciliation match');
                }
                if (!Schema::hasColumn('bank_transactions', 'recon_match_score')) {
                    $table->decimal('recon_match_score', 5, 2)->nullable()->after('recon_match_type')
                        ->comment('Matching algorithm confidence score 0-100');
                }
            });
        }

        // 3. Create recon_matching_transactions table
        if (!Schema::hasTable('recon_matching_transactions')) {
            Schema::create('recon_matching_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('session_id');
                $table->unsignedBigInteger('bank_transaction_id');
                $table->unsignedBigInteger('cashbook_transaction_id')->comment('Transaction from transactions table');

                // Matching details
                $table->enum('match_type', ['exact', 'partial', 'manual'])->default('exact');
                $table->decimal('match_confidence', 5, 2)->default(100.00)->comment('Match confidence percentage');
                $table->json('match_criteria')->nullable()->comment('JSON of criteria used for matching');

                // Amount reconciliation
                $table->decimal('bank_amount', 20, 2);
                $table->decimal('cashbook_amount', 20, 2);
                $table->decimal('variance', 20, 2)->default(0.00)->comment('Difference between bank and cashbook');

                // Dates
                $table->date('bank_date');
                $table->date('cashbook_date');
                $table->integer('date_variance_days')->default(0);

                // Status and notes
                $table->enum('status', ['matched', 'pending_approval', 'approved', 'rejected'])->default('matched');
                $table->text('notes')->nullable();
                $table->text('variance_explanation')->nullable();

                // Audit fields
                $table->unsignedBigInteger('matched_by')->nullable();
                $table->timestamp('matched_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();

                $table->timestamps();
                $table->unsignedBigInteger('branch_id')->nullable();

                // Foreign keys
                $table->foreign('session_id')->references('id')->on('analysis_sessions')->onDelete('cascade');
                $table->foreign('bank_transaction_id')->references('id')->on('bank_transactions')->onDelete('cascade');
                $table->foreign('cashbook_transaction_id')->references('id')->on('transactions')->onDelete('cascade');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('restrict');

                // Indexes for performance
                $table->index(['session_id', 'status']);
                $table->index(['bank_transaction_id']);
                $table->index(['cashbook_transaction_id']);
                $table->index(['match_type', 'status']);

                // Unique constraint: one bank transaction can only match one cashbook transaction per session
                $table->unique(['session_id', 'bank_transaction_id'], 'unique_bank_transaction_per_session');
            });
        }

        // 4. Create recon_non_matching_transactions table
        if (!Schema::hasTable('recon_non_matching_transactions')) {
            Schema::create('recon_non_matching_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('session_id');

                // Transaction source: 'bank' or 'cashbook'
                $table->enum('source', ['bank', 'cashbook']);
                $table->unsignedBigInteger('transaction_id')->comment('ID from bank_transactions or transactions table');

                // Transaction details (denormalized for quick access)
                $table->date('transaction_date');
                $table->string('reference_number')->nullable();
                $table->text('narration')->nullable();
                $table->decimal('debit_amount', 20, 2)->default(0.00);
                $table->decimal('credit_amount', 20, 2)->default(0.00);
                $table->decimal('amount', 20, 2)->comment('Absolute amount');

                // Analysis fields
                $table->enum('non_match_reason', [
                    'not_in_cashbook',
                    'not_in_bank',
                    'amount_mismatch',
                    'date_mismatch',
                    'missing_reference',
                    'duplicate',
                    'timing_difference',
                    'other'
                ])->nullable();
                $table->text('reason_details')->nullable();

                // Resolution tracking
                $table->enum('resolution_status', ['pending', 'investigating', 'resolved', 'accepted'])->default('pending');
                $table->text('resolution_notes')->nullable();
                $table->date('expected_resolution_date')->nullable();

                // Follow-up actions
                $table->boolean('requires_action')->default(true);
                $table->enum('action_type', ['investigate', 'adjust', 'wait', 'accept', 'none'])->nullable();
                $table->text('action_notes')->nullable();

                // Assigned for resolution
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->timestamp('assigned_at')->nullable();

                // Resolution
                $table->unsignedBigInteger('resolved_by')->nullable();
                $table->timestamp('resolved_at')->nullable();

                $table->timestamps();
                $table->unsignedBigInteger('branch_id')->nullable();

                // Foreign keys
                $table->foreign('session_id')->references('id')->on('analysis_sessions')->onDelete('cascade');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('restrict');

                // Indexes for performance
                $table->index(['session_id', 'source']);
                $table->index(['session_id', 'resolution_status']);
                $table->index(['source', 'transaction_id']);
                $table->index(['non_match_reason']);
                $table->index(['requires_action', 'resolution_status']);
                $table->index(['assigned_to', 'resolution_status']);
            });
        }

        // 5. Create recon_audit_log table for tracking all reconciliation actions
        if (!Schema::hasTable('recon_audit_log')) {
            Schema::create('recon_audit_log', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('session_id');
                $table->string('action_type')->comment('upload, match, unmatch, approve, resolve, etc.');
                $table->string('entity_type')->comment('session, transaction, match, etc.');
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('performed_by');
                $table->timestamp('performed_at');
                $table->string('ip_address')->nullable();
                $table->timestamps();

                // Foreign keys
                $table->foreign('session_id')->references('id')->on('analysis_sessions')->onDelete('cascade');

                // Indexes
                $table->index(['session_id', 'performed_at']);
                $table->index(['action_type']);
                $table->index(['entity_type', 'entity_id']);
                $table->index(['performed_by']);
            });
        }
    }

    /**
     * Reverse the migrations.
     * PRODUCTION SAFE: Only removes what was added by this migration
     *
     * @return void
     */
    public function down()
    {
        // Drop new tables (only if they exist and have no dependencies)
        Schema::dropIfExists('recon_audit_log');
        Schema::dropIfExists('recon_non_matching_transactions');
        Schema::dropIfExists('recon_matching_transactions');

        // Note: We do NOT remove columns from existing tables in production
        // as this could break existing functionality and lose data
        // If needed, those can be removed manually after confirming safety
    }
};
