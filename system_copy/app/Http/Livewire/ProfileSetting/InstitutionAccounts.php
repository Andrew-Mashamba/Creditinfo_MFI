<?php

namespace App\Http\Livewire\ProfileSetting;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class InstitutionAccounts extends Component
{
    // Institution account properties
    public $operations_account;
    public $mandatory_shares_account;
    public $mandatory_savings_account;
    public $mandatory_deposits_account;
    
    // New main accounts
    public $main_vaults_account;
    public $main_till_account;
    public $main_petty_cash_account;
    
    // Additional existing accounts
    public $members_external_loans_crealance;
    public $temp_shares_holding_account;
    public $depreciation_expense_account;
    public $accumulated_depreciation_account;
    public $property_and_equipment_account;
    
    // Asset Accounts
    public $trade_receivables_account;
    public $prepaid_expenses_account;
    public $short_term_investments_account;
    public $long_term_investments_account;
    public $other_current_assets_account;
    public $intangible_assets_account;
    
    // Liability Accounts
    public $trade_payables_account;
    public $interest_payable_account;
    public $unearned_revenue_account;
    public $accrued_expenses_account;
    public $other_payables_account;
    public $deferred_tax_account;
    public $long_term_debt_account;
    public $provisions_account;
    
    // Equity Accounts
    public $retained_earnings_account;
    public $reserves_account;
    public $share_capital_account;
    public $share_premium_account;
    
    // Income Accounts
    public $fee_income_account;
    public $other_income_account;
    public $interest_income_account;
    
    // Expense Accounts
    public $interest_expense_account;
    public $deposit_interest_account;
    public $loan_loss_provision_account;
    public $operating_expenses_account;
    public $administrative_expenses_account;
    public $personnel_expenses_account;
    
    public $institution_id;

    protected $rules = [
        'operations_account' => 'required|exists:accounts,account_number',
        'mandatory_shares_account' => 'required|exists:accounts,account_number',
        'mandatory_savings_account' => 'required|exists:accounts,account_number',
        'mandatory_deposits_account' => 'required|exists:accounts,account_number',
        'main_vaults_account' => 'required|exists:accounts,account_number',
        'main_till_account' => 'required|exists:accounts,account_number',
        'main_petty_cash_account' => 'required|exists:accounts,account_number',
        'members_external_loans_crealance' => 'nullable|exists:accounts,account_number',
        'temp_shares_holding_account' => 'nullable|exists:accounts,account_number',
        'depreciation_expense_account' => 'nullable|exists:accounts,account_number',
        'accumulated_depreciation_account' => 'nullable|exists:accounts,account_number',
        'property_and_equipment_account' => 'nullable|exists:accounts,account_number',
        // Asset Accounts
        'trade_receivables_account' => 'nullable|exists:accounts,account_number',
        'prepaid_expenses_account' => 'nullable|exists:accounts,account_number',
        'short_term_investments_account' => 'nullable|exists:accounts,account_number',
        'long_term_investments_account' => 'nullable|exists:accounts,account_number',
        'other_current_assets_account' => 'nullable|exists:accounts,account_number',
        'intangible_assets_account' => 'nullable|exists:accounts,account_number',
        // Liability Accounts
        'trade_payables_account' => 'nullable|exists:accounts,account_number',
        'interest_payable_account' => 'nullable|exists:accounts,account_number',
        'unearned_revenue_account' => 'nullable|exists:accounts,account_number',
        'accrued_expenses_account' => 'nullable|exists:accounts,account_number',
        'other_payables_account' => 'nullable|exists:accounts,account_number',
        'deferred_tax_account' => 'nullable|exists:accounts,account_number',
        'long_term_debt_account' => 'nullable|exists:accounts,account_number',
        'provisions_account' => 'nullable|exists:accounts,account_number',
        // Equity Accounts
        'retained_earnings_account' => 'nullable|exists:accounts,account_number',
        'reserves_account' => 'nullable|exists:accounts,account_number',
        'share_capital_account' => 'nullable|exists:accounts,account_number',
        'share_premium_account' => 'nullable|exists:accounts,account_number',
        // Income Accounts
        'fee_income_account' => 'nullable|exists:accounts,account_number',
        'other_income_account' => 'nullable|exists:accounts,account_number',
        'interest_income_account' => 'nullable|exists:accounts,account_number',
        // Expense Accounts
        'interest_expense_account' => 'nullable|exists:accounts,account_number',
        'deposit_interest_account' => 'nullable|exists:accounts,account_number',
        'loan_loss_provision_account' => 'nullable|exists:accounts,account_number',
        'operating_expenses_account' => 'nullable|exists:accounts,account_number',
        'administrative_expenses_account' => 'nullable|exists:accounts,account_number',
        'personnel_expenses_account' => 'nullable|exists:accounts,account_number',
    ];

    public function mount()
    {
        try {
            // Get the institution ID from the database
            $institution = DB::table('institutions')->where('id', 1)->first();
            $this->institution_id = $institution ? $institution->id : null;

            // Load existing institution accounts if any
            if ($institution) {
                try {
                    // Get account numbers directly from the accounts table
                    $this->operations_account = DB::table('accounts')
                        ->where('account_number', $institution->operations_account)
                        ->value('account_number');
                } catch (\Exception $e) {
                    Log::warning('Failed to load operations account', [
                        'error' => $e->getMessage(),
                        'account' => $institution->operations_account
                    ]);
                    $this->operations_account = null;
                }

                try {
                    $this->mandatory_shares_account = DB::table('accounts')
                        ->where('account_number', $institution->mandatory_shares_account)
                        ->value('account_number');
                } catch (\Exception $e) {
                    Log::warning('Failed to load mandatory shares account', [
                        'error' => $e->getMessage(),
                        'account' => $institution->mandatory_shares_account
                    ]);
                    $this->mandatory_shares_account = null;
                }

                try {
                    $this->mandatory_savings_account = DB::table('accounts')
                        ->where('account_number', $institution->mandatory_savings_account)
                        ->value('account_number');
                } catch (\Exception $e) {
                    Log::warning('Failed to load mandatory savings account', [
                        'error' => $e->getMessage(),
                        'account' => $institution->mandatory_savings_account
                    ]);
                    $this->mandatory_savings_account = null;
                }

                try {
                    $this->mandatory_deposits_account = DB::table('accounts')
                        ->where('account_number', $institution->mandatory_deposits_account)
                        ->value('account_number');
                } catch (\Exception $e) {
                    Log::warning('Failed to load mandatory deposits account', [
                        'error' => $e->getMessage(),
                        'account' => $institution->mandatory_deposits_account
                    ]);
                    $this->mandatory_deposits_account = null;
                }

                // Load new main accounts
                try {
                    $this->main_vaults_account = DB::table('accounts')
                        ->where('account_number', $institution->main_vaults_account)
                        ->value('account_number');
                } catch (\Exception $e) {
                    Log::warning('Failed to load main vaults account', [
                        'error' => $e->getMessage(),
                        'account' => $institution->main_vaults_account ?? 'null'
                    ]);
                    $this->main_vaults_account = null;
                }

                try {
                    $this->main_till_account = DB::table('accounts')
                        ->where('account_number', $institution->main_till_account)
                        ->value('account_number');
                } catch (\Exception $e) {
                    Log::warning('Failed to load main till account', [
                        'error' => $e->getMessage(),
                        'account' => $institution->main_till_account ?? 'null'
                    ]);
                    $this->main_till_account = null;
                }

                try {
                    $this->main_petty_cash_account = DB::table('accounts')
                        ->where('account_number', $institution->main_petty_cash_account)
                        ->value('account_number');
                } catch (\Exception $e) {
                    Log::warning('Failed to load main petty cash account', [
                        'error' => $e->getMessage(),
                        'account' => $institution->main_petty_cash_account ?? 'null'
                    ]);
                    $this->main_petty_cash_account = null;
                }

                // Load additional existing accounts
                try {
                    $this->members_external_loans_crealance = DB::table('accounts')
                        ->where('account_number', $institution->members_external_loans_crealance)
                        ->value('account_number');
                } catch (\Exception $e) {
                    Log::warning('Failed to load members external loans clearance account', [
                        'error' => $e->getMessage(),
                        'account' => $institution->members_external_loans_crealance ?? 'null'
                    ]);
                    $this->members_external_loans_crealance = null;
                }

                try {
                    $this->temp_shares_holding_account = DB::table('accounts')
                        ->where('account_number', $institution->temp_shares_holding_account)
                        ->value('account_number');
                } catch (\Exception $e) {
                    Log::warning('Failed to load temp shares holding account', [
                        'error' => $e->getMessage(),
                        'account' => $institution->temp_shares_holding_account ?? 'null'
                    ]);
                    $this->temp_shares_holding_account = null;
                }

                try {
                    $this->depreciation_expense_account = DB::table('accounts')
                        ->where('account_number', $institution->depreciation_expense_account)
                        ->value('account_number');
                } catch (\Exception $e) {
                    Log::warning('Failed to load depreciation expense account', [
                        'error' => $e->getMessage(),
                        'account' => $institution->depreciation_expense_account ?? 'null'
                    ]);
                    $this->depreciation_expense_account = null;
                }

                try {
                    $this->accumulated_depreciation_account = DB::table('accounts')
                        ->where('account_number', $institution->accumulated_depreciation_account)
                        ->value('account_number');
                } catch (\Exception $e) {
                    Log::warning('Failed to load accumulated depreciation account', [
                        'error' => $e->getMessage(),
                        'account' => $institution->accumulated_depreciation_account ?? 'null'
                    ]);
                    $this->accumulated_depreciation_account = null;
                }

                try {
                    $this->property_and_equipment_account = DB::table('accounts')
                        ->where('account_number', $institution->property_and_equipment_account)
                        ->value('account_number');
                } catch (\Exception $e) {
                    Log::warning('Failed to load property and equipment account', [
                        'error' => $e->getMessage(),
                        'account' => $institution->property_and_equipment_account ?? 'null'
                    ]);
                    $this->property_and_equipment_account = null;
                }
                
                // Load new account fields
                $newAccountFields = [
                    'trade_receivables_account',
                    'prepaid_expenses_account',
                    'short_term_investments_account',
                    'long_term_investments_account',
                    'other_current_assets_account',
                    'intangible_assets_account',
                    'trade_payables_account',
                    'interest_payable_account',
                    'unearned_revenue_account',
                    'accrued_expenses_account',
                    'other_payables_account',
                    'deferred_tax_account',
                    'long_term_debt_account',
                    'provisions_account',
                    'retained_earnings_account',
                    'reserves_account',
                    'share_capital_account',
                    'share_premium_account',
                    'fee_income_account',
                    'other_income_account',
                    'interest_income_account',
                    'interest_expense_account',
                    'deposit_interest_account',
                    'loan_loss_provision_account',
                    'operating_expenses_account',
                    'administrative_expenses_account',
                    'personnel_expenses_account',
                ];
                
                foreach ($newAccountFields as $field) {
                    try {
                        if (isset($institution->$field) && $institution->$field) {
                            $this->$field = DB::table('accounts')
                                ->where('account_number', $institution->$field)
                                ->value('account_number');
                        } else {
                            $this->$field = null;
                        }
                    } catch (\Exception $e) {
                        Log::warning("Failed to load {$field}", [
                            'error' => $e->getMessage(),
                            'account' => $institution->$field ?? 'null'
                        ]);
                        $this->$field = null;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to load institution accounts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Set all account fields to null to prevent errors
            $this->operations_account = null;
            $this->mandatory_shares_account = null;
            $this->mandatory_savings_account = null;
            $this->mandatory_deposits_account = null;
            $this->main_vaults_account = null;
            $this->main_till_account = null;
            $this->main_petty_cash_account = null;
            $this->members_external_loans_crealance = null;
            $this->temp_shares_holding_account = null;
            $this->depreciation_expense_account = null;
            $this->accumulated_depreciation_account = null;
            $this->property_and_equipment_account = null;
            // New account fields
            $this->trade_receivables_account = null;
            $this->prepaid_expenses_account = null;
            $this->short_term_investments_account = null;
            $this->long_term_investments_account = null;
            $this->other_current_assets_account = null;
            $this->intangible_assets_account = null;
            $this->trade_payables_account = null;
            $this->interest_payable_account = null;
            $this->unearned_revenue_account = null;
            $this->accrued_expenses_account = null;
            $this->other_payables_account = null;
            $this->deferred_tax_account = null;
            $this->long_term_debt_account = null;
            $this->provisions_account = null;
            $this->retained_earnings_account = null;
            $this->reserves_account = null;
            $this->share_capital_account = null;
            $this->share_premium_account = null;
            $this->fee_income_account = null;
            $this->other_income_account = null;
            $this->interest_income_account = null;
            $this->interest_expense_account = null;
            $this->deposit_interest_account = null;
            $this->loan_loss_provision_account = null;
            $this->operating_expenses_account = null;
            $this->administrative_expenses_account = null;
            $this->personnel_expenses_account = null;
        }
    }

    public function getAccountsForCategory($majorCategory, $category)
    {
        return DB::table('accounts')
                //->where('major_category_code', $majorCategory)
                ->whereBetween('category_code', [$majorCategory, $category])
                ->get();
    }

    public function saveInstitutionAccounts()
    {
        $this->validate();

        if (!$this->institution_id) {
            session()->flash('error', 'No institution found.');
            return;
        }

        try {
            DB::beginTransaction();

            // Get account IDs from account numbers
            $operationsAccountId = $this->operations_account;
                
            $mandatorySharesAccountId = $this->mandatory_shares_account;
                
            $mandatorySavingsAccountId = $this->mandatory_savings_account;
                
            $mandatoryDepositsAccountId = $this->mandatory_deposits_account;

            DB::table('institutions')
                ->where('id', $this->institution_id)
                ->update([
                    'operations_account' => $operationsAccountId,
                    'mandatory_shares_account' => $mandatorySharesAccountId,
                    'mandatory_savings_account' => $mandatorySavingsAccountId,
                    'mandatory_deposits_account' => $mandatoryDepositsAccountId,
                    'main_vaults_account' => $this->main_vaults_account,
                    'main_till_account' => $this->main_till_account,
                    'main_petty_cash_account' => $this->main_petty_cash_account,
                    'members_external_loans_crealance' => $this->members_external_loans_crealance,
                    'temp_shares_holding_account' => $this->temp_shares_holding_account,
                    'depreciation_expense_account' => $this->depreciation_expense_account,
                    'accumulated_depreciation_account' => $this->accumulated_depreciation_account,
                    'property_and_equipment_account' => $this->property_and_equipment_account,
                    // Asset Accounts
                    'trade_receivables_account' => $this->trade_receivables_account,
                    'prepaid_expenses_account' => $this->prepaid_expenses_account,
                    'short_term_investments_account' => $this->short_term_investments_account,
                    'long_term_investments_account' => $this->long_term_investments_account,
                    'other_current_assets_account' => $this->other_current_assets_account,
                    'intangible_assets_account' => $this->intangible_assets_account,
                    // Liability Accounts
                    'trade_payables_account' => $this->trade_payables_account,
                    'interest_payable_account' => $this->interest_payable_account,
                    'unearned_revenue_account' => $this->unearned_revenue_account,
                    'accrued_expenses_account' => $this->accrued_expenses_account,
                    'other_payables_account' => $this->other_payables_account,
                    'deferred_tax_account' => $this->deferred_tax_account,
                    'long_term_debt_account' => $this->long_term_debt_account,
                    'provisions_account' => $this->provisions_account,
                    // Equity Accounts
                    'retained_earnings_account' => $this->retained_earnings_account,
                    'reserves_account' => $this->reserves_account,
                    'share_capital_account' => $this->share_capital_account,
                    'share_premium_account' => $this->share_premium_account,
                    // Income Accounts
                    'fee_income_account' => $this->fee_income_account,
                    'other_income_account' => $this->other_income_account,
                    'interest_income_account' => $this->interest_income_account,
                    // Expense Accounts
                    'interest_expense_account' => $this->interest_expense_account,
                    'deposit_interest_account' => $this->deposit_interest_account,
                    'loan_loss_provision_account' => $this->loan_loss_provision_account,
                    'operating_expenses_account' => $this->operating_expenses_account,
                    'administrative_expenses_account' => $this->administrative_expenses_account,
                    'personnel_expenses_account' => $this->personnel_expenses_account,
                    'updated_at' => now(),
                ]);

            DB::commit();
            
            // Clear all account-related caches
            $this->clearAccountCaches();
            
            session()->flash('message', 'Institution accounts have been saved successfully.');
            $this->emit('refreshAccounts');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save institution accounts: ' . $e->getMessage());
            session()->flash('error', 'Failed to save institution accounts. Please try again.');
        }
    }

    protected function clearAccountCaches()
    {
        // Clear all account-related caches
        $categories = [
            ['1000', '1000'], // Cash and Cash Equivalents
            ['1000', '1999'], // All Asset accounts
            ['2000', '2999'], // All Liability accounts
            ['3000', '3999'], // All Equity accounts
            ['4000', '4999'], // All Revenue/Income accounts
            ['5000', '5999'], // All Expense accounts
        ];

        foreach ($categories as [$majorCategory, $category]) {
            Cache::forget("accounts_{$majorCategory}_{$category}");
        }
        
        // Clear additional account caches
        Cache::forget("all_accounts");
        Cache::forget("institution_accounts");
    }

    public function render()
    {
        return view('livewire.profile-setting.institution-accounts');
    }
} 