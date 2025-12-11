{{-- Branch Vault Management System --}}
<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-indigo-600 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Vault Management</h2>
                <p class="text-indigo-100 mb-4">Manage branch vaults, limits, and automatic bank transfers</p>
            </div>
            <div class="text-right">
                <div class="p-4 bg-red-600 bg-opacity-20 rounded-xl">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        {{-- System Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-white bg-opacity-20 rounded-lg p-3">
                <p class="text-sm text-red-900">Total Vaults</p>
                <p class="text-xl font-bold text-red-900">{{ $vaultStats['total_vaults'] ?? 0 }}</p>
            </div>
            <div class="bg-white bg-opacity-20 rounded-lg p-3">
                <p class="text-sm text-red-900">Total Cash</p>
                <p class="text-xl font-bold text-red-900">${{ number_format($vaultStats['total_cash'] ?? 0, 2) }}</p>
            </div>
            <div class="bg-white bg-opacity-20 rounded-lg p-3">
                <p class="text-sm text-red-900">Over Limit</p>
                <p class="text-xl font-bold text-red-900">{{ $vaultStats['over_limit'] ?? 0 }}</p>
            </div>
            <div class="bg-white bg-opacity-20 rounded-lg p-3">
                <p class="text-sm text-red-900">Bank Transfers Today</p>
                <p class="text-xl font-bold text-red-900">{{ $vaultStats['bank_transfers_today'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- Pending Vault Replenishment Requests --}}
    @if($pendingReplenishments->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        Pending Replenishment Requests
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            {{ $pendingReplenishments->count() }}
                        </span>
                    </h3>
                    <button wire:click="loadPendingReplenishments" class="text-sm text-indigo-600 hover:text-indigo-800">
                        Refresh
                    </button>
                </div>
            </div>
            
            <div class="divide-y divide-gray-200">
                @foreach($pendingReplenishments as $replenishment)
                    @php
                        $requestData = json_decode($replenishment->edit_package, true) ?? [];
                        $urgency = $requestData['urgency'] ?? 'normal';
                        if ($urgency === 'emergency') {
                            $urgencyClass = 'bg-red-100 text-red-800 border-red-200';
                        } elseif ($urgency === 'urgent') {
                            $urgencyClass = 'bg-orange-100 text-orange-800 border-orange-200';
                        } else {
                            $urgencyClass = 'bg-red-100 text-red-800 border-red-200';
                        }
                    @endphp
                    
                    <div class="p-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <h4 class="text-sm font-medium text-gray-900">
                                                {{ $replenishment->branch->name ?? 'Unknown Branch' }}
                                            </h4>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $urgencyClass }}">
                                                {{ strtoupper($requestData['urgency'] ?? 'normal') }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            {{ $replenishment->user->name ?? 'Unknown User' }} • 
                                            Requested by {{ $replenishment->user->name ?? 'Unknown User' }} • 
                                            {{ $replenishment->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">Requested Amount</p>
                                        <p class="text-lg font-bold text-green-600">
                                            TZS {{ number_format($requestData['amount'] ?? 0, 2) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">Current Balance</p>
                                        <p class="text-sm text-gray-900">
                                            TZS {{ number_format($requestData['current_balance'] ?? 0, 2) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">Vault Limit</p>
                                        <p class="text-sm text-gray-900">
                                            TZS {{ number_format($requestData['vault_limit'] ?? 0, 2) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <p class="text-xs font-medium text-gray-500 mb-1">Reason</p>
                                    <p class="text-sm text-gray-900">{{ $requestData['reason'] ?? 'No reason provided' }}</p>
                                    @if(!empty($requestData['notes']))
                                        <p class="text-xs text-gray-600 mt-1">
                                            <strong>Notes:</strong> {{ $requestData['notes'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex-shrink-0 ml-6">
                                <div class="flex space-x-3">
                                    <button wire:click="showReplenishmentApprovalModal({{ $replenishment->id }})" 
                                            class="inline-flex items-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Review
                                    </button>
                                    
                                    <button wire:click="approveVaultReplenishment({{ $replenishment->id }})" 
                                            onclick="return confirm('Are you sure you want to approve this replenishment request?')"
                                            class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Approve
                                    </button>
                                    
                                    <button wire:click="rejectVaultReplenishment({{ $replenishment->id }})" 
                                            onclick="return confirm('Are you sure you want to reject this replenishment request?')"
                                            class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Action Buttons and Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Vault Operations</h3>
            <div class="flex items-center space-x-3">
                @if($isAdmin || $isSupervisor)
                    <button wire:click="showCreateVaultModal" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Vault
                    </button>
                    
                    <button wire:click="showBankToVaultModal" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Bank to Vault
                    </button>
                    
                    <button wire:click="showVaultToBankModal" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" transform="rotate(180)">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Vault to Bank
                    </button>
                @endif
                
                <button wire:click="exportVaults" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Report
                </button>
                
                <button wire:click="refreshVaultData" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Institution</label>
                <select wire:model="filterInstitution" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Institutions</option>
                    @foreach($institutions as $institution)
                        <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select wire:model="filterBranch" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select wire:model="filterStatus" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="over_limit">Over Limit</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" wire:model.debounce.300ms="searchTerm" placeholder="Search vaults..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>
    </div>

    {{-- Vaults Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($vaults as $vault)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow duration-200">
                {{-- Vault Header --}}
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="p-3 rounded-lg {{ $vault->status === 'active' ? 'bg-green-100' : ($vault->status === 'over_limit' ? 'bg-red-100' : 'bg-gray-100') }}">
                                <svg class="w-6 h-6 {{ $vault->status === 'active' ? 'text-green-600' : ($vault->status === 'over_limit' ? 'text-red-600' : 'text-gray-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $vault->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $vault->branch->name ?? 'No Branch' }}</p>
                            </div>
                        </div>
                        
                        {{-- Status Badge --}}
                        @php
                            $statusClasses = [
                                'active' => 'bg-green-100 text-green-800',
                                'inactive' => 'bg-gray-100 text-gray-800',
                                'maintenance' => 'bg-yellow-100 text-yellow-800',
                                'over_limit' => 'bg-red-100 text-red-800'
                            ];
                            $statusClass = $statusClasses[$vault->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                            {{ ucwords(str_replace('_', ' ', $vault->status)) }}
                        </span>
                    </div>

                    {{-- Vault Balance and Limit --}}
                    <div class="space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Current Balance</span>
                                <span class="text-lg font-bold {{ $vault->current_balance > $vault->limit ? 'text-red-600' : 'text-green-600' }}">
                                    ${{ number_format($vault->current_balance, 2) }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                @php
                                    $percentage = $vault->limit > 0 ? min(($vault->current_balance / $vault->limit) * 100, 100) : 0;
                                    $colorClass = $percentage > 100 ? 'bg-red-500' : ($percentage > 80 ? 'bg-yellow-500' : 'bg-green-500');
                                @endphp
                                <div class="h-2 rounded-full {{ $colorClass }}" style="width: {{ min($percentage, 100) }}%"></div>
                            </div>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-500">Limit: ${{ number_format($vault->limit, 2) }}</span>
                                <span class="text-xs {{ $percentage > 100 ? 'text-red-600' : ($percentage > 80 ? 'text-yellow-600' : 'text-green-600') }}">
                                    {{ number_format($percentage, 1) }}%
                                </span>
                            </div>
                        </div>

                        {{-- Over Limit Warning --}}
                        @if($vault->current_balance > $vault->limit)
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-red-800">Over Limit</p>
                                        <p class="text-xs text-red-600">
                                            Excess: ${{ number_format($vault->current_balance - $vault->limit, 2) }}
                                        </p>
                                    </div>
                                </div>
                                @if($vault->auto_bank_transfer)
                                    <button wire:click="initiateBankTransfer({{ $vault->id }})" 
                                            class="mt-2 w-full bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                        Transfer to Bank
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Vault Details --}}
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Institution</p>
                            <p class="text-sm font-medium text-gray-900">{{ $vault->branch->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Code</p>
                            <p class="text-sm font-medium text-gray-900">{{ $vault->code }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Auto Transfer</p>
                            <p class="text-sm font-medium {{ $vault->auto_bank_transfer ? 'text-green-600' : 'text-gray-600' }}">
                                {{ $vault->auto_bank_transfer ? 'Enabled' : 'Disabled' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Last Updated</p>
                            <p class="text-sm font-medium text-gray-900">{{ $vault->updated_at->format('M d, H:i') }}</p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="space-y-2">
                        {{-- Primary Actions Row --}}
                        <div class="flex items-center space-x-2">
                            <button wire:click="viewVaultDetails({{ $vault->id }})" 
                                    class="flex-1 bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                View Details
                            </button>
                            
                            @if($isAdmin || $isSupervisor)
                                <button wire:click="editVault({{ $vault->id }})" 
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                
                                <button wire:click="confirmDeleteVault({{ $vault->id }})" 
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                        
                        {{-- Transfer Actions Row --}}
                        @if($isAdmin || $isSupervisor)
                            <div class="flex items-center space-x-2">
                                <button wire:click="showBankToVaultModal({{ $vault->id }})" 
                                        class="flex-1 bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs font-medium transition-colors">
                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                    Bank → Vault
                                </button>
                                
                                <button wire:click="showVaultToBankModal({{ $vault->id }})" 
                                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-2 py-1 rounded text-xs font-medium transition-colors">
                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" transform="rotate(180)">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                    Vault → Bank
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-12">
                    <div class="p-6 bg-gray-50 rounded-xl inline-block">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Vaults Found</h3>
                        <p class="text-gray-600 mb-4">Get started by creating your first vault.</p>
                        
                        @if($isAdmin || $isSupervisor)
                            <button wire:click="showCreateVaultModal" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Create First Vault
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($vaults->hasPages())
        <div class="flex justify-center">
            {{ $vaults->links() }}
        </div>
    @endif

    {{-- Recent Bank Transfers --}}
    @if($recentBankTransfers->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    Recent Bank Transfers
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vault</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentBankTransfers as $transfer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $transfer->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $transfer->vault->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                    ${{ number_format($transfer->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ ucwords(str_replace('_', ' ', $transfer->reason)) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'failed' => 'bg-red-100 text-red-800'
                                        ];
                                        $statusClass = $statusClasses[$transfer->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                        {{ ucfirst($transfer->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $transfer->reference }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div> 

{{-- Vault Replenishment Approval Modal --}}
@if($showReplenishmentApprovalModal && $selectedReplenishment)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="replenishment-modal">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Vault Replenishment Request Review
                    </h3>
                    <button wire:click="closeReplenishmentApprovalModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Modal Content --}}
                @php
                    $requestData = json_decode($selectedReplenishment->edit_package, true) ?? [];
                    $urgency = $requestData['urgency'] ?? 'normal';
                    $urgencyClass = $urgency === 'emergency' ? 'text-red-600' : ($urgency === 'urgent' ? 'text-orange-600' : 'text-red-600');
                @endphp

                <div class="mt-6 space-y-6">
                    {{-- Request Information --}}
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Request Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Branch</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedReplenishment->branch->name ?? 'Unknown Branch' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Institution</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedReplenishment->user->name ?? 'Unknown User' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Requested By</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedReplenishment->user->name ?? 'Unknown User' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Request Date</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedReplenishment->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Financial Information --}}
                    <div class="bg-red-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Financial Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Requested Amount</p>
                                <p class="text-xl font-bold text-green-600">TZS {{ number_format($requestData['amount'] ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Current Vault Balance</p>
                                <p class="text-lg font-medium text-gray-900">TZS {{ number_format($requestData['current_balance'] ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Vault Limit</p>
                                <p class="text-lg font-medium text-gray-900">TZS {{ number_format($requestData['vault_limit'] ?? 0, 2) }}</p>
                            </div>
                        </div>
                        
                        {{-- Balance After Approval Calculation --}}
                        @php
                            $currentBalance = $requestData['current_balance'] ?? 0;
                            $requestedAmount = $requestData['amount'] ?? 0;
                            $balanceAfterApproval = $currentBalance + $requestedAmount;
                            $vaultLimit = $requestData['vault_limit'] ?? 0;
                            $utilizationAfter = $vaultLimit > 0 ? ($balanceAfterApproval / $vaultLimit) * 100 : 0;
                        @endphp
                        
                        <div class="mt-4 pt-4 border-t border-red-200">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Balance After Approval:</span>
                                <span class="text-lg font-bold {{ $balanceAfterApproval > $vaultLimit ? 'text-red-600' : 'text-green-600' }}">
                                    TZS {{ number_format($balanceAfterApproval, 2) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-500">Utilization After Approval:</span>
                                <span class="text-sm font-medium {{ $utilizationAfter > 100 ? 'text-red-600' : ($utilizationAfter > 80 ? 'text-orange-600' : 'text-green-600') }}">
                                    {{ number_format($utilizationAfter, 1) }}%
                                </span>
                            </div>
                            
                            {{-- Warning if over limit --}}
                            @if($balanceAfterApproval > $vaultLimit)
                                <div class="mt-2 bg-red-100 border border-red-300 rounded-lg p-2">
                                    <p class="text-xs text-red-700">
                                        <strong>Warning:</strong> Approval will exceed vault limit by TZS {{ number_format($balanceAfterApproval - $vaultLimit, 2) }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Request Reason and Urgency --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-900">Request Justification</h4>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 {{ $urgencyClass }}">
                                {{ strtoupper($urgency) }} PRIORITY
                            </span>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs font-medium text-gray-500 mb-1">Reason</p>
                                <p class="text-sm text-gray-900 bg-gray-50 rounded p-2">{{ $requestData['reason'] ?? 'No reason provided' }}</p>
                            </div>
                            @if(!empty($requestData['notes']))
                                <div>
                                    <p class="text-xs font-medium text-gray-500 mb-1">Additional Notes</p>
                                    <p class="text-sm text-gray-900 bg-gray-50 rounded p-2">{{ $requestData['notes'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Approval Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">HQ Approval Notes</label>
                        <textarea wire:model="replenishmentApprovalNotes" rows="3" 
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                  placeholder="Add notes for this approval decision (optional)"></textarea>
                    </div>
                </div>

                {{-- Modal Actions --}}
                <div class="mt-8 flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <button wire:click="closeReplenishmentApprovalModal" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    
                    <button wire:click="rejectVaultReplenishment({{ $selectedReplenishment->id }})" 
                            onclick="return confirm('Are you sure you want to reject this replenishment request?')"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Reject Request
                    </button>
                    
                    <button wire:click="approveVaultReplenishment({{ $selectedReplenishment->id }})" 
                            onclick="return confirm('Are you sure you want to approve this replenishment request? This will transfer TZS {{ number_format($requestData['amount'] ?? 0, 2) }} to the vault.')"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Approve & Transfer Funds
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif 