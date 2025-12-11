<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trade_payables', function (Blueprint $table) {
            // Subscription/Recurring fields
            if (!Schema::hasColumn('trade_payables', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false)->after('status');
            }
            if (!Schema::hasColumn('trade_payables', 'recurring_frequency')) {
                $table->enum('recurring_frequency', ['monthly', 'quarterly', 'annually'])->default('monthly')->after('is_recurring');
            }
            if (!Schema::hasColumn('trade_payables', 'recurring_start_date')) {
                $table->date('recurring_start_date')->nullable()->after('recurring_frequency');
            }
            if (!Schema::hasColumn('trade_payables', 'recurring_end_date')) {
                $table->date('recurring_end_date')->nullable()->after('recurring_start_date');
            }
            if (!Schema::hasColumn('trade_payables', 'next_billing_date')) {
                $table->date('next_billing_date')->nullable()->after('recurring_end_date');
            }
            if (!Schema::hasColumn('trade_payables', 'service_type')) {
                $table->string('service_type')->default('general')->after('next_billing_date');
            }
            if (!Schema::hasColumn('trade_payables', 'subscription_status')) {
                $table->enum('subscription_status', ['active', 'paused', 'cancelled'])->default('active')->after('service_type');
            }
            if (!Schema::hasColumn('trade_payables', 'parent_subscription_id')) {
                $table->bigInteger('parent_subscription_id')->nullable()->after('subscription_status');
            }
            
            // Add indexes for better performance
            $table->index(['is_recurring'], 'trade_payables_is_recurring_index');
            $table->index(['next_billing_date'], 'trade_payables_next_billing_date_index');
            $table->index(['subscription_status'], 'trade_payables_subscription_status_index');
            $table->index(['service_type'], 'trade_payables_service_type_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trade_payables', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('trade_payables_is_recurring_index');
            $table->dropIndex('trade_payables_next_billing_date_index');
            $table->dropIndex('trade_payables_subscription_status_index');
            $table->dropIndex('trade_payables_service_type_index');
            
            // Drop columns
            $table->dropColumn([
                'is_recurring',
                'recurring_frequency',
                'recurring_start_date',
                'recurring_end_date',
                'next_billing_date',
                'service_type',
                'subscription_status',
                'parent_subscription_id'
            ]);
        });
    }
};