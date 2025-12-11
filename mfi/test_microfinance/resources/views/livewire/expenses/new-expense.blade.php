<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">New Expense Registration</h1>
                    <p class="mt-2 text-gray-600">Register a new expense with budget compliance checking</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="bg-red-100 rounded-full p-2">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session()->has('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('warning'))
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Form Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-red-50 to-indigo-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Expense Details</h2>
                        <p class="text-sm text-gray-600 mt-1">Fill in the expense information below</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                        <span class="text-sm text-gray-600">Budget Integration Active</span>
                    </div>
                </div>
            </div>

            <form wire:submit.prevent="checkBudget" class="p-6 space-y-6">
                <!-- Expense Account Selection -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="expense_type_id" class="block text-sm font-semibold text-gray-700">
                            Expense Account <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select 
                                wire:model="expense_type_id" 
                                id="expense_type_id" 
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 transition-colors duration-200"
                                required
                            >
                                <option value="">Select an expense account</option>
                                @foreach($expense_types as $type)
                                    <option value="{{ $type->id }}">{{ $type->account_name }} ({{ $type->account_number }})</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        @error('expense_type_id') 
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="payment_type" class="block text-sm font-semibold text-gray-700">
                            Payment Type <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select 
                                wire:model="payment_type" 
                                id="payment_type" 
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 transition-colors duration-200"
                                required
                            >
                                <option value="">Select payment type</option>
                                @foreach($payment_types as $key => $type)
                                    <option value="{{ $key }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        @error('payment_type') 
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>

                <!-- Amount and Month -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="amount" class="block text-sm font-semibold text-gray-700">
                            Amount (TZS) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">TZS</span>
                            </div>
                            <input 
                                type="number" 
                                wire:model="amount" 
                                id="amount" 
                                step="0.01" 
                                min="0" 
                                class="block w-full pl-12 rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 transition-colors duration-200"
                                placeholder="0.00"
                                required
                            >
                        </div>
                        @error('amount') 
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="expense_month" class="block text-sm font-semibold text-gray-700">
                            Expense Month
                        </label>
                        <input 
                            type="month" 
                            wire:model="expense_month" 
                            id="expense_month" 
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 transition-colors duration-200"
                        >
                        <p class="text-xs text-gray-500">Leave empty to use current month</p>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <label for="description" class="block text-sm font-semibold text-gray-700">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        wire:model="description" 
                        id="description" 
                        rows="4" 
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 transition-colors duration-200"
                        placeholder="Provide a detailed description of the expense..."
                        required
                    ></textarea>
                    @error('description') 
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Budget Preview Section -->
                @if($expense_type_id && $amount)
                    <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                        <div class="flex items-center mb-3">
                            <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="text-sm font-semibold text-red-900">Expense Preview</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Selected Account:</span>
                                <p class="font-medium text-gray-900">
                                    @php
                                        $selectedAccount = $expense_types->firstWhere('id', $expense_type_id);
                                    @endphp
                                    {{ $selectedAccount ? $selectedAccount->account_name : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-gray-600">Expense Amount:</span>
                                <p class="font-medium text-gray-900">{{ number_format($amount, 2) }} TZS</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Month:</span>
                                <p class="font-medium text-gray-900">
                                    {{ $expense_month ? \Carbon\Carbon::parse($expense_month . '-01')->format('F Y') : 'Current Month' }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-red-200">
                            <p class="text-xs text-red-700">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Budget compliance will be checked when you submit this expense
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Submit Button -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                            <span class="text-sm text-gray-600">All fields completed</span>
                        </div>
                    </div>
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-red-600 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                    >
                        <svg wire:loading wire:target="checkBudget" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg wire:loading.remove wire:target="checkBudget" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Check Budget & Submit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Budget Overrun Modal -->
    @if($showBudgetModal)
        <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 px-6 py-4 border-b border-yellow-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900" id="modal-title">
                                    Budget Exceeded
                                </h3>
                                <p class="text-sm text-gray-600">This expense exceeds the monthly budget limit</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-6">
                        @if($budgetCheckResult)
                            <!-- Budget Details -->
                            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4">Budget Analysis</h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Monthly Budget:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($budgetCheckResult['monthly_budget'], 2) }} TZS</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Already Spent:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($budgetCheckResult['monthly_spent'], 2) }} TZS</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Remaining Budget:</span>
                                            <span class="text-sm font-medium {{ $budgetCheckResult['remaining_budget'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ number_format($budgetCheckResult['remaining_budget'], 2) }} TZS
                                            </span>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Over Budget By:</span>
                                            <span class="text-sm font-medium text-red-600">{{ number_format($budgetCheckResult['over_budget_amount'], 2) }} TZS</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Current Utilization:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($budgetCheckResult['utilization_percentage'], 1) }}%</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">After This Expense:</span>
                                            <span class="text-sm font-medium text-red-600">{{ number_format($budgetCheckResult['new_utilization_percentage'], 1) }}%</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div class="mt-4">
                                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                                        <span>Budget Utilization</span>
                                        <span>{{ number_format($budgetCheckResult['utilization_percentage'], 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="h-3 rounded-full transition-all duration-300 {{ $budgetCheckResult['utilization_percentage'] > 100 ? 'bg-red-500' : ($budgetCheckResult['utilization_percentage'] > 90 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                                             style="width: {{ min(100, $budgetCheckResult['utilization_percentage']) }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resolution Options -->
                            <div class="space-y-4">
                                <h4 class="text-sm font-semibold text-gray-900">Choose how to proceed:</h4>
                                
                                <!-- Option 1: Use Previous Months -->
                                <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <h5 class="text-sm font-medium text-red-900">Use Previous Months' Budget</h5>
                                            <p class="text-sm text-red-700 mt-1">If available, use unused budget from previous months to cover this expense.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Option 2: Request Additional Funds -->
                                <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <h5 class="text-sm font-medium text-orange-900">Request Additional Funds</h5>
                                            <p class="text-sm text-orange-700 mt-1">Submit a request for additional budget allocation to cover this expense.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Option 3: Proceed with Override -->
                                <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <h5 class="text-sm font-medium text-red-900">Proceed with Override</h5>
                                            <p class="text-sm text-red-700 mt-1">Submit with budget override (requires additional approval).</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Notes -->
                                <div class="space-y-2">
                                    <label for="budget_notes" class="block text-sm font-medium text-gray-700">Additional Notes (Optional)</label>
                                    <textarea 
                                        wire:model="budgetNotes" 
                                        id="budget_notes" 
                                        rows="3" 
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50"
                                        placeholder="Explain why this expense is necessary and any additional context..."
                                    ></textarea>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse">
                        <button 
                            type="button" 
                            wire:click="usePreviousMonthsBudget"
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto transition-colors duration-200"
                            wire:loading.attr="disabled"
                        >
                            <svg wire:loading wire:target="usePreviousMonthsBudget" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Use Previous Months
                        </button>
                        <button 
                            type="button" 
                            wire:click="requestAdditionalFunds"
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto transition-colors duration-200"
                            wire:loading.attr="disabled"
                        >
                            <svg wire:loading wire:target="requestAdditionalFunds" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Request Funds
                        </button>
                        <button 
                            type="button" 
                            wire:click="proceedWithBudgetOverride"
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto transition-colors duration-200"
                            wire:loading.attr="disabled"
                        >
                            <svg wire:loading wire:target="proceedWithBudgetOverride" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Override
                        </button>
                        <button 
                            type="button" 
                            wire:click="cancelExpense"
                            class="mt-3 w-full inline-flex justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto transition-colors duration-200"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
