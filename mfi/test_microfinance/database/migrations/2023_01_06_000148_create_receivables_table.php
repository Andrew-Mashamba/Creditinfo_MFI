<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for receivables table
 * 
 * Combined from these migrations:
 * - 2024_03_13_create_receivables_table.php
 * - 2024_03_14_add_receivable_types_fields.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('receivables', function (Blueprint $table) {
            $table->id();
            $table->string('account_number');
            $table->bigInteger('customer_id')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('status')->default('unpaid');
            $table->text('description')->nullable();
            $table->string('customer_name', 250)->nullable();
            $table->string('source', 150)->nullable();
            $table->string('income_account', 150)->nullable();
            $table->string('asset_account', 150)->nullable();
            $table->string('payment', 150)->nullable();
            $table->string('receivable_type', 50)->default('credit_sales');
            $table->string('service_type', 100)->nullable();
            $table->string('property_type', 100)->nullable();
            $table->string('investment_type', 100)->nullable();
            $table->string('insurance_claim_type', 100)->nullable();
            $table->string('government_agency', 100)->nullable();
            $table->string('contract_type', 100)->nullable();
            $table->string('subscription_type', 100)->nullable();
            $table->string('installment_plan', 100)->nullable();
            $table->string('royalty_type', 100)->nullable();
            $table->string('commission_type', 100)->nullable();
            $table->string('utility_type', 100)->nullable();
            $table->string('healthcare_type', 100)->nullable();
            $table->string('education_type', 100)->nullable();
            $table->date('aging_date')->nullable();
            $table->string('payment_terms', 100)->nullable();
            $table->string('collection_status', 50)->nullable();
            $table->text('collection_notes')->nullable();
            $table->string('assigned_to', 100)->nullable();
            $table->date('last_payment_date')->nullable();
            $table->decimal('last_payment_amount', 15, 2)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->string('revenue_category', 100)->nullable();
            $table->string('cost_center', 100)->nullable();
            $table->string('project_code', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('document_reference', 100)->nullable();
            $table->string('approval_status', 50)->nullable();
            $table->string('approved_by', 100)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->index(['account_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivables');
    }
};