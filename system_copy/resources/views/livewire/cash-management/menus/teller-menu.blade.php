{{-- Teller Role Navigation Menu --}}
{{-- Following the Teller Journey from docs/cash-management-user-journeys.md --}}

@php
    $menuItems = [
        [
            'id' => 'morning-setup',
            'label' => 'Morning Setup',
            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'description' => 'Open till & prepare for day',
            'color' => 'green',
            'available_from' => '06:30',
            'available_to' => '08:00',
            'badge' => $morningTasksPending ?? null
        ],
        [
            'id' => 'customer-transactions',
            'label' => 'Customer Transactions',
            'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
            'description' => 'Process deposits & withdrawals',
            'color' => 'blue',
            'available_from' => '08:00',
            'available_to' => '17:00',
            'badge' => $queueCount ?? null
        ],
        [
            'id' => 'cash-management',
            'label' => 'Cash Management',
            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
            'description' => 'Buy/sell from vault',
            'color' => 'purple',
            'available_from' => '08:00',
            'available_to' => '17:00',
            'badge' => $cashRequestsPending ?? null
        ],
        [
            'id' => 'till-transfers',
            'label' => 'Till Transfers',
            'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
            'description' => 'Inter-teller transfers',
            'color' => 'indigo',
            'available_from' => '08:00',
            'available_to' => '17:00',
            'badge' => null
        ],
        [
            'id' => 'balance-inquiry',
            'label' => 'Balance & Status',
            'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
            'description' => 'Check till balance & limits',
            'color' => 'teal',
            'available_from' => '08:00',
            'available_to' => '17:30',
            'badge' => null
        ],
        [
            'id' => 'denomination-management',
            'label' => 'Denominations',
            'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
            'description' => 'Manage denomination mix',
            'color' => 'orange',
            'available_from' => '08:00',
            'available_to' => '17:00',
            'badge' => null
        ],
        [
            'id' => 'end-of-day',
            'label' => 'End of Day',
            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'description' => 'Balance & close till',
            'color' => 'red',
            'available_from' => '16:30',
            'available_to' => '18:00',
            'badge' => $eodTasksPending ?? null
        ],
        [
            'id' => 'transaction-history',
            'label' => 'My Transactions',
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
            'description' => 'View transaction log',
            'color' => 'gray',
            'available_from' => '08:00',
            'available_to' => '18:00',
            'badge' => null
        ]
    ];
    
    $currentTime = now()->format('H:i');
    
    // Color classes mapping
    $colorClasses = [
        'green' => ['bg' => 'bg-green-600', 'text' => 'text-green-600', 'light' => 'bg-green-50', 'hover' => 'hover:bg-green-100'],
        'blue' => ['bg' => 'bg-blue-900', 'text' => 'text-blue-600', 'light' => 'bg-blue-50', 'hover' => 'hover:bg-blue-100'],
        'purple' => ['bg' => 'bg-purple-600', 'text' => 'text-purple-600', 'light' => 'bg-purple-50', 'hover' => 'hover:bg-purple-100'],
        'indigo' => ['bg' => 'bg-indigo-600', 'text' => 'text-indigo-600', 'light' => 'bg-indigo-50', 'hover' => 'hover:bg-indigo-100'],
        'teal' => ['bg' => 'bg-teal-600', 'text' => 'text-teal-600', 'light' => 'bg-teal-50', 'hover' => 'hover:bg-teal-100'],
        'orange' => ['bg' => 'bg-orange-600', 'text' => 'text-orange-600', 'light' => 'bg-orange-50', 'hover' => 'hover:bg-orange-100'],
        'red' => ['bg' => 'bg-red-600', 'text' => 'text-red-600', 'light' => 'bg-red-50', 'hover' => 'hover:bg-red-100'],
        'gray' => ['bg' => 'bg-gray-600', 'text' => 'text-gray-600', 'light' => 'bg-gray-50', 'hover' => 'hover:bg-gray-100'],
    ];
@endphp

@foreach($menuItems as $item)
    @php
        $isActive = $activeTab === $item['id'];
        $colors = $colorClasses[$item['color']];
        $isAvailable = true;
        
        // Check time availability
        if (isset($item['available_from']) && isset($item['available_to'])) {
            $isAvailable = $currentTime >= $item['available_from'] && $currentTime <= $item['available_to'];
        }
        
        // Special conditions
        if ($item['id'] === 'morning-setup' && $tillStatus === 'open') {
            $isAvailable = false; // Till already opened
        }
        if ($item['id'] === 'end-of-day' && $tillStatus === 'closed') {
            $isAvailable = false; // Till already closed
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
            
            {{-- Time indicator for time-sensitive items --}}
            @if(!$isAvailable && isset($item['available_from']))
                <div class="ml-2">
                    <span class="text-xs {{ $colors['text'] }}">
                        {{ $item['available_from'] }}
                    </span>
                </div>
            @endif
        </div>
    </button>
@endforeach

{{-- Quick Actions for Teller --}}
<div class="mt-6 pt-4 border-t border-gray-200">
    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 px-2">Quick Actions</h4>
    
    <div class="space-y-2">
        {{-- Request Manager Assistance --}}
        <button wire:click="requestAssistance" 
                class="w-full flex items-center p-2 text-sm text-gray-700 hover:text-gray-900 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">Request Assistance</span>
        </button>
        
        {{-- View Performance --}}
        <button wire:click="viewMyPerformance" 
                class="w-full flex items-center p-2 text-sm text-gray-700 hover:text-gray-900 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span class="font-medium">My Performance</span>
        </button>
    </div>
</div>