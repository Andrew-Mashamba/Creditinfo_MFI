<div class="space-y-8">
    {{-- Enhanced Vault Hero Section --}}
    <div class="bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 rounded-3xl p-8 text-white shadow-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold mb-2">Vault Operations Center</h3>
                <p class="text-gray-500 text-lg">Monitor, manage, and control your vault operations with precision</p>
            </div>
            <div class="text-right">
                <div class="bg-white bg-opacity-20 rounded-2xl p-6 backdrop-blur-sm">
                    <h4 class="text-lg font-semibold mb-2">Current Balance</h4>
                    <p class="text-4xl font-bold">TZS {{ number_format(($vaultBalance ?? 0) / 1000000, 1) }}M</p>
                    <p class="text-gray-500 text-sm">{{ ($vaultLimit ?? 0) > 0 ? number_format((($vaultBalance ?? 0) / ($vaultLimit ?? 1)) * 100, 1) : 'N/A' }}% of capacity</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Vault Status Dashboard --}}
    @if($vault)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border border-green-200">
                <div class="flex items-center">
                    <div class="p-3 bg-green-500 rounded-xl shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-700">Current Balance</p>
                        <p class="text-xl font-bold text-green-900">TZS {{ number_format($vaultBalance ?? 0) }}</p>
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
                        <p class="text-sm font-medium text-blue-700">Vault Limit</p>
                        <p class="text-xl font-bold text-blue-900">TZS {{ number_format($vaultLimit ?? 0) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-500 rounded-xl shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-purple-700">Available Space</p>
                        <p class="text-xl font-bold text-purple-900">TZS {{ number_format(($vaultLimit ?? 0) - ($vaultBalance ?? 0)) }}</p>
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
                        <p class="text-sm font-medium text-orange-700">Utilization</p>
                        <p class="text-xl font-bold text-orange-900">{{ ($vaultLimit ?? 0) > 0 ? number_format((($vaultBalance ?? 0) / ($vaultLimit ?? 1)) * 100, 1) : 'N/A' }}%</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Vault Operations Grid --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            {{-- Vault Transfers Card --}}
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
                <div class="flex items-center mb-6">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 ml-4">Vault Transfer Operations</h3>
                </div>
                
                <form wire:submit.prevent="processVaultTransfer" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Transfer Type</label>
                        <select wire:model="vaultTransferType" class="w-full rounded-xl border-2 border-blue-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-200 transition-all duration-200 p-4 text-lg">
                            <option value="deposit">Deposit to Vault</option>
                            <option value="withdrawal">Withdrawal from Vault</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Amount (TZS)</label>
                        <input type="number" wire:model="vaultTransferAmount" step="1" min="1" 
                               class="w-full rounded-xl border-2 border-blue-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-200 transition-all duration-200 p-4 text-lg" 
                               placeholder="Enter amount">
                        @error('vaultTransferAmount') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Reference Number</label>
                        <input type="text" wire:model="vaultReference" 
                               class="w-full rounded-xl border-2 border-blue-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-200 transition-all duration-200 p-4" 
                               placeholder="Enter reference">
                        @error('vaultReference') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea wire:model="vaultDescription" rows="4" 
                                  class="w-full rounded-xl border-2 border-blue-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-200 transition-all duration-200 p-4" 
                                  placeholder="Describe the transfer purpose"></textarea>
                        @error('vaultDescription') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-4 px-6 rounded-xl hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 transition-all duration-200 font-bold text-lg shadow-lg">
                        Submit for Approval
                    </button>
                </form>
            </div>

            {{-- Vault Replenishment Request --}}
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
                <div class="flex items-center mb-6">
                    <div class="p-3 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 ml-4">Request HQ Replenishment</h3>
                </div>
                
                <form wire:submit.prevent="requestVaultReplenishment" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Requested Amount (TZS)</label>
                        <input type="number" wire:model="replenishmentAmount" step="1" min="1" 
                               class="w-full rounded-xl border-2 border-orange-200 shadow-sm focus:border-orange-500 focus:ring-4 focus:ring-orange-200 transition-all duration-200 p-4 text-lg" 
                               placeholder="Enter amount needed">
                        @error('replenishmentAmount') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Urgency Level</label>
                        <select wire:model="replenishmentUrgency" class="w-full rounded-xl border-2 border-orange-200 shadow-sm focus:border-orange-500 focus:ring-4 focus:ring-orange-200 transition-all duration-200 p-4">
                            <option value="normal">Normal (2-3 business days)</option>
                            <option value="urgent">Urgent (Same day)</option>
                            <option value="emergency">Emergency (Immediate)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Reason for Request</label>
                        <input type="text" wire:model="replenishmentReason" 
                               class="w-full rounded-xl border-2 border-orange-200 shadow-sm focus:border-orange-500 focus:ring-4 focus:ring-orange-200 transition-all duration-200 p-4" 
                               placeholder="e.g., High customer demand, Low cash reserves">
                        @error('replenishmentReason') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Additional Notes</label>
                        <textarea wire:model="replenishmentNotes" rows="4" 
                                  class="w-full rounded-xl border-2 border-orange-200 shadow-sm focus:border-orange-500 focus:ring-4 focus:ring-orange-200 transition-all duration-200 p-4" 
                                  placeholder="Any additional information for HQ"></textarea>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white py-4 px-6 rounded-xl hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-4 focus:ring-orange-200 transition-all duration-200 font-bold text-lg shadow-lg">
                        Submit Replenishment Request
                    </button>
                </form>
            </div>
        </div>

        {{-- Vault Details Information --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-gray-500 to-gray-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Vault Configuration & Details</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6">
                    <h4 class="text-lg font-bold text-blue-900 mb-4">Basic Information</h4>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-blue-700 font-medium">Vault Name:</span>
                            <span class="font-bold text-blue-900">{{ $vault->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700 font-medium">Code:</span>
                            <span class="font-bold text-blue-900">{{ $vault->code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700 font-medium">Status:</span>
                            <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">
                                {{ ucwords(str_replace('_', ' ', $vault->status)) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700 font-medium">Warning Threshold:</span>
                            <span class="font-bold text-blue-900">{{ $vault->warning_threshold ?? 80 }}%</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6">
                    <h4 class="text-lg font-bold text-green-900 mb-4">Banking Details</h4>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-green-700 font-medium">Bank Name:</span>
                            <span class="font-bold text-green-900">{{ $vault->bank_name ?? 'Not Set' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-green-700 font-medium">Bank Account:</span>
                            <span class="font-bold text-green-900">{{ $vault->bank_account_number ?? 'Not Set' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-green-700 font-medium">Internal Account:</span>
                            <span class="font-bold text-green-900">{{ $vault->internal_account_number ?? 'Not Set' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-green-700 font-medium">Auto Transfer:</span>
                            <span class="font-bold {{ ($vault->auto_bank_transfer ?? false) ? 'text-green-600' : 'text-gray-600' }}">
                                {{ ($vault->auto_bank_transfer ?? false) ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6">
                    <h4 class="text-lg font-bold text-purple-900 mb-4">Security Settings</h4>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-purple-700 font-medium">Dual Approval:</span>
                            <span class="font-bold {{ ($vault->requires_dual_approval ?? false) ? 'text-red-600' : 'text-green-600' }}">
                                {{ ($vault->requires_dual_approval ?? false) ? 'Required' : 'Not Required' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-purple-700 font-medium">Alerts:</span>
                            <span class="font-bold {{ ($vault->send_alerts ?? true) ? 'text-green-600' : 'text-gray-600' }}">
                                {{ ($vault->send_alerts ?? true) ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-purple-700 font-medium">Last Updated:</span>
                            <span class="font-bold text-purple-900">{{ $vault->updated_at->format('M d, H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-purple-700 font-medium">Branch:</span>
                            <span class="font-bold text-purple-900">{{ $vault->branch->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($vault->description)
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h4 class="text-lg font-bold text-gray-900 mb-3">Description</h4>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-gray-700">{{ $vault->description }}</p>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-3xl p-12 text-center">
            <svg class="w-24 h-24 text-gray-400 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <h3 class="text-2xl font-bold text-gray-700 mb-3">No Vault Configured</h3>
            <p class="text-gray-600 text-lg">No active vault found for your branch. Contact system administrator to set up vault operations.</p>
        </div>
    @endif
</div> 