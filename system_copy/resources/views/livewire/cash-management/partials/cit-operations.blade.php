<div class="space-y-8">
    {{-- Enhanced CIT Hero Section --}}
    <div class="bg-gradient-to-br from-indigo-500 via-indigo-600 to-indigo-700 rounded-3xl p-8 text-white shadow-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold mb-2">Cash-in-Transit Operations</h3>
                <p class="text-indigo-100 text-lg">Secure and efficient cash transfer management with real-time tracking</p>
            </div>
            <div class="text-right">
                <div class="bg-white bg-opacity-20 rounded-2xl p-6 backdrop-blur-sm">
                    <h4 class="text-lg font-semibold mb-2">Active Transfers</h4>
                    <p class="text-4xl font-bold">{{ count($activeCitTransfers ?? []) }}</p>
                    <p class="text-indigo-100 text-sm">In progress</p>
                </div>
            </div>
        </div>
    </div>

    {{-- CIT Status Dashboard --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
            <div class="flex items-center">
                <div class="p-3 bg-blue-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-700">Secured Transfers</p>
                    <p class="text-2xl font-bold text-blue-900">{{ count($secureCitTransfers ?? []) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border border-green-200">
            <div class="flex items-center">
                <div class="p-3 bg-green-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-700">Completed Today</p>
                    <p class="text-2xl font-bold text-green-900">{{ count($completedCitTransfers ?? []) }}</p>
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
                    <p class="text-2xl font-bold text-orange-900">{{ count($pendingCitTransfers ?? []) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200">
            <div class="flex items-center">
                <div class="p-3 bg-purple-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-purple-700">Total Value</p>
                    <p class="text-2xl font-bold text-purple-900">TZS {{ number_format($totalCitValue ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- CIT Operations Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        {{-- Schedule New Transfer --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Schedule New CIT Transfer</h3>
            </div>
            
            <form wire:submit.prevent="scheduleNewCitTransfer" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Transfer Type</label>
                    <select wire:model="citTransferType" class="w-full rounded-xl border-2 border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all duration-200 p-4 text-lg">
                        <option value="">Select Transfer Type</option>
                        <option value="bank_deposit">Bank Deposit</option>
                        <option value="bank_withdrawal">Bank Withdrawal</option>
                        <option value="branch_transfer">Branch Transfer</option>
                        <option value="vault_replenishment">Vault Replenishment</option>
                    </select>
                    @error('citTransferType') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">From Location</label>
                        <select wire:model="citFromLocation" class="w-full rounded-xl border-2 border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all duration-200 p-4">
                            <option value="">Choose Location</option>
                            <option value="vault">Main Vault</option>
                            <option value="branch">Branch Office</option>
                            <option value="bank">Bank</option>
                        </select>
                        @error('citFromLocation') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">To Location</label>
                        <select wire:model="citToLocation" class="w-full rounded-xl border-2 border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all duration-200 p-4">
                            <option value="">Choose Location</option>
                            <option value="vault">Main Vault</option>
                            <option value="branch">Branch Office</option>
                            <option value="bank">Bank</option>
                        </select>
                        @error('citToLocation') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Amount (TZS)</label>
                    <input type="number" wire:model="citAmount" step="1" min="1" 
                           class="w-full rounded-xl border-2 border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all duration-200 p-4 text-lg" 
                           placeholder="Enter transfer amount">
                    @error('citAmount') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">CIT Provider</label>
                    <select wire:model="citProvider" class="w-full rounded-xl border-2 border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all duration-200 p-4">
                        <option value="">Select Provider</option>
                        @foreach($citProviders ?? [] as $provider)
                            <option value="{{ $provider->id }}">{{ $provider->name }} - {{ $provider->contact_person }}</option>
                        @endforeach
                    </select>
                    @error('citProvider') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Scheduled Date & Time</label>
                    <input type="datetime-local" wire:model="citScheduledDateTime" 
                           class="w-full rounded-xl border-2 border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all duration-200 p-4">
                    @error('citScheduledDateTime') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Special Instructions</label>
                    <textarea wire:model="citInstructions" rows="4" 
                              class="w-full rounded-xl border-2 border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all duration-200 p-4" 
                              placeholder="Any special handling requirements or notes"></textarea>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-500 to-indigo-600 text-white py-4 px-6 rounded-xl hover:from-indigo-600 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200 transition-all duration-200 font-bold text-lg shadow-lg">
                    Schedule Transfer
                </button>
            </form>
        </div>

        {{-- Active Transfers Tracking --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 ml-4">Active Transfers</h3>
                </div>
                <button wire:click="refreshCitData" class="text-blue-600 hover:text-blue-800 text-sm font-medium bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
            
            <div class="space-y-4 max-h-96 overflow-y-auto">
                @forelse($activeCitTransfers ?? [] as $transfer)
                    <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-2xl p-4 border border-green-200 hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <div class="p-2 bg-gradient-to-br from-blue-500 to-green-500 rounded-xl text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-bold text-gray-900">{{ $transfer->reference_number }}</h4>
                                    <p class="text-sm text-gray-600">{{ $transfer->from_location }} → {{ $transfer->to_location }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600">TZS {{ number_format($transfer->amount) }}</p>
                                <span class="inline-flex px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst($transfer->status) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center text-sm">
                            <div>
                                <p class="text-gray-600">Provider: <span class="font-medium">{{ $transfer->provider->name ?? 'N/A' }}</span></p>
                                <p class="text-gray-600">ETA: <span class="font-medium">{{ $transfer->estimated_arrival->format('H:i') ?? 'N/A' }}</span></p>
                            </div>
                            <div class="flex space-x-2">
                                <button wire:click="trackTransfer({{ $transfer->id }})" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    Track
                                </button>
                                <button wire:click="updateTransfer({{ $transfer->id }})" class="text-blue-600 hover:text-blue-800 font-medium">
                                    Update
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-gray-600 font-medium">No active transfers</p>
                        <p class="text-sm text-gray-500">Schedule a new transfer to get started</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- CIT Providers Management --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">CIT Service Providers</h3>
            </div>
            <button class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-6 py-3 rounded-xl hover:from-purple-600 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-200 transition-all duration-200 font-bold shadow-lg">
                Add Provider
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($citProviders ?? [] as $provider)
                <div class="bg-gradient-to-br from-gray-50 to-purple-50 rounded-2xl p-6 border border-purple-200 hover:shadow-lg transition-all duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-500 rounded-xl">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="font-bold text-gray-900">{{ $provider->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $provider->license_number }}</p>
                            </div>
                        </div>
                        <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full {{ $provider->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($provider->status) }}
                        </span>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Contact:</span>
                            <span class="font-medium">{{ $provider->contact_person }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phone:</span>
                            <span class="font-medium">{{ $provider->phone }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Response Time:</span>
                            <span class="font-medium">{{ $provider->average_response_time ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Rating:</span>
                            <div class="flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= ($provider->rating ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-purple-200">
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-600">{{ $provider->completed_transfers ?? 0 }} completed transfers</p>
                            <div class="flex space-x-2">
                                <button wire:click="editProvider({{ $provider->id }})" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Edit
                                </button>
                                <button wire:click="viewProvider({{ $provider->id }})" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                    View
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <svg class="w-20 h-20 text-gray-400 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <h3 class="text-xl font-bold text-gray-600 mb-2">No CIT Providers</h3>
                    <p class="text-gray-500 mb-4">Add service providers to start scheduling transfers</p>
                    <button class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-6 py-3 rounded-xl hover:from-purple-600 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-200 transition-all duration-200 font-bold shadow-lg">
                        Add First Provider
                    </button>
                </div>
            @endforelse
        </div>
    </div>
</div> 