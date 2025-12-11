{{-- Vault Custodian Role Navigation Menu --}}
{{-- Following the Vault Custodian Journey from docs/cash-management-user-journeys.md --}}

@php
    $menuItems = [
        [
            'id' => 'security-verification',
            'label' => 'Security Check',
            'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            'description' => 'Verify security protocols',
            'color' => 'red',
            'available_from' => '06:00',
            'available_to' => '07:00',
            'badge' => $securityAlerts ?? null
        ],
        [
            'id' => 'vault-opening',
            'label' => 'Vault Opening',
            'icon' => 'M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z',
            'description' => 'Dual control opening',
            'color' => 'purple',
            'available_from' => '06:30',
            'available_to' => '07:30',
            'badge' => null
        ],
        [
            'id' => 'inventory-count',
            'label' => 'Inventory Count',
            'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z',
            'description' => 'Count & verify inventory',
            'color' => 'blue',
            'available_from' => '07:00',
            'available_to' => '08:00',
            'badge' => $pendingCounts ?? null
        ],
        [
            'id' => 'vault-operations',
            'label' => 'Vault Operations',
            'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            'description' => 'Process transactions',
            'color' => 'indigo',
            'available_from' => '08:00',
            'available_to' => '17:00',
            'badge' => $pendingRequests ?? null
        ],
        [
            'id' => 'currency-processing',
            'label' => 'Currency Processing',
            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
            'description' => 'Sort & strap currency',
            'color' => 'green',
            'available_from' => '08:00',
            'available_to' => '17:00',
            'badge' => null
        ],
        [
            'id' => 'atm-preparation',
            'label' => 'ATM Preparation',
            'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
            'description' => 'Prepare ATM cassettes',
            'color' => 'teal',
            'available_from' => '08:00',
            'available_to' => '16:00',
            'badge' => $atmReplenishments ?? null
        ],
        [
            'id' => 'federal-reserve',
            'label' => 'Fed Shipments',
            'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
            'description' => 'Prepare Fed shipments',
            'color' => 'orange',
            'available_from' => '10:00',
            'available_to' => '15:00',
            'badge' => $fedShipments ?? null
        ],
        [
            'id' => 'vault-audit',
            'label' => 'Vault Audit',
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            'description' => 'Perform audit checks',
            'color' => 'pink',
            'available_from' => '08:00',
            'available_to' => '17:00',
            'badge' => null
        ],
        [
            'id' => 'vault-closing',
            'label' => 'Vault Closing',
            'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
            'description' => 'Secure vault for night',
            'color' => 'red',
            'available_from' => '17:00',
            'available_to' => '18:00',
            'badge' => $closingTasks ?? null
        ],
        [
            'id' => 'security-logs',
            'label' => 'Security Logs',
            'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
            'description' => 'Review access logs',
            'color' => 'gray',
            'available_from' => '08:00',
            'available_to' => '18:00',
            'badge' => null
        ]
    ];
    
    $currentTime = now()->format('H:i');
    
    $colorClasses = [
        'red' => ['bg' => 'bg-red-600', 'text' => 'text-red-600', 'light' => 'bg-red-50', 'hover' => 'hover:bg-red-100'],
        'purple' => ['bg' => 'bg-purple-600', 'text' => 'text-purple-600', 'light' => 'bg-purple-50', 'hover' => 'hover:bg-purple-100'],
        'blue' => ['bg' => 'bg-blue-900', 'text' => 'text-blue-600', 'light' => 'bg-blue-50', 'hover' => 'hover:bg-blue-100'],
        'indigo' => ['bg' => 'bg-indigo-600', 'text' => 'text-indigo-600', 'light' => 'bg-indigo-50', 'hover' => 'hover:bg-indigo-100'],
        'green' => ['bg' => 'bg-green-600', 'text' => 'text-green-600', 'light' => 'bg-green-50', 'hover' => 'hover:bg-green-100'],
        'teal' => ['bg' => 'bg-teal-600', 'text' => 'text-teal-600', 'light' => 'bg-teal-50', 'hover' => 'hover:bg-teal-100'],
        'orange' => ['bg' => 'bg-orange-600', 'text' => 'text-orange-600', 'light' => 'bg-orange-50', 'hover' => 'hover:bg-orange-100'],
        'pink' => ['bg' => 'bg-pink-600', 'text' => 'text-pink-600', 'light' => 'bg-pink-50', 'hover' => 'hover:bg-pink-100'],
        'gray' => ['bg' => 'bg-gray-600', 'text' => 'text-gray-600', 'light' => 'bg-gray-50', 'hover' => 'hover:bg-gray-100'],
    ];
@endphp

@foreach($menuItems as $item)
    @php
        $isActive = $activeTab === $item['id'];
        $colors = $colorClasses[$item['color']];
        $isAvailable = true;
        
        if (isset($item['available_from']) && isset($item['available_to'])) {
            $isAvailable = $currentTime >= $item['available_from'] && $currentTime <= $item['available_to'];
        }
        
        // Special conditions for Vault Custodian
        if ($item['id'] === 'vault-opening' && $vaultStatus === 'open') {
            $isAvailable = false;
        }
        if ($item['id'] === 'vault-closing' && $vaultStatus === 'closed') {
            $isAvailable = false;
        }
    @endphp
    
    <button
        wire:click="setActiveTab('{{ $item['id'] }}')"
        @if(!$isAvailable) disabled @endif
        class="w-full group transition-all duration-200 @if(!$isAvailable) opacity-50 cursor-not-allowed @endif"
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
            @if($item['badge'] && $item['badge'] > 0)
                <div class="ml-2">
                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                        {{ $item['badge'] }}
                    </span>
                </div>
            @endif
        </div>
    </button>
@endforeach

{{-- Vault Status Display --}}
<div class="mt-6 pt-4 border-t border-gray-200">
    <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-4 border border-purple-200">
        <h4 class="text-xs font-bold text-purple-800 uppercase tracking-wider mb-3">Vault Status</h4>
        
        <div class="space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Status:</span>
                <span class="font-bold text-sm {{ $vaultStatus === 'open' ? 'text-green-600' : 'text-red-600' }}">
                    {{ ucfirst($vaultStatus ?? 'closed') }}
                </span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Dual Control:</span>
                <span class="font-bold text-sm {{ $dualControlActive ? 'text-green-600' : 'text-gray-400' }}">
                    {{ $dualControlActive ? 'Active' : 'Inactive' }}
                </span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Last Audit:</span>
                <span class="font-bold text-sm text-blue-600">
                    {{ $lastAuditTime ?? 'Today 08:00' }}
                </span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Security Level:</span>
                <span class="font-bold text-sm text-green-600">Maximum</span>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions for Vault Custodian --}}
<div class="mt-4">
    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 px-2">Emergency Actions</h4>
    
    <div class="space-y-2">
        {{-- Panic Button --}}
        <button wire:click="activatePanic" 
                class="w-full flex items-center p-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">Emergency Alert</span>
        </button>
        
        {{-- Request Dual Control --}}
        <button wire:click="requestDualControl" 
                class="w-full flex items-center p-2 text-sm text-gray-700 hover:text-gray-900 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <span class="font-medium">Request Partner</span>
        </button>
    </div>
</div>