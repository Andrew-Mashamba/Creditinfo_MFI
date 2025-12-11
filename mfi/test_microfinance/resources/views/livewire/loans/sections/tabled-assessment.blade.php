<div class="w-full bg-white rounded-lg shadow-lg p-6">
    <!-- Header Section -->
    <div class="text-center mb-6">
        <div class="text-xs text-red-600 mb-2">COMPLETE ALL BLUE COLORED FIELDS</div>
        <h1 class="text-2xl font-bold text-gray-900">MFI Management System LIMITED AFFORDABILITY CALCULATOR</h1>
        <div class="flex justify-between items-center mt-2">
            <div class="text-sm text-gray-600">MFI Management System 2024</div>
            <div class="text-sm text-gray-600">{{ now()->format('d-M-y') }}</div>
        </div>
        
        <!-- Export Button -->
        <div class="mt-4">
            <button wire:click="exportTabledAssessment" 
                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Assessment Report
            </button>
        </div>
    </div>

    <!-- Applicant and Basic Information Section -->
    <div class="mb-6">
        <div class="grid grid-cols-5 gap-4 text-sm">
            <div class="col-span-2">
                <div class="bg-red-50 p-3 rounded border border-red-200">
                    <label class="block text-xs font-medium text-red-700 mb-1">APPLICANT NAME</label>
                    <div class="text-lg font-semibold text-red-900">{{ $member->first_name ?? '' }} {{ $member->middle_name ?? '' }} {{ $member->last_name ?? '' }}</div>
                </div>
            </div>
            <div>
                <div class="bg-red-50 p-3 rounded border border-red-200">
                    <label class="block text-xs font-medium text-red-700 mb-1">DOB (dd/mm/yyyy)</label>
                    <div class="text-sm font-semibold text-red-900">{{ $member->date_of_birth ? Carbon\Carbon::parse($member->date_of_birth)->format('d-M-y') : 'N/A' }}</div>
                </div>
            </div>
            <div>
                <div class="bg-green-50 p-3 rounded border border-green-200">
                    <label class="block text-xs font-medium text-green-700 mb-1">Current Age</label>
                    <div class="text-lg font-semibold text-green-900">{{ number_format($age ?? 0, 2) }}</div>
                </div>
            </div>
            <div>
                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Date</label>
                    <div class="text-sm font-semibold text-gray-900">{{ now()->format('d-M-y') }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mt-4 text-sm">
            <div class="col-span-2">
                <div class="bg-red-50 p-3 rounded border border-red-200">
                    <label class="block text-xs font-medium text-red-700 mb-1">PRODUCT NAMES</label>
                    <div class="text-lg font-semibold text-red-900">{{ $product->sub_product_name ?? 'N/A' }}</div>
                </div>
            </div>
            <div>
                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Basic salary</label>
                    <div class="text-sm font-semibold text-gray-900">{{ number_format(0, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mt-4 text-sm">
            <div>
                <div class="bg-green-50 p-3 rounded border border-green-200">
                    <label class="block text-xs font-medium text-green-700 mb-1">A. TOTAL SAVING (TZS)</label>
                    <div class="text-lg font-semibold text-green-900">{{ number_format($savings ?? 0, 2) }}</div>
                </div>
            </div>
            <div>
                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                    <label class="block text-xs font-medium text-gray-700 mb-1">B. [NEW/TOP UP]</label>
                    <div class="text-sm font-semibold text-gray-900">{{ $loan->loan_type_2 ?? 'New' }}</div>
                </div>
            </div>
            <div>
                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Take home salary (from Salary slip)</label>
                    <div class="text-lg font-semibold text-gray-900">{{ number_format($take_home ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: MFI Management System LOANS -->
    <div class="mb-6">
        <div class="flex items-center mb-3">
            <div class="bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3">1</div>
            <h3 class="text-lg font-semibold text-gray-900">MFI Management System LOANS</h3>
        </div>
        
        <div class="bg-white border border-gray-300 rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">NBC SACOSS LOANS</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-700">Loan Balance</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-700">Loan Deduction</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-200">
                        <td class="px-4 py-2">Loan 1</td>
                        <td class="px-4 py-2 text-right">{{ number_format(0, 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format(0, 2) }}</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="px-4 py-2">Loan 2</td>
                        <td class="px-4 py-2 text-right">{{ number_format(0, 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format(0, 2) }}</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="px-4 py-2">Loan 3</td>
                        <td class="px-4 py-2 text-right">{{ number_format(0, 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format(0, 2) }}</td>
                    </tr>
                    <tr class="bg-gray-50 font-semibold">
                        <td class="px-4 py-2">Total Amount</td>
                        <td class="px-4 py-2 text-right">{{ number_format(0, 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format(0, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Specific Loan Types -->
        <div class="mt-4 bg-green-50 p-4 rounded-lg border border-green-200">
            <div class="text-sm font-semibold text-green-800 mb-3">Loans to be settled and takeover for business loans.</div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">D. Takeover for business Loans only</label>
                    <div class="text-sm font-semibold text-gray-900">Finca</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Balance for Buyback and Penalties</label>
                    <div class="text-sm font-semibold text-gray-900">{{ number_format(0, 2) }}</div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mt-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">E. Loan to be settled outstanding balance</label>
                    <div class="text-sm font-semibold text-gray-900">NBC Saccos</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Balance</label>
                    <div class="text-sm font-semibold text-gray-900">{{ number_format(0, 2) }}</div>
                </div>
            </div>
            
            <div class="mt-3 pt-3 border-t border-green-300">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-semibold text-green-800">Total Amount</span>
                    <span class="text-lg font-bold text-green-900">{{ number_format(0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Existing Loans Balance (Top Up Only) -->
    @if(isset($loan) && $loan && $loan->loan_type_2 == 'Top-up')
    <div class="mb-6">
        <div class="flex items-center mb-3">
            <div class="bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3">2</div>
            <h3 class="text-lg font-semibold text-gray-900">NBC Saccos Loans Limited</h3>
        </div>
        <div class="text-sm text-gray-600 mb-3">Remaining loan Balance for existing facilities (Top Up Loan only)</div>
        
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <div class="text-sm font-semibold text-green-800 mb-3">Existing Loans balance for Top up loans only</div>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-700">Mkopo Maendeleo Mkubwa</span>
                        <span class="font-medium">{{ number_format(0, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Mkopo Maendeleo Mdogo</span>
                        <span class="font-medium">{{ number_format(0, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Mkopo wa wa bima</span>
                        <span class="font-medium">{{ number_format(0, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Mkopo dharura</span>
                        <span class="font-medium">{{ number_format(0, 2) }}</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-700">Mkopo wa wastaafu</span>
                        <span class="font-medium">{{ number_format(0, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Mkopo Solar</span>
                        <span class="font-medium">{{ number_format(0, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Mkopo wa Elimu</span>
                        <span class="font-medium">{{ number_format(0, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Mkopo wa kukomboa gari</span>
                        <span class="font-medium">{{ number_format(0, 2) }}</span>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-t border-green-300">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-semibold text-green-800">F. Top Up Penalties for loans issued before six months ({{ $product->penalty_value ?? 5 }}%)</span>
                    <span class="text-lg font-bold text-green-900">{{ number_format($penaltyAmount ?? 0, 2) }}</span>
                </div>
            </div>
            
            <div class="mt-3 pt-3 border-t border-green-300">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-semibold text-green-800">Total Amount</span>
                    <span class="text-lg font-bold text-green-900">{{ number_format(($topUpAmount ?? 0) + ($penaltyAmount ?? 0), 2) }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Financial Parameters Section -->
    <div class="mb-6">
        <div class="grid grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Interest Rates</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Interest Rate applicable Monthly</span>
                        <span class="font-medium">{{ number_format(($interestRate ?? 0) / 12, 1) }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Interest Rate per annum</span>
                        <span class="font-medium">{{ number_format($interestRate ?? 0, 1) }}%</span>
                    </div>
                </div>
            </div>
            
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Repayment Terms</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Maximum Repayment Term per product</span>
                        <span class="font-medium">{{ $product->max_term ?? 24 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Repayment term requested by the Customer</span>
                        <span class="font-medium">{{ $approved_term ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Other Parameters -->
    <div class="mb-6">
        <div class="grid grid-cols-2 gap-6">
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">One month grace period</span>
                    <span class="font-medium">1</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Security value (Forced Sale Value)</span>
                    <span class="font-medium">{{ number_format($collateral_value ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Policy LTV</span>
                    <span class="font-medium">{{ $product->ltv ?? 70 }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Maximum Qualifying amount per take home</span>
                    <span class="font-medium">{{ number_format(($take_home ?? 0) * (($product->ltv ?? 70) / 100), 2) }}</span>
                </div>
            </div>
            
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Maximum Loan amount per product</span>
                    <span class="font-medium">{{ number_format($product->principle_max_value ?? 20000000, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Loan amount requested by the customer (Loan value)</span>
                    <span class="font-medium">{{ number_format($approved_loan_value ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">First Interest (Deducted upfront)</span>
                    <span class="font-medium">{{ number_format($firstInstallmentInterestAmount ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Fees and Premiums Section -->
    <div class="mb-6">
        <div class="flex items-center mb-3">
            <div class="bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3">4</div>
            <h4 class="text-sm font-semibold text-gray-700">Management fee percentage (%)</h4>
            <span class="ml-2 text-sm font-medium">{{ number_format(0.30, 2) }}%</span>
        </div>
        <div class="flex items-center mb-3">
            <div class="bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3">4</div>
            <h4 class="text-sm font-semibold text-gray-700">Management fee amount (TZS)</h4>
            <span class="ml-2 text-sm font-medium">{{ number_format($totalCharges ?? 0, 2) }}</span>
        </div>
        
        <div class="flex items-center mb-3">
            <div class="bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3">5</div>
            <h4 class="text-sm font-semibold text-gray-700">Credit life premium/Majanga</h4>
            <span class="ml-2 text-sm font-medium">{{ number_format($totalInsurance ?? 0, 2) }}</span>
        </div>
    </div>

    <!-- Final Calculation Results -->
    <div class="mb-6">
        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
            <h4 class="text-sm font-semibold text-red-800 mb-3">Final Calculation Results</h4>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-red-700">Total to be repaid</span>
                    <span class="font-bold text-red-900">{{ number_format($totalToRepay ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-red-700">Monthly loan instalment</span>
                    <span class="font-bold text-red-900">{{ number_format($monthlyPayment ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        
        <div class="flex items-center mt-4">
            <div class="bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3">6</div>
            <h4 class="text-sm font-semibold text-gray-700">Amount to be credited into customer's account (Deduct existing Balance in case of Top-up)</h4>
            <span class="ml-2 text-lg font-bold text-green-600">{{ number_format($netDisbursement ?? 0, 2) }}</span>
        </div>
    </div>

    <!-- LTV Warning -->
    @if(($collateral_value ?? 0) > 0 && ($approved_loan_value ?? 0) > 0)
        @php
            $ltvRatio = (($approved_loan_value ?? 0) / ($collateral_value ?? 1)) * 100;
            $maxLtv = $product->ltv ?? 70;
        @endphp
        @if($ltvRatio > $maxLtv)
            <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                <div class="flex items-center">
                    <div class="bg-red-600 text-white rounded px-2 py-1 text-xs font-bold mr-3">LTV BREACH</div>
                    <div class="text-sm text-red-800">
                        Loan-to-Value ratio ({{ number_format($ltvRatio, 1) }}%) exceeds maximum allowed ({{ $maxLtv }}%)
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
