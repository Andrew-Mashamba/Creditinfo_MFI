<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use App\Models\AccountsModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ValidateServiceAccounts extends Command
{
    protected $signature = 'services:validate-accounts {--fix : Attempt to fix invalid accounts}';
    protected $description = 'Validate that all services have valid debit and credit account numbers';

    public function handle()
    {
        $this->info('Starting service account validation...');
        
        $services = Service::all();
        $invalidServices = [];
        $fixedServices = [];
        
        foreach ($services as $service) {
            $this->info("\nChecking service: {$service->name} (Code: {$service->code})");
            
            // Validate debit account
            $debitAccount = AccountsModel::where('account_number', $service->debit_account)->first();
            if (!$debitAccount) {
                $this->error("Debit account {$service->debit_account} not found in sub_accounts table");
                
                // Try to find a matching account by name
                $potentialDebitAccount = AccountsModel::where('account_name', 'like', '%' . $service->name . '%')
                    ->orWhere('account_name', 'like', '%' . $service->code . '%')
                    ->first();
                
                if ($potentialDebitAccount && $this->option('fix')) {
                    $this->info("Found potential matching debit account: {$potentialDebitAccount->account_number} ({$potentialDebitAccount->account_name})");
                    $service->debit_account = $potentialDebitAccount->account_number;
                    $service->save();
                    $fixedServices[] = [
                        'service' => $service->name,
                        'type' => 'debit',
                        'old_value' => $service->debit_account,
                        'new_value' => $potentialDebitAccount->account_number
                    ];
                }
            } else {
                $this->info("✓ Debit account {$service->debit_account} ({$debitAccount->account_name}) is valid");
            }
            
            // Validate credit account
            $creditAccount = AccountsModel::where('account_number', $service->credit_account)->first();
            if (!$creditAccount) {
                $this->error("Credit account {$service->credit_account} not found in accounts table");
                
                // Try to find a matching account by name
                $potentialCreditAccount = AccountsModel::where('account_name', 'like', '%' . $service->name . '%')
                    ->orWhere('account_name', 'like', '%' . $service->code . '%')
                    ->first();
                
                if ($potentialCreditAccount && $this->option('fix')) {
                    $this->info("Found potential matching credit account: {$potentialCreditAccount->account_number} ({$potentialCreditAccount->account_name})");
                    $service->credit_account = $potentialCreditAccount->account_number;
                    $service->save();
                    $fixedServices[] = [
                        'service' => $service->name,
                        'type' => 'credit',
                        'old_value' => $service->credit_account,
                        'new_value' => $potentialCreditAccount->account_number
                    ];
                }
            } else {
                $this->info("✓ Credit account {$service->credit_account} ({$creditAccount->account_name}) is valid");
            }
            
            if (!$debitAccount || !$creditAccount) {
                $invalidServices[] = [
                    'service_id' => $service->id,
                    'service_name' => $service->name,
                    'service_code' => $service->code,
                    'debit_account' => $service->debit_account,
                    'credit_account' => $service->credit_account,
                    'debit_account_found' => !is_null($debitAccount),
                    'credit_account_found' => !is_null($creditAccount)
                ];
            }
        }
        
        // Print summary
        $this->newLine();
        $this->info('Validation Summary:');
        $this->info('------------------');
        $this->info("Total services checked: " . count($services));
        $this->info("Invalid services: " . count($invalidServices));
        
        if (count($fixedServices) > 0) {
            $this->info("\nFixed Services:");
            foreach ($fixedServices as $fix) {
                $this->info("- {$fix['service']}: {$fix['type']} account updated from {$fix['old_value']} to {$fix['new_value']}");
            }
        }
        
        if (count($invalidServices) > 0) {
            $this->error("\nInvalid Services:");
            foreach ($invalidServices as $invalid) {
                $this->error("- {$invalid['service_name']} (Code: {$invalid['service_code']})");
                $this->error("  Debit: {$invalid['debit_account']} (" . ($invalid['debit_account_found'] ? 'Found' : 'Not Found') . ")");
                $this->error("  Credit: {$invalid['credit_account']} (" . ($invalid['credit_account_found'] ? 'Found' : 'Not Found') . ")");
            }
            
            Log::error('Service account validation failed', ['invalid_services' => $invalidServices]);
            
            if (!$this->option('fix')) {
                $this->info("\nTo attempt automatic fixes, run: php artisan services:validate-accounts --fix");
            }
            
            return 1;
        }
        
        $this->info("\n✓ All services have valid account numbers!");
        return 0;
    }
} 