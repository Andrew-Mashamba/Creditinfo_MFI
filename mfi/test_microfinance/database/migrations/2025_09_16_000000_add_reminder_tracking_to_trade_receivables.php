<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReminderTrackingToTradeReceivables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trade_receivables', function (Blueprint $table) {
            if (!Schema::hasColumn('trade_receivables', 'last_reminder_sent_at')) {
                $table->timestamp('last_reminder_sent_at')->nullable()
                    ->after('sms_sent_to')
                    ->comment('Last time a reminder was sent');
            }
            
            if (!Schema::hasColumn('trade_receivables', 'reminder_count')) {
                $table->integer('reminder_count')->default(0)
                    ->after('last_reminder_sent_at')
                    ->comment('Number of reminders sent');
            }
            
            // Add indexes for efficient querying
            $table->index(['status', 'balance', 'due_date']);
            $table->index('last_reminder_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trade_receivables', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['status', 'balance', 'due_date']);
            $table->dropIndex(['last_reminder_sent_at']);
            
            if (Schema::hasColumn('trade_receivables', 'last_reminder_sent_at')) {
                $table->dropColumn('last_reminder_sent_at');
            }
            
            if (Schema::hasColumn('trade_receivables', 'reminder_count')) {
                $table->dropColumn('reminder_count');
            }
        });
    }
}