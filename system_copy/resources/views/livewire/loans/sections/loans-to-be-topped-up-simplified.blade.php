{{-- SIMPLIFIED LOANS TO BE TOPPED UP SECTION --}}
@php
// Get current loan details
$currentLoanID = session('currentloanID');
$currentLoan = DB::table('loans')->find($currentLoanID);

// Only process if this is a top-up loan
if ($currentLoan && $currentLoan->loan_type_2 == "Top-up") {
    // Get the loan being topped up from selectedLoan field
    $topupLoan = null;
    $outstandingBalance = 0;
    $loanAge = 0;
    $penaltyAmount = 0;
    $penaltyApplied = false;
    $penaltyPercentage = 0;
    $productPenaltyValue = 0;
    
    if ($currentLoan->selectedLoan) {
        $topupLoan = DB::table('loans')->where('id', $currentLoan->selectedLoan)->first();
        
        if ($topupLoan && $topupLoan->loan_account_number) {
            // Get outstanding balance from accounts table
            $loanAccount = DB::table('accounts')->where('account_number', $topupLoan->loan_account_number)->first();
            $outstandingBalance = abs($loanAccount->balance ?? 0);
        }
        
        if ($topupLoan) {
            // Calculate loan age in months
            $disbursementDate = $topupLoan->disbursement_date ?? $topupLoan->created_at;
            $disbursementDate = \Carbon\Carbon::parse($disbursementDate);
            $currentDate = \Carbon\Carbon::now();
            $loanAge = $disbursementDate->diffInMonths($currentDate);
            
            // Get the loan product to check penalty value
            $loanProduct = DB::table('loan_sub_products')->where('sub_product_id', $topupLoan->loan_sub_product)->first();
            $productPenaltyValue = $loanProduct->penalty_value ?? 0;
            
            // Apply penalty if loan is less than 6 months old and product has penalty configured
            if ($loanAge < 6 && $productPenaltyValue > 0) {
                $penaltyPercentage = $productPenaltyValue;
                // Calculate penalty on the top-up amount (outstanding balance)
                $penaltyAmount = ($outstandingBalance * $penaltyPercentage) / 100;
                $penaltyApplied = true;
            }
        }
    }
}
@endphp

@if($currentLoan && $currentLoan->loan_type_2 == "Top-upx")
<div class="mt-4">
    <p class="text-sm font-medium text-gray-700 mb-2">LOAN TO BE TOPPED UP</p>
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        
        @if($topupLoan)
        {{-- Loan Being Topped Up Info --}}
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <div class="text-sm text-gray-600 mb-1">Loan ID</div>
                <div class="text-lg font-semibold">{{ $topupLoan->id }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-600 mb-1">Account Number</div>
                <div class="text-lg font-semibold">{{ $topupLoan->loan_account_number ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-600 mb-1">Original Loan Amount</div>
                <div class="text-lg font-semibold">{{ number_format($topupLoan->principle ?? 0, 2) }} TZS</div>
            </div>
            <div>
                <div class="text-sm text-gray-600 mb-1">Disbursement Date</div>
                <div class="text-lg font-semibold">
                    {{ $topupLoan->disbursement_date ? \Carbon\Carbon::parse($topupLoan->disbursement_date)->format('M d, Y') : 'N/A' }}
                </div>
                <div class="text-xs text-gray-500">{{ $loanAge }} months ago</div>
            </div>
        </div>

        {{-- Outstanding Balance & Penalty Section --}}
        <div class="bg-red-50 p-3 rounded-lg border border-red-200 mb-4">
            <div class="flex justify-between items-center">
                <div>
                    <div class="text-sm font-medium text-red-800">Outstanding Balance</div>
                    <div class="text-xs text-red-600">This amount will be deducted from disbursement</div>
                </div>
                <div class="text-right">
                    <div class="text-xl font-bold text-red-900">{{ number_format($outstandingBalance, 2) }} TZS</div>
                </div>
            </div>
            
            @if($penaltyApplied)
            <div class="mt-3 pt-3 border-t border-red-300">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-sm font-medium text-orange-800">Early Settlement Penalty ({{ number_format($penaltyPercentage, 1) }}%)</div>
                      
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-orange-900">{{ number_format($penaltyAmount, 2) }} TZS</div>
                        <div class="text-xs text-orange-600">{{ number_format($penaltyPercentage, 1) }}% of original</div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3 pt-3 border-t border-red-300">
                <div class="flex justify-between items-center">
                    <div class="text-sm font-semibold text-red-800">Total Deduction Amount</div>
                    <div class="text-xl font-bold text-red-900">{{ number_format($outstandingBalance + $penaltyAmount, 2) }} TZS</div>
                </div>
            </div>
            @elseif($productPenaltyValue > 0 && $loanAge >= 6)
      
            @endif
        </div>

        {{-- New Loan Summary --}}
        <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-blue-700">New Loan Principle:</span>
                    <span class="font-medium text-blue-900">{{ number_format($currentLoan->principle ?? 0, 2) }} TZS</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-blue-700">Outstanding Balance:</span>
                    <span class="font-medium text-red-700">-{{ number_format($outstandingBalance, 2) }} TZS</span>
                </div>
                @if($penaltyApplied)
                <div class="flex justify-between items-center">
                    <span class="text-sm text-orange-700">Early Settlement Penalty:</span>
                    <span class="font-medium text-orange-700">-{{ number_format($penaltyAmount, 2) }} TZS</span>
                </div>
                @endif
                <div class="flex justify-between items-center border-t border-blue-200 pt-2">
                    <span class="text-sm font-semibold text-blue-800">Net Disbursement:</span>
                    <span class="font-bold text-green-900">
                        {{ number_format(($currentLoan->principle ?? 0) - ($outstandingBalance + $penaltyAmount), 2) }} TZS
                    </span>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-4 text-gray-500">
            <div class="text-sm">No loan selected for top-up</div>
        </div>
        @endif
    </div>
</div>
@endif