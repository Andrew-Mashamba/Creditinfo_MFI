{{-- Assessment Completion Banner --}}
@php
$tabStateService = app(\App\Services\LoanTabStateService::class);
$loanID = session('currentloanID');
$isAssessmentCompleted = $tabStateService->isTabCompleted($loanID, 'assessment');

// Get current loan details
$currentLoan = DB::table('loans')->find($loanID);

// Only show this section if loan_type_2 is "Top-up"
if ($currentLoan && $currentLoan->loan_type_2 == "Top-up" && $currentLoan->selectedLoan) {
    // Get the loan being topped up
    $topupLoan = DB::table('loans')->where('id', $currentLoan->selectedLoan)->first();
    
    // Get the loan account details
    $loanAccount = null;
    if ($topupLoan && $topupLoan->loan_account_number) {
        $loanAccount = DB::table('accounts')->where('account_number', $topupLoan->loan_account_number)->first();
    }
    
    // Calculate outstanding balance
    $outstandingBalance = $loanAccount ? $loanAccount->balance : 0;
    
    // Get loan performance data
    $loanSchedule = DB::table('loans_schedules')
        ->where('loan_id', $topupLoan->loan_id)
        ->orderBy('installment_date', 'desc')
        ->get();
    
    $loanArrears = DB::table('loans_arreas')
        ->where('loan_id', $topupLoan->loan_id)
        ->get();
    
    // Calculate performance metrics
    $totalInstallments = $loanSchedule->count();
    $paidInstallments = $loanSchedule->where('completion_status', 'CLOSED')->count();
    $overdueInstallments = $loanSchedule->where('days_in_arrears', '>', 0)->count();
    $totalArrears = $loanSchedule->sum('amount_in_arrears');
    $totalPenalties = $loanSchedule->sum('penalties');
    
    // Get last payment date
    $lastPayment = $loanSchedule->where('completion_status', 'CLOSED')->first();
    $lastPaymentDate = $lastPayment ? $lastPayment->installment_date : null;
    
    // Get next payment date
    $nextPayment = $loanSchedule->where('completion_status', '!=', 'CLOSED')->first();
    $nextPaymentDate = $nextPayment ? $nextPayment->installment_date : null;
    
    // Calculate monthly payment
    $monthlyPayment = $loanSchedule->first() ? ($loanSchedule->first()->installment ?? 0) : 0;
    
    // Calculate remaining term
    $remainingInstallments = $loanSchedule->where('completion_status', '!=', 'CLOSED')->count();
    
    // Get client details
    $client = DB::table('clients')->where('client_number', $topupLoan->client_number)->first();
}
@endphp

@if($currentLoan && $currentLoan->loan_type_2 == "Top-up" && $currentLoan->selectedLoan && isset($topupLoan))
<div class="mt-2"></div>

{{-- LOANS TO BE TOPPED UP SECTION --}}

<p for="stability" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400 mt-4">LOANS TO BE TOPPED UP</p>
<div id="topup" class="w-full bg-gray-50 rounded-lg shadow-sm p-1 mb-4">

    <div class="w-full bg-white rounded-lg shadow-sm p-4">

        <!-- Top-up Loan Details -->
        <div class="mb-6 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg border border-purple-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-purple-900">Loan Being Topped Up</h3>
                <span class="px-3 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                    Top-up Loan
                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
                <div class="bg-white p-2 rounded-lg border border-purple-200">
                    <div class="text-xs text-purple-600 mb-1">Loan ID</div>
                    <div class="text-lg font-bold text-purple-900">{{ $topupLoan->id }}</div>
                </div>
                <div class="bg-white p-2 rounded-lg border border-purple-200">
                    <div class="text-xs text-purple-600 mb-1">Client Number</div>
                    <div class="text-lg font-bold text-purple-900">{{ $topupLoan->client_number }}</div>
                </div>
                <div class="bg-white p-2 rounded-lg border border-purple-200">
                    <div class="text-xs text-purple-600 mb-1">Loan Account</div>
                    <div class="text-lg font-bold text-purple-900">{{ $topupLoan->loan_account_number ?? 'N/A' }}</div>
                </div>
                <div class="bg-white p-2 rounded-lg border border-purple-200">
                    <div class="text-xs text-purple-600 mb-1">Original Amount</div>
                    <div class="text-lg font-bold text-purple-900">{{ number_format($topupLoan->principle ?? 0, 2) }} TZS</div>
                </div>
            </div>

            <!-- Outstanding Balance Highlight -->
            <div class="mt-4 p-3 bg-red-50 rounded-lg border border-red-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h6 class="text-sm font-semibold text-red-900">Outstanding Balance</h6>
                        <p class="text-xs text-red-700">This amount will be deducted from the new loan disbursement</p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-red-900">{{ number_format($outstandingBalance, 2) }} TZS</div>
                        <div class="text-xs text-red-600">Deduction</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loan Performance Summary -->
        <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg border border-blue-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-blue-900">Loan Performance Summary</h3>
                <span class="px-3 py-1 text-xs font-medium rounded-full 
                    @if($topupLoan->loan_status == 'NORMAL') bg-green-100 text-green-800
                    @elseif($topupLoan->loan_status == 'OVERDUE') bg-yellow-100 text-yellow-800
                    @else bg-red-100 text-red-800 @endif">
                    {{ $topupLoan->loan_status ?? 'NORMAL' }}
                </span>
            </div>

            <div class="grid grid-cols-4 gap-3 text-center">
                <div class="bg-white p-2 rounded-lg border border-blue-200">
                    <div class="text-lg font-bold text-blue-900">{{ $paidInstallments }}/{{ $totalInstallments }}</div>
                    <div class="text-xs text-blue-600">Installments Paid</div>
                </div>
                <div class="bg-white p-2 rounded-lg border border-blue-200">
                    <div class="text-lg font-bold text-blue-900">{{ $remainingInstallments }}</div>
                    <div class="text-xs text-blue-600">Remaining Term</div>
                </div>
                <div class="bg-white p-2 rounded-lg border border-blue-200">
                    <div class="text-lg font-bold text-blue-900">{{ number_format($monthlyPayment, 2) }}</div>
                    <div class="text-xs text-blue-600">Monthly Payment (TZS)</div>
                </div>
                <div class="bg-white p-2 rounded-lg border border-blue-200">
                    <div class="text-lg font-bold text-blue-900">{{ $topupLoan->tenure ?? 0 }}</div>
                    <div class="text-xs text-blue-600">Original Term (Months)</div>
                </div>
            </div>
        </div>

        <!-- Payment History & Arrears -->
        <div class="mb-6 p-4 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg border border-yellow-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-yellow-900">Payment History & Arrears</h3>
                <span class="px-3 py-1 text-xs font-medium rounded-full 
                    @if($overdueInstallments == 0) bg-green-100 text-green-800
                    @elseif($overdueInstallments <= 2) bg-yellow-100 text-yellow-800
                    @else bg-red-100 text-red-800 @endif">
                    {{ $overdueInstallments }} Overdue
                </span>
            </div>

            <div class="grid grid-cols-4 gap-3 text-center">
                <div class="bg-white p-2 rounded-lg border border-yellow-200">
                    <div class="text-lg font-bold text-yellow-900">{{ $overdueInstallments }}</div>
                    <div class="text-xs text-yellow-600">Overdue Installments</div>
                </div>
                <div class="bg-white p-2 rounded-lg border border-yellow-200">
                    <div class="text-lg font-bold text-red-900">{{ number_format($totalArrears, 2) }}</div>
                    <div class="text-xs text-red-600">Total Arrears (TZS)</div>
                </div>
                <div class="bg-white p-2 rounded-lg border border-yellow-200">
                    <div class="text-lg font-bold text-red-900">{{ number_format($totalPenalties, 2) }}</div>
                    <div class="text-xs text-red-600">Total Penalties (TZS)</div>
                </div>
                <div class="bg-white p-2 rounded-lg border border-yellow-200">
                    <div class="text-lg font-bold text-yellow-900">{{ $topupLoan->days_in_arrears ?? 0 }}</div>
                    <div class="text-xs text-yellow-600">Days in Arrears</div>
                </div>
            </div>

            <!-- Payment Dates -->
            <div class="mt-4 grid grid-cols-2 gap-3">
                <div class="bg-white p-2 rounded-lg border border-yellow-200">
                    <div class="text-xs text-yellow-600 mb-1">Last Payment Date</div>
                    <div class="text-sm font-bold text-yellow-900">
                        {{ $lastPaymentDate ? \Carbon\Carbon::parse($lastPaymentDate)->format('M d, Y') : 'No payments yet' }}
                    </div>
                </div>
                <div class="bg-white p-2 rounded-lg border border-yellow-200">
                    <div class="text-xs text-yellow-600 mb-1">Next Payment Due</div>
                    <div class="text-sm font-bold text-yellow-900">
                        {{ $nextPaymentDate ? \Carbon\Carbon::parse($nextPaymentDate)->format('M d, Y') : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Top-up Impact Summary -->
        <div class="mb-6 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg border border-indigo-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-indigo-900">Top-up Impact Summary</h3>
                <span class="px-3 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full">
                    Impact Analysis
                </span>
            </div>

            <div class="grid grid-cols-4 gap-3 text-center">
                <div class="bg-white p-2 rounded-lg border border-indigo-200">
                    <div class="text-lg font-bold text-indigo-900">{{ number_format($currentLoan->principle ?? 0, 2) }}</div>
                    <div class="text-xs text-indigo-600">New Loan Amount (TZS)</div>
                </div>
                <div class="bg-white p-2 rounded-lg border border-red-200">
                    <div class="text-lg font-bold text-red-900">{{ number_format($outstandingBalance, 2) }}</div>
                    <div class="text-xs text-red-600">Outstanding Balance (TZS)</div>
                </div>
                <div class="bg-white p-2 rounded-lg border border-green-200">
                    <div class="text-lg font-bold text-green-900">{{ number_format(($currentLoan->principle ?? 0) - $outstandingBalance, 2) }}</div>
                    <div class="text-xs text-green-600">Net Disbursement (TZS)</div>
                </div>
                <div class="bg-white p-2 rounded-lg border border-blue-200">
                    <div class="text-lg font-bold text-blue-900">{{ $remainingInstallments }}</div>
                    <div class="text-xs text-blue-600">Remaining Term</div>
                </div>
            </div>

            <!-- Recommendation -->
            <div class="mt-4 p-2 bg-white rounded-lg border border-indigo-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h6 class="text-sm font-semibold text-indigo-900">Top-up Recommendation</h6>
                        <p class="text-xs text-blue-900">
                            @if($overdueInstallments == 0 && $totalArrears == 0)
                                ✅ Good candidate for top-up - Excellent payment history
                            @elseif($overdueInstallments <= 2 && $totalArrears < 100000)
                                ⚠️ Conditional approval - Some payment delays but manageable
                            @else
                                ❌ High risk - Significant arrears and overdue payments
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-indigo-900">
                            @if($overdueInstallments == 0 && $totalArrears == 0) APPROVE
                            @elseif($overdueInstallments <= 2 && $totalArrears < 100000) CONDITIONAL
                            @else REJECT
                            @endif
                        </div>
                        <div class="text-xs text-indigo-600">Recommendation</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endif
