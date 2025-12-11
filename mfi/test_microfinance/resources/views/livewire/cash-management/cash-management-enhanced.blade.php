{{-- Enhanced Cash Management System - Compliant with User Journeys --}}
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-red-50">
    <div class="p-4 lg:p-6">
        {{-- Role-Based Header with Time-Aware Greeting --}}
        <div class="mb-6">
            <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        {{-- Dynamic Icon Based on User Role --}}
                        <div class="p-4 bg-gradient-to-br 
                            @if($userRole === 'teller') from-green-600 to-green-700
                            @elseif($userRole === 'head_teller') from-red-600 to-red-700
                            @elseif($userRole === 'vault_custodian') from-purple-600 to-purple-700
                            @elseif($userRole === 'branch_manager') from-indigo-600 to-indigo-700
                            @else from-gray-600 to-gray-700
                            @endif rounded-2xl shadow-xl">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($userRole === 'teller')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                @elseif($userRole === 'head_teller')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                @elseif($userRole === 'vault_custodian')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                @endif
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                @php
                                    $hour = now()->hour;
                                    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
                                @endphp
                                {{ $greeting }}, {{ Auth::user()->name }}
                            </h1>
                            <p class="text-gray-600 mt-1 text-lg">
                                @switch($userRole)
                                    @case('teller')
                                        Teller Operations Dashboard - {{ now()->format('l, F j, Y') }}
                                        @break
                                    @case('head_teller')
                                        Head Teller Control Center - Managing {{ $activeTellerCount ?? 0 }} Active Tellers
                                        @break
                                    @case('vault_custodian')
                                        Vault Management System - Security Level: Maximum
                                        @break
                                    @case('branch_manager')
                                        Branch Cash Operations Overview
                                        @break
                                    @default
                                        Cash Management System
                                @endswitch
                            </p>
                        </div>
                    </div>
                    
                    {{-- Real-Time Status Indicators --}}
                    <div class="flex items-center space-x-3">
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
                        
                        {{-- Quick Logout --}}
                        <button class="bg-red-50 text-red-600 rounded-xl px-4 py-2 border border-red-200 hover:bg-red-100 transition-colors">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span class="font-semibold">Sign Out</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Critical Alerts Section (Role-Specific) --}}
        @if($criticalAlerts && count($criticalAlerts) > 0)
            <div class="mb-6 space-y-3">
                @foreach($criticalAlerts as $alert)
                    <div class="bg-white rounded-2xl shadow-lg border-l-4 
                        @if($alert['type'] === 'critical') border-red-500 bg-red-50
                        @elseif($alert['type'] === 'warning') border-yellow-500 bg-yellow-50
                        @else border-red-500 bg-red-50
                        @endif p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 mr-4">
                                @if($alert['type'] === 'critical')
                                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($alert['type'] === 'warning')
                                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-bold 
                                    @if($alert['type'] === 'critical') text-red-900
                                    @elseif($alert['type'] === 'warning') text-yellow-900
                                    @else text-red-900
                                    @endif">
                                    {{ $alert['title'] }}
                                </h3>
                                <p class="text-sm 
                                    @if($alert['type'] === 'critical') text-red-700
                                    @elseif($alert['type'] === 'warning') text-yellow-700
                                    @else text-red-700
                                    @endif mt-1">
                                    {{ $alert['message'] }}
                                </p>
                            </div>
                            @if($alert['action'])
                                <button wire:click="{{ $alert['action'] }}" class="ml-4 px-4 py-2 bg-white rounded-lg border 
                                    @if($alert['type'] === 'critical') border-red-300 text-red-700 hover:bg-red-50
                                    @elseif($alert['type'] === 'warning') border-yellow-300 text-yellow-700 hover:bg-yellow-50
                                    @else border-red-300 text-red-700 hover:bg-red-50
                                    @endif text-sm font-semibold transition-colors">
                                    {{ $alert['actionLabel'] ?? 'Take Action' }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Main Layout with Role-Specific Navigation --}}
        <div class="flex gap-6">
            {{-- Enhanced Sidebar Navigation (Role-Aware) --}}
            <div class="w-80 bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                {{-- User Info Card --}}
                <div class="p-6 bg-gradient-to-br from-gray-50 to-red-50 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br 
                            @if($userRole === 'teller') from-green-400 to-green-600
                            @elseif($userRole === 'head_teller') from-red-400 to-red-600
                            @elseif($userRole === 'vault_custodian') from-purple-400 to-purple-600
                            @else from-gray-400 to-gray-600
                            @endif flex items-center justify-center text-white font-bold text-lg">
                            {{ substr(Auth::user()->name, 0, 2) }}
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-sm text-gray-600">{{ ucwords(str_replace('_', ' ', $userRole)) }}</p>
                            <p class="text-xs text-gray-500">ID: {{ Auth::user()->employee_id ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Navigation Menu (Role-Specific) --}}
                <div class="p-4">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4 px-2">Operations Menu</h3>
                    
                    <nav class="space-y-2">
                        @if($userRole === 'teller')
                            {{-- Teller Specific Menu Items --}}
                            @include('livewire.cash-management.menus.teller-menu')
                        @elseif($userRole === 'head_teller')
                            {{-- Head Teller Specific Menu Items --}}
                            @include('livewire.cash-management.menus.head-teller-menu')
                        @elseif($userRole === 'vault_custodian')
                            {{-- Vault Custodian Specific Menu Items --}}
                            @include('livewire.cash-management.menus.vault-custodian-menu')
                        @elseif($userRole === 'branch_manager')
                            {{-- Branch Manager Specific Menu Items --}}
                            @include('livewire.cash-management.menus.branch-manager-menu')
                        @else
                            {{-- Default Menu Items --}}
                            @include('livewire.cash-management.menus.default-menu')
                        @endif
                    </nav>
                </div>

                {{-- Quick Stats (Role-Specific) --}}
                <div class="p-4 border-t border-gray-200 bg-gradient-to-br from-gray-50 to-red-50">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4 px-2">Quick Stats</h3>
                    
                    @if($userRole === 'teller')
                        {{-- Teller Stats --}}
                        <div class="space-y-3">
                            <div class="bg-white rounded-xl p-3 border border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">My Till Balance</span>
                                    <span class="font-bold text-green-600">{{ number_format($myTillBalance ?? 0) }}</span>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl p-3 border border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Today's Transactions</span>
                                    <span class="font-bold text-red-600">{{ $todayTransactionCount ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl p-3 border border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Customers Served</span>
                                    <span class="font-bold text-purple-600">{{ $customersServed ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    @elseif($userRole === 'head_teller')
                        {{-- Head Teller Stats --}}
                        <div class="space-y-3">
                            <div class="bg-white rounded-xl p-3 border border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Vault Balance</span>
                                    <span class="font-bold text-green-600">{{ number_format($vaultBalance ?? 0) }}</span>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl p-3 border border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Active Tellers</span>
                                    <span class="font-bold text-red-600">{{ $activeTellerCount ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl p-3 border border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Pending Approvals</span>
                                    <span class="font-bold text-orange-600">{{ $pendingApprovals ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    @elseif($userRole === 'vault_custodian')
                        {{-- Vault Custodian Stats --}}
                        <div class="space-y-3">
                            <div class="bg-white rounded-xl p-3 border border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Vault Status</span>
                                    <span class="font-bold {{ $vaultStatus === 'open' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ ucfirst($vaultStatus ?? 'closed') }}
                                    </span>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl p-3 border border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Last Audit</span>
                                    <span class="font-bold text-red-600">{{ $lastAudit ?? 'Today' }}</span>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl p-3 border border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Security Level</span>
                                    <span class="font-bold text-green-600">Maximum</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Main Content Area (Dynamic Based on Active Tab and Role) --}}
            <div class="flex-1">
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden min-h-[85vh]">
                    {{-- Content Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 via-white to-red-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">
                                    {{ $this->getTabTitle() }}
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    {{ $this->getTabDescription() }}
                                </p>
                            </div>
                            
                            {{-- Action Buttons Based on Context --}}
                            <div class="flex items-center space-x-3">
                                @if($activeTab === 'morning-setup' && $userRole === 'teller')
                                    <button wire:click="requestTillOpening" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold">
                                        Request Till Opening
                                    </button>
                                @elseif($activeTab === 'vault-opening' && $userRole === 'vault_custodian')
                                    <button wire:click="initiateVaultOpening" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold">
                                        Initiate Vault Opening
                                    </button>
                                @elseif($activeTab === 'end-of-day' && $userRole === 'teller')
                                    <button wire:click="startEndOfDay" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold">
                                        Start EOD Process
                                    </button>
                                @endif
                                
                                {{-- Refresh Button --}}
                                <button wire:click="refreshData" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
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
                                <p class="text-gray-600 font-medium">Loading {{ $this->getTabTitle() }}...</p>
                            </div>
                        </div>

                        {{-- Content Based on Active Tab and User Role --}}
                        <div wire:loading.remove wire:target="setActiveTab">
                            @if($userRole === 'teller')
                                @include('livewire.cash-management.journeys.teller-journey')
                            @elseif($userRole === 'head_teller')
                                @include('livewire.cash-management.journeys.head-teller-journey')
                            @elseif($userRole === 'vault_custodian')
                                @include('livewire.cash-management.journeys.vault-custodian-journey')
                            @elseif($userRole === 'branch_manager')
                                @include('livewire.cash-management.journeys.branch-manager-journey')
                            @elseif($userRole === 'auditor')
                                @include('livewire.cash-management.journeys.auditor-journey')
                            @else
                                @include('livewire.cash-management.journeys.default-journey')
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @include('livewire.cash-management.modals.dual-control-modal')
    @include('livewire.cash-management.modals.approval-modal')
    @include('livewire.cash-management.modals.variance-explanation-modal')
    @include('livewire.cash-management.modals.denomination-breakdown-modal')
</div>

{{-- Push Scripts --}}
@push('scripts')
<script nonce="{{ csp_nonce() }}">
    // Auto-refresh for real-time updates
    setInterval(() => {
        @this.refreshStats();
    }, 30000); // Refresh every 30 seconds

    // Handle session timeout warning
    let warningTimer;
    let logoutTimer;
    
    function resetTimers() {
        clearTimeout(warningTimer);
        clearTimeout(logoutTimer);
        
        // Warn after 25 minutes of inactivity
        warningTimer = setTimeout(() => {
            if (confirm('Your session will expire in 5 minutes. Do you want to continue?')) {
                @this.extendSession();
            }
        }, 25 * 60 * 1000);
        
        // Auto logout after 30 minutes
        logoutTimer = setTimeout(() => {
            window.location.href = '/logout';
        }, 30 * 60 * 1000);
    }
    
    // Reset timers on user activity
    document.addEventListener('click', resetTimers);
    document.addEventListener('keypress', resetTimers);
    
    // Initialize timers
    resetTimers();

    // Handle dual control authentication
    window.addEventListener('dual-control-required', event => {
        // Show dual control modal
        Livewire.emit('showDualControlModal', event.detail);
    });

    // Handle approval notifications
    window.addEventListener('approval-received', event => {
        // Show notification
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('New Approval Request', {
                body: event.detail.message,
                icon: '/icon.png'
            });
        }
    });
</script>
@endpush

{{-- Push Styles --}}
@push('styles')
<style nonce="{{ csp_nonce() }}">
    /* Custom scrollbar for sidebar */
    .sidebar-scroll::-webkit-scrollbar {
        width: 6px;
    }
    
    .sidebar-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .sidebar-scroll::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    .sidebar-scroll::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Pulse animation for critical alerts */
    @keyframes pulse-red {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    
    .pulse-red {
        animation: pulse-red 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    /* Smooth transitions */
    .transition-all-smooth {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>
@endpush