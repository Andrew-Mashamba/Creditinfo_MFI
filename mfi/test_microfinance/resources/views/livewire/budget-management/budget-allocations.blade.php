{{-- Budget Allocations Management View --}}
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">Monthly Budget Allocations</h3>
        <div class="flex space-x-2">
            <button wire:click="openAllocationSetupModal" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Setup Allocations
            </button>
            <button wire:click="openAdvanceModal" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                Request Advance
            </button>
            <button wire:click="openSupplementaryModal" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                Request Supplementary
            </button>
        </div>
    </div>
    
    {{-- Year and Budget Selector --}}
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Budget</label>
                <select wire:model="selectedBudgetId" wire:change="loadAllocations" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Budget...</option>
                    @foreach($budgets as $budget)
                        <option value="{{ $budget->id }}">{{ $budget->budget_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Year</label>
                <select wire:model="selectedYear" wire:change="loadAllocations" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Rollover Policy</label>
                <select wire:model="rolloverPolicy" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" {{ !$selectedBudgetId ? 'disabled' : '' }}>
                    <option value="AUTOMATIC">Automatic</option>
                    <option value="APPROVAL_REQUIRED">Approval Required</option>
                    <option value="NO_ROLLOVER">No Rollover</option>
                </select>
            </div>
        </div>
    </div>
    
    @if($selectedBudgetId && $allocations)
        {{-- Annual Summary --}}
        <div class="bg-white p-4 rounded-lg shadow">
            <h4 class="text-md font-medium text-gray-900 mb-3">Annual Summary</h4>
            <div class="grid grid-cols-6 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Total Allocated</p>
                    <p class="text-lg font-semibold">{{ number_format($annualSummary['allocated'] ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Utilized</p>
                    <p class="text-lg font-semibold text-red-600">{{ number_format($annualSummary['utilized'] ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Available</p>
                    <p class="text-lg font-semibold text-green-600">{{ number_format($annualSummary['available'] ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Rollover</p>
                    <p class="text-lg font-semibold text-red-600">{{ number_format($annualSummary['rollover'] ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Advances</p>
                    <p class="text-lg font-semibold text-orange-600">{{ number_format($annualSummary['advances'] ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Supplementary</p>
                    <p class="text-lg font-semibold text-purple-600">{{ number_format($annualSummary['supplementary'] ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
        
        {{-- Monthly Allocations Grid --}}
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allocated</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rollover</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Advances</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfers</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplementary</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Available</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilized</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($allocations as $allocation)
                        @php
                            $isCurrentMonth = $allocation->period == now()->month && $allocation->year == now()->year;
                            $isPastMonth = ($allocation->year < now()->year) || ($allocation->year == now()->year && $allocation->period < now()->month);
                            $utilizationPercentage = $allocation->utilization_percentage;
                            $totalAvailable = $allocation->total_available;
                        @endphp
                        <tr class="{{ $isCurrentMonth ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ Carbon\Carbon::create($allocation->year, $allocation->period, 1)->format('F Y') }}
                                </div>
                                @if($isCurrentMonth)
                                    <span class="text-xs text-red-600">Current Period</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($allocation->allocated_amount, 2) }}
                                <div class="text-xs text-gray-500">{{ $allocation->percentage }}%</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                @if($allocation->rollover_amount > 0)
                                    +{{ number_format($allocation->rollover_amount, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600">
                                @if($allocation->advance_amount > 0)
                                    +{{ number_format($allocation->advance_amount, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($allocation->transferred_in > 0)
                                    <span class="text-green-600">+{{ number_format($allocation->transferred_in, 2) }}</span>
                                @endif
                                @if($allocation->transferred_out > 0)
                                    <span class="text-red-600">-{{ number_format($allocation->transferred_out, 2) }}</span>
                                @endif
                                @if($allocation->transferred_in == 0 && $allocation->transferred_out == 0)
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600">
                                @if($allocation->supplementary_amount > 0)
                                    +{{ number_format($allocation->supplementary_amount, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                {{ number_format($totalAvailable, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                {{ number_format($allocation->utilized_amount, 2) }}
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-{{ $utilizationPercentage > 90 ? 'red' : ($utilizationPercentage > 75 ? 'yellow' : 'green') }}-500 h-2 rounded-full" 
                                         style="width: {{ min($utilizationPercentage, 100) }}%"></div>
                                </div>
                                <span class="text-xs">{{ $utilizationPercentage }}%</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $allocation->available_amount > 0 ? 'text-green-600' : 'text-gray-500' }}">
                                {{ number_format($allocation->available_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($allocation->is_locked)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Locked
                                    </span>
                                @elseif($utilizationPercentage >= 100)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Over-utilized
                                    </span>
                                @elseif($utilizationPercentage >= 80)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Warning
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Normal
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    @if(!$allocation->is_locked)
                                        @if($allocation->available_amount > 0 && $allocation->canRollover() && $isPastMonth)
                                            <button wire:click="processRollover({{ $allocation->id }})" 
                                                    class="text-red-600 hover:text-red-900" title="Rollover">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                            </button>
                                        @endif
                                        
                                        @if($isCurrentMonth || !$isPastMonth)
                                            <button wire:click="openAdvanceModal({{ $allocation->id }})" 
                                                    class="text-orange-600 hover:text-orange-900" title="Request Advance">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                </svg>
                                            </button>
                                            
                                            <button wire:click="openSupplementaryModal({{ $allocation->id }})" 
                                                    class="text-purple-600 hover:text-purple-900" title="Request Supplementary">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    @endif
                                    
                                    <button wire:click="viewDetails({{ $allocation->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900" title="View Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Alerts Section --}}
        @if($budgetAlerts && $budgetAlerts->count() > 0)
            <div class="bg-white p-4 rounded-lg shadow">
                <h4 class="text-md font-medium text-gray-900 mb-3">Budget Alerts</h4>
                <div class="space-y-2">
                    @foreach($budgetAlerts as $alert)
                        <div class="p-3 rounded-lg {{ $alert->severity === 'CRITICAL' ? 'bg-red-50 border border-red-200' : ($alert->severity === 'WARNING' ? 'bg-yellow-50 border border-yellow-200' : 'bg-red-50 border border-red-200') }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h5 class="font-medium {{ $alert->severity === 'CRITICAL' ? 'text-red-800' : ($alert->severity === 'WARNING' ? 'text-yellow-800' : 'text-red-800') }}">
                                        {{ $alert->alert_title }}
                                    </h5>
                                    <p class="text-sm {{ $alert->severity === 'CRITICAL' ? 'text-red-600' : ($alert->severity === 'WARNING' ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $alert->alert_message }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $alert->created_at->diffForHumans() }}</p>
                                </div>
                                @if(!$alert->is_resolved)
                                    <button wire:click="resolveAlert({{ $alert->id }})" 
                                            class="text-sm text-gray-600 hover:text-gray-900">
                                        Resolve
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-8 bg-gray-50 rounded-lg">
            <p class="text-gray-500">Select a budget and year to view allocations</p>
        </div>
    @endif
</div>