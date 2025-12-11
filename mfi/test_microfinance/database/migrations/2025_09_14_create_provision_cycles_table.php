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
        // Create provision_cycles table to track the complete lifecycle
        Schema::create('provision_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('cycle_id', 50)->unique(); // e.g., PROV-2025-Q1-001
            $table->enum('frequency', ['MONTHLY', 'QUARTERLY', 'SEMI_ANNUAL', 'ANNUAL', 'EVENT_TRIGGERED']);
            $table->integer('year');
            $table->integer('period'); // Month (1-12) or Quarter (1-4)
            $table->date('cycle_date');
            
            // Step 1: CALCULATE
            $table->decimal('portfolio_value', 20, 2)->nullable();
            $table->decimal('calculated_ecl', 20, 2)->nullable();
            $table->json('aging_analysis')->nullable(); // Store detailed aging breakdown
            $table->timestamp('calculated_at')->nullable();
            
            // Step 2: COMPARE
            $table->decimal('current_reserve', 20, 2)->nullable();
            $table->decimal('required_reserve', 20, 2)->nullable();
            $table->decimal('provision_gap', 20, 2)->nullable(); // Can be negative (over-provisioned)
            $table->timestamp('compared_at')->nullable();
            
            // Step 3: ADJUST
            $table->decimal('adjustment_amount', 20, 2)->nullable();
            $table->enum('adjustment_type', ['PROVISION', 'REVERSAL', 'NONE'])->nullable();
            $table->string('transaction_reference', 100)->nullable();
            $table->timestamp('adjusted_at')->nullable();
            
            // Step 4: MONITOR
            $table->decimal('coverage_ratio', 5, 2)->nullable(); // Percentage
            $table->decimal('npl_ratio', 5, 2)->nullable(); // Non-performing loans ratio
            $table->decimal('provision_coverage', 5, 2)->nullable(); // Provision/NPL ratio
            $table->json('monitoring_metrics')->nullable(); // Additional KPIs
            $table->timestamp('monitored_at')->nullable();
            
            // Step 5: REPORT
            $table->boolean('board_report_generated')->default(false);
            $table->string('board_report_path', 500)->nullable();
            $table->boolean('regulatory_report_submitted')->default(false);
            $table->string('regulatory_report_reference', 100)->nullable();
            $table->timestamp('reported_at')->nullable();
            
            // Cycle Status
            $table->enum('status', [
                'INITIATED',
                'CALCULATED',
                'COMPARED',
                'ADJUSTED',
                'MONITORED',
                'REPORTED',
                'COMPLETED',
                'FAILED'
            ])->default('INITIATED');
            
            // Approval workflow
            $table->bigInteger('prepared_by')->nullable();
            $table->bigInteger('reviewed_by')->nullable();
            $table->bigInteger('approved_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Audit trail
            $table->text('notes')->nullable();
            $table->json('audit_log')->nullable(); // Track all changes
            $table->timestamps();
            
            // Indexes
            $table->index(['year', 'period']);
            $table->index('cycle_date');
            $table->index('status');
            $table->index('frequency');
        });
        
        // Create provision_cycle_details for line-by-line loan analysis
        Schema::create('provision_cycle_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('provision_cycle_id');
            $table->string('loan_account_number', 100);
            $table->string('client_number', 100);
            $table->decimal('outstanding_principal', 20, 2);
            $table->decimal('outstanding_interest', 20, 2);
            $table->integer('days_in_arrears');
            $table->string('risk_classification', 50); // CURRENT, WATCH, SUBSTANDARD, DOUBTFUL, LOSS
            $table->decimal('provision_rate', 5, 2);
            $table->decimal('required_provision', 20, 2);
            $table->timestamps();
            
            // Indexes
            $table->index('provision_cycle_id');
            $table->index('loan_account_number');
            $table->index('risk_classification');
            
            // Foreign key
            $table->foreign('provision_cycle_id')->references('id')->on('provision_cycles')->onDelete('cascade');
        });
        
        // Create automated provision schedules
        Schema::create('provision_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_name', 100);
            $table->enum('frequency', ['DAILY', 'WEEKLY', 'MONTHLY', 'QUARTERLY', 'SEMI_ANNUAL', 'ANNUAL']);
            $table->integer('day_of_period')->nullable(); // Day of month (1-31) or day of quarter (1-90)
            $table->time('execution_time')->default('09:00:00');
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_approve')->default(false);
            $table->boolean('auto_post')->default(false);
            $table->date('next_run_date');
            $table->date('last_run_date')->nullable();
            $table->json('notification_emails')->nullable();
            $table->json('configuration')->nullable(); // Additional settings
            $table->timestamps();
            
            // Indexes
            $table->index('is_active');
            $table->index('next_run_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provision_cycle_details');
        Schema::dropIfExists('provision_schedules');
        Schema::dropIfExists('provision_cycles');
    }
};