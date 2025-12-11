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
        // Main loan write-offs table
        Schema::create('loan_write_offs', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id')->index();
            $table->string('client_number')->nullable();
            $table->date('write_off_date');
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_amount', 15, 2)->default(0);
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('provision_utilized', 15, 2)->default(0);
            $table->decimal('direct_writeoff_amount', 15, 2)->default(0);
            $table->text('reason');
            $table->string('writeoff_type')->default('full'); // 'full', 'partial'
            $table->string('status')->default('pending_approval');
            $table->unsignedBigInteger('initiated_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('board_approved_by')->nullable();
            $table->datetime('approved_date')->nullable();
            $table->datetime('board_approval_date')->nullable();
            $table->decimal('board_approval_threshold', 15, 2)->default(1000000); // TZS 1M default
            $table->boolean('requires_board_approval')->default(false);
            $table->json('approval_workflow')->nullable();
            $table->json('collection_efforts')->nullable(); // Documentation of collection attempts
            $table->text('member_notification_sent')->nullable();
            $table->json('audit_trail')->nullable();
            $table->string('recovery_status')->default('not_recovered'); // 'not_recovered', 'partial', 'full'
            $table->decimal('recovered_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('initiated_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('board_approved_by')->references('id')->on('users');
            $table->index(['write_off_date', 'status']);
            $table->index(['loan_id', 'writeoff_type']);
        });

        // Write-off recoveries tracking table
        Schema::create('loan_writeoff_recoveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('writeoff_id');
            $table->string('loan_id')->index();
            $table->string('client_number')->nullable();
            $table->date('recovery_date');
            $table->decimal('recovery_amount', 15, 2);
            $table->string('recovery_method'); // 'cash', 'collateral_sale', 'legal_settlement', 'other'
            $table->text('recovery_description');
            $table->string('recovery_source')->nullable(); // 'client', 'guarantor', 'collateral', 'legal'
            $table->json('recovery_details')->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->datetime('approved_date')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'approved', 'reversed'
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('writeoff_id')->references('id')->on('loan_write_offs');
            $table->foreign('recorded_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->index(['recovery_date', 'status']);
        });

        // Collection efforts documentation table
        Schema::create('loan_collection_efforts', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id')->index();
            $table->string('client_number')->nullable();
            $table->date('effort_date');
            $table->string('effort_type'); // 'call', 'sms', 'email', 'visit', 'letter', 'legal_notice', 'other'
            $table->text('effort_description');
            $table->string('outcome'); // 'promise_to_pay', 'dispute', 'no_response', 'payment_made', 'unreachable', 'other'
            $table->date('promised_payment_date')->nullable();
            $table->decimal('promised_amount', 15, 2)->nullable();
            $table->text('client_response')->nullable();
            $table->unsignedBigInteger('staff_id');
            $table->json('contact_details')->nullable();
            $table->json('supporting_documents')->nullable(); // File paths/references
            $table->decimal('cost_incurred', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('staff_id')->references('id')->on('users');
            $table->index(['loan_id', 'effort_date']);
            $table->index(['effort_type', 'outcome']);
        });

        // Write-off approval workflow table
        Schema::create('writeoff_approval_workflow', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('writeoff_id');
            $table->string('approval_level'); // 'manager', 'director', 'board', 'external_auditor'
            $table->string('approver_role');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected', 'escalated'
            $table->datetime('assigned_date');
            $table->datetime('action_date')->nullable();
            $table->text('comments')->nullable();
            $table->json('conditions')->nullable(); // Any conditions set by approver
            $table->boolean('is_final_approval')->default(false);
            $table->string('next_approval_level')->nullable();
            $table->timestamps();
            
            $table->foreign('writeoff_id')->references('id')->on('loan_write_offs');
            $table->foreign('approver_id')->references('id')->on('users');
            $table->index(['writeoff_id', 'approval_level']);
        });

        // Write-off trends and analytics table
        Schema::create('writeoff_analytics', function (Blueprint $table) {
            $table->id();
            $table->date('analysis_date');
            $table->string('period_type'); // 'daily', 'weekly', 'monthly', 'quarterly', 'yearly'
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('total_writeoffs_count');
            $table->decimal('total_writeoffs_amount', 15, 2);
            $table->decimal('total_recoveries_amount', 15, 2)->default(0);
            $table->decimal('net_writeoffs_amount', 15, 2);
            $table->decimal('recovery_rate', 5, 2)->default(0); // Percentage
            $table->json('by_loan_product')->nullable();
            $table->json('by_client_segment')->nullable();
            $table->json('by_loan_officer')->nullable();
            $table->json('by_branch')->nullable();
            $table->json('trends_data')->nullable();
            $table->decimal('provision_coverage_ratio', 5, 2)->default(0);
            $table->text('analysis_notes')->nullable();
            $table->timestamps();
            
            $table->unique(['analysis_date', 'period_type']);
            $table->index(['period_start', 'period_end']);
        });

        // Member communication log for writeoffs
        Schema::create('writeoff_member_communications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('writeoff_id');
            $table->string('loan_id');
            $table->string('client_number');
            $table->string('communication_type'); // 'sms', 'email', 'letter', 'call', 'meeting'
            $table->text('message_content');
            $table->datetime('sent_date');
            $table->string('delivery_status')->default('pending'); // 'pending', 'delivered', 'failed', 'read'
            $table->text('delivery_details')->nullable();
            $table->string('template_used')->nullable();
            $table->json('personalization_data')->nullable();
            $table->unsignedBigInteger('sent_by');
            $table->boolean('member_acknowledged')->default(false);
            $table->datetime('acknowledgment_date')->nullable();
            $table->text('member_response')->nullable();
            $table->timestamps();
            
            $table->foreign('writeoff_id')->references('id')->on('loan_write_offs');
            $table->foreign('sent_by')->references('id')->on('users');
            $table->index(['writeoff_id', 'communication_type']);
            $table->index(['client_number', 'sent_date']);
        });

        // Default thresholds are now stored in the institutions table
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('writeoff_member_communications');
        Schema::dropIfExists('writeoff_analytics');
        Schema::dropIfExists('writeoff_approval_workflow');
        Schema::dropIfExists('loan_collection_efforts');
        Schema::dropIfExists('loan_writeoff_recoveries');
        Schema::dropIfExists('loan_write_offs');
        
        // Thresholds are now in institutions table, no cleanup needed
    }
};