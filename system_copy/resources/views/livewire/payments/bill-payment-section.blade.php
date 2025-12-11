{{-- NBC BILLS PAYMENT SECTION --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200" wire:poll.60s="$refresh">
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">NBC Bill Payment</h3>
                <p class="text-sm text-gray-600">Pay your utility bills and services through NBC Bills Payments Engine</p>
            </div>
            @if($selectedSpCode || $billDetails || $paymentResponse)
                <button wire:click="resetBillPayment" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Start New Payment
                </button>
            @endif
        </div>
    </div>

    {{-- Alerts --}}
    <div class="px-6 pt-4">
        @if($errorMessage)
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg animate-fade-in">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-red-700 text-sm">{{ $errorMessage }}</span>
                </div>
            </div>
        @endif

        @if($successMessage)
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg animate-fade-in">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-green-700 text-sm">{{ $successMessage }}</span>
                </div>
            </div>
        @endif
    </div>

    {{-- Content Area --}}
    <div class="p-6">
        @if(!$selectedSpCode)
            {{-- STEP 1: BILLER SELECTION --}}
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h4 class="text-md font-medium text-gray-900">Step 1: Select Service Provider</h4>
                    <button wire:click="fetchBillers" class="text-sm text-gray-500 hover:text-gray-700">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>

                @if(count($billersGrouped) > 0)
                    {{-- Category Tabs --}}
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Categories">
                            <button wire:click="$set('selectedCategory', null)"
                                    class="{{ !$selectedCategory ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                                All Billers
                            </button>
                            @foreach($billersGrouped as $category => $categoryBillers)
                                <button wire:click="$set('selectedCategory', '{{ $category }}')"
                                        class="{{ $selectedCategory === $category ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm capitalize">
                                    {{ str_replace('_', ' ', $category) }} ({{ count($categoryBillers) }})
                                </button>
                            @endforeach
                        </nav>
                    </div>

                    {{-- Billers Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @php
                            $displayBillers = $selectedCategory 
                                ? ($billersGrouped[$selectedCategory] ?? [])
                                : $billers;
                        @endphp
                        
                        @forelse($displayBillers as $biller)
                            <button wire:click="selectBiller('{{ $biller['spCode'] }}')"
                                    wire:loading.attr="disabled"
                                    class="p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all duration-200 text-left group disabled:opacity-50 disabled:cursor-not-allowed">
                                <div class="flex items-start space-x-3">
                                    @if(isset($biller['spIcon']) && $biller['spIcon'])
                                        <img src="data:image/png;base64,{{ $biller['spIcon'] }}" alt="{{ $biller['shortName'] }}" class="w-10 h-10 rounded-lg object-cover">
                                    @else
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center text-white font-bold">
                                            {{ substr($biller['shortName'] ?? $biller['spCode'], 0, 2) }}
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900 group-hover:text-blue-600">
                                            {{ $biller['shortName'] ?? $biller['spCode'] }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            {{ $biller['fullName'] ?? '' }}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1">
                                            Code: {{ $biller['spCode'] }}
                                        </div>
                                    </div>
                                </div>
                            </button>
                        @empty
                            <div class="col-span-full text-center py-8 text-gray-500">
                                No billers found in this category
                            </div>
                        @endforelse
                    </div>
                @else
                    {{-- No Billers Available --}}
                    <div class="text-center py-12">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">No Service Providers Available</h3>
                        <p class="text-sm text-gray-500 mb-4">Unable to load billers at this time.</p>
                        <button wire:click="fetchBillers" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                            <span wire:loading.remove wire:target="fetchBillers">Try Again</span>
                            <span wire:loading wire:target="fetchBillers">Loading...</span>
                        </button>
                    </div>
                @endif
            </div>

        @elseif(!$billDetails)
            {{-- STEP 2: BILL INQUIRY --}}
            <div class="space-y-6">
                {{-- Progress Indicator --}}
                <div class="flex items-center space-x-2">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">✓</div>
                        <span class="ml-2 text-sm text-gray-600">Biller Selected</span>
                    </div>
                    <div class="flex-1 h-0.5 bg-gray-200"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">2</div>
                        <span class="ml-2 text-sm font-medium text-gray-900">Bill Inquiry</span>
                    </div>
                    <div class="flex-1 h-0.5 bg-gray-200"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-white rounded-full flex items-center justify-center text-sm font-medium">3</div>
                        <span class="ml-2 text-sm text-gray-500">Payment</span>
                    </div>
                </div>

                {{-- Selected Biller Card --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <span class="text-blue-900 font-medium">
                                    {{ $selectedBiller['shortName'] ?? $selectedSpCode }}
                                </span>
                                <span class="text-blue-700 text-sm ml-2">
                                    ({{ $selectedBiller['fullName'] ?? '' }})
                                </span>
                            </div>
                        </div>
                        <button wire:click="$set('selectedSpCode', null)" 
                                class="text-sm text-blue-600 hover:text-blue-700">
                            Change
                        </button>
                    </div>
                </div>

                {{-- Inquiry Form --}}
                <form wire:submit.prevent="inquireBill" class="space-y-4">
                    <div>
                        <label for="billRef" class="block text-sm font-medium text-gray-700 mb-2">
                            Bill Reference Number
                        </label>
                        <input type="text" 
                               wire:model.defer="billRef" 
                               id="billRef"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter your bill reference number"
                               required>
                        @error('billRef') 
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-between">
                        <button type="button"
                                wire:click="$set('selectedSpCode', null)"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            ← Back to Billers
                        </button>
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-900 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="inquireBill">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Inquire Bill
                            </span>
                            <span wire:loading wire:target="inquireBill" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Inquiring...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

        @elseif(!$paymentResponse)
            {{-- STEP 3: PAYMENT CONFIRMATION --}}
            <div class="space-y-6">
                {{-- Progress Indicator --}}
                <div class="flex items-center space-x-2">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">✓</div>
                        <span class="ml-2 text-sm text-gray-600">Biller Selected</span>
                    </div>
                    <div class="flex-1 h-0.5 bg-gray-200"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">✓</div>
                        <span class="ml-2 text-sm text-gray-600">Bill Inquiry</span>
                    </div>
                    <div class="flex-1 h-0.5 bg-gray-200"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">3</div>
                        <span class="ml-2 text-sm font-medium text-gray-900">Payment</span>
                    </div>
                </div>

                {{-- Bill Details Card --}}
                <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <svg class="w-6 h-6 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <h5 class="text-lg font-medium text-gray-900">Bill Details Retrieved</h5>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white rounded-lg p-3">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Service Provider</p>
                            <p class="font-medium text-gray-900 mt-1">{{ $selectedBiller['shortName'] ?? $selectedSpCode }}</p>
                        </div>
                        
                        <div class="bg-white rounded-lg p-3">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Bill Reference</p>
                            <p class="font-medium text-gray-900 mt-1">{{ $billRef }}</p>
                        </div>
                        
                        @if(isset($billDetails['billedName']))
                            <div class="bg-white rounded-lg p-3">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Customer Name</p>
                                <p class="font-medium text-gray-900 mt-1">{{ $billDetails['billedName'] }}</p>
                            </div>
                        @endif
                        
                        @if(isset($billDetails['serviceName']))
                            <div class="bg-white rounded-lg p-3">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Service</p>
                                <p class="font-medium text-gray-900 mt-1">{{ $billDetails['serviceName'] }}</p>
                            </div>
                        @endif
                        
                        @if(isset($billDetails['totalAmount']))
                            <div class="bg-white rounded-lg p-3">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Total Amount</p>
                                <p class="font-medium text-gray-900 mt-1">{{ number_format($billDetails['totalAmount'], 2) }} {{ $billDetails['currency'] ?? 'TZS' }}</p>
                            </div>
                        @endif
                        
                        @if(isset($billDetails['balance']))
                            <div class="bg-white rounded-lg p-3">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Balance Due</p>
                                <p class="font-medium text-gray-900 mt-1">{{ number_format($billDetails['balance'], 2) }} {{ $billDetails['currency'] ?? 'TZS' }}</p>
                            </div>
                        @endif
                        
                        @if(isset($billDetails['expiryDate']))
                            <div class="bg-white rounded-lg p-3">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Expiry Date</p>
                                <p class="font-medium text-gray-900 mt-1">{{ \Carbon\Carbon::parse($billDetails['expiryDate'])->format('d M Y H:i') }}</p>
                            </div>
                        @endif
                        
                        @if(isset($billDetails['paymentMode']))
                            <div class="bg-white rounded-lg p-3">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Payment Mode</p>
                                <p class="font-medium text-gray-900 mt-1 capitalize">{{ $billDetails['paymentMode'] }}</p>
                            </div>
                        @endif
                    </div>
                    
                    @if(isset($billDetails['description']))
                        <div class="bg-white rounded-lg p-3 mt-4">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Description</p>
                            <p class="text-gray-700 mt-1">{{ $billDetails['description'] }}</p>
                        </div>
                    @endif
                </div>

                {{-- Payment Form --}}
                <form wire:submit.prevent="makePayment" class="space-y-6">
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h5 class="text-md font-medium text-gray-900 mb-4">Payment Information</h5>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Amount Field --}}
                            <div class="md:col-span-2">
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                    Payment Amount (TZS)
                                    @if($paymentMode === 'exact')
                                        <span class="text-xs text-gray-500 ml-1">(Exact amount required)</span>
                                    @elseif($paymentMode === 'full')
                                        <span class="text-xs text-gray-500 ml-1">(Minimum: {{ number_format($billDetails['balance'] ?? 0, 2) }})</span>
                                    @elseif($paymentMode === 'partial')
                                        <span class="text-xs text-gray-500 ml-1">(Partial payment allowed)</span>
                                    @endif
                                </label>
                                <input type="number" 
                                       wire:model.defer="amount" 
                                       id="amount"
                                       step="0.01"
                                       min="100"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Enter amount to pay"
                                       {{ $paymentMode === 'exact' ? 'readonly' : '' }}
                                       required>
                                @error('amount') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Payer Name --}}
                            <div>
                                <label for="payerName" class="block text-sm font-medium text-gray-700 mb-2">
                                    Payer Name
                                </label>
                                <input type="text" 
                                       wire:model.defer="payerName" 
                                       id="payerName"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Enter your name"
                                       required>
                                @error('payerName') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Payer Phone --}}
                            <div>
                                <label for="payerPhone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <input type="tel" 
                                       wire:model.defer="payerPhone" 
                                       id="payerPhone"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="255XXXXXXXXX"
                                       required>
                                @error('payerPhone') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Payer Email --}}
                            <div>
                                <label for="payerEmail" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address
                                </label>
                                <input type="email" 
                                       wire:model.defer="payerEmail" 
                                       id="payerEmail"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="your@email.com"
                                       required>
                                @error('payerEmail') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Narration --}}
                            <div>
                                <label for="narration" class="block text-sm font-medium text-gray-700 mb-2">
                                    Payment Description (Optional)
                                </label>
                                <input type="text" 
                                       wire:model.defer="narration" 
                                       id="narration"
                                       maxlength="50"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Payment for...">
                                @error('narration') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button type="button"
                                wire:click="$set('billDetails', null)"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            ← Back to Inquiry
                        </button>
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="makePayment">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Confirm Payment
                            </span>
                            <span wire:loading wire:target="makePayment" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

        @else
            {{-- STEP 4: PAYMENT STATUS --}}
            <div class="space-y-6">
                @if($paymentResponse['status'] === 'processing')
                    {{-- Processing State --}}
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                            <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Processing Payment</h3>
                        <p class="text-gray-600 mb-4">Your payment is being processed. Please wait...</p>
                        <div class="space-y-2">
                            @if($gatewayRef)
                                <p class="text-sm text-gray-500">Gateway Reference: <span class="font-mono">{{ $gatewayRef }}</span></p>
                            @endif
                            @if($channelRef)
                                <p class="text-sm text-gray-500">Transaction Reference: <span class="font-mono">{{ $channelRef }}</span></p>
                            @endif
                        </div>
                        <button wire:click="checkPaymentStatus" 
                                wire:loading.attr="disabled"
                                class="mt-4 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                            <span wire:loading.remove wire:target="checkPaymentStatus">Check Status</span>
                            <span wire:loading wire:target="checkPaymentStatus">Checking...</span>
                        </button>
                    </div>
                @elseif($paymentResponse['status'] === 'completed')
                    {{-- Success State --}}
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                            <svg class="h-8 w-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Payment Successful!</h3>
                        <p class="text-gray-600 mb-6">Your bill payment has been completed successfully.</p>
                        
                        @if($paymentStatus && isset($paymentStatus['paymentDetails']))
                            <div class="bg-gray-50 rounded-lg p-6 text-left max-w-md mx-auto">
                                <h4 class="font-medium text-gray-900 mb-3">Transaction Details</h4>
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500">Amount:</dt>
                                        <dd class="text-sm font-medium text-gray-900">{{ number_format($amount, 2) }} TZS</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500">Bill Reference:</dt>
                                        <dd class="text-sm font-medium text-gray-900">{{ $billRef }}</dd>
                                    </div>
                                    @if(isset($paymentStatus['paymentDetails']['gatewayRef']))
                                        <div class="flex justify-between">
                                            <dt class="text-sm text-gray-500">Receipt No:</dt>
                                            <dd class="text-sm font-medium text-gray-900">{{ $paymentStatus['paymentDetails']['gatewayRef'] }}</dd>
                                        </div>
                                    @endif
                                    @if(isset($paymentStatus['paymentDetails']['billerReceipt']))
                                        <div class="flex justify-between">
                                            <dt class="text-sm text-gray-500">Biller Receipt:</dt>
                                            <dd class="text-sm font-medium text-gray-900">{{ $paymentStatus['paymentDetails']['billerReceipt'] }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                        
                        <div class="mt-6 flex justify-center space-x-3">
                            <button wire:click="resetBillPayment" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-900 hover:bg-blue-700">
                                New Payment
                            </button>
                            <button onclick="window.print()" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Print Receipt
                            </button>
                        </div>
                    </div>
                @else
                    {{-- Error State --}}
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                            <svg class="h-8 w-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Payment Failed</h3>
                        <p class="text-gray-600 mb-4">{{ $paymentResponse['message'] ?? 'An error occurred during payment processing.' }}</p>
                        <button wire:click="$set('paymentResponse', null)" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-900 hover:bg-blue-700">
                            Try Again
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Recent Transactions --}}
    @if(count($transactions) > 0)
        <div class="border-t border-gray-200 px-6 py-4">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Transactions</h4>
            <div class="space-y-2">
                @foreach(array_slice($transactions, -3) as $transaction)
                    <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                @if(($transaction['status'] ?? '') === 'completed')
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                @elseif(($transaction['status'] ?? '') === 'processing')
                                    <svg class="animate-spin w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $transaction['billRef'] }}</p>
                                <p class="text-xs text-gray-500">{{ $transaction['timestamp'] ?? '' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ number_format($transaction['amount'], 2) }} TZS</p>
                            <p class="text-xs text-gray-500">{{ $transaction['gatewayRef'] ?? 'Pending' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- JavaScript for auto-checking payment status --}}
@push('scripts')
<script nonce="{{ csp_nonce() }}">
    document.addEventListener('livewire:load', function () {
        Livewire.on('check-payment-status', function(data) {
            setTimeout(function() {
                @this.checkPaymentStatus(data.channelRef);
            }, data.delay || 3000);
        });
    });
</script>
@endpush

<style nonce="{{ csp_nonce() }}">
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in {
        animation: fade-in 0.3s ease-out;
    }
</style>