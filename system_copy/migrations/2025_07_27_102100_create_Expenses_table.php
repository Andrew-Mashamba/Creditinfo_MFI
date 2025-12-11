<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for Expenses table
 * 
 * Combined from these migrations:
 * - 2023_07_20_204539_expenses.php
 * - 2025_07_02_015413_add_status_column_to_expenses_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Expenses', function (Blueprint $table) {
            $table->id();
            $table->string('vendor');
            $table->string('category');
            $table->string('amount'); // Original type: double precision
            $table->integer('paymentMethod');
            $table->string('expenditureAccount');
            $table->string('destinationAccount');
            $table->integer('employeeId');
            $table->integer('departmentId');
            $table->string('expenseDate');
            $table->string('status')->nullable()->default('PENDING');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Expenses');
    }
};