<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Service to generate financial statements directly from the accounts table
 * using the hierarchical account structure
 */
class AccountsBasedFinancialStatementService
{
    /**
     * Generate Statement of Financial Position from accounts table
     */
    public function getStatementOfFinancialPosition($asOfDate = null)
    {
        $asOfDate = $asOfDate ?: Carbon::now()->format('Y-m-d');
        
        try {
            // Get main account categories (Level 1)
            $assets = $this->getAccountBalance('010110000000', $asOfDate);
            $liabilities = $this->getAccountBalance('010120000000', $asOfDate);
            $equity = $this->getAccountBalance('010130000000', $asOfDate);
            $revenue = $this->getAccountBalance('010140000000', $asOfDate);
            $expenses = $this->getAccountBalance('010150000000', $asOfDate);
            
            // Calculate net income (Revenue - Expenses)
            $netIncome = abs($revenue) - $expenses;
            
            // Adjust equity with net income
            $totalEquity = $equity + $netIncome;
            
            // Get detailed breakdown
            $currentAssets = $this->getCurrentAssets($asOfDate);
            $nonCurrentAssets = $this->getNonCurrentAssets($asOfDate);
            $currentLiabilities = $this->getCurrentLiabilities($asOfDate);
            $nonCurrentLiabilities = $this->getNonCurrentLiabilities($asOfDate);
            $equityDetails = $this->getEquityDetails($asOfDate, $netIncome);
            
            // Format data to match blade template expectations
            $formattedCurrentAssets = [];
            foreach ($currentAssets['components'] as $key => $component) {
                $formattedCurrentAssets[] = [
                    'account_name' => $component['description'],
                    'account_number' => $component['account_number'] ?? '',
                    'amount' => $component['amount']
                ];
            }
            
            $formattedNonCurrentAssets = [];
            foreach ($nonCurrentAssets['components'] as $key => $component) {
                $formattedNonCurrentAssets[] = [
                    'account_name' => $component['description'],
                    'account_number' => $component['account_number'] ?? '',
                    'amount' => $component['amount']
                ];
            }
            
            $formattedCurrentLiabilities = [];
            foreach ($currentLiabilities['components'] as $key => $component) {
                $formattedCurrentLiabilities[] = [
                    'account_name' => $component['description'],
                    'account_number' => $component['account_number'] ?? '',
                    'amount' => $component['amount']
                ];
            }
            
            $formattedNonCurrentLiabilities = [];
            foreach ($nonCurrentLiabilities['components'] as $key => $component) {
                $formattedNonCurrentLiabilities[] = [
                    'account_name' => $component['description'],
                    'account_number' => $component['account_number'] ?? '',
                    'amount' => $component['amount']
                ];
            }
            
            $formattedEquity = [];
            foreach ($equityDetails as $key => $component) {
                $formattedEquity[] = [
                    'account_name' => $component['description'],
                    'account_number' => $component['account_number'] ?? '',
                    'amount' => $component['amount']
                ];
            }
            
            return [
                'as_of_date' => $asOfDate,
                'assets' => [
                    'current' => $formattedCurrentAssets,
                    'non_current' => $formattedNonCurrentAssets,
                    'current_total' => $currentAssets['total'],
                    'non_current_total' => $nonCurrentAssets['total'],
                    'total' => $assets
                ],
                'liabilities' => [
                    'current' => $formattedCurrentLiabilities,
                    'non_current' => $formattedNonCurrentLiabilities,
                    'current_total' => $currentLiabilities['total'],
                    'non_current_total' => $nonCurrentLiabilities['total'],
                    'total' => $liabilities
                ],
                'equity' => [
                    'items' => $formattedEquity,
                    'total' => $totalEquity
                ],
                'total_assets' => $assets,
                'total_liabilities' => $liabilities,
                'total_equity' => $totalEquity,
                'total_liabilities_equity' => $liabilities + $totalEquity,
                'is_balanced' => abs($assets - ($liabilities + $totalEquity)) < 0.01
            ];
            
        } catch (Exception $e) {
            Log::error('Error generating Statement of Financial Position: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get current assets breakdown
     */
    private function getCurrentAssets($asOfDate)
    {
        $components = [];
        $total = 0;
        
        // Cash and Cash Equivalents (010110001000)
        $cash = $this->getAccountBalance('010110001000', $asOfDate);
        if ($cash > 0) {
            $components['cash_and_equivalents'] = [
                'description' => 'Cash and Cash Equivalents',
                'account_number' => '010110001000',
                'amount' => $cash,
                'details' => $this->getSubAccountDetails('010110001000', $asOfDate)
            ];
            $total += $cash;
        }
        
        // Short-term Investments (010110001100)
        $shortTermInv = $this->getAccountBalance('010110001100', $asOfDate);
        if ($shortTermInv > 0) {
            $components['short_term_investments'] = [
                'description' => 'Short-term Investments',
                'account_number' => '010110001100',
                'amount' => $shortTermInv
            ];
            $total += $shortTermInv;
        }
        
        // Loan Portfolio - Current (010110001200)
        $loans = $this->getAccountBalance('010110001200', $asOfDate);
        if ($loans > 0) {
            $components['loan_portfolio'] = [
                'description' => 'Loan Portfolio (Current)',
                'amount' => $loans
            ];
            $total += $loans;
        }
        
        // Interest Receivable (010110001400)
        $interestRec = $this->getAccountBalance('010110001400', $asOfDate);
        if ($interestRec > 0) {
            $components['interest_receivable'] = [
                'description' => 'Interest Receivable',
                'amount' => $interestRec
            ];
            $total += $interestRec;
        }
        
        // Accounts Receivable (010110001500)
        $receivables = $this->getAccountBalance('010110001500', $asOfDate);
        if ($receivables > 0) {
            $components['accounts_receivable'] = [
                'description' => 'Accounts Receivable',
                'amount' => $receivables,
                'details' => $this->getSubAccountDetails('010110001500', $asOfDate)
            ];
            $total += $receivables;
        }
        
        // Prepaid Expenses (010110001800)
        $prepaid = $this->getAccountBalance('010110001800', $asOfDate);
        if ($prepaid > 0) {
            $components['prepaid_expenses'] = [
                'description' => 'Prepaid Expenses',
                'amount' => $prepaid
            ];
            $total += $prepaid;
        }
        
        return [
            'components' => $components,
            'total' => $total
        ];
    }
    
    /**
     * Get non-current assets breakdown
     */
    private function getNonCurrentAssets($asOfDate)
    {
        $components = [];
        $total = 0;
        
        // Property and Equipment (010110001600)
        $ppe = $this->getAccountBalance('010110001600', $asOfDate);
        if ($ppe > 0) {
            $components['property_equipment'] = [
                'description' => 'Property and Equipment',
                'amount' => $ppe
            ];
            $total += $ppe;
        }
        
        // Long-term Investments (010110001700)
        $longTermInv = $this->getAccountBalance('010110001700', $asOfDate);
        if ($longTermInv > 0) {
            $components['long_term_investments'] = [
                'description' => 'Long-term Investments',
                'amount' => $longTermInv
            ];
            $total += $longTermInv;
        }
        
        // Other Assets (010110001900)
        $otherAssets = $this->getAccountBalance('010110001900', $asOfDate);
        if ($otherAssets > 0) {
            $components['other_assets'] = [
                'description' => 'Other Assets',
                'amount' => $otherAssets
            ];
            $total += $otherAssets;
        }
        
        // Loan Loss Provisions (negative asset) (010110001300)
        $provisions = $this->getAccountBalance('010110001300', $asOfDate);
        if ($provisions != 0) {
            $components['loan_loss_provisions'] = [
                'description' => 'Loan Loss Provisions',
                'amount' => -abs($provisions) // Should be negative
            ];
            $total -= abs($provisions);
        }
        
        return [
            'components' => $components,
            'total' => $total
        ];
    }
    
    /**
     * Get current liabilities breakdown
     */
    private function getCurrentLiabilities($asOfDate)
    {
        $components = [];
        $total = 0;
        
        // Member Deposits (010120002100)
        $deposits = $this->getAccountBalance('010120002100', $asOfDate);
        if ($deposits > 0) {
            $components['member_deposits'] = [
                'description' => 'Member Deposits',
                'amount' => $deposits
            ];
            $total += $deposits;
        }
        
        // Short-term Debt (010120002200)
        $shortTermDebt = $this->getAccountBalance('010120002200', $asOfDate);
        if ($shortTermDebt > 0) {
            $components['short_term_debt'] = [
                'description' => 'Short-term Debt',
                'amount' => $shortTermDebt
            ];
            $total += $shortTermDebt;
        }
        
        // Accounts Payable (010120002400)
        $payables = $this->getAccountBalance('010120002400', $asOfDate);
        if ($payables > 0) {
            $components['accounts_payable'] = [
                'description' => 'Accounts Payable',
                'amount' => $payables
            ];
            $total += $payables;
        }
        
        // Accrued Liabilities (010120002500)
        $accrued = $this->getAccountBalance('010120002500', $asOfDate);
        if ($accrued > 0) {
            $components['accrued_liabilities'] = [
                'description' => 'Accrued Liabilities',
                'amount' => $accrued,
                'details' => $this->getSubAccountDetails('010120002500', $asOfDate)
            ];
            $total += $accrued;
        }
        
        // Deferred Revenue (010120002600)
        $deferred = $this->getAccountBalance('010120002600', $asOfDate);
        if ($deferred > 0) {
            $components['deferred_revenue'] = [
                'description' => 'Deferred Revenue',
                'amount' => $deferred
            ];
            $total += $deferred;
        }
        
        // Other Liabilities (010120002900)
        $otherLiab = $this->getAccountBalance('010120002900', $asOfDate);
        if ($otherLiab > 0) {
            $components['other_liabilities'] = [
                'description' => 'Other Liabilities',
                'amount' => $otherLiab
            ];
            $total += $otherLiab;
        }
        
        return [
            'components' => $components,
            'total' => $total
        ];
    }
    
    /**
     * Get non-current liabilities breakdown
     */
    private function getNonCurrentLiabilities($asOfDate)
    {
        $components = [];
        $total = 0;
        
        // Long-term Debt (010120002300)
        $longTermDebt = $this->getAccountBalance('010120002300', $asOfDate);
        if ($longTermDebt > 0) {
            $components['long_term_debt'] = [
                'description' => 'Long-term Debt',
                'amount' => $longTermDebt
            ];
            $total += $longTermDebt;
        }
        
        // Deferred Grants (010120002700)
        $deferredGrants = $this->getAccountBalance('010120002700', $asOfDate);
        if ($deferredGrants > 0) {
            $components['deferred_grants'] = [
                'description' => 'Deferred Grants',
                'amount' => $deferredGrants
            ];
            $total += $deferredGrants;
        }
        
        // Provisions (010120002800)
        $provisions = $this->getAccountBalance('010120002800', $asOfDate);
        if ($provisions > 0) {
            $components['provisions'] = [
                'description' => 'Provisions',
                'amount' => $provisions
            ];
            $total += $provisions;
        }
        
        return [
            'components' => $components,
            'total' => $total
        ];
    }
    
    /**
     * Get equity breakdown
     */
    private function getEquityDetails($asOfDate, $netIncome)
    {
        $components = [];
        
        // Member Share Capital (010130003000)
        $shareCapital = $this->getAccountBalance('010130003000', $asOfDate);
        if ($shareCapital != 0) {
            $components['share_capital'] = [
                'description' => 'Member Share Capital',
                'amount' => $shareCapital
            ];
        }
        
        // Retained Earnings (010130003100)
        $retained = $this->getAccountBalance('010130003100', $asOfDate);
        if ($retained != 0) {
            $components['retained_earnings'] = [
                'description' => 'Retained Earnings',
                'amount' => $retained
            ];
        }
        
        // Current Year Net Income
        if ($netIncome != 0) {
            $components['net_income'] = [
                'description' => 'Net Income (Current Year)',
                'amount' => $netIncome
            ];
        }
        
        // Reserves (010130003200)
        $reserves = $this->getAccountBalance('010130003200', $asOfDate);
        if ($reserves != 0) {
            $components['reserves'] = [
                'description' => 'Reserves',
                'amount' => $reserves
            ];
        }
        
        // Donated Capital (010130003300)
        $donated = $this->getAccountBalance('010130003300', $asOfDate);
        if ($donated != 0) {
            $components['donated_capital'] = [
                'description' => 'Donated Capital',
                'amount' => $donated
            ];
        }
        
        // Revaluation Reserves (010130003400)
        $revaluation = $this->getAccountBalance('010130003400', $asOfDate);
        if ($revaluation != 0) {
            $components['revaluation_reserves'] = [
                'description' => 'Revaluation Reserves',
                'amount' => $revaluation
            ];
        }
        
        // Other Equity (010130003500)
        $otherEquity = $this->getAccountBalance('010130003500', $asOfDate);
        if ($otherEquity != 0) {
            $components['other_equity'] = [
                'description' => 'Other Equity',
                'amount' => $otherEquity
            ];
        }
        
        return $components;
    }
    
    /**
     * Get account balance for a specific account only (not children)
     */
    private function getAccountBalance($accountNumber, $asOfDate)
    {
        // Get just the specific account balance
        $account = DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->where('account_use', 'internal')
            ->first();
        
        return $account ? $account->balance : 0;
    }
    
    /**
     * Get details of sub-accounts for a parent account
     */
    private function getSubAccountDetails($parentAccount, $asOfDate)
    {
        $subAccounts = DB::table('accounts')
            ->where('parent_account_number', $parentAccount)
            ->where('balance', '!=', 0)
            ->where('account_use', 'internal')
            ->select('account_name', 'balance')
            ->get();
        
        $details = [];
        foreach ($subAccounts as $account) {
            $details[$account->account_name] = $account->balance;
        }
        
        return $details;
    }
    
    /**
     * Generate Income Statement from accounts
     */
    public function getIncomeStatement($startDate, $endDate)
    {
        try {
            // Get revenue accounts (010140000000)
            $totalRevenue = abs($this->getAccountBalance('010140000000', $endDate));
            $revenueDetails = $this->getRevenueDetails($endDate);
            
            // Get expense accounts (010150000000)
            $totalExpenses = $this->getAccountBalance('010150000000', $endDate);
            $expenseDetails = $this->getExpenseDetails($endDate);
            
            // Calculate net income
            $netIncome = $totalRevenue - $totalExpenses;
            
            return [
                'period_start' => $startDate,
                'period_end' => $endDate,
                'revenue' => [
                    'components' => $revenueDetails,
                    'total' => $totalRevenue
                ],
                'expenses' => [
                    'components' => $expenseDetails,
                    'total' => $totalExpenses
                ],
                'net_income' => $netIncome,
                'profitability_metrics' => [
                    'gross_margin' => $totalRevenue > 0 ? (($totalRevenue - $totalExpenses) / $totalRevenue) * 100 : 0,
                    'expense_ratio' => $totalRevenue > 0 ? ($totalExpenses / $totalRevenue) * 100 : 0
                ]
            ];
            
        } catch (Exception $e) {
            Log::error('Error generating Income Statement: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get revenue breakdown
     */
    private function getRevenueDetails($asOfDate)
    {
        $components = [];
        
        // Get all Level 2 revenue accounts
        $revenueAccounts = DB::table('accounts')
            ->where('parent_account_number', '010140000000')
            ->where('account_level', '2')
            ->get();
        
        foreach ($revenueAccounts as $account) {
            $balance = $this->getAccountBalance($account->account_number, $asOfDate);
            if ($balance != 0) {
                $components[strtolower(str_replace(' ', '_', $account->account_name))] = [
                    'description' => $account->account_name,
                    'amount' => abs($balance) // Revenue is typically credit (negative)
                ];
            }
        }
        
        return $components;
    }
    
    /**
     * Get expense breakdown
     */
    private function getExpenseDetails($asOfDate)
    {
        $components = [];
        
        // Get all Level 2 expense accounts
        $expenseAccounts = DB::table('accounts')
            ->where('parent_account_number', '010150000000')
            ->where('account_level', '2')
            ->get();
        
        foreach ($expenseAccounts as $account) {
            $balance = $this->getAccountBalance($account->account_number, $asOfDate);
            if ($balance != 0) {
                $components[strtolower(str_replace(' ', '_', $account->account_name))] = [
                    'description' => $account->account_name,
                    'amount' => $balance,
                    'details' => $balance > 100000 ? $this->getSubAccountDetails($account->account_number, $asOfDate) : null
                ];
            }
        }
        
        return $components;
    }
    
    /**
     * Get trial balance
     */
    public function getTrialBalance($asOfDate = null)
    {
        $asOfDate = $asOfDate ?: Carbon::now()->format('Y-m-d');
        
        // Get all Level 1 and Level 2 accounts with balances
        $accounts = DB::table('accounts')
            ->whereIn('account_level', ['1', '2'])
            ->where('account_use', 'internal')
            ->orderBy('account_number')
            ->get();
        
        $trialBalance = [
            'as_of_date' => $asOfDate,
            'accounts' => [],
            'totals' => [
                'debit_total' => 0,
                'credit_total' => 0
            ]
        ];
        
        foreach ($accounts as $account) {
            $balance = $this->getAccountBalance($account->account_number, $asOfDate);
            
            if ($balance != 0) {
                $isDebit = in_array($account->type, ['asset_accounts', 'expense_accounts']);
                
                $trialBalance['accounts'][] = [
                    'account_number' => $account->account_number,
                    'account_name' => $account->account_name,
                    'account_type' => $account->type,
                    'debit' => $isDebit && $balance > 0 ? $balance : 0,
                    'credit' => !$isDebit && $balance > 0 ? $balance : ($isDebit && $balance < 0 ? abs($balance) : 0)
                ];
                
                if ($isDebit && $balance > 0) {
                    $trialBalance['totals']['debit_total'] += $balance;
                } else {
                    $trialBalance['totals']['credit_total'] += abs($balance);
                }
            }
        }
        
        $trialBalance['is_balanced'] = abs($trialBalance['totals']['debit_total'] - $trialBalance['totals']['credit_total']) < 0.01;
        
        return $trialBalance;
    }
}