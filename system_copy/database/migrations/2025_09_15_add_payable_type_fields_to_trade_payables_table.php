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
            // Payable type field
            if (!Schema::hasColumn('trade_payables', 'payable_type')) {
                $table->enum('payable_type', ['once_off', 'installment', 'subscription'])
                    ->default('once_off')
                    ->after('bill_number')
                    ->comment('Type of payable: once_off for single payment, installment for split payments, subscription for recurring');
            }
            
            // Installment-specific fields
            if (!Schema::hasColumn('trade_payables', 'installment_count')) {
                $table->integer('installment_count')->nullable()->after('payable_type')
                    ->comment('Total number of installments');
            }
            
            if (!Schema::hasColumn('trade_payables', 'installment_frequency')) {
                $table->enum('installment_frequency', ['weekly', 'bi_weekly', 'monthly', 'quarterly'])
                    ->nullable()
                    ->after('installment_count')
                    ->comment('Frequency of installment payments');
            }
            
            if (!Schema::hasColumn('trade_payables', 'installments_paid')) {
                $table->integer('installments_paid')->default(0)->after('installment_frequency')
                    ->comment('Number of installments already paid');
            }
            
            if (!Schema::hasColumn('trade_payables', 'installment_amount')) {
                $table->decimal('installment_amount', 15, 2)->nullable()->after('installments_paid')
                    ->comment('Amount per installment');
            }
            
            if (!Schema::hasColumn('trade_payables', 'next_installment_date')) {
                $table->date('next_installment_date')->nullable()->after('installment_amount')
                    ->comment('Date when next installment is due');
            }
            
            if (!Schema::hasColumn('trade_payables', 'last_installment_date')) {
                $table->date('last_installment_date')->nullable()->after('next_installment_date')
                    ->comment('Date when last installment was paid');
            }
            
            // Parent payable for tracking related installments
            if (!Schema::hasColumn('trade_payables', 'parent_payable_id')) {
                $table->bigInteger('parent_payable_id')->nullable()->after('last_installment_date')
                    ->comment('ID of parent payable for installment tracking');
            }
            
            // Indexes for better performance
            $table->index(['payable_type'], 'trade_payables_payable_type_index');
            $table->index(['next_installment_date'], 'trade_payables_next_installment_date_index');
            $table->index(['parent_payable_id'], 'trade_payables_parent_payable_id_index');
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
            $table->dropIndex('trade_payables_payable_type_index');
            $table->dropIndex('trade_payables_next_installment_date_index');
            $table->dropIndex('trade_payables_parent_payable_id_index');
            
            // Drop columns
            $table->dropColumn([
                'payable_type',
                'installment_count',
                'installment_frequency',
                'installments_paid',
                'installment_amount',
                'next_installment_date',
                'last_installment_date',
                'parent_payable_id'
            ]);
        });
    }
};