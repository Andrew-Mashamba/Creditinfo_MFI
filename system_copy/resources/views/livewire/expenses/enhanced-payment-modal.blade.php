<!-- Enhanced Multi-Step Payment Modal -->
@if($showPaymentModal && $selectedExpense)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             wire:click="closePaymentModal" aria-hidden="true"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-4xl ">
            
            <!-- Modal Header with Progress Indicator -->
            <div class="bg-gradient-to-r from-blue-900 to-blue-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        Process Expense Payment
                    </h3>
                    <button wire:click="closePaymentModal" class="text-white hover:text-gray-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Progress Steps -->
                <div class="mt-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Step 1 -->
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $currentStep >= 1 ? 'bg-white text-blue-900' : 'bg-blue-800 text-white' }} flex items-center justify-center text-sm font-medium">
                                    1
                                </div>
                                <div class="ml-2 text-sm text-white">Funding Source</div>
                            </div>
                            
                            <!-- Arrow -->
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            
                            <!-- Step 2 -->
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $currentStep >= 2 ? 'bg-white text-blue-900' : 'bg-blue-800 text-white' }} flex items-center justify-center text-sm font-medium">
                                    2
                                </div>
                                <div class="ml-2 text-sm text-white">Payment Method</div>
                            </div>
                            
                            <!-- Arrow -->
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            
                            <!-- Step 3 -->
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $currentStep >= 3 ? 'bg-white text-blue-900' : 'bg-blue-800 text-white' }} flex items-center justify-center text-sm font-medium">
                                    3
                                </div>
                                <div class="ml-2 text-sm text-white">Review & Process</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="px-6 py-4">
                <!-- Expense Details Summary -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedExpense->description }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Amount</dt>
                            <dd class="mt-1 text-lg font-semibold text-green-600">TZS {{ number_format($selectedExpense->amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Expense Account</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedExpense->account->account_name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Request Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedExpense->expense_date }}</dd>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Funding Source Selection -->
                @if($currentStep == 1)
                <div class="space-y-6">
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-4">💰 Select Funding Source</h4>
                        
                        <div class="space-y-4">
                            <!-- Petty Cash Option -->
                            <label class="relative flex cursor-pointer rounded-lg border border-gray-300 p-4 focus:outline-none {{ $fundingSource === 'petty_cash' ? 'ring-2 ring-blue-500 border-transparent bg-blue-50' : 'hover:bg-gray-50' }}">
                                <input type="radio" wire:model="fundingSource" value="petty_cash" class="sr-only" name="funding-source">
                                <div class="flex flex-1">
                                    <div class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            Petty Cash
                                        </span>
                                        <span class="mt-1 block text-sm text-gray-500">Use branch petty cash account for this payment</span>
                                        <span class="mt-1 block text-xs text-gray-400">Source: Branch Petty Cash Account</span>
                                    </div>
                                </div>
                            </label>

                            <!-- Bank Account Option -->
                            <label class="relative flex cursor-pointer rounded-lg border border-gray-300 p-4 focus:outline-none {{ $fundingSource === 'bank_account' ? 'ring-2 ring-blue-500 border-transparent bg-blue-50' : 'hover:bg-gray-50' }}">
                                <input type="radio" wire:model="fundingSource" value="bank_account" class="sr-only" name="funding-source">
                                <div class="flex flex-1">
                                    <div class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            Organization Bank Account
                                        </span>
                                        <span class="mt-1 block text-sm text-gray-500">Use specific organization bank account for this payment</span>
                                        <span class="mt-1 block text-xs text-gray-400">Choose from registered bank accounts</span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Bank Account Selection (shown when bank_account is selected) -->
                        @if($fundingSource === 'bank_account')
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Bank Account</label>
                            <select wire:model="selectedBankAccount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Choose bank account...</option>
                                @if($availableBankAccounts && count($availableBankAccounts) > 0)
                                    @foreach($availableBankAccounts as $account)
                                    <option value="{{ is_array($account) ? $account['id'] : $account->id }}">
                                        {{ is_array($account) ? $account['account_name'] : $account->account_name }} 
                                        ({{ is_array($account) ? $account['bank_name'] : $account->bank_name }}) - 
                                        {{ is_array($account) ? $account['account_number'] : $account->account_number }}
                                        - Balance: TZS {{ number_format(is_array($account) ? $account['current_balance'] : $account->current_balance, 2) }}
                                    </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Step 2: Payment Method Selection -->
                @if($currentStep == 2)
                <div class="space-y-6">
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-4">📊 Select Payment Method</h4>
                        
                        <div class="space-y-4">
                            <!-- Cash Payment -->
                            <label class="relative flex cursor-pointer rounded-lg border border-gray-300 p-4 focus:outline-none {{ $paymentMethod === 'cash' ? 'ring-2 ring-blue-500 border-transparent bg-blue-50' : 'hover:bg-gray-50' }}">
                                <input type="radio" wire:model="paymentMethod" value="cash" class="sr-only" name="payment-method">
                                <div class="flex flex-1">
                                    <div class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            Cash Payment
                                        </span>
                                        <span class="mt-1 block text-sm text-gray-500">Internal accounting transaction only</span>
                                    </div>
                                </div>
                            </label>

                            <!-- Bank Transfer -->
                            <label class="relative flex cursor-pointer rounded-lg border border-gray-300 p-4 focus:outline-none {{ $paymentMethod === 'bank_transfer' ? 'ring-2 ring-blue-500 border-transparent bg-blue-50' : 'hover:bg-gray-50' }}">
                                <input type="radio" wire:model="paymentMethod" value="bank_transfer" class="sr-only" name="payment-method">
                                <div class="flex flex-1">
                                    <div class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                            </svg>
                                            Bank Transfer
                                        </span>
                                        <span class="mt-1 block text-sm text-gray-500">Transfer to recipient's bank account</span>
                                    </div>
                                </div>
                            </label>

                            <!-- Mobile Money -->
                            <label class="relative flex cursor-pointer rounded-lg border border-gray-300 p-4 focus:outline-none {{ $paymentMethod === 'mobile_money' ? 'ring-2 ring-blue-500 border-transparent bg-blue-50' : 'hover:bg-gray-50' }}">
                                <input type="radio" wire:model="paymentMethod" value="mobile_money" class="sr-only" name="payment-method">
                                <div class="flex flex-1">
                                    <div class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            Mobile Money
                                        </span>
                                        <span class="mt-1 block text-sm text-gray-500">Transfer to mobile wallet (M-Pesa, TigoPesa, etc.)</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Step 3: Payment Details & Processing -->
                @if($currentStep == 3)
                <div class="space-y-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">🔄 Payment Details & Processing</h4>
                    
                    <!-- Payment Details Forms -->
                    @if($paymentMethod === 'bank_transfer')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Account Holder Name</label>
                            <input type="text" wire:model="accountHolderName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter recipient name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                            <input type="text" wire:model="recipientAccountNumber" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter account number">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Destination Bank</label>
                            <select wire:model="recipientBankCode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select destination bank...</option>
                                @php
                                    $banks = config('fsp_providers.banks', []);
                                    // Sort banks by name
                                    uasort($banks, function($a, $b) {
                                        return strcmp($a['name'] ?? '', $b['name'] ?? '');
                                    });
                                @endphp
                                @foreach($banks as $bankKey => $bank)
                                    @if($bank['active'] ?? false)
                                    <option value="{{ $bank['code'] ?? $bankKey }}">
                                        {{ $bank['name'] }} 
                                        @if(isset($bank['tested']) && $bank['tested'])
                                            @if($bank['working'] ?? false)
                                                ✅
                                            @else
                                                ⚠️
                                            @endif
                                        @endif
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500">Select the recipient's bank from the available financial service providers</p>
                        </div>
                    </div>
                    @endif

                    @if($paymentMethod === 'mobile_money')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="text" wire:model="phoneNumber" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="255XXXXXXXXX">
                            <p class="mt-1 text-xs text-gray-500">Format: 255XXXXXXXXX (without +)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Network Provider</label>
                            <select wire:model="mnoProvider" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select provider...</option>
                                @php
                                    $wallets = config('fsp_providers.mobile_wallets', []);
                                @endphp
                                @foreach($wallets as $walletKey => $wallet)
                                    @if($wallet['active'] ?? false)
                                    <option value="{{ $walletKey }}">
                                        {{ $wallet['name'] }}
                                        @if(isset($wallet['tested']) && $wallet['tested'])
                                            @if($wallet['working'] ?? false)
                                                ✅
                                            @else
                                                ⚠️ (Issues in testing)
                                            @endif
                                        @endif
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Account Holder Name</label>
                            <input type="text" wire:model="accountHolderName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter recipient name">
                        </div>
                        
                        <!-- Mobile Money Amount Limit Notice -->
                        @if($selectedExpense && $selectedExpense->amount > 20000000)
                        <div class="md:col-span-2">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">Amount Exceeds Mobile Money Limit</h3>
                                        <p class="mt-1 text-sm text-yellow-700">Mobile money transfers are limited to TZS 20,000,000. Consider using bank transfer for this amount.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Payment Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Notes (Optional)</label>
                        <textarea wire:model="paymentNotes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <!-- Payment Summary -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h5 class="font-medium text-blue-900 mb-2">Payment Summary</h5>
                        <div class="space-y-2 text-sm text-blue-800">
                            <div class="flex justify-between">
                                <span>Funding Source:</span>
                                <span>{{ $fundingSource === 'petty_cash' ? 'Petty Cash' : 'Bank Account' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Payment Method:</span>
                                <span>{{ ucwords(str_replace('_', ' ', $paymentMethod)) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Amount:</span>
                                <span class="font-semibold">TZS {{ number_format($selectedExpense->amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Error Messages -->
                @if(count($paymentErrors) > 0)
                <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach($paymentErrors as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Modal Footer with Navigation -->
            <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse">
                @if($currentStep == 3)
                    <!-- Final Process Button -->
                    <button wire:click="processPayment" 
                            {{ $isProcessingPayment ? 'disabled' : '' }}
                            class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm {{ $isProcessingPayment ? 'opacity-50 cursor-not-allowed' : '' }}">
                        @if($isProcessingPayment)
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing Payment...
                        @else
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            Process Payment
                        @endif
                    </button>
                    
                    <!-- Back Button -->
                    <button wire:click="previousStep" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Back
                    </button>
                @else
                    <!-- Next Button -->
                    <button wire:click="nextStep" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Next
                    </button>
                    
                    <!-- Back Button (for step 2) -->
                    @if($currentStep > 1)
                    <button wire:click="previousStep" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Back
                    </button>
                    @endif
                @endif
                
                <!-- Cancel Button -->
                <button wire:click="closePaymentModal" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif