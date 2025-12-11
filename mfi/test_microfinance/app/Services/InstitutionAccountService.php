<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class InstitutionAccountService
{
    protected $institution;
    protected $institutionId = 1; // Default institution ID
    
    public function __construct()
    {
        $this->loadInstitution();
    }
    
    protected function loadInstitution()
    {
        $this->institution = Cache::remember('institution_accounts', 3600, function () {
            return DB::table('institutions')
                ->where('id', $this->institutionId)
                ->first();
        });
    }
    
    /**
     * Get balance for a specific account type from the institution configuration
     */
    public function getAccountBalance($accountField, $asOfDate = null)
    {
        if (!$this->institution || !isset($this->institution->$accountField)) {
            return 0;
        }
        
        $accountNumber = $this->institution->$accountField;
        
        if (empty($accountNumber)) {
            return 0;
        }
        
        // Get balance from accounts table using account_number
        $query = DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->where('status', 'ACTIVE');
            
        return $query->value('balance') ?? 0;
    }
    
    /**
     * Get balance for multiple accounts
     */
    public function getMultipleAccountBalances(array $accountFields, $asOfDate = null)
    {
        $totalBalance = 0;
        
        foreach ($accountFields as $field) {
            $totalBalance += $this->getAccountBalance($field, $asOfDate);
        }
        
        return $totalBalance;
    }
    
    /**
     * Get all revenue account balances
     */
    public function getRevenueAccounts($startDate = null, $endDate = null)
    {
        $revenueFields = [
            'fee_income_account',
            'other_income_account',
            'interest_income_account'
        ];
        
        $revenues = [];
        foreach ($revenueFields as $field) {
            if ($this->institution && !empty($this->institution->$field)) {
                $balance = $this->getAccountBalance($field);
                if ($balance != 0) {
                    $revenues[] = [
                        'account_name' => str_replace('_account', '', ucwords(str_replace('_', ' ', $field))),
                        'amount' => abs($balance) // Revenue is typically credit balance (negative in accounting)
                    ];
                }
            }
        }
        
        return $revenues;
    }
    
    /**
     * Get all expense account balances
     */
    public function getExpenseAccounts($startDate = null, $endDate = null)
    {
        $expenseFields = [
            'interest_expense_account',
            'deposit_interest_account',
            'loan_loss_provision_account',
            'operating_expenses_account',
            'administrative_expenses_account',
            'personnel_expenses_account',
            'depreciation_expense_account'
        ];
        
        $expenses = [];
        foreach ($expenseFields as $field) {
            if ($this->institution && !empty($this->institution->$field)) {
                $balance = $this->getAccountBalance($field);
                if ($balance != 0) {
                    $expenses[] = [
                        'account_name' => str_replace('_account', '', ucwords(str_replace('_', ' ', $field))),
                        'amount' => abs($balance) // Expenses are typically debit balance (positive)
                    ];
                }
            }
        }
        
        return $expenses;
    }
    
    /**
     * Clear cache when institution accounts are updated
     */
    public function clearCache()
    {
        Cache::forget('institution_accounts');
        $this->loadInstitution();
    }
}