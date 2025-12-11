{{-- ASSESSMENT SECTION --}}
@php
$tabStateService = app(\App\Services\LoanTabStateService::class);
$loanID = session('currentloanID');
$isAssessmentCompleted = $tabStateService->isTabCompleted($loanID, 'assessment');

// Calculate loan amounts and payments
$theInt = (float)($product->interest_value ?? 0)/100;
$loanAmt = 0;

if(isset($take_home) && isset($approved_term)) {
    $loanAmt = calculateTotalLoanAmount($theInt, (float)$approved_term, (float)$take_home);
}

// Loan calculation function
function calculateTotalLoanAmount($interestRate, $tenure, $takeHome) {
    if ($tenure <= 0 || $takeHome <= 0) {
        return 0;
    }
    
    $monthlyInterestRate = $interestRate / 12;

    if ($monthlyInterestRate == 0 || $monthlyInterestRate < 0.000001) {
        $presentValue = $takeHome * $tenure;
    } else {
        $presentValue = ($takeHome * (1 - pow(1 + $monthlyInterestRate, -$tenure))) / $monthlyInterestRate;
    }

    return floor($presentValue / 10000) * 10000;
}

// Check for stored deduction breakdown in assessment data
$storedDeductionBreakdown = null;
$storedChargesBreakdown = null;
if (isset($loan->assessment_data) && $loan->assessment_data) {
    $assessmentData = json_decode($loan->assessment_data, true);
    if (isset($assessmentData['deductionBreakdown'])) {
        $storedDeductionBreakdown = $assessmentData['deductionBreakdown'];
    }
    if (isset($assessmentData['chargesBreakdown'])) {
        $storedChargesBreakdown = $assessmentData['chargesBreakdown'];
    }
}

// Ensure deduction amounts are calculated and ensure positive values
if (!isset($totalCharges) || $totalCharges === null) {
    $totalCharges = 0;
}
if (!isset($totalInsurance) || $totalInsurance === null) {
    $totalInsurance = 0;
}
if (!isset($firstInstallmentInterestAmount) || $firstInstallmentInterestAmount === null) {
    $firstInstallmentInterestAmount = 0;
}
if (!isset($topUpAmount) || $topUpAmount === null) {
    $topUpAmount = 0;
}
if (!isset($closedLoanBalance) || $closedLoanBalance === null) {
    $closedLoanBalance = 0;
}
if (!isset($totalAmount) || $totalAmount === null) {
    $totalAmount = 0;
}
if (!isset($approved_loan_value) || $approved_loan_value === null) {
    $approved_loan_value = 0;
}
if (!isset($approved_term) || $approved_term === null) {
    $approved_term = 0;
}
if (!isset($take_home) || $take_home === null) {
    $take_home = 0;
}

// Ensure top-up amount is calculated if this is a top-up loan
if ($loan && in_array($loan->loan_type_2, ['Top-up', 'TopUp', 'Top Up'])) {
    // Priority 1: Get from top_up_amount field directly
    if (isset($loan->top_up_amount) && $loan->top_up_amount > 0) {
        $topUpAmount = abs($loan->top_up_amount);
    }
    // Priority 2: Calculate from top_up_loan_id (for existing loans)
    elseif (isset($loan->top_up_loan_id) && $loan->top_up_loan_id) {
        $topupLoan = DB::table('loans')->where('id', $loan->top_up_loan_id)->first();
        if ($topupLoan && $topupLoan->loan_account_number) {
            $topupAccount = DB::table('accounts')->where('account_number', $topupLoan->loan_account_number)->first();
            if ($topupAccount) {
                $topUpAmount = abs($topupAccount->balance ?? 0);
            }
        }
    }
    // Priority 3: Try selectedLoan field (for new loans)
    elseif ($loan->selectedLoan) {
        $topupLoan = DB::table('loans')->where('id', $loan->selectedLoan)->first();
        if ($topupLoan && $topupLoan->loan_account_number) {
            $topupAccount = DB::table('accounts')->where('account_number', $topupLoan->loan_account_number)->first();
            if ($topupAccount) {
                $topUpAmount = abs($topupAccount->balance ?? 0);
            }
        }
    }
    // Priority 4: Try assessment data
    elseif (isset($loan->assessment_data) && $loan->assessment_data) {
        $assessmentData = json_decode($loan->assessment_data, true);
        if (isset($assessmentData['top_up_amount']) && $assessmentData['top_up_amount'] > 0) {
            $topUpAmount = abs($assessmentData['top_up_amount']);
        }
    }
    
    // Ensure topUpAmount is set
    if (!isset($topUpAmount) || $topUpAmount === null) {
        $topUpAmount = 0;
    }
}

// Get penalty amount from loan data if this is a top-up loan
$penaltyAmount = 0;
if ($loan && in_array($loan->loan_type_2, ['Top-up', 'TopUp', 'Top Up'])) {
    // Priority 1: Get from top_up_penalty_amount field directly
    if (isset($loan->top_up_penalty_amount) && $loan->top_up_penalty_amount > 0) {
        $penaltyAmount = abs($loan->top_up_penalty_amount);
    }
    // Priority 2: Calculate from top-up amount (5% penalty)
    elseif ($topUpAmount > 0) {
        $productPenaltyPercentage = (float)($product->penalty_value ?? 5.0) / 100;
        $penaltyAmount = $topUpAmount * $productPenaltyPercentage;
    }
    // Priority 3: Try assessment data
    elseif (isset($loan->assessment_data) && $loan->assessment_data) {
        $assessmentData = json_decode($loan->assessment_data, true);
        if (isset($assessmentData['penalty_amount']) && $assessmentData['penalty_amount'] > 0) {
            $penaltyAmount = abs($assessmentData['penalty_amount']);
        }
    }
}

// For restructure loans, calculate the restructure amount from original loan outstanding + arrears
$restructureAmount = 0;
if ($loan && in_array($loan->loan_type_2, ['Restructure', 'Restructuring'])) {
    // First try to get from restructure_amount field directly
    if (isset($loan->restructure_amount) && $loan->restructure_amount > 0) {
        $restructureAmount = (float)($loan->restructure_amount);
    }
    // If not found, calculate dynamically from original loan
    elseif (isset($loan->restructure_loan_id) && $loan->restructure_loan_id) {
        $originalLoan = DB::table('loans')->where('id', $loan->restructure_loan_id)->first();
        if ($originalLoan) {
            // Get outstanding balance from account
            $account = DB::table('accounts')->where('account_number', $originalLoan->loan_account_number)->first();
            $outstandingBalance = $account ? abs($account->balance ?? 0) : 0;
            
            // Get arrears from loans_schedules
            $arrears = DB::table('loans_schedules')
                ->where('loan_id', $originalLoan->id)
                ->where('completion_status', '!=', 'ACTIVE')
                ->sum('amount_in_arrears');
            
            // Calculate restructure amount = outstanding + arrears
            $restructureAmount = $outstandingBalance + $arrears;
        }
    }
    
    // If not found via restructure_loan_id, try assessment data sources
    if ($restructureAmount == 0) {
        if (isset($assessmentResult['restructure_amount']) && $assessmentResult['restructure_amount'] > 0) {
            $restructureAmount = (float)($assessmentResult['restructure_amount']);
        } elseif (isset($loan->assessment_data) && $loan->assessment_data) {
            $assessmentData = json_decode($loan->assessment_data, true);
            if (isset($assessmentData['restructure_amount']) && $assessmentData['restructure_amount'] > 0) {
                $restructureAmount = (float)($assessmentData['restructure_amount']);
            } elseif (isset($assessmentData['loan_type_data']['restructure_amount']) && $assessmentData['loan_type_data']['restructure_amount'] > 0) {
                $restructureAmount = (float)($assessmentData['loan_type_data']['restructure_amount']);
            }
        }
    }
}

// For restructure loans, settlements should be 0
if ($loan && in_array($loan->loan_type_2, ['Restructure', 'Restructuring'])) {
    $totalAmount = 0;
}

// Ensure all deduction values are positive
$firstInstallmentInterestAmount = abs($firstInstallmentInterestAmount ?? 0);
$totalCharges = abs($totalCharges ?? 0);
$totalInsurance = abs($totalInsurance ?? 0);
$totalAmount = abs($totalAmount ?? 0);
$topUpAmount = abs($topUpAmount ?? 0);
$penaltyAmount = abs($penaltyAmount ?? 0);
$restructureAmount = abs($restructureAmount ?? 0);

// Calculate total deductions with fresh values
// Include First Interest for all loan types (including Restructure)
$totalDeductions = (float)($firstInstallmentInterestAmount ?? 0) +
                   (float)($totalCharges ?? 0) +
                   (float)($totalInsurance ?? 0) +
                   (float)($totalAmount ?? 0) +
                   (float)($topUpAmount ?? 0) +
                   (float)($penaltyAmount ?? 0);

// Calculate buffer for restructure loans (covers loan processing costs)
$bufferAmount = 0;
if ($loan && in_array($loan->loan_type_2, ['Restructure', 'Restructuring'])) {
    // Buffer = Management Fees + Insurance + First Interest (to cover processing costs)
    $bufferAmount = (float)($totalCharges ?? 0) +
                    (float)($totalInsurance ?? 0) +
                    (float)($firstInstallmentInterestAmount ?? 0);
}

$netDisbursement = (float)($approved_loan_value ?? 0) - (float)($totalDeductions ?? 0);



// Use stored deduction breakdown if available (for persistence only)
// Display values are calculated fresh below

// Calculate First Interest
$monthlyInterestRate = $theInt / 12;
$principal = (float)($approved_loan_value ?? 0);

// Get payroll date from member_group
$dayOfMonth = 15; // Default value
if (isset($member->client_number)) {
    // Get client record
    $client = DB::table('clients')->where('client_number', $member->client_number)->first();
    if ($client && $client->member_group) {
        // Get payroll date from member_groups table
        $memberGroup = DB::table('member_groups')->where('group_id', $client->member_group)->first();
        if ($memberGroup && $memberGroup->payrol_date) {
            $dayOfMonth = (int)$memberGroup->payrol_date;
        }
    }
}

// Calculate first installment interest amount and get days
$firstInterestResult = calculateFirstInterestAmount($principal, $monthlyInterestRate, $dayOfMonth);
$firstInstallmentInterestAmount = $firstInterestResult['amount'];
$daysBetween = $firstInterestResult['days'];

// Create graceData array for display
$graceData = [['days' => $daysBetween, 'balance' => $firstInstallmentInterestAmount]];

// First Interest calculation function
function calculateFirstInterestAmount($principal, $monthlyInterestRate, $dayOfTheMonth) {
    try {
        if ($principal <= 0 || $monthlyInterestRate <= 0 || $dayOfTheMonth <= 0) {
            return ['amount' => 0, 'days' => 0];
        }
        
        $disbursementDate = new DateTime();
        $nextDrawdownDate = clone $disbursementDate;
        $nextDrawdownDate->setDate($disbursementDate->format('Y'), $disbursementDate->format('m'), $dayOfTheMonth);

        if ($disbursementDate->format('Y-m-d') === $nextDrawdownDate->format('Y-m-d')) {
            $daysBetween = 0;
        } else {
            if ($disbursementDate > $nextDrawdownDate) {
                $nextDrawdownDate->modify('first day of next month');
                $nextDrawdownDate->setDate($nextDrawdownDate->format('Y'), $nextDrawdownDate->format('m'), $dayOfTheMonth);
            }
            $daysBetween = $disbursementDate->diff($nextDrawdownDate)->days;
        }

        $daysInMonth = (int) $disbursementDate->format('t');
        $dailyInterestRate = $monthlyInterestRate / $daysInMonth;
        $interestAccrued = $principal * $dailyInterestRate * $daysBetween;

        return ['amount' => $interestAccrued, 'days' => $daysBetween];
    } catch (\Exception $e) {
        return ['amount' => 0, 'days' => 0];
    }
}
@endphp

<div class="w-full bg-white border border-gray-200 rounded-lg">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-sm font-semibold text-gray-900">Loan Assessment</h3>
        <span class="text-sm font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ $loan->loan_type_2 ?? 'New' }}</span>
    </div>

    <div class="p-4 space-y-4">
        <!-- Original Request Summary -->


        <!-- Loan Amount Limits Table -->
        <div>
            <h4 class="text-xs font-medium text-gray-700 mb-2">Loan Amount Limits</h4>
            <div class="grid grid-cols-4 gap-2 text-xs">
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Maximum Qualifying</div>
                    <div class="font-semibold">{{ number_format((float)($loanAmt ?? 0), 2) }} TZS</div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Product Maximum</div>
                    <div class="font-semibold">{{ number_format((float)($product->principle_max_value ?? 0), 2) }} TZS</div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Requested Amount
                        @if($loan && in_array($loan->loan_type_2, ['Restructure', 'Restructuring']))
                            <span class="text-xs text-gray-500">(w/ buffer)</span>
                        @endif
                    </div>
                    <div class="font-semibold">
                        @php
                            $requestedAmount = (float)($loan->principle ?? 0);
                            if($loan && in_array($loan->loan_type_2, ['Restructure', 'Restructuring'])) {
                                $requestedAmount += (float)($bufferAmount ?? 0);
                            }
                        @endphp
                        {{ number_format($requestedAmount, 2) }} TZS
                    </div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Available for Repayment (DSR 67%)</div>
                    <div class="font-semibold">
                        {{ number_format((float)($take_home ?? 0)*(67/100), 2) }} TZS
                    </div>
                </div>
            </div>
        </div>

        <!-- Restructured Loan Information -->
        @if($loan && in_array($loan->loan_type_2, ['Restructure', 'Restructuring']) && isset($loan->restructure_loan_id) && $loan->restructure_loan_id)
        @php
            $originalLoan = DB::table('loans')->where('id', $loan->restructure_loan_id)->first();
            $originalProduct = null;
            $outstandingBalance = 0;
            $arrears = 0;
            $daysInArrears = 0;
            
            if ($originalLoan) {
                // Get original loan product
                $originalProduct = DB::table('loan_sub_products')->where('sub_product_id', $originalLoan->loan_sub_product)->first();
                
                // Get outstanding balance from account
                $account = DB::table('accounts')->where('account_number', $originalLoan->loan_account_number)->first();
                $outstandingBalance = $account ? abs($account->balance ?? 0) : 0;
                
                // Get arrears from loans_schedules
                $arrears = DB::table('loans_schedules')
                    ->where('loan_id', $originalLoan->id)
                    ->where('completion_status', '!=', 'ACTIVE')
                    ->sum('amount_in_arrears');
                
                // Get days in arrears (from loan record)
                $daysInArrears = $originalLoan->days_in_arrears ?? 0;
            }
        @endphp
        
        <div>
            <h4 class="text-xs font-medium text-gray-700 mb-2">Restructured Loan Information</h4>
            <div class="grid grid-cols-4 gap-2 text-xs">
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Original Loan ID</div>
                    <div class="font-semibold text-red-900">{{ $originalLoan->loan_id ?? 'N/A' }}</div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Loan Product</div>
                    <div class="font-semibold">{{ $originalProduct->sub_product_name ?? 'N/A' }}</div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Original Principle</div>
                    <div class="font-semibold">{{ number_format((float)($originalLoan->principle ?? 0), 2) }} TZS</div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Interest Amount</div>
                    <div class="font-semibold">{{ number_format((float)($originalLoan->interest ?? 0), 2) }} TZS</div>
                </div>
            </div>
            
            <div class="grid grid-cols-4 gap-2 text-xs mt-2">
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Outstanding Balance</div>
                    <div class="font-semibold text-red-600">{{ number_format($outstandingBalance, 2) }} TZS</div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Days in Arrears</div>
                    <div class="font-semibold {{ $daysInArrears > 30 ? 'text-red-600' : ($daysInArrears > 7 ? 'text-yellow-600' : 'text-green-600') }}">
                        {{ $daysInArrears }} days
                    </div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Amount in Arrears</div>
                    <div class="font-semibold text-red-600">{{ number_format($arrears, 2) }} TZS</div>
                </div>
                <div class="p-2 border border-gray-200 rounded bg-red-50">
                    <div class="text-gray-600 font-semibold">Total to Restructure</div>
                    <div class="font-semibold text-red-900">{{ number_format($restructureAmount, 2) }} TZS</div>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-2 text-xs mt-2">
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Buffer</div>
                    <div class="font-semibold text-red-600">
                        {{ number_format($bufferAmount, 2) }} TZS
                    </div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600 text-xs">Buffer covers processing costs:</div>
                    <div class="font-semibold text-xs text-gray-700">
                        Charges + Insurance + Interest
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Topped-Up Loan Information -->
        @if($loan && in_array($loan->loan_type_2, ['Top-up', 'TopUp', 'Top Up']) && (isset($loan->top_up_loan_id) || isset($loan->top_up_amount)))
        @php
            $originalTopupLoan = null;
            $originalTopupProduct = null;
            $topupOutstandingBalance = 0;
            $topupDaysInArrears = 0;
            $topupPenaltyAmount = 0;
            
            // Get original loan if top_up_loan_id exists
            if (isset($loan->top_up_loan_id) && $loan->top_up_loan_id) {
                $originalTopupLoan = DB::table('loans')->where('id', $loan->top_up_loan_id)->first();
            }
            
            if ($originalTopupLoan) {
                // Get original loan product
                $originalTopupProduct = DB::table('loan_sub_products')->where('sub_product_id', $originalTopupLoan->loan_sub_product)->first();
                
                // Get outstanding balance from account
                $topupAccount = DB::table('accounts')->where('account_number', $originalTopupLoan->loan_account_number)->first();
                $topupOutstandingBalance = $topupAccount ? abs($topupAccount->balance ?? 0) : 0;
                
                // Get days in arrears (from loan record)
                $topupDaysInArrears = $originalTopupLoan->days_in_arrears ?? 0;
            }
            
            // Get penalty amount
            $topupPenaltyAmount = $loan->top_up_penalty_amount ?? 0;
            if ($topupPenaltyAmount == 0 && $topUpAmount > 0) {
                // Calculate penalty if not set
                $productPenaltyPercentage = $product->penalty_percentage ?? 0;
                $topupPenaltyAmount = $topUpAmount * ($productPenaltyPercentage / 100);
            }
            
            // Use the main topUpAmount variable for consistency
            $topupAmount = $topUpAmount ?? 0;
        @endphp
        
        <div>
            <h4 class="text-xs font-medium text-gray-700 mb-2">Topped-Up Loan Information</h4>
            <div class="grid grid-cols-4 gap-2 text-xs">
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Original Loan ID</div>
                    <div class="font-semibold text-red-900">{{ $originalTopupLoan->loan_id ?? 'N/A' }}</div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Loan Product</div>
                    <div class="font-semibold">{{ $originalTopupProduct->sub_product_name ?? 'N/A' }}</div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Original Principle</div>
                    <div class="font-semibold">{{ number_format((float)($originalTopupLoan->principle ?? 0), 2) }} TZS</div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Interest Amount</div>
                    <div class="font-semibold">{{ number_format((float)($originalTopupLoan->interest ?? 0), 2) }} TZS</div>
                </div>
            </div>
            
            <div class="grid grid-cols-4 gap-2 text-xs mt-2">
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Outstanding Balance</div>
                    <div class="font-semibold text-red-600">{{ number_format($topupOutstandingBalance, 2) }} TZS</div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Days in Arrears</div>
                    <div class="font-semibold {{ $topupDaysInArrears > 30 ? 'text-red-600' : ($topupDaysInArrears > 7 ? 'text-yellow-600' : 'text-green-600') }}">
                        {{ $topupDaysInArrears }} days
                    </div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Penalty Amount</div>
                    <div class="font-semibold text-orange-600">{{ number_format($topupPenaltyAmount, 2) }} TZS</div>
                </div>
                <div class="p-2 border border-gray-200 rounded bg-green-50">
                    <div class="text-gray-600 font-semibold">Total to Top-Up</div>
                    <div class="font-semibold text-green-900">{{ number_format($topupAmount, 2) }} TZS</div>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-2 text-xs mt-2">
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Months on book</div>
                    <div class="font-semibold text-red-600">
                        @php
                            $monthsOnBook = 0;
                            if ($originalTopupLoan && $originalTopupLoan->created_at) {
                                $createdDate = \Carbon\Carbon::parse($originalTopupLoan->created_at);
                                $currentDate = \Carbon\Carbon::now();
                                $monthsOnBook = $createdDate->diffInMonths($currentDate);
                            }
                        @endphp
                        {{ $monthsOnBook }} {{ $monthsOnBook == 1 ? 'month' : 'months' }}
                    </div>
                </div>
            </div>

        </div>
        @endif

        <!-- Income Assessment Table -->
        <div>
            <h4 class="text-xs font-medium text-gray-700 mb-2">Income Assessment</h4>
       
            
            <div class="grid grid-cols-4 gap-4 mt-2">

            <div>
                    <label class="block text-xs text-gray-600 mb-1">Take Home Salary</label>
                    <input wire:model="take_home" type="number" step="0.01" min="0"
                        class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                        placeholder="Enter amount">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Repayment Term (Months)</label>
                    <input wire:model="approved_term" type="number" min="1" max="{{ $product->max_term ?? 1 }}"
                        class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Interest Rate (%)</label>
                    <input type="text" value="{{ number_format((float)($product->interest_value ?? 0), 2) }}%" 
                        class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-50 text-gray-600" 
                        disabled readonly>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Loan Amount
                        @if(in_array($loan->loan_type_2 ?? '', ['Restructure', 'Restructuring']))
                            <span class="text-xs text-gray-500">(includes buffer)</span>
                        @endif
                    </label>
                    @if(in_array($loan->loan_type_2 ?? '', ['Restructure', 'Restructuring']))
                        <input type="text"
                            value="{{ number_format((float)($approved_loan_value ?? 0) + (float)($bufferAmount ?? 0), 2) }}"
                            class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-50 text-gray-600 cursor-not-allowed"
                            disabled readonly>
                    @else
                        <input wire:model="approved_loan_value" type="number" step="0.01" min="0"
                            max="{{ $product->principle_max_value ?? 999999999 }}"
                            class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                    @endif
                </div>
            </div>
        </div>

        <!-- Collateral Information -->
        @if($isPhysicalCollateral ?? false)
        <div>
            <h4 class="text-xs font-medium text-gray-700 mb-2">Collateral Information</h4>
            <div class="grid grid-cols-3 gap-2 text-xs">
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Security Value (FSV)</div>
                    <div class="font-semibold">{{ number_format((float)($collateral_value ?? 0), 2) }} TZS</div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">LTV Policy</div>
                    <div class="font-semibold">{{ $product->ltv ?? '-' }}%</div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Forced Sale Value</div>
                    <div class="font-semibold">{{ number_format(((float)($collateral_value ?? 0))*(70/100), 2) }} TZS</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Collateral Details Table -->
        @if(!empty($collateralDetails ?? []))
        <div>
            <h4 class="text-xs font-medium text-gray-700 mb-2">Collateral Details</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-1 text-left border-b border-r border-gray-200">Type</th>
                            <th class="px-2 py-1 text-right border-b border-r border-gray-200">Amount</th>
                            <th class="px-2 py-1 text-right border-b border-r border-gray-200">Locked</th>
                            <th class="px-2 py-1 text-left border-b border-gray-200">Account</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($collateralDetails as $collateral)
                        <tr class="border-b">
                            <td class="px-2 py-1 border-r border-gray-200">{{ ucfirst($collateral['type']) }}</td>
                            <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-red-900">{{ number_format((float)($collateral['amount'] ?? 0), 2) }} TZS</td>
                            <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-red-900">{{ number_format((float)($collateral['locked_amount'] ?? 0), 2) }} TZS</td>
                            <td class="px-2 py-1">{{ $collateral['account_number'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-50 font-semibold">
                            <td class="px-2 py-1 border-r border-gray-200">Total</td>
                            <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-red-900">{{ number_format((float)($collateral_value ?? 0), 2) }} TZS</td>
                            <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-red-900">{{ number_format((float)collect($collateralDetails)->sum('locked_amount'), 2) }} TZS</td>
                            <td class="px-2 py-1"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @endif



        <!-- Deduction Breakdown Table -->
        @if($totalDeductions > 0 || ($topUpAmount ?? 0) > 0)
        <div>
            <h4 class="text-xs font-medium text-gray-700 mb-2">Deduction Breakdown</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-1 text-left border-b border-r border-gray-200">Deduction Type</th>
                            <th class="px-2 py-1 text-right border-b border-gray-200">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(($firstInstallmentInterestAmount ?? 0) > 0)
                        <tr class="border-b">
                            <td class="px-2 py-1 border-r border-gray-200 pl-6 text-gray-600">First Interest</td>
                            <td class="px-2 py-1 text-right text-gray-700">{{ number_format((float)($firstInstallmentInterestAmount ?? 0), 2) }} TZS</td>
                        </tr>
                        @endif
                        
                        @if(($totalCharges ?? 0) > 0)
                            @if(!empty($storedChargesBreakdown) || !empty($chargesBreakdown))
                                @foreach(($storedChargesBreakdown ?? $chargesBreakdown ?? []) as $charge)
                                <tr class="border-b text-xs">
                                    <td class="px-2 py-1 border-r border-gray-200 pl-6 text-gray-600">
                                        {{ $charge['name'] }}
                                        @if(isset($charge['cap_applied']))
                                            
                                        @endif
                                    </td>
                                    <td class="px-2 py-1 text-right text-gray-700">{{ number_format($charge['amount'], 2) }} TZS</td>
                                </tr>
                                @endforeach
                            @else
                                <tr class="border-b text-xs">
                                    <td class="px-2 py-1 border-r border-gray-200 pl-6 text-gray-600">Management Fee</td>
                                    <td class="px-2 py-1 text-right text-gray-700">{{ number_format((float)($totalCharges ?? 0), 2) }} TZS</td>
                                </tr>
                            @endif
                        @endif
                        
                        @if(($totalInsurance ?? 0) > 0)
                     
                            @if(!empty($insuranceBreakdown))
                                @foreach($insuranceBreakdown as $insurance)
                                <tr class="border-b text-xs">
                                    <td class="px-2 py-1 border-r border-gray-200 pl-6 text-gray-600">
                                        {{ $insurance['name'] }}
                                        @if($insurance['value_type'] === 'percentage' && isset($insurance['tenure']))
                                            
                                        @endif
                                    </td>
                                    <td class="px-2 py-1 text-right text-gray-700">{{ number_format($insurance['amount'], 2) }} TZS</td>
                                </tr>
                                @endforeach
                            @endif
                        @endif
                        
                        @if(($totalAmount ?? 0) > 0)
                        <tr class="border-b">
                            <td class="px-2 py-1 border-r border-gray-200">Settlements</td>
                            <td class="px-2 py-1 text-right font-semibold text-red-900">{{ number_format((float)($totalAmount ?? 0), 2) }} TZS</td>
                        </tr>
                        @endif
                        
                        @if(($topUpAmount ?? 0) > 0 || ($loan && in_array($loan->loan_type_2, ['Top-up', 'TopUp', 'Top Up'])))
                        <tr class="border-b">
                            <td class="px-2 py-1 border-r border-gray-200">Top-Up Balance</td>
                            <td class="px-2 py-1 text-right font-semibold text-red-900">{{ number_format((float)($topUpAmount ?? 0), 2) }} TZS</td>
                        </tr>
                        @endif
                        
                        @if(($penaltyAmount ?? 0) > 0)
                        <tr class="border-b">
                            <td class="px-2 py-1 border-r border-gray-200">Early Settlement Penalty</td>
                            <td class="px-2 py-1 text-right font-semibold text-red-900">{{ number_format((float)($penaltyAmount ?? 0), 2) }} TZS</td>
                        </tr>
                        @endif
                        
                        <tr class="bg-gray-50 font-semibold">
                            <td class="px-2 py-1 border-r border-gray-200">Total Deductions</td>
                            <td class="px-2 py-1 text-right font-semibold text-red-900">{{ number_format((float)($totalDeductions ?? 0), 2) }} TZS</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @endif

  

        <!-- Disbursement Summary Table -->
        <div>
            <h4 class="text-xs font-medium text-gray-700 mb-2">Disbursement Summary</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border border-gray-200">
                    <tbody>
                        <tr class="border-b">
                            <td class="px-2 py-1 font-medium border-r border-gray-200">Loan Amount
                                @if($loan && in_array($loan->loan_type_2, ['Restructure', 'Restructuring']) && ($bufferAmount ?? 0) > 0)
                                    <span class="text-xs text-gray-500">(includes buffer)</span>
                                @endif
                            </td>
                            <td class="px-2 py-1 text-right font-semibold text-red-900">
                                @php
                                    $displayApprovedAmount = (float)($approved_loan_value ?? 0);
                                    if($loan && in_array($loan->loan_type_2, ['Restructure', 'Restructuring'])) {
                                        // Add buffer to cover processing costs (charges + insurance + first interest)
                                        $displayApprovedAmount += (float)($bufferAmount ?? 0);
                                    }
                                @endphp
                                {{ number_format($displayApprovedAmount, 2) }} TZS
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-2 py-1 font-medium border-r border-gray-200">Total Deductions</td>
                            <td class="px-2 py-1 text-right font-semibold text-red-700">-{{ number_format((float)($totalDeductions ?? 0), 2) }} TZS</td>
                        </tr>
                        <tr class="bg-gray-50 font-semibold">
                            <td class="px-2 py-1 border-r border-gray-200">Net Disbursement</td>
                            <td class="px-2 py-1 text-right font-semibold text-red-900">
                                @php
                                    $finalNetDisbursement = $displayApprovedAmount - (float)($totalDeductions ?? 0);
                                @endphp
                                {{ number_format($finalNetDisbursement, 2) }} TZS
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="p-4 border-t border-gray-200 flex justify-end">
        <button wire:click="exportReport" 
            class="px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-500">
            Export Report
        </button>
    </div>
</div> 