<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-blue-900 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Transactions Management</h1>
                        <p class="text-gray-600 mt-1">Monitor, manage, and analyze all financial transactions</p>
                    </div>
                </div>
                <!-- Quick Stats -->
                <div class="flex flex-wrap items-center gap-2">
                    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div class="ml-2">
                                <p class="text-xs text-gray-500">Total</p>
                                <p class="text-sm font-semibold text-gray-900">{{ number_format($totalTransactions) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-2">
                                <p class="text-xs text-gray-500">Completed</p>
                                <p class="text-sm font-semibold text-green-600">{{ number_format($completedCount) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-2">
                                <p class="text-xs text-gray-500">Pending</p>
                                <p class="text-sm font-semibold text-yellow-600">{{ number_format($pendingCount) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <div class="ml-2">
                                <p class="text-xs text-gray-500">Failed</p>
                                <p class="text-sm font-semibold text-red-600">{{ number_format($failedCount) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-gray-100 rounded-lg">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-2">
                                <p class="text-xs text-gray-500">Unreconciled</p>
                                <p class="text-sm font-semibold text-gray-600">{{ number_format($unreconciledCount) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!($permissions['canView'] ?? false) && !($permissions['canCreate'] ?? false) && !($permissions['canManage'] ?? false) && !($permissions['canExport'] ?? false))
        <div class="bg-white shadow rounded-lg p-8 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Access</h3>
            <p class="text-gray-500">You don't have permission to access the transactions management module.</p>
        </div>
        @else
        <!-- Sidebar and Main Content -->
        <div class="flex gap-6">
            <!-- Sidebar Navigation -->
            <div class="w-72 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden flex-shrink-0">
                <div class="p-4 border-b border-gray-100">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" wire:model.debounce.300ms="search" placeholder="Search transactions..."
                            class="block w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 hover:bg-white focus:bg-white"/>
                    </div>
                </div>
                <div class="p-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Navigation</h3>
                    @php
                        $sections = [
                            ['id' => 1, 'label' => 'Dashboard', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'description' => 'Analytics overview', 'permission' => 'view'],
                            ['id' => 3, 'label' => 'All Transactions', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'description' => 'View all transactions', 'permission' => 'view'],
                            ['id' => 4, 'label' => 'Pending', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'description' => 'Awaiting processing', 'permission' => 'view'],
                            ['id' => 5, 'label' => 'Reconciliation', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'description' => 'Reconcile transactions', 'permission' => 'manage'],
                        ];
                    @endphp
                    <nav class="space-y-1">
                        @foreach ($sections as $section)
                            @php
                                $permissionKey = 'can' . ucfirst($section['permission']);
                                $hasPermission = $permissions[$permissionKey] ?? false;
                            @endphp
                            @if($hasPermission)
                                @php $isActive = $selectedMenuItem == $section['id']; @endphp
                                <button wire:click="selectedMenu({{ $section['id'] }})" class="relative w-full group transition-all duration-200">
                                    <div class="flex items-center p-2.5 rounded-lg transition-all duration-200
                                        @if ($isActive) bg-blue-900 text-white shadow-lg @else bg-gray-50 hover:bg-gray-100 text-gray-700 @endif">
                                        <svg class="w-5 h-5 mr-2 @if ($isActive) text-white @else text-gray-500 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"></path>
                                        </svg>
                                        <div class="flex-1 text-left">
                                            <div class="font-medium text-sm">{{ $section['label'] }}</div>
                                        </div>
                                    </div>
                                </button>
                            @endif
                        @endforeach
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1">
                @if($selectedMenuItem == 1 && ($permissions['canView'] ?? false))
                <!-- Dashboard Section -->
                <div class="space-y-6">
                    <!-- Period Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Today</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ number_format($todayTransactions) }}</p>
                                    <p class="text-sm text-green-600">TZS {{ number_format($todayAmount, 0) }}</p>
                                </div>
                                <div class="p-3 bg-blue-100 rounded-full">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">This Week</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ number_format($weekTransactions) }}</p>
                                    <p class="text-sm text-green-600">TZS {{ number_format($weekAmount, 0) }}</p>
                                </div>
                                <div class="p-3 bg-green-100 rounded-full">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">This Month</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ number_format($monthTransactions) }}</p>
                                    <p class="text-sm text-green-600">TZS {{ number_format($monthAmount, 0) }}</p>
                                </div>
                                <div class="p-3 bg-purple-100 rounded-full">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Trend -->
                    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">7-Day Transaction Trend</h3>
                        <div class="grid grid-cols-7 gap-2">
                            @foreach($dailyTrend as $day)
                            <div class="text-center">
                                <div class="text-xs text-gray-500 mb-1">{{ $day['day'] }}</div>
                                <div class="bg-blue-100 rounded-lg p-2">
                                    <div class="text-lg font-bold text-blue-900">{{ $day['count'] }}</div>
                                    <div class="text-xs text-gray-500">{{ number_format($day['amount']/1000, 0) }}K</div>
                                </div>
                                <div class="text-xs text-gray-400 mt-1">{{ $day['date'] }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- By Source -->
                        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Transactions by Source</h3>
                            <div class="space-y-3">
                                @forelse($transactionsBySource as $item)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div>
                                        <span class="text-sm text-gray-700">{{ $item['source'] ?? 'Unknown' }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-semibold text-gray-900">{{ number_format($item['count']) }}</span>
                                        <span class="text-xs text-gray-500 ml-2">TZS {{ number_format($item['total'], 0) }}</span>
                                    </div>
                                </div>
                                @empty
                                <p class="text-gray-500 text-sm">No data available</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- By Category -->
                        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Transactions by Category</h3>
                            <div class="space-y-3">
                                @forelse($transactionsByCategory as $item)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                                        <span class="text-sm text-gray-700">{{ $item['transaction_category'] ?? 'Unknown' }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-semibold text-gray-900">{{ number_format($item['count']) }}</span>
                                        <span class="text-xs text-gray-500 ml-2">TZS {{ number_format($item['total'], 0) }}</span>
                                    </div>
                                </div>
                                @empty
                                <p class="text-gray-500 text-sm">No data available</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Transactions</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Payer</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($recentTransactions as $txn)
                                    <tr class="hover:bg-gray-50 cursor-pointer" wire:click="viewTransaction({{ $txn['id'] }})">
                                        <td class="px-4 py-2 text-sm text-blue-600">{{ Str::limit($txn['reference'], 20) }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ $txn['service_name'] ?? $txn['source'] ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ $txn['payer_name'] ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 text-right font-medium">{{ number_format($txn['amount'], 0) }}</td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 text-xs rounded-full
                                                @if($txn['status'] === 'COMPLETED' || $txn['status'] === 'SUCCESS') bg-green-100 text-green-800
                                                @elseif($txn['status'] === 'FAILED') bg-red-100 text-red-800
                                                @elseif($txn['status'] === 'PENDING') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $txn['status'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ \Carbon\Carbon::parse($txn['created_at'])->diffForHumans() }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="px-4 py-4 text-center text-gray-500">No recent transactions</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
                @if($selectedMenuItem == 3 && ($permissions['canView'] ?? false))
                <!-- Transaction List with Advanced Filters -->
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <!-- Filters -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
                            <button wire:click="clearFilters" class="text-sm text-blue-600 hover:text-blue-800">Clear All</button>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                <select wire:model="status" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All</option>
                                    @foreach($statuses as $s) <option value="{{ $s }}">{{ $s }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Category</label>
                                <select wire:model="category" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All</option>
                                    @foreach($categories as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Source</label>
                                <select wire:model="source" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All</option>
                                    @foreach($sources as $src) <option value="{{ $src }}">{{ $src }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Service</label>
                                <select wire:model="serviceName" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All</option>
                                    @foreach($serviceNames as $sn) <option value="{{ $sn }}">{{ $sn }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Channel</label>
                                <select wire:model="channelCode" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All</option>
                                    @foreach($channelCodes as $ch) <option value="{{ $ch }}">{{ $ch }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Reconciliation</label>
                                <select wire:model="reconciliationStatus" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All</option>
                                    @foreach($reconciliationStatuses as $rs) <option value="{{ $rs }}">{{ $rs }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Date From</label>
                                <input type="date" wire:model="dateFrom" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Date To</label>
                                <input type="date" wire:model="dateTo" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Amount From</label>
                                <input type="number" wire:model="amountFrom" placeholder="0" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Amount To</label>
                                <input type="number" wire:model="amountTo" placeholder="0" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">External System</label>
                                <select wire:model="externalSystem" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All</option>
                                    @foreach($externalSystems as $es) <option value="{{ $es }}">{{ $es }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Per Page</label>
                                <select wire:model="perPage" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Dir</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer" wire:click="sortBy('reference')">
                                        Reference @if($sortField === 'reference') @if($sortDirection === 'asc') ↑ @else ↓ @endif @endif
                                    </th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Payer/Sender</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Destination</th>
                                    <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase cursor-pointer" wire:click="sortBy('amount')">
                                        Amount @if($sortField === 'amount') @if($sortDirection === 'asc') ↑ @else ↓ @endif @endif
                                    </th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer" wire:click="sortBy('status')">
                                        Status @if($sortField === 'status') @if($sortDirection === 'asc') ↑ @else ↓ @endif @endif
                                    </th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer" wire:click="sortBy('created_at')">
                                        Date @if($sortField === 'created_at') @if($sortDirection === 'asc') ↑ @else ↓ @endif @endif
                                    </th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($transactions as $transaction)
                                @php
                                    // Determine direction: Incoming (Bills Payment) vs Outgoing (IFT/EFT)
                                    $isIncoming = in_array($transaction->source, ['payment_api']) || $transaction->transaction_category === 'BILL_PAYMENT';
                                    $isIFT = $transaction->source === 'IFT_SERVICE' || $transaction->transaction_subcategory === 'IFT';
                                    $isEFT = $transaction->source === 'EFT_SERVICE' || in_array($transaction->transaction_subcategory, ['TIPS', 'TISS', 'EFT']);

                                    // Get destination info
                                    $destAccount = $transaction->credit_account ?? '-';
                                    $destBank = $transaction->lookup_bank_code ?? '';

                                    // Bank code to name mapping
                                    $bankNames = [
                                        'CRDBTZTZ' => 'CRDB',
                                        'NMIBTZTZ' => 'NMB',
                                        'EABORTZTZ' => 'EXIM',
                                        'ABORTZTZ' => 'ABSA',
                                        'STABORTZ' => 'Stanbic',
                                        'BARBTZTZ' => 'Barclays',
                                        'SCBLTZTZ' => 'StanChart',
                                        'DTBKTZTZ' => 'DTB',
                                        'PBZATZTZ' => 'PBZ',
                                    ];
                                    $destBankName = $bankNames[$destBank] ?? ($destBank ? substr($destBank, 0, 4) : '');
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-2 py-2">
                                        @if($isIncoming)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800" title="Incoming Payment">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                                IN
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800" title="Outgoing Transfer">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                                                OUT
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2">
                                        <div class="text-sm font-medium text-blue-600">{{ Str::limit($transaction->reference, 16) }}</div>
                                        <div class="text-xs text-gray-500">{{ Str::limit($transaction->external_reference ?? '-', 16) }}</div>
                                    </td>
                                    <td class="px-2 py-2">
                                        @if($isIncoming)
                                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700">BILL PAY</span>
                                        @elseif($isIFT)
                                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700">IFT</span>
                                        @elseif($isEFT)
                                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-orange-50 text-orange-700">EFT</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-50 text-gray-700">{{ $transaction->transaction_subcategory ?? '-' }}</span>
                                        @endif
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $transaction->branch_code ?? '' }}</div>
                                    </td>
                                    <td class="px-2 py-2">
                                        <div class="text-sm text-gray-900">{{ Str::limit($transaction->payer_name ?? '-', 18) }}</div>
                                        <div class="text-xs text-gray-500">{{ $transaction->payer_phone ?? '-' }}</div>
                                    </td>
                                    <td class="px-2 py-2">
                                        @if($isEFT && $destBank)
                                            <div class="text-sm text-gray-900 font-medium">{{ $destBankName }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ Str::limit($destAccount, 14) }}</div>
                                        @elseif($isIFT)
                                            <div class="text-sm text-gray-900">NBC Internal</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ Str::limit($destAccount, 14) }}</div>
                                        @elseif($isIncoming)
                                            <div class="text-sm text-gray-900">SACCOS Account</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ Str::limit($destAccount, 14) }}</div>
                                        @else
                                            <div class="text-sm text-gray-500">-</div>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 text-right">
                                        <div class="text-sm font-semibold text-gray-900">{{ number_format($transaction->amount, 0) }}</div>
                                        <div class="text-xs text-gray-500">{{ $transaction->currency ?? 'TZS' }}</div>
                                    </td>
                                    <td class="px-2 py-2">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                            @if($transaction->status === 'COMPLETED' || $transaction->status === 'SUCCESS') bg-green-100 text-green-800
                                            @elseif($transaction->status === 'FAILED') bg-red-100 text-red-800
                                            @elseif($transaction->status === 'PENDING') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $transaction->status }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2 text-xs text-gray-500">
                                        {{ $transaction->created_at ? $transaction->created_at->format('M d, H:i') : '-' }}
                                    </td>
                                    <td class="px-2 py-2">
                                        <button wire:click="viewTransaction({{ $transaction->id }})" class="text-blue-600 hover:text-blue-900 text-xs font-medium">View</button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="9" class="px-3 py-4 text-center text-sm text-gray-500">No transactions found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $transactions->links() }}</div>
                </div>
                @endif
                @if($selectedMenuItem == 4 && ($permissions['canView'] ?? false))
                <!-- Pending Transactions -->
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Pending Transactions</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Payer</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $pendingTransactions = \App\Models\Transaction::where('status', 'PENDING')->orderByDesc('created_at')->limit(50)->get();
                                @endphp
                                @forelse($pendingTransactions as $txn)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm text-blue-600">{{ Str::limit($txn->reference, 20) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $txn->service_name ?? $txn->source }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $txn->payer_name ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900 text-right font-medium">{{ number_format($txn->amount, 0) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $txn->created_at ? $txn->created_at->diffForHumans() : '-' }}</td>
                                    <td class="px-4 py-2">
                                        <button wire:click="viewTransaction({{ $txn->id }})" class="text-blue-600 hover:text-blue-900 text-xs">View</button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="px-4 py-4 text-center text-gray-500">No pending transactions</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                @if($selectedMenuItem == 5 && ($permissions['canManage'] ?? false))
                <!-- Reconciliation Section -->
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Unreconciled Transactions</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">External Ref</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $unreconciledTxns = \App\Models\Transaction::where('reconciliation_status', 'UNRECONCILED')->orderByDesc('created_at')->limit(50)->get();
                                @endphp
                                @forelse($unreconciledTxns as $txn)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm text-blue-600">{{ Str::limit($txn->reference, 20) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $txn->external_reference ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $txn->service_name ?? $txn->source }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900 text-right font-medium">{{ number_format($txn->amount, 0) }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 text-xs rounded-full
                                            @if($txn->status === 'COMPLETED' || $txn->status === 'SUCCESS') bg-green-100 text-green-800
                                            @elseif($txn->status === 'FAILED') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ $txn->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $txn->created_at ? $txn->created_at->format('M d, Y') : '-' }}</td>
                                    <td class="px-4 py-2">
                                        <div class="flex space-x-2">
                                            <button wire:click="viewTransaction({{ $txn->id }})" class="text-blue-600 hover:text-blue-900 text-xs">View</button>
                                            <button wire:click="reconcileTransaction({{ $txn->id }})" class="text-green-600 hover:text-green-900 text-xs">Reconcile</button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="px-4 py-4 text-center text-gray-500">All transactions are reconciled</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
    <!-- View Transaction Modal -->
    @if($showTransactionModal && $selectedTransaction)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeTransactionModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Transaction Details</h3>
                        <button wire:click="closeTransactionModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="max-h-[70vh] overflow-y-auto space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wider">Basic Information</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Reference</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $selectedTransaction->reference }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">External Reference</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $selectedTransaction->external_reference ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Correlation ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $selectedTransaction->correlation_id ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Amount</dt>
                                    <dd class="mt-1 text-lg font-bold text-gray-900">{{ number_format($selectedTransaction->amount, 2) }} {{ $selectedTransaction->currency ?? 'TZS' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            @if($selectedTransaction->status === 'COMPLETED' || $selectedTransaction->status === 'SUCCESS') bg-green-100 text-green-800
                                            @elseif($selectedTransaction->status === 'FAILED') bg-red-100 text-red-800
                                            @elseif($selectedTransaction->status === 'PENDING') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $selectedTransaction->status }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Reconciliation</dt>
                                    <dd class="mt-1">
                                        <span class="px-2 py-1 text-xs rounded-full
                                            @if($selectedTransaction->reconciliation_status === 'RECONCILED') bg-green-100 text-green-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ $selectedTransaction->reconciliation_status ?? '-' }}
                                        </span>
                                    </dd>
                                </div>
                            </div>
                        </div>

                        <!-- Direction & Destination -->
                        @php
                            $modalIsIncoming = in_array($selectedTransaction->source, ['payment_api']) || $selectedTransaction->transaction_category === 'BILL_PAYMENT';
                            $modalIsIFT = $selectedTransaction->source === 'IFT_SERVICE' || $selectedTransaction->transaction_subcategory === 'IFT';
                            $modalIsEFT = $selectedTransaction->source === 'EFT_SERVICE' || in_array($selectedTransaction->transaction_subcategory, ['TIPS', 'TISS', 'EFT']);
                            $modalDestBank = $selectedTransaction->lookup_bank_code ?? '';
                            $modalBankNames = [
                                'CRDBTZTZ' => 'CRDB Bank',
                                'NMIBTZTZ' => 'NMB Bank',
                                'EABORTZTZ' => 'Exim Bank',
                                'ABORTZTZ' => 'ABSA Bank',
                                'STABORTZ' => 'Stanbic Bank',
                                'BARBTZTZ' => 'Barclays Bank',
                                'SCBLTZTZ' => 'Standard Chartered',
                                'DTBKTZTZ' => 'DTB Bank',
                                'PBZATZTZ' => 'PBZ Bank',
                            ];
                            $modalDestBankName = $modalBankNames[$modalDestBank] ?? $modalDestBank;
                        @endphp
                        <div class="bg-indigo-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wider">Direction & Destination</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Direction</dt>
                                    <dd class="mt-1">
                                        @if($modalIsIncoming)
                                            <span class="inline-flex items-center px-3 py-1 rounded text-sm font-medium bg-green-100 text-green-800">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                                INCOMING
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded text-sm font-medium bg-blue-100 text-blue-800">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                                                OUTGOING
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Transaction Type</dt>
                                    <dd class="mt-1">
                                        @if($modalIsIncoming)
                                            <span class="px-3 py-1 rounded text-sm font-medium bg-green-50 text-green-700">Bill Payment</span>
                                        @elseif($modalIsIFT)
                                            <span class="px-3 py-1 rounded text-sm font-medium bg-purple-50 text-purple-700">Internal Funds Transfer (IFT)</span>
                                        @elseif($modalIsEFT)
                                            <span class="px-3 py-1 rounded text-sm font-medium bg-orange-50 text-orange-700">External Funds Transfer (EFT)</span>
                                        @else
                                            <span class="px-3 py-1 rounded text-sm font-medium bg-gray-50 text-gray-700">{{ $selectedTransaction->transaction_subcategory ?? 'Other' }}</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Destination</dt>
                                    <dd class="mt-1">
                                        @if($modalIsEFT && $modalDestBank)
                                            <div class="text-sm font-medium text-gray-900">{{ $modalDestBankName }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $selectedTransaction->credit_account ?? '-' }}</div>
                                        @elseif($modalIsIFT)
                                            <div class="text-sm font-medium text-gray-900">NBC Internal</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $selectedTransaction->credit_account ?? '-' }}</div>
                                        @elseif($modalIsIncoming)
                                            <div class="text-sm font-medium text-gray-900">SACCOS Account</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $selectedTransaction->credit_account ?? '-' }}</div>
                                        @else
                                            <div class="text-sm text-gray-500">-</div>
                                        @endif
                                    </dd>
                                </div>
                                @if($modalDestBank)
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Bank Code (SWIFT/BIC)</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $modalDestBank }}</dd>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Service & Channel Information -->
                        <div class="bg-blue-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wider">Service & Channel</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Service Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->service_name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Source</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->source ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Category</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->transaction_category ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Subcategory</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->transaction_subcategory ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Channel ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->channel_id ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Channel Code</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->channel_code ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">SP Code</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->sp_code ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Branch Code</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->branch_code ?? '-' }}</dd>
                                </div>
                            </div>
                        </div>

                        <!-- Payer Information -->
                        <div class="bg-green-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wider">Payer Information</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Payer Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $selectedTransaction->payer_name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Payer Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->payer_phone ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Payer Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->payer_email ?? '-' }}</dd>
                                </div>
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="bg-purple-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wider">Account Information</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Credit Account</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $selectedTransaction->credit_account ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Credit Currency</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->credit_currency ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Lookup Account</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $selectedTransaction->lookup_account_number ?? '-' }}</dd>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Gateway Information -->
                        <div class="bg-yellow-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wider">Payment Gateway</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Gateway Reference</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $selectedTransaction->gateway_ref ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Biller Receipt</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $selectedTransaction->biller_receipt ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Payment Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->payment_type ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Approach</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->approach ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Callback URL</dt>
                                    <dd class="mt-1 text-sm text-gray-900 truncate" title="{{ $selectedTransaction->callback_url }}">{{ Str::limit($selectedTransaction->callback_url, 40) ?? '-' }}</dd>
                                </div>
                            </div>
                        </div>

                        <!-- External System -->
                        @if($selectedTransaction->external_system)
                        <div class="bg-orange-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wider">External System</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">External System</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->external_system }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">External Transaction ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $selectedTransaction->external_transaction_id ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">External Status Code</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->external_status_code ?? '-' }}</dd>
                                </div>
                                <div class="col-span-2 md:col-span-3">
                                    <dt class="text-xs font-medium text-gray-500">External Status Message</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->external_status_message ?? '-' }}</dd>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Timestamps -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wider">Timestamps</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Created At</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->created_at ? $selectedTransaction->created_at->format('Y-m-d H:i:s') : '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Received At</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->received_at ? $selectedTransaction->received_at->format('Y-m-d H:i:s') : '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Initiated At</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->initiated_at ? $selectedTransaction->initiated_at->format('Y-m-d H:i:s') : '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Processed At</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->processed_at ? $selectedTransaction->processed_at->format('Y-m-d H:i:s') : '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Completed At</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->completed_at ? $selectedTransaction->completed_at->format('Y-m-d H:i:s') : '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Request Timestamp</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->request_timestamp ? $selectedTransaction->request_timestamp->format('Y-m-d H:i:s') : '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Initiated By</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedTransaction->initiated_by ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Client IP</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $selectedTransaction->client_ip ?? '-' }}</dd>
                                </div>
                            </div>
                        </div>

                        <!-- Narration -->
                        @if($selectedTransaction->narration || $selectedTransaction->description)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wider">Narration</h4>
                            <p class="text-sm text-gray-700">{{ $selectedTransaction->narration ?? $selectedTransaction->description ?? '-' }}</p>
                        </div>
                        @endif

                        <!-- Error Information -->
                        @if($selectedTransaction->error_message || $selectedTransaction->status === 'FAILED')
                        <div class="bg-red-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-red-900 mb-3 uppercase tracking-wider">Error Information</h4>
                            <div class="space-y-2">
                                @if($selectedTransaction->error_code)
                                <div><span class="text-xs font-medium text-gray-500">Error Code:</span> <span class="text-sm text-red-700">{{ $selectedTransaction->error_code }}</span></div>
                                @endif
                                @if($selectedTransaction->error_message)
                                <div><span class="text-xs font-medium text-gray-500">Error Message:</span> <span class="text-sm text-red-700">{{ $selectedTransaction->error_message }}</span></div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="closeTransactionModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Reconciliation Modal -->
    @if($showReconciliationModal && $selectedTransaction)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showReconciliationModal', false)"></div>
            <div class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Reconcile Transaction</h3>
                    <p class="text-sm text-gray-600 mb-4">Reference: <strong>{{ $selectedTransaction->reference }}</strong></p>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reconciliation Notes</label>
                        <textarea wire:model="reconciliationNotes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter reconciliation notes..."></textarea>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse">
                    <button wire:click="confirmReconciliation" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Confirm Reconciliation
                    </button>
                    <button wire:click="$set('showReconciliationModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
