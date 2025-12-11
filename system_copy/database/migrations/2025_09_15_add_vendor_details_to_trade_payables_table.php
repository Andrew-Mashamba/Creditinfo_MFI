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
            // Add missing vendor contact details
            if (!Schema::hasColumn('trade_payables', 'vendor_email')) {
                $table->string('vendor_email')->nullable()->after('vendor_name');
            }
            if (!Schema::hasColumn('trade_payables', 'vendor_phone')) {
                $table->string('vendor_phone')->nullable()->after('vendor_email');
            }
            if (!Schema::hasColumn('trade_payables', 'vendor_address')) {
                $table->text('vendor_address')->nullable()->after('vendor_phone');
            }
            if (!Schema::hasColumn('trade_payables', 'vendor_tax_id')) {
                $table->string('vendor_tax_id')->nullable()->after('vendor_address');
            }
            
            // Add currency field
            if (!Schema::hasColumn('trade_payables', 'currency')) {
                $table->string('currency', 10)->default('TZS')->after('amount');
            }
            
            // Add VAT amount field
            if (!Schema::hasColumn('trade_payables', 'vat_amount')) {
                $table->decimal('vat_amount', 20, 2)->default(0)->after('amount');
            }
            
            // Add payment tracking fields
            if (!Schema::hasColumn('trade_payables', 'last_payment_date')) {
                $table->date('last_payment_date')->nullable()->after('paid_amount');
            }
            if (!Schema::hasColumn('trade_payables', 'payment_date')) {
                $table->date('payment_date')->nullable()->after('last_payment_date');
            }
            
            // Add priority field
            if (!Schema::hasColumn('trade_payables', 'priority')) {
                $table->enum('priority', ['high', 'normal', 'low'])->default('normal')->after('status');
            }
            
            // Add processing status for job tracking
            if (!Schema::hasColumn('trade_payables', 'processing_status')) {
                $table->string('processing_status')->nullable()->after('status');
            }
            
            // Add file attachment fields
            if (!Schema::hasColumn('trade_payables', 'invoice_attachment')) {
                $table->string('invoice_attachment')->nullable();
            }
            if (!Schema::hasColumn('trade_payables', 'purchase_order_attachment')) {
                $table->string('purchase_order_attachment')->nullable();
            }
            
            // Add notes field
            if (!Schema::hasColumn('trade_payables', 'notes')) {
                $table->text('notes')->nullable();
            }
            
            // Add indexes for better performance (check if they don't exist)
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('trade_payables');
            
            if (!array_key_exists('trade_payables_vendor_email_index', $indexesFound)) {
                $table->index(['vendor_email'], 'trade_payables_vendor_email_index');
            }
            if (!array_key_exists('trade_payables_status_index', $indexesFound)) {
                $table->index(['status'], 'trade_payables_status_index');
            }
            if (!array_key_exists('trade_payables_due_date_index', $indexesFound)) {
                $table->index(['due_date'], 'trade_payables_due_date_index');
            }
            if (!array_key_exists('trade_payables_bill_date_index', $indexesFound)) {
                $table->index(['bill_date'], 'trade_payables_bill_date_index');
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
        Schema::table('trade_payables', function (Blueprint $table) {
            // Drop indexes first (check if they exist)
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('trade_payables');
            
            if (array_key_exists('trade_payables_vendor_email_index', $indexesFound)) {
                $table->dropIndex('trade_payables_vendor_email_index');
            }
            if (array_key_exists('trade_payables_status_index', $indexesFound)) {
                $table->dropIndex('trade_payables_status_index');
            }
            if (array_key_exists('trade_payables_due_date_index', $indexesFound)) {
                $table->dropIndex('trade_payables_due_date_index');
            }
            if (array_key_exists('trade_payables_bill_date_index', $indexesFound)) {
                $table->dropIndex('trade_payables_bill_date_index');
            }
            
            // Drop columns
            $table->dropColumn([
                'vendor_email',
                'vendor_phone',
                'vendor_address',
                'vendor_tax_id',
                'currency',
                'vat_amount',
                'last_payment_date',
                'payment_date',
                'priority',
                'processing_status',
                'invoice_attachment',
                'purchase_order_attachment',
                'notes'
            ]);
        });
    }
};