<div class="min-h-screen bg-gradient-to-br from-slate-50 to-red-50">
    <!-- Include Error Handler Component -->
    @include('livewire.payments.error-handler')
    
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-red-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a5 5 0 00-10 0v2a2 2 0 00-2 2v7a2 2 0 002 2h10a2 2 0 002-2v-7a2 2 0 00-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Payments Manager</h1>
                        <p class="text-gray-600 mt-1">Manage, track, and analyze all payment activities</p>
                    </div>
                </div>
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Paid</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format(DB::table('payments')->where('status', 'Confirmed')->sum('amount'), 2) }} TZS</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Pending</p>
                                <p class="text-lg font-semibold text-gray-900">{{ DB::table('payments')->where('status', 'Pending')->count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Channels</p>
                                <p class="text-lg font-semibold text-gray-900">{{ DB::table('payments')->distinct('payment_channel')->count('payment_channel') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-6">
            <!-- Sidebar Navigation -->
            <div class="w-80 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model.debounce.300ms="search" 
                            placeholder="Search payments, billers, or accounts..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"
                            aria-label="Search payments"
                        />
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Navigation</h3>
                    @php
                        $sections = [
                            ['id' => 1, 'label' => 'Dashboard', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'description' => 'Overview and analytics'],
                            ['id' => 2, 'label' => 'New Payment', 'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6', 'description' => 'Initiate new payment'],
                            ['id' => 3, 'label' => 'Payment List', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'description' => 'View and manage all payment records'],
                            ['id' => 4, 'label' => 'Pending', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'description' => 'Pending payments'],
                            ['id' => 5, 'label' => 'Channels', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'description' => 'Payment channels'],
                            ['id' => 6, 'label' => 'Reports', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'description' => 'Generate reports'],
                        ];
                        $selectedMenuItem = $selectedMenuItem ?? 1;
                    @endphp
                    <nav class="space-y-2">
                        @foreach ($sections as $section)
                            @php
                                $isActive = $selectedMenuItem == $section['id'];
                                
                                // Check permissions for each section
                                $showSection = true;
                                if ($section['id'] == 1) {
                                    // Dashboard - requires view permission
                                    $showSection = $permissions['canView'] ?? false;
                                }
                                if ($section['id'] == 2) {
                                    // New Payment - requires create permission
                                    $showSection = $permissions['canCreate'] ?? false;
                                }
                                if ($section['id'] == 3) {
                                    // Payment List - requires view permission
                                    $showSection = $permissions['canView'] ?? false;
                                }
                                if ($section['id'] == 4) {
                                    // Pending - requires view permission
                                    $showSection = $permissions['canView'] ?? false;
                                }
                                if ($section['id'] == 5) {
                                    // Channels - requires view permission
                                    $showSection = $permissions['canView'] ?? false;
                                }
                                if ($section['id'] == 6) {
                                    // Reports - requires reports permission
                                    $showSection = ($permissions['canViewReports'] ?? false) || ($permissions['canView'] ?? false);
                                }
                            @endphp
                            
                            @if($showSection)
                            <button
                                wire:click="selectedMenu({{ $section['id'] }})"
                                class="relative w-full group transition-all duration-200"
                                aria-label="{{ $section['label'] }}"
                            >
                                <div class="flex items-center p-3 rounded-xl transition-all duration-200
                                    @if ($isActive) 
                                        bg-red-600 text-white shadow-lg 
                                    @else 
                                        bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900 
                                    @endif">
                                    <div wire:loading wire:target="selectedMenu({{ $section['id'] }})" class="mr-3">
                                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    <div wire:loading.remove wire:target="selectedMenu({{ $section['id'] }})" class="mr-3">
                                        <svg class="w-5 h-5 @if ($isActive) text-white @else text-gray-500 group-hover:text-gray-700 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 text-left">
                                        <div class="font-medium text-sm">{{ $section['label'] }}</div>
                                        <div class="text-xs opacity-75">{{ $section['description'] }}</div>
                                    </div>
                                </div>
                            </button>
                            @endif
                        @endforeach
                    </nav>
                </div>
            </div>
            <!-- Main Content Area (Dashboard Cards) -->
            <div class="flex-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">
                                    @switch($selectedMenuItem)
                                        @case(1) Dashboard @break
                                        @case(2) New Payment @break
                                        @case(3) Payment List @break
                                        @case(4) Pending @break
                                        @case(5) Channels @break
                                        @case(6) Reports @break
                                        @default Dashboard
                                    @endswitch
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    @switch($selectedMenuItem)
                                        @case(1) Overview of payment performance and key metrics @break
                                        @case(2) Initiate and process new payments @break
                                        @case(3) View and manage all payment records @break
                                        @case(4) Review and process pending payments @break
                                        @case(5) Manage payment channels and billers @break
                                        @case(6) Generate detailed payment reports and analytics @break
                                        @default Overview of payment performance and key metrics
                                    @endswitch
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="p-8">
                        {{-- Dynamic Content Based on Selected Menu Item --}}
                        @switch($selectedMenuItem)
                            @case(1)
                                {{-- Dashboard Content --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 border border-red-200">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold text-red-900">Total Paid</h3>
                                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                        </div>
                                        <div class="text-2xl font-bold text-red-900">{{ number_format(DB::table('payments')->where('status', 'Confirmed')->sum('amount'), 2) }} TZS</div>
                                        <div class="text-sm text-red-700 mt-2">All confirmed payments</div>
                                    </div>
                                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold text-green-900">Recent Payments</h3>
                                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        @php
                                            $recentPayments = DB::table('payments')->orderBy('created_at', 'desc')->limit(3)->get();
                                        @endphp
                                        <div class="space-y-2">
                                            @foreach($recentPayments as $payment)
                                                <div class="flex items-center justify-between bg-white p-2 rounded-lg">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $payment->payer_name ?? 'Payment' }}</p>
                                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($payment->created_at)->format('M d, Y') }}</p>
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">{{ number_format($payment->amount, 2) }} TZS</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold text-yellow-900">Pending Payments</h3>
                                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="text-3xl font-bold text-yellow-900 mb-2">{{ DB::table('payments')->where('status', 'Pending')->count() }}</div>
                                        <p class="text-sm text-yellow-700">Payments awaiting confirmation</p>
                                    </div>
                                </div>
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome to Payments Manager</h3>
                                    <p class="text-gray-600">Choose a payment type to get started</p>
                                </div>
                                @break

                            @case(2)
                                {{-- New Payment - Payment Type Navigation --}}
                                @php
                                    $paymentTypes = [
                                        ['id' => 'money_transfer', 'label' => 'Money Transfer', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'description' => 'Bank-to-Bank & Bank-to-Wallet transfersy'],
                                        ['id' => 'bill_payment', 'label' => 'Bill Payment', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'description' => 'Pay bills from various service providers'],
                                        ['id' => 'luku_payment', 'label' => 'LUKU Payment', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'description' => 'Electricity token purchase'],
                                        ['id' => 'gepg_payment', 'label' => 'GEPG Payment', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'description' => 'Government bills and services'],
                                        ['id' => 'tips_lookup', 'label' => 'TIPS Lookup', 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', 'description' => 'Account verification and lookup services']
                                    ];
                                    $selectedPaymentType = $selectedPaymentType ?? 'money_transfer';
                                @endphp

                                {{-- Payment Type Tabs --}}
                                <div class="mb-8">
                                    <div class="border-b border-gray-200">
                                        <nav class="-mb-px flex space-x-8" aria-label="Payment Types">
                                            @foreach($paymentTypes as $type)
                                                @php
                                                    $isActiveType = $selectedPaymentType === $type['id'];
                                                @endphp
                                                <button
                                                    wire:click="$set('selectedPaymentType', '{{ $type['id'] }}')"
                                                    class="@if($isActiveType) border-red-500 text-red-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-200"
                                                    aria-current="@if($isActiveType) page @endif"
                                                >
                                                    <div class="flex items-center space-x-2">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $type['icon'] }}"></path>
                                                        </svg>
                                                        <span>{{ $type['label'] }}</span>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </nav>
                                    </div>

                                    {{-- Payment Type Description --}}
                                    @foreach($paymentTypes as $type)
                                        @if($selectedPaymentType === $type['id'])
                                            <div class="mt-4 p-4 bg-red-50 rounded-lg border border-red-200">
                                                <p class="text-red-800 text-sm font-medium">{{ $type['description'] }}</p>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                {{-- Payment Content Area --}}
                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                    @switch($selectedPaymentType)
                                        @case('money_transfer')
                                            {{-- Use the improved Money Transfer Livewire Component --}}
                                            @livewire('payments.money-transfer')
                                            @break

                                        @case('bill_payment')
                                            {{-- NBC BILL PAYMENT INTEGRATION --}}
                                            @include('livewire.payments.bill-payment-section')
                                            @break
                                            
                                        @case('bill_payment_old')
                                            {{-- OLD BILL PAYMENT WORKFLOW --}}
                                            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                                                {{-- Header --}}
                                                <div class="px-6 py-4 border-b border-gray-200">
                                                    <h3 class="text-lg font-semibold text-gray-900">Bill Payment</h3>
                                                    <p class="text-sm text-gray-600">Pay your utility bills and services</p>
                                                </div>

                                                {{-- Error and Success Messages --}}
                                                @if($errorMessage)
                                                    <div class="mx-6 mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                                        <div class="flex items-center">
                                                            <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            <span class="text-red-700 text-sm font-medium">{{ $errorMessage }}</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($successMessage)
                                                    <div class="mx-6 mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                                        <div class="flex items-center">
                                                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            <span class="text-green-700 text-sm font-medium">{{ $successMessage }}</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Content --}}
                                                <div class="p-6">
                                                    @if(!$selectedSpCode)
                                                        {{-- STEP 1: SELECT BILLER --}}
                                                        <div class="space-y-6">
                                                            <div>
                                                                <h4 class="text-md font-medium text-gray-900 mb-4">Select Service Provider</h4>
                                                                
                                                                @if(count($billers) > 0)
                                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                                        @foreach($billers as $biller)
                                                                            <button wire:click="selectBiller('{{ $biller['spCode'] }}')"
                                                                                    class="p-4 border-2 border-gray-200 rounded-lg hover:border-red-500 hover:bg-red-50 transition-all duration-200 text-left">
                                                                                <div class="flex items-center space-x-3">
                                                                                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                                                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                                                        </svg>
                                                                                    </div>
                                                                                    <div>
                                                                                        <div class="font-medium text-gray-900">{{ $biller['spName'] ?? $biller['spCode'] }}</div>
                                                                                        <div class="text-sm text-gray-500">Code: {{ $biller['spCode'] }}</div>
                                                                                    </div>
                                                                                </div>
                                                                            </button>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <div class="text-center py-8">
                                                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                        </svg>
                                                                        <h3 class="text-sm font-medium text-gray-900 mb-1">No Billers Available</h3>
                                                                        <p class="text-sm text-gray-500">Unable to load service providers at this time.</p>
                                                                        <button wire:click="fetchBillers" class="mt-3 text-red-600 hover:text-red-500 text-sm font-medium">
                                                                            Try Again
                                                                        </button>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                    @elseif(!$billDetails)
                                                        {{-- STEP 2: BILL INQUIRY --}}
                                                        <div class="space-y-6">
                                                            <div class="flex items-center justify-between">
                                                                <h4 class="text-md font-medium text-gray-900">Bill Inquiry</h4>
                                                                <button wire:click="$set('selectedSpCode', null)" 
                                                                        class="text-sm text-gray-500 hover:text-gray-700">
                                                                    ← Back to Billers
                                                                </button>
                                                            </div>

                                                            {{-- Selected Biller Info --}}
                                                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                                                <div class="flex items-center">
                                                                    <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    <span class="text-red-900 font-medium">
                                                                        Selected: {{ collect($billers)->firstWhere('spCode', $selectedSpCode)['spName'] ?? $selectedSpCode }}
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            {{-- Bill Reference Input --}}
                                                            <form wire:submit.prevent="inquireBill" class="space-y-4">
                                                                <div>
                                                                    <label for="billRef" class="block text-sm font-medium text-gray-700 mb-2">
                                                                        Bill Reference Number *
                                                                    </label>
                                                                    <input type="text" 
                                                                           wire:model.defer="billRef" 
                                                                           id="billRef"
                                                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                                                           placeholder="Enter your bill reference number"
                                                                           required>
                                                                    @error('billRef') 
                                                                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                                                    @enderror
                                                                </div>

                                                                <div class="flex justify-end">
                                                                    <button type="submit" 
                                                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                                                            wire:loading.attr="disabled">
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

                                                    @else
                                                        {{-- STEP 3: PAYMENT PROCESSING --}}
                                                        <div class="space-y-6">
                                                            <div class="flex items-center justify-between">
                                                                <h4 class="text-md font-medium text-gray-900">Payment Confirmation</h4>
                                                                <button wire:click="$set('billDetails', null)" 
                                                                        class="text-sm text-gray-500 hover:text-gray-700">
                                                                    ← Back to Inquiry
                                                                </button>
                                                            </div>

                                                            {{-- Bill Details --}}
                                                            @if($billDetails)
                                                                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                                                                    <div class="flex items-center mb-4">
                                                                        <svg class="w-6 h-6 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                        </svg>
                                                                        <h5 class="text-lg font-medium text-green-900">Bill Found</h5>
                                                                    </div>
                                                                    
                                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                        <div>
                                                                            <p class="text-sm text-green-700">Service Provider</p>
                                                                            <p class="font-medium text-green-900">{{ collect($billers)->firstWhere('spCode', $selectedSpCode)['spName'] ?? $selectedSpCode }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-sm text-green-700">Bill Reference</p>
                                                                            <p class="font-medium text-green-900">{{ $billRef }}</p>
                                                                        </div>
                                                                        @if(isset($billDetails['customerName']))
                                                                            <div>
                                                                                <p class="text-sm text-green-700">Customer Name</p>
                                                                                <p class="font-medium text-green-900">{{ $billDetails['customerName'] }}</p>
                                                                            </div>
                                                                        @endif
                                                                        @if(isset($billDetails['amount']))
                                                                            <div>
                                                                                <p class="text-sm text-green-700">Amount Due</p>
                                                                                <p class="font-medium text-green-900">{{ number_format($billDetails['amount'], 2) }} TZS</p>
                                                                            </div>
                                                                        @endif
                                                                        @if(isset($billDetails['dueDate']))
                                                                            <div>
                                                                                <p class="text-sm text-green-700">Due Date</p>
                                                                                <p class="font-medium text-green-900">{{ $billDetails['dueDate'] }}</p>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            {{-- Payment Form --}}
                                                            @if(!$paymentResponse)
                                                                <form wire:submit.prevent="makePayment" class="space-y-4">
                                                                    <div>
                                                                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                                                            Payment Amount (TZS) *
                                                                        </label>
                                                                        <input type="number" 
                                                                               wire:model.defer="amount" 
                                                                               id="amount"
                                                                               min="100"
                                                                               step="0.01"
                                                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                                                               placeholder="Enter payment amount"
                                                                               value="{{ $billDetails['amount'] ?? '' }}"
                                                                               required>
                                                                        @error('amount') 
                                                                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                                                        @enderror
                                                                    </div>

                                                                    <div class="flex justify-end space-x-3">
                                                                        <button type="button" 
                                                                                wire:click="$set('billDetails', null)"
                                                                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                                            Cancel
                                                                        </button>
                                                                        
                                                                        <button type="submit" 
                                                                                class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                                                                wire:loading.attr="disabled">
                                                                            <span wire:loading.remove wire:target="makePayment">
                                                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                                                </svg>
                                                                                Pay Now
                                                                            </span>
                                                                            <span wire:loading wire:target="makePayment" class="flex items-center">
                                                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                                </svg>
                                                                                Processing Payment...
                                                                            </span>
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            @else
                                                                {{-- Payment Success --}}
                                                                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                                                                    <div class="text-center">
                                                                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                                                                            <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                            </svg>
                                                                        </div>
                                                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Payment Successful!</h3>
                                                                        <p class="text-gray-600 mb-4">Your bill payment has been processed successfully.</p>
                                                                        
                                                                        @if(isset($paymentResponse['gatewayRef']))
                                                                            <div class="bg-white rounded-lg p-4 mb-4">
                                                                                <p class="text-sm text-gray-600">Transaction Reference</p>
                                                                                <p class="font-mono font-medium text-gray-900">{{ $paymentResponse['gatewayRef'] }}</p>
                                                                            </div>
                                                                        @endif

                                                                        <div class="flex justify-center space-x-4">
                                                                            <button wire:click="$set('paymentResponse', null); $set('billDetails', null); $set('selectedSpCode', null); $set('billRef', ''); $set('amount', '')" 
                                                                                    class="inline-flex items-center px-4 py-2 border border-green-600 rounded-md shadow-sm text-sm font-medium text-green-600 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                                                New Payment
                                                                            </button>
                                                                            
                                                                            <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                                                Print Receipt
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            @break

                                        @case('luku_payment')
                                            {{-- LUKU PAYMENT WORKFLOW --}}
                                            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                                                {{-- Header --}}
                                                <div class="px-6 py-4 border-b border-gray-200">
                                                    <h3 class="text-lg font-semibold text-gray-900">LUKU Electricity Payment</h3>
                                                    <p class="text-sm text-gray-600">Purchase electricity tokens for your meter</p>
                                                </div>

                                                {{-- Error and Success Messages --}}
                                                @if($errorMessage)
                                                    <div class="mx-6 mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                                        <div class="flex items-center">
                                                            <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            <span class="text-red-700 text-sm font-medium">{{ $errorMessage }}</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($successMessage)
                                                    <div class="mx-6 mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                                        <div class="flex items-center">
                                                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            <span class="text-green-700 text-sm font-medium">{{ $successMessage }}</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Content --}}
                                                <div class="p-6">
                                                    @if(!$lookupResult)
                                                        {{-- STEP 1: METER LOOKUP --}}
                                                        <div class="space-y-6">
                                                            <div>
                                                                <h4 class="text-md font-medium text-gray-900 mb-4">Meter Lookup</h4>
                                                                <p class="text-sm text-gray-600 mb-6">Enter your meter number to check meter details and purchase electricity tokens</p>
                                                                
                                                                {{-- Meter Lookup Form --}}
                                                                <form wire:submit.prevent="lookup" class="space-y-6">
                                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                                        {{-- Meter Number --}}
                                                                        <div>
                                                                            <label for="meterNumber" class="block text-sm font-medium text-gray-700 mb-2">
                                                                                Meter Number *
                                                                            </label>
                                                                            <input type="text" 
                                                                                   wire:model.defer="meterNumber" 
                                                                                   id="meterNumber"
                                                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                                                                   placeholder="Enter your meter number"
                                                                                   required>
                                                                            @error('meterNumber') 
                                                                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                                                            @enderror
                                                                        </div>

                                                                        {{-- Account Number --}}
                                                                        <div>
                                                                            <label for="accountNumber" class="block text-sm font-medium text-gray-700 mb-2">
                                                                                Account Number *
                                                                            </label>
                                                                            <input type="text" 
                                                                                   wire:model.defer="accountNumber" 
                                                                                   id="accountNumber"
                                                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                                                                   placeholder="Your account number"
                                                                                   required>
                                                                            @error('accountNumber') 
                                                                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                                                            @enderror
                                                                        </div>
                                                                    </div>

                                                                    {{-- Submit Button --}}
                                                                    <div class="flex justify-end">
                                                                        <button type="submit" 
                                                                                class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                                                                wire:loading.attr="disabled">
                                                                            <span wire:loading.remove wire:target="lookup">
                                                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                                                </svg>
                                                                                Lookup Meter
                                                                            </span>
                                                                            <span wire:loading wire:target="lookup" class="flex items-center">
                                                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                                </svg>
                                                                                Looking up...
                                                                            </span>
                                                                        </button>
                                                                    </div>
                                                                </form>

                                                                {{-- Info Section --}}
                                                                <div class="mt-8 bg-red-50 border border-red-200 rounded-lg p-4">
                                                                    <div class="flex items-start">
                                                                        <svg class="w-5 h-5 text-red-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                                        </svg>
                                                                        <div>
                                                                            <h5 class="text-sm font-medium text-red-900 mb-2">How to find your meter number:</h5>
                                                                            <ul class="text-sm text-red-800 space-y-1">
                                                                                <li>• Check your electricity bill for the meter number</li>
                                                                                <li>• Look at the digital display on your meter</li>
                                                                                <li>• Find the serial number on the meter label</li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    @else
                                                        {{-- STEP 2: TOKEN PURCHASE --}}
                                                        <div class="space-y-6">
                                                            <div class="flex items-center justify-between">
                                                                <h4 class="text-md font-medium text-gray-900">Token Purchase</h4>
                                                                <button wire:click="$set('lookupResult', null); $set('meterNumber', ''); $set('accountNumber', '')" 
                                                                        class="text-sm text-gray-500 hover:text-gray-700">
                                                                    ← New Lookup
                                                                </button>
                                                            </div>

                                                            {{-- Meter Details --}}
                                                            @if($lookupResult)
                                                                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                                                                    <div class="flex items-center mb-4">
                                                                        <svg class="w-6 h-6 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                        </svg>
                                                                        <h5 class="text-lg font-medium text-green-900">Meter Found</h5>
                                                                    </div>
                                                                    
                                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                        <div>
                                                                            <p class="text-sm text-green-700">Meter Number</p>
                                                                            <p class="font-medium text-green-900">{{ $meterNumber }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-sm text-green-700">Account Number</p>
                                                                            <p class="font-medium text-green-900">{{ $accountNumber }}</p>
                                                                        </div>
                                                                        @if(isset($lookupResult['customerName']))
                                                                            <div>
                                                                                <p class="text-sm text-green-700">Customer Name</p>
                                                                                <p class="font-medium text-green-900">{{ $lookupResult['customerName'] }}</p>
                                                                            </div>
                                                                        @endif
                                                                        @if(isset($lookupResult['meterType']))
                                                                            <div>
                                                                                <p class="text-sm text-green-700">Meter Type</p>
                                                                                <p class="font-medium text-green-900">{{ $lookupResult['meterType'] }}</p>
                                                                            </div>
                                                                        @endif
                                                                        @if(isset($lookupResult['tariff']))
                                                                            <div>
                                                                                <p class="text-sm text-green-700">Tariff</p>
                                                                                <p class="font-medium text-green-900">{{ $lookupResult['tariff'] }}</p>
                                                                            </div>
                                                                        @endif
                                                                        @if(isset($lookupResult['balance']))
                                                                            <div>
                                                                                <p class="text-sm text-green-700">Current Balance</p>
                                                                                <p class="font-medium text-green-900">{{ $lookupResult['balance'] }} TZS</p>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            {{-- Token Purchase Form --}}
                                                            @if(!$paymentResult)
                                                                <form wire:submit.prevent="pay" class="space-y-6">
                                                                    <div>
                                                                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                                                            Purchase Amount (TZS) *
                                                                        </label>
                                                                        <input type="number" 
                                                                               wire:model.defer="amount" 
                                                                               id="amount"
                                                                               min="1000"
                                                                               step="100"
                                                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                                                               placeholder="Enter amount to purchase (minimum 1,000 TZS)"
                                                                               required>
                                                                        @error('amount') 
                                                                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                                                        @enderror
                                                                        <p class="text-sm text-gray-500 mt-1">Minimum purchase amount is 1,000 TZS</p>
                                                                    </div>

                                                                    {{-- Quick Amount Buttons --}}
                                                                    <div>
                                                                        <label class="block text-sm font-medium text-gray-700 mb-3">Quick Amounts</label>
                                                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                                                            @foreach([1000, 2000, 5000, 10000] as $quickAmount)
                                                                                <button type="button" 
                                                                                        wire:click="$set('amount', {{ $quickAmount }})"
                                                                                        class="p-3 border border-gray-300 rounded-lg text-center hover:border-red-500 hover:bg-red-50 transition-all duration-200 @if($amount == $quickAmount) border-red-500 bg-red-50 @endif">
                                                                                    <div class="font-medium text-gray-900">{{ number_format($quickAmount) }}</div>
                                                                                    <div class="text-xs text-gray-500">TZS</div>
                                                                                </button>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>

                                                                    {{-- Submit Button --}}
                                                                    <div class="flex justify-end space-x-3">
                                                                        <button type="button" 
                                                                                wire:click="$set('lookupResult', null); $set('meterNumber', ''); $set('accountNumber', '')"
                                                                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                                            Cancel
                                                                        </button>
                                                                        
                                                                        <button type="submit" 
                                                                                class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                                                                wire:loading.attr="disabled">
                                                                            <span wire:loading.remove wire:target="pay">
                                                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                                                </svg>
                                                                                Purchase Tokens
                                                                            </span>
                                                                            <span wire:loading wire:target="pay" class="flex items-center">
                                                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                                </svg>
                                                                                Processing Purchase...
                                                                            </span>
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            @else
                                                                {{-- Purchase Success --}}
                                                                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                                                                    <div class="text-center">
                                                                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                                                                            <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                            </svg>
                                                                        </div>
                                                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Purchase Successful!</h3>
                                                                        <p class="text-gray-600 mb-4">Your electricity tokens have been purchased successfully.</p>
                                                                        
                                                                        {{-- Token Details --}}
                                                                        @if(isset($paymentResult['token']))
                                                                            <div class="bg-white rounded-lg p-4 mb-4">
                                                                                <p class="text-sm text-gray-600 mb-2">Your Token Number</p>
                                                                                <p class="font-mono text-lg font-bold text-gray-900 bg-gray-100 p-3 rounded border tracking-wider">{{ $paymentResult['token'] }}</p>
                                                                                <p class="text-xs text-gray-500 mt-2">Enter this token number on your meter keypad</p>
                                                                            </div>
                                                                        @endif

                                                                        {{-- Transaction Details --}}
                                                                        <div class="bg-white rounded-lg p-4 mb-4 text-left">
                                                                            <h5 class="font-medium text-gray-900 mb-3">Transaction Details</h5>
                                                                            <div class="space-y-2 text-sm">
                                                                                @if(isset($paymentResult['transactionRef']))
                                                                                    <div class="flex justify-between">
                                                                                        <span class="text-gray-600">Transaction Reference</span>
                                                                                        <span class="font-mono">{{ $paymentResult['transactionRef'] }}</span>
                                                                                    </div>
                                                                                @endif
                                                                                <div class="flex justify-between">
                                                                                    <span class="text-gray-600">Meter Number</span>
                                                                                    <span>{{ $meterNumber }}</span>
                                                                                </div>
                                                                                <div class="flex justify-between">
                                                                                    <span class="text-gray-600">Amount Paid</span>
                                                                                    <span>{{ number_format($amount ?? 0, 2) }} TZS</span>
                                                                                </div>
                                                                                @if(isset($paymentResult['units']))
                                                                                    <div class="flex justify-between">
                                                                                        <span class="text-gray-600">Units Purchased</span>
                                                                                        <span>{{ $paymentResult['units'] }} kWh</span>
                                                                                    </div>
                                                                                @endif
                                                                                <div class="flex justify-between">
                                                                                    <span class="text-gray-600">Date & Time</span>
                                                                                    <span>{{ now()->format('d M Y, H:i:s') }}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="flex justify-center space-x-4">
                                                                            <button wire:click="$set('paymentResult', null); $set('lookupResult', null); $set('meterNumber', ''); $set('accountNumber', ''); $set('amount', '')" 
                                                                                    class="inline-flex items-center px-4 py-2 border border-green-600 rounded-md shadow-sm text-sm font-medium text-green-600 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                                                New Purchase
                                                                            </button>
                                                                            
                                                                            <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                                                Print Receipt
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            @break

                                        @case('gepg_payment')
                                            {{-- GEPG Payment Integration: Simplified version for testing
                                                 - Supports control number verification
                                                 - Test with control number: 991060011847
                                                 - Payment amounts: 1000-2000 TZS
                                                 - Integrated with NBC GEPG Gateway Service --}}
                                            <div class="gepg-payment-container">
                                                <livewire:payments.simple-gepg-payment />
                                            </div>
                                            @break

                                        @case('tips_lookup')
                                            {{-- TIPS LOOKUP WORKFLOW --}}
                                            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                                                {{-- Header --}}
                                                <div class="px-6 py-4 border-b border-gray-200">
                                                    <h3 class="text-lg font-semibold text-gray-900">TIPS Account Lookup</h3>
                                                    <p class="text-sm text-gray-600">Verify bank accounts, mobile wallets, and merchant accounts</p>
                                                </div>

                                                {{-- Error and Success Messages --}}
                                                @if($errorMessage)
                                                    <div class="mx-6 mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                                        <div class="flex items-center">
                                                            <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            <span class="text-red-700 text-sm font-medium">{{ $errorMessage }}</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Content --}}
                                                <div class="p-6">
                                                    {{-- Lookup Type Selection --}}
                                                    <div class="space-y-6">
                                                        <div>
                                                            <h4 class="text-md font-medium text-gray-900 mb-4">Select Lookup Type</h4>
                                                            
                                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                                                {{-- Bank to Bank --}}
                                                                <button wire:click="$set('lookupType', 'bank-to-bank')"
                                                                        class="relative p-4 border-2 rounded-lg transition-all duration-200 @if($lookupType === 'bank-to-bank') border-red-500 bg-red-50 @else border-gray-200 hover:border-gray-300 @endif">
                                                                    <div class="text-center">
                                                                        <svg class="w-8 h-8 mx-auto mb-2 @if($lookupType === 'bank-to-bank') text-red-600 @else text-gray-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                                        </svg>
                                                                        <div class="font-medium @if($lookupType === 'bank-to-bank') text-red-900 @else text-gray-900 @endif">Bank to Bank</div>
                                                                        <div class="text-sm text-gray-500">Verify bank account details</div>
                                                                    </div>
                                                                    @if($lookupType === 'bank-to-bank')
                                                                        <div class="absolute top-2 right-2">
                                                                            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                            </svg>
                                                                        </div>
                                                                    @endif
                                                                </button>

                                                                {{-- Bank to Wallet --}}
                                                                <button wire:click="$set('lookupType', 'bank-to-wallet')"
                                                                        class="relative p-4 border-2 rounded-lg transition-all duration-200 @if($lookupType === 'bank-to-wallet') border-red-500 bg-red-50 @else border-gray-200 hover:border-gray-300 @endif">
                                                                    <div class="text-center">
                                                                        <svg class="w-8 h-8 mx-auto mb-2 @if($lookupType === 'bank-to-wallet') text-red-600 @else text-gray-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                                        </svg>
                                                                        <div class="font-medium @if($lookupType === 'bank-to-wallet') text-red-900 @else text-gray-900 @endif">Bank to Wallet</div>
                                                                        <div class="text-sm text-gray-500">Verify mobile wallet details</div>
                                                                    </div>
                                                                    @if($lookupType === 'bank-to-wallet')
                                                                        <div class="absolute top-2 right-2">
                                                                            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                            </svg>
                                                                        </div>
                                                                    @endif
                                                                </button>

                                                                {{-- Merchant Payment --}}
                                                                <button wire:click="$set('lookupType', 'merchant-payment')"
                                                                        class="relative p-4 border-2 rounded-lg transition-all duration-200 @if($lookupType === 'merchant-payment') border-red-500 bg-red-50 @else border-gray-200 hover:border-gray-300 @endif">
                                                                    <div class="text-center">
                                                                        <svg class="w-8 h-8 mx-auto mb-2 @if($lookupType === 'merchant-payment') text-red-600 @else text-gray-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                                                        </svg>
                                                                        <div class="font-medium @if($lookupType === 'merchant-payment') text-red-900 @else text-gray-900 @endif">Merchant Payment</div>
                                                                        <div class="text-sm text-gray-500">Verify merchant account details</div>
                                                                    </div>
                                                                    @if($lookupType === 'merchant-payment')
                                                                        <div class="absolute top-2 right-2">
                                                                            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                            </svg>
                                                                        </div>
                                                                    @endif
                                                                </button>
                                                            </div>
                                                        </div>

                                                        {{-- Lookup Form --}}
                                                        <form wire:submit.prevent="performLookup" class="space-y-6">
                                                            {{-- Common Fields --}}
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                                {{-- Debit Account --}}
                                                                <div>
                                                                    <label for="form.debitAccount" class="block text-sm font-medium text-gray-700 mb-2">
                                                                        Your Debit Account *
                                                                    </label>
                                                                    <input type="text" 
                                                                           wire:model.defer="form.debitAccount" 
                                                                           id="form.debitAccount"
                                                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                                                           placeholder="Enter your account number"
                                                                           required>
                                                                    @error('form.debitAccount') 
                                                                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                                                    @enderror
                                                                </div>

                                                                {{-- Amount --}}
                                                                <div>
                                                                    <label for="form.amount" class="block text-sm font-medium text-gray-700 mb-2">
                                                                        Amount (TZS) *
                                                                    </label>
                                                                    <input type="number" 
                                                                           wire:model.defer="form.amount" 
                                                                           id="form.amount"
                                                                           min="100"
                                                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                                                           placeholder="Enter amount to verify"
                                                                           required>
                                                                    @error('form.amount') 
                                                                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                                                    @enderror
                                                                </div>

                                                                {{-- Account Category --}}
                                                                <div>
                                                                    <label for="form.debitAccountCategory" class="block text-sm font-medium text-gray-700 mb-2">
                                                                        Account Category *
                                                                    </label>
                                                                    <select wire:model.defer="form.debitAccountCategory" 
                                                                            id="form.debitAccountCategory"
                                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                                                        <option value="PERSON">Person</option>
                                                                        <option value="BUSINESS">Business</option>
                                                                    </select>
                                                                    @error('form.debitAccountCategory') 
                                                                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            {{-- Conditional Fields Based on Lookup Type --}}
                                                            @if($lookupType === 'bank-to-bank')
                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                                    {{-- Target Account Number --}}
                                                                    <div>
                                                                        <label for="form.accountNumber" class="block text-sm font-medium text-gray-700 mb-2">
                                                                            Target Account Number *
                                                                        </label>
                                                                        <input type="text" 
                                                                               wire:model.defer="form.accountNumber" 
                                                                               id="form.accountNumber"
                                                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                                                               placeholder="Enter account number to verify"
                                                                               required>
                                                                        @error('form.accountNumber') 
                                                                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                                                        @enderror
                                                                    </div>

                                                                    {{-- Bank Code --}}
                                                                    <div>
                                                                        <label for="form.bankCode" class="block text-sm font-medium text-gray-700 mb-2">
                                                                            Bank Code *
                                                                        </label>
                                                                        <select wire:model.defer="form.bankCode" 
                                                                                id="form.bankCode"
                                                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                                                                required>
                                                                            <option value="">Select Bank</option>
                                                                            @foreach($availableBanks ?? [] as $code => $name)
                                                                                <option value="{{ $code }}">{{ $name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('form.bankCode') 
                                                                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                                                        @enderror
                                                                    </div>
                                                                </div>

                                                            @elseif($lookupType === 'bank-to-wallet')
                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                                    {{-- Phone Number --}}
                                                                    <div>
                                                                        <label for="form.phoneNumber" class="block text-sm font-medium text-gray-700 mb-2">
                                                                            Phone Number *
                                                                        </label>
                                                                        <input type="text" 
                                                                               wire:model.defer="form.phoneNumber" 
                                                                               id="form.phoneNumber"
                                                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                                                               placeholder="255XXXXXXXXX"
                                                                               required>
                                                                        @error('form.phoneNumber') 
                                                                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                                                        @enderror
                                                                    </div>

                                                                    {{-- Wallet Provider --}}
                                                                    <div>
                                                                        <label for="form.walletProvider" class="block text-sm font-medium text-gray-700 mb-2">
                                                                            Wallet Provider *
                                                                        </label>
                                                                        <select wire:model.defer="form.walletProvider" 
                                                                                id="form.walletProvider"
                                                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                                                                required>
                                                                            <option value="">Select Provider</option>
                                                                            @foreach($availableWallets ?? [] as $code => $name)
                                                                                <option value="{{ $code }}">{{ $name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('form.walletProvider') 
                                                                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                                                        @enderror
                                                                    </div>
                                                                </div>

                                                            @elseif($lookupType === 'merchant-payment')
                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                                    {{-- Merchant ID --}}
                                                                    <div>
                                                                        <label for="form.merchantId" class="block text-sm font-medium text-gray-700 mb-2">
                                                                            Merchant ID *
                                                                        </label>
                                                                        <input type="text" 
                                                                               wire:model.defer="form.merchantId" 
                                                                               id="form.merchantId"
                                                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                                                               placeholder="Enter merchant ID"
                                                                               required>
                                                                        @error('form.merchantId') 
                                                                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                                                        @enderror
                                                                    </div>

                                                                    {{-- Bank Code --}}
                                                                    <div>
                                                                        <label for="form.bankCode" class="block text-sm font-medium text-gray-700 mb-2">
                                                                            Bank Code *
                                                                        </label>
                                                                        <select wire:model.defer="form.bankCode" 
                                                                                id="form.bankCode"
                                                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                                                                required>
                                                                            <option value="">Select Bank</option>
                                                                            @foreach($availableBanks ?? [] as $code => $name)
                                                                                <option value="{{ $code }}">{{ $name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('form.bankCode') 
                                                                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            {{-- Submit Button --}}
                                                            <div class="flex justify-end pt-4">
                                                                <button type="submit" 
                                                                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                                                        wire:loading.attr="disabled">
                                                                    <span wire:loading.remove wire:target="performLookup">
                                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                                        </svg>
                                                                        Perform Lookup
                                                                    </span>
                                                                    <span wire:loading wire:target="performLookup" class="flex items-center">
                                                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                        </svg>
                                                                        Looking up...
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </form>

                                                        {{-- Lookup Results --}}
                                                        @if($response)
                                                            <div class="mt-8 bg-green-50 border border-green-200 rounded-lg p-6">
                                                                <div class="flex items-center mb-4">
                                                                    <svg class="w-6 h-6 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    <h5 class="text-lg font-medium text-green-900">Lookup Successful</h5>
                                                                </div>
                                                                
                                                                <div class="space-y-4">
                                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                        <div>
                                                                            <p class="text-sm text-green-700">Lookup Type</p>
                                                                            <p class="font-medium text-green-900 capitalize">{{ str_replace('-', ' ', $lookupType) }}</p>
                                                                        </div>
                                                                        @if(isset($response['success']))
                                                                            <div>
                                                                                <p class="text-sm text-green-700">Status</p>
                                                                                <p class="font-medium text-green-900">{{ $response['success'] ? 'Verified' : 'Failed' }}</p>
                                                                            </div>
                                                                        @endif
                                                                        @if(isset($response['data']['body']['fullName']))
                                                                            <div>
                                                                                <p class="text-sm text-green-700">Account Holder</p>
                                                                                <p class="font-medium text-green-900">{{ $response['data']['body']['fullName'] }}</p>
                                                                            </div>
                                                                        @endif
                                                                        @if(isset($response['data']['body']['accountNumber']))
                                                                            <div>
                                                                                <p class="text-sm text-green-700">Account Number</p>
                                                                                <p class="font-medium text-green-900">{{ $response['data']['body']['accountNumber'] }}</p>
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    {{-- Raw Response for Development --}}
                                                                    <details class="mt-4">
                                                                        <summary class="text-sm text-green-700 cursor-pointer hover:text-green-800">View Raw Response</summary>
                                                                        <pre class="mt-2 text-xs bg-green-100 p-3 rounded overflow-x-auto">{{ json_encode($response, JSON_PRETTY_PRINT) }}</pre>
                                                                    </details>
                                                                </div>

                                                                {{-- Clear Results --}}
                                                                <div class="mt-4 pt-4 border-t border-green-200">
                                                                    <button wire:click="$set('response', null)" 
                                                                            class="inline-flex items-center px-4 py-2 border border-green-600 rounded-md shadow-sm text-sm font-medium text-green-600 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                                        New Lookup
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            @break

                                        @default
                                            <div class="p-8 text-center">
                                                <h3 class="text-lg font-medium text-gray-900 mb-2">Select Payment Type</h3>
                                                <p class="text-gray-600">Choose a payment type from the tabs above to get started</p>
                                            </div>
                                    @endswitch
                                </div>
                                @break

                            @case(3)
                                {{-- Payment List Content --}}
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Payment List</h3>
                                    <p class="text-gray-600">Payment records will be displayed here</p>
                                </div>
                                @break

                            @case(4)
                                {{-- Pending Payments Content --}}
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Pending Payments</h3>
                                    <p class="text-gray-600">Pending payment reviews will be displayed here</p>
                                </div>
                                @break

                            @case(5)
                                {{-- Payment Channels Content --}}
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Payment Channels</h3>
                                    <p class="text-gray-600">Channel management will be displayed here</p>
                                </div>
                                @break

                            @case(6)
                                {{-- Reports Content --}}
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Payment Reports</h3>
                                    <p class="text-gray-600">Payment analytics and reports will be displayed here</p>
                                </div>
                                @break

                            @default
                                {{-- Default Dashboard Content --}}
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome to Payments Manager</h3>
                                    <p class="text-gray-600">Select a section from the sidebar to get started</p>
                                </div>
                        @endswitch
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

