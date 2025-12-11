<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for expenses table
 * 
 * Combined from these migrations:
 * - 2025_07_07_205322_create_expenses_table.php
 * - 2025_07_22_080255_add_budget_tracking_to_expenses_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id');
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->string('payment_type');
            $table->bigInteger('user_id');
            $table->string('status')->default('PENDING_APPROVAL');
            $table->bigInteger('approval_id')->nullable();
            $table->string('retirement_receipt_path')->nullable();
            $table->bigInteger('budget_item_id')->nullable();
            $table->decimal('monthly_budget_amount', 15, 2)->nullable();
            $table->decimal('monthly_spent_amount', 15, 2)->default('0');
            $table->decimal('budget_utilization_percentage', 5, 2)->default('0');
            $table->string('budget_status')->default('WITHIN_BUDGET');
            $table->string('budget_resolution')->default('NONE');
            $table->text('budget_notes')->nullable();
            $table->date('expense_month')->nullable();
            $table->foreign('approval_id')->references('id')->on('approvals');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('budget_item_id')->references('id')->on('budget_managements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};