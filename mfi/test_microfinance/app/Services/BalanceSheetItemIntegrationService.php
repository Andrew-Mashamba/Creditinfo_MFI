<?php

namespace App\Services;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service to ensure all balance sheet items are properly reflected in the accounts table
 * and that the accounts table is the single source of truth for financial statements
 */
class BalanceSheetItemIntegrationService
{
    private $accountCreationService;
    private $transactionPostingService;
    
    // Account type mappings for balance sheet items
    private const ACCOUNT_TYPE_MAPPINGS = [
        'ppe' => 'asset_accounts',
        'trade_receivables' => 'asset_accounts',
        'investments' => 'asset_accounts',
        'loan_portfolio' => 'asset_accounts',
        'cash' => 'asset_accounts',
        'trade_payables' => 'liability_accounts',
        'creditors' => 'liability_accounts',
        'interest_payable' => 'liability_accounts',
        'borrowings' => 'liability_accounts',
        'unearned_revenue' => 'liability_accounts',
        'insurance_liabilities' => 'liability_accounts',
        'member_deposits' => 'liability_accounts',
        'share_capital' => 'capital_accounts',
        'retained_earnings' => 'capital_accounts'
    ];
    
    // Sub-category codes for different balance sheet items
    private const SUB_CATEGORY_CODES = [
        'ppe' => '1600',
        'accumulated_depreciation' => '1601',
        'trade_receivables' => '1500',
        'bad_debt_provision' => '1501',
        'short_term_investments' => '1100',
        'long_term_investments' => '1700',
        'loan_portfolio_current' => '1200',
        'loan_portfolio_long_term' => '1201',
        'cash_and_equivalents' => '1000',
        'trade_payables' => '2400',
        'creditors' => '2401',
        'interest_payable' => '2500',
        'current_borrowings' => '2200',
        'long_term_borrowings' => '2300',
        'unearned_revenue' => '2600',
        'insurance_liabilities' => '2700',
        'member_deposits' => '2100',
        'share_capital' => '3000',
        'retained_earnings' => '3100'
    ];
    
    public function __construct()
    {
        $this->accountCreationService = new AccountCreationService();
        $this->transactionPostingService = new TransactionPostingService();
    }
    
    /**
     * Create or update an account for a PPE asset
     * @param object $ppeAsset The PPE asset object
     * @param string|null $debitAccountId Custom debit account ID
     * @param string|null $creditAccountId Custom credit account ID
     */
    public function createPPEAccount($ppeAsset, $parentAccountNumber = null, $otherAccountId = null)
    {
        try {
            DB::beginTransaction();
            
            // Check if account already exists for this PPE
            $accountNumber = $this->generatePPEAccountNumber($ppeAsset);
            $existingAccount = AccountsModel::where('account_number', $accountNumber)->first();
            
            if (!$existingAccount) {
                // Create PPE asset account using ONLY AccountCreationService
                $accountData = [
                    'account_use' => 'internal',
                    'account_name' => 'PPE - ' . $ppeAsset->asset_name,
                    'type' => 'asset_accounts',
                    'major_category_code' => '1000',
                    'category_code' => '1600',
                    'sub_category_code' => self::SUB_CATEGORY_CODES['ppe'],
                    'product_number' => self::SUB_CATEGORY_CODES['ppe'],
                    'branch_number' => auth()->user()->branch ?? '01',
                    'account_level' => 3,
                    'balance' => $ppeAsset->cost,
                    'status' => 'ACTIVE'
                ];
                
                // Create account under user-selected parent
                $assetAccount = $this->accountCreationService->createAccount($accountData, $parentAccountNumber);
                
                // Create accumulated depreciation account under same parent
                $depreciationAccountData = [
                    'account_use' => 'internal',
                    'account_name' => 'Accumulated Depreciation - ' . $ppeAsset->asset_name,
                    'type' => 'asset_accounts', // Contra-asset
                    'major_category_code' => '1000',
                    'category_code' => '1600',
                    'sub_category_code' => self::SUB_CATEGORY_CODES['accumulated_depreciation'],
                    'product_number' => self::SUB_CATEGORY_CODES['accumulated_depreciation'],
                    'branch_number' => auth()->user()->branch ?? '01',
                    'account_level' => 3,
                    'balance' => 0,
                    'status' => 'ACTIVE'
                ];
                
                // Create depreciation account under same parent
                $depreciationAccount = $this->accountCreationService->createAccount($depreciationAccountData, $parentAccountNumber);
                
                // Store both asset and depreciation account references in PPE record
                if (isset($ppeAsset->id)) {
                    // Update the PPE record with both account numbers
                    \App\Models\PPE::where('id', $ppeAsset->id)->update([
                        'account_number' => $assetAccount->account_number,
                        'depreciation_account_number' => $depreciationAccount->account_number
                    ]);
                    
                    Log::info('Updated PPE record with account numbers', [
                        'ppe_id' => $ppeAsset->id,
                        'asset_account' => $assetAccount->account_number,
                        'depreciation_account' => $depreciationAccount->account_number
                    ]);
                }
                
                // Post initial transaction to GL: newly created PPE account + user-selected other account
                $this->postPPEAcquisition($assetAccount, $ppeAsset->cost, $otherAccountId);
                
                Log::info('PPE accounts created', [
                    'asset_id' => $ppeAsset->id,
                    'asset_account' => $assetAccount->account_number,
                    'depreciation_account' => $depreciationAccount->account_number
                ]);
            } else {
                // Update existing account balance if needed
                $this->updateAccountBalance($existingAccount, $ppeAsset->cost);
            }
            
            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create PPE account: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Process depreciation and update accounts
     */
    public function processDepreciation($ppeAsset, $depreciationAmount)
    {
        try {
            DB::beginTransaction();
            
            // Get the depreciation account
            $depreciationAccount = AccountsModel::where('account_number', $ppeAsset->depreciation_account_number)->first();
            
            if (!$depreciationAccount) {
                throw new Exception('Depreciation account not found for asset: ' . $ppeAsset->id);
            }
            
            // Update depreciation account balance (increase as it's a contra-asset)
            $depreciationAccount->balance += $depreciationAmount;
            $depreciationAccount->save();
            
            // Post to GL - Debit: Depreciation Expense, Credit: Accumulated Depreciation
            $this->postDepreciation($depreciationAccount, $depreciationAmount);
            
            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to process depreciation: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create or update account for Trade Receivables
     * @param object $receivable The receivable object
     * @param string|null $debitAccountId Custom debit account ID
     * @param string|null $creditAccountId Custom credit account ID
     */
    public function createTradeReceivableAccount($receivable, $parentAccountNumber = null, $otherAccountId = null)
    {
        try {
            DB::beginTransaction();
            
            // Get or create trade receivable account under user-selected parent
            $mainAccount = $this->getOrCreateMainAccount(
                'trade_receivables',
                'Trade Receivable - ' . ($receivable->customer_name ?? 'Customer'),
                self::SUB_CATEGORY_CODES['trade_receivables'],
                $parentAccountNumber
            );
            
            // Update account balance
            $this->updateAccountBalance($mainAccount, $receivable->amount);
            
            // Post to GL: newly created receivable account + user-selected other account
            $this->postTradeReceivable($mainAccount, $receivable, $otherAccountId);
            
            DB::commit();
            return $mainAccount;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create trade receivable account: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Process bad debt provision
     */
    public function processBadDebtProvision($amount)
    {
        try {
            DB::beginTransaction();
            
            // Get or create bad debt provision account (contra-asset)
            $provisionAccount = $this->getOrCreateMainAccount(
                'bad_debt_provision',
                'Provision for Bad Debts',
                self::SUB_CATEGORY_CODES['bad_debt_provision']
            );
            
            // Update provision account balance
            $provisionAccount->balance += $amount;
            $provisionAccount->save();
            
            // Post to GL - Debit: Bad Debt Expense, Credit: Provision for Bad Debts
            $this->postBadDebtProvision($provisionAccount, $amount);
            
            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to process bad debt provision: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create or update account for Trade Payables
     * @param object $payable The payable object
     * @param string|null $parentAccountNumber Parent account to create payable account under
     * @param string|null $otherAccountId The other account for double-entry (Expense/Inventory - debit side)
     */
    public function createTradePayableAccount($payable, $parentAccountNumber = null, $otherAccountId = null)
    {
        try {
            DB::beginTransaction();
            
            // Create individual payable account using ONLY AccountCreationService
            $accountData = [
                'account_use' => 'internal',
                'account_name' => 'Payable - ' . $payable->vendor_name,
                'type' => 'liability_accounts',
                'major_category_code' => '2000',
                'category_code' => '2400',
                'sub_category_code' => self::SUB_CATEGORY_CODES['trade_payables'],
                'product_number' => self::SUB_CATEGORY_CODES['trade_payables'],
                'branch_number' => auth()->user()->branch ?? '01',
                'account_level' => 3,
                'balance' => $payable->amount,
                'status' => 'ACTIVE'
            ];
            
            // Create account under user-selected parent
            $payableAccount = $this->accountCreationService->createAccount($accountData, $parentAccountNumber);
            
            // Post initial transaction: newly created payable account + user-selected other account
            $this->postTradePayable($payableAccount, $payable, $otherAccountId);
            
            DB::commit();
            return $payableAccount;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create trade payable account: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create or update account for Investments
     * @param object $investment The investment object
     * @param string|null $debitAccountId Custom debit account ID
     * @param string|null $creditAccountId Custom credit account ID
     */
    public function createInvestmentAccount($investment, $debitAccountId = null, $creditAccountId = null)
    {
        try {
            DB::beginTransaction();
            
            // Determine if short-term or long-term
            $isShortTerm = $investment->maturity_date && 
                           \Carbon\Carbon::parse($investment->maturity_date)->diffInDays(now()) <= 365;
            
            $subCategoryCode = $isShortTerm ? 
                self::SUB_CATEGORY_CODES['short_term_investments'] : 
                self::SUB_CATEGORY_CODES['long_term_investments'];
            
            $accountName = $isShortTerm ? 'Short-term Investments' : 'Long-term Investments';
            
            // Get or create investment account
            $mainAccount = $this->getOrCreateMainAccount(
                $isShortTerm ? 'short_term_investments' : 'long_term_investments',
                $accountName,
                $subCategoryCode
            );
            
            // Update account balance
            $this->updateAccountBalance($mainAccount, $investment->current_value ?? $investment->purchase_amount);
            
            // Post to GL with custom accounts if provided
            $this->postInvestment($mainAccount, $investment, $debitAccountId, $creditAccountId);
            
            DB::commit();
            return $mainAccount;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create investment account: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create or update account for Interest Payable
     * @param object $interestPayable The interest payable object
     * @param string|null $parentAccountNumber Parent account to create interest payable account under
     * @param string|null $otherAccountId The other account for double-entry (Expense - debit side)
     */
    public function createInterestPayableAccount($interestPayable, $parentAccountNumber = null, $otherAccountId = null)
    {
        try {
            DB::beginTransaction();
            
            // Create individual interest payable account using ONLY AccountCreationService
            $accountData = [
                'account_use' => 'internal',
                'account_name' => 'Interest Payable - ' . $interestPayable->description,
                'type' => 'liability_accounts',
                'major_category_code' => '2000',
                'category_code' => '2300',
                'sub_category_code' => self::SUB_CATEGORY_CODES['interest_payable'],
                'product_number' => self::SUB_CATEGORY_CODES['interest_payable'],
                'branch_number' => auth()->user()->branch ?? '01',
                'account_level' => 3,
                'balance' => $interestPayable->amount ?? 0,
                'status' => 'ACTIVE'
            ];
            
            // Create account under user-selected parent
            $interestPayableAccount = $this->accountCreationService->createAccount($accountData, $parentAccountNumber);
            
            // Post initial transaction: newly created interest payable account + user-selected other account
            $this->postInterestPayable($interestPayableAccount, $interestPayable, $otherAccountId);
            
            DB::commit();
            return $interestPayableAccount;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create interest payable account: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create or update account for Other Income
     */
    public function createOtherIncomeAccount($income, $parentAccountNumber = null, $otherAccountId = null)
    {
        try {
            DB::beginTransaction();
            
            // Create income account under user-selected parent using ONLY AccountCreationService
            $accountNumber = $this->generateOtherIncomeAccountNumber($income);
            $existingAccount = AccountsModel::where('account_number', $accountNumber)->first();
            
            if (!$existingAccount) {
                // Create other income account using ONLY AccountCreationService
                $accountData = [
                    'account_use' => 'internal',
                    'account_name' => 'Other Income - ' . ($income->income_source ?? 'Income #' . $income->id),
                    'type' => 'income_accounts',
                    'major_category_code' => '4000',
                    'category_code' => '4100',
                    'sub_category_code' => '4101',
                    'product_number' => '4101',
                    'branch_number' => auth()->user()->branch ?? '01',
                    'account_level' => 3,
                    'balance' => 0,
                    'status' => 'ACTIVE'
                ];
                
                // Create account under user-selected parent
                $incomeAccount = $this->accountCreationService->createAccount($accountData, $parentAccountNumber);
                
                // Update other_income record with account ID
                DB::table('other_incomes')->where('id', $income->id)
                    ->update(['account_id' => $incomeAccount->id]);
                
                // Post to GL using newly created income account and user-selected other account
                $this->postOtherIncome($incomeAccount, $otherAccountId, $income);
            } else {
                // Post to GL using existing account and user-selected other account
                $this->postOtherIncome($existingAccount, $otherAccountId, $income);
            }
            
            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create other income account: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create or update account for Unearned Revenue
     */
    public function createUnearnedRevenueAccount($unearnedRevenue, $parentAccountNumber = null, $otherAccountId = null)
    {
        try {
            DB::beginTransaction();
            
            // Create unearned revenue account using ONLY AccountCreationService
            $accountData = [
                'account_use' => 'internal',
                'account_name' => 'Unearned Revenue - ' . ($unearnedRevenue->customer_name ?? $unearnedRevenue->name ?? 'Customer'),
                'type' => 'liability_accounts',
                'major_category_code' => '2000',
                'category_code' => '2600',
                'sub_category_code' => self::SUB_CATEGORY_CODES['unearned_revenue'],
                'product_number' => self::SUB_CATEGORY_CODES['unearned_revenue'],
                'branch_number' => auth()->user()->branch ?? '01',
                'account_level' => 3,
                'balance' => $unearnedRevenue->amount ?? 0,
                'status' => 'ACTIVE'
            ];
            
            // Create account under user-selected parent
            $unearnedAccount = $this->accountCreationService->createAccount($accountData, $parentAccountNumber);
            
            // Update unearned_deferred_revenue record with account ID
            DB::table('unearned_deferred_revenue')->where('id', $unearnedRevenue->id)
                ->update(['account_id' => $unearnedAccount->id]);
            
            // Post to GL using newly created unearned account and user-selected other account
            $this->postUnearnedRevenue($unearnedAccount, $otherAccountId, $unearnedRevenue);
            
            DB::commit();
            return $unearnedAccount;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create unearned revenue account: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create or update account for Loan Outstanding
     */
    public function createLoanOutstandingAccount($loan)
    {
        try {
            DB::beginTransaction();
            
            // Determine if current or long-term portion
            $isCurrent = $loan->maturity_date && 
                        \Carbon\Carbon::parse($loan->maturity_date)->diffInDays(now()) <= 365;
            
            $subCategoryCode = $isCurrent ? 
                self::SUB_CATEGORY_CODES['loan_portfolio_current'] : 
                self::SUB_CATEGORY_CODES['loan_portfolio_long_term'];
            
            $accountName = $isCurrent ? 'Loan Portfolio - Current' : 'Loan Portfolio - Long Term';
            
            // Get or create loan portfolio account
            $mainAccount = $this->getOrCreateMainAccount(
                $isCurrent ? 'loan_portfolio_current' : 'loan_portfolio_long_term',
                $accountName,
                $subCategoryCode
            );
            
            // Update account balance
            $this->updateAccountBalance($mainAccount, $loan->outstanding_principal);
            
            // Post to GL
            $this->postLoanOutstanding($mainAccount, $loan);
            
            DB::commit();
            return $mainAccount;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create loan outstanding account: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create or update account for Creditors
     * @param object $creditor The creditor object
     * @param string|null $parentAccountNumber Parent account to create creditor account under
     * @param string|null $otherAccountId The other account for double-entry (Cash/Expense - debit side)
     */
    public function createCreditorAccount($creditor, $parentAccountNumber = null, $otherAccountId = null)
    {
        try {
            DB::beginTransaction();
            
            // Create individual creditor account using ONLY AccountCreationService
            $accountData = [
                'account_use' => 'internal',
                'account_name' => 'Creditor - ' . $creditor->name,
                'type' => 'liability_accounts',
                'major_category_code' => '2000',
                'category_code' => '2401',
                'sub_category_code' => self::SUB_CATEGORY_CODES['creditors'],
                'product_number' => self::SUB_CATEGORY_CODES['creditors'],
                'branch_number' => auth()->user()->branch ?? '01',
                'account_level' => 3,
                'balance' => $creditor->current_balance ?? 0,
                'status' => 'ACTIVE'
            ];
            
            // Create account under user-selected parent
            $creditorAccount = $this->accountCreationService->createAccount($accountData, $parentAccountNumber);
            
            // Post initial transaction: newly created creditor account + user-selected other account
            $this->postCreditor($creditorAccount, $creditor, $otherAccountId);
            
            DB::commit();
            return $creditorAccount;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create creditor account: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create or update account for Insurance Liabilities
     */
    public function createInsuranceLiabilityAccount($insurance, $parentAccountNumber = null, $otherAccountId = null)
    {
        try {
            DB::beginTransaction();
            
            // Get or create insurance liability account under user-selected parent
            $mainAccount = $this->getOrCreateMainAccount(
                'insurance_liabilities',
                'Insurance Liability - ' . $insurance->name,
                self::SUB_CATEGORY_CODES['insurance_liabilities'],
                $parentAccountNumber
            );
            
            // Calculate total liability (unearned premiums + claims payable)
            $totalLiability = ($insurance->premium ?? 0) + ($insurance->coverage_amount ?? 0);
            
            // Update account balance
            $this->updateAccountBalance($mainAccount, $totalLiability);
            
            // Post to GL: newly created insurance account + user-selected other account
            $this->postInsuranceLiability($mainAccount, $insurance, $otherAccountId);
            
            DB::commit();
            return $mainAccount;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create insurance liability account: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get or create a main account for a balance sheet category
     */
    private function getOrCreateMainAccount($type, $accountName, $subCategoryCode, $parentAccountNumber = null)
    {
        // Build account number based on sub-category code
        $branchNumber = auth()->user()->branch ?? '01';
        $accountNumber = $branchNumber . '00000' . $subCategoryCode . '0'; // Internal account format
        
        $account = AccountsModel::where('account_number', $accountNumber)->first();
        
        if (!$account) {
            // Determine account type and category codes
            $accountType = self::ACCOUNT_TYPE_MAPPINGS[$type] ?? 'asset_accounts';
            $majorCategoryCode = $this->getMajorCategoryCode($accountType);
            $categoryCode = substr($subCategoryCode, 0, 2) . '00';
            
            $accountData = [
                'account_use' => 'internal',
                'account_name' => $accountName,
                'type' => $accountType,
                'major_category_code' => $majorCategoryCode,
                'category_code' => $categoryCode,
                'sub_category_code' => $subCategoryCode,
                'product_number' => $subCategoryCode,
                'branch_number' => $branchNumber,
                'account_level' => 3,
                'balance' => 0,
                'status' => 'ACTIVE'
            ];
            
            // Use ONLY AccountCreationService to create accounts with proper parent
            $account = $this->accountCreationService->createAccount($accountData, $parentAccountNumber);
        }
        
        return $account;
    }
    
    /**
     * Update account balance
     */
    private function updateAccountBalance($account, $newBalance)
    {
        $oldBalance = $account->balance;
        $account->balance = $newBalance;
        $account->save();
        
        // Log balance change
        Log::info('Account balance updated', [
            'account_number' => $account->account_number,
            'old_balance' => $oldBalance,
            'new_balance' => $newBalance,
            'change' => $newBalance - $oldBalance
        ]);
    }
    
    /**
     * Get major category code based on account type
     */
    private function getMajorCategoryCode($accountType)
    {
        switch ($accountType) {
            case 'asset_accounts':
                return '1000';
            case 'liability_accounts':
                return '2000';
            case 'capital_accounts':
                return '3000';
            case 'income_accounts':
                return '4000';
            case 'expense_accounts':
                return '5000';
            default:
                return '1000';
        }
    }
    
    /**
     * Create or update account for Long-term/Short-term Loans
     */
    public function createLoanAccount($loan, $parentAccountNumber = null, $otherAccountId = null)
    {
        try {
            DB::beginTransaction();
            
            // Determine sub-category based on loan type
            $subCategory = $loan->loan_type === 'Long' ? 
                self::SUB_CATEGORY_CODES['long_term_borrowings'] : 
                self::SUB_CATEGORY_CODES['current_borrowings'];
            
            // Create loan account using ONLY AccountCreationService
            $accountData = [
                'account_use' => 'internal',
                'account_name' => $loan->loan_type . ' Term Loan - ' . $loan->organization_name,
                'type' => 'liability_accounts',
                'major_category_code' => '2000',
                'category_code' => $loan->loan_type === 'Long' ? '2300' : '2200',
                'sub_category_code' => $subCategory,
                'product_number' => $subCategory,
                'branch_number' => auth()->user()->branch ?? '01',
                'account_level' => 3,
                'balance' => $loan->amount,
                'status' => 'ACTIVE'
            ];
            
            // Create account under user-selected parent
            $loanAccount = $this->accountCreationService->createAccount($accountData, $parentAccountNumber);
            
            // Update loan record with account ID
            DB::table('long_term_and_short_terms')->where('id', $loan->id)
                ->update(['account_id' => $loanAccount->id]);
            
            // Post to GL using newly created loan account and user-selected other account
            $this->postLoan($loanAccount, $otherAccountId, $loan);
            
            DB::commit();
            return $loanAccount;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create loan account: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generate account number for PPE
     */
    private function generatePPEAccountNumber($ppeAsset)
    {
        $branchNumber = auth()->user()->branch ?? '01';
        $subCategory = self::SUB_CATEGORY_CODES['ppe'];
        $assetId = str_pad($ppeAsset->id, 4, '0', STR_PAD_LEFT);
        return $branchNumber . '00' . $assetId . $subCategory . '0';
    }
    
    private function generateOtherIncomeAccountNumber($income)
    {
        $branchNumber = auth()->user()->branch ?? '01';
        $subCategory = '4101'; // Other Income sub-category
        $incomeId = str_pad($income->id, 4, '0', STR_PAD_LEFT);
        return $branchNumber . '00' . $incomeId . $subCategory . '0';
    }
    
    // GL Posting Methods
    
    private function postPPEAcquisition($newlyCreatedAccount, $amount, $otherAccountId = null)
    {
        // CORRECT FLOW: Newly created PPE account + User-selected other account
        // PPE Acquisition: Debit PPE Asset (newly created), Credit Cash/Bank/Payable (user-selected)
        
        // Try to find the other account - could be passed as ID or account_number
        $otherAccount = null;
        if ($otherAccountId) {
            // First try to find by ID
            $otherAccount = AccountsModel::find($otherAccountId);
            
            // If not found, try to find by account_number
            if (!$otherAccount) {
                $otherAccount = AccountsModel::where('account_number', $otherAccountId)->first();
            }
        }
        
        // If still no account, use the default cash account
        if (!$otherAccount) {
            $otherAccount = $this->getCashAccount();
        }
        
        // Make sure we have both accounts before posting
        if (!$newlyCreatedAccount || !$otherAccount) {
            Log::error('Cannot post PPE acquisition - missing accounts', [
                'newly_created_account' => $newlyCreatedAccount ? $newlyCreatedAccount->account_number : 'null',
                'other_account' => $otherAccount ? $otherAccount->account_number : 'null',
                'other_account_id' => $otherAccountId
            ]);
            return;
        }
        
        $data = [
            'first_account' => $newlyCreatedAccount->account_number,  // Debit: Newly created PPE account
            'second_account' => $otherAccount->account_number,        // Credit: User-selected account
            'amount' => $amount,
            'narration' => 'PPE Asset Acquisition - ' . $newlyCreatedAccount->account_name,
            'action' => 'asset_purchase'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }
    
    private function postDepreciation($depreciationAccount, $amount)
    {
        // Debit: Depreciation Expense
        // Credit: Accumulated Depreciation
        $expenseAccount = $this->getDepreciationExpenseAccount();
        
        $data = [
            'first_account' => $expenseAccount->account_number,
            'second_account' => $depreciationAccount->account_number,
            'amount' => $amount,
            'narration' => 'Monthly Depreciation',
            'action' => 'depreciation'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }
    
    private function postTradeReceivable($newlyCreatedAccount, $receivable, $otherAccountId = null)
    {
        // CORRECT FLOW: Newly created receivable account + User-selected other account
        // Trade Receivable: Debit Trade Receivable (newly created), Credit Revenue (user-selected)
        
        $otherAccount = $otherAccountId ? AccountsModel::find($otherAccountId) : $this->getRevenueAccount();
        
        $data = [
            'first_account' => $newlyCreatedAccount->account_number,  // Debit: Newly created receivable account
            'second_account' => $otherAccount->account_number,        // Credit: User-selected revenue account
            'amount' => $receivable->amount,
            'narration' => 'Trade Receivable - ' . $newlyCreatedAccount->account_name,
            'action' => 'receivable'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }
    
    private function postTradePayable($newlyCreatedAccount, $payable, $otherAccountId = null)
    {
        // CORRECT FLOW: Newly created payable account + User-selected other account
        // Trade Payable Creation: Debit Expense/Inventory (user-selected), Credit Trade Payable (newly created)
        $otherAccount = $otherAccountId ? AccountsModel::find($otherAccountId) : $this->getExpenseAccount();
        
        $data = [
            'first_account' => $otherAccount->account_number,        // Debit: User-selected expense/inventory account
            'second_account' => $newlyCreatedAccount->account_number, // Credit: Newly created payable account
            'amount' => $payable->amount,
            'narration' => 'Trade Payable - ' . ($payable->description ?? $payable->vendor_name),
            'action' => 'payable_creation'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }
    
    private function postBadDebtProvision($provisionAccount, $amount)
    {
        // Debit: Bad Debt Expense
        // Credit: Provision for Bad Debts
        $badDebtExpenseAccount = $this->getBadDebtExpenseAccount();
        
        $data = [
            'first_account' => $badDebtExpenseAccount->account_number,
            'second_account' => $provisionAccount->account_number,
            'amount' => $amount,
            'narration' => 'Bad Debt Provision',
            'action' => 'provision'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }
    
    private function postInvestment($account, $investment, $customDebitId = null, $customCreditId = null)
    {
        // Use custom accounts if provided, otherwise use defaults
        // Default: Debit: Investment Account, Credit: Cash/Bank
        $debitAccount = $customDebitId ? AccountsModel::find($customDebitId) : $account;
        $creditAccount = $customCreditId ? AccountsModel::find($customCreditId) : $this->getCashAccount();
        
        $data = [
            'first_account' => $debitAccount->account_number,
            'second_account' => $creditAccount->account_number,
            'amount' => $investment->purchase_amount ?? $investment->amount,
            'narration' => 'Investment Purchase - ' . $investment->description,
            'action' => 'investment'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }
    
    private function postInterestPayable($newlyCreatedAccount, $interestPayable, $otherAccountId = null)
    {
        // CORRECT FLOW: Newly created interest payable account + User-selected other account
        // Interest Payable Accrual: Debit Interest Expense (user-selected), Credit Interest Payable (newly created)
        $otherAccount = $otherAccountId ? AccountsModel::find($otherAccountId) : $this->getInterestExpenseAccount();
        
        $data = [
            'first_account' => $otherAccount->account_number,        // Debit: User-selected expense account
            'second_account' => $newlyCreatedAccount->account_number, // Credit: Newly created interest payable account
            'amount' => $interestPayable->amount ?? 0,
            'narration' => 'Interest Payable - ' . ($interestPayable->description ?? 'Interest Accrual'),
            'action' => 'interest_payable_accrual'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }
    
    private function postOtherIncome($incomeAccount, $otherAccountId, $income)
    {
        // Debit: User-selected other account (Cash/Bank)
        // Credit: Newly created or existing income account
        $otherAccount = AccountsModel::find($otherAccountId);
        
        if (!$otherAccount) {
            throw new Exception('Other account not found for double-entry posting');
        }
        
        $data = [
            'first_account' => $otherAccount->account_number,  // Debit - Cash/Bank
            'second_account' => $incomeAccount->account_number, // Credit - Income Account
            'amount' => $income->net_amount ?? $income->amount ?? 0,
            'narration' => 'Other Income - ' . ($income->description ?? $income->income_source ?? 'Income'),
            'action' => 'other_income'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }
    
    private function postLoan($loanAccount, $otherAccountId, $loan)
    {
        // Debit: User-selected other account (Cash/Bank - receiving loan proceeds)
        // Credit: Newly created loan account (liability)
        $otherAccount = AccountsModel::find($otherAccountId);
        
        if (!$otherAccount) {
            throw new Exception('Other account not found for double-entry posting');
        }
        
        $data = [
            'first_account' => $otherAccount->account_number,  // Debit - Cash/Bank (receiving funds)
            'second_account' => $loanAccount->account_number,  // Credit - Loan Account (liability)
            'amount' => $loan->amount,
            'narration' => $loan->loan_type . ' Term Loan from ' . $loan->organization_name,
            'action' => 'loan_receipt'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }

    private function postUnearnedRevenue($unearnedAccount, $otherAccountId, $unearnedRevenue)
    {
        // Debit: User-selected other account (Cash/Bank - receiving payment)
        // Credit: Newly created unearned revenue account (liability)
        $otherAccount = AccountsModel::find($otherAccountId);
        
        if (!$otherAccount) {
            throw new Exception('Other account not found for double-entry posting');
        }
        
        $data = [
            'first_account' => $otherAccount->account_number,  // Debit - Cash/Bank (receiving payment)
            'second_account' => $unearnedAccount->account_number,  // Credit - Unearned Revenue (liability)
            'amount' => $unearnedRevenue->amount ?? $unearnedRevenue->total_amount ?? 0,
            'narration' => 'Unearned Revenue - ' . $unearnedRevenue->description,
            'action' => 'unearned_revenue'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }
    
    private function postLoanOutstanding($account, $loan)
    {
        // Debit: Loan Portfolio
        // Credit: Cash/Bank
        $cashAccount = $this->getCashAccount();
        
        $data = [
            'first_account' => $account->account_number,
            'second_account' => $cashAccount->account_number,
            'amount' => $loan->disbursed_amount,
            'narration' => 'Loan Disbursement - ' . $loan->loan_number,
            'action' => 'loan_disbursement'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }
    
    private function postCreditor($newlyCreatedAccount, $creditor, $otherAccountId = null)
    {
        // CORRECT FLOW: Newly created creditor account + User-selected other account
        // Creditor Creation: Debit Cash/Expense (user-selected), Credit Creditor (newly created)
        $otherAccount = $otherAccountId ? AccountsModel::find($otherAccountId) : $this->getCashAccount();
        
        $data = [
            'first_account' => $otherAccount->account_number,        // Debit: User-selected cash/expense account
            'second_account' => $newlyCreatedAccount->account_number, // Credit: Newly created creditor account
            'amount' => $creditor->current_balance ?? 0,
            'narration' => 'Creditor Balance - ' . $creditor->name,
            'action' => 'creditor_creation'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }
    
    private function postInsuranceLiability($newlyCreatedAccount, $insurance, $otherAccountId = null)
    {
        // CORRECT FLOW: User-selected other account + Newly created insurance liability account
        // Insurance: Debit Cash/Bank (user-selected), Credit Insurance Liability (newly created)
        
        $otherAccount = $otherAccountId ? AccountsModel::find($otherAccountId) : $this->getCashAccount();
        
        $data = [
            'first_account' => $otherAccount->account_number,           // Debit: User-selected account (Cash/Bank)
            'second_account' => $newlyCreatedAccount->account_number,   // Credit: Newly created insurance account
            'amount' => $insurance->premium ?? $insurance->coverage_amount ?? 0,
            'narration' => 'Insurance Liability - ' . $newlyCreatedAccount->account_name,
            'action' => 'insurance_liability'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }
    
    // Helper methods to get standard accounts
    
    private function getCashAccount()
    {
        return $this->getOrCreateMainAccount(
            'cash',
            'Cash and Cash Equivalents',
            self::SUB_CATEGORY_CODES['cash_and_equivalents']
        );
    }
    
    private function getIncomeAccount()
    {
        return AccountsModel::where('major_category_code', '4000') // Revenue accounts
            ->where('account_name', 'LIKE', '%Other Income%')
            ->first() ?? $this->getRevenueAccount();
    }
    
    private function getRevenueAccount()
    {
        $accountNumber = '01000004100';
        $account = AccountsModel::where('account_number', $accountNumber)->first();
        
        if (!$account) {
            $accountData = [
                'account_use' => 'internal',
                'account_name' => 'Sales Revenue',
                'type' => 'income_accounts',
                'major_category_code' => '4000',
                'category_code' => '4100',
                'sub_category_code' => '4100',
                'product_number' => '4100',
                'branch_number' => '01',
                'account_level' => 3,
                'balance' => 0,
                'status' => 'ACTIVE'
            ];
            
            $account = $this->accountCreationService->createAccount($accountData);
        }
        
        return $account;
    }
    
    private function getExpenseAccount($category = 'general')
    {
        $accountNumber = '01000005100';
        $account = AccountsModel::where('account_number', $accountNumber)->first();
        
        if (!$account) {
            $accountData = [
                'account_use' => 'internal',
                'account_name' => 'Operating Expenses',
                'type' => 'expense_accounts',
                'major_category_code' => '5000',
                'category_code' => '5100',
                'sub_category_code' => '5100',
                'product_number' => '5100',
                'branch_number' => '01',
                'account_level' => 3,
                'balance' => 0,
                'status' => 'ACTIVE'
            ];
            
            $account = $this->accountCreationService->createAccount($accountData);
        }
        
        return $account;
    }
    
    private function getDepreciationExpenseAccount()
    {
        $accountNumber = '01000005200';
        $account = AccountsModel::where('account_number', $accountNumber)->first();
        
        if (!$account) {
            $accountData = [
                'account_use' => 'internal',
                'account_name' => 'Depreciation Expense',
                'type' => 'expense_accounts',
                'major_category_code' => '5000',
                'category_code' => '5200',
                'sub_category_code' => '5200',
                'product_number' => '5200',
                'branch_number' => '01',
                'account_level' => 3,
                'balance' => 0,
                'status' => 'ACTIVE'
            ];
            
            $account = $this->accountCreationService->createAccount($accountData);
        }
        
        return $account;
    }
    
    private function getBadDebtExpenseAccount()
    {
        $accountNumber = '01000005300';
        $account = AccountsModel::where('account_number', $accountNumber)->first();
        
        if (!$account) {
            $accountData = [
                'account_use' => 'internal',
                'account_name' => 'Bad Debt Expense',
                'type' => 'expense_accounts',
                'major_category_code' => '5000',
                'category_code' => '5300',
                'sub_category_code' => '5300',
                'product_number' => '5300',
                'branch_number' => '01',
                'account_level' => 3,
                'balance' => 0,
                'status' => 'ACTIVE'
            ];
            
            $account = $this->accountCreationService->createAccount($accountData);
        }
        
        return $account;
    }
    
    private function getInterestExpenseAccount()
    {
        $accountNumber = '01000005400';
        $account = AccountsModel::where('account_number', $accountNumber)->first();
        
        if (!$account) {
            $accountData = [
                'account_use' => 'internal',
                'account_name' => 'Interest Expense',
                'type' => 'expense_accounts',
                'major_category_code' => '5000',
                'category_code' => '5400',
                'sub_category_code' => '5400',
                'product_number' => '5400',
                'branch_number' => '01',
                'account_level' => 3,
                'balance' => 0,
                'status' => 'ACTIVE'
            ];
            
            $account = $this->accountCreationService->createAccount($accountData);
        }
        
        return $account;
    }
    
    /**
     * Synchronize all balance sheet items with accounts table
     * This should be run periodically to ensure data consistency
     */
    public function synchronizeAllBalanceSheetItems()
    {
        try {
            Log::info('Starting balance sheet synchronization');
            
            // Synchronize PPE assets
            $this->synchronizePPEAssets();
            
            // Synchronize Trade Receivables
            $this->synchronizeTradeReceivables();
            
            // Synchronize Trade Payables
            $this->synchronizeTradePayables();
            
            // Synchronize Investments
            $this->synchronizeInvestments();
            
            // Synchronize Loan Portfolio
            $this->synchronizeLoanPortfolio();
            
            // Synchronize Interest Payable
            $this->synchronizeInterestPayable();
            
            // Synchronize Unearned Revenue
            $this->synchronizeUnearnedRevenue();
            
            // Synchronize Creditors
            $this->synchronizeCreditors();
            
            // Synchronize Insurance Liabilities
            $this->synchronizeInsuranceLiabilities();
            
            Log::info('Balance sheet synchronization completed successfully');
            return true;
            
        } catch (Exception $e) {
            Log::error('Balance sheet synchronization failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function synchronizePPEAssets()
    {
        if (!\Schema::hasTable('ppe_assets')) {
            return;
        }
        
        $ppeAssets = DB::table('ppe_assets')->where('status', 'active')->get();
        
        foreach ($ppeAssets as $asset) {
            // Calculate net book value
            $depreciation = DB::table('ppe_depreciation')
                ->where('asset_id', $asset->id)
                ->sum('depreciation_amount');
            
            $netBookValue = $asset->cost - $depreciation;
            
            // Update or create account
            $account = $this->getOrCreateMainAccount(
                'ppe',
                'Property, Plant and Equipment',
                self::SUB_CATEGORY_CODES['ppe']
            );
            
            // The balance should be the total of all PPE assets
            $totalPPEValue = DB::table('ppe_assets')
                ->where('status', 'active')
                ->sum('cost');
            
            $totalDepreciation = DB::table('ppe_depreciation')
                ->sum('depreciation_amount');
            
            $this->updateAccountBalance($account, $totalPPEValue - $totalDepreciation);
        }
    }
    
    private function synchronizeTradeReceivables()
    {
        if (!\Schema::hasTable('trade_receivables')) {
            return;
        }
        
        $totalReceivables = DB::table('trade_receivables')
            ->where('status', 'active')
            ->sum(DB::raw('amount - COALESCE(amount_paid, 0)'));
        
        $totalProvision = DB::table('trade_receivables')
            ->where('status', 'active')
            ->sum('bad_debt_provision');
        
        // Update main receivables account
        $account = $this->getOrCreateMainAccount(
            'trade_receivables',
            'Trade and Other Receivables',
            self::SUB_CATEGORY_CODES['trade_receivables']
        );
        
        $this->updateAccountBalance($account, $totalReceivables);
        
        // Update provision account if exists
        if ($totalProvision > 0) {
            $provisionAccount = $this->getOrCreateMainAccount(
                'bad_debt_provision',
                'Provision for Bad Debts',
                self::SUB_CATEGORY_CODES['bad_debt_provision']
            );
            
            $this->updateAccountBalance($provisionAccount, $totalProvision);
        }
    }
    
    private function synchronizeTradePayables()
    {
        $totalPayables = 0;
        
        if (\Schema::hasTable('trade_payables')) {
            $totalPayables = DB::table('trade_payables')
                ->where('status', 'active')
                ->sum(DB::raw('amount - COALESCE(amount_paid, 0)'));
        }
        
        $account = $this->getOrCreateMainAccount(
            'trade_payables',
            'Trade and Other Payables',
            self::SUB_CATEGORY_CODES['trade_payables']
        );
        
        $this->updateAccountBalance($account, $totalPayables);
    }
    
    private function synchronizeInvestments()
    {
        if (!\Schema::hasTable('investments')) {
            return;
        }
        
        // Short-term investments
        $shortTermInvestments = DB::table('investments')
            ->where('investment_type', 'short_term')
            ->where('status', 'active')
            ->sum('current_value');
        
        if ($shortTermInvestments > 0) {
            $account = $this->getOrCreateMainAccount(
                'short_term_investments',
                'Short-term Investments',
                self::SUB_CATEGORY_CODES['short_term_investments']
            );
            
            $this->updateAccountBalance($account, $shortTermInvestments);
        }
        
        // Long-term investments
        $longTermInvestments = DB::table('investments')
            ->where('investment_type', 'long_term')
            ->where('status', 'active')
            ->sum('current_value');
        
        if ($longTermInvestments > 0) {
            $account = $this->getOrCreateMainAccount(
                'long_term_investments',
                'Long-term Investments',
                self::SUB_CATEGORY_CODES['long_term_investments']
            );
            
            $this->updateAccountBalance($account, $longTermInvestments);
        }
    }
    
    private function synchronizeLoanPortfolio()
    {
        // Current portion (due within 12 months)
        $currentLoans = DB::table('loan_accounts')
            ->where('status', 'active')
            ->whereRaw('DATEDIFF(maturity_date, NOW()) <= 365')
            ->sum('principle_amount');
        
        if ($currentLoans > 0) {
            $account = $this->getOrCreateMainAccount(
                'loan_portfolio_current',
                'Loan Portfolio - Current',
                self::SUB_CATEGORY_CODES['loan_portfolio_current']
            );
            
            $this->updateAccountBalance($account, $currentLoans);
        }
        
        // Long-term portion (due after 12 months)
        $longTermLoans = DB::table('loan_accounts')
            ->where('status', 'active')
            ->whereRaw('DATEDIFF(maturity_date, NOW()) > 365')
            ->sum('principle_amount');
        
        if ($longTermLoans > 0) {
            $account = $this->getOrCreateMainAccount(
                'loan_portfolio_long_term',
                'Loan Portfolio - Long Term',
                self::SUB_CATEGORY_CODES['loan_portfolio_long_term']
            );
            
            $this->updateAccountBalance($account, $longTermLoans);
        }
    }
    
    private function synchronizeInterestPayable()
    {
        if (!\Schema::hasTable('interest_payables')) {
            return;
        }
        
        $totalInterestPayable = DB::table('interest_payables')
            ->where('status', 'active')
            ->sum('outstanding_interest');
        
        $account = $this->getOrCreateMainAccount(
            'interest_payable',
            'Interest Payable',
            self::SUB_CATEGORY_CODES['interest_payable']
        );
        
        $this->updateAccountBalance($account, $totalInterestPayable);
    }
    
    private function synchronizeUnearnedRevenue()
    {
        if (!\Schema::hasTable('unearned_deferred_revenue')) {
            return;
        }
        
        $totalUnearned = DB::table('unearned_deferred_revenue')
            ->where('status', 'active')
            ->sum(DB::raw('total_amount - COALESCE(recognized_amount, 0)'));
        
        $account = $this->getOrCreateMainAccount(
            'unearned_revenue',
            'Unearned/Deferred Revenue',
            self::SUB_CATEGORY_CODES['unearned_revenue']
        );
        
        $this->updateAccountBalance($account, $totalUnearned);
    }
    
    private function synchronizeCreditors()
    {
        if (!\Schema::hasTable('creditors')) {
            return;
        }
        
        $totalCreditors = DB::table('creditors')
            ->where('status', 'active')
            ->sum('current_balance');
        
        $account = $this->getOrCreateMainAccount(
            'creditors',
            'Creditors',
            self::SUB_CATEGORY_CODES['creditors']
        );
        
        $this->updateAccountBalance($account, $totalCreditors);
    }
    
    private function synchronizeInsuranceLiabilities()
    {
        $totalLiabilities = 0;
        
        if (\Schema::hasTable('insurance_premiums')) {
            // Unearned premiums
            $unearnedPremiums = DB::table('insurance_premiums')
                ->where('status', 'active')
                ->sum(DB::raw('premium_amount - COALESCE(earned_amount, 0)'));
            $totalLiabilities += $unearnedPremiums;
        }
        
        if (\Schema::hasTable('insurance_claims')) {
            // Outstanding claims
            $outstandingClaims = DB::table('insurance_claims')
                ->whereIn('status', ['pending', 'approved'])
                ->sum('claim_amount');
            $totalLiabilities += $outstandingClaims;
        }
        
        if ($totalLiabilities > 0) {
            $account = $this->getOrCreateMainAccount(
                'insurance_liabilities',
                'Insurance Liabilities',
                self::SUB_CATEGORY_CODES['insurance_liabilities']
            );
            
            $this->updateAccountBalance($account, $totalLiabilities);
        }
    }

    /**
     * Process investment maturity/liquidation
     */
    public function processInvestmentMaturity($investment, $proceeds, $type = 'maturity')
    {
        try {
            DB::beginTransaction();
            
            // Get investment account
            $investmentAccount = AccountsModel::where('sub_category_code', self::SUB_CATEGORY_CODES['investments'])
                ->first();

            if (!$investmentAccount) {
                throw new \Exception('Investment account not found');
            }

            // Get cash account
            $cashAccount = AccountsModel::where('sub_category_code', '1001')->first();
            if (!$cashAccount) {
                $cashAccount = $this->getOrCreateMainAccount('cash', 'Cash', '1001');
            }

            // Calculate gain/loss
            $originalAmount = $investment->principal_amount ?? $investment->purchase_amount ?? 0;
            $gainLoss = $proceeds - $originalAmount;

            // Post to GL - Receive cash
            $this->transactionPostingService->postTransaction(
                $cashAccount->account_number,
                $investmentAccount->account_number,
                $proceeds,
                0,
                'Investment ' . $type . ' - ' . ($investment->investment_type ?? 'Investment'),
                'INVESTMENT_' . strtoupper($type)
            );

            // If there's a gain, record investment income
            if ($gainLoss > 0) {
                $incomeAccount = $this->getOrCreateMainAccount('investment_income', 'Investment Income', '4100');
                
                $this->transactionPostingService->postTransaction(
                    $investmentAccount->account_number,
                    $incomeAccount->account_number,
                    $gainLoss,
                    0,
                    'Investment gain on ' . $type,
                    'INVESTMENT_INCOME'
                );
            } elseif ($gainLoss < 0) {
                // Record investment loss
                $lossAccount = $this->getOrCreateMainAccount('investment_loss', 'Investment Loss', '5500');
                
                $this->transactionPostingService->postTransaction(
                    $lossAccount->account_number,
                    $investmentAccount->account_number,
                    abs($gainLoss),
                    0,
                    'Investment loss on ' . $type,
                    'INVESTMENT_LOSS'
                );
            }

            // Update investment record
            if ($type === 'liquidation' || $type === 'maturity') {
                $investment->status = 'liquidated';
                $investment->save();
                
                // Record in investment_transactions table
                if (\Schema::hasTable('investment_transactions')) {
                    DB::table('investment_transactions')->insert([
                        'investment_id' => $investment->id,
                        'transaction_type' => $type === 'maturity' ? 'maturity' : 'sale',
                        'transaction_date' => now(),
                        'amount' => $proceeds,
                        'account_number' => $investmentAccount->account_number,
                        'description' => 'Investment ' . $type . ' proceeds',
                        'created_by' => auth()->id(),
                        'created_at' => now()
                    ]);
                }
            }

            DB::commit();
            
            Log::info('Investment ' . $type . ' processed', [
                'investment_id' => $investment->id,
                'proceeds' => $proceeds,
                'gain_loss' => $gainLoss
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to process investment ' . $type . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process receivable collection
     */
    public function processReceivableCollection($receivable, $amountCollected)
    {
        try {
            DB::beginTransaction();
            
            // Get AR account
            $arAccount = AccountsModel::where('sub_category_code', self::SUB_CATEGORY_CODES['trade_receivables'])
                ->first();

            if (!$arAccount) {
                throw new \Exception('Receivable account not found');
            }

            // Get cash account
            $cashAccount = AccountsModel::where('sub_category_code', '1001')->first();
            if (!$cashAccount) {
                $cashAccount = $this->getOrCreateMainAccount('cash', 'Cash', '1001');
            }

            // Post to GL - Cash receipt from customer
            $this->transactionPostingService->postTransaction(
                $cashAccount->account_number,
                $arAccount->account_number,
                $amountCollected,
                0,
                'Collection from ' . $receivable->customer_name . ' - Invoice ' . $receivable->invoice_number,
                'COLLECTION'
            );

            // Update receivable balance
            $receivable->paid_amount = ($receivable->paid_amount ?? 0) + $amountCollected;
            $receivable->balance = $receivable->amount - $receivable->paid_amount;
            
            if ($receivable->balance <= 0) {
                $receivable->status = 'paid';
            } else {
                $receivable->status = 'partial';
            }
            
            $receivable->save();

            // Record in receivable_collections table
            if (\Schema::hasTable('receivable_collections')) {
                DB::table('receivable_collections')->insert([
                    'receivable_id' => $receivable->id,
                    'collection_number' => 'COL-' . date('YmdHis'),
                    'collection_date' => now(),
                    'amount_collected' => $amountCollected,
                    'payment_method' => 'cash',
                    'account_number' => $arAccount->account_number,
                    'collected_by' => auth()->id(),
                    'created_at' => now()
                ]);
            }

            DB::commit();
            
            Log::info('Receivable collection processed', [
                'receivable_id' => $receivable->id,
                'amount_collected' => $amountCollected,
                'new_balance' => $receivable->balance
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to process receivable collection: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process payable payment
     */
    public function processPayablePayment($payable, $amountPaid)
    {
        try {
            DB::beginTransaction();
            
            // Get AP account
            $apAccount = AccountsModel::where('sub_category_code', self::SUB_CATEGORY_CODES['trade_payables'])
                ->first();

            if (!$apAccount) {
                throw new \Exception('Payable account not found');
            }

            // Get cash account
            $cashAccount = AccountsModel::where('sub_category_code', '1001')->first();
            if (!$cashAccount) {
                $cashAccount = $this->getOrCreateMainAccount('cash', 'Cash', '1001');
            }

            // Post to GL - Payment to vendor
            $this->transactionPostingService->postTransaction(
                $apAccount->account_number,
                $cashAccount->account_number,
                $amountPaid,
                0,
                'Payment to ' . $payable->vendor_name . ' - Bill ' . $payable->bill_number,
                'PAYMENT'
            );

            // Update payable balance
            $payable->paid_amount = ($payable->paid_amount ?? 0) + $amountPaid;
            $payable->balance = $payable->amount - $payable->paid_amount;
            
            if ($payable->balance <= 0) {
                $payable->status = 'paid';
            } else {
                $payable->status = 'partial';
            }
            
            $payable->save();

            // Record in payable_payments table
            if (\Schema::hasTable('payable_payments')) {
                DB::table('payable_payments')->insert([
                    'payable_id' => $payable->id,
                    'payment_number' => 'PAY-' . date('YmdHis'),
                    'payment_date' => now(),
                    'amount_paid' => $amountPaid,
                    'payment_method' => 'cash',
                    'account_number' => $apAccount->account_number,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'created_at' => now()
                ]);
            }

            DB::commit();
            
            Log::info('Payable payment processed', [
                'payable_id' => $payable->id,
                'amount_paid' => $amountPaid,
                'new_balance' => $payable->balance
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to process payable payment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create financial insurance account
     * @param object $insurance The insurance object
     * @param string|null $parentAccountNumber Parent account to create insurance account under
     * @param string|null $otherAccountId The other account for double-entry (Cash/Bank - credit side)
     */
    public function createFinancialInsuranceAccount($insurance, $parentAccountNumber = null, $otherAccountId = null)
    {
        try {
            DB::beginTransaction();
            
            // Create individual insurance account using ONLY AccountCreationService
            $accountData = [
                'account_use' => 'internal',
                'account_name' => 'Insurance - ' . $insurance->policy_number,
                'type' => 'asset_accounts',
                'major_category_code' => '1000',
                'category_code' => '1060',
                'sub_category_code' => self::SUB_CATEGORY_CODES['short_term_investments'], // Will be updated
                'product_number' => self::SUB_CATEGORY_CODES['short_term_investments'],
                'branch_number' => auth()->user()->branch ?? '01',
                'account_level' => 3,
                'balance' => $insurance->coverage_amount ?? 0,
                'status' => 'ACTIVE'
            ];
            
            // Create account under user-selected parent
            $insuranceAccount = $this->accountCreationService->createAccount($accountData, $parentAccountNumber);
            
            // Post initial transaction: newly created insurance account + user-selected other account
            $this->postFinancialInsurance($insuranceAccount, $insurance, $otherAccountId);
            
            DB::commit();
            return $insuranceAccount;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create financial insurance account: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function postFinancialInsurance($newlyCreatedAccount, $insurance, $otherAccountId = null)
    {
        // CORRECT FLOW: Newly created insurance account + User-selected other account
        // Financial Insurance: Debit Prepaid Insurance (newly created), Credit Cash/Bank (user-selected)
        $otherAccount = $otherAccountId ? AccountsModel::find($otherAccountId) : $this->getCashAccount();
        
        $data = [
            'first_account' => $newlyCreatedAccount->account_number, // Debit: Newly created insurance account
            'second_account' => $otherAccount->account_number,       // Credit: User-selected cash/bank account
            'amount' => $insurance->premium_amount ?? $insurance->coverage_amount,
            'narration' => 'Financial Insurance - ' . $insurance->policy_number,
            'action' => 'insurance_premium'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }
    
    /**
     * Create or update account for Intangible Assets
     * @param object $asset The intangible asset object
     * @param string|null $parentAccountNumber Parent account to create asset account under
     * @param string|null $otherAccountId The other account for double-entry (Cash/Bank - credit side)
     */
    public function createIntangibleAssetAccount($asset, $parentAccountNumber = null, $otherAccountId = null)
    {
        try {
            DB::beginTransaction();
            
            // Create individual intangible asset account using ONLY AccountCreationService
            $accountData = [
                'account_use' => 'internal',
                'account_name' => 'Intangible Asset - ' . $asset->name,
                'type' => 'asset_accounts',
                'major_category_code' => '1000',
                'category_code' => '1500',
                'sub_category_code' => '1520', // Intangible assets
                'product_number' => '1520',
                'branch_number' => auth()->user()->branch ?? '01',
                'account_level' => 3,
                'balance' => $asset->cost ?? 0,
                'status' => 'ACTIVE'
            ];
            
            // Create account under user-selected parent
            $assetAccount = $this->accountCreationService->createAccount($accountData, $parentAccountNumber);
            
            // Post initial transaction: newly created intangible asset account + user-selected other account
            $this->postIntangibleAsset($assetAccount, $asset, $otherAccountId);
            
            DB::commit();
            return $assetAccount;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create intangible asset account: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function postIntangibleAsset($newlyCreatedAccount, $asset, $otherAccountId = null)
    {
        // CORRECT FLOW: Newly created intangible asset account + User-selected other account
        // Intangible Asset Acquisition: Debit Intangible Asset (newly created), Credit Cash/Bank (user-selected)
        $otherAccount = $otherAccountId ? AccountsModel::find($otherAccountId) : $this->getCashAccount();
        
        $data = [
            'first_account' => $newlyCreatedAccount->account_number, // Debit: Newly created intangible asset account
            'second_account' => $otherAccount->account_number,       // Credit: User-selected cash/bank account
            'amount' => $asset->cost ?? 0,
            'narration' => 'Intangible Asset Acquisition - ' . $asset->name,
            'action' => 'intangible_asset_acquisition'
        ];
        
        $this->transactionPostingService->postTransaction($data);
    }
    
    /**
     * Calculate annual premium based on frequency
     */
    private function calculateAnnualPremium($amount, $frequency)
    {
        switch ($frequency) {
            case 'monthly':
                return $amount * 12;
            case 'quarterly':
                return $amount * 4;
            case 'semi_annually':
                return $amount * 2;
            case 'annually':
                return $amount;
            default:
                return $amount * 12;
        }
    }
}