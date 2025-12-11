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
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Employee Self-Services</h1>
                        <p class="text-gray-600 mt-1">Request HR and admin services from any department</p>
                    </div>
                </div>
                <!-- Quick Stats (optional) -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Leave Days</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $remainingLeaveDays }}</p>
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
                                <p class="text-lg font-semibold text-gray-900">{{ $pendingRequests }}</p>
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
                        <input type="text" placeholder="Search services..." class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white" aria-label="Search services" />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Self-Service Menu</h3>
                    <nav class="space-y-2">
                        @if($permissions['canView'] ?? false)
                        <button wire:click="$set('selectedMenu', 'dashboard')" class="relative w-full group transition-all duration-200">
                            <div class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $selectedMenu === 'dashboard' ? 'bg-blue-900 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                <span>Dashboard</span>
                            </div>
                        </button>
                        @endif
                        
                        @if($permissions['canLeave'] ?? false)
                        <button wire:click="$set('selectedMenu', 'leave')" class="relative w-full group transition-all duration-200">
                            <div class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $selectedMenu === 'leave' ? 'bg-blue-900 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span>Leave Request</span>
                            </div>
                        </button>
                        @endif
                        
                        @if($permissions['canCreate'] ?? false)
                        <button wire:click="$set('selectedMenu', 'materials')" class="relative w-full group transition-all duration-200">
                            <div class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $selectedMenu === 'materials' ? 'bg-blue-900 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <span>Working Materials</span>
                            </div>
                        </button>
                        @endif
                        
                        @if($permissions['canCreate'] ?? false)
                        <button wire:click="$set('selectedMenu', 'resignation')" class="relative w-full group transition-all duration-200">
                            <div class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $selectedMenu === 'resignation' ? 'bg-blue-900 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" /></svg>
                                <span>Resignation</span>
                            </div>
                        </button>
                        @endif
                        
                        @if($permissions['canCreate'] ?? false)
                        <button wire:click="$set('selectedMenu', 'travel')" class="relative w-full group transition-all duration-200">
                            <div class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $selectedMenu === 'travel' ? 'bg-blue-900 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a5 5 0 00-10 0v2a2 2 0 00-2 2v7a2 2 0 002 2h10a2 2 0 002-2v-7a2 2 0 00-2-2z" /></svg>
                                <span>Travel/Advance</span>
                            </div>
                        </button>
                        @endif
                        
                        @if($permissions['canCreate'] ?? false)
                        <button wire:click="$set('selectedMenu', 'training')" class="relative w-full group transition-all duration-200">
                            <div class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $selectedMenu === 'training' ? 'bg-blue-900 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0 0H3m9 0h9" /></svg>
                                <span>Training/Workshop</span>
                            </div>
                        </button>
                        @endif
                        
                        @if($permissions['canCreate'] ?? false)
                        <button wire:click="$set('selectedMenu', 'overtime')" class="relative w-full group transition-all duration-200">
                            <div class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $selectedMenu === 'overtime' ? 'bg-blue-900 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>Overtime Request</span>
                            </div>
                        </button>
                        @endif
                        
                        @if($permissions['canView'] ?? false)
                        <button wire:click="$set('selectedMenu', 'payslip')" class="relative w-full group transition-all duration-200">
                            <div class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $selectedMenu === 'payslip' ? 'bg-blue-900 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <span>Payslip/HR Docs</span>
                            </div>
                        </button>
                        @endif
                        
                        @if($permissions['canCreate'] ?? false)
                        <button wire:click="$set('selectedMenu', 'general')" class="relative w-full group transition-all duration-200">
                            <div class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $selectedMenu === 'general' ? 'bg-blue-900 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                                <span>General Request</span>
                            </div>
                        </button>
                        @endif
                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
<div>
                            <h2 class="text-xl font-semibold text-gray-900">
                                @switch($selectedMenu)
                                    @case('dashboard') Dashboard @break
                                    @case('leave') Leave Request @break
                                    @case('materials') Working Materials @break
                                    @case('resignation') Resignation @break
                                    @case('travel') Travel/Advance @break
                                    @case('training') Training/Workshop @break
                                    @case('overtime') Overtime Request @break
                                    @case('payslip') Payslip/HR Docs @break
                                    @case('general') General Request @break
                                    @default Dashboard
                                @endswitch
                            </h2>
                            <p class="text-gray-600 mt-1">
                                @switch($selectedMenu)
                                    @case('dashboard') Overview of your requests and status @break
                                    @case('leave') Request for annual, sick, or emergency leave @break
                                    @case('materials') Request for work materials or equipment @break
                                    @case('resignation') Submit resignation or exit request @break
                                    @case('travel') Request travel or advance funds @break
                                    @case('training') Request for training or workshop attendance @break
                                    @case('overtime') Request approval for overtime/extra hours @break
                                    @case('payslip') Request payslip or HR documents @break
                                    @case('general') Send a general request to any department @break
                                    @default Overview of your requests and status
                                @endswitch
                            </p>
                        </div>
                    </div>
                    <div class="p-8 min-h-[400px]">
                        @switch($selectedMenu)
                            @case('dashboard')
                                @if($permissions['canView'] ?? false)
                                {{-- Dashboard summary: show employee information and stats --}}
                                @if($employee)
                                <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                                    <h3 class="text-lg font-semibold text-blue-900 mb-2">Employee Information</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600">Name:</p>
                                            <p class="font-medium">{{ $employee->first_name }} {{ $employee->last_name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Employee Number:</p>
                                            <p class="font-medium">{{ $employee->employee_number }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Department:</p>
                                            <p class="font-medium">{{ $employee->department->department_name ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Status:</p>
                                            <p class="font-medium">{{ ucfirst($employee->employee_status ?? 'active') }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                        <h3 class="text-lg font-semibold text-blue-900 mb-2">Leave Balance</h3>
                                        <div class="text-3xl font-bold text-blue-900">{{ $remainingLeaveDays }} days</div>
                                    </div>
                                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
                                        <h3 class="text-lg font-semibold text-yellow-900 mb-2">Pending Leaves</h3>
                                        <div class="text-3xl font-bold text-yellow-900">{{ $pendingLeaves }}</div>
                                    </div>
                                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                                        <h3 class="text-lg font-semibold text-green-900 mb-2">Approved Leaves</h3>
                                        <div class="text-3xl font-bold text-green-900">{{ $approvedLeaves }}</div>
                                    </div>
                                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                                        <h3 class="text-lg font-semibold text-purple-900 mb-2">Total Requests</h3>
                                        <div class="text-3xl font-bold text-purple-900">{{ $pendingRequests }}</div>
                                    </div>
                                </div>
                                {{-- Recent Leave Requests --}}
                                <div class="bg-white rounded-xl p-6 border border-gray-200 mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Leave Requests</h3>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @forelse($leaves as $leave)
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $leave->created_at->format('Y-m-d') }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($leave->leave_type) }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                                                @if($leave->status == 'approved') bg-green-100 text-green-800
                                                                @elseif($leave->status == 'pending') bg-yellow-100 text-yellow-800
                                                                @else bg-red-100 text-red-800 @endif">
                                                                {{ ucfirst($leave->status) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-gray-500 py-8">No leave requests found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                {{-- Recent Payrolls --}}
                                @if($lastPayroll)
                                <div class="bg-white rounded-xl p-6 border border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Last Payroll</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600">Period:</p>
                                            <p class="font-medium">{{ $lastPayroll->month }}/{{ $lastPayroll->year }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Net Salary:</p>
                                            <p class="font-medium">TZS {{ number_format($lastPayroll->net_salary, 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Status:</p>
                                            <p class="font-medium">{{ ucfirst($lastPayroll->status) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Payment Date:</p>
                                            <p class="font-medium">{{ $lastPayroll->payment_date ? $lastPayroll->payment_date->format('Y-m-d') : 'Pending' }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @else
                                <div class="text-center py-12">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Access</h3>
                                    <p class="mt-1 text-sm text-gray-500">You don't have permission to view the dashboard.</p>
                                </div>
                                @endif
                                @break
                            @case('leave')
                                @if($permissions['canLeave'] ?? false)
                                {{-- Leave Request Form --}}
                                <div class="max-w-2xl">
                                    @if($showLeaveModal)
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                        <p class="text-green-800">{{ session('success') }}</p>
                                    </div>
                                    @endif
                                    
                                    <form wire:submit.prevent="submitLeaveRequest">
                                        <div class="space-y-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Leave Type</label>
                                                <select wire:model="leaveType" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                    <option value="">Select leave type</option>
                                                    <option value="annual">Annual Leave</option>
                                                    <option value="sick">Sick Leave</option>
                                                    <option value="maternity">Maternity Leave</option>
                                                    <option value="paternity">Paternity Leave</option>
                                                    <option value="compassionate">Compassionate Leave</option>
                                                    <option value="study">Study Leave</option>
                                                    <option value="unpaid">Unpaid Leave</option>
                                                </select>
                                                @error('leaveType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                                    <input type="date" wire:model="leaveStartDate" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                    @error('leaveStartDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                                    <input type="date" wire:model="leaveEndDate" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                    @error('leaveEndDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Leave</label>
                                                <textarea wire:model="leaveReason" rows="4" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Please provide details..."></textarea>
                                                @error('leaveReason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="bg-blue-50 rounded-lg p-4">
                                                <p class="text-sm text-blue-800">Your current leave balance: <strong>{{ $remainingLeaveDays }} days</strong></p>
                                            </div>
                                            
                                            <div class="flex justify-end space-x-3">
                                                <button type="button" wire:click="resetLeaveForm" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                                    Clear
                                                </button>
                                                <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                                                    Submit Request
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    {{-- My Leave History --}}
                                    <div class="mt-8">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">My Leave History</h3>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @forelse($leaves as $leave)
                                                        <tr>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $leave->start_date }}</td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($leave->leave_type) }}</td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}
                                                        </td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                                                    @if($leave->status == 'approved') bg-green-100 text-green-800
                                                                    @elseif($leave->status == 'pending') bg-yellow-100 text-yellow-800
                                                                    @else bg-red-100 text-red-800 @endif">
                                                                    {{ ucfirst($leave->status) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center text-gray-500 py-8">No leave history found</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="text-center py-12">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Access</h3>
                                    <p class="mt-1 text-sm text-gray-500">You don't have permission to access leave requests.</p>
                                </div>
                                @endif
                                @break
                            @case('materials')
                                @if($permissions['canCreate'] ?? false)
                                {{-- Working Materials Request Form --}}
                                <div class="max-w-2xl">
                                    <form wire:submit.prevent="submitMaterialsRequest">
                                        <div class="space-y-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Items Needed</label>
                                                <textarea wire:model="materialItems" rows="3" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="List the materials or equipment you need..."></textarea>
                                                @error('materialItems') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Purpose/Justification</label>
                                                <textarea wire:model="materialPurpose" rows="4" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Explain why you need these materials..."></textarea>
                                                @error('materialPurpose') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="flex justify-end space-x-3">
                                                <button type="button" wire:click="resetRequestForm" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                                    Clear
                                                </button>
                                                <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                                                    Submit Request
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                @else
                                <div class="text-center py-12">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Access</h3>
                                    <p class="mt-1 text-sm text-gray-500">You don't have permission to request working materials.</p>
                                </div>
                                @endif
                                @break
                            @case('resignation')
                                @if($permissions['canCreate'] ?? false)
                                {{-- Resignation Request Form --}}
                                <div class="max-w-2xl">
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                                        <p class="text-red-800 text-sm">⚠️ This is a formal resignation request. Please ensure all information is accurate.</p>
                                    </div>
                                    
                                    <form wire:submit.prevent="submitResignationRequest">
                                        <div class="space-y-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Proposed Last Working Day</label>
                                                <input type="date" wire:model="resignationDate" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                @error('resignationDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Resignation</label>
                                                <textarea wire:model="resignationReason" rows="6" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Please provide your reason for resignation..."></textarea>
                                                @error('resignationReason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="flex justify-end space-x-3">
                                                <button type="button" wire:click="resetRequestForm" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                                    Submit Resignation
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                @else
                                <div class="text-center py-12">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Access</h3>
                                    <p class="mt-1 text-sm text-gray-500">You don't have permission to submit resignation requests.</p>
                                </div>
                                @endif
                                @break
                            @case('travel')
                                @if($permissions['canCreate'] ?? false)
                                {{-- Travel/Advance Request Form --}}
                                <div class="max-w-2xl">
                                    <div class="mb-6">
                                        <div class="flex space-x-4">
                                            <button wire:click="$set('requestType', 'travel')" class="px-4 py-2 rounded-lg {{ $requestType == 'travel' ? 'bg-blue-900 text-white' : 'bg-gray-200 text-gray-700' }}">
                                                Travel Request
                                            </button>
                                            <button wire:click="$set('requestType', 'advance')" class="px-4 py-2 rounded-lg {{ $requestType == 'advance' ? 'bg-blue-900 text-white' : 'bg-gray-200 text-gray-700' }}">
                                                Salary Advance
                                            </button>
                                        </div>
                                    </div>
                                    
                                    @if($requestType == 'travel')
                                    <form wire:submit.prevent="submitTravelRequest">
                                        <div class="space-y-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Destination</label>
                                                <input type="text" wire:model="travelDestination" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="City, Country">
                                                @error('travelDestination') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                                    <input type="date" wire:model="travelStartDate" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                    @error('travelStartDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                                    <input type="date" wire:model="travelEndDate" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                    @error('travelEndDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Purpose of Travel</label>
                                                <textarea wire:model="travelPurpose" rows="4" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Business meeting, conference, training..."></textarea>
                                                @error('travelPurpose') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="flex justify-end space-x-3">
                                                <button type="button" wire:click="resetRequestForm" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                                    Clear
                                                </button>
                                                <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                                                    Submit Request
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    @elseif($requestType == 'advance')
                                    <form wire:submit.prevent="submitAdvanceRequest">
                                        <div class="space-y-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Amount Requested (TZS)</label>
                                                <input type="number" wire:model="advanceAmount" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Enter amount">
                                                @error('advanceAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Advance</label>
                                                <textarea wire:model="advanceReason" rows="4" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Explain why you need the advance..."></textarea>
                                                @error('advanceReason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="bg-yellow-50 rounded-lg p-4">
                                                <p class="text-sm text-yellow-800">Note: Salary advances are subject to company policy and approval. Repayment will be deducted from your next salary.</p>
                                            </div>
                                            
                                            <div class="flex justify-end space-x-3">
                                                <button type="button" wire:click="resetRequestForm" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                                    Clear
                                                </button>
                                                <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                                                    Submit Request
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    @else
                                    <div class="text-center py-8">
                                        <p class="text-gray-500">Select either Travel Request or Salary Advance</p>
                                    </div>
                                    @endif
                                </div>
                                @else
                                <div class="text-center py-12">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Access</h3>
                                    <p class="mt-1 text-sm text-gray-500">You don't have permission to submit travel or advance requests.</p>
                                </div>
                                @endif
                                @break
                            @case('training')
                                @if($permissions['canCreate'] ?? false)
                                {{-- Training/Workshop Request Form --}}
                                <div class="max-w-2xl">
                                    <form wire:submit.prevent="submitTrainingRequest">
                                        <div class="space-y-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Training/Workshop Title</label>
                                                <input type="text" wire:model="trainingTitle" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Name of the training or workshop">
                                                @error('trainingTitle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                                    <input type="date" wire:model="trainingStartDate" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                    @error('trainingStartDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                                    <input type="date" wire:model="trainingEndDate" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                    @error('trainingEndDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                                <input type="text" wire:model="trainingLocation" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Venue or online">
                                                @error('trainingLocation') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="flex justify-end space-x-3">
                                                <button type="button" wire:click="resetRequestForm" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                                    Clear
                                                </button>
                                                <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                                                    Submit Request
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                @else
                                <div class="text-center py-12">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Access</h3>
                                    <p class="mt-1 text-sm text-gray-500">You don't have permission to request training or workshops.</p>
                                </div>
                                @endif
                                @break
                            @case('overtime')
                                @if($permissions['canCreate'] ?? false)
                                {{-- Overtime Request Form --}}
                                <div class="max-w-2xl">
                                    <form wire:submit.prevent="submitOvertimeRequest">
                                        <div class="space-y-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Overtime</label>
                                                <input type="date" wire:model="overtimeDate" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                @error('overtimeDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Number of Hours</label>
                                                <input type="number" wire:model="overtimeHours" step="0.5" min="1" max="12" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Enter overtime hours">
                                                @error('overtimeHours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Reason/Justification</label>
                                                <textarea wire:model="overtimeReason" rows="4" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Explain the work done during overtime..."></textarea>
                                                @error('overtimeReason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="bg-blue-50 rounded-lg p-4">
                                                <p class="text-sm text-blue-800">Overtime rates are calculated based on company policy.</p>
                                            </div>
                                            
                                            <div class="flex justify-end space-x-3">
                                                <button type="button" wire:click="resetRequestForm" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                                    Clear
                                                </button>
                                                <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                                                    Submit Request
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                @else
                                <div class="text-center py-12">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Access</h3>
                                    <p class="mt-1 text-sm text-gray-500">You don't have permission to submit overtime requests.</p>
                                </div>
                                @endif
                                @break
                            @case('payslip')
                                @if($permissions['canView'] ?? false)
                                {{-- Payslip/HR Docs --}}
                                <div class="max-w-4xl">
                                    <div class="mb-6">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Documents</h3>
                                        <form wire:submit.prevent="submitPayslipRequest" class="mb-8">
                                            <div class="flex space-x-4">
                                                <select wire:model="documentType" class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                    <option value="">Select document type</option>
                                                    <option value="payslip">Payslip</option>
                                                    <option value="employment_letter">Employment Letter</option>
                                                    <option value="salary_certificate">Salary Certificate</option>
                                                    <option value="experience_letter">Experience Letter</option>
                                                </select>
                                                <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                                                    Request Document
                                                </button>
                                            </div>
                                            @error('documentType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </form>
                                    </div>
                                    
                                    <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-6">My Payroll History</h3>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Basic Salary</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Salary</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Salary</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @forelse($payrolls as $payroll)
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payroll->month }}/{{ $payroll->year }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($payroll->basic_salary, 2) }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($payroll->gross_salary, 2) }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($payroll->total_deductions, 2) }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">TZS {{ number_format($payroll->net_salary, 2) }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                                                @if($payroll->status == 'paid') bg-green-100 text-green-800
                                                                @elseif($payroll->status == 'approved') bg-blue-100 text-blue-800
                                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                                {{ ucfirst($payroll->status) }}
                                                            </span>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <button wire:click="viewPayslip({{ $payroll->id }})" class="text-blue-600 hover:underline text-sm">View Payslip</button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-gray-500 py-8">No payroll records found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @else
                                <div class="text-center py-12">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Access</h3>
                                    <p class="mt-1 text-sm text-gray-500">You don't have permission to view payslips or request HR documents.</p>
                                </div>
                                @endif
                                @break
                            @case('general')
                                @if($permissions['canCreate'] ?? false)
                                {{-- General Request Form --}}
                                <div class="max-w-2xl">
                                    <form wire:submit.prevent="submitGeneralRequest">
                                        <div class="space-y-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Department (Optional)</label>
                                                <select wire:model="requestDepartment" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                    <option value="">Select department</option>
                                                    <option value="hr">Human Resources</option>
                                                    <option value="finance">Finance</option>
                                                    <option value="it">IT Support</option>
                                                    <option value="admin">Administration</option>
                                                    <option value="operations">Operations</option>
                                                </select>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                                                <input type="text" wire:model="requestSubject" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Brief description of your request">
                                                @error('requestSubject') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Details</label>
                                                <textarea wire:model="requestDetails" rows="6" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Provide full details of your request..."></textarea>
                                                @error('requestDetails') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="flex justify-end space-x-3">
                                                <button type="button" wire:click="resetRequestForm" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                                    Clear
                                                </button>
                                                <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                                                    Submit Request
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    {{-- Recent Requests --}}
                                    <div class="mt-8">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">My Recent Requests</h3>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @forelse($requests as $request)
                                                        <tr>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->created_at->format('Y-m-d') }}</td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->type_label }}</td>
                                                            <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($request->subject, 30) }}</td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $request->status_color }}-100 text-{{ $request->status_color }}-800">
                                                                    {{ ucfirst($request->status) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center text-gray-500 py-8">No requests found</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="text-center py-12">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Access</h3>
                                    <p class="mt-1 text-sm text-gray-500">You don't have permission to submit general requests.</p>
                                </div>
                                @endif
                                @break
                            @default
                                @if(!($permissions['canView'] ?? false) && !($permissions['canLeave'] ?? false) && !($permissions['canCreate'] ?? false))
                                <div class="text-center py-12">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Access</h3>
                                    <p class="mt-1 text-sm text-gray-500">You don't have permission to access any self-service features.</p>
                                </div>
                                @else
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome to Self-Services</h3>
                                    <p class="text-gray-600">Select a service from the sidebar to get started</p>
                                </div>
                                @endif
                        @endswitch
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
