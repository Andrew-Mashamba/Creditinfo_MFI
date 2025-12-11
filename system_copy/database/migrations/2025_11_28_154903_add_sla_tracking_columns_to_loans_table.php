<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add SLA tracking columns to loans table for monitoring loan approval SLAs
     */
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Track when the loan entered the current approval stage
            $table->timestamp('stage_entered_at')->nullable()->after('approval_stage_role_name');

            // SLA breach tracking
            $table->integer('sla_breach_level')->nullable()->after('stage_entered_at')
                  ->comment('The approval stage level where SLA was breached (1=First Checker, 2=Second Checker, 3=Approver)');
            $table->timestamp('sla_breached_at')->nullable()->after('sla_breach_level');

            // Notification tracking
            $table->timestamp('sla_warning_sent_at')->nullable()->after('sla_breached_at');
            $table->timestamp('sla_breach_notified_at')->nullable()->after('sla_warning_sent_at');

            // Escalation tracking
            $table->boolean('sla_escalated')->default(false)->after('sla_breach_notified_at');
            $table->timestamp('sla_escalated_at')->nullable()->after('sla_escalated');

            // Count of SLA notifications sent
            $table->integer('sla_notification_count')->default(0)->after('sla_escalated_at');

            // Index for SLA monitoring queries
            $table->index(['status', 'approval_stage', 'sla_breached_at'], 'loans_sla_monitoring_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex('loans_sla_monitoring_index');

            $table->dropColumn([
                'stage_entered_at',
                'sla_breach_level',
                'sla_breached_at',
                'sla_warning_sent_at',
                'sla_breach_notified_at',
                'sla_escalated',
                'sla_escalated_at',
                'sla_notification_count'
            ]);
        });
    }
};
