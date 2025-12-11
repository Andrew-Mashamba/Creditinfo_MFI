<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add EXPENSE_REG process code configuration
        DB::table('process_code_configs')->insertOrIgnore([
            'process_code' => 'EXPENSE_REG',
            'process_name' => 'Expense Registration',
            'description' => 'Process for registering and approving expenses with budget validation',
            'requires_first_checker' => true,
            'requires_second_checker' => true,
            'requires_approver' => true,
            'first_checker_roles' => json_encode([1, 2, 3]), // Admin, Manager, Supervisor roles (adjust as needed)
            'second_checker_roles' => json_encode([1, 2]), // Admin, Manager roles
            'approver_roles' => json_encode([1]), // Admin role
            'min_amount' => 0,
            'max_amount' => null, // No maximum limit
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Also add some other common expense-related process codes
        $additionalProcessCodes = [
            [
                'process_code' => 'EXPENSE_PAYMENT',
                'process_name' => 'Expense Payment',
                'description' => 'Process for paying approved expenses',
                'requires_first_checker' => true,
                'requires_second_checker' => false,
                'requires_approver' => true,
                'first_checker_roles' => json_encode([1, 2, 4]), // Admin, Manager, Accountant
                'second_checker_roles' => json_encode([]),
                'approver_roles' => json_encode([1, 2]), // Admin, Manager
                'min_amount' => 0,
                'max_amount' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'process_code' => 'EXPENSE_REIMBURSEMENT',
                'process_name' => 'Expense Reimbursement',
                'description' => 'Process for reimbursing employee expenses',
                'requires_first_checker' => true,
                'requires_second_checker' => true,
                'requires_approver' => true,
                'first_checker_roles' => json_encode([1, 2, 3, 4]), // Admin, Manager, Supervisor, Accountant
                'second_checker_roles' => json_encode([1, 2]), // Admin, Manager
                'approver_roles' => json_encode([1]), // Admin
                'min_amount' => 0,
                'max_amount' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'process_code' => 'BUDGET_OVERRIDE',
                'process_name' => 'Budget Override',
                'description' => 'Process for approving expenses that exceed budget',
                'requires_first_checker' => true,
                'requires_second_checker' => true,
                'requires_approver' => true,
                'first_checker_roles' => json_encode([1, 2]), // Admin, Manager
                'second_checker_roles' => json_encode([1]), // Admin
                'approver_roles' => json_encode([1]), // Admin only
                'min_amount' => 0,
                'max_amount' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($additionalProcessCodes as $processCode) {
            DB::table('process_code_configs')->insertOrIgnore($processCode);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the process codes we added
        DB::table('process_code_configs')->whereIn('process_code', [
            'EXPENSE_REG',
            'EXPENSE_PAYMENT',
            'EXPENSE_REIMBURSEMENT',
            'BUDGET_OVERRIDE'
        ])->delete();
    }
};