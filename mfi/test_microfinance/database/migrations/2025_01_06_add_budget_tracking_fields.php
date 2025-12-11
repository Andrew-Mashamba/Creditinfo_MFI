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
        Schema::table('budget_managements', function (Blueprint $table) {
            // Budget tracking fields
            $table->decimal('allocated_amount', 15, 2)->default(0)->after('capital_expenditure');
            $table->decimal('committed_amount', 15, 2)->default(0)->after('spent_amount');
            $table->decimal('available_amount', 15, 2)->default(0)->after('committed_amount');
            $table->decimal('variance_amount', 15, 2)->default(0)->after('available_amount');
            $table->decimal('utilization_percentage', 5, 2)->default(0)->after('variance_amount');
            
            // Budget type and configuration
            $table->enum('budget_type', ['OPERATING', 'CAPITAL', 'PROJECT', 'ZERO_BASED', 'FLEXIBLE', 'ROLLING'])->default('OPERATING')->after('budget_name');
            $table->enum('allocation_pattern', ['EQUAL', 'CUSTOM', 'SEASONAL', 'FRONT_LOADED', 'BACK_LOADED'])->default('EQUAL')->after('budget_type');
            $table->json('monthly_allocations')->nullable()->after('allocation_pattern');
            $table->json('quarterly_allocations')->nullable()->after('monthly_allocations');
            
            // Alert thresholds
            $table->decimal('warning_threshold', 5, 2)->default(80)->after('currency');
            $table->decimal('critical_threshold', 5, 2)->default(90)->after('warning_threshold');
            $table->boolean('alerts_enabled')->default(true)->after('critical_threshold');
            $table->timestamp('last_alert_sent')->nullable()->after('alerts_enabled');
            
            // Tracking timestamps
            $table->timestamp('last_transaction_date')->nullable()->after('last_alert_sent');
            $table->timestamp('last_calculated_at')->nullable()->after('last_transaction_date');
            
            // Budget period tracking
            $table->integer('budget_year')->nullable()->after('end_date');
            $table->integer('budget_quarter')->nullable()->after('budget_year');
            $table->integer('budget_month')->nullable()->after('budget_quarter');
            
            // Add indexes for performance
            $table->index(['status', 'utilization_percentage']);
            $table->index(['budget_year', 'budget_month']);
            $table->index(['expense_account_id', 'status']);
        });
        
        // Create budget_transactions table to link budgets with actual transactions
        Schema::create('budget_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->string('transaction_type', 50); // EXPENSE, COMMITMENT, TRANSFER
            $table->string('reference_number', 100)->nullable();
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->enum('status', ['PENDING', 'POSTED', 'REVERSED', 'CANCELLED'])->default('PENDING');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('budget_id')->references('id')->on('budget_managements')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('posted_by')->references('id')->on('users');
            
            // Indexes
            $table->index(['budget_id', 'status']);
            $table->index(['transaction_date']);
            $table->index(['transaction_type', 'status']);
        });
        
        // Create budget_alerts table for tracking notifications
        Schema::create('budget_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->enum('alert_type', ['WARNING', 'CRITICAL', 'OVERSPENT', 'MILESTONE', 'PERIOD_END']);
            $table->decimal('threshold_value', 5, 2);
            $table->decimal('actual_value', 5, 2);
            $table->text('message');
            $table->json('recipients')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->boolean('is_acknowledged')->default(false);
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('budget_id')->references('id')->on('budget_managements')->onDelete('cascade');
            $table->foreign('acknowledged_by')->references('id')->on('users');
            
            // Indexes
            $table->index(['budget_id', 'alert_type']);
            $table->index(['is_sent', 'is_acknowledged']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_alerts');
        Schema::dropIfExists('budget_transactions');
        
        Schema::table('budget_managements', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['status', 'utilization_percentage']);
            $table->dropIndex(['budget_year', 'budget_month']);
            $table->dropIndex(['expense_account_id', 'status']);
            
            // Drop columns
            $table->dropColumn([
                'allocated_amount',
                'committed_amount',
                'available_amount',
                'variance_amount',
                'utilization_percentage',
                'budget_type',
                'allocation_pattern',
                'monthly_allocations',
                'quarterly_allocations',
                'warning_threshold',
                'critical_threshold',
                'alerts_enabled',
                'last_alert_sent',
                'last_transaction_date',
                'last_calculated_at',
                'budget_year',
                'budget_quarter',
                'budget_month'
            ]);
        });
    }
};