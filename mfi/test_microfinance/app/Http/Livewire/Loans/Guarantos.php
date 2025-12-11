<?php

namespace App\Http\Livewire\Loans;

use App\Models\AccountsModel;
use App\Models\CollateralModel;
use App\Models\LoansModel;
use App\Models\LoanGuarantor;
use App\Models\LoanCollateral;
use App\Services\CollateralManagementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use App\Models\LockedAmount;

class Guarantos extends Component
{
    use WithFileUploads;

    // Step management
    public $currentStep = 1;
    public $totalSteps = 4;

    // Step 1: Guarantor
    public $guarantor_type = 'self_guarantee';
    public $guarantorType = 'self_guarantee'; // Alias for compatibility
    public $search = '';
    public $clients = [];
    public $selected_guarantor_id = null;
    public $selectedGuarantorId = null; // Alias for compatibility
    public $guarantor_name = '';
    public $guarantorRelationship = ''; // Alias for compatibility
    public $show_client_dropdown = false;
    public $relationship = '';

    // Step 2: Collateral Types
    public $available_collateral_types = ['deposits', 'savings', 'shares', 'physical_collaterals'];
    public $selected_collateral_types = [];

    // Step 3: Collateral Details
    public $member_number;
    public $loan_id;
    public $selected_accounts = [];
    public $collateral_amounts = [];
    
    // Additional properties for loan-application compatibility
    public $selectedAccountId = '';
    public $collateralAmount = 0;
    public $collateralCommitted = false;
    public $showPhysicalCollateral = false;
    public $physicalCollateralDescription = '';
    public $physicalCollateralValue = '';
    public $physicalCollateralLocation = '';
    public $physicalCollateralOwnerName = '';
    public $physicalCollateralOwnerContact = '';
    
    public $physical_collateral = [
        'collateral_id' => '',
        'description' => '',
        'value' => '',
        'location' => '',
        'type_of_owner' => 'Individual',
        'owner_name' => '',
        'owner_nida' => '',
        'owner_contact' => '',
        'owner_address' => '',
        'valuation_date' => '',
        'valuation_method' => '',
        'valuer_name' => '',
        'insurance_policy_number' => '',
        'insurance_company_name' => '',
        'insurance_coverage_details' => '',
        'insurance_expiration_date' => '',
    ];

    // Step 4: Review
    public $collateral_summary = [];
    public $guarantor_id = null;
    
    // Additional properties for loan-application compatibility
    public $existingGuarantorData = [];
    public $existingCollateralData = [];
    public $committedCollateralAmount = 0;
    public $committedPhysicalCollateralValue = 0;
    
    // Debug properties
    public $debugInfo = [];
    public $showDebugInfo = false;
    
    // Loan type and product information
    public $loanType = '';
    public $loanProduct = null;
    
    // Warnings and violations
    public $warnings = [];
    public $step3SavingsViolations = [];

    // Service
    protected $collateralService;

    // Validation rules
    protected $rules = [
        'guarantor_type' => 'required|in:self_guarantee,third_party_guarantee',
        'guarantorType' => 'required|in:self_guarantee,third_party_guarantee',
        'selected_guarantor_id' => 'required_if:guarantor_type,third_party_guarantee',
        'selectedGuarantorId' => 'required_if:guarantorType,third_party_guarantee',
        'relationship' => 'required_if:guarantor_type,third_party_guarantee',
        'guarantorRelationship' => 'required_if:guarantorType,third_party_guarantee',
        'selected_collateral_types' => 'required|array|min:1',
        'selected_collateral_types.*' => 'string',
        'selected_accounts.*' => 'required_if:selected_collateral_types,deposits,savings,shares|numeric|min:1',
        'collateral_amounts.*' => 'required_if:selected_collateral_types,deposits,savings,shares|numeric|min:1',
        'selectedAccountId' => 'required_if:showPhysicalCollateral,false',
        'collateralAmount' => 'required_if:showPhysicalCollateral,false|numeric|min:0',
        'physical_collateral.collateral_id' => 'required_if:selected_collateral_types,physical_collaterals',
        'physical_collateral.description' => 'required_if:selected_collateral_types,physical_collaterals',
        'physical_collateral.value' => 'required_if:selected_collateral_types,physical_collaterals|numeric|min:1',
        'physical_collateral.location' => 'required_if:selected_collateral_types,physical_collaterals',
        'physical_collateral.owner_name' => 'required_if:selected_collateral_types,physical_collaterals',
        'physical_collateral.owner_nida' => 'required_if:selected_collateral_types,physical_collaterals',
        'physical_collateral.owner_contact' => 'required_if:selected_collateral_types,physical_collaterals',
        'physical_collateral.owner_address' => 'required_if:selected_collateral_types,physical_collaterals',
        'physicalCollateralDescription' => 'required_if:showPhysicalCollateral,true',
        'physicalCollateralValue' => 'required_if:showPhysicalCollateral,true|numeric|min:1',
        'physicalCollateralLocation' => 'required_if:showPhysicalCollateral,true',
        'physicalCollateralOwnerName' => 'required_if:showPhysicalCollateral,true',
        'physicalCollateralOwnerContact' => 'required_if:showPhysicalCollateral,true',
    ];

    protected $messages = [
        'guarantor_type.required' => 'Please select a guarantor type.',
        'guarantorType.required' => 'Please select a guarantor type.',
        'selected_guarantor_id.required_if' => 'Please select a guarantor.',
        'selectedGuarantorId.required_if' => 'Please select a guarantor.',
        'relationship.required_if' => 'Please specify your relationship with the guarantor.',
        'guarantorRelationship.required_if' => 'Please specify your relationship with the guarantor.',
        'selected_collateral_types.required' => 'Please select at least one collateral type.',
        'selected_accounts.*.required_if' => 'Please select an account.',
        'selected_accounts.*.numeric' => 'Account ID must be a valid number.',
        'selected_accounts.*.min' => 'Account ID must be greater than 0.',
        'collateral_amounts.*.required_if' => 'Please enter the amount.',
        'collateral_amounts.*.numeric' => 'Amount must be a valid number.',
        'collateral_amounts.*.min' => 'Amount must be greater than 0.',
        'selectedAccountId.required_if' => 'Please select an account for collateral.',
        'collateralAmount.required_if' => 'Please enter the collateral amount.',
        'collateralAmount.numeric' => 'Collateral amount must be a number.',
        'physical_collateral.collateral_id.required_if' => 'Collateral ID is required.',
        'physicalCollateralDescription.required_if' => 'Please describe the physical collateral.',
        'physicalCollateralValue.required_if' => 'Please enter the collateral value.',
        'physicalCollateralValue.numeric' => 'Collateral value must be a number.',
        'physicalCollateralLocation.required_if' => 'Please enter the collateral location.',
        'physicalCollateralOwnerName.required_if' => 'Please enter the owner name.',
        'physicalCollateralOwnerContact.required_if' => 'Please enter the owner contact.',
        'physical_collateral.description.required_if' => 'Description is required.',
        'physical_collateral.value.required_if' => 'Value is required.',
        'physical_collateral.value.numeric' => 'Value must be a number.',
        'physical_collateral.value.min' => 'Value must be greater than 0.',
        'physical_collateral.location.required_if' => 'Location is required.',
        'physical_collateral.owner_name.required_if' => 'Owner name is required.',
        'physical_collateral.owner_nida.required_if' => 'Owner NIDA is required.',
        'physical_collateral.owner_contact.required_if' => 'Owner contact is required.',
        'physical_collateral.owner_address.required_if' => 'Owner address is required.',
    ];

    public function boot(CollateralManagementService $collateralService)
    {
        Log::info('=== GUARANTOS COMPONENT BOOT START ===', [
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
            'currentloanID' => Session::get('currentloanID')
        ]);

        $this->collateralService = $collateralService;
        
        // Set loan_id from session if not already set
        $sessionLoanId = Session::get('currentloanID');
        if ($sessionLoanId && !$this->loan_id) {
            $this->loan_id = $sessionLoanId;
        }

        // Pre-load all saved guarantor and collateral data comprehensively
        $this->preloadAllSavedData();

        Log::info('=== GUARANTOS COMPONENT BOOT END ===', [
            'loan_id' => $this->loan_id ?? null,
            'data_loaded' => true
        ]);
    }

    /**
     * Helper method to get loan by ID (handles both numeric and string loan_id)
     * @param mixed $loanId - Can be numeric ID or string loan_id
     * @return LoansModel|null
     */
    private function getLoanById($loanId)
    {
        if (!$loanId) {
            return null;
        }

        // Check if it's numeric (database ID)
        if (is_numeric($loanId)) {
            return LoansModel::find($loanId);
        }

        // Otherwise it's a string loan_id like "LN202510169920"
        return LoansModel::where('loan_id', $loanId)->first();
    }

    public function mount()
    {
        Log::info('=== GUARANTOS COMPONENT MOUNT START ===', [
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
            'currentloanID' => Session::get('currentloanID')
        ]);

        // Set loan_id from session - the session contains the actual loan ID
        $sessionLoanId = Session::get('currentloanID');
        if ($sessionLoanId && !$this->loan_id) {
            // Get the loan to extract both numeric ID and other info
            $loan = $this->getLoanById($sessionLoanId);
            if ($loan) {
                // Store the NUMERIC ID for all database queries
                $this->loan_id = $loan->id;
                $this->member_number = $loan->client_number;

                // Load loan type information
                $this->loanType = $loan->loan_type_2 ?? 'New';
                $this->loanProduct = $loan->loanProduct;

                // Load existing guarantor and collateral data
                $this->loadExistingGuarantorData();

                // Initialize aliases for compatibility
                $this->guarantorType = $this->guarantor_type;
                $this->selectedGuarantorId = $this->selected_guarantor_id;
                $this->guarantorRelationship = $this->relationship;
            }
        }

        Log::info('=== GUARANTOS COMPONENT MOUNT END ===', [
            'loan_id' => $this->loan_id,
            'member_number' => $this->member_number,
            'loan_type' => $this->loanType,
            'existing_guarantors' => count($this->existingGuarantorData),
            'existing_collaterals' => count($this->existingCollateralData),
            'data_loaded' => true
        ]);
    }

    /**
     * Comprehensive method to pre-load all saved guarantor and collateral data
     * This ensures all data is available when the guarantos page opens
     */
    private function preloadAllSavedData(): void
    {
        try {
            Log::info('=== PRELOAD ALL SAVED GUARANTOR DATA START ===');

            if (!$this->loan_id) {
                Log::warning('No loan_id available for preloading data');
                return;
            }

            // 1. Load loan information
            $this->loadLoanInformation();

            // 2. Load existing guarantor data (this will also pre-populate collateral fields for restructure loans)
            $this->loadExistingGuarantorData();

            // 3. For restructure loans, we don't need to load existing collateral data from current loan
            // because we've already pre-populated the fields from the restructured loan
            if ($this->loanType !== 'Restructure') {
                $this->loadExistingCollateralData();
            }

            // 4. Build collateral summary if data exists
            $this->buildCollateralSummaryIfNeeded();

            Log::info('=== PRELOAD ALL SAVED GUARANTOR DATA END ===', [
                'loan_id' => $this->loan_id,
                'loan_type' => $this->loanType,
                'guarantor_loaded' => isset($this->guarantor_id),
                'collateral_loaded' => !empty($this->selected_collateral_types) || $this->collateralCommitted,
                'restructure_loan' => $this->loanType === 'Restructure'
            ]);

        } catch (\Exception $e) {
            Log::error('Error preloading guarantor data: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Load loan information
     */
    private function loadLoanInformation(): void
    {
        $loan = $this->getLoanById($this->loan_id);
        if ($loan) {
            // Ensure we're using the numeric ID
            $this->loan_id = $loan->id;
            $this->loanType = $loan->loan_type_2 ?? 'New';
            $this->loanProduct = $loan->loanProduct;

            Log::info('Loan information loaded', [
                'loan_id' => $this->loan_id,
                'loan_type' => $this->loanType,
                'loan_product' => isset($this->loanProduct)
            ]);
        }
    }

    /**
     * Load existing collateral data
     */
    private function loadExistingCollateralData(): void
    {
        if (!$this->guarantor_id) return;

        // Load existing collaterals
        $existingCollaterals = DB::table('loan_collaterals')
            ->where('loan_guarantor_id', $this->guarantor_id)
            ->where('status', 'active')
            ->get();
        
        if ($existingCollaterals->count() > 0) {
            // Populate selected collateral types
            $this->selected_collateral_types = $existingCollaterals->pluck('collateral_type')->unique()->toArray();

            // Populate financial collaterals
            foreach (['deposits', 'savings', 'shares'] as $type) {
                $collateral = $existingCollaterals->where('collateral_type', $type)->first();
                if ($collateral) {
                    $this->selected_accounts[$type] = $collateral->account_id;
                    $this->collateral_amounts[$type] = $collateral->collateral_amount;
                }
            }

            // Populate physical collateral
            $physicalCollateral = $existingCollaterals->where('collateral_type', 'physical')->first();
            if ($physicalCollateral) {
                $this->physical_collateral = [
                    'collateral_id' => $physicalCollateral->physical_collateral_id,
                    'description' => $physicalCollateral->physical_collateral_description,
                    'value' => $physicalCollateral->physical_collateral_value,
                    'location' => $physicalCollateral->physical_collateral_location,
                    'type_of_owner' => 'Individual',
                    'owner_name' => $physicalCollateral->physical_collateral_owner_name,
                    'owner_nida' => $physicalCollateral->physical_collateral_owner_nida,
                    'owner_contact' => $physicalCollateral->physical_collateral_owner_contact,
                    'owner_address' => $physicalCollateral->physical_collateral_owner_address,
                    'valuation_date' => $physicalCollateral->physical_collateral_valuation_date,
                    'valuation_method' => $physicalCollateral->physical_collateral_valuation_method,
                    'valuer_name' => $physicalCollateral->physical_collateral_valuer_name,
                    'insurance_policy_number' => $physicalCollateral->insurance_policy_number,
                    'insurance_company_name' => $physicalCollateral->insurance_company_name,
                    'insurance_coverage_details' => $physicalCollateral->insurance_coverage_details,
                    'insurance_expiration_date' => $physicalCollateral->insurance_expiration_date,
                ];
            }

            Log::info('Existing collateral data loaded', [
                'collateral_types' => $this->selected_collateral_types,
                'financial_collaterals' => count($this->selected_accounts),
                'physical_collateral' => isset($this->physical_collateral['description'])
            ]);
        }
    }

    /**
     * Build collateral summary if data exists
     */
    private function buildCollateralSummaryIfNeeded(): void
    {
        if (!empty($this->selected_collateral_types)) {
            // Set step to review if data exists
            $this->currentStep = 4;
            $this->buildCollateralSummary();
            
            Log::info('Collateral summary built', [
                'current_step' => $this->currentStep,
                'summary_count' => count($this->collateral_summary)
            ]);
        }
    }

    public function loadExistingData()
    {
        if (!$this->loan_id) {
            return;
        }

        // Load loan information
        $loan = $this->getLoanById($this->loan_id);
        if ($loan) {
            // Ensure we're using the numeric ID
            $this->loan_id = $loan->id;
            $this->loanType = $loan->loan_type_2 ?? 'New';
            $this->loanProduct = $loan->loanProduct;
        }

        // Load existing guarantor and collateral data
        $this->loadExistingGuarantorData();

        // Check if guarantor already exists
        $existingGuarantor = LoanGuarantor::where('loan_id', $this->loan_id)->first();
        if ($existingGuarantor) {
            $this->guarantor_id = $existingGuarantor->id;
            $this->guarantor_type = $existingGuarantor->guarantor_type;
            $this->selected_guarantor_id = $existingGuarantor->guarantor_member_id;
            $this->relationship = $existingGuarantor->relationship;
            
            // Load guarantor member details
            $member = DB::table('clients')->find($existingGuarantor->guarantor_member_id);
            if ($member) {
                $this->guarantor_name = $member->first_name . ' ' . $member->middle_name . ' ' . $member->last_name;
                $this->member_number = $member->client_number;
            }

            // Load existing collaterals
            $existingCollaterals = $existingGuarantor->collaterals()->active()->get();
            
            if ($existingCollaterals->count() > 0) {
                // Populate selected collateral types
                $this->selected_collateral_types = $existingCollaterals->pluck('collateral_type')->unique()->toArray();

                // Populate financial collaterals
                foreach (['deposits', 'savings', 'shares'] as $type) {
                    $collateral = $existingCollaterals->where('collateral_type', $type)->first();
                    if ($collateral) {
                        $this->selected_accounts[$type] = $collateral->account_id;
                        $this->collateral_amounts[$type] = $collateral->collateral_amount;
                    }
                }

                // Populate physical collateral
                $physicalCollateral = $existingCollaterals->where('collateral_type', 'physical')->first();
                if ($physicalCollateral) {
                    $this->physical_collateral = [
                        'collateral_id' => $physicalCollateral->physical_collateral_id,
                        'description' => $physicalCollateral->physical_collateral_description,
                        'value' => $physicalCollateral->physical_collateral_value,
                        'location' => $physicalCollateral->physical_collateral_location,
                        'type_of_owner' => 'Individual',
                        'owner_name' => $physicalCollateral->physical_collateral_owner_name,
                        'owner_nida' => $physicalCollateral->physical_collateral_owner_nida,
                        'owner_contact' => $physicalCollateral->physical_collateral_owner_contact,
                        'owner_address' => $physicalCollateral->physical_collateral_owner_address,
                        'valuation_date' => $physicalCollateral->physical_collateral_valuation_date,
                        'valuation_method' => $physicalCollateral->physical_collateral_valuation_method,
                        'valuer_name' => $physicalCollateral->physical_collateral_valuer_name,
                        'insurance_policy_number' => $physicalCollateral->insurance_policy_number,
                        'insurance_company_name' => $physicalCollateral->insurance_company_name,
                        'insurance_coverage_details' => $physicalCollateral->insurance_coverage_details,
                        'insurance_expiration_date' => $physicalCollateral->insurance_expiration_date,
                    ];
                }

                // Set step to review if data exists
                $this->currentStep = 4;
                $this->buildCollateralSummary();
            }
        }
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->clients = DB::table('clients')
                ->where('first_name', 'like', "%{$this->search}%")
                ->orWhere('middle_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")
                ->orWhere('client_number', 'like', "%{$this->search}%")
                ->limit(10)
                ->get();
            $this->show_client_dropdown = true;
        } else {
            $this->clients = [];
            $this->show_client_dropdown = false;
        }
    }

    public function selectGuarantor($clientId)
    {
        $client = DB::table('clients')->find($clientId);
        if ($client) {
            $this->selected_guarantor_id = $client->id;
            $this->guarantor_name = $client->first_name . ' ' . $client->middle_name . ' ' . $client->last_name;
            $this->member_number = $client->client_number;
            
            // Check if member can be guarantor
            if (!$this->collateralService->canMemberBeGuarantor($client->id, $this->loan_id)) {
                session()->flash('message_feedback_fail', 'This member cannot be a guarantor. They may already be guaranteeing too many loans.');
                return;
            }
        }
        $this->show_client_dropdown = false;
        $this->search = '';
    }

    public function getAccountsForType($type)
    {
        if (!$this->member_number) return collect();
        
        $memberId = DB::table('clients')->where('client_number', $this->member_number)->value('id');
        if (!$memberId) return collect();
        
        return $this->collateralService->getAvailableAccounts($memberId, $type, $this->loan_id);
    }

    public function selectAccount($type, $accountId)
    {
        $this->selected_accounts[$type] = $accountId;
        $account = AccountsModel::find($accountId);
        if ($account) {
            $this->collateral_amounts[$type] = $account->balance;
        }
    }

    public function toggleCollateralType($type)
    {
        if (in_array($type, $this->selected_collateral_types)) {
            // Remove the type
            $this->selected_collateral_types = array_values(array_filter($this->selected_collateral_types, function($t) use ($type) {
                return $t !== $type;
            }));
            
            // Clear related data
            unset($this->selected_accounts[$type]);
            unset($this->collateral_amounts[$type]);
        } else {
            // Add the type
            $this->selected_collateral_types[] = $type;
        }
        
        // Reset validation errors for this field
        $this->resetValidation('selected_collateral_types');
    }

    public function updated($fieldName, $value)
    {
        // Sync aliased properties for compatibility with loan-application
        if ($fieldName === 'guarantorType') {
            $this->guarantor_type = $value;
        } elseif ($fieldName === 'guarantor_type') {
            $this->guarantorType = $value;
        } elseif ($fieldName === 'selectedGuarantorId') {
            $this->selected_guarantor_id = $value;
        } elseif ($fieldName === 'selected_guarantor_id') {
            $this->selectedGuarantorId = $value;
        } elseif ($fieldName === 'guarantorRelationship') {
            $this->relationship = $value;
        } elseif ($fieldName === 'relationship') {
            $this->guarantorRelationship = $value;
        }
        
        try {
            Log::info('=== GUARANTOS UPDATED METHOD CALLED ===', [
                'field_name' => $fieldName,
                'value' => $value,
                'value_type' => gettype($value),
                'timestamp' => now()->toISOString()
            ]);

            // Handle specific field updates
            if (strpos($fieldName, 'collateral_amounts.') === 0) {
                $this->handleCollateralAmountUpdate($fieldName, $value);
            } elseif (strpos($fieldName, 'selected_accounts.') === 0) {
                $this->handleAccountSelectionUpdate($fieldName, $value);
            } elseif (strpos($fieldName, 'physical_collateral.') === 0) {
                $this->handlePhysicalCollateralUpdate($fieldName, $value);
            } elseif ($fieldName === 'guarantor_type' || $fieldName === 'selected_guarantor_id' || $fieldName === 'relationship') {
                $this->handleGuarantorUpdate($fieldName, $value);
            }

            // Auto-save guarantor data after any field update
            $this->autoSaveGuarantorData();

        } catch (\Exception $e) {
            Log::error('Error updating guarantor field: ' . $e->getMessage());
            session()->flash('message_feedback_fail', 'Error updating field. Please try again.');
        }
    }

    /**
     * Handle collateral amount updates
     */
    private function handleCollateralAmountUpdate($fieldName, $value)
    {
        $type = str_replace('collateral_amounts.', '', $fieldName);
        
        $accountId = $this->selected_accounts[$type] ?? null;

        if ($accountId && $value) {
            $account = AccountsModel::find($accountId);
            if ($account && $value > $account->balance) {
                $this->collateral_amounts[$type] = $account->balance;
                session()->flash('message_feedback_fail', "Amount cannot exceed account balance of " . number_format($account->balance, 2));
            }
        }

        Log::info('Collateral amount updated', [
            'type' => $type,
            'value' => $value,
            'account_id' => $accountId
        ]);
    }

    /**
     * Handle account selection updates
     */
    private function handleAccountSelectionUpdate($fieldName, $value)
    {
        $type = str_replace('selected_accounts.', '', $fieldName);
        
        // Reset collateral amount when account changes
        if (isset($this->collateral_amounts[$type])) {
            $this->collateral_amounts[$type] = 0;
        }

        Log::info('Account selection updated', [
            'type' => $type,
            'account_id' => $value
        ]);
    }

    /**
     * Handle physical collateral updates
     */
    private function handlePhysicalCollateralUpdate($fieldName, $value)
    {
        $field = str_replace('physical_collateral.', '', $fieldName);
        
        Log::info('Physical collateral updated', [
            'field' => $field,
            'value' => $value
        ]);
    }

    /**
     * Handle guarantor updates
     */
    private function handleGuarantorUpdate($fieldName, $value)
    {
        Log::info('Guarantor field updated', [
            'field' => $fieldName,
            'value' => $value
        ]);
    }

    /**
     * Auto-save guarantor data to database
     */
    private function autoSaveGuarantorData()
    {
        try {
            if (!$this->loan_id) {
                Log::warning('No loan_id available for auto-saving guarantor data');
                return;
            }

            // Save guarantor data if we have a guarantor_id
            if ($this->guarantor_id) {
                $guarantor = LoanGuarantor::find($this->guarantor_id);
                if ($guarantor) {
                    $guarantor->update([
                        'guarantor_type' => $this->guarantor_type,
                        'relationship' => $this->relationship,
                        'updated_at' => now()
                    ]);

                    Log::info('Guarantor data auto-saved', [
                        'guarantor_id' => $this->guarantor_id,
                        'loan_id' => $this->loan_id
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error auto-saving guarantor data: ' . $e->getMessage());
        }
    }

    public function updatedCollateralAmounts($value, $key)
    {
        // This method is kept for backward compatibility
        $this->handleCollateralAmountUpdate('collateral_amounts.' . $key, $value);
    }

    public function nextStep()
    {
        if ($this->validateStep()) {
            if ($this->currentStep < $this->totalSteps) {
                $this->currentStep++;
                if ($this->currentStep == 4) {
                    $this->buildCollateralSummary();
                }
            }
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function validateStep()
    {
        switch ($this->currentStep) {
            case 1:
                $this->validate([
                    'guarantor_type' => 'required|in:self_guarantee,third_party_guarantee',
                    'selected_guarantor_id' => 'required_if:guarantor_type,third_party_guarantee',
                    'relationship' => 'required_if:guarantor_type,third_party_guarantee',
                ]);
                break;
            case 2:
                $this->validate([
                    'selected_collateral_types' => 'required|array|min:1',
                ]);
                break;
            case 3:
                $this->validate([
                    'selected_accounts.*' => 'required_if:selected_collateral_types,deposits,savings,shares|numeric|min:1',
                    'collateral_amounts.*' => 'required_if:selected_collateral_types,deposits,savings,shares|numeric|min:1',
                    'physical_collateral.collateral_id' => 'required_if:selected_collateral_types,physical_collaterals',
                    'physical_collateral.description' => 'required_if:selected_collateral_types,physical_collaterals',
                    'physical_collateral.value' => 'required_if:selected_collateral_types,physical_collaterals|numeric|min:1',
                    'physical_collateral.location' => 'required_if:selected_collateral_types,physical_collaterals',
                    'physical_collateral.owner_name' => 'required_if:selected_collateral_types,physical_collaterals',
                    'physical_collateral.owner_nida' => 'required_if:selected_collateral_types,physical_collaterals',
                    'physical_collateral.owner_contact' => 'required_if:selected_collateral_types,physical_collaterals',
                    'physical_collateral.owner_address' => 'required_if:selected_collateral_types,physical_collaterals',
                ]);
                break;
        }
        return true;
    }

    public function registerCollateral()
    {
        try {
            // This method is now used for finalizing the collateral registration
            // The actual registration is done in saveStep3()
            
            // Update the guarantor's total guaranteed amount
            if ($this->guarantor_id) {
                $guarantor = LoanGuarantor::find($this->guarantor_id);
                if ($guarantor) {
                    $totalAmount = $guarantor->collaterals()->active()->sum('collateral_amount');
                    $guarantor->update([
                        'total_guaranteed_amount' => $totalAmount,
                        'available_amount' => $totalAmount
                    ]);
                }
            }

            session()->flash('message_feedback', 'Collateral registration finalized successfully!');
            $this->buildCollateralSummary();
            
            // Mark guarantor tab as completed
            $this->emit('tabCompleted', 'guarantor');
            
        } catch (\Exception $e) {
            Log::error('Error finalizing collateral registration: ' . $e->getMessage());
            session()->flash('message_feedback_fail', 'Error finalizing collateral registration: ' . $e->getMessage());
        }
    }

    public function getStepProgressProperty()
    {
        return ($this->currentStep / $this->totalSteps) * 100;
    }

    /**
     * Calculate total collateral value from selected collaterals
     */
    public function getTotalCollateralValueProperty()
    {
        $total = 0;
        
        // Add financial collateral amounts
        foreach (['savings', 'deposits', 'shares'] as $type) {
            if (in_array($type, $this->selected_collateral_types) && 
                isset($this->collateral_amounts[$type]) && 
                $this->collateral_amounts[$type] > 0) {
                $total += (float)$this->collateral_amounts[$type];
            }
        }
        
        // Add physical collateral value
        if (in_array('physical_collaterals', $this->selected_collateral_types) && 
            isset($this->physical_collateral['value']) && 
            $this->physical_collateral['value'] > 0) {
            $total += (float)$this->physical_collateral['value'];
        }
        
        return $total;
    }

    /**
     * Get collateral breakdown for display
     */
    public function getCollateralBreakdownProperty()
    {
        $breakdown = [];
        
        // Financial collaterals
        foreach (['savings', 'deposits', 'shares'] as $type) {
            if (in_array($type, $this->selected_collateral_types) && 
                isset($this->collateral_amounts[$type]) && 
                $this->collateral_amounts[$type] > 0) {
                $breakdown[] = [
                    'type' => ucfirst($type),
                    'amount' => (float)$this->collateral_amounts[$type]
                ];
            }
        }
        
        // Physical collateral
        if (in_array('physical_collaterals', $this->selected_collateral_types) && 
            isset($this->physical_collateral['value']) && 
            $this->physical_collateral['value'] > 0) {
            $breakdown[] = [
                'type' => 'Physical Collateral',
                'amount' => (float)$this->physical_collateral['value']
            ];
        }
        
        return $breakdown;
    }

    public function render()
    {
        return view('livewire.loans.guarantos');
    }

    public function saveStep3()
    {
        if ($this->validateStep()) {
            try {
                DB::transaction(function () {
                    // Check if guarantor already exists for this loan
                    $existingGuarantor = LoanGuarantor::where('loan_id', $this->loan_id)
                        ->where('guarantor_member_id', $this->guarantor_type === 'self_guarantee' ? 
                            DB::table('clients')->where('client_number', $this->member_number)->value('id') : 
                            $this->selected_guarantor_id)
                        ->whereIn('status', ['active', 'inactive'])
                        ->first();

                    if ($existingGuarantor) {
                        // Reactivate existing guarantor
                        $existingGuarantor->update([
                            'status' => 'active',
                            'guarantor_type' => $this->guarantor_type,
                            'relationship' => $this->relationship,
                            'total_guaranteed_amount' => 0,
                            'available_amount' => 0
                        ]);
                        $this->guarantor_id = $existingGuarantor->id;
                        
                        Log::info('Reactivated existing guarantor', [
                            'guarantor_id' => $existingGuarantor->id,
                            'loan_id' => $this->loan_id
                        ]);
                    } else {
                        // Create new guarantor
                        $guarantorMemberId = $this->guarantor_type === 'self_guarantee' ? null : $this->selected_guarantor_id;
                        
                        $guarantor = $this->collateralService->createGuarantor(
                            $this->loan_id,
                            $guarantorMemberId,
                            $this->guarantor_type,
                            $this->relationship
                        );
                        $this->guarantor_id = $guarantor->id;
                        
                        Log::info('Created new guarantor', [
                            'guarantor_id' => $guarantor->id,
                            'loan_id' => $this->loan_id
                        ]);
                    }

                    // Add financial collaterals
                    foreach (['deposits', 'savings', 'shares'] as $type) {
                        if (in_array($type, $this->selected_collateral_types) && 
                            isset($this->selected_accounts[$type]) && 
                            isset($this->collateral_amounts[$type])) {
                            
                            $this->collateralService->addFinancialCollateral(
                                $this->guarantor_id,
                                $this->selected_accounts[$type],
                                $this->collateral_amounts[$type]
                            );
                        }
                    }

                    // Add physical collateral
                    if (in_array('physical_collaterals', $this->selected_collateral_types)) {
                        $this->collateralService->addPhysicalCollateral($this->guarantor_id, $this->physical_collateral);
                    }
                });

                session()->flash('message_feedback', 'Collateral set successfully!');
                $this->currentStep = 4;
                $this->buildCollateralSummary();
                
                // Mark guarantor tab as completed
                $this->emit('tabCompleted', 'guarantor');
                
            } catch (\Exception $e) {
                Log::error('Error setting collateral: ' . $e->getMessage());
                session()->flash('message_feedback_fail', 'Error setting collateral: ' . $e->getMessage());
            }
        }
    }

    private function buildCollateralSummary()
    {
        // If we have an existing guarantor, build from database
        if ($this->guarantor_id) {
            $guarantor = LoanGuarantor::with(['collaterals.account', 'guarantorMember'])->find($this->guarantor_id);
            if (!$guarantor) {
                $this->collateral_summary = [];
                return;
            }

            $this->collateral_summary = [
                'guarantor_name' => $guarantor->guarantorMember->first_name . ' ' . $guarantor->guarantorMember->last_name,
                'guarantor_number' => $guarantor->guarantorMember->client_number,
                'guarantor_type' => $guarantor->guarantor_type,
                'relationship' => $guarantor->relationship,
                'total_guaranteed_amount' => $guarantor->total_guaranteed_amount,
                'collaterals' => []
            ];

            foreach ($guarantor->collaterals as $collateral) {
                $collateralData = [
                    'id' => $collateral->id,
                    'type' => $collateral->collateral_type,
                    'amount' => $collateral->collateral_amount,
                    'locked_amount' => $collateral->locked_amount,
                    'available_amount' => $collateral->available_amount,
                ];

                if ($collateral->isFinancialCollateral()) {
                    $collateralData['account_number'] = $collateral->account ? $collateral->account->account_number : 'N/A';
                    $collateralData['account_balance'] = $collateral->account ? $collateral->account->balance : 0;
                } else {
                    $collateralData['description'] = $collateral->physical_collateral_description;
                    $collateralData['location'] = $collateral->physical_collateral_location;
                    $collateralData['owner_name'] = $collateral->physical_collateral_owner_name;
                }

                $this->collateral_summary['collaterals'][] = $collateralData;
            }
        } else {
            // Build summary from current form data
            $this->buildSummaryFromFormData();
        }
    }

    private function buildSummaryFromFormData()
    {
        $totalAmount = 0;
        $collaterals = [];

        // Build guarantor info
        $guarantorName = '';
        $guarantorNumber = '';
        
        if ($this->guarantor_type === 'self_guarantee') {
            $guarantorName = 'Self Guarantee';
            $guarantorNumber = $this->member_number;
        } else {
            if ($this->selected_guarantor_id) {
                $member = DB::table('clients')->find($this->selected_guarantor_id);
                if ($member) {
                    $guarantorName = $member->first_name . ' ' . $member->last_name;
                    $guarantorNumber = $member->client_number;
                }
            }
        }

        // Build financial collaterals
        foreach (['deposits', 'savings', 'shares'] as $type) {
            if (in_array($type, $this->selected_collateral_types) && 
                isset($this->selected_accounts[$type]) && 
                isset($this->collateral_amounts[$type])) {
                
                $account = AccountsModel::find($this->selected_accounts[$type]);
                $amount = $this->collateral_amounts[$type];
                $totalAmount += $amount;

                $collaterals[] = [
                    'id' => null, // No ID yet until registered
                    'type' => $type,
                    'amount' => $amount,
                    'locked_amount' => 0, // Not locked yet until registered
                    'available_amount' => $account ? $account->balance : 0,
                    'account_number' => $account ? $account->account_number : 'N/A',
                    'account_balance' => $account ? $account->balance : 0,
                ];
            }
        }

        // Build physical collateral
        if (in_array('physical_collaterals', $this->selected_collateral_types) && 
            !empty($this->physical_collateral['description'])) {
            
            $amount = $this->physical_collateral['value'] ?? 0;
            $totalAmount += $amount;

            $collaterals[] = [
                'id' => null, // No ID yet until registered
                'type' => 'physical',
                'amount' => $amount,
                'locked_amount' => 0, // Not locked yet until registered
                'available_amount' => $amount, // Full amount available for physical collateral
                'description' => $this->physical_collateral['description'],
                'location' => $this->physical_collateral['location'],
                'owner_name' => $this->physical_collateral['owner_name'],
            ];
        }

        $this->collateral_summary = [
            'guarantor_name' => $guarantorName,
            'guarantor_number' => $guarantorNumber,
            'guarantor_type' => $this->guarantor_type,
            'relationship' => $this->relationship,
            'total_guaranteed_amount' => $totalAmount,
            'collaterals' => $collaterals
        ];
    }

    public function deleteCollateral($collateralId)
    {
        try {
            $collateral = LoanCollateral::find($collateralId);
            if ($collateral && $collateral->loan_guarantor_id == $this->guarantor_id) {
                $collateral->releaseCollateral();
                session()->flash('message_feedback', 'Collateral released successfully!');
                $this->buildCollateralSummary();
            }
        } catch (\Exception $e) {
            Log::error('Error deleting collateral: ' . $e->getMessage());
            session()->flash('message_feedback_fail', 'Error deleting collateral: ' . $e->getMessage());
        }
    }

    public function resetAndStartOver()
    {
        if ($this->guarantor_id) {
            try {
                DB::transaction(function () {
                    $guarantor = LoanGuarantor::find($this->guarantor_id);
                    if ($guarantor) {
                        // Get all collaterals for this guarantor
                        $collaterals = $guarantor->collaterals()->active()->get();
                        
                        // Release all locked amounts for each collateral
                        foreach ($collaterals as $collateral) {
                            if ($collateral->isFinancialCollateral()) {
                                // Release all locked amounts for this collateral
                                LockedAmount::releaseAllForService('loan_collateral', $collateral->id);
                            }
                            
                            // Mark collateral as released
                            $collateral->update([
                                'status' => 'released',
                                'locked_amount' => 0
                            ]);
                        }
                        
                        // Update guarantor totals
                        $guarantor->update([
                            'total_guaranteed_amount' => 0,
                            'available_amount' => 0,
                            'status' => 'inactive'
                        ]);
                        
                        Log::info('Reset completed for guarantor', [
                            'guarantor_id' => $this->guarantor_id,
                            'loan_id' => $this->loan_id,
                            'collaterals_released' => $collaterals->count()
                        ]);
                    }
                });
            } catch (\Exception $e) {
                Log::error('Error resetting guarantor: ' . $e->getMessage());
                session()->flash('message_feedback_fail', 'Error resetting: ' . $e->getMessage());
                return;
            }
        }

        // Reset all form properties
        $this->reset([
            'currentStep', 'guarantor_type', 'search', 'clients', 'selected_guarantor_id',
            'guarantor_name', 'show_client_dropdown', 'relationship', 'selected_collateral_types',
            'selected_accounts', 'collateral_amounts', 'physical_collateral', 'collateral_summary',
            'guarantor_id'
        ]);

        // Reset to step 1
        $this->currentStep = 1;

        session()->flash('message_feedback', 'All collateral records removed successfully. You can start over.');
    }

    /**
     * Finalize collateral registration and mark tab as completed
     */
    public function finalizeCollateralRegistration()
    {
        try {
            $result = $this->collateralService->finalizeCollateralRegistration($this->loan_id);
            
            if ($result['success']) {
                // Mark guarantor tab as completed
                $this->emit('tabCompleted', 'guarantor');
                
                session()->flash('message_feedback', 'Collateral registration finalized successfully! Tab marked as completed.');
                
                // Refresh the summary
                $this->buildCollateralSummary();
                
                Log::info('Collateral registration finalized successfully', [
                    'loanId' => $this->loan_id,
                    'totalCollateralAmount' => $result['totalCollateralAmount'],
                    'totalLockedAmount' => $result['totalLockedAmount'],
                    'guarantorsCount' => $result['guarantorsCount']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error finalizing collateral registration: ' . $e->getMessage());
            session()->flash('message_feedback_fail', 'Error finalizing collateral registration: ' . $e->getMessage());
        }
    }

    /**
     * Check if guarantor tab is completed
     */
    public function isTabCompleted()
    {
        if (!$this->loan_id) {
            return false;
        }

        try {
            $tabStateService = app(\App\Services\LoanTabStateService::class);
            $completedTabs = $tabStateService->getCompletedTabs($this->loan_id);
            
            // Ensure completedTabs is an array
            if (!is_array($completedTabs)) {
                Log::warning('getCompletedTabs returned non-array value', [
                    'loanId' => $this->loan_id,
                    'completedTabs' => $completedTabs
                ]);
                return false;
            }
            
            return in_array('guarantor', $completedTabs);
        } catch (\Exception $e) {
            Log::error('Error checking tab completion status: ' . $e->getMessage());
            return false;
        }
    }

    // Methods from loan-application component for compatibility
    public function loadExistingGuarantorData()
    {
        \Log::info('Guarantos: loadExistingGuarantorData called', [
            'loanType' => $this->loanType,
            'loan_id' => $this->loan_id,
        ]);
        
        // Initialize debug info
        $this->debugInfo = [
            'method_called' => 'loadExistingGuarantorData',
            'timestamp' => now()->toISOString(),
            'loan_id' => $this->loan_id,
            'loan_type' => $this->loanType,
            'member_number' => $this->member_number,
            'queries' => [],
            'results' => [],
            'errors' => []
        ];
        
        if ($this->loan_id) {
            // First, ensure we have the numeric loan ID
            $currentLoan = $this->getLoanById($this->loan_id);
            if ($currentLoan) {
                $this->loan_id = $currentLoan->id; // Ensure we're using numeric ID
            }

            // For restructure loans, get the loan being restructured
            $restructuredLoanId = null;
            $originalLoan = null;
            if ($this->loanType === 'Restructure') {
                $loan = $this->getLoanById($this->loan_id);
                if ($loan) {
                    // Try different field names for the original loan reference
                    // restructure_loan_id (from assessment blade) or restructured_loan (from debug)
                    $restructuredLoanId = $loan->restructure_loan_id ?? $loan->restructured_loan ?? null;

                    if ($restructuredLoanId) {
                        // Load the original loan details
                        $originalLoan = DB::table('loans')->where('id', $restructuredLoanId)->first();

                        \Log::info('Guarantos: Found restructured loan', [
                            'restructured_loan_id' => $restructuredLoanId,
                            'current_loan_id' => $this->loan_id,
                            'original_loan_found' => $originalLoan ? true : false,
                            'original_loan_number' => $originalLoan->loan_id ?? 'N/A',
                        ]);

                        $this->debugInfo['restructured_loan_id'] = $restructuredLoanId;
                        $this->debugInfo['original_loan_id'] = $this->loan_id;
                        $this->debugInfo['original_loan_number'] = $originalLoan->loan_id ?? 'N/A';
                        $this->debugInfo['is_restructure_loan'] = true;
                    } else {
                        \Log::warning('Guarantos: Restructure loan has no original loan reference', [
                            'current_loan_id' => $this->loan_id,
                            'restructure_loan_id' => $loan->restructure_loan_id ?? 'null',
                            'restructured_loan' => $loan->restructured_loan ?? 'null',
                        ]);

                        $this->debugInfo['restructure_warning'] = 'No original loan reference found';
                        $this->debugInfo['is_restructure_loan'] = true;
                    }
                }
            }

            // Use restructured loan ID for data loading if available
            // IMPORTANT: For Restructure loans, we load collateral from the ORIGINAL loan being restructured
            $loanIdToQuery = $restructuredLoanId ?: $this->loan_id;

            // Ensure this is numeric - loan_guarantors.loan_id is bigint
            $numericLoanId = is_numeric($loanIdToQuery) ? $loanIdToQuery : null;

            // If we don't have numeric ID, try to get it
            if (!$numericLoanId) {
                $loanRecord = $this->getLoanById($loanIdToQuery);
                $numericLoanId = $loanRecord ? $loanRecord->id : null;
            }

            // Also get the string loan_id for reference
            $stringLoanId = null;
            $loan = LoansModel::find($numericLoanId);
            if ($loan && $loan->loan_id) {
                $stringLoanId = $loan->loan_id; // This is the string format like "LN202508311692"
            }

            $this->debugInfo['loan_id_to_query'] = $numericLoanId; // Use numeric ID for queries
            $this->debugInfo['string_loan_id'] = $stringLoanId; // Keep string ID for reference
            $this->debugInfo['original_numeric_id'] = $restructuredLoanId ?: $this->loan_id;

            // Guard against null numeric loan ID
            if (!$numericLoanId) {
                $this->debugInfo['errors']['numeric_loan_id'] = 'Could not determine numeric loan ID';
                \Log::error('Guarantos: Could not determine numeric loan ID', [
                    'session_loan_id' => Session::get('currentloanID'),
                    'component_loan_id' => $this->loan_id,
                ]);
                return;
            }

            // Debug: Check if loan exists in loans table
            try {
                $loanCheck = DB::table('loans')->where('id', $numericLoanId)->first();
                $this->debugInfo['loan_exists'] = $loanCheck ? true : false;
                $this->debugInfo['loan_data'] = $loanCheck ? [
                    'id' => $loanCheck->id,
                    'loan_id' => $loanCheck->loan_id ?? 'N/A',
                    'client_number' => $loanCheck->client_number ?? 'N/A',
                    'principle' => $loanCheck->principle ?? 'N/A',
                    'loan_amount' => $loanCheck->loan_amount ?? 'N/A',
                    'loan_type_2' => $loanCheck->loan_type_2 ?? 'N/A',
                    'restructured_loan' => $loanCheck->restructured_loan ?? 'N/A'
                ] : null;
            } catch (\Exception $e) {
                $this->debugInfo['errors']['loan_check'] = $e->getMessage();
            }
            
            // Query guarantor data with debug info - use numeric ID
            try {
                $guarantorQuery = DB::table('loan_guarantors')->where('loan_id', $numericLoanId);
                $this->debugInfo['queries']['guarantors'] = [
                    'sql' => $guarantorQuery->toSql(),
                    'bindings' => $guarantorQuery->getBindings(),
                    'table' => 'loan_guarantors',
                    'where_conditions' => ['loan_id' => $numericLoanId]
                ];
                
                $this->existingGuarantorData = $guarantorQuery->get()->toArray();
                $this->debugInfo['results']['guarantors'] = [
                    'count' => count($this->existingGuarantorData),
                    'data' => $this->existingGuarantorData
                ];
            } catch (\Exception $e) {
                $this->debugInfo['errors']['guarantors_query'] = $e->getMessage();
            }
            
            // Get collateral data through loan_guarantors relationship with debug info - use numeric ID
            try {
                $guarantorIds = DB::table('loan_guarantors')
                    ->where('loan_id', $numericLoanId)
                    ->pluck('id');
                
                $this->debugInfo['guarantor_ids'] = $guarantorIds->toArray();
                
                if ($guarantorIds->count() > 0) {
                    $collateralQuery = DB::table('loan_collaterals')
                        ->whereIn('loan_guarantor_id', $guarantorIds);
                    
                    $this->debugInfo['queries']['collaterals'] = [
                        'sql' => $collateralQuery->toSql(),
                        'bindings' => $collateralQuery->getBindings(),
                        'table' => 'loan_collaterals',
                        'where_conditions' => ['loan_guarantor_id' => $guarantorIds->toArray()]
                    ];
                    
                    $this->existingCollateralData = $collateralQuery->get()->toArray();
                    $this->debugInfo['results']['collaterals'] = [
                        'count' => count($this->existingCollateralData),
                        'data' => $this->existingCollateralData
                    ];
                } else {
                    $this->existingCollateralData = [];
                    $this->debugInfo['results']['collaterals'] = [
                        'count' => 0,
                        'data' => [],
                        'note' => 'No guarantor IDs found'
                    ];
                }
            } catch (\Exception $e) {
                $this->debugInfo['errors']['collaterals_query'] = $e->getMessage();
            }
            
            // Debug: Check table structures
            try {
                // Use PostgreSQL syntax instead of MySQL
                $guarantorColumns = DB::select("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'loan_guarantors' ORDER BY ordinal_position");
                $collateralColumns = DB::select("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'loan_collaterals' ORDER BY ordinal_position");
                
                $this->debugInfo['table_structures'] = [
                    'loan_guarantors' => $guarantorColumns,
                    'loan_collaterals' => $collateralColumns
                ];
            } catch (\Exception $e) {
                $this->debugInfo['errors']['table_structure'] = $e->getMessage();
            }

            // Debug: Check other possible tables for guarantor data
            try {
                // Check if there are any records in loan_guarantors table at all
                $totalGuarantors = DB::table('loan_guarantors')->count();
                $this->debugInfo['table_counts'] = [
                    'loan_guarantors_total' => $totalGuarantors,
                    'loan_collaterals_total' => DB::table('loan_collaterals')->count(),
                ];

                // Check if there are any guarantor records with different loan_id formats
                $allGuarantorLoanIds = DB::table('loan_guarantors')
                    ->select('loan_id')
                    ->distinct()
                    ->get()
                    ->pluck('loan_id')
                    ->toArray();
                
                $this->debugInfo['all_guarantor_loan_ids'] = $allGuarantorLoanIds;

                // Check if the loan_id might be stored as string instead of integer
                try {
                    $stringLoanIdGuarantors = DB::table('loan_guarantors')
                        ->where('loan_id', (string)$loanIdToQuery)
                        ->get()
                        ->toArray();

                    $this->debugInfo['string_loan_id_guarantors'] = [
                        'count' => count($stringLoanIdGuarantors),
                        'data' => $stringLoanIdGuarantors
                    ];
                } catch (\Exception $e) {
                    $this->debugInfo['string_loan_id_guarantors'] = [
                        'count' => 0,
                        'data' => [],
                        'error' => 'Type mismatch expected - loan_id is bigint, not string'
                    ];
                }

                // Check if there are any guarantor records with the actual loan_id string
                // This intentionally tries a string loan_id to verify schema
                $actualLoanId = $this->debugInfo['loan_data']['loan_id'] ?? null;
                if ($actualLoanId) {
                    try {
                        $actualLoanIdGuarantors = DB::table('loan_guarantors')
                            ->where('loan_id', $actualLoanId)
                            ->get()
                            ->toArray();

                        $this->debugInfo['actual_loan_id_guarantors'] = [
                            'loan_id_string' => $actualLoanId,
                            'count' => count($actualLoanIdGuarantors),
                            'data' => $actualLoanIdGuarantors
                        ];
                    } catch (\Exception $e) {
                        // Expected error: loan_guarantors.loan_id is bigint, not varchar
                        $this->debugInfo['actual_loan_id_guarantors'] = [
                            'loan_id_string' => $actualLoanId,
                            'count' => 0,
                            'data' => [],
                            'note' => 'Confirmed: loan_guarantors.loan_id is bigint (numeric), not varchar'
                        ];
                    }
                }

                // Check other possible tables that might contain guarantor data
                $possibleTables = ['guarantors', 'loan_guarantor', 'guarantor', 'collaterals', 'loan_collateral'];
                $this->debugInfo['other_tables_check'] = [];
                
                foreach ($possibleTables as $tableName) {
                    try {
                        $tableExists = DB::select("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = ?)", [$tableName]);
                        $exists = $tableExists[0]->exists ?? false;
                        
                        if ($exists) {
                            $count = DB::table($tableName)->count();
                            $this->debugInfo['other_tables_check'][$tableName] = [
                                'exists' => true,
                                'count' => $count
                            ];
                        } else {
                            $this->debugInfo['other_tables_check'][$tableName] = [
                                'exists' => false,
                                'count' => 0
                            ];
                        }
                    } catch (\Exception $e) {
                        $this->debugInfo['other_tables_check'][$tableName] = [
                            'exists' => false,
                            'error' => $e->getMessage()
                        ];
                    }
                }

            } catch (\Exception $e) {
                $this->debugInfo['errors']['other_tables_check'] = $e->getMessage();
            }
            
            \Log::info('Guarantos: Existing data loaded', [
                'guarantor_count' => count($this->existingGuarantorData),
                'collateral_count' => count($this->existingCollateralData),
                'loan_id_queried' => $loanIdToQuery,
                'debug_info' => $this->debugInfo
            ]);
            
            // Pre-populate form fields with existing collateral data for restructure loans
            if ($this->loanType === 'Restructure' && !empty($this->existingCollateralData)) {
                $this->prepopulateCollateralFields();
            }
        } else {
            $this->debugInfo['errors']['no_loan_id'] = 'No loan_id provided';
        }
    }
    
    /**
     * Pre-populate collateral form fields with existing data
     */
    private function prepopulateCollateralFields()
    {
        $totalAccountCollateral = 0;
        $totalPhysicalCollateral = 0;
        
        foreach ($this->existingCollateralData as $collateral) {
            if (is_object($collateral)) {
                if (in_array($collateral->collateral_type, ['savings', 'deposits', 'shares'])) {
                    $totalAccountCollateral += floatval($collateral->collateral_amount);
                } elseif ($collateral->collateral_type === 'physical') {
                    $totalPhysicalCollateral += floatval($collateral->physical_collateral_value ?? $collateral->collateral_amount);
                    
                    // Pre-populate physical collateral fields with first physical collateral found
                    if (empty($this->physicalCollateralDescription) && !empty($collateral->physical_collateral_description)) {
                        $this->physicalCollateralDescription = $collateral->physical_collateral_description;
                        $this->physicalCollateralValue = $collateral->physical_collateral_value ?? $collateral->collateral_amount;
                        $this->physicalCollateralLocation = $collateral->physical_collateral_location ?? '';
                        $this->physicalCollateralOwnerName = $collateral->physical_collateral_owner_name ?? '';
                        $this->physicalCollateralOwnerContact = $collateral->physical_collateral_owner_contact ?? '';
                    }
                }
            }
        }
        
        // Set the collateral amount to the total of account-based collateral
        if ($totalAccountCollateral > 0) {
            $this->collateralAmount = $totalAccountCollateral;
            
            // Try to find and select the account that was used as collateral
            foreach ($this->existingCollateralData as $collateral) {
                if (is_object($collateral) && in_array($collateral->collateral_type, ['savings', 'deposits', 'shares'])) {
                    // Try to find the account by account number or ID
                    $clientNumber = Session::get('currentloanClient');
                    $account = DB::table('accounts')
                        ->where('client_number', $clientNumber)
                        ->where(function($query) use ($collateral) {
                            $query->where('account_number', $collateral->account_number ?? '')
                                  ->orWhere('id', $collateral->account_id ?? 0);
                        })
                        ->first();
                    
                    if ($account) {
                        $this->selectedAccountId = $account->id;
                        break;
                    }
                }
            }
        }
        
        // Show physical collateral section if there's physical collateral
        if ($totalPhysicalCollateral > 0) {
            $this->showPhysicalCollateral = true;
        }
        
        // Mark collateral as committed if we have existing data
        if (!empty($this->existingCollateralData)) {
            $this->collateralCommitted = true;
        }
        
        \Log::info('Guarantos: Collateral fields pre-populated', [
            'total_account_collateral' => $totalAccountCollateral,
            'total_physical_collateral' => $totalPhysicalCollateral,
            'selected_account_id' => $this->selectedAccountId,
            'collateral_amount' => $this->collateralAmount,
            'show_physical_collateral' => $this->showPhysicalCollateral,
            'collateral_committed' => $this->collateralCommitted,
        ]);
    }



    /**
     * Save guarantor and collateral information to the database
     * Similar to the save logic in LoanApplication
     */
    public function saveGuarantorAndCollateral()
    {
        $this->validate([
            'guarantorType' => 'required|in:self_guarantee,third_party_guarantee',
            'selectedAccountId' => 'required',
            'collateralAmount' => 'required|numeric|min:0',
        ]);

        try {
            Log::info('Guarantos: Saving guarantor and collateral information', [
                'loan_id' => $this->loan_id,
                'guarantor_type' => $this->guarantorType,
                'selected_account_id' => $this->selectedAccountId,
                'collateral_amount' => $this->collateralAmount,
            ]);

            // Get the loan to determine the total guaranteed amount
            $loan = $this->getLoanById($this->loan_id);
            if (!$loan) {
                throw new \Exception('Loan not found');
            }
            // Ensure we're using numeric ID
            $this->loan_id = $loan->id;

            $totalGuaranteedAmount = $loan->principle ?? $loan->loan_amount ?? 0;

            // Determine guarantor member ID
            $guarantorMemberId = null;
            if ($this->guarantorType === 'third_party_guarantee' && !empty($this->selectedGuarantorId)) {
                $guarantorMemberId = (int)$this->selectedGuarantorId;
            } else {
                // Self-guarantee - find client record
                $client = DB::table('clients')->where('client_number', $this->member_number)->first();
                if ($client) {
                    $guarantorMemberId = $client->id;
                } else {
                    throw new \Exception('Client record not found for self-guarantee');
                }
            }

            // Save guarantor information
            $guarantorData = [
                'loan_id' => $this->loan_id,
                'guarantor_member_id' => $guarantorMemberId,
                'guarantor_type' => $this->guarantorType,
                'relationship' => $this->guarantorRelationship ?? null,
                'total_guaranteed_amount' => $totalGuaranteedAmount,
                'available_amount' => $totalGuaranteedAmount,
                'status' => 'active',
                'guarantee_start_date' => now(),
                'notes' => 'Guarantor for loan application',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $guarantorId = DB::table('loan_guarantors')->insertGetId($guarantorData);
            $guarantor = (object) array_merge($guarantorData, ['id' => $guarantorId]);

            Log::info('Guarantos: Guarantor information saved successfully', [
                'guarantor_id' => $guarantorId,
            ]);

            // Save account collateral
            if (!empty($this->selectedAccountId) && !empty($this->collateralAmount)) {
                // Determine the correct collateral type based on account type
                $account = DB::table('accounts')->where('id', $this->selectedAccountId)->first();
                $collateralType = 'savings'; // Default to savings
                
                if ($account) {
                    // Map account types to collateral types
                    switch (strtolower($account->account_type ?? '')) {
                        case 'savings':
                        case 'savings_account':
                            $collateralType = 'savings';
                            break;
                        case 'deposits':
                        case 'fixed_deposit':
                        case 'term_deposit':
                            $collateralType = 'deposits';
                            break;
                        case 'shares':
                        case 'share_account':
                            $collateralType = 'shares';
                            break;
                        default:
                            $collateralType = 'savings'; // Default fallback
                    }
                }

                DB::table('loan_collaterals')->insert([
                    'loan_guarantor_id' => $guarantor->id,
                    'collateral_type' => $collateralType,
                    'account_id' => $this->selectedAccountId,
                    'collateral_amount' => $this->collateralAmount,
                    'account_balance' => $this->collateralAmount,
                    'locked_amount' => $this->collateralAmount,
                    'available_amount' => 0,
                    'status' => 'active',
                    'collateral_start_date' => now(),
                    'notes' => 'Account collateral',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Save physical collateral if available
            if (!empty($this->physicalCollateralValue)) {
                DB::table('loan_collaterals')->insert([
                    'loan_guarantor_id' => $guarantor->id,
                    'collateral_type' => 'physical',
                    'collateral_amount' => (float)$this->physicalCollateralValue,
                    'physical_collateral_description' => $this->physicalCollateralDescription ?? 'Physical collateral',
                    'physical_collateral_location' => $this->physicalCollateralLocation ?? 'Not specified',
                    'physical_collateral_owner_name' => $this->physicalCollateralOwnerName ?? null,
                    'physical_collateral_owner_contact' => $this->physicalCollateralOwnerContact ?? null,
                    'physical_collateral_value' => (float)$this->physicalCollateralValue,
                    'physical_collateral_valuation_date' => now(),
                    'status' => 'active',
                    'collateral_start_date' => now(),
                    'notes' => 'Physical collateral for loan',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Log::info('Guarantos: Collateral information saved successfully');

            // Mark as committed
            $this->collateralCommitted = true;

            // Reload existing data
            $this->loadExistingGuarantorData();

            session()->flash('message_feedback', 'Guarantor and collateral information saved successfully!');

        } catch (\Exception $e) {
            Log::error('Guarantos: Error saving guarantor and collateral', [
                'error' => $e->getMessage(),
                'loan_id' => $this->loan_id,
            ]);
            
            session()->flash('message_feedback_fail', 'Error saving guarantor and collateral: ' . $e->getMessage());
        }
    }

    /**
     * Get all member accounts for collateral selection
     */
    public function getAllMemberAccounts()
    {
        $clientNumber = $this->member_number;
        return DB::table('accounts')
            ->where('client_number', $clientNumber)
            ->get();
    }

    /**
     * Commit collateral - saves guarantor and collateral information
     * Similar to the logic in LoanApplication component
     */
    public function commitCollateral()
    {
        try {
            // Validate the input
            $this->validate([
                'guarantorType' => 'required|in:self_guarantee,third_party_guarantee',
                'selectedGuarantorId' => 'required_if:guarantorType,third_party_guarantee',
                'guarantorRelationship' => 'required_if:guarantorType,third_party_guarantee',
                'selectedAccountId' => 'required',
                'collateralAmount' => 'required|numeric|min:0',
            ], [
                'guarantorType.required' => 'Please select a guarantor type.',
                'selectedGuarantorId.required_if' => 'Please enter the guarantor member number.',
                'guarantorRelationship.required_if' => 'Please specify your relationship with the guarantor.',
                'selectedAccountId.required' => 'Please select an account for collateral.',
                'collateralAmount.required' => 'Please enter the collateral amount.',
            ]);

            DB::beginTransaction();

            // Get loan ID from session or property
            $loanId = $this->loan_id ?? Session::get('loanId');
            if (!$loanId) {
                throw new \Exception('Loan ID not found. Please ensure you have a loan application in progress.');
            }

            // Get the actual database loan record - use helper method to handle both string and numeric IDs
            $loan = $this->getLoanById($loanId);
            if (!$loan) {
                throw new \Exception('Loan record not found for ID: ' . $loanId);
            }

            $loanDbId = $loan->id; // Use the numeric database ID
            $this->loan_id = $loanDbId; // Ensure component property uses numeric ID

            // Determine guarantor member ID
            $guarantorMemberId = null;
            
            if ($this->guarantorType === 'third_party_guarantee') {
                // For third-party guarantor
                $guarantorMemberId = (int)$this->selectedGuarantorId;
                
                // Verify the guarantor exists in clients table
                $guarantorClient = DB::table('clients')->where('id', $guarantorMemberId)->first();
                if (!$guarantorClient) {
                    // Try to find by client_number
                    $guarantorClient = DB::table('clients')->where('client_number', $this->selectedGuarantorId)->first();
                    if ($guarantorClient) {
                        $guarantorMemberId = $guarantorClient->id;
                    } else {
                        throw new \Exception('Guarantor member not found with ID/Number: ' . $this->selectedGuarantorId);
                    }
                }
            } else {
                // Self-guarantee - use current client
                $clientNumber = $this->member_number ?? Session::get('member_number');
                
                // Check if client exists in clients table
                $client = DB::table('clients')->where('client_number', $clientNumber)->first();
                
                if (!$client) {
                    // Create client record if it doesn't exist
                    Log::info('Guarantos: Creating client record for self-guarantee', [
                        'client_number' => $clientNumber,
                    ]);
                    
                    $guarantorMemberId = DB::table('clients')->insertGetId([
                        'client_number' => $clientNumber,
                        'first_name' => 'Member',
                        'last_name' => $clientNumber,
                        'email' => '',
                        'phone_number' => '',
                        'client_status' => 'ACTIVE',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $guarantorMemberId = $client->id;
                }
            }

            // Calculate total guaranteed amount (use loan amount from loan record)
            $totalGuaranteedAmount = $loan->principle ?? $loan->loan_amount ?? 0;

            // Check if guarantor already exists for this loan
            $existingGuarantor = DB::table('loan_guarantors')
                ->where('loan_id', $loanDbId)
                ->where('guarantor_member_id', $guarantorMemberId)
                ->first();

            if ($existingGuarantor) {
                // Update existing guarantor
                DB::table('loan_guarantors')
                    ->where('id', $existingGuarantor->id)
                    ->update([
                        'guarantor_type' => $this->guarantorType,
                        'relationship' => $this->guarantorRelationship ?? null,
                        'total_guaranteed_amount' => $totalGuaranteedAmount,
                        'available_amount' => $totalGuaranteedAmount,
                        'status' => 'active',
                        'updated_at' => now(),
                    ]);
                $guarantorId = $existingGuarantor->id;
            } else {
                // Create new guarantor record
                $guarantorData = [
                    'loan_id' => $loanDbId, // Use the numeric ID
                    'guarantor_member_id' => $guarantorMemberId,
                    'guarantor_type' => $this->guarantorType,
                    'relationship' => $this->guarantorRelationship ?? null,
                    'total_guaranteed_amount' => $totalGuaranteedAmount,
                    'available_amount' => $totalGuaranteedAmount,
                    'status' => 'active',
                    'guarantee_start_date' => now(),
                    'notes' => 'Guarantor for loan application',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $guarantorId = DB::table('loan_guarantors')->insertGetId($guarantorData);
            }

            Log::info('Guarantos: Guarantor information saved', [
                'guarantor_id' => $guarantorId,
                'loan_id' => $loanDbId,
                'guarantor_type' => $this->guarantorType,
            ]);

            // Save account collateral
            if (!empty($this->selectedAccountId) && !empty($this->collateralAmount)) {
                // Get account details
                $account = DB::table('accounts')->where('id', $this->selectedAccountId)->first();
                
                if ($account) {
                    // Determine collateral type based on account type
                    $collateralType = 'savings'; // default
                    if (stripos($account->account_type, 'deposit') !== false) {
                        $collateralType = 'deposits';
                    } elseif (stripos($account->account_type, 'share') !== false) {
                        $collateralType = 'shares';
                    }

                    // Lock the collateral amount in the account
                    $lockedAmount = min((float)$this->collateralAmount, (float)$account->balance);
                    
                    // Create or update collateral record
                    $existingCollateral = DB::table('loan_collaterals')
                        ->where('loan_guarantor_id', $guarantorId)
                        ->where('account_id', $this->selectedAccountId)
                        ->first();

                    if ($existingCollateral) {
                        // Update existing collateral
                        DB::table('loan_collaterals')
                            ->where('id', $existingCollateral->id)
                            ->update([
                                'collateral_amount' => (float)$this->collateralAmount,
                                'account_balance' => $account->balance,
                                'locked_amount' => $lockedAmount,
                                'available_amount' => $account->balance - $lockedAmount,
                                'status' => 'active',
                                'updated_at' => now(),
                            ]);
                    } else {
                        // Create new collateral record
                        DB::table('loan_collaterals')->insert([
                            'loan_guarantor_id' => $guarantorId,
                            'collateral_type' => $collateralType,
                            'account_id' => $this->selectedAccountId,
                            'collateral_amount' => (float)$this->collateralAmount,
                            'account_balance' => $account->balance,
                            'locked_amount' => $lockedAmount,
                            'available_amount' => $account->balance - $lockedAmount,
                            'status' => 'active',
                            'collateral_start_date' => now(),
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    Log::info('Guarantos: Account collateral saved', [
                        'account_id' => $this->selectedAccountId,
                        'collateral_type' => $collateralType,
                        'amount' => $this->collateralAmount,
                    ]);
                }
            }

            // Save physical collateral if provided
            if ($this->showPhysicalCollateral && !empty($this->physicalCollateralValue)) {
                // Check for existing physical collateral
                $existingPhysicalCollateral = DB::table('loan_collaterals')
                    ->where('loan_guarantor_id', $guarantorId)
                    ->where('collateral_type', 'physical')
                    ->first();

                if ($existingPhysicalCollateral) {
                    // Update existing physical collateral
                    DB::table('loan_collaterals')
                        ->where('id', $existingPhysicalCollateral->id)
                        ->update([
                            'collateral_amount' => (float)$this->physicalCollateralValue,
                            'physical_collateral_description' => $this->physicalCollateralDescription ?? 'Physical collateral',
                            'physical_collateral_location' => $this->physicalCollateralLocation ?? 'Not specified',
                            'physical_collateral_owner_name' => $this->physicalCollateralOwnerName ?? null,
                            'physical_collateral_owner_contact' => $this->physicalCollateralOwnerContact ?? null,
                            'physical_collateral_value' => (float)$this->physicalCollateralValue,
                            'physical_collateral_valuation_date' => now(),
                            'status' => 'active',
                            'updated_at' => now(),
                        ]);
                } else {
                    // Create new physical collateral record
                    DB::table('loan_collaterals')->insert([
                        'loan_guarantor_id' => $guarantorId,
                        'collateral_type' => 'physical',
                        'collateral_amount' => (float)$this->physicalCollateralValue,
                        'physical_collateral_description' => $this->physicalCollateralDescription ?? 'Physical collateral',
                        'physical_collateral_location' => $this->physicalCollateralLocation ?? 'Not specified',
                        'physical_collateral_owner_name' => $this->physicalCollateralOwnerName ?? null,
                        'physical_collateral_owner_contact' => $this->physicalCollateralOwnerContact ?? null,
                        'physical_collateral_value' => (float)$this->physicalCollateralValue,
                        'physical_collateral_valuation_date' => now(),
                        'status' => 'active',
                        'collateral_start_date' => now(),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                Log::info('Guarantos: Physical collateral saved', [
                    'value' => $this->physicalCollateralValue,
                    'description' => $this->physicalCollateralDescription,
                ]);
            }

            DB::commit();

            // Set success flag
            $this->collateralCommitted = true;
            
            // Store committed values
            $this->committedCollateralAmount = (float)($this->collateralAmount ?? 0);
            $this->committedPhysicalCollateralValue = (float)($this->physicalCollateralValue ?? 0);
            
            // Refresh existing data
            $this->loadExistingGuarantorData();
            
            session()->flash('message_feedback', 'Guarantor and collateral information saved successfully.');
            
            Log::info('Guarantos: Collateral committed successfully', [
                'loan_id' => $loanDbId,
                'guarantor_id' => $guarantorId,
                'total_collateral' => $this->committedCollateralAmount + $this->committedPhysicalCollateralValue,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Guarantos: Error committing collateral', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('message_feedback_fail', 'Error saving collateral: ' . $e->getMessage());
        }
    }



    // Property getters for compatibility
    public function getExistingGuarantorDataProperty()
    {
        return $this->existingGuarantorData;
    }

    public function getExistingCollateralDataProperty()
    {
        return $this->existingCollateralData;
    }

    // Property getter for loan type to ensure it's always available
    public function getLoanTypeProperty()
    {
        if (empty($this->loanType)) {
            // Fallback: load from loan if not set
            if ($this->loan_id) {
                $loan = $this->getLoanById($this->loan_id);
                if ($loan) {
                    // Ensure we're using numeric ID
                    $this->loan_id = $loan->id;
                    $this->loanType = $loan->loan_type_2 ?? 'New';
                }
            }
        }
        return $this->loanType;
    }
    
    /**
     * Refresh collateral data for restructure loans
     * This method can be called to reload existing collateral data
     */
    public function refreshCollateralData()
    {
        \Log::info('Guarantos: refreshCollateralData called', [
            'loanType' => $this->loanType,
            'loan_id' => $this->loan_id,
        ]);
        
        if ($this->loanType === 'Restructure') {
            // Reload existing guarantor and collateral data
            $this->loadExistingGuarantorData();
            
            \Log::info('Guarantos: Collateral data refreshed for restructure loan', [
                'existing_guarantor_count' => count($this->existingGuarantorData),
                'existing_collateral_count' => count($this->existingCollateralData),
                'collateral_amount' => $this->collateralAmount,
                'selected_account_id' => $this->selectedAccountId,
                'show_physical_collateral' => $this->showPhysicalCollateral,
            ]);
        }
    }

    /**
     * Manually refresh debug information
     */
    public function refreshDebugInfo()
    {
        $this->loadExistingGuarantorData();
        session()->flash('message_feedback', 'Debug information refreshed!');
    }

    /**
     * Toggle debug information display
     */
    public function toggleDebugInfo()
    {
        $this->showDebugInfo = !$this->showDebugInfo;
    }
}
