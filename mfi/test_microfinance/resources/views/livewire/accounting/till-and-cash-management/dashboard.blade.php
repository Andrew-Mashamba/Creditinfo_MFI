{{-- Enhanced Till Dashboard --}}
<div class="space-y-6">
    {{-- Till Status Overview Cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- My Till Status Card --}}
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 border border-red-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-red-900">My Till Status</h3>
                @if($currentTill)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $tillStatus === 'open' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        <div class="w-2 h-2 rounded-full mr-2 {{ $tillStatus === 'open' ? 'bg-green-500' : 'bg-gray-500' }}"></div>
                        {{ ucfirst($tillStatus) }}
                    </span>
                @endif
            </div>

            @if($currentTill)
                <div class="space-y-4">
                    {{-- Till Information Grid --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white rounded-lg p-3 shadow-sm">
                            <p class="text-sm font-medium text-gray-500">Current Balance</p>
                            <p class="text-2xl font-bold text-red-900">${{ number_format($currentBalance, 2) }}</p>
                            <p class="text-xs text-green-600 mt-1">
                                @if($currentTill->opening_balance > 0)
                                    {{ $currentBalance > $currentTill->opening_balance ? '+' : '' }}${{ number_format($currentBalance - $currentTill->opening_balance, 2) }} from opening
                                @endif
                            </p>
                        </div>
                        <div class="bg-white rounded-lg p-3 shadow-sm">
                            <p class="text-sm font-medium text-gray-500">Till Information</p>
                            <p class="text-lg font-semibold text-red-700">{{ $currentTill->name ?? 'Till #' . $currentTill->id }}</p>
                            <p class="text-xs text-gray-600 mt-1">Number: {{ $currentTill->till_number ?? $currentTill->id }}</p>
                        </div>
                    </div>

                    {{-- Till Actions --}}
                    @if($tillStatus === 'closed')
                        <div class="bg-white rounded-lg p-4 border-2 border-dashed border-red-300">
                            <h4 class="font-medium text-red-900 mb-2 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                </svg>
                                Open Till to Start Operations
                            </h4>
                            <p class="text-sm text-red-700 mb-3">Enter your opening balance to start the day's transactions</p>
                            <div class="flex items-center space-x-3">
                                <div class="flex-1">
                                    <input type="number" wire:model="openingBalance" step="0.01" min="0" 
                                           placeholder="Opening Balance ($)" 
                                           class="w-full border border-red-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                    @error('openingBalance') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <button wire:click="openTill" 
                                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>Open Till</span>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-lg p-4 border border-green-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-green-100 rounded-lg">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-green-900">Till is Operational</h4>
                                        <p class="text-sm text-green-700">
                                            Opened at {{ $currentTill->opened_at->format('H:i') }} 
                                            @if($currentTill->opened_by) by {{ $currentTill->openedBy->name ?? 'System' }} @endif
                                        </p>
                                    </div>
                                </div>
                                <button wire:click="closeTill" 
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    <span>Close Till</span>
                                </button>
                            </div>
                            
                            {{-- Till Limits Information --}}
                            <div class="mt-3 pt-3 border-t border-green-100">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-green-700">Minimum Limit:</span>
                                        <span class="font-semibold">${{ number_format($currentTill->minimum_limit ?? 0, 2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-green-700">Maximum Limit:</span>
                                        <span class="font-semibold">${{ number_format($currentTill->maximum_limit ?? 0, 2) }}</span>
                                    </div>
                                </div>
                                
                                {{-- Balance Warning --}}
                                @if($currentBalance > ($currentTill->maximum_limit ?? 500000))
                                    <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <p class="text-xs text-yellow-800">⚠️ Till balance exceeds maximum limit. Consider vault transfer.</p>
                                    </div>
                                @elseif($currentBalance < ($currentTill->minimum_limit ?? 10000))
                                    <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded-lg">
                                        <p class="text-xs text-red-800">⚠️ Till balance below minimum limit. Replenishment recommended.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8">
                    <div class="p-4 bg-white rounded-lg inline-block shadow-sm">
                        <svg class="w-12 h-12 text-red-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-red-900 mb-2">No Till Assigned</h3>
                        <p class="text-red-700 mb-4">Contact your supervisor to get a till assigned to start operations.</p>
                        
                        @if($isSupervisor)
                            <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                Assign Till
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Quick Actions Card --}}
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
            <h3 class="text-lg font-semibold text-green-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Quick Actions
            </h3>
            <div class="grid grid-cols-2 gap-3">
                {{-- New Transaction --}}
                <button wire:click="showTransactionModal" 
                        @disabled($tillStatus !== 'open')
                        class="flex flex-col items-center justify-center p-4 border-2 border-dashed rounded-lg transition-all duration-200
                            {{ $tillStatus === 'open' ? 'border-green-300 text-green-700 hover:border-green-400 hover:bg-white bg-white/50' : 'border-gray-200 text-gray-400 cursor-not-allowed bg-gray-50' }}">
                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="text-sm font-medium">New Transaction</span>
                    <span class="text-xs opacity-75">Process deposit/withdrawal</span>
                </button>

                {{-- View History --}}
                <button wire:click="$set('activeTab', 'transactions')" 
                        class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-red-300 text-red-700 hover:border-red-400 hover:bg-white bg-white/50 rounded-lg transition-all duration-200">
                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span class="text-sm font-medium">View History</span>
                    <span class="text-xs opacity-75">Transaction records</span>
                </button>

                {{-- Vault Transfer (Supervisor) --}}
                @if($isSupervisor)
                    <button wire:click="showStrongroomModal" 
                            class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-purple-300 text-purple-700 hover:border-purple-400 hover:bg-white bg-white/50 rounded-lg transition-all duration-200">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <span class="text-sm font-medium">Vault Transfer</span>
                        <span class="text-xs opacity-75">Strongroom operations</span>
                    </button>
                @endif

                {{-- Reconciliation --}}
                <button wire:click="$set('activeTab', 'reconciliation')" 
                        class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-yellow-300 text-yellow-700 hover:border-yellow-400 hover:bg-white bg-white/50 rounded-lg transition-all duration-200">
                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">Reconciliation</span>
                    <span class="text-xs opacity-75">Balance verification</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Today's Performance Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Deposits Today</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ $recentTransactions->where('type', 'deposit')->where('created_at', '>=', today())->count() }}
                    </p>
                    <p class="text-sm text-green-600">
                        ${{ number_format($recentTransactions->where('type', 'deposit')->where('created_at', '>=', today())->sum('amount'), 2) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Withdrawals Today</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ $recentTransactions->where('type', 'withdrawal')->where('created_at', '>=', today())->count() }}
                    </p>
                    <p class="text-sm text-red-600">
                        ${{ number_format($recentTransactions->where('type', 'withdrawal')->where('created_at', '>=', today())->sum('amount'), 2) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Vault Transfers</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ $cashMovements->where('created_at', '>=', today())->count() }}
                    </p>
                    <p class="text-sm text-red-600">Today</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Net Cash Flow</p>
                    @php
                        $depositsToday = $recentTransactions->where('type', 'deposit')->where('created_at', '>=', today())->sum('amount');
                        $withdrawalsToday = $recentTransactions->where('type', 'withdrawal')->where('created_at', '>=', today())->sum('amount');
                        $netFlow = $depositsToday - $withdrawalsToday;
                    @endphp
                    <p class="text-2xl font-bold {{ $netFlow >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $netFlow >= 0 ? '+' : '-' }}${{ number_format(abs($netFlow), 2) }}
                    </p>
                    <p class="text-sm {{ $netFlow >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $netFlow >= 0 ? 'Positive' : 'Negative' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Recent Activity
            </h3>
            <button wire:click="$set('activeTab', 'transactions')" 
                    class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors duration-200">
                View All →
            </button>
        </div>
        
        <div class="space-y-3">
            @forelse($recentTransactions->take(5) as $transaction)
                <div class="flex items-center justify-between py-3 px-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 rounded-lg {{ in_array($transaction->type, ['deposit', 'transfer_from_vault']) ? 'bg-green-100' : 'bg-red-100' }}">
                            <svg class="w-4 h-4 {{ in_array($transaction->type, ['deposit', 'transfer_from_vault']) ? 'text-green-600' : 'text-red-600' }}" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if(in_array($transaction->type, ['deposit', 'transfer_from_vault']))
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                @endif
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $transaction->type)) }}</p>
                            <p class="text-sm text-gray-500">{{ Str::limit($transaction->description, 40) }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold {{ in_array($transaction->type, ['deposit', 'transfer_from_vault']) ? 'text-green-600' : 'text-red-600' }}">
                            {{ in_array($transaction->type, ['deposit', 'transfer_from_vault']) ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                        </p>
                        <p class="text-sm text-gray-500">{{ $transaction->created_at->format('H:i') }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Recent Activity</h3>
                    <p class="text-gray-500">Transactions will appear here once you start processing.</p>
                    @if($tillStatus === 'open')
                        <button wire:click="showTransactionModal" 
                                class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Process First Transaction
                        </button>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    {{-- All Tills Overview (Supervisor Only) --}}
    @if($isSupervisor && $allTills->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                All Tills Overview
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($allTills as $till)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-900">{{ $till->name ?? 'Till #' . $till->id }}</h4>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                {{ $till->status === 'open' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($till->status) }}
                            </span>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Balance:</span>
                                <span class="font-semibold text-gray-900">${{ number_format($till->current_balance, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Teller:</span>
                                <span class="text-gray-900">{{ $till->teller->user->name ?? 'Unassigned' }}</span>
                            </div>
                            @if($till->status === 'open')
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Opened:</span>
                                    <span class="text-gray-900">{{ $till->opened_at->format('H:i') }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <div class="flex space-x-2">
                                <button class="flex-1 px-3 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700 transition-colors">
                                    View Details
                                </button>
                                @if($till->status === 'open')
                                    <button class="px-3 py-1 bg-yellow-600 text-white rounded text-xs hover:bg-yellow-700 transition-colors">
                                        Force Close
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div> 