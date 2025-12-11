<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Report Header --}}
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Member Details Report</h1>
                    <p class="mt-1 text-sm text-gray-500">Comprehensive member information and membership details</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button wire:click="exportReport('pdf')" 
                            wire:loading.attr="disabled"
                            wire:target="exportReport('pdf')"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading.remove wire:target="exportReport('pdf')" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="exportReport('pdf')">Export PDF</span>
                        <span wire:loading wire:target="exportReport('pdf')">Exporting PDF...</span>
                    </button>
                    <button wire:click="exportReport('excel')" 
                            wire:loading.attr="disabled"
                            wire:target="exportReport('excel')"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading.remove wire:target="exportReport('excel')" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="exportReport('excel')">Export Excel</span>
                        <span wire:loading wire:target="exportReport('excel')">Exporting Excel...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Report Filters</h3>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Member Selection</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" wire:model="client_type" value="ALL" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">All Members</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" wire:model="client_type" value="MULTIPLE" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Selected Members</span>
                        </label>
                    </div>
                </div>
                
                @if($client_type === 'MULTIPLE')
                <div class="md:col-span-2">
                    <label for="custome_client_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Member Numbers
                    </label>
                    <input type="text" 
                           wire:model="custome_client_number" 
                           placeholder="e.g., 0001,0002,0003"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Enter member numbers separated by commas</p>
                </div>
                @else
                <div>
                    <label for="branchFilter" class="block text-sm font-medium text-gray-700 mb-2">
                        Branch Filter
                    </label>
                    <select wire:model="branchFilter" 
                            wire:change="loadMembers"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">
                        Status Filter
                    </label>
                    <select wire:model="statusFilter" 
                            wire:change="loadMembers"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Statuses</option>
                        <option value="ACTIVE">Active</option>
                        <option value="INACTIVE">Inactive</option>
                        <option value="PENDING">Pending</option>
                        <option value="SUSPENDED">Suspended</option>
                    </select>
                </div>
                @endif
                
                <div class="flex items-end">
                    <button wire:click="loadMembers" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Load Members
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $totalMembers }}</h4>
                    <p class="text-sm text-gray-500">Total Members</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $activeMembers }}</h4>
                    <p class="text-sm text-gray-500">Active Members</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $pendingMembers }}</h4>
                    <p class="text-sm text-gray-500">Pending Members</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $inactiveMembers }}</h4>
                    <p class="text-sm text-gray-500">Inactive Members</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $totalBranches }}</h4>
                    <p class="text-sm text-gray-500">Total Branches</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($totalSavings, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">Total Savings</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $membersWithLoans }}</h4>
                    <p class="text-sm text-gray-500">Members with Loans</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Members Table --}}
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Member Details</h3>
            <p class="text-sm text-gray-500">Comprehensive member information and membership details</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Savings Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($members as $index => $member)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $member->client_number ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $member->full_name ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-500">{{ $member->nida_number ?? 'No NIDA' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $member->gender ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex flex-col">
                                    <span>{{ $member->phone_number ?? 'N/A' }}</span>
                                    @if($member->mobile_phone_number && $member->mobile_phone_number !== $member->phone_number)
                                        <span class="text-xs text-gray-500">{{ $member->mobile_phone_number }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $member->email ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $member->branch_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($member->status === 'ACTIVE') bg-green-100 text-green-800
                                    @elseif($member->status === 'PENDING') bg-yellow-100 text-yellow-800
                                    @elseif($member->status === 'INACTIVE') bg-red-100 text-red-800
                                    @elseif($member->status === 'SUSPENDED') bg-orange-100 text-orange-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $member->status ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $member->registration_date ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($member->savings_balance ?? 0, 2) }} TZS</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button wire:click="viewMemberDetails({{ $member->id }})" 
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-900 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-900">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-4 text-center text-sm text-gray-500">
                                No members found for the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($members->count() > 0)
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ $members->count() }} member(s) {{ $client_type === 'MULTIPLE' ? 'for selected members' : 'across all criteria' }}
                    </div>
                    <div class="text-sm text-gray-500">
                        Total Savings: {{ number_format($members->sum('savings_balance'), 2) }} TZS
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Member Details Modal --}}
    @if($showMemberModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[85vh] overflow-hidden">
                {{-- Modal Header --}}
                <div class="bg-blue-900 px-6 py-4 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold">Member Details</h3>
                                <p class="text-indigo-100 text-sm">Comprehensive member information</p>
                            </div>
                        </div>
                        <button wire:click="closeMemberModal" class="text-white hover:text-indigo-200 transition-colors duration-200 p-2 hover:bg-white hover:bg-opacity-10 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    </div>
                    
                {{-- Modal Content --}}
                <div class="overflow-y-auto max-h-[calc(85vh-100px)]">
                    @if($selectedMember)
                        <div class="p-6">
                            {{-- Member Overview Card --}}
                            <div class="bg-blue-50 rounded-lg p-4 mb-6 border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-6">
                                        <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                            <div>
                                            <h2 class="text-2xl font-bold text-gray-900">{{ $selectedMember->full_name }}</h2>
                                            <p class="text-lg text-gray-600">Member #{{ $selectedMember->client_number }}</p>
                                            <div class="flex items-center space-x-4 mt-2">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                                    @if($selectedMember->status === 'ACTIVE') bg-green-100 text-green-800
                                                    @elseif($selectedMember->status === 'PENDING') bg-yellow-100 text-yellow-800
                                                    @elseif($selectedMember->status === 'INACTIVE') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ $selectedMember->status ?? 'N/A' }}
                                                </span>
                                                <span class="text-sm text-gray-500">{{ $selectedMember->branch_name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500">Savings Balance</p>
                                        <p class="text-2xl font-bold text-green-600">{{ number_format($selectedMember->savings_balance ?? 0, 2) }} TZS</p>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Information Grid --}}
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                {{-- Personal Information --}}
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center mb-4">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <h4 class="text-lg font-semibold text-gray-900">Personal Information</h4>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Full Name</span>
                                            <span class="text-gray-900">{{ $selectedMember->full_name }}</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Member Number</span>
                                            <span class="text-gray-900 font-mono">{{ $selectedMember->client_number }}</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">NIDA Number</span>
                                            <span class="text-gray-900">{{ $selectedMember->nida_number ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Gender</span>
                                            <span class="text-gray-900">{{ $selectedMember->gender ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Date of Birth</span>
                                            <span class="text-gray-900">{{ $selectedMember->date_of_birth ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between py-2">
                                            <span class="font-medium text-gray-600">Marital Status</span>
                                            <span class="text-gray-900">{{ $selectedMember->marital_status ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Contact Information --}}
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center mb-4">
                                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                        </div>
                                        <h4 class="text-lg font-semibold text-gray-900">Contact Information</h4>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Phone Number</span>
                                            <span class="text-gray-900">{{ $selectedMember->phone_number ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Mobile Number</span>
                                            <span class="text-gray-900">{{ $selectedMember->mobile_phone_number ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Email Address</span>
                                            <span class="text-gray-900">{{ $selectedMember->email ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Address</span>
                                            <span class="text-gray-900 text-right max-w-xs">{{ $selectedMember->address ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Region</span>
                                            <span class="text-gray-900">{{ $selectedMember->region ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between py-2">
                                            <span class="font-medium text-gray-600">District</span>
                                            <span class="text-gray-900">{{ $selectedMember->district ?? 'N/A' }}</span>
                                        </div>
                                </div>
                            </div>
                            
                                {{-- Employment Information --}}
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center mb-4">
                                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                                            </svg>
                                        </div>
                                        <h4 class="text-lg font-semibold text-gray-900">Employment Information</h4>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Employment Status</span>
                                            <span class="text-gray-900">{{ $selectedMember->employment ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Employer Name</span>
                                            <span class="text-gray-900 text-right max-w-xs">{{ $selectedMember->employer_name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Occupation</span>
                                            <span class="text-gray-900">{{ $selectedMember->occupation ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between py-2">
                                            <span class="font-medium text-gray-600">Monthly Income</span>
                                            <span class="text-gray-900 font-semibold">{{ number_format($selectedMember->income_available ?? 0, 2) }} TZS</span>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Membership Information --}}
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center mb-4">
                                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                        <h4 class="text-lg font-semibold text-gray-900">Membership Information</h4>
                            </div>
                                    <div class="space-y-3">
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Status</span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($selectedMember->status === 'ACTIVE') bg-green-100 text-green-800
                                            @elseif($selectedMember->status === 'PENDING') bg-yellow-100 text-yellow-800
                                            @elseif($selectedMember->status === 'INACTIVE') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $selectedMember->status ?? 'N/A' }}
                                        </span>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Branch</span>
                                            <span class="text-gray-900">{{ $selectedMember->branch_name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="font-medium text-gray-600">Registration Date</span>
                                            <span class="text-gray-900">{{ $selectedMember->registration_date ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between py-2">
                                            <span class="font-medium text-gray-600">Savings Balance</span>
                                            <span class="text-gray-900 font-semibold text-green-600">{{ number_format($selectedMember->savings_balance ?? 0, 2) }} TZS</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                {{-- Modal Footer --}}
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                    <div class="flex justify-end">
                        <button wire:click="closeMemberModal" 
                                class="inline-flex items-center px-6 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Success/Error Messages --}}
    @if (session()->has('success'))
        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif
</div>