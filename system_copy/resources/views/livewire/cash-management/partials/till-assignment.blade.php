<div class="space-y-8">
    {{-- Enhanced Till Assignment Hero Section --}}
    <div class="bg-gradient-to-br from-purple-500 via-purple-600 to-purple-700 rounded-3xl p-8 text-white shadow-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold mb-2">Till Assignment Center</h3>
                <p class="text-purple-100 text-lg">Efficiently assign and manage till allocations for optimal operations</p>
            </div>
            <div class="text-right">
                <div class="bg-white bg-opacity-20 rounded-2xl p-6 backdrop-blur-sm">
                    <h4 class="text-lg font-semibold mb-2">Available Tills</h4>
                    <p class="text-4xl font-bold">{{ count($unassignedTills ?? []) }}</p>
                    <p class="text-purple-100 text-sm">Ready for assignment</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Till Overview Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
            <div class="flex items-center">
                <div class="p-3 bg-blue-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-700">Total Tills</p>
                    <p class="text-2xl font-bold text-blue-900">{{ count($availableTills ?? []) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border border-green-200">
            <div class="flex items-center">
                <div class="p-3 bg-green-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-700">Assigned Tills</p>
                    <p class="text-2xl font-bold text-green-900">{{ count($availableTills ?? []) - count($unassignedTills ?? []) }}</p>
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
                    <p class="text-sm font-medium text-orange-700">Open Tills</p>
                    <p class="text-2xl font-bold text-orange-900">{{ collect($tillSummary ?? [])->where('status', 'open')->count() }}</p>
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
                    <p class="text-sm font-medium text-purple-700">Total Till Balance</p>
                    <p class="text-2xl font-bold text-purple-900">TZS {{ number_format(collect($tillSummary ?? [])->sum('current_balance')) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Operations Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        {{-- Till Assignment Form --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Assign Till to Teller</h3>
            </div>
            
            <form wire:submit.prevent="assignTillToTeller" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Select Till</label>
                    <select wire:model="assignTillId" class="w-full rounded-xl border-2 border-blue-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-200 transition-all duration-200 p-4 text-lg">
                        <option value="">Choose a Till</option>
                        @foreach($unassignedTills ?? [] as $till)
                            <option value="{{ $till->id }}">{{ $till->name }} ({{ $till->code }})</option>
                        @endforeach
                    </select>
                    @error('assignTillId') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Select Teller</label>
                    <select wire:model="assignTellerId" class="w-full rounded-xl border-2 border-blue-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-200 transition-all duration-200 p-4 text-lg">
                        <option value="">Choose a Teller</option>
                        @foreach($availableTellers ?? [] as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    @error('assignTellerId') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Assignment Notes</label>
                    <textarea wire:model="assignmentNotes" rows="4" 
                              class="w-full rounded-xl border-2 border-blue-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-200 transition-all duration-200 p-4" 
                              placeholder="Optional notes about the assignment"></textarea>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-4 px-6 rounded-xl hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 transition-all duration-200 font-bold text-lg shadow-lg">
                    Assign Till
                </button>
            </form>
        </div>

        {{-- Available Tellers --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Available Tellers</h3>
            </div>
            
            <div class="space-y-4 max-h-96 overflow-y-auto">
                @forelse($availableTellers ?? [] as $teller)
                    <div class="bg-gradient-to-r from-gray-50 to-green-50 rounded-2xl p-4 border border-green-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="p-2 bg-green-100 rounded-xl">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-bold text-gray-900">{{ $teller->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $teller->email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @php
                                    $assignedTillsCount = collect($availableTills)->where('assigned_user_id', $teller->id)->count();
                                @endphp
                                <div class="text-sm">
                                    <span class="inline-flex px-2 py-1 text-xs font-bold rounded-full {{ $assignedTillsCount > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $assignedTillsCount }} Till{{ $assignedTillsCount !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-gray-600 font-medium">No tellers available</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Quick Actions</h3>
            </div>
            
            <div class="space-y-4">
                <button wire:click="refreshTillData" class="w-full flex items-center p-4 text-sm text-gray-700 hover:text-gray-900 bg-gradient-to-r from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-blue-200">
                    <div class="p-2 bg-blue-500 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <span class="font-medium">Refresh Till Data</span>
                </button>
                
                <button class="w-full flex items-center p-4 text-sm text-gray-700 hover:text-gray-900 bg-gradient-to-r from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-green-200">
                    <div class="p-2 bg-green-500 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <span class="font-medium">Add New Till</span>
                </button>
                
                <button class="w-full flex items-center p-4 text-sm text-gray-700 hover:text-gray-900 bg-gradient-to-r from-orange-50 to-orange-100 hover:from-orange-100 hover:to-orange-200 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-orange-200">
                    <div class="p-2 bg-orange-500 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <span class="font-medium">Assignment Report</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Till Status Overview --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-br from-gray-500 to-gray-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">All Tills Status Overview</h3>
            </div>
            <button wire:click="refreshTillData" class="text-blue-600 hover:text-blue-800 text-sm font-medium bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-xl transition-all duration-200">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh Data
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($tillSummary as $till)
                <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-2xl p-6 border-l-4 {{ $till['status'] === 'open' ? 'border-green-400' : 'border-gray-400' }} hover:shadow-lg transition-all duration-200">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="text-lg font-bold text-gray-900">{{ $till['name'] }}</h4>
                            <p class="text-sm text-gray-600">Code: {{ $till['code'] ?? 'N/A' }}</p>
                        </div>
                        <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full {{ $till['status'] === 'open' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($till['status']) }}
                        </span>
                    </div>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 font-medium">Assigned to:</span>
                            <span class="font-bold text-gray-900">{{ $till['assigned_user'] ?? 'Unassigned' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 font-medium">Current Balance:</span>
                            <span class="font-bold text-blue-600">TZS {{ number_format($till['current_balance']) }}</span>
                        </div>
                        @if($till['status'] === 'open' && isset($till['opening_balance']))
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium">Opening Balance:</span>
                                <span class="font-bold text-green-600">TZS {{ number_format($till['opening_balance']) }}</span>
                            </div>
                        @endif
                        @if(isset($till['assigned_at']))
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium">Assigned Date:</span>
                                <span class="text-gray-800">{{ \Carbon\Carbon::parse($till['assigned_at'])->format('M d, Y') }}</span>
                            </div>
                        @endif
                    </div>
                    
                    @if($till['assigned_user'] === 'Unassigned')
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center text-orange-600 text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                Needs Assignment
                            </div>
                        </div>
                    @else
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center text-green-600 text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Properly Assigned
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <svg class="w-20 h-20 text-gray-400 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="text-xl font-bold text-gray-600 mb-2">No Tills Available</h3>
                    <p class="text-gray-500">Configure tills to start managing assignments</p>
                </div>
            @endforelse
        </div>
    </div>
</div> 