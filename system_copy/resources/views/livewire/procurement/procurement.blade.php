<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-blue-900 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Procurement Manager</h1>
                        <p class="text-gray-600 mt-1">Manage, track, and analyze all procurement activities</p>
                    </div>
                </div>
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Vendors</p>
                                <p class="text-lg font-semibold text-gray-900">{{ DB::table('vendors')->where('status', '!=', 'DELETED')->count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Pending Orders</p>
                                <p class="text-lg font-semibold text-gray-900">{{ DB::table('purchases')->where('status', 'PENDING')->count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Active Contracts</p>
                                <p class="text-lg font-semibold text-gray-900">{{ DB::table('contract_managements')->where('status', 'ACTIVE')->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-6">
            <!-- Sidebar Navigation -->
            <div class="w-80 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model.debounce.300ms="search" 
                            placeholder="Search procurement items, vendors, or contracts..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"
                            aria-label="Search procurement"
                        />
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Navigation</h3>
                    @php
                        $sections = [
                            ['id' => 1, 'label' => 'Dashboard', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'description' => 'Overview and analytics'],
                            ['id' => 2, 'label' => 'Purchase Requisition', 'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6', 'description' => 'Create purchase requests'],
                            ['id' => 3, 'label' => 'Vendor Management', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'description' => 'Manage vendors'],
                            ['id' => 4, 'label' => 'Contract Management', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'description' => 'Manage contracts'],
                            ['id' => 5, 'label' => 'Tender Management', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'description' => 'Manage tenders'],
                            ['id' => 6, 'label' => 'Inventory Management', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'description' => 'Manage inventory'],
                            ['id' => 7, 'label' => 'Assets Management', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'description' => 'Manage assets'],
                            ['id' => 8, 'label' => 'Reports', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'description' => 'Generate reports'],
                        ];
                        $selectedMenuItem = $selectedMenuItem ?? 1;
                    @endphp
                    <nav class="space-y-2">
                        @foreach ($sections as $section)
                            @php
                                $isActive = $selectedMenuItem == $section['id'];
                            @endphp
                            <button
                                wire:click="selectedMenu({{ $section['id'] }})"
                                class="relative w-full group transition-all duration-200"
                                aria-label="{{ $section['label'] }}"
                            >
                                <div class="flex items-center p-3 rounded-xl transition-all duration-200
                                    @if ($isActive) 
                                        bg-blue-900 text-white shadow-lg 
                                    @else 
                                        bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900 
                                    @endif">
                                    <div wire:loading wire:target="selectedMenu({{ $section['id'] }})" class="mr-3">
                                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    <div wire:loading.remove wire:target="selectedMenu({{ $section['id'] }})" class="mr-3">
                                        <svg class="w-5 h-5 @if ($isActive) text-white @else text-gray-500 group-hover:text-gray-700 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 text-left">
                                        <div class="font-medium text-sm">{{ $section['label'] }}</div>
                                        <div class="text-xs opacity-75">{{ $section['description'] }}</div>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </nav>
                </div>
            </div>
            <!-- Main Content Area (Dashboard Cards) -->
            <div class="flex-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">
                                    @switch($selectedMenuItem)
                                        @case(1) Dashboard @break
                                        @case(2) Purchase Requisition @break
                                        @case(3) Vendor Management @break
                                        @case(4) Contract Management @break
                                        @case(5) Tender Management @break
                                        @case(6) Inventory Management @break
                                        @case(7) Assets Management @break
                                        @case(8) Reports @break
                                        @default Dashboard
                                    @endswitch
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    @switch($selectedMenuItem)
                                        @case(1) Overview of procurement performance and key metrics @break
                                        @case(2) Create and manage purchase requisitions @break
                                        @case(3) Manage vendor relationships and information @break
                                        @case(4) Manage contracts and agreements @break
                                        @case(5) Manage tender processes and bids @break
                                        @case(6) Track and manage inventory levels @break
                                        @case(7) Manage organizational assets @break
                                        @case(8) Generate detailed procurement reports and analytics @break
                                        @default Overview of procurement performance and key metrics
                                    @endswitch
                                </p>
                            </div>
                            <div class="flex items-center space-x-3">
                                @if($selectedMenuItem != 1 && $selectedMenuItem != 8)
                                    <button
                                        wire:click="toggleFilters"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                                        </svg>
                                        Filters
                                    </button>
                                    @if($search || $filterStatus || $filterDate)
                                        <button
                                            wire:click="clearFilters"
                                            class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Clear
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                        
                        @if($showFilters && $selectedMenuItem != 1 && $selectedMenuItem != 8)
                            <div class="mt-4 p-4 bg-white rounded-lg border border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select wire:model="filterStatus" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Statuses</option>
                                            <option value="ACTIVE">Active</option>
                                            <option value="PENDING">Pending</option>
                                            <option value="COMPLETED">Completed</option>
                                            <option value="CANCELLED">Cancelled</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                                        <input type="date" wire:model="filterDate" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div class="flex items-end">
                                        <button
                                            wire:click="clearFilters"
                                            class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500"
                                        >
                                            Reset Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="p-8">
                        <!-- Dashboard Cards (Placeholder for now) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-blue-900">Total Vendors</h3>
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold text-blue-900">{{ DB::table('vendors')->where('status', '!=', 'DELETED')->count() }}</div>
                                <div class="text-sm text-blue-700 mt-2">Active vendor relationships</div>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-green-900">Pending Orders</h3>
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold text-green-900">{{ DB::table('purchases')->where('status', 'PENDING')->count() }}</div>
                                <div class="text-sm text-green-700 mt-2">Orders awaiting approval</div>
                            </div>
                            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-yellow-900">Active Contracts</h3>
                                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold text-yellow-900">{{ DB::table('contract_managements')->where('status', 'ACTIVE')->count() }}</div>
                                <div class="text-sm text-yellow-700 mt-2">Ongoing contracts</div>
                            </div>
                        </div>
                        <!-- Dynamic Content Sections -->
                        @switch($selectedMenuItem)
                            @case(1)
                                <!-- Dashboard Overview -->
                                <div class="space-y-6">
                                    <!-- Recent Activity -->
                                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                            <h3 class="text-lg font-semibold text-gray-900">Recent Procurement Activity</h3>
                                        </div>
                                        <div class="p-6">
                                            <div class="space-y-4">
                                                @php
                                                    $recentVendors = DB::table('vendors')->where('status', '!=', 'DELETED')->orderBy('created_at', 'desc')->limit(5)->get();
                                                    $recentPurchases = DB::table('purchases')->orderBy('created_at', 'desc')->limit(5)->get();
                                                @endphp
                                                
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <!-- Recent Vendors -->
                                                    <div>
                                                        <h4 class="font-medium text-gray-900 mb-3">Recent Vendors</h4>
                                                        <div class="space-y-2">
                                                            @forelse($recentVendors as $vendor)
                                                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                                                    <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                                                    <div class="flex-1">
                                                                        <p class="text-sm font-medium text-gray-900">{{ $vendor->name ?? 'Vendor' }}</p>
                                                                        <p class="text-xs text-gray-500">{{ $vendor->email ?? 'No email' }}</p>
                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <p class="text-sm text-gray-500">No recent vendors</p>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Recent Purchases -->
                                                    <div>
                                                        <h4 class="font-medium text-gray-900 mb-3">Recent Purchase Orders</h4>
                                                        <div class="space-y-2">
                                                            @forelse($recentPurchases as $purchase)
                                                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                                                    <div class="flex-1">
                                                                        <p class="text-sm font-medium text-gray-900">{{ $purchase->description ?? 'Purchase Order' }}</p>
                                                                        <p class="text-xs text-gray-500">Status: {{ $purchase->status ?? 'Unknown' }}</p>
                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <p class="text-sm text-gray-500">No recent purchases</p>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @break
                                
                            @case(2)
                                <!-- Purchase Requisition -->
                                <livewire:procurement.p-o />
                                @break
                                
                            @case(3)
                                <!-- Vendor Management -->
                                <livewire:procurement.vendor />
                                @break
                                
                            @case(4)
                                <!-- Contract Management -->
                                <livewire:procurement.contract />
                                @break
                                
                            @case(5)
                                <!-- Tender Management -->
                                <livewire:procurement.tender />
                                @break
                                
                            @case(6)
                                <!-- Inventory Management -->
                                <livewire:procurement.inventory />
                                @break
                                
                            @case(7)
                                <!-- Assets Management -->
                                <livewire:procurement.assets />
                                @break
                                
                            @case(8)
                                <!-- Reports and Analytics -->
                                <div class="space-y-6">
                                    <!-- Reports Overview -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-lg font-semibold text-purple-900">Vendor Performance</h3>
                                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                            </div>
                                            <div class="text-2xl font-bold text-purple-900">{{ DB::table('vendors')->where('status', '!=', 'DELETED')->count() }}</div>
                                            <div class="text-sm text-purple-700 mt-2">Active vendors</div>
                                        </div>
                                        
                                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 border border-indigo-200">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-lg font-semibold text-indigo-900">Contract Value</h3>
                                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                            </div>
                                            <div class="text-2xl font-bold text-indigo-900">{{ DB::table('contract_managements')->count() }}</div>
                                            <div class="text-sm text-blue-900 mt-2">Total contracts</div>
                                        </div>
                                        
                                        <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-xl p-6 border border-teal-200">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-lg font-semibold text-teal-900">Procurement Efficiency</h3>
                                                <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                                </svg>
                                            </div>
                                            <div class="text-2xl font-bold text-teal-900">85%</div>
                                            <div class="text-sm text-teal-700 mt-2">Average completion rate</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Detailed Reports -->
                                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                            <h3 class="text-lg font-semibold text-gray-900">Procurement Analytics</h3>
                                        </div>
                                        <div class="p-6">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div>
                                                    <h4 class="font-medium text-gray-900 mb-3">Monthly Procurement Trends</h4>
                                                    <div class="space-y-3">
                                                        @php
                                                            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
                                                            $values = [12000, 19000, 15000, 25000, 22000, 30000];
                                                        @endphp
                                                        @foreach($months as $index => $month)
                                                            <div class="flex items-center justify-between">
                                                                <span class="text-sm text-gray-600">{{ $month }}</span>
                                                                <div class="flex items-center space-x-2">
                                                                    <div class="w-32 bg-gray-200 rounded-full h-2">
                                                                        <div class="bg-blue-900 h-2 rounded-full" style="width: {{ ($values[$index] / max($values)) * 100 }}%"></div>
                                                                    </div>
                                                                    <span class="text-sm font-medium text-gray-900">{{ number_format($values[$index]) }}</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                
                                                <div>
                                                    <h4 class="font-medium text-gray-900 mb-3">Vendor Categories</h4>
                                                    <div class="space-y-3">
                                                        @php
                                                            $categories = ['Suppliers', 'Service Providers', 'Contractors', 'Consultants'];
                                                            $counts = [15, 8, 12, 6];
                                                            $colors = ['bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500'];
                                                        @endphp
                                                        @foreach($categories as $index => $category)
                                                            <div class="flex items-center justify-between">
                                                                <div class="flex items-center">
                                                                    <div class="w-3 h-3 {{ $colors[$index] }} rounded-full mr-3"></div>
                                                                    <span class="text-sm text-gray-600">{{ $category }}</span>
                                                                </div>
                                                                <span class="text-sm font-medium text-gray-900">{{ $counts[$index] }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @break
                                
                            @default
                                <!-- Default Dashboard -->
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome to Procurement Manager</h3>
                                    <p class="text-gray-600">Select a section from the sidebar to get started</p>
                                </div>
                        @endswitch
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

