{{-- Comprehensive Budget Reports View --}}
<div class="space-y-6">
    {{-- Report Header --}}
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">Budget Reports & Analytics</h3>
        <div class="flex space-x-2">
            <button wire:click="exportReport('pdf')" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export PDF
            </button>
            <button wire:click="exportReport('excel')" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2zm-5-5h2"></path>
                </svg>
                Export Excel
            </button>
            <button wire:click="printReport" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print
            </button>
        </div>
    </div>
    
    {{-- Report Filters --}}
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="grid grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Budget</label>
                <select wire:model="reportBudgetId" wire:change="generateReports" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">All Budgets</option>
                    @foreach($budgets as $budget)
                        <option value="{{ $budget->id }}">{{ $budget->budget_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Report Type</label>
                <select wire:model="reportType" wire:change="generateReports" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="variance">Variance Analysis</option>
                    <option value="utilization">Utilization Trends</option>
                    <option value="rollover">Rollover Summary</option>
                    <option value="advances">Advance Tracking</option>
                    <option value="supplementary">Supplementary Requests</option>
                    <option value="comprehensive">Comprehensive Report</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Period</label>
                <select wire:model="reportPeriod" wire:change="generateReports" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="monthly">Current Month</option>
                    <option value="quarterly">Current Quarter</option>
                    <option value="yearly">Current Year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            @if($reportPeriod === 'custom')
                <div>
                    <label class="block text-sm font-medium text-gray-700">From</label>
                    <input type="date" wire:model="reportFrom" wire:change="generateReports" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">To</label>
                    <input type="date" wire:model="reportTo" wire:change="generateReports" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            @else
                <div>
                    <label class="block text-sm font-medium text-gray-700">Year</label>
                    <select wire:model="reportYear" wire:change="generateReports" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                @if($reportPeriod === 'monthly')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Month</label>
                        <select wire:model="reportMonth" wire:change="generateReports" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $month)
                                <option value="{{ $index + 1 }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif($reportPeriod === 'quarterly')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Quarter</label>
                        <select wire:model="reportQuarter" wire:change="generateReports" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="1">Q1 (Jan-Mar)</option>
                            <option value="2">Q2 (Apr-Jun)</option>
                            <option value="3">Q3 (Jul-Sep)</option>
                            <option value="4">Q4 (Oct-Dec)</option>
                        </select>
                    </div>
                @endif
            @endif
        </div>
    </div>
    
    {{-- Report Content Based on Type --}}
    @if($reportType === 'variance' || $reportType === 'comprehensive')
        {{-- Variance Analysis Report --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h4 class="text-md font-medium text-gray-900 mb-4">Budget vs Actual Variance Analysis</h4>
            
            {{-- Summary Cards --}}
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-red-50 p-4 rounded-lg">
                    <p class="text-sm text-red-600">Total Budget</p>
                    <p class="text-2xl font-bold text-red-900">{{ number_format($varianceReport['total_budget'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-sm text-green-600">Total Actual</p>
                    <p class="text-2xl font-bold text-green-900">{{ number_format($varianceReport['total_actual'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <p class="text-sm text-yellow-600">Variance Amount</p>
                    <p class="text-2xl font-bold {{ ($varianceReport['variance_amount'] ?? 0) < 0 ? 'text-red-900' : 'text-yellow-900' }}">
                        {{ number_format(abs($varianceReport['variance_amount'] ?? 0), 2) }}
                        @if(($varianceReport['variance_amount'] ?? 0) < 0)
                            <span class="text-sm">(Over)</span>
                        @else
                            <span class="text-sm">(Under)</span>
                        @endif
                    </p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <p class="text-sm text-purple-600">Variance %</p>
                    <p class="text-2xl font-bold {{ ($varianceReport['variance_percentage'] ?? 0) > 10 ? 'text-red-900' : 'text-purple-900' }}">
                        {{ number_format(abs($varianceReport['variance_percentage'] ?? 0), 1) }}%
                    </p>
                </div>
            </div>
            
            {{-- Detailed Variance Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Budget Item</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Budgeted</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actual</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Variance</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Variance %</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($varianceReport['items'] ?? [] as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $item['name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($item['budgeted'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($item['actual'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $item['variance'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $item['variance'] < 0 ? '(' : '' }}{{ number_format(abs($item['variance']), 2) }}{{ $item['variance'] < 0 ? ')' : '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    {{ number_format($item['variance_percentage'], 1) }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($item['variance_percentage'] > 10)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Over Budget
                                        </span>
                                    @elseif($item['variance_percentage'] > 5)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Warning
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            On Track
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    
    @if($reportType === 'utilization' || $reportType === 'comprehensive')
        {{-- Utilization Trends Report --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h4 class="text-md font-medium text-gray-900 mb-4">Budget Utilization Trends</h4>
            
            {{-- Utilization Chart --}}
            <div class="mb-6">
                <canvas id="utilizationChart" width="400" height="100"></canvas>
            </div>
            
            {{-- Monthly Utilization Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Allocated</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Utilized</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Remaining</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Utilization %</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Trend</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($utilizationReport['monthly'] ?? [] as $month)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $month['month_name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($month['allocated'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($month['utilized'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $month['remaining'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($month['remaining'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <div class="flex items-center justify-end">
                                        <span class="mr-2">{{ number_format($month['utilization_percentage'], 1) }}%</span>
                                        <div class="w-20 bg-gray-200 rounded-full h-2">
                                            <div class="bg-{{ $month['utilization_percentage'] > 90 ? 'red' : ($month['utilization_percentage'] > 75 ? 'yellow' : 'green') }}-500 h-2 rounded-full" 
                                                 style="width: {{ min($month['utilization_percentage'], 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($month['trend'] === 'up')
                                        <svg class="w-5 h-5 text-red-500 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                    @elseif($month['trend'] === 'down')
                                        <svg class="w-5 h-5 text-green-500 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-gray-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"></path>
                                        </svg>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    
    @if($reportType === 'rollover' || $reportType === 'comprehensive')
        {{-- Rollover Summary Report --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h4 class="text-md font-medium text-gray-900 mb-4">Rollover Summary Report</h4>
            
            {{-- Rollover Statistics --}}
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-red-50 p-4 rounded-lg">
                    <p class="text-sm text-red-600">Total Unused Budget</p>
                    <p class="text-2xl font-bold text-red-900">{{ number_format($rolloverReport['total_unused'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-sm text-green-600">Automatic Rollover</p>
                    <p class="text-2xl font-bold text-green-900">{{ number_format($rolloverReport['automatic_rollover'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <p class="text-sm text-yellow-600">Pending Approval</p>
                    <p class="text-2xl font-bold text-yellow-900">{{ number_format($rolloverReport['pending_approval'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <p class="text-sm text-red-600">Expired/Lost</p>
                    <p class="text-2xl font-bold text-red-900">{{ number_format($rolloverReport['expired'] ?? 0, 2) }}</p>
                </div>
            </div>
            
            {{-- Rollover Details Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Budget Item</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unused Amount</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rollover Policy</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Rolled Over</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($rolloverReport['items'] ?? [] as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item['period'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item['budget_name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($item['unused_amount'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($item['rollover_policy'] === 'AUTOMATIC')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Automatic
                                        </span>
                                    @elseif($item['rollover_policy'] === 'APPROVAL_REQUIRED')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Approval Required
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            No Rollover
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $item['rolled_over'] > 0 ? 'text-green-600' : 'text-gray-500' }}">
                                    {{ number_format($item['rolled_over'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($item['status'] === 'completed')
                                        <span class="text-green-600">✓ Completed</span>
                                    @elseif($item['status'] === 'pending')
                                        <span class="text-yellow-600">⏳ Pending</span>
                                    @else
                                        <span class="text-red-600">✗ Expired</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($item['status'] === 'pending' && $item['rollover_policy'] === 'APPROVAL_REQUIRED')
                                        <button wire:click="approveRollover({{ $item['id'] }})" 
                                                class="text-indigo-600 hover:text-indigo-900">
                                            Approve
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    
    @if($reportType === 'advances' || $reportType === 'comprehensive')
        {{-- Advance Tracking Report --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h4 class="text-md font-medium text-gray-900 mb-4">Budget Advance Tracking Report</h4>
            
            {{-- Advance Statistics --}}
            <div class="grid grid-cols-5 gap-4 mb-6">
                <div class="bg-orange-50 p-4 rounded-lg">
                    <p class="text-sm text-orange-600">Total Advances</p>
                    <p class="text-2xl font-bold text-orange-900">{{ number_format($advanceReport['total_advances'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-sm text-green-600">Repaid</p>
                    <p class="text-2xl font-bold text-green-900">{{ number_format($advanceReport['total_repaid'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <p class="text-sm text-red-600">Outstanding</p>
                    <p class="text-2xl font-bold text-red-900">{{ number_format($advanceReport['outstanding'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <p class="text-sm text-yellow-600">Pending Approval</p>
                    <p class="text-2xl font-bold text-yellow-900">{{ $advanceReport['pending_count'] ?? 0 }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <p class="text-sm text-purple-600">Overdue</p>
                    <p class="text-2xl font-bold text-purple-900">{{ $advanceReport['overdue_count'] ?? 0 }}</p>
                </div>
            </div>
            
            {{-- Advance Details Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Budget Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">From Period</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Repaid</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Repayment</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($advanceReport['advances'] ?? [] as $advance)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $advance['request_date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $advance['budget_name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $advance['from_period'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                    {{ number_format($advance['amount'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">
                                    {{ number_format($advance['repaid_amount'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $advance['outstanding_amount'] > 0 ? 'text-red-600' : 'text-gray-500' }}">
                                    {{ number_format($advance['outstanding_amount'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($advance['status'] === 'APPROVED')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    @elseif($advance['status'] === 'PENDING')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @elseif($advance['status'] === 'PARTIALLY_REPAID')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Partial Repaid
                                        </span>
                                    @elseif($advance['status'] === 'FULLY_REPAID')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Fully Repaid
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ $advance['status'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $advance['repayment_percentage'] }}%"></div>
                                    </div>
                                    <span class="text-xs">{{ $advance['repayment_percentage'] }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    
    @if($reportType === 'supplementary' || $reportType === 'comprehensive')
        {{-- Supplementary Requests Report --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h4 class="text-md font-medium text-gray-900 mb-4">Supplementary Budget Requests Report</h4>
            
            {{-- Supplementary Statistics --}}
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-purple-50 p-4 rounded-lg">
                    <p class="text-sm text-purple-600">Total Requested</p>
                    <p class="text-2xl font-bold text-purple-900">{{ number_format($supplementaryReport['total_requested'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-sm text-green-600">Approved</p>
                    <p class="text-2xl font-bold text-green-900">{{ number_format($supplementaryReport['total_approved'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <p class="text-sm text-yellow-600">Pending</p>
                    <p class="text-2xl font-bold text-yellow-900">{{ number_format($supplementaryReport['total_pending'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <p class="text-sm text-red-600">Rejected</p>
                    <p class="text-2xl font-bold text-red-900">{{ number_format($supplementaryReport['total_rejected'] ?? 0, 2) }}</p>
                </div>
            </div>
            
            {{-- Supplementary Requests Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Budget Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Requested</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Approved</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Justification</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approvers</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($supplementaryReport['requests'] ?? [] as $request)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $request['request_date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $request['budget_name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $request['period'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                    {{ number_format($request['requested_amount'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $request['approved_amount'] > 0 ? 'text-green-600' : 'text-gray-500' }}">
                                    {{ number_format($request['approved_amount'], 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="max-w-xs truncate" title="{{ $request['justification'] }}">
                                        {{ Str::limit($request['justification'], 50) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($request['status'] === 'APPROVED')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    @elseif($request['status'] === 'PENDING')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @elseif($request['status'] === 'REJECTED')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Rejected
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ $request['status'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($request['approvers'])
                                        <div class="flex -space-x-1">
                                            @foreach(array_slice($request['approvers'], 0, 3) as $approver)
                                                <div class="w-6 h-6 rounded-full bg-gray-300 border-2 border-white flex items-center justify-center" 
                                                     title="{{ $approver['name'] }}">
                                                    <span class="text-xs">{{ substr($approver['name'], 0, 1) }}</span>
                                                </div>
                                            @endforeach
                                            @if(count($request['approvers']) > 3)
                                                <div class="w-6 h-6 rounded-full bg-gray-400 border-2 border-white flex items-center justify-center">
                                                    <span class="text-xs text-white">+{{ count($request['approvers']) - 3 }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    
    {{-- Report Footer --}}
    <div class="bg-gray-50 p-4 rounded-lg">
        <div class="flex justify-between items-center">
            <p class="text-sm text-gray-600">
                Report generated on {{ now()->format('F d, Y h:i A') }}
            </p>
            <div class="flex space-x-4">
                <button wire:click="scheduleReport" class="text-sm text-indigo-600 hover:text-indigo-900">
                    Schedule Report
                </button>
                <button wire:click="shareReport" class="text-sm text-indigo-600 hover:text-indigo-900">
                    Share Report
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script nonce="{{ csp_nonce() }}">
document.addEventListener('livewire:load', function () {
    @if($reportType === 'utilization' || $reportType === 'comprehensive')
        const ctx = document.getElementById('utilizationChart');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json($utilizationReport['chart']['labels'] ?? []),
                    datasets: [{
                        label: 'Budget Utilization %',
                        data: @json($utilizationReport['chart']['data'] ?? []),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Monthly Utilization Trend'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
    @endif
});
</script>
@endpush