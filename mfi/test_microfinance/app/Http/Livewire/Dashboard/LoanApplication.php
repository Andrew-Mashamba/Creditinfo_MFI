<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\approvals;
use App\Models\TeamUser;
use App\Services\LoanApplication\LoanAssessmentService;
use App\Services\LoanCalculationService;
use App\Services\LoanApplication\LoanDocumentService;
use App\Services\OtpService;

class LoanApplication extends Component
{
    use WithFileUploads, WithPagination;

    // Step management
    public $currentStep = 1;
    public $totalSteps = 5;
    public $showSuccessMessage = true;

    // Loan type and product selection
    public $loanType = 'New';
    public $loan_type_2 = 'New';
    public $selectedProductId = '';
    public $loanProducts = [];
    public $hasLoanProducts = false;
    // Not persisted; we rehydrate on demand using selectedProductId
    protected $selectedProduct = null;
    public $isLoanTypeLocked = false;
    
    // Selected product details for view
    public $selectedProductName = '';
    public $selectedProductInterestRate = 0;
    public $selectedProductManagementFee = 0;
    public $selectedProductMajangaFee = 0;
    public $selectedProductMinTerm = 1;
    public $selectedProductMaxTerm = 60;
    // Dynamic product charges (from loan_product_charges)
    public $productCharges = [];
    public $totalChargesAmount = 0;
    public $productInsurance = [];
    public $totalInsuranceAmount = 0;

    // Loan details
    public $loanAmount = '';
    public $loanPurpose = '';
    public $repaymentPeriod = '';
    public $salaryTakeHome = '';

    // Loan type specific selections
    public $selectedLoanForTopUp = '';
    public $selectedLoanForRestructure = '';
    public $selectedLoanForTakeover = '';
    public $existingLoansForSelection = [];
    public $topUpAmount = 0;
    public $restructureAmount = 0;
    public $takeoverAmount = 0;

    // Credit assessment
    public $creditScoreValue = 0;
    public $creditScoreRisk = 'UNKNOWN';
    public $creditScoreGrade = 'XX';
    public $creditScoreSource = 'Internal';
    public $creditScoreProbabilityOfDefault = 0;
    public $creditScorePastDueAmount = 0;
    public $creditScoreInquiriesLastMonth = 0;
    public $monthlyInstallment = 0;
    public $totalAmountPayable = 0;
    public $eligibleLoanAmount = 0;
    public $activeLoansCount = 0;
    public $totalSavings = 0;
    public $totalDeposits = 0;
    public $takeHome = 0;
    public $firstInterest = 0;
    public $earlyTopUpPenalty = 0;
    public $adjustmentValue = 0;

    // Policy violations
    protected $step1PolicyViolations = [];
    protected $step3SavingsViolations = [];

    // Non-blocking inline warnings per field
    public $warnings = [];

    // Guarantor and collateral
    public $guarantorType = 'self_guarantee';
    public $selectedGuarantorId = '';
    public $guarantorRelationship = '';
    public $selectedAccountId = '';
    public $collateralAmount = 0;
    public $collateralCommitted = false;
    public $committedCollateralAmount = 0;
    public $committedPhysicalCollateralValue = 0;
    public $existingDocumentsData = [];
    public $committedDocumentsData = [];
    public $showPhysicalCollateral = false;
    public $physicalCollateralDescription = '';
    public $physicalCollateralValue = '';
    public $physicalCollateralLocation = '';
    public $physicalCollateralOwnerName = '';
    public $physicalCollateralOwnerContact = '';
    public $existingGuarantorData = [];
    public $existingCollateralData = [];

    // Document upload
    public $documentFile;
    public $documentCategory = 'general';
    public $documentDescription = '';
    public $uploadedDocuments = [];
    public $uploadedDocumentsCount = 0;
    public $isUploading = false;
    public $uploadProgress = 0;
    public $isDragging = false;

    // Client information
    public $client_number = '';

    // Selected product amount limits and requirements
    public $selectedProductMinAmount = 0;
    public $selectedProductMaxAmount = 0;
    public $selectedProductRequiresMemberImage = false;
    public $selectedProductRequiresIdImage = false;

    // Client retirement alignment helper
    public $monthsToRetirement = null;

    // Exception approval letter (required if there are warnings at submission)
    public $exceptionApprovalFile = null;
    public $exceptionApprovalUploaded = false;
    public $exceptionApprovalDocMeta = [];

    // Client number validation
    public $showClientNumberModal = false;
    public $inputClientNumber = '';
    public $clientNumberError = '';

    // OTP verification
    public $enableOtpVerification = true;
    public $showOtpModal = false;
    public $otpSent = false;
    public $otpSentVia = '';
    public $otpCode = '';
    
    // Restructure loan amount field control
    public $isRestructureLoanAmountDisabled = false;
    public $otpExpiresIn = 300;
    public $otpResendCooldown = 0;
    public $showOtpVerification = false;
    public $generatedOtp = '';
    public $otpVerificationInProgress = false;
    
    // Terms and conditions acceptance
    public $acceptedTerms = false;

    // Validation rules
    protected $rules = [
        'loanType' => 'required|in:New,Top-up,Restructure,Restructuring,Takeover',
        'selectedProductId' => 'required',
        'loanAmount' => 'required|numeric|min:1000',
        'loanPurpose' => 'required|min:10',
        'repaymentPeriod' => 'required|numeric|min:1',
        'salaryTakeHome' => 'required|numeric|min:1000',
        'guarantorType' => 'required|in:self_guarantee,third_party_guarantee',
        'selectedAccountId' => 'required',
        'collateralAmount' => 'required|numeric|min:0',
        'documentFile' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
        'documentDescription' => 'required_with:documentFile|min:3',
    ];

    protected $messages = [
        'loanType.required' => 'Please select a loan type.',
        'selectedProductId.required' => 'Please select a loan product.',
        'loanAmount.required' => 'Please enter the loan amount.',
        'loanAmount.numeric' => 'Loan amount must be a number.',
        'loanAmount.min' => 'Loan amount must be at least 1,000 TZS.',
        'loanPurpose.required' => 'Please describe the purpose of the loan.',
        'loanPurpose.min' => 'Loan purpose must be at least 10 characters.',
        'repaymentPeriod.required' => 'Please enter the repayment period.',
        'repaymentPeriod.numeric' => 'Repayment period must be a number.',
        'repaymentPeriod.min' => 'Repayment period must be at least 1 month.',
        'salaryTakeHome.required' => 'Please enter your monthly income.',
        'salaryTakeHome.numeric' => 'Monthly income must be a number.',
        'salaryTakeHome.min' => 'Monthly income must be at least 1,000 TZS.',
        'guarantorType.required' => 'Please select a guarantor type.',
        'selectedAccountId.required' => 'Please select an account for collateral.',
        'collateralAmount.required' => 'Please enter the collateral amount.',
        'collateralAmount.numeric' => 'Collateral amount must be a number.',
        'documentFile.max' => 'Document size must not exceed 10MB.',
        'documentFile.mimes' => 'Document must be a PDF, DOC, DOCX, JPG, JPEG, or PNG file.',
        'documentDescription.required_with' => 'Please provide a description for the document.',
        'documentDescription.min' => 'Document description must be at least 3 characters.',
    ];

    public function mount()
    {
        // Initialize textual loan type mirror
        $this->loan_type_2 = $this->loanType;
        
        // Initialize document counter
        $this->uploadedDocumentsCount = count($this->uploadedDocuments);
        
        // Always load loan products first - they should always be available
        $this->loadLoanProducts();
        
        // Check if client number is set and valid
        if (!$this->isClientNumberValid()) {
            $this->showClientNumberModal = true;
            // Don't return early - allow the component to render with products loaded
            // but without client-specific data
        } else {
            // Only load client-specific data if we have a valid client number
            $this->loadExistingLoans();
            $this->loadMemberData();
            
            // Always load existing collateral data for top-up/restructure loans
            if (in_array($this->loanType, ['Top-up', 'Restructuring', 'Restructure'])) {
                $loanId = $this->selectedLoanForTopUp ?: $this->selectedLoanForRestructure;
                if ($loanId) {
                    $this->loadExistingGuarantorData();
                }
            }
            // If we're on step 4 and have a top-up/restructure loan, load existing documents data
            if ($this->currentStep === 4 && in_array($this->loanType, ['Top-up', 'Restructuring', 'Restructure'])) {
                $this->reloadExistingDocumentsData();
            }
        }
    }

    public function isClientNumberValid()
    {
        if (empty($this->client_number)) {
            return false;
        }
        
        // Check if client number exists in clients table
        $client = DB::table('clients')->where('client_number', $this->client_number)->first();
        return $client !== null;
    }

    public function submitClientNumber()
    {
        $this->clientNumberError = '';
        
        if (empty($this->inputClientNumber)) {
            $this->clientNumberError = 'Please enter a client number.';
            return;
        }
        
        // Check if client number exists in clients table
        $client = DB::table('clients')->where('client_number', $this->inputClientNumber)->first();
        
        if (!$client) {
            $this->clientNumberError = 'Invalid client number. Please check and try again.';
            return;
        }
        
        // Set the valid client number
        $this->client_number = $this->inputClientNumber;
        $this->showClientNumberModal = false;
        $this->inputClientNumber = '';
        
        // Load data with the new client number
        $this->loadLoanProducts();
        $this->loadExistingLoans();
        $this->loadMemberData();
        
        session()->flash('success', 'Client number verified successfully!');
    }

    public function closeClientNumberModal()
    {
        $this->showClientNumberModal = false;
        $this->inputClientNumber = '';
        $this->clientNumberError = '';
    }

    public function loadLoanProducts()
    {
        // For Top-up and Restructuring, we need to include all products (even inactive)
        // so that the product from the existing loan can be selected
        if (in_array($this->loanType, ['Top-up', 'Restructuring', 'Restructure'])) {
            $this->loanProducts = DB::table('loan_sub_products')
                ->orderBy('sub_product_name')
                ->get();
        } else {
            // For new loans, load only active loan products
            // Use case-insensitive comparison for status
            $this->loanProducts = DB::table('loan_sub_products')
                ->where(function($query) {
                    $query->whereRaw("LOWER(sub_product_status) = ?", ['active'])
                          ->orWhere('sub_product_status', '1')
                          ->orWhere('sub_product_status', 'Active') // Also check exact case
                          ->orWhereNull('sub_product_status');
                })
                ->orderBy('sub_product_name')
                ->get();
        }
        
        $this->hasLoanProducts = count($this->loanProducts) > 0;
        
        // Log for debugging
        \Log::info('LoanApplication: Loaded loan products', [
            'loan_type' => $this->loanType,
            'total_products' => $this->loanProducts->count(),
            'products' => $this->loanProducts->pluck('sub_product_name', 'id')->toArray(),
            'hasLoanProducts' => $this->hasLoanProducts
        ]);
    }

    public function loadExistingLoans()
    {
        $currentUserId = auth()->id();
        $currentUserClientNumber = $this->client_number;
        
        \Log::info('LoanApplication: loadExistingLoans called', [
            'client_number' => $currentUserClientNumber,
            'loan_type' => $this->loanType
        ]);
        
        // Get current user's loans with collateral for Top-up
        $currentUserLoansQuery = DB::table('loans')
            ->where('client_number', $currentUserClientNumber)
            ->whereIn('status', ['ACTIVE', 'PENDING']);
            
        // For Top-up loans, only show loans that have existing collateral
        if ($this->loanType === 'Top-up') {
            $currentUserLoansQuery->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('loan_guarantors')
                      ->join('loan_collaterals', 'loan_guarantors.id', '=', 'loan_collaterals.loan_guarantor_id')
                      ->whereRaw('loan_guarantors.loan_id = loans.id')
                      ->where('loan_guarantors.status', 'active')
                      ->where('loan_collaterals.status', 'active');
            });
        }
        
        $currentUserLoans = $currentUserLoansQuery->get();
        
        \Log::info('LoanApplication: Current user loans found', [
            'count' => $currentUserLoans->count(),
            'loan_type_filter' => $this->loanType === 'Top-up' ? 'with_collateral' : 'all_active'
        ]);
        
        $currentUserLoans = $currentUserLoans->map(function($loan) {
            // Get collateral information for display
            $collateralInfo = '';
            if ($this->loanType === 'Top-up') {
                $totalCollateral = DB::table('loan_guarantors')
                    ->join('loan_collaterals', 'loan_guarantors.id', '=', 'loan_collaterals.loan_guarantor_id')
                    ->where('loan_guarantors.loan_id', $loan->id)
                    ->where('loan_guarantors.status', 'active')
                    ->where('loan_collaterals.status', 'active')
                    ->sum('loan_collaterals.collateral_amount');
                
                $collateralInfo = ' (Collateral: TZS ' . number_format($totalCollateral, 0) . ')';
            }
            
            return [
                'id' => $loan->id,
                'loan_id' => $loan->loan_id,
                'display_text' => "Loan: TZS " . number_format($loan->principle ?? 0, 0) . $collateralInfo,
                'owner' => 'current_user',
                'owner_name' => auth()->user()->name,
                'owner_client_number' => $this->client_number,
                'has_collateral' => $this->loanType === 'Top-up'
            ];
        });

        // Get other members' loans (for takeover) - only if not Top-up
        $otherMemberLoans = collect();
        if ($this->loanType !== 'Top-up') {
            $otherMemberLoansQuery = DB::table('loans')
                ->where('client_number', '!=', $currentUserClientNumber)
                ->whereIn('status', ['ACTIVE']);
                
            $otherMemberLoans = $otherMemberLoansQuery->get()
                ->map(function($loan) {
                    $client = DB::table('clients')->where('client_number', $loan->client_number)->first();
                    return [
                        'id' => $loan->id,
                        'loan_id' => $loan->loan_id,
                        'display_text' => "Loan: TZS " . number_format($loan->principle ?? 0, 0),
                        'owner' => 'other_member',
                        'owner_name' => ($client->first_name ?? '') . ' ' . ($client->last_name ?? 'Unknown'),
                        'owner_client_number' => $loan->client_number,
                        'has_collateral' => false
                    ];
                });
        }

        $this->existingLoansForSelection = $currentUserLoans->merge($otherMemberLoans);
        
        \Log::info('LoanApplication: Total loans for selection', [
            'total_count' => $this->existingLoansForSelection->count(),
            'current_user_loans' => $currentUserLoans->count(),
            'other_member_loans' => $otherMemberLoans->count(),
            'loan_type' => $this->loanType
        ]);
    }

    public function loadMemberData()
    {
        $clientNumber = $this->client_number;
        $client = DB::table('clients')->where('client_number', $clientNumber)->first();
        
        if ($client) {
            // Load member accounts for collateral
            $this->totalSavings = DB::table('accounts')
                ->where('client_number', $clientNumber)
                ->where('product_number', '2000')
                ->sum('balance');
            
            $this->activeLoansCount = DB::table('loans')
                ->where('client_number', $clientNumber)
                ->whereIn('status', ['ACTIVE', 'PENDING'])
                ->count();

            // Compute months to retirement if DOB exists
            if (!empty($client->date_of_birth)) {
                try {
                    $dob = Carbon::parse($client->date_of_birth);
                    $retirementAge = 60; // standard
                    $retirementDate = $dob->copy()->addYears($retirementAge)->endOfMonth();
                    $this->monthsToRetirement = now()->diffInMonths($retirementDate, false);
                } catch (\Throwable $e) {
                    $this->monthsToRetirement = null;
                }
            }
        }
    }

    public function updatedSelectedProductId($value)
    {
        //dd($value);
        \Log::info('LoanApplication: Product selection changed', [
            'selected_value' => $value,
            'type' => gettype($value)
        ]);
        
        if ($value) {
            $this->selectedProduct = DB::table('loan_sub_products')->find($value);
            
            \Log::info('LoanApplication: Product loaded', [
                'product_found' => $this->selectedProduct ? 'Yes' : 'No',
                'product_name' => $this->selectedProduct->sub_product_name ?? 'N/A',
                'sub_product_id' => $this->selectedProduct->sub_product_id ?? null,
                'db_key_used' => 'id'
            ]);
            
            if ($this->selectedProduct) {
                $this->updateSelectedProductProperties();
                $this->loadProductCharges();
            }
            $this->calculateLoanDetails();
        }
        $this->recalculateStep1Warnings();
        $this->checkPolicyViolations();

        // If on Step 3, recalculate policy violations (members portal pattern)
        if ($this->currentStep === 3) {
            $this->calculateStep1PolicyViolations();
        }
    }
    
    private function updateSelectedProductProperties()
    {
        if ($this->selectedProduct) {
            // Update public properties for view access
            $this->selectedProductName = $this->selectedProduct->sub_product_name ?? '';
            $this->selectedProductInterestRate = $this->selectedProduct->interest_value ?? 0;
            $this->selectedProductManagementFee = $this->selectedProduct->management_fee ?? 0;
            $this->selectedProductMajangaFee = $this->selectedProduct->majanga_fee ?? 0;
            $this->selectedProductMinTerm = $this->selectedProduct->min_term ?? 1;
            $this->selectedProductMaxTerm = $this->selectedProduct->max_term ?? 60;

            // Amount limits and simple requirements
            $this->selectedProductMinAmount = (float)($this->selectedProduct->principle_min_value ?? 0);
            $this->selectedProductMaxAmount = (float)($this->selectedProduct->principle_max_value ?? 0);
            // Handle different case variations for require_image_member and require_image_id
            $requireMember = $this->selectedProduct->require_image_member ?? '';
            $requireId = $this->selectedProduct->require_image_id ?? '';
            $this->selectedProductRequiresMemberImage = (strtoupper($requireMember) === 'YES' || $requireMember === 'Yes');
            $this->selectedProductRequiresIdImage = (strtoupper($requireId) === 'YES' || $requireId === 'Yes');
            // Refresh charges when product changes
            $this->loadProductCharges();
        }
    }

    private function loadProductCharges(): void
    {
        try {
            $this->rehydrateSelectedProduct();
            $this->productCharges = [];
            $this->productInsurance = [];
            $this->totalChargesAmount = 0;
            $this->totalInsuranceAmount = 0;
            
            if (!$this->selectedProduct) {
                return;
            }
            
            // Charges are keyed by sub_product_id
            $subProductId = $this->selectedProduct->sub_product_id ?? null;
            if (!$subProductId) {
                return;
            }
            
            $allCharges = DB::table('loan_product_charges')
                ->where('loan_product_id', $subProductId)
                ->get();

            // Backward-compatibility: some historical rows may have stored the numeric product ID
            if ($allCharges->isEmpty()) {
                $altId = $this->selectedProduct->id ?? $this->selectedProductId;
                if (!empty($altId)) {
                    $allCharges = DB::table('loan_product_charges')
                        ->where('loan_product_id', (string) $altId)
                        ->get();
                }
            }
            
            // Compute amounts based on current principal
            $principal = (float)($this->loanAmount ?? 0);
            $tenure = (int)($this->repaymentPeriod ?? 12); // Default to 12 months if not set
            
            $computedCharges = [];
            $computedInsurance = [];
            $totalCharges = 0.0;
            $totalInsurance = 0.0;
            
            foreach ($allCharges as $c) {
                $amount = 0.0;
                $baseAmount = 0.0;
                $capApplied = null;
                
                // Check if this is a charge or insurance
                $isInsurance = (strtolower($c->type) === 'insurance');
                
                if (strtolower((string)$c->value_type) === 'percentage') {
                    $baseAmount = (float)$principal;

                    if ($isInsurance) {
                        // Insurance: one-time premium (MEMBERS PORTAL PATTERN)
                        // Value already in decimal form (e.g., 0.00125 = 0.125%)
                        // No caps applied to insurance
                        $amount = ($principal > 0) ? ($principal * (float)$c->value) : 0.0;
                    } else {
                        // Charges: one-time percentage with caps (MEMBERS PORTAL PATTERN)
                        // Value already in decimal form (e.g., 0.003 = 0.3%)
                        $amount = ($principal > 0) ? ($principal * (float)$c->value) : 0.0;

                        // Apply min cap if set (only for charges)
                        if (!empty($c->min_cap) && $amount < (float)$c->min_cap) {
                            $amount = (float)$c->min_cap;
                            $capApplied = 'Min cap';
                        }

                        // Apply max cap if set (only for charges)
                        if (!empty($c->max_cap) && $amount > (float)$c->max_cap) {
                            $amount = (float)$c->max_cap;
                            $capApplied = 'Max cap';
                        }
                    }
                } else {
                    // Fixed amount
                    if ($isInsurance) {
                        // Fixed insurance also multiplied by tenure
                        $amount = (float)$c->value * $tenure;
                    } else {
                        $amount = (float)$c->value;
                    }
                }
                
                $chargeData = [
                    'type' => $c->type,
                    'name' => $c->name,
                    'value_type' => $c->value_type,
                    'value' => (float)$c->value,
                    'account_id' => $c->account_id,
                    'base_amount' => $baseAmount,
                    'min_cap' => $c->min_cap ?? null,
                    'max_cap' => $c->max_cap ?? null,
                    'cap_applied' => $capApplied,
                    'computed_amount' => round($amount, 2),
                    'tenure' => $isInsurance ? $tenure : null, // Include tenure for insurance
                ];
                
                if ($isInsurance) {
                    $computedInsurance[] = $chargeData;
                    $totalInsurance += $amount;
                } else {
                    $computedCharges[] = $chargeData;
                    $totalCharges += $amount;
                }
            }
            
            $this->productCharges = $computedCharges;
            $this->productInsurance = $computedInsurance;
            $this->totalChargesAmount = round($totalCharges, 2);
            $this->totalInsuranceAmount = round($totalInsurance, 2);

            \Log::info('Dashboard LoanApplication: Charges and Insurance loaded', [
                'principal' => $principal,
                'tenure' => $tenure,
                'charges_count' => count($computedCharges),
                'insurance_count' => count($computedInsurance),
                'total_charges' => $this->totalChargesAmount,
                'total_insurance' => $this->totalInsuranceAmount,
                'productCharges' => $computedCharges,
                'productInsurance' => $computedInsurance
            ]);

            // Calculate firstInterest and takeHome after charges are loaded (MEMBERS PORTAL PATTERN)
            $this->calculateFirstInterestAndTakeHome();

        } catch (\Throwable $e) {
            \Log::error('LoanApplication: Failed to load product charges', [
                'error' => $e->getMessage(),
                'selected_product_id' => $this->selectedProductId,
                'selected_product_sub_id' => $this->selectedProduct->sub_product_id ?? null,
            ]);
        }
    }

    /**
     * Calculate First Interest and Take Home (MEMBERS PORTAL PATTERN)
     * Called after product charges are loaded
     */
    private function calculateFirstInterestAndTakeHome(): void
    {
        try {
            if (!$this->selectedProduct || !$this->loanAmount) {
                $this->firstInterest = 0;
                $this->takeHome = 0;
                return;
            }

            $principal = (float)$this->loanAmount;
            $annualInterestRate = (float)($this->selectedProduct->interest_value ?? 0);

            // Calculate first interest (interest from disbursement to first payroll date)
            if ($annualInterestRate > 0 && $principal > 0) {
                // Get member's payroll date from their group (MEMBERS PORTAL PATTERN)
                $daysToFirstInstallment = 30; // Default fallback

                try {
                    $client = DB::table('clients')->where('client_number', $this->client_number)->first();
                    if ($client && $client->member_group) {
                        // Join on member_groups.id (member_group is the ID, not the name)
                        $memberGroup = DB::table('member_groups')->where('id', $client->member_group)->first();

                        if ($memberGroup && $memberGroup->payrol_date) {
                            $payrollDay = (int)$memberGroup->payrol_date;
                            $disbursementDate = \Carbon\Carbon::now();

                            // Calculate first payment date based on payroll day
                            if ($disbursementDate->day < $payrollDay) {
                                // Use this month's payroll date
                                $firstPaymentDate = $disbursementDate->copy()->day($payrollDay);
                            } else {
                                // Use next month's payroll date
                                $firstPaymentDate = $disbursementDate->copy()->addMonth()->day($payrollDay);
                            }

                            // Calculate actual days
                            $daysToFirstInstallment = $disbursementDate->diffInDays($firstPaymentDate);

                            \Log::info('LoanApplication Dashboard: First payment date calculated', [
                                'client_number' => $this->client_number,
                                'member_group_id' => $client->member_group,
                                'member_group_name' => $memberGroup->group_name ?? null,
                                'payroll_day' => $payrollDay,
                                'disbursement_date' => $disbursementDate->format('Y-m-d'),
                                'first_payment_date' => $firstPaymentDate->format('Y-m-d'),
                                'days_to_first_installment' => $daysToFirstInstallment
                            ]);
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Could not calculate exact days to first payment, using default 30 days', [
                        'error' => $e->getMessage(),
                        'client_number' => $this->client_number
                    ]);
                }

                $dailyInterestAmount = ($principal * $annualInterestRate / 100) / 365;
                $firstInterestAmount = $dailyInterestAmount * $daysToFirstInstallment;
                $this->firstInterest = round($firstInterestAmount, 2);
            } else {
                $this->firstInterest = 0;
            }

            // Calculate early settlement penalty for top-up loans
            $this->earlyTopUpPenalty = $this->calculateEarlyTopUpPenalty();

            // Calculate take home (MEMBERS PORTAL PATTERN)
            // Take Home = Loan Amount - Management Fee - Insurance - First Interest - Early Top Up Penalty
            $this->takeHome = $principal - $this->totalChargesAmount - $this->totalInsuranceAmount - $this->firstInterest - $this->earlyTopUpPenalty;

            // Adjust for top-up loans (deduct original loan balance)
            if ($this->loanType === 'Top-up' && $this->selectedLoanForTopUp) {
                $originalLoan = DB::table('loans')->where('id', $this->selectedLoanForTopUp)->first();
                if ($originalLoan) {
                    $originalLoanBalance = (float)($originalLoan->principle ?? 0) - (float)($originalLoan->total_paid ?? 0);
                    $this->takeHome -= $originalLoanBalance;
                }
            }

            // Adjust for restructure loans (deduct original loan balance)
            if (in_array($this->loanType, ['Restructure', 'Restructuring']) && $this->selectedLoanForRestructure) {
                $originalLoan = DB::table('loans')->where('id', $this->selectedLoanForRestructure)->first();
                if ($originalLoan) {
                    $originalLoanBalance = (float)($originalLoan->principle ?? 0) - (float)($originalLoan->total_paid ?? 0);
                    $this->takeHome -= $originalLoanBalance;
                }
            }

            // Calculate adjustment for Restructure loans
            // Adjustment = Management Fees + Insurance + First Interest
            if (in_array($this->loanType, ['Restructure', 'Restructuring'])) {
                $this->adjustmentValue = $this->totalChargesAmount + $this->totalInsuranceAmount + $this->firstInterest;

                // Take home should be 0 for restructure loans (no disbursement)
                $this->takeHome = 0;

                \Log::info('LoanApplication: Restructure adjustment calculated', [
                    'management_fees' => $this->totalChargesAmount,
                    'insurance' => $this->totalInsuranceAmount,
                    'first_interest' => $this->firstInterest,
                    'adjustment_total' => $this->adjustmentValue
                ]);
            } else {
                $this->adjustmentValue = 0;
            }

            \Log::info('LoanApplication Dashboard: First Interest and Take Home calculated', [
                'loan_type' => $this->loanType,
                'principal' => $principal,
                'total_charges' => $this->totalChargesAmount,
                'total_insurance' => $this->totalInsuranceAmount,
                'first_interest' => $this->firstInterest,
                'early_top_up_penalty' => $this->earlyTopUpPenalty,
                'take_home_before_adjustments' => $principal - $this->totalChargesAmount - $this->totalInsuranceAmount - $this->firstInterest - $this->earlyTopUpPenalty,
                'take_home' => $this->takeHome,
                'adjustment_value' => $this->adjustmentValue,
                'calculation_breakdown' => [
                    'principal' => $principal,
                    'minus_charges' => -$this->totalChargesAmount,
                    'minus_insurance' => -$this->totalInsuranceAmount,
                    'minus_first_interest' => -$this->firstInterest,
                    'minus_early_penalty' => -$this->earlyTopUpPenalty,
                    'equals_take_home' => $this->takeHome
                ]
            ]);

        } catch (\Throwable $e) {
            \Log::error('LoanApplication: Failed to calculate first interest and take home', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->firstInterest = 0;
            $this->takeHome = 0;
        }
    }

    /**
     * Calculate early top-up penalty (5% if within 6 months)
     */
    private function calculateEarlyTopUpPenalty(): float
    {
        if ($this->loanType !== 'Top-up' || !$this->selectedLoanForTopUp) {
            return 0;
        }

        try {
            $originalLoan = DB::table('loans')->where('id', $this->selectedLoanForTopUp)->first();

            if (!$originalLoan || !$originalLoan->created_at) {
                return 0;
            }

            $loanCreatedAt = \Carbon\Carbon::parse($originalLoan->created_at);
            $today = \Carbon\Carbon::now();
            $monthsDifference = $loanCreatedAt->diffInMonths($today);

            // Apply 5% penalty if loan is less than 6 months old
            if ($monthsDifference < 6) {
                $originalBalance = (float)($originalLoan->principle ?? 0) - (float)($originalLoan->total_paid ?? 0);
                $penaltyAmount = round(($originalBalance * 5) / 100, 2);

                \Log::info('LoanApplication Dashboard: Early top-up penalty calculated', [
                    'original_loan_id' => $this->selectedLoanForTopUp,
                    'months_difference' => $monthsDifference,
                    'original_balance' => $originalBalance,
                    'penalty_amount' => $penaltyAmount
                ]);

                return $penaltyAmount;
            }

            return 0;
        } catch (\Throwable $e) {
            \Log::error('LoanApplication: Error calculating early top-up penalty', [
                'error' => $e->getMessage(),
                'loan_id' => $this->selectedLoanForTopUp
            ]);
            return 0;
        }
    }

    public function updatedLoanAmount($value)
    {
        if ($value && $this->selectedProduct) {
            $this->calculateLoanDetails();
        }
        // Recompute charges and insurance based on new principal
        $this->loadProductCharges();
        $this->recalculateStep1Warnings();
        $this->checkPolicyViolations();
    }

    public function updatedSalaryTakeHome($value)
    {
        if ($value && $this->selectedProduct) {
            $this->calculateLoanDetails();
        }
        $this->recalculateStep1Warnings();
        $this->checkPolicyViolations();
    }

    public function updatedRepaymentPeriod($value)
    {
        if ($this->selectedProduct) {
            // Reload charges and insurance with new tenure
            $this->loadProductCharges();
            $this->calculateLoanDetails();
        }
        $this->recalculateStep1Warnings();
        $this->checkPolicyViolations();
    }

    public function updatedLoanType($value)
    {
        \Log::info('LoanApplication: Loan type changed', [
            'new_loan_type' => $value,
            'previous_loan_type' => $this->loanType
        ]);
        
        // Clear existing loan selections when loan type changes
        $this->selectedLoanForTopUp = '';
        $this->selectedLoanForRestructure = '';
        $this->selectedLoanForTakeover = '';
        
        // Clear existing guarantor and collateral data
        $this->existingGuarantorData = [];
        $this->existingCollateralData = [];
        $this->resetCollateralFormFields();
        
        // Reload existing loans for the new loan type
        $this->loadExistingLoans();
        
        \Log::info('LoanApplication: Loan type change processed', [
            'new_loan_type' => $value,
            'loans_available' => $this->existingLoansForSelection->count()
        ]);
    }
    
    public function updatedSelectedLoanForTopUp($value)
    {
        \Log::info('LoanApplication: Top-up loan selection changed', [
            'selected_loan_id' => $value,
            'loan_type' => $this->loanType
        ]);
        
        if ($value && $this->loanType === 'Top-up') {
            // Load existing guarantor and collateral data immediately
            $this->loadExistingGuarantorData();
            
            // Process the top-up loan data
            $this->processTopUp();
            
            \Log::info('LoanApplication: Top-up loan data loaded', [
                'loan_id' => $value,
                'existing_guarantor_count' => count($this->existingGuarantorData ?? []),
                'existing_collateral_count' => count($this->existingCollateralData ?? [])
            ]);
        } else {
            // Clear existing data if no loan selected
            $this->existingGuarantorData = [];
            $this->existingCollateralData = [];
            $this->resetCollateralFormFields();
        }
    }
    
    public function updatedSelectedLoanForRestructure($value)
    {
        \Log::info('LoanApplication: Restructure loan selection changed', [
            'selected_loan_id' => $value,
            'loan_type' => $this->loanType
        ]);
        
        if ($value && in_array($this->loanType, ['Restructure', 'Restructuring'])) {
            // Load existing guarantor and collateral data immediately
            $this->loadExistingGuarantorData();
            
            // Process the restructure loan data
            $this->processRestructure();
            
            \Log::info('LoanApplication: Restructure loan data loaded', [
                'loan_id' => $value,
                'existing_guarantor_count' => count($this->existingGuarantorData ?? []),
                'existing_collateral_count' => count($this->existingCollateralData ?? [])
            ]);
        } else {
            // Clear existing data if no loan selected
            $this->existingGuarantorData = [];
            $this->existingCollateralData = [];
            $this->resetCollateralFormFields();
        }
    }

    public function calculateLoanDetails()
    {
        if (!$this->selectedProduct || !$this->loanAmount || !$this->repaymentPeriod) {
            return;
        }

        $calculationService = new LoanCalculationService();

        // Ensure charges are up to date and use them instead of legacy management/majanga placeholders
        if (empty($this->productCharges)) {
            $this->loadProductCharges();
        }

        $details = $calculationService->calculateLoanDetails(
            $this->loanAmount,
            $this->selectedProduct->interest_value,
            $this->repaymentPeriod,
            0, // management fee placeholder not used; dynamic charges applied separately
            0  // majanga fee placeholder not used; dynamic charges applied separately
        );

        $this->monthlyInstallment = $details['monthly_installment'];

        // Calculate total amount payable (MEMBERS PORTAL PATTERN)
        // Total = monthly installment × term (this already includes interest)
        $this->totalAmountPayable = round($this->monthlyInstallment * $this->repaymentPeriod, 3, PHP_ROUND_HALF_UP);

        // Calculate eligible loan amount (MEMBERS PORTAL PATTERN)
        // Eligible = (Total Savings + Total Deposits) × 2
        $this->eligibleLoanAmount = ($this->totalSavings + ($this->totalDeposits ?? 0)) * 2;

        \Log::info('LoanApplication Dashboard: Loan details calculated', [
            'monthly_installment' => $this->monthlyInstallment,
            'total_amount_payable' => $this->totalAmountPayable,
            'eligible_loan_amount' => $this->eligibleLoanAmount,
            'total_savings' => $this->totalSavings,
            'total_deposits' => $this->totalDeposits ?? 0
        ]);
    }

    public function nextStep()
    {
        // BLOCKING: Validate current step before proceeding
        $isValid = $this->validateStep();

        // If validation fails, block progression
        if (!$isValid) {
            return;
        }

        // Gate moving past Step 3 if there are warnings and approval letter is missing
        if ($this->currentStep === 3 && $this->getHasWarningsProperty() && !$this->exceptionApprovalUploaded) {
            session()->flash('error', 'Breaches detected. Please upload an exception approval letter to proceed to the next step.');
            return;
        }
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
            $this->processStepData();
            $this->rehydrateSelectedProduct();
            // Refresh dynamic product charges on each step change
            $this->loadProductCharges();
            // Recompute loan details to keep monthlyInstallment/total payable fresh
            $this->calculateLoanDetails();

            // Check savings violations when moving to step 2
            if ($this->currentStep === 2) {
                $this->checkSavingsPolicyViolations();
                $this->reloadExistingCollateralData();
            }

            // Check policy violations when moving to step 3 (Application Summary)
            if ($this->currentStep === 3) {
                \Log::info('Moving to Step 3 - Recalculating all values', [
                    'client_number' => $this->client_number,
                    'loanAmount' => $this->loanAmount,
                    'repaymentPeriod' => $this->repaymentPeriod,
                    'selectedProductId' => $this->selectedProductId,
                    'selectedProduct_exists' => $this->selectedProduct ? true : false,
                    'selectedProductName' => $this->selectedProductName ?? 'Not set'
                ]);

                // Ensure product is loaded
                $this->rehydrateSelectedProduct();

                // Update product properties if product is available
                if ($this->selectedProduct) {
                    $this->updateSelectedProductProperties();
                }

                $this->loadProductCharges(); // Refresh charges and take home
                $this->calculateLoanDetails(); // Refresh calculations including first interest
                $this->calculateStep1PolicyViolations();
            }

            // Reload existing documents data when entering step 4
            if ($this->currentStep === 4) {
                $this->reloadExistingDocumentsData();
            }
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            $this->rehydrateSelectedProduct();
            // Refresh dynamic product charges on each step change
            $this->loadProductCharges();
            // Recompute loan details to keep monthlyInstallment/total payable fresh
            $this->calculateLoanDetails();

            // Reload existing collateral data when entering step 2
            if ($this->currentStep === 2) {
                $this->reloadExistingCollateralData();
            }

            // Recalculate when moving back to step 3
            if ($this->currentStep === 3) {
                \Log::info('Moving back to Step 3 - Recalculating all values', [
                    'client_number' => $this->client_number,
                    'loanAmount' => $this->loanAmount,
                    'repaymentPeriod' => $this->repaymentPeriod,
                    'selectedProductId' => $this->selectedProductId,
                    'selectedProduct_exists' => $this->selectedProduct ? true : false
                ]);

                // Ensure product is loaded
                $this->rehydrateSelectedProduct();

                // Update product properties if product is available
                if ($this->selectedProduct) {
                    $this->updateSelectedProductProperties();
                }

                $this->loadProductCharges(); // Refresh charges and take home
                $this->calculateLoanDetails(); // Refresh calculations including first interest
                $this->calculateStep1PolicyViolations();
            }

            // Reload existing documents data when entering step 4
            if ($this->currentStep === 4) {
                $this->reloadExistingDocumentsData();
            }
        }
    }

    private function rehydrateSelectedProduct(): void
    {
        // Ensure $selectedProduct is available between Livewire requests
        if (!$this->selectedProduct && !empty($this->selectedProductId)) {
            $this->selectedProduct = \DB::table('loan_sub_products')->find($this->selectedProductId);
        }
    }

    public function validateStep()
    {
        switch ($this->currentStep) {
            case 1:
                return $this->validateStep1();
            case 2:
                return $this->validateStep2();
            case 3:
                return $this->validateStep3();
            case 4:
                return $this->validateStep4();
            default:
                return true;
        }
    }

    public function validateStep1()
    {
        // BLOCKING: Check all required fields are filled
        $errors = [];

        // Log current state for debugging
        \Log::info('Step 1 Validation Check', [
            'client_number' => $this->client_number,
            'loanType' => $this->loanType,
            'selectedProductId' => $this->selectedProductId,
            'selectedProduct_exists' => $this->selectedProduct ? true : false,
            'loanAmount' => $this->loanAmount,
            'repaymentPeriod' => $this->repaymentPeriod
        ]);

        // Check member selection
        if (empty($this->client_number)) {
            $errors[] = 'Please select a member';
        }

        // Check loan type
        if (empty($this->loanType)) {
            $errors[] = 'Please select a loan type';
        }

        // For Restructure loans: Ensure loan is selected and data is loaded
        if (in_array($this->loanType, ['Restructure', 'Restructuring'])) {
            if (empty($this->selectedLoanForRestructure)) {
                $errors[] = 'Please select a loan to restructure';
            } else {
                // Try to rehydrate restructure loan data if loan amount is not set
                if (empty($this->loanAmount) || $this->loanAmount <= 0) {
                    \Log::info('Rehydrating restructure loan data', [
                        'selectedLoanForRestructure' => $this->selectedLoanForRestructure,
                        'current_loanAmount' => $this->loanAmount
                    ]);
                    $this->processRestructure();

                    // Check again after rehydration
                    if (!empty($this->loanAmount) && $this->loanAmount > 0) {
                        \Log::info('Restructure loan data rehydrated successfully', [
                            'loanAmount' => $this->loanAmount,
                            'selectedProductId' => $this->selectedProductId
                        ]);
                    }
                }
            }
        }

        // Check loan product
        if (empty($this->selectedProductId) || !$this->selectedProduct) {
            $errors[] = 'Please select a loan product';

            // Try to rehydrate product if ID exists but product doesn't
            if (!empty($this->selectedProductId) && !$this->selectedProduct) {
                $this->rehydrateSelectedProduct();

                // Check again after rehydration
                if ($this->selectedProduct) {
                    // Remove the error since we successfully loaded the product
                    array_pop($errors);
                    \Log::info('Product rehydrated successfully', [
                        'product_id' => $this->selectedProductId,
                        'product_name' => $this->selectedProduct->sub_product_name ?? 'Unknown'
                    ]);
                }
            }
        }

        // Check loan amount (after potential rehydration for restructure loans)
        if (empty($this->loanAmount) || $this->loanAmount <= 0) {
            $errors[] = 'Please enter a valid loan amount';
        }

        // Check repayment period
        if (empty($this->repaymentPeriod) || $this->repaymentPeriod <= 0) {
            $errors[] = 'Please enter a valid repayment period (tenure)';
        }

        // If there are errors, show them and block progression
        if (!empty($errors)) {
            $errorMessage = implode('. ', $errors);
            \Log::warning('Step 1 Validation Failed', [
                'errors' => $errors,
                'client_number' => $this->client_number,
                'selectedProductId' => $this->selectedProductId
            ]);
            session()->flash('error', $errorMessage);
            return false;
        }

        \Log::info('Step 1 Validation Passed');

        // All required fields filled - compute warnings but allow progression
        $this->recalculateStep1Warnings();
        return true;
    }

    public function validateStep2()
    {
        // After swapping steps: Step 2 is now Guarantor & Collateral
        // Non-blocking: compute inline warnings only
        $this->recalculateStep3Warnings();
        return true;
    }

    public function validateStep3()
    {
        // After swapping steps: Step 3 is now Application Summary (informational)
        return true;
    }

    public function validateStep4()
    {
        // Step 4 validation is handled in the upload process
        return true;
    }

    public function checkPolicyViolations()
    {
        $this->step1PolicyViolations = [];
        
        // Log for debugging
        \Log::info('LoanApplication: Checking policy violations', [
            'salary_take_home' => $this->salaryTakeHome,
            'monthly_installment' => $this->monthlyInstallment,
            'loan_amount' => $this->loanAmount,
            'repayment_period' => $this->repaymentPeriod,
            'has_selected_product' => $this->selectedProduct ? true : false
        ]);
        
        // Check loan amount vs income ratio (DTI - Debt to Income)
        $salaryTakeHome = (float)$this->salaryTakeHome;
        $monthlyInstallment = (float)$this->monthlyInstallment;
        
        if ($salaryTakeHome > 0 && $monthlyInstallment > 0) {
            // Get DTI threshold from product or use default of 70%
            $dtiThreshold = 70; // Default threshold
            if ($this->selectedProduct && isset($this->selectedProduct->score_limit)) {
                $dtiThreshold = (float)$this->selectedProduct->score_limit;
            }
            
            $incomeRatio = ($monthlyInstallment / $salaryTakeHome) * 100;
            
            \Log::info('LoanApplication: DTI calculation', [
                'income_ratio' => $incomeRatio,
                'dti_threshold' => $dtiThreshold,
                'is_breach' => $incomeRatio > $dtiThreshold
            ]);
            
            if ($incomeRatio > $dtiThreshold) {
                $this->step1PolicyViolations[] = [
                    'severity' => 'high',
                    'title' => 'Installment exceeds ' . rtrim(rtrim(number_format($dtiThreshold, 2), '0'), '.') . '% of take-home',
                    'current_value' => number_format($incomeRatio, 1) . '%',
                    'limit_value' => rtrim(rtrim(number_format($dtiThreshold, 2), '0'), '.') . '%',
                    'recommendation' => 'Consider reducing loan amount or increasing repayment period'
                ];
            }
        }

        // Check loan amount vs savings ratio
        $loanAmount = (float)$this->loanAmount;
        $totalSavings = (float)$this->totalSavings;
        
        if ($totalSavings > 0 && $loanAmount > 0) {
            $savingsRatio = $loanAmount / $totalSavings;
            if ($savingsRatio > 3) {
                $this->step1PolicyViolations[] = [
                    'severity' => 'medium',
                    'title' => 'Loan Amount Exceeds Savings Limit',
                    'current_value' => number_format($savingsRatio, 1) . 'x savings',
                    'limit_value' => '3x savings',
                    'recommendation' => 'Provide additional collateral or reduce loan amount'
                ];
            }
        }
        
        // Log final violations
        \Log::info('LoanApplication: Policy violations check complete', [
            'violations_count' => count($this->step1PolicyViolations),
            'violations' => $this->step1PolicyViolations,
            'has_any_breaches' => $this->hasAnyBreaches()
        ]);
    }

    public function checkSavingsPolicyViolations()
    {
        $this->step3SavingsViolations = [];

        if ($this->totalSavings > 0) {
            $savingsLimit = $this->totalSavings * 3;
            if ($this->loanAmount > $savingsLimit) {
                $shortfall = $this->loanAmount - $savingsLimit;
                $this->step3SavingsViolations[] = [
                    'title' => 'Loan Amount Exceeds Standard Savings Multiplier',
                    'description' => 'Your loan amount exceeds the standard 3x savings multiplier limit.',
                    'current_value' => number_format($this->loanAmount, 2) . ' TZS',
                    'limit_value' => number_format($savingsLimit, 2) . ' TZS',
                    'savings_shortfall' => number_format($shortfall, 2) . ' TZS',
                    'recommendation' => 'Provide additional collateral to cover the shortfall amount.'
                ];
            }
        }
    }

    /**
     * Calculate Step 1 Policy Violations
     * Following members portal pattern - checks product limits and DSR
     * Called when moving to Step 3 (Application Summary)
     */
    public function calculateStep1PolicyViolations()
    {
        $this->step1PolicyViolations = [];

        // Check loan amount against product limits
        if ($this->selectedProduct && $this->loanAmount) {
            $minAmount = $this->selectedProduct->principle_min_value ?? 0;
            $maxAmount = $this->selectedProduct->principle_max_value ?? 0;

            if ($this->loanAmount < $minAmount) {
                $this->step1PolicyViolations[] = [
                    'severity' => 'high',
                    'title' => 'Loan Amount Below Minimum',
                    'current_value' => 'TZS ' . number_format($this->loanAmount, 0),
                    'limit_value' => 'TZS ' . number_format($minAmount, 0),
                    'recommendation' => 'Increase loan amount'
                ];
            }

            if ($maxAmount > 0 && $this->loanAmount > $maxAmount) {
                $this->step1PolicyViolations[] = [
                    'severity' => 'high',
                    'title' => 'Loan Amount Exceeds Maximum',
                    'current_value' => 'TZS ' . number_format($this->loanAmount, 0),
                    'limit_value' => 'TZS ' . number_format($maxAmount, 0),
                    'recommendation' => 'Reduce loan amount'
                ];
            }
        }

        // Check repayment period - only check maximum tenure (NOT minimum)
        if ($this->selectedProduct && $this->repaymentPeriod) {
            $maxTerm = $this->selectedProduct->max_term ?? 60;

            // Only check if tenure exceeds maximum
            if ($this->repaymentPeriod > $maxTerm) {
                $this->step1PolicyViolations[] = [
                    'severity' => 'medium',
                    'title' => 'Tenure Exceeds Maximum',
                    'current_value' => $this->repaymentPeriod . ' months',
                    'limit_value' => $maxTerm . ' months',
                    'recommendation' => 'Reduce tenure'
                ];
            }
        }

        // Check DSR (Debt Service Ratio) - using 50% limit
        if ($this->monthlyInstallment > 0 && $this->salaryTakeHome > 0) {
            $dsr = ($this->monthlyInstallment / $this->salaryTakeHome) * 100;
            if ($dsr > 50) {
                $this->step1PolicyViolations[] = [
                    'severity' => 'high',
                    'title' => 'High Debt Service Ratio',
                    'current_value' => number_format($dsr, 1) . '%',
                    'limit_value' => '50%',
                    'recommendation' => 'Reduce loan amount or increase tenure'
                ];
            }
        }

        \Log::info('LoanApplication Dashboard: Policy violations calculated', [
            'violations_count' => count($this->step1PolicyViolations),
            'violations' => $this->step1PolicyViolations,
            'has_any_breaches' => $this->hasAnyBreaches()
        ]);
    }

    public function processStepData()
    {
        switch ($this->currentStep) {
            case 2:
                // After swapping steps: Step 2 is Guarantor & Collateral
                $this->loadExistingGuarantorData();
                break;
            case 3:
                // After swapping steps: Step 3 is Application Summary
                $this->performCreditAssessment();
                break;
            case 5:
                $this->recalculateAllWarnings();
                break;
        }
    }

    public function performCreditAssessment()
    {
        try {
            $creditService = new LoanAssessmentService();
            $result = $creditService->assessCredit(
                $this->client_number,
                $this->loanAmount,
                $this->salaryTakeHome
            );
            
            // Update individual properties
            $this->creditScoreValue = $result['score'] ?? 0;
            $this->creditScoreRisk = $result['risk'] ?? 'UNKNOWN';
            $this->creditScoreGrade = $result['grade'] ?? 'XX';
            $this->creditScoreSource = $result['source'] ?? 'Internal';
            $this->creditScoreProbabilityOfDefault = $result['probability_of_default'] ?? 0;
            $this->creditScorePastDueAmount = $result['crb_data']['past_due_amount'] ?? 0;
            $this->creditScoreInquiriesLastMonth = $result['crb_data']['inquiries_last_month'] ?? 0;
            
        } catch (\Exception $e) {
            // Fallback to default values if service fails
            $this->creditScoreValue = 0;
            $this->creditScoreRisk = 'UNKNOWN';
            $this->creditScoreGrade = 'XX';
            $this->creditScoreSource = 'Internal';
            $this->creditScoreProbabilityOfDefault = 0;
            $this->creditScorePastDueAmount = 0;
            $this->creditScoreInquiriesLastMonth = 0;
        }
    }

    public function loadExistingGuarantorData()
    {
        \Log::info('LoanApplication: loadExistingGuarantorData called', [
            'loanType' => $this->loanType,
            'selectedLoanForTopUp' => $this->selectedLoanForTopUp,
            'selectedLoanForRestructure' => $this->selectedLoanForRestructure,
        ]);
        
        if (in_array($this->loanType, ['Top-up', 'Restructuring', 'Restructure'])) {
            $loanId = $this->selectedLoanForTopUp ?: $this->selectedLoanForRestructure;
            
            \Log::info('LoanApplication: Checking for existing guarantor/collateral', [
                'loanId' => $loanId,
            ]);
            
            if ($loanId) {
                // Get guarantor data and convert to array properly for Livewire persistence
                $guarantorData = DB::table('loan_guarantors')
                    ->where('loan_id', $loanId)
                    ->get()
                    ->map(function ($item) {
                        return (array) $item;
                    })
                    ->toArray();
                
                $this->existingGuarantorData = $guarantorData;
                
                \Log::info('LoanApplication: Guarantor data loaded', [
                    'guarantor_count' => count($this->existingGuarantorData),
                    'guarantor_data' => $this->existingGuarantorData,
                    'loan_id_queried' => $loanId,
                ]);
                
                // Get collateral data through loan_guarantors relationship and convert to array properly
                $collateralData = DB::table('loan_collaterals')
                    ->join('loan_guarantors', 'loan_collaterals.loan_guarantor_id', '=', 'loan_guarantors.id')
                    ->where('loan_guarantors.loan_id', $loanId)
                    ->select('loan_collaterals.*')
                    ->get()
                    ->map(function ($item) {
                        return (array) $item;
                    })
                    ->toArray();
                
                $this->existingCollateralData = $collateralData;
                
                \Log::info('LoanApplication: Collateral data loaded', [
                    'collateral_count' => count($this->existingCollateralData),
                    'collateral_data' => $this->existingCollateralData,
                    'loan_id_queried' => $loanId,
                    'guarantor_ids' => collect($this->existingGuarantorData)->pluck('id')->toArray(),
                ]);
                
                // Don't pre-populate form fields - leave them empty for additional collateral
                // The existing collateral is displayed in the "Existing Collateral from Original Loan" section
                
                // Reset form fields to ensure they are empty for additional collateral
                $this->resetCollateralFormFields();
                
                \Log::info('LoanApplication: Existing collateral data loaded but form fields left empty for additional collateral', [
                    'existing_collateral_count' => count($this->existingCollateralData),
                    'existing_guarantor_count' => count($this->existingGuarantorData),
                    'form_fields_left_empty' => true,
                ]);
                
                // Don't pre-populate guarantor fields - leave them empty for user input
                // The existing guarantor information is displayed in the "Existing Collateral from Original Loan" section
                \Log::info('LoanApplication: Existing guarantor data loaded but form fields left empty for user input', [
                    'existing_guarantor_count' => count($this->existingGuarantorData),
                    'guarantor_form_fields_left_empty' => true,
                ]);
            }
        } else {
            \Log::info('LoanApplication: Not a Top-up, Restructuring, or Restructure loan, skipping guarantor/collateral load');
        }
    }
    
    /**
     * Load existing documents from the original loan for top-up and restructure loans
     * Similar to loadExistingGuarantorData() - stores existing documents separately
     */
    public function loadExistingDocuments()
    {
        \Log::info('LoanApplication: loadExistingDocuments called', [
            'loanType' => $this->loanType,
            'selectedLoanForTopUp' => $this->selectedLoanForTopUp,
            'selectedLoanForRestructure' => $this->selectedLoanForRestructure,
        ]);
        
        if (in_array($this->loanType, ['Top-up', 'Restructuring', 'Restructure'])) {
            $loanId = $this->selectedLoanForTopUp ?: $this->selectedLoanForRestructure;
            
            \Log::info('LoanApplication: Loading existing documents', [
                'loanId' => $loanId,
            ]);
            
            if ($loanId) {
                // Get existing documents from the loan
                $existingDocuments = DB::table('loan_images')
                    ->where('loan_id', $loanId)
                    ->get()
                    ->toArray();
                
                \Log::info('LoanApplication: Existing documents loaded', [
                    'document_count' => count($existingDocuments),
                    'documents' => $existingDocuments,
                ]);
                
                // Store existing documents in separate property (like existingGuarantorData)
                $this->existingDocumentsData = [];
                foreach ($existingDocuments as $doc) {
                    if (is_object($doc)) {
                        $this->existingDocumentsData[] = [
                            'filename' => $doc->filename ?? $doc->original_filename ?? 'Document',
                            'description' => $doc->description ?? 'Document from original loan',
                            'category' => $doc->category ?? 'general',
                            'size' => $doc->file_size ?? 0,
                            'path' => $doc->url ?? '',
                            'original_document_id' => $doc->id // Keep reference to original document
                        ];
                    }
                }
                
                // Don't reset uploaded documents - preserve any documents already uploaded
                // $this->uploadedDocuments = [];
                // $this->uploadedDocumentsCount = 0;
                
                // Instead, just update the count to reflect current state
                $this->uploadedDocumentsCount = count($this->uploadedDocuments);
                
                \Log::info('LoanApplication: Existing documents stored separately', [
                    'existing_documents_count' => count($this->existingDocumentsData),
                    'existing_documents' => $this->existingDocumentsData,
                    'uploaded_documents_count' => $this->uploadedDocumentsCount,
                ]);
                
                // Debug: Check if document files exist
                foreach ($this->existingDocumentsData as $index => $doc) {
                    if (isset($doc['original_document_id'])) {
                        $originalDocument = DB::table('loan_images')
                            ->where('id', $doc['original_document_id'])
                            ->first();
                        
                        if ($originalDocument) {
                            $filePath = storage_path('app/' . $originalDocument->url);
                            $fileExists = file_exists($filePath);

                            \Log::info('LoanApplication: Document file check', [
                                'document_id' => $doc['original_document_id'],
                                'filename' => $doc['filename'],
                                'file_path' => $originalDocument->url,
                                'full_path' => $filePath,
                                'file_exists' => $fileExists,
                                'file_size' => $fileExists ? filesize($filePath) : 'N/A'
                            ]);
                        }
                    }
                }
            }
        } else {
            \Log::info('LoanApplication: Not a Top-up, Restructuring, or Restructure loan, skipping document load');
        }
    }

    public function getAllMemberAccounts()
    {
        $clientNumber = $this->client_number;
        return DB::table('accounts')
            ->where('client_number', $clientNumber)
            //->where('status', 'active')
            ->get();
    }

    public function commitCollateral()
    {
        $this->validate([
            'selectedAccountId' => 'required',
            'collateralAmount' => 'required|numeric|min:0',
        ]);

        // Store the committed collateral values
        $this->committedCollateralAmount = (float)($this->collateralAmount ?? 0);
        $this->committedPhysicalCollateralValue = (float)($this->physicalCollateralValue ?? 0);
        
        $this->collateralCommitted = true;
        session()->flash('success', 'Collateral committed successfully.');
        // Recompute warnings after committing
        $this->recalculateStep3Warnings();
        
        \Log::info('LoanApplication: Collateral committed', [
            'account_collateral' => $this->committedCollateralAmount,
            'physical_collateral' => $this->committedPhysicalCollateralValue,
            'total_committed' => $this->committedCollateralAmount + $this->committedPhysicalCollateralValue,
        ]);
    }

    public function uncommitCollateral(): void
    {
        // Allow user to edit/change collateral after it was set
        $this->collateralCommitted = false;
        $this->committedCollateralAmount = 0;
        $this->committedPhysicalCollateralValue = 0;
        session()->flash('success', 'Collateral unlocked for editing. You can make changes.');
        // Recompute warnings immediately
        $this->recalculateStep3Warnings();
    }

    public function resetLoanSelection()
    {
        $this->selectedLoanForTopUp = '';
        $this->selectedLoanForRestructure = '';
        $this->selectedLoanForTakeover = '';
        $this->topUpAmount = 0;
        $this->restructureAmount = 0;
        $this->takeoverAmount = 0;
    }

    public function processTopUp()
    {
        if ($this->selectedLoanForTopUp) {
            $loan = DB::table('loans')->find($this->selectedLoanForTopUp);

            if ($loan) {
                // Get the outstanding balance from the accounts table
                $loanAccount = DB::table('accounts')->where('account_number', $loan->loan_account_number)->first();
                $this->topUpAmount = abs($loanAccount->balance ?? 0);
                
                // Auto-fill loan application form with selected loan data
                $this->autoFillFromSelectedLoan($loan);
                
                // loan_sub_product contains the sub_product_id string (e.g., 'MAE001'), not the numeric ID
                $loanSubProductCode = $loan->loan_sub_product;
                
                \Log::info('LoanApplication: Processing top-up loan', [
                    'loan_id' => $this->selectedLoanForTopUp,
                    'loan_sub_product_code' => $loanSubProductCode,
                    'auto_filled_data' => [
                        'loan_amount' => $this->loanAmount,
                        'loan_purpose' => $this->loanPurpose,
                        'repayment_period' => $this->repaymentPeriod,
                        'salary_take_home' => $this->salaryTakeHome,
                    ]
                ]);
                
                // Find the product by sub_product_id instead of numeric id
                $this->selectedProduct = DB::table('loan_sub_products')
                    ->where('sub_product_id', $loanSubProductCode)
                    ->first();
                    
                if ($this->selectedProduct) {
                    // Set the selectedProductId to the actual numeric ID for consistency
                    $this->selectedProductId = $this->selectedProduct->id;
                    
                    \Log::info('LoanApplication: Found and set loan product', [
                        'selectedProductId' => $this->selectedProductId,
                        'product_name' => $this->selectedProduct->sub_product_name,
                    ]);
                    
                    $this->updateSelectedProductProperties();
                    
                    // Force a refresh of the UI
                    $this->emit('$refresh');
                } else {
                    \Log::warning('LoanApplication: Could not find loan product', [
                        'sub_product_id' => $loanSubProductCode,
                    ]);
                }
                
                // Load existing guarantor and collateral data from the topped-up loan
                $this->loadExistingGuarantorData();
                
                // Recalculate loan details to include the penalty
                $this->calculateLoanDetails();
                
                // Show success message
                session()->flash('success', 'Top-up loan data loaded successfully. Amount: ' . number_format($this->topUpAmount, 2) . ' TZS');
            }
        }
    }

    /**
     * Reset collateral form fields to empty state
     * Used when loading existing collateral to ensure form fields are clean for additional collateral
     */
    public function resetCollateralFormFields()
    {
        // Reset collateral form fields
        $this->selectedAccountId = '';
        $this->collateralAmount = '';
        $this->showPhysicalCollateral = false;
        $this->physicalCollateralDescription = '';
        $this->physicalCollateralValue = '';
        $this->physicalCollateralLocation = '';
        $this->physicalCollateralOwnerName = '';
        $this->physicalCollateralOwnerContact = '';
        
        // Reset guarantor form fields (but preserve default guarantor type)
        $this->guarantorType = 'self_guarantee'; // Always default to self guarantee
        $this->selectedGuarantorId = '';
        $this->guarantorRelationship = '';
        
        // Don't reset collateralCommitted - preserve the committed state
        // This ensures the "Current Loan Collateral" section remains visible
        // when navigating between steps after collateral has been committed
        
        \Log::info('LoanApplication: Collateral form fields reset for additional collateral input (committed state preserved)');
    }

    /**
     * Reload existing collateral data when navigating to step 2
     * This ensures the existing collateral information is always available
     */
    public function reloadExistingCollateralData()
    {
        if (in_array($this->loanType, ['Top-up', 'Restructuring', 'Restructure'])) {
            $loanId = $this->selectedLoanForTopUp ?: $this->selectedLoanForRestructure;
            
            if ($loanId) {
                // Reload existing guarantor and collateral data
                $this->loadExistingGuarantorData();
                
                \Log::info('LoanApplication: Existing collateral data reloaded for step 2', [
                    'loan_id' => $loanId,
                    'loan_type' => $this->loanType,
                    'existing_collateral_count' => count($this->existingCollateralData ?? []),
                    'existing_guarantor_count' => count($this->existingGuarantorData ?? []),
                    'collateral_committed' => $this->collateralCommitted,
                ]);
            }
        }
    }

    /**
     * Reload existing documents data when navigating to step 4
     * This ensures the existing documents information is always available
     */
    public function reloadExistingDocumentsData()
    {
        if (in_array($this->loanType, ['Top-up', 'Restructuring', 'Restructure'])) {
            $loanId = $this->selectedLoanForTopUp ?: $this->selectedLoanForRestructure;
            
            if ($loanId) {
                // Reload existing documents data
                $this->loadExistingDocuments();
                
                \Log::info('LoanApplication: Existing documents data reloaded for step 4', [
                    'loan_id' => $loanId,
                    'loan_type' => $this->loanType,
                    'existing_documents_count' => count($this->existingDocumentsData ?? []),
                    'committed_documents_count' => count($this->committedDocumentsData ?? []),
                ]);
            }
        }
    }

    /**
     * Handle step changes to ensure data persistence
     * This method is called whenever the current step changes
     */
    public function updatedCurrentStep()
    {
        // When entering step 2, ensure guarantor type is set to default and reload existing collateral data if needed
        if ($this->currentStep === 2) {
            // Always ensure guarantor type is set to default when entering step 2
            if (empty($this->guarantorType)) {
                $this->guarantorType = 'self_guarantee';
            }
            
            // Reload existing collateral data if needed for Top-up/Restructure loans
            if (in_array($this->loanType, ['Top-up', 'Restructuring', 'Restructure'])) {
                $this->reloadExistingCollateralData();
            }
        }
        // When entering step 4, reload existing documents data if needed
        if ($this->currentStep === 4 && in_array($this->loanType, ['Top-up', 'Restructuring', 'Restructure'])) {
            $this->reloadExistingDocumentsData();
        }
    }

    /**
     * Auto-fill loan application form with data from selected loan
     * Used for both top-up and restructure loans
     */
    public function autoFillFromSelectedLoan($loan, $customAmount = null)
    {
        // Try to get purpose from assessment_data if not in main purpose field
        $assessmentData = $loan->assessment_data ? json_decode($loan->assessment_data, true) : [];
        $purposeFromAssessment = $assessmentData['loan_purpose'] ?? $assessmentData['purpose'] ?? null;
        
        // Auto-fill loan amount (use custom amount for restructure, original amount for top-up)
        if ($customAmount !== null) {
            $this->loanAmount = $customAmount; // For restructure, use current balance
        } else {
            $this->loanAmount = $loan->principle ?? 0; // For top-up, use original amount
        }
        
        // Auto-fill loan purpose
        if ($this->loanType === 'Top-up') {
            $this->loanPurpose = $loan->purpose ?? $purposeFromAssessment ?? 'Top-up of existing loan - ' . $loan->loan_account_number;
        } else {
            $this->loanPurpose = $loan->purpose ?? $purposeFromAssessment ?? 'Restructure of existing loan - ' . $loan->loan_account_number;
        }
        
        // Auto-fill repayment period
        $this->repaymentPeriod = $loan->tenure ?? $loan->approved_term ?? '';
        
        // Auto-fill salary/take home
        $this->salaryTakeHome = $loan->take_home ?? '';
        
        // Auto-fill collateral information if available
        if (!empty($loan->collateral_value)) {
            $this->collateralValue = $loan->collateral_value;
        }
        
        if (!empty($loan->collateral_type)) {
            $this->collateralType = $loan->collateral_type;
        }
        
        // Auto-fill employment information if available in assessment data
        if (!empty($assessmentData['client_info_data']['employment_info'])) {
            $employmentInfo = $assessmentData['client_info_data']['employment_info'];
            $this->employmentStatus = $employmentInfo['employment_status'] ?? '';
            $this->employerName = $employmentInfo['employer_name'] ?? '';
            $this->occupation = $employmentInfo['occupation'] ?? '';
            $this->businessName = $employmentInfo['business_name'] ?? '';
            $this->incomeSource = $employmentInfo['income_source'] ?? '';
            $this->educationLevel = $employmentInfo['education_level'] ?? '';
            $this->basicSalary = $employmentInfo['basic_salary'] ?? 0;
            $this->grossSalary = $employmentInfo['gross_salary'] ?? 0;
            $this->annualIncome = $employmentInfo['annual_income'] ?? 0;
        }
        
        // Auto-fill contact information if available in assessment data
        if (!empty($assessmentData['client_info_data']['contact_info'])) {
            $contactInfo = $assessmentData['client_info_data']['contact_info'];
            $this->phoneNumber = $contactInfo['phone_number'] ?? '';
            $this->mobilePhone = $contactInfo['mobile_phone'] ?? '';
            $this->email = $contactInfo['email'] ?? '';
            $this->address = $contactInfo['address'] ?? '';
            $this->city = $contactInfo['city'] ?? '';
            $this->region = $contactInfo['region'] ?? '';
            $this->district = $contactInfo['district'] ?? '';
            $this->ward = $contactInfo['ward'] ?? '';
        }
        
        // Auto-fill personal information if available in assessment data
        if (!empty($assessmentData['client_info_data']['basic_info'])) {
            $basicInfo = $assessmentData['client_info_data']['basic_info'];
            $this->fullName = $basicInfo['full_name'] ?? '';
            $this->firstName = $basicInfo['first_name'] ?? '';
            $this->middleName = $basicInfo['middle_name'] ?? '';
            $this->lastName = $basicInfo['last_name'] ?? '';
            $this->dateOfBirth = $basicInfo['date_of_birth'] ?? '';
            $this->gender = $basicInfo['gender'] ?? '';
            $this->maritalStatus = $basicInfo['marital_status'] ?? '';
            $this->nationality = $basicInfo['nationality'] ?? '';
        }
        
        \Log::info('LoanApplication: Auto-filled form data from selected loan', [
            'loan_id' => $loan->id,
            'loan_type' => $this->loanType,
            'auto_filled_fields' => [
                'loan_amount' => $this->loanAmount,
                'loan_purpose' => $this->loanPurpose,
                'repayment_period' => $this->repaymentPeriod,
                'salary_take_home' => $this->salaryTakeHome,
                'collateral_value' => $this->collateralValue ?? 'not set',
                'collateral_type' => $this->collateralType ?? 'not set',
                'employment_status' => $this->employmentStatus ?? 'not set',
                'employer_name' => $this->employerName ?? 'not set',
                'phone_number' => $this->phoneNumber ?? 'not set',
                'email' => $this->email ?? 'not set',
            ]
        ]);
    }

    /**
     * Calculate early settlement penalty for top-up loans
     * Applies penalty if the original loan is less than 6 months old
     */
    public function calculateEarlySettlementPenalty()
    {
        if ($this->loanType !== 'Top-up' || !$this->selectedLoanForTopUp || !$this->selectedProduct) {
            return 0;
        }

        try {
            // Get the original loan details
            $originalLoan = DB::table('loans')->where('id', $this->selectedLoanForTopUp)->first();
            
            if (!$originalLoan) {
                return 0;
            }

            // Calculate months difference between loan creation and today
            $loanCreatedAt = \Carbon\Carbon::parse($originalLoan->created_at);
            $today = \Carbon\Carbon::now();
            $monthsDifference = $loanCreatedAt->diffInMonths($today);

            // Apply penalty if loan is less than 6 months old
            if ($monthsDifference < 6) {
                $penaltyPercentage = $this->selectedProduct->penalty_value ?? 0;
                
                if ($penaltyPercentage > 0) {
                    // Use the top-up amount for penalty calculation
                    $penaltyAmount = ($this->topUpAmount * $penaltyPercentage) / 100;
                    
                    \Log::info('LoanApplication: Early settlement penalty calculated', [
                        'loan_id' => $this->selectedLoanForTopUp,
                        'loan_created_at' => $originalLoan->created_at,
                        'months_difference' => $monthsDifference,
                        'penalty_percentage' => $penaltyPercentage,
                        'top_up_amount' => $this->topUpAmount,
                        'penalty_amount' => $penaltyAmount,
                    ]);
                    
                    return $penaltyAmount;
                }
            }

            return 0;
        } catch (\Exception $e) {
            \Log::error('LoanApplication: Error calculating early settlement penalty', [
                'error' => $e->getMessage(),
                'loan_id' => $this->selectedLoanForTopUp,
            ]);
            return 0;
        }
    }

    /**
     * Get early settlement penalty information for display
     */
    public function getEarlySettlementPenaltyInfo()
    {
        if ($this->loanType !== 'Top-up' || !$this->selectedLoanForTopUp || !$this->selectedProduct) {
            return null;
        }

        try {
            $originalLoan = DB::table('loans')->where('id', $this->selectedLoanForTopUp)->first();
            
            if (!$originalLoan) {
                return null;
            }

            $loanCreatedAt = \Carbon\Carbon::parse($originalLoan->created_at);
            $today = \Carbon\Carbon::now();
            $monthsDifference = $loanCreatedAt->diffInMonths($today);
            $penaltyPercentage = $this->selectedProduct->penalty_value ?? 0;
            $penaltyAmount = $this->calculateEarlySettlementPenalty();

            return [
                'months_difference' => $monthsDifference,
                'penalty_percentage' => $penaltyPercentage,
                'penalty_amount' => $penaltyAmount,
                'loan_created_at' => $originalLoan->created_at,
                'applies' => $monthsDifference < 6 && $penaltyPercentage > 0,
            ];
        } catch (\Exception $e) {
            \Log::error('LoanApplication: Error getting early settlement penalty info', [
                'error' => $e->getMessage(),
                'loan_id' => $this->selectedLoanForTopUp,
            ]);
            return null;
        }
    }

    public function processRestructure()
    {
        if ($this->selectedLoanForRestructure) {
            $loan = DB::table('loans')->find($this->selectedLoanForRestructure);
            if ($loan) {
                // Get the current loan balance from accounts table
                // First, check if the account exists
                $account = DB::table('accounts')
                    ->where('account_number', trim($loan->loan_account_number))
                    ->first();

                \Log::info('LoanApplication: Looking up account balance for restructure', [
                    'loan_id' => $loan->id,
                    'loan_account_number' => $loan->loan_account_number,
                    'account_found' => $account ? 'yes' : 'no',
                    'account_balance' => $account->balance ?? 'N/A'
                ]);

                $currentBalance = $account->balance ?? 0;

                // If balance is 0 or not found, fall back to using principle
                if ($currentBalance == 0 && $loan->principle > 0) {
                    \Log::warning('LoanApplication: Account balance is 0, using loan principle instead', [
                        'loan_id' => $loan->id,
                        'principle' => $loan->principle
                    ]);
                    $currentBalance = $loan->principle;
                }

                $this->restructureAmount = $currentBalance;
                
                // Auto-fill loan application form with selected loan data
                $this->autoFillFromSelectedLoan($loan, $currentBalance);
                
                // loan_sub_product contains the sub_product_id string (e.g., 'MAE001'), not the numeric ID
                $loanSubProductCode = $loan->loan_sub_product;
                
                // Find the product by sub_product_id instead of numeric id
                $this->selectedProduct = DB::table('loan_sub_products')
                    ->where('sub_product_id', $loanSubProductCode)
                    ->first();
                    
                if ($this->selectedProduct) {
                    // Set the selectedProductId to the actual numeric ID for consistency
                    $this->selectedProductId = $this->selectedProduct->id;
                    $this->updateSelectedProductProperties();
                }
                
                // Load existing guarantor and collateral data from the restructured loan
                $this->loadExistingGuarantorData();
                
                // Load existing documents from the restructured loan
                $this->loadExistingDocuments();
                
                // Recalculate loan details with the new amount
                $this->calculateLoanDetails();
                
                // Disable loan amount field for restructure loans
                $this->isRestructureLoanAmountDisabled = true;
                
                // Log the restructure data loading
                \Log::info('LoanApplication: Restructure data loaded', [
                    'restructured_loan_id' => $this->selectedLoanForRestructure,
                    'original_loan_amount' => $loan->principle ?? 0,
                    'current_balance' => $currentBalance,
                    'loan_account_number' => $loan->loan_account_number,
                    'pre_populated_amount' => $this->loanAmount,
                    'pre_populated_purpose' => $this->loanPurpose,
                    'pre_populated_tenure' => $this->repaymentPeriod,
                    'pre_populated_salary' => $this->salaryTakeHome,
                    'loan_amount_disabled' => $this->isRestructureLoanAmountDisabled,
                    'debug_loan_data' => [
                        'purpose_column' => $loan->purpose ?? 'NULL',
                        'tenure_column' => $loan->tenure ?? 'NULL',
                        'take_home_column' => $loan->take_home ?? 'NULL',
                        'principle_column' => $loan->principle ?? 'NULL',
                        'assessment_data_purpose' => $purposeFromAssessment ?? 'NULL',
                        'has_assessment_data' => !empty($assessmentData)
                    ]
                ]);
                
                // Show success message
                session()->flash('success', 'Restructure loan data loaded successfully. Amount: ' . number_format($currentBalance, 2) . ' TZS');
            }
        }
    }
    
    /**
     * Manually reprocess restructure data (for when user wants to refresh the data)
     */
    public function reprocessRestructure()
    {
        if ($this->selectedLoanForRestructure) {
            $this->processRestructure();
            session()->flash('success', 'Restructure data has been refreshed.');
        }
    }
    
    /**
     * Manually refresh collateral data (for debugging and manual refresh)
     */
    public function refreshCollateralData()
    {
        $this->loadExistingGuarantorData();
        $this->loadExistingDocuments();
        session()->flash('success', 'Collateral and document data has been refreshed.');
    }
    
    /**
     * Manually refresh auto-fill data from selected loan
     */
    public function refreshAutoFillData()
    {
        if ($this->loanType === 'Top-up' && $this->selectedLoanForTopUp) {
            $loan = DB::table('loans')->find($this->selectedLoanForTopUp);
            if ($loan) {
                $this->autoFillFromSelectedLoan($loan);
                session()->flash('success', 'Top-up loan data has been refreshed.');
            }
        } elseif (($this->loanType === 'Restructure' || $this->loanType === 'Restructuring') && $this->selectedLoanForRestructure) {
            $loan = DB::table('loans')->find($this->selectedLoanForRestructure);
            if ($loan) {
                $currentBalance = DB::table('accounts')
                    ->where('account_number', $loan->loan_account_number)
                    ->value('balance') ?? 0;
                $this->autoFillFromSelectedLoan($loan, $currentBalance);
                session()->flash('success', 'Restructure loan data has been refreshed.');
            }
        }
    }
    
    /**
     * Download a document
     */
    public function downloadDocument($index)
    {
        if (isset($this->uploadedDocuments[$index])) {
            $document = $this->uploadedDocuments[$index];
            
            // Check if this is an existing document from the restructured loan
            if (isset($document['is_existing']) && $document['is_existing'] && isset($document['original_document_id'])) {
                // Get the original document from the database
                $originalDocument = DB::table('loan_images')
                    ->where('id', $document['original_document_id'])
                    ->first();
                
                if ($originalDocument && $originalDocument->url) {
                    $filePath = storage_path('app/' . $originalDocument->url);
                    
                    if (file_exists($filePath)) {
                        $filename = $originalDocument->original_filename ?? $originalDocument->filename;
                        return response()->download($filePath, $filename);
                    }
                }
            } else {
                // Handle newly uploaded documents
                if (isset($document['path']) && !empty($document['path'])) {
                    $filePath = storage_path('app/' . $document['path']);
                    
                    if (file_exists($filePath)) {
                        return response()->download($filePath, $document['filename']);
                    }
                }
            }
        }
        
        session()->flash('error', 'Document not found or could not be downloaded.');
    }
    
    /**
     * Download document using Livewire's download functionality
     */
    public function downloadDocumentFile($index)
    {
        if (isset($this->uploadedDocuments[$index])) {
            $document = $this->uploadedDocuments[$index];
            
            try {
                // Check if this is an existing document from the restructured loan
                if (isset($document['is_existing']) && $document['is_existing'] && isset($document['original_document_id'])) {
                    // Get the original document from the database
                    $originalDocument = DB::table('loan_images')
                        ->where('id', $document['original_document_id'])
                        ->first();
                    
                    if ($originalDocument && $originalDocument->url) {
                        $filePath = storage_path('app/' . $originalDocument->url);
                        
                        if (file_exists($filePath)) {
                            $filename = $originalDocument->original_filename ?? $originalDocument->filename;
                            return $this->download($filePath, $filename);
                        }
                    }
                } else {
                    // Handle newly uploaded documents
                    if (isset($document['path']) && !empty($document['path'])) {
                        $filePath = storage_path('app/' . $document['path']);
                        
                        if (file_exists($filePath)) {
                            return $this->download($filePath, $document['filename']);
                        }
                    }
                }
                
                session()->flash('error', 'Document file not found.');
            } catch (\Exception $e) {
                session()->flash('error', 'Error downloading document: ' . $e->getMessage());
            }
        } else {
            session()->flash('error', 'Document not found.');
        }
    }

    public function processTakeover()
    {
        if ($this->selectedLoanForTakeover) {
            $loan = DB::table('loans')->find($this->selectedLoanForTakeover);
            if ($loan) {
                $this->takeoverAmount = $loan->loan_amount ?? 0;
                
                // loan_sub_product contains the sub_product_id string (e.g., 'MAE001'), not the numeric ID
                $loanSubProductCode = $loan->loan_sub_product;
                
                // Find the product by sub_product_id instead of numeric id
                $this->selectedProduct = DB::table('loan_sub_products')
                    ->where('sub_product_id', $loanSubProductCode)
                    ->first();
                    
                if ($this->selectedProduct) {
                    // Set the selectedProductId to the actual numeric ID for consistency
                    $this->selectedProductId = $this->selectedProduct->id;
                    $this->updateSelectedProductProperties();
                }
            }
        }
    }

    public function uploadDocument()
    {
        $this->validate([
            'documentFile' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
            'documentDescription' => 'required|min:3',
            'documentCategory' => 'required|in:general,identity,financial,collateral,other',
        ]);

        $this->isUploading = true;
        $this->uploadProgress = 0;

        try {
            $uploadService = new LoanDocumentService();
            $uploadedFile = $uploadService->uploadDocument(
                $this->documentFile,
                $this->documentCategory,
                $this->documentDescription
            );

            if ($uploadedFile['success']) {
                $newDocument = [
                    'filename' => $uploadedFile['document']['filename'],
                    'description' => $this->documentDescription,
                    'category' => $this->documentCategory,
                    'size' => $uploadedFile['document']['size'],
                    'path' => $uploadedFile['document']['path']
                ];
                
                $this->uploadedDocuments[] = $newDocument;
                
                // Store in committed documents data for top-up/restructure loans
                if (in_array($this->loanType, ['Top-up', 'Restructuring', 'Restructure'])) {
                    $this->committedDocumentsData[] = $newDocument;
                }
                
                // Update counter
                $this->uploadedDocumentsCount = count($this->uploadedDocuments);
            } else {
                throw new \Exception($uploadedFile['error']);
            }

            $this->documentFile = null;
            $this->documentDescription = '';
            $this->documentCategory = 'general';
            $this->isUploading = false;
            $this->uploadProgress = 0;

            session()->flash('success', 'Document uploaded successfully.');
            $this->recalculateDocumentWarnings();
        } catch (\Exception $e) {
            $this->isUploading = false;
            $this->uploadProgress = 0;
            session()->flash('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }

    public function uploadExceptionApprovalLetter()
    {
        $this->validate([
            'exceptionApprovalFile' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ], [
            'exceptionApprovalFile.required' => 'Please select the approval letter file.',
            'exceptionApprovalFile.mimes' => 'Approval letter must be a PDF or image file.',
            'exceptionApprovalFile.max' => 'Approval letter must not exceed 10MB.',
        ]);

        try {
            $uploadService = new LoanDocumentService();
            $uploadedFile = $uploadService->uploadDocument(
                $this->exceptionApprovalFile,
                'exception_approval',
                'Exception approval letter from SACCOs'
            );

            if (!($uploadedFile['success'] ?? false)) {
                throw new \Exception($uploadedFile['error'] ?? 'Unknown error uploading approval letter');
            }

            $doc = [
                'filename' => $uploadedFile['document']['filename'],
                'description' => 'Exception approval letter',
                'category' => 'exception_approval',
                'size' => $uploadedFile['document']['size'],
                'path' => $uploadedFile['document']['path']
            ];
            $this->uploadedDocuments[] = $doc;
            $this->uploadedDocumentsCount = count($this->uploadedDocuments);

            $this->exceptionApprovalDocMeta = $doc;
            $this->exceptionApprovalUploaded = true;
            $this->exceptionApprovalFile = null;

            session()->flash('success', 'Approval letter uploaded successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to upload approval letter: ' . $e->getMessage());
        }
    }

    public function updatedExceptionApprovalFile($value)
    {
        if (!empty($this->exceptionApprovalFile)) {
            $this->uploadExceptionApprovalLetter();
        }
    }

    public function removeDocument($index)
    {
        if (isset($this->uploadedDocuments[$index])) {
            $document = $this->uploadedDocuments[$index];
            
            // Delete file from storage
            if (Storage::exists($document['path'])) {
                Storage::delete($document['path']);
            }
            
            unset($this->uploadedDocuments[$index]);
            $this->uploadedDocuments = array_values($this->uploadedDocuments);
            
            // Update counter
            $this->uploadedDocumentsCount = count($this->uploadedDocuments);
            
            session()->flash('success', 'Document removed successfully.');
            $this->recalculateDocumentWarnings();
        }
    }

    /**
     * Download existing document from original loan
     */
    public function downloadExistingDocument($index)
    {
        if (isset($this->existingDocumentsData[$index])) {
            $document = $this->existingDocumentsData[$index];
            
            if (isset($document['original_document_id'])) {
                // Get the original document from database
                $originalDocument = DB::table('loan_images')
                    ->where('id', $document['original_document_id'])
                    ->first();
                
                if ($originalDocument && Storage::exists($originalDocument->url)) {
                    return Storage::download($originalDocument->url, $document['filename']);
                }
            }
            
            session()->flash('error', 'Document file not found.');
        }
        
        session()->flash('error', 'Document not found.');
    }

    /**
     * Remove existing document from original loan (only from display, not from database)
     */
    public function removeExistingDocument($index)
    {
        if (isset($this->existingDocumentsData[$index])) {
            $document = $this->existingDocumentsData[$index];
            
            // Remove from existing documents array (only from display)
            unset($this->existingDocumentsData[$index]);
            $this->existingDocumentsData = array_values($this->existingDocumentsData);
            
            \Log::info('LoanApplication: Existing document removed from display', [
                'document_index' => $index,
                'document_filename' => $document['filename'],
                'remaining_existing_documents' => count($this->existingDocumentsData),
            ]);
            
            session()->flash('success', 'Existing document removed from display. (Original file preserved)');
        } else {
            session()->flash('error', 'Document not found.');
        }
    }

    // Livewire updated hooks for Step 3 to keep warnings fresh
    public function updatedGuarantorType($value) { 
        $this->recalculateStep3Warnings(); 
        $this->ensureExistingCollateralDataPersists();
    }
    public function updatedSelectedGuarantorId($value) { 
        $this->recalculateStep3Warnings(); 
        $this->ensureExistingCollateralDataPersists();
    }
    public function updatedGuarantorRelationship($value) { 
        $this->recalculateStep3Warnings(); 
        $this->ensureExistingCollateralDataPersists();
    }
    public function updatedSelectedAccountId($value) { 
        $this->recalculateStep3Warnings(); 
        $this->ensureExistingCollateralDataPersists();
    }
    public function updatedCollateralAmount($value) { 
        $this->recalculateStep3Warnings(); 
        $this->ensureExistingCollateralDataPersists();
    }
    public function updatedPhysicalCollateralValue($value) { 
        $this->recalculateStep3Warnings(); 
        $this->ensureExistingCollateralDataPersists();
    }
    
    /**
     * Ensure existing collateral data persists across Livewire updates
     * This prevents the existing collateral from being lost when form fields are updated
     */
    private function ensureExistingCollateralDataPersists(): void
    {
        // Only reload if we're on step 2 and have a loan selected for top-up/restructure
        if ($this->currentStep === 2 && in_array($this->loanType, ['Top-up', 'Restructuring', 'Restructure'])) {
            $loanId = $this->selectedLoanForTopUp ?: $this->selectedLoanForRestructure;
            
            // Only reload if the data is empty but we have a loan selected
            if ($loanId && (empty($this->existingCollateralData) || empty($this->existingGuarantorData))) {
                \Log::info('LoanApplication: Reloading existing collateral data to prevent loss', [
                    'loan_id' => $loanId,
                    'loan_type' => $this->loanType,
                ]);
                
                $this->loadExistingGuarantorData();
            }
        }
    }

    private function addWarning(string $key, string $message): void
    {
        if (!isset($this->warnings[$key])) {
            $this->warnings[$key] = [];
        }
        if (!in_array($message, $this->warnings[$key])) {
            $this->warnings[$key][] = $message;
        }
    }

    private function clearWarning(string $key): void
    {
        unset($this->warnings[$key]);
    }

    private function recalculateStep1Warnings(): void
    {
        foreach (['loanType','selectedProductId','selectedLoanForTopUp','selectedLoanForRestructure','selectedLoanForTakeover','loanAmount','loanPurpose','repaymentPeriod','salaryTakeHome'] as $key) {
            $this->clearWarning($key);
        }

        if ($this->loanType === 'Top-up' && empty($this->selectedLoanForTopUp)) {
            $this->addWarning('selectedLoanForTopUp', '');
        }
        if (in_array($this->loanType, ['Restructure','Restructuring']) && empty($this->selectedLoanForRestructure)) {
            $this->addWarning('selectedLoanForRestructure', '');
        }
        if ($this->loanType === 'Takeover' && empty($this->selectedLoanForTakeover)) {
            $this->addWarning('selectedLoanForTakeover', '');
        }

        if (empty($this->selectedProductId)) {
            $this->addWarning('selectedProductId', 'Pick a product.');
        }

        if (!empty($this->loanPurpose) && strlen(trim($this->loanPurpose)) < 10) {
            //$this->addWarning('loanPurpose', 'Add a bit more detail.');
        }

        if (!empty($this->repaymentPeriod)) {
            if ($this->selectedProductMinTerm && $this->repaymentPeriod < $this->selectedProductMinTerm) {
                //$this->addWarning('repaymentPeriod', 'Below product minimum ' . (int)$this->selectedProductMinTerm . ' months.');
            }
            if ($this->selectedProductMaxTerm && $this->repaymentPeriod > $this->selectedProductMaxTerm) {
                //$this->addWarning('repaymentPeriod', 'Above product maximum ' . (int)$this->selectedProductMaxTerm . ' months.');
            }
            if ($this->monthsToRetirement !== null && $this->repaymentPeriod > $this->monthsToRetirement) {
                //$this->addWarning('repaymentPeriod', 'Tenure extends beyond retirement.');
            }
        }

        if ($this->loanAmount !== '' && $this->loanAmount !== null) {
            $amount = (float)$this->loanAmount;
            if ($this->selectedProductMinAmount && $amount < $this->selectedProductMinAmount) {
                //$this->addWarning('loanAmount', 'Below product minimum ' . number_format($this->selectedProductMinAmount, 0) . '.');
            }
            if ($this->selectedProductMaxAmount && $amount > $this->selectedProductMaxAmount) {
                //$this->addWarning('loanAmount', 'Above product maximum ' . number_format($this->selectedProductMaxAmount, 0) . '.');
            }
            // Dynamic savings multiplier from product parameters (fallback to 3x if not set)
            $savingsMultiplier = (float)($this->selectedProduct->loan_multiplier ?? 3);
            if ($this->totalSavings > 0 && $savingsMultiplier > 0) {
                $limit = (float)$this->totalSavings * $savingsMultiplier;
                if ($amount > $limit) {
                    $shortfall = $amount - $limit;
                    $this->addWarning('loanAmount', 'Exceeds ' . rtrim(rtrim(number_format($savingsMultiplier, 2), '0'), '.') . 'x savings; add collateral.');
                    $this->addWarning('selectedAccountId', 'Shortfall ' . number_format($shortfall, 2) . '. Provide collateral.');
                }
            }
            if ($this->eligibleLoanAmount && $amount > $this->eligibleLoanAmount) {
                $this->addWarning('loanAmount', 'Requested exceeds eligible amount ' . number_format($this->eligibleLoanAmount, 0) . '.');
            }
        }

        if (!empty($this->salaryTakeHome)) {
            // Recalculate to ensure monthlyInstallment is current
            if ($this->selectedProduct && $this->loanAmount && $this->repaymentPeriod) {
                $this->calculateLoanDetails();
            }
            $takeHome = (float)$this->salaryTakeHome;
            if ($takeHome > 0 && !empty($this->monthlyInstallment)) {
                $dti = ($this->monthlyInstallment / $takeHome) * 100;
                // Dynamic DTI threshold from product (fallback 70%)
                $dtiThreshold = (float)($this->selectedProduct->score_limit ?? 70);
                if ($dti > $dtiThreshold) {
                    $this->addWarning('salaryTakeHome', 'Installment exceeds ' . rtrim(rtrim(number_format($dtiThreshold, 2), '0'), '.') . '% of take-home.');
                    $this->addWarning('loanAmount', 'Installment exceeds ' . rtrim(rtrim(number_format($dtiThreshold, 2), '0'), '.') . '% of take-home.');
                }
            }
        }
    }

    private function recalculateStep3Warnings(): void
    {
        foreach (['guarantorType','selectedGuarantorId','guarantorRelationship','selectedAccountId','collateralAmount','physicalCollateralValue'] as $key) {
            $this->clearWarning($key);
        }

        if (empty($this->guarantorType)) {
            $this->addWarning('guarantorType', 'Choose self or third-party.');
        }
        if ($this->guarantorType === 'third_party_guarantee') {
            if (empty($this->selectedGuarantorId)) {
                $this->addWarning('selectedGuarantorId', 'Pick a guarantor.');
            }
            if (!empty($this->guarantorRelationship) && strlen(trim($this->guarantorRelationship)) < 2) {
                $this->addWarning('guarantorRelationship', 'Add relationship.');
            }
        }

        if (empty($this->selectedAccountId)) {
            $this->addWarning('selectedAccountId', 'Select an account for collateral.');
        }

        if (!empty($this->selectedAccountId) && !empty($this->collateralAmount)) {
            $account = DB::table('accounts')->where('id', $this->selectedAccountId)->first();
            if ($account) {
                $balance = (float)($account->available_balance ?? $account->balance ?? 0);
                if ((float)$this->collateralAmount > $balance) {
                    $this->addWarning('collateralAmount', 'Exceeds account balance ' . number_format($balance, 2) . '.');
                }
            }
        }

        if ($this->totalSavings > 0 && $this->loanAmount) {
            $limit = $this->totalSavings * 3;
            if ((float)$this->loanAmount > $limit) {
                $shortfall = (float)$this->loanAmount - $limit;
                $this->addWarning('collateralAmount', 'Shortfall ' . number_format($shortfall, 2) . '. Provide collateral.');
            }
        }

        if (!empty($this->physicalCollateralDescription) || !empty($this->physicalCollateralLocation) || !empty($this->physicalCollateralOwnerName)) {
            if (empty($this->physicalCollateralValue) || (float)$this->physicalCollateralValue <= 0) {
                $this->addWarning('physicalCollateralValue', 'Add valuation.');
            }
        }
    }

    private function recalculateDocumentWarnings(): void
    {
        foreach (['documentFile','documentCategory','documentDescription','requiredDocuments'] as $key) {
            $this->clearWarning($key);
        }

        if (!empty($this->documentFile)) {
            if (empty($this->documentCategory)) {
                $this->addWarning('documentCategory', 'Choose a category.');
            }
            if (empty($this->documentDescription) || strlen(trim($this->documentDescription)) < 3) {
                $this->addWarning('documentDescription', 'Add description.');
            }
        }

        // Removed ID document and Member photo breach tracking as requested
        // These checks are not needed for Step 3 breach summary
    }

    public function submitApplication()
    {
        // Gate submission: require exception approval letter if there are any warnings
        $this->recalculateAllWarnings();
        if ($this->getHasWarningsProperty() && !$this->exceptionApprovalUploaded) {
            session()->flash('error', 'Breaches detected. Please upload an exception approval letter to proceed.');
            return;
        }
        $this->showOtpModal();
    }
    
    /**
     * Test method to save example loan data with documents
     * This bypasses OTP and directly saves test data
     */
    public function testSaveLoanWithDocuments()
    {
        \Log::info('LoanApplication: testSaveLoanWithDocuments called');
        
        try {
            DB::beginTransaction();
            
            // Set up test data if not already set
            if (empty($this->client_number)) {
                $this->client_number = '10003'; // Example client number
            }
            
            if (empty($this->selectedProductId)) {
                // Get first available product
                $product = DB::table('loan_products')->first();
                $this->selectedProductId = $product ? $product->id : 1;
            }
            
            if (empty($this->loanAmount)) {
                $this->loanAmount = 1000000; // 1 million test amount
            }
            
            if (empty($this->repaymentPeriod)) {
                $this->repaymentPeriod = 12; // 12 months
            }
            
            // Add test documents to the uploadedDocuments array
            $this->uploadedDocuments = [
                [
                    'filename' => 'test_identity_doc_' . time() . '.pdf',
                    'description' => 'Test Identity Document',
                    'category' => 'identity',
                    'size' => 512000, // 500KB
                    'path' => 'loan_applications/documents/identity/test_' . time() . '.pdf'
                ],
                [
                    'filename' => 'test_financial_doc_' . time() . '.pdf',
                    'description' => 'Test Financial Statement',
                    'category' => 'financial',
                    'size' => 768000, // 750KB
                    'path' => 'loan_applications/documents/financial/test_' . time() . '.pdf'
                ],
                [
                    'filename' => 'test_collateral_doc_' . time() . '.jpg',
                    'description' => 'Test Collateral Document',
                    'category' => 'collateral',
                    'size' => 1024000, // 1MB
                    'path' => 'loan_applications/documents/collateral/test_' . time() . '.jpg'
                ]
            ];
            
            $this->uploadedDocumentsCount = count($this->uploadedDocuments);
            
            \Log::info('LoanApplication: Test data prepared', [
                'client_number' => $this->client_number,
                'loan_amount' => $this->loanAmount,
                'documents_count' => $this->uploadedDocumentsCount
            ]);
            
            // Generate a test loan ID
            $loanId = 'TEST_' . date('YmdHis') . '_' . rand(1000, 9999);
            
            // Create the loan record with correct column names
            $loanData = [
                'loan_id' => $loanId,
                'loan_account_number' => 'TEST_LAC_' . rand(100000, 999999),
                'client_number' => $this->client_number,
                'loan_sub_product' => $this->selectedProductId, // Using loan_sub_product instead of loan_product_id
                'principle' => $this->loanAmount,
                'interest' => $this->loanAmount * 0.15, // 15% test interest
                'tenure' => $this->repaymentPeriod, // Using tenure instead of loan_period
                'loan_type' => 'New', // loan_type instead of payment_mode
                'loan_type_2' => 'New',
                'status' => 'TEST',
                'loan_status' => 'TEST',
                'days_in_arrears' => 0,
                'arrears_in_amount' => 0,
                'source' => 'TEST',
                'approval_stage' => 'Inputter',
                'approval_stage_role_name' => 'Loan Officer',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $loanDbId = DB::table('loans')->insertGetId($loanData);
            
            \Log::info('LoanApplication: Test loan created', [
                'loan_id' => $loanId,
                'db_id' => $loanDbId
            ]);
            
            // Save documents to loan_images table
            if (!empty($this->uploadedDocuments)) {
                \Log::info('LoanApplication: Saving test documents to loan_images table', [
                    'loan_id' => $loanId,
                    'documents_count' => count($this->uploadedDocuments),
                ]);
                
                foreach ($this->uploadedDocuments as $document) {
                    $docData = [
                        'loan_id' => $loanId,
                        'category' => $document['category'],
                        'filename' => $document['filename'],
                        'url' => $document['path'],
                        'document_descriptions' => $document['description'],
                        'document_category' => $document['category'],
                        'file_size' => $document['size'],
                        'mime_type' => $this->getMimeType($document['filename']),
                        'original_name' => $document['filename'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    $docId = DB::table('loan_images')->insertGetId($docData);
                    
                    \Log::info('LoanApplication: Test document saved', [
                        'doc_id' => $docId,
                        'filename' => $document['filename']
                    ]);
                }
                
                \Log::info('LoanApplication: All test documents saved successfully');
            }
            
            DB::commit();
            
            session()->flash('success', 'Test loan with documents created successfully! Loan ID: ' . $loanId);
            
            \Log::info('LoanApplication: Test save completed successfully', [
                'loan_id' => $loanId,
                'documents_saved' => count($this->uploadedDocuments)
            ]);
            
            // Return the loan ID for verification
            return $loanId;
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('LoanApplication: Error in testSaveLoanWithDocuments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Test save failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processLoanApplication()
    {
        \Log::info('LoanApplication: Starting application submission', [
            'member_id' => Auth::id(),
            'client_number' => $this->client_number,
            'selected_product_id' => $this->selectedProductId,
            'loan_amount' => $this->loanAmount,
            'repayment_period' => $this->repaymentPeriod,
        ]);

        try {
            // Custom validation for uploaded documents since it's a protected property
            if (empty($this->uploadedDocuments) || count($this->uploadedDocuments) < 1) {
                \Log::warning('LoanApplication: No documents uploaded, proceeding without documents for testing', [
                    'client_number' => $this->client_number,
                    'documents_count' => count($this->uploadedDocuments ?? [])
                ]);
                // Temporarily commented out for testing
                // $this->addError('uploadedDocuments', 'Please upload at least one supporting document.');
                // session()->flash('error', 'Please upload at least one supporting document before submitting.');
                // 
                // // Close OTP modal and show error
                // $this->showOtpVerification = false;
                // $this->emit('$refresh');
                // return;
            }

            \Log::info('LoanApplication: Documents validated', [
                'documents_count' => count($this->uploadedDocuments)
            ]);
            
            \Log::info('LoanApplication: Starting database transaction');
            DB::beginTransaction();
            
            // Ensure selectedProduct is loaded
            if (!$this->selectedProduct) {
                \Log::info('LoanApplication: Loading selected product', [
                    'product_id' => $this->selectedProductId
                ]);
                $this->selectedProduct = DB::table('loan_sub_products')->where('id', $this->selectedProductId)->first();
                if (!$this->selectedProduct) {
                    throw new \Exception('Selected loan product not found');
                }
            }
            
            // Generate loan ID
            $loanId = 'LN' . date('Ymd') . rand(1000, 9999);
            $loanAccountNumber = ($this->selectedProduct->prefix ?? 'LN') . date('Y') . rand(10000, 99999);
            
            \Log::info('LoanApplication: Generated loan identifiers', [
                'loan_id' => $loanId,
                'loan_account_number' => $loanAccountNumber,
                'product_prefix' => $this->selectedProduct->prefix ?? 'LN',
            ]);
            
            // Calculate interest (simple calculation)
            $interestRate = floatval($this->selectedProduct->interest_value ?? 15);
            $interest = ($this->loanAmount * $interestRate * $this->repaymentPeriod) / (100 * 12);
            
            \Log::info('LoanApplication: Calculated loan interest', [
                'interest_rate' => $interestRate,
                'loan_amount' => $this->loanAmount,
                'repayment_period' => $this->repaymentPeriod,
                'calculated_interest' => $interest,
            ]);
            
            // Handle different loan types
            $loanTypeData = [];
            $loanType = 'New';
            
            if ($this->loanType === 'Top-up' && $this->selectedLoanForTopUp) {
                $selectedLoan = collect($this->existingLoansForSelection)->firstWhere('id', $this->selectedLoanForTopUp);
                $loanType = 'TOPUP';
                
                // Calculate early settlement penalty
                $earlySettlementPenalty = $this->calculateEarlySettlementPenalty();
                
                $loanTypeData = [
                    'top_up_loan_id' => $this->selectedLoanForTopUp,
                    'top_up_amount' => $this->topUpAmount,
                    'top_up_penalty_amount' => $earlySettlementPenalty,
                    'top_up_loan_account' => $selectedLoan['loan_id'] ?? '',
                    'top_up_processed_at' => now(),
                    'top_up_processed_by' => Auth::id(),
                ];
                \Log::info('LoanApplication: Processing top-up loan', $loanTypeData);
            } elseif (in_array($this->loanType, ['Restructure', 'Restructuring']) && $this->selectedLoanForRestructure) {
                $selectedLoan = collect($this->existingLoansForSelection)->firstWhere('id', $this->selectedLoanForRestructure);
                $loanType = 'RESTRUCTURED';
                $loanTypeData = [
                    'restructure_loan_id' => $this->selectedLoanForRestructure,
                    'restructure_amount' => $this->restructureAmount,
                    'restructure_loan_account' => $selectedLoan['loan_id'] ?? '',
                    'restructure_processed_at' => now(),
                    'restructure_processed_by' => Auth::id(),
                ];
                \Log::info('LoanApplication: Processing restructure loan', $loanTypeData);
            } elseif ($this->loanType === 'Takeover' && $this->selectedLoanForTakeover) {
                $selectedLoan = collect($this->existingLoansForSelection)->firstWhere('id', $this->selectedLoanForTakeover);
                $loanType = 'TAKEOVER';
                $loanTypeData = [
                    'takeover_loan_id' => $this->selectedLoanForTakeover,
                    'takeover_amount' => $this->takeoverAmount,
                    'takeover_loan_account' => $selectedLoan['loan_id'] ?? '',
                    'takeover_original_owner_name' => $selectedLoan['owner_name'] ?? '',
                    'takeover_original_owner_client_number' => $selectedLoan['owner_client_number'] ?? '',
                    'takeover_processed_at' => now(),
                    'takeover_processed_by' => Auth::id(),
                ];
                \Log::info('LoanApplication: Processing takeover loan', $loanTypeData);
            }
            
            // Create loan application
            \Log::info('LoanApplication: Creating loan record in database');

            // Determine pending status based on presence of warnings (exceptions)
            $pendingStatus = $this->hasWarnings ? 'PENDING-WITH-EXCEPTIONS' : 'PENDING';
            
            // Set initial stage based on status
            // If loan has exceptions, it starts at Exception stage (Stage 0)
            // Otherwise, it starts at Inputter stage (Stage 1)
            if ($pendingStatus === 'PENDING-WITH-EXCEPTIONS') {
                $initialApprovalStage = 'Exception'; // Stage 0 for exception loans
                $initialApprovalStageRoleNames = 'Loan Officer'; // Same role clears exceptions
            } else {
                $initialApprovalStage = 'Inputter'; // Stage 1 for normal loans
                $initialApprovalStageRoleNames = 'Loan Officer'; // Initial role name
            }

            // Prepare exception tracking data
            $exceptionData = [];
            if ($this->hasWarnings) {
                $exceptionData = [
                    'has_exceptions' => true,
                    'exception_tracking_id' => 'EXC_' . $loanId . '_' . time(),
                    'status' => 'PENDING-WITH-EXCEPTIONS'
                ];
                
                \Log::info('LoanApplication: Loan has exceptions, preparing exception data', [
                    'exception_data' => $exceptionData,
                    'has_warnings' => $this->hasWarnings
                ]);
            }

            $loanData = array_merge([
                'loan_id' => $loanId,
                'loan_account_number' => $loanAccountNumber,
                'client_number' => $this->client_number,
                'loan_sub_product' => $this->selectedProduct->sub_product_id ?? $this->selectedProductId,
                'principle' => $this->loanAmount,
                'interest' => $interest,
                'tenure' => $this->repaymentPeriod,
                'status' => $pendingStatus,
                'loan_status' => $pendingStatus,
                'loan_type' => $loanType,
                'loan_type_2' => $this->loanType, // textual type used by listing components (original user selection)
                'approved_loan_value' => $this->loanAmount,
                'approved_term' => $this->repaymentPeriod,
                'take_home' => $this->salaryTakeHome,
                'days_in_arrears' => 0,
                'arrears_in_amount' => 0,
                'source' => 'OFFICE',
                'approval_stage' => $initialApprovalStage,
                'approval_stage_role_name' => $initialApprovalStageRoleNames,
                'created_at' => now(),
                'updated_at' => now(),
            ], $loanTypeData, $exceptionData);
            
            // Debug: Log the loan data before insert
            \Log::info('LoanApplication: Attempting to insert loan data', [
                'loan_data' => $loanData,
                'loan_id_length' => strlen($loanId),
                'loan_account_number_length' => strlen($loanAccountNumber),
                'loan_type_system' => $loanType,
                'loan_type_2_user_selection' => $this->loanType,
            ]);
            
            try {
                $loanDbId = DB::table('loans')->insertGetId($loanData);
            } catch (\Exception $e) {
                \Log::error('LoanApplication: Database insert failed', [
                    'error' => $e->getMessage(),
                    'loan_data' => $loanData,
                    'sql' => DB::getQueryLog() ? end(DB::getQueryLog()) : 'No query log available'
                ]);
                throw $e;
            }
            
            // Create a loan object for consistency
            $loan = (object) array_merge($loanData, ['id' => $loanDbId]);
            
            \Log::info('LoanApplication: Loan record created successfully', [
                'loan_id' => $loan->loan_id,
                'database_id' => $loan->id,
                'created_at' => $loan->created_at,
            ]);
            
            // Prepare additional loan data for JSON storage
            $additionalData = [
                'salary_take_home' => $this->salaryTakeHome,
                'loan_purpose' => $this->loanPurpose,
                'guarantor_type' => $this->guarantorType,
                'guarantor_id' => $this->selectedGuarantorId ?? null,
                'guarantor_relationship' => $this->guarantorRelationship ?? null,
                'collateral_account_id' => $this->selectedAccountId ?? null,
                'collateral_amount' => $this->collateralAmount ?? 0,
                'physical_collateral' => [
                    'description' => $this->physicalCollateralDescription,
                    'value' => $this->physicalCollateralValue,
                    'location' => $this->physicalCollateralLocation,
                    'owner_name' => $this->physicalCollateralOwnerName,
                    'owner_contact' => $this->physicalCollateralOwnerContact
                ],
                'has_warnings' => (bool) $this->hasWarnings,
                'exception_approval' => $this->exceptionApprovalUploaded ? ($this->exceptionApprovalDocMeta ?? []) : null,
                'credit_score_data' => [
                    'score' => $this->creditScoreValue,
                    'risk' => $this->creditScoreRisk,
                    'grade' => $this->creditScoreGrade,
                    'source' => $this->creditScoreSource,
                    'probability_of_default' => $this->creditScoreProbabilityOfDefault,
                    'crb_data' => [
                        'past_due_amount' => $this->creditScorePastDueAmount,
                        'inquiries_last_month' => $this->creditScoreInquiriesLastMonth
                    ]
                ],
                'loan_type' => $loanType,
                'loan_type_data' => $loanTypeData,
                'monthly_installment' => $this->monthlyInstallment,
                'total_amount_payable' => $this->totalAmountPayable,
                'early_settlement_penalty' => [
                    'applies' => $this->loanType === 'Top-up' ? ($this->getEarlySettlementPenaltyInfo()['applies'] ?? false) : false,
                    'amount' => $this->loanType === 'Top-up' ? $this->calculateEarlySettlementPenalty() : 0,
                    'percentage' => $this->loanType === 'Top-up' ? ($this->selectedProduct->penalty_value ?? 0) : 0,
                    'original_loan_age_months' => $this->loanType === 'Top-up' ? ($this->getEarlySettlementPenaltyInfo()['months_difference'] ?? 0) : 0,
                    'original_loan_created_at' => $this->loanType === 'Top-up' ? ($this->getEarlySettlementPenaltyInfo()['loan_created_at'] ?? null) : null,
                ],
            ];
            
            \Log::info('LoanApplication: Saving additional loan data as JSON', [
                'loan_id' => $loan->loan_id,
                'data_keys' => array_keys($additionalData),
                'documents_count' => count($this->uploadedDocuments),
            ]);
            
            // Save additional loan data in JSON format
            DB::table('loans')->where('id', $loan->id)->update([
                'assessment_data' => json_encode($additionalData),
            ]);
            
            \Log::info('LoanApplication: Additional data saved successfully');
            
            // Save guarantor information to loan_guarantors table
            if (!empty($this->guarantorType)) {
                \Log::info('LoanApplication: Saving guarantor information to loan_guarantors table', [
                    'loan_id' => $loan->loan_id,
                    'guarantor_type' => $this->guarantorType,
                    'selected_guarantor_id' => $this->selectedGuarantorId ?: 'self_guarantee',
                ]);
                
                // Calculate total guaranteed amount (loan amount)
                $totalGuaranteedAmount = $this->loanAmount;
                
                // Determine guarantor member ID (should be client ID, not client_number)
                $guarantorMemberId = null;
                if (!empty($this->selectedGuarantorId)) {
                    // For third-party guarantor, get the client ID
                    $guarantorMemberId = (int)$this->selectedGuarantorId;
                } else {
                    // Self-guarantee - find or create client record
                    $clientNumber = $this->client_number;
                    
                    // Check if client exists in clients table
                    $client = DB::table('clients')->where('client_number', $clientNumber)->first();
                    
                    if (!$client) {
                        // Create client record if it doesn't exist
                        \Log::info('LoanApplication: Creating client record for self-guarantee', [
                            'client_number' => $clientNumber,
                            'user_id' => Auth::id(),
                        ]);
                        
                        $guarantorMemberId = DB::table('clients')->insertGetId([
                            'client_number' => $clientNumber,
                            'first_name' => Auth::user()->name ?? 'Unknown',
                            'last_name' => '',
                            'email' => Auth::user()->email ?? '',
                            'phone_number' => Auth::user()->phone ?? '',
                            'client_status' => 'ACTIVE',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $guarantorMemberId = $client->id;
                    }
                }
                
                // Ensure we have a valid guarantor member ID
                if (empty($guarantorMemberId)) {
                    throw new \Exception('Unable to determine guarantor member ID for loan application');
                }
                
                \Log::info('LoanApplication: Guarantor member ID determined', [
                    'selected_guarantor_id' => $this->selectedGuarantorId,
                    'current_user_client_number' => $this->client_number,
                    'final_guarantor_member_id' => $guarantorMemberId,
                    'client_exists' => isset($client),
                ]);
                
                $guarantorData = [
                    'loan_id' => $loanDbId, // Use the numeric ID, not the string loan_id
                    'guarantor_member_id' => $guarantorMemberId,
                    'guarantor_type' => $this->guarantorType === 'self' ? 'self_guarantee' : 'third_party_guarantee',
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
                
                \Log::info('LoanApplication: Guarantor information saved successfully');
                
                // Save collateral information if available
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
                
                \Log::info('LoanApplication: Collateral information saved successfully');
            }
            
            // Save documents to loan_images table
            if (!empty($this->uploadedDocuments)) {
                \Log::info('LoanApplication: Saving documents to loan_images table', [
                    'loan_id' => $loan->loan_id,
                    'documents_count' => count($this->uploadedDocuments),
                ]);
                
                foreach ($this->uploadedDocuments as $document) {
                    DB::table('loan_images')->insert([
                        'loan_id' => $loan->loan_id,
                        'category' => $document['category'],
                        'filename' => $document['filename'],
                        'url' => $document['path'],
                        'document_descriptions' => $document['description'],
                        'document_category' => $document['category'],
                        'file_size' => $document['size'],
                        'mime_type' => $this->getMimeType($document['filename']),
                        'original_name' => $document['filename'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                \Log::info('LoanApplication: Documents saved to loan_images table successfully');
            }
            
            DB::commit();
            
            \Log::info('LoanApplication: Database transaction committed successfully', [
                'loan_id' => $loan->loan_id,
                'submission_time' => now(),
            ]);
            
            session()->flash('success', 'Your loan application has been submitted successfully! Application ID: ' . $loanId);
            
            \Log::info('LoanApplication: Success message set, resetting form');
            
            // Reset form
            $this->resetForm();
            
            // Ensure success message is visible
            $this->showSuccessMessage = true;
            
            // Emit event to refresh the component
            $this->emit('loanApplicationSubmitted', $loanId);
            $this->emit('$refresh');
            
            \Log::info('LoanApplication: Form reset successfully');
            
            \Log::info('LoanApplication: Application submission completed successfully', [
                'loan_id' => $loan->loan_id,
                'member_id' => Auth::id(),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('LoanApplication: Error during application submission', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'member_id' => Auth::id(),
                'client_number' => $this->client_number,
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            DB::rollback();
            session()->flash('error', 'Failed to submit application: ' . $e->getMessage());
            
            // Close OTP modal and show error
            $this->showOtpVerification = false;
            $this->emit('$refresh');
        }
    }

    public function resetForm()
    {
        $this->currentStep = 1;
        $this->loanType = 'New';
        $this->selectedProductId = '';
        $this->selectedProduct = null;
        $this->loanProducts = [];
        $this->hasLoanProducts = false;
        $this->existingLoansForSelection = [];
        $this->selectedProductName = '';
        $this->selectedProductInterestRate = 0;
        $this->selectedProductManagementFee = 0;
        $this->selectedProductMajangaFee = 0;
        $this->selectedProductMinTerm = 1;
        $this->selectedProductMaxTerm = 60;
        $this->loanAmount = '';
        $this->loanPurpose = '';
        $this->repaymentPeriod = '';
        $this->salaryTakeHome = '';
        $this->guarantorType = 'self_guarantee';
        $this->selectedGuarantorId = '';
        $this->guarantorRelationship = '';
        $this->selectedAccountId = '';
        $this->collateralAmount = 0;
        $this->collateralCommitted = false;
        $this->uploadedDocuments = [];
        $this->uploadedDocumentsCount = 0;
        $this->step1PolicyViolations = [];
        $this->step3SavingsViolations = [];
        $this->creditScoreValue = 0;
        $this->creditScoreRisk = 'UNKNOWN';
        $this->creditScoreGrade = 'XX';
        $this->creditScoreSource = 'Internal';
        $this->creditScoreProbabilityOfDefault = 0;
        $this->creditScorePastDueAmount = 0;
        $this->creditScoreInquiriesLastMonth = 0;
        $this->physicalCollateralDescription = '';
        $this->physicalCollateralValue = '';
        $this->physicalCollateralLocation = '';
        $this->physicalCollateralOwnerName = '';
        $this->physicalCollateralOwnerContact = '';
        $this->monthlyInstallment = 0;
        $this->totalAmountPayable = 0;
        $this->eligibleLoanAmount = 0;
        
        // Reset client number validation
        $this->showClientNumberModal = false;
        $this->inputClientNumber = '';
        $this->clientNumberError = '';
        
        // Reset OTP properties
        $this->showOtpVerification = false;
        $this->otpCode = '';
        $this->generatedOtp = '';
        $this->otpSentVia = '';
        session()->forget(['loan_otp', 'loan_otp_expires']);
    }

    public function hideSuccessMessage()
    {
        $this->showSuccessMessage = false;
    }
    
    public function getStep1PolicyViolationsProperty()
    {
        return $this->step1PolicyViolations;
    }

    public function getSelectedProductProperty()
    {
        if (!$this->selectedProduct && $this->selectedProductId) {
            $this->selectedProduct = DB::table('loan_sub_products')->find($this->selectedProductId);
        }
        return $this->selectedProduct;
    }
    
    public function getStep3SavingsViolationsProperty()
    {
        return $this->step3SavingsViolations;
    }
    
    public function getLoanProductsProperty()
    {
        return $this->loanProducts;
    }
    
    public function getExistingLoansForSelectionProperty()
    {
        return $this->existingLoansForSelection;
    }
    

    public function debugCurrentState()
    {
        session()->flash('debug', 'Current step: ' . $this->currentStep . ', Loan type: ' . $this->loanType . ', Product: ' . $this->selectedProductId);
    }

    // Convenience: recompute all warnings at once
    private function recalculateAllWarnings(): void
    {
        $this->recalculateStep1Warnings();
        $this->recalculateStep3Warnings();
        $this->recalculateDocumentWarnings();
    }

    // Convenience: whether any warnings exist
    public function getHasWarningsProperty(): bool
    {
        foreach (($this->warnings ?? []) as $arr) {
            if (is_array($arr) && count($arr) > 0) {
                return true;
            }
        }
        return false;
    }

    // Flatten and deduplicate warnings across all fields for clean UI display
    public function getWarningsUniqueProperty(): array
    {
        $unique = [];
        foreach (($this->warnings ?? []) as $arr) {
            if (is_array($arr)) {
                foreach ($arr as $msg) {
                    if (is_string($msg) && $msg !== '') {
                        $unique[$msg] = true;
                    }
                }
            }
        }
        return array_keys($unique);
    }

    public function sendApproval($id, $msg, $code)
    {
        $user = auth()->user();
        $institution = TeamUser::where('user_id', $user->id)->value('institution');

        approvals::create([
            'institution' => $institution,
            'process_name' => 'loanApplication',
            'process_description' => $msg,
            'approval_process_description' => 'has approved a loan application',
            'process_code' => $code,
            'process_id' => $id,
            'process_status' => 'PENDING',
            'user_id' => $user->id,
            'team_id' => ""
        ]);
    }

    public function showOtpModal()
    {

        $this->showOtpVerification = true;
        //dd($this->showOtpVerification);
        $this->requestOtp();
    }

    public function requestOtp()
    {
        try {
            // Get client details from clients table
            $client = DB::table('clients')
                ->where('client_number', $this->client_number)
                ->first();
            
            if (!$client) {
                session()->flash('otp_error', 'Client details not found. Please verify your client number.');
                return;
            }
            
            // Generate 6-digit OTP
            $this->generatedOtp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store OTP in session with expiry time (5 minutes)
            session(['loan_otp' => $this->generatedOtp, 'loan_otp_expires' => now()->addMinutes(5)]);
            
            // Get contact details from client
            $phoneNumber = $client->mobile_phone ?? $client->contact_number ?? $client->phone_number ?? null;
            $email = $client->email ?? null;
            
            \Log::info('LoanApplication: OTP generated for loan submission', [
                'client_number' => $this->client_number,
                'phone' => $phoneNumber,
                'email' => $email,
                'otp' => $this->generatedOtp,
                'expires_at' => now()->addMinutes(5)
            ]);
            
            $smsSent = false;
            $emailSent = false;

            // Send OTP via SMS if phone number is available
            if ($phoneNumber) {
                try {
                    $smsService = app(\App\Services\SmsService::class);
                    $message = "Your loan application OTP is: {$this->generatedOtp}. Valid for 5 minutes. Do not share this code.";

                    // Call SMS service with additional parameters for transactional SMS
                    $smsResult = $smsService->send($phoneNumber, $message, $client, [
                        'smsType' => 'TRANSACTIONAL',
                        'serviceName' => 'LOAN_APPLICATION',
                        'language' => 'English'
                    ]);

                    if ($smsResult['success'] ?? false) {
                        $smsSent = true;
                        \Log::info('LoanApplication: OTP sent via SMS successfully', [
                            'client_number' => $this->client_number,
                            'phone' => $phoneNumber
                        ]);
                    }
                } catch (\Exception $smsException) {
                    \Log::warning('LoanApplication: SMS sending failed', [
                        'client_number' => $this->client_number,
                        'phone' => $phoneNumber,
                        'error' => $smsException->getMessage()
                    ]);
                }
            } else {
                \Log::warning('LoanApplication: No phone number available for SMS OTP', [
                    'client_number' => $this->client_number
                ]);
            }

            // Send OTP via email (independently of SMS status)
            if ($email) {
                try {
                    // Send OTP via email
                    \Mail::send('emails.loan-otp', [
                        'otp' => $this->generatedOtp,
                        'clientName' => $client->full_name ?? $client->present_surname ?? 'Valued Member',
                        'expiryMinutes' => 5
                    ], function ($message) use ($email, $client) {
                        $message->to($email)
                            ->subject('Loan Application Verification Code');
                    });

                    $emailSent = true;
                    \Log::info('LoanApplication: OTP sent via email successfully', [
                        'client_number' => $this->client_number,
                        'email' => $email
                    ]);
                } catch (\Exception $emailException) {
                    \Log::error('LoanApplication: Email sending failed', [
                        'client_number' => $this->client_number,
                        'email' => $email,
                        'error' => $emailException->getMessage()
                    ]);
                }
            } else {
                \Log::warning('LoanApplication: No email address available for email OTP', [
                    'client_number' => $this->client_number
                ]);
            }
            
            // Determine how OTP was sent and set appropriate message
            if ($smsSent && $emailSent) {
                $this->otpSentVia = 'phone number and email';
                session()->flash('otp_success', 'Verification code sent to your phone and email.');
            } elseif ($smsSent) {
                $this->otpSentVia = 'phone number (' . substr($phoneNumber, 0, 3) . '****' . substr($phoneNumber, -3) . ')';
                session()->flash('otp_success', 'Verification code sent to your phone number.');
            } elseif ($emailSent) {
                $this->otpSentVia = 'email (' . substr($email, 0, 3) . '****@' . substr(strrchr($email, "@"), 1) . ')';
                session()->flash('otp_success', 'Verification code sent to your email.');
            } else {
                // Neither SMS nor email was sent
                \Log::critical('LoanApplication: Failed to send OTP via any channel', [
                    'client_number' => $this->client_number,
                    'phone' => $phoneNumber,
                    'email' => $email,
                    'otp' => $this->generatedOtp // Log OTP for manual intervention
                ]);
                
                session()->flash('otp_error', 'Unable to send verification code. Please contact support.');
                $this->otpSentVia = 'contact support for assistance';
            }
            
        } catch (\Exception $e) {
            \Log::error('LoanApplication: Error generating OTP', [
                'error' => $e->getMessage(),
                'client_number' => $this->client_number
            ]);
            session()->flash('otp_error', 'Failed to generate OTP. Please try again.');
        }
    }

    public function verifyOtp()
    {

        //dd($this->otpCode);
        \Log::info('LoanApplication: verifyOtp method called', [
            'client_number' => $this->client_number,
            'otp_entered' => $this->otpCode,
            'otp_length' => strlen($this->otpCode)
        ]);
        
        // Force Livewire to update
        $this->emit('otpVerificationStarted');
        
        try {
            // Validate OTP input
            if (empty($this->otpCode) || strlen($this->otpCode) !== 6) {
                \Log::warning('LoanApplication: Invalid OTP format', [
                    'client_number' => $this->client_number,
                    'otp_length' => strlen($this->otpCode)
                ]);
                session()->flash('otp_error', 'Please enter a valid 6-digit OTP.');
                $this->emit('$refresh');
                return;
            }
            
            // Get stored OTP from session
            $storedOtp = session('loan_otp');
            $otpExpires = session('loan_otp_expires');
            
            \Log::info('LoanApplication: Session OTP data', [
                'stored_otp' => $storedOtp,
                'otp_expires' => $otpExpires,
                'current_time' => now()->toDateTimeString()
            ]);
            
            if (!$storedOtp || !$otpExpires) {
                \Log::warning('LoanApplication: OTP not found in session');
                session()->flash('otp_error', 'OTP not found. Please request a new one.');
                $this->emit('$refresh');
                return;
            }
            
            // Check if OTP has expired
            if (now()->greaterThan($otpExpires)) {
                \Log::warning('LoanApplication: OTP has expired', [
                    'client_number' => $this->client_number,
                    'otp_expires' => $otpExpires,
                    'current_time' => now()->toDateTimeString()
                ]);
                session()->forget(['loan_otp', 'loan_otp_expires']);
                session()->flash('otp_error', 'OTP has expired. Please request a new one.');
                $this->emit('$refresh'); // Force Livewire to refresh the component
                return;
            }
            
            // Verify OTP
            if ($this->otpCode !== $storedOtp) {
                session()->flash('otp_error', 'Invalid OTP. Please try again.');
                \Log::warning('LoanApplication: Invalid OTP entered', [
                    'client_number' => $this->client_number,
                    'entered_otp' => $this->otpCode
                ]);
                $this->emit('$refresh');
                return;
            }
            
            // OTP verified successfully
            \Log::info('LoanApplication: OTP verification passed, processing loan application', [
                'client_number' => $this->client_number,
                'verified_at' => now()->toDateTimeString()
            ]);
            
            session()->forget(['loan_otp', 'loan_otp_expires']);
            session()->flash('otp_success', 'OTP verified successfully!');
            
            // Close OTP modal and process loan application
            $this->closeOtpModal();
            $this->processLoanApplication();
            
            \Log::info('LoanApplication: OTP verified successfully', [
                'client_number' => $this->client_number,
                'verified_at' => now()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('LoanApplication: Error verifying OTP', [
                'error' => $e->getMessage(),
                'client_number' => $this->client_number
            ]);
            session()->flash('otp_error', 'Failed to verify OTP. Please try again.');
        }
    }

    public function closeOtpModal()
    {
        $this->showOtpVerification = false;
        $this->otpCode = '';
        $this->generatedOtp = '';
        session()->forget(['loan_otp', 'loan_otp_expires']);
    }
    
    public function updatedOtpCode($value)
    {
        \Log::info('LoanApplication: OTP code updated', [
            'value' => $value,
            'length' => strlen($value)
        ]);
    }

    public function resendOtp()
    {
        try {
            // Clear any existing OTP input
            $this->otpCode = '';
            
            // Request new OTP
            $this->requestOtp();
            
            session()->flash('otp_success', 'A new verification code has been sent.');
            
        } catch (\Exception $e) {
            \Log::error('LoanApplication: Error resending OTP', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            session()->flash('otp_error', 'Failed to resend OTP. Please try again.');
        }
    }

    protected function getMimeType($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'txt' => 'text/plain',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Calculate total physical collateral value from step 2
     */
    public function getTotalPhysicalCollateralValue()
    {
        $totalPhysicalCollateral = 0;
        
        // Get physical collateral value from current form
        if (!empty($this->physicalCollateralValue)) {
            $totalPhysicalCollateral += (float)$this->physicalCollateralValue;
        }
        
        // Get physical collateral value from existing collateral data (for restructure loans)
        if (!empty($this->existingCollateralData)) {
            foreach ($this->existingCollateralData as $collateral) {
                if (is_object($collateral) && $collateral->collateral_type === 'physical') {
                    $totalPhysicalCollateral += floatval($collateral->physical_collateral_value ?? $collateral->collateral_amount);
                }
            }
        }
        
        return $totalPhysicalCollateral;
    }
    
    /**
     * Calculate LTV (Loan-to-Value) ratio
     */
    public function calculateLTV()
    {
        $physicalCollateralValue = $this->getTotalPhysicalCollateralValue();
        $loanAmount = (float)($this->loanAmount ?? 0);
        
        // Check if we have valid values for calculation
        if ($physicalCollateralValue <= 0 || $loanAmount <= 0) {
            return [
                'ratio' => 0,
                'limit' => 0,
                'is_exceeded' => false,
                'status' => 'N/A'
            ];
        }
        
        // Calculate LTV ratio: (Loan Amount / Collateral Value) × 100
        $ltvRatio = $physicalCollateralValue > 0 ? round(($loanAmount / $physicalCollateralValue) * 100, 2) : 0;
        $ltvLimit = (float)($this->selectedProduct->ltv ?? 70);
        $isExceeded = $ltvRatio > $ltvLimit;
        
        return [
            'ratio' => $ltvRatio,
            'limit' => $ltvLimit,
            'is_exceeded' => $isExceeded,
            'status' => $isExceeded ? 'EXCEEDED' : 'WITHIN_LIMIT'
        ];
    }
    
    /**
     * Get LTV data for display in policy violations
     */
    public function getLTVData()
    {
        // Only proceed if product has LTV limit and it's greater than zero
        if (!$this->selectedProduct || !$this->selectedProduct->ltv || $this->selectedProduct->ltv <= 0) {
            return null;
        }
        
        $ltvData = $this->calculateLTV();
        
        // If calculation returned N/A status, return null
        if ($ltvData['status'] === 'N/A') {
            return null;
        }

        //'description' => "{$ltvData['ratio']}% (Limit: {$ltvData['limit']}%)"
        
        return [
            'ratio' => $ltvData['ratio'],
            'limit' => $ltvData['limit'],
            'is_exceeded' => $ltvData['is_exceeded'],
            'status' => $ltvData['status'],
            'description' => "{$ltvData['limit']}%"
        ];
    }
    
    /**
     * Computed property for LTV data
     */
    public function getLTVDataProperty()
    {
        return $this->getLTVData();
    }
    
    /**
     * Computed property for total physical collateral value
     */
    public function getTotalPhysicalCollateralValueProperty()
    {
        return $this->getTotalPhysicalCollateralValue();
    }
    
    /**
     * Calculate months on book for top-up loans
     */
    public function getMonthsOnBook()
    {
        if ($this->loanType !== 'Top-up' || !$this->selectedLoanForTopUp) {
            return 0;
        }
        
        try {
            $originalLoan = DB::table('loans')->where('id', $this->selectedLoanForTopUp)->first();
            if (!$originalLoan) {
                return 0;
            }
            
            $originalLoanDate = Carbon::parse($originalLoan->created_at);
            $currentDate = Carbon::now();
            
            return $originalLoanDate->diffInMonths($currentDate);
        } catch (\Exception $e) {
            Log::error('Error calculating months on book: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get minimum months on book requirement
     */
    public function getMinMobRequirement()
    {
        return (int)($this->selectedProduct->min_mob ?? 6);
    }
    
    /**
     * Calculate savings multiplier breach details
     * Uses total collateral amount (financial + physical) from step 2
     */
    public function getSavingsMultiplierBreachData()
    {
        if (!$this->selectedProduct || !$this->loanAmount) {
            return null;
        }
        
        // Calculate total collateral amount (financial + physical) from step 2
        $totalCollateralAmount = (float)($this->collateralAmount ?? 0) + (float)($this->physicalCollateralValue ?? 0);
        
        if ($totalCollateralAmount <= 0) {
            return null;
        }
        
        $requestedAmount = (float)$this->loanAmount;
        $savingsMultiplier = (float)($this->selectedProduct->loan_multiplier ?? 3);
        $collateralLimit = $totalCollateralAmount * $savingsMultiplier;
        
        if ($requestedAmount <= $collateralLimit) {
            return null;
        }
        
        return [
            'limit' => $collateralLimit,
            'actual' => $requestedAmount,
            'multiplier' => $savingsMultiplier,
            'total_collateral' => $totalCollateralAmount,
            'financial_collateral' => (float)($this->collateralAmount ?? 0),
            'physical_collateral' => (float)($this->physicalCollateralValue ?? 0)
        ];
    }
    
    /**
     * Calculate Debt Service Ratio (DSR) breach details
     * DSR = (Monthly Installment / Monthly Income) * 100
     * Should not exceed 67% of monthly income
     */
    public function getDSRBreachData()
    {
        if (!$this->loanAmount || !$this->repaymentPeriod || !$this->salaryTakeHome || $this->salaryTakeHome <= 0) {
            return null;
        }
        
        $monthlyInstallment = $this->monthlyInstallment ?? 0;
        $monthlyIncome = (float)$this->salaryTakeHome;
        $dsrLimit = 67; // 67% limit
        
        if ($monthlyInstallment <= 0 || $monthlyIncome <= 0) {
            return null;
        }
        
        $currentDSR = ($monthlyInstallment / $monthlyIncome) * 100;
        
        if ($currentDSR <= $dsrLimit) {
            return null;
        }
        
        return [
            'limit' => $dsrLimit,
            'actual' => round($currentDSR, 2),
            'monthly_installment' => $monthlyInstallment,
            'monthly_income' => $monthlyIncome
        ];
    }
    
    /**
     * Check if there are any policy breaches
     */
    public function hasAnyBreaches()
    {
        // Check step1 policy violations
        if (!empty($this->step1PolicyViolations)) {
            return true;
        }
        
        // Check if we have product and loan amount
        if (!$this->selectedProduct || !$this->loanAmount) {
            return false;
        }
        
        $requestedAmount = (float)$this->loanAmount;
        $requestedTenure = (int)$this->repaymentPeriod;
        
        // Check loan amount breaches
        $maxAmount = (float)($this->selectedProduct->principle_max_value ?? 0);
        $minAmount = (float)($this->selectedProduct->principle_min_value ?? 0);
        
        if ($maxAmount > 0 && $requestedAmount > $maxAmount) {
            return true;
        }
        
        if ($minAmount > 0 && $requestedAmount < $minAmount) {
            return true;
        }
        
        // Check tenure breaches
        $maxTenure = (int)($this->selectedProduct->max_term ?? 0);
        $minTenure = (int)($this->selectedProduct->min_term ?? 0);
        
        if ($maxTenure > 0 && $requestedTenure > $maxTenure) {
            return true;
        }
        
        if ($minTenure > 0 && $requestedTenure < $minTenure) {
            return true;
        }
        
        // Check LTV breaches
        if ($this->selectedProduct->ltv > 0 && $this->ltvData && $this->ltvData['is_exceeded']) {
            return true;
        }
        
        // Check savings multiplier breaches (only if no LTV)
        if ((!$this->selectedProduct->ltv || $this->selectedProduct->ltv <= 0) && $this->getSavingsMultiplierBreachData()) {
            return true;
        }
        
        // Check DSR breaches (only if no LTV)
        if ((!$this->selectedProduct->ltv || $this->selectedProduct->ltv <= 0) && $this->getDSRBreachData()) {
            return true;
        }
        
        // Check MoB breaches for top-up loans
        if ($this->loanType === 'Top-up' && $this->selectedLoanForTopUp) {
            $monthsOnBook = $this->getMonthsOnBook();
            $minMob = $this->getMinMobRequirement();
            
            if ($minMob > 0 && $monthsOnBook < $minMob) {
                return true;
            }
        }
        
        return false;
    }

    public function render()
    {
        // Check if client number is valid before rendering
        if (!$this->isClientNumberValid()) {
            $this->showClientNumberModal = true;
        }
        
        // Ensure existing collateral data is available on every render for Step 2
        if ($this->currentStep === 2 && in_array($this->loanType, ['Top-up', 'Restructuring', 'Restructure'])) {
            $loanId = $this->selectedLoanForTopUp ?: $this->selectedLoanForRestructure;
            
            // If we have a loan selected but no existing data loaded, reload it
            if ($loanId && empty($this->existingCollateralData) && empty($this->existingGuarantorData)) {
                \Log::info('LoanApplication: Reloading existing collateral data on render', [
                    'loan_id' => $loanId,
                    'currentStep' => $this->currentStep,
                ]);
                $this->loadExistingGuarantorData();
            }
        }
        
        return view('livewire.dashboard.loan-application');
    }
}
