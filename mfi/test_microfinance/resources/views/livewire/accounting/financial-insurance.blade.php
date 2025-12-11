<div>
    {{-- Header Section --}}
    <div class="p-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Financial Insurance Management</h2>
            <button wire:click="openCreateModal" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-plus mr-2"></i> New Insurance Policy
            </button>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Policies</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $totalPolicies }}</p>
                    </div>
                    <div class="text-red-500">
                        <i class="fas fa-file-contract text-3xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Active Policies</p>
                        <p class="text-2xl font-bold text-green-600">{{ $activePolicies }}</p>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-shield-alt text-3xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Coverage</p>
                        <p class="text-xl font-bold text-gray-800">{{ number_format($totalCoverage, 2) }}</p>
                    </div>
                    <div class="text-purple-500">
                        <i class="fas fa-umbrella text-3xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Claims Ratio</p>
                        <p class="text-2xl font-bold {{ $claimsRatio > 75 ? 'text-red-600' : 'text-gray-800' }}">
                            {{ number_format($claimsRatio, 1) }}%
                        </p>
                    </div>
                    <div class="text-orange-500">
                        <i class="fas fa-percentage text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Statistics Row --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm">Premiums Paid (Year)</p>
                <p class="text-xl font-bold text-gray-800">{{ number_format($totalPremiumsPaid, 2) }}</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm">Claims Paid (Year)</p>
                <p class="text-xl font-bold text-gray-800">{{ number_format($totalClaimsPaid, 2) }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm">Upcoming Renewals</p>
                <p class="text-xl font-bold text-yellow-600">{{ count($upcomingRenewals) }}</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button wire:click="$set('activeTab', 'overview')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'overview' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Overview
                </button>
                <button wire:click="$set('activeTab', 'policies')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'policies' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Insurance Policies
                </button>
                <button wire:click="$set('activeTab', 'claims')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'claims' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Claims
                </button>
                <button wire:click="$set('activeTab', 'premiums')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'premiums' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Premium Payments
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="mt-6">
            @if($activeTab === 'overview')
                {{-- Overview Tab --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Upcoming Renewals --}}
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b">
                            <h3 class="text-lg font-semibold">Upcoming Renewals (30 days)</h3>
                        </div>
                        <div class="p-4">
                            @if(count($upcomingRenewals) > 0)
                                <div class="space-y-3">
                                    @foreach($upcomingRenewals as $renewal)
                                        <div class="flex justify-between items-center p-3 bg-yellow-50 rounded">
                                            <div>
                                                <p class="font-medium">{{ $renewal->policy_number }}</p>
                                                <p class="text-sm text-gray-600">{{ $renewal->insurer_name }}</p>
                                                <p class="text-xs text-gray-500">Expires: {{ \Carbon\Carbon::parse($renewal->policy_end_date)->format('d/m/Y') }}</p>
                                            </div>
                                            <button wire:click="renew({{ $renewal->id }})" 
                                                class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">
                                                Renew
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-center py-4">No upcoming renewals</p>
                            @endif
                        </div>
                    </div>

                    {{-- Recent Claims --}}
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b">
                            <h3 class="text-lg font-semibold">Recent Claims</h3>
                        </div>
                        <div class="p-4">
                            @if($recentClaims->count() > 0)
                                <div class="space-y-3">
                                    @foreach($recentClaims as $claim)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                            <div>
                                                <p class="font-medium">{{ $claim->claim_number }}</p>
                                                <p class="text-sm text-gray-600">{{ $claim->policy_number }}</p>
                                                <p class="text-xs text-gray-500">Amount: {{ number_format($claim->claim_amount, 2) }}</p>
                                            </div>
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                {{ $claim->claim_status === 'paid' ? 'bg-green-100 text-green-800' : 
                                                   ($claim->claim_status === 'approved' ? 'bg-red-100 text-red-800' : 
                                                   ($claim->claim_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                                {{ ucfirst($claim->claim_status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-center py-4">No recent claims</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Insurance Types Distribution --}}
                <div class="mt-6 bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Insurance Types Distribution</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @php
                            $typeDistribution = $insurancePolicies->groupBy('insurance_type')->map->count();
                        @endphp
                        @foreach($predefinedTypes as $key => $type)
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-800">{{ $typeDistribution[$key] ?? 0 }}</p>
                                <p class="text-sm text-gray-600">{{ $type }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

            @elseif($activeTab === 'policies')
                {{-- Insurance Policies Tab --}}
                <div class="bg-white rounded-lg shadow">
                    {{-- Filters --}}
                    <div class="p-4 border-b">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" wire:model.debounce.300ms="search" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                    placeholder="Policy number, insurer...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <select wire:model="typeFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="all">All Types</option>
                                    @foreach($predefinedTypes as $key => $type)
                                        <option value="{{ $key }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select wire:model="statusFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="all">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="expired">Expired</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                                <div class="flex space-x-2">
                                    <input type="date" wire:model="dateFrom" class="w-1/2 rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <input type="date" wire:model="dateTo" class="w-1/2 rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Policy Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Insurer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Insured Entity</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Coverage</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Premium</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($insurancePolicies as $policy)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            {{ $policy->policy_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $predefinedTypes[$policy->insurance_type] ?? ucwords(str_replace('_', ' ', $policy->insurance_type)) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            {{ $policy->insurer_name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            {{ $policy->insured_entity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                            {{ number_format($policy->coverage_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                            {{ number_format($policy->premium_amount, 2) }}
                                            <span class="text-xs text-gray-500">/ {{ $policy->premium_frequency }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-center">
                                            {{ \Carbon\Carbon::parse($policy->policy_start_date)->format('d/m/y') }} -
                                            {{ \Carbon\Carbon::parse($policy->policy_end_date)->format('d/m/y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @php
                                                $isExpired = \Carbon\Carbon::parse($policy->policy_end_date)->isPast();
                                                $statusClass = $policy->status === 'active' && !$isExpired ? 'bg-green-100 text-green-800' : 
                                                              ($policy->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                {{ $isExpired && $policy->status === 'active' ? 'Expired' : ucfirst($policy->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <div class="flex justify-center space-x-2">
                                                <button wire:click="edit({{ $policy->id }})" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button wire:click="openClaimModal({{ $policy->id }})" class="text-green-600 hover:text-green-900">
                                                    <i class="fas fa-file-medical"></i>
                                                </button>
                                                <button wire:click="processPremiumPayment({{ $policy->id }})" class="text-purple-600 hover:text-purple-900">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </button>
                                                @if(\Carbon\Carbon::parse($policy->policy_end_date)->diffInDays(now()) <= 30)
                                                    <button wire:click="renew({{ $policy->id }})" class="text-orange-600 hover:text-orange-900">
                                                        <i class="fas fa-sync"></i>
                                                    </button>
                                                @endif
                                                <button wire:click="delete({{ $policy->id }})" 
                                                    onclick="return confirm('Are you sure you want to delete this policy?')"
                                                    class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                            No insurance policies found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="px-6 py-4">
                        {{ $insurancePolicies->links() }}
                    </div>
                </div>

            @elseif($activeTab === 'claims')
                {{-- Claims Tab --}}
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-semibold">Insurance Claims</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claim #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Policy</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Insurer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Claim Amount</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Settlement</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentClaims as $claim)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            {{ $claim->claim_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $claim->policy_number }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            {{ $claim->insurer_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ \Carbon\Carbon::parse($claim->claim_date)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            {{ Str::limit($claim->claim_reason, 30) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                            {{ number_format($claim->claim_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                            {{ $claim->settlement_amount ? number_format($claim->settlement_amount, 2) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $claim->claim_status === 'paid' ? 'bg-green-100 text-green-800' : 
                                                   ($claim->claim_status === 'approved' ? 'bg-red-100 text-red-800' : 
                                                   ($claim->claim_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                                {{ ucfirst($claim->claim_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                            No claims found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @elseif($activeTab === 'premiums')
                {{-- Premium Payments Tab --}}
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Premium Payment Schedule</h3>
                        <div class="text-sm text-gray-600">
                            Total Premiums This Year: <span class="font-bold text-gray-800">{{ number_format($totalPremiumsPaid, 2) }}</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-500 text-center py-8">Premium payment schedule and history will be displayed here</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Create/Edit Policy Modal --}}
    @if($showCreateModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">{{ $editMode ? 'Edit' : 'New' }} Insurance Policy</h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Insurance Type --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Insurance Type <span class="text-red-500">*</span></label>
                            <select wire:model="insurance_type" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                <option value="">Select Type</option>
                                @foreach($predefinedTypes as $key => $type)
                                    <option value="{{ $key }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('insurance_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Policy Number --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Policy Number <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="policy_number" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="AUTO-GENERATED" {{ $editMode ? '' : 'readonly' }}>
                            @error('policy_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Insurer Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Insurer Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="insurer_name" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="Insurance Company Name">
                            @error('insurer_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Insurer Contact --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Insurer Contact</label>
                            <input type="text" wire:model="insurer_contact"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="Phone/Email">
                            @error('insurer_contact') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Coverage Amount --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Coverage Amount <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" wire:model="coverage_amount" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="0.00">
                            @error('coverage_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Premium Amount --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Premium Amount <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" wire:model="premium_amount" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="0.00">
                            @error('premium_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Premium Frequency --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Premium Frequency <span class="text-red-500">*</span></label>
                            <select wire:model="premium_frequency" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="semi_annually">Semi-Annually</option>
                                <option value="annually">Annually</option>
                            </select>
                            @error('premium_frequency') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Coverage Type --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Coverage Type</label>
                            <input type="text" wire:model="coverage_type"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="e.g., Comprehensive, Third Party">
                            @error('coverage_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Policy Start Date --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Policy Start Date <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="policy_start_date" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            @error('policy_start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Policy End Date --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Policy End Date <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="policy_end_date" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            @error('policy_end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Insured Entity --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Insured Entity <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="insured_entity" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="Person/Property/Asset being insured">
                            @error('insured_entity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Insured Entity ID --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Entity ID/Reference</label>
                            <input type="text" wire:model="insured_entity_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="Asset ID, Member ID, etc.">
                            @error('insured_entity_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Beneficiary --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Beneficiary</label>
                            <input type="text" wire:model="beneficiary"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="Beneficiary name">
                            @error('beneficiary') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Deductible --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deductible</label>
                            <input type="number" step="0.01" wire:model="deductible"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="0.00">
                            @error('deductible') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Co-payment Percentage --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Co-payment %</label>
                            <input type="number" step="0.01" wire:model="copayment_percentage"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="0.00">
                            @error('copayment_percentage') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select wire:model="status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Notes --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea wire:model="notes" rows="2"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="Additional notes..."></textarea>
                            @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Policy Document --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Policy Document</label>
                            <input type="file" wire:model="policy_document" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            @error('policy_document') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            @if($policy_document)
                                <p class="text-sm text-gray-600 mt-1">File: {{ $policy_document->getClientOriginalName() }}</p>
                            @endif
                        </div>

                        {{-- Account Selection - Corrected Flow --}}
                        <div class="md:col-span-2 bg-gray-50 rounded-lg border border-gray-200 p-4 mt-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Account Selection</h3>
                            <p class="text-sm text-gray-600 mb-4">Select where to create the insurance account and the other account for double-entry posting</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="parent_account_number" class="block text-sm font-medium text-gray-700 mb-2">
                                        Parent Account (Create Insurance Under) *
                                    </label>
                                    <select wire:model="parent_account_number" id="parent_account_number" 
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" required>
                                        <option value="">-- Select Parent Account --</option>
                                        @foreach($parentAccounts as $account)
                                            <option value="{{ $account->account_number }}">
                                                {{ $account->account_number }} - {{ $account->account_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">New insurance account will be created under this parent</p>
                                    @error('parent_account_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="other_account_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Other Account (Cash/Bank) *
                                    </label>
                                    <select wire:model="other_account_id" id="other_account_id" 
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" required>
                                        <option value="">-- Select Cash/Bank Account --</option>
                                        @foreach($otherAccounts as $account)
                                            <option value="{{ $account->internal_mirror_account_number }}">
                                                {{ $account->bank_name }} - {{ $account->account_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Account to be credited (Cash/Bank payment)</p>
                                    @error('other_account_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" wire:click="$set('showCreateModal', false)"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            {{ $editMode ? 'Update' : 'Create' }} Policy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Claim Modal --}}
    @if($showClaimModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Submit Insurance Claim</h3>
                    <button wire:click="$set('showClaimModal', false)" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="submitClaim">
                    <div class="space-y-4">
                        {{-- Claim Number --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Claim Number</label>
                            <input type="text" wire:model="claim_number" readonly
                                class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                        </div>

                        {{-- Claim Date --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Claim Date <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="claim_date" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            @error('claim_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Claim Amount --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Claim Amount <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" wire:model="claim_amount" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="0.00">
                            @error('claim_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Claim Reason --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Claim Reason <span class="text-red-500">*</span></label>
                            <textarea wire:model="claim_reason" rows="3" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="Describe the reason for the claim..."></textarea>
                            @error('claim_reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Claim Notes --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                            <textarea wire:model="claim_notes" rows="2"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="Any additional information..."></textarea>
                        </div>

                        {{-- Supporting Documents --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Supporting Documents</label>
                            <input type="file" wire:model="claim_documents" multiple accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            @error('claim_documents.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            @if($claim_documents)
                                <p class="text-sm text-gray-600 mt-1">{{ count($claim_documents) }} file(s) selected</p>
                            @endif
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" wire:click="$set('showClaimModal', false)"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            Submit Claim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Success/Error Messages --}}
    @if (session()->has('message'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('error') }}
        </div>
    @endif
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize any required JavaScript
        window.addEventListener('alert', event => {
            if (event.detail.type === 'success') {
                // Show success notification
                console.log('Success:', event.detail.message);
            } else if (event.detail.type === 'error') {
                // Show error notification
                console.log('Error:', event.detail.message);
            }
        });
    });
</script>
@endpush