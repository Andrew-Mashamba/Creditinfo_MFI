{{-- HR Management Dashboard --}}
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-blue-900 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Human Resources Manager</h1>
                        <p class="text-gray-600 mt-1">Manage employees, departments, attendance, and documents</p>
        </div>
    </div>
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Employees</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $totalEmployees }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Departments</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $totalDepartments }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Active Leaves</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $activeLeaves }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-6">
            <!-- Sidebar -->
            <div class="w-80 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="relative">
                        <input type="text" placeholder="Search HR..." class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white" aria-label="Search HR" />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Navigation</h3>
                    <nav class="space-y-2">
                        <button wire:click="setMenuNumber(0)" class="relative w-full group transition-all duration-200">
                            <div class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $menuNumber === 0 ? 'bg-blue-900 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                <span>Dashboard</span>
                            </div>
                        </button>
                        <button wire:click="setMenuNumber(1)" class="relative w-full group transition-all duration-200">
                            <div class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $menuNumber === 1 ? 'bg-blue-900 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                <span>Recruitment</span>
                            </div>
                        </button>
                        <button wire:click="setMenuNumber(2)" class="relative w-full group transition-all duration-200">
                            <div class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $menuNumber === 2 ? 'bg-blue-900 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>Attendance</span>
                            </div>
                        </button>
                        <button wire:click="setMenuNumber(3)" class="relative w-full group transition-all duration-200">
                            <div class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $menuNumber === 3 ? 'bg-blue-900 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span>Leave Management</span>
                            </div>
                        </button>
                        <button wire:click="setMenuNumber(4)" class="relative w-full group transition-all duration-200">
                            <div class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $menuNumber === 4 ? 'bg-blue-900 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                <span>Documents</span>
                            </div>
                        </button>
                    </nav>
                </div>
            </div>
            <!-- Main Content Area -->
            <div class="flex-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">
                                @switch($menuNumber)
                                    @case(0) Dashboard @break
                                    @case(1) Recruitment @break
                                    @case(2) Attendance @break
                                    @case(3) Leave Management @break
                                    @case(4) Document Management @break
                                    @default Dashboard
                                @endswitch
                            </h2>
                            <p class="text-gray-600 mt-1">
                                @switch($menuNumber)
                                    @case(0) Overview of HR statistics and department data @break
                                    @case(1) Manage recruitment and onboarding @break
                                    @case(2) Track employee attendance @break
                                    @case(3) Manage leave requests and balances @break
                                    @case(4) Upload and manage HR documents @break
                                    @default Overview of HR statistics and department data
                                @endswitch
                            </p>
                        </div>
                    </div>
                    <div class="p-8 min-h-[400px]">
                        @switch($menuNumber)
                            @case(0)
                                <!-- HR Dashboard Cards -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                        <h3 class="text-lg font-semibold text-blue-900 mb-2">Total Employees</h3>
                                        <div class="text-3xl font-bold text-blue-900">{{ $totalEmployees }}</div>
                                    </div>
                                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                                        <h3 class="text-lg font-semibold text-green-900 mb-2">Departments</h3>
                                        <div class="text-3xl font-bold text-green-900">{{ $totalDepartments }}</div>
                                    </div>
                                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
                                        <h3 class="text-lg font-semibold text-yellow-900 mb-2">Active Leaves</h3>
                                        <div class="text-3xl font-bold text-yellow-900">{{ $activeLeaves }}</div>
                                    </div>
                                </div>
                                <!-- Department Statistics -->
                                <div class="bg-white rounded-xl p-6 border border-gray-200 mb-8">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Department Statistics</h3>
                    <div class="space-y-4">
                        @foreach($departmentStats as $dept)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                                <span class="text-gray-600">{{ $dept['name'] }}</span>
                            </div>
                            <span class="text-gray-800 font-medium">{{ $dept['count'] }} employees</span>
                        </div>
                        @endforeach
                                    </div>
                                </div>
                                {{-- Add a chart here if needed --}}
                                @break
                            @case(1)
                                <livewire:h-r.recruitment />
                                @break
                            @case(2)
                                <livewire:h-r.attendance />
                                @break
                            @case(3)
                                <livewire:h-r.leave-management />
                                @break
                            @case(4)
                                <!-- Modern File Upload for HR Documents -->
                                <div class="bg-white rounded-xl p-6 border border-gray-200 max-w-2xl mx-auto">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload HR Documents</h3>
                                    <livewire:components.file-uploader name="hr_documents" multiple="true" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" preview="true" showProgress="true" customClass="w-full" />
                                    {{-- List uploaded documents here if needed --}}
                                </div>
                                @break
                            @default
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome to HR Management</h3>
                                    <p class="text-gray-600">Select a section from the sidebar to get started</p>
                                </div>
                        @endswitch
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

