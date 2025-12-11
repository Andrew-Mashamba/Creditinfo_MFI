<?php

namespace App\Http\Livewire\ProductsManagement;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Loan_sub_products;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Committee;
use App\Models\Department;
use App\Models\AccountsModel;

class Loans extends Component
{
    use WithPagination;
    // Form Properties
    public $showAddModal = false;
    public $editingProduct = null;
    public $search = '';
    public $showFilters = false;
    public $sortField = 'sub_product_name';
    public $sortDirection = 'asc';
    public $activeTab = 'basic';
    
    // Charges and Insurance Management  
    public $charges = [];
    public $insurance = [];
    public $showAddChargeModal = false;
    public $editingChargeIndex = null;
    public $showAddInsuranceModal = false;
    public $editingChargeId = null;
    public $editingInsuranceId = null;
    

    
    public $insuranceForm = [
        'name' => '',
        'value_type' => 'fixed', // fixed or percentage  
        'value' => 0,
        'account_id' => ''
    ];

    public $filters = [
        'status' => '',
        'type' => '',
        'min_amount' => '',
        'interest_rate' => ''
    ];

    // Form Data
    public $form = [
        'sub_product_id' => '',
        'product_id' => '',
        'sub_product_name' => '',
        'prefix' => '',
        'sub_product_status' => '1',
        'currency' => 'TZS',
        'collection_account_loan_interest' => '',
        'collection_account_loan_principle' => '',
        'collection_account_loan_charges' => '',
        'collection_account_loan_penalties' => '',
        'principle_min_value' => 0,
        'principle_max_value' => 0,
        'min_term' => 0,
        'max_term' => 0,
        'interest_value' => '',
        'interest_tenure' => '',
        'interest_method' => 'flat',
        'amortization_method' => 'equal_installments',
        'days_in_a_year' => 365,
        'days_in_a_month' => 30,
        'repayment_strategy' => 'standard',
        'maintenance_fees_value' => 0,
        'ledger_fees' => '',
        'ledger_fees_value' => 0,
        'lock_guarantee_funds' => '0',
        'maintenance_fees' => '0',
        'inactivity' => '0',
        'requires_approval' => '1',
        'notes' => '',
        //'institution_id' => '',
        'loan_product_account' => '',
        'charges' => '',
        'loan_multiplier' => '',
        'ltv' => '',
        'score_limit' => '',
        'repayment_frequency' => 'monthly',
        'interest_account' => '',
        'fees_account' => '',
        'payable_account' => '',
        'insurance_account' => '',
        'loan_interest_account' => '',
        'loan_charges_account' => '',
        'loan_insurance_account' => '',
        'charge_product_account' => '',
        'insurance_product_account' => '',
        'penalty_value' => ''
    ];

    // Validation Rules - Only Essential Fields Required
    protected $rules = [
        // Core Required Fields
        'form.sub_product_name' => 'required|string|min:3|max:255',
        //'form.sub_product_status' => 'required|string|in:0,1',
        //'form.currency' => 'required|string|max:255',
        'form.principle_min_value' => 'required|numeric|min:0',
        'form.principle_max_value' => 'required|numeric|min:0|gt:form.principle_min_value',
        'form.min_term' => 'required|numeric|min:1',
        'form.max_term' => 'required|numeric|min:1|gte:form.min_term',
        'form.interest_value' => 'required|numeric|min:0|max:100',
        //'form.interest_method' => 'required|string|in:flat,declining',
        //'form.amortization_method' => 'required|string|in:equal_installments,equal_principal,balloon',
        
        // Optional Basic Fields
        'form.sub_product_id' => 'nullable|string|max:255',
        'form.product_id' => 'nullable|string|max:255',
        'form.prefix' => 'nullable|string|max:10',
        'form.notes' => 'nullable|string|max:1000',
        
        // Optional Account Mappings
        'form.collection_account_loan_interest' => 'nullable|exists:accounts,account_number',
        'form.collection_account_loan_principle' => 'nullable|exists:accounts,account_number',
        'form.collection_account_loan_charges' => 'nullable|exists:accounts,account_number',
        'form.collection_account_loan_penalties' => 'nullable|exists:accounts,account_number',
        'form.loan_product_account' => 'nullable|string|max:150',
        'form.interest_account' => 'nullable|string|max:150',
        'form.fees_account' => 'nullable|string|max:150',
        'form.payable_account' => 'nullable|string|max:150',
        'form.insurance_account' => 'nullable|string|max:150',
        'form.loan_interest_account' => 'nullable|string|max:255',
        'form.loan_charges_account' => 'nullable|string|max:255',
        'form.loan_insurance_account' => 'nullable|string|max:255',
        'form.charge_product_account' => 'nullable|string|max:250',
        'form.insurance_product_account' => 'nullable|string|max:250',
        
        // Optional Advanced Settings
        'form.interest_tenure' => 'nullable|string|max:255',
        'form.days_in_a_year' => 'nullable|integer|in:360,365,366',
        'form.days_in_a_month' => 'nullable|integer|in:28,29,30,31',
        'form.repayment_strategy' => 'nullable|string|max:255',
        'form.repayment_frequency' => 'nullable|string|in:daily,weekly,monthly,quarterly',
        
        // Optional Fee Settings
        'form.maintenance_fees_value' => 'nullable|numeric|min:0',
        'form.ledger_fees' => 'nullable|string|max:255',
        'form.ledger_fees_value' => 'nullable|numeric|min:0',
        'form.lock_guarantee_funds' => 'nullable|string|in:0,1',
        'form.maintenance_fees' => 'nullable|string|in:0,1',
        'form.penalty_value' => 'nullable|numeric|min:0|max:100',
        
        // Optional Business Rules
        'form.requires_approval' => 'nullable|string|in:0,1',
        'form.inactivity' => 'nullable|integer|min:0',
        'form.charges' => 'nullable|string|max:150',
        'form.loan_multiplier' => 'nullable|numeric|min:0|max:100',
        'form.ltv' => 'nullable|numeric|min:0|max:100',
        'form.score_limit' => 'nullable|numeric|min:0|max:1000',
        
        // Optional System Fields
        //'form.institution_id' => 'nullable|string|max:255'
    ];

    protected $messages = [
        'form.sub_product_name.required' => 'The product name is required.',
        'form.sub_product_name.min' => 'The product name must be at least 3 characters.',
        'form.sub_product_name.max' => 'The product name cannot exceed 255 characters.',
        'form.currency.required' => 'The currency is required.',
        'form.collection_account_loan_interest.required' => 'The loan interest collection account is required.',
        'form.collection_account_loan_interest.exists' => 'The selected loan interest collection account is invalid.',
        'form.collection_account_loan_principle.required' => 'The loan principle collection account is required.',
        'form.collection_account_loan_principle.exists' => 'The selected loan principle collection account is invalid.',
        'form.collection_account_loan_charges.required' => 'The loan charges collection account is required.',
        'form.collection_account_loan_charges.exists' => 'The selected loan charges collection account is invalid.',
        'form.collection_account_loan_penalties.required' => 'The loan penalties collection account is required.',
        'form.collection_account_loan_penalties.exists' => 'The selected loan penalties collection account is invalid.',
        'form.principle_min_value.required' => 'The minimum loan amount is required.',
        'form.principle_min_value.numeric' => 'The minimum loan amount must be a number.',
        'form.principle_min_value.min' => 'The minimum loan amount cannot be negative.',
        'form.principle_max_value.required' => 'The maximum loan amount is required.',
        'form.principle_max_value.numeric' => 'The maximum loan amount must be a number.',
        'form.principle_max_value.min' => 'The maximum loan amount cannot be negative.',
        'form.principle_max_value.gt' => 'The maximum loan amount must be greater than the minimum loan amount.',
        'form.min_term.required' => 'The minimum term is required.',
        'form.min_term.numeric' => 'The minimum term must be a number.',
        'form.min_term.min' => 'The minimum term must be at least 1 month.',
        'form.max_term.required' => 'The maximum term is required.',
        'form.max_term.numeric' => 'The maximum term must be a number.',
        'form.max_term.min' => 'The maximum term must be at least 1 month.',
        'form.max_term.gte' => 'The maximum term must be greater than or equal to the minimum term.',
        'form.interest_value.required' => 'The interest value is required.',
        'form.interest_method.required' => 'The interest method is required.',
        'form.amortization_method.required' => 'The amortization method is required.',
        'form.days_in_a_year.required' => 'The days in a year is required.',
        'form.days_in_a_year.integer' => 'The days in a year must be a whole number.',
        'form.days_in_a_year.in' => 'The days in a year must be either 360, 365, or 366.',
        'form.days_in_a_month.required' => 'The days in a month is required.',
        'form.days_in_a_month.integer' => 'The days in a month must be a whole number.',
        'form.days_in_a_month.in' => 'The days in a month must be either 28, 29, 30, or 31.',
        'form.repayment_strategy.required' => 'The repayment strategy is required.',
        'form.repayment_strategy.in' => 'The repayment strategy must be either standard or balloon.',
        'form.maintenance_fees_value.required' => 'The maintenance fees value is required.',
        'form.maintenance_fees_value.numeric' => 'The maintenance fees value must be a number.',
        'form.maintenance_fees_value.min' => 'The maintenance fees value cannot be negative.',
        'form.ledger_fees_value.required' => 'The ledger fees value is required.',
        'form.ledger_fees_value.numeric' => 'The ledger fees value must be a number.',
        'form.ledger_fees_value.min' => 'The ledger fees value cannot be negative.',
        'form.lock_guarantee_funds.required' => 'The lock guarantee funds setting is required.',
        'form.lock_guarantee_funds.in' => 'The lock guarantee funds setting must be either 0 or 1.',
        'form.maintenance_fees.required' => 'The maintenance fees setting is required.',
        'form.maintenance_fees.in' => 'The maintenance fees setting must be either 0 or 1.',
        'form.inactivity.required' => 'The inactivity setting is required.',
        'form.requires_approval.required' => 'The requires approval setting is required.',
        'form.requires_approval.in' => 'The requires approval setting must be either 0 or 1.',
        'form.notes.max' => 'The notes cannot exceed 255 characters.',
        'form.charges.max' => 'The charges cannot exceed 150 characters.',
        'form.loan_multiplier.max' => 'The loan multiplier cannot exceed 100 characters.',
        'form.ltv.max' => 'The LTV cannot exceed 100 characters.',
        'form.score_limit.max' => 'The score limit cannot exceed 100 characters.',
        'form.repayment_frequency.required' => 'The repayment frequency is required.',
        'form.repayment_frequency.in' => 'The repayment frequency must be either daily, weekly, monthly, or quarterly.',
        'form.penalty_value.max' => 'The penalty value cannot exceed 100%.',
        'form.sub_product_status.required' => 'The product status is required.',
        'form.sub_product_status.in' => 'The product status must be either 0 or 1.',
        'form.loan_account.required' => 'The loan account is required.',
        'form.loan_account.max' => 'The loan account cannot exceed 150 characters.'
    ];

    public $committees = [];
    public $departments = [];
    public $accounts = [];
    public $loan_accounts = [];
    public $charge_accounts = [];
    public $insurance_accounts = [];
    public $gl_accounts = [];
    
    // Account selection properties
    public $loan_category = '';
    public $loan_subcategory = '';
    public $interest_category = '';
    public $interest_subcategory = '';
    public $principle_category = '';
    public $principle_subcategory = '';
    public $charge_category = '';
    public $charge_subcategory = '';
    public $insurance_category = '';
    public $insurance_subcategory = '';
    
    // Hierarchical selector properties
    public $selectedLoanProductLevel2 = '';
    public $selectedPrincipalLevel2 = '';
    public $selectedChargesLevel2 = '';
    public $selectedInsuranceLevel2 = '';
    public $selectedInterestLevel2 = '';
    public $selectedPenaltiesLevel2 = '';
    
    // New charge/insurance form account selection properties
    public $new_charge_category = '';
    public $new_charge_subcategory = '';
    
    public $set_stage = '';
    public $loan_stages = [];
    public $selectedCollaterals = [];
    public $loan_multiplier;
    public $ltv;
    public $score_limit;

    public $newCharge = [
        'type' => 'charge',
        'name' => '',
        'value_type' => 'fixed',
        'value' => '',
        'account_id' => '',
        'min_cap' => '',
        'max_cap' => ''
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilters()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->loadData();
        $this->loadLoanStages();
        
        // Initialize form with default values
        $this->form = array_merge($this->form, [
            'loan_multiplier' => '',
            'ltv' => '',
            'score_limit' => '',
            'sub_product_id' => $this->generateProductId(), // Generate unique 6-digit ID
        ]);

        // Prefill with default account values if available
        $this->prefillDefaultAccounts();

        // Load existing charges if editing
        if ($this->editingProduct) {
            $this->charges = $this->editingProduct->charges->map(function ($charge) {
                return [
                    'type' => $charge->type,
                    'name' => $charge->name,
                    'value_type' => $charge->value_type,
                    'value' => $charge->value,
                    'account_id' => $charge->account_id ?? '',
                    'min_cap' => $charge->min_cap ?? '',
                    'max_cap' => $charge->max_cap ?? ''
                ];
            })->toArray();
        }
    }

    /**
     * Prefill default account values from most recently created product or sensible defaults
     */
    private function prefillDefaultAccounts()
    {
        // Try to get the most recently created loan product for default values
        $recentProduct = Loan_sub_products::orderBy('created_at', 'desc')->first();
        
        if ($recentProduct) {
            // Use values from the most recent product as defaults
            $this->form['loan_product_account'] = $recentProduct->loan_product_account ?: '';
            $this->form['collection_account_loan_interest'] = $recentProduct->collection_account_loan_interest ?: '';
            $this->form['collection_account_loan_principle'] = $recentProduct->collection_account_loan_principle ?: '';
            $this->form['collection_account_loan_charges'] = $recentProduct->collection_account_loan_charges ?: '';
            $this->form['collection_account_loan_penalties'] = $recentProduct->collection_account_loan_penalties ?: '';
            $this->form['insurance_account'] = $recentProduct->insurance_account ?: '';
            
            // Set the hierarchical selectors based on these accounts
            $this->setHierarchicalSelectorsFromAccounts();
        } else {
            // Set common default accounts if no products exist yet
            $this->setDefaultAccountValues();
        }
    }

    /**
     * Set hierarchical selectors based on selected account values
     */
    private function setHierarchicalSelectorsFromAccounts()
    {
        // Set Loan Product Level2 selector
        if (!empty($this->form['loan_product_account'])) {
            $loanAccount = Account::where('account_number', $this->form['loan_product_account'])->first();
            if ($loanAccount && $loanAccount->category_code == '1200') {
                $this->selectedLoanProductLevel2 = '010110001200';
            }
        }
        
        // Set Interest Level2 selector
        if (!empty($this->form['collection_account_loan_interest'])) {
            $interestAccount = Account::where('account_number', $this->form['collection_account_loan_interest'])->first();
            if ($interestAccount) {
                // Interest accounts are typically under 010140004000
                if (strpos($interestAccount->account_number, '010140004000') === 0) {
                    $this->selectedInterestLevel2 = '010140004000';
                }
            }
        }
        
        // Set Principal Level2 selector
        if (!empty($this->form['collection_account_loan_principle'])) {
            $principalAccount = Account::where('account_number', $this->form['collection_account_loan_principle'])->first();
            if ($principalAccount && $principalAccount->category_code == '1200') {
                $this->selectedPrincipalLevel2 = '010110001200';
            }
        }
        
        // Set Charges Level2 selector
        if (!empty($this->form['collection_account_loan_charges'])) {
            $chargesAccount = Account::where('account_number', $this->form['collection_account_loan_charges'])->first();
            if ($chargesAccount) {
                // Determine which level 2 category based on the account number
                if (strpos($chargesAccount->account_number, '010140004100') === 0) {
                    $this->selectedChargesLevel2 = '010140004100';
                } elseif (strpos($chargesAccount->account_number, '010140004200') === 0) {
                    $this->selectedChargesLevel2 = '010140004200';
                }
            }
        }
        
        // Set Penalties Level2 selector
        if (!empty($this->form['collection_account_loan_penalties'])) {
            $penaltiesAccount = Account::where('account_number', $this->form['collection_account_loan_penalties'])->first();
            if ($penaltiesAccount) {
                // Penalties are typically under 010140004100
                if (strpos($penaltiesAccount->account_number, '010140004100') === 0) {
                    $this->selectedPenaltiesLevel2 = '010140004100';
                }
            }
        }
        
        // Set Insurance Level2 selector  
        if (!empty($this->form['insurance_account'])) {
            $insuranceAccount = Account::where('account_number', $this->form['insurance_account'])->first();
            if ($insuranceAccount) {
                $this->selectedInsuranceLevel2 = substr($insuranceAccount->account_number, 0, 12);
            }
        }
    }

    /**
     * Set sensible default account values for new products
     */
    private function setDefaultAccountValues()
    {
        // Set default loan product account (first available loan account)
        $defaultLoanAccount = Account::where('type', 'asset_accounts')
            ->where('account_level', '3')
            ->where('category_code', '1200')
            ->first();
        if ($defaultLoanAccount) {
            $this->form['loan_product_account'] = $defaultLoanAccount->account_number;
            $this->selectedLoanProductLevel2 = '010110001200';
        }
        
        // Set default interest collection account
        $defaultInterestAccount = Account::where('type', 'income_accounts')
            ->where('account_level', '3')
            ->where('account_number', 'like', '010140004000%')
            ->first();
        if ($defaultInterestAccount) {
            $this->form['collection_account_loan_interest'] = $defaultInterestAccount->account_number;
            $this->selectedInterestLevel2 = '010140004000';
        }
        
        // Set default principal collection account
        $defaultPrincipalAccount = Account::where('type', 'asset_accounts')
            ->where('account_level', '3')
            ->where('category_code', '1200')
            ->whereIn('account_number', ['0101100012001210', '0101100012001220', '0101100012001240'])
            ->first();
        if ($defaultPrincipalAccount) {
            $this->form['collection_account_loan_principle'] = $defaultPrincipalAccount->account_number;
            $this->selectedPrincipalLevel2 = '010110001200';
        }
        
        // Set default charges collection account
        $defaultChargesAccount = Account::where('type', 'income_accounts')
            ->where('account_level', '3')
            ->whereIn('account_number', ['0101400041004120', '0101400041004130', '0101400042004215'])
            ->first();
        if ($defaultChargesAccount) {
            $this->form['collection_account_loan_charges'] = $defaultChargesAccount->account_number;
            if (strpos($defaultChargesAccount->account_number, '010140004100') === 0) {
                $this->selectedChargesLevel2 = '010140004100';
            } elseif (strpos($defaultChargesAccount->account_number, '010140004200') === 0) {
                $this->selectedChargesLevel2 = '010140004200';
            }
        }
        
        // Set default penalties collection account
        $defaultPenaltiesAccount = Account::where('type', 'income_accounts')
            ->where('account_level', '3')
            ->where('account_number', '0101400041004110')
            ->first();
        if ($defaultPenaltiesAccount) {
            $this->form['collection_account_loan_penalties'] = $defaultPenaltiesAccount->account_number;
            $this->selectedPenaltiesLevel2 = '010140004100';
        }
        
        // Set default insurance account
        $defaultInsuranceAccount = Account::where('type', 'capital_accounts')
            ->where('account_level', '3')
            ->whereIn('account_number', ['0101200025002530', '0101200029002920'])
            ->first();
        if ($defaultInsuranceAccount) {
            $this->form['insurance_account'] = $defaultInsuranceAccount->account_number;
            $this->selectedInsuranceLevel2 = substr($defaultInsuranceAccount->account_number, 0, 12);
        }
    }

    public function loadData()
    {
       // $this->committees = Committee::where('status', 'ACTIVE')->get();
       // $this->departments = Department::where('status', 'ACTIVE')->get();
        $this->accounts = Account::where('status', 'ACTIVE')->get();
        
        // Load major categories (account_level = 1) from accounts table instead of GL_accounts
        $this->gl_accounts = Account::where('account_level', '1')
            //->where('status', 'ACTIVE')
            ->get();
        
        // Load loan accounts (asset accounts) - level 2 accounts under asset_accounts type
        $this->loan_accounts = Account::where('type', 'asset_accounts')
            ->where('account_level', '3')
            //->where('status', 'ACTIVE')
            ->get();
            
        // Load charge accounts (income accounts) - level 2 accounts under income_accounts type
        $this->charge_accounts = Account::where('type', 'income_accounts')
            ->where('account_level', '2')
            //->where('status', 'ACTIVE')
            ->get();
            
        // Load insurance accounts (liability accounts) - level 2 accounts under liability_accounts type
        $this->insurance_accounts = Account::where('type', 'liability_accounts')
            ->where('account_level', '2')
            //->where('status', 'ACTIVE')
            ->get();
    }

    public function loadLoanStages($product = null)
    {
        if ($product) {
            // Load stages for a specific product (used in edit)
            // Use numeric primary key for tables where column is integer
            $stages = DB::table('loan_stages')
                ->where('loan_product_id', $product->id)
                ->get();
            
            if ($stages->count() > 0) {
                $this->loan_stages = $stages->map(function($stage) {
                    return [
                        'id' => $stage->stage_id,
                        'type' => $stage->stage_type,
                        'name' => $stage->stage_type
                    ];
                })->toArray();
            }
        } elseif ($this->editingProduct) {
            // Load stages for current editing product
            $stages = \App\Models\LoanStage::where('loan_sub_product_id', $this->editingProduct->sub_product_id)->get();
            $this->loan_stages = $stages->map(function($stage) {
                return [
                    'id' => $stage->id,
                    'type' => $stage->type,
                    'name' => $stage->name
                ];
            })->toArray();
        }
    }

    // Account selection methods
    public function updatedLoanCategory()
    {
        $this->loan_subcategory = '';
        $this->form['loan_product_account'] = '';
        $this->form['sub_product_name'] = '';
    }

    public function updatedLoanSubcategory()
    {
        $this->form['loan_product_account'] = '';
        $this->form['sub_product_name'] = '';
    }

    public function updatedLoanAccount()
    {
        // Auto-generate product ID if not set
        if (empty($this->form['sub_product_id'])) {
            $this->form['sub_product_id'] = $this->generateProductId();
        }
        
        // Set product name based on selected account
        if (!empty($this->form['loan_product_account'])) {
            $account = Account::where('account_number', $this->form['loan_product_account'])->first();
            if ($account) {
                $this->form['sub_product_name'] = $account->account_name;
            }
        }
    }

    public function updatedInterestCategory()
    {
        $this->interest_subcategory = '';
        $this->form['collection_account_loan_interest'] = '';
    }

    public function updatedInterestSubcategory()
    {
        $this->form['collection_account_loan_interest'] = '';
    }

    public function updatedPrincipleCategory()
    {
        $this->principle_subcategory = '';
        $this->form['collection_account_loan_principle'] = '';
    }

    public function updatedPrincipleSubcategory()
    {
        $this->form['collection_account_loan_principle'] = '';
    }

    public function updatedChargeCategory()
    {
        $this->charge_subcategory = '';
        $this->form['collection_account_loan_charges'] = '';
    }

    public function updatedChargeSubcategory()
    {
        $this->form['collection_account_loan_charges'] = '';
    }

    public function updatedInsuranceCategory()
    {
        $this->insurance_subcategory = '';
        $this->form['collection_account_loan_penalties'] = '';
    }

    public function updatedInsuranceSubcategory()
    {
        $this->form['collection_account_loan_penalties'] = '';
    }

    // New charge form account selection methods
    public function updatedNewChargeCategory()
    {
        $this->new_charge_subcategory = '';
        $this->newCharge['account_id'] = '';
    }

    public function updatedNewChargeSubcategory()
    {
        $this->newCharge['account_id'] = '';
    }

    public function updatedNewChargeType()
    {
        $this->new_charge_category = '';
        $this->new_charge_subcategory = '';
        $this->newCharge['account_id'] = '';
    }

    public function updatedNewChargeValueType()
    {
        // Clear min/max caps when switching away from percentage
        if ($this->newCharge['value_type'] !== 'percentage') {
            $this->newCharge['min_cap'] = '';
            $this->newCharge['max_cap'] = '';
        }
    }

    /**
     * Get whether to show min/max cap fields
     */
    public function getShowCapFieldsProperty()
    {
        return $this->newCharge['value_type'] === 'percentage';
    }



    public function saveCollaterals()
    {
        $this->validate([
            'selectedCollaterals' => 'required|array',
            'selectedCollaterals.*' => 'string',
        ]);

        foreach ($this->selectedCollaterals as $type) {
            DB::table('collateral_types')->updateOrInsert(
                [
                    'loan_product_id' => $this->form['sub_product_id'],
                    'type' => $type
                ],
                ['updated_at' => now()]
            );
        }

        session()->flash('message', 'Collateral types saved successfully.');
    }

    public function storeCharge($charge_id)
    {
        if (DB::table('product_has_charges')
            ->where('product_id', $this->form['sub_product_id'])
            ->where('charge_id', $charge_id)
            ->exists()) {
            DB::table('product_has_charges')
                ->where('product_id', $this->form['sub_product_id'])
                ->where('charge_id', $charge_id)
                ->delete();
        } else {
            DB::table('product_has_charges')->insert([
                'charge_id' => $charge_id,
                'product_id' => $this->form['sub_product_id'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function storeInsurance($insurance_id)
    {
        if (DB::table('product_has_insurance')
            ->where('product_id', $this->form['sub_product_id'])
            ->where('insurance_id', $insurance_id)
            ->exists()) {
            DB::table('product_has_insurance')
                ->where('product_id', $this->form['sub_product_id'])
                ->where('insurance_id', $insurance_id)
                ->delete();
        } else {
            DB::table('product_has_insurance')->insert([
                'insurance_id' => $insurance_id,
                'product_id' => $this->form['sub_product_id'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function removeArray($index)
    {
        unset($this->loan_stages[$index]);
        $this->loan_stages = array_values($this->loan_stages);
    }

    public function setLoanStage()
    {
        if (empty($this->set_stage)) {
            return;
        }

        $stage = json_decode($this->set_stage);
        $this->loan_stages[] = [
            'id' => $stage->id,
            'type' => $stage->type,
            'name' => $stage->name
        ];

        $this->set_stage = '';
    }

    protected function getSubProductData()
    {
        Log::info('Preparing sub-product data for database insertion', [
            'sub_product_id' => $this->form['sub_product_id'],
            'sub_product_name' => $this->form['sub_product_name']
        ]);

        // Map account selections to form fields
        $data = [
            'sub_product_id' => (string) $this->form['sub_product_id'],
            'product_id' => (string) $this->form['product_id'],
            'sub_product_name' => $this->form['sub_product_name'],
            'prefix' => $this->form['prefix'],
            'sub_product_status' => $this->form['sub_product_status'],
            'currency' => $this->form['currency'],
            'collection_account_loan_interest' => (string) $this->form['collection_account_loan_interest'],
            'collection_account_loan_principle' => (string) $this->form['collection_account_loan_principle'],
            'collection_account_loan_charges' => (string) $this->form['collection_account_loan_charges'],
            'collection_account_loan_penalties' => (string) $this->form['collection_account_loan_penalties'],
            'principle_min_value' => $this->form['principle_min_value'],
            'principle_max_value' => $this->form['principle_max_value'],
            'min_term' => $this->form['min_term'],
            'max_term' => $this->form['max_term'],
            'interest_value' => $this->form['interest_value'],
            'interest_tenure' => $this->form['interest_tenure'] ?: 'monthly', // Default to monthly if empty
            'interest_method' => $this->form['interest_method'],
            'amortization_method' => $this->form['amortization_method'],
            'days_in_a_year' => $this->form['days_in_a_year'],
            'days_in_a_month' => $this->form['days_in_a_month'],
            'repayment_strategy' => $this->form['repayment_strategy'],
            'maintenance_fees_value' => $this->form['maintenance_fees_value'],
            'ledger_fees' => $this->form['ledger_fees'] ?: 'monthly', // Default to monthly if empty
            'ledger_fees_value' => $this->form['ledger_fees_value'],
            'lock_guarantee_funds' => $this->form['lock_guarantee_funds'],
            'maintenance_fees' => $this->form['maintenance_fees'],
            'inactivity' => $this->form['inactivity'],
            'requires_approval' => $this->form['requires_approval'],
            'notes' => $this->form['notes'],
            //'institution_id' => auth()->user()->institution_id ?? '1', // Set from authenticated user
            'loan_product_account' => (string) $this->form['loan_product_account'],
            'charges' => $this->form['charges'],
            'loan_multiplier' => $this->form['loan_multiplier'],
            'ltv' => $this->form['ltv'],
            'score_limit' => $this->form['score_limit'],
            'repayment_frequency' => $this->form['repayment_frequency'],
            'interest_account' => (string) $this->form['collection_account_loan_interest'],
            'fees_account' => (string) $this->form['collection_account_loan_charges'],
            'payable_account' => (string) $this->form['collection_account_loan_principle'],
            'insurance_account' => (string) $this->form['collection_account_loan_penalties'],
            'loan_interest_account' => (string) $this->form['collection_account_loan_interest'],
            'loan_charges_account' => (string) $this->form['collection_account_loan_charges'],
            'loan_insurance_account' => (string) $this->form['collection_account_loan_penalties'],
            'charge_product_account' => (string) $this->form['collection_account_loan_charges'],
            'insurance_product_account' => (string) $this->form['collection_account_loan_penalties'],
            'penalty_value' => $this->form['penalty_value']
        ];

        Log::info('Sub-product data prepared successfully', [
            'data_keys' => array_keys($data),
            'data_count' => count($data),
            //'institution_id' => $data['institution_id'],
            'loan_product_account' => $data['loan_product_account']
        ]);

        return $data;
    }

    public function getAccountsProperty()
    {
        return Account::where('category_code', '2100')
            ->get();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getProductsProperty()
    {
        $query = Loan_sub_products::query()
            ->when($this->search, function ($query) {
                $query->where('sub_product_name', 'like', '%' . $this->search . '%');
            })
            ->when($this->filters['status'], function ($query) {
                $query->where('sub_product_status', $this->filters['status']);
            })
            ->when($this->filters['min_amount'], function ($query) {
                $query->where('principle_min_value', '>=', $this->filters['min_amount']);
            })
            ->when($this->filters['interest_rate'], function ($query) {
                $query->where('interest_value', '>=', $this->filters['interest_rate']);
            });

        return $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function addCharge()
    {
        $rules = [
            'newCharge.type' => 'required|in:charge,insurance',
            'newCharge.name' => 'required|string|max:255',
            'newCharge.value_type' => 'required|in:fixed,percentage',
            'newCharge.value' => 'required|numeric|min:0',
            'newCharge.account_id' => 'required|string|max:150'
        ];

        // Add conditional validation for min/max caps when value_type is percentage
        if ($this->newCharge['value_type'] === 'percentage') {
            $rules['newCharge.min_cap'] = 'nullable|numeric|min:0';
            $rules['newCharge.max_cap'] = 'nullable|numeric|min:0';
            
            // If both min and max are set, ensure max is greater than min
            if (!empty($this->newCharge['min_cap']) && !empty($this->newCharge['max_cap'])) {
                $rules['newCharge.max_cap'] .= '|gt:newCharge.min_cap';
            }
        }

        $this->validate($rules);

        $this->charges[] = $this->newCharge;
        
        $this->newCharge = [
            'type' => 'charge',
            'name' => '',
            'value_type' => 'fixed',
            'value' => '',
            'account_id' => '',
            'min_cap' => '',
            'max_cap' => ''
        ];

        // Reset account selection variables
        $this->new_charge_category = '';
        $this->new_charge_subcategory = '';

        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => 'Charge/Insurance added successfully!'
        ]);
    }

    public function removeCharge($index)
    {
        unset($this->charges[$index]);
        $this->charges = array_values($this->charges);
        
        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => 'Charge/Insurance removed successfully!'
        ]);
    }

    /**
     * Begin editing a charge at the given index
     */
    public function startEditCharge(int $index): void
    {
        if (!isset($this->charges[$index])) {
            return;
        }
        $charge = $this->charges[$index];
        $this->newCharge = [
            'type' => $charge['type'] ?? 'charge',
            'name' => $charge['name'] ?? '',
            'value_type' => $charge['value_type'] ?? 'fixed',
            'value' => $charge['value'] ?? 0,
            'account_id' => $charge['account_id'] ?? '',
            'min_cap' => $charge['min_cap'] ?? '',
            'max_cap' => $charge['max_cap'] ?? ''
        ];
        $this->editingChargeIndex = $index;
        $this->showAddChargeModal = true;
    }

    /**
     * Save the edited charge back to the array
     */
    public function updateCharge(): void
    {
        $rules = [
            'newCharge.name' => 'required|string|max:255',
            'newCharge.value_type' => 'required|in:fixed,percentage',
            'newCharge.value' => 'required|numeric|min:0',
            'newCharge.account_id' => 'required|string|max:150',
        ];

        // Add conditional validation for min/max caps when value_type is percentage
        if ($this->newCharge['value_type'] === 'percentage') {
            $rules['newCharge.min_cap'] = 'nullable|numeric|min:0';
            $rules['newCharge.max_cap'] = 'nullable|numeric|min:0';
            
            // If both min and max are set, ensure max is greater than min
            if (!empty($this->newCharge['min_cap']) && !empty($this->newCharge['max_cap'])) {
                $rules['newCharge.max_cap'] .= '|gt:newCharge.min_cap';
            }
        }

        $this->validate($rules);

        if ($this->editingChargeIndex === null || !isset($this->charges[$this->editingChargeIndex])) {
            return;
        }

        $original = $this->charges[$this->editingChargeIndex];
        $this->charges[$this->editingChargeIndex] = array_merge($original, [
            'name' => $this->newCharge['name'],
            'value_type' => $this->newCharge['value_type'],
            'value' => $this->newCharge['value'],
            'account_id' => $this->newCharge['account_id'],
            'min_cap' => $this->newCharge['min_cap'] ?? '',
            'max_cap' => $this->newCharge['max_cap'] ?? '',
        ]);

        $this->resetChargeForm();
        $this->editingChargeIndex = null;
        $this->showAddChargeModal = false;

        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => 'Charge updated successfully!'
        ]);
    }

    protected function saveCharges($loanProduct)
    {
        Log::info('Starting to save charges/insurance for loan product', [
            'loan_product_id' => $loanProduct->id,
            'sub_product_id' => $loanProduct->sub_product_id,
            'charges_count' => count($this->charges)
        ]);

        try {
        // Clear existing charges
            Log::info('Clearing existing charges for loan product', [
                'loan_product_id' => $loanProduct->id
            ]);
            
            $deletedCount = $loanProduct->charges()->delete();
            Log::info('Existing charges cleared', ['deleted_count' => $deletedCount]);
        
        // Save new charges
            if (!empty($this->charges)) {
                Log::info('Saving new charges/insurance', [
                    'charges_to_save' => count($this->charges),
                    'charges_data' => $this->charges
                ]);

                foreach ($this->charges as $index => $charge) {
                    Log::info('Saving charge/insurance', [
                        'index' => $index,
                        'charge_data' => $charge
                    ]);

                    $savedCharge = $loanProduct->charges()->create([
                'type' => $charge['type'],
                'name' => $charge['name'],
                'value_type' => $charge['value_type'],
                        'value' => $charge['value'],
                        'account_id' => (string) $charge['account_id'],
                        'min_cap' => $charge['min_cap'] ?? null,
                        'max_cap' => $charge['max_cap'] ?? null,
                    ]);

                    Log::info('Charge/insurance saved successfully', [
                        'charge_id' => $savedCharge->id,
                        'charge_name' => $savedCharge->name,
                        'charge_type' => $savedCharge->type
                    ]);
                }

                Log::info('All charges/insurance saved successfully', [
                    'total_saved' => count($this->charges)
                ]);
            } else {
                Log::info('No charges/insurance to save');
            }

        } catch (\Exception $e) {
            Log::error('Error saving charges/insurance for loan product', [
                'loan_product_id' => $loanProduct->id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'charges_data' => $this->charges
            ]);
            throw $e;
        }
    }

    public function createProduct()
    {
        Log::info('Starting loan product creation process', [
            'user_id' => auth()->id(),
            //'institution_id' => auth()->user()->institution_id ?? 'unknown',
            'form_data' => $this->form
        ]);

        try {
            // Validate form data
            Log::info('Validating form data for loan product creation');
            $this->validate();
            Log::info('Form validation passed successfully');

            DB::beginTransaction();
            Log::info('Database transaction started for loan product creation');

            // Create the loan product
            Log::info('Creating loan product record', [
                'sub_product_id' => $this->form['sub_product_id'],
                'sub_product_name' => $this->form['sub_product_name']
            ]);

            $product = Loan_sub_products::create($this->getSubProductData());
            
            Log::info('Loan product created successfully', [
                'product_id' => $product->id,
                'sub_product_id' => $product->sub_product_id,
                'sub_product_name' => $product->sub_product_name
            ]);

            // Save loan stages
            if (!empty($this->loan_stages)) {
                Log::info('Saving loan stages', [
                    'stages_count' => count($this->loan_stages),
                    'stages' => $this->loan_stages
                ]);

            foreach ($this->loan_stages as $stage) {
                DB::table('loan_stages')->insert([
                    'loan_product_id' => $product->id,
                    'stage_id' => $stage['id'],
                    'stage_type' => $stage['type'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                }
                Log::info('Loan stages saved successfully', ['stages_count' => count($this->loan_stages)]);
            } else {
                Log::info('No loan stages to save');
            }

            // Save collaterals
            if (!empty($this->selectedCollaterals)) {
                Log::info('Saving collateral types', [
                    'collaterals_count' => count($this->selectedCollaterals),
                    'collaterals' => $this->selectedCollaterals
                ]);

            foreach ($this->selectedCollaterals as $type) {
                // Use insertOrIgnore to prevent duplicate key errors
                DB::table('collateral_types')->insertOrIgnore([
                    'loan_product_id' => $product->id,
                    'type' => $type,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                }
                Log::info('Collateral types saved successfully', ['collaterals_count' => count($this->selectedCollaterals)]);
            } else {
                Log::info('No collateral types to save');
            }

            // Save charges
            if (!empty($this->charges)) {
                Log::info('Saving charges/insurance', [
                    'charges_count' => count($this->charges),
                    'charges' => $this->charges
                ]);

            $this->saveCharges($product);
                Log::info('Charges/insurance saved successfully', ['charges_count' => count($this->charges)]);
            } else {
                Log::info('No charges/insurance to save');
            }

            DB::commit();
            Log::info('Database transaction committed successfully for loan product creation', [
                'product_id' => $product->id,
                'sub_product_id' => $product->sub_product_id
            ]);

            $this->resetForm();
            $this->showAddModal = false;
            
            Log::info('Loan product creation completed successfully', [
                'product_id' => $product->id,
                'sub_product_id' => $product->sub_product_id,
                'sub_product_name' => $product->sub_product_name
            ]);

            session()->flash('message', 'Loan product created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Validation failed during loan product creation', [
                'user_id' => auth()->id(),
                'errors' => $e->errors(),
                'form_data' => $this->form
            ]);
            session()->flash('error', 'Validation failed. Please check your input and try again.');
            throw $e;

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Database error during loan product creation', [
                'user_id' => auth()->id(),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'unknown',
                'bindings' => $e->getBindings() ?? [],
                'form_data' => $this->form
            ]);
            session()->flash('error', 'Database error occurred. Please try again or contact support.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unexpected error during loan product creation', [
                'user_id' => auth()->id(),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'form_data' => $this->form
            ]);
            session()->flash('error', 'An unexpected error occurred. Please try again or contact support.');
        }
    }

    public function editProduct($id)
    {
        Log::info('Starting editProduct method', ['product_id' => $id]);
        
        $product = Loan_sub_products::findOrFail($id);
        $this->editingProduct = $product;
        
        Log::info('Product found', [
            'product_id' => $product->id,
            'sub_product_id' => $product->sub_product_id,
            'sub_product_name' => $product->sub_product_name,
            'loan_product_account' => $product->loan_product_account,
            'collection_account_loan_interest' => $product->collection_account_loan_interest,
            'collection_account_loan_principle' => $product->collection_account_loan_principle,
            'collection_account_loan_charges' => $product->collection_account_loan_charges,
            'collection_account_loan_penalties' => $product->collection_account_loan_penalties
        ]);
        
        $this->form = [
            'sub_product_id' => $product->sub_product_id,
            'product_id' => $product->product_id,
            'sub_product_name' => $product->sub_product_name,
            'prefix' => $product->prefix,
            'sub_product_status' => $product->sub_product_status,
            'currency' => $product->currency,
            'collection_account_loan_interest' => $product->collection_account_loan_interest,
            'collection_account_loan_principle' => $product->collection_account_loan_principle,
            'collection_account_loan_charges' => $product->collection_account_loan_charges,
            'collection_account_loan_penalties' => $product->collection_account_loan_penalties,
            'principle_min_value' => $product->principle_min_value,
            'principle_max_value' => $product->principle_max_value,
            'min_term' => $product->min_term,
            'max_term' => $product->max_term,
            'interest_value' => $product->interest_value,
            'interest_tenure' => $product->interest_tenure,
            'interest_method' => $product->interest_method,
            'amortization_method' => $product->amortization_method,
            'days_in_a_year' => $product->days_in_a_year ?? 365,
            'days_in_a_month' => $product->days_in_a_month ?? 30,
            'repayment_strategy' => $product->repayment_strategy,
            'maintenance_fees_value' => $product->maintenance_fees_value,
            'ledger_fees' => $product->ledger_fees,
            'ledger_fees_value' => $product->ledger_fees_value,
            'lock_guarantee_funds' => $product->lock_guarantee_funds,
            'maintenance_fees' => $product->maintenance_fees,
            'inactivity' => $product->inactivity,
            'requires_approval' => $product->requires_approval,
            'notes' => $product->notes,
            //'institution_id' => $product->institution_id,
            'loan_product_account' => $product->loan_product_account,
            'charges' => $product->charges,
            'loan_multiplier' => $product->loan_multiplier,
            'ltv' => $product->ltv,
            'score_limit' => $product->score_limit,
            'repayment_frequency' => $product->repayment_frequency,
            'interest_account' => $product->interest_account,
            'fees_account' => $product->fees_account,
            'payable_account' => $product->payable_account,
            'insurance_account' => $product->insurance_account,
            'loan_interest_account' => $product->loan_interest_account,
            'loan_charges_account' => $product->loan_charges_account,
            'loan_insurance_account' => $product->loan_insurance_account,
            'charge_product_account' => $product->charge_product_account,
            'insurance_product_account' => $product->insurance_product_account,
            'penalty_value' => $product->penalty_value
        ];

        Log::info('Form data populated', [
            'form_keys' => array_keys($this->form),
            'loan_product_account' => $this->form['loan_product_account'],
            'collection_account_loan_interest' => $this->form['collection_account_loan_interest']
        ]);

        // Populate account selection properties
        $this->populateAccountSelectionProperties($product);

        // Load charges
        $this->loadCharges($product);

        // Load collaterals
        $this->loadCollaterals($product);

        // Load loan stages
        $this->loadLoanStages($product);

        Log::info('Edit product setup completed', [
            'loan_category' => $this->loan_category,
            'loan_subcategory' => $this->loan_subcategory,
            'charges_count' => count($this->charges),
            'selectedCollaterals_count' => count($this->selectedCollaterals),
            'loan_stages_count' => count($this->loan_stages)
        ]);

        $this->activeTab = 'basic'; // Reset to first tab when editing
        $this->showAddModal = true;
    }

    /**
     * Populate account selection properties based on selected accounts
     */
    private function populateAccountSelectionProperties($product)
    {
        Log::info('Populating account selection properties', [
            'product_id' => $product->id,
            'loan_product_account' => $product->loan_product_account,
            'collection_account_loan_interest' => $product->collection_account_loan_interest,
            'collection_account_loan_principle' => $product->collection_account_loan_principle,
            'collection_account_loan_charges' => $product->collection_account_loan_charges,
            'collection_account_loan_penalties' => $product->collection_account_loan_penalties
        ]);

        // Check what accounts exist in the database
        $allAccounts = Account::select('account_number', 'account_name', 'major_category_code', 'category_code')->get();
        Log::info('Available accounts in database', [
            'total_accounts' => $allAccounts->count(),
            'sample_accounts' => $allAccounts->take(5)->toArray()
        ]);

        // Helper function to format account number with leading "0"
        $formatAccountNumber = function($accountNumber) {
            if (empty($accountNumber)) return '';
            // Add leading "0" if it doesn't exist
            return strpos($accountNumber, '0') === 0 ? $accountNumber : '0' . $accountNumber;
        };

        // Loan account selection
        if (!empty($product->loan_product_account)) {
            $formattedAccountNumber = $formatAccountNumber($product->loan_product_account);
            $loanAccount = Account::where('account_number', $formattedAccountNumber)->first();
            if ($loanAccount) {
                $this->loan_category = $loanAccount->major_category_code;
                $this->loan_subcategory = $loanAccount->category_code;
                Log::info('Loan account selection populated', [
                    'original_account_number' => $product->loan_product_account,
                    'formatted_account_number' => $formattedAccountNumber,
                    'major_category_code' => $loanAccount->major_category_code,
                    'category_code' => $loanAccount->category_code
                ]);
            } else {
                Log::warning('Loan account not found', [
                    'original_account_number' => $product->loan_product_account,
                    'formatted_account_number' => $formattedAccountNumber,
                    'available_accounts' => $allAccounts->pluck('account_number')->toArray()
                ]);
            }
        }

        // Interest account selection
        if (!empty($product->collection_account_loan_interest)) {
            $formattedAccountNumber = $formatAccountNumber($product->collection_account_loan_interest);
            $interestAccount = Account::where('account_number', $formattedAccountNumber)->first();
            if ($interestAccount) {
                $this->interest_category = $interestAccount->major_category_code;
                $this->interest_subcategory = $interestAccount->category_code;
                Log::info('Interest account selection populated', [
                    'original_account_number' => $product->collection_account_loan_interest,
                    'formatted_account_number' => $formattedAccountNumber,
                    'major_category_code' => $interestAccount->major_category_code,
                    'category_code' => $interestAccount->category_code
                ]);
            } else {
                Log::warning('Interest account not found', [
                    'original_account_number' => $product->collection_account_loan_interest,
                    'formatted_account_number' => $formattedAccountNumber,
                    'available_accounts' => $allAccounts->pluck('account_number')->toArray()
                ]);
            }
        }

        // Principle account selection
        if (!empty($product->collection_account_loan_principle)) {
            $formattedAccountNumber = $formatAccountNumber($product->collection_account_loan_principle);
            $principleAccount = Account::where('account_number', $formattedAccountNumber)->first();
            if ($principleAccount) {
                $this->principle_category = $principleAccount->major_category_code;
                $this->principle_subcategory = $principleAccount->category_code;
                Log::info('Principle account selection populated', [
                    'original_account_number' => $product->collection_account_loan_principle,
                    'formatted_account_number' => $formattedAccountNumber,
                    'major_category_code' => $principleAccount->major_category_code,
                    'category_code' => $principleAccount->category_code
                ]);
            } else {
                Log::warning('Principle account not found', [
                    'original_account_number' => $product->collection_account_loan_principle,
                    'formatted_account_number' => $formattedAccountNumber,
                    'available_accounts' => $allAccounts->pluck('account_number')->toArray()
                ]);
            }
        }

        // Charge account selection
        if (!empty($product->collection_account_loan_charges)) {
            $formattedAccountNumber = $formatAccountNumber($product->collection_account_loan_charges);
            $chargeAccount = Account::where('account_number', $formattedAccountNumber)->first();
            if ($chargeAccount) {
                $this->charge_category = $chargeAccount->major_category_code;
                $this->charge_subcategory = $chargeAccount->category_code;
                Log::info('Charge account selection populated', [
                    'original_account_number' => $product->collection_account_loan_charges,
                    'formatted_account_number' => $formattedAccountNumber,
                    'major_category_code' => $chargeAccount->major_category_code,
                    'category_code' => $chargeAccount->category_code
                ]);
            } else {
                Log::warning('Charge account not found', [
                    'original_account_number' => $product->collection_account_loan_charges,
                    'formatted_account_number' => $formattedAccountNumber,
                    'available_accounts' => $allAccounts->pluck('account_number')->toArray()
                ]);
            }
        }

        // Insurance account selection
        if (!empty($product->collection_account_loan_penalties)) {
            $formattedAccountNumber = $formatAccountNumber($product->collection_account_loan_penalties);
            $insuranceAccount = Account::where('account_number', $formattedAccountNumber)->first();
            if ($insuranceAccount) {
                $this->insurance_category = $insuranceAccount->major_category_code;
                $this->insurance_subcategory = $insuranceAccount->category_code;
                Log::info('Insurance account selection populated', [
                    'original_account_number' => $product->collection_account_loan_penalties,
                    'formatted_account_number' => $formattedAccountNumber,
                    'major_category_code' => $insuranceAccount->major_category_code,
                    'category_code' => $insuranceAccount->category_code
                ]);
            } else {
                Log::warning('Insurance account not found', [
                    'original_account_number' => $product->collection_account_loan_penalties,
                    'formatted_account_number' => $formattedAccountNumber,
                    'available_accounts' => $allAccounts->pluck('account_number')->toArray()
                ]);
            }
        }
    }

    /**
     * Load charges for the product
     */
    private function loadCharges($product)
    {
        Log::info('Loading charges for product', [
            'product_id' => $product->id,
            'sub_product_id' => $product->sub_product_id
        ]);

        $this->charges = [];
        
        // Check what charges exist in the database for this product
        $existingCharges = DB::table('loan_product_charges')
            ->where('loan_product_id', $product->sub_product_id)
            ->get();
        
        Log::info('Existing charges in database', [
            'charges_count' => $existingCharges->count(),
            'charges' => $existingCharges->toArray(),
            'loan_product_id_searched' => $product->sub_product_id
        ]);
        
        // Load charges directly from the database since relationship is not working
        if ($existingCharges->count() > 0) {
            $this->charges = $existingCharges->map(function ($charge) {
                return [
                    'type' => $charge->type,
                    'name' => $charge->name,
                    'value_type' => $charge->value_type,
                    'value' => $charge->value,
                    'account_id' => $charge->account_id ?? ''
                ];
            })->toArray();
            
            Log::info('Charges loaded successfully from direct query', [
                'charges_count' => count($this->charges),
                'charges' => $this->charges
            ]);
        } else {
            Log::info('No charges found in database');
        }

        Log::info('Final charges array', [
            'charges_count' => count($this->charges),
            'charges' => $this->charges
        ]);
    }

    /**
     * Load collaterals for the product
     */
    private function loadCollaterals($product)
    {
        $this->selectedCollaterals = [];
        $collaterals = DB::table('collateral_types')
            ->where('loan_product_id', $product->id)
            ->pluck('type')
            ->toArray();
        
        $this->selectedCollaterals = $collaterals;
    }

    public function updateProduct()
    {
        try {
            DB::beginTransaction();

            // Update the loan product
            $data = $this->getSubProductData();
            // Remove empty values so we don't overwrite existing DB values with blanks
            $filteredData = array_filter($data, function ($value) {
                return !($value === '' || $value === null);
            });
            $this->editingProduct->update($filteredData);

            // Update loan stages
            DB::table('loan_stages')
                ->where('loan_product_id', $this->editingProduct->id)
                ->delete();

            foreach ($this->loan_stages as $stage) {
                DB::table('loan_stages')->insert([
                    'loan_product_id' => $this->editingProduct->id,
                    'stage_id' => $stage['id'],
                    'stage_type' => $stage['type'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Update collaterals
            DB::table('collateral_types')
                ->where('loan_product_id', $this->editingProduct->id)
                ->delete();

            foreach ($this->selectedCollaterals as $type) {
                // Use insertOrIgnore to prevent duplicate key errors
                DB::table('collateral_types')->insertOrIgnore([
                    'loan_product_id' => $this->editingProduct->id,
                    'type' => $type,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $this->saveCharges($this->editingProduct);

            DB::commit();

            $this->resetForm();
            $this->showAddModal = false;
            session()->flash('message', 'Loan product updated successfully.');
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Loan product updated successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating loan product: ' . $e->getMessage());
            session()->flash('error', 'Error updating loan product. Please try again.');
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error updating loan product: ' . ($e->getMessage() ?: 'Unexpected error')
            ]);
        }
    }

    public function deleteProduct($id)
    {
        try {
            $product = Loan_sub_products::findOrFail($id);
            $product->delete();
            session()->flash('message', 'Loan product deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting loan product: ' . $e->getMessage());
            session()->flash('error', 'Error deleting loan product. Please try again.');
        }
    }

    public function resetForm()
    {
        // Reset form data
        $this->form = [
            'sub_product_id' => $this->generateProductId(), // Generate new ID for next product
            'product_id' => '',
            'sub_product_name' => '',
            'prefix' => '',
            'sub_product_status' => '1',
            'currency' => 'TZS',
            'collection_account_loan_interest' => '',
            'collection_account_loan_principle' => '',
            'collection_account_loan_charges' => '',
            'collection_account_loan_penalties' => '',
            'principle_min_value' => 0,
            'principle_max_value' => 0,
            'min_term' => 0,
            'max_term' => 0,
            'interest_value' => '',
            'interest_tenure' => '',
            'interest_method' => 'flat',
            'amortization_method' => 'equal_installments',
            'days_in_a_year' => 365,
            'days_in_a_month' => 30,
            'repayment_strategy' => 'standard',
            'maintenance_fees_value' => 0,
            'ledger_fees' => '',
            'ledger_fees_value' => 0,
            'lock_guarantee_funds' => '0',
            'maintenance_fees' => '0',
            'inactivity' => '0',
            'requires_approval' => '1',
            'notes' => '',
            //'institution_id' => '',
            'loan_product_account' => '',
            'charges' => '',
            'loan_multiplier' => '',
            'ltv' => '',
            'score_limit' => '',
            'repayment_frequency' => 'monthly',
            'interest_account' => '',
            'fees_account' => '',
            'payable_account' => '',
            'insurance_account' => '',
            'loan_interest_account' => '',
            'loan_charges_account' => '',
            'loan_insurance_account' => '',
            'charge_product_account' => '',
            'insurance_product_account' => '',
            'penalty_value' => ''
        ];

        // Reset account selection properties
        $this->loan_category = '';
        $this->loan_subcategory = '';
        $this->interest_category = '';
        $this->interest_subcategory = '';
        $this->principle_category = '';
        $this->principle_subcategory = '';
        $this->charge_category = '';
        $this->charge_subcategory = '';
        $this->insurance_category = '';
        $this->insurance_subcategory = '';
        
        // Reset hierarchical selector properties
        $this->selectedLoanProductLevel2 = '';
        $this->selectedPrincipalLevel2 = '';
        $this->selectedChargesLevel2 = '';
        $this->selectedInsuranceLevel2 = '';
        $this->selectedInterestLevel2 = '';
        $this->selectedPenaltiesLevel2 = '';
        
        // Reset new charge form properties
        $this->new_charge_category = '';
        $this->new_charge_subcategory = '';
        
        // Reset charges and new charge form
        $this->charges = [];
        $this->newCharge = [
            'type' => 'charge',
            'name' => '',
            'value_type' => 'fixed',
            'value' => '',
            'account_id' => ''
        ];
        
        // Reset other form properties
        $this->selectedCollaterals = [];
        $this->loan_stages = [];
        $this->set_stage = '';
        $this->editingProduct = null;
        
        // Reset search and filters
        $this->search = '';
        $this->filters = [
            'status' => '',
            'type' => '',
            'min_amount' => '',
            'interest_rate' => ''
        ];
    }

    public function resetFilters()
    {
        $this->filters = [
            'status' => '',
            'type' => '',
            'min_amount' => '',
            'interest_rate' => ''
        ];
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->showAddModal = false;
        $this->activeTab = 'basic'; // Reset to first tab when modal is closed
    }

    public function openModal()
    {
        $this->resetForm();
        $this->activeTab = 'basic';
        $this->showAddModal = true;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    // ========================================
    // CHARGES AND INSURANCE MANAGEMENT
    // ========================================

    public function editCharge($chargeId)
    {
        foreach ($this->charges as $charge) {
            if ($charge['id'] == $chargeId) {
                $this->chargeForm = [
                    'name' => $charge['name'],
                    'value_type' => $charge['value_type'],
                    'value' => $charge['value'],
                    'account_id' => $charge['account_id']
                ];
                $this->editingChargeId = $chargeId;
                $this->showAddChargeModal = true;
                break;
            }
        }
    }

    public function deleteCharge($chargeId)
    {
        $this->charges = array_filter($this->charges, function ($charge) use ($chargeId) {
            return $charge['id'] != $chargeId;
        });
        $this->charges = array_values($this->charges); // Re-index array
    }

    public function addInsurance()
    {
        $this->validate([
            'insuranceForm.name' => 'required|string|max:255',
            'insuranceForm.value_type' => 'required|in:fixed,percentage',
            'insuranceForm.value' => 'required|numeric|min:0',
            'insuranceForm.account_id' => 'required|string|max:150'
        ]);

        if ($this->editingInsuranceId) {
            // Update existing insurance
            foreach ($this->insurance as $key => $item) {
                if ($item['id'] == $this->editingInsuranceId) {
                    $this->insurance[$key] = array_merge($item, $this->insuranceForm, ['type' => 'insurance']);
                    break;
                }
            }
        } else {
            // Add new insurance
            $this->insurance[] = array_merge($this->insuranceForm, [
                'id' => uniqid(),
                'type' => 'insurance'
            ]);
        }

        $this->resetInsuranceForm();
        $this->showAddInsuranceModal = false;
    }

    public function editInsurance($insuranceId)
    {
        foreach ($this->insurance as $item) {
            if ($item['id'] == $insuranceId) {
                $this->insuranceForm = [
                    'name' => $item['name'],
                    'value_type' => $item['value_type'],
                    'value' => $item['value'],
                    'account_id' => $item['account_id']
                ];
                $this->editingInsuranceId = $insuranceId;
                $this->showAddInsuranceModal = true;
                break;
            }
        }
    }

    public function deleteInsurance($insuranceId)
    {
        $this->insurance = array_filter($this->insurance, function ($item) use ($insuranceId) {
            return $item['id'] != $insuranceId;
        });
        $this->insurance = array_values($this->insurance); // Re-index array
    }

    public function resetChargeForm()
    {
        $this->newCharge = [
            'type' => 'charge',
            'name' => '',
            'value_type' => 'fixed',
            'value' => '',
            'account_id' => '',
            'min_cap' => '',
            'max_cap' => ''
        ];
        $this->editingChargeId = null;
        $this->editingChargeIndex = null;
    }

    public function resetInsuranceForm()
    {
        $this->insuranceForm = [
            'name' => '',
            'value_type' => 'fixed',
            'value' => 0,
            'account_id' => ''
        ];
        $this->editingInsuranceId = null;
    }

    public function openAddChargeModal()
    {
        $this->resetChargeForm();
        $this->showAddChargeModal = true;
    }

    public function openAddInsuranceModal()
    {
        $this->resetInsuranceForm();
        $this->showAddInsuranceModal = true;
    }

    public function closeChargeModal()
    {
        $this->resetChargeForm();
        $this->showAddChargeModal = false;
    }

    public function closeInsuranceModal()
    {
        $this->resetInsuranceForm();
        $this->showAddInsuranceModal = false;
    }

    public function render()
    {
        $this->loadData();
        return view('livewire.products-management.loans', [
            'committees' => $this->committees,
            'departments' => $this->departments,
            'accounts' => $this->accounts,
            'loan_accounts' => $this->loan_accounts,
            'charge_accounts' => $this->charge_accounts,
            'insurance_accounts' => $this->insurance_accounts,
            'gl_accounts' => $this->gl_accounts,
            'loan_stages' => $this->loan_stages,
        ]);
    }

    // Generate 6-digit product ID
    private function generateProductId()
    {
        do {
            $productId = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Loan_sub_products::where('sub_product_id', $productId)->exists());
        
        return $productId;
    }
}
