{{-- Simplified HR Dashboard --}}
<div class="min-h-screen bg-gray-50">
    <div class="p-6">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Human Resources Management</h1>
            <p class="text-gray-600 mt-1">Manage employees, payroll, and HR operations</p>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total Employees</p>
                        <p class="text-3xl font-bold text-blue-900 mt-1">{{ $totalEmployees }}</p>
                        <p class="text-xs text-gray-500 mt-1">All employees</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-8 h-8 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Active Employees</p>
                        <p class="text-3xl font-bold text-blue-900 mt-1">{{ $activeEmployees }}</p>
                        <p class="text-xs text-green-600 mt-1">{{ $totalEmployees > 0 ? round(($activeEmployees / $totalEmployees) * 100, 1) : 0 }}% of total</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-8 h-8 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Inactive/Suspended</p>
                        <p class="text-3xl font-bold text-blue-900 mt-1">{{ $inactiveEmployees + $suspendedEmployees }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $inactiveEmployees }} inactive, {{ $suspendedEmployees }} suspended</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-8 h-8 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Departments</p>
                        <p class="text-3xl font-bold text-blue-900 mt-1">{{ $totalDepartments }}</p>
                        <p class="text-xs text-gray-500 mt-1">Active departments</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-8 h-8 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-6">
            {{-- Sidebar Navigation --}}
            <div class="w-64 bg-white rounded-lg shadow">
                <div class="p-4">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Navigation</h3>
                    <nav class="space-y-1">
                        @if($permissions['canView'] ?? false)
                        <button wire:click="setMenuNumber(0)" 
                            class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md {{ $menuNumber === 0 ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard
                        </button>
                        @endif

                        @if($permissions['canEmployees'] ?? false)
                        <button wire:click="setMenuNumber(1)" 
                            class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md {{ $menuNumber === 1 ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Employees
                        </button>
                        @endif

                        @if($permissions['canPayroll'] ?? false)
                        <button wire:click="setMenuNumber(2)" 
                            class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md {{ $menuNumber === 2 ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Payroll
                        </button>
                        @endif

                        @if($permissions['canLeave'] ?? false)
                        <button wire:click="setMenuNumber(3)" 
                            class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md {{ $menuNumber === 3 ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Leave Management
                        </button>
                        @endif

                        @if($permissions['canAttendance'] ?? false)
                        <button wire:click="setMenuNumber(4)" 
                            class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md {{ $menuNumber === 4 ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Attendance
                        </button>
                        @endif

                        @if($permissions['canRequests'] ?? false)
                        <button wire:click="setMenuNumber(5)" 
                            class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md {{ $menuNumber === 5 ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Request Management
                        </button>
                        @endif
                    </nav>
                </div>
            </div>

            {{-- Main Content Area --}}
            <div class="flex-1">
                <div class="bg-white rounded-lg shadow min-h-[500px]">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">
                            @switch($menuNumber)
                                @case(0) Dashboard Overview @break
                                @case(1) Employee Management @break
                                @case(2) Payroll Management @break
                                @case(3) Leave Management @break
                                @case(4) Attendance Tracking @break
                                @case(5) Request Management @break
                                @default Dashboard Overview
                            @endswitch
                        </h2>
                    </div>

                    <div class="p-6">
                        @switch($menuNumber)
                            @case(0)
                                @if($permissions['canView'] ?? false)
                                {{-- Dashboard Content --}}
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    {{-- Left Column --}}
                                    <div class="lg:col-span-2 space-y-6">
                                        {{-- Salary & Payroll Overview --}}
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="bg-white border border-gray-200 rounded-lg p-5">
                                                <div class="flex items-center justify-between mb-3">
                                                    <h3 class="text-sm font-semibold text-gray-700">Total Salary Expense</h3>
                                                    <svg class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <p class="text-2xl font-bold text-blue-900">TZS {{ number_format($totalSalaryExpense, 0) }}</p>
                                                <p class="text-xs text-gray-500 mt-1">Active employees only</p>
                                            </div>

                                            <div class="bg-white border border-gray-200 rounded-lg p-5">
                                                <div class="flex items-center justify-between mb-3">
                                                    <h3 class="text-sm font-semibold text-gray-700">Average Salary</h3>
                                                    <svg class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                    </svg>
                                                </div>
                                                <p class="text-2xl font-bold text-blue-900">TZS {{ number_format($averageSalary, 0) }}</p>
                                                <p class="text-xs text-gray-500 mt-1">Per employee</p>
                                            </div>
                                        </div>

                                        {{-- Top 5 Departments --}}
                                        <div class="bg-white border border-gray-200 rounded-lg p-5">
                                            <h3 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                </svg>
                                                Top Departments by Headcount
                                            </h3>
                                            <div class="space-y-3">
                                                @forelse($topDepartments as $dept)
                                                    <div>
                                                        <div class="flex items-center justify-between mb-1">
                                                            <span class="text-sm font-medium text-gray-700">{{ $dept['name'] }}</span>
                                                            <span class="text-sm font-semibold text-blue-900">{{ $dept['count'] }}</span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                                            <div class="bg-blue-900 h-2 rounded-full" style="width: {{ $dept['percentage'] }}%"></div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-gray-500">No department data available</p>
                                                @endforelse
                                            </div>
                                        </div>

                                        {{-- Employment Type Distribution --}}
                                        <div class="bg-white border border-gray-200 rounded-lg p-5">
                                            <h3 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                </svg>
                                                Employment Type Distribution
                                            </h3>
                                            <div class="grid grid-cols-2 gap-4">
                                                @forelse($employmentTypeStats as $type)
                                                    <div class="bg-gray-50 rounded-lg p-3 border-l-4 border-blue-900">
                                                        <p class="text-xs text-gray-600 font-medium">{{ $type['type'] }}</p>
                                                        <p class="text-xl font-bold text-gray-900 mt-1">{{ $type['count'] }}</p>
                                                        <p class="text-xs text-gray-500 mt-1">{{ $type['percentage'] }}% of workforce</p>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-gray-500 col-span-2">No data available</p>
                                                @endforelse
                                            </div>
                                        </div>

                                        {{-- Gender Distribution --}}
                                        <div class="bg-white border border-gray-200 rounded-lg p-5">
                                            <h3 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                </svg>
                                                Gender Distribution
                                            </h3>
                                            <div class="flex space-x-4">
                                                @forelse($genderStats as $gender)
                                                    <div class="flex-1 text-center">
                                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-2">
                                                            <span class="text-2xl font-bold text-blue-900">{{ $gender['percentage'] }}%</span>
                                                        </div>
                                                        <p class="text-sm font-medium text-gray-700">{{ $gender['gender'] }}</p>
                                                        <p class="text-xs text-gray-500">{{ $gender['count'] }} employees</p>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-gray-500">No data available</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Right Column --}}
                                    <div class="space-y-6">
                                        {{-- Monthly Payroll Card --}}
                                        <div class="bg-white border border-gray-200 rounded-lg p-5">
                                            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                Current Month Payroll
                                            </h3>
                                            <p class="text-2xl font-bold text-blue-900">TZS {{ number_format($monthlyPayrollTotal, 0) }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ date('F Y') }}</p>
                                            <div class="mt-3 pt-3 border-t">
                                                <div class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-600">Pending</span>
                                                    <span class="font-semibold text-orange-600">{{ $pendingPayroll }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Recent Hires --}}
                                        <div class="bg-white border border-gray-200 rounded-lg p-5">
                                            <h3 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                                </svg>
                                                Recent Hires (30 days)
                                            </h3>
                                            <div class="space-y-3">
                                                @forelse($recentHires as $hire)
                                                    <div class="flex items-start space-x-3 pb-3 border-b border-gray-100 last:border-0">
                                                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                                            <span class="text-xs font-semibold text-green-700">{{ strtoupper(substr($hire['name'], 0, 2)) }}</span>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $hire['name'] }}</p>
                                                            <p class="text-xs text-gray-500 truncate">{{ $hire['job_title'] }}</p>
                                                            <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($hire['hire_date'])->format('M d, Y') }}</p>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-gray-500">No recent hires</p>
                                                @endforelse
                                            </div>
                                        </div>

                                        {{-- Recent Exits --}}
                                        <div class="bg-white border border-gray-200 rounded-lg p-5">
                                            <h3 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                </svg>
                                                Recent Exits (60 days)
                                            </h3>
                                            <div class="space-y-3">
                                                @forelse($recentExits as $exit)
                                                    <div class="flex items-start space-x-3 pb-3 border-b border-gray-100 last:border-0">
                                                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                                                            <span class="text-xs font-semibold text-orange-700">{{ strtoupper(substr($exit['name'], 0, 2)) }}</span>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $exit['name'] }}</p>
                                                            <p class="text-xs text-gray-500 truncate">{{ $exit['job_title'] }}</p>
                                                            <div class="flex items-center mt-1">
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                                                    {{ $exit['exit_type'] }}
                                                                </span>
                                                                <span class="text-xs text-gray-400 ml-2">{{ \Carbon\Carbon::parse($exit['exit_date'])->format('M d, Y') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-gray-500">No recent exits</p>
                                                @endforelse
                                            </div>
                                        </div>

                                        {{-- Quick Actions --}}
                                        @if($permissions['canEmployees'] ?? false || $permissions['canPayroll'] ?? false)
                                        <div class="bg-white border border-gray-200 rounded-lg p-5">
                                            <h3 class="text-md font-semibold text-gray-900 mb-4">Quick Actions</h3>
                                            <div class="space-y-2">
                                                @if($permissions['canEmployees'] ?? false)
                                                <button wire:click="setMenuNumber(1)"
                                                    class="w-full p-3 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition text-sm font-medium flex items-center justify-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                    </svg>
                                                    Manage Employees
                                                </button>
                                                @endif
                                                @if($permissions['canPayroll'] ?? false)
                                                <button wire:click="setMenuNumber(2)"
                                                    class="w-full p-3 bg-white border-2 border-blue-900 text-blue-900 rounded-lg hover:bg-blue-50 transition text-sm font-medium flex items-center justify-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                    </svg>
                                                    Process Payroll
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                @break

                            @case(1)
                                @if($permissions['canEmployees'] ?? false)
                                {{-- Employee Management --}}
                                <livewire:h-r.employee-management />
                                @endif
                                @break

                            @case(2)
                                @if($permissions['canPayroll'] ?? false)
                                {{-- Payroll Management --}}
                                <livewire:h-r.payroll-management />
                                @endif
                                @break

                            @case(3)
                                @if($permissions['canLeave'] ?? false)
                                {{-- Leave Management --}}
                                <livewire:h-r.leave-management />
                                @endif
                                @break

                            @case(4)
                                @if($permissions['canAttendance'] ?? false)
                                {{-- Attendance --}}
                                <livewire:h-r.attendance />
                                @endif
                                @break

                            @case(5)
                                @if($permissions['canRequests'] ?? false)
                                {{-- Request Management --}}
                                <livewire:h-r.request-management />
                                @endif
                                @break

                            @default
                                <div class="text-center py-12">
                                    <p class="text-gray-500">Select an option from the sidebar</p>
                                </div>
                        @endswitch
                        
                        <!-- Show message if no permissions for current section -->
                        @if(!($permissions['canView'] ?? false) && !($permissions['canEmployees'] ?? false) && !($permissions['canPayroll'] ?? false) && !($permissions['canLeave'] ?? false) && !($permissions['canAttendance'] ?? false) && !($permissions['canRequests'] ?? false))
                            <div class="text-center py-12">
                                <div class="mx-auto h-12 w-12 text-gray-400">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No Access</h3>
                                <p class="mt-1 text-sm text-gray-500">You don't have permission to access any HR management features.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>