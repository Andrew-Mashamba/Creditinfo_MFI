{{-- Branch Manager Role Navigation Menu --}}
{{-- Following the Branch Manager Journey from docs/cash-management-user-journeys.md --}}

@php
    $menuItems = [
        [
            'id' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            'description' => 'Branch overview',
            'color' => 'red',
            'badge' => null
        ],
        [
            'id' => 'cash-position',
            'label' => 'Cash Position',
            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
            'description' => 'Monitor branch cash',
            'color' => 'green',
            'badge' => $cashAlerts ?? null
        ],
        [
            'id' => 'team-management',
            'label' => 'Team Management',
            'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
            'description' => 'Staff performance',
            'color' => 'purple',
            'badge' => $staffIssues ?? null
        ],
        [
            'id' => 'approvals',
            'label' => 'Approvals',
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'description' => 'Pending approvals',
            'color' => 'orange',
            'badge' => $pendingApprovals ?? 0
        ],
        [
            'id' => 'cash-optimization',
            'label' => 'Cash Optimization',
            'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
            'description' => 'Optimize cash usage',
            'color' => 'indigo',
            'badge' => null
        ],
        [
            'id' => 'compliance',
            'label' => 'Compliance',
            'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            'description' => 'Compliance status',
            'color' => 'red',
            'badge' => $complianceIssues ?? null
        ],
        [
            'id' => 'reports',
            'label' => 'Reports',
            'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'description' => 'Management reports',
            'color' => 'teal',
            'badge' => null
        ],
        [
            'id' => 'cash-forecasting',
            'label' => 'Forecasting',
            'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
            'description' => 'Cash forecasting',
            'color' => 'pink',
            'badge' => null
        ],
        [
            'id' => 'vendor-management',
            'label' => 'Vendors',
            'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
            'description' => 'CIT & vendors',
            'color' => 'yellow',
            'badge' => null
        ],
        [
            'id' => 'analytics',
            'label' => 'Analytics',
            'icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A7.025 7.025 0 0120.488 9z',
            'description' => 'Performance analytics',
            'color' => 'gray',
            'badge' => null
        ]
    ];
    
    $colorClasses = [
        'red' => ['bg' => 'bg-red-600', 'text' => 'text-red-600', 'light' => 'bg-red-50', 'hover' => 'hover:bg-red-100'],
        'green' => ['bg' => 'bg-green-600', 'text' => 'text-green-600', 'light' => 'bg-green-50', 'hover' => 'hover:bg-green-100'],
        'purple' => ['bg' => 'bg-purple-600', 'text' => 'text-purple-600', 'light' => 'bg-purple-50', 'hover' => 'hover:bg-purple-100'],
        'orange' => ['bg' => 'bg-orange-600', 'text' => 'text-orange-600', 'light' => 'bg-orange-50', 'hover' => 'hover:bg-orange-100'],
        'indigo' => ['bg' => 'bg-indigo-600', 'text' => 'text-indigo-600', 'light' => 'bg-indigo-50', 'hover' => 'hover:bg-indigo-100'],
        'red' => ['bg' => 'bg-red-600', 'text' => 'text-red-600', 'light' => 'bg-red-50', 'hover' => 'hover:bg-red-100'],
        'teal' => ['bg' => 'bg-teal-600', 'text' => 'text-teal-600', 'light' => 'bg-teal-50', 'hover' => 'hover:bg-teal-100'],
        'pink' => ['bg' => 'bg-pink-600', 'text' => 'text-pink-600', 'light' => 'bg-pink-50', 'hover' => 'hover:bg-pink-100'],
        'yellow' => ['bg' => 'bg-yellow-600', 'text' => 'text-yellow-600', 'light' => 'bg-yellow-50', 'hover' => 'hover:bg-yellow-100'],
        'gray' => ['bg' => 'bg-gray-600', 'text' => 'text-gray-600', 'light' => 'bg-gray-50', 'hover' => 'hover:bg-gray-100'],
    ];
@endphp

@foreach($menuItems as $item)
    @php
        $isActive = $activeTab === $item['id'];
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
            @if($item['badge'] && $item['badge'] > 0)
                <div class="ml-2">
                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none 
                        @if($item['id'] === 'approvals' && $item['badge'] > 5) 
                            bg-red-500 text-white animate-pulse
                        @elseif($item['id'] === 'compliance' && $item['badge'] > 0)
                            bg-red-500 text-white
                        @else
                            bg-orange-500 text-white
                        @endif rounded-full">
                        {{ $item['badge'] }}
                    </span>
                </div>
            @endif
        </div>
    </button>
@endforeach

{{-- Branch Performance Summary --}}
<div class="mt-6 pt-4 border-t border-gray-200">
    <div class="bg-gradient-to-br from-red-50 to-indigo-50 rounded-xl p-4 border border-red-200">
        <h4 class="text-xs font-bold text-red-800 uppercase tracking-wider mb-3">Branch Performance</h4>
        
        <div class="space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Cash Utilization:</span>
                <span class="font-bold text-sm {{ $branchUtilization > 85 ? 'text-green-600' : 'text-yellow-600' }}">
                    {{ $branchUtilization ?? 82 }}%
                </span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Service Level:</span>
                <span class="font-bold text-sm text-green-600">
                    {{ $serviceLevel ?? 96 }}%
                </span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Compliance:</span>
                <span class="font-bold text-sm {{ $complianceScore === 100 ? 'text-green-600' : 'text-yellow-600' }}">
                    {{ $complianceScore ?? 98 }}%
                </span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Today's Volume:</span>
                <span class="font-bold text-sm text-red-600">
                    {{ number_format($todayVolume ?? 150000000) }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions for Branch Manager --}}
<div class="mt-4">
    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 px-2">Quick Actions</h4>
    
    <div class="space-y-2">
        {{-- Emergency Cash Order --}}
        <button wire:click="emergencyCashOrder" 
                class="w-full flex items-center p-2 text-sm text-gray-700 hover:text-gray-900 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">Emergency Order</span>
        </button>
        
        {{-- View VIP Customers --}}
        <button wire:click="viewVipCustomers" 
                class="w-full flex items-center p-2 text-sm text-gray-700 hover:text-gray-900 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
            </svg>
            <span class="font-medium">VIP Customers</span>
        </button>
        
        {{-- Export Reports --}}
        <button wire:click="exportDailyReport" 
                class="w-full flex items-center p-2 text-sm text-gray-700 hover:text-gray-900 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="font-medium">Export Reports</span>
        </button>
    </div>
</div>