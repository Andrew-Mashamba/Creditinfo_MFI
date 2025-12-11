<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Add payroll integration fields
            $table->bigInteger('payroll_id')->nullable()->after('budget_allocation_id');
            $table->bigInteger('employee_id')->nullable()->after('payroll_id');

            // Add indexes for better query performance
            $table->index('payroll_id');
            $table->index('employee_id');
        });

        // Add expense_created flag to pay_rolls table if it exists
        if (Schema::hasTable('pay_rolls')) {
            Schema::table('pay_rolls', function (Blueprint $table) {
                if (!Schema::hasColumn('pay_rolls', 'expense_created')) {
                    $table->boolean('expense_created')->default(false)->after('status');
                    $table->index('expense_created');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['payroll_id']);
            $table->dropIndex(['employee_id']);

            // Drop columns
            $table->dropColumn(['payroll_id', 'employee_id']);
        });

        if (Schema::hasTable('pay_rolls')) {
            Schema::table('pay_rolls', function (Blueprint $table) {
                if (Schema::hasColumn('pay_rolls', 'expense_created')) {
                    $table->dropIndex(['expense_created']);
                    $table->dropColumn('expense_created');
                }
            });
        }
    }
};
