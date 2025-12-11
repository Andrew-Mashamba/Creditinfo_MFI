<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-blue-900 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Teller Management</h1>
                        <p class="text-gray-600 mt-1">Manage tellers, transactions, and daily operations</p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Tills</p>
                                <p class="text-lg font-semibold text-gray-900">{{ DB::table('tellers')->count() }}</p>
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
                                <p class="text-sm font-medium text-gray-500">Assigned Tills</p>
                                <p class="text-lg font-semibold text-gray-900">{{ DB::table('tellers')->where('employee_id', '!=', null)->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Pending Tills</p>
                                <p class="text-lg font-semibold text-gray-900">{{ DB::table('tellers')->where('employee_id', '=', null)->count() }}</p>
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
                            placeholder="Search tellers, transactions..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"
                            aria-label="Search tellers"
                        />
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Teller Operations</h3>
                    
                    @php
                        $teller_sections = [
                            [
                                'id' => 1, 
                                'label' => 'Tellers', 
                                'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                                'description' => 'Manage teller accounts'
                            ],
                            [
                                'id' => 2, 
                                'label' => 'Teller Transactions', 
                                'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                                'description' => 'Inter-teller transfers'
                            ],
                            [
                                'id' => 3, 
                                'label' => 'Strong Room Management', 
                                'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                                'description' => 'Vault operations'
                            ],
                            [
                                'id' => 4, 
                                'label' => 'End Of Day', 
                                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                'description' => 'Daily reconciliation'
                            ],
                            [
                                'id' => 5, 
                                'label' => 'Teller Position', 
                                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                                'description' => 'Position monitoring'
                            ],
                        ];
                    @endphp

                    <nav class="space-y-2">
                        @foreach ($teller_sections as $section)
                            @php
                                $count = 0;
                                if ($section['id'] == 1) {
                                    $count = DB::table('tellers')->where('employee_id', '=', null)->count();
                                }
                                $isActive = $this->teller_tab == $section['id'];
                            @endphp

                            <button
                                wire:click="menu_sub_button({{ $section['id'] }})"
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
                                    <div wire:loading wire:target="menu_sub_button({{ $section['id'] }})" class="mr-3">
                                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>

                                    <!-- Icon -->
                                    <div wire:loading.remove wire:target="menu_sub_button({{ $section['id'] }})" class="mr-3">
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
                        @endforeach
                    </nav>
                </div>

                <!-- Quick Actions -->
                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Quick Actions</h3>
                    <div class="space-y-2">
                        <button wire:click="menu_sub_button(1)" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            New Teller
                        </button>
                        <button wire:click="menu_sub_button(2)" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            New Transaction
                        </button>
                        <button wire:click="menu_sub_button(4)" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Daily Report
                        </button>
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
                                    @switch($this->teller_tab)
                                        @case(1) Teller Management @break
                                        @case(2) Teller Transactions @break
                                        @case(3) Strong Room Management @break
                                        @case(4) End Of Day Operations @break
                                        @case(5) Teller Position Monitoring @break
                                        @default Teller Management
                                    @endswitch
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    @switch($this->teller_tab)
                                        @case(1) Register and manage teller accounts and assignments @break
                                        @case(2) Process inter-teller transfers and transactions @break
                                        @case(3) Manage cash movements between tellers and vault @break
                                        @case(4) Daily reconciliation and till closure operations @break
                                        @case(5) Monitor teller positions and handle discrepancies @break
                                        @default Register and manage teller accounts and assignments
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
                                            Teller Management
                                        </a>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                                                @switch($this->teller_tab)
                                                    @case(1) Tellers @break
                                                    @case(2) Transactions @break
                                                    @case(3) Strong Room @break
                                                    @case(4) End of Day @break
                                                    @case(5) Position @break
                                                    @default Tellers
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
                        <div wire:loading wire:target="menu_sub_button" class="flex items-center justify-center py-12">
                            <div class="flex items-center space-x-2">
                                <svg class="w-6 h-6 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-gray-600">Loading...</span>
                            </div>
                        </div>

                        <!-- Dynamic Content -->
                        <div wire:loading.remove wire:target="menu_sub_button" class="min-h-[400px]">
                            @if ($this->teller_tab == 1)
                                <livewire:teller-management.teller-register />
                            @elseif ($this->teller_tab == 2)
                                <livewire:teller-management.teller-transaction />
                            @elseif ($this->teller_tab == 3)
                                <livewire:teller-management.strong-room />
                            @elseif ($this->teller_tab == 4)
                                <livewire:teller-management.teller-report />
                            @elseif ($this->teller_tab == 5)
                                <livewire:teller-management.teller-position />
                            @else
                                <!-- Default Dashboard Overview -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                                    <!-- Teller Overview Card -->
                                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold text-blue-900">Teller Overview</h3>
                                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="space-y-3">
                                            <div class="flex justify-between">
                                                <span class="text-sm text-blue-700">Total Tills:</span>
                                                <span class="font-semibold text-blue-900">{{ DB::table('tellers')->count() }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-blue-700">Assigned:</span>
                                                <span class="font-semibold text-green-600">{{ DB::table('tellers')->where('employee_id', '!=', null)->count() }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-blue-700">Pending:</span>
                                                <span class="font-semibold text-red-600">{{ DB::table('tellers')->where('employee_id', '=', null)->count() }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Recent Transactions Card -->
                                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold text-green-900">Recent Transactions</h3>
                                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                            </svg>
                                        </div>
                                        <div class="space-y-2">
                                            @php
                                                $recentTransactions = DB::table('general_ledger')->where('transaction_type', 'IFT')->orderBy('created_at', 'desc')->limit(3)->get();
                                            @endphp
                                            @foreach($recentTransactions as $transaction)
                                                <div class="flex items-center justify-between bg-white p-2 rounded-lg">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ Str::limit($transaction->narration ?? 'Transaction', 20) }}</p>
                                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y') }}</p>
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">{{number_format($transaction->debit + $transaction->credit, 2)}} TZS</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Daily Operations Card -->
                                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold text-yellow-900">Daily Operations</h3>
                                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-3xl font-bold text-yellow-900 mb-2">{{ DB::table('teller_end_of_day_positions')->whereDate('created_at', today())->count() }}</div>
                                            <p class="text-sm text-yellow-700">Tills closed today</p>
                                            <button wire:click="menu_sub_button(4)" class="mt-3 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                                                End of Day
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Actions Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                                    <button wire:click="menu_sub_button(1)" class="bg-white p-6 rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-lg transition-all duration-200 group">
                                        <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mb-4 group-hover:bg-blue-200 transition-colors">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </div>
                                        <h3 class="font-semibold text-gray-900 mb-2">New Teller</h3>
                                        <p class="text-sm text-gray-600">Register a new teller account</p>
                                    </button>

                                    <button wire:click="menu_sub_button(2)" class="bg-white p-6 rounded-xl border border-gray-200 hover:border-green-300 hover:shadow-lg transition-all duration-200 group">
                                        <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg mb-4 group-hover:bg-green-200 transition-colors">
                                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="font-semibold text-gray-900 mb-2">Transactions</h3>
                                        <p class="text-sm text-gray-600">Process inter-teller transfers</p>
                                    </button>

                                    <button wire:click="menu_sub_button(3)" class="bg-white p-6 rounded-xl border border-gray-200 hover:border-purple-300 hover:shadow-lg transition-all duration-200 group">
                                        <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg mb-4 group-hover:bg-purple-200 transition-colors">
                                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                        <h3 class="font-semibold text-gray-900 mb-2">Strong Room</h3>
                                        <p class="text-sm text-gray-600">Manage vault operations</p>
                                    </button>

                                    <button wire:click="menu_sub_button(5)" class="bg-white p-6 rounded-xl border border-gray-200 hover:border-orange-300 hover:shadow-lg transition-all duration-200 group">
                                        <div class="flex items-center justify-center w-12 h-12 bg-orange-100 rounded-lg mb-4 group-hover:bg-orange-200 transition-colors">
                                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="font-semibold text-gray-900 mb-2">Positions</h3>
                                        <p class="text-sm text-gray-600">Monitor teller positions</p>
                                    </button>
                                </div>

                                <!-- Activity Timeline -->
                                <div class="bg-white rounded-xl p-6 border border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                                    <div class="space-y-4">
                                        @php
                                            $recentActivity = DB::table('general_ledger')->orderBy('created_at', 'desc')->limit(5)->get();
                                        @endphp
                                        @foreach($recentActivity as $activity)
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-shrink-0">
                                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900">{{ $activity->narration ?? 'Transaction' }}</p>
                                                    <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($activity->created_at)->format('M d, Y H:i') }}</p>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <span class="text-sm font-semibold text-gray-900">{{number_format($activity->debit + $activity->credit, 2)}} TZS</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
