<?php

namespace App\Http\Livewire\Loans;


use App\Models\Charges;
use App\Models\Employee;
use App\Models\general_ledger;
use App\Models\Loan_sub_products;
use App\Models\loans_schedules;
use App\Models\loans_summary;
use App\Models\LoansModel;
use App\Models\MembersModel;
use App\Services\LoanScheduleServiceVersionTwo;
use App\Services\CreditScoreService;
use App\Services\ClientInformationServicex;
use App\Services\ProductParametersServicex;
use App\Services\ExceptionService;
use App\Services\NbcLoansServicex;
use App\Services\SettlementService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Assessment extends Component
{
    use WithFileUploads;

    public $photo, $futureInterest = false, $collateral_type, $collateral_description, $daily_sales, $loan, $collateral_value, $loan_sub_product;
    public $principle = 0, $member, $guarantor, $disbursement_account, $collection_account_loan_interest;
    public $collection_account_loan_principle, $collection_account_loan_charges, $collection_account_loan_penalties;
    public $principle_min_value, $principle_max_value, $min_term, $max_term, $interest_value;
    public $principle_grace_period, $interest_grace_period, $amortization_method;
    public $days_in_a_month = 30, $loan_id, $loan_account_number, $member_number, $topUpBoolena, $new_principle;
    public $interest = 0, $business_licence_number, $business_tin_number, $business_inventory, $cash_at_hand;
    public $cost_of_goods_sold, $operating_expenses, $monthly_taxes, $other_expenses, $monthly_sales;
    public $gross_profit, $table = [], $tablefooter = [], $recommended_tenure, $recommended_installment;
    public $totalAmount, $recommended = true,$monthlyInstallmentValue, $business_age, $bank1 = 123456, $available_funds;
    public $interest_method, $loan_is_settled=false, $approved_term = 12, $approved_loan_value = 0, $future_interests, $futureInsteresAmount, $valueAmmount, $net_profit, $status, $products;
    public $coverage;
    public $idx;
    public $sub_product_id;
    public $product, $account;
    public $charges;

    public $institution1;
    public $institutionAmount;

    public $institution2;
    public $institutionAmount2;
    public $daysBetweenx = 0;


    ///////////////
    public $non_permanent_income_non_taxable = 0;
    public $non_permanent_income_taxable = 0;
    public $take_home = 0;
    public $totalInstallment = 0;
    public $tenure = 12;
    public $max_loan, $selectedContracts=[];
    public $x ;
    public $isPhysicalCollateral = false;
    public $account1,$insurance_list=[];
    public $account2;

    public $selectedLoan;
    public $ClosedLoanBalance;

    public $listeners = [
        'refreshAssessment' => '$refresh',
        'checkAssessmentCompletion' => 'checkAssessmentCompletion'
    ];

    public $errorMessage;

    public $age, $name, $monthsToRetirement, $savings;

    public $creditScoreData;
    protected $creditScoreService;
    public $clientInfoData;
    protected $clientInformationService;
    public $productParamsData;
    protected $productParametersService;
    public $exceptionData;
    protected $exceptionService;
    public $nbcLoansData;
    protected $nbcLoansService;
    public $settlementData = [
        'total_amount' => 0,
        'settlements' => [],
        'count' => 0,
        'has_settlements' => false
    ];
    
    // Breakdown properties for detailed charge/insurance display
    public $chargesBreakdown = [];
    public $insuranceBreakdown = [];
    protected $settlementService;
    
    // Button states and action tracking
    public $actionCompleted = false;
    public $actionType = '';
    public $actionMessage = '';
    public $showActionButtons = true;
    public $isProcessing = false;
    
    // Settlement form properties
    public $settlementForm = [
        'institution' => '',
        'account' => '',
        'amount' => 0
    ];
    public $editingSettlementId = null;
    public $showSettlementForm = false;

    public $topUpAmount;

    // Deduction calculation properties
    public $totalCharges = 0;
    public $totalInsurance = 0;
    public $firstInstallmentInterestAmount = 0;

    // Loan schedule properties
    public $schedule = [];
    public $footer = [];

    // Assessment data property
    public $assessmentData = [];

    public function toggleAmount($amount, $key)
    {
        try {
            Log::info('=== TOGGLE AMOUNT START ===', [
                'key' => $key,
                'amount' => $amount,
                'current_selectedContracts' => $this->selectedContracts,
                'current_totalAmount' => $this->totalAmount ?? 0
            ]);

            // Check if the current contract is selected or not
            if (in_array($key, $this->selectedContracts)) {
                Log::info('Removing settlement from selection', ['key' => $key]);
                
                // If selected, remove it and decrement the total amount
                $this->selectedContracts = array_diff($this->selectedContracts, [$key]);

                DB::table('settled_loans')->where('loan_array_id', $key)
                ->where('loan_id',session('currentloanID'))
                ->update([
                    'is_selected'=>false
                ]);

                Log::info('Settlement removed from database', [
                    'key' => $key,
                    'new_selectedContracts' => $this->selectedContracts
                ]);
            } else {
                Log::info('Adding settlement to selection', ['key' => $key]);
                
                // If not selected, add it and increment the total amount
                $this->selectedContracts[] = $key;
                DB::table('settled_loans')->where('loan_array_id', $key)
                ->where('loan_id',session('currentloanID'))
                ->update([
                    'is_selected'=>true
                ]);

                Log::info('Settlement added to database', [
                    'key' => $key,
                    'new_selectedContracts' => $this->selectedContracts
                ]);
            }

            // Recalculate total amount based on selected settlements
            Log::info('Calling recalculateTotalAmount');
            $this->recalculateTotalAmount();
            
            // Recalculate deduction amounts to update the UI
            Log::info('Calling calculateDeductionAmounts');
            $this->calculateDeductionAmounts();
            
            Log::info('=== TOGGLE AMOUNT END ===', [
                'final_selectedContracts' => $this->selectedContracts,
                'final_totalAmount' => $this->totalAmount ?? 0,
                'final_settlementData_total' => $this->settlementData['total_amount'] ?? 0
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error toggling amount: ' . $e->getMessage(), [
                'key' => $key,
                'amount' => $amount,
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorMessage = 'Error updating selection. Please try again.';
        }
    }

    /**
     * Recalculate total amount based on selected settlements
     */
    private function recalculateTotalAmount()
    {
        try {
            Log::info('=== RECALCULATE TOTAL AMOUNT START ===');
            
            // Get all selected settlements from database
            $selectedSettlements = DB::table('settled_loans')
                ->where('loan_id', session('currentloanID'))
                ->where('is_selected', true)
                ->get();

            Log::info('Selected settlements for total calculation', [
                'count' => $selectedSettlements->count(),
                'settlements' => $selectedSettlements->toArray()
            ]);

            $totalAmount = 0;
            foreach ($selectedSettlements as $settlement) {
                $amount = (float)($settlement->amount ?? 0);
                $totalAmount += $amount;
                Log::info('Adding settlement amount', [
                    'settlement_id' => $settlement->loan_array_id,
                    'amount' => $amount,
                    'running_total' => $totalAmount
                ]);
            }

            Log::info('Setting totalAmount', [
                'old_totalAmount' => $this->totalAmount ?? 0,
                'new_totalAmount' => $totalAmount
            ]);

            $this->totalAmount = $totalAmount;
            
            // Update settlement data to reflect the new total
            if (isset($this->settlementData)) {
                Log::info('Updating settlementData', [
                    'old_settlementData_total' => $this->settlementData['total_amount'] ?? 0,
                    'new_settlementData_total' => $totalAmount
                ]);
                $this->settlementData['total_amount'] = $totalAmount;
            } else {
                Log::warning('settlementData is not set');
            }
            
            Log::info('=== RECALCULATE TOTAL AMOUNT END ===', [
                'final_totalAmount' => $this->totalAmount,
                'final_settlementData_total' => $this->settlementData['total_amount'] ?? 0
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error recalculating total amount: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->totalAmount = 0;
        }
    }

    public function calculateLoanProductCharge($product_id, $principle)
    {
        try {
            if (!$product_id || !$principle) {
                return 0;
            }
            
            // Use the new loan_product_charges table
            $charges = DB::table('loan_product_charges')
                ->where('loan_product_id', $product_id)
                ->where('type', 'charge')
                ->get();

            $totalAmount = 0;
            $breakdown = [];

            foreach ($charges as $charge) {
                $baseAmount = 0;
                $chargeAmount = 0;
                $capApplied = null;

                // Calculate the charge amount based on its type
                if ($charge->value_type === "percentage") {
                    $baseAmount = (float)($principle);
                    $chargeAmount = ($baseAmount * ((float)($charge->value) / 100));
                    
                    // Apply min cap if set (only for charges, not insurance)
                    if (!empty($charge->min_cap) && $chargeAmount < (float)$charge->min_cap) {
                        $chargeAmount = (float)$charge->min_cap;
                        $capApplied = 'Min cap';
                    }
                    
                    // Apply max cap if set (only for charges, not insurance)
                    if (!empty($charge->max_cap) && $chargeAmount > (float)$charge->max_cap) {
                        $chargeAmount = (float)$charge->max_cap;
                        $capApplied = 'Max cap';
                    }
                } else {
                    // Fixed charge (one-time, not multiplied by tenure)
                    $chargeAmount = (float)$charge->value;
                }

                // Add to breakdown
                $breakdown[] = [
                    'name' => $charge->name,
                    'value_type' => $charge->value_type,
                    'value' => (float)$charge->value,
                    'base_amount' => $baseAmount,
                    'amount' => $chargeAmount,
                    'min_cap' => $charge->min_cap ? (float)$charge->min_cap : null,
                    'max_cap' => $charge->max_cap ? (float)$charge->max_cap : null,
                    'cap_applied' => $capApplied
                ];

                // Accumulate the total amount
                $totalAmount += $chargeAmount;
            }

            // Store breakdown for the view
            $this->chargesBreakdown = $breakdown;

            return $totalAmount;
        } catch (\Exception $e) {
            Log::error('Error calculating loan product charge: ' . $e->getMessage());
            return 0;
        }
    }




    public function calculateLoanProductInsurance($product_id,$principle){
        try {
            if (!$product_id || !$principle) {
                return 0;
            }
            
            // Use the new loan_product_charges table
            $insurances = DB::table('loan_product_charges')
                ->where('loan_product_id', $product_id)
                ->where('type', 'insurance')
                ->get();

            $totalAmount = 0;
            $breakdown = [];

            foreach ($insurances as $insurance) {
                $baseAmount = 0;
                $insuranceAmount = 0;
                $capApplied = null;

                // Calculate the insurance amount based on its type
                if ($insurance->value_type === "percentage") {
                    $baseAmount = (float)($principle);
                    // Insurance is monthly rate × principal × tenure (no caps)
                    $tenure = (int)($this->tenure ?? 12); // Get tenure, default to 12 months
                    $monthlyAmount = ($baseAmount * ((float)($insurance->value) / 100));
                    $insuranceAmount = $monthlyAmount * $tenure;
                    // No caps applied to insurance
                } else {
                    // Fixed insurance also multiplied by tenure
                    $tenure = (int)($this->tenure ?? 12);
                    $insuranceAmount = (float)$insurance->value * $tenure;
                }

                // Add to breakdown
                $tenure = (int)($this->tenure ?? 12);
                $breakdown[] = [
                    'name' => $insurance->name,
                    'value_type' => $insurance->value_type,
                    'value' => (float)$insurance->value,
                    'base_amount' => $baseAmount,
                    'amount' => $insuranceAmount,
                    'min_cap' => null, // No caps for insurance
                    'max_cap' => null, // No caps for insurance
                    'cap_applied' => null,
                    'tenure' => $tenure, // Include tenure in breakdown
                    'monthly_amount' => $insurance->value_type === "percentage" ? 
                        ($baseAmount * ((float)($insurance->value) / 100)) : 
                        (float)$insurance->value
                ];

                // Accumulate the total amount
                $totalAmount += $insuranceAmount;
            }

            // Store breakdown for the view
            $this->insuranceBreakdown = $breakdown;

            return $totalAmount;
        } catch (\Exception $e) {
            Log::error('Error calculating loan product insurance: ' . $e->getMessage());
            return 0;
        }
    }




    public function boot(): void
    {
        try {
            Log::info('=== ASSESSMENT COMPONENT BOOT START ===', [
                'timestamp' => now()->toISOString(),
                'session_id' => session()->getId(),
                'currentloanID' => Session::get('currentloanID')
            ]);

            // Initialize services
            $this->creditScoreService = new CreditScoreService();
            $this->clientInformationService = new ClientInformationServicex();
            $this->productParametersService = new ProductParametersServicex();

            // Pre-load all saved loan data comprehensively
            $this->preloadAllSavedLoanData();

            Log::info('=== ASSESSMENT COMPONENT BOOT END ===', [
                'timestamp' => now()->toISOString(),
                'loan_loaded' => isset($this->loan),
                'member_loaded' => isset($this->member),
                'product_loaded' => isset($this->product)
            ]);

        } catch (\Exception $e) {
            Log::error('Assessment component boot error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorMessage = 'Error loading assessment data. Please try again.';
        }
    }

    /**
     * Comprehensive method to pre-load all saved loan data
     * This ensures all data is available when the assessment page opens
     */
    private function preloadAllSavedLoanData(): void
    {
        try {
            Log::info('=== PRELOAD ALL SAVED LOAN DATA START ===');

            $this->loan = LoansModel::find(Session::get('currentloanID'));

            if (!$this->loan) {
                Log::warning('No loan found for currentloanID', [
                    'currentloanID' => Session::get('currentloanID')
                ]);
                return;
            }

            // 1. Load basic loan properties
            $this->loadBasicLoanProperties();

            // 2. Load member and client data
            $this->loadMemberAndClientData();

            // 3. Load product and loan details
            $this->loadProductAndLoanDetails();

            // 4. Load settlement data
            $this->loadSettlementData();

            // 5. Initialize services with loaded data
            $this->initializeServices();

            // 6. Load NBC loans data
            $this->loadNbcLoansData();

            // 7. Load loan schedule
            $this->loadLoanSchedule();

            // 8. Calculate assessment score
            $this->calculateAssessmentScore();

            // 9. Initialize selected contracts
            $this->initializeSelectedContracts();

            // 10. Calculate totals and deductions
            $this->calculateTotal();
            $this->calculateDeductionAmounts();

            // Load exception data now that all other data is loaded
            // This ensures we have all required data for proper exception calculation
            Log::info('Loading exception data after all other data is loaded');
            $this->loadExceptionData();

            Log::info('=== PRELOAD ALL SAVED LOAN DATA END ===', [
                'loan_id' => $this->loan->id ?? null,
                'client_number' => $this->loan->client_number ?? null,
                'data_loaded' => true,
                'exception_status' => $this->exceptionData['summary']['overall_status'] ?? 'N/A'
            ]);

        } catch (\Exception $e) {
            Log::error('Error preloading saved loan data: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Load basic loan properties from database
     */
    private function loadBasicLoanProperties(): void
    {
        if (!$this->loan) return;

        $this->idx = $this->loan->id;
        $this->loan_id = $this->loan->loan_id;
        $this->member_number = $this->loan->client_number;
        $this->sub_product_id = $this->loan->loan_sub_product;
        
        // Load saved values from database, with fallbacks and proper casting
        // IMPORTANT: If approved values are not in DB, use the requested values
        $this->take_home = (float)($this->loan->take_home ?? 0);
        
        // For approved_loan_value: check if it exists in DB, otherwise use principle (requested amount)
        if (isset($this->loan->approved_loan_value) && $this->loan->approved_loan_value > 0) {
            $this->approved_loan_value = (float)$this->loan->approved_loan_value;
        } else {
            $this->approved_loan_value = (float)($this->loan->principle ?? 0);
            Log::info('Using requested loan amount as approved value not set', [
                'requested_amount' => $this->loan->principle
            ]);
        }
        
        // For approved_term: check if it exists in DB, otherwise use tenure (requested term)
        if (isset($this->loan->approved_term) && $this->loan->approved_term > 0) {
            $this->approved_term = (int)$this->loan->approved_term;
        } else {
            $this->approved_term = (int)($this->loan->tenure ?? 12);
            Log::info('Using requested term as approved term not set', [
                'requested_term' => $this->loan->tenure
            ]);
        }
        
        $this->monthlyInstallmentValue = (float)($this->loan->monthly_installment ?? 0);
        $this->collateral_value = (float)($this->loan->collateral_value ?? 0);
        
        // Determine if physical collateral
        $this->isPhysicalCollateral = !empty($this->loan->collateral_type) && $this->loan->collateral_type === 'physical' || 
                                    !empty($this->loan->physical_collateral_value);

        // Load additional loan properties
        $this->interest_method = $this->loan->interest_method ?? "flat";
        $this->selectedLoan = $this->loan->selectedLoan ?? null;
        $this->topUpAmount = $this->loan->top_up_amount ?? 0;

        Log::info('Basic loan properties loaded', [
            'loan_id' => $this->loan_id,
            'approved_loan_value' => $this->approved_loan_value,
            'approved_term' => $this->approved_term,
            'take_home' => $this->take_home,
            'collateral_value' => $this->collateral_value
        ]);
    }

    /**
     * Load member and client data
     */
    private function loadMemberAndClientData(): void
    {
        if (!$this->loan) return;

        // Load member data
        $this->member = MembersModel::where('client_number', $this->loan->client_number)->first();

        // Load credit score data
        $this->loadCreditScoreData($this->loan->client_number);

        // Load client information data
        $this->loadClientInformationData($this->loan->client_number);

        Log::info('Member and client data loaded', [
            'client_number' => $this->loan->client_number,
            'member_loaded' => isset($this->member),
            'credit_score_loaded' => isset($this->creditScoreData),
            'client_info_loaded' => isset($this->clientInfoData)
        ]);
    }

    /**
     * Load product and loan details
     */
    private function loadProductAndLoanDetails(): void
    {
        if (!$this->loan) return;

        // Load product parameters data
        $this->loadProductParametersData($this->loan->loan_sub_product, $this->approved_loan_value);

        // Load product data
        $this->product = Loan_sub_products::where('sub_product_id', $this->loan->loan_sub_product)->first();

        // Load loan details (includes collateral calculations)
        $this->loadLoanDetails();

        // Load product details
        $this->loadProductDetails();

        // Load member details
        $this->loadMemberDetails();

        // Generate schedule
        $this->receiveData();

        Log::info('Product and loan details loaded', [
            'sub_product_id' => $this->loan->loan_sub_product,
            'product_loaded' => isset($this->product),
            'product_params_loaded' => isset($this->productParamsData)
        ]);
    }

    /**
     * Load settlement data
     */
    private function loadSettlementData(): void
    {
        if (!$this->loan) return;

        // Get data for loan_array_id = 1
        $settlement1 = DB::table('settled_loans')
            ->where('loan_id', session('currentloanID'))
            ->where('loan_array_id', 1)
            ->first();

        if ($settlement1) {
            $this->institution1 = $settlement1->institution;
            $this->institutionAmount = $settlement1->amount;
            $this->account1 = $settlement1->account;
        }

        // Get data for loan_array_id = 2
        $settlement2 = DB::table('settled_loans')
            ->where('loan_id', session('currentloanID'))
            ->where('loan_array_id', 2)
            ->first();

        if ($settlement2) {
            $this->institution2 = $settlement2->institution;
            $this->institutionAmount2 = $settlement2->amount;
            $this->account2 = $settlement2->account;
        }

        Log::info('Settlement data loaded', [
            'settlement1_loaded' => isset($settlement1),
            'settlement2_loaded' => isset($settlement2)
        ]);
    }

    /**
     * Initialize services with loaded data
     */
    private function initializeServices(): void
    {
        if (!$this->loan) return;

        // Initialize NBC loans service
        $this->nbcLoansService = new NbcLoansServicex(
            $this->loan->client_number ?? '',
            $this->selectedLoan
        );

        // Initialize settlement service
        $this->settlementService = new SettlementService(
            Session::get('currentloanID'),
            $this->loan->client_number ?? ''
        );

        Log::info('Services initialized', [
            'nbc_loans_service' => isset($this->nbcLoansService),
            'settlement_service' => isset($this->settlementService)
        ]);
    }

    /**
     * Load credit score data for the client
     */
    private function loadCreditScoreData($clientNumber)
    {
        try {
            $this->creditScoreData = $this->creditScoreService->getClientCreditScore($clientNumber);
        } catch (\Exception $e) {
            Log::error('Error loading credit score data: ' . $e->getMessage());
            $this->creditScoreData = $this->creditScoreService->getClientCreditScore(0); // Get default
        }
    }

    /**
     * Load client information data
     */
    private function loadClientInformationData($clientNumber)
    {
        try {
            $this->clientInfoData = $this->clientInformationService->getClientInformation($clientNumber);
        } catch (\Exception $e) {
            Log::error('Error loading client information data: ' . $e->getMessage());
            $this->clientInfoData = $this->clientInformationService->getClientInformation(0); // Get default
        }
    }

    /**
     * Load product parameters data
     */
    private function loadProductParametersData($productId, $approvedLoanValue = 0)
    {
        try {
            $this->productParamsData = $this->productParametersService->getProductParameters($productId, $approvedLoanValue);
        } catch (\Exception $e) {
            Log::error('Error loading product parameters data: ' . $e->getMessage());
            $this->productParamsData = $this->productParametersService->getProductParameters(0, 0); // Get default
        }
    }

    /**
     * Load loan details and fill component properties
     */
    private function loadLoanDetails(): void
    {
        try {
            Log::info('Assessment: Loading loan details');
            
            $this->loan = LoansModel::find(Session::get('currentloanID'));
            if ($this->loan) {
                // Store current user-modified values before filling
                $currentApprovedLoanValue = $this->approved_loan_value ?? 0;
                $currentTakeHome = $this->take_home ?? 0;
                $currentApprovedTerm = $this->approved_term ?? 0;
                
                $this->fill($this->loan->toArray());
                
                // Restore user-modified values if they exist
                if ($currentApprovedLoanValue > 0) {
                    $this->approved_loan_value = $currentApprovedLoanValue;
                }
                if ($currentTakeHome > 0) {
                    $this->take_home = $currentTakeHome;
                }
                if ($currentApprovedTerm > 0) {
                    $this->approved_term = $currentApprovedTerm;
                }

                // Load collateral information
                $this->collateral_value = $this->calculateCollateralValue(Session::get('currentloanID'));
                $this->collateral_type = $this->collateralType(Session::get('currentloanID'));
                $this->isPhysicalCollateral = $this->isPhysicalCollateralType($this->collateral_type);

                // Calculate coverage percentage
                if ($this->loan->principle > 0) {
                    $this->coverage = (($this->collateral_value / $this->loan->principle) * 100);
                } else {
                    $this->coverage = 0;
                }

                // Calculate monthly sales and profits
                $this->monthly_sales = $this->loan->daily_sales * 30;
                $this->gross_profit = $this->monthly_sales - $this->cost_of_goods_sold;
                $this->net_profit = $this->gross_profit - $this->monthly_taxes;
                $this->available_funds = ($this->net_profit - $this->other_expenses) / 2;

                Log::info('Assessment: Loan details loaded successfully', [
                    'loan_id' => $this->loan->id,
                    'client_number' => $this->loan->client_number,
                    'collateral_value' => $this->collateral_value,
                    'collateral_type' => $this->collateral_type,
                    'coverage_percentage' => $this->coverage
                ]);
            } else {
                Log::warning('Assessment: No loan found for currentloanID', [
                    'currentloanID' => Session::get('currentloanID')
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Assessment: Error loading loan details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Calculate total collateral value for a loan using the same logic as Guarantos component
     */
    private function calculateCollateralValue($loan_id)
    {
        try {
            if (!$loan_id) {
                Log::info('calculateCollateralValue: No loan_id provided');
                return 0;
            }
            
            $totalAmount = 0;
            
            // Get active guarantors for the loan
            $guarantors = DB::table('loan_guarantors')
                ->where('loan_id', $loan_id)
                ->where('status', 'active')
                ->get();
            
            Log::info("calculateCollateralValue: Found " . $guarantors->count() . " active guarantors for loan_id: " . $loan_id);
            
            foreach ($guarantors as $guarantor) {
                // Get active collaterals for this guarantor
                $collaterals = DB::table('loan_collaterals')
                    ->where('loan_guarantor_id', $guarantor->id)
                    ->where('status', 'active')
                    ->get();
                
                Log::info("calculateCollateralValue: Found " . $collaterals->count() . " active collaterals for guarantor_id: " . $guarantor->id);
                
                foreach ($collaterals as $collateral) {
                    $collateralAmount = (float)($collateral->collateral_amount ?? 0);
                    $totalAmount += $collateralAmount;
                    
                    Log::info("calculateCollateralValue: Added collateral amount: " . $collateralAmount . " for collateral_id: " . $collateral->id . " (type: " . $collateral->collateral_type . ")");
                }
            }
            
            // If no collaterals found, check if there are any pending/unregistered collaterals
            if ($totalAmount == 0) {
                $totalAmount = $this->calculatePendingCollateralValue($loan_id);
            }
            
            Log::info("calculateCollateralValue: Total collateral value: " . $totalAmount . " for loan_id: " . $loan_id);
            return $totalAmount;
        } catch (\Exception $e) {
            Log::error('Error calculating collateral value: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate pending/unregistered collateral value from form data or temporary storage
     */
    private function calculatePendingCollateralValue($loan_id)
    {
        try {
            // Check if there's any pending collateral data in session or temporary storage
            $pendingCollateral = session('pending_collateral_' . $loan_id, []);
            
            if (!empty($pendingCollateral)) {
                $totalAmount = 0;
                
                // Calculate from pending financial collaterals
                if (isset($pendingCollateral['financial_collaterals'])) {
                    foreach ($pendingCollateral['financial_collaterals'] as $collateral) {
                        $totalAmount += (float)($collateral['amount'] ?? 0);
                    }
                }
                
                // Calculate from pending physical collaterals
                if (isset($pendingCollateral['physical_collaterals'])) {
                    foreach ($pendingCollateral['physical_collaterals'] as $collateral) {
                        $totalAmount += (float)($collateral['value'] ?? 0);
                    }
                }
                
                Log::info("calculatePendingCollateralValue: Found pending collateral value: " . $totalAmount . " for loan_id: " . $loan_id);
                return $totalAmount;
            }
            
            // Check if there are any unregistered collaterals in the old system
            $oldCollaterals = DB::table('collaterals')
                ->where('loan_id', $loan_id)
                ->where('release_status', 'held')
                ->get();
            
            if ($oldCollaterals->count() > 0) {
                $totalAmount = 0;
                foreach ($oldCollaterals as $collateral) {
                    $totalAmount += (float)($collateral->collateral_value ?? 0);
                }
                
                Log::info("calculatePendingCollateralValue: Found old system collateral value: " . $totalAmount . " for loan_id: " . $loan_id);
                return $totalAmount;
            }
            
            return 0;
        } catch (\Exception $e) {
            Log::error('Error calculating pending collateral value: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get collateral type for a loan
     */
    private function collateralType($loan_id)
    {
        try {
            if (!$loan_id) {
                Log::info('collateralType: No loan_id provided');
                return '';
            }
            
            $collateral_type = '';
            $this->isPhysicalCollateral = false;
            
            // Use the new collateral system - get collateral from loan_guarantors and loan_collaterals tables
            $guarantors = DB::table('loan_guarantors')
                ->where('loan_id', $loan_id)
                ->where('status', 'active')
                ->get();
            
            Log::info("collateralType: Found " . $guarantors->count() . " active guarantors for loan_id: " . $loan_id);
            
            foreach ($guarantors as $guarantor) {
                $collaterals = DB::table('loan_collaterals')
                    ->where('loan_guarantor_id', $guarantor->id)
                    ->where('status', 'active')
                    ->get();
                
                Log::info("collateralType: Found " . $collaterals->count() . " active collaterals for guarantor_id: " . $guarantor->id);
                
                foreach ($collaterals as $collateral) {
                    Log::info("collateralType: Processing collateral type: " . $collateral->collateral_type . " for collateral_id: " . $collateral->id);
                    
                    if (in_array($collateral->collateral_type, ['savings', 'deposits', 'shares'])) {
                        $collateral_type = $collateral->collateral_type . '/' . $collateral_type;
                    } else {
                        $collateral_type = $collateral->collateral_type . '/' . $collateral_type;
                        $this->isPhysicalCollateral = true;
                        Log::info("collateralType: Set isPhysicalCollateral to true for collateral_id: " . $collateral->id);
                    }
                }
            }
            
            Log::info("collateralType: Final collateral type: " . $collateral_type . " for loan_id: " . $loan_id);
            return $collateral_type;
        } catch (\Exception $e) {
            Log::error('Error calculating collateral type: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Check if collateral type is physical
     */
    private function isPhysicalCollateralType($collateralType)
    {
        $physicalTypes = ['land', 'building', 'vehicle', 'equipment', 'machinery'];
        $collateralTypeLower = strtolower($collateralType);
        
        foreach ($physicalTypes as $type) {
            if (strpos($collateralTypeLower, $type) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Load product details and fill component properties
     */
    private function loadProductDetails(): void
    {
        try {
            // Use the loan's sub_product_id from the database, not the component property
            $productId = $this->loan ? $this->loan->loan_sub_product : ($this->loan_sub_product ?? null);
            
            Log::info('Assessment: Loading product details', [
                'loan_sub_product' => $productId ?? 'N/A',
                'loan_id' => $this->loan->id ?? 'N/A'
            ]);
            
            if (!$productId) {
                Log::error('Assessment: No product ID available for loading product details');
                return;
            }
            
            $this->products = Loan_sub_products::where('sub_product_id', $productId)->get();
            
            if ($this->products->count() > 0) {
                foreach ($this->products as $product) {
                    $this->disbursement_account = $product->disbursement_account;
                    $this->collection_account_loan_interest = $product->collection_account_loan_interest;
                    $this->collection_account_loan_principle = $product->collection_account_loan_principle;
                    $this->collection_account_loan_charges = $product->collection_account_loan_charges;
                    $this->collection_account_loan_penalties = $product->collection_account_loan_penalties;
                    $this->principle_min_value = $product->principle_min_value;
                    $this->principle_max_value = $product->principle_max_value;
                    $this->min_term = $product->min_term;
                    $this->max_term = $product->max_term;
                    $this->interest_value = $product->interest_value;
                    $this->principle_grace_period = $product->principle_grace_period;
                    $this->interest_grace_period = $product->interest_grace_period;
                    $this->amortization_method = $product->amortization_method;
                    
                    // Set the product object for use in other methods
                    $this->product = $product;
                }
                
                Log::info('Assessment: Product details loaded successfully', [
                    'product_id' => $this->product->sub_product_id ?? 'N/A',
                    'product_name' => $this->product->sub_product_name ?? 'N/A',
                    'loan_multiplier' => $this->product->loan_multiplier ?? 'N/A',
                    'ltv' => $this->product->ltv ?? 'N/A',
                    'principle_min_value' => $this->principle_min_value,
                    'principle_max_value' => $this->principle_max_value,
                    'min_term' => $this->min_term,
                    'max_term' => $this->max_term,
                    'interest_value' => $this->interest_value
                ]);
                
                // Validate that we're using the correct product
                if ($this->loan && $this->product->sub_product_id !== $this->loan->loan_sub_product) {
                    Log::error('Assessment: Product mismatch detected', [
                        'loan_sub_product' => $this->loan->loan_sub_product,
                        'loaded_product_id' => $this->product->sub_product_id
                    ]);
                }
            } else {
                Log::warning('Assessment: No product found for loan_sub_product', [
                    'loan_sub_product' => $this->loan_sub_product
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Assessment: Error loading product details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Recalculate exceptions when key variables change
     */
    public function recalculateExceptions()
    {
        try {
            Log::info('=== RECALCULATE EXCEPTIONS START ===', [
                'approved_loan_value' => $this->approved_loan_value ?? 0,
                'approved_term' => $this->approved_term ?? 0,
                'take_home' => $this->take_home ?? 0,
                'collateral_value' => $this->collateral_value ?? 0,
                'isPhysicalCollateral' => $this->isPhysicalCollateral ?? false,
                'timestamp' => now()->toISOString()
            ]);
            
            // Recalculate monthly installment first if loan value or term changed
            $this->calculateMonthlyInstallment();
            
            // Recalculate deduction breakdown when variables change
            $this->recalculateDeductions();
            
            // Then reload exception data with new values
            $this->loadExceptionData();
            
            // Auto-save the updated assessment data
            $this->autoSaveAssessmentData();
            
            Log::info('=== RECALCULATE EXCEPTIONS END ===', [
                'exception_status' => $this->exceptionData['summary']['overall_status'] ?? 'N/A',
                'can_approve' => $this->exceptionData['summary']['can_approve'] ?? false
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error recalculating exceptions: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Load exception data
     */
    private function loadExceptionData()
    {
        try {
            Log::info('=== LOAD EXCEPTION DATA START ===', [
                'approved_loan_value' => $this->approved_loan_value ?? 0,
                'approved_term' => $this->approved_term ?? 0,
                'take_home' => $this->take_home ?? 0,
                'collateral_value' => $this->collateral_value ?? 0,
                'isPhysicalCollateral' => $this->isPhysicalCollateral ?? false,
                'monthlyInstallmentValue' => $this->monthlyInstallmentValue ?? 0,
                'product_exists' => isset($this->product),
                'member_exists' => isset($this->member),
                'creditScoreData_exists' => isset($this->creditScoreData)
            ]);

            // Ensure monthly installment value is calculated before loading exceptions
            if (!isset($this->monthlyInstallmentValue) || $this->monthlyInstallmentValue <= 0) {
                Log::warning('Monthly installment value not set, calculating it first');
                $this->calculateMonthlyInstallment();
            }
            
            // Validate monthly installment value before passing to exception service
            $monthlyInstallment = (float)($this->monthlyInstallmentValue ?? 0);
            Log::info('Monthly installment validation', [
                'monthly_installment' => $monthlyInstallment,
                'is_valid' => $monthlyInstallment > 0 && $monthlyInstallment <= 100000000
            ]);
            
            if ($monthlyInstallment <= 0 || $monthlyInstallment > 100000000) {
                Log::error('Invalid monthly installment value for exception calculation', [
                    'monthly_installment' => $monthlyInstallment
                ]);
                throw new \Exception('Invalid monthly installment value');
            }
            
            // Initialize exception service with current data
            Log::info('Initializing ExceptionService with data', [
                'approved_loan_value' => $this->approved_loan_value,
                'approved_term' => $this->approved_term,
                'take_home' => $this->take_home,
                'monthly_installment' => $monthlyInstallment,
                'collateral_value' => $this->collateral_value,
                'is_physical_collateral' => $this->isPhysicalCollateral,
                'product_exists' => isset($this->product),
                'member_exists' => isset($this->member),
                'credit_score_data_exists' => isset($this->creditScoreData)
            ]);
            
            // Use the APPROVED values for exception checking
            // This ensures we validate against the actual approved loan parameters
            $requestedLoanValue = (float)($this->loan->principle ?? 0);
            $approvedLoanValue = (float)($this->approved_loan_value ?? 0);
            $currentApprovedLoanValue = $approvedLoanValue; // Use approved value, not max
            
            $requestedTerm = (int)($this->loan->tenure ?? 0);
            $approvedTerm = (int)($this->approved_term ?? 0);
            $currentApprovedTerm = $approvedTerm; // Use approved value, not max
            
            // For take home and collateral, use the actual values
            $currentTakeHome = $this->take_home > 0 ? $this->take_home : ($this->loan->take_home ?? 0);
            $currentCollateralValue = $this->collateral_value > 0 ? $this->collateral_value : ($this->loan->collateral_value ?? 0);
            
            // Log the values being used for exception checking
            Log::info('Using approved values for exception checking', [
                'requested_loan_value' => $requestedLoanValue,
                'approved_loan_value' => $approvedLoanValue,
                'using_loan_value' => $currentApprovedLoanValue,
                'requested_term' => $requestedTerm,
                'approved_term' => $approvedTerm,
                'using_term' => $currentApprovedTerm
            ]);
            
            Log::info('Using values for ExceptionService', [
                'requested_loan_value' => $requestedLoanValue,
                'approved_loan_value' => $approvedLoanValue,
                'using_loan_value' => $currentApprovedLoanValue,
                'requested_term' => $requestedTerm,
                'approved_term' => $approvedTerm,
                'using_term' => $currentApprovedTerm,
                'take_home' => $currentTakeHome,
                'collateral_value' => $currentCollateralValue
            ]);
            
            // Validate required data before creating ExceptionService
            if (!$this->loan) {
                throw new \Exception('Loan object is required for exception calculation');
            }
            if (!$this->product) {
                throw new \Exception('Product object is required for exception calculation');
            }
            if (!$this->member) {
                throw new \Exception('Member object is required for exception calculation');
            }
            if (!$this->creditScoreData || !isset($this->creditScoreData['score'])) {
                // Provide default credit score if not available
                Log::warning('Credit score data not available, using default');
                $this->creditScoreData = ['score' => 500, 'grade' => 'E'];
            }
            
            $this->exceptionService = new ExceptionService(
                $this->loan,
                $this->product,
                $this->member,
                $this->creditScoreData,
                $currentApprovedLoanValue,
                $currentApprovedTerm,
                $currentTakeHome,
                $monthlyInstallment,
                $currentCollateralValue,
                $this->isPhysicalCollateral
            );

            // Get exception data
            $this->exceptionData = $this->exceptionService->getExceptions();
            
            Log::info('=== LOAD EXCEPTION DATA END ===', [
                'exception_data_count' => count($this->exceptionData ?? []),
                'summary_status' => $this->exceptionData['summary']['overall_status'] ?? 'N/A'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading exception data: ' . $e->getMessage(), [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'loan_id' => $this->loan->id ?? 'NULL',
                'product_id' => $this->product->sub_product_id ?? 'NULL',
                'approved_loan_value' => $this->approved_loan_value ?? 'NULL',
                'approved_term' => $this->approved_term ?? 'NULL',
                'trace' => $e->getTraceAsString()
            ]);
            
            // Set error state with more detailed information
            $this->exceptionData = [
                'loan_amount' => ['status' => 'ERROR', 'is_exceeded' => false, 'error' => $e->getMessage()],
                'term' => ['status' => 'ERROR', 'is_exceeded' => false],
                'credit_score' => ['status' => 'ERROR', 'is_exceeded' => false],
                'salary_installment' => ['status' => 'ERROR', 'is_exceeded' => false],
                'collateral' => ['status' => 'ERROR', 'is_exceeded' => false],
                'summary' => ['overall_status' => 'ERROR', 'can_approve' => false, 'error_message' => $e->getMessage()]
            ];
        }
    }

    /**
     * Load NBC loans data
     */
    private function loadNbcLoansData()
    {
        try {
            // Initialize NBC loans service
            $this->nbcLoansService = new NbcLoansServicex(
                $this->loan->client_number ?? '',
                $this->selectedLoan
            );

            // Get NBC loans data
            $this->nbcLoansData = $this->nbcLoansService->getNbcLoansData();
            
            // Get settlement data using SettlementService for consistent structure
            if ($this->settlementService) {
                $this->settlementData = $this->settlementService->getSettlementData();
            } else {
                // Fallback to NBC service if settlement service is not available
                $this->settlementData = $this->nbcLoansService->getSettlementData() ?? [];
                
                // Ensure the structure matches what the Blade template expects
                if (!isset($this->settlementData['has_settlements'])) {
                    $this->settlementData['has_settlements'] = ($this->settlementData['total_amount'] ?? 0) > 0;
                }
                if (!isset($this->settlementData['count'])) {
                    $this->settlementData['count'] = 0;
                    if (isset($this->settlementData['settlement1']) && $this->settlementData['settlement1']['exists']) {
                        $this->settlementData['count']++;
                    }
                    if (isset($this->settlementData['settlement2']) && $this->settlementData['settlement2']['exists']) {
                        $this->settlementData['count']++;
                    }
                }
                if (!isset($this->settlementData['settlements'])) {
                    $this->settlementData['settlements'] = [];
                    if (isset($this->settlementData['settlement1']) && $this->settlementData['settlement1']['exists']) {
                        $this->settlementData['settlements'][] = [
                            'id' => 1,
                            'institution' => $this->settlementData['settlement1']['institution'],
                            'account' => $this->settlementData['settlement1']['account'],
                            'amount' => $this->settlementData['settlement1']['amount'],
                            'updated_at' => now()
                        ];
                    }
                    if (isset($this->settlementData['settlement2']) && $this->settlementData['settlement2']['exists']) {
                        $this->settlementData['settlements'][] = [
                            'id' => 2,
                            'institution' => $this->settlementData['settlement2']['institution'],
                            'account' => $this->settlementData['settlement2']['account'],
                            'amount' => $this->settlementData['settlement2']['amount'],
                            'updated_at' => now()
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error loading NBC loans data: ' . $e->getMessage());
            $this->nbcLoansData = [
                'loans' => [],
                'summary' => ['total_balance' => 0, 'total_installment' => 0, 'total_loans' => 0],
                'has_loans' => false,
                'total_balance' => 0,
                'total_installment' => 0,
                'selected_loan' => null
            ];
            $this->settlementData = [
                'total_amount' => 0,
                'settlements' => [],
                'count' => 0,
                'has_settlements' => false
            ];
        }
    }

    /**
     * Load loan schedule data
     */
    private function loadLoanSchedule()
    {
        try {
            Log::info('=== LOAD LOAN SCHEDULE START ===', [
                'loan_exists' => isset($this->loan),
                'approved_loan_value' => $this->approved_loan_value ?? 0,
                'approved_term' => $this->approved_term ?? 0,
                'product_exists' => isset($this->product)
            ]);

            if (!$this->loan || !$this->approved_loan_value || !$this->approved_term || !$this->product) {
                Log::warning('Missing required data for schedule generation', [
                    'loan' => isset($this->loan),
                    'approved_loan_value' => $this->approved_loan_value ?? 0,
                    'approved_term' => $this->approved_term ?? 0,
                    'product' => isset($this->product)
                ]);
                $this->schedule = [];
                $this->footer = [];
                return;
            }

            // Validate loan parameters to prevent unrealistic values
            $this->validateLoanParameters();

            // First, generate the schedule using receiveData to set monthlyInstallmentValue
            $this->receiveData();

            // Generate schedule directly from current parameters using reducing balance with equal installments
            // For restructure loans, add buffer to principal
            $principal = (float)$this->approved_loan_value;

            // Add buffer for restructure loans (covers processing costs)
            if ($this->loan && in_array($this->loan->loan_type_2 ?? '', ['Restructure', 'Restructuring'])) {
                // Calculate buffer: Management Fees + Insurance + First Interest
                $bufferAmount = (float)$this->totalCharges + (float)$this->totalInsurance + (float)$this->firstInstallmentInterestAmount;
                $principal += $bufferAmount;

                Log::info('Restructure loan: Adding buffer to principal for schedule', [
                    'original_principal' => (float)$this->approved_loan_value,
                    'buffer_amount' => $bufferAmount,
                    'total_principal' => $principal
                ]);
            }

            $annualInterestRate = (float)($this->product->interest_value ?? 0);
            $monthlyInterestRate = $annualInterestRate / 12 / 100; // Convert to decimal
            $tenure = (int)$this->approved_term;

            Log::info('Generating reducing balance schedule with parameters', [
                'principal' => $principal,
                'annualInterestRate' => $annualInterestRate,
                'monthlyInterestRate' => $monthlyInterestRate,
                'tenure' => $tenure
            ]);

            // Calculate equal monthly installment using amortization formula
            // PMT = P * (r * (1 + r)^n) / ((1 + r)^n - 1)
            if ($monthlyInterestRate > 0) {
                $monthlyInstallment = $principal * ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $tenure)) / (pow(1 + $monthlyInterestRate, $tenure) - 1);
            } else {
                $monthlyInstallment = $principal / $tenure; // If no interest, equal principal payments
            }

            // Validate monthly installment to prevent unrealistic values
            if ($monthlyInstallment <= 0 || $monthlyInstallment > $principal * 10) {
                Log::error('Invalid monthly installment calculated', [
                    'monthly_installment' => $monthlyInstallment,
                    'principal' => $principal,
                    'tenure' => $tenure
                ]);
                throw new \Exception('Invalid monthly installment calculated');
            }

            // Set the monthly installment value for use in exceptions
            $this->monthlyInstallmentValue = $monthlyInstallment;

            Log::info('Monthly installment calculated', [
                'monthly_installment' => $monthlyInstallment
            ]);

            // Generate schedule
            $this->schedule = [];
            $remainingBalance = $principal;
            $totalPayment = 0;
            $totalInterest = 0;
            $totalPrincipal = 0;

            // Calculate dates
            $disbursementDate = \Carbon\Carbon::now()->addDay();
            $firstRegularDate = $disbursementDate->copy()->addMonth();
            
            // Fix: Calculate first interest correctly using actual days between disbursement and first regular payment
            $daysToFirstInstallment = $disbursementDate->diffInDays($firstRegularDate);
            $firstInterestAmount = $principal * ($annualInterestRate / 100) * ($daysToFirstInstallment / 365);
            
            // Validate first interest amount
            if ($firstInterestAmount < 0 || $firstInterestAmount > $principal * 0.5) {
                Log::error('Invalid first interest amount calculated', [
                    'first_interest_amount' => $firstInterestAmount,
                    'principal' => $principal,
                    'days_to_first_installment' => $daysToFirstInstallment,
                    'annual_interest_rate' => $annualInterestRate
                ]);
                $firstInterestAmount = 0; // Set to 0 if calculation is invalid
            }
            
            if ($firstInterestAmount > 0) {
                $this->schedule[] = [
                    'installment_date' => $firstRegularDate->format('Y-m-d'),
                    'opening_balance' => $principal,
                    'payment' => $firstInterestAmount,
                    'principal' => 0,
                    'interest' => $firstInterestAmount,
                    'closing_balance' => $principal, // Balance doesn't change for interest-only payment
                ];
                
                $totalPayment += $firstInterestAmount;
                $totalInterest += $firstInterestAmount;
                // Principal remains unchanged for first interest installment
            }

            // Generate regular installments starting from the second month
            $regularStartDate = $firstRegularDate->copy()->addMonth();
            
            for ($i = 0; $i < $tenure; $i++) {
                $openingBalance = $remainingBalance;
                
                // Calculate interest for this month
                $monthlyInterest = $remainingBalance * $monthlyInterestRate;
                
                // Calculate principal for this month
                $monthlyPrincipal = $monthlyInstallment - $monthlyInterest;
                
                // Ensure we don't overpay in the last installment
                if ($i == $tenure - 1) {
                    $monthlyPrincipal = $remainingBalance;
                    $monthlyInstallment = $monthlyPrincipal + $monthlyInterest;
                }
                
                // Update remaining balance
                $remainingBalance -= $monthlyPrincipal;
                if ($remainingBalance < 0.01) $remainingBalance = 0; // Round to zero if very small
                
                $this->schedule[] = [
                    'installment_date' => $regularStartDate->format('Y-m-d'),
                    'opening_balance' => $openingBalance,
                    'payment' => $monthlyInstallment,
                    'principal' => $monthlyPrincipal,
                    'interest' => $monthlyInterest,
                    'closing_balance' => $remainingBalance,
                ];
                
                $totalPayment += $monthlyInstallment;
                $totalInterest += $monthlyInterest;
                $totalPrincipal += $monthlyPrincipal;
                
                $regularStartDate->addMonth();
            }
            
            // Set footer data
            $this->footer = [
                'total_payment' => $totalPayment,
                'total_interest' => $totalInterest,
                'total_principal' => $totalPrincipal,
                'final_closing_balance' => $remainingBalance,
            ];

            Log::info('Reducing balance schedule generated successfully', [
                'installments_count' => count($this->schedule),
                'monthly_installment' => $monthlyInstallment,
                'total_payment' => $totalPayment,
                'total_interest' => $totalInterest,
                'total_principal' => $totalPrincipal
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading loan schedule: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->schedule = [];
            $this->footer = [];
        }
    }

    /**
     * Validate loan parameters to prevent unrealistic values
     */
    private function validateLoanParameters()
    {
        $errors = [];

        // Validate approved loan value
        if ($this->approved_loan_value <= 0 || $this->approved_loan_value > 1000000000) {
            $errors[] = 'Invalid approved loan value: ' . $this->approved_loan_value;
        }

        // Validate approved term
        if ($this->approved_term <= 0 || $this->approved_term > 360) {
            $errors[] = 'Invalid approved term: ' . $this->approved_term;
        }

        // Validate take home salary
        if ($this->take_home < 0 || $this->take_home > 100000000) {
            $errors[] = 'Invalid take home salary: ' . $this->take_home;
        }

        // Validate collateral value
        if ($this->collateral_value < 0 || $this->collateral_value > 1000000000) {
            $errors[] = 'Invalid collateral value: ' . $this->collateral_value;
        }

        if (!empty($errors)) {
            Log::error('Loan parameter validation failed', $errors);
            throw new \Exception('Loan parameter validation failed: ' . implode(', ', $errors));
        }
    }

    /**
     * Calculate monthly installment value for use in exceptions
     */
    private function calculateMonthlyInstallment()
    {
        try {
            // Use current form values, fallback to database values
            $currentApprovedLoanValue = $this->approved_loan_value > 0 ? $this->approved_loan_value : ($this->loan->principle ?? 0);
            $currentApprovedTerm = $this->approved_term > 0 ? $this->approved_term : ($this->loan->tenure ?? 12);
            
            Log::info('=== CALCULATE MONTHLY INSTALLMENT START ===', [
                'approved_loan_value' => $currentApprovedLoanValue,
                'approved_term' => $currentApprovedTerm,
                'product_exists' => isset($this->product),
                'product_interest_value' => $this->product->interest_value ?? 'NULL'
            ]);

            if (!$currentApprovedLoanValue || !$currentApprovedTerm || !$this->product) {
                Log::warning('Missing required data for monthly installment calculation', [
                    'approved_loan_value' => $currentApprovedLoanValue,
                    'approved_term' => $currentApprovedTerm,
                    'product_exists' => isset($this->product)
                ]);
                $this->monthlyInstallmentValue = 0;
                return;
            }

            $principal = (float)$currentApprovedLoanValue;
            $annualInterestRate = (float)($this->product->interest_value ?? 0);
            $monthlyInterestRate = $annualInterestRate / 12 / 100;
            $tenure = (int)$currentApprovedTerm;

            Log::info('Monthly installment calculation parameters', [
                'principal' => $principal,
                'annual_interest_rate' => $annualInterestRate,
                'monthly_interest_rate' => $monthlyInterestRate,
                'tenure' => $tenure
            ]);

            // Calculate equal monthly installment using amortization formula
            if ($monthlyInterestRate > 0) {
                $monthlyInstallment = $principal * ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $tenure)) / (pow(1 + $monthlyInterestRate, $tenure) - 1);
            } else {
                $monthlyInstallment = $principal / $tenure;
            }

            Log::info('Monthly installment calculation result', [
                'monthly_installment' => $monthlyInstallment,
                'calculation_method' => $monthlyInterestRate > 0 ? 'amortization' : 'simple'
            ]);

            // Validate the calculated monthly installment
            if ($monthlyInstallment <= 0 || $monthlyInstallment > $principal * 10) {
                Log::error('Invalid monthly installment calculated', [
                    'monthly_installment' => $monthlyInstallment,
                    'principal' => $principal,
                    'tenure' => $tenure
                ]);
                $this->monthlyInstallmentValue = 0;
                return;
            }

            $this->monthlyInstallmentValue = $monthlyInstallment;

            Log::info('=== CALCULATE MONTHLY INSTALLMENT END ===', [
                'monthly_installment' => $monthlyInstallment,
                'principal' => $principal,
                'tenure' => $tenure,
                'annual_interest_rate' => $annualInterestRate
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculating monthly installment: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->monthlyInstallmentValue = 0;
        }
    }

    /**
     * Recalculate deduction breakdown when variables change
     */
    private function recalculateDeductions()
    {
        try {
            Log::info('=== RECALCULATE DEDUCTIONS START ===', [
                'approved_loan_value' => $this->approved_loan_value ?? 0,
                'approved_term' => $this->approved_term ?? 0,
                'product_exists' => isset($this->product),
                'timestamp' => now()->toISOString()
            ]);
            
            // Recalculate charges based on new loan amount
            if ($this->product && $this->approved_loan_value > 0) {
                $this->totalCharges = $this->calculateLoanProductCharge($this->product->sub_product_id, (float)($this->approved_loan_value));
                Log::info('Recalculated total charges', ['totalCharges' => $this->totalCharges]);
            } else {
                $this->totalCharges = 0;
                Log::info('No product or approved_loan_value, setting totalCharges to 0');
            }
            
            // Recalculate first installment interest based on new loan amount and term
            if ($this->product && $this->approved_loan_value > 0 && $this->approved_term > 0) {
                // Get payroll date from member_group
                $dayOfMonth = 15; // Default value
                if (isset($this->member->client_number)) {
                    // Get client record
                    $client = DB::table('clients')->where('client_number', $this->member->client_number)->first();
                    if ($client && $client->member_group) {
                        // Get payroll date from member_groups table
                        $memberGroup = DB::table('member_groups')->where('group_id', $client->member_group)->first();
                        if ($memberGroup && $memberGroup->payrol_date) {
                            $dayOfMonth = (int)$memberGroup->payrol_date;
                        }
                    }
                }
                
                $this->firstInstallmentInterestAmount = $this->calculateFirstInterestAmount(
                    (float)($this->approved_loan_value),
                    (float)($this->product->interest_value ?? 0) / 12 / 100,
                    $dayOfMonth
                );
                Log::info('Recalculated first installment interest', [
                    'firstInstallmentInterestAmount' => $this->firstInstallmentInterestAmount
                ]);
            } else {
                $this->firstInstallmentInterestAmount = 0;
                Log::info('No product, approved_loan_value, or approved_term, setting firstInstallmentInterestAmount to 0');
            }
            
            Log::info('=== RECALCULATE DEDUCTIONS END ===', [
                'final_totalCharges' => $this->totalCharges,
                'final_firstInstallmentInterestAmount' => $this->firstInstallmentInterestAmount,
                'topUpAmount' => $this->topUpAmount ?? 0,
                'penaltyAmount' => $this->topUpAmount ? ($this->topUpAmount * 0.05) : 0
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error recalculating deductions: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Reset to safe defaults on error
            $this->totalCharges = 0;
            $this->firstInstallmentInterestAmount = 0;
        }
    }

    /**
     * Check if credit score meets product requirements
     */
    public function checkCreditScoreException()
    {
        if (!$this->product || !$this->creditScoreData) {
            return false;
        }

        return !$this->creditScoreService->meetsProductRequirements(
            $this->loan->client_number, 
            $this->product->score_limit ?? 0
        );
    }

    public function updated($fieldName, $value) {
        try {
            Log::info('=== UPDATED METHOD CALLED ===', [
                'field_name' => $fieldName,
                'value' => $value,
                'value_type' => gettype($value),
                'timestamp' => now()->toISOString()
            ]);
            
            // Cast numeric values to appropriate types
            $this->castNumericValue($fieldName, $value);
            
            // List of fields that should trigger exception recalculation
            $exceptionTriggerFields = [
                'approved_loan_value',
                'approved_term',
                'take_home',
                'monthlyInstallmentValue',
                'isPhysicalCollateral',
                'collateral_value'
            ];
            
            // Check if the updated field should trigger exception recalculation
            if (in_array($fieldName, $exceptionTriggerFields)) {
                Log::info('Field change triggers exception recalculation', [
                    'field' => $fieldName,
                    'new_value' => $value
                ]);
                $this->recalculateExceptions();
            }
            
            // Skip database update for properties that are not database columns
            $nonDatabaseFields = ['selectedContracts', 'max_loan', 'x', 'isPhysicalCollateral', 'account1', 'account2', 'selectedLoan', 'ClosedLoanBalance', 'errorMessage', 'age', 'name', 'monthsToRetirement', 'savings', 'creditScoreData', 'clientInfoData', 'productParamsData', 'exceptionData', 'nbcLoansData', 'settlementData', 'actionCompleted', 'actionType', 'actionMessage', 'showActionButtons', 'isProcessing', 'settlementForm', 'editingSettlementId', 'showSettlementForm', 'topUpAmount', 'totalCharges', 'totalInsurance', 'firstInstallmentInterestAmount', 'schedule', 'footer'];
            
            if (!in_array($fieldName, $nonDatabaseFields)) {
                $this->updateFieldInDatabase($fieldName, $value);
            } else {
                // Auto-save assessment data for important non-database fields
                $importantNonDatabaseFields = ['selectedLoan', 'topUpAmount', 'exceptionData', 'settlementData', 'schedule'];
                if (in_array($fieldName, $importantNonDatabaseFields)) {
                    $this->autoSaveAssessmentData();
                }
            }
            
            // Refresh exception data when key loan parameters change
            if (in_array($fieldName, ['approved_loan_value', 'approved_term', 'take_home', 'collateral_value'])) {
                $this->loadExceptionData();
                $this->calculateDeductionAmounts();
                $this->loadLoanSchedule();
                
                // Auto-save after recalculations
                $this->autoSaveAssessmentData();
            }
            
            // Refresh NBC loans data when selected loan changes
            if ($fieldName === 'selectedLoan') {
                $this->loadNbcLoansData();
                $this->calculateTopUpAmount();
                $this->autoSaveAssessmentData();
            }

            // Check assessment completion when key assessment fields are updated
            if (in_array($fieldName, ['approved_loan_value', 'approved_term', 'monthlyInstallmentValue', 'take_home'])) {
                // Add a small delay to ensure the database update is complete
                $this->dispatchBrowserEvent('assessment-field-updated');
                
                // Check completion status after a brief delay
                $this->dispatchBrowserEvent('check-assessment-completion');
            }
            
            // ALWAYS update assessment data after any field change
            // This ensures assessment data is always current with the latest form values
            $this->autoSaveAssessmentData();
            
        } catch (\Exception $e) {
            Log::error('Error updating field: ' . $e->getMessage());
            $this->errorMessage = 'Error updating field. Please try again.';
        }
    }

    /**
     * Cast numeric values to appropriate types to prevent type errors
     */
    private function castNumericValue($fieldName, $value) {
        // Define numeric fields and their expected types
        $floatFields = [
            'take_home', 'approved_loan_value', 'monthlyInstallmentValue', 
            'collateral_value', 'topUpAmount', 'totalCharges', 'totalInsurance', 
            'firstInstallmentInterestAmount', 'totalAmount', 'netDisbursement',
            'totalDeductions', 'closedLoanBalance'
        ];
        
        $intFields = [
            'approved_term', 'tenure', 'max_term'
        ];
        
        // Cast to float if it's a float field
        if (in_array($fieldName, $floatFields)) {
            $this->$fieldName = (float)($value ?? 0);
            Log::info("Casted {$fieldName} to float: " . $this->$fieldName);
        }
        
        // Cast to int if it's an int field
        if (in_array($fieldName, $intFields)) {
            $this->$fieldName = (int)($value ?? 0);
            Log::info("Casted {$fieldName} to int: " . $this->$fieldName);
        }
    }

    public function updateFieldInDatabase($fieldName, $value) {
        try {
            $model = LoansModel::find(Session::get('currentloanID'));
            if ($model) {
                $model->$fieldName = $value; // Update the field dynamically
                $model->save();
                
                Log::info('Database field updated successfully', [
                    'field_name' => $fieldName,
                    'value' => $value,
                    'loan_id' => $model->id
                ]);
                
                // Auto-save comprehensive assessment data after every field update
                $this->autoSaveAssessmentData();
            }
        } catch (\Exception $e) {
            Log::error('Error updating field in database: ' . $e->getMessage());
            $this->errorMessage = 'Error updating loan data. Please try again.';
        }
    }

    /**
     * Auto-save comprehensive assessment data to database
     * This method is called after every parameter change to ensure assessment data is always current
     */
    private function autoSaveAssessmentData()
    {
        try {
            Log::info('=== AUTO SAVE ASSESSMENT DATA START ===', [
                'timestamp' => now()->toISOString(),
                'session_id' => session()->getId(),
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'unknown'
            ]);

            $loanId = Session::get('currentloanID');
            Log::info('Retrieved loan ID from session', [
                'loan_id' => $loanId,
                'session_has_currentloanID' => Session::has('currentloanID')
            ]);

            if (!$loanId) {
                Log::warning('No loan ID found in session, aborting auto-save', [
                    'session_data' => Session::all()
                ]);
                return;
            }

            Log::info('Looking up loan model in database', [
                'loan_id' => $loanId,
                'search_criteria' => ['id' => $loanId]
            ]);

            $model = LoansModel::find($loanId);
            
            if (!$model) {
                Log::error('Loan model not found in database', [
                    'loan_id' => $loanId,
                    'search_criteria' => ['id' => $loanId],
                    'database_connection' => config('database.default')
                ]);
                return;
            }

            Log::info('Loan model found successfully', [
                'loan_id' => $model->id,
                'loan_status' => $model->status,
                'client_number' => $model->client_number,
                'existing_assessment_data_size' => $model->assessment_data ? strlen($model->assessment_data) : 0,
                'existing_assessment_data_keys' => $model->assessment_data ? array_keys(json_decode($model->assessment_data, true) ?? []) : []
            ]);

            // Build comprehensive assessment data
            Log::info('Building comprehensive assessment data', [
                'assessment_score_exists' => isset($this->assessmentScore),
                'assessment_score_keys' => isset($this->assessmentScore) ? array_keys($this->assessmentScore) : [],
                'exception_data_exists' => isset($this->exceptionData),
                'exception_data_count' => isset($this->exceptionData) ? count($this->exceptionData) : 0,
                'settlement_data_exists' => isset($this->settlementData),
                'settlement_data_keys' => isset($this->settlementData) ? array_keys($this->settlementData) : []
            ]);

            $assessmentData = $this->buildComprehensiveAssessmentData();
            
            Log::info('Comprehensive assessment data built successfully', [
                'assessment_data_keys' => array_keys($assessmentData),
                'assessment_data_size' => strlen(json_encode($assessmentData)),
                'assessment_data_sample' => array_slice($assessmentData, 0, 5, true), // First 5 items
                'overall_score' => $assessmentData['overall_score'] ?? 'not_set',
                'approved_loan_value' => $assessmentData['approved_loan_value'] ?? 'not_set',
                'monthly_installment' => $assessmentData['monthly_installment'] ?? 'not_set'
            ]);

            // Calculate total deductions and net disbursement for database persistence
            $totalDeductions = (float)($this->firstInstallmentInterestAmount ?? 0) + 
                              (float)($this->totalCharges ?? 0) + 
                              (float)($this->totalInsurance ?? 0) + 
                              (float)($this->totalAmount ?? 0) + 
                              (float)($this->topUpAmount ?? 0) + 
                              (float)($this->approved_loan_value ?? 0) * 0.05; // Early Settlement Penalty (5%)
            
            $netDisbursementAmount = (float)($this->approved_loan_value ?? 0) - $totalDeductions;

            // Prepare update data
            $updateData = [
                'assessment_data' => json_encode($assessmentData),
                'monthly_installment' => $this->monthlyInstallmentValue ?? 0,
                'approved_loan_value' => $this->approved_loan_value ?? 0,
                'approved_term' => $this->approved_term ?? 0,
                'take_home' => $this->take_home ?? 0,
                'collateral_value' => $this->collateral_value ?? 0,
                'collateral_type' => $this->collateral_type ?? null,
                'available_funds' => $this->available_funds ?? 0,
                'total_deductions' => $totalDeductions,
                'net_disbursement_amount' => $netDisbursementAmount,
                'updated_at' => now()
            ];

            Log::info('Prepared update data for loan model', [
                'update_fields' => array_keys($updateData),
                'update_values' => $updateData,
                'json_encoded_assessment_size' => strlen($updateData['assessment_data']),
                'previous_assessment_data_size' => $model->assessment_data ? strlen($model->assessment_data) : 0,
                'total_deductions' => $totalDeductions,
                'net_disbursement_amount' => $netDisbursementAmount,
                'early_settlement_penalty' => (float)($this->approved_loan_value ?? 0) * 0.05
            ]);

            // Update the loan with comprehensive assessment data
            Log::info('Executing database update operation', [
                'loan_id' => $model->id,
                'update_fields_count' => count($updateData),
                'database_connection' => config('database.default'),
                'table_name' => $model->getTable()
            ]);

            $updateResult = $model->update($updateData);

            Log::info('Database update operation completed', [
                'update_successful' => $updateResult,
                'rows_affected' => $updateResult ? 1 : 0,
                'updated_at_timestamp' => $updateData['updated_at']->toISOString()
            ]);

            // Verify the update was successful
            $updatedModel = LoansModel::find($loanId);
            if ($updatedModel) {
                Log::info('Verification: Loan model updated successfully', [
                    'loan_id' => $updatedModel->id,
                    'new_assessment_data_size' => $updatedModel->assessment_data ? strlen($updatedModel->assessment_data) : 0,
                    'new_assessment_data_keys' => $updatedModel->assessment_data ? array_keys(json_decode($updatedModel->assessment_data, true) ?? []) : [],
                    'new_monthly_installment' => $updatedModel->monthly_installment,
                    'new_approved_loan_value' => $updatedModel->approved_loan_value,
                    'new_updated_at' => $updatedModel->updated_at->toISOString()
                ]);
            } else {
                Log::error('Verification failed: Could not retrieve updated loan model', [
                    'loan_id' => $loanId
                ]);
            }

            Log::info('=== AUTO SAVE ASSESSMENT DATA END ===', [
                'total_execution_time' => microtime(true) - LARAVEL_START,
                'memory_usage' => memory_get_usage(true),
                'peak_memory_usage' => memory_get_peak_usage(true)
            ]);

        } catch (\Exception $e) {
            Log::error('=== AUTO SAVE ASSESSMENT DATA ERROR ===', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'loan_id' => Session::get('currentloanID'),
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'stack_trace' => $e->getTraceAsString(),
                'current_assessment_data_size' => isset($assessmentData) ? strlen(json_encode($assessmentData)) : 'not_built',
                'current_assessment_data_keys' => isset($assessmentData) ? array_keys($assessmentData) : 'not_built'
            ]);
        }
    }

    /**
     * Build comprehensive assessment data structure
     * This method collects all current assessment information into a structured format
     */
    private function buildComprehensiveAssessmentData()
    {
        try {
            $loanId = Session::get('currentloanID');
            $user = Auth::user();

            // Calculate assessment score if not already calculated
            if (!isset($this->assessmentScore)) {
                $this->calculateAssessmentScore();
            }

            // Build the comprehensive assessment data structure
            $assessmentData = [
                // Basic loan information
                'approved_loan_value' => $this->approved_loan_value ?? 0,
                'approved_term' => $this->approved_term ?? 0,
                'monthly_installment' => $this->monthlyInstallmentValue ?? 0,
                'take_home' => $this->take_home ?? 0,
                'collateral_value' => $this->collateral_value ?? 0,
                'collateral_type' => $this->collateral_type ?? null,
                'available_funds' => $this->available_funds ?? 0,
                'coverage' => $this->coverage ?? 0,

                // Income assessment
                'monthly_sales' => $this->monthly_sales ?? 0,
                'gross_profit' => $this->gross_profit ?? 0,
                'net_profit' => $this->net_profit ?? 0,
                'recommended' => $this->recommended ?? 0,
                'recommended_tenure' => $this->recommended_tenure ?? 0,

                // Product parameters
                'product_name' => $this->product->sub_product_name ?? null,
                'interest_rate' => $this->product->interest_value ?? 0,
                'max_amount' => $this->product->max_amount ?? 0,
                'max_term' => $this->product->max_term ?? 0,

                // Term calculations
                'requested_term' => $this->tenure ?? 0,
                'days_first_interest' => $this->daysBetweenx ?? 0,

                // Loan amount limits
                'requested_amount' => $this->principle ?? 0,
                'max_qualifying_amount' => $this->max_loan ?? 0,

                // Deductions and settlements
                'existing_settlements' => (float)($this->totalAmount ?? 0),
                'top_up_clearance' => (float)($this->topUpAmount ?? 0),
                'total_deductions' => (float)($this->totalAmount ?? 0) + (float)($this->topUpAmount ?? 0),
                'net_amount' => (float)($this->approved_loan_value ?? 0) - ((float)($this->totalAmount ?? 0) + (float)($this->topUpAmount ?? 0)),

                // Loan statistics
                'total_repayment' => (float)($this->monthlyInstallmentValue ?? 0) * (int)($this->approved_term ?? 0),
                'total_interest' => ((float)($this->monthlyInstallmentValue ?? 0) * (int)($this->approved_term ?? 0)) - (float)($this->approved_loan_value ?? 0),

                // Assessment scores
                'overall_score' => $this->assessmentScore['overall_score'] ?? 0,
                'credit_score' => $this->assessmentScore['credit_score'] ?? 0,
                'income_score' => $this->assessmentScore['income_score'] ?? 0,
                'member_activeness' => $this->assessmentScore['member_activeness'] ?? 0,
                'collateral_score' => $this->assessmentScore['collateral_score'] ?? 0,
                'ltv_score' => $this->assessmentScore['ltv_score'] ?? 0,
                'affordability_score' => $this->assessmentScore['affordability_score'] ?? 0,

                // Recommendations
                'recommendations' => $this->assessmentScore['recommendations'] ?? [],

                // Exceptions
                'exception_data' => $this->exceptionData ?? [],
                'requires_exception' => !empty($this->exceptionData),

                // Top-up information
                'selectedLoan' => $this->selectedLoan ?? null,
                'top_up_amount' => $this->topUpAmount ?? 0,

                // Restructure information
                'restructured_loan' => $this->restructuredLoan ?? null,
                'restructure_amount' => $this->restructureAmount ?? 0,

                // Settlement information
                'settlements' => $this->settlementData ?? [],
                'loan_schedule' => $this->schedule ?? [],

                // Deduction breakdown for persistence
                'deductionBreakdown' => [
                    'first_interest' => (float)($this->firstInstallmentInterestAmount ?? 0),
                    'management_fee' => (float)($this->totalCharges ?? 0),
                    'top_up_balance' => (float)($this->topUpAmount ?? 0),
                    'early_settlement_penalty' => (float)($this->topUpAmount ?? 0) * 0.05, // 5% of top-up amount
                    'total_deductions' => (float)($this->firstInstallmentInterestAmount ?? 0) + 
                                        (float)($this->totalCharges ?? 0) + 
                                        (float)($this->topUpAmount ?? 0) + 
                                        ((float)($this->topUpAmount ?? 0) * 0.05)
                ],
                'chargesBreakdown' => $this->chargesBreakdown ?? [],

                // Service data
                'credit_score_data' => $this->creditScoreData ?? [],
                'client_info_data' => $this->clientInfoData ?? [],
                'product_params_data' => $this->productParamsData ?? [],
                'nbc_loans_data' => $this->nbcLoansData ?? [],

                // Metadata
                'assessed_at' => now(),
                'assessed_by' => $user->id ?? null,
                'assessment_type' => !empty($this->exceptionData) ? 'with_exceptions' : 'standard',
                'last_updated' => now(),
                'version' => '1.0'
            ];

            Log::info('Comprehensive assessment data built successfully', [
                'loan_id' => $loanId,
                'data_keys' => array_keys($assessmentData),
                'overall_score' => $assessmentData['overall_score'],
                'approved_amount' => $assessmentData['approved_loan_value'],
                'monthly_installment' => $assessmentData['monthly_installment']
            ]);

            return $assessmentData;

        } catch (\Exception $e) {
            Log::error('Error building comprehensive assessment data: ' . $e->getMessage(), [
                'loan_id' => Session::get('currentloanID'),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return basic data structure if comprehensive build fails
            return [
                'approved_loan_value' => $this->approved_loan_value ?? 0,
                'approved_term' => $this->approved_term ?? 0,
                'monthly_installment' => $this->monthlyInstallmentValue ?? 0,
                'assessed_at' => now(),
                'assessed_by' => Auth::id(),
                'error' => 'Failed to build comprehensive data: ' . $e->getMessage()
            ];
        }
    }

    // Calculate the first interest amount of the first installment
    // Calculate the first interest amount of the first installment
    public function calculateFirstInterestAmount($principal, $monthlyInterestRate, $dayOfTheMonth) {
        try {
            if ($principal <= 0 || $monthlyInterestRate <= 0 || $dayOfTheMonth <= 0) {
                return 0;
            }
            
            // Get the current date (disbursement date)
            $disbursementDate = new DateTime(); // current date

            // Clone the current date to calculate the next drawdown date
            $nextDrawdownDate = clone $disbursementDate;

            // Set the day of the month for the drawdown date in the current month
            $nextDrawdownDate->setDate($disbursementDate->format('Y'), $disbursementDate->format('m'), $dayOfTheMonth);

            // Check if today's date equals the drawdown date, if yes, set daysBetween to 0
            if ($disbursementDate->format('Y-m-d') === $nextDrawdownDate->format('Y-m-d')) {
                $daysBetween = 0;
            } else {
                // If the drawdown date for this month has already passed, move to the next month
                if ($disbursementDate > $nextDrawdownDate) {
                    $nextDrawdownDate->modify('first day of next month');
                    $nextDrawdownDate->setDate($nextDrawdownDate->format('Y'), $nextDrawdownDate->format('m'), $dayOfTheMonth);
                }

                // Calculate the number of days between the disbursement date and the next drawdown date
                $daysBetween = $disbursementDate->diff($nextDrawdownDate)->days;
            }

            // Store the days between for debugging or further use
            $this->daysBetweenx = $daysBetween;

            // Get the number of days in the current month
            $daysInMonth = (int) $disbursementDate->format('t');

            // Calculate the daily interest rate based on the monthly interest rate
            $dailyInterestRate = $monthlyInterestRate / $daysInMonth;

            // Calculate the interest accrued for the days between
            $interestAccrued = $principal * $dailyInterestRate * $daysBetween;

            return $interestAccrued;
        } catch (\Exception $e) {
            Log::error('Error calculating first interest amount: ' . $e->getMessage());
            return 0;
        }
    }




    public function calculateTotal()
    {
        try {
            Log::info('=== CALCULATE TOTAL START ===');
            
            // Get all selected settlements from database
            $selectedSettlements = DB::table('settled_loans')
                ->where('loan_id', session('currentloanID'))
                ->where('is_selected', true)
                ->get();

            Log::info('Selected settlements for total calculation', [
                'count' => $selectedSettlements->count(),
                'settlements' => $selectedSettlements->toArray()
            ]);

            $totalAmount = 0;
            foreach ($selectedSettlements as $settlement) {
                $amount = (float)($settlement->amount ?? 0);
                $totalAmount += $amount;
                Log::info('Adding settlement to total', [
                    'settlement_id' => $settlement->loan_array_id,
                    'amount' => $amount,
                    'running_total' => $totalAmount
                ]);
            }

            $this->totalAmount = $totalAmount;
            
            // Update settlement data to reflect the new total
            if (isset($this->settlementData)) {
                $this->settlementData['total_amount'] = $totalAmount;
            }

            Log::info('=== CALCULATE TOTAL END ===', [
                'final_totalAmount' => $this->totalAmount,
                'final_settlementData_total' => $this->settlementData['total_amount'] ?? 0
            ]);

            // Auto-save assessment data after total calculation
            $this->autoSaveAssessmentData();

        } catch (\Exception $e) {
            Log::error('Error calculating total: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->totalAmount = 0;
        }
    }

    /**
     * Calculate deduction amounts for loan disbursement
     */
    public function calculateDeductionAmounts()
    {
        try {
            Log::info('=== CALCULATE DEDUCTION AMOUNTS START ===', [
                'product_exists' => isset($this->product),
                'approved_loan_value' => $this->approved_loan_value ?? 0,
                'totalAmount' => $this->totalAmount ?? 0,
                'settlementData_total' => $this->settlementData['total_amount'] ?? 0
            ]);

            // Calculate total charges
            if ($this->product && $this->approved_loan_value) {
                $this->totalCharges = $this->calculateLoanProductCharge($this->product->sub_product_id, (float)($this->approved_loan_value));
                Log::info('Calculated total charges', ['totalCharges' => $this->totalCharges]);
            } else {
                $this->totalCharges = 0;
                Log::info('No product or approved_loan_value, setting totalCharges to 0');
            }

            // Calculate total insurance
            if ($this->product && $this->approved_loan_value) {
                $this->totalInsurance = $this->calculateLoanProductInsurance($this->product->sub_product_id, (float)($this->approved_loan_value));
                Log::info('Calculated total insurance', ['totalInsurance' => $this->totalInsurance]);
            } else {
                $this->totalInsurance = 0;
                Log::info('No product or approved_loan_value, setting totalInsurance to 0');
            }

            // Calculate first installment interest amount
            if ($this->product && $this->approved_loan_value && $this->approved_term) {
                // Convert annual interest rate to monthly (same as Blade file)
                $annualInterestRate = (float)($this->product->interest_value ?? 0) / 100; // Convert percentage to decimal
                $monthlyInterestRate = $annualInterestRate / 12; // Convert annual to monthly
                
                // Get payroll date from member_group
                $dayOfTheMonth = 15; // Default value
                if (isset($this->member->client_number)) {
                    // Get client record
                    $client = DB::table('clients')->where('client_number', $this->member->client_number)->first();
                    if ($client && $client->member_group) {
                        // Get payroll date from member_groups table
                        $memberGroup = DB::table('member_groups')->where('group_id', $client->member_group)->first();
                        if ($memberGroup && $memberGroup->payrol_date) {
                            $dayOfTheMonth = (int)$memberGroup->payrol_date;
                        }
                    }
                }
                
                $this->firstInstallmentInterestAmount = $this->calculateFirstInterestAmount(
                    (float)($this->approved_loan_value), 
                    $monthlyInterestRate, 
                    $dayOfTheMonth
                );
                Log::info('Calculated first installment interest', [
                    'monthlyInterestRate' => $monthlyInterestRate,
                    'firstInstallmentInterestAmount' => $this->firstInstallmentInterestAmount
                ]);
            } else {
                $this->firstInstallmentInterestAmount = 0;
                Log::info('No product, approved_loan_value, or approved_term, setting firstInstallmentInterestAmount to 0');
            }

            Log::info('=== CALCULATE DEDUCTION AMOUNTS END ===', [
                'final_totalCharges' => $this->totalCharges,
                'final_totalInsurance' => $this->totalInsurance,
                'final_firstInstallmentInterestAmount' => $this->firstInstallmentInterestAmount,
                'totalAmount_for_deductions' => $this->totalAmount ?? 0
            ]);

            // Auto-save assessment data after deduction calculations
            $this->autoSaveAssessmentData();

        } catch (\Exception $e) {
            Log::error('Error calculating deduction amounts: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->totalCharges = 0;
            $this->totalInsurance = 0;
            $this->firstInstallmentInterestAmount = 0;
        }
    }

    /**
     * Handle top-up loan processing with enhanced business rules
     */
    public function topUp()
    {
        try {
            if (!$this->selectedLoan) {
                $this->errorMessage = 'Please select an existing loan for top-up.';
                return;
            }

            // Get the existing loan details
            $existingLoan = DB::table('loans')->where('id', $this->selectedLoan)->first();
            if (!$existingLoan) {
                $this->errorMessage = 'Selected loan not found.';
                return;
            }

            // Validate that the selected loan belongs to the same client
            if ($existingLoan->client_number !== $this->loan->client_number) {
                $this->errorMessage = 'Selected loan does not belong to this client.';
                return;
            }

            // Validate that the selected loan is active
            if ($existingLoan->status !== 'ACTIVE') {
                $this->errorMessage = 'Only active loans can be topped up.';
                return;
            }

            // Calculate outstanding balance from accounts
            $outstandingBalance = DB::table('accounts')
                ->where('account_number', $existingLoan->loan_account_number)
                ->value('balance') ?? 0;

            // Calculate loan age and apply penalty rules
            $loanAge = $this->calculateLoanAge($existingLoan);
            $penaltyAmount = 0;
            $penaltyApplied = false;

            // Apply 5% penalty if loan is less than 6 months old
            if ($loanAge < 6) {
                $penaltyAmount = $outstandingBalance * 0.05; // 5% penalty
                $penaltyApplied = true;
                
                Log::info('Top-up penalty applied', [
                    'loan_id' => $existingLoan->id,
                    'loan_age_months' => $loanAge,
                    'outstanding_balance' => $outstandingBalance,
                    'penalty_amount' => $penaltyAmount,
                    'penalty_percentage' => '5%'
                ]);
            }

            // Calculate total top-up amount (including penalty)
            $totalTopUpAmount = $outstandingBalance + $penaltyAmount;

            // Validate top-up eligibility based on payment performance
            $paymentPerformance = $this->assessTopUpEligibility($existingLoan);
            
            if (!$paymentPerformance['eligible']) {
                $this->errorMessage = 'Top-up not eligible: ' . $paymentPerformance['reason'];
                return;
            }

            // Set the top-up amount
            $this->topUpAmount = $totalTopUpAmount;

            // Get current assessment data
            $currentAssessmentData = $this->loan->assessment_data ? json_decode($this->loan->assessment_data, true) : [];
            
            // Update assessment data with enhanced top-up information
            $topUpData = [
                'top_up_loan_id' => $this->selectedLoan,
                'top_up_amount' => $totalTopUpAmount,
                'outstanding_balance' => $outstandingBalance,
                'penalty_amount' => $penaltyAmount,
                'penalty_applied' => $penaltyApplied,
                'loan_age_months' => $loanAge,
                'top_up_loan_account' => $existingLoan->loan_account_number,
                'top_up_loan_balance' => $existingLoan->principle ?? 0,
                'payment_performance' => $paymentPerformance,
                'top_up_processed_at' => now(),
                'top_up_processed_by' => Auth::id(),
                'loan_type' => 'TOPUP'
            ];

            // Merge with existing assessment data
            $updatedAssessmentData = array_merge($currentAssessmentData, $topUpData);

            // Update the current loan with top-up information
            DB::table('loans')
                ->where('id', Session::get('currentloanID'))
                ->update([
                    'selectedLoan' => $this->selectedLoan,
                    'top_up_amount' => $totalTopUpAmount,
                    'top_up_penalty_amount' => $penaltyAmount,
                    'loan_type_2' => 'TopUp',
                    'assessment_data' => json_encode($updatedAssessmentData)
                ]);

            // Log the top-up action
            Log::info('Top-up loan configured with enhanced rules', [
                'loan_id' => Session::get('currentloanID'),
                'top_up_loan_id' => $this->selectedLoan,
                'outstanding_balance' => $outstandingBalance,
                'penalty_amount' => $penaltyAmount,
                'total_top_up_amount' => $totalTopUpAmount,
                'loan_age_months' => $loanAge,
                'payment_performance' => $paymentPerformance,
                'user_id' => Auth::id()
            ]);

            $this->errorMessage = null;
            
            // Create success message with penalty information
            $successMessage = 'Top-up loan configured successfully. ';
            $successMessage .= 'Outstanding Balance: ' . number_format($outstandingBalance, 2) . ' TZS';
            
            if ($penaltyApplied) {
                $successMessage .= ' | Penalty (5%): ' . number_format($penaltyAmount, 2) . ' TZS';
                $successMessage .= ' | Total: ' . number_format($totalTopUpAmount, 2) . ' TZS';
            }
            
            session()->flash('message', $successMessage);
            
            // Auto-save assessment data after top-up configuration
            $this->autoSaveAssessmentData();
            
        } catch (\Exception $e) {
            Log::error('Error processing top-up: ' . $e->getMessage());
            $this->errorMessage = 'Error processing top-up loan. Please try again.';
        }
    }

    /**
     * Calculate loan age in months
     */
    private function calculateLoanAge($loan)
    {
        try {
            $disbursementDate = $loan->disbursement_date ?? $loan->created_at;
            $disbursementDate = \Carbon\Carbon::parse($disbursementDate);
            $currentDate = \Carbon\Carbon::now();
            
            return $disbursementDate->diffInMonths($currentDate);
        } catch (\Exception $e) {
            Log::error('Error calculating loan age: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Assess top-up eligibility based on payment performance
     */
    private function assessTopUpEligibility($loan)
    {
        try {
            // Get loan schedule data
            $loanSchedules = DB::table('loans_schedules')
                ->where('loan_id', $loan->loan_id ?? $loan->id)
                ->get();

            if ($loanSchedules->isEmpty()) {
                return [
                    'eligible' => false,
                    'reason' => 'No payment history found',
                    'overdue_installments' => 0,
                    'total_arrears' => 0,
                    'payment_ratio' => 0
                ];
            }

            $totalInstallments = $loanSchedules->count();
            $paidInstallments = $loanSchedules->where('completion_status', 'CLOSED')->count();
            $overdueInstallments = $loanSchedules->where('days_in_arrears', '>', 0)->count();
            $totalArrears = $loanSchedules->sum('amount_in_arrears');
            $paymentRatio = $totalInstallments > 0 ? ($paidInstallments / $totalInstallments) * 100 : 0;

            // Eligibility criteria
            $eligible = true;
            $reason = '';

            // Check for excessive overdue installments
            if ($overdueInstallments > 3) {
                $eligible = false;
                $reason = 'Too many overdue installments (' . $overdueInstallments . ')';
            }

            // Check for high arrears amount
            if ($totalArrears > 100000) { // 100,000 TZS threshold
                $eligible = false;
                $reason = 'High arrears amount (' . number_format($totalArrears, 2) . ' TZS)';
            }

            // Check payment ratio
            if ($paymentRatio < 70) { // Less than 70% payment ratio
                $eligible = false;
                $reason = 'Poor payment history (' . number_format($paymentRatio, 1) . '% payments made)';
            }

            return [
                'eligible' => $eligible,
                'reason' => $reason,
                'overdue_installments' => $overdueInstallments,
                'total_arrears' => $totalArrears,
                'payment_ratio' => $paymentRatio,
                'total_installments' => $totalInstallments,
                'paid_installments' => $paidInstallments
            ];

        } catch (\Exception $e) {
            Log::error('Error assessing top-up eligibility: ' . $e->getMessage());
            return [
                'eligible' => false,
                'reason' => 'Error assessing eligibility',
                'overdue_installments' => 0,
                'total_arrears' => 0,
                'payment_ratio' => 0
            ];
        }
    }

    /**
     * Handle loan restructuring
     */
    public function restructure()
    {
        try {
            if (!$this->selectedLoan) {
                $this->errorMessage = 'Please select an existing loan for restructuring.';
                return;
            }

            // Get the existing loan details
            $existingLoan = DB::table('loans')->where('id', $this->selectedLoan)->first();
            if (!$existingLoan) {
                $this->errorMessage = 'Selected loan not found.';
                return;
            }

            // Validate that the selected loan belongs to the same client
            if ($existingLoan->client_number !== $this->loan->client_number) {
                $this->errorMessage = 'Selected loan does not belong to this client.';
                return;
            }

            // Validate that the selected loan is active
            if ($existingLoan->status !== 'ACTIVE') {
                $this->errorMessage = 'Only active loans can be restructured.';
                return;
            }

            // Get the current loan balance from accounts
            $currentBalance = DB::table('accounts')
                ->where('account_number', $existingLoan->loan_account_number)
                ->value('balance') ?? 0;

            // Set the restructured amount to the current balance
            $this->approved_loan_value = $currentBalance;

            // Get current assessment data
            $currentAssessmentData = $this->loan->assessment_data ? json_decode($this->loan->assessment_data, true) : [];
            
            // Update assessment data with restructuring information
            $restructureData = [
                'restructure_loan_id' => $this->selectedLoan,
                'restructure_amount' => $currentBalance,
                'restructure_loan_account' => $existingLoan->loan_account_number,
                'restructure_original_balance' => $existingLoan->principle ?? 0,
                'restructure_processed_at' => now(),
                'restructure_processed_by' => Auth::id(),
                'loan_type' => 'RESTRUCTURED',
                'approved_loan_value' => $currentBalance // Update the approved amount
            ];

            // Merge with existing assessment data
            $updatedAssessmentData = array_merge($currentAssessmentData, $restructureData);

            // Update the current loan with restructuring information
            DB::table('loans')
                ->where('id', Session::get('currentloanID'))
                ->update([
                    'selectedLoan' => $this->selectedLoan,
                    'approved_loan_value' => $currentBalance,
                    'loan_type_2' => 'Restructured',
                    'assessment_data' => json_encode($updatedAssessmentData)
                ]);

            // Log the restructuring action
            Log::info('Loan restructuring configured', [
                'loan_id' => Session::get('currentloanID'),
                'restructured_loan_id' => $this->selectedLoan,
                'restructure_amount' => $currentBalance,
                'user_id' => Auth::id(),
                'assessment_data' => $restructureData
            ]);

            $this->errorMessage = null;
            session()->flash('message', 'Loan restructuring configured successfully. Restructured amount: ' . number_format($currentBalance, 2) . ' TZS');
            
            // Auto-save assessment data after restructure configuration
            $this->autoSaveAssessmentData();
            
        } catch (\Exception $e) {
            Log::error('Error processing loan restructuring: ' . $e->getMessage());
            $this->errorMessage = 'Error processing loan restructuring. Please try again.';
        }
    }

    function getStage($loan_id)
    {

        $loan = DB::table('loans')->where('id', $loan_id)->first();
        $product_id = $loan->loan_sub_product;
        $stage_id = $loan->stage_id;
        $loan_product_id = DB::table('loan_sub_products')->where('sub_product_id', $product_id)->value('id');
        $current_stage = DB::table('loan_stages')->where('committee_id', $stage_id)
            ->where('loan_product_id', $loan_product_id)->value('stage_id');

        $next_statge = $current_stage + 1;

        if (DB::table('loan_stages')->where('stage_id', $next_statge)
            ->where('loan_product_id', $loan_product_id)->exists()
        ) {

            $committee_id = DB::table('loan_stages')->where('stage_id', $next_statge)
                ->where('loan_product_id', $loan_product_id)->value('committee_id');
        } else {
            $committee_id = "AWAITING_APPROVAL";
        }

        return $committee_id;
    }


    function getBackStage($loan_id)
    {

        $loan = DB::table('loans')->where('id', $loan_id)->first();
        $product_id = $loan->loan_sub_product;
        $stage_id = $loan->stage_id;
        $loan_product_id = DB::table('loan_sub_products')->where('sub_product_id', $product_id)->value('id');
        $current_stage = DB::table('loan_stages')->where('committee_id', $stage_id)
            ->where('loan_product_id', $loan_product_id)->value('stage_id');

        $next_statge = $current_stage - 1;

        if (DB::table('loan_stages')->where('stage_id', $next_statge)
            ->where('loan_product_id', $loan_product_id)->exists()
        ) {

            $committee_id = DB::table('loan_stages')->where('stage_id', $next_statge)
                ->where('loan_product_id', $loan_product_id)->value('committee_id');
        } else {
            $committee_id = $stage_id;
        }

        return $committee_id;
    }



    public function commit()
    {
        $currentStage = Session::get('LoanStage');
        //dd($currentStage);
        $status = 'ONPROGRESS';
        if ($currentStage == 'ONPROGRESS') {
            $status = 'BRANCH COMMITTEE';
        }
        if ($currentStage == 'BRANCH COMMITTEE') {
            $status = 'CREDIT ANALYST';
        }
        if ($currentStage == 'CREDIT ANALYST') {
            $status = 'HQ COMMITTEE';
        }
        if ($currentStage == 'HQ COMMITTEE') {
            $status = 'CREDIT ADMINISTRATION';
        }
        if ($currentStage == 'CREDIT ADMINISTRATION') {
            $status = 'AWAITING DISBURSEMENT';
        }

        $stage= $this->getStage(Session::get('currentloanID'));

        if($stage=="AWAITING_APPROVAL"){

            $status=$stage;

        }else{
            $stage_id=$stage;
        }


        $data = [
            'principle'        => $this->principle,
            'interest'         => $this->interest,
            'tenure'           => $this->recommended ? $this->recommended_tenure : $this->tenure,
            'available_funds'  => $this->available_funds,
            'status'           => $status,
            'interest_method'  => $this->interest_method,
        ];

        // Check if stage_id is numeric
        if (is_numeric($stage_id)) {
            // Fetch the stage name from the database
            $stage_name = DB::table('committees')->where('id', $stage_id)->value('name');

            // Merge additional data into the existing array
            $data = array_merge($data, [
                'stage_id' => $stage_id,
                'stage'    => $stage_name,
            ]);
        }


        LoansModel::where('id', Session::get('currentloanID'))->update($data);
        Session::flash('loan_commit', 'The loan has been committed!');
        Session::flash('alert-class', 'alert-success');
        Session::put('currentloanID', null);
        Session::put('currentloanClient', null);
        $this->emit('currentloanID');
    }


    function returnBack(){

        $stage_id= $this->getBackStage(Session::get('currentloanID'));
        $data = [
            'principle' => $this->principle,
            'interest' => $this->interest,
            'tenure' => $this->recommended ? $this->recommended_tenure : $this->tenure,
            'available_funds' => $this->available_funds,
            'status' =>DB::table('committees')->where('id',$stage_id)->value('name'),
            'interest_method' => $this->interest_method,
            'stage_id'=>$stage_id,
            'stage'=>DB::table('committees')->where('id',$stage_id)->value('name'),
        ];

        LoansModel::where('id', Session::get('currentloanID'))->update($data);
        Session::flash('loan_commit', 'The loan has been committed!');
        Session::flash('alert-class', 'alert-success');
        Session::put('currentloanID', null);
        Session::put('currentloanClient', null);
        $this->emit('currentloanID');

    }

    function rejectLoan(){

        $data = [
            'principle' => $this->principle,
            'interest' => $this->interest,
            'tenure' => $this->recommended ? $this->recommended_tenure : $this->tenure,
            'available_funds' => $this->available_funds,
            'status' =>"REJECTED",
            'interest_method' => $this->interest_method,
        ];

        LoansModel::where('id', Session::get('currentloanID'))->update($data);
        Session::flash('loan_commit', 'The loan has been committed!');
        Session::flash('alert-class', 'alert-success');
        Session::put('currentloanID', null);
        Session::put('currentloanClient', null);
        $this->emit('currentloanID');
    }

    function approveLoanxx(){


        $loan = LoansModel::find(Session::get('currentloanID'));
        $status = $loan->status;

        $currentLoanID = Session::get('currentloanID');

        // Fetch all loan stages for the current loan ID
        $current_loans_stages = DB::table('current_loans_stages')
            ->where('loan_id', $currentLoanID)
            ->get();

        // Iterate through each loan stage
        foreach ($current_loans_stages as $stageIndex => $stage) {
            // Check if the current stage's status matches the loan's status
            if ($status == $stage->stage_name) {
                // Get the current stage ID
                $currentStageID = $stage->id;

                // Fetch the next record in current_loans_stages after the current stage
                $nextStage = DB::table('current_loans_stages')
                    ->where('loan_id', $currentLoanID)
                    ->where('id', '>', $currentStageID) // Find the next stage with a greater ID
                    ->orderBy('id', 'asc') // Ensure the next stage is the one with the next higher ID
                    ->first();

                // Check if the next stage exists
                if ($nextStage) {
                    $nextStageID = $nextStage->id; // Get the next stage ID

                    $stage_name = DB::table('current_loans_stages')
                        ->where('id', $nextStageID) // Find the next stage with a greater ID
                        ->orderBy('id', 'asc') // Ensure the next stage is the one with the next higher ID
                        ->value('stage_name');



                        //dd($currentLoanID,$status,auth()->user()->id);

/////////////////////HAPAAAAAAAAAAAAAAAAAAAA
///
                        if($stage->stage_type == 'Committee'){
                            DB::table('approvers_of_loans_stages')
                                ->where('loan_id', $currentLoanID)
                                ->where('stage_name', $status)
                                ->where('user_id', auth()->user()->id) // Find the next stage with a greater ID
                                ->update([
                                    'status' => 'APPROVED'
                                ]);

                            $currentLoanID = Session::get('currentloanID');

                            // Fetch all loan stages for the current loan ID
                            $current_loans_stages = DB::table('approvers_of_loans_stages')
                                ->where('loan_id', $currentLoanID)
                                ->where('stage_name', $status)
                                ->get();

                            // Fetch all loan stages where status is 'APPROVED' for the current loan ID
                            $approved_loans_stages = DB::table('approvers_of_loans_stages')
                                ->where('loan_id', $currentLoanID)
                                ->where('stage_name', $status)
                                ->where('status', 'APPROVED')
                                ->get();

                            // Check if the count of approved stages equals the count of all stages
                            if ($current_loans_stages->count() == $approved_loans_stages->count()) {
                                //dd($stage_name);
                                LoansModel::where('id', Session::get('currentloanID'))
                                    ->update([
                                        'status' => $stage_name
                                    ]);
                            } else {
                                // Not all loan stages are approved
                                // Handle this case accordingly
                            }



                        }else{
                            DB::table('approvers_of_loans_stages')
                                ->where('loan_id', $currentLoanID)
                                ->where('stage_name', $status)
                                ->where('user_id', null) // Find the next stage with a greater ID
                                ->update([
                                    'status' => 'APPROVED',
                                    'user_id' => auth()->user()->id,
                                    'user_name' => auth()->user()->name
                                ]);

                            LoansModel::where('id', Session::get('currentloanID'))
                                ->update([
                                    'status' => $stage_name
                                ]);

                        }


                    Session::flash('loan_commit', 'The loan has been committed!');
                    Session::flash('alert-class', 'alert-success');
                    //Session::put('currentloanID', null);
                    //Session::put('currentloanClient', null);
                    $this->emit('currentloanID');

                } else {
                    // hapaa echo "No next stage found.";
                }
            }
        }



        //LoansModel::where('id', Session::get('currentloanID'))->update($data);


    }


    function approveLoan($health) {
        // Retrieve the loan and its current status
        $loan = LoansModel::find(Session::get('currentloanID'));
        $status = $loan->status;
        $currentLoanID = Session::get('currentloanID');

        // Fetch all loan stages for the current loan ID
        $current_loans_stages = DB::table('current_loans_stages')
            ->where('loan_id', $currentLoanID)
            ->get();

        // Iterate through each loan stage
        foreach ($current_loans_stages as $stage) {
            // Check if the current stage's status matches the loan's status
            if ($status == $stage->stage_name) {
                // Get the current stage ID
                $currentStageID = $stage->id;

                // Fetch the next stage after the current one
                $nextStage = DB::table('current_loans_stages')
                    ->where('loan_id', $currentLoanID)
                    ->where('id', '>', $currentStageID) // Find the next stage with a greater ID
                    ->orderBy('id', 'asc') // Ensure the next stage is the one with the next higher ID
                    ->first();

                // Handle missing next stage (loan might be completed)
                if (!$nextStage) {

                    if ($stage->stage_type == 'Committee') {

                        // Fetch all stages for the current loan and check if all are approved
                        $allStages = DB::table('approvers_of_loans_stages')
                            ->where('loan_id', $currentLoanID)
                            ->where('stage_name', $status)
                            ->get();

                        $approvedStages = DB::table('approvers_of_loans_stages')
                            ->where('loan_id', $currentLoanID)
                            ->where('stage_name', $status)
                            ->where('status', 'APPROVED')
                            ->get();

                        DB::table('approvers_of_loans_stages')
                            ->where('loan_id', $currentLoanID)
                            ->where('stage_name', $status)
                            ->where('user_id', auth()->user()->id) // Example approver ID
                            ->update([
                                'status' => 'APPROVED'
                            ]);

                        if ($allStages->count() == ($approvedStages->count() + 1)) {
                            LoansModel::where('id', $currentLoanID)
                                ->update(['status' => 'ACCOUNTING']);
                        }else{

                        }
                    }else{
                        //dd('hh');
                        DB::table('approvers_of_loans_stages')
                            ->where('loan_id', $currentLoanID)
                            ->where('stage_name', $status)
                            ->where('user_id', null) // Example approver ID
                            ->update([
                                'status' => 'APPROVED',
                                'user_id' => auth()->user()->id,
                                'user_name' => auth()->user()->name

                            ]);

                        LoansModel::where('id', $currentLoanID)
                            ->update(['status' => 'ACCOUNTING']); // Mark loan as completed
                        Session::flash('loan_commit', 'Loan has been completed!');
                        Session::flash('alert-class', 'alert-success');
                    }




                    return;
                }

                // Get the next stage name
                $nextStageName = $nextStage->stage_name;

                // Handle 'Committee' stage type
                if ($stage->stage_type == 'Committee') {
                    // Update approval status for the current stage
                    DB::table('approvers_of_loans_stages')
                        ->where('loan_id', $currentLoanID)
                        ->where('stage_name', $status)
                        ->where('user_id', auth()->user()->id) // Example approver ID
                        ->update([
                            'status' => 'APPROVED'
                        ]);

                    // Fetch all stages for the current loan and check if all are approved
                    $allStages = DB::table('approvers_of_loans_stages')
                        ->where('loan_id', $currentLoanID)
                        ->where('stage_name', $status)
                        ->get();

                    $approvedStages = DB::table('approvers_of_loans_stages')
                        ->where('loan_id', $currentLoanID)
                        ->where('stage_name', $status)
                        ->where('status', 'APPROVED')
                        ->get();

                    // If all stages are approved, update loan status to the next stage
                    if ($allStages->count() == $approvedStages->count()) {
                        LoansModel::where('id', $currentLoanID)
                            ->update(['status' => $nextStageName]);
                    } else {
                        // If not all are approved, handle the case as necessary
                        // You could flash a message or take another action
                        Session::flash('loan_commit', 'Loan not fully approved yet.');
                        Session::flash('alert-class', 'alert-warning');
                    }
                } else {
                    // Handle non-Committee stages: approve stage and update loan status
                    DB::table('approvers_of_loans_stages')
                        ->where('loan_id', $currentLoanID)
                        ->where('stage_name', $status)
                        ->where('user_id', null) // Find approver-less stage
                        ->update([
                            'status' => 'APPROVED',
                            'user_id' => auth()->user()->id,
                            'user_name' => auth()->user()->name
                        ]);

                    // Move the loan to the next stage
                    LoansModel::where('id', $currentLoanID)
                        ->update(['status' => $nextStageName]);
                }

                LoansModel::where('id', $currentLoanID)
                    ->update(['heath' => $health]);

                // Flash success message and emit event
                Session::flash('loan_commit', 'The loan has been committed!');
                Session::flash('alert-class', 'alert-success');
                $this->emit('currentloanID');
            }
        }
    }



    public function updatedFutureInsteresAmount()
    {
        try {
            if ($this->futureInsteresAmount > $this->valueAmmount) {
                return $this->futureInsteresAmount = round($this->valueAmmount, 2);
            }
            return $this->futureInsteresAmount;
        } catch (\Exception $e) {
            Log::error('Error updating future interest amount: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Specific update handlers for exception-triggering fields
     */
    public function updatedApprovedLoanValue($value)
    {
        Log::info('Approved loan value updated', ['new_value' => $value]);
        $this->recalculateExceptions();
    }
    
    public function updatedApprovedTerm($value)
    {
        Log::info('Approved term updated', ['new_value' => $value]);
        $this->recalculateExceptions();
    }
    
    public function updatedTakeHome($value)
    {
        Log::info('Take home updated', ['new_value' => $value]);
        $this->recalculateExceptions();
    }
    
    public function updatedCollateralValue($value)
    {
        Log::info('Collateral value updated', ['new_value' => $value]);
        $this->recalculateExceptions();
    }
    
    public function updatedIsPhysicalCollateral($value)
    {
        Log::info('Is physical collateral updated', ['new_value' => $value]);
        $this->recalculateExceptions();
    }

    public function closeLoan()
    {
        try {
            $loan_data = LoansModel::where('id', Session::get('currentloanID'))->first();
            if ($loan_data) {
                LoansModel::where('id', Session::get('currentloanID'))->update(['status' => "CLOSED"]);

                if ($this->future_interests) {
                    $this->handleFutureInterests($loan_data);
                } else {
                    $this->emit('refreshAssessment');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error closing loan: ' . $e->getMessage());
            $this->errorMessage = 'Error closing loan. Please try again.';
        }
    }

    private function handleFutureInterests($loan_data)
    {
        try {
            $total_principle_amount = loans_schedules::where('loan_id', $loan_data->loan_id)
                ->where('installment_date', '>', Carbon::today()->format('Y-m-d'))
                ->sum('principle');

            $total_interest_amount = loans_schedules::where('loan_id', $loan_data->loan_id)
                ->where('installment_date', '>', Carbon::today()->format('Y-m-d'))
                ->sum('interest');

            $principle_collection_account = $this->getCollectionAccountDetails($loan_data->loan_sub_product, 'collection_account_loan_principle');
            $interest_collection_account = $this->getCollectionAccountDetails($loan_data->loan_sub_product, 'collection_account_loan_interest');

            general_ledger::create($this->createLedgerData($loan_data, $total_principle_amount, $total_interest_amount, $principle_collection_account, $interest_collection_account));

            loans_schedules::where('loan_id', $loan_data->loan_id)->where('installment_date', '>', Carbon::today()->format('Y-m-d'))->delete();

            $this->emit('refreshAssessment');
        } catch (\Exception $e) {
            Log::error('Error handling future interests: ' . $e->getMessage());
            $this->errorMessage = 'Error handling future interests. Please try again.';
        }
    }

    private function getCollectionAccountDetails($loan_sub_product, $account_type)
    {
        try {
            $product = Loan_sub_products::where('sub_product_id', $loan_sub_product)->first();
            return $product ? $product[$account_type] : null;
        } catch (\Exception $e) {
            Log::error('Error getting collection account details: ' . $e->getMessage());
            return null;
        }
    }

    private function createLedgerData($loan_data, $total_principle_amount, $total_interest_amount, $principle_collection_account, $interest_collection_account)
    {
        try {
            return [
                [
                    'gl_code' => $principle_collection_account,
                    'description' => 'LOAN PRINCIPLE RECEIVED ON CLOSURE',
                    'narrative' => 'LOAN PRINCIPLE RECEIVED ON CLOSURE',
                    'debit' => $total_principle_amount,
                    'credit' => 0,
                    'branch' => $loan_data->branch,
                    'date' => Carbon::now(),
                    'teller' => Auth::user()->id,
                ],
                [
                    'gl_code' => $interest_collection_account,
                    'description' => 'LOAN INTEREST RECEIVED ON CLOSURE',
                    'narrative' => 'LOAN INTEREST RECEIVED ON CLOSURE',
                    'debit' => $total_interest_amount,
                    'credit' => 0,
                    'branch' => $loan_data->branch,
                    'date' => Carbon::now(),
                    'teller' => Auth::user()->id,
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error creating ledger data: ' . $e->getMessage());
            return [];
        }
    }




    public function approve()
    {


        // CREATE LOAN ACCOUNT
        $loan =  LoansModel::where('id', session()->get('currentloanID'))->first();


        // $client_email = MembersModel::where('client_number', $loan->member_number)->first();
        // // $client_name = $client_email->first_name . ' ' . $client_email->middle_name . ' ' . $client_email->last_name;
        // // $officer_phone_number = Employee::where('id', $client_email->loan_officer)->value('email');

        //        Mail::to($client_email->email)->send(new LoanProgress($officer_phone_number,$client_name,'Your loan has been approved! We are now finalizing the disbursement process'));
        if (LoansModel::where('id', session()->get('currentloanID'))->value('loan_status') == "RESTRUCTURED") {

            loans_schedules::where('loan_id', $loan->restructure_loanId)->where('completion_status', 'ACTIVE')->update([
                'completion_status' => 'CLOSED',
                'status' => 'CLOSED'
            ]);


            //  LoansModel::where('id',session()->get('currentloanID'))->update(['status'=>"CLOSED"]);
            // source account number

            $next_due_date = Carbon::now()->toDateTimeString();

            foreach ($this->table as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date . ' +30 days'));
                $product = new loans_schedules;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                $product->installment_date = $next_due_date;
                // $product->save();
            }

            foreach ($this->tablefooter as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date . ' +30 days'));
                $product = new loans_summary;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                //   $product->save();
            }



            LoansModel::where('id', Session::get('currentloanID'))->update([
                'status' => 'AWAITING DISBURSEMENT',
                //
            ]);
        } elseif (LoansModel::where('id', session()->get('currentloanID'))->value('loan_status') == "TOPUP") {
            // top up process here  TOPUP

            $loanValues = LoansModel::where('id', session()->get('currentloanID'))->where('loan_status', 'TOPUP')->first();


            //principle
            $prev_loan = $loanValues->restructure_loanId;
            // close loan
            LoansModel::where('loan_id', $loanValues->restructure_loanId)->update(['status' => "CLOSED"]);

            // close installment
            loans_schedules::where('loan_id', $prev_loan)->update(['completion_status' => 'CLOSED']);

            $next_due_date = Carbon::now()->toDateTimeString();

            foreach ($this->table as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date . ' +30 days'));
                $product = new loans_schedules;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                $product->installment_date = $next_due_date;
                //   $product->save();
            }

            foreach ($this->tablefooter as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date . ' +30 days'));
                $product = new loans_summary;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                //   $product->save();
            }



            LoansModel::where('id', Session::get('currentloanID'))->update([
                'status' => 'AWAITING DISBURSEMENT',
                //
            ]);





            Session::flash('loan_commit', 'The loan has been Approved!');
            Session::flash('alert-class', 'alert-success');

            Session::put('currentloanID', null);
            Session::put('currentloanClient', null);
            $this->emit('currentloanID');
        } else {

            $next_due_date = Carbon::now()->toDateTimeString();

            foreach ($this->table as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date . ' +30 days'));
                $product = new loans_schedules;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                $product->installment_date = $next_due_date;
                //  $product->save();
            }

            foreach ($this->tablefooter as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date . ' +30 days'));
                $product = new loans_summary;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                //   $product->save();
            }

            $currentStage = Session::get('LoanStage');
            //dd($currentStage);
            $status = 'ONPROGRESS';
            if ($currentStage == 'ONPROGRESS') {
                $status = 'BRANCH COMMITTEE';
            }
            if ($currentStage == 'BRANCH COMMITTEE') {
                $status = 'CREDIT ANALYST';
            }
            if ($currentStage == 'CREDIT ANALYST') {
                $status = 'HQ COMMITTEE';
            }
            if ($currentStage == 'HQ COMMITTEE') {
                $status = 'CREDIT ADMINISTRATION';
            }
            if ($currentStage == 'CREDIT ADMINISTRATION') {
                $status = 'ACTIVE';
            }

            LoansModel::where('id', Session::get('currentloanID'))->update([
                'status' => $status

            ]);
        }

        Session::flash('loan_commit', 'The loan has been Approved!');
        Session::flash('alert-class', 'alert-success');

        Session::put('currentloanID', null);
        Session::put('currentloanClient', null);
        $this->emit('currentloanID');
    }

    public function reject()
    {

        $currentStage = Session::get('LoanStage');
        $status = 'ONPROGRESS';
        if ($currentStage == 'PROGRESS') {
            $status = 'ONPROGRESS';
        }
        if ($currentStage == 'BRANCH COMMITTEE') {
            $status = 'ONPROGRESS';
        }
        if ($currentStage == 'CREDIT ANALYST') {
            $status = 'ONPROGRESS';
        }
        if ($currentStage == 'HQ COMMITTEE') {
            $status = 'ONPROGRESS';
        }
        if ($currentStage == 'CREDIT ADMINISTRATION') {
            $status = 'ONPROGRESS';
        }


        LoansModel::where('id', Session::get('currentloanID'))->update([
            'status' => $status
        ]);
        MembersModel::where('id', DB::table('loans')->where('id', Session::get('currentloanID'))->value('client_id'))->update([
            'client_status' => $status
        ]);

        Session::flash('loan_commit', 'The loan has been Rejected!');
        Session::flash('alert-class', 'alert-danger');

        Session::put('currentloanID', null);
        Session::put('currentloanClient', null);
        $this->emit('currentloanID');
    }

    public function disburse()
    {



        $this->emit('approvalAndDisburse', Session::get('currentloanID'));

        LoansModel::where('id', Session::get('currentloanID'))->update([
            'status' => 'AWAITING DISBURSEMENT',

        ]);

        $member_number = DB::table('loans')->where('id', Session::get('currentloanID'))->value('client_number');

        DB::table('clients')->where('client_number', $member_number)->update([
            'client_status' => 'ACTIVE',
        ]);


        Session::flash('loan_commit', 'The loan has been Approved!');
        Session::flash('alert-class', 'alert-success');

        Session::put('currentloanID', null);
        Session::put('currentloanClient', null);
        $this->emit('currentloanID');
    }

    /**
     * Check if loan has any exceptions that would prevent approval
     */
    public function hasExceptions()
    {
        if (!$this->exceptionData || !isset($this->exceptionData['summary'])) {
            return true; // Assume there are exceptions if data is not available
        }
        
        return $this->exceptionData['summary']['requires_exception'];
    }

    /**
     * Check if loan can be approved without exceptions
     */
    public function canApproveWithoutExceptions()
    {
        if (!$this->exceptionData || !isset($this->exceptionData['summary'])) {
            return false; // Cannot approve if data is not available
        }
        
        return $this->exceptionData['summary']['can_approve'];
    }

    /**
     * Add new settlement
     */
    public function addSettlement()
    {
        try {
            // Ensure settlement service is initialized
            if (!$this->settlementService) {
                $this->settlementService = new SettlementService(
                    Session::get('currentloanID'),
                    $this->loan->client_number ?? ''
                );
            }

            // Validate the form data
            $validation = $this->settlementService->validateSettlement($this->settlementForm);
            
            if (!$validation['is_valid']) {
                $this->errorMessage = implode(', ', $validation['errors']);
                return;
            }

            // Get next available slot
            $availableSlots = $this->settlementService->getAvailableSlots();
            if (empty($availableSlots)) {
                $this->errorMessage = 'Maximum number of settlements reached (5)';
                return;
            }

            $settlementId = $availableSlots[0];
            
            // Save the settlement
            $success = $this->settlementService->saveSettlement($settlementId, $this->settlementForm);
            
            if ($success) {
                // Refresh settlement data
                $this->settlementData = $this->settlementService->getSettlementData();
                
                // Reset form
                $this->resetSettlementForm();
                
                $this->errorMessage = null;
                
                // Auto-save assessment data after settlement addition
                $this->autoSaveAssessmentData();
            } else {
                $this->errorMessage = 'Error saving settlement. Please try again.';
            }
        } catch (\Exception $e) {
            Log::error('Error adding settlement: ' . $e->getMessage());
            $this->errorMessage = 'Error adding settlement. Please try again.';
        }
    }

    /**
     * Edit existing settlement
     */
    public function editSettlement($settlementId)
    {
        try {
            $settlement = collect($this->settlementData['settlements'])->firstWhere('id', $settlementId);
            
            if ($settlement) {
                $this->settlementForm = [
                    'institution' => $settlement['institution'],
                    'account' => $settlement['account'],
                    'amount' => $settlement['amount']
                ];
                $this->editingSettlementId = $settlementId;
                $this->showSettlementForm = true;
            }
        } catch (\Exception $e) {
            Log::error('Error editing settlement: ' . $e->getMessage());
            $this->errorMessage = 'Error loading settlement data. Please try again.';
        }
    }

    /**
     * Update existing settlement
     */
    public function updateSettlement()
    {
        try {
            if (!$this->editingSettlementId) {
                $this->errorMessage = 'No settlement selected for editing';
                return;
            }

            // Ensure settlement service is initialized
            if (!$this->settlementService) {
                $this->settlementService = new SettlementService(
                    Session::get('currentloanID'),
                    $this->loan->client_number ?? ''
                );
            }

            // Validate the form data
            $validation = $this->settlementService->validateSettlement($this->settlementForm);
            
            if (!$validation['is_valid']) {
                $this->errorMessage = implode(', ', $validation['errors']);
                return;
            }

            // Update the settlement
            $success = $this->settlementService->saveSettlement($this->editingSettlementId, $this->settlementForm);
            
            if ($success) {
                // Refresh settlement data
                $this->settlementData = $this->settlementService->getSettlementData();
                
                // Reset form
                $this->resetSettlementForm();
                
                $this->errorMessage = null;
                
                // Auto-save assessment data after settlement update
                $this->autoSaveAssessmentData();
            } else {
                $this->errorMessage = 'Error updating settlement. Please try again.';
            }
        } catch (\Exception $e) {
            Log::error('Error updating settlement: ' . $e->getMessage());
            $this->errorMessage = 'Error updating settlement. Please try again.';
        }
    }

    /**
     * Delete settlement
     */
    public function deleteSettlement($settlementId)
    {
        try {
            // Ensure settlement service is initialized
            if (!$this->settlementService) {
                $this->settlementService = new SettlementService(
                    Session::get('currentloanID'),
                    $this->loan->client_number ?? ''
                );
            }

            $success = $this->settlementService->deleteSettlement($settlementId);
            
            if ($success) {
                // Refresh settlement data
                $this->settlementData = $this->settlementService->getSettlementData();
                
                // Reset form if editing this settlement
                if ($this->editingSettlementId == $settlementId) {
                    $this->resetSettlementForm();
                }
                
                $this->errorMessage = null;
                
                // Auto-save assessment data after settlement deletion
                $this->autoSaveAssessmentData();
            } else {
                $this->errorMessage = 'Error deleting settlement. Please try again.';
            }
        } catch (\Exception $e) {
            Log::error('Error deleting settlement: ' . $e->getMessage());
            $this->errorMessage = 'Error deleting settlement. Please try again.';
        }
    }

    /**
     * Reset settlement form
     */
    public function resetSettlementForm()
    {
        $this->settlementForm = [
            'institution' => '',
            'account' => '',
            'amount' => 0
        ];
        $this->editingSettlementId = null;
        $this->showSettlementForm = false;
    }

    /**
     * Cancel settlement form
     */
    public function cancelSettlementForm()
    {
        $this->resetSettlementForm();
        $this->errorMessage = null;
    }

    /**
     * Clear settlement selection
     */
    public function clearSettlementSelection()
    {
        try {
            Log::info('=== CLEAR SETTLEMENT SELECTION START ===', [
                'current_selectedContracts' => $this->selectedContracts,
                'current_totalAmount' => $this->totalAmount ?? 0
            ]);

            // Clear all selected contracts
            $this->selectedContracts = [];

            // Update database to unselect all settlements
            DB::table('settled_loans')
                ->where('loan_id', session('currentloanID'))
                ->update(['is_selected' => false]);

            // Reset total amount
            $this->totalAmount = 0;

            // Recalculate deduction amounts
            $this->calculateDeductionAmounts();

            Log::info('=== CLEAR SETTLEMENT SELECTION END ===', [
                'final_selectedContracts' => $this->selectedContracts,
                'final_totalAmount' => $this->totalAmount ?? 0
            ]);

            $this->errorMessage = null;
        } catch (\Exception $e) {
            Log::error('Error clearing settlement selection: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorMessage = 'Error clearing selection. Please try again.';
        }
    }

    /**
     * Export settlement report
     */
    public function exportReport()
    {
        try {
            Log::info('=== EXPORT ASSESSMENT REPORT START ===', [
                'loan_id' => session('currentloanID'),
                'client_number' => $this->loan->client_number ?? '',
                'assessment_score' => $this->assessmentData['overall_score'] ?? 0
            ]);

            // Ensure we have the latest assessment data
            $this->calculateAssessmentScore();

            // Get all necessary data
            $loanId = session('currentloanID');
            $loan = $this->loan;
            $member = $this->member;
            $product = $this->product;
            $assessmentData = $this->assessmentData ?? [];
            $exceptionData = $this->exceptionData ?? [];
            $schedule = $this->schedule ?? [];
            $collateralDetails = $this->collateralDetails ?? [];
            $settlementData = $this->settlementService ? $this->settlementService->getSettlementData() : [];

            // Generate Excel file using PhpSpreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            
            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('NBC SACCO System')
                ->setLastModifiedBy('NBC SACCO System')
                ->setTitle('Loan Assessment Report - ' . $loanId)
                ->setSubject('Comprehensive Loan Assessment Report')
                ->setDescription('Detailed assessment report for loan application')
                ->setKeywords('loan assessment SACCO')
                ->setCategory('Loan Assessment');

            // Create multiple worksheets
            $this->createAssessmentSummarySheet($spreadsheet, $loan, $member, $product, $assessmentData);
            $this->createDetailedAssessmentSheet($spreadsheet, $assessmentData);
            $this->createFinancialAnalysisSheet($spreadsheet, $loan, $member, $product, $assessmentData);
            $this->createMemberInformationSheet($spreadsheet, $member, $loan);
            $this->createProductParametersSheet($spreadsheet, $product);
            $this->createExceptionsSheet($spreadsheet, $exceptionData);
            $this->createLoanScheduleSheet($spreadsheet, $schedule);
            $this->createCollateralSheet($spreadsheet, $collateralDetails);
            $this->createSettlementsSheet($spreadsheet, $settlementData);

            // Generate filename
            $filename = "loan_assessment_report_" . $loanId . "_" . date('Y-m-d_H-i-s') . ".xlsx";
            
            // Create writer and output file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Set headers for download
            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ];

            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'assessment_report_');
            $writer->save($tempFile);

            Log::info('=== EXPORT ASSESSMENT REPORT END ===', [
                'filename' => $filename,
                'temp_file' => $tempFile,
                'assessment_score' => $assessmentData['overall_score'] ?? 0
            ]);

            return response()->download($tempFile, $filename, $headers)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error exporting assessment report: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorMessage = 'Error exporting report. Please try again.';
            return null;
        }
    }

    /**
     * Create Assessment Summary Sheet
     */
    private function createAssessmentSummarySheet($spreadsheet, $loan, $member, $product, $assessmentData)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Executive Summary');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(25);

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $subHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $sectionHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '70AD47']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $riskStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C00000']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        // Report Header
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'LOAN COMMITTEE ASSESSMENT REPORT');
        $sheet->getStyle('A1')->applyFromArray($headerStyle);
        $sheet->getStyle('A1')->getFont()->setSize(18);

        // Report Subtitle
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'NBC SACCO - Loan Application Decision Document');
        $sheet->getStyle('A2')->applyFromArray($subHeaderStyle);
        $sheet->getStyle('A2')->getFont()->setSize(12);

        // Report Date
        $sheet->mergeCells('A3:F3');
        $sheet->setCellValue('A3', 'Report Generated: ' . now()->format('F j, Y \a\t g:i A'));
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Executive Summary
        $row = 5;
        $sheet->setCellValue('A' . $row, 'EXECUTIVE SUMMARY');
        $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $row++;

        // Key Decision Points
        $sheet->setCellValue('A' . $row, 'Loan ID');
        $sheet->setCellValue('B' . $row, $loan->id ?? 'N/A');
        $sheet->setCellValue('C' . $row, 'Client Number');
        $sheet->setCellValue('D' . $row, $loan->client_number ?? 'N/A');
        $sheet->setCellValue('E' . $row, 'Product');
        $sheet->setCellValue('F' . $row, $product->sub_product_name ?? 'N/A');
        $row++;

        $sheet->setCellValue('A' . $row, 'Applicant Name');
        $sheet->setCellValue('B' . $row, $member->first_name . ' ' . $member->last_name ?? 'N/A');
        $sheet->setCellValue('C' . $row, 'Employment Type');
        $sheet->setCellValue('D' . $row, $member->employment_type ?? 'N/A');
        $sheet->setCellValue('E' . $row, 'Monthly Income');
        $sheet->setCellValue('F' . $row, number_format($this->take_home ?? 0, 2) . ' TZS');
        $row++;

        $sheet->setCellValue('A' . $row, 'Requested Amount');
        $sheet->setCellValue('B' . $row, number_format($loan->principle ?? 0, 2) . ' TZS');
        $sheet->setCellValue('C' . $row, 'Recommended Amount');
        $sheet->setCellValue('D' . $row, number_format($this->approved_loan_value ?? 0, 2) . ' TZS');
        $sheet->setCellValue('E' . $row, 'Term (Months)');
        $sheet->setCellValue('F' . $row, ($this->approved_term ?? 0) . ' months');
        $row++;

        $sheet->setCellValue('A' . $row, 'Monthly Payment');
        $sheet->setCellValue('B' . $row, number_format($this->monthlyInstallmentValue ?? 0, 2) . ' TZS');
        $sheet->setCellValue('C' . $row, 'Interest Rate');
        $sheet->setCellValue('D' . $row, number_format($product->interest_value ?? 0, 2) . '%');
        $sheet->setCellValue('E' . $row, 'Assessment Score');
        $sheet->setCellValue('F' . $row, number_format($assessmentData['overall_score'] ?? 0, 1) . '%');
        $row++;

        // Risk Assessment
        $row += 2;
        $sheet->setCellValue('A' . $row, 'RISK ASSESSMENT');
        $sheet->getStyle('A' . $row)->applyFromArray($riskStyle);
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $row++;

        $riskLevel = $this->getRiskLevel($assessmentData['overall_score'] ?? 0);
        $riskColor = $this->getRiskColor($assessmentData['overall_score'] ?? 0);
        
        $sheet->setCellValue('A' . $row, 'Overall Risk Level');
        $sheet->setCellValue('B' . $row, $riskLevel);
        $sheet->getStyle('B' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('B' . $row)->getFill()->getStartColor()->setRGB($riskColor);
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('C' . $row, 'Credit Score');
        $sheet->setCellValue('D' . $row, $this->creditScoreData['score'] ?? 500 . ' (' . ($this->creditScoreData['grade'] ?? 'E') . ')');
        $sheet->setCellValue('E' . $row, 'Collateral Coverage');
        $sheet->setCellValue('F' . $row, $this->collateral_value ? number_format(($this->collateral_value / ($this->approved_loan_value ?? 1)) * 100, 1) . '%' : 'N/A');
        $row++;

        // Decision Matrix
        $row += 2;
        $sheet->setCellValue('A' . $row, 'DECISION MATRIX');
        $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $row++;

        // Headers for decision matrix
        $sheet->setCellValue('A' . $row, 'Assessment Criteria');
        $sheet->setCellValue('B' . $row, 'Score (%)');
        $sheet->setCellValue('C' . $row, 'Weight (%)');
        $sheet->setCellValue('D' . $row, 'Weighted Score');
        $sheet->setCellValue('E' . $row, 'Risk Level');
        $sheet->setCellValue('F' . $row, 'Recommendation');
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($subHeaderStyle);
        $row++;

        // Assessment details data
        $totalWeightedScore = 0;
        foreach ($assessmentData['details'] ?? [] as $detail) {
            $weightedScore = ($detail['score'] ?? 0) * ($detail['weight'] ?? 0) / 100;
            $totalWeightedScore += $weightedScore;
            
            $sheet->setCellValue('A' . $row, $detail['criterion'] ?? 'N/A');
            $sheet->setCellValue('B' . $row, number_format($detail['score'] ?? 0, 1));
            $sheet->setCellValue('C' . $row, $detail['weight'] ?? 0);
            $sheet->setCellValue('D' . $row, number_format($weightedScore, 1));
            $sheet->setCellValue('E' . $row, $this->getRiskLevel($detail['score'] ?? 0));
            $sheet->setCellValue('F' . $row, $detail['recommendation'] ?? 'N/A');
            $row++;
        }

        // Total row
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('B' . $row, number_format($assessmentData['overall_score'] ?? 0, 1));
        $sheet->setCellValue('C' . $row, '100');
        $sheet->setCellValue('D' . $row, number_format($totalWeightedScore, 1));
        $sheet->setCellValue('E' . $row, $riskLevel);
        $sheet->setCellValue('F' . $row, $this->getOverallRecommendation($assessmentData['overall_score'] ?? 0));
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->getStartColor()->setRGB('E7E6E6');
        $row++;

        // Key Recommendations
        $row += 2;
        $sheet->setCellValue('A' . $row, 'KEY RECOMMENDATIONS');
        $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $row++;

        if (!empty($assessmentData['recommendations'])) {
            foreach ($assessmentData['recommendations'] as $index => $recommendation) {
                $sheet->setCellValue('A' . $row, ($index + 1) . '.');
                $sheet->setCellValue('B' . $row, $recommendation['message'] ?? 'N/A');
                $sheet->setCellValue('E' . $row, 'Priority: ' . ($recommendation['priority'] ?? 'LOW'));
                $sheet->mergeCells('B' . $row . ':D' . $row);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No specific recommendations');
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $row++;
        }

        // Committee Decision Section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'LOAN COMMITTEE DECISION');
        $sheet->getStyle('A' . $row)->applyFromArray($headerStyle);
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, 'Decision Options:');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue('A' . $row, 'â˜ APPROVE as recommended');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->setCellValue('D' . $row, 'â˜ APPROVE with conditions');
        $sheet->mergeCells('D' . $row . ':F' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, 'â˜ DECLINE');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->setCellValue('D' . $row, 'â˜ REFER for additional review');
        $sheet->mergeCells('D' . $row . ':F' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, 'â˜ REQUEST additional documents');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $row++;

        // Conditions/Comments
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Special Conditions/Comments:');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        for ($i = 0; $i < 5; $i++) {
            $sheet->setCellValue('A' . $row, ($i + 1) . '.');
            $sheet->mergeCells('B' . $row . ':F' . $row);
            $sheet->getStyle('B' . $row . ':F' . $row)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $row++;
        }

        // Signature Section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'COMMITTEE SIGNATURES');
        $sheet->getStyle('A' . $row)->applyFromArray($headerStyle);
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $row++;

        // Signature headers
        $sheet->setCellValue('A' . $row, 'Committee Member');
        $sheet->setCellValue('B' . $row, 'Position');
        $sheet->setCellValue('C' . $row, 'Decision');
        $sheet->setCellValue('D' . $row, 'Signature');
        $sheet->setCellValue('E' . $row, 'Date');
        $sheet->setCellValue('F' . $row, 'Comments');
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($subHeaderStyle);
        $row++;

        // Signature rows
        for ($i = 0; $i < 6; $i++) {
            $sheet->setCellValue('A' . $row, ($i + 1) . '.');
            $sheet->setCellValue('B' . $row, '');
            $sheet->setCellValue('C' . $row, '');
            $sheet->setCellValue('D' . $row, '');
            $sheet->setCellValue('E' . $row, '');
            $sheet->setCellValue('F' . $row, '');
            
            // Add borders for signature areas
            $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->getStyle('D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle('D' . $row)->getFill()->getStartColor()->setRGB('F0F0F0');
            $row++;
        }

        // Final Approval
        $row += 2;
        $sheet->setCellValue('A' . $row, 'FINAL APPROVAL');
        $sheet->getStyle('A' . $row)->applyFromArray($headerStyle);
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, 'Chief Executive Officer:');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->setCellValue('D' . $row, '');
        $sheet->mergeCells('D' . $row . ':F' . $row);
        $sheet->getStyle('D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('D' . $row)->getFill()->getStartColor()->setRGB('F0F0F0');
        $sheet->getStyle('D' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $row++;

        $sheet->setCellValue('A' . $row, 'Date:');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->setCellValue('D' . $row, '');
        $sheet->mergeCells('D' . $row . ':F' . $row);
        $sheet->getStyle('D' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $row++;

        // Apply borders to all data
        $lastRow = $row - 1;
        $sheet->getStyle('A1:F' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    /**
     * Get risk level based on score
     */
    private function getRiskLevel($score)
    {
        if ($score >= 80) return 'LOW RISK';
        if ($score >= 60) return 'MEDIUM RISK';
        if ($score >= 40) return 'HIGH RISK';
        return 'VERY HIGH RISK';
    }

    /**
     * Get risk color based on score
     */
    private function getRiskColor($score)
    {
        if ($score >= 80) return '00B050'; // Green
        if ($score >= 60) return 'FFC000'; // Yellow
        if ($score >= 40) return 'FF6600'; // Orange
        return 'C00000'; // Red
    }

    /**
     * Get overall recommendation based on score
     */
    private function getOverallRecommendation($score)
    {
        if ($score >= 80) return 'APPROVE - Excellent application';
        if ($score >= 60) return 'APPROVE - Good application';
        if ($score >= 40) return 'CONDITIONAL APPROVAL - Requires monitoring';
        return 'DECLINE - High risk application';
    }

    /**
     * Create Detailed Assessment Sheet
     */
    private function createDetailedAssessmentSheet($spreadsheet, $assessmentData)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Detailed Assessment');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(40);

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        // Headers
        $sheet->setCellValue('A1', 'Criterion');
        $sheet->setCellValue('B1', 'Description');
        $sheet->setCellValue('C1', 'Score (%)');
        $sheet->setCellValue('D1', 'Weight (%)');
        $sheet->setCellValue('E1', 'Recommendation');
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($assessmentData['details'] ?? [] as $detail) {
            $sheet->setCellValue('A' . $row, $detail['criterion'] ?? 'N/A');
            $sheet->setCellValue('B' . $row, $detail['description'] ?? 'N/A');
            $sheet->setCellValue('C' . $row, number_format($detail['score'] ?? 0, 1));
            $sheet->setCellValue('D' . $row, $detail['weight'] ?? 0);
            $sheet->setCellValue('E' . $row, $detail['recommendation'] ?? 'N/A');
            $row++;
        }

        // Apply borders
        $lastRow = $row - 1;
        $sheet->getStyle('A1:E' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    /**
     * Create Member Information Sheet
     */
    private function createMemberInformationSheet($spreadsheet, $member, $loan)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Member Information');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '70AD47']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        // Headers
        $sheet->setCellValue('A1', 'Field');
        $sheet->setCellValue('B1', 'Value');
        $sheet->getStyle('A1:B1')->applyFromArray($headerStyle);

        $row = 2;
        $memberData = [
            'Client Number' => $member->client_number ?? 'N/A',
            'First Name' => $member->first_name ?? 'N/A',
            'Last Name' => $member->last_name ?? 'N/A',
            'Phone Number' => $member->phone_number ?? 'N/A',
            'Email' => $member->email ?? 'N/A',
            'Date of Birth' => $member->date_of_birth ?? 'N/A',
            'Gender' => $member->gender ?? 'N/A',
            'National ID' => $member->national_id ?? 'N/A',
            'Employment Type' => $member->employment_type ?? 'N/A',
            'Employer Name' => $member->employer_name ?? 'N/A',
            'Take Home Salary' => number_format($this->take_home ?? 0, 2) . ' TZS',
            'Address' => $member->address ?? 'N/A',
            'City' => $member->city ?? 'N/A',
            'Country' => $member->country ?? 'N/A',
            'Member Since' => $member->created_at ?? 'N/A',
            'Status' => $member->status ?? 'N/A'
        ];

        foreach ($memberData as $field => $value) {
            $sheet->setCellValue('A' . $row, $field);
            $sheet->setCellValue('B' . $row, $value);
            $row++;
        }

        // Apply borders
        $lastRow = $row - 1;
        $sheet->getStyle('A1:B' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    /**
     * Create Product Parameters Sheet
     */
    private function createProductParametersSheet($spreadsheet, $product)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Product Parameters');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC000']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        // Headers
        $sheet->setCellValue('A1', 'Parameter');
        $sheet->setCellValue('B1', 'Value');
        $sheet->getStyle('A1:B1')->applyFromArray($headerStyle);

        $row = 2;
        $productData = [
            'Product Name' => $product->sub_product_name ?? 'N/A',
            'Product Code' => $product->sub_product_code ?? 'N/A',
            'Annual Interest Rate' => number_format($product->interest_value ?? 0, 2) . '%',
            'Monthly Interest Rate' => number_format(($product->interest_value ?? 0) / 12, 2) . '%',
            'Minimum Amount' => number_format($product->principle_min_value ?? 0, 2) . ' TZS',
            'Maximum Amount' => number_format($product->principle_max_value ?? 0, 2) . ' TZS',
            'Minimum Term' => ($product->min_term ?? 0) . ' months',
            'Maximum Term' => ($product->max_term ?? 0) . ' months',
            'Grace Period' => ($product->principle_grace_period ?? 0) . ' months',
            'LTV Ratio' => ($product->ltv ?? 0) . '%',
            'Processing Fee' => number_format($product->processing_fee ?? 0, 2) . '%',
            'Insurance Fee' => number_format($product->insurance_fee ?? 0, 2) . '%',
            'Status' => $product->status ?? 'N/A'
        ];

        foreach ($productData as $parameter => $value) {
            $sheet->setCellValue('A' . $row, $parameter);
            $sheet->setCellValue('B' . $row, $value);
            $row++;
        }

        // Apply borders
        $lastRow = $row - 1;
        $sheet->getStyle('A1:B' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    /**
     * Create Exceptions Sheet
     */
    private function createExceptionsSheet($spreadsheet, $exceptionData)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Exceptions');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C00000']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        // Headers
        $sheet->setCellValue('A1', 'Exception Type');
        $sheet->setCellValue('B1', 'Description');
        $sheet->setCellValue('C1', 'Severity');
        $sheet->setCellValue('D1', 'Status');
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

        $row = 2;
        if (!empty($exceptionData)) {
            foreach ($exceptionData as $exception) {
                $sheet->setCellValue('A' . $row, $exception['type'] ?? 'N/A');
                $sheet->setCellValue('B' . $row, $exception['description'] ?? 'N/A');
                $sheet->setCellValue('C' . $row, $exception['severity'] ?? 'N/A');
                $sheet->setCellValue('D' . $row, $exception['status'] ?? 'N/A');
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No Exceptions');
            $sheet->setCellValue('B' . $row, 'This loan application has no exceptions');
            $sheet->mergeCells('A' . $row . ':D' . $row);
            $row++;
        }

        // Apply borders
        $lastRow = $row - 1;
        $sheet->getStyle('A1:D' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    /**
     * Create Loan Schedule Sheet
     */
    private function createLoanScheduleSheet($spreadsheet, $schedule)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Loan Schedule');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '7030A0']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        // Headers
        $sheet->setCellValue('A1', 'Payment #');
        $sheet->setCellValue('B1', 'Due Date');
        $sheet->setCellValue('C1', 'Principal');
        $sheet->setCellValue('D1', 'Interest');
        $sheet->setCellValue('E1', 'Total Payment');
        $sheet->setCellValue('F1', 'Remaining Balance');
        $sheet->setCellValue('G1', 'Status');
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        $row = 2;
        if (!empty($schedule)) {
            foreach ($schedule as $payment) {
                $sheet->setCellValue('A' . $row, $payment['payment_number'] ?? 'N/A');
                $sheet->setCellValue('B' . $row, $payment['due_date'] ?? 'N/A');
                $sheet->setCellValue('C' . $row, number_format($payment['principal'] ?? 0, 2));
                $sheet->setCellValue('D' . $row, number_format($payment['interest'] ?? 0, 2));
                $sheet->setCellValue('E' . $row, number_format($payment['total_payment'] ?? 0, 2));
                $sheet->setCellValue('F' . $row, number_format($payment['remaining_balance'] ?? 0, 2));
                $sheet->setCellValue('G' . $row, $payment['status'] ?? 'Pending');
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No Schedule');
            $sheet->setCellValue('B' . $row, 'Loan schedule not generated yet');
            $sheet->mergeCells('A' . $row . ':G' . $row);
            $row++;
        }

        // Apply borders
        $lastRow = $row - 1;
        $sheet->getStyle('A1:G' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    /**
     * Create Collateral Sheet
     */
    private function createCollateralSheet($spreadsheet, $collateralDetails)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Collateral');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(30);

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ED7D31']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        // Headers
        $sheet->setCellValue('A1', 'Type');
        $sheet->setCellValue('B1', 'Guarantor Type');
        $sheet->setCellValue('C1', 'Amount');
        $sheet->setCellValue('D1', 'Locked Amount');
        $sheet->setCellValue('E1', 'Description');
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

        $row = 2;
        if (!empty($collateralDetails)) {
            foreach ($collateralDetails as $collateral) {
                $sheet->setCellValue('A' . $row, $collateral['type'] ?? 'N/A');
                $sheet->setCellValue('B' . $row, $collateral['guarantor_type'] ?? 'N/A');
                $sheet->setCellValue('C' . $row, number_format($collateral['amount'] ?? 0, 2));
                $sheet->setCellValue('D' . $row, number_format($collateral['locked_amount'] ?? 0, 2));
                $sheet->setCellValue('E' . $row, $collateral['description'] ?? 'N/A');
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No Collateral');
            $sheet->setCellValue('B' . $row, 'This loan has no collateral');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $row++;
        }

        // Apply borders
        $lastRow = $row - 1;
        $sheet->getStyle('A1:E' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    /**
     * Create Settlements Sheet
     */
    private function createSettlementsSheet($spreadsheet, $settlementData)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Settlements');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(20);

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '00B050']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        // Headers
        $sheet->setCellValue('A1', 'Settlement ID');
        $sheet->setCellValue('B1', 'Institution');
        $sheet->setCellValue('C1', 'Account Number');
        $sheet->setCellValue('D1', 'Amount');
        $sheet->setCellValue('E1', 'Selected');
        $sheet->setCellValue('F1', 'Created Date');
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

        $row = 2;
        if (!empty($settlementData['settlements'])) {
            foreach ($settlementData['settlements'] as $settlement) {
                $sheet->setCellValue('A' . $row, $settlement['id'] ?? 'N/A');
                $sheet->setCellValue('B' . $row, $settlement['institution'] ?? 'N/A');
                $sheet->setCellValue('C' . $row, $settlement['account'] ?? 'N/A');
                $sheet->setCellValue('D' . $row, number_format($settlement['amount'] ?? 0, 2));
                $sheet->setCellValue('E' . $row, in_array($settlement['id'], $this->selectedContracts ?? []) ? 'Yes' : 'No');
                $sheet->setCellValue('F' . $row, $settlement['created_at'] ?? 'N/A');
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No Settlements');
            $sheet->setCellValue('B' . $row, 'No settlements available for this loan');
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $row++;
        }

        // Apply borders
        $lastRow = $row - 1;
        $sheet->getStyle('A1:F' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    /**
     * Get assessment status based on score
     */
    private function getAssessmentStatus($score)
    {
        if ($score >= 80) return 'Excellent';
        if ($score >= 60) return 'Good';
        if ($score >= 40) return 'Fair';
        return 'Poor';
    }

    /**
     * Get settlement summary
     */
    public function getSettlementSummary()
    {
        // Ensure settlement service is initialized
        if (!$this->settlementService) {
            $this->settlementService = new SettlementService(
                Session::get('currentloanID'),
                $this->loan->client_number ?? ''
            );
        }
        
        return $this->settlementService->getSettlementSummary();
    }

    /**
     * Get settlement statistics
     */
    public function getSettlementStatistics()
    {
        // Implementation for getting settlement statistics
            return [
            'total_settlements' => 0,
            'total_amount' => 0,
            'average_amount' => 0
        ];
    }

    // Action Methods for Approval Workflow
    public function sendForApproval()
    {
        $startTime = microtime(true);
        $logContext = [
            'method' => 'sendForApproval',
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
            'user_id' => Auth::id() ?? 'unknown',
            'user_name' => Auth::user()->name ?? 'unknown'
        ];

        Log::info('=== SEND FOR APPROVAL START ===', $logContext);

        try {
            // Step 1: Initialize processing state
            Log::info('Step 1: Initializing processing state', $logContext);
            $this->isProcessing = true;
            
            // Step 2: Get user and session data
            Log::info('Step 2: Retrieving user and session data', $logContext);
            $user = Auth::user();
            $loanId = session('currentloanID');
            
            Log::info('User and session data retrieved', array_merge($logContext, [
                'user_id' => $user->id ?? 'unknown',
                'user_name' => $user->name ?? 'unknown',
                'loan_id' => $loanId,
                'session_has_currentloanID' => session()->has('currentloanID')
            ]));

            // Step 3: Validate loan exists
            Log::info('Step 3: Validating loan exists', $logContext);
            $loan = LoansModel::find($loanId);

            if (!$loan) {
                Log::error('Loan not found in database', array_merge($logContext, [
                    'loan_id' => $loanId,
                    'error' => 'Loan not found'
                ]));
                throw new \Exception('Loan not found');
            }

            Log::info('Loan found successfully', array_merge($logContext, [
                'loan_id' => $loan->id,
                'loan_number' => $loan->loan_id,
                'client_number' => $loan->client_number,
                'loan_status' => $loan->status,
                'loan_amount' => $loan->principle
            ]));

            // Step 4: Get approval stage configuration
            Log::info('Step 4: Retrieving approval stage configuration', $logContext);
            $initialApprovalStage = 'first_checker';
            $initialApprovalStageRoleNames = [];

            try {
                $processConfig = DB::table('process_code_configs')
                    ->where('process_code', 'LOAN_DISB')
                    ->first();

                if (!$processConfig) {
                    Log::warning('Process code config not found', array_merge($logContext, [
                        'process_code' => 'LOAN_DISB',
                        'fallback_stage' => $initialApprovalStage
                    ]));
                } else {
                    Log::info('Process code config found', array_merge($logContext, [
                        'process_code' => $processConfig->process_code,
                        'first_checker_role_id' => $processConfig->first_checker_role_id
                    ]));

                    // Handle role IDs (could be single ID or JSON array)
                    $roleIds = $processConfig->first_checker_role_id;
                    
                    if (is_string($roleIds) && json_decode($roleIds) !== null) {
                        // JSON array of role IDs
                        $roleIds = json_decode($roleIds, true);
                        Log::info('Role IDs from JSON', array_merge($logContext, [
                            'role_ids' => $roleIds,
                            'role_ids_type' => 'json_array'
                        ]));
                    } else {
                        // Single role ID
                        $roleIds = [$roleIds];
                        Log::info('Single role ID', array_merge($logContext, [
                            'role_ids' => $roleIds,
                            'role_ids_type' => 'single_id'
                        ]));
                    }

                    $roleNames = [];
                    foreach ($roleIds as $roleId) {
                        try {
                            $role = DB::table('roles')->where('id', $roleId)->first();
                            if ($role) {
                                $roleNames[] = $role->name;
                                Log::info('Role found', array_merge($logContext, [
                                    'role_id' => $roleId,
                                    'role_name' => $role->name
                                ]));
                            } else {
                                Log::warning('Role not found', array_merge($logContext, [
                                    'role_id' => $roleId
                                ]));
                            }
                        } catch (\Exception $roleError) {
                            Log::error('Error retrieving role', array_merge($logContext, [
                                'role_id' => $roleId,
                                'error' => $roleError->getMessage()
                            ]));
                        }
                    }

                    $initialApprovalStageRoleNames = implode(', ', $roleNames);
                }
            } catch (\Exception $configError) {
                Log::error('Error retrieving approval stage configuration', array_merge($logContext, [
                    'error' => $configError->getMessage(),
                    'fallback_stage' => $initialApprovalStage,
                    'fallback_roles' => $initialApprovalStageRoleNames
                ]));
            }

            Log::info('Approval stage configuration determined', array_merge($logContext, [
                'approval_stage' => $initialApprovalStage,
                'approval_stage_role_names' => $initialApprovalStageRoleNames
            ]));

            // Step 5: Prepare assessment data
            Log::info('Step 5: Preparing assessment data', $logContext);
            $assessmentData = [
                'approved_loan_value' => $this->approved_loan_value,
                'approved_term' => $this->approved_term,
                'monthly_installment' => $this->monthlyInstallmentValue,
                'selectedLoan' => $this->selectedLoan,
                'top_up_amount' => $this->topUpAmount,
                'assessed_by' => $user->id,
                'assessed_at' => now(),
                'assessment_type' => 'standard'
            ];

            Log::info('Assessment data prepared', array_merge($logContext, [
                'assessment_data_keys' => array_keys($assessmentData),
                'approved_loan_value' => $this->approved_loan_value,
                'approved_term' => $this->approved_term,
                'monthly_installment' => $this->monthlyInstallmentValue,
                'selected_loan' => $this->selectedLoan,
                'top_up_amount' => $this->topUpAmount
            ]));

            // Calculate total deductions and net disbursement for database persistence
            $totalDeductions = (float)($this->firstInstallmentInterestAmount ?? 0) + 
                              (float)($this->totalCharges ?? 0) + 
                              (float)($this->totalInsurance ?? 0) + 
                              (float)($this->totalAmount ?? 0) + 
                              (float)($this->topUpAmount ?? 0) + 
                              (float)($this->approved_loan_value ?? 0) * 0.05; // Early Settlement Penalty (5%)
            
            $netDisbursementAmount = (float)($this->approved_loan_value ?? 0) - $totalDeductions;

            // Step 6: Update loan record
            Log::info('Step 6: Updating loan record', $logContext);
            $updateData = [
                'status' => 'PENDING_APPROVAL',
                'monthly_installment' => $this->monthlyInstallmentValue ?? 0,
                'approval_stage' => $initialApprovalStage,
                'approval_stage_role_name' => $initialApprovalStageRoleNames,
                'total_deductions' => $totalDeductions,
                'net_disbursement_amount' => $netDisbursementAmount,
                'assessment_data' => json_encode($assessmentData)
            ];

            Log::info('Loan update data prepared', array_merge($logContext, [
                'update_data' => $updateData,
                'assessment_data_json_size' => strlen(json_encode($assessmentData))
            ]));

            $loan->update($updateData);

            Log::info('Loan record updated successfully', array_merge($logContext, [
                'loan_id' => $loan->id,
                'new_status' => $loan->status,
                'new_approval_stage' => $loan->approval_stage
            ]));

            // Step 7: Create approval record
            Log::info('Step 7: Creating approval record', $logContext);
            $approvalData = [
                'process_name' => 'Loan Approval',
                'process_description' => "Loan approval request for client {$loan->client_number} - Amount: " . number_format($this->approved_loan_value, 2) . " TZS",
                'approval_process_description' => 'Loan assessment completed and ready for approval',
                'process_code' => 'LOAN_DISB',
                'process_id' => $loanId,
                'process_status' => 'PENDING',
                'user_id' => $user->id,
                'team_id' => $user->currentTeam->id ?? "",
                'created_at' => now(),
                'updated_at' => now()
            ];

            Log::info('Approval data prepared', array_merge($logContext, [
                'approval_data' => $approvalData
            ]));

            DB::table('approvals')->insert($approvalData);

            Log::info('Approval record created successfully', array_merge($logContext, [
                'approval_id' => DB::getPdo()->lastInsertId()
            ]));

            // Step 8: Save tab state
            Log::info('Step 8: Saving assessment tab state', $logContext);
            $this->saveAssessmentTabState();
            Log::info('Assessment tab state saved successfully', $logContext);

            // Step 9: Set completion states
            Log::info('Step 9: Setting completion states', $logContext);
            $this->actionCompleted = true;
            $this->actionType = 'approval_sent';
            $this->actionMessage = 'Loan has been sent for approval successfully.';
            $this->showActionButtons = false;
            $this->isProcessing = false;

            // Step 10: Set success message
            Log::info('Step 10: Setting success message', $logContext);
            session()->flash('message', 'Loan sent for approval successfully.');

            $executionTime = microtime(true) - $startTime;
            Log::info('=== SEND FOR APPROVAL SUCCESS ===', array_merge($logContext, [
                'execution_time_seconds' => round($executionTime, 3),
                'final_status' => 'success'
            ]));

        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            
            Log::error('=== SEND FOR APPROVAL FAILED ===', array_merge($logContext, [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'execution_time_seconds' => round($executionTime, 3),
                'final_status' => 'failed'
            ]));

            // Reset processing state
            $this->isProcessing = false;
            
            // Set error message
            session()->flash('error', 'Error sending for approval: ' . $e->getMessage());
            
            // Log additional context for debugging
            Log::error('Additional error context', array_merge($logContext, [
                'current_loan_id' => session('currentloanID'),
                'user_authenticated' => Auth::check(),
                'session_data' => session()->all(),
                'component_properties' => [
                    'approved_loan_value' => $this->approved_loan_value ?? 'null',
                    'approved_term' => $this->approved_term ?? 'null',
                    'monthlyInstallmentValue' => $this->monthlyInstallmentValue ?? 'null',
                    'selected_loan' => $this->selectedLoan ?? 'null',
                    'top_up_amount' => $this->topUpAmount ?? 'null'
                ]
            ]));
        }
    }

    /**
     * Send for approval with exceptions
     */
    public function sendForApprovalWithExceptions()
    {
        $startTime = microtime(true);
        $logContext = [
            'method' => 'sendForApprovalWithExceptions',
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
            'user_id' => Auth::id() ?? 'unknown',
            'user_name' => Auth::user()->name ?? 'unknown'
        ];

        Log::info('=== SEND FOR APPROVAL WITH EXCEPTIONS START ===', $logContext);

        try {
            // Step 1: Initialize processing state
            Log::info('Step 1: Initializing processing state', $logContext);
            $this->isProcessing = true;
            
            // Step 2: Get user and session data
            Log::info('Step 2: Retrieving user and session data', $logContext);
            $user = Auth::user();
            $loanId = session('currentloanID');
            
            Log::info('User and session data retrieved', array_merge($logContext, [
                'user_id' => $user->id ?? 'unknown',
                'user_name' => $user->name ?? 'unknown',
                'loan_id' => $loanId,
                'session_has_currentloanID' => session()->has('currentloanID')
            ]));

            // Step 3: Validate loan exists
            Log::info('Step 3: Validating loan exists', $logContext);
            $loan = LoansModel::find($loanId);

            if (!$loan) {
                Log::error('Loan not found in database', array_merge($logContext, [
                    'loan_id' => $loanId,
                    'error' => 'Loan not found'
                ]));
                throw new \Exception('Loan not found');
            }

            Log::info('Loan found successfully', array_merge($logContext, [
                'loan_id' => $loan->id,
                'loan_number' => $loan->loan_id,
                'client_number' => $loan->client_number,
                'loan_status' => $loan->status,
                'loan_amount' => $loan->principle
            ]));

            // Step 4: Get approval stage configuration
            Log::info('Step 4: Retrieving approval stage configuration', $logContext);
            $initialApprovalStage = 'first_checker';
            $initialApprovalStageRoleNames = [];

            try {
                $processConfig = DB::table('process_code_configs')
                    ->where('process_code', 'LOAN_DISB')
                    ->first();

                if (!$processConfig) {
                    Log::warning('Process code config not found', array_merge($logContext, [
                        'process_code' => 'LOAN_DISB',
                        'fallback_stage' => $initialApprovalStage
                    ]));
                } else {
                    Log::info('Process code config found', array_merge($logContext, [
                        'process_code' => $processConfig->process_code,
                        'first_checker_role_id' => $processConfig->first_checker_role_id
                    ]));

                    // Handle role IDs (could be single ID or JSON array)
                    $roleIds = $processConfig->first_checker_role_id;
                    
                    if (is_string($roleIds) && json_decode($roleIds) !== null) {
                        // JSON array of role IDs
                        $roleIds = json_decode($roleIds, true);
                        Log::info('Role IDs from JSON', array_merge($logContext, [
                            'role_ids' => $roleIds,
                            'role_ids_type' => 'json_array'
                        ]));
                    } else {
                        // Single role ID
                        $roleIds = [$roleIds];
                        Log::info('Single role ID', array_merge($logContext, [
                            'role_ids' => $roleIds,
                            'role_ids_type' => 'single_id'
                        ]));
                    }

                    $roleNames = [];
                    foreach ($roleIds as $roleId) {
                        try {
                            $role = DB::table('roles')->where('id', $roleId)->first();
                            if ($role) {
                                $roleNames[] = $role->name;
                                Log::info('Role found', array_merge($logContext, [
                                    'role_id' => $roleId,
                                    'role_name' => $role->name
                                ]));
                            } else {
                                Log::warning('Role not found', array_merge($logContext, [
                                    'role_id' => $roleId
                                ]));
                            }
                        } catch (\Exception $roleError) {
                            Log::error('Error retrieving role', array_merge($logContext, [
                                'role_id' => $roleId,
                                'error' => $roleError->getMessage()
                            ]));
                        }
                    }

                    $initialApprovalStageRoleNames = implode(', ', $roleNames);
                }
            } catch (\Exception $configError) {
                Log::error('Error retrieving approval stage configuration', array_merge($logContext, [
                    'error' => $configError->getMessage(),
                    'fallback_stage' => $initialApprovalStage,
                    'fallback_roles' => $initialApprovalStageRoleNames
                ]));
            }

            Log::info('Approval stage configuration determined', array_merge($logContext, [
                'approval_stage' => $initialApprovalStage,
                'approval_stage_role_names' => $initialApprovalStageRoleNames
            ]));

            // Step 5: Prepare assessment data
            Log::info('Step 5: Preparing assessment data', $logContext);
            $assessmentData = [
                'approved_loan_value' => $this->approved_loan_value,
                'approved_term' => $this->approved_term,
                'monthly_installment' => $this->monthlyInstallmentValue,
                'exception_data' => $this->exceptionData,
                'assessed_by' => $user->id,
                'assessed_at' => now(),
                'assessment_type' => 'with_exceptions'
            ];

            Log::info('Assessment data prepared', array_merge($logContext, [
                'assessment_data_keys' => array_keys($assessmentData),
                'approved_loan_value' => $this->approved_loan_value,
                'approved_term' => $this->approved_term,
                'monthly_installment' => $this->monthlyInstallmentValue,
                'has_exception_data' => !empty($this->exceptionData)
            ]));

            // Calculate total deductions and net disbursement for database persistence
            $totalDeductions = (float)($this->firstInstallmentInterestAmount ?? 0) + 
                              (float)($this->totalCharges ?? 0) + 
                              (float)($this->totalInsurance ?? 0) + 
                              (float)($this->totalAmount ?? 0) + 
                              (float)($this->topUpAmount ?? 0) + 
                              (float)($this->approved_loan_value ?? 0) * 0.05; // Early Settlement Penalty (5%)
            
            $netDisbursementAmount = (float)($this->approved_loan_value ?? 0) - $totalDeductions;

            // Step 6: Update loan record
            Log::info('Step 6: Updating loan record', $logContext);
            $updateData = [
                'status' => 'PENDING_EXCEPTION_APPROVAL',
                'monthly_installment' => $this->monthlyInstallmentValue ?? 0,
                'approval_stage' => $initialApprovalStage,
                'approval_stage_role_name' => $initialApprovalStageRoleNames,
                'total_deductions' => $totalDeductions,
                'net_disbursement_amount' => $netDisbursementAmount,
                'assessment_data' => json_encode($assessmentData)
            ];

            Log::info('Loan update data prepared', array_merge($logContext, [
                'update_data' => $updateData,
                'assessment_data_json_size' => strlen(json_encode($assessmentData))
            ]));

            $loan->update($updateData);

            Log::info('Loan record updated successfully', array_merge($logContext, [
                'loan_id' => $loan->id,
                'new_status' => $loan->status,
                'new_approval_stage' => $loan->approval_stage
            ]));

            // Step 7: Create approval record
            Log::info('Step 7: Creating approval record', $logContext);
            $approvalData = [
                'process_name' => 'Loan Exception Approval',
                'process_description' => "Loan exception approval request for client {$loan->client_number} - Amount: " . number_format($this->approved_loan_value, 2) . " TZS",
                'approval_process_description' => 'Loan assessment completed with exceptions requiring approval',
                'process_code' => 'LOAN_DISB',
                'process_id' => $loanId,
                'process_status' => 'PENDING',
                'user_id' => $user->id,
                'team_id' => $user->currentTeam->id ?? "",
                'created_at' => now(),
                'updated_at' => now()
            ];

            Log::info('Approval data prepared', array_merge($logContext, [
                'approval_data' => $approvalData
            ]));

            DB::table('approvals')->insert($approvalData);

            Log::info('Approval record created successfully', array_merge($logContext, [
                'approval_id' => DB::getPdo()->lastInsertId()
            ]));

            // Step 8: Save tab state
            Log::info('Step 8: Saving assessment tab state', $logContext);
            $this->saveAssessmentTabState();
            Log::info('Assessment tab state saved successfully', $logContext);

            // Step 9: Set completion states
            Log::info('Step 9: Setting completion states', $logContext);
            $this->actionCompleted = true;
            $this->actionType = 'exception_approval_sent';
            $this->actionMessage = 'Loan has been sent for exception approval successfully.';
            $this->showActionButtons = false;
            $this->isProcessing = false;

            // Step 10: Set success message
            Log::info('Step 10: Setting success message', $logContext);
            session()->flash('message', 'Loan sent for exception approval successfully.');

            $executionTime = microtime(true) - $startTime;
            Log::info('=== SEND FOR APPROVAL WITH EXCEPTIONS SUCCESS ===', array_merge($logContext, [
                'execution_time_seconds' => round($executionTime, 3),
                'final_status' => 'success'
            ]));
            
        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            
            Log::error('=== SEND FOR APPROVAL WITH EXCEPTIONS FAILED ===', array_merge($logContext, [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'execution_time_seconds' => round($executionTime, 3),
                'final_status' => 'failed'
            ]));

            // Reset processing state
            $this->isProcessing = false;
            
            // Set error message
            session()->flash('error', 'Error sending for exception approval: ' . $e->getMessage());
            
            // Log additional context for debugging
            Log::error('Additional error context', array_merge($logContext, [
                'current_loan_id' => session('currentloanID'),
                'user_authenticated' => Auth::check(),
                'session_data' => session()->all(),
                'component_properties' => [
                    'approved_loan_value' => $this->approved_loan_value ?? 'null',
                    'approved_term' => $this->approved_term ?? 'null',
                    'monthlyInstallmentValue' => $this->monthlyInstallmentValue ?? 'null',
                    'has_exception_data' => !empty($this->exceptionData)
                ]
            ]));
        }
    }

    /**
     * Decline loan
     */
    public function declineLoan()
    {
        try {
            $this->isProcessing = true;
            
            $user = Auth::user();
            $loanId = session('currentloanID');
            $loan = LoansModel::find($loanId);

            if (!$loan) {
                throw new \Exception('Loan not found');
            }

            // Update loan status to rejected
            $loan->update([
                'status' => 'REJECTED',
                'assessment_data' => json_encode([
                    'rejected_at' => now(),
                    'rejected_by' => $user->id,
                    'rejection_reason' => 'Failed to meet loan criteria',
                    'exception_data' => $this->exceptionData,
                    'current_assessment' => [
                        'approved_loan_value' => $this->approved_loan_value,
                        'approved_term' => $this->approved_term,
                        'monthly_installment' => $this->monthlyInstallmentValue
                    ]
                ])
            ]);

            // Set action completion states
            $this->actionCompleted = true;
            $this->actionType = 'loan_declined';
            $this->actionMessage = 'Loan has been declined due to not meeting criteria.';
            $this->isProcessing = false;

            session()->flash('message', 'Loan has been declined.');
            
        } catch (\Exception $e) {
            $this->isProcessing = false;
            Log::error('Error declining loan: ' . $e->getMessage());
            session()->flash('error', 'Error declining loan: ' . $e->getMessage());
        }
    }

    /**
     * Save assessment tab state using the tab state service
     */
    protected function saveAssessmentTabState()
    {
        try {
            $loanId = session('currentloanID');
            if (!$loanId) {
                return;
            }

            // Get the tab state service
            $tabStateService = app(\App\Services\LoanTabStateService::class);
            
            // Save assessment tab state
            $tabStateService->saveTabState($loanId, 'assessment', [
                'approved_loan_value' => $this->approved_loan_value,
                'approved_term' => $this->approved_term,
                'monthly_installment' => $this->monthlyInstallmentValue,
                'take_home' => $this->take_home,
                'exception_data' => $this->exceptionData,
                'assessed_at' => now(),
                'assessed_by' => auth()->id()
            ]);

            // Update completion status
            $completedTabs = $tabStateService->getCompletedTabs($loanId);
            $tabStateService->saveTabCompletionStatus($loanId, $completedTabs);

            // Emit event to parent component
            $this->emit('tabCompleted', 'assessment');

        } catch (\Exception $e) {
            Log::error('Error saving assessment tab state: ' . $e->getMessage());
        }
    }

    public function saveAsDraft()
    {
        try {
            $this->isProcessing = true;
            
            $loanId = session('currentloanID');
            $user = Auth::user();
            
            // Validate loan exists
            $loan = LoansModel::find($loanId);
            if (!$loan) {
                throw new \Exception('Loan not found');
            }

            // Calculate total deductions and net disbursement for database persistence
            $totalDeductions = (float)($this->firstInstallmentInterestAmount ?? 0) + 
                              (float)($this->totalCharges ?? 0) + 
                              (float)($this->totalInsurance ?? 0) + 
                              (float)($this->totalAmount ?? 0) + 
                              (float)($this->topUpAmount ?? 0) + 
                              (float)($this->approved_loan_value ?? 0) * 0.05; // Early Settlement Penalty (5%)
            
            $netDisbursementAmount = (float)($this->approved_loan_value ?? 0) - $totalDeductions;

            // Save current assessment as draft with comprehensive data
            $loan->update([
                'status' => 'DRAFT',
                'approved_loan_value' => $this->approved_loan_value,
                'approved_term' => $this->approved_term,
                'monthly_installment' => $this->monthlyInstallmentValue,
                'take_home' => $this->take_home,
                'collateral_value' => $this->collateral_value,
                'collateral_type' => $this->collateral_type,
                'available_funds' => $this->available_funds,
                'principle' => $this->principle,
                'interest' => $this->interest,
                'tenure' => $this->tenure,
                'interest_method' => $this->interest_method,
                'total_deductions' => $totalDeductions,
                'net_disbursement_amount' => $netDisbursementAmount,
                'assessment_data' => json_encode([
                    'take_home' => $this->take_home,
                    'approved_loan_value' => $this->approved_loan_value,
                    'approved_term' => $this->approved_term,
                    'monthly_installment' => $this->monthlyInstallmentValue,
                    'collateral_value' => $this->collateral_value,
                    'collateral_type' => $this->collateral_type,
                    'available_funds' => $this->available_funds,
                    'coverage' => $this->coverage,
                    'monthly_sales' => $this->monthly_sales,
                    'gross_profit' => $this->gross_profit,
                    'net_profit' => $this->net_profit,
                    'recommended' => $this->recommended,
                    'recommended_tenure' => $this->recommended_tenure,
                    'selectedLoan' => $this->selectedLoan,
                    'top_up_amount' => $this->topUpAmount,
                    'exception_data' => $this->exceptionData,
                    'credit_score_data' => $this->creditScoreData,
                    'client_info_data' => $this->clientInfoData,
                    'product_params_data' => $this->productParamsData,
                    'nbc_loans_data' => $this->nbcLoansData,
                    'settlement_data' => $this->settlementData,
                    'assessed_at' => now(),
                    'assessed_by' => $user->id
                ])
            ]);

            // Save tab state
            $this->saveAssessmentTabState();

            // Set action completion states
            $this->actionCompleted = true;
            $this->actionType = 'draft_saved';
            $this->actionMessage = 'Assessment has been saved as draft successfully.';
            $this->isProcessing = false;

            session()->flash('message', 'Assessment saved as draft successfully.');
        } catch (\Exception $e) {
            $this->isProcessing = false;
            Log::error('Error saving as draft: ' . $e->getMessage());
            session()->flash('error', 'Error saving as draft: ' . $e->getMessage());
        }
    }

    public function modifyLoanParameters()
    {
        try {
            // This method would typically open a modal or redirect to edit form
            // For now, we'll just show a message
            session()->flash('info', 'Please modify the loan parameters above and save the changes.');
            
        } catch (\Exception $e) {
            Log::error('Error modifying loan parameters: ' . $e->getMessage());
            session()->flash('error', 'Failed to modify loan parameters. Please try again.');
        }
    }

    public function requestAdditionalDocuments()
    {
        try {
            $this->isProcessing = true;
            
            $loanId = session('currentloanID');
            $user = Auth::user();
            
            // Validate loan exists
            $loan = LoansModel::find($loanId);
            if (!$loan) {
                throw new \Exception('Loan not found');
            }

            // Update loan status to request documents
            $loan->update([
                'status' => 'DOCUMENTS_REQUESTED',
                'assessment_data' => json_encode([
                    'documents_requested_at' => now(),
                    'requested_by' => $user->id,
                    'current_assessment' => [
                        'approved_loan_value' => $this->approved_loan_value,
                        'approved_term' => $this->approved_term,
                        'monthly_installment' => $this->monthlyInstallmentValue,
                        'exception_data' => $this->exceptionData
                    ]
                ])
            ]);

            Log::info('Additional documents requested for loan', [
                'loan_id' => $loanId,
                'user_id' => $user->id
            ]);

            session()->flash('success', 'Additional documents have been requested for this loan.');
            $this->emit('documentsRequested', $loanId);
            
        } catch (\Exception $e) {
            Log::error('Error requesting additional documents: ' . $e->getMessage());
            session()->flash('error', 'Failed to request additional documents. Please try again.');
        }
    }

    // Helper methods for approval workflow
    public function canSendForApproval()
    {
        $canApprove = isset($this->exceptionData['summary']['can_approve']) && 
               $this->exceptionData['summary']['can_approve'] === true;
        
        Log::info('canSendForApproval check', [
            'exceptionData_summary' => $this->exceptionData['summary'] ?? 'not_set',
            'can_approve' => $canApprove
        ]);
        
        return $canApprove;
    }

    public function canSendForExceptionApproval()
    {
        $requiresException = isset($this->exceptionData['summary']['requires_exception']) && 
               $this->exceptionData['summary']['requires_exception'] === true;
        
        Log::info('canSendForExceptionApproval check', [
            'exceptionData_summary' => $this->exceptionData['summary'] ?? 'not_set',
            'requires_exception' => $requiresException
        ]);
        
        return $requiresException;
    }

    public function shouldDeclineLoan()
    {
        $shouldDecline = !$this->canSendForApproval() && !$this->canSendForExceptionApproval();
        
        Log::info('shouldDeclineLoan check', [
            'can_send_for_approval' => $this->canSendForApproval(),
            'can_send_for_exception_approval' => $this->canSendForExceptionApproval(),
            'should_decline' => $shouldDecline
        ]);
        
        return $shouldDecline;
    }

    /**
     * Check if assessment is completed and emit tab completion event
     */
    public function checkAssessmentCompletion()
    {
        try {
            $loanId = session('currentloanID');
            if (!$loanId) {
                return;
            }

            $tabStateService = app(\App\Services\LoanTabStateService::class);
            $isCompleted = $tabStateService->isTabCompleted($loanId, 'assessment');

            if ($isCompleted) {
                $this->emit('tabCompleted', 'assessment');
            }

            return $isCompleted;
        } catch (\Exception $e) {
            Log::error('Error checking assessment completion: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if assessment is completed
     */
    public function isAssessmentCompleted()
    {
        try {
            $loanId = session('currentloanID');
            if (!$loanId) {
                return false;
            }

            $tabStateService = app(\App\Services\LoanTabStateService::class);
            return $tabStateService->isTabCompleted($loanId, 'assessment');
        } catch (\Exception $e) {
            Log::error('Error checking if assessment is completed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mount method to check completion status on component load
     */
    public function mount()
    {
        // Check if assessment is completed and emit event if needed
        $this->checkAssessmentCompletion();
        
        // Initialize approved_term only if not already set
        if ($this->approved_term === null || $this->approved_term === '' || $this->approved_term === 0) {
            // Load loan details first to get the loan object
            $this->loadLoanDetails();
            $this->loadProductDetails();
            
            // Check if there's a saved value in the database first
            if ($this->loan && $this->loan->approved_term && $this->loan->approved_term > 0) {
                $this->approved_term = $this->loan->approved_term;
            } elseif ($this->product && $this->product->max_term) {
                // Only set to product max if no saved value exists
                $this->approved_term = $this->product->max_term;
            } else {
                $this->approved_term = 12; // Default fallback
            }
        }
        
        // Initialize selectedContracts with currently selected settlements
        $this->initializeSelectedContracts();
        
        // Calculate total amount for selected settlements
        $this->calculateTotal();
        
        // Load loan schedule if we have the required data
        if ($this->loan && $this->approved_loan_value && $this->approved_term && $this->product) {
            $this->loadLoanSchedule();
        }
        
        // Calculate assessment score
        $this->calculateAssessmentScore();
        
        // Calculate deduction amounts
        $this->calculateDeductionAmounts();
        
        // Calculate top-up amount if applicable
        $this->calculateTopUpAmount();
        
        // Ensure exceptionData is always initialized
        if (!isset($this->exceptionData) || !is_array($this->exceptionData)) {
            $this->exceptionData = [
                'loan_amount' => ['status' => 'PENDING', 'is_exceeded' => false],
                'term' => ['status' => 'PENDING', 'is_exceeded' => false],
                'credit_score' => ['status' => 'PENDING', 'is_exceeded' => false],
                'salary_installment' => ['status' => 'PENDING', 'is_exceeded' => false],
                'collateral' => ['status' => 'PENDING', 'is_exceeded' => false],
                'summary' => [
                    'overall_status' => 'PENDING',
                    'can_approve' => false,
                    'requires_exception' => false,
                    'total_checks' => 0,
                    'passed_checks' => 0,
                    'failed_checks' => 0,
                    'high_severity' => 0,
                    'medium_severity' => 0
                ]
            ];
        }
    }

    /**
     * Render method - called after component is fully loaded and ready
     * This is where we calculate exceptions after successful rendering
     */
    public function render()
    {
        Log::info('=== ASSESSMENT COMPONENT RENDER START ===', [
            'timestamp' => now()->toISOString(),
            'loan_id' => $this->loan->id ?? null,
            'exception_data_loaded' => isset($this->exceptionData) && !empty($this->exceptionData),
            'exception_data_status' => $this->exceptionData['summary']['overall_status'] ?? 'N/A'
        ]);

        // Calculate exceptions after successful rendering
        // This ensures all data is available and the component is fully loaded
        if (!isset($this->exceptionData) || empty($this->exceptionData) || ($this->exceptionData['summary']['overall_status'] ?? 'PENDING') === 'PENDING') {
            Log::info('Loading exception data after successful rendering');
            $this->loadExceptionData();
        }

        Log::info('=== ASSESSMENT COMPONENT RENDER END ===', [
            'exception_data_count' => count($this->exceptionData ?? []),
            'summary_status' => $this->exceptionData['summary']['overall_status'] ?? 'N/A',
            'can_approve' => $this->exceptionData['summary']['can_approve'] ?? false
        ]);

        // Dispatch browser event to notify that component is rendered
        $this->dispatchBrowserEvent('assessment-component-rendered', [
            'loan_id' => $this->loan->id ?? null,
            'exception_status' => $this->exceptionData['summary']['overall_status'] ?? 'N/A'
        ]);

        return view('livewire.loans.assessment');
    }

    /**
     * Hydrated method - called after component is fully hydrated
     * This ensures exceptions are calculated after all data is loaded
     */
    public function hydrated()
    {
        Log::info('=== ASSESSMENT COMPONENT HYDRATED ===', [
            'timestamp' => now()->toISOString(),
            'loan_id' => $this->loan->id ?? null,
            'all_data_loaded' => isset($this->loan) && isset($this->product) && isset($this->member)
        ]);

        // Ensure exceptions are calculated after full hydration
        if (isset($this->loan) && isset($this->product) && isset($this->member)) {
            Log::info('Component fully hydrated, ensuring exceptions are calculated');
            $this->loadExceptionData();
        }
    }

    /**
     * Public method to ensure exceptions are calculated after successful rendering
     * This can be called from JavaScript or other components
     */
    public function ensureExceptionsCalculated()
    {
        Log::info('=== ENSURE EXCEPTIONS CALCULATED ===', [
            'timestamp' => now()->toISOString(),
            'loan_id' => $this->loan->id ?? null,
            'exception_data_exists' => isset($this->exceptionData),
            'exception_status' => $this->exceptionData['summary']['overall_status'] ?? 'N/A'
        ]);

        // Check if exceptions need to be calculated
        if (!isset($this->exceptionData) || 
            empty($this->exceptionData) || 
            ($this->exceptionData['summary']['overall_status'] ?? 'PENDING') === 'PENDING') {
            
            Log::info('Exceptions need to be calculated, loading now');
            $this->loadExceptionData();
            
            // Dispatch browser event to notify that exceptions have been calculated
            $this->dispatchBrowserEvent('exceptions-calculated', [
                'status' => $this->exceptionData['summary']['overall_status'] ?? 'N/A',
                'can_approve' => $this->exceptionData['summary']['can_approve'] ?? false,
                'timestamp' => now()->toISOString()
            ]);
        } else {
            Log::info('Exceptions already calculated', [
                'status' => $this->exceptionData['summary']['overall_status'] ?? 'N/A'
            ]);
        }
    }

    /**
     * Enhanced loan schedule generation with comprehensive validation
     */
    private function generateEnhancedLoanSchedule($loanId, $principal, $interestRate, $tenure, $disbursementDate = null)
    {
        try {
            Log::info('Generating enhanced loan schedule', [
                'loan_id' => $loanId,
                'principal' => $principal,
                'interest_rate' => $interestRate,
                'tenure' => $tenure
            ]);

            // Validate inputs
            if ($principal <= 0 || $interestRate <= 0 || $tenure <= 0) {
                throw new \Exception('Invalid loan parameters for schedule generation');
            }

            // Set disbursement date to current date if not provided
            if (!$disbursementDate) {
                $disbursementDate = \Carbon\Carbon::now();
            } else {
                $disbursementDate = \Carbon\Carbon::parse($disbursementDate);
            }

            // Clear existing schedule
            DB::table('loans_schedules')->where('loan_id', $loanId)->delete();

            $schedule = [];
            $totalPayment = 0;
            $totalInterest = 0;
            $totalPrincipal = 0;
            $remainingBalance = $principal;

            // Calculate monthly interest rate
            $monthlyInterestRate = $interestRate / 12 / 100;

            // Calculate equal monthly installment using amortization formula
            if ($monthlyInterestRate > 0) {
                $monthlyInstallment = $principal * ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $tenure)) / (pow(1 + $monthlyInterestRate, $tenure) - 1);
            } else {
                $monthlyInstallment = $principal / $tenure;
            }

            // Generate schedule for each month
            for ($month = 1; $month <= $tenure; $month++) {
                $openingBalance = $remainingBalance;
                $monthlyInterest = $remainingBalance * $monthlyInterestRate;
                $monthlyPrincipal = $monthlyInstallment - $monthlyInterest;
                
                // Ensure principal doesn't exceed remaining balance
                if ($monthlyPrincipal > $remainingBalance) {
                    $monthlyPrincipal = $remainingBalance;
                    $monthlyInstallment = $monthlyPrincipal + $monthlyInterest;
                }

                $remainingBalance -= $monthlyPrincipal;

                // Calculate installment date
                $installmentDate = $disbursementDate->copy()->addMonths($month);

                // Create schedule record
                $scheduleRecord = [
                    'loan_id' => $loanId,
                    'installment' => $monthlyInstallment,
                    'interest' => $monthlyInterest,
                    'principle' => $monthlyPrincipal,
                    'opening_balance' => $openingBalance,
                    'closing_balance' => $remainingBalance,
                    'completion_status' => 'ACTIVE',
                    'status' => 'ACTIVE',
                    'installment_date' => $installmentDate->format('Y-m-d'),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                DB::table('loans_schedules')->insert($scheduleRecord);
                $schedule[] = $scheduleRecord;

                $totalPayment += $monthlyInstallment;
                $totalInterest += $monthlyInterest;
                $totalPrincipal += $monthlyPrincipal;
            }

            // Update loan with schedule summary
            DB::table('loans')->where('id', $loanId)->update([
                'monthly_installment' => $monthlyInstallment,
                'total_interest' => $totalInterest,
                'total_principal' => $totalPrincipal,
                'total_payment' => $totalPayment,
                'schedule_generated_at' => now()
            ]);

            Log::info('Enhanced loan schedule generated successfully', [
                'loan_id' => $loanId,
                'total_installments' => count($schedule),
                'monthly_installment' => $monthlyInstallment,
                'total_payment' => $totalPayment,
                'total_interest' => $totalInterest
            ]);

            return [
                'schedule' => $schedule,
                'summary' => [
                    'total_installments' => count($schedule),
                    'monthly_installment' => $monthlyInstallment,
                    'total_payment' => $totalPayment,
                    'total_interest' => $totalInterest,
                    'total_principal' => $totalPrincipal
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error generating enhanced loan schedule: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate loan schedule parameters
     */
    private function validateScheduleParameters($principal, $interestRate, $tenure)
    {
        $errors = [];

        if ($principal <= 0) {
            $errors[] = 'Principal amount must be greater than zero';
        }

        if ($interestRate <= 0) {
            $errors[] = 'Interest rate must be greater than zero';
        }

        if ($interestRate > 100) {
            $errors[] = 'Interest rate cannot exceed 100%';
        }

        if ($tenure <= 0) {
            $errors[] = 'Loan tenure must be greater than zero';
        }

        if ($tenure > 120) { // Maximum 10 years
            $errors[] = 'Loan tenure cannot exceed 120 months (10 years)';
        }

        return $errors;
    }

    /**
     * Calculate loan affordability metrics
     */
    private function calculateAffordabilityMetrics($monthlyInstallment, $takeHomeSalary)
    {
        try {
            if ($takeHomeSalary <= 0) {
                return [
                    'debt_service_ratio' => 0,
                    'affordability_status' => 'UNKNOWN',
                    'risk_level' => 'HIGH'
                ];
            }

            $debtServiceRatio = ($monthlyInstallment / $takeHomeSalary) * 100;

            // Determine affordability status
            if ($debtServiceRatio <= 30) {
                $affordabilityStatus = 'EXCELLENT';
                $riskLevel = 'LOW';
            } elseif ($debtServiceRatio <= 40) {
                $affordabilityStatus = 'GOOD';
                $riskLevel = 'LOW';
            } elseif ($debtServiceRatio <= 50) {
                $affordabilityStatus = 'ACCEPTABLE';
                $riskLevel = 'MEDIUM';
            } elseif ($debtServiceRatio <= 60) {
                $affordabilityStatus = 'CAUTION';
                $riskLevel = 'HIGH';
            } else {
                $affordabilityStatus = 'HIGH_RISK';
                $riskLevel = 'VERY_HIGH';
            }

            return [
                'debt_service_ratio' => $debtServiceRatio,
                'affordability_status' => $affordabilityStatus,
                'risk_level' => $riskLevel,
                'monthly_installment' => $monthlyInstallment,
                'take_home_salary' => $takeHomeSalary
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating affordability metrics: ' . $e->getMessage());
            return [
                'debt_service_ratio' => 0,
                'affordability_status' => 'ERROR',
                'risk_level' => 'UNKNOWN'
            ];
        }
        }

    /**
     * Initialize selectedContracts array with currently selected settlements
     */
    private function initializeSelectedContracts()
    {
        try {
            Log::info('=== INITIALIZE SELECTED CONTRACTS START ===', [
                'loan_id' => session('currentloanID')
            ]);

            $selectedSettlements = DB::table('settled_loans')
                ->where('loan_id', session('currentloanID'))
                ->where('is_selected', true)
                ->pluck('loan_array_id')
                ->toArray();

            Log::info('Found selected settlements in database', [
                'count' => count($selectedSettlements),
                'selected_array_ids' => $selectedSettlements
            ]);

            $this->selectedContracts = $selectedSettlements;

            Log::info('=== INITIALIZE SELECTED CONTRACTS END ===', [
                'final_selectedContracts' => $this->selectedContracts
            ]);

        } catch (\Exception $e) {
            Log::error('Error initializing selected contracts: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->selectedContracts = [];
        }
    }

    /**
     * Get detailed collateral information for the loan
     */
    public function getCollateralDetails($loan_id)
    {
        try {
            if (!$loan_id) {
                return [];
            }
            
            $collateralDetails = [];
            
            // Get guarantors and their collaterals
            $guarantors = DB::table('loan_guarantors')
                ->where('loan_id', $loan_id)
                ->where('status', 'active')
                ->get();
            
            foreach ($guarantors as $guarantor) {
                $collaterals = DB::table('loan_collaterals')
                    ->where('loan_guarantor_id', $guarantor->id)
                    ->where('status', 'active')
                    ->get();
                
                foreach ($collaterals as $collateral) {
                    $collateralInfo = [
                        'id' => $collateral->id,
                        'type' => $collateral->collateral_type,
                        'amount' => (float)$collateral->collateral_amount,
                        'locked_amount' => (float)$collateral->locked_amount,
                        'available_amount' => (float)$collateral->available_amount,
                        'guarantor_type' => $guarantor->guarantor_type,
                        'guarantor_id' => $guarantor->id
                    ];
                    
                    // Add account information for financial collaterals
                    if (in_array($collateral->collateral_type, ['savings', 'deposits', 'shares']) && $collateral->account_id) {
                        $account = DB::table('accounts')->find($collateral->account_id);
                        if ($account) {
                            $collateralInfo['account_number'] = $account->account_number;
                            $collateralInfo['account_balance'] = (float)$account->balance;
                        }
                    }
                    
                    // Add physical collateral details
                    if ($collateral->collateral_type === 'physical') {
                        $collateralInfo['description'] = $collateral->physical_collateral_description;
                        $collateralInfo['location'] = $collateral->physical_collateral_location;
                        $collateralInfo['owner_name'] = $collateral->physical_collateral_owner_name;
                        $collateralInfo['value'] = (float)$collateral->physical_collateral_value;
                    }
                    
                    $collateralDetails[] = $collateralInfo;
                }
            }
            
            return $collateralDetails;
        } catch (\Exception $e) {
            Log::error('Error getting collateral details: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate top-up amount for display
     */
    public function calculateTopUpAmount()
    {
        try {
            if ($this->selectedLoan) {
                $existingLoan = DB::table('loans')->where('id', $this->selectedLoan)->first();
                if ($existingLoan) {
                    // Use accounts table balance for more accurate calculation
                    $this->topUpAmount = DB::table('accounts')
                        ->where('account_number', $existingLoan->loan_account_number)
                        ->value('balance') ?? 0;
                }
            } else {
                $this->topUpAmount = 0;
            }
            
            // Auto-save assessment data after top-up amount calculation
            $this->autoSaveAssessmentData();
            
        } catch (\Exception $e) {
            Log::error('Error calculating top-up amount: ' . $e->getMessage());
            $this->topUpAmount = 0;
        }
    }

    /**
     * Withdraw approval request and return to assessment stage
     */
    public function withdrawApprovalRequest()
    {
        try {
            $loanId = session('currentloanID');
            if (!$loanId) {
                $this->errorMessage = 'No loan selected.';
                return;
            }

            $loan = LoansModel::find($loanId);
            if (!$loan) {
                $this->errorMessage = 'Loan not found.';
                return;
            }

            // Update loan status back to assessment
            $loan->status = 'ASSESSMENT';
            $loan->save();

            // Log the action
            Log::info('Approval request withdrawn', [
                'loan_id' => $loanId,
                'user_id' => Auth::id(),
                'action' => 'withdraw_approval_request'
            ]);

            $this->actionCompleted = true;
            $this->actionType = 'withdraw_approval';
            $this->actionMessage = 'Approval request has been withdrawn successfully.';

            // Refresh the component
            $this->emit('refreshAssessment');

        } catch (\Exception $e) {
            Log::error('Error withdrawing approval request: ' . $e->getMessage());
            $this->errorMessage = 'Error withdrawing approval request. Please try again.';
        }
    }

    /**
     * Withdraw exception approval request and return to assessment stage
     */
    public function withdrawExceptionRequest()
    {
        try {
            $loanId = session('currentloanID');
            if (!$loanId) {
                $this->errorMessage = 'No loan selected.';
                return;
            }

            $loan = LoansModel::find($loanId);
            if (!$loan) {
                $this->errorMessage = 'Loan not found.';
                return;
            }

            // Update loan status back to assessment
            $loan->status = 'ASSESSMENT';
            $loan->save();

            // Log the action
            Log::info('Exception approval request withdrawn', [
                'loan_id' => $loanId,
                'user_id' => Auth::id(),
                'action' => 'withdraw_exception_request'
            ]);

            $this->actionCompleted = true;
            $this->actionType = 'withdraw_exception';
            $this->actionMessage = 'Exception approval request has been withdrawn successfully.';

            // Refresh the component
            $this->emit('refreshAssessment');

        } catch (\Exception $e) {
            Log::error('Error withdrawing exception request: ' . $e->getMessage());
            $this->errorMessage = 'Error withdrawing exception request. Please try again.';
        }
    }

    /**
     * Load member details and fill component properties
     */
    private function loadMemberDetails(): void
    {
        try {
            if ($this->guarantor) {
                $this->guarantor = MembersModel::where('client_number', $this->guarantor)->first();
            }
            if ($this->member_number) {
                $this->member = MembersModel::where('client_number', $this->member_number)->first();
                // If member not found, try alternative search methods
                if (!$this->member) {
                    $this->member = MembersModel::where('member_number', $this->member_number)->first();
                }
                if (!$this->member) {
                    $this->member = MembersModel::where('account_number', $this->member_number)->first();
                }
                if (!$this->member) {
                    Log::warning("Member not found for member_number: {$this->member_number}");
                    $this->member = new MembersModel();
                    $this->member->first_name = 'N/A';
                    $this->member->middle_name = '';
                    $this->member->last_name = 'N/A';
                    $this->member->date_of_birth = null;
                    $this->member->member_category = 'N/A';
                }
            } else {
                $this->member = new MembersModel();
                $this->member->first_name = 'N/A';
                $this->member->middle_name = '';
                $this->member->last_name = 'N/A';
                $this->member->date_of_birth = null;
                $this->member->member_category = 'N/A';
            }
            // Initialize additional variables
            $this->age = $this->calculateAge($this->member->date_of_birth ?? null);
            $this->name = $this->member->member_category ?? 'N/A';
            $this->monthsToRetirement = $this->calculateMonthsToRetirement($this->member->date_of_birth ?? null);
            $this->savings = $this->calculateTotalSavings($this->member_number ?? null);
        } catch (\Exception $e) {
            Log::error('Error loading member details: ' . $e->getMessage());
            $this->errorMessage = 'Error loading member details. Please try again.';
            $this->member = new MembersModel();
            $this->member->first_name = 'Error';
            $this->member->middle_name = '';
            $this->member->last_name = 'Loading';
            $this->member->date_of_birth = null;
            $this->member->member_category = 'N/A';
            $this->age = 'N/A';
            $this->name = 'N/A';
            $this->monthsToRetirement = 'N/A';
            $this->savings = 0;
        }
    }

    /**
     * Calculate age from date of birth
     */
    private function calculateAge($dateOfBirth): string
    {
        try {
            if (!$dateOfBirth) {
                return 'N/A';
            }
            $birthDate = new \DateTime($dateOfBirth);
            $currentDate = new \DateTime();
            $age = $currentDate->diff($birthDate)->y;
            return (string) $age;
        } catch (\Exception $e) {
            Log::error('Error calculating age: ' . $e->getMessage());
            return 'N/A';
        }
    }

    /**
     * Calculate months to retirement (assuming retirement at 60)
     */
    private function calculateMonthsToRetirement($dateOfBirth): string
    {
        try {
            if (!$dateOfBirth) {
                return 'N/A';
            }
            $birthDate = new \DateTime($dateOfBirth);
            $currentDate = new \DateTime();
            $retirementDate = clone $birthDate;
            $retirementDate->modify('+60 years');
            if ($currentDate >= $retirementDate) {
                return '0';
            }
            $diff = $currentDate->diff($retirementDate);
            $months = ($diff->y * 12) + $diff->m;
            return (string) $months;
        } catch (\Exception $e) {
            Log::error('Error calculating months to retirement: ' . $e->getMessage());
            return 'N/A';
        }
    }

    /**
     * Calculate total savings for a member
     */
    private function calculateTotalSavings($memberNumber): float
    {
        try {
            if (!$memberNumber) {
                Log::warning('Assessment: No member number provided for savings calculation');
                return 0.0;
            }

            $shareOwnership = DB::table('share_ownership')
                ->where('client_number', $memberNumber)
                ->first();

            if ($shareOwnership) {
                $savings = (float) ($shareOwnership->savings ?? 0);
                $deposits = (float) ($shareOwnership->deposits ?? 0);
                $total = $savings + $deposits;

                Log::info('Assessment: Calculated total savings from share_ownership', [
                    'member_number' => $memberNumber,
                    'savings' => $savings,
                    'deposits' => $deposits,
                    'total' => $total
                ]);

                return $total;
            }

            Log::info('Assessment: No share_ownership record found for member', [
                'member_number' => $memberNumber
            ]);

            return 0.0;
        } catch (\Exception $e) {
            Log::error('Assessment: Error calculating total savings: ' . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Receive data and generate schedule
     */
    public function receiveData()
    {
        try {
            Log::info('Assessment: receiveData called', [
                'principle' => $this->principle ?? 0,
                'interest' => $this->interest ?? 0,
                'tenure' => $this->tenure ?? 12
            ]);

            $principle = (float)($this->principle ?? 0);
            $interest = (float)($this->interest ?? 0);
            $tenure = (float)($this->tenure ?? 12);
            
            if ($principle > 0 && $interest > 0 && $tenure > 0) {
                $this->generateSchedule($principle, $interest, $tenure);
                Log::info('Assessment: Schedule generated successfully');
            } else {
                Log::warning('Assessment: Cannot generate schedule - invalid parameters', [
                    'principle' => $principle,
                    'interest' => $interest,
                    'tenure' => $tenure
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Assessment: Error generating schedule: ' . $e->getMessage());
            $this->errorMessage = 'Error generating loan schedule. Please check your input values.';
        }
    }

    /**
     * Generate loan repayment schedule with improved calculations
     */
    private function generateSchedule($disbursed_amount, $interest_rate, $tenure): void
    {
        try {
            Log::info('Assessment: Generating improved schedule', [
                'disbursed_amount' => $disbursed_amount,
                'interest_rate' => $interest_rate,
                'tenure' => $tenure
            ]);

            if ($disbursed_amount <= 0 || $interest_rate <= 0 || $tenure <= 0) {
                Log::warning('Assessment: Invalid parameters for schedule generation', [
                    'disbursed_amount' => $disbursed_amount,
                    'interest_rate' => $interest_rate,
                    'tenure' => $tenure
                ]);
                $this->table = [];
                $this->tablefooter = [];
                $this->recommended_tenure = $tenure;
                return;
            }

            $principal = (float)$disbursed_amount;
            $annualInterestRate = (float)$interest_rate;
            $monthlyInterestRate = $annualInterestRate / 12 / 100; // Convert to decimal monthly rate
            $termMonths = (int)$tenure;
            $balance = $principal;
            
            // Get payment day from product or default to 20th
            $paymentDay = $this->product->payment_day ?? 20;
            $disbursementDate = \Carbon\Carbon::now()->addDay();
            
            $datalist = [];
            $totalPayment = 0;
            $totalInterest = 0;
            $totalPrincipal = 0;

            // Calculate monthly payment using proper amortization formula
            // PMT = P * (r * (1 + r)^n) / ((1 + r)^n - 1)
            if ($monthlyInterestRate > 0) {
                $monthlyPayment = $principal * ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $termMonths)) / (pow(1 + $monthlyInterestRate, $termMonths) - 1);
            } else {
                $monthlyPayment = $principal / $termMonths; // If no interest, equal principal payments
            }

            // Calculate first payment date (next payment day)
            $firstPaymentDate = $disbursementDate->copy()->day($paymentDay);
            if ($firstPaymentDate->lte($disbursementDate)) {
                $firstPaymentDate->addMonth();
            }

            // Generate schedule for each month
            for ($i = 1; $i <= $termMonths; $i++) {
                $paymentDate = $firstPaymentDate->copy()->addMonths($i - 1);
                
                // Calculate interest for this period
                $monthlyInterest = $balance * $monthlyInterestRate;
                
                // Calculate principal payment
                $principalPayment = $monthlyPayment - $monthlyInterest;
                
                // Ensure we don't overpay principal
                if ($principalPayment > $balance) {
                    $principalPayment = $balance;
                    $monthlyPayment = $principalPayment + $monthlyInterest;
                }
                
                // Update balance
                $balance -= $principalPayment;
                
                // Accumulate totals
                $totalPayment += $monthlyPayment;
                $totalInterest += $monthlyInterest;
                $totalPrincipal += $principalPayment;

                $datalist[] = [
                    "Payment" => round($monthlyPayment, 2),
                    "Interest" => round($monthlyInterest, 2),
                    "Principle" => round($principalPayment, 2),
                    "balance" => round($balance, 2),
                    "Date" => $paymentDate->format('Y-m-d'),
                    "installment_date" => $paymentDate->format('Y-m-d'),
                    "opening_balance" => round($balance + $principalPayment, 2),
                    "closing_balance" => round($balance, 2)
                ];
            }

            $this->table = $datalist;
            $this->tablefooter = [[
                "Payment" => round($totalPayment, 2),
                "Interest" => round($totalInterest, 2),
                "Principle" => round($totalPrincipal, 2),
                "balance" => round($balance, 2),
            ]];

            $this->recommended_tenure = $termMonths;

            // Calculate and set the monthly installment value
            if (count($datalist) > 0) {
                $this->monthlyInstallmentValue = $datalist[0]['Payment'] ?? 0;
            } else {
                $this->monthlyInstallmentValue = 0;
            }

            Log::info('Assessment: Improved schedule generated successfully', [
                'total_payments' => count($datalist),
                'total_payment' => $totalPayment,
                'total_interest' => $totalInterest,
                'total_principal' => $totalPrincipal,
                'recommended_tenure' => $this->recommended_tenure,
                'monthly_installment_value' => $this->monthlyInstallmentValue,
                'monthly_interest_rate' => $monthlyInterestRate,
                'payment_day' => $paymentDay
            ]);

        } catch (\Exception $e) {
            Log::error('Assessment: Error generating improved schedule: ' . $e->getMessage());
            $this->errorMessage = 'Error generating loan schedule. Please check your input values.';
            $this->table = [];
            $this->tablefooter = [];
        }
    }

    /**
     * Calculate loan assessment score
     */
    private function calculateAssessmentScore()
    {
        try {
            Log::info('=== CALCULATE ASSESSMENT SCORE START ===');

            if (!$this->loan) {
                Log::warning('No loan data available for assessment');
                $this->assessmentData = [
                    'overall_score' => 0,
                    'details' => [],
                    'recommendations' => ['No loan data available for assessment']
                ];
                return;
            }

            $details = [];
            $totalScore = 0;
            $totalWeight = 0;

            // 1. Credit Score Assessment (25% weight)
            $creditScore = $this->creditScoreData['score'] ?? 500;
            $creditScoreGrade = $this->creditScoreData['grade'] ?? 'E';
            
            $creditScoreValue = $this->calculateCreditScoreValue($creditScore, $creditScoreGrade);
            $details[] = [
                'criterion' => 'Credit Score',
                'description' => "Credit score: {$creditScore} ({$creditScoreGrade})",
                'score' => $creditScoreValue,
                'weight' => 25,
                'recommendation' => $this->getCreditScoreRecommendation($creditScore, $creditScoreGrade)
            ];
            $totalScore += $creditScoreValue * 25;
            $totalWeight += 25;

            // 2. Income Assessment (25% weight) - Using Take Home Salary/Sales Proceeds
            $incomeScore = $this->calculateIncomeScore();
            $details[] = [
                'criterion' => 'Income Assessment',
                'description' => "Take Home Salary/Sales: " . number_format((float)$this->take_home ?? 0, 2) . " TZS",
                'score' => $incomeScore,
                'weight' => 25,
                'recommendation' => $this->getIncomeRecommendation($incomeScore)
            ];
            $totalScore += $incomeScore * 25;
            $totalWeight += 25;

            // 3. Member Activeness Assessment (25% weight) - SACCO specific
            $memberActivenessScore = $this->calculateMemberActivenessScore();
            
            // Get actual account balances for display
            $clientNumber = $this->loan->client_number ?? '';
            $savingsBalance = 0;
            $sharesBalance = 0;
            $depositsBalance = 0;
            
            if (!empty($clientNumber)) {
                $savingsAccount = DB::table('accounts')
                    ->where('client_number', $clientNumber)
                    ->where('product_number', '2000')
                    ->where('status', 'ACTIVE')
                    ->first();
                $savingsBalance = $savingsAccount ? (float)$savingsAccount->balance : 0;

                $sharesAccount = DB::table('accounts')
                    ->where('client_number', $clientNumber)
                    ->where('product_number', '1000')
                    ->where('status', 'ACTIVE')
                    ->first();
                $sharesBalance = $sharesAccount ? (float)$sharesAccount->balance : 0;

                $depositsAccount = DB::table('accounts')
                    ->where('client_number', $clientNumber)
                    ->where('product_number', '3000')
                    ->where('status', 'ACTIVE')
                    ->first();
                $depositsBalance = $depositsAccount ? (float)$depositsAccount->balance : 0;
            }
            
            $details[] = [
                'criterion' => 'Member Activeness',
                'description' => "Savings: " . number_format($savingsBalance, 2) . " TZS | Shares: " . number_format($sharesBalance, 2) . " TZS | Deposits: " . number_format($depositsBalance, 2) . " TZS",
                'score' => $memberActivenessScore,
                'weight' => 25,
                'recommendation' => $this->getMemberActivenessRecommendation($memberActivenessScore)
            ];
            $totalScore += $memberActivenessScore * 25;
            $totalWeight += 25;

            // 4. Collateral Assessment (15% weight)
            $collateralScore = $this->calculateCollateralScore();
            $details[] = [
                'criterion' => 'Collateral Assessment',
                'description' => "Collateral value: " . number_format($this->collateral_value ?? 0, 2) . " TZS",
                'score' => $collateralScore,
                'weight' => 15,
                'recommendation' => $this->getCollateralRecommendation($collateralScore)
            ];
            $totalScore += $collateralScore * 15;
            $totalWeight += 15;

            // 5. Affordability Assessment (10% weight) - SACCO rule: max 50% of take home
            $affordabilityScore = $this->calculateAffordabilityScore();
            $details[] = [
                'criterion' => 'Affordability',
                'description' => "Monthly payment: " . number_format($this->monthlyInstallmentValue ?? 0, 2) . " TZS",
                'score' => $affordabilityScore,
                'weight' => 10,
                'recommendation' => $this->getAffordabilityRecommendation($affordabilityScore)
            ];
            $totalScore += $affordabilityScore * 10;
            $totalWeight += 10;

            // Calculate overall score
            $overallScore = $totalWeight > 0 ? ($totalScore / $totalWeight) : 0;

            // Generate recommendations
            $recommendations = $this->generateOverallRecommendations($overallScore, $details);

            $this->assessmentData = [
                'overall_score' => round($overallScore, 1),
                'details' => $details,
                'recommendations' => $recommendations,
                'calculated_at' => now()
            ];

            Log::info('=== CALCULATE ASSESSMENT SCORE END ===', [
                'overall_score' => $overallScore,
                'details_count' => count($details),
                'recommendations_count' => count($recommendations)
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculating assessment score: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->assessmentData = [
                'overall_score' => 0,
                'details' => [],
                'recommendations' => ['Error calculating assessment score']
            ];
        }
    }

    /**
     * Calculate credit score value (0-100)
     */
    private function calculateCreditScoreValue($score, $grade)
    {
        if ($score >= 750) return 100;
        if ($score >= 700) return 90;
        if ($score >= 650) return 80;
        if ($score >= 600) return 70;
        if ($score >= 550) return 60;
        if ($score >= 500) return 50;
        if ($score >= 450) return 40;
        if ($score >= 400) return 30;
        if ($score >= 350) return 20;
        return 10;
    }

    /**
     * Calculate income score (0-100) - Using Take Home Salary/Sales Proceeds
     */
    private function calculateIncomeScore()
    {
        $takeHomeSalary = $this->take_home ?? 0;
        
        if ($takeHomeSalary >= 2000000) return 100;
        if ($takeHomeSalary >= 1000000) return 95;
        if ($takeHomeSalary >= 500000) return 90;
        if ($takeHomeSalary >= 300000) return 85;
        if ($takeHomeSalary >= 200000) return 80;
        if ($takeHomeSalary >= 150000) return 75;
        if ($takeHomeSalary >= 100000) return 70;
        if ($takeHomeSalary >= 75000) return 65;
        if ($takeHomeSalary >= 50000) return 60;
        if ($takeHomeSalary >= 30000) return 50;
        if ($takeHomeSalary >= 20000) return 40;
        if ($takeHomeSalary >= 10000) return 30;
        if ($takeHomeSalary >= 5000) return 20;
        return 10;
    }

    /**
     * Calculate member activeness score (0-100) - SACCO specific
     */
    private function calculateMemberActivenessScore()
    {
        try {
            $clientNumber = $this->loan->client_number ?? '';
            
            if (empty($clientNumber)) {
                Log::warning('No client number available for member activeness calculation');
                return 50;
            }

            // Get account balances from accounts table
            $savingsAccount = DB::table('accounts')
                ->where('client_number', $clientNumber)
                ->where('product_number', '2000')
                ->where('status', 'ACTIVE')
                ->first();

            $sharesAccount = DB::table('accounts')
                ->where('client_number', $clientNumber)
                ->where('product_number', '1000')
                ->where('status', 'ACTIVE')
                ->first();

            $depositsAccount = DB::table('accounts')
                ->where('client_number', $clientNumber)
                ->where('product_number', '3000')
                ->where('status', 'ACTIVE')
                ->first();

            // Get account balances
            $savingsBalance = $savingsAccount ? (float)$savingsAccount->balance : 0;
            $sharesBalance = $sharesAccount ? (float)$sharesAccount->balance : 0;
            $depositsBalance = $depositsAccount ? (float)$depositsAccount->balance : 0;

            // Calculate total member funds
            $totalMemberFunds = $savingsBalance + $sharesBalance + $depositsBalance;

            // Get transaction activity for the past 3 months
            $threeMonthsAgo = now()->subMonths(3);
            $accountNumbers = array_filter([
                $savingsAccount ? $savingsAccount->account_number : null,
                $sharesAccount ? $sharesAccount->account_number : null,
                $depositsAccount ? $depositsAccount->account_number : null
            ]);

            $transactionActivity = 0;
            if (!empty($accountNumbers)) {
                $transactions = DB::table('general_ledger')
                    ->whereIn('record_on_account_number', $accountNumbers)
                    ->where('created_at', '>=', $threeMonthsAgo)
                    ->where('trans_status', 'SUCCESS')
                    ->get();

                $transactionActivity = $transactions->count();
            }

            // Calculate loan amount for comparison
            $loanAmount = $this->approved_loan_value ?? 0;

            if ($loanAmount <= 0) {
                Log::warning('No loan amount available for member activeness calculation');
                return 50;
            }

            // Calculate member funds to loan ratio
            $memberFundsRatio = $totalMemberFunds / $loanAmount;

            // Calculate activity score based on transactions
            $activityScore = $this->calculateActivityScore($transactionActivity);

            // Calculate balance score based on member funds ratio
            $balanceScore = $this->calculateBalanceScore($memberFundsRatio);

            // Calculate savings consistency score
            $savingsConsistencyScore = $this->calculateSavingsConsistencyScore($savingsBalance, $loanAmount);

            // Weighted average: 50% balance, 30% activity, 20% savings consistency
            $finalScore = ($balanceScore * 0.5) + ($activityScore * 0.3) + ($savingsConsistencyScore * 0.2);

            Log::info('Member Activeness Calculation', [
                'client_number' => $clientNumber,
                'savings_balance' => $savingsBalance,
                'shares_balance' => $sharesBalance,
                'deposits_balance' => $depositsBalance,
                'total_member_funds' => $totalMemberFunds,
                'loan_amount' => $loanAmount,
                'member_funds_ratio' => $memberFundsRatio,
                'transaction_activity' => $transactionActivity,
                'balance_score' => $balanceScore,
                'activity_score' => $activityScore,
                'savings_consistency_score' => $savingsConsistencyScore,
                'final_score' => $finalScore
            ]);

            return round($finalScore, 0);

        } catch (\Exception $e) {
            Log::error('Error calculating member activeness score: ' . $e->getMessage());
            return 50; // Neutral score on error
        }
    }

    /**
     * Calculate balance score based on member funds to loan ratio
     */
    private function calculateBalanceScore($memberFundsRatio)
    {
        // Score based on member funds ratio (higher is better)
        if ($memberFundsRatio >= 2.0) return 100;    // Excellent: 200%+ of loan
        if ($memberFundsRatio >= 1.5) return 95;     // Very Good: 150%+ of loan
        if ($memberFundsRatio >= 1.2) return 90;     // Good: 120%+ of loan
        if ($memberFundsRatio >= 1.0) return 85;     // Fair: 100%+ of loan
        if ($memberFundsRatio >= 0.8) return 80;     // Acceptable: 80%+ of loan
        if ($memberFundsRatio >= 0.6) return 75;     // Moderate: 60%+ of loan
        if ($memberFundsRatio >= 0.5) return 70;     // Borderline: 50%+ of loan
        if ($memberFundsRatio >= 0.4) return 65;     // Limited: 40%+ of loan
        if ($memberFundsRatio >= 0.3) return 60;     // Poor: 30%+ of loan
        if ($memberFundsRatio >= 0.2) return 50;     // Very Poor: 20%+ of loan
        if ($memberFundsRatio >= 0.1) return 40;     // Critical: 10%+ of loan
        return 30;                                    // Failed: <10% of loan
    }

    /**
     * Calculate activity score based on transaction frequency
     */
    private function calculateActivityScore($transactionCount)
    {
        // Score based on transaction activity in past 3 months
        if ($transactionCount >= 20) return 100;     // Excellent: 20+ transactions
        if ($transactionCount >= 15) return 95;      // Very Good: 15+ transactions
        if ($transactionCount >= 10) return 90;      // Good: 10+ transactions
        if ($transactionCount >= 8) return 85;       // Fair: 8+ transactions
        if ($transactionCount >= 6) return 80;       // Acceptable: 6+ transactions
        if ($transactionCount >= 4) return 75;       // Moderate: 4+ transactions
        if ($transactionCount >= 3) return 70;       // Borderline: 3+ transactions
        if ($transactionCount >= 2) return 65;       // Limited: 2+ transactions
        if ($transactionCount >= 1) return 60;       // Poor: 1+ transactions
        return 40;                                    // Failed: No transactions
    }

    /**
     * Calculate savings consistency score
     */
    private function calculateSavingsConsistencyScore($savingsBalance, $loanAmount)
    {
        if ($loanAmount <= 0) return 50;

        $savingsRatio = $savingsBalance / $loanAmount;

        // Score based on savings balance relative to loan amount
        if ($savingsRatio >= 1.0) return 100;        // Excellent: Savings >= loan
        if ($savingsRatio >= 0.8) return 95;         // Very Good: 80%+ of loan
        if ($savingsRatio >= 0.6) return 90;         // Good: 60%+ of loan
        if ($savingsRatio >= 0.5) return 85;         // Fair: 50%+ of loan
        if ($savingsRatio >= 0.4) return 80;         // Acceptable: 40%+ of loan
        if ($savingsRatio >= 0.3) return 75;         // Moderate: 30%+ of loan
        if ($savingsRatio >= 0.2) return 70;         // Borderline: 20%+ of loan
        if ($savingsRatio >= 0.1) return 65;         // Limited: 10%+ of loan
        if ($savingsRatio >= 0.05) return 60;        // Poor: 5%+ of loan
        if ($savingsRatio > 0) return 50;            // Very Poor: Some savings
        return 30;                                    // Failed: No savings
    }

    /**
     * Calculate collateral score (0-100)
     */
    private function calculateCollateralScore()
    {
        $collateralValue = $this->collateral_value ?? 0;
        $loanAmount = $this->approved_loan_value ?? 0;
        
        if ($loanAmount <= 0) return 50;
        
        $coverageRatio = $collateralValue / $loanAmount;
        
        if ($coverageRatio >= 2.0) return 100;
        if ($coverageRatio >= 1.5) return 90;
        if ($coverageRatio >= 1.2) return 80;
        if ($coverageRatio >= 1.0) return 70;
        if ($coverageRatio >= 0.8) return 60;
        if ($coverageRatio >= 0.6) return 50;
        if ($coverageRatio >= 0.4) return 40;
        if ($coverageRatio >= 0.2) return 30;
        return 20;
    }

    /**
     * Calculate business score (0-100)
     */
    private function calculateBusinessScore()
    {
        $businessAge = $this->loan->business_age ?? 0;
        
        if ($businessAge >= 10) return 100;
        if ($businessAge >= 7) return 90;
        if ($businessAge >= 5) return 80;
        if ($businessAge >= 3) return 70;
        if ($businessAge >= 2) return 60;
        if ($businessAge >= 1) return 50;
        if ($businessAge >= 0.5) return 40;
        return 30;
    }

    /**
     * Calculate LTV score (0-100)
     */
    private function calculateLTVScore()
    {
        $coverage = $this->coverage ?? 0;
        
        if ($coverage >= 200) return 100;
        if ($coverage >= 150) return 90;
        if ($coverage >= 120) return 80;
        if ($coverage >= 100) return 70;
        if ($coverage >= 80) return 60;
        if ($coverage >= 60) return 50;
        if ($coverage >= 40) return 40;
        if ($coverage >= 20) return 30;
        return 20;
    }

    /**
     * Calculate affordability score (0-100) - SACCO rule: max 50% of take home
     */
    private function calculateAffordabilityScore()
    {
        $monthlyPayment = $this->monthlyInstallmentValue ?? 0;
        $takeHomeSalary = $this->take_home ?? 0;
        
        if ($takeHomeSalary <= 0) return 30;
        
        // Calculate payment to take home ratio
        $affordabilityRatio = ($monthlyPayment / $takeHomeSalary) * 100;
        
        // SACCO rule: maximum 50% of take home salary
        if ($affordabilityRatio <= 20) return 100; // Excellent: 20% or less
        if ($affordabilityRatio <= 25) return 95;  // Very Good: 25% or less
        if ($affordabilityRatio <= 30) return 90;  // Good: 30% or less
        if ($affordabilityRatio <= 35) return 85;  // Fair: 35% or less
        if ($affordabilityRatio <= 40) return 80;  // Acceptable: 40% or less
        if ($affordabilityRatio <= 45) return 70;  // Borderline: 45% or less
        if ($affordabilityRatio <= 50) return 60;  // Maximum allowed: 50%
        if ($affordabilityRatio <= 55) return 40;  // Exceeds limit: 55%
        if ($affordabilityRatio <= 60) return 30;  // High risk: 60%
        if ($affordabilityRatio <= 70) return 20;  // Very high risk: 70%
        return 10; // Critical: Above 70%
    }

    /**
     * Get credit score recommendation
     */
    private function getCreditScoreRecommendation($score, $grade)
    {
        if ($score >= 700) return "Excellent credit score - favorable terms available";
        if ($score >= 600) return "Good credit score - standard terms apply";
        if ($score >= 500) return "Fair credit score - consider additional security";
        return "Poor credit score - requires additional collateral or guarantor";
    }

    /**
     * Get income recommendation
     */
    private function getIncomeRecommendation($score)
    {
        if ($score >= 80) return "Strong take home salary - supports loan amount";
        if ($score >= 60) return "Adequate take home salary - monitor affordability";
        if ($score >= 40) return "Limited take home salary - consider reducing loan amount";
        return "Low take home salary - high risk, requires additional security";
    }

    /**
     * Get collateral recommendation
     */
    private function getCollateralRecommendation($score)
    {
        if ($score >= 80) return "Strong collateral coverage";
        if ($score >= 60) return "Adequate collateral - standard terms";
        if ($score >= 40) return "Limited collateral - consider additional security";
        return "Weak collateral - high risk, requires additional security";
    }

    /**
     * Get business recommendation
     */
    private function getBusinessRecommendation($score)
    {
        if ($score >= 80) return "Established business - low risk";
        if ($score >= 60) return "Stable business - standard terms";
        if ($score >= 40) return "New business - monitor closely";
        return "Very new business - high risk, requires additional security";
    }

    /**
     * Get LTV recommendation
     */
    private function getLTVRecommendation($score)
    {
        if ($score >= 80) return "Excellent loan-to-value ratio";
        if ($score >= 60) return "Good loan-to-value ratio";
        if ($score >= 40) return "Acceptable loan-to-value ratio";
        return "High loan-to-value ratio - consider additional collateral";
    }

    /**
     * Get affordability recommendation
     */
    private function getAffordabilityRecommendation($score)
    {
        if ($score >= 80) return "Highly affordable - well within SACCO limits (â‰¤40% of take home)";
        if ($score >= 60) return "Affordable - within SACCO limits (â‰¤50% of take home)";
        if ($score >= 40) return "Borderline - exceeds SACCO limits (>50% of take home)";
        return "Unaffordable - significantly exceeds SACCO limits, reduce loan amount";
    }

    /**
     * Generate overall recommendations
     */
    private function generateOverallRecommendations($overallScore, $details)
    {
        $recommendations = [];

        if ($overallScore >= 80) {
            $recommendations[] = "Excellent loan application - recommend approval with standard terms";
        } elseif ($overallScore >= 60) {
            $recommendations[] = "Good loan application - recommend approval with standard terms";
        } elseif ($overallScore >= 40) {
            $recommendations[] = "Fair loan application - recommend approval with additional conditions";
            $recommendations[] = "Consider requiring additional collateral or guarantor";
        } else {
            $recommendations[] = "Poor loan application - recommend rejection or significant modifications";
            $recommendations[] = "Require substantial additional security or guarantor";
        }

        foreach ($details as $detail) {
            if (($detail['score'] ?? 0) < 40) {
                $recommendations[] = "Address {$detail['criterion']}: {$detail['recommendation']}";
            }
        }

        return $recommendations;
    }

    /**
     * Get member activeness recommendation
     */
    private function getMemberActivenessRecommendation($score)
    {
        if ($score >= 80) return "Excellent member participation - strong savings and shares";
        if ($score >= 60) return "Good member participation - adequate savings and shares";
        if ($score >= 40) return "Limited member participation - consider encouraging more savings";
        return "Poor member participation - high risk, requires additional security";
    }

    /**
     * Get detailed risk analysis
     */
    private function getDetailedRiskAnalysis($assessmentData)
    {
        $analysis = [];
        
        foreach ($assessmentData['details'] ?? [] as $detail) {
            $score = $detail['score'] ?? 0;
            $criterion = $detail['criterion'] ?? '';
            
            if ($score < 40) {
                $analysis[] = [
                    'type' => 'CRITICAL',
                    'criterion' => $criterion,
                    'score' => $score,
                    'impact' => 'High - May require additional security or guarantor'
                ];
            } elseif ($score < 60) {
                $analysis[] = [
                    'type' => 'WARNING',
                    'criterion' => $criterion,
                    'score' => $score,
                    'impact' => 'Medium - Requires monitoring and conditions'
                ];
            }
        }
        
        return $analysis;
    }

    /**
     * Get loan approval conditions based on assessment
     */
    private function getApprovalConditions($assessmentData, $loan, $member)
    {
        $conditions = [];
        $score = $assessmentData['overall_score'] ?? 0;
        
        // Credit score conditions
        $creditScore = $this->creditScoreData['score'] ?? 500;
        if ($creditScore < 600) {
            $conditions[] = 'Require additional guarantor with good credit history';
        }
        
        // Income conditions
        $monthlyPayment = $this->monthlyInstallmentValue ?? 0;
        $takeHome = $this->take_home ?? 0;
        $paymentRatio = $takeHome > 0 ? ($monthlyPayment / $takeHome) * 100 : 0;
        
        if ($paymentRatio > 50) {
            $conditions[] = 'Monthly payment exceeds 50% of take home salary - require additional income verification';
        }
        
        // Collateral conditions
        $collateralRatio = $this->collateral_value && $this->approved_loan_value ? 
            ($this->collateral_value / $this->approved_loan_value) * 100 : 0;
        
        if ($collateralRatio < 120) {
            $conditions[] = 'Collateral coverage below 120% - require additional security';
        }
        
        // Member activeness conditions
        if ($score < 60) {
            $conditions[] = 'Member activeness below acceptable level - require regular savings commitment';
        }
        
        // General conditions based on overall score
        if ($score < 70) {
            $conditions[] = 'Regular monitoring required - monthly payment tracking';
            $conditions[] = 'Quarterly review of member financial status';
        }
        
        return $conditions;
    }

    /**
     * Get financial impact analysis
     */
    private function getFinancialImpactAnalysis($loan, $product)
    {
        $approvedAmount = $this->approved_loan_value ?? 0;
        $term = $this->approved_term ?? 0;
        $interestRate = $product->interest_value ?? 0;
        $monthlyPayment = $this->monthlyInstallmentValue ?? 0;
        
        $totalInterest = ($monthlyPayment * $term) - $approvedAmount;
        $totalRepayment = $monthlyPayment * $term;
        $interestToPrincipalRatio = $approvedAmount > 0 ? ($totalInterest / $approvedAmount) * 100 : 0;
        
            return [
            'total_interest' => $totalInterest,
            'total_repayment' => $totalRepayment,
            'interest_to_principal_ratio' => $interestToPrincipalRatio,
            'monthly_payment' => $monthlyPayment,
            'annual_interest_cost' => $totalInterest / ($term / 12)
        ];
    }

    /**
     * Create Financial Analysis Sheet
     */
    private function createFinancialAnalysisSheet($spreadsheet, $loan, $member, $product, $assessmentData)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Financial Analysis');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $subHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $sectionHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '70AD47']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        // Report Header
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'FINANCIAL ANALYSIS & RISK ASSESSMENT');
        $sheet->getStyle('A1')->applyFromArray($headerStyle);
        $sheet->getStyle('A1')->getFont()->setSize(16);

        // Loan Summary
        $row = 3;
        $sheet->setCellValue('A' . $row, 'LOAN SUMMARY');
        $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, 'Loan Amount');
        $sheet->setCellValue('B' . $row, number_format($this->approved_loan_value ?? 0, 2) . ' TZS');
        $sheet->setCellValue('C' . $row, 'Term (Months)');
        $sheet->setCellValue('D' . $row, ($this->approved_term ?? 0) . ' months');
        $row++;

        $sheet->setCellValue('A' . $row, 'Interest Rate (Annual)');
        $sheet->setCellValue('B' . $row, number_format($product->interest_value ?? 0, 2) . '%');
        $sheet->setCellValue('C' . $row, 'Monthly Payment');
        $sheet->setCellValue('D' . $row, number_format($this->monthlyInstallmentValue ?? 0, 2) . ' TZS');
        $row++;

        // Financial Impact Analysis
        $financialImpact = $this->getFinancialImpactAnalysis($loan, $product);
        
        $row += 2;
        $sheet->setCellValue('A' . $row, 'FINANCIAL IMPACT ANALYSIS');
        $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Interest Payable');
        $sheet->setCellValue('B' . $row, number_format($financialImpact['total_interest'] ?? 0, 2) . ' TZS');
        $sheet->setCellValue('C' . $row, 'Total Repayment');
        $sheet->setCellValue('D' . $row, number_format($financialImpact['total_repayment'] ?? 0, 2) . ' TZS');
        $row++;

        $sheet->setCellValue('A' . $row, 'Interest to Principal Ratio');
        $sheet->setCellValue('B' . $row, number_format($financialImpact['interest_to_principal_ratio'] ?? 0, 1) . '%');
        $sheet->setCellValue('C' . $row, 'Annual Interest Cost');
        $sheet->setCellValue('D' . $row, number_format($financialImpact['annual_interest_cost'] ?? 0, 2) . ' TZS');
        $row++;

        // Affordability Analysis
        $row += 2;
        $sheet->setCellValue('A' . $row, 'AFFORDABILITY ANALYSIS');
        $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $row++;

        $takeHome = $this->take_home ?? 0;
        $monthlyPayment = $this->monthlyInstallmentValue ?? 0;
        $paymentRatio = $takeHome > 0 ? ($monthlyPayment / $takeHome) * 100 : 0;
        $saccoLimit = 50; // SACCO rule: max 50% of take home

        $sheet->setCellValue('A' . $row, 'Monthly Take Home Salary');
        $sheet->setCellValue('B' . $row, number_format($takeHome, 2) . ' TZS');
        $sheet->setCellValue('C' . $row, 'Payment Ratio');
        $sheet->setCellValue('D' . $row, number_format($paymentRatio, 1) . '%');
        $row++;

        $sheet->setCellValue('A' . $row, 'SACCO Limit (50%)');
        $sheet->setCellValue('B' . $row, number_format($takeHome * 0.5, 2) . ' TZS');
        $sheet->setCellValue('C' . $row, 'Available for Other Expenses');
        $sheet->setCellValue('D' . $row, number_format($takeHome - $monthlyPayment, 2) . ' TZS');
        $row++;

        // Risk Assessment
        $row += 2;
        $sheet->setCellValue('A' . $row, 'RISK ASSESSMENT');
        $sheet->getStyle('A' . $row)->applyFromArray($subHeaderStyle);
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $row++;

        $riskAnalysis = $this->getDetailedRiskAnalysis($assessmentData);
        
        if (!empty($riskAnalysis)) {
            $sheet->setCellValue('A' . $row, 'Risk Type');
            $sheet->setCellValue('B' . $row, 'Criterion');
            $sheet->setCellValue('C' . $row, 'Score (%)');
            $sheet->setCellValue('D' . $row, 'Impact');
            $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($subHeaderStyle);
            $row++;

            foreach ($riskAnalysis as $risk) {
                $sheet->setCellValue('A' . $row, $risk['type']);
                $sheet->setCellValue('B' . $row, $risk['criterion']);
                $sheet->setCellValue('C' . $row, number_format($risk['score'], 1));
                $sheet->setCellValue('D' . $row, $risk['impact']);
                
                // Color code risk levels
                if ($risk['type'] === 'CRITICAL') {
                    $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                    $sheet->getStyle('A' . $row)->getFill()->getStartColor()->setRGB('C00000');
                    $sheet->getStyle('A' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                } elseif ($risk['type'] === 'WARNING') {
                    $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                    $sheet->getStyle('A' . $row)->getFill()->getStartColor()->setRGB('FFC000');
                }
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No significant risks identified');
            $sheet->mergeCells('A' . $row . ':D' . $row);
            $row++;
        }

        // Approval Conditions
        $row += 2;
        $sheet->setCellValue('A' . $row, 'RECOMMENDED APPROVAL CONDITIONS');
        $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $row++;

        $conditions = $this->getApprovalConditions($assessmentData, $loan, $member);
        
        if (!empty($conditions)) {
            foreach ($conditions as $index => $condition) {
                $sheet->setCellValue('A' . $row, ($index + 1) . '.');
                $sheet->setCellValue('B' . $row, $condition);
                $sheet->mergeCells('B' . $row . ':D' . $row);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No special conditions required');
            $sheet->mergeCells('A' . $row . ':D' . $row);
            $row++;
        }

        // Collateral Analysis
        $row += 2;
        $sheet->setCellValue('A' . $row, 'COLLATERAL ANALYSIS');
        $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $row++;

        $collateralValue = $this->collateral_value ?? 0;
        $loanAmount = $this->approved_loan_value ?? 0;
        $collateralRatio = $loanAmount > 0 ? ($collateralValue / $loanAmount) * 100 : 0;
        $ltvPolicy = $product->ltv ?? 0;

        $sheet->setCellValue('A' . $row, 'Collateral Value');
        $sheet->setCellValue('B' . $row, number_format($collateralValue, 2) . ' TZS');
        $sheet->setCellValue('C' . $row, 'Collateral Coverage');
        $sheet->setCellValue('D' . $row, number_format($collateralRatio, 1) . '%');
        $row++;

        $sheet->setCellValue('A' . $row, 'LTV Policy Limit');
        $sheet->setCellValue('B' . $row, $ltvPolicy . '%');
        $sheet->setCellValue('C' . $row, 'Forced Sale Value (70%)');
        $sheet->setCellValue('D' . $row, number_format($collateralValue * 0.7, 2) . ' TZS');
        $row++;

        // Member Financial Position
        $row += 2;
        $sheet->setCellValue('A' . $row, 'MEMBER FINANCIAL POSITION');
        $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $row++;

        // Get member account balances
        $clientNumber = $loan->client_number ?? '';
        $savingsBalance = 0;
        $sharesBalance = 0;
        $depositsBalance = 0;
        
        if (!empty($clientNumber)) {
            $savingsAccount = DB::table('accounts')
                ->where('client_number', $clientNumber)
                ->where('product_number', '2000')
                ->where('status', 'ACTIVE')
                ->first();
            $savingsBalance = $savingsAccount ? (float)$savingsAccount->balance : 0;

            $sharesAccount = DB::table('accounts')
                ->where('client_number', $clientNumber)
                ->where('product_number', '1000')
                ->where('status', 'ACTIVE')
                ->first();
            $sharesBalance = $sharesAccount ? (float)$sharesAccount->balance : 0;

            $depositsAccount = DB::table('accounts')
                ->where('client_number', $clientNumber)
                ->where('product_number', '3000')
                ->where('status', 'ACTIVE')
                ->first();
            $depositsBalance = $depositsAccount ? (float)$depositsAccount->balance : 0;
        }

        $totalMemberFunds = $savingsBalance + $sharesBalance + $depositsBalance;
        $memberFundsRatio = $loanAmount > 0 ? ($totalMemberFunds / $loanAmount) * 100 : 0;

        $sheet->setCellValue('A' . $row, 'Total Savings');
        $sheet->setCellValue('B' . $row, number_format($savingsBalance, 2) . ' TZS');
        $sheet->setCellValue('C' . $row, 'Total Shares');
        $sheet->setCellValue('D' . $row, number_format($sharesBalance, 2) . ' TZS');
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Deposits');
        $sheet->setCellValue('B' . $row, number_format($depositsBalance, 2) . ' TZS');
        $sheet->setCellValue('C' . $row, 'Total Member Funds');
        $sheet->setCellValue('D' . $row, number_format($totalMemberFunds, 2) . ' TZS');
        $row++;

        $sheet->setCellValue('A' . $row, 'Member Funds to Loan Ratio');
        $sheet->setCellValue('B' . $row, number_format($memberFundsRatio, 1) . '%');
        $sheet->setCellValue('C' . $row, 'Assessment Score');
        $sheet->setCellValue('D' . $row, number_format($assessmentData['overall_score'] ?? 0, 1) . '%');
        $row++;

        // Apply borders to all data
        $lastRow = $row - 1;
        $sheet->getStyle('A1:D' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }
}
