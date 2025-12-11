<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for transactions table
 * 
 * Combined from these migrations:
 * - 2024_03_21_000002_create_transactions_table.php
 * - 2024_12_19_000000_add_lookup_fields_to_transactions_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('transaction_uuid');
            $table->string('client_number')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->decimal('amount', 20, 6);
            $table->string('currency', 3)->default('TZS');
            $table->string('type');
            $table->string('transaction_category')->nullable();
            $table->string('transaction_subcategory')->nullable();
            $table->string('source')->nullable();
            $table->string('service_name')->nullable();
            $table->text('raw_payload')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->string('channel_id')->nullable();
            $table->string('sp_code')->nullable();
            $table->string('gateway_ref')->nullable();
            $table->string('biller_receipt')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('channel_code')->nullable();
            $table->string('payer_name')->nullable();
            $table->string('payer_phone')->nullable();
            $table->string('payer_email')->nullable();
            $table->json('extra_fields')->nullable();
            $table->string('narration')->nullable();
            $table->text('description')->nullable();
            $table->string('reference');
            $table->string('external_reference')->nullable();
            $table->string('correlation_id')->nullable();
            $table->string('status')->default('PENDING');
            $table->string('reconciliation_status')->default('UNRECONCILED');
            $table->decimal('balance_before', 20, 6)->nullable();
            $table->decimal('balance_after', 20, 6)->nullable();
            $table->decimal('running_balance', 20, 6)->nullable();
            $table->string('external_system')->nullable();
            $table->string('external_system_version')->nullable();
            $table->string('external_transaction_id')->nullable();
            $table->json('external_request_payload')->nullable();
            $table->json('external_response_payload')->nullable();
            $table->string('external_status_code')->nullable();
            $table->string('external_status_message')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->integer('max_retries')->default(3);
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->text('error_details')->nullable();
            $table->json('error_context')->nullable();
            $table->string('failure_reason')->nullable();
            $table->string('reversal_reason')->nullable();
            $table->text('reversal_notes')->nullable();
            $table->string('initiated_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('processed_by')->nullable();
            $table->string('reversed_by')->nullable();
            $table->string('client_ip')->nullable(); // Original type: inet
            $table->string('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->boolean('is_system_generated')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->string('approval_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable();
            $table->string('batch_id')->nullable();
            $table->string('process_id')->nullable();
            $table->integer('processing_time_ms')->nullable();
            $table->string('queue_name')->nullable();
            $table->string('job_id')->nullable();
            $table->string('regulatory_category')->nullable();
            $table->string('reporting_period')->nullable();
            $table->boolean('is_suspicious')->default(false);
            $table->string('risk_level')->nullable();
            $table->softDeletes();
            $table->string('lookup_reference')->nullable();
            $table->string('lookup_status')->nullable();
            $table->string('lookup_error_code')->nullable();
            $table->text('lookup_error_message')->nullable();
            $table->json('lookup_request_payload')->nullable();
            $table->json('lookup_response_payload')->nullable();
            $table->timestamp('lookup_performed_at')->nullable();
            $table->integer('lookup_processing_time_ms')->nullable();
            $table->string('lookup_account_name')->nullable();
            $table->string('lookup_account_type')->nullable();
            $table->string('lookup_bank_name')->nullable();
            $table->string('lookup_bank_code')->nullable();
            $table->string('lookup_wallet_provider')->nullable();
            $table->string('lookup_phone_number')->nullable();
            $table->string('lookup_account_number')->nullable();
            $table->string('lookup_identity_type')->nullable();
            $table->string('lookup_identity_value')->nullable();
            $table->boolean('lookup_validated')->default(false);
            $table->string('lookup_validation_status')->nullable();
            $table->text('lookup_validation_notes')->nullable();
            $table->index(['account_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['external_system', 'external_transaction_id']);
            $table->index(['transaction_category', 'transaction_subcategory']);
            $table->index(['initiated_at', 'status']);
            $table->index(['batch_id', 'status']);
            $table->index(['process_id', 'status']);
            $table->index(['correlation_id', 'status']);
            $table->index(['reconciliation_status', 'created_at']);
            $table->index(['is_manual', 'status']);
            $table->index(['requires_approval', 'is_approved']);
            $table->index(['transaction_uuid']);
            $table->index(['reference']);
            $table->index(['external_reference']);
            $table->index(['correlation_id']);
            $table->index(['status']);
            $table->index(['reconciliation_status']);
            $table->index(['external_transaction_id']);
            $table->index(['batch_id']);
            $table->index(['process_id']);
            $table->index(['lookup_reference']);
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};