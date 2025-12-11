<div class="w-full p-2">
    {{-- Compact Header --}}
    <div class="flex items-center justify-between mb-4 p-3 bg-gray-50 rounded">
        <h2 class="text-lg font-semibold text-blue-900">Loan Repayment</h2>
        @if($currentStep > 1)
            <button wire:click="newPayment" class="px-3 py-1.5 bg-blue-900 text-white text-sm rounded hover:bg-blue-800">
                <i class="fas fa-plus mr-1 text-xs"></i>New Payment
            </button>
        @endif
    </div>

    {{-- Compact Progress Steps --}}
    <div class="mb-4 p-3 bg-white rounded shadow-sm">
        <div class="flex items-center justify-between">
            @php
                $steps = [
                    1 => 'Search',
                    2 => 'Details',
                    3 => 'Confirm',
                    4 => 'Receipt'
                ];
            @endphp
            @foreach($steps as $step => $label)
                <div class="flex items-center {{ $currentStep >= $step ? 'text-blue-900' : 'text-gray-400' }}">
                    <div class="rounded-full h-8 w-8 flex items-center justify-center border {{ $currentStep >= $step ? 'border-blue-900 bg-blue-900 text-white' : 'border-gray-300' }} text-xs font-semibold">
                        {{ $step }}
                    </div>
                    <span class="ml-1 text-xs hidden sm:inline">{{ $label }}</span>
                </div>
                @if($step < 4)
                    <div class="flex-1 border-t {{ $currentStep > $step ? 'border-blue-900' : 'border-gray-300' }} mx-2"></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Compact Alert Messages --}}
    @if (session()->has('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-3 mb-3">
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-3 mb-3">
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="bg-blue-50 border-l-4 border-blue-500 p-3 mb-3">
            <p class="text-sm text-blue-700">{{ session('info') }}</p>
        </div>
    @endif

    {{-- Step 1: Search --}}
    @if($currentStep == 1)
        <div class="bg-white rounded shadow-sm p-4">
            <h3 class="text-sm font-semibold text-blue-900 mb-3">Search for Loan</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Search By</label>
                    <select wire:model="searchType" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-900 focus:outline-none">
                        <option value="loan_id">Loan ID</option>
                        <option value="account_number">Account Number</option>
                        <option value="member_number">Member Number</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs text-gray-600 mb-1">
                        {{ $searchType == 'loan_id' ? 'Loan ID' : ($searchType == 'account_number' ? 'Account Number' : 'Member Number') }}
                    </label>
                    <input type="text" wire:model="searchValue" 
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-900 focus:outline-none"
                           placeholder="Enter value...">
                    @error('searchValue') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex items-end">
                    <button wire:click="searchLoan" 
                            class="w-full px-3 py-1.5 bg-blue-900 text-white text-sm rounded hover:bg-blue-800">
                        <i class="fas fa-search mr-1 text-xs"></i>Search
                    </button>
                </div>
            </div>
        </div>

        {{-- Multiple Loans Selection --}}
        @if($showLoanSelection && count($availableLoans) > 0)
            <div class="bg-white rounded shadow-sm p-4 mt-3">
                <h3 class="text-sm font-semibold text-blue-900 mb-2">Select Loan</h3>
                <p class="text-xs text-gray-600 mb-3">{{ $availableLoans[0]['member_name'] ?? '' }} ({{ $availableLoans[0]['member_number'] ?? '' }})</p>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-3 py-2 text-left">Loan Details</th>
                                <th class="px-3 py-2 text-left">Principal</th>
                                <th class="px-3 py-2 text-left">Outstanding</th>
                                <th class="px-3 py-2 text-left">Status</th>
                                <th class="px-3 py-2 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($availableLoans as $loan)
                                <tr>
                                    <td class="px-3 py-2">
                                        <div>
                                            <p class="font-semibold text-sm">{{ $loan['loan_id'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $loan['product_name'] }}</p>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">{{ number_format($loan['principal'], 0) }}</td>
                                    <td class="px-3 py-2">
                                        <span class="font-semibold">{{ number_format($loan['outstanding_balance'], 0) }}</span>
                                        @if($loan['days_in_arrears'] > 0)
                                            <span class="block text-xs text-gray-500">{{ $loan['days_in_arrears'] }} days overdue</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="px-2 py-0.5 text-xs rounded bg-gray-100">{{ $loan['status'] }}</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <button wire:click="selectLoan('{{ $loan['loan_id'] }}')" 
                                                class="px-3 py-1 bg-blue-900 text-white text-xs rounded hover:bg-blue-800">
                                            Select
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    {{-- Step 2: Payment Details --}}
    @if($currentStep == 2 && $selectedLoanId)
        <div class="space-y-3">
            {{-- Loan Info Grid --}}
            <div class="bg-white rounded shadow-sm p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-semibold text-blue-900">Loan Information</h3>
                    <div class="space-x-2">
                        <button wire:click="togglePaymentHistory" class="text-xs text-blue-900 hover:underline">
                            History
                        </button>
                        <button wire:click="toggleSchedule" class="text-xs text-blue-900 hover:underline">
                            Schedule
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="text-xs text-gray-600">Member</label>
                        <p class="text-sm font-semibold">{{ $memberDetails['name'] ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $memberDetails['member_number'] ?? '' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Loan ID</label>
                        <p class="text-sm font-semibold">{{ $loanDetails['loan_id'] ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $loanDetails['account_number'] ?? '' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Product</label>
                        <p class="text-sm font-semibold">{{ $loanDetails['product_name'] ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $loanDetails['interest_rate'] ?? 0 }}% Interest</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Principal</label>
                        <p class="text-sm font-semibold">{{ number_format($loanDetails['principal'] ?? 0, 0) }}</p>
                        <p class="text-xs text-gray-500">{{ $loanDetails['tenure'] ?? 0 }} months</p>
                    </div>
                </div>
            </div>

            {{-- Outstanding Balance Grid --}}
            <div class="bg-white rounded shadow-sm p-4">
                <h3 class="text-sm font-semibold text-blue-900 mb-3">Outstanding Balance</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="border-l-4 border-gray-300 pl-3">
                        <label class="text-xs text-gray-600">Penalties</label>
                        <p class="text-lg font-bold">{{ number_format($outstandingBalance['penalties'] ?? 0, 0) }}</p>
                    </div>
                    <div class="border-l-4 border-gray-300 pl-3">
                        <label class="text-xs text-gray-600">Interest</label>
                        <p class="text-lg font-bold">{{ number_format($outstandingBalance['interest'] ?? 0, 0) }}</p>
                    </div>
                    <div class="border-l-4 border-gray-300 pl-3">
                        <label class="text-xs text-gray-600">Principal</label>
                        <p class="text-lg font-bold">{{ number_format($outstandingBalance['principal'] ?? 0, 0) }}</p>
                    </div>
                    <div class="border-l-4 border-blue-900 pl-3">
                        <label class="text-xs text-gray-600">Total</label>
                        <p class="text-lg font-bold text-blue-900">{{ number_format($outstandingBalance['total'] ?? 0, 0) }}</p>
                    </div>
                </div>
                
                <button wire:click="calculateEarlySettlement" class="mt-3 text-xs text-blue-900 hover:underline">
                    Calculate Early Settlement
                </button>
            </div>

            {{-- Payment History (Compact) --}}
            @if($showPaymentHistory && count($paymentHistory) > 0)
                <div class="bg-white rounded shadow-sm p-4">
                    <h3 class="text-sm font-semibold text-blue-900 mb-3">Recent Payments</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-1 text-left">Date</th>
                                    <th class="px-2 py-1 text-left">Receipt</th>
                                    <th class="px-2 py-1 text-left">Amount</th>
                                    <th class="px-2 py-1 text-left">Method</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($paymentHistory as $payment)
                                    <tr>
                                        <td class="px-2 py-1">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
                                        <td class="px-2 py-1">{{ $payment->receipt_number }}</td>
                                        <td class="px-2 py-1 font-semibold">{{ number_format($payment->amount, 0) }}</td>
                                        <td class="px-2 py-1">{{ $payment->payment_method }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Schedule (Compact) --}}
            @if($showSchedule && count($repaymentSchedule) > 0)
                <div class="bg-white rounded shadow-sm p-4">
                    <h3 class="text-sm font-semibold text-blue-900 mb-3">Repayment Schedule</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-1 text-left">Due Date</th>
                                    <th class="px-2 py-1 text-left">Installment</th>
                                    <th class="px-2 py-1 text-left">Paid</th>
                                    <th class="px-2 py-1 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($repaymentSchedule as $schedule)
                                    <tr>
                                        <td class="px-2 py-1">{{ \Carbon\Carbon::parse($schedule->installment_date)->format('d/m/Y') }}</td>
                                        <td class="px-2 py-1 font-semibold">{{ number_format($schedule->installment, 0) }}</td>
                                        <td class="px-2 py-1">{{ number_format($schedule->payment ?? 0, 0) }}</td>
                                        <td class="px-2 py-1">
                                            <span class="px-2 py-0.5 text-xs rounded bg-gray-100">{{ $schedule->completion_status }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Payment Form --}}
            <div class="bg-white rounded shadow-sm p-4">
                <h3 class="text-sm font-semibold text-blue-900 mb-3">Payment Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Payment Amount *</label>
                        <div class="relative">
                            <span class="absolute left-2 top-1.5 text-xs text-gray-500">TZS</span>
                            <input type="number" wire:model="paymentAmount" 
                                   class="w-full pl-10 pr-2 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-900 focus:outline-none"
                                   placeholder="0.00" step="0.01" min="100">
                        </div>
                        @error('paymentAmount') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        
                        @if($paymentAmount)
                            <button wire:click="previewAllocation" class="mt-1 text-xs text-blue-900 hover:underline">
                                Preview Allocation
                            </button>
                        @endif
                    </div>
                    
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Payment Method *</label>
                        <select wire:model="paymentMethod" 
                                class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-900 focus:outline-none">
                            <option value="CASH">Cash</option>
                            <option value="BANK">Bank Deposit</option>
                            <option value="MOBILE">Mobile Money</option>
                          
                        </select>
                        @error('paymentMethod') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Dynamic Fields Based on Payment Method --}}
                @if($paymentMethod == 'BANK')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Bank Name *</label>
                            <select wire:model="bankName" 
                                    class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-900 focus:outline-none">
                                <option value="">Select Bank</option>
                                @foreach($banks as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('bankName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Reference Number *</label>
                            <input type="text" wire:model="referenceNumber" 
                                   class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-900 focus:outline-none"
                                   placeholder="Enter reference">
                            @error('referenceNumber') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @elseif($paymentMethod == 'MOBILE')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Mobile Provider *</label>
                            <select wire:model="mobileProvider" 
                                    class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-900 focus:outline-none">
                                <option value="">Select Provider</option>
                                @foreach($mnoProviders as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('mobileProvider') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Mobile Number *</label>
                            <input type="text" wire:model="mobileNumber" 
                                   class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-900 focus:outline-none"
                                   placeholder="0712345678">
                            @error('mobileNumber') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @elseif($paymentMethod == 'INTERNAL')
                    <div class="mt-3">
                        <label class="block text-xs text-gray-600 mb-1">Source Account *</label>
                        <input type="text" wire:model="sourceAccount" 
                               class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-900 focus:outline-none"
                               placeholder="Enter account number">
                        @error('sourceAccount') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div class="mt-3">
                    <label class="block text-xs text-gray-600 mb-1">Narration</label>
                    <textarea wire:model="narration" rows="2"
                              class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-900 focus:outline-none"
                              placeholder="Optional description"></textarea>
                </div>

                {{-- Allocation Preview --}}
                @if($showAllocationPreview && $allocationPreview)
                    <div class="mt-3 p-3 bg-blue-50 rounded">
                        <h4 class="text-xs font-semibold mb-2">Payment Allocation</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                            @if(($allocationPreview['penalties'] ?? 0) > 0)
                                <div>
                                    <span class="text-gray-600">Penalties:</span>
                                    <span class="font-semibold block">{{ number_format($allocationPreview['penalties'], 0) }}</span>
                                </div>
                            @endif
                            @if(($allocationPreview['interest'] ?? 0) > 0)
                                <div>
                                    <span class="text-gray-600">Interest:</span>
                                    <span class="font-semibold block">{{ number_format($allocationPreview['interest'], 0) }}</span>
                                </div>
                            @endif
                            @if(($allocationPreview['principal'] ?? 0) > 0)
                                <div>
                                    <span class="text-gray-600">Principal:</span>
                                    <span class="font-semibold block">{{ number_format($allocationPreview['principal'], 0) }}</span>
                                </div>
                            @endif
                            @if(($allocationPreview['overpayment'] ?? 0) > 0)
                                <div>
                                    <span class="text-gray-600">Overpayment:</span>
                                    <span class="font-semibold block">{{ number_format($allocationPreview['overpayment'], 0) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="mt-4 flex justify-between">
                    <button wire:click="previousStep" 
                            class="px-3 py-1.5 bg-gray-300 text-gray-700 text-sm rounded hover:bg-gray-400">
                        Back
                    </button>
                    
                    <button wire:click="nextStep" 
                            class="px-4 py-1.5 bg-blue-900 text-white text-sm rounded hover:bg-blue-800"
                            @if(!$paymentAmount) disabled @endif>
                        Continue
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Step 3: Confirmation --}}
    @if($currentStep == 3 && $selectedLoanId)
        <div class="bg-white rounded shadow-sm p-4">
            <h3 class="text-sm font-semibold text-blue-900 mb-3">Confirm Payment</h3>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-3">
                <p class="text-xs">Please review details before confirming.</p>
            </div>

            <div class="grid grid-cols-2 gap-3 p-3 bg-gray-50 rounded mb-3">
                <div>
                    <label class="text-xs text-gray-600">Loan ID</label>
                    <p class="text-sm font-semibold">{{ $loanDetails['loan_id'] ?? '' }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-600">Member</label>
                    <p class="text-sm font-semibold">{{ $memberDetails['name'] ?? '' }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-600">Amount</label>
                    <p class="text-lg font-bold text-blue-900">{{ number_format($paymentAmount ?? 0, 0) }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-600">Method</label>
                    <p class="text-sm font-semibold">{{ $paymentMethod }}</p>
                </div>
            </div>

            @if($allocationPreview)
                <div class="p-3 bg-blue-50 rounded mb-3">
                    <h4 class="text-xs font-semibold mb-2">Allocation:</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                        @if(($allocationPreview['penalties'] ?? 0) > 0)
                            <div>Penalties: <span class="font-semibold">{{ number_format($allocationPreview['penalties'], 0) }}</span></div>
                        @endif
                        @if(($allocationPreview['interest'] ?? 0) > 0)
                            <div>Interest: <span class="font-semibold">{{ number_format($allocationPreview['interest'], 0) }}</span></div>
                        @endif
                        @if(($allocationPreview['principal'] ?? 0) > 0)
                            <div>Principal: <span class="font-semibold">{{ number_format($allocationPreview['principal'], 0) }}</span></div>
                        @endif
                        @if(($allocationPreview['overpayment'] ?? 0) > 0)
                            <div>Overpayment: <span class="font-semibold">{{ number_format($allocationPreview['overpayment'], 0) }}</span></div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="flex justify-between">
                <button wire:click="previousStep" 
                        class="px-3 py-1.5 bg-gray-300 text-gray-700 text-sm rounded hover:bg-gray-400">
                    Back
                </button>
                
                <button wire:click="processRepayment" 
                        wire:loading.attr="disabled"
                        class="px-4 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="processRepayment">Confirm Payment</span>
                    <span wire:loading wire:target="processRepayment">Processing...</span>
                </button>
            </div>
        </div>
    @endif

    {{-- Step 4: Receipt --}}
    @if($currentStep == 4 && $receipt)
        <div class="bg-white rounded-lg shadow-sm p-6">
            {{-- Success Header --}}
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-3">
                    <i class="fas fa-check-circle text-3xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-bold text-green-600">Payment Successful!</h3>
                <p class="text-sm text-gray-600 mt-1">Your loan repayment has been processed successfully</p>
            </div>

            {{-- Receipt Display --}}
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div id="receipt-content" class="bg-white border-2 border-gray-300 rounded p-6 max-w-md mx-auto">
                    {{-- Header --}}
                    <div class="text-center border-b-2 border-gray-800 pb-3 mb-4">
                        <h1 class="text-base font-bold uppercase">SACCOS CORE SYSTEM</h1>
                        <p class="text-xs text-gray-600">Loan Payment Receipt</p>
                        <p class="text-xs text-gray-600">{{ $receipt['branch'] ?? 'Main Branch' }}</p>
                    </div>

                    {{-- Receipt Title --}}
                    <div class="text-center font-bold text-sm uppercase mb-3">
                        LOAN PAYMENT RECEIPT
                    </div>

                    {{-- Receipt Number --}}
                    <div class="text-center text-xs mb-4">
                        Receipt No: <span class="font-bold">{{ $receipt['receipt_number'] ?? '' }}</span>
                    </div>

                    {{-- Transaction Details --}}
                    <div class="space-y-2 text-xs mb-4">
                        <div class="flex justify-between border-b border-dotted border-gray-400 pb-1">
                            <span class="font-semibold">Date:</span>
                            <span>{{ $receipt['generated_at'] ?? '' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-dotted border-gray-400 pb-1">
                            <span class="font-semibold">Member:</span>
                            <span>{{ $receipt['member_name'] ?? '' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-dotted border-gray-400 pb-1">
                            <span class="font-semibold">Member No:</span>
                            <span>{{ $receipt['member_number'] ?? '' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-dotted border-gray-400 pb-1">
                            <span class="font-semibold">Loan ID:</span>
                            <span>{{ $receipt['loan_id'] ?? '' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-dotted border-gray-400 pb-1">
                            <span class="font-semibold">Product:</span>
                            <span>{{ $receipt['loan_product'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-dotted border-gray-400 pb-1">
                            <span class="font-semibold">Payment Method:</span>
                            <span>{{ $receipt['payment_method'] ?? '' }}</span>
                        </div>
                        
                        @if(($receipt['payment_method'] ?? '') == 'BANK')
                            <div class="flex justify-between border-b border-dotted border-gray-400 pb-1">
                                <span class="font-semibold">Bank:</span>
                                <span>{{ $receipt['bank_name'] ?? '' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-dotted border-gray-400 pb-1">
                                <span class="font-semibold">Reference:</span>
                                <span>{{ $receipt['reference_number'] ?? '' }}</span>
                            </div>
                        @elseif(($receipt['payment_method'] ?? '') == 'MOBILE')
                            <div class="flex justify-between border-b border-dotted border-gray-400 pb-1">
                                <span class="font-semibold">Provider:</span>
                                <span>{{ $receipt['mobile_provider'] ?? '' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-dotted border-gray-400 pb-1">
                                <span class="font-semibold">Mobile No:</span>
                                <span>{{ $receipt['mobile_number'] ?? '' }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Payment Amount Section --}}
                    <div class="bg-gray-100 p-3 rounded mb-4">
                        <div class="text-center">
                            <p class="text-xs font-semibold uppercase mb-1">Amount Paid</p>
                            <p class="text-2xl font-bold text-blue-900">TZS {{ number_format($receipt['amount_paid'] ?? 0, 0) }}</p>
                        </div>
                    </div>

                    {{-- Payment Breakdown --}}
                    @if($receipt['payment_breakdown'] ?? false)
                        <div class="mb-4">
                            <h4 class="text-xs font-semibold mb-2 uppercase">Payment Allocation:</h4>
                            <div class="space-y-1 text-xs">
                                @if(($receipt['payment_breakdown']['penalties'] ?? 0) > 0)
                                    <div class="flex justify-between border-b border-dotted border-gray-300 pb-1">
                                        <span>Penalties:</span>
                                        <span class="font-semibold">TZS {{ number_format($receipt['payment_breakdown']['penalties'], 0) }}</span>
                                    </div>
                                @endif
                                @if(($receipt['payment_breakdown']['interest'] ?? 0) > 0)
                                    <div class="flex justify-between border-b border-dotted border-gray-300 pb-1">
                                        <span>Interest:</span>
                                        <span class="font-semibold">TZS {{ number_format($receipt['payment_breakdown']['interest'], 0) }}</span>
                                    </div>
                                @endif
                                @if(($receipt['payment_breakdown']['principal'] ?? 0) > 0)
                                    <div class="flex justify-between border-b border-dotted border-gray-300 pb-1">
                                        <span>Principal:</span>
                                        <span class="font-semibold">TZS {{ number_format($receipt['payment_breakdown']['principal'], 0) }}</span>
                                    </div>
                                @endif
                                @if(($receipt['payment_breakdown']['overpayment'] ?? 0) > 0)
                                    <div class="flex justify-between border-b border-dotted border-gray-300 pb-1">
                                        <span>Overpayment:</span>
                                        <span class="font-semibold">TZS {{ number_format($receipt['payment_breakdown']['overpayment'], 0) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Outstanding Balance --}}
                    <div class="border-t-2 border-gray-800 pt-3 mb-4">
                        <div class="flex justify-between text-xs font-semibold">
                            <span>Outstanding Balance:</span>
                            <span class="text-red-600">TZS {{ number_format($receipt['outstanding_balance'] ?? 0, 0) }}</span>
                        </div>
                    </div>

                    {{-- Barcode --}}
                    <div class="text-center font-mono text-xs mb-4">
                        *{{ $receipt['receipt_number'] ?? '' }}*
                    </div>

                    {{-- Signature Line --}}
                    <div class="text-xs mb-4">
                        Processed by: <span class="font-semibold">{{ $receipt['processed_by'] ?? '' }}</span>
                    </div>

                    {{-- Footer --}}
                    <div class="text-center text-xs text-gray-600 border-t pt-3">
                        <p class="font-semibold">Thank you for your payment!</p>
                        <p>This is a computer generated receipt</p>
                        <p>For queries, contact your branch office</p>
                        <p class="text-xs mt-1">Generated on: {{ $receipt['generated_at'] ?? '' }}</p>
                    </div>
                </div>
            </div>

            {{-- Information Box --}}
            <div class="bg-blue-50 p-4 rounded-lg mb-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    Your receipt has been generated. You can print it or save it for your records.
                </p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-center space-x-3">
                <button onclick="printLoanReceipt()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-900 text-white text-sm font-medium rounded-md hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-print mr-2"></i>
                    Print Receipt
                </button>
                
                <button wire:click="newPayment" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <i class="fas fa-plus mr-2"></i>
                    New Payment
                </button>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    window.addEventListener('print-receipt', event => {
        const printContent = document.getElementById('receipt-content').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        location.reload();
    });
</script>
@endpush

<style nonce="{{ csp_nonce() }}">
@media print {
    body * {
        visibility: hidden;
    }
    #receipt-content, #receipt-content * {
        visibility: visible;
    }
    #receipt-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>