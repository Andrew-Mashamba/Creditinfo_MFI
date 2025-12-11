<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetmanagementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('budget_managements')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'revenue' => 4000000,
                'expenditure' => null,
                'capital_expenditure' => 333333.33,
                'budget_name' => 'TRAVEL EXPENSES',
                'start_date' => '2025-07-22 00:00:00',
                'end_date' => '2026-06-22 00:00:00',
                'spent_amount' => null,
                'status' => 'active',
                'approval_status' => 'approved',
                'notes' => 'Travel',
                'created_at' => '2025-07-22 06:36:46',
                'updated_at' => '2025-07-22 07:23:35',
                'department' => null,
                'currency' => 'TZS',
                'expense_account_id' => 48,
            ],
            [
                'id' => 2,
                'revenue' => 4000000,
                'expenditure' => null,
                'capital_expenditure' => 333333.33,
                'budget_name' => 'TRAVEL EXPENSES',
                'start_date' => '2025-07-22 00:00:00',
                'end_date' => '2026-06-22 00:00:00',
                'spent_amount' => null,
                'status' => 'active',
                'approval_status' => 'approved',
                'notes' => 'Travel',
                'created_at' => '2025-07-22 06:37:50',
                'updated_at' => '2025-07-22 07:26:12',
                'department' => null,
                'currency' => 'TZS',
                'expense_account_id' => 48,
            ],
            [
                'id' => 3,
                'revenue' => 4000000,
                'expenditure' => null,
                'capital_expenditure' => 333333.33,
                'budget_name' => 'TRAVEL EXPENSES',
                'start_date' => '2025-07-22 00:00:00',
                'end_date' => '2026-06-22 00:00:00',
                'spent_amount' => null,
                'status' => 'active',
                'approval_status' => 'approved',
                'notes' => 'Travel',
                'created_at' => '2025-07-22 06:49:30',
                'updated_at' => '2025-07-22 07:26:12',
                'department' => null,
                'currency' => 'TZS',
                'expense_account_id' => 48,
            ],
        ];

        foreach ($data as $row) {
            // Check if expense_account exists
            if (isset($row['expense_account_id'])) {
                $accountExists = DB::table('accounts')->where('id', $row['expense_account_id'])->exists();
                if (!$accountExists) {
                    // Find any expense account or use null
                    $expenseAccount = DB::table('accounts')
                        ->where('account_name', 'like', '%expense%')
                        ->orWhere('account_name', 'like', '%budget%')
                        ->first();
                    
                    if ($expenseAccount) {
                        $row['expense_account_id'] = $expenseAccount->id;
                    } else {
                        $row['expense_account_id'] = null;
                    }
                }
            }
            
            DB::table('budget_managements')->insert($row);
    }
}
}