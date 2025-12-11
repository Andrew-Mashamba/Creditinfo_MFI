<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for loans table
 * 
 * Combined from these migrations:
 * - 2024_03_13_create_loans_table.php
 * - 2024_12_19_000004_improve_loans_table_structure.php
 * - 2025_01_15_000000_add_disbursement_method_to_loans_table.php
 * - 2025_06_27_000000_add_assessment_columns_to_loans_table.php
 * - 2025_06_30_153053_add_loan_calculation_fields_to_loans_table.php
 * - 2025_06_30_154242_add_missing_loan_fields_to_loans_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id')->nullable();
            $table->string('loan_account_number')->nullable();
            $table->string('loan_sub_product')->nullable();
            $table->string('client_number')->nullable();
            $table->string('guarantor')->nullable();
            $table->string('branch_id', 11)->nullable();
            $table->string('principle')->default('0'); // Original type: double precision
            $table->string('interest')->default('0'); // Original type: double precision
            $table->string('business_name')->nullable();
            $table->integer('business_age')->nullable();
            $table->string('business_category')->nullable();
            $table->string('business_type')->nullable();
            $table->string('business_licence_number')->nullable();
            $table->string('business_tin_number')->nullable();
            $table->string('business_inventory')->nullable(); // Original type: double precision
            $table->string('cash_at_hand')->nullable(); // Original type: double precision
            $table->string('daily_sales')->nullable(); // Original type: double precision
            $table->string('cost_of_goods_sold')->nullable(); // Original type: double precision
            $table->string('available_funds')->nullable(); // Original type: double precision
            $table->string('operating_expenses')->nullable(); // Original type: double precision
            $table->string('monthly_taxes')->nullable(); // Original type: double precision
            $table->string('other_expenses')->nullable(); // Original type: double precision
            $table->string('collateral_value')->nullable(); // Original type: double precision
            $table->text('collateral_location')->nullable();
            $table->text('collateral_description')->nullable();
            $table->string('collateral_type')->nullable();
            $table->integer('tenure')->nullable();
            $table->string('principle_amount')->nullable(); // Original type: double precision
            $table->string('interest_method', 30)->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank', 100)->nullable();
            $table->integer('LoanPhoneNo')->nullable();
            $table->string('status')->nullable();
            $table->string('loan_status', 20)->default('NORMAL');
            $table->string('restructure_loanId', 50)->nullable();
            $table->string('heath')->default('GOOD');
            $table->string('phone_number', 30)->nullable();
            $table->string('pay_method', 30)->nullable();
            $table->bigInteger('days_in_arrears')->nullable();
            $table->bigInteger('total_days_in_arrears')->nullable();
            $table->string('arrears_in_amount')->nullable(); // Original type: double precision
            $table->bigInteger('supervisor_id')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->bigInteger('client_id')->nullable();
            $table->string('relationship', 100)->nullable();
            $table->string('loan_type', 20)->nullable();
            $table->string('future_interest')->nullable(); // Original type: double precision
            $table->string('total_principle')->nullable(); // Original type: double precision
            $table->string('loan_type_2', 150)->nullable();
            $table->string('loan_purpose')->nullable();
            $table->string('stage_id')->nullable();
            $table->string('stage')->nullable();
            $table->string('loan_type_3')->nullable();
            $table->string('take_home', 150)->nullable();
            $table->string('approved_loan_value', 150)->nullable();
            $table->string('approved_term', 150)->nullable();
            $table->string('amount_to_be_credited', 150)->nullable();
            $table->timestamp('disbursement_date')->nullable();
            $table->string('bank_account', 150)->nullable();
            $table->string('interest_account_number', 150)->nullable();
            $table->string('charge_account_number', 150)->nullable();
            $table->string('insurance_account_number', 150)->nullable();
            $table->string('selectedLoan')->nullable();
            $table->string('client_type', 150)->nullable();
            $table->string('group_number', 150)->nullable();
            $table->bigInteger('group_id')->nullable();
            $table->string('parent_loan_id', 150)->nullable();
            $table->string('disbursement_method', 50)->nullable();
            $table->string('monthly_installment')->nullable(); // Original type: double precision
            $table->json('assessment_data')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->bigInteger('declined_by')->nullable();
            $table->text('decline_reason')->nullable();
            $table->decimal('total_interest', 15, 2)->nullable();
            $table->decimal('total_principal', 15, 2)->nullable();
            $table->decimal('total_payment', 15, 2)->nullable();
            $table->string('disbursement_account', 150)->nullable();
            $table->decimal('net_disbursement_amount', 15, 2)->nullable();
            $table->decimal('total_deductions', 15, 2)->nullable();
            $table->string('source')->nullable();
            $table->index(['client_number', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['created_at']);
            $table->index(['loan_type_2', 'status']);
            $table->index(['stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};