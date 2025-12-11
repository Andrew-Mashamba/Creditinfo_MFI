<div>
    <!-- Assessment Completion Banner -->
    @php
    $tabStateService = app(\App\Services\LoanTabStateService::class);
    $loanID = session('currentloanID');
    $isAssessmentCompleted = $tabStateService->isTabCompleted($loanID, 'assessment');
    @endphp



    @php
    use Illuminate\Support\Facades\DB;
    use Carbon\Carbon;

    // Fetch loan details by loan ID
    $loanID = session('currentloanID');
    $loan = DB::table('loans')->find($loanID);
    $exception_status = false;
    $topUpAmount = 0;

    // If loan exists, fetch related client and product details
    if ($loan) {
        $member = DB::table('clients')->where('client_number', $loan->client_number)->first();
        $product = DB::table('loan_sub_products')->where('sub_product_id', $loan->loan_sub_product)->first();
    } else {
        $member = null;
        $product = null;
    }

    // Get credit score data from the service (already loaded in component)
    $creditScore = $this->creditScoreData ?? null;
    $creditScoreValue = $creditScore['score'] ?? 500;
    $creditScoreGrade = $creditScore['grade'] ?? 'E';
    $creditScoreRisk = $creditScore['risk_description'] ?? 'Very High Risk - No Data';
    $creditScoreTrend = $creditScore['trend'] ?? 'Stable';
    $creditScoreDate = $creditScore['date'] ?? now();

    // Get client information data from the service (already loaded in component)
    $clientInfo = $this->clientInfoData ?? null;
    $basicInfo = $clientInfo['basic_info'] ?? [];
    $contactInfo = $clientInfo['contact_info'] ?? [];
    $employmentInfo = $clientInfo['employment_info'] ?? [];
    $financialInfo = $clientInfo['financial_info'] ?? [];
    $riskIndicators = $clientInfo['risk_indicators'] ?? [];
    $demographics = $clientInfo['demographics'] ?? [];
    $statusIndicators = $clientInfo['status_indicators'] ?? [];

    // Get product parameters data from the service (already loaded in component)
    $productParams = $this->productParamsData ?? null;
    $productBasicInfo = $productParams['basic_info'] ?? [];
    $productLoanLimits = $productParams['loan_limits'] ?? [];
    $productInterestInfo = $productParams['interest_info'] ?? [];
    $productGracePeriods = $productParams['grace_periods'] ?? [];
    $productFeesAndCharges = $productParams['fees_and_charges'] ?? [];
    $productInsuranceInfo = $productParams['insurance_info'] ?? [];
    $productRepaymentInfo = $productParams['repayment_info'] ?? [];
    $productAccountInfo = $productParams['account_info'] ?? [];
    $productRequirements = $productParams['requirements'] ?? [];
    $productValidation = $productParams['validation'] ?? [];

    // Set the standard retirement age
    $retirementAge = 60; // Adjust based on your requirements

    // Check if date_of_birth is available
    if (!is_null($member->date_of_birth)) {
        // Parse date of birth
        $dob = Carbon::parse($member->date_of_birth);

        // Calculate current age
        $age = $dob->age;

        // Calculate remaining years to retirement
        $yearsToRetirement = $retirementAge - $age;

        // Ensure the remaining years to retirement is not negative
        $yearsToRetirement = max(0, $yearsToRetirement);

        // Calculate the retirement date by adding the remaining years to the date of birth
        $retirementDate = $dob->copy()->addYears($retirementAge);

        // Adjust the day to the 30th or 31st of the retirement month
        $lastDayOfMonth = $retirementDate->endOfMonth()->day;
        $retirementDay = $lastDayOfMonth == 31 ? 31 : 30;
        $retirementDate->day = $retirementDay;

        // Calculate months to retirement (from now to the retirement date)
        $monthsToRetirement = now()->diffInMonths($retirementDate);
    } else {
        $monthsToRetirement = null;
        $retirementDate = null;
    }

    $savings = DB::table('accounts')->where('product_number','20000')->where('client_number',$loan->client_number)->sum('balance');

    $loanCount = Illuminate\Support\Facades\DB::table('loans')->where('client_number',$loan->client_number)->where('status','ACTIVE')->count() + 2;

    if ($this->non_permanent_income_taxable <= 270000) {
        $tax = 0;
    } elseif ($this->non_permanent_income_taxable <= 520000) {
        $tax = 0.08 * ($this->non_permanent_income_taxable - 270000);
    } elseif ($this->non_permanent_income_taxable <= 760000) {
        $tax = 20000 + 0.20 * ($this->non_permanent_income_taxable - 520000);
    } elseif ($this->non_permanent_income_taxable <= 1000000) {
        $tax = 68000 + 0.25 * ($this->non_permanent_income_taxable - 760000);
    } else {
        $tax = 128000 + 0.30 * ($this->non_permanent_income_taxable - 1000000);
    }

    $records = \Illuminate\Support\Facades\DB::table('query_responses')
        ->where('CheckNumber', 111222333)
        ->get();

    $totalContracts = 0; // Initialize total contract count
    $totalContracts2 = 3;
    $ctotalAmount = 0;
    // First pass to count the total number of contracts
    foreach ($records as $record) {
        $response_data = json_decode($record->response_data, true);
        $contracts = $response_data['response']['CustomReport']['Contracts']['ContractList']['Contract'] ?? [];

        if (isset($contracts['Subscriber'])) {
            $totalContracts += 1; // Single contract object
            $ctotalAmount += $contracts['TotalAmount']['Value']; // Add to total amount
        } elseif (is_array($contracts)) {
            $totalContracts += count($contracts); // Array of contracts
            foreach ($contracts as $contract) {
                $ctotalAmount += $contract['TotalAmount']['Value']; // Add to total amount
            }
        }
    }

    // Note: approved_loan_value initialization is now handled in the component's boot() method
    // to prevent overwriting user input during render

    // Get settlement data from the component
    $settlementData = $this->settlementData ?? [];
    $selectedLoansToSettle = $this->selectedLoansToSettle ?? [];

    // Get loan and active loans data from the component
    $nbcLoansData = $this->nbcLoansData ?? [];
    $activeLoans = $nbcLoansData['loans'] ?? collect();
    $selectedLoan = $this->selectedLoan ?? null;

    // Get assessment data from the component
    $assessmentResult = $this->assessmentData ?? [];

    // Get loan repayment schedule data from the component
    $schedule = $this->schedule ?? [];
    $footer = $this->footer ?? [];
    $loanAmount = $this->approved_loan_value ?? 0;
    $totalInterest = $this->totalInterest ?? 0;
    $monthlyPayment = $this->monthlyInstallmentValue ?? 0;
    $loanTerm = $this->approved_term ?? 0;
    $completedPayments = $this->completedPayments ?? 0;
    $pendingPayments = $this->pendingPayments ?? 0;
    $overduePayments = $this->overduePayments ?? 0;
    $interestRate = $this->interestRate ?? 0;
    $paymentFrequency = $this->paymentFrequency ?? 'Monthly';
    @endphp

    <div class="w-full flex gap-4">
        <div class="w-1/3">
            
            @include('livewire.loans.sections.simple-exceptions')
            @include('livewire.loans.sections.simple-product-infor')

        </div>

        <div class="w-2/3">
           
            @include('livewire.loans.sections.assessment')
    
            @include('livewire.loans.sections.loans-to-be-topped-up-simplified')
            @include('livewire.loans.sections.select-loan-to-restructure-simplified')

            @include('livewire.loans.sections.loan-repayment-schedule')
        </div>
    </div>

  

   
    <script nonce="{{ csp_nonce() }}">
        // Function to ensure exceptions are calculated
        function ensureExceptionsCalculated() {
            console.log('Ensuring exceptions are calculated after successful rendering');
            
            // Try Livewire.find method first
            if (typeof Livewire !== 'undefined' && Livewire.find) {
                try {
                    const wireElement = document.querySelector('[wire\\:id]');
                    if (wireElement) {
                        const component = Livewire.find(wireElement.getAttribute('wire:id'));
                        if (component) {
                            component.call('ensureExceptionsCalculated');
                            return;
                        }
                    }
                } catch (error) {
                    console.log('Livewire.find method failed, trying alternative approach');
                }
            }
            
            // Fallback: try to find the component using Alpine.js
            if (typeof Alpine !== 'undefined') {
                try {
                    const component = Alpine.$data(document.querySelector('[wire\\:id]'));
                    if (component && component.$wire) {
                        component.$wire.call('ensureExceptionsCalculated');
                        return;
                    }
                } catch (error) {
                    console.log('Alpine.js method failed');
                }
            }
            
            console.log('Could not find Livewire component for exception calculation');
        }

        // Listen for DOM content loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Assessment component DOM fully loaded');
            
            // Wait a bit more to ensure Livewire is fully hydrated
            setTimeout(function() {
                ensureExceptionsCalculated();
            }, 1000);
        });

        // Listen for Livewire load
        document.addEventListener('livewire:load', function() {
            console.log('Livewire loaded, setting up exception calculation listeners');
        });

        // Listen for exceptions calculated event
        document.addEventListener('exceptions-calculated', function(event) {
            console.log('Exceptions calculated successfully:', event.detail);
        });

        // Listen for assessment component rendered event
        window.addEventListener('assessment-component-rendered', function(event) {
            console.log('Assessment component rendered:', event.detail);
            
            // Ensure exceptions are calculated after component is fully rendered
            setTimeout(function() {
                ensureExceptionsCalculated();
            }, 200);
        });
        
    </script>
</div> 