{{-- Employee Management --}}
<div>
    {{-- Header with Add Button --}}
    <div class="flex justify-between items-center mb-4">
        <div class="flex-1 max-w-md">
            <input type="text" wire:model.debounce.300ms="search" 
                placeholder="Search employees..." 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        {{--<button wire:click="openAddModal" 
            class="ml-4 px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700 transition">
            <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Employee
            </span>
        </button>--}}
    </div>

    {{-- Success Message --}}
    @if(session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Employees</p>
                    <h3 class="text-3xl font-bold text-blue-900 mt-1">{{ $totalEmployees }}</h3>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Active Employees</p>
                    <h3 class="text-3xl font-bold text-blue-900 mt-1">{{ $activeEmployees }}</h3>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Inactive Employees</p>
                    <h3 class="text-3xl font-bold text-blue-900 mt-1">{{ $inactiveEmployees }}</h3>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Suspended</p>
                    <h3 class="text-3xl font-bold text-blue-900 mt-1">{{ $suspendedEmployees }}</h3>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Advanced Filters --}}
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-700 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Advanced Filters
            </h3>
            <button wire:click="resetFilters" class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reset Filters
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Department</label>
                <select wire:model="departmentFilter" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                <select wire:model="statusFilter" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                    <option value="SUSPENDED">Suspended</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Employment Type</label>
                <select wire:model="employmentTypeFilter" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="full-time">Full Time</option>
                    <option value="part-time">Part Time</option>
                    <option value="contract">Contract</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Branch</label>
                <select wire:model="branchFilter" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Employee List Table --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortBy('first_name')" class="flex items-center text-xs font-semibold text-gray-700 uppercase tracking-wider hover:text-blue-600 transition">
                                <span>Employee</span>
                                @if($sortField === 'first_name')
                                    <svg class="w-3 h-3 ml-1 {{ $sortDirection === 'asc' ? '' : 'transform rotate-180' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortBy('employee_number')" class="flex items-center text-xs font-semibold text-gray-700 uppercase tracking-wider hover:text-blue-600 transition">
                                <span>Employee #</span>
                                @if($sortField === 'employee_number')
                                    <svg class="w-3 h-3 ml-1 {{ $sortDirection === 'asc' ? '' : 'transform rotate-180' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Phone</th>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortBy('department_id')" class="flex items-center text-xs font-semibold text-gray-700 uppercase tracking-wider hover:text-blue-600 transition">
                                <span>Department</span>
                                @if($sortField === 'department_id')
                                    <svg class="w-3 h-3 ml-1 {{ $sortDirection === 'asc' ? '' : 'transform rotate-180' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Branch</th>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortBy('job_title')" class="flex items-center text-xs font-semibold text-gray-700 uppercase tracking-wider hover:text-blue-600 transition">
                                <span>Position</span>
                                @if($sortField === 'job_title')
                                    <svg class="w-3 h-3 ml-1 {{ $sortDirection === 'asc' ? '' : 'transform rotate-180' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortBy('employment_type')" class="flex items-center text-xs font-semibold text-gray-700 uppercase tracking-wider hover:text-blue-600 transition">
                                <span>Type</span>
                                @if($sortField === 'employment_type')
                                    <svg class="w-3 h-3 ml-1 {{ $sortDirection === 'asc' ? '' : 'transform rotate-180' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortBy('basic_salary')" class="flex items-center text-xs font-semibold text-gray-700 uppercase tracking-wider hover:text-blue-600 transition">
                                <span>Salary</span>
                                @if($sortField === 'basic_salary')
                                    <svg class="w-3 h-3 ml-1 {{ $sortDirection === 'asc' ? '' : 'transform rotate-180' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortBy('employee_status')" class="flex items-center text-xs font-semibold text-gray-700 uppercase tracking-wider hover:text-blue-600 transition">
                                <span>Status</span>
                                @if($sortField === 'employee_status')
                                    <svg class="w-3 h-3 ml-1 {{ $sortDirection === 'asc' ? '' : 'transform rotate-180' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortBy('hire_date')" class="flex items-center text-xs font-semibold text-gray-700 uppercase tracking-wider hover:text-blue-600 transition">
                                <span>Hire Date</span>
                                @if($sortField === 'hire_date')
                                    <svg class="w-3 h-3 ml-1 {{ $sortDirection === 'asc' ? '' : 'transform rotate-180' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-gray-50 transition duration-150" x-data="{ openMenu: false }">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-900 flex items-center justify-center text-white font-semibold text-sm">
                                            {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $employee->first_name }} {{ $employee->last_name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $employee->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $employee->employee_number }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $employee->phone ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $employee->department->department_name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $employee->branch->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $employee->job_title }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-medium rounded-md
                                    {{ $employee->employment_type === 'full-time' ? 'bg-blue-100 text-blue-800' : ($employee->employment_type === 'part-time' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucwords(str_replace('-', ' ', $employee->employment_type ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $employee->basic_salary ? number_format($employee->basic_salary, 0) . ' TZS' : 'N/A' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $employee->employee_status === 'ACTIVE' ? 'bg-green-100 text-green-800' : ($employee->employee_status === 'SUSPENDED' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($employee->employee_status ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('M d, Y') : 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium">
                                <div class="relative inline-block text-left" @click.away="openMenu = false">
                                    <button @click="openMenu = !openMenu" type="button" class="inline-flex items-center p-2 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 rounded-full hover:bg-gray-100 transition">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                        </svg>
                                    </button>

                                    <div x-show="openMenu"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="origin-top-right absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                        <div class="py-1" role="menu">
                                            <button wire:click="viewEmployee({{ $employee->id }})" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-900 flex items-center transition" role="menuitem">
                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View Details
                                            </button>
                                            <button wire:click="editEmployee({{ $employee->id }})" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-900 flex items-center transition" role="menuitem">
                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit Employee
                                            </button>
                                            <div class="border-t border-gray-100"></div>
                                            <button wire:click="openExitModal({{ $employee->id }})" class="w-full text-left px-4 py-2 text-sm text-orange-600 hover:bg-orange-50 hover:text-orange-900 flex items-center transition" role="menuitem">
                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                </svg>
                                                Employee Exit
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">No employees found</h3>
                                    <p class="text-gray-500">Try adjusting your search or filter criteria</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination with Per Page --}}
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <label class="text-sm text-gray-700 mr-2">Show</label>
                    <select wire:model="perPage" class="px-3 py-1 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="ml-2 text-sm text-gray-700">entries</span>
                </div>

                @if($employees->hasPages())
                    <div>
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Add Employee Modal --}}
    @if($showAddModal)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity" wire:click="closeAddModal">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit.prevent="saveEmployee">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Employee</h3>
                            
                            <div class="grid grid-cols-2 gap-4">
                                {{-- First Name --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                                    <input type="text" wire:model="first_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Last Name --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                                    <input type="text" wire:model="last_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                    <input type="email" wire:model="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Phone --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                                    <input type="text" wire:model="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Employee Number --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee Number *</label>
                                    <input type="text" wire:model="employee_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('employee_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Department --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Department *</label>
                                    <select wire:model="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Job Title --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Title *</label>
                                    <input type="text" wire:model="job_title" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('job_title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Hire Date --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date *</label>
                                    <input type="date" wire:model="hire_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('hire_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Basic Salary --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Basic Salary *</label>
                                    <input type="number" step="0.01" wire:model="basic_salary" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('basic_salary') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Gender --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender *</label>
                                    <select wire:model="gender" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                    @error('gender') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Employment Type --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Employment Type *</label>
                                    <select wire:model="employment_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="full-time">Full Time</option>
                                        <option value="part-time">Part Time</option>
                                        <option value="contract">Contract</option>
                                    </select>
                                    @error('employment_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Date of Birth --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                                    <input type="date" wire:model="date_of_birth" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                {{-- Address --}}
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                    <textarea wire:model="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Save Employee
                            </button>
                            <button type="button" wire:click="closeAddModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Employee Modal --}}
    @if($showEditModal)
        <div class="fixed z-50 inset-0 overflow-y-auto" x-data="{ activeTab: 'basic' }">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity" wire:click="$set('showEditModal', false)">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-[60vw] h-[60vh] overflow-y-auto">
                    <form wire:submit.prevent="updateEmployee">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Employee</h3>

                            {{-- Tab Navigation --}}
                            <div class="border-b border-gray-200 mb-6">
                                <nav class="flex space-x-4">
                                    <button type="button" @click="activeTab = 'basic'"
                                        :class="activeTab === 'basic' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="px-4 py-2 border-b-2 font-medium text-sm transition">
                                        Basic Information
                                    </button>
                                    <button type="button" @click="activeTab = 'address'"
                                        :class="activeTab === 'address' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="px-4 py-2 border-b-2 font-medium text-sm transition">
                                        Address Details
                                    </button>
                                    <button type="button" @click="activeTab = 'emergency'"
                                        :class="activeTab === 'emergency' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="px-4 py-2 border-b-2 font-medium text-sm transition">
                                        Emergency & Next of Kin
                                    </button>
                                    <button type="button" @click="activeTab = 'employment'"
                                        :class="activeTab === 'employment' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="px-4 py-2 border-b-2 font-medium text-sm transition">
                                        Employment Details
                                    </button>
                                    <button type="button" @click="activeTab = 'tax'"
                                        :class="activeTab === 'tax' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="px-4 py-2 border-b-2 font-medium text-sm transition">
                                        Tax & Benefits
                                    </button>
                                    <button type="button" @click="activeTab = 'roles'"
                                        :class="activeTab === 'roles' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="px-4 py-2 border-b-2 font-medium text-sm transition">
                                        Role & Permissions
                                    </button>
                                </nav>
                            </div>

                            {{-- Tab Content --}}
                            {{-- Basic Information Tab --}}
                            <div x-show="activeTab === 'basic'" class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                                    <input type="text" wire:model="first_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                    <input type="text" wire:model="middle_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('middle_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                                    <input type="text" wire:model="last_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee Number *</label>
                                    <input type="text" wire:model="employee_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('employee_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                    <input type="email" wire:model="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                                    <input type="text" wire:model="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                                    <input type="date" wire:model="date_of_birth" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('date_of_birth') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Place of Birth</label>
                                    <input type="text" wire:model="place_of_birth" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('place_of_birth') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender *</label>
                                    <select wire:model="gender" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                    @error('gender') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Marital Status</label>
                                    <select wire:model="marital_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Status</option>
                                        <option value="single">Single</option>
                                        <option value="married">Married</option>
                                        <option value="divorced">Divorced</option>
                                        <option value="widowed">Widowed</option>
                                    </select>
                                    @error('marital_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
                                    <input type="text" wire:model="nationality" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('nationality') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Education Level</label>
                                    <input type="text" wire:model="education_level" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('education_level') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Address Details Tab --}}
                            <div x-show="activeTab === 'address'" class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                    <textarea wire:model="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
                                    @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Street</label>
                                    <input type="text" wire:model="street" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('street') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                    <input type="text" wire:model="city" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                                    <input type="text" wire:model="region" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('region') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                                    <input type="text" wire:model="district" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('district') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ward</label>
                                    <input type="text" wire:model="ward" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('ward') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                                    <input type="text" wire:model="postal_code" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('postal_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Physical Address</label>
                                    <textarea wire:model="physical_address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
                                    @error('physical_address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Emergency & Next of Kin Tab --}}
                            <div x-show="activeTab === 'emergency'" class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <h4 class="font-medium text-gray-900 mb-3">Emergency Contact</h4>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Name</label>
                                    <input type="text" wire:model="emergency_contact_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('emergency_contact_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Relationship</label>
                                    <input type="text" wire:model="emergency_contact_relationship" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('emergency_contact_relationship') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                                    <input type="text" wire:model="emergency_contact_phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('emergency_contact_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                                    <input type="email" wire:model="emergency_contact_email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('emergency_contact_email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-span-2">
                                    <h4 class="font-medium text-gray-900 mb-3 mt-4">Next of Kin</h4>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Next of Kin Name</label>
                                    <input type="text" wire:model="next_of_kin_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('next_of_kin_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Next of Kin Phone</label>
                                    <input type="text" wire:model="next_of_kin_phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('next_of_kin_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Employment Details Tab --}}
                            <div x-show="activeTab === 'employment'" class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Department *</label>
                                    <select wire:model="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                    <select wire:model="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Title *</label>
                                    <input type="text" wire:model="job_title" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('job_title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reporting Manager</label>
                                    <select wire:model="reporting_manager_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Manager</option>
                                        @foreach($allEmployees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_number }})</option>
                                        @endforeach
                                    </select>
                                    @error('reporting_manager_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Employment Type *</label>
                                    <select wire:model="employment_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="full-time">Full Time</option>
                                        <option value="part-time">Part Time</option>
                                        <option value="contract">Contract</option>
                                    </select>
                                    @error('employment_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee Status</label>
                                    <select wire:model="employee_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="ACTIVE">Active</option>
                                        <option value="INACTIVE">Inactive</option>
                                        <option value="SUSPENDED">Suspended</option>
                                    </select>
                                    @error('employee_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date *</label>
                                    <input type="date" wire:model="hire_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('hire_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Frequency</label>
                                    <select wire:model="payment_frequency" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Frequency</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="bi-weekly">Bi-Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="annually">Annually</option>
                                    </select>
                                    @error('payment_frequency') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Basic Salary *</label>
                                    <input type="number" step="0.01" wire:model="basic_salary" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('basic_salary') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                    <textarea wire:model="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
                                    @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Tax & Benefits Tab --}}
                            <div x-show="activeTab === 'tax'" class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <h4 class="font-medium text-gray-900 mb-3">Tax Information</h4>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">TIN Number</label>
                                    <input type="text" wire:model="tin_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('tin_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">NIDA Number</label>
                                    <input type="text" wire:model="nida_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('nida_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">PAYE Rate (%)</label>
                                    <input type="number" step="0.01" wire:model="paye_rate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('paye_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-span-2">
                                    <h4 class="font-medium text-gray-900 mb-3 mt-4">Social Security & Benefits</h4>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">NSSF Number</label>
                                    <input type="text" wire:model="nssf_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('nssf_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">NSSF Rate (%)</label>
                                    <input type="number" step="0.01" wire:model="nssf_rate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('nssf_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">NHIF Number</label>
                                    <input type="text" wire:model="nhif_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('nhif_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">NHIF Rate (%)</label>
                                    <input type="number" step="0.01" wire:model="nhif_rate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @error('nhif_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Role & Permissions Tab --}}
                            <div x-show="activeTab === 'roles'" class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <h4 class="font-medium text-gray-900 mb-3">User Role Assignment</h4>
                                    <p class="text-sm text-gray-600 mb-4">Assign role and sub-roles to this employee's user account</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                    <select wire:model="selectedRole" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" {{ empty($availableRoles) ? 'disabled' : '' }}>
                                        <option value="">Select Role</option>
                                        @if(!empty($availableRoles))
                                            @foreach($availableRoles as $role)
                                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('selectedRole') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    @if(empty($availableRoles))
                                        <p class="text-xs text-gray-500 mt-1">Please select a department in Employment Details tab first</p>
                                    @endif
                                </div>

                                @if(!empty($availableSubRoles))
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Sub-Roles</label>
                                        <div class="border border-gray-300 rounded-lg p-3 max-h-48 overflow-y-auto bg-gray-50">
                                            @foreach($availableSubRoles as $subRole)
                                                <label class="flex items-center mb-2 hover:bg-white p-2 rounded cursor-pointer transition">
                                                    <input type="checkbox" wire:model="selectedSubRoles" value="{{ $subRole->id }}" class="mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                    <span class="text-sm text-gray-700">{{ $subRole->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('selectedSubRoles') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                @endif

                                <div class="col-span-2">
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <div class="text-sm text-blue-800">
                                                <p class="font-medium mb-1">Note:</p>
                                                <ul class="list-disc ml-4 space-y-1">
                                                    <li>Roles are department-specific and grant access permissions</li>
                                                    <li>Sub-roles provide additional granular permissions</li>
                                                    <li>Changing the department will reset role selection</li>
                                                    <li>Permissions are automatically synced when you save changes</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Update Employee
                            </button>
                            <button type="button" wire:click="$set('showEditModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- View Employee Modal --}}
    @if($showViewModal && $viewingEmployee)
        <div class="fixed z-50 inset-0 overflow-y-auto" x-data="{ activeTab: 'basic' }">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity" wire:click="$set('showViewModal', false)">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-[70vw] max-h-[85vh] overflow-y-auto">
                    <div class="bg-blue-900 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="h-16 w-16 rounded-full bg-white bg-opacity-20 flex items-center justify-center text-white font-bold text-2xl mr-4">
                                    {{ strtoupper(substr($viewingEmployee->first_name, 0, 1) . substr($viewingEmployee->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white">{{ $viewingEmployee->first_name }} {{ $viewingEmployee->last_name }}</h3>
                                    <p class="text-blue-100 text-sm">{{ $viewingEmployee->job_title }} • {{ $viewingEmployee->employee_number }}</p>
                                </div>
                            </div>
                            <button wire:click="$set('showViewModal', false)" class="text-white hover:text-gray-200 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="bg-white px-6 pt-4">
                        {{-- Status Badge --}}
                        <div class="mb-4">
                            <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full
                                {{ $viewingEmployee->employee_status === 'ACTIVE' ? 'bg-green-100 text-green-800' : ($viewingEmployee->employee_status === 'SUSPENDED' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($viewingEmployee->employee_status ?? 'N/A') }}
                            </span>
                            <span class="ml-2 px-3 py-1 inline-flex text-sm font-medium rounded-md
                                {{ $viewingEmployee->employment_type === 'full-time' ? 'bg-blue-100 text-blue-800' : ($viewingEmployee->employment_type === 'part-time' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucwords(str_replace('-', ' ', $viewingEmployee->employment_type ?? 'N/A')) }}
                            </span>
                        </div>

                        {{-- Tab Navigation --}}
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="flex space-x-4">
                                <button type="button" @click="activeTab = 'basic'"
                                    :class="activeTab === 'basic' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-4 py-2 border-b-2 font-medium text-sm transition">
                                    Basic Information
                                </button>
                                <button type="button" @click="activeTab = 'contact'"
                                    :class="activeTab === 'contact' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-4 py-2 border-b-2 font-medium text-sm transition">
                                    Contact & Address
                                </button>
                                <button type="button" @click="activeTab = 'employment'"
                                    :class="activeTab === 'employment' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-4 py-2 border-b-2 font-medium text-sm transition">
                                    Employment Details
                                </button>
                                <button type="button" @click="activeTab = 'tax'"
                                    :class="activeTab === 'tax' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-4 py-2 border-b-2 font-medium text-sm transition">
                                    Tax & Benefits
                                </button>
                            </nav>
                        </div>

                        {{-- Tab Content --}}
                        {{-- Basic Information Tab --}}
                        <div x-show="activeTab === 'basic'" class="pb-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Full Name</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">
                                        {{ $viewingEmployee->first_name }} {{ $viewingEmployee->middle_name }} {{ $viewingEmployee->last_name }}
                                    </p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Email Address</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->email }}</p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone Number</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->phone ?? 'N/A' }}</p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Date of Birth</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">
                                        {{ $viewingEmployee->date_of_birth ? \Carbon\Carbon::parse($viewingEmployee->date_of_birth)->format('M d, Y') : 'N/A' }}
                                    </p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Gender</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">{{ ucfirst($viewingEmployee->gender ?? 'N/A') }}</p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Marital Status</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">{{ ucfirst($viewingEmployee->marital_status ?? 'N/A') }}</p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Nationality</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->nationality ?? 'N/A' }}</p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Place of Birth</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->place_of_birth ?? 'N/A' }}</p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4 col-span-2">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Education Level</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->education_level ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Contact & Address Tab --}}
                        <div x-show="activeTab === 'contact'" class="pb-6">
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Address Details
                                </h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="bg-gray-50 rounded-lg p-4 col-span-2">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Address</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->address ?? 'N/A' }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Street</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->street ?? 'N/A' }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">City</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->city ?? 'N/A' }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Region</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->region ?? 'N/A' }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">District</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->district ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    Emergency Contact
                                </h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact Name</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->emergency_contact_name ?? 'N/A' }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Relationship</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->emergency_contact_relationship ?? 'N/A' }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->emergency_contact_phone ?? 'N/A' }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->emergency_contact_email ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Next of Kin
                                </h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->next_of_kin_name ?? 'N/A' }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->next_of_kin_phone ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Employment Details Tab --}}
                        <div x-show="activeTab === 'employment'" class="pb-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="bg-white rounded-lg p-4 border-l-4 border-blue-900 shadow-sm">
                                    <label class="text-xs font-semibold text-blue-900 uppercase tracking-wider">Department</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->department->department_name ?? 'N/A' }}</p>
                                </div>

                                <div class="bg-white rounded-lg p-4 border-l-4 border-blue-900 shadow-sm">
                                    <label class="text-xs font-semibold text-blue-900 uppercase tracking-wider">Branch</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->branch->name ?? 'N/A' }}</p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Job Title</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->job_title }}</p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Reporting Manager</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">
                                        {{ $viewingEmployee->reportingManager ? $viewingEmployee->reportingManager->first_name . ' ' . $viewingEmployee->reportingManager->last_name : 'N/A' }}
                                    </p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Hire Date</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">
                                        {{ $viewingEmployee->hire_date ? \Carbon\Carbon::parse($viewingEmployee->hire_date)->format('M d, Y') : 'N/A' }}
                                    </p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Employment Type</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">{{ ucwords(str_replace('-', ' ', $viewingEmployee->employment_type ?? 'N/A')) }}</p>
                                </div>

                                <div class="bg-white rounded-lg p-4 border-l-4 border-blue-900 shadow-sm">
                                    <label class="text-xs font-semibold text-blue-900 uppercase tracking-wider">Basic Salary</label>
                                    <p class="text-lg font-bold text-blue-900 mt-1">
                                        {{ $viewingEmployee->basic_salary ? number_format($viewingEmployee->basic_salary, 0) . ' TZS' : 'N/A' }}
                                    </p>
                                </div>

                                <div class="bg-white rounded-lg p-4 border-l-4 border-blue-900 shadow-sm">
                                    <label class="text-xs font-semibold text-blue-900 uppercase tracking-wider">Gross Salary</label>
                                    <p class="text-lg font-bold text-blue-900 mt-1">
                                        {{ $viewingEmployee->gross_salary ? number_format($viewingEmployee->gross_salary, 0) . ' TZS' : 'N/A' }}
                                    </p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Payment Frequency</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">{{ ucfirst($viewingEmployee->payment_frequency ?? 'N/A') }}</p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Employee Status</label>
                                    <p class="text-base font-medium text-gray-900 mt-1">{{ ucfirst($viewingEmployee->employee_status ?? 'N/A') }}</p>
                                </div>

                                @if($viewingEmployee->notes)
                                    <div class="bg-gray-50 rounded-lg p-4 col-span-2">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Tax & Benefits Tab --}}
                        <div x-show="activeTab === 'tax'" class="pb-6">
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Tax Information
                                </h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">TIN Number</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->tin_number ?? 'N/A' }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">NIDA Number</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->nida_number ?? 'N/A' }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">PAYE Rate</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->paye_rate ? $viewingEmployee->paye_rate . '%' : 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    Social Security & Benefits
                                </h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">NSSF Number</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->nssf_number ?? 'N/A' }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">NSSF Rate</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->nssf_rate ? $viewingEmployee->nssf_rate . '%' : 'N/A' }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">NHIF Number</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->nhif_number ?? 'N/A' }}</p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">NHIF Rate</label>
                                        <p class="text-base font-medium text-gray-900 mt-1">{{ $viewingEmployee->nhif_rate ? $viewingEmployee->nhif_rate . '%' : 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t">
                        <button wire:click="editEmployee({{ $viewingEmployee->id }})" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-900 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Employee
                        </button>
                        <button wire:click="$set('showViewModal', false)" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Employee Exit Modal --}}
    @if($showExitModal && $exitingEmployee)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity" wire:click="$set('showExitModal', false)">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit.prevent="processEmployeeExit">
                        <div class="bg-blue-900 px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-xl font-semibold text-white">Employee Exit Process</h3>
                                    <p class="text-sm text-blue-100 mt-1">{{ $exitingEmployee->first_name }} {{ $exitingEmployee->last_name }} ({{ $exitingEmployee->employee_number }})</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white px-6 py-5">
                            <div class="grid grid-cols-2 gap-4">
                                {{-- Exit Type --}}
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Exit Type *</label>
                                    <select wire:model="exit_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-900 focus:border-blue-900">
                                        <option value="">Select Exit Type</option>
                                        <option value="resignation">Resignation</option>
                                        <option value="termination">Termination</option>
                                        <option value="retirement">Retirement</option>
                                        <option value="contract_end">Contract End</option>
                                        <option value="voluntary">Voluntary Separation</option>
                                        <option value="involuntary">Involuntary Separation</option>
                                    </select>
                                    @error('exit_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Exit Date --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Exit Date *</label>
                                    <input type="date" wire:model="exit_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-900 focus:border-blue-900">
                                    @error('exit_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Notice Period --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Notice Period (Days)</label>
                                    <input type="number" wire:model="notice_period_days" min="0" placeholder="e.g., 30" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-900 focus:border-blue-900">
                                    @error('notice_period_days') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Final Settlement Amount --}}
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Final Settlement Amount (TZS)</label>
                                    <input type="number" wire:model="final_settlement_amount" step="0.01" min="0" placeholder="Enter final settlement amount" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-900 focus:border-blue-900">
                                    @error('final_settlement_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    <p class="text-xs text-gray-500 mt-1">Include any pending salary, unused leave, gratuity, etc.</p>
                                </div>

                                {{-- Reason for Exit --}}
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Exit *</label>
                                    <textarea wire:model="exit_reason" rows="3" placeholder="Provide detailed reason for exit..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-900 focus:border-blue-900"></textarea>
                                    @error('exit_reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    <p class="text-xs text-gray-500 mt-1">Minimum 10 characters required</p>
                                </div>

                                {{-- Additional Comments --}}
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Additional Comments</label>
                                    <textarea wire:model="exit_comments" rows="2" placeholder="Any additional notes or comments..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-900 focus:border-blue-900"></textarea>
                                    @error('exit_comments') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Warning Notice --}}
                            <div class="mt-4 bg-orange-50 border-l-4 border-orange-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-orange-700">
                                            <strong>Important:</strong> This action will:
                                        </p>
                                        <ul class="list-disc list-inside text-sm text-orange-700 mt-1">
                                            <li>Change employee status to INACTIVE</li>
                                            <li>Deactivate associated user account</li>
                                            <li>Record exit details in employee notes</li>
                                            <li>Stop all future payroll processing for this employee</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                            <button type="button" wire:click="$set('showExitModal', false)" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-900 transition">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition">
                                Process Exit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation --}}
    <script nonce="{{ csp_nonce() }}">
        window.addEventListener('confirmDelete', event => {
            if (confirm('Are you sure you want to delete this employee?')) {
                @this.deleteEmployee(event.detail);
            }
        });
    </script>
</div>