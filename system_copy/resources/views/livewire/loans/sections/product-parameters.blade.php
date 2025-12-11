{{-- PRODUCT PARAMETERS SECTION --}}
<p for="stability" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">PRODUCT PARAMETERS</p>
<div id="stability" class="w-full bg-gray-50 rounded rounded-lg shadow-sm p-1 mb-4">
    <div class="w-full bg-white rounded rounded-lg shadow-sm p-2">

        <!-- Basic Product Information Section -->
        <div class="mb-4">
            <h4 class="text-xs font-semibold text-gray-700 mb-2 border-b pb-1">Basic Information</h4>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Product Name:</span>
                    <span class="text-slate-900 font-medium">{{ $productBasicInfo['sub_product_name'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Product ID:</span>
                    <span class="text-slate-900 font-medium">{{ $productBasicInfo['sub_product_id'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Status:</span>
                    <span class="text-slate-900 font-medium">
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if(($productBasicInfo['status'] ?? '') === 'ACTIVE') bg-green-100 text-green-800 
                            @else bg-red-100 text-red-800 @endif">
                            {{ $productBasicInfo['status'] ?? 'N/A' }}
                        </span>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Currency:</span>
                    <span class="text-slate-900 font-medium">{{ $productBasicInfo['currency'] ?? 'TZS' }}</span>
                </div>
            </div>
        </div>

        <!-- Loan Limits Section -->
        <div class="mb-4">
            <h4 class="text-xs font-semibold text-gray-700 mb-2 border-b pb-1">Loan Limits</h4>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Minimum Amount:</span>
                    <span class="text-slate-900 font-medium">{{ number_format($productLoanLimits['min_amount'] ?? 0, 2) }} TZS</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Maximum Amount:</span>
                    <span class="text-slate-900 font-semibold">{{ number_format($productLoanLimits['max_amount'] ?? 0, 2) }} TZS</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Minimum Term:</span>
                    <span class="text-slate-900 font-medium">{{ $productLoanLimits['min_term'] ?? 0 }} months</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Maximum Term:</span>
                    <span class="text-slate-900 font-semibold">{{ $productLoanLimits['max_term'] ?? 0 }} months</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Loan Multiplier:</span>
                    <span class="text-slate-900 font-medium">{{ $productLoanLimits['loan_multiplier'] ?? 0 }}x</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">LTV Ratio:</span>
                    <span class="text-slate-900 font-medium">{{ $productLoanLimits['ltv'] ?? 0 }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Credit Score Limit:</span>
                    <span class="text-slate-900 font-medium">{{ $productLoanLimits['score_limit'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Interest Information Section -->
        <div class="mb-4">
            <h4 class="text-xs font-semibold text-gray-700 mb-2 border-b pb-1">Interest Information</h4>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Annual Interest Rate:</span>
                    <span class="text-slate-900 font-semibold">{{ number_format($productInterestInfo['interest_rate'] ?? 0, 2) }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Monthly Interest Rate:</span>
                    <span class="text-slate-900 font-medium">{{ number_format($productInterestInfo['monthly_rate'] ?? 0, 4) }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Interest Method:</span>
                    <span class="text-slate-900 font-medium">{{ $productInterestInfo['interest_method'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Amortization Method:</span>
                    <span class="text-slate-900 font-medium">{{ $productInterestInfo['amortization_method'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Days in Year:</span>
                    <span class="text-slate-900 font-medium">{{ $productInterestInfo['days_in_year'] ?? 365 }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Days in Month:</span>
                    <span class="text-slate-900 font-medium">{{ $productInterestInfo['days_in_month'] ?? 30 }}</span>
                </div>
            </div>
        </div>

        <!-- Grace Periods Section -->
        @if($productGracePeriods['has_grace_period'] ?? false)
        <div class="mb-4">
            <h4 class="text-xs font-semibold text-gray-700 mb-2 border-b pb-1">Grace Periods</h4>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Principal Grace Period:</span>
                    <span class="text-slate-900 font-medium">{{ $productGracePeriods['principle_grace_period'] ?? 0 }} months</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Interest Grace Period:</span>
                    <span class="text-slate-900 font-medium">{{ $productGracePeriods['interest_grace_period'] ?? 0 }} months</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Fees and Charges Section -->
        @if(!empty($productFeesAndCharges['charges']))
        <div class="mb-4">
            <h4 class="text-xs font-semibold text-gray-700 mb-2 border-b pb-1">Fees and Charges</h4>
            <div class="space-y-2">
                @foreach($productFeesAndCharges['charges'] as $charge)
                <div class="flex justify-between items-center text-xs">
                    <div class="flex-1">
                        <div class="font-medium text-slate-900">{{ $charge['name'] }}</div>
                        <div class="text-slate-500 text-xs">{{ $charge['type'] }} - {{ $charge['description'] }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-slate-900">{{ number_format($charge['amount'], 2) }} TZS</div>
                        <div class="text-slate-500 text-xs">
                            @if($charge['type'] === 'Percent')
                            {{ number_format($charge['value'], 2) }}%
                            @else
                            Fixed
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
                <div class="border-t pt-2 mt-2">
                    <div class="flex justify-between font-semibold">
                        <span class="text-slate-700">Total Charges:</span>
                        <span class="text-slate-900">{{ number_format($productFeesAndCharges['total_charges'] ?? 0, 2) }} TZS</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Insurance Information Section -->
        @if(!empty($productInsuranceInfo['insurances']))
        <div class="mb-4">
            <h4 class="text-xs font-semibold text-gray-700 mb-2 border-b pb-1">Insurance</h4>
            <div class="space-y-2">
                @foreach($productInsuranceInfo['insurances'] as $insurance)
                <div class="flex justify-between items-center text-xs">
                    <div class="flex-1">
                        <div class="font-medium text-slate-900">{{ $insurance['name'] }}</div>
                        <div class="text-slate-500 text-xs">{{ $insurance['type'] }} - {{ $insurance['description'] }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-slate-900">{{ number_format($insurance['amount'], 2) }} TZS</div>
                        <div class="text-slate-500 text-xs">
                            @if($insurance['type'] === 'Percent')
                            {{ number_format($insurance['value'], 2) }}%
                            @else
                            Fixed
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
                <div class="border-t pt-2 mt-2">
                    <div class="flex justify-between font-semibold">
                        <span class="text-slate-700">Total Insurance:</span>
                        <span class="text-slate-900">{{ number_format($productInsuranceInfo['total_insurance'] ?? 0, 2) }} TZS</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Repayment Information Section -->
        <div class="mb-4">
            <h4 class="text-xs font-semibold text-gray-700 mb-2 border-b pb-1">Repayment Information</h4>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Repayment Method:</span>
                    <span class="text-slate-900 font-medium">{{ $productRepaymentInfo['repayment_method'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Repayment Frequency:</span>
                    <span class="text-slate-900 font-medium">{{ $productRepaymentInfo['repayment_frequency'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Repayment Day:</span>
                    <span class="text-slate-900 font-medium">{{ $productRepaymentInfo['repayment_day'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Late Payment Fee:</span>
                    <span class="text-slate-900 font-medium">{{ number_format($productRepaymentInfo['late_payment_fee'] ?? 0, 2) }} TZS</span>
                </div>
            </div>
        </div>

        <!-- Account Information Section -->
        <div class="mb-4">
            <h4 class="text-xs font-semibold text-gray-700 mb-2 border-b pb-1">Account Information</h4>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Account Type:</span>
                    <span class="text-slate-900 font-medium">{{ $productAccountInfo['account_type'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Account Number Format:</span>
                    <span class="text-slate-900 font-medium">{{ $productAccountInfo['account_number_format'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Minimum Balance:</span>
                    <span class="text-slate-900 font-medium">{{ number_format($productAccountInfo['minimum_balance'] ?? 0, 2) }} TZS</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Account Status:</span>
                    <span class="text-slate-900 font-medium">{{ $productAccountInfo['status'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Requirements Section -->
        @if(!empty($productRequirements['requirements']))
        <div class="mb-4">
            <h4 class="text-xs font-semibold text-gray-700 mb-2 border-b pb-1">Requirements</h4>
            <div class="space-y-1">
                @foreach($productRequirements['requirements'] as $requirement)
                <div class="flex items-center text-xs">
                    <span class="w-2 h-2 rounded-full mr-2 bg-blue-500"></span>
                    <span class="text-slate-700">{{ $requirement['description'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Validation Rules Section -->
        @if(!empty($productValidation['validation_rules']))
        <div class="mb-4">
            <h4 class="text-xs font-semibold text-gray-700 mb-2 border-b pb-1">Validation Rules</h4>
            <div class="space-y-1">
                @foreach($productValidation['validation_rules'] as $rule)
                <div class="flex items-center text-xs">
                    <span class="w-2 h-2 rounded-full mr-2 bg-orange-500"></span>
                    <span class="text-slate-700">{{ $rule['description'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div> 
