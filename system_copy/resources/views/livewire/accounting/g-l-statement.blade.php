<div class="p-4">
    {{-- Header Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-4">
        <div class="bg-blue-900 text-white px-4 py-2 rounded-t-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v7m3-2h6" />
                    </svg>
                    <h2 class="text-lg font-semibold">General Ledger Statement</h2>
                </div>
                <div class="flex items-center space-x-2">
                    <button wire:click="exportPDF" class="bg-white text-blue-900 px-3 py-1 text-sm rounded-md hover:bg-blue-50 transition-colors flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Export PDF</span>
                    </button>
                    <button wire:click="exportExcel" class="bg-white text-blue-900 px-3 py-1 text-sm rounded-md hover:bg-blue-50 transition-colors flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        <span>Export Excel</span>
                    </button>
                </div>
            </div>
        </div>
        
        {{-- Account & Period Selection --}}
        <div class="p-4 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Account</label>
                    <select wire:model="selectedAccount" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $account)
                            @php
                                $acc = (object) $account;
                            @endphp
                            <option value="{{ $acc->account_number }}">{{ $acc->account_number }} - {{ $acc->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" wire:model="startDate" 
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" wire:model="endDate" 
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Transaction Type</label>
                    <select wire:model="transactionType" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Transactions</option>
                        <option value="credit">Credits Only</option>
                        <option value="debit">Debits Only</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-between items-center mt-3">
                <div class="flex space-x-2">
                    <button wire:click="applyFilters" class="bg-blue-900 text-white px-3 py-1 text-sm rounded-md hover:bg-blue-800 transition-colors">
                        Apply Filters
                    </button>
                    <button wire:click="resetFilters" class="bg-gray-200 text-gray-700 px-3 py-1 text-sm rounded-md hover:bg-gray-300 transition-colors">
                        Reset
                    </button>
                </div>
                
                <div class="text-sm text-gray-600">
                    <span>Period: </span>
                    <span class="font-medium">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                </div>
            </div>
        </div>
        
        {{-- Account Summary --}}
        @if($selectedAccount)
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-600">Account Name</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $accountDetails->account_name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-600">Opening Balance</p>
                    <p class="text-sm font-semibold text-gray-900">{{ number_format($openingBalance, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-600">Total Credits</p>
                    <p class="text-sm font-semibold text-green-600">+{{ number_format($totalCredits, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-600">Total Debits</p>
                    <p class="text-sm font-semibold text-red-600">-{{ number_format($totalDebits, 2) }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    {{-- Statement Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Date</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Reference</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Description</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Account</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Debit</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Credit</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Opening Balance Row --}}
                    @if($selectedAccount && $transactions->count() > 0)
                    <tr class="bg-blue-50">
                        <td class="px-3 py-2 text-sm text-gray-900">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-sm text-gray-600">-</td>
                        <td class="px-3 py-2 text-sm font-medium text-gray-900">Opening Balance</td>
                        <td class="px-3 py-2 text-sm text-gray-600">-</td>
                        <td class="px-3 py-2 text-sm text-right">-</td>
                        <td class="px-3 py-2 text-sm text-right">-</td>
                        <td class="px-3 py-2 text-sm text-right font-semibold">{{ number_format($openingBalance, 2) }}</td>
                    </tr>
                    @endif
                    
                    @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50 @if($loop->even) bg-gray-50/30 @endif">
                        <td class="px-3 py-2 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y') }}
                            <span class="text-xs text-gray-500 block">{{ \Carbon\Carbon::parse($transaction->created_at)->format('H:i') }}</span>
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-sm font-medium text-blue-600">{{ $transaction->reference_number }}</p>
                            @if($transaction->bank_reference_number)
                                <p class="text-xs text-gray-500">Bank: {{ $transaction->bank_reference_number }}</p>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-sm text-gray-900">{{ Str::limit($transaction->narration, 50) }}</p>
                            <p class="text-xs text-gray-500">
                                @if($transaction->transaction_type)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $transaction->transaction_type }}
                                    </span>
                                @endif
                            </p>
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-sm text-gray-900">{{ $transaction->record_on_account_number }}</p>
                            @if($transaction->account)
                                <p class="text-xs text-gray-500">{{ Str::limit($transaction->account->account_name, 20) }}</p>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            @if($transaction->debit > 0)
                                <span class="text-sm font-medium text-red-600">{{ number_format($transaction->debit, 2) }}</span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            @if($transaction->credit > 0)
                                <span class="text-sm font-medium text-green-600">{{ number_format($transaction->credit, 2) }}</span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            <span class="text-sm font-semibold @if($transaction->running_balance < 0) text-red-600 @else text-gray-900 @endif">
                                {{ number_format($transaction->running_balance, 2) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-3 py-8 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-gray-500 text-sm">No transactions found</p>
                                <p class="text-gray-400 text-xs mt-1">Try adjusting your filters</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                    
                    {{-- Closing Balance Row --}}
                    @if($selectedAccount && $transactions->count() > 0)
                    <tr class="bg-blue-100 font-semibold">
                        <td class="px-3 py-2 text-sm text-gray-900">{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-sm text-gray-600">-</td>
                        <td class="px-3 py-2 text-sm text-gray-900">Closing Balance</td>
                        <td class="px-3 py-2 text-sm text-gray-600">-</td>
                        <td class="px-3 py-2 text-sm text-right text-red-600">{{ number_format($totalDebits, 2) }}</td>
                        <td class="px-3 py-2 text-sm text-right text-green-600">{{ number_format($totalCredits, 2) }}</td>
                        <td class="px-3 py-2 text-sm text-right text-blue-900">{{ number_format($closingBalance, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        {{-- Summary Footer --}}
        @if($transactions->count() > 0)
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                <div>
                    <p class="text-xs text-gray-600">Transactions</p>
                    <p class="font-semibold text-gray-900">{{ $transactions->count() }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-600">Total Debits</p>
                    <p class="font-semibold text-red-600">{{ number_format($totalDebits, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-600">Total Credits</p>
                    <p class="font-semibold text-green-600">{{ number_format($totalCredits, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-600">Net Change</p>
                    <p class="font-semibold @if($netChange < 0) text-red-600 @else text-green-600 @endif">
                        {{ $netChange >= 0 ? '+' : '' }}{{ number_format($netChange, 2) }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-600">Final Balance</p>
                    <p class="font-semibold text-blue-900">{{ number_format($closingBalance, 2) }}</p>
                </div>
            </div>
        </div>
        @endif
        
        {{-- Pagination --}}
        @if($transactions->hasPages())
        <div class="px-4 py-2 border-t border-gray-200">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
    
   
</div>