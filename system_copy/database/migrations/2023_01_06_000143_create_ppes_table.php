<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for ppes table
 * 
 * Combined from these migrations:
 * - 2024_03_13_create_ppes_table.php
 * - 2025_07_03_101953_update_ppes_table_numeric_precision.php
 * - 2025_07_03_102500_enhance_ppes_table_for_proper_accounting.php
 * - 2025_07_03_151816_add_disposal_fields_to_ppes_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ppes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('salvage_value', 15, 2)->nullable();
            $table->integer('useful_life')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('initial_value', 15, 2)->nullable();
            $table->decimal('depreciation_rate', 15, 2)->nullable();
            $table->decimal('accumulated_depreciation', 15, 2)->nullable();
            $table->decimal('depreciation_for_year', 15, 2)->nullable();
            $table->decimal('closing_value', 15, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->string('account_number')->nullable();
            $table->decimal('depreciation_for_month', 15, 2)->nullable()->default(0);
            $table->decimal('legal_fees', 15, 2)->nullable()->default('0');
            $table->decimal('registration_fees', 15, 2)->nullable()->default('0');
            $table->decimal('renovation_costs', 15, 2)->nullable()->default('0');
            $table->decimal('transportation_costs', 15, 2)->nullable()->default('0');
            $table->decimal('installation_costs', 15, 2)->nullable()->default('0');
            $table->decimal('other_costs', 15, 2)->nullable()->default('0');
            $table->string('payment_method')->default('cash');
            $table->string('payment_account_number')->nullable();
            $table->string('payable_account_number')->nullable();
            $table->string('accounting_transaction_id')->nullable();
            $table->boolean('accounting_entry_created')->default(false);
            $table->string('supplier_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->text('additional_notes')->nullable();
            $table->date('disposal_date')->nullable();
            $table->string('disposal_method')->nullable();
            $table->decimal('disposal_proceeds', 15, 2)->default('0');
            $table->text('disposal_notes')->nullable();
            $table->string('disposal_approval_status')->default('pending');
            $table->bigInteger('disposal_approved_by')->nullable();
            $table->timestamp('disposal_approved_at')->nullable();
            $table->text('disposal_rejection_reason')->nullable();
            $table->foreign('disposal_approved_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppes');
    }
};