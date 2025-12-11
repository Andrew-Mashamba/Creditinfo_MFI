<div>
    {{-- Header Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">Trade and Other Payables</h2>
                <p class="text-sm text-gray-500 mt-1">Manage vendor bills, payments, and cash flow</p>
                
                {{-- Available Features --}}
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-orange-100 text-orange-800">
                        ✓ Bill Creation (Debit: Expense, Credit: Payables)
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                        ✓ Payment Processing (Debit: Payables, Credit: Bank)
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-800">
                        ✓ Early Payment Discounts
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-purple-100 text-purple-800">
                        ✓ VAT Input Tracking
                    </span>
                </div>
            </div>
            <button wire:click="openCreateModal" 
                class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors duration-150">
                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Bill
            </button>
        </div>
    </div>

    {{-- Statistics Cards - Minimal Design --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Payables</p>
                    <p class="text-xl font-semibold text-gray-900 mt-1">{{ number_format($totalPayables, 2) }}</p>
                </div>
                <div class="p-2 bg-gray-50 rounded-lg">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Overdue</p>
                    <p class="text-xl font-semibold text-gray-900 mt-1">{{ number_format($totalOverdue, 2) }}</p>
                </div>
                <div class="p-2 bg-red-50 rounded-lg">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</p>
                    <p class="text-xl font-semibold text-gray-900 mt-1">{{ number_format($totalPaid, 2) }}</p>
                </div>
                <div class="p-2 bg-gray-50 rounded-lg">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Upcoming</p>
                    <p class="text-xl font-semibold text-gray-900 mt-1">{{ number_format($upcomingPayments, 2) }}</p>
                </div>
                <div class="p-2 bg-gray-50 rounded-lg">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Approved</p>
                    <p class="text-xl font-semibold text-gray-900 mt-1">{{ number_format($totalApproved, 2) }}</p>
                </div>
                <div class="p-2 bg-gray-50 rounded-lg">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Days</p>
                    <p class="text-xl font-semibold text-gray-900 mt-1">{{ $averagePaymentPeriod }}</p>
                </div>
                <div class="p-2 bg-gray-50 rounded-lg">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation - Minimal Style --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button wire:click="$set('activeTab', 'overview')" 
                    class="px-6 py-3 text-sm font-medium transition-colors duration-150 {{ $activeTab == 'overview' ? 'text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Overview
                </button>
                <button wire:click="$set('activeTab', 'payables')" 
                    class="px-6 py-3 text-sm font-medium transition-colors duration-150 {{ $activeTab == 'payables' ? 'text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Payables
                </button>
                <button wire:click="$set('activeTab', 'aging')" 
                    class="px-6 py-3 text-sm font-medium transition-colors duration-150 {{ $activeTab == 'aging' ? 'text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Aging Analysis
                </button>
                <button wire:click="$set('activeTab', 'cashflow')" 
                    class="px-6 py-3 text-sm font-medium transition-colors duration-150 {{ $activeTab == 'cashflow' ? 'text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Cash Flow
                </button>
                <button wire:click="$set('activeTab', 'vendors')" 
                    class="px-6 py-3 text-sm font-medium transition-colors duration-150 {{ $activeTab == 'vendors' ? 'text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Vendors
                </button>
                <button wire:click="$set('activeTab', 'notifications')" 
                    class="px-6 py-3 text-sm font-medium transition-colors duration-150 relative {{ $activeTab == 'notifications' ? 'text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Notifications
                    @if(isset($notificationStats['total_unread']) && $notificationStats['total_unread'] > 0)
                        <span class="absolute top-2 right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                            {{ $notificationStats['total_unread'] }}
                        </span>
                    @endif
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            @if($activeTab == 'overview')
                {{-- Aging Summary - Clean Design --}}
                <div>
                    <h3 class="text-base font-medium text-gray-900 mb-4">Aging Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                        @foreach($agingBuckets as $bucket)
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">{{ $bucket['label'] }}</p>
                                <p class="text-lg font-semibold text-gray-900 mt-2">{{ number_format($bucket['amount'], 0) }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $bucket['count'] }} bills</p>
                                <div class="mt-3">
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-gray-600 h-1.5 rounded-full" style="width: {{ $bucket['percentage'] }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">{{ $bucket['percentage'] }}%</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Cash Flow Projection --}}
                <div class="mt-8">
                    <h3 class="text-base font-medium text-gray-900 mb-4">Cash Flow Projection</h3>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                        <div class="grid grid-cols-6 gap-4">
                            @foreach($cashFlowProjection as $month)
                                <div class="text-center">
                                    <p class="text-xs font-medium text-gray-600">{{ $month['month'] }}</p>
                                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ number_format($month['amount'], 0) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if($activeTab == 'payables')
                {{-- Filters - Clean Design --}}
                <div class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-6">
                    <div>
                        <input type="text" wire:model.debounce.300ms="search" 
                            placeholder="Search..." 
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-transparent">
                    </div>
                    <div>
                        <select wire:model="statusFilter" 
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-transparent">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <select wire:model="priorityFilter" 
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-transparent">
                            <option value="all">All Priority</option>
                            <option value="high">High</option>
                            <option value="normal">Normal</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div>
                        <select wire:model="ageFilter" 
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-transparent">
                            <option value="all">All Ages</option>
                            <option value="current">Current</option>
                            <option value="30">1-30 Days</option>
                            <option value="60">31-60 Days</option>
                            <option value="90">61-90 Days</option>
                            <option value="over90">Over 90 Days</option>
                        </select>
                    </div>
                    <div>
                        <input type="date" wire:model="dateFrom" 
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-transparent">
                    </div>
                    <div>
                        <input type="date" wire:model="dateTo" 
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-transparent">
                    </div>
                </div>

                {{-- Batch Actions --}}
                @if($batchPaymentMode && count($selectedPayables) > 0)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-900">
                                    {{ count($selectedPayables) }} payables selected for batch payment
                                </p>
                                <p class="text-xs text-yellow-700 mt-1">
                                    Total Amount: {{ number_format(array_sum(array_column($selectedPayables, 'amount')), 2) }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="processBatchPayment" 
                                    class="px-3 py-1 bg-yellow-600 text-white text-xs font-medium rounded hover:bg-yellow-700">
                                    Process Batch Payment
                                </button>
                                <button wire:click="$set('batchPaymentMode', false)" 
                                    class="px-3 py-1 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Payables Table - Clean Design --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                @if($batchPaymentMode)
                                    <th class="px-6 py-3 text-left">
                                        <input type="checkbox" wire:model="selectAll" 
                                            class="rounded border-gray-300 text-gray-900 focus:ring-gray-500">
                                    </th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Bill #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Vendor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Due Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Amount</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Balance</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Days</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($payables as $payable)
                                @php
                                    $daysOverdue = $payable->days_overdue ?? 0;
                                    $isOverdue = $daysOverdue > 0 && $payable->status != 'paid';
                                @endphp
                                <tr class="{{ $isOverdue ? 'bg-red-50' : ($payable->is_system ?? false ? 'bg-blue-50' : '') }}">
                                    @if($batchPaymentMode)
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if(!($payable->is_system ?? false))
                                                <input type="checkbox" 
                                                    wire:model="selectedPayables" 
                                                    value="{{ $payable->id }}"
                                                    class="rounded border-gray-300 text-gray-900 focus:ring-gray-500">
                                            @else
                                                <span class="text-xs text-gray-400">System</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center">
                                            @if($payable->is_system ?? false)
                                                <svg class="w-4 h-4 text-blue-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                            <span class="font-medium text-gray-900">{{ $payable->bill_number }}</span>
                                        </div>
                                        @if($payable->system_code ?? false)
                                            <span class="text-xs text-blue-600">Service: {{ $payable->system_code }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div>
                                            <p class="text-gray-900">{{ $payable->vendor_name }}</p>
                                            @if(isset($payable->vendor_email) && $payable->vendor_email)
                                                <p class="text-xs text-gray-500">{{ $payable->vendor_email }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $type = $payable->payable_type ?? 'once_off';
                                            $typeColors = [
                                                'once_off' => 'bg-gray-100 text-gray-800',
                                                'installment' => 'bg-blue-100 text-blue-800',
                                                'subscription' => 'bg-purple-100 text-purple-800'
                                            ];
                                            $typeLabels = [
                                                'once_off' => 'Once Off',
                                                'installment' => 'Installment',
                                                'subscription' => 'Subscription'
                                            ];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $typeColors[$type] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $typeLabels[$type] ?? 'Once Off' }}
                                        </span>
                                        @if($type === 'installment' && isset($payable->installments_paid) && isset($payable->installment_count))
                                            <p class="text-xs text-gray-500 mt-1">{{ $payable->installments_paid }}/{{ $payable->installment_count }}</p>
                                        @endif
                                        @if($type === 'subscription' && isset($payable->recurring_frequency))
                                            <p class="text-xs text-gray-500 mt-1">{{ ucfirst($payable->recurring_frequency) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($payable->bill_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="{{ $isOverdue ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                            {{ \Carbon\Carbon::parse($payable->due_date)->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        {{ number_format($payable->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        <span class="{{ $payable->balance > 0 ? 'font-medium text-gray-900' : 'text-gray-500' }}">
                                            {{ number_format($payable->balance, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if(($payable->is_system ?? false) && !($payable->is_enabled ?? true))
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-300 text-gray-700">Disabled</span>
                                        @else
                                            @switch($payable->status)
                                                @case('paid')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                                    @break
                                                @case('partial')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Partial</span>
                                                    @break
                                                @case('overdue')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Cancelled</span>
                                                    @break
                                                @default
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Pending</span>
                                            @endswitch
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        @if($isOverdue)
                                            <span class="text-red-600 font-medium">{{ abs($daysOverdue) }} overdue</span>
                                        @elseif($daysOverdue < 0 && $payable->status != 'paid')
                                            <span class="text-green-600">{{ abs($daysOverdue) }} left</span>
                                        @else
                                            <span class="text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            @if($payable->is_system ?? false)
                                                {{-- System payables have different actions --}}
                                                @if($payable->status != 'paid' && ($payable->is_enabled ?? true))
                                                    <button wire:click="openPaymentModal({{ $payable->id }})" 
                                                        class="text-green-600 hover:text-green-900">Pay</button>
                                                @endif
                                                <button wire:click="viewDetails({{ $payable->id }})" 
                                                    class="text-gray-600 hover:text-gray-900">View</button>
                                                @php
                                                    $service = collect($institutionServices)->firstWhere('code', $payable->system_code ?? '');
                                                    $isMandatory = $service && $service['type'] === 'mandatory';
                                                @endphp
                                                @if(!$isMandatory)
                                                    @if($payable->is_enabled ?? true)
                                                        <button wire:click="toggleSystemPayable({{ $payable->id }})" 
                                                            class="text-yellow-600 hover:text-yellow-900">Disable</button>
                                                    @else
                                                        <button wire:click="toggleSystemPayable({{ $payable->id }})" 
                                                            class="text-blue-600 hover:text-blue-900">Enable</button>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-gray-400">Mandatory</span>
                                                @endif
                                            @else
                                                {{-- Regular payables actions --}}
                                                @if($payable->status != 'paid' && $payable->status != 'cancelled')
                                                    <button wire:click="openPaymentModal({{ $payable->id }})" 
                                                        class="text-green-600 hover:text-green-900">Pay</button>
                                                    <button wire:click="edit({{ $payable->id }})" 
                                                        class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                                @endif
                                                <button wire:click="viewDetails({{ $payable->id }})" 
                                                    class="text-gray-600 hover:text-gray-900">View</button>
                                                @if($payable->status == 'pending')
                                                    <button wire:click="confirmDelete({{ $payable->id }})" 
                                                        class="text-red-600 hover:text-red-900">Cancel</button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $batchPaymentMode ? 10 : 9 }}" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No payables found. Create your first bill to get started.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $payables->links() }}
                </div>
            @endif

            @if($activeTab == 'aging')
                {{-- Detailed Aging Analysis --}}
                <div class="space-y-6">
                    @foreach($agingBuckets as $key => $bucket)
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-sm font-medium text-gray-900">{{ $bucket['label'] }}</h4>
                                <div class="flex items-center gap-4">
                                    <span class="text-sm text-gray-500">{{ $bucket['count'] }} bills</span>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($bucket['amount'], 2) }}</span>
                                    <span class="text-xs text-gray-500">({{ $bucket['percentage'] }}%)</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-gray-400 to-gray-600 h-2 rounded-full" style="width: {{ $bucket['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($activeTab == 'cashflow')
                {{-- Cash Flow Chart --}}
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-100">
                    <h3 class="text-base font-medium text-gray-900 mb-4">6-Month Cash Flow Projection</h3>
                    <div class="space-y-4">
                        @foreach($cashFlowProjection as $month)
                            <div class="flex items-center">
                                <span class="w-20 text-sm text-gray-600">{{ $month['month'] }}</span>
                                <div class="flex-1 mx-4">
                                    <div class="w-full bg-gray-200 rounded-full h-6">
                                        @php
                                            $maxAmount = max(array_column($cashFlowProjection, 'amount')) ?: 1;
                                            $percentage = ($month['amount'] / $maxAmount) * 100;
                                        @endphp
                                        <div class="bg-gray-600 h-6 rounded-full flex items-center justify-end pr-2" style="width: {{ $percentage }}%">
                                            <span class="text-xs text-white font-medium">{{ number_format($month['amount'], 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($activeTab == 'vendors')
                {{-- Vendors Summary --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($vendors as $vendor)
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ $vendor->organization_name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ $vendor->email }}</p>
                                </div>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">Total Bills</span>
                                    <span class="font-medium text-gray-900">{{ $vendor->total_bills ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between text-xs mt-1">
                                    <span class="text-gray-500">Outstanding</span>
                                    <span class="font-medium text-gray-900">{{ number_format($vendor->outstanding_amount ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 text-center py-8">
                            <p class="text-gray-500 text-sm">No vendors found. Add vendors when creating bills.</p>
                        </div>
                    @endforelse
                </div>
            @endif

            @if($activeTab == 'notifications')
                {{-- Notifications Tab --}}
                <div class="space-y-4">
                    {{-- Notification Stats --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Unread</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $notificationStats['total_unread'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Urgent</p>
                                    <p class="text-2xl font-semibold text-red-600">{{ $notificationStats['urgent_unread'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Overdue</p>
                                    <p class="text-2xl font-semibold text-yellow-600">{{ $notificationStats['overdue_count'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Upcoming</p>
                                    <p class="text-2xl font-semibold text-green-600">{{ $notificationStats['upcoming_count'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Manual Trigger Button --}}
                    <div class="flex justify-end mb-4">
                        <button wire:click="triggerPaymentNotifications" 
                            class="px-4 py-2 bg-blue-900 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Send Daily Notifications Now
                        </button>
                    </div>

                    {{-- Notifications List --}}
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Payment Notifications</h3>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor/Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($notifications as $notification)
                                        <tr class="{{ !$notification['is_read'] ? 'bg-blue-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($notification['type'] == 'upcoming_payment')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        Upcoming
                                                    </span>
                                                @elseif($notification['type'] == 'overdue_payment')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Overdue
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Reminder
                                                    </span>
                                                @endif
                                                <span class="ml-2 text-xs text-gray-500">
                                                    {{ $notification['category'] == 'payable' ? '📤' : '📥' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $notification['vendor_or_customer_name'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($notification['amount'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($notification['due_date'])->format('d M Y') }}
                                                @if($notification['days_until_due'] !== null)
                                                    <span class="block text-xs {{ $notification['days_until_due'] < 0 ? 'text-red-600' : 'text-gray-400' }}">
                                                        {{ abs($notification['days_until_due']) }} days {{ $notification['days_until_due'] < 0 ? 'overdue' : 'until due' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($notification['priority'] == 'urgent')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Urgent
                                                    </span>
                                                @elseif($notification['priority'] == 'high')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                                        High
                                                    </span>
                                                @elseif($notification['priority'] == 'medium')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Medium
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Low
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($notification['notification_status'] == 'sent')
                                                    <span class="text-green-600">✓ Sent</span>
                                                    @if($notification['sent_at'])
                                                        <span class="block text-xs text-gray-400">
                                                            {{ \Carbon\Carbon::parse($notification['sent_at'])->diffForHumans() }}
                                                        </span>
                                                    @endif
                                                @elseif($notification['notification_status'] == 'failed')
                                                    <span class="text-red-600">✗ Failed</span>
                                                @else
                                                    <span class="text-gray-400">Pending</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if(!$notification['is_read'])
                                                    <button wire:click="markNotificationAsRead({{ $notification['id'] }})" 
                                                        class="text-blue-600 hover:text-blue-900 text-xs">
                                                        Mark as Read
                                                    </button>
                                                @else
                                                    <span class="text-gray-400 text-xs">Read</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                                No notifications found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Pagination removed since notifications is now an array --}}
                    </div>
                </div>
            @endif


        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all my-8 max-w-4xl">
                        <form wire:submit.prevent="save">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="mb-4">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        {{ $editMode ? 'Edit Bill' : 'Create New Bill' }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Record vendor bills and expenses. This will create accounting entries automatically.
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    {{-- Vendor Information --}}
                                    <div class="col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">Vendor Information</h4>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Vendor Name *</label>
                                        <input type="text" wire:model="vendor_name" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                        @error('vendor_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Vendor Email</label>
                                        <input type="email" wire:model="vendor_email" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                        @error('vendor_email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Vendor Phone</label>
                                        <input type="text" wire:model="vendor_phone" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                        @error('vendor_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tax ID</label>
                                        <input type="text" wire:model="vendor_tax_id" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                    </div>

                                    {{-- Bill Information --}}
                                    <div class="col-span-2 mt-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">Bill Information</h4>
                                    </div>
                                    
                                    {{-- Payable Type Selection --}}
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Payable Type *</label>
                                        <div class="mt-2 grid grid-cols-3 gap-3">
                                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none {{ $payable_type === 'once_off' ? 'border-gray-900 ring-2 ring-gray-900' : 'border-gray-300' }}">
                                                <input type="radio" wire:model.live="payable_type" value="once_off" class="sr-only">
                                                <div class="flex flex-1">
                                                    <div class="flex flex-col">
                                                        <span class="block text-sm font-medium text-gray-900">Once Off</span>
                                                        <span class="mt-1 flex items-center text-xs text-gray-500">Single payment on due date</span>
                                                    </div>
                                                </div>
                                                @if($payable_type === 'once_off')
                                                    <svg class="h-5 w-5 text-gray-900" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </label>
                                            
                                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none {{ $payable_type === 'installment' ? 'border-gray-900 ring-2 ring-gray-900' : 'border-gray-300' }}">
                                                <input type="radio" wire:model.live="payable_type" value="installment" class="sr-only">
                                                <div class="flex flex-1">
                                                    <div class="flex flex-col">
                                                        <span class="block text-sm font-medium text-gray-900">Installments</span>
                                                        <span class="mt-1 flex items-center text-xs text-gray-500">Split into multiple payments</span>
                                                    </div>
                                                </div>
                                                @if($payable_type === 'installment')
                                                    <svg class="h-5 w-5 text-gray-900" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </label>
                                            
                                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none {{ $payable_type === 'subscription' ? 'border-gray-900 ring-2 ring-gray-900' : 'border-gray-300' }}">
                                                <input type="radio" wire:model.live="payable_type" value="subscription" class="sr-only">
                                                <div class="flex flex-1">
                                                    <div class="flex flex-col">
                                                        <span class="block text-sm font-medium text-gray-900">Subscription</span>
                                                        <span class="mt-1 flex items-center text-xs text-gray-500">Recurring monthly/yearly</span>
                                                    </div>
                                                </div>
                                                @if($payable_type === 'subscription')
                                                    <svg class="h-5 w-5 text-gray-900" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bill Number *</label>
                                        <input type="text" wire:model="bill_number" readonly
                                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                        @error('bill_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Purchase Order</label>
                                        <input type="text" wire:model="purchase_order_number" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bill Date *</label>
                                        <input type="date" wire:model="invoice_date" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                        @error('invoice_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Terms (Days) *</label>
                                        <input type="number" wire:model="payment_terms" min="0"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                        @error('payment_terms') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Due Date *</label>
                                        <input type="date" wire:model="due_date" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                        @error('due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    {{-- Installment-specific fields --}}
                                    @if($payable_type === 'installment')
                                        <div class="col-span-2 mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                            <h4 class="text-sm font-medium text-blue-900 mb-3">Installment Configuration</h4>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Number of Installments *</label>
                                                    <input type="number" wire:model.live="installment_count" min="2" max="24"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                                    @error('installment_count') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Frequency *</label>
                                                    <select wire:model.live="installment_frequency" 
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                                        <option value="weekly">Weekly</option>
                                                        <option value="bi_weekly">Bi-Weekly</option>
                                                        <option value="monthly">Monthly</option>
                                                        <option value="quarterly">Quarterly</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            @if(count($installmentSchedule) > 0)
                                                <div class="mt-4">
                                                    <h5 class="text-xs font-medium text-gray-700 mb-2">Installment Schedule Preview</h5>
                                                    <div class="bg-white rounded-lg p-3 max-h-48 overflow-y-auto">
                                                        <table class="min-w-full text-xs">
                                                            <thead class="bg-gray-50">
                                                                <tr>
                                                                    <th class="px-2 py-1 text-left">Installment</th>
                                                                    <th class="px-2 py-1 text-left">Due Date</th>
                                                                    <th class="px-2 py-1 text-right">Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-gray-200">
                                                                @foreach($installmentSchedule as $installment)
                                                                    <tr>
                                                                        <td class="px-2 py-1">#{{ $installment['installment_number'] }}</td>
                                                                        <td class="px-2 py-1">{{ \Carbon\Carbon::parse($installment['due_date'])->format('M d, Y') }}</td>
                                                                        <td class="px-2 py-1 text-right">{{ number_format($installment['amount'], 2) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    {{-- Subscription-specific fields --}}
                                    @if($payable_type === 'subscription')
                                        <div class="col-span-2 mt-4 p-4 bg-purple-50 rounded-lg border border-purple-200">
                                            <h4 class="text-sm font-medium text-purple-900 mb-3">Subscription Configuration</h4>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Billing Frequency *</label>
                                                    <select wire:model="recurring_frequency" 
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                                        <option value="monthly">Monthly</option>
                                                        <option value="quarterly">Quarterly</option>
                                                        <option value="annually">Annually</option>
                                                    </select>
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Service Type</label>
                                                    <select wire:model="service_type" 
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                                        <option value="general">General Service</option>
                                                        <option value="software">Software License</option>
                                                        <option value="maintenance">Maintenance</option>
                                                        <option value="hosting">Hosting</option>
                                                        <option value="utilities">Utilities</option>
                                                        <option value="rent">Rent</option>
                                                    </select>
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Start Date *</label>
                                                    <input type="date" wire:model="recurring_start_date" 
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                                    @error('recurring_start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">End Date (Optional)</label>
                                                    <input type="date" wire:model="recurring_end_date" 
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Currency</label>
                                        <select wire:model="currency" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                            <option value="TZS">TZS</option>
                                            <option value="USD">USD</option>
                                            <option value="EUR">EUR</option>
                                            <option value="GBP">GBP</option>
                                        </select>
                                    </div>

                                    {{-- Amount Information --}}
                                    <div class="col-span-2 mt-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">Amount Information</h4>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Amount (Excl. VAT) *</label>
                                        <input type="number" wire:model="amount" step="0.01" min="0"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                        @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">VAT Amount</label>
                                        <input type="number" wire:model="vat_amount" step="0.01" min="0"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                        @error('vat_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-2">
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <p class="text-sm font-medium text-gray-700">Total Amount: 
                                                <span class="text-lg font-semibold text-gray-900">{{ $currency }} {{ number_format($total_amount, 2) }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Account Selection --}}
                                    <div class="col-span-2 mt-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">Account Configuration</h4>
                                    </div>

                                    {{-- Parent Payable Account is set automatically from institution settings --}}
                                    <input type="hidden" wire:model="parent_account_number">

                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Expense Account *</label>
                                        <select wire:model="other_account_id" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                            <option value="">Select Expense Account</option>
                                            @foreach($expenseAccounts as $account)
                                                <option value="{{ $account->id }}">
                                                    {{ $account->account_name }} ({{ $account->account_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('other_account_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Vendor Bank Details --}}
                                    <div class="col-span-2 mt-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">Vendor Bank Details (For Payment)</h4>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bank Name <span class="text-red-500">*</span></label>
                                        <select wire:model="vendor_bank_name" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                            <option value="">Select Bank</option>
                                            @foreach($this->getAvailableBanks() as $bank)
                                                <option value="{{ $bank['name'] }}">
                                                    {{ $bank['name'] }}
                                                    @if($bank['is_nbc'])
                                                        (Internal)
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('vendor_bank_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Account Number <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="vendor_bank_account_number" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm"
                                            placeholder="Enter account number">
                                        @error('vendor_bank_account_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bank Branch</label>
                                        <input type="text" wire:model="vendor_bank_branch" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm"
                                            placeholder="Optional">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SWIFT Code</label>
                                        <input type="text" wire:model="vendor_swift_code" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm"
                                            placeholder="Auto-populated when bank is selected" readonly>
                                    </div>

                                    {{-- Additional Information --}}
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea wire:model="description" rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm"></textarea>
                                    </div>

                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                                        <textarea wire:model="notes" rows="2"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm"></textarea>
                                    </div>

                                    {{-- File Attachments --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Invoice Attachment</label>
                                        <input type="file" wire:model="invoice_attachment" 
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                                        @error('invoice_attachment') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">PO Attachment</label>
                                        <input type="file" wire:model="purchase_order_attachment" 
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit" 
                                    class="inline-flex w-full justify-center rounded-md bg-gray-900 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 sm:ml-3 sm:w-auto">
                                    {{ $editMode ? 'Update Bill' : 'Create Bill' }}
                                </button>
                                <button type="button" wire:click="$set('showCreateModal', false)" 
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Payment Modal --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50">
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <form wire:submit.prevent="processPayment">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="mb-4">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Process Bank Transfer Payment</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Outstanding Balance: <span class="font-medium text-gray-900">{{ number_format($selectedBalance, 2) }} TZS</span>
                                    </p>
                                </div>

                                {{-- Vendor Bank Details --}}
                                @php
                                    $payable = $payment_payable_id ? \DB::table('trade_payables')->find($payment_payable_id) : null;
                                @endphp
                                
                                @if($payable)
                                    <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                        <h4 class="text-sm font-semibold text-blue-900 mb-2">Vendor Information</h4>
                                        <div class="grid grid-cols-1 gap-2 text-sm">
                                            <div>
                                                <span class="text-gray-600">Vendor Name:</span>
                                                <span class="font-medium text-gray-900 ml-2">{{ $payable->vendor_name }}</span>
                                            </div>
                                            @if($payable->vendor_email)
                                                <div>
                                                    <span class="text-gray-600">Email:</span>
                                                    <span class="font-medium text-gray-900 ml-2">{{ $payable->vendor_email }}</span>
                                                </div>
                                            @endif
                                            @if($payable->vendor_phone)
                                                <div>
                                                    <span class="text-gray-600">Phone:</span>
                                                    <span class="font-medium text-gray-900 ml-2">{{ $payable->vendor_phone }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        @if($payable->vendor_bank_name || $payable->vendor_bank_account_number)
                                            <h4 class="text-sm font-semibold text-blue-900 mt-3 mb-2">Bank Details for Transfer</h4>
                                            <div class="grid grid-cols-1 gap-2 text-sm">
                                                @if($payable->vendor_bank_name)
                                                    <div>
                                                        <span class="text-gray-600">Bank:</span>
                                                        <span class="font-medium text-gray-900 ml-2">{{ $payable->vendor_bank_name }}</span>
                                                    </div>
                                                @endif
                                                @if($payable->vendor_bank_account_number)
                                                    <div>
                                                        <span class="text-gray-600">Account Number:</span>
                                                        <span class="font-medium text-gray-900 ml-2">{{ $payable->vendor_bank_account_number }}</span>
                                                    </div>
                                                @endif
                                                @if($payable->vendor_bank_branch)
                                                    <div>
                                                        <span class="text-gray-600">Branch:</span>
                                                        <span class="font-medium text-gray-900 ml-2">{{ $payable->vendor_bank_branch }}</span>
                                                    </div>
                                                @endif
                                                @if($payable->vendor_swift_code)
                                                    <div>
                                                        <span class="text-gray-600">SWIFT Code:</span>
                                                        <span class="font-medium text-gray-900 ml-2">{{ $payable->vendor_swift_code }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Amount *</label>
                                        <input type="number" wire:model="payment_amount" step="0.01" min="0.01" max="{{ $selectedBalance }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                        @error('payment_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Date *</label>
                                        <input type="date" wire:model="payment_date" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                        @error('payment_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Payment Method is always Bank Transfer --}}
                                    <input type="hidden" wire:model="payment_method" value="bank_transfer">
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                        <div class="mt-1 px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm">
                                            <span class="font-medium text-gray-900">Bank Transfer</span>
                                            <span class="text-gray-500 ml-2">(All payments are processed via bank transfer)</span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Account *</label>
                                        <select wire:model="payment_account_id" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                            <option value="">Select Payment Account</option>
                                            @foreach($paymentAccounts as $account)
                                                <option value="{{ $account['account_number'] }}">
                                                    {{ $account['bank_name'] }} - {{ $account['account_name'] }}
                                                    (Balance: {{ number_format($account['current_balance'], 2) }} {{ $account['currency'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('payment_account_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    @if($early_payment_discount > 0)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Early Payment Discount</label>
                                            <input type="number" wire:model="early_payment_discount" step="0.01" min="0"
                                                class="mt-1 block w-full rounded-md border-gray-300 bg-green-50 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                        </div>
                                    @endif

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Withholding Tax</label>
                                        <input type="number" wire:model="withholding_tax" step="0.01" min="0"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Notes</label>
                                        <textarea wire:model="payment_notes" rows="2"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Voucher</label>
                                        <input type="file" wire:model="payment_voucher" 
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit" 
                                    class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:ml-3 sm:w-auto">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    Process Bank Transfer
                                </button>
                                <button type="button" wire:click="$set('showPaymentModal', false)" 
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirmation Modal --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50">
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-base font-semibold leading-6 text-gray-900">{{ $confirmTitle }}</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">{{ $confirmMessage }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="button" wire:click="proceedWithAction" 
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                                {{ $confirmButtonText }}
                            </button>
                            <button type="button" wire:click="$set('showConfirmModal', false)" 
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Livewire Notifications (Pure Livewire, no JavaScript) --}}
    @if(session()->has('success'))
        <div class="fixed bottom-4 right-4 z-50 animate-pulse"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-full"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-full">
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('success') }}
                        </p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="show = false" class="inline-flex text-green-400 hover:text-green-600 focus:outline-none">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="fixed bottom-4 right-4 z-50"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 8000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-full"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-full">
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">
                            {{ session('error') }}
                        </p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="show = false" class="inline-flex text-red-400 hover:text-red-600 focus:outline-none">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>