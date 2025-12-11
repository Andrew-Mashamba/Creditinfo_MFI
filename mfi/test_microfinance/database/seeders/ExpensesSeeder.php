<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpensesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('expenses')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'account_id' => 204,
                'amount' => 700000.00,
                'description' => 'ughjj',
                'payment_type' => 'money_transfer',
                'user_id' => 1,
                'status' => 'PENDING_APPROVAL',
                'approval_id' => null,
                'retirement_receipt_path' => null,
                'created_at' => '2025-07-22 08:34:47',
                'updated_at' => '2025-07-22 08:34:47',
                'budget_item_id' => null,
                'monthly_budget_amount' => null,
                'monthly_spent_amount' => 0.00,
                'budget_utilization_percentage' => 0.00,
                'budget_status' => 'WITHIN_BUDGET',
                'budget_resolution' => 'NONE',
                'budget_notes' => null,
                'expense_month' => '2025-07-01',
            ],
            [
                'id' => 2,
                'account_id' => 212,
                'amount' => 8000000.00,
                'description' => 'hghghgh',
                'payment_type' => 'money_transfer',
                'user_id' => 1,
                'status' => 'PENDING_APPROVAL',
                'approval_id' => null,
                'retirement_receipt_path' => null,
                'created_at' => '2025-07-22 08:43:51',
                'updated_at' => '2025-07-22 08:43:51',
                'budget_item_id' => null,
                'monthly_budget_amount' => null,
                'monthly_spent_amount' => 0.00,
                'budget_utilization_percentage' => 0.00,
                'budget_status' => 'WITHIN_BUDGET',
                'budget_resolution' => 'NONE',
                'budget_notes' => null,
                'expense_month' => '2025-07-01',
            ],
        ];

        foreach ($data as $row) {
            // Check if account exists
            if (!DB::table('accounts')->where('id', $row['account_id'])->exists()) {
                // Try to find any expense account
                $expenseAccount = DB::table('accounts')
                    ->where('account_name', 'like', '%expense%')
                    ->orWhere('account_name', 'like', '%cost%')
                    ->first();
                
                if ($expenseAccount) {
                    $row['account_id'] = $expenseAccount->id;
                } else {
                    if ($this->command) $this->command->warn("Skipping expense - no suitable account found");
                    continue;
                }
            }
            
            // Check if user exists
            if (!DB::table('users')->where('id', $row['user_id'])->exists()) {
                $firstUser = DB::table('users')->first();
                if ($firstUser) {
                    $row['user_id'] = $firstUser->id;
                } else {
                    if ($this->command) $this->command->warn("Skipping expense - no users found");
                    continue;
                }
            }
            
            DB::table('expenses')->insert($row);
    }
}
}