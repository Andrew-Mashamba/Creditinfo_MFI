<div class="space-y-8">
    {{-- Enhanced Reconciliation Hero Section --}}
    <div class="bg-gradient-to-br from-gray-500 via-gray-600 to-gray-700 rounded-3xl p-8 text-white shadow-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold mb-2">Reconciliation Center</h3>
                <p class="text-gray-100 text-lg">End-of-day reconciliation and cash balancing operations</p>
            </div>
            <div class="text-right">
                <div class="bg-white bg-opacity-20 rounded-2xl p-6 backdrop-blur-sm">
                    <h4 class="text-lg font-semibold mb-2">Open Tills</h4>
                    <p class="text-4xl font-bold">{{ count($openTills ?? []) }}</p>
                    <p class="text-gray-100 text-sm">Require reconciliation</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Reconciliation Status Dashboard --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
            <div class="flex items-center">
                <div class="p-3 bg-blue-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-700">Reconciled Today</p>
                    <p class="text-2xl font-bold text-blue-900">{{ count($reconciledToday ?? []) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl p-6 border border-orange-200">
            <div class="flex items-center">
                <div class="p-3 bg-orange-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-orange-700">Pending</p>
                    <p class="text-2xl font-bold text-orange-900">{{ count($pendingReconciliation ?? []) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-6 border border-red-200">
            <div class="flex items-center">
                <div class="p-3 bg-red-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-red-700">Variances</p>
                    <p class="text-2xl font-bold text-red-900">{{ count($variancesFound ?? []) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border border-green-200">
            <div class="flex items-center">
                <div class="p-3 bg-green-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-700">Total Variance</p>
                    <p class="text-2xl font-bold text-green-900">TZS {{ number_format($totalVariance ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Till Reconciliation Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        {{-- Quick Reconciliation --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Quick Till Reconciliation</h3>
            </div>
            
            <form wire:submit.prevent="performQuickReconciliation" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Select Till</label>
                    <select wire:model="selectedTillForRecon" class="w-full rounded-xl border-2 border-green-200 shadow-sm focus:border-green-500 focus:ring-4 focus:ring-green-200 transition-all duration-200 p-4 text-lg">
                        <option value="">Choose a Till</option>
                        @foreach($openTills ?? [] as $till)
                            <option value="{{ $till->id }}">{{ $till->name }} - {{ $till->assigned_user->name ?? 'Unassigned' }}</option>
                        @endforeach
                    </select>
                    @error('selectedTillForRecon') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                </div>
                
                @if($selectedTillForRecon ?? null)
                    <div class="bg-blue-50 rounded-2xl p-6 border border-blue-200">
                        <h4 class="text-lg font-bold text-blue-900 mb-4">Till Details</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-blue-700 font-medium">Opening Balance:</p>
                                <p class="text-blue-900 font-bold text-lg">TZS {{ number_format($selectedTillDetails['opening_balance'] ?? 0) }}</p>
                            </div>
                            <div>
                                <p class="text-blue-700 font-medium">System Balance:</p>
                                <p class="text-blue-900 font-bold text-lg">TZS {{ number_format($selectedTillDetails['system_balance'] ?? 0) }}</p>
                            </div>
                            <div>
                                <p class="text-blue-700 font-medium">Transactions:</p>
                                <p class="text-blue-900 font-bold">{{ $selectedTillDetails['transaction_count'] ?? 0 }}</p>
                            </div>
                            <div>
                                <p class="text-blue-700 font-medium">Last Transaction:</p>
                                <p class="text-blue-900">{{ $selectedTillDetails['last_transaction_time'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Physical Cash Count (TZS)</label>
                    <input type="number" wire:model="physicalCashCount" step="1" min="0" 
                           class="w-full rounded-xl border-2 border-green-200 shadow-sm focus:border-green-500 focus:ring-4 focus:ring-green-200 transition-all duration-200 p-4 text-lg" 
                           placeholder="Enter actual cash count">
                    @error('physicalCashCount') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Counted By</label>
                        <input type="text" wire:model="countedBy" 
                               class="w-full rounded-xl border-2 border-green-200 shadow-sm focus:border-green-500 focus:ring-4 focus:ring-green-200 transition-all duration-200 p-4" 
                               placeholder="Teller name">
                        @error('countedBy') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Witnessed By</label>
                        <input type="text" wire:model="witnessedBy" 
                               class="w-full rounded-xl border-2 border-green-200 shadow-sm focus:border-green-500 focus:ring-4 focus:ring-green-200 transition-all duration-200 p-4" 
                               placeholder="Supervisor name">
                        @error('witnessedBy') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Reconciliation Notes</label>
                    <textarea wire:model="reconciliationNotes" rows="4" 
                              class="w-full rounded-xl border-2 border-green-200 shadow-sm focus:border-green-500 focus:ring-4 focus:ring-green-200 transition-all duration-200 p-4" 
                              placeholder="Any discrepancies or notes about the reconciliation"></textarea>
                </div>
                
                @if(($physicalCashCount ?? null) && ($selectedTillDetails ?? null) && isset(($selectedTillDetails ?? [])['system_balance']))
                    @php
                        $variance = ($physicalCashCount ?? 0) - (($selectedTillDetails ?? [])['system_balance'] ?? 0);
                    @endphp
                    <div class="bg-{{ $variance == 0 ? 'green' : 'red' }}-50 rounded-2xl p-6 border border-{{ $variance == 0 ? 'green' : 'red' }}-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-{{ $variance == 0 ? 'green' : 'red' }}-500 rounded-lg mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($variance == 0)
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    @endif
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-{{ $variance == 0 ? 'green' : 'red' }}-900">
                                    {{ $variance == 0 ? 'Perfect Balance!' : 'Variance Detected' }}
                                </h4>
                                <p class="text-{{ $variance == 0 ? 'green' : 'red' }}-800">
                                    {{ $variance == 0 ? 'Physical count matches system balance' : 'Variance: TZS ' . number_format(abs($variance)) . ' (' . ($variance > 0 ? 'Overage' : 'Shortage') . ')' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white py-4 px-6 rounded-xl hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-4 focus:ring-green-200 transition-all duration-200 font-bold text-lg shadow-lg">
                    Complete Reconciliation
                </button>
            </form>
        </div>

        {{-- Denomination Breakdown --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Denomination Breakdown</h3>
            </div>
            
            <div class="space-y-4">
                @foreach($denominations ?? [] as $denomination)
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex justify-between items-center mb-2">
                            <div class="flex items-center">
                                <div class="w-12 h-8 bg-gradient-to-r from-green-400 to-green-500 rounded-lg flex items-center justify-center mr-3">
                                    <span class="text-white font-bold text-xs">{{ $denomination['value'] }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">TZS {{ number_format($denomination['value']) }}</p>
                                    <p class="text-sm text-gray-600">{{ $denomination['type'] }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <input type="number" wire:model="denominationCounts.{{ $denomination['id'] }}" 
                                       class="w-20 rounded-lg border-2 border-blue-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 p-2 text-center" 
                                       placeholder="0" min="0">
                            </div>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Count × Value</span>
                            <span class="font-bold text-blue-600">
                                TZS {{ number_format((($denominationCounts ?? [])[$denomination['id']] ?? 0) * $denomination['value']) }}
                            </span>
                        </div>
                    </div>
                @endforeach
                
                <div class="border-t-2 border-gray-200 pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-900">Total Count:</span>
                        <span class="text-xl font-bold text-green-600">
                            @php
                                $denoms = $denominations ?? collect();
                                $total = collect($denominationCounts ?? [])->sum(function($count, $denomId) use ($denoms) {
                                    return $count * ($denoms->firstWhere('id', $denomId)['value'] ?? 0);
                                });
                            @endphp
                            TZS {{ number_format($total) }}
                        </span>
                    </div>
                </div>
                
                <button wire:click="updatePhysicalCountFromDenominations" 
                        class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-6 rounded-xl hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 transition-all duration-200 font-bold shadow-lg">
                    Update Physical Count
                </button>
            </div>
        </div>
    </div>

    {{-- Reconciliation History --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Recent Reconciliations</h3>
            </div>
            <button wire:click="exportReconciliationHistory" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-6 py-3 rounded-xl hover:from-purple-600 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-200 transition-all duration-200 font-bold shadow-lg">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left py-4 px-6 text-sm font-bold text-gray-700">Date & Time</th>
                        <th class="text-left py-4 px-6 text-sm font-bold text-gray-700">Till</th>
                        <th class="text-left py-4 px-6 text-sm font-bold text-gray-700">Teller</th>
                        <th class="text-right py-4 px-6 text-sm font-bold text-gray-700">System Balance</th>
                        <th class="text-right py-4 px-6 text-sm font-bold text-gray-700">Physical Count</th>
                        <th class="text-right py-4 px-6 text-sm font-bold text-gray-700">Variance</th>
                        <th class="text-center py-4 px-6 text-sm font-bold text-gray-700">Status</th>
                        <th class="text-center py-4 px-6 text-sm font-bold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reconciliationHistory ?? [] as $reconciliation)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-all duration-200">
                            <td class="py-4 px-6">
                                <div class="text-sm">
                                    <p class="font-medium text-gray-900">{{ $reconciliation->created_at->format('M d, Y') }}</p>
                                    <p class="text-gray-600">{{ $reconciliation->created_at->format('H:i:s') }}</p>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-sm">
                                    <p class="font-medium text-gray-900">{{ $reconciliation->till->name }}</p>
                                    <p class="text-gray-600">{{ $reconciliation->till->code }}</p>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-sm">
                                    <p class="font-medium text-gray-900">{{ $reconciliation->teller->name ?? 'Unknown' }}</p>
                                    <p class="text-gray-600">{{ $reconciliation->witnessed_by ?? 'N/A' }}</p>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <p class="font-bold text-blue-600">TZS {{ number_format($reconciliation->system_balance) }}</p>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <p class="font-bold text-green-600">TZS {{ number_format($reconciliation->physical_count) }}</p>
                            </td>
                            <td class="py-4 px-6 text-right">
                                @php
                                    $variance = $reconciliation->physical_count - $reconciliation->system_balance;
                                @endphp
                                <p class="font-bold {{ $variance == 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $variance == 0 ? 'Perfect' : 'TZS ' . number_format(abs($variance)) }}
                                </p>
                                @if($variance != 0)
                                    <p class="text-xs text-gray-600">{{ $variance > 0 ? 'Overage' : 'Shortage' }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full
                                    {{ $reconciliation->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $reconciliation->status === 'pending_approval' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $reconciliation->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $reconciliation->status)) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button wire:click="viewReconciliation({{ $reconciliation->id }})" 
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-lg transition-all duration-200">
                                        View
                                    </button>
                                    @if($reconciliation->status === 'pending_approval')
                                        <button wire:click="approveReconciliation({{ $reconciliation->id }})" 
                                                class="text-green-600 hover:text-green-800 text-sm font-medium bg-green-50 hover:bg-green-100 px-3 py-1 rounded-lg transition-all duration-200">
                                            Approve
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <h3 class="text-xl font-bold text-gray-600 mb-2">No Reconciliations Yet</h3>
                                    <p class="text-gray-500">Start by reconciling your first till</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Variance Analysis --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Variance Analysis</h3>
            </div>
            
            <div class="space-y-4">
                @foreach($varianceAnalysis ?? [] as $analysis)
                    <div class="bg-gradient-to-r from-red-50 to-orange-50 rounded-2xl p-4 border border-red-200">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="font-bold text-red-900">{{ $analysis['period'] }}</h4>
                            <span class="text-sm font-bold text-red-600">{{ $analysis['count'] }} occurrences</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-red-700">Total Variance:</p>
                                <p class="font-bold text-red-900">TZS {{ number_format($analysis['total_variance']) }}</p>
                            </div>
                            <div>
                                <p class="text-red-700">Avg Variance:</p>
                                <p class="font-bold text-red-900">TZS {{ number_format($analysis['avg_variance']) }}</p>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-t border-red-200">
                            <p class="text-xs text-red-600">{{ $analysis['trend'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Reconciliation Trends</h3>
            </div>
            
            <div class="space-y-4">
                @foreach($reconciliationTrends ?? [] as $trend)
                    <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-2xl p-4 border border-green-200">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="font-bold text-green-900">{{ $trend['date'] }}</h4>
                            <span class="text-sm font-bold text-green-600">{{ $trend['count'] }} reconciliations</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-green-700">Perfect Matches:</p>
                                <p class="font-bold text-green-900">{{ $trend['perfect_matches'] }}</p>
                            </div>
                            <div>
                                <p class="text-green-700">With Variances:</p>
                                <p class="font-bold text-green-900">{{ $trend['with_variances'] }}</p>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-t border-green-200">
                            <div class="flex justify-between">
                                <p class="text-xs text-green-600">Success Rate:</p>
                                <p class="text-xs font-bold text-green-800">{{ $trend['success_rate'] }}%</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div> 