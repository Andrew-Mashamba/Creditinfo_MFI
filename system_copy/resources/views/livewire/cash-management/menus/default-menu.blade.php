{{-- Default Navigation Menu (For roles without specific menu or fallback) --}}

@php
    $menuItems = [
        [
            'id' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            'description' => 'Overview',
            'color' => 'blue',
            'badge' => null
        ],
        [
            'id' => 'cash-status',
            'label' => 'Cash Status',
            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
            'description' => 'View cash positions',
            'color' => 'green',
            'badge' => null
        ],
        [
            'id' => 'transactions',
            'label' => 'Transactions',
            'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
            'description' => 'Transaction history',
            'color' => 'purple',
            'badge' => null
        ],
        [
            'id' => 'reports',
            'label' => 'Reports',
            'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'description' => 'View reports',
            'color' => 'indigo',
            'badge' => null
        ],
        [
            'id' => 'help',
            'label' => 'Help & Support',
            'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'description' => 'Get assistance',
            'color' => 'gray',
            'badge' => null
        ]
    ];
    
    $colorClasses = [
        'blue' => ['bg' => 'bg-blue-900', 'text' => 'text-blue-600', 'light' => 'bg-blue-50', 'hover' => 'hover:bg-blue-100'],
        'green' => ['bg' => 'bg-green-600', 'text' => 'text-green-600', 'light' => 'bg-green-50', 'hover' => 'hover:bg-green-100'],
        'purple' => ['bg' => 'bg-purple-600', 'text' => 'text-purple-600', 'light' => 'bg-purple-50', 'hover' => 'hover:bg-purple-100'],
        'indigo' => ['bg' => 'bg-indigo-600', 'text' => 'text-indigo-600', 'light' => 'bg-indigo-50', 'hover' => 'hover:bg-indigo-100'],
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
                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                        {{ $item['badge'] }}
                    </span>
                </div>
            @endif
        </div>
    </button>
@endforeach

{{-- Information Panel --}}
<div class="mt-6 pt-4 border-t border-gray-200">
    <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-xl p-4 border border-gray-200">
        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">System Information</h4>
        
        <div class="space-y-2 text-sm">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Role:</span>
                <span class="font-semibold text-gray-900">{{ ucwords(str_replace('_', ' ', $userRole ?? 'User')) }}</span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Branch:</span>
                <span class="font-semibold text-gray-900">{{ $branchName ?? 'Main' }}</span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Status:</span>
                <span class="font-semibold text-green-600">Active</span>
            </div>
        </div>
    </div>
</div>

{{-- Help Section --}}
<div class="mt-4">
    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 px-2">Need Help?</h4>
    
    <div class="space-y-2">
        {{-- Contact Support --}}
        <button wire:click="contactSupport" 
                class="w-full flex items-center p-2 text-sm text-gray-700 hover:text-gray-900 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <span class="font-medium">Contact Support</span>
        </button>
        
        {{-- View Documentation --}}
        <button wire:click="viewDocumentation" 
                class="w-full flex items-center p-2 text-sm text-gray-700 hover:text-gray-900 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <span class="font-medium">Documentation</span>
        </button>
    </div>
</div>