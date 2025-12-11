{{-- Enhanced Payroll Management with Budget & Approval Workflow --}}
<div>
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Payroll Management</h1>
        <p class="text-gray-600 mt-1">Manage employee payroll with budget control and approval workflow</p>
    </div>

    {{-- Success/Error Messages --}}
    @if(session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Period Selection and Actions Row --}}
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            {{-- Period Selectors --}}
            <div class="flex items-center space-x-3">
                <label class="text-sm font-medium text-gray-700">Period:</label>
                <select wire:model="month" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                    @endfor
                </select>

                <select wire:model="year" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
                    @for($i = date('Y') - 2; $i <= date('Y') + 1; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>

                <input type="text" wire:model.debounce.300ms="search"
                    placeholder="Search employee..."
                    class="px-4 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center space-x-2">
                <button wire:click="generatePayroll"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-800 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Generate Payroll
                </button>
            </div>
        </div>
    </div>

    {{-- Budget Status & Approval Workflow --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Budget Status Card --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-red-600 px-6 py-4">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Budget Status
                </h3>
            </div>
            <div class="p-6">
                @if($budgetCheckResult)
                    @if($budgetCheckResult['success'])
                        <div class="flex items-start mb-4">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-lg font-semibold text-green-700">Budget Available ✓</h4>
                                <p class="text-sm text-gray-600">{{ $budgetCheckResult['message'] }}</p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-start mb-4">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-lg font-semibold text-red-700">Insufficient Budget ✗</h4>
                                <p class="text-sm text-gray-600">{{ $budgetCheckResult['message'] }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Total Required:</span>
                            <span class="text-sm font-bold text-gray-900">TZS {{ number_format($budgetCheckResult['total_required'] ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Available Budget:</span>
                            <span class="text-sm font-bold text-gray-900">TZS {{ number_format($budgetCheckResult['available_budget'] ?? 0, 2) }}</span>
                        </div>
                        @if(isset($budgetCheckResult['surplus']))
                            <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                <span class="text-sm font-medium text-green-700">Surplus:</span>
                                <span class="text-sm font-bold text-green-700">TZS {{ number_format($budgetCheckResult['surplus'], 2) }}</span>
                            </div>
                        @endif
                        @if(isset($budgetCheckResult['shortfall']))
                            <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                                <span class="text-sm font-medium text-red-700">Shortfall:</span>
                                <span class="text-sm font-bold text-red-700">TZS {{ number_format($budgetCheckResult['shortfall'], 2) }}</span>
                            </div>
                        @endif
                        @if(isset($budgetCheckResult['utilization_percentage']))
                            <div class="mt-4">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700">Budget Utilization</span>
                                    <span class="font-semibold">{{ number_format($budgetCheckResult['utilization_percentage'], 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="h-3 rounded-full {{ $budgetCheckResult['utilization_percentage'] > 100 ? 'bg-red-500' : ($budgetCheckResult['utilization_percentage'] > 90 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                         style="width: {{ min($budgetCheckResult['utilization_percentage'], 100) }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <button wire:click="viewBudgetDetails" class="mt-4 w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-800 transition">
                        View Detailed Breakdown
                    </button>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p>No payroll data for this period</p>
                        <p class="text-sm mt-1">Generate payroll to check budget status</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Approval Status Card --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-red-600 px-6 py-4">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Approval Status
                </h3>
            </div>
            <div class="p-6">
                @if($approvalStatus && isset($approvalStatus['status']))
                    @if($approvalStatus['status'] === 'NOT_SUBMITTED')
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-600 mb-4">Payroll not submitted for approval</p>
                            <button wire:click="submitForApproval"
                                class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-800 transition flex items-center mx-auto"
                                @if(!$budgetCheckResult || !$budgetCheckResult['success']) disabled @endif>
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Submit for Approval
                            </button>
                        </div>
                    @elseif($approvalStatus['status'] === 'PENDING')
                        <div class="mb-4">
                            <div class="flex items-center justify-center mb-4">
                                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <h4 class="text-lg font-semibold text-yellow-700 text-center mb-2">Pending Approval</h4>
                            <p class="text-sm text-gray-600 text-center mb-4">Waiting for Finance/Management approval</p>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm p-2 bg-gray-50 rounded">
                                <span class="text-gray-600">Submitted By:</span>
                                <span class="font-medium">User #{{ $approvalStatus['submitted_by'] }}</span>
                            </div>
                            <div class="flex justify-between text-sm p-2 bg-gray-50 rounded">
                                <span class="text-gray-600">Submitted At:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($approvalStatus['submitted_at'])->format('M d, Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between text-sm p-2 bg-gray-50 rounded">
                                <span class="text-gray-600">Total Amount:</span>
                                <span class="font-medium">TZS {{ number_format($approvalStatus['total_amount'] ?? 0, 2) }}</span>
                            </div>
                        </div>

                        {{-- Approve/Reject Buttons --}}
                        <div class="space-y-2">
                            <button wire:click="approvePayrollBatch"
                                class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Approve Payroll
                            </button>

                            <div class="relative">
                                <input type="text" wire:model="rejectionReason"
                                    placeholder="Enter rejection reason..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                                <button wire:click="rejectPayrollBatch"
                                    class="mt-2 w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Reject Payroll
                                </button>
                            </div>
                        </div>
                    @elseif($approvalStatus['status'] === 'APPROVED')
                        <div class="mb-4">
                            <div class="flex items-center justify-center mb-4">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <h4 class="text-lg font-semibold text-green-700 text-center mb-2">Approved ✓</h4>
                            <p class="text-sm text-gray-600 text-center mb-4">Ready for payment processing</p>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm p-2 bg-gray-50 rounded">
                                <span class="text-gray-600">Approved By:</span>
                                <span class="font-medium">User #{{ $approvalStatus['approved_by'] }}</span>
                            </div>
                            <div class="flex justify-between text-sm p-2 bg-gray-50 rounded">
                                <span class="text-gray-600">Approved At:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($approvalStatus['approved_at'])->format('M d, Y H:i') }}</span>
                            </div>
                        </div>

                        {{-- Process Payment Button --}}
                        @if($allPayrollsPaid)
                            <div class="w-full px-4 py-3 bg-green-100 text-green-700 rounded-lg border border-green-300 flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                All Payments Completed
                            </div>
                            <p class="text-sm text-gray-600 text-center mt-2">Generate new payroll to enable payment processing</p>
                        @else
                            <button wire:click="processMonthlyPayments"
                                class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-800 transition flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Process Monthly Payments
                            </button>
                        @endif
                    @elseif($approvalStatus['status'] === 'REJECTED')
                        <div class="mb-4">
                            <div class="flex items-center justify-center mb-4">
                                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <h4 class="text-lg font-semibold text-red-700 text-center mb-2">Rejected</h4>
                            <p class="text-sm text-gray-600 text-center mb-4">{{ $approvalStatus['comments'] }}</p>
                        </div>

                        <button wire:click="submitForApproval"
                            class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-800 transition">
                            Resubmit for Approval
                        </button>
                    @endif
                @else
                    <div class="text-center py-8 text-gray-500">
                        <p>No approval information available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Payroll Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-red-600 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Total Employees</p>
                    <p class="text-3xl font-bold mt-2">{{ $payrolls->total() }}</p>
                </div>
                <svg class="w-12 h-12 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-red-600 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Total Gross</p>
                    <p class="text-2xl font-bold mt-2">TZS {{ number_format($payrolls->sum('gross_salary'), 0) }}</p>
                </div>
                <svg class="w-12 h-12 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-red-600 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Total Deductions</p>
                    <p class="text-2xl font-bold mt-2">TZS {{ number_format($payrolls->sum('total_deductions'), 0) }}</p>
                </div>
                <svg class="w-12 h-12 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-red-600 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Total Net Pay</p>
                    <p class="text-2xl font-bold mt-2">TZS {{ number_format($payrolls->sum('net_salary'), 0) }}</p>
                </div>
                <svg class="w-12 h-12 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Payroll Table --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Payroll Records</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Basic Salary</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allowances</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Salary</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($payrolls as $payroll)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-red-600 rounded-full flex items-center justify-center text-white font-semibold">
                                        {{ substr($payroll->employee->first_name ?? 'N', 0, 1) }}{{ substr($payroll->employee->last_name ?? 'A', 0, 1) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $payroll->employee->first_name ?? '' }} {{ $payroll->employee->last_name ?? '' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $payroll->employee->employee_number ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                TZS {{ number_format($payroll->basic_salary, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                TZS {{ number_format(($payroll->house_allowance ?? 0) + ($payroll->transport_allowance ?? 0), 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                TZS {{ number_format($payroll->gross_salary, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                TZS {{ number_format($payroll->total_deductions, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                                TZS {{ number_format($payroll->net_salary, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($payroll->status === 'paid')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Paid
                                    </span>
                                @elseif($payroll->status === 'approved')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Approved
                                    </span>
                                @elseif($payroll->status === 'submitted_for_approval')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        Submitted
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button wire:click="viewPayslip({{ $payroll->id }})"
                                    class="text-red-900 hover:text-red-700 inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    View
                                </button>
                                @if($payroll->status === 'approved')
                                    <button wire:click="processPayment({{ $payroll->id }})"
                                        class="text-red-900 hover:text-red-700 inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        Pay
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="text-gray-500 text-lg">No payroll records found for {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</p>
                                <p class="text-gray-400 text-sm mt-2">Click "Generate Payroll" to create payroll for this period</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($payrolls->hasPages())
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                {{ $payrolls->links() }}
            </div>
        @endif
    </div>

    {{-- Payslip Modal --}}
    @if($showPayslipModal && $selectedEmployee)
        <div class="fixed z-50 inset-0 overflow-y-auto" style="display: block;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity" wire:click="$set('showPayslipModal', false)">
                    <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    {{-- Header --}}
                    <div class="bg-red-600 px-6 py-5">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Payslip - {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}
                        </h3>
                    </div>

                    <div class="bg-white px-6 py-6">
                        {{-- Employee Info --}}
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Employee Information
                            </h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Name:</span>
                                    <span class="font-medium">{{ $selectedEmployee->employee->first_name ?? '' }} {{ $selectedEmployee->employee->last_name ?? '' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Employee #:</span>
                                    <span class="font-medium">{{ $selectedEmployee->employee->employee_number ?? '' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Position:</span>
                                    <span class="font-medium">{{ $selectedEmployee->employee->job_title ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Pay Period:</span>
                                    <span class="font-medium">{{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            {{-- Earnings --}}
                            <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                                <h4 class="font-semibold text-green-800 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Earnings
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between py-1">
                                        <span class="text-gray-700">Basic Salary</span>
                                        <span class="font-medium">TZS {{ number_format($selectedEmployee->basic_salary, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between py-1">
                                        <span class="text-gray-700">House Allowance</span>
                                        <span class="font-medium">TZS {{ number_format($selectedEmployee->house_allowance ?? 0, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between py-1">
                                        <span class="text-gray-700">Transport Allowance</span>
                                        <span class="font-medium">TZS {{ number_format($selectedEmployee->transport_allowance ?? 0, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between font-bold pt-2 mt-2 border-t-2 border-green-300 text-green-800">
                                        <span>Gross Salary</span>
                                        <span>TZS {{ number_format($selectedEmployee->gross_salary, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Deductions --}}
                            <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                                <h4 class="font-semibold text-red-800 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                    Deductions
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between py-1">
                                        <span class="text-gray-700">PAYE (Tax)</span>
                                        <span class="font-medium">TZS {{ number_format($selectedEmployee->paye ?? 0, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between py-1">
                                        <span class="text-gray-700">NSSF</span>
                                        <span class="font-medium">TZS {{ number_format($selectedEmployee->nssf ?? 0, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between py-1">
                                        <span class="text-gray-700">NHIF</span>
                                        <span class="font-medium">TZS {{ number_format($selectedEmployee->nhif ?? 0, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between font-bold pt-2 mt-2 border-t-2 border-red-300 text-red-800">
                                        <span>Total Deductions</span>
                                        <span>TZS {{ number_format($selectedEmployee->total_deductions, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Net Pay --}}
                        <div class="bg-red-600 rounded-lg p-6 text-white">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-red-100 text-sm mb-1">NET PAY</p>
                                    <p class="text-4xl font-bold">TZS {{ number_format($selectedEmployee->net_salary, 2) }}</p>
                                </div>
                                <svg class="w-20 h-20 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                            </div>
                            @if($selectedEmployee->payment_date)
                                <div class="mt-3 text-sm text-red-100">
                                    Payment Date: {{ \Carbon\Carbon::parse($selectedEmployee->payment_date)->format('M d, Y') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                        <button type="button" wire:click="$set('showPayslipModal', false)"
                            class="px-5 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                            Close
                        </button>
                        <button type="button" class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-800 transition flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                            Download PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Budget Details Modal (placeholder - to be implemented) --}}
    @if($showBudgetModal)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0" wire:click="$set('showBudgetModal', false)">
                    <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
                </div>
                <div class="bg-white rounded-lg p-6 max-w-4xl w-full relative z-10">
                    <h3 class="text-xl font-bold mb-4">Budget Breakdown Details</h3>
                    <p class="text-gray-600">Detailed budget breakdown will be displayed here...</p>
                    <button wire:click="$set('showBudgetModal', false)" class="mt-4 px-4 py-2 bg-gray-300 rounded-lg">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>
