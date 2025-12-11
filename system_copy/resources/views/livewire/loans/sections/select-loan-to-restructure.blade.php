{{-- SELECT LOAN TO RESTRUCTURE SECTION --}}
@if($loan && in_array($loan->loan_type_2, ['Restructure', 'Restructuring']))
<div class="w-full bg-gray-50 rounded-lg shadow-sm p-1 mb-4">
    <div class="w-full bg-white rounded-lg shadow-sm p-4">

        <!-- Section Header -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">SELECT LOAN TO RESTRUCTURE</h3>
            <p class="text-sm text-gray-600">Choose an active loan to restructure with this new application</p>
        </div>

        @if($activeLoans && count($activeLoans) > 0)
        <!-- Active Loans List -->
        <div class="space-y-4 mb-6">
            @foreach($activeLoans as $activeLoan)
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors 
                @if($selectedLoan == $activeLoan['id']) border-blue-300 bg-blue-50 @endif">
                
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <input type="radio" 
                                wire:model="selectedLoan" 
                                value="{{ $activeLoan['id'] }}"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2">
                            <label class="ml-2 text-sm font-semibold text-gray-900 cursor-pointer">
                                {{ $activeLoan['loan_number'] ?? 'N/A' }}
                            </label>
                            <span class="ml-2 px-2 py-1 text-xs rounded-full 
                                @if(($activeLoan['loan_status'] ?? '') === 'Active') bg-green-100 text-green-800 
                                @elseif(($activeLoan['loan_status'] ?? '') === 'Overdue') bg-red-100 text-red-800 
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $activeLoan['loan_status'] ?? 'N/A' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600">{{ $activeLoan['product_name'] ?? 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-gray-900">{{ number_format($activeLoan['account_balance'] ?? 0, 2) }} TZS</div>
                        <div class="text-xs text-gray-500">Outstanding</div>
                    </div>
                </div>

                <!-- Loan Details Grid -->
                <div class="grid grid-cols-2 gap-4 text-xs mb-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Principal Balance:</span>
                        <span class="font-medium">{{ number_format($activeLoan['principal_balance'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Interest Balance:</span>
                        <span class="font-medium">{{ number_format($activeLoan['interest_balance'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Penalty Balance:</span>
                        <span class="font-medium">{{ number_format($activeLoan['penalty_balance'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Days Overdue:</span>
                        <span class="font-medium 
                            @if(($activeLoan['days_overdue'] ?? 0) > 30) text-red-600 
                            @elseif(($activeLoan['days_overdue'] ?? 0) > 7) text-yellow-600 
                            @else text-green-600 @endif">
                            {{ $activeLoan['days_overdue'] ?? 0 }} days
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Monthly Payment:</span>
                        <span class="font-medium">{{ number_format($activeLoan['installment'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Next Due Date:</span>
                        <span class="font-medium">{{ $activeLoan['next_due_date'] ?? 'N/A' }}</span>
                    </div>
                </div>

                <!-- Restructuring Benefits -->
                @if($selectedLoan == $activeLoan['id'])
                <div class="bg-blue-50 p-3 rounded border border-blue-200">
                    <h5 class="text-xs font-semibold text-blue-900 mb-2">Restructuring Benefits:</h5>
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="flex justify-between">
                            <span class="text-blue-700">Interest Savings:</span>
                            <span class="font-semibold text-green-600">{{ number_format($activeLoan['interest_savings'] ?? 0, 2) }} TZS</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Penalty Savings:</span>
                            <span class="font-semibold text-green-600">{{ number_format($activeLoan['penalty_savings'] ?? 0, 2) }} TZS</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">New Monthly Payment:</span>
                            <span class="font-semibold text-blue-600">{{ number_format($activeLoan['new_monthly_payment'] ?? 0, 2) }} TZS</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Payment Reduction:</span>
                            <span class="font-semibold text-green-600">{{ number_format($activeLoan['payment_reduction'] ?? 0, 2) }} TZS</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Restructuring Actions -->
        @if($selectedLoan)
        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div class="flex-1">
                    <h4 class="text-sm font-semibold text-yellow-800">Restructuring Impact</h4>
                    <p class="text-sm text-yellow-700 mt-1">
                        The selected loan will be set to PENDING status and returned to the initial assessment stage. 
                        All outstanding balances will be consolidated into this new loan application.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 flex space-x-3">
            <button wire:click="confirmRestructuring" 
                class="px-4 py-2 bg-blue-900 text-white text-sm font-medium rounded-lg hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Confirm Restructuring
            </button>
            
            <button wire:click="clearRestructuringSelection" 
                class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Clear Selection
            </button>
        </div>
        @endif

        @else
        <!-- No Active Loans -->
        <div class="text-center py-8">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Active Loans Found</h3>
            <p class="text-sm text-gray-500">There are no active loans available for restructuring with this application.</p>
        </div>
        @endif

    </div>
</div>
@endif 