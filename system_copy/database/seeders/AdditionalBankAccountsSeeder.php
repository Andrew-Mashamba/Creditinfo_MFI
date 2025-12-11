<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdditionalBankAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $bankAccounts = [
            [
                'bank_name' => 'CRDB Bank',
                'account_name' => 'CRDB SACCOS SAVINGS ACCOUNT',
                'account_number' => '0150245789123',
                'branch_name' => 'Dar es Salaam Branch',
                'swift_code' => 'CORUTZTZ',
                'currency' => 'TZS',
                'opening_balance' => 50000000.00,
                'current_balance' => 75000000.00,
                'internal_mirror_account_number' => '1001002',
                'status' => 'ACTIVE',
                'description' => 'CRDB Bank account for member savings and deposits operations',
                'created_by' => 'admin',
                'updated_by' => 'admin',
                'account_type' => 'savings_operations',
                'branch_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'bank_name' => 'Equity Bank',
                'account_name' => 'EQUITY LOAN DISBURSEMENT ACCOUNT',
                'account_number' => '1234567890123',
                'branch_name' => 'Mwanza Branch',
                'swift_code' => 'EQBLTZTZ',
                'currency' => 'TZS',
                'opening_balance' => 100000000.00,
                'current_balance' => 125000000.00,
                'internal_mirror_account_number' => '4001001',
                'status' => 'ACTIVE',
                'description' => 'Equity Bank account specifically for loan disbursements and collections',
                'created_by' => 'admin',
                'updated_by' => 'admin',
                'account_type' => 'loan_operations',
                'branch_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        // Check if accounts already exist to avoid duplicates
        foreach ($bankAccounts as $account) {
            $exists = DB::table('bank_accounts')
                ->where('account_number', $account['account_number'])
                ->exists();
            
            if (!$exists) {
                DB::table('bank_accounts')->insert($account);
                $this->command->info("✓ Inserted: {$account['bank_name']} - {$account['account_name']} ({$account['account_number']})");
            } else {
                $this->command->warn("⚠ Skipped: {$account['bank_name']} - {$account['account_name']} ({$account['account_number']}) - Already exists");
            }
        }

        $this->command->info("\n🎉 Additional Bank Accounts Seeder completed successfully!");
        $this->command->info("📊 Total accounts processed: " . count($bankAccounts));
        
        // Display summary
        $totalAccounts = DB::table('bank_accounts')->count();
        $this->command->info("📈 Total bank accounts in database: {$totalAccounts}");
        
        // Show all accounts
        $this->command->info("\n📋 All Bank Accounts:");
        $allAccounts = DB::table('bank_accounts')
            ->select('bank_name', 'account_name', 'account_number', 'account_type', 'current_balance')
            ->get();
            
        foreach ($allAccounts as $acc) {
            $this->command->info("  • {$acc->bank_name} - {$acc->account_name} ({$acc->account_number}) - {$acc->account_type} - Balance: " . number_format($acc->current_balance, 2) . " TZS");
        }
    }
}
