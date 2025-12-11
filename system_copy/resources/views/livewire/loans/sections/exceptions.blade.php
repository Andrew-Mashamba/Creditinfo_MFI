{{-- EXCEPTIONS SECTION --}}

<div class="w-full" data-exceptions-section>
<p for="stability" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">EXCEPTIONS</p>
<div id="stability" class="w-fullx bg-gray-50 rounded rounded-lg shadow-sm p-1 mb-4">
    <div class="w-full bg-white rounded rounded-lg shadow-sm p-2">

        @if(isset($exceptionData) && !empty($exceptionData))
        <!-- Exception Summary -->
        @if(isset($exceptionData['summary']))
        <div class="mb-4 p-3 rounded-lg 
            @if(($exceptionData['summary']['overall_status'] ?? '') === 'APPROVED') bg-green-50 border border-green-200
            @elseif(($exceptionData['summary']['overall_status'] ?? '') === 'REJECTED') bg-red-50 border border-red-200
            @else bg-yellow-50 border border-yellow-200 @endif">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full mr-2 
                        @if(($exceptionData['summary']['overall_status'] ?? '') === 'APPROVED') bg-green-500
                        @elseif(($exceptionData['summary']['overall_status'] ?? '') === 'REJECTED') bg-red-500
                        @else bg-yellow-500 @endif">
                    </div>
                    <span class="font-semibold text-sm 
                            @if(($exceptionData['summary']['overall_status'] ?? '') === 'APPROVED') text-green-800
                        @elseif(($exceptionData['summary']['overall_status'] ?? '') === 'REJECTED') text-red-800
                            @else text-yellow-800 @endif">
                        Overall Status: {{ $exceptionData['summary']['overall_status'] ?? 'PENDING' }}
                    </span>
                </div>
                <div class="text-xs text-gray-600">
                    {{ $exceptionData['summary']['passed_checks'] ?? 0 }}/{{ $exceptionData['summary']['total_checks'] ?? 0 }} checks passed
                </div>
            </div>
            @if(($exceptionData['summary']['high_severity'] ?? 0) > 0 || ($exceptionData['summary']['medium_severity'] ?? 0) > 0)
            <div class="mt-2 text-xs">
                @if(($exceptionData['summary']['high_severity'] ?? 0) > 0)
                <span class="text-red-600 font-medium">{{ $exceptionData['summary']['high_severity'] ?? 0 }} high severity issues</span>
                @endif
                @if(($exceptionData['summary']['medium_severity'] ?? 0) > 0)
                <span class="text-yellow-600 font-medium">{{ $exceptionData['summary']['medium_severity'] ?? 0 }} medium severity issues</span>
                @endif
            </div>
            @endif
        </div>
        @endif

        <!-- Exception Details -->
        <div class="space-y-3">
            @if(isset($exceptionData['loan_amount']))
            <div class="border border-gray-200 rounded-lg p-3 
                @if($exceptionData['loan_amount']['is_exceeded'] ?? false) bg-red-50 border-red-200
                @else bg-green-50 border-green-200 @endif">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h4 class="font-semibold text-sm text-gray-900">{{ $exceptionData['loan_amount']['name'] ?? 'Loan Amount Check' }}</h4>
                        <p class="text-xs text-gray-600 mt-1">{{ $exceptionData['loan_amount']['description'] ?? 'Checking loan amount limits' }}</p>
                        @if($exceptionData['loan_amount']['recommendation'] ?? false)
                        <p class="text-xs text-blue-600 mt-1 font-medium">💡 {{ $exceptionData['loan_amount']['recommendation'] }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-600">
                            <div>Limit: {{ number_format($exceptionData['loan_amount']['limit'] ?? 0, 2) }} {{ $exceptionData['loan_amount']['unit'] ?? 'TZS' }}</div>
                            <div>Given: {{ number_format($exceptionData['loan_amount']['given'] ?? 0, 2) }} {{ $exceptionData['loan_amount']['unit'] ?? 'TZS' }}</div>
                            @if(($exceptionData['loan_amount']['percentage'] ?? 0) > 0)
                            <div class="text-xs {{ ($exceptionData['loan_amount']['is_exceeded'] ?? false) ? 'text-red-600' : 'text-green-600' }}">
                                {{ $exceptionData['loan_amount']['percentage'] }}% of limit
                            </div>
                            @endif
                        </div>
                        <div class="mt-1">
                            <span class="px-2 py-1 text-xs rounded-full font-medium
                                @if($exceptionData['loan_amount']['is_exceeded'] ?? false) bg-red-100 text-red-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ $exceptionData['loan_amount']['status'] ?? 'PENDING' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($exceptionData['term']))
            <div class="border border-gray-200 rounded-lg p-3 
                @if($exceptionData['term']['is_exceeded'] ?? false) bg-red-50 border-red-200
                @else bg-green-50 border-green-200 @endif">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h4 class="font-semibold text-sm text-gray-900">{{ $exceptionData['term']['name'] ?? 'Term Check' }}</h4>
                        <p class="text-xs text-gray-600 mt-1">{{ $exceptionData['term']['description'] ?? 'Checking loan term limits' }}</p>
                        @if($exceptionData['term']['recommendation'] ?? false)
                        <p class="text-xs text-blue-600 mt-1 font-medium">💡 {{ $exceptionData['term']['recommendation'] }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-600">
                            <div>Limit: {{ $exceptionData['term']['limit'] ?? 0 }} {{ $exceptionData['term']['unit'] ?? 'months' }}</div>
                            <div>Given: {{ $exceptionData['term']['given'] ?? 0 }} {{ $exceptionData['term']['unit'] ?? 'months' }}</div>
                            @if(($exceptionData['term']['percentage'] ?? 0) > 0)
                            <div class="text-xs {{ ($exceptionData['term']['is_exceeded'] ?? false) ? 'text-red-600' : 'text-green-600' }}">
                                {{ $exceptionData['term']['percentage'] }}% of limit
                            </div>
                            @endif
                        </div>
                        <div class="mt-1">
                            <span class="px-2 py-1 text-xs rounded-full font-medium
                                @if($exceptionData['term']['is_exceeded'] ?? false) bg-red-100 text-red-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ $exceptionData['term']['status'] ?? 'PENDING' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($exceptionData['credit_score']))
            <div class="border border-gray-200 rounded-lg p-3 
                @if($exceptionData['credit_score']['is_exceeded'] ?? false) bg-red-50 border-red-200
                @else bg-green-50 border-green-200 @endif">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h4 class="font-semibold text-sm text-gray-900">{{ $exceptionData['credit_score']['name'] ?? 'Credit Score Check' }}</h4>
                        <p class="text-xs text-gray-600 mt-1">{{ $exceptionData['credit_score']['description'] ?? 'Checking credit score requirements' }}</p>
                        @if($exceptionData['credit_score']['recommendation'] ?? false)
                        <p class="text-xs text-blue-600 mt-1 font-medium">💡 {{ $exceptionData['credit_score']['recommendation'] }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-600">
                            <div>Limit: {{ $exceptionData['credit_score']['limit'] ?? 0 }} {{ $exceptionData['credit_score']['unit'] ?? 'score' }}</div>
                            <div>Given: {{ $exceptionData['credit_score']['given'] ?? 0 }} (Grade: {{ $exceptionData['credit_score']['grade'] ?? 'N/A' }})</div>
                            @if(($exceptionData['credit_score']['percentage'] ?? 0) > 0)
                            <div class="text-xs {{ ($exceptionData['credit_score']['is_exceeded'] ?? false) ? 'text-red-600' : 'text-green-600' }}">
                                {{ $exceptionData['credit_score']['percentage'] }}% of limit
                            </div>
                            @endif
                        </div>
                        <div class="mt-1">
                            <span class="px-2 py-1 text-xs rounded-full font-medium
                                @if($exceptionData['credit_score']['is_exceeded'] ?? false) bg-red-100 text-red-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ $exceptionData['credit_score']['status'] ?? 'PENDING' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($exceptionData['salary_installment']))
            <div class="border border-gray-200 rounded-lg p-3 
                @if($exceptionData['salary_installment']['is_exceeded'] ?? false) bg-red-50 border-red-200
                @else bg-green-50 border-green-200 @endif">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h4 class="font-semibold text-sm text-gray-900">{{ $exceptionData['salary_installment']['name'] ?? 'Salary/Installment Check' }}</h4>
                        <p class="text-xs text-gray-600 mt-1">{{ $exceptionData['salary_installment']['description'] ?? 'Checking salary to installment ratio' }}</p>
                        @if($exceptionData['salary_installment']['recommendation'] ?? false)
                        <p class="text-xs text-blue-600 mt-1 font-medium">💡 {{ $exceptionData['salary_installment']['recommendation'] }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-600">
                            <div>Take Home: {{ number_format($exceptionData['salary_installment']['take_home'] ?? 0, 2) }} {{ $exceptionData['salary_installment']['unit'] ?? 'TZS' }}</div>
                            <div>Limit: {{ number_format($exceptionData['salary_installment']['limit'] ?? 0, 2) }} {{ $exceptionData['salary_installment']['unit'] ?? 'TZS' }}</div>
                            <div>Given: {{ number_format($exceptionData['salary_installment']['given'] ?? 0, 2) }} {{ $exceptionData['salary_installment']['unit'] ?? 'TZS' }}</div>
                            @if(($exceptionData['salary_installment']['percentage'] ?? 0) > 0)
                            <div class="text-xs {{ ($exceptionData['salary_installment']['is_exceeded'] ?? false) ? 'text-red-600' : 'text-green-600' }}">
                                {{ $exceptionData['salary_installment']['percentage'] }}% of limit
                            </div>
                            @endif
                        </div>
                        <div class="mt-1">
                            <span class="px-2 py-1 text-xs rounded-full font-medium
                                @if($exceptionData['salary_installment']['is_exceeded'] ?? false) bg-red-100 text-red-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ $exceptionData['salary_installment']['status'] ?? 'PENDING' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($exceptionData['collateral']))
            <div class="border border-gray-200 rounded-lg p-3 
                @if($exceptionData['collateral']['is_exceeded'] ?? false) bg-red-50 border-red-200
                @else bg-green-50 border-green-200 @endif">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h4 class="font-semibold text-sm text-gray-900">{{ $exceptionData['collateral']['name'] ?? 'Collateral Check' }}</h4>
                        <p class="text-xs text-gray-600 mt-1">{{ $exceptionData['collateral']['description'] ?? 'Checking collateral requirements' }}</p>
                        @if($exceptionData['collateral']['recommendation'] ?? false)
                        <p class="text-xs text-blue-600 mt-1 font-medium">💡 {{ $exceptionData['collateral']['recommendation'] }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-600">
                            @if(($exceptionData['collateral']['unit'] ?? '') === '%')
                            <div>Limit: {{ $exceptionData['collateral']['limit'] ?? 0 }}%</div>
                            <div>Given: {{ number_format($exceptionData['collateral']['given'] ?? 0, 2) }}%</div>
                            @else
                            <div>Limit: {{ number_format($exceptionData['collateral']['limit'] ?? 0, 2) }} {{ $exceptionData['collateral']['unit'] ?? 'TZS' }}</div>
                            <div>Given: {{ number_format($exceptionData['collateral']['given'] ?? 0, 2) }} {{ $exceptionData['collateral']['unit'] ?? 'TZS' }}</div>
                            @endif
                            @if(($exceptionData['collateral']['percentage'] ?? 0) > 0)
                            <div class="text-xs {{ ($exceptionData['collateral']['is_exceeded'] ?? false) ? 'text-red-600' : 'text-green-600' }}">
                                {{ $exceptionData['collateral']['percentage'] }}% of limit
                            </div>
                            @endif
                        </div>
                        <div class="mt-1">
                            <span class="px-2 py-1 text-xs rounded-full font-medium
                                @if($exceptionData['collateral']['is_exceeded'] ?? false) bg-red-100 text-red-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ $exceptionData['collateral']['status'] ?? 'PENDING' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Action Buttons -->
        @if($showActionButtons && isset($exceptionData['summary']) && !in_array($loan->status ?? '', ['PENDING_DISBURSEMENT', 'PENDING_EXCEPTION_APPROVAL', 'APPROVED', 'DISBURSED']))
        <div class="mt-6 pt-4 border-t border-gray-200">
            <div class="bg-gradient-to-r from-gray-50 to-blue-50 p-6 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-3 mb-4">
                            @if($exceptionData['summary']['can_approve'] ?? false)
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-green-800 font-semibold text-lg">✅ Loan Assessment Complete</span>
                            </div>
                            @elseif($exceptionData['summary']['requires_exception'] ?? false)
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-yellow-800 font-semibold text-lg">⚠️ Requires Exception Approval</span>
                            </div>
                            @else
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-red-800 font-semibold text-lg">❌ Loan Does Not Meet Requirements</span>
                            </div>
                            @endif
                        </div>

                        <div class="text-base text-gray-700 leading-relaxed">
                            @if($exceptionData['summary']['can_approve'] ?? false)
                            <p class="mb-2">All loan requirements have been met. The loan is ready for disbursement approval.</p>
                            <p class="text-sm text-gray-600">Approved Amount: <span class="font-semibold text-green-700">{{ number_format($this->approved_loan_value ?? 0, 2) }} TZS</span></p>
                            @elseif($exceptionData['summary']['requires_exception'] ?? false)
                            <p class="mb-2">Some loan parameters exceed standard limits but can be approved with exceptions.</p>
                            <p class="text-sm text-gray-600">Requested Amount: <span class="font-semibold text-yellow-700">{{ number_format($this->approved_loan_value ?? 0, 2) }} TZS</span></p>
                            @else
                            <p class="mb-2">Critical loan requirements are not met. The loan cannot be approved at this time.</p>
                            <p class="text-sm text-gray-600">Requested Amount: <span class="font-semibold text-red-700">{{ number_format($this->approved_loan_value ?? 0, 2) }} TZS</span></p>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
                        @if(in_array($loan->status ?? '', ['PENDING_APPROVAL', 'PENDING_EXCEPTION_APPROVAL']))
                        <!-- Status-specific buttons for pending approval loans -->
                        @if($loan->status === 'PENDING_APPROVAL')
                        <div class="flex flex-col sm:flex-row gap-3 w-full">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex-1">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-blue-800">⏳ Pending Approval</h4>
                                        <p class="text-xs text-blue-600">Loan is awaiting approval from the approval team.</p>
                                    </div>
                                </div>
                            </div>

                            <button wire:click="withdrawApprovalRequest()"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="inline-flex items-center justify-center px-6 py-3 bg-orange-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wide hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-800 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md min-w-[200px] disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg wire:loading.remove class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <svg wire:loading class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove>Withdraw Request</span>
                                <span wire:loading>Withdrawing...</span>
                            </button>
                        </div>
                        @elseif($loan->status === 'PENDING_EXCEPTION_APPROVAL')
                        <div class="flex flex-col sm:flex-row gap-3 w-full">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex-1">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-yellow-800">⚠️ Pending Exception Approval</h4>
                                        <p class="text-xs text-yellow-600">Loan is awaiting exception approval from the approval team.</p>
                                    </div>
                                </div>
                            </div>

                            <button wire:click="withdrawExceptionRequest()"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="inline-flex items-center justify-center px-6 py-3 bg-orange-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wide hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-800 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md min-w-[200px] disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg wire:loading.remove class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <svg wire:loading class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove>Withdraw Request</span>
                                <span wire:loading>Withdrawing...</span>
                            </button>
                        </div>
                        @endif
                        @elseif(isset($exceptionData['summary']['can_approve']) && ($exceptionData['summary']['can_approve'] ?? false))
                        <button wire:click="sendForApproval()"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="inline-flex items-center justify-center px-6 py-3 bg-green-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wide hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md min-w-[200px] disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg wire:loading.remove class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <svg wire:loading class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove>Send for Approval</span>
                            <span wire:loading>Sending...</span>
                        </button>

                       

                        @elseif(isset($exceptionData['summary']['requires_exception']) && ($exceptionData['summary']['requires_exception'] ?? false))
                        <button wire:click="sendForApprovalWithExceptions()"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="inline-flex items-center justify-center px-6 py-3 bg-yellow-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wide hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md min-w-[200px] disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg wire:loading.remove class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <svg wire:loading class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove>Send for Exception Approval</span>
                            <span wire:loading>Sending...</span>
                        </button>

                      
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        @else
        <!-- No Exception Data Available -->
        <div class="text-center py-8">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No exceptions found</h3>
            <p class="text-sm text-gray-500">All loan parameters are within acceptable limits.</p>
        </div>
        @endif

    </div>
</div> 
</div>