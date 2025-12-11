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
        // Add budget linking to general_ledger
        Schema::table('general_ledger', function (Blueprint $table) {
            $table->unsignedBigInteger('budget_id')->nullable()->after('account_level');
            $table->unsignedBigInteger('budget_transaction_id')->nullable()->after('budget_id');
            $table->foreign('budget_id')->references('id')->on('budget_managements');
            $table->foreign('budget_transaction_id')->references('id')->on('budget_transactions');
            $table->index(['budget_id', 'created_at']);
        });
        
        // Create budget versions table
        Schema::create('budget_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->integer('version_number')->default(1);
            $table->string('version_name', 100); // Original, Revised 1, etc.
            $table->enum('version_type', ['ORIGINAL', 'REVISED', 'FORECAST', 'SCENARIO']);
            $table->decimal('allocated_amount', 15, 2);
            $table->json('budget_data'); // Complete budget snapshot
            $table->text('revision_reason')->nullable();
            $table->enum('status', ['DRAFT', 'ACTIVE', 'ARCHIVED'])->default('DRAFT');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('budget_id')->references('id')->on('budget_managements');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->unique(['budget_id', 'version_number']);
            $table->index(['budget_id', 'status']);
        });
        
        // Create budget scenarios table
        Schema::create('budget_scenarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->string('scenario_name', 100);
            $table->enum('scenario_type', ['BEST_CASE', 'WORST_CASE', 'EXPECTED', 'CUSTOM']);
            $table->decimal('adjustment_percentage', 5, 2)->default(0);
            $table->json('assumptions'); // Scenario assumptions
            $table->decimal('projected_amount', 15, 2);
            $table->decimal('projected_utilization', 5, 2);
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('budget_id')->references('id')->on('budget_managements');
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['budget_id', 'is_active']);
        });
        
        // Create budget departments table
        Schema::create('budget_departments', function (Blueprint $table) {
            $table->id();
            $table->string('department_code', 20)->unique();
            $table->string('department_name', 100);
            $table->unsignedBigInteger('parent_department_id')->nullable();
            $table->integer('hierarchy_level')->default(1);
            $table->string('cost_center', 50)->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->decimal('total_budget_allocation', 15, 2)->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('parent_department_id')->references('id')->on('budget_departments');
            $table->foreign('manager_id')->references('id')->on('users');
            $table->index(['department_code', 'is_active']);
        });
        
        // Create budget transfers table
        Schema::create('budget_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_reference', 50)->unique();
            $table->unsignedBigInteger('from_budget_id');
            $table->unsignedBigInteger('to_budget_id');
            $table->decimal('transfer_amount', 15, 2);
            $table->text('transfer_reason');
            $table->json('transfer_details')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'CANCELLED'])->default('PENDING');
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();
            
            $table->foreign('from_budget_id')->references('id')->on('budget_managements');
            $table->foreign('to_budget_id')->references('id')->on('budget_managements');
            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->index(['status', 'created_at']);
        });
        
        // Create budget commitments table (for purchase orders, contracts, etc.)
        Schema::create('budget_commitments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->enum('commitment_type', ['PURCHASE_ORDER', 'CONTRACT', 'REQUISITION', 'OTHER']);
            $table->string('commitment_number', 100)->unique();
            $table->string('vendor_name', 200)->nullable();
            $table->text('description');
            $table->decimal('committed_amount', 15, 2);
            $table->decimal('utilized_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2);
            $table->date('commitment_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['DRAFT', 'COMMITTED', 'PARTIALLY_UTILIZED', 'FULLY_UTILIZED', 'CANCELLED', 'EXPIRED'])->default('DRAFT');
            $table->unsignedBigInteger('created_by');
            $table->json('line_items')->nullable();
            $table->timestamps();
            
            $table->foreign('budget_id')->references('id')->on('budget_managements');
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['budget_id', 'status']);
            $table->index('commitment_date');
        });
        
        // Create budget custom allocations table
        Schema::create('budget_custom_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->integer('period_number'); // Month (1-12) or Quarter (1-4)
            $table->enum('period_type', ['MONTHLY', 'QUARTERLY']);
            $table->decimal('allocated_amount', 15, 2);
            $table->decimal('allocated_percentage', 5, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('budget_id')->references('id')->on('budget_managements');
            $table->unique(['budget_id', 'period_type', 'period_number']);
            $table->index(['budget_id', 'period_type']);
        });
        
        // Create budget carry_forwards table
        Schema::create('budget_carry_forwards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_budget_id');
            $table->unsignedBigInteger('to_budget_id')->nullable();
            $table->decimal('carry_forward_amount', 15, 2);
            $table->integer('from_year');
            $table->integer('to_year');
            $table->enum('status', ['PENDING', 'APPROVED', 'APPLIED', 'REJECTED'])->default('PENDING');
            $table->text('justification')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('from_budget_id')->references('id')->on('budget_managements');
            $table->foreign('to_budget_id')->references('id')->on('budget_managements');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->index(['from_year', 'to_year', 'status']);
        });
        
        // Create budget_reports table for saved reports
        Schema::create('budget_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_name', 200);
            $table->enum('report_type', [
                'BUDGET_VS_ACTUAL',
                'VARIANCE_ANALYSIS',
                'DEPARTMENT_SUMMARY',
                'TREND_ANALYSIS',
                'FORECAST',
                'COMMITMENT_STATUS',
                'CUSTOM'
            ]);
            $table->json('report_parameters'); // Filters, date ranges, etc.
            $table->json('report_data')->nullable(); // Cached data
            $table->enum('frequency', ['DAILY', 'WEEKLY', 'MONTHLY', 'QUARTERLY', 'ANNUAL', 'ON_DEMAND'])->default('ON_DEMAND');
            $table->boolean('is_scheduled')->default(false);
            $table->string('schedule_cron', 100)->nullable();
            $table->json('recipients')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['report_type', 'is_scheduled']);
        });
        
        // Update budget_managements table with new fields
        Schema::table('budget_managements', function (Blueprint $table) {
            // Department and cost center
            $table->unsignedBigInteger('budget_department_id')->nullable()->after('department');
            $table->string('cost_center', 50)->nullable()->after('budget_department_id');
            $table->string('project_code', 50)->nullable()->after('cost_center');
            
            // Budget versioning
            $table->integer('current_version')->default(1)->after('budget_type');
            $table->unsignedBigInteger('active_scenario_id')->nullable()->after('current_version');
            
            // Carry forward settings
            $table->boolean('allow_carry_forward')->default(false)->after('alerts_enabled');
            $table->decimal('carry_forward_limit', 5, 2)->nullable()->after('allow_carry_forward');
            
            // Tolerance levels
            $table->decimal('variance_tolerance', 5, 2)->default(10)->after('critical_threshold');
            $table->boolean('auto_adjust')->default(false)->after('variance_tolerance');
            
            // Rolling budget settings
            $table->boolean('is_rolling')->default(false)->after('budget_type');
            $table->integer('rolling_period_months')->nullable()->after('is_rolling');
            
            // Multi-dimensional tracking
            $table->json('dimensions')->nullable()->after('project_code'); // For custom dimensions
            
            // Foreign keys
            $table->foreign('budget_department_id')->references('id')->on('budget_departments');
            $table->foreign('active_scenario_id')->references('id')->on('budget_scenarios');
            
            // Indexes
            $table->index(['budget_department_id', 'status']);
            $table->index('cost_center');
            $table->index('project_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_managements', function (Blueprint $table) {
            $table->dropForeign(['budget_department_id']);
            $table->dropForeign(['active_scenario_id']);
            $table->dropColumn([
                'budget_department_id',
                'cost_center',
                'project_code',
                'current_version',
                'active_scenario_id',
                'allow_carry_forward',
                'carry_forward_limit',
                'variance_tolerance',
                'auto_adjust',
                'is_rolling',
                'rolling_period_months',
                'dimensions'
            ]);
        });
        
        Schema::dropIfExists('budget_reports');
        Schema::dropIfExists('budget_carry_forwards');
        Schema::dropIfExists('budget_custom_allocations');
        Schema::dropIfExists('budget_commitments');
        Schema::dropIfExists('budget_transfers');
        Schema::dropIfExists('budget_departments');
        Schema::dropIfExists('budget_scenarios');
        Schema::dropIfExists('budget_versions');
        
        Schema::table('general_ledger', function (Blueprint $table) {
            $table->dropForeign(['budget_id']);
            $table->dropForeign(['budget_transaction_id']);
            $table->dropColumn(['budget_id', 'budget_transaction_id']);
        });
    }
};