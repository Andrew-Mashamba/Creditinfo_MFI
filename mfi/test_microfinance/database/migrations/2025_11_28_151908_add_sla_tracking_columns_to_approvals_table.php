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
        Schema::table('approvals', function (Blueprint $table) {
            // SLA Tracking
            $table->integer('sla_breach_level')->nullable()->after('approval_category'); // 1=first check, 2=second check, 3=approval
            $table->timestamp('sla_breached_at')->nullable()->after('sla_breach_level');
            $table->timestamp('sla_warning_sent_at')->nullable()->after('sla_breached_at');
            $table->timestamp('sla_breach_notified_at')->nullable()->after('sla_warning_sent_at');
            $table->boolean('sla_escalated')->default(false)->after('sla_breach_notified_at');
            $table->timestamp('sla_escalated_at')->nullable()->after('sla_escalated');
            $table->integer('sla_notification_count')->default(0)->after('sla_escalated_at');

            // Indexes for efficient querying
            $table->index(['process_status', 'sla_breached_at']);
            $table->index(['process_status', 'checker_level', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('approvals', function (Blueprint $table) {
            $table->dropIndex(['process_status', 'sla_breached_at']);
            $table->dropIndex(['process_status', 'checker_level', 'created_at']);
            $table->dropColumn([
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
