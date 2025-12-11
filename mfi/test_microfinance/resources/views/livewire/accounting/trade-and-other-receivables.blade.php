<div>
    {{-- Header Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">Trade and Other Receivables</h2>
                <p class="text-sm text-gray-500 mt-1">Manage customer invoices, payments, and credit control</p>
                
                {{-- Available Features --}}
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-800">
                        ✓ Invoice Creation (Debit: Receivables, Credit: Income)
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-red-100 text-red-800">
                        ✓ Payment Receipt (Debit: Bank, Credit: Income)
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-orange-100 text-orange-800">
                        ✓ Write-Off (Debit: Bad Debt, Credit: Receivables)
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-red-100 text-red-800">
                        ✓ Cancellation (Reversal Entries)
                    </span>
                </div>
            </div>
            <button wire:click="openCreateModal" 
                class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors duration-150">
                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Invoice
            </button>
        </div>
    </div>

    {{-- Statistics Cards - Minimal Design --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Receivables</p>
                    <p class="text-xl font-semibold text-gray-900 mt-1">{{ number_format($totalReceivables, 2) }}</p>
                </div>
                <div class="p-2 bg-gray-50 rounded-lg">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
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
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Collected</p>
                    <p class="text-xl font-semibold text-gray-900 mt-1">{{ number_format($totalCollected, 2) }}</p>
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
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Bad Debt</p>
                    <p class="text-xl font-semibold text-gray-900 mt-1">{{ number_format($totalBadDebt, 2) }}</p>
                </div>
                <div class="p-2 bg-gray-50 rounded-lg">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Days</p>
                    <p class="text-xl font-semibold text-gray-900 mt-1">{{ $averageCollectionPeriod }}</p>
                </div>
                <div class="p-2 bg-gray-50 rounded-lg">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
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
                <button wire:click="$set('activeTab', 'receivables')" 
                    class="px-6 py-3 text-sm font-medium transition-colors duration-150 {{ $activeTab == 'receivables' ? 'text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Receivables
                </button>
                <button wire:click="$set('activeTab', 'aging')" 
                    class="px-6 py-3 text-sm font-medium transition-colors duration-150 {{ $activeTab == 'aging' ? 'text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Aging Analysis
                </button>
                <button wire:click="$set('activeTab', 'collections')" 
                    class="px-6 py-3 text-sm font-medium transition-colors duration-150 {{ $activeTab == 'collections' ? 'text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Collections
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            @if($activeTab == 'overview')
                {{-- Aging Summary - Clean Design --}}
                <div>
                    <h3 class="text-base font-medium text-gray-900 mb-4">Aging Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($agingBuckets as $bucket)
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">{{ $bucket['label'] }}</p>
                                <p class="text-lg font-semibold text-gray-900 mt-2">{{ number_format($bucket['amount'], 0) }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $bucket['count'] }} invoices</p>
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
            @endif

            @if($activeTab == 'receivables')
                {{-- Filters - Clean Design --}}
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
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
                            <option value="written_off">Written Off</option>
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

                {{-- Receivables Table - Clean Design --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Invoice #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Balance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Age</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($receivables as $receivable)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <div class="flex items-center space-x-2">
                                            <span>{{ $receivable->invoice_number }}</span>
                                            @if(isset($receivable->processing_status))
                                                @if($receivable->processing_status == 'processing')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <svg class="animate-spin -ml-0.5 mr-1 h-3 w-3 text-yellow-600" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        Processing
                                                    </span>
                                                @elseif($receivable->processing_status == 'failed')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800" title="{{ $receivable->processing_error ?? 'Error processing invoice' }}">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        Failed
                                                    </span>
                                                @elseif($receivable->processing_status == 'completed' && $receivable->invoice_file_path)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        Ready
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $receivable->customer_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($receivable->invoice_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($receivable->due_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        {{ number_format($receivable->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        {{ number_format($receivable->balance, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($receivable->status == 'paid')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <svg class="w-3 h-3 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Paid
                                            </span>
                                        @elseif($receivable->status == 'partial')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <svg class="w-3 h-3 mr-1 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                Partial
                                            </span>
                                        @elseif($receivable->days_overdue > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                Overdue
                                            </span>
                                        @elseif($receivable->status == 'written_off')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                Written Off
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($receivable->days_overdue > 0)
                                            <span class="text-red-600 font-medium">{{ abs($receivable->days_overdue) }}d overdue</span>
                                        @else
                                            <span class="text-gray-500">{{ abs($receivable->days_overdue) }} days</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center space-x-3">
                                            @if($receivable->status != 'paid' && $receivable->status != 'written_off')
                                                <button wire:click="openPaymentModal({{ $receivable->id }})" 
                                                    class="text-gray-500 hover:text-gray-700 transition-colors duration-150" title="Record Payment">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                            {{-- Download Invoice Button --}}
                                            <button wire:click="downloadInvoice({{ $receivable->id }})" 
                                                class="text-gray-500 hover:text-red-600 transition-colors duration-150" 
                                                title="Download Invoice">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </button>
                                            
                                            {{-- View Invoice Button --}}
                                            <button wire:click="viewInvoice({{ $receivable->id }})" 
                                                class="text-gray-500 hover:text-green-600 transition-colors duration-150" 
                                                title="View Invoice"
                                                target="_blank">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                            
                                            <button wire:click="edit({{ $receivable->id }})" 
                                                class="text-gray-500 hover:text-gray-700 transition-colors duration-150" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            
                                            @if($receivable->status == 'pending' && $receivable->balance == $receivable->amount)
                                                <button wire:click="confirmDelete({{ $receivable->id }})"
                                                    class="text-gray-500 hover:text-red-600 transition-colors duration-150" title="Cancel/Delete Invoice">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                            @if($receivable->status != 'paid' && $receivable->days_overdue > 30)
                                                <button wire:click="sendReminder({{ $receivable->id }})" 
                                                    class="text-gray-500 hover:text-gray-700 transition-colors duration-150" title="Send Reminder">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                            @if($receivable->status != 'paid' && $receivable->status != 'written_off' && $receivable->balance > 0)
                                                @if($receivable->days_overdue > 90)
                                                    <button wire:click="confirmWriteOff({{ $receivable->id }})" 
                                                        class="text-gray-500 hover:text-orange-600 transition-colors duration-150" 
                                                        title="Write Off Bad Debt ({{ $receivable->days_overdue }} days overdue)">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-gray-500 text-sm">No receivables found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $receivables->links() }}
                </div>
            @endif

            @if($activeTab == 'aging')
                {{-- Detailed Aging Analysis - Clean Design --}}
                <div class="space-y-4">
                    @foreach($agingBuckets as $key => $bucket)
                        @if($bucket['count'] > 0)
                            <div class="bg-white border border-gray-200 rounded-lg p-5">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $bucket['label'] }}</h4>
                                    <div class="text-right">
                                        <p class="text-lg font-semibold text-gray-900">{{ number_format($bucket['amount'], 2) }}</p>
                                        <p class="text-xs text-gray-500">{{ $bucket['count'] }} invoices</p>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-gray-600 h-2 rounded-full transition-all duration-300" style="width: {{ $bucket['percentage'] }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">{{ $bucket['percentage'] }}% of total</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            @if($activeTab == 'collections')
                {{-- Collections Dashboard - Clean Design --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-base font-medium text-gray-900 mb-4">Collection Targets</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">This Week</span>
                                    <span class="text-sm font-medium text-gray-900">75%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-gray-600 h-2 rounded-full" style="width: 75%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">This Month</span>
                                    <span class="text-sm font-medium text-gray-900">62%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-gray-600 h-2 rounded-full" style="width: 62%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">This Quarter</span>
                                    <span class="text-sm font-medium text-gray-900">85%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-gray-600 h-2 rounded-full" style="width: 85%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-base font-medium text-gray-900 mb-4">Top Debtors</h3>
                        <div class="space-y-3">
                            @php
                                $topDebtors = DB::table('trade_receivables')
                                    ->select('customer_name', DB::raw('SUM(balance) as total_due'))
                                    ->where('status', '!=', 'paid')
                                    ->where('status', '!=', 'written_off')
                                    ->groupBy('customer_name')
                                    ->orderBy('total_due', 'desc')
                                    ->limit(5)
                                    ->get();
                            @endphp
                            @forelse($topDebtors as $index => $debtor)
                                <div class="flex justify-between items-center py-2 {{ $index < count($topDebtors) - 1 ? 'border-b border-gray-100' : '' }}">
                                    <div class="flex items-center">
                                        <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-xs font-medium flex items-center justify-center mr-3">
                                            {{ $index + 1 }}
                                        </span>
                                        <span class="text-sm text-gray-700">{{ $debtor->customer_name }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ number_format($debtor->total_due, 2) }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 text-center py-4">No outstanding debtors</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
            <div class="fixed inset-0 z-90 overflow-y-auto">
                <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
                    <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-4xl sm:w-full">
                        <form wire:submit.prevent="save">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    {{ $editMode ? 'Edit Invoice' : 'Create New Invoice' }}
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Customer Information --}}
                                    <div class="col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Customer Information</h4>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Customer Name <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="customer_name" 
                                            placeholder="Enter customer name"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('customer_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Company/Organization</label>
                                        <input type="text" wire:model="customer_company" 
                                            placeholder="Enter company name (optional)"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('customer_company') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                                        <input type="email" wire:model="customer_email" 
                                            placeholder="customer@example.com"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('customer_email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                        <input type="text" wire:model="customer_phone" 
                                            placeholder="+255 xxx xxx xxx"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('customer_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Customer Address</label>
                                        <textarea wire:model="customer_address" rows="2"
                                            placeholder="Enter customer address"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500"></textarea>
                                        @error('customer_address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Invoice Details --}}
                                    <div class="col-span-2 mt-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Invoice Details</h4>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Invoice Number</label>
                                        <input type="text" wire:model="invoice_number" {{ $editMode ? 'readonly' : '' }}
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm {{ $editMode ? 'bg-gray-100' : 'focus:ring-gray-500 focus:border-gray-500' }}">
                                        @error('invoice_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Invoice Date</label>
                                        <input type="date" wire:model.lazy="invoice_date"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('invoice_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Terms (Days)</label>
                                        <input type="number" wire:model.lazy="payment_terms"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('payment_terms') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Due Date</label>
                                        <input type="date" wire:model="due_date" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Financial Details --}}
                                    <div class="col-span-2 mt-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Financial Details</h4>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                                        <input type="number" step="0.01" wire:model.lazy="amount"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">VAT Amount</label>
                                        <input type="number" step="0.01" wire:model.lazy="vat_amount"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('vat_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Total Amount</label>
                                        <input type="number" step="0.01" wire:model="total_amount" readonly
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Currency</label>
                                        <select wire:model="currency" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                            <option value="TZS">TZS - Tanzanian Shilling</option>
                                            <option value="USD">USD - US Dollar</option>
                                            <option value="EUR">EUR - Euro</option>
                                            <option value="GBP">GBP - British Pound</option>
                                        </select>
                                        @error('currency') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Additional Information --}}
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea wire:model="description" rows="2"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500"></textarea>
                                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reference Number</label>
                                        <input type="text" wire:model="reference_number" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('reference_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Status</label>
                                        <select wire:model="status" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                            <option value="pending">Pending</option>
                                            <option value="paid">Paid</option>
                                            <option value="partial">Partial</option>
                                            <option value="overdue">Overdue</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-900 text-base font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                    wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="save">{{ $editMode ? 'Update' : 'Create' }}</span>
                                    <span wire:loading wire:target="save">Processing...</span>
                                </button>
                                <button type="button" wire:click="$set('showCreateModal', false)" 
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
                    <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
                        <form wire:submit.prevent="processPayment">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    <i class="fas fa-money-check-alt mr-2"></i> Record Payment
                                </h3>
                                @if($selectedBalance > 0)
                                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-info-circle text-red-400"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-red-700">
                                                    Outstanding Balance: <strong>{{ number_format($selectedBalance, 2) }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Amount</label>
                                        <input type="number" step="0.01" wire:model="payment_amount" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('payment_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Date</label>
                                        <input type="date" wire:model="payment_date" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('payment_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                        <select wire:model="payment_method" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="cash">Cash</option>
                                            <option value="cheque">Cheque</option>
                                            <option value="card">Card</option>
                                            <option value="mobile_money">Mobile Money</option>
                                        </select>
                                        @error('payment_method') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bank Account <span class="text-red-500">*</span></label>
                                        <select wire:model="payment_account_id" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500" required>
                                            <option value="">Select Bank Account</option>
                                            @if($paymentAccounts && count($paymentAccounts) > 0)
                                                @foreach($paymentAccounts as $account)
                                                    <option value="{{ $account['account_number'] }}">
                                                        {{ $account['bank_name'] }} - {{ $account['account_name'] }} 
                                                        ({{ $account['bank_account_number'] }})
                                                        @if($account['currency'] != 'TZS')
                                                            [{{ $account['currency'] }}]
                                                        @endif
                                                    </option>
                                                @endforeach
                                            @else
                                                <option value="" disabled>No bank accounts available</option>
                                            @endif
                                        </select>
                                        @error('payment_account_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reference Number</label>
                                        <input type="text" wire:model="payment_reference" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('payment_reference') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bank Charges</label>
                                        <input type="number" step="0.01" wire:model="bank_charges" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('bank_charges') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Discount Amount</label>
                                        <input type="number" step="0.01" wire:model="discount_amount" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500">
                                        @error('discount_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Receipt (Optional)</label>
                                        <input type="file" wire:model="payment_receipt" accept=".pdf,.jpg,.jpeg,.png"
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                                        @error('payment_receipt') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                                        <textarea wire:model="payment_notes" rows="2"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-gray-500 focus:border-gray-500"></textarea>
                                        @error('payment_notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-900 text-base font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Process Payment
                                </button>
                                <button type="button" wire:click="$set('showPaymentModal', false)" 
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
            <div class="bg-white rounded-lg shadow-lg p-6 max-w-md">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">
                    {{ $confirmTitle }}
                </h3>
                <p class="text-gray-600 mb-5">
                    {{ $confirmMessage }}
                </p>
                <div class="flex justify-end space-x-3">
                    <button wire:click="$set('showConfirmModal', false)" 
                        class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="proceedWithAction" 
                        class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                        {{ $confirmButtonText }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- JavaScript for alerts only --}}
    @push('scripts')
        <script nonce="{{ csp_nonce() }}">
            window.addEventListener('alert', event => {
                const type = event.detail.type;
                const message = event.detail.message;
                
                // You can implement your preferred notification system here
                if (type === 'success') {
                    // Show success toast
                    console.log('Success:', message);
                } else if (type === 'error') {
                    // Show error toast
                    console.log('Error:', message);
                } else {
                    // Show info toast
                    console.log('Info:', message);
                }
            });
        </script>
    @endpush
</div>