<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-blue-900 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Expenses Management</h1>
                        <p class="text-gray-600 mt-1">Track, manage, and analyze organizational expenses</p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Available Budget</p>
                                @php
                                    $currentMonthBudget = \App\Models\BudgetAllocation::where('year', now()->year)
                                        ->where('period', now()->month)
                                        ->sum('available_amount');
                                    if ($currentMonthBudget == 0) {
                                        // Fallback to old calculation
                                        $currentMonthBudget = DB::table('main_budget')->where('year',\Carbon\Carbon::now()->year)->sum('total');
                                    }
                                @endphp
                                <p class="text-lg font-semibold text-gray-900">{{number_format($currentMonthBudget)}} TZS</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Spent</p>
                                <p class="text-lg font-semibold text-gray-900">{{number_format(DB::table('expenses')->where('status',"PAID")->sum('amount'),2)}} TZS</p>
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
                                <p class="text-sm font-medium text-gray-500">Pending</p>
                                <p class="text-lg font-semibold text-gray-900">{{DB::table('expenses')->where('status',"PENDING")->count()}}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-6">
            <!-- Enhanced Sidebar -->
            <div class="w-80 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <!-- Search Section -->
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
                            placeholder="Search expenses, categories, or vendors..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"
                            aria-label="Search expenses"
                        />
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Navigation</h3>
                    
                    @php
                        $expense_sections = [
                            [
                                'id' => 1, 
                                'label' => 'Dashboard Overview', 
                                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                                'description' => 'Analytics and insights'
                            ],
                            [
                                'id' => 2, 
                                'label' => 'New Expense', 
                                'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                                'description' => 'Create expense entry'
                            ],
                            [
                                'id' => 3, 
                                'label' => 'Expense List', 
                                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                'description' => 'View all expenses'
                            ],
                            [
                                'id' => 4, 
                                'label' => 'Pending Approval', 
                                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                'description' => 'Awaiting approval'
                            ],
                            [
                                'id' => 5, 
                                'label' => 'Categories', 
                                'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
                                'description' => 'Manage categories'
                            ],
                            [
                                'id' => 6,
                                'label' => 'Reports',
                                'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                'description' => 'Generate reports'
                            ],
                            [
                                'id' => 7,
                                'label' => 'Accrued Expenses',
                                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z M9 12l2 2 4-4',
                                'description' => 'Manage accruals'
                            ],
                        ];
                    @endphp

                    <nav class="space-y-2">
                        @foreach ($expense_sections as $section)
                            @php
                                $count = 0;
                                if ($section['id'] == 4) {
                                    $count = DB::table('expenses')->where('status', 'PENDING')->count();
                                } elseif ($section['id'] == 7) {
                                    $count = DB::table('expenses')->where('is_accrued', true)->whereNull('realization_date')->count();
                                }
                                $isActive = $this->selectedMenuItem == $section['id'];

                                // Check permissions for each section
                                $permissionMap = [
                                    1 => 'view',         // Dashboard Overview
                                    2 => 'create',       // New Expense
                                    3 => 'view',         // Expense List
                                    4 => 'approve',      // Pending Approval
                                    5 => 'manage',       // Categories
                                    6 => 'view',         // Reports
                                    7 => 'view'          // Accrued Expenses
                                ];
                                $requiredPermission = $permissionMap[$section['id']] ?? 'view';
                                $permissionKey = 'can' . ucfirst($requiredPermission);
                                $hasPermission = $permissions[$permissionKey] ?? false;
                            @endphp

                            @if($hasPermission)
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
                                    
                                    <!-- Loading State -->
                                    <div wire:loading wire:target="selectedMenu({{ $section['id'] }})" class="mr-3">
                                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>

                                    <!-- Icon -->
                                    <div wire:loading.remove wire:target="selectedMenu({{ $section['id'] }})" class="mr-3">
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
                        <button wire:click="selectedMenu(2)" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            New Expense
                        </button>
                        @endif
                        @if($permissions['canView'] ?? false)
                        <button wire:click="selectedMenu(6)" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
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
                                    @switch($this->selectedMenuItem)
                                        @case(1) Dashboard Overview @break
                                        @case(2) New Expense @break
                                        @case(3) Expense List @break
                                        @case(4) Pending Approval @break
                                        @case(5) Categories @break
                                        @case(6) Reports @break
                                        @case(7) Accrued Expenses @break
                                        @default Dashboard Overview
                                    @endswitch
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    @switch($this->selectedMenuItem)
                                        @case(1) Monitor expense trends and budget performance @break
                                        @case(2) Create and submit new expense entries @break
                                        @case(3) View and manage all expense records @break
                                        @case(4) Review and approve pending expenses @break
                                        @case(5) Manage expense categories and classifications @break
                                        @case(6) Generate detailed expense reports and analytics @break
                                        @case(7) Track and manage accrued expenses awaiting payment @break
                                        @default Monitor expense trends and budget performance
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
                                            Expenses
                                        </a>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                                                @switch($this->selectedMenuItem)
                                                    @case(1) Dashboard @break
                                                    @case(2) New Expense @break
                                                    @case(3) List @break
                                                    @case(4) Pending @break
                                                    @case(5) Categories @break
                                                    @case(6) Reports @break
                                                    @case(7) Accrued @break
                                                    @default Dashboard
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
                        <!-- Dynamic Content -->
                        <div wire:loading.remove wire:target="selectedMenu" class="min-h-[400px]">
                            @switch($this->selectedMenuItem)
                                @case(1)
                                    @if($permissions['canView'] ?? false)
                                    <!-- Dashboard Overview -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                                        <!-- Budget Overview Card -->
                                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-lg font-semibold text-blue-900">Budget Overview</h3>
                                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                            </div>
                                            <div class="space-y-3">
                                                @php
                                                    // Get current month allocation totals
                                                    $currentAllocations = \App\Models\BudgetAllocation::where('year', now()->year)
                                                        ->where('period', now()->month)
                                                        ->selectRaw('
                                                            SUM(allocated_amount) as allocated,
                                                            SUM(utilized_amount) as spent,
                                                            SUM(available_amount) as available,
                                                            SUM(rollover_amount) as rollover,
                                                            SUM(advance_amount) as advances,
                                                            SUM(supplementary_amount) as supplementary
                                                        ')
                                                        ->first();
                                                    
                                                    $totalBudget = $currentAllocations ? $currentAllocations->allocated : 
                                                        DB::table('main_budget')->where('year',\Carbon\Carbon::now()->year)->sum('total')/12;
                                                    $totalSpent = $currentAllocations ? $currentAllocations->spent :
                                                        DB::table('expenses')->where('status',"PAID")
                                                            ->whereYear('expense_month', now()->year)
                                                            ->whereMonth('expense_month', now()->month)
                                                            ->sum('amount');
                                                    $totalRemaining = $currentAllocations ? $currentAllocations->available :
                                                        $totalBudget - $totalSpent;
                                                @endphp
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-blue-700">Monthly Budget:</span>
                                                    <span class="font-semibold text-blue-900">{{number_format($totalBudget)}} TZS</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-blue-700">Spent:</span>
                                                    <span class="font-semibold text-red-600">{{number_format($totalSpent, 2)}} TZS</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-blue-700">Available:</span>
                                                    <span class="font-semibold text-green-600">{{number_format($totalRemaining, 2)}} TZS</span>
                                                </div>
                                                @if($currentAllocations && $currentAllocations->rollover > 0)
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-blue-600">Includes Rollover:</span>
                                                    <span class="font-medium text-blue-800">+{{number_format($currentAllocations->rollover, 2)}}</span>
                                                </div>
                                                @endif
                                                @if($currentAllocations && $currentAllocations->advances > 0)
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-orange-600">Advances:</span>
                                                    <span class="font-medium text-orange-800">+{{number_format($currentAllocations->advances, 2)}}</span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Recent Expenses Card -->
                                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-lg font-semibold text-green-900">Recent Expenses</h3>
                                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <div class="space-y-2">
                                                @php
                                                        $recentExpenses = DB::table('expenses')->orderBy('id', 'desc')->limit(3)->get();
                                                @endphp
                                                @foreach($recentExpenses as $expense)
                                                    <div class="flex items-center justify-between bg-white p-2 rounded-lg">
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900">{{ Str::limit($expense->description ?? 'Expense', 20) }}</p>
                                                            <p class="text-xs text-gray-500">{{ ($expense->created_at ?? $expense->expense_month) ? \Carbon\Carbon::parse($expense->created_at ?? $expense->expense_month)->format('M d, Y') : '-' }}</p>
                                                        </div>
                                                        <span class="text-sm font-semibold text-gray-900">{{number_format($expense->amount, 2)}} TZS</span>
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
                                                <div class="text-3xl font-bold text-yellow-900 mb-2">{{DB::table('expenses')->where('status',"PENDING")->count()}}</div>
                                                <p class="text-sm text-yellow-700">Expenses awaiting approval</p>
                                                <button wire:click="selectedMenu(4)" class="mt-3 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                                                    View All
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Monthly Trend Chart -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Expense Trend</h3>
                                        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                                            <p class="text-gray-500">Chart visualization would go here</p>
                                        </div>
                                    </div>
                                    @endif
                                    @break
                                    
                                @case(2)
                                    @if($permissions['canCreate'] ?? false)
                                    <livewire:expenses.new-expense />
                                    @endif
                                    @break
                                    
                                @case(3)
                                    @if($permissions['canView'] ?? false)
                                    <livewire:expenses.expenses-table />
                                    <livewire:expenses.view-expense-details />
                                    @endif
                                    @break
                                    
                                @case(4)
                                    @if($permissions['canApprove'] ?? false)
                                    <!-- Pending Approvals -->
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                                        <h3 class="text-lg font-semibold text-yellow-900 mb-4">Pending Expense Approvals</h3>
                                        <div class="space-y-4">
                                            @php
                                                $pendingExpenses = DB::table('expenses')->where('status', 'PENDING')->get();
                                            @endphp
                                            @if($pendingExpenses->count() > 0)
                                                @foreach($pendingExpenses as $expense)
                                                    <div class="bg-white p-4 rounded-lg border border-yellow-200">
                                                        <div class="flex items-center justify-between">
                                                            <div>
                                                                <h4 class="font-medium text-gray-900">{{ $expense->description ?? 'Expense' }}</h4>
                                                                <p class="text-sm text-gray-600">{{ ($expense->created_at ?? $expense->expense_month) ? \Carbon\Carbon::parse($expense->created_at ?? $expense->expense_month)->format('M d, Y H:i') : '-' }}</p>
                                                                <p class="text-sm text-gray-600">Category: {{ $expense->category ?? 'Uncategorized' }}</p>
                                                            </div>
                                                            <div class="text-right">
                                                                <p class="text-lg font-semibold text-gray-900">{{number_format($expense->amount, 2)}} TZS</p>
                                                                <div class="flex space-x-2 mt-2">
                                                                    <button class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">Approve</button>
                                                                    <button class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">Reject</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="text-center py-8">
                                                    <svg class="w-16 h-16 text-yellow-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="text-yellow-700">No pending expenses to approve</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                    @break
                                    
                                @case(5)
                                    @if($permissions['canManage'] ?? false)
                                    <!-- Categories Management -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <div class="flex items-center justify-between mb-6">
                                            <h3 class="text-lg font-semibold text-gray-900">Expense Categories</h3>
                                            <button class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-900 transition-colors">
                                                Add Category
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            @php
                                                $categories = ['Office Supplies', 'Travel', 'Utilities', 'Marketing', 'Equipment', 'Software'];
                                            @endphp
                                            @foreach($categories as $category)
                                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <h4 class="font-medium text-gray-900">{{ $category }}</h4>
                                                            <p class="text-sm text-gray-600">{{ rand(5, 25) }} expenses</p>
                                                        </div>
                                                        <div class="flex space-x-2">
                                                            <button class="text-blue-600 hover:text-blue-800">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                </svg>
                                                            </button>
                                                            <button class="text-red-600 hover:text-red-800">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                    @break
                                    
                                @case(6)
                                    @if($permissions['canView'] ?? false)
                                    <!-- Reports Section -->
                                    <div class="space-y-6">
                                        <!-- Report Filters -->
                                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Generate Reports</h3>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                                                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        <option>Last 30 Days</option>
                                                        <option>Last 3 Months</option>
                                                        <option>Last 6 Months</option>
                                                        <option>This Year</option>
                                                        <option>Custom Range</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        <option>All Categories</option>
                                                        <option>Office Supplies</option>
                                                        <option>Travel</option>
                                                        <option>Utilities</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        <option>All Status</option>
                                                        <option>Paid</option>
                                                        <option>Pending</option>
                                                        <option>Rejected</option>
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
                                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sample Expense Report</h3>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        @php
                                                                $sampleExpenses = DB::table('expenses')->limit(5)->get();
                                                        @endphp
                                                        @foreach($sampleExpenses as $expense)
                                                            <tr>
                                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    {{ ($expense->created_at ?? $expense->expense_month) ? \Carbon\Carbon::parse($expense->created_at ?? $expense->expense_month)->format('M d, Y') : '-' }}
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    {{ Str::limit($expense->description ?? 'Expense', 30) }}
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    {{ $expense->category ?? 'Uncategorized' }}
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                    {{number_format($expense->amount, 2)}} TZS
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                                                        @if($expense->status == 'PAID') bg-green-100 text-green-800
                                                                        @elseif($expense->status == 'PENDING') bg-yellow-100 text-yellow-800
                                                                        @else bg-gray-100 text-gray-800 @endif">
                                                                        {{ $expense->status }}
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

                                @case(7)
                                    @if($permissions['canView'] ?? false)
                                    <!-- Accrued Expenses Section -->
                                    <livewire:expenses.accrued-expenses />
                                    @endif
                                    @break

                                @default
                                    <!-- Default Dashboard View -->
                                    <div class="text-center py-12">
                                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome to Expenses Management</h3>
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
                                    <p class="mt-1 text-sm text-gray-500">You don't have permission to access any expense management features.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

