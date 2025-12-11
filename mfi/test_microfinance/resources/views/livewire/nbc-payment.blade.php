<div class="min-h-screen bg-gradient-to-br from-red-50 to-indigo-100">
    <!-- Mobile Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <img class="h-8 w-auto" src="{{ asset('images/logo.png') }}" alt="MFI Management System" onerror="this.style.display='none'">
                    </div>
                    <div class="ml-3">
                        <h1 class="text-lg font-bold text-gray-900">MFI Management System</h1>
                        <p class="text-xs text-gray-600">Payment Portal</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-600">Client</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $clientNumber }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-md mx-auto px-4 py-6">
        <!-- Client Information Card -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-gray-900 truncate">
                        {{ $client->full_name ?? $client->first_name . ' ' . $client->last_name }}
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        <i class="fas fa-phone mr-2"></i>
                        {{ $client->phone_number ?? $client->mobile_phone_number ?? 'No phone' }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-envelope mr-2"></i>
                        {{ $client->email ?? 'No email' }}
                    </p>
                </div>
                <div class="ml-4">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>Active
                    </span>
                </div>
            </div>
        </div>

        <!-- Bills Section -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Pending Bills</h3>
                <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ $pendingBills->count() }} bills
                </span>
            </div>

            @if($pendingBills->count() > 0)
                <div class="space-y-3">
                    @foreach($pendingBills as $bill)
                        @php
                            $serviceName = $bill->service->name ?? 'Service';
                            $isCustomAmount = $this->isCustomAmountAllowed($serviceName);
                        @endphp
                        <div class="border border-gray-200 rounded-lg p-3 transition-all duration-300 hover:shadow-md">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3 flex-1">
                                    <input type="checkbox" 
                                           wire:model="selectedBills" 
                                           value="{{ $bill->id }}"
                                           class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 focus:ring-2">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-gray-900 text-sm truncate">
                                            {{ $serviceName }}
                                        </h4>
                                        <p class="text-xs text-gray-600">Control: {{ $bill->control_number }}</p>
                                        <p class="text-xs text-gray-600">Due: {{ \Carbon\Carbon::parse($bill->due_date)->format('M j, Y') }}</p>
                                        @if($isCustomAmount)
                                            <p class="text-xs text-red-600 font-medium mt-1">
                                                <i class="fas fa-edit mr-1"></i>Custom amount allowed
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right ml-3">
                                    @if($isCustomAmount)
                                        <div class="mb-2">
                                            <input type="number" 
                                                   wire:model="customAmounts.{{ $bill->id }}"
                                                   placeholder="{{ number_format($bill->amount_due, 0) }}"
                                                   class="w-20 px-2 py-1 text-xs border border-gray-300 rounded focus:border-red-500 focus:ring-1 focus:ring-red-500"
                                                   min="1000">
                                        </div>
                                    @endif
                                    <p class="text-sm font-semibold text-gray-900">
                                        TZS {{ $this->getBillAmount($bill->id, $bill->amount_due) }}
                                    </p>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Select All -->
                <div class="mt-4 pt-3 border-t border-gray-200">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" 
                               wire:click="toggleSelectAll"
                               @if(count($selectedBills) === $pendingBills->count()) checked @endif
                               class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 focus:ring-2">
                        <span class="text-sm font-medium text-gray-700">Select All Bills</span>
                    </label>
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-3xl text-green-500 mb-3"></i>
                    <h4 class="text-base font-medium text-gray-900 mb-2">No Pending Bills</h4>
                    <p class="text-sm text-gray-600">You have no outstanding bills to pay.</p>
                </div>
            @endif
        </div>

        <!-- Payment Form -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Details</h3>

            <!-- Selected Bills Summary -->
            @if(count($selectedBills) > 0)
                <div class="mb-4">
                    <div class="bg-red-50 rounded-lg p-3">
                        <h4 class="font-medium text-red-900 mb-2 text-sm">Selected Bills</h4>
                        <p class="text-xs text-red-700 mb-2">
                            {{ count($selectedBills) }} bill(s) selected
                        </p>
                        <p class="text-base font-semibold text-red-900">
                            Total: TZS {{ number_format($totalAmount, 0) }}
                        </p>
                    </div>
                </div>
            @endif

            <!-- Payment Form -->
            @if(count($selectedBills) > 0 || $totalAmount > 0)
                <form wire:submit.prevent="processPayment">
                    <!-- Phone Number -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" 
                               wire:model="phoneNumber"
                               placeholder="e.g., 0755123456"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-base @error('phoneNumber') border-red-500 @enderror"
                               required>
                        @error('phoneNumber')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Enter the phone number registered with your mobile money account</p>
                    </div>

                    <!-- MNO Provider -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Mobile Money Provider <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="mnoProvider"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-base @error('mnoProvider') border-red-500 @enderror"
                                required>
                            <option value="">Select provider</option>
                            <option value="MPESA">M-Pesa</option>
                            <option value="AIRTEL">Airtel Money</option>
                            <option value="TIGOPESA">Tigo Pesa</option>
                            <option value="HALOPESA">Halo Pesa</option>
                        </select>
                        @error('mnoProvider')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Button -->
                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center text-base">
                        <div wire:loading wire:target="processPayment" class="mr-2">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                        </div>
                        <i class="fas fa-mobile-alt mr-2"></i>
                        Pay TZS {{ number_format($totalAmount, 0) }}
                    </button>
                </form>
            @else
                <!-- No Bills Selected -->
                <div class="text-center py-6">
                    <i class="fas fa-hand-pointer text-3xl text-gray-400 mb-3"></i>
                    <p class="text-sm text-gray-600">Select bills to proceed with payment</p>
                </div>
            @endif
        </div>

        <!-- Payment Instructions -->
        <div class="bg-white rounded-lg shadow-md p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Instructions</h3>
            <div class="space-y-4">
                <div class="flex items-start space-x-3">
                    <div class="bg-red-100 rounded-full w-8 h-8 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-mobile-alt text-red-600 text-sm"></i>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 text-sm">1. Select Bills</h4>
                        <p class="text-xs text-gray-600">Choose the bills you want to pay from the list above</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="bg-green-100 rounded-full w-8 h-8 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-credit-card text-green-600 text-sm"></i>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 text-sm">2. Enter Details</h4>
                        <p class="text-xs text-gray-600">Provide your phone number and select your mobile money provider</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="bg-purple-100 rounded-full w-8 h-8 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-check-circle text-purple-600 text-sm"></i>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 text-sm">3. Authorize Payment</h4>
                        <p class="text-xs text-gray-600">You'll receive a push notification to authorize the payment</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    @if($showSuccessModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-sm shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <i class="fas fa-check text-green-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Payment Successful!</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500 mb-4">
                            Your payment has been processed successfully.
                        </p>
                        <div class="bg-gray-50 rounded-lg p-4 text-left">
                            <p class="text-sm text-gray-600"><strong>Transaction Reference:</strong></p>
                            <p class="text-sm font-mono text-gray-900">{{ $transactionReference }}</p>
                            <p class="text-sm text-gray-600 mt-2"><strong>Amount Paid:</strong></p>
                            <p class="text-sm font-semibold text-gray-900">TZS {{ number_format($processedAmount, 0) }}</p>
                        </div>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button wire:click="closeSuccessModal"
                                class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Error Modal -->
    @if($showErrorModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-sm shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Payment Failed</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500 mb-4">{{ $errorMessage }}</p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button wire:click="closeErrorModal"
                                class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Try Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if(session()->has('message'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif
</div>
