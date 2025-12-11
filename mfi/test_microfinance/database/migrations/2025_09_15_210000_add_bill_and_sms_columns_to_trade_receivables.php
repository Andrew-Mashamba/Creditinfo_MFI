<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillAndSmsColumnsToTradeReceivables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trade_receivables', function (Blueprint $table) {
            // Bill and control number columns
            if (!Schema::hasColumn('trade_receivables', 'control_number')) {
                $table->string('control_number')->nullable()->after('invoice_number');
            }
            
            if (!Schema::hasColumn('trade_receivables', 'bill_id')) {
                $table->unsignedBigInteger('bill_id')->nullable()->after('control_number');
            }
            
            // SMS tracking columns
            if (!Schema::hasColumn('trade_receivables', 'sms_sent')) {
                $table->boolean('sms_sent')->default(false)->after('invoice_sent_to');
            }
            
            if (!Schema::hasColumn('trade_receivables', 'sms_sent_at')) {
                $table->timestamp('sms_sent_at')->nullable()->after('sms_sent');
            }
            
            if (!Schema::hasColumn('trade_receivables', 'sms_sent_to')) {
                $table->string('sms_sent_to')->nullable()->after('sms_sent_at');
            }
            
            // Add indexes for performance
            $table->index('control_number');
            $table->index('bill_id');
            
            // Add foreign key constraint for bills table if it exists
            if (Schema::hasTable('bills')) {
                $table->foreign('bill_id')->references('id')->on('bills')->onDelete('set null');
            }
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
            // Drop foreign key if exists
            if (Schema::hasTable('bills')) {
                $table->dropForeign(['bill_id']);
            }
            
            // Drop indexes
            $table->dropIndex(['control_number']);
            $table->dropIndex(['bill_id']);
            
            // Drop columns
            if (Schema::hasColumn('trade_receivables', 'control_number')) {
                $table->dropColumn('control_number');
            }
            
            if (Schema::hasColumn('trade_receivables', 'bill_id')) {
                $table->dropColumn('bill_id');
            }
            
            if (Schema::hasColumn('trade_receivables', 'sms_sent')) {
                $table->dropColumn('sms_sent');
            }
            
            if (Schema::hasColumn('trade_receivables', 'sms_sent_at')) {
                $table->dropColumn('sms_sent_at');
            }
            
            if (Schema::hasColumn('trade_receivables', 'sms_sent_to')) {
                $table->dropColumn('sms_sent_to');
            }
        });
    }
}