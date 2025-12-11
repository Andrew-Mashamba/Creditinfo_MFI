<div class="space-y-8">
    {{-- Enhanced Approvals Hero Section --}}
    <div class="bg-gradient-to-br from-orange-500 via-orange-600 to-orange-700 rounded-3xl p-8 text-white shadow-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold mb-2">Pending Approvals Center</h3>
                <p class="text-orange-100 text-lg">Review and process pending cash management requests efficiently</p>
            </div>
            <div class="text-right">
                <div class="bg-white bg-opacity-20 rounded-2xl p-6 backdrop-blur-sm">
                    <h4 class="text-lg font-semibold mb-2">Pending Items</h4>
                    <p class="text-4xl font-bold">{{ count($pendingApprovals ?? []) }}</p>
                    <p class="text-orange-100 text-sm">Awaiting your review</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Approval Stats Dashboard --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-6 border border-red-200">
            <div class="flex items-center">
                <div class="p-3 bg-red-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-red-700">Urgent Requests</p>
                    <p class="text-2xl font-bold text-red-900">{{ collect($pendingApprovals ?? [])->where('urgency', 'urgent')->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl p-6 border border-yellow-200">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-yellow-700">Till Requests</p>
                    <p class="text-2xl font-bold text-yellow-900">{{ collect($pendingApprovals ?? [])->where('process_code', 'TILL_REPLENISHMENT')->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
            <div class="flex items-center">
                <div class="p-3 bg-blue-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-700">Vault Transfers</p>
                    <p class="text-2xl font-bold text-blue-900">{{ collect($pendingApprovals ?? [])->whereIn('process_code', ['VAULT_TO_TILL', 'TILL_TO_VAULT'])->count() }}</p>
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
                    <p class="text-sm font-medium text-green-700">Total Amount</p>
                    <p class="text-2xl font-bold text-green-900">TZS {{ number_format(collect($pendingApprovals ?? [])->sum(function($approval) { return json_decode($approval->edit_package, true)['amount'] ?? 0; })) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Approvals List --}}
    @if(count($pendingApprovals ?? []) > 0)
        <div class="space-y-6">
            @foreach($pendingApprovals as $approval)
                @php
                    $data = json_decode($approval->edit_package, true);
                    $amount = $data['amount'] ?? 0;
                    $urgency = $data['urgency'] ?? 'normal';
                    
                    // Determine card color based on process type
                    $cardColors = [
                        'VAULT_TO_TILL' => ['bg' => 'from-blue-50 to-blue-100', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'icon' => 'bg-blue-500'],
                        'TILL_TO_VAULT' => ['bg' => 'from-orange-50 to-orange-100', 'border' => 'border-orange-200', 'text' => 'text-orange-800', 'icon' => 'bg-orange-500'],
                        'TILL_REPLENISHMENT' => ['bg' => 'from-green-50 to-green-100', 'border' => 'border-green-200', 'text' => 'text-green-800', 'icon' => 'bg-green-500'],
                        'VAULT_REPLENISHMENT' => ['bg' => 'from-purple-50 to-purple-100', 'border' => 'border-purple-200', 'text' => 'text-purple-800', 'icon' => 'bg-purple-500'],
                        'TILL_CLOSURE' => ['bg' => 'from-red-50 to-red-100', 'border' => 'border-red-200', 'text' => 'text-red-800', 'icon' => 'bg-red-500'],
                        'CIT_TRANSFER' => ['bg' => 'from-indigo-50 to-indigo-100', 'border' => 'border-indigo-200', 'text' => 'text-indigo-800', 'icon' => 'bg-indigo-500'],
                    ];
                    
                    $colors = $cardColors[$approval->process_code] ?? ['bg' => 'from-gray-50 to-gray-100', 'border' => 'border-gray-200', 'text' => 'text-gray-800', 'icon' => 'bg-gray-500'];
                @endphp
                
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300">
                    <div class="bg-gradient-to-r {{ $colors['bg'] }} {{ $colors['border'] }} border-b p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="p-3 {{ $colors['icon'] }} rounded-2xl shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @switch($approval->process_code)
                                            @case('VAULT_TO_TILL')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                                @break
                                            @case('TILL_TO_VAULT')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                                @break
                                            @case('TILL_REPLENISHMENT')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                @break
                                            @case('TILL_CLOSURE')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                @break
                                            @default
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        @endswitch
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-xl font-bold {{ $colors['text'] }}">{{ $approval->process_name }}</h3>
                                    <p class="text-sm {{ $colors['text'] }} opacity-80">{{ $approval->process_description }}</p>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                @if($urgency === 'urgent' || $urgency === 'emergency')
                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 mb-2">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        {{ ucfirst($urgency) }}
                                    </div>
                                @endif
                                <p class="text-2xl font-bold {{ $colors['text'] }}">TZS {{ number_format($amount) }}</p>
                                <p class="text-sm {{ $colors['text'] }} opacity-70">{{ $approval->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <h4 class="text-lg font-bold text-gray-900 mb-3">Request Details</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 font-medium">Requested by:</span>
                                        <span class="font-bold text-gray-900">{{ $approval->user->name ?? 'Unknown' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 font-medium">Date:</span>
                                        <span class="text-gray-800">{{ $approval->created_at->format('M d, Y H:i') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 font-medium">Process Code:</span>
                                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $approval->process_code }}</span>
                                    </div>
                                    @if(isset($data['reason']))
                                        <div class="mt-3">
                                            <span class="text-gray-600 font-medium">Reason:</span>
                                            <p class="text-gray-800 mt-1">{{ $data['reason'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="text-lg font-bold text-gray-900 mb-3">Additional Information</h4>
                                <div class="space-y-2 text-sm">
                                    @if(isset($data['till_account']))
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Till Account:</span>
                                            <span class="font-mono text-xs bg-blue-100 px-2 py-1 rounded">{{ $data['till_account'] }}</span>
                                        </div>
                                    @endif
                                    @if(isset($data['vault_account']))
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Vault Account:</span>
                                            <span class="font-mono text-xs bg-green-100 px-2 py-1 rounded">{{ $data['vault_account'] }}</span>
                                        </div>
                                    @endif
                                    @if(isset($data['current_balance']))
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Current Balance:</span>
                                            <span class="font-bold text-blue-600">TZS {{ number_format($data['current_balance']) }}</span>
                                        </div>
                                    @endif
                                    @if(isset($data['notes']))
                                        <div class="mt-3">
                                            <span class="text-gray-600 font-medium">Notes:</span>
                                            <p class="text-gray-800 mt-1">{{ $data['notes'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-6">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    <p><strong>Approval Required:</strong> {{ $approval->approval_process_description }}</p>
                                </div>
                                
                                <div class="flex space-x-3">
                                    <button wire:click="rejectApproval({{ $approval->id }})" 
                                            class="px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 focus:outline-none focus:ring-4 focus:ring-red-200 transition-all duration-200 font-bold shadow-lg">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Reject
                                    </button>
                                    
                                    <button wire:click="approveRequest({{ $approval->id }})" 
                                            class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-4 focus:ring-green-200 transition-all duration-200 font-bold shadow-lg">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Approve
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- No Pending Approvals State --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-12 text-center">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full mb-6">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-700 mb-3">All Caught Up!</h3>
            <p class="text-gray-600 text-lg mb-6">No pending approvals at this time. All requests have been processed.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-2xl mx-auto">
                <div class="bg-blue-50 rounded-2xl p-4 text-center">
                    <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <p class="text-blue-800 font-medium text-sm">Auto-refresh every 30s</p>
                </div>
                
                <div class="bg-green-50 rounded-2xl p-4 text-center">
                    <svg class="w-8 h-8 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-5a7.5 7.5 0 00-15 0v2.5"></path>
                    </svg>
                    <p class="text-green-800 font-medium text-sm">Real-time notifications</p>
                </div>
                
                <div class="bg-purple-50 rounded-2xl p-4 text-center">
                    <svg class="w-8 h-8 text-purple-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-purple-800 font-medium text-sm">Audit trail available</p>
                </div>
            </div>
        </div>
    @endif
</div> 