<div class="space-y-4">
    
    <!-- Debug Information -->
    @if($showDebugInfo ?? false)
        <div class="bg-red-50 border border-red-200 rounded p-4 mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-red-900">Debug Information</h3>
                <button type="button" wire:click="toggleDebugInfo" class="text-red-600 hover:text-red-800 text-xs">
                    Hide Debug
                </button>
            </div>
            <div class="text-xs space-y-2">
                <div><strong>Loan ID:</strong> {{ $loan_id ?? 'Not set' }}</div>
                <div><strong>Loan Type:</strong> {{ $loanType ?? 'Not set' }}</div>
                <div><strong>Existing Guarantor Data Count:</strong> {{ count($existingGuarantorData ?? []) }}</div>
                <div><strong>Existing Collateral Data Count:</strong> {{ count($existingCollateralData ?? []) }}</div>
                <div><strong>Session Loan ID:</strong> {{ session('currentloanID') ?? 'Not set' }}</div>
                @if(isset($debugInfo['loan_id_to_query']))
                    <div><strong>Query Loan ID:</strong> {{ $debugInfo['loan_id_to_query'] }}</div>
                @endif
                @if(isset($debugInfo['original_numeric_id']))
                    <div><strong>Original Numeric ID:</strong> {{ $debugInfo['original_numeric_id'] }}</div>
                @endif
                @if(isset($debugInfo))
                    <div class="mt-3">
                        <strong>Debug Info:</strong>
                        <pre class="text-xs bg-white p-2 rounded border">{{ json_encode($debugInfo, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="mb-4 space-x-2">
            <button type="button" wire:click="toggleDebugInfo" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                Show Debug Info
            </button>
            <button type="button" wire:click="refreshDebugInfo" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                Refresh Data
            </button>
        </div>
    @endif
  
    <!-- Savings Policy Violations Section -->
    @if(!empty($this->step3SavingsViolations))
        <div class="bg-orange-50 border border-orange-200 rounded p-4 space-y-3">
            <div class="flex items-center">
                <svg class="w-4 h-4 text-orange-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <h3 class="text-sm font-semibold text-orange-900">Savings Policy Notice</h3>
            </div>
            <p class="text-orange-700 text-xs">
                Your loan amount exceeds the standard savings multiplier. You may need to provide additional collateral to proceed.
            </p>
            
            <div class="space-y-3">
                @foreach($this->step3SavingsViolations as $violation)
                    <div class="bg-white border border-orange-200 rounded p-3">
                        <div class="space-y-2">
                            <div class="flex items-center mb-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                    High Priority
                                </span>
                            </div>
                            <h4 class="font-semibold text-gray-900 text-sm">{{ $violation['title'] }}</h4>
                            <p class="text-gray-700 text-xs mb-2">{{ $violation['description'] }}</p>
                            
                            <div class="grid grid-cols-2 gap-3 text-xs mb-2">
                                <div>
                                    <span class="font-medium text-gray-600">Loan Amount:</span>
                                    <span class="text-red-600 font-semibold">{{ $violation['current_value'] }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Savings Limit (3x):</span>
                                    <span class="text-green-600 font-semibold">{{ $violation['limit_value'] }}</span>
                                </div>
                                @if(isset($violation['savings_shortfall']))
                                    <div class="col-span-2">
                                        <span class="font-medium text-gray-600">Additional Collateral Needed:</span>
                                        <span class="text-orange-600 font-semibold">{{ $violation['savings_shortfall'] }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="bg-gray-50 border border-gray-200 rounded p-2">
                                <div class="flex items-start">
                                    <svg class="w-3 h-3 text-gray-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <span class="font-medium text-gray-900 text-xs">Recommendation:</span>
                                        <p class="text-gray-700 text-xs mt-1">{{ $violation['recommendation'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="bg-gray-50 border border-gray-200 rounded p-3">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium text-gray-900 text-sm">Next Steps</span>
                </div>
                <p class="text-gray-800 text-xs mt-1">
                    Please provide sufficient collateral below to cover the loan amount. You can use savings accounts, shares, deposits, or physical collateral.
                </p>
            </div>
        </div>
    @endif

    <!-- Original Loan Information for Restructure Loans -->
    @if($loanType === 'Restructure' && isset($debugInfo['is_restructure_loan']) && isset($debugInfo['restructured_loan_id']))
        @php
            // Get original loan details
            $originalLoanId = $debugInfo['restructured_loan_id'];
            $originalLoan = DB::table('loans')->where('id', $originalLoanId)->first();
            $originalProduct = null;
            $outstandingBalance = 0;

            if ($originalLoan) {
                // Get original loan product
                $originalProduct = DB::table('loan_sub_products')
                    ->where('sub_product_id', $originalLoan->loan_sub_product)
                    ->first();

                // Get outstanding balance from account
                if ($originalLoan->loan_account_number) {
                    $account = DB::table('accounts')
                        ->where('account_number', $originalLoan->loan_account_number)
                        ->first();
                    $outstandingBalance = $account ? abs($account->balance ?? 0) : 0;
                }
            }
        @endphp

        @if($originalLoan)
            <div class="bg-red-50 border-2 border-red-300 rounded-lg p-4 mb-4">
                <div class="flex items-center mb-3">
                    <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <h3 class="text-sm font-bold text-red-900">Restructured Loan Information</h3>
                </div>

                <div class="bg-white rounded-lg p-3 space-y-2 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <span class="font-semibold text-gray-700">Original Loan ID:</span>
                            <span class="text-red-900 font-bold ml-1">{{ $originalLoan->loan_id ?? 'N/A' }}</span>
                        </div>

                        @if($originalProduct)
                        <div>
                            <span class="font-semibold text-gray-700">Loan Product:</span>
                            <span class="text-gray-900 ml-1">{{ $originalProduct->sub_product_name ?? 'N/A' }}</span>
                        </div>
                        @endif

                        <div>
                            <span class="font-semibold text-gray-700">Original Principle:</span>
                            <span class="text-gray-900 ml-1">{{ number_format($originalLoan->principle ?? 0, 2) }} TZS</span>
                        </div>

                        <div>
                            <span class="font-semibold text-gray-700">Outstanding Balance:</span>
                            <span class="text-red-600 font-bold ml-1">{{ number_format($outstandingBalance, 2) }} TZS</span>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <p class="text-red-800 font-semibold">
                            The guarantor and collateral information shown below is from the original loan being restructured.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Existing Guarantor & Collateral Display -->
    @if(!empty($existingGuarantorData) || !empty($existingCollateralData))
        <div class="bg-gray-50 border border-gray-200 rounded p-4 mb-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <h3 class="text-xs font-semibold text-gray-900">
                        @if(!empty($existingGuarantorData) && !empty($existingCollateralData))
                            Existing Guarantor & Collateral Information
                        @elseif(!empty($existingGuarantorData))
                            Existing Guarantor Information
                        @else
                            Existing Collateral Information
                        @endif
                    </h3>
                </div>
                @if($loanType === 'Restructure')
                    <button type="button" 
                            wire:click="refreshCollateralData"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="px-2 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700 transition-colors font-medium inline-flex items-center">
                        <svg wire:loading.remove class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <svg wire:loading class="animate-spin w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove>Refresh Data</span>
                        <span wire:loading>Refreshing...</span>
                    </button>
                @endif
            </div>
         
            @php
                $totalAccountCollateral = 0;
                $totalPhysicalCollateral = 0;
                foreach ($existingCollateralData ?? [] as $collateral) {
                    if (is_object($collateral)) {
                        if (in_array($collateral->collateral_type, ['savings', 'deposits', 'shares'])) {
                            $totalAccountCollateral += floatval($collateral->collateral_amount);
                        } elseif ($collateral->collateral_type === 'physical') {
                            $totalPhysicalCollateral += floatval($collateral->physical_collateral_value ?? $collateral->collateral_amount);
                        }
                    }
                }
            @endphp

            <!-- Existing Collateral Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                @if(count($existingGuarantorData ?? []) > 0)
                <div class="bg-white rounded p-3 border border-gray-100">
                    <div class="text-xs text-gray-600 font-medium">Guarantors</div>
                    <div class="text-sm font-bold text-gray-900">{{ count($existingGuarantorData ?? []) }}</div>
                </div>
                @endif
                
                @if($totalAccountCollateral > 0)
                <div class="bg-white rounded p-3 border border-gray-100">
                    <div class="text-xs text-gray-600 font-medium">Account Collateral</div>
                    <div class="text-sm font-bold text-gray-900">{{ number_format($totalAccountCollateral, 0) }} TZS</div>
                </div>
                @endif
                
                @if($totalPhysicalCollateral > 0)
                <div class="bg-white rounded p-3 border border-gray-100">
                    <div class="text-xs text-gray-600 font-medium">Physical Collateral</div>
                    <div class="text-sm font-bold text-gray-900">{{ number_format($totalPhysicalCollateral, 0) }} TZS</div>
                </div>
                @endif
            </div>
            
            <!-- Total Existing Collateral -->
            @if(($totalAccountCollateral + $totalPhysicalCollateral) > 0)
            <div class="bg-gray-100 rounded p-3 border border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-sm font-semibold text-gray-900">Total Existing Collateral</div>
                    <div class="text-lg font-bold text-gray-900">
                        {{ number_format($totalAccountCollateral + $totalPhysicalCollateral, 0) }} TZS
                    </div>
                </div>
            </div>
            @endif
        </div>
    @elseif($loanType === 'Restructure')
        <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <h3 class="text-xs font-semibold text-yellow-900">No Collateral Found for Original Loan</h3>
                </div>
                <button type="button"
                        wire:click="refreshCollateralData"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="px-2 py-1 bg-yellow-600 text-white text-xs rounded hover:bg-yellow-700 transition-colors font-medium inline-flex items-center">
                    <svg wire:loading.remove class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <svg wire:loading class="animate-spin w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove>Refresh Data</span>
                    <span wire:loading>Refreshing...</span>
                </button>
            </div>
            <p class="text-xs text-yellow-700">
                @if(isset($debugInfo['original_loan_number']))
                    The original loan being restructured ({{ $debugInfo['original_loan_number'] }}) has no collateral information in the system.
                @else
                    Unable to find the original loan reference. The restructure loan may not have a valid original loan linked.
                @endif
                You may need to provide new collateral details below.
            </p>
        </div>
    @endif
            
    <!-- Messages -->
    @if (session()->has('message_feedback'))
        <div class="bg-green-50 border border-green-200 rounded p-3 mt-4">
            <div class="flex items-center">
                <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-xs font-medium text-green-800">{{ session('message_feedback') }}</span>
            </div>
        </div>
    @endif
    
    @if (session()->has('message_feedback_fail'))
        <div class="bg-red-50 border border-red-200 rounded p-3 mt-4">
            <div class="flex items-center">
                <svg class="w-4 h-4 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-xs font-medium text-red-800">{{ session('message_feedback_fail') }}</span>
            </div>
        </div>
    @endif
</div>