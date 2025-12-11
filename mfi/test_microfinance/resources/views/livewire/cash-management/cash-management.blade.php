{{-- Enhanced Cash Management System with Complete User Journeys (Testing Mode - All Features Enabled) --}}
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-red-50">
    <div class="p-4 lg:p-6">
        {{-- Header with Testing Mode Indicator --}}
        <div class="mb-6">
            <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        {{-- Icon --}}
                        <div class="p-4 bg-gradient-to-br from-red-600 to-red-700 rounded-2xl shadow-xl">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                @php
                                    $hour = now()->hour;
                                    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
                                @endphp
                                {{ $greeting }}, {{ Auth::user()->name ?? 'Test User' }}
                            </h1>
                            <p class="text-gray-600 mt-1 text-lg">
                                Cash Management System - Full Access Mode (Testing)
                            </p>
                        </div>
                    </div>
                    
                    {{-- Status Indicators --}}
                    <div class="flex items-center space-x-3">
                        {{-- Testing Mode Badge --}}
                        <div class="bg-yellow-100 rounded-xl px-4 py-2 border border-yellow-300">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-.834-2.024-.834-2.732 0L4.268 16.5c-.77.833.192 3 1.732 3z"/>
                                </svg>
                                <span class="text-yellow-700 font-semibold">Testing Mode</span>
                            </div>
                        </div>
                        
                        {{-- System Time --}}
                        <div class="bg-gray-50 rounded-xl px-4 py-2 border border-gray-200">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-gray-700 font-semibold" wire:poll.1s>{{ now()->format('H:i:s') }}</span>
                            </div>
                        </div>
                        
                        {{-- Branch Status --}}
                        <div class="bg-green-50 rounded-xl px-4 py-2 border border-green-200">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-green-700 font-semibold">Branch: {{ $branchName ?? 'Main' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sample Alerts Section --}}
        <div class="mb-6 space-y-3">
            {{-- Critical Alert Example --}}
            <div class="bg-white rounded-2xl shadow-lg border-l-4 border-red-500 bg-red-50 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-4">
                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-red-900">Sample Critical Alert</h3>
                        <p class="text-sm text-red-700 mt-1">Till balance exceeds maximum limit - Transfer to vault required</p>
                    </div>
                </div>
            </div>
            
            {{-- Warning Alert Example --}}
            <div class="bg-white rounded-2xl shadow-lg border-l-4 border-yellow-500 bg-yellow-50 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-yellow-900">Sample Warning</h3>
                        <p class="text-sm text-yellow-700 mt-1">Vault balance below optimal level - Consider replenishment</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Layout --}}
        <div class="flex gap-6">
            {{-- Sidebar Navigation with All Menu Items --}}
            <div class="w-80 bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                {{-- User Info Card --}}
                <div class="p-6 bg-gradient-to-br from-gray-50 to-red-50 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center text-white font-bold text-lg">
                            {{ substr(Auth::user()->name ?? 'TU', 0, 2) }}
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">{{ Auth::user()->name ?? 'Test User' }}</p>
                            <p class="text-sm text-gray-600">All Roles Enabled</p>
                            <p class="text-xs text-gray-500">Testing Mode Active</p>
                        </div>
                    </div>
                </div>

                {{-- Combined Navigation Menu - All Options Visible --}}
                <div class="p-4 max-h-[calc(100vh-300px)] overflow-y-auto">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4 px-2">All Operations</h3>
                    
                    <nav class="space-y-2">
                        @php
                            // Combined menu items from all roles for testing
                            $allMenuItems = [
                                // Teller Operations
                                ['id' => 'morning-setup', 'label' => 'Morning Setup', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'description' => 'Open till & prepare', 'color' => 'green', 'badge' => 2],
                                ['id' => 'customer-transactions', 'label' => 'Customer Transactions', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'description' => 'Deposits & withdrawals', 'color' => 'red', 'badge' => 5],
                                ['id' => 'cash-management', 'label' => 'Cash Management', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1', 'description' => 'Buy/sell from vault', 'color' => 'purple', 'badge' => null],
                                ['id' => 'end-of-day', 'label' => 'End of Day', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'description' => 'Balance & close', 'color' => 'red', 'badge' => 1],
                                
                                // Head Teller Operations
                                ['id' => 'vault-opening', 'label' => 'Vault Opening', 'icon' => 'M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z', 'description' => 'Dual control access', 'color' => 'purple', 'badge' => null],
                                ['id' => 'teller-distribution', 'label' => 'Teller Distribution', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'description' => 'Distribute to tellers', 'color' => 'indigo', 'badge' => 3],
                                ['id' => 'vault-operations', 'label' => 'Vault Operations', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'description' => 'Vault management', 'color' => 'teal', 'badge' => null],
                                ['id' => 'approval-queue', 'label' => 'Approval Queue', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'description' => 'Pending approvals', 'color' => 'orange', 'badge' => 7],
                                
                                // Additional Operations
                                ['id' => 'till-assignment', 'label' => 'Till Assignment', 'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'description' => 'Assign tills', 'color' => 'pink', 'badge' => 2],
                                ['id' => 'cit', 'label' => 'CIT Operations', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'description' => 'Cash transport', 'color' => 'yellow', 'badge' => null],
                                ['id' => 'movements', 'label' => 'Cash Movements', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'description' => 'Transaction history', 'color' => 'cyan', 'badge' => null],
                                ['id' => 'reports', 'label' => 'Reports & Analytics', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'description' => 'Generate reports', 'color' => 'emerald', 'badge' => null],
                                ['id' => 'reconciliation', 'label' => 'Reconciliation', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'description' => 'Balance checks', 'color' => 'gray', 'badge' => null],
                            ];
                            
                            $colorClasses = [
                                'green' => ['bg' => 'bg-green-600', 'text' => 'text-green-600', 'light' => 'bg-green-50', 'hover' => 'hover:bg-green-100'],
                                'red' => ['bg' => 'bg-red-600', 'text' => 'text-red-600', 'light' => 'bg-red-50', 'hover' => 'hover:bg-red-100'],
                                'purple' => ['bg' => 'bg-purple-600', 'text' => 'text-purple-600', 'light' => 'bg-purple-50', 'hover' => 'hover:bg-purple-100'],
                                'red' => ['bg' => 'bg-red-600', 'text' => 'text-red-600', 'light' => 'bg-red-50', 'hover' => 'hover:bg-red-100'],
                                'indigo' => ['bg' => 'bg-indigo-600', 'text' => 'text-indigo-600', 'light' => 'bg-indigo-50', 'hover' => 'hover:bg-indigo-100'],
                                'teal' => ['bg' => 'bg-teal-600', 'text' => 'text-teal-600', 'light' => 'bg-teal-50', 'hover' => 'hover:bg-teal-100'],
                                'orange' => ['bg' => 'bg-orange-600', 'text' => 'text-orange-600', 'light' => 'bg-orange-50', 'hover' => 'hover:bg-orange-100'],
                                'pink' => ['bg' => 'bg-pink-600', 'text' => 'text-pink-600', 'light' => 'bg-pink-50', 'hover' => 'hover:bg-pink-100'],
                                'yellow' => ['bg' => 'bg-yellow-600', 'text' => 'text-yellow-600', 'light' => 'bg-yellow-50', 'hover' => 'hover:bg-yellow-100'],
                                'cyan' => ['bg' => 'bg-cyan-600', 'text' => 'text-cyan-600', 'light' => 'bg-cyan-50', 'hover' => 'hover:bg-cyan-100'],
                                'emerald' => ['bg' => 'bg-emerald-600', 'text' => 'text-emerald-600', 'light' => 'bg-emerald-50', 'hover' => 'hover:bg-emerald-100'],
                                'gray' => ['bg' => 'bg-gray-600', 'text' => 'text-gray-600', 'light' => 'bg-gray-50', 'hover' => 'hover:bg-gray-100'],
                            ];
                        @endphp
                        
                        @foreach($allMenuItems as $item)
                            @php
                                $isActive = ($activeTab ?? 'customer-transactions') === $item['id'];
                                $colors = $colorClasses[$item['color']];
                            @endphp
                            
                            <button
                                wire:click="setActiveTab('{{ $item['id'] }}')"
                                class="w-full group transition-all duration-200"
                            >
                                <div class="flex items-center p-3 rounded-xl transition-all duration-200
                                    @if($isActive)
                                        {{ $colors['bg'] }} text-white shadow-lg
                                    @else
                                        {{ $colors['light'] }} {{ $colors['hover'] }} border border-transparent hover:border-gray-200
                                    @endif">
                                    
                                    {{-- Icon --}}
                                    <div class="mr-3">
                                        <svg class="w-5 h-5 @if($isActive) text-white @else {{ $colors['text'] }} @endif" 
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                        </svg>
                                    </div>
                                    
                                    {{-- Content --}}
                                    <div class="flex-1 text-left">
                                        <div class="font-semibold text-sm @if($isActive) text-white @else text-gray-900 @endif">
                                            {{ $item['label'] }}
                                        </div>
                                        <div class="text-xs @if($isActive) text-white opacity-90 @else text-gray-600 @endif">
                                            {{ $item['description'] }}
                                        </div>
                                    </div>
                                    
                                    {{-- Badge --}}
                                    @if($item['badge'])
                                        <div class="ml-2">
                                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                                                {{ $item['badge'] }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </nav>
                </div>

                {{-- Quick Stats --}}
                <div class="p-4 border-t border-gray-200 bg-gradient-to-br from-gray-50 to-red-50">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4 px-2">System Stats</h3>
                    
                    <div class="space-y-3">
                        <div class="bg-white rounded-xl p-3 border border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Vault Balance</span>
                                <span class="font-bold text-green-600">{{ number_format($vaultBalance ?? 50000000) }}</span>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl p-3 border border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Active Tills</span>
                                <span class="font-bold text-red-600">{{ collect($tillSummary ?? [])->where('status', 'open')->count() ?: 5 }}/8</span>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl p-3 border border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Pending Items</span>
                                <span class="font-bold text-orange-600">{{ count($pendingApprovals ?? []) ?: 12 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Content Area --}}
            <div class="flex-1">
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden min-h-[85vh]">
                    {{-- Content Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 via-white to-red-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">
                                    @switch($activeTab ?? 'customer-transactions')
                                        @case('morning-setup') Morning Setup @break
                                        @case('customer-transactions') Customer Transactions @break
                                        @case('cash-management') Cash Management @break
                                        @case('end-of-day') End of Day Process @break
                                        @case('vault-opening') Vault Opening @break
                                        @case('teller-distribution') Teller Distribution @break
                                        @case('vault-operations') Vault Operations @break
                                        @case('approval-queue') Approval Queue @break
                                        @case('till-assignment') Till Assignment @break
                                        @case('cit') CIT Operations @break
                                        @case('movements') Cash Movements @break
                                        @case('reports') Reports & Analytics @break
                                        @case('reconciliation') Reconciliation @break
                                        @default Customer Transactions
                                    @endswitch
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    @switch($activeTab ?? 'customer-transactions')
                                        @case('morning-setup') Complete morning procedures and prepare for operations @break
                                        @case('customer-transactions') Process customer deposits and withdrawals @break
                                        @case('cash-management') Manage till balance and vault transfers @break
                                        @case('end-of-day') Complete end of day reconciliation @break
                                        @case('vault-opening') Open vault with dual control procedures @break
                                        @case('teller-distribution') Distribute cash to tellers @break
                                        @case('vault-operations') Manage vault inventory and operations @break
                                        @case('approval-queue') Review and approve pending requests @break
                                        @case('till-assignment') Assign tills to tellers @break
                                        @case('cit') Manage cash-in-transit operations @break
                                        @case('movements') View cash movement history @break
                                        @case('reports') Generate reports and analytics @break
                                        @case('reconciliation') Perform reconciliation @break
                                        @default Process customer transactions
                                    @endswitch
                                </p>
                            </div>
                            
                            {{-- Action Buttons --}}
                            <div class="flex items-center space-x-3">
                                <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    New Action
                                </button>
                                <button wire:click="$refresh" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Content Area --}}
                    <div class="p-6">
                        {{-- Loading State --}}
                        <div wire:loading.delay.longer wire:target="setActiveTab" class="flex items-center justify-center min-h-[400px]">
                            <div class="text-center">
                                <svg class="w-16 h-16 text-red-600 animate-spin mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-gray-600 font-medium">Loading...</p>
                            </div>
                        </div>

                        {{-- Content - Show Teller Journey for all tabs during testing --}}
                        <div wire:loading.remove wire:target="setActiveTab">
                            @include('livewire.cash-management.journeys.teller-journey')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Push Scripts --}}
@push('scripts')
<script nonce="{{ csp_nonce() }}">
    // Auto-refresh for real-time updates
    setInterval(() => {
        @this.call('$refresh');
    }, 30000); // Refresh every 30 seconds

    // Handle session timeout warning (disabled for testing)
    console.log('Cash Management System - Testing Mode Active');
</script>
@endpush

{{-- Push Styles --}}
@push('styles')
<style nonce="{{ csp_nonce() }}">
    /* Custom scrollbar for sidebar */
    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Smooth transitions */
    .transition-all-smooth {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Testing mode indicator */
    @keyframes pulse-yellow {
        0%, 100% {
            border-color: rgb(252 211 77);
        }
        50% {
            border-color: rgb(251 191 36);
        }
    }
    
    .testing-mode {
        animation: pulse-yellow 2s infinite;
    }
</style>
@endpush