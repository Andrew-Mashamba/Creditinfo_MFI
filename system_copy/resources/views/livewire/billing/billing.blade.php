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
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Bills Management</h1>
                        <p class="text-gray-600 mt-1">Manage, track, and analyze all system and manual bills</p>
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
                                <p class="text-sm font-medium text-gray-500">Total Bills</p>
                                <p class="text-lg font-semibold text-gray-900">{{ \App\Models\Bill::count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Due</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format(\App\Models\Bill::sum('amount_due'), 2) }} TZS</p>
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
                                <p class="text-lg font-semibold text-gray-900">{{ \App\Models\Bill::where('status','PENDING')->count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-indigo-100 rounded-lg">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Paid</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format(\App\Models\Bill::sum('amount_paid'), 2) }} TZS</p>
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
                            placeholder="Search bills, members, or services..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"
                            aria-label="Search bills"
                        />
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Navigation</h3>
                    @php
                        $sections = [
                            ['id' => 1, 'label' => 'Dashboard Overview', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'description' => 'Analytics and insights'],
                            ['id' => 2, 'label' => 'New Bill', 'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6', 'description' => 'Create bill entry'],
                            ['id' => 3, 'label' => 'Bill List', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'description' => 'View all bills'],
                            ['id' => 4, 'label' => 'Pending Bills', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'description' => 'Awaiting payment'],
                            ['id' => 5, 'label' => 'Bill Types', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'description' => 'System/Manual'],
                            ['id' => 6, 'label' => 'Reports', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'description' => 'Generate reports'],
                        ];
                    @endphp
                    <nav class="space-y-2">
                        @foreach ($sections as $section)
                            @php
                                $isActive = $selectedMenuItem == $section['id'];
                            @endphp
                            @if($permissions['canView'] ?? false)
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
                            @else
                            <div class="relative w-full group transition-all duration-200 opacity-50 cursor-not-allowed" title="No permission to access this section">
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
                            </div>
                            @endif
                        @endforeach
                    </nav>
                </div>
            </div>
            <!-- Main Content Area -->
            <div class="flex-1">
                @if($selectedMenuItem == 1)
                <!-- Dashboard Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Total Bills Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-blue-900">Total Bills</h3>
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="text-3xl font-bold text-blue-900 mb-2">{{ \App\Models\Bill::count() }}</div>
                        <p class="text-sm text-blue-700">All system and manual bills</p>
                    </div>
                    <!-- Total Due Card -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-green-900">Total Due</h3>
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="text-3xl font-bold text-green-900 mb-2">{{ number_format(\App\Models\Bill::sum('amount_due'), 2) }} TZS</div>
                        <p class="text-sm text-green-700">Outstanding amount</p>
                    </div>
                    <!-- Pending Bills Card -->
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-yellow-900">Pending Bills</h3>
                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="text-3xl font-bold text-yellow-900 mb-2">{{ \App\Models\Bill::where('status','PENDING')->count() }}</div>
                        <p class="text-sm text-yellow-700">Awaiting payment</p>
                    </div>
                </div>
                <!-- Recent Bills Table -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Bills</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Control #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach(\App\Models\Bill::with(['client','service'])->latest()->take(5)->get() as $bill)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->control_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->client_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->service->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($bill->amount_due, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $bill->status === 'PAID' ? 'bg-green-100 text-green-800' :
                                               ($bill->status === 'CANCELLED' ? 'bg-red-100 text-red-800' :
                                               ($bill->status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                            {{ $bill->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->due_date ? $bill->due_date->format('Y-m-d') : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Chart Placeholder -->
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Billing Trend</h3>
                    <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                        <p class="text-gray-500">Chart visualization would go here</p>
                    </div>
                </div>
                @endif
                @if($selectedMenuItem == 3)
                <!-- Bill List Table -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 mb-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
                        <div class="flex flex-wrap gap-2">
                            <input type="text" wire:model.debounce.300ms="search" placeholder="Search bills, members, or services..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <select wire:model="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Statuses</option>
                                <option value="PENDING">Pending</option>
                                <option value="PAID">Paid</option>
                                <option value="CANCELLED">Cancelled</option>
                            </select>
                            <input type="date" wire:model="dateFrom" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="date" wire:model="dateTo" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex gap-2 items-center">
                            <label class="text-sm text-gray-500">Per Page:</label>
                            <select wire:model="perPage" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('control_number')">
                                        Control Number
                                        @if($sortField === 'control_number')
                                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                        @endif
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('member_id')">
                                        Member
                                        @if($sortField === 'member_id')
                                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                        @endif
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('service_id')">
                                        Service
                                        @if($sortField === 'service_id')
                                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                        @endif
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('amount_due')">
                                        Amount
                                        @if($sortField === 'amount_due')
                                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                        @endif
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                                        Status
                                        @if($sortField === 'status')
                                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                        @endif
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('due_date')">
                                        Due Date
                                        @if($sortField === 'due_date')
                                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                        @endif
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($bills as $bill)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->control_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->client_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->service->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($bill->amount_due, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                {{ $bill->status === 'PAID' ? 'bg-green-100 text-green-800' :
                                                   ($bill->status === 'CANCELLED' ? 'bg-red-100 text-red-800' :
                                                   ($bill->status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                                {{ $bill->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->due_date ? $bill->due_date->format('Y-m-d') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-2">
                                                @if($permissions['canView'] ?? false)
                                                <button wire:click="viewBill({{ $bill->id }})" class="text-indigo-600 hover:text-indigo-900">View</button>
                                                @endif
                                                @if($bill->status === 'PENDING')
                                                    @if($permissions['canEdit'] ?? false)
                                                    <button wire:click="confirmPause({{ $bill->id }})" class="text-yellow-600 hover:text-yellow-900">Pause</button>
                                                    @endif
                                                    @if($permissions['canDelete'] ?? false)
                                                    <button wire:click="confirmDelete({{ $bill->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No bills found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $bills->links() }}
                    </div>
                </div>
                @endif
                @if($selectedMenuItem == 4)
<!-- Pending Bills Section -->
<div class="bg-white rounded-xl p-6 border border-yellow-200 mb-8">
    <h3 class="text-lg font-semibold text-yellow-900 mb-4">Pending Bills</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-yellow-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Control #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach(\App\Models\Bill::with(['client','service'])->where('status','PENDING')->latest()->paginate(10) as $bill)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->control_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->client_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->service->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($bill->amount_due, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->due_date ? $bill->due_date->format('Y-m-d') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div class="flex space-x-2">
                            <button wire:click="viewBill({{ $bill->id }})" class="text-indigo-600 hover:text-indigo-900">View</button>
                            <button wire:click="confirmDelete({{ $bill->id }})" class="text-red-600 hover:text-red-900">Cancel</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@if($selectedMenuItem == 2)
<!-- Create New Bill Section -->
@if($permissions['canCreate'] ?? false)
<div class="bg-white rounded-xl p-6 border border-gray-200 mb-8">
    <livewire:billing.create-bill />
</div>
@else
<div class="bg-white rounded-xl p-6 border border-gray-200 mb-8">
    <div class="text-center py-8">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
        </svg>
        <p class="text-gray-500">You don't have permission to create bills.</p>
    </div>
</div>
@endif
@endif
@if($selectedMenuItem == 6)
<!-- Reports Section -->
<div class="bg-white rounded-xl p-6 border border-gray-200 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Billing Reports & Analytics</h3>
    <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
        <p class="text-gray-500">Reports, analytics, and export features coming soon.</p>
    </div>
</div>
@endif
            </div>
        </div>
    </div>
</div>





    <!-- Bill Details Modal -->
    @if($selectedBill)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 max-w-2xl w-full">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Bill Details</h3>
                    <button wire:click="$set('selectedBill', null)" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Control Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedBill->control_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Client</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedBill->client_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Service</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedBill->service->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Amount Due</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ number_format($selectedBill->amount_due, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Amount Paid</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ number_format($selectedBill->amount_paid, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedBill->status }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedBill->due_date->format('Y-m-d') }}</dd>
                        </div>
<div>
                            <dt class="text-sm font-medium text-gray-500">Payment Mode</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @switch($selectedBill->payment_mode)
                                    @case(1) Partial @break
                                    @case(2) Full @break
                                    @case(3) Exact @break
                                    @case(4) Limited @break
                                    @case(5) Infinity @break
                                @endswitch
                            </dd>
                        </div>
                    </dl>

                    @if($selectedBill->payments->count() > 0)
                        <div class="mt-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Payment History</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Channel</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($selectedBill->payments as $payment)
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ $payment->paid_at->format('Y-m-d H:i') }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ number_format($payment->amount, 2) }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ $payment->payment_channel }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ $payment->status }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-medium mb-4">Confirm Delete</h3>
                <p class="text-sm text-gray-500 mb-4">Are you sure you want to delete this bill? This action cannot be undone.</p>
                <div class="flex justify-end space-x-4">
                    <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button wire:click="deleteBill" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Pause Confirmation Modal -->
    @if($showPauseModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-medium mb-4">Pause Bill</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Reason for pausing</label>
                    <textarea wire:model="pauseReason" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3"></textarea>
                </div>
                <div class="flex justify-end space-x-4">
                    <button wire:click="$set('showPauseModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button wire:click="pauseBill" class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        Pause
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    document.addEventListener('livewire:load', function () {
        // Handle service selection to update amount constraints
        Livewire.on('serviceSelected', function(service) {
            const amountInput = document.querySelector('input[wire\\:model="amount"]');
            if (amountInput) {
                amountInput.min = service.lower_limit;
                if (service.upper_limit) {
                    amountInput.max = service.upper_limit;
                }
            }
        });
    });
</script>
@endpush
