<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for budget_managements table
 * 
 * Combined from these migrations:
 * - 2024_03_13_000007_create_budget_managements_table.php
 * - 2025_07_22_061255_add_expense_account_id_to_budget_managements_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budget_managements', function (Blueprint $table) {
            $table->id();
            $table->string('revenue')->nullable(); // Original type: double precision
            $table->string('expenditure')->nullable(); // Original type: double precision
            $table->string('capital_expenditure')->nullable(); // Original type: double precision
            $table->string('budget_name', 40)->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('spent_amount')->nullable(); // Original type: double precision
            $table->string('status', 10)->nullable();
            $table->string('approval_status', 10)->nullable();
            $table->text('notes')->nullable();
            $table->bigInteger('department')->nullable();
            $table->string('currency', 4)->default('TZS');
            $table->bigInteger('expense_account_id')->nullable();
            $table->timestamps();
            $table->foreign('expense_account_id')->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_managements');
    }
};