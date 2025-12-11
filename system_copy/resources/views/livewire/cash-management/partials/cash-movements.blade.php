<div class="space-y-8">
    {{-- Enhanced Cash Movements Hero Section --}}
    <div class="bg-gradient-to-br from-teal-500 via-teal-600 to-teal-700 rounded-3xl p-8 text-white shadow-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold mb-2">Cash Movements Tracker</h3>
                <p class="text-teal-100 text-lg">Real-time monitoring of all cash transactions and movements</p>
            </div>
            <div class="text-right">
                <div class="bg-white bg-opacity-20 rounded-2xl p-6 backdrop-blur-sm">
                    <h4 class="text-lg font-semibold mb-2">Today's Movements</h4>
                    <p class="text-4xl font-bold">{{ count($todayMovements ?? []) }}</p>
                    <p class="text-teal-100 text-sm">Total transactions</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Movement Stats Dashboard --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border border-green-200">
            <div class="flex items-center">
                <div class="p-3 bg-green-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-700">Total Inflow</p>
                    <p class="text-2xl font-bold text-green-900">TZS {{ number_format($totalInflow ?? 0) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-6 border border-red-200">
            <div class="flex items-center">
                <div class="p-3 bg-red-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-red-700">Total Outflow</p>
                    <p class="text-2xl font-bold text-red-900">TZS {{ number_format($totalOutflow ?? 0) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
            <div class="flex items-center">
                <div class="p-3 bg-blue-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-700">Net Movement</p>
                    <p class="text-2xl font-bold {{ (($totalInflow ?? 0) - ($totalOutflow ?? 0)) >= 0 ? 'text-green-900' : 'text-red-900' }}">
                        TZS {{ number_format(($totalInflow ?? 0) - ($totalOutflow ?? 0)) }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200">
            <div class="flex items-center">
                <div class="p-3 bg-purple-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-purple-700">Active Transfers</p>
                    <p class="text-2xl font-bold text-purple-900">{{ count($activeTransfers ?? []) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter and Search --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Filter & Search Movements</h3>
            <button wire:click="exportMovements" class="bg-gradient-to-r from-teal-500 to-teal-600 text-white px-6 py-3 rounded-xl hover:from-teal-600 hover:to-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-200 transition-all duration-200 font-bold shadow-lg">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Date Range</label>
                <select wire:model="movementDateRange" class="w-full rounded-xl border-2 border-teal-200 shadow-sm focus:border-teal-500 focus:ring-4 focus:ring-teal-200 transition-all duration-200 p-4">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="last_week">Last Week</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Movement Type</label>
                <select wire:model="movementType" class="w-full rounded-xl border-2 border-teal-200 shadow-sm focus:border-teal-500 focus:ring-4 focus:ring-teal-200 transition-all duration-200 p-4">
                    <option value="">All Types</option>
                    <option value="vault_to_till">Vault to Till</option>
                    <option value="till_to_vault">Till to Vault</option>
                    <option value="customer_deposit">Customer Deposit</option>
                    <option value="customer_withdrawal">Customer Withdrawal</option>
                    <option value="cit_transfer">CIT Transfer</option>
                    <option value="vault_replenishment">Vault Replenishment</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                <select wire:model="movementStatus" class="w-full rounded-xl border-2 border-teal-200 shadow-sm focus:border-teal-500 focus:ring-4 focus:ring-teal-200 transition-all duration-200 p-4">
                    <option value="">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                <input type="text" wire:model="searchTerm" 
                       class="w-full rounded-xl border-2 border-teal-200 shadow-sm focus:border-teal-500 focus:ring-4 focus:ring-teal-200 transition-all duration-200 p-4" 
                       placeholder="Search by reference, amount, or user">
            </div>
        </div>
    </div>

    {{-- Cash Movements List --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Recent Cash Movements</h3>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600">
                    Showing {{ count($movements ?? []) }} of {{ $totalMovements ?? 0 }} movements
                </div>
                <button wire:click="refreshMovements" class="text-teal-600 hover:text-teal-800 text-sm font-medium bg-teal-50 hover:bg-teal-100 px-4 py-2 rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left py-4 px-6 text-sm font-bold text-gray-700">Date & Time</th>
                        <th class="text-left py-4 px-6 text-sm font-bold text-gray-700">Reference</th>
                        <th class="text-left py-4 px-6 text-sm font-bold text-gray-700">Type</th>
                        <th class="text-left py-4 px-6 text-sm font-bold text-gray-700">From</th>
                        <th class="text-left py-4 px-6 text-sm font-bold text-gray-700">To</th>
                        <th class="text-right py-4 px-6 text-sm font-bold text-gray-700">Amount</th>
                        <th class="text-center py-4 px-6 text-sm font-bold text-gray-700">Status</th>
                        <th class="text-center py-4 px-6 text-sm font-bold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements ?? [] as $movement)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-all duration-200">
                            <td class="py-4 px-6">
                                <div class="text-sm">
                                    <p class="font-medium text-gray-900">{{ $movement->created_at->format('M d, Y') }}</p>
                                    <p class="text-gray-600">{{ $movement->created_at->format('H:i:s') }}</p>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-sm">
                                    <p class="font-mono text-gray-900">{{ $movement->reference_number }}</p>
                                    <p class="text-gray-600">{{ $movement->description ?? 'N/A' }}</p>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                    {{ $movement->type === 'vault_to_till' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $movement->type === 'till_to_vault' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $movement->type === 'customer_deposit' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $movement->type === 'customer_withdrawal' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $movement->type === 'cit_transfer' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $movement->type === 'vault_replenishment' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                    {{ !in_array($movement->type, ['vault_to_till', 'till_to_vault', 'customer_deposit', 'customer_withdrawal', 'cit_transfer', 'vault_replenishment']) ? 'bg-gray-100 text-gray-800' : '' }}
                                ">
                                    {{ ucwords(str_replace('_', ' ', $movement->type)) }}
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-sm">
                                    <p class="font-medium text-gray-900">{{ $movement->from_account_name ?? 'N/A' }}</p>
                                    <p class="text-gray-600 font-mono text-xs">{{ $movement->from_account_number ?? 'N/A' }}</p>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-sm">
                                    <p class="font-medium text-gray-900">{{ $movement->to_account_name ?? 'N/A' }}</p>
                                    <p class="text-gray-600 font-mono text-xs">{{ $movement->to_account_number ?? 'N/A' }}</p>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="text-sm">
                                    <p class="font-bold text-lg {{ $movement->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        TZS {{ number_format(abs($movement->amount)) }}
                                    </p>
                                    <p class="text-gray-600">{{ $movement->amount >= 0 ? '+' : '-' }}</p>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full
                                    {{ $movement->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $movement->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $movement->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $movement->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ ucfirst($movement->status) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button wire:click="viewMovement({{ $movement->id }})" 
                                            class="text-teal-600 hover:text-teal-800 text-sm font-medium bg-teal-50 hover:bg-teal-100 px-3 py-1 rounded-lg transition-all duration-200">
                                        View
                                    </button>
                                    @if($movement->status === 'failed')
                                        <button wire:click="retryMovement({{ $movement->id }})" 
                                                class="text-red-600 hover:text-red-800 text-sm font-medium bg-red-50 hover:bg-red-100 px-3 py-1 rounded-lg transition-all duration-200">
                                            Retry
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-20 h-20 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <h3 class="text-xl font-bold text-gray-600 mb-2">No Cash Movements Found</h3>
                                    <p class="text-gray-500">No movements match your current filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
                    @if(count($movements ?? []) > 0)
            <div class="mt-8 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing {{ $movements->firstItem() ?? 0 }} to {{ $movements->lastItem() ?? 0 }} of {{ $movements->total() ?? 0 }} results
                </div>
                <div>
                    {{ $movements->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- Movement Summary Charts --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Movement Volume by Type</h3>
            </div>
            
            <div class="space-y-4">
                @foreach($movementsByType ?? [] as $type => $data)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $data['color'] }}"></div>
                            <span class="font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $type)) }}</span>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-900">{{ $data['count'] }}</p>
                            <p class="text-sm text-gray-600">TZS {{ number_format($data['total']) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Movement Trends</h3>
            </div>
            
            <div class="space-y-4">
                @foreach($movementTrends ?? [] as $trend)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                        <div>
                            <p class="font-medium text-gray-900">{{ $trend['date'] }}</p>
                            <p class="text-sm text-gray-600">{{ $trend['movement_count'] }} movements</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-green-600">+TZS {{ number_format($trend['inflow']) }}</p>
                            <p class="font-bold text-red-600">-TZS {{ number_format($trend['outflow']) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div> 