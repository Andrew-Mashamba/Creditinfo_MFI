<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MFI Management System') }} - Payment Portal</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/flowbite.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style nonce="{{ csp_nonce() }}">
        [x-cloak] {
            display: none !important;
        }
        .payment-card {
            transition: all 0.3s ease;
        }
        .payment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .mobile-optimized {
            max-width: 100%;
            margin: 0 auto;
        }
        @media (min-width: 768px) {
            .mobile-optimized {
                max-width: 480px;
            }
        }
        .custom-amount-input {
            transition: all 0.3s ease;
        }
        .custom-amount-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>
</head>

<body class="h-full font-sans antialiased">
    <div x-data="paymentApp()" class="min-h-screen bg-gradient-to-br from-red-50 to-indigo-100">
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
        <div class="mobile-optimized px-4 py-6">
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
                                $isCustomAmount = in_array(strtolower($serviceName), ['savings deposit', 'member deposits', 'fixed deposit', 'loan payment', 'loan repayment']);
                            @endphp
                            <div class="payment-card border border-gray-200 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3 flex-1">
                                        <input type="checkbox" 
                                               x-model="selectedBills" 
                                               value="{{ $bill->id }}"
                                               @change="updateTotalAmount()"
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
                                                       x-model="customAmounts[{{ $bill->id }}]"
                                                       @change="updateTotalAmount()"
                                                       placeholder="{{ number_format($bill->amount_due, 0) }}"
                                                       class="w-20 px-2 py-1 text-xs border border-gray-300 rounded custom-amount-input"
                                                       min="1000">
                                            </div>
                                        @endif
                                        <p class="text-sm font-semibold text-gray-900">
                                            TZS <span x-text="getBillAmount({{ $bill->id }}, {{ $bill->amount_due }})"></span>
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
                                   x-model="selectAll"
                                   @change="toggleSelectAll()"
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
                <div x-show="selectedBills.length > 0" class="mb-4">
                    <div class="bg-red-50 rounded-lg p-3">
                        <h4 class="font-medium text-red-900 mb-2 text-sm">Selected Bills</h4>
                        <p class="text-xs text-red-700 mb-2">
                            <span x-text="selectedBills.length"></span> bill(s) selected
                        </p>
                        <p class="text-base font-semibold text-red-900">
                            Total: TZS <span x-text="formatCurrency(totalAmount)"></span>
                        </p>
                    </div>
                </div>

                <!-- Payment Form -->
                <form x-show="selectedBills.length > 0" @submit.prevent="processPayment()">
                    <!-- Phone Number -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" 
                               x-model="phoneNumber"
                               placeholder="e.g., 0755123456"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-base"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Enter the phone number registered with your mobile money account</p>
                    </div>

                    <!-- MNO Provider -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Mobile Money Provider <span class="text-red-500">*</span>
                        </label>
                        <select x-model="mnoProvider"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-base"
                                required>
                            <option value="">Select provider</option>
                            <option value="MPESA">M-Pesa</option>
                            <option value="AIRTEL">Airtel Money</option>
                            <option value="TIGOPESA">Tigo Pesa</option>
                            <option value="HALOPESA">Halo Pesa</option>
                        </select>
                    </div>

                    <!-- Payment Button -->
                    <button type="submit"
                            x-show="!isProcessing"
                            :disabled="selectedBills.length === 0"
                            class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center text-base">
                        <i class="fas fa-mobile-alt mr-2"></i>
                        Pay TZS <span x-text="formatCurrency(totalAmount)"></span>
                    </button>

                    <!-- Processing State -->
                    <div x-show="isProcessing" class="w-full bg-red-600 text-white font-medium py-3 px-4 rounded-lg flex items-center justify-center text-base">
                        <div class="loading-spinner mr-3"></div>
                        Processing Payment...
                    </div>
                </form>

                <!-- No Bills Selected -->
                <div x-show="selectedBills.length === 0" class="text-center py-6">
                    <i class="fas fa-hand-pointer text-3xl text-gray-400 mb-3"></i>
                    <p class="text-sm text-gray-600">Select bills to proceed with payment</p>
                </div>
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
        <div x-show="showSuccessModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
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
                            <p class="text-sm font-mono text-gray-900" x-text="transactionReference"></p>
                            <p class="text-sm text-gray-600 mt-2"><strong>Amount Paid:</strong></p>
                            <p class="text-sm font-semibold text-gray-900">TZS <span x-text="formatCurrency(totalAmount)"></span></p>
                        </div>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button @click="closeSuccessModal()"
                                class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Modal -->
        <div x-show="showErrorModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-sm shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Payment Failed</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500 mb-4" x-text="errorMessage"></p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button @click="closeErrorModal()"
                                class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Try Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script nonce="{{ csp_nonce() }}">
        function paymentApp() {
            return {
                selectedBills: [],
                selectAll: false,
                phoneNumber: '{{ $client->phone_number ?? $client->mobile_phone_number ?? "" }}',
                mnoProvider: '',
                isProcessing: false,
                showSuccessModal: false,
                showErrorModal: false,
                errorMessage: '',
                transactionReference: '',
                totalAmount: 0,
                savingsAmount: '',
                depositAmount: '',
                loanAmount: '',
                customAmounts: {},

                init() {
                    this.updateTotalAmount();
                },

                toggleSelectAll() {
                    if (this.selectAll) {
                        this.selectedBills = {!! json_encode($pendingBills->pluck('id')) !!};
                    } else {
                        this.selectedBills = [];
                    }
                    this.updateTotalAmount();
                },

                getBillAmount(billId, defaultAmount) {
                    const customAmount = this.customAmounts[billId];
                    if (customAmount && customAmount > 0) {
                        return this.formatCurrency(customAmount);
                    }
                    return this.formatCurrency(defaultAmount);
                },

                updateTotalAmount() {
                    const bills = {!! json_encode($pendingBills) !!};
                    this.totalAmount = this.selectedBills.reduce((total, billId) => {
                        const bill = bills.find(b => b.id === billId);
                        if (!bill) return total;
                        
                        // Check if this bill has a custom amount
                        const customAmount = this.customAmounts[billId];
                        if (customAmount && customAmount > 0) {
                            return total + parseFloat(customAmount);
                        }
                        
                        return total + parseFloat(bill.amount_due);
                    }, 0);
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('en-TZ').format(amount);
                },

                addCustomService(type) {
                    let amount = 0;
                    let serviceName = '';
                    
                    switch(type) {
                        case 'savings':
                            amount = parseFloat(this.savingsAmount) || 0;
                            serviceName = 'Savings Deposit';
                            break;
                        case 'deposit':
                            amount = parseFloat(this.depositAmount) || 0;
                            serviceName = 'Fixed Deposit';
                            break;
                        case 'loan':
                            amount = parseFloat(this.loanAmount) || 0;
                            serviceName = 'Loan Payment';
                            break;
                    }

                    if (amount < 1000) {
                        this.showError('Minimum amount is TZS 1,000');
                        return;
                    }

                    // Add to total amount
                    this.totalAmount += amount;
                    
                    // Clear the input
                    switch(type) {
                        case 'savings':
                            this.savingsAmount = '';
                            break;
                        case 'deposit':
                            this.depositAmount = '';
                            break;
                        case 'loan':
                            this.loanAmount = '';
                            break;
                    }

                    // Show success message
                    alert(`${serviceName} of TZS ${this.formatCurrency(amount)} added to payment`);
                },

                async processPayment() {
                    console.log('Selected bills:', this.selectedBills);
                    console.log('Total amount:', this.totalAmount);
                    console.log('Custom amounts:', this.customAmounts);
                    
                    if (this.selectedBills.length === 0 && this.totalAmount === 0) {
                        this.showError('Please select bills or add custom services to pay.');
                        return;
                    }

                    if (!this.phoneNumber) {
                        this.showError('Please enter your phone number.');
                        return;
                    }

                    if (!this.mnoProvider) {
                        this.showError('Please select your mobile money provider.');
                        return;
                    }

                    this.isProcessing = true;

                    try {
                        const requestBody = {
                            bill_ids: this.selectedBills,
                            phone_number: this.phoneNumber,
                            mno_provider: this.mnoProvider,
                            custom_amounts: this.customAmounts,
                            custom_amount: this.totalAmount - this.selectedBills.reduce((total, billId) => {
                                const bills = {!! json_encode($pendingBills) !!};
                                const bill = bills.find(b => b.id === billId);
                                if (!bill) return total;
                                
                                const customAmount = this.customAmounts[billId];
                                if (customAmount && customAmount > 0) {
                                    return total + parseFloat(customAmount);
                                }
                                return total + parseFloat(bill.amount_due);
                            }, 0)
                        };
                        
                        console.log('Request body:', requestBody);
                        
                        const response = await fetch('/NBC/process-payment', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(requestBody)
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.transactionReference = result.transaction_reference;
                            this.showSuccessModal = true;
                            // Clear selected bills after successful payment
                            this.selectedBills = [];
                            this.selectAll = false;
                            this.customAmounts = {};
                            this.updateTotalAmount();
                        } else {
                            this.showError(result.message || 'Payment failed. Please try again.');
                        }
                    } catch (error) {
                        console.error('Payment error:', error);
                        this.showError('An error occurred while processing your payment. Please try again.');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                showError(message) {
                    this.errorMessage = message;
                    this.showErrorModal = true;
                },

                closeSuccessModal() {
                    this.showSuccessModal = false;
                },

                closeErrorModal() {
                    this.showErrorModal = false;
                }
            }
        }

        // Watch for changes in selectedBills
        document.addEventListener('alpine:init', () => {
            Alpine.data('paymentApp', paymentApp);
        });
    </script>
</body>
</html> 