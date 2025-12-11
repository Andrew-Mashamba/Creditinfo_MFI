<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-blue-900 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Budget Management</h1>
                        <p class="text-gray-600 mt-1">Plan, track, and manage organizational budgets effectively</p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Budget {{$this->selectYear}}</p>
                                @php
                                    $totalBudget = App\Models\BudgetManagement::selectRaw("COALESCE(SUM(CAST(revenue as numeric)),0) as total")->value('total');
                                @endphp
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($totalBudget, 2) }} TZS</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Approved Budget Items</p>
                                <p class="text-lg font-semibold text-gray-900">{{ App\Models\BudgetManagement::where('approval_status', 'APPROVED')->count() }}</p>
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
                                <p class="text-sm font-medium text-gray-500">Pending Budget Items</p>
                                <p class="text-lg font-semibold text-gray-900">{{ App\Models\BudgetManagement::where('approval_status', 'PENDING')->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Budget Items</p>
                                <p class="text-lg font-semibold text-gray-900">{{ App\Models\BudgetManagement::count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-6">
            <!-- Enhanced Sidebar -->
            <div class="w-80 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <!-- Year Selector -->
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Select Year</h3>
                    <div class="relative">
                        <select wire:model="selectYear" wire:change="refreshComponent"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white">
                            <option value="2022">2022</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                            <option value="2028">2028</option>
                            <option value="2029">2029</option>
                        </select>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Navigation</h3>
                    
                    @php
                        $budget_sections = [
                            [
                                'id' => 1, 
                                'label' => 'Budget Overview', 
                                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                                'description' => 'Analytics and insights'
                            ],
                            [
                                'id' => 2, 
                                'label' => 'Budget Items', 
                                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                'description' => 'Create & manage expense budgets'
                            ],
                            [
                                'id' => 3, 
                                'label' => 'Pending Approval', 
                                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                'description' => 'Review pending items'
                            ],
                            [
                                'id' => 4, 
                                'label' => 'Budget Reports', 
                                'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                'description' => 'Generate reports'
                            ],
                            [
                                'id' => 5, 
                                'label' => 'Budget Analysis', 
                                'icon' => 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z',
                                'description' => 'Performance analysis'
                            ],
                            [
                                'id' => 6, 
                                'label' => 'Budget Monitor', 
                                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                                'description' => 'Real-time monitoring & alerts'
                            ],
                            [
                                'id' => 7, 
                                'label' => 'Advanced Features', 
                                'icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z',
                                'description' => 'Commitments, transfers & GL'
                            ],
                        ];
                    @endphp

                    <nav class="space-y-2">
                        @foreach ($budget_sections as $section)
                            @php
                                $count = 0;
                                if ($section['id'] == 3) {
                                    $count = App\Models\BudgetManagement::where('approval_status', 'PENDING')->count();
                                }
                                $isActive = $this->tab_id == $section['id'];
                                
                                // Check permissions for each section
                                $permissionMap = [
                                    1 => 'view',         // Budget Overview
                                    2 => 'create',       // Budget Items
                                    3 => 'approve',      // Pending Approval
                                    4 => 'view',         // Budget Reports
                                    5 => 'view',         // Budget Analysis
                                    6 => 'view',         // Budget Monitor
                                    7 => 'manage'        // Advanced Features
                                ];
                                $requiredPermission = $permissionMap[$section['id']] ?? 'view';
                                $permissionKey = 'can' . ucfirst($requiredPermission);
                                $hasPermission = $permissions[$permissionKey] ?? false;
                            @endphp

                            @if($hasPermission)
                            <button
                                wire:click="menuItemClicked({{ $section['id'] }})"
                                class="relative w-full group transition-all duration-200"
                                aria-label="{{ $section['label'] }}"
                            >
                                <div class="flex items-center p-3 rounded-xl transition-all duration-200
                                    @if ($isActive) 
                                        bg-blue-900 text-white shadow-lg 
                                    @else 
                                        bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900 
                                    @endif">
                                    
                                    <!-- Loading State -->
                                    <div wire:loading wire:target="menuItemClicked({{ $section['id'] }})" class="mr-3">
                                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>

                                    <!-- Icon -->
                                    <div wire:loading.remove wire:target="menuItemClicked({{ $section['id'] }})" class="mr-3">
                                        <svg class="w-5 h-5 @if ($isActive) text-white @else text-gray-500 group-hover:text-gray-700 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"></path>
                                        </svg>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 text-left">
                                        <div class="font-medium text-sm">{{ $section['label'] }}</div>
                                        <div class="text-xs opacity-75">{{ $section['description'] }}</div>
                                    </div>

                                    <!-- Notification Badge -->
                                    @if ($count > 0)
                                        <div class="ml-2">
                                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full min-w-[20px] h-5">
                                                {{ $count > 99 ? '99+' : $count }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </button>
                            @endif
                        @endforeach
                    </nav>
                </div>

                <!-- Quick Actions -->
                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Quick Actions</h3>
                    <div class="space-y-2">
                        @if($permissions['canCreate'] ?? false)
                        <button wire:click="menuItemClicked(2)" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            New Budget Item
                        </button>
                        @endif
                        @if($permissions['canApprove'] ?? false)
                        <button wire:click="menuItemClicked(3)" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Review Pending
                        </button>
                        @endif
                        @if($permissions['canView'] ?? false)
                        <button wire:click="menuItemClicked(4)" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Generate Report
                        </button>
                        @endif
                        @if($permissions['canExport'] ?? false)
                        <button class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Export Data
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Enhanced Main Content Area -->
            <div class="flex-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <!-- Content Header -->
                    <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">
                                    @switch($this->tab_id)
                                        @case(1) Budget Overview @break
                                        @case(2) Budget Items @break
                                        @case(3) Pending Approval @break
                                        @case(4) Budget Reports @break
                                        @case(5) Budget Analysis @break
                                        @case(6) Budget Monitoring Dashboard @break
                                        @case(7) Advanced Budget Management @break
                                        @default Budget Overview
                                    @endswitch
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    @switch($this->tab_id)
                                        @case(1) Monitor budget performance and financial insights @break
                                        @case(2) Create and manage expense budget items with monthly allocations @break
                                        @case(3) Review and approve pending budget item requests @break
                                        @case(4) Generate detailed budget reports and analytics @break
                                        @case(5) Analyze budget performance and trends @break
                                        @case(6) Real-time budget tracking with alerts and monitoring @break
                                        @case(7) Commitments, transfers, GL linking, and advanced features @break
                                        @default Monitor budget performance and financial insights
                                    @endswitch
                                </p>
                            </div>
                            
                            <!-- Breadcrumb -->
                            <nav class="flex" aria-label="Breadcrumb">
                                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                    <li class="inline-flex items-center">
                                        <a href="#" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                            </svg>
                                            Budget Management
                                        </a>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                                                @switch($this->tab_id)
                                                    @case(1) Overview @break
                                                    @case(2) Items @break
                                                    @case(3) Pending @break
                                                    @case(4) Reports @break
                                                    @case(5) Analysis @break
                                                    @case(6) Monitor @break
                                                    @case(7) Advanced @break
                                                    @default Overview
                                                @endswitch
                                            </span>
                                        </div>
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="p-8">
                        <!-- Loading State -->
                    
                        <!-- Dynamic Content -->
                        <div wire:loading.remove wire:target="menuItemClicked,refreshComponent" class="min-h-[400px]">
                            @switch($this->tab_id)
                                @case(1)
                                    @if($permissions['canView'] ?? false)
                                    <!-- Budget Overview Dashboard -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                                        <!-- Budget Summary Card -->
                                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-lg font-semibold text-blue-900">Budget Summary</h3>
                                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                            </div>
                                            <div class="space-y-3">
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-blue-700">Total Budget:</span>
                                                    @php
                                                        $totalBudget2 = App\Models\BudgetManagement::selectRaw("COALESCE(SUM(CAST(revenue as numeric)),0) as total")->value('total');
                                                    @endphp
                                                    <span class="font-semibold text-blue-900">{{ number_format($totalBudget2, 2) }} TZS</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-blue-700">Budget Items:</span>
                                                    <span class="font-semibold text-blue-900">{{App\Models\BudgetManagement::count()}}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-blue-700">Avg. per Item:</span>
                                                    <span class="font-semibold text-blue-900">
                                                        @php
                                                            $total = App\Models\BudgetManagement::selectRaw("COALESCE(SUM(CAST(revenue as numeric)),0) as total")->value('total');
                                                            $count = App\Models\BudgetManagement::count();
                                                            $avg = $count > 0 ? ($total / $count) : 0;
                                                        @endphp
                                                        {{ number_format($avg, 2) }} TZS
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Monthly Distribution Card -->
                                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-lg font-semibold text-green-900">Monthly Distribution</h3>
                                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                                </svg>
                                            </div>
                                            <div class="space-y-2">
                                                @php
                                                    $budgets = App\Models\BudgetManagement::latest()->limit(3)->get();
                                                @endphp
                                                @foreach($budgets as $budget)
                                                    <div class="flex items-center justify-between bg-white p-2 rounded-lg">
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900">{{ Str::limit($budget->budget_name ?? 'Budget Item', 20) }}</p>
                                                            <p class="text-xs text-gray-500">{{ $budget->created_at ? $budget->created_at->format('M d, Y') : 'N/A' }}</p>
                                                        </div>
                                                        <span class="text-sm font-semibold text-gray-900">{{number_format($budget->revenue, 2)}} TZS</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Pending Approvals Card -->
                                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-lg font-semibold text-yellow-900">Pending Approvals</h3>
                                                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-3xl font-bold text-yellow-900 mb-2">{{App\Models\BudgetManagement::where('approval_status', 'PENDING')->count()}}</div>
                                                <p class="text-sm text-yellow-700">Budget items awaiting approval</p>
                                                <button wire:click="menuItemClicked(3)" class="mt-3 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                                                    View All
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Budget Performance Chart -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Budget Performance Overview</h3>
                                        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                                            <p class="text-gray-500">Budget performance chart visualization would go here</p>
                                        </div>
                                    </div>
                                    @endif
                                    @break
                                    
                                @case(2)
                                    @if($permissions['canCreate'] ?? false)
                                    <livewire:budget-management.budget-item />
                                    @endif
                                    @break
                                    
                                @case(3)
                                    @if($permissions['canApprove'] ?? false)
                                    <livewire:budget-management.awaiting-approval />
                                    @endif
                                    @break
                                    
                                @case(4)
                                    @if($permissions['canView'] ?? false)
                                    <!-- Budget Reports Section -->
                                    <div class="space-y-6">
                                        <!-- Report Filters -->
                                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Generate Budget Reports</h3>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                                                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        <option>Budget Summary</option>
                                                        <option>Monthly Breakdown</option>
                                                        <option>Category Analysis</option>
                                                        <option>Variance Report</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                                                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        <option value="2025">2025</option>
                                                        <option value="2024">2024</option>
                                                        <option value="2023">2023</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                                                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        <option>PDF</option>
                                                        <option>Excel</option>
                                                        <option>CSV</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mt-4 flex space-x-3">
                                                <button class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-900 transition-colors">
                                                    Generate Report
                                                </button>
                                                <button class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                                    Export to Excel
                                                </button>
                                                <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                                    Export to PDF
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Sample Report -->
                                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Budget Summary Report</h3>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budgeted</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variance</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        @php
                                                            $budgetItems = App\Models\BudgetManagement::latest()->limit(5)->get();
                                                        @endphp
                                                        @foreach($budgetItems as $item)
                                                            <tr>
                                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    {{ Str::limit($item->budget_name ?? 'Budget Item', 30) }}
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                    {{number_format($item->revenue, 2)}} TZS
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    {{number_format($item->revenue * 0.85, 2)}} TZS
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                                                    -{{number_format($item->revenue * 0.15, 2)}} TZS
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                                        @if($item->approval_status === 'APPROVED') bg-green-100 text-green-800
                                                                        @elseif($item->approval_status === 'PENDING') bg-yellow-100 text-yellow-800
                                                                        @else bg-red-100 text-red-800 @endif">
                                                                        {{ $item->approval_status ?? 'Unknown' }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @break
                                    
                                @case(5)
                                    @if($permissions['canView'] ?? false)
                                    <!-- Budget Analysis Section -->
                                    <div class="space-y-6">
                                        <!-- Analysis Cards -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h3 class="text-lg font-semibold text-purple-900">Budget Efficiency</h3>
                                                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                                    </svg>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-3xl font-bold text-purple-900 mb-2">85%</div>
                                                    <p class="text-sm text-purple-700">Efficiency Rate</p>
                                                </div>
                                            </div>

                                            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 border border-indigo-200">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h3 class="text-lg font-semibold text-indigo-900">Cost Variance</h3>
                                                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                    </svg>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-3xl font-bold text-indigo-900 mb-2">-15%</div>
                                                    <p class="text-sm text-blue-900">Variance</p>
                                                </div>
                                            </div>

                                            <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-xl p-6 border border-teal-200">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h3 class="text-lg font-semibold text-teal-900">ROI</h3>
                                                    <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                    </svg>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-3xl font-bold text-teal-900 mb-2">120%</div>
                                                    <p class="text-sm text-teal-700">Return on Investment</p>
                                                </div>
                                            </div>

                                            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h3 class="text-lg font-semibold text-orange-900">Growth Rate</h3>
                                                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                                    </svg>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-3xl font-bold text-orange-900 mb-2">+8.5%</div>
                                                    <p class="text-sm text-orange-700">Annual Growth</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Analysis Chart -->
                                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Budget Performance Trends</h3>
                                            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                                                <p class="text-gray-500">Budget performance trends chart would go here</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @break
                                    
                                @case(6)
                                    @if($permissions['canView'] ?? false)
                                    <!-- Budget Monitoring Dashboard -->
                                    <livewire:budget-management.budget-dashboard />
                                    @endif
                                    @break
                                    
                                @case(7)
                                    @if($permissions['canManage'] ?? false)
                                    <!-- Advanced Budget Management Features -->
                                    <livewire:budget-management.enhanced-budget-manager />
                                    @endif
                                    @break
                                    
                                @default
                                    <!-- Default Dashboard View -->
                                    <div class="text-center py-12">
                                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome to Budget Management</h3>
                                        <p class="text-gray-600">Select a section from the sidebar to get started</p>
                                    </div>
                            @endswitch
                            
                            <!-- Show message if no permissions for current section -->
                            @if(!($permissions['canView'] ?? false) && !($permissions['canCreate'] ?? false) && !($permissions['canApprove'] ?? false) && !($permissions['canManage'] ?? false))
                                <div class="text-center py-12">
                                    <div class="mx-auto h-12 w-12 text-gray-400">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Access</h3>
                                    <p class="mt-1 text-sm text-gray-500">You don't have permission to access any budget management features.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Because she competes with no one, no one can compete with her. --}}
