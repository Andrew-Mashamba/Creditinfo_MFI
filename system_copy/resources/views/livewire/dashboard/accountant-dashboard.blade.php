@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header with Actions --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-blue-900 mb-2">Accountant / Finance Officer Dashboard</h1>
            <p class="text-slate-600">Financial overview and transaction management</p>
        </div>
        <div class="flex space-x-3 mt-4 md:mt-0">
            <button wire:click="refreshData" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition-colors duration-200 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Refresh</span>
            </button>
            <button wire:click="exportData" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Export</span>
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            {{ session('message') }}
        </div>
    @endif

    {{-- Financial KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="group bg-white rounded-2xl shadow-lg border border-slate-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center space-x-2 mb-2">
                <div class="w-2 h-2 bg-blue-900 rounded-full"></div>
                <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Receipts Today</p>
            </div>
            <p class="text-2xl font-bold text-blue-900 mb-1">TZS {{ number_format($totalReceiptsToday) }}</p>
            <p class="text-xs text-slate-500 font-medium">Month: TZS {{ number_format($totalReceiptsMTD) }}</p>
        </div>
        <div class="group bg-white rounded-2xl shadow-lg border border-slate-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center space-x-2 mb-2">
                <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Payments Today</p>
            </div>
            <p class="text-2xl font-bold text-red-600 mb-1">TZS {{ number_format($totalPaymentsToday) }}</p>
            <p class="text-xs text-slate-500 font-medium">Month: TZS {{ number_format($totalPaymentsMTD) }}</p>
        </div>
        <div class="group bg-white rounded-2xl shadow-lg border border-slate-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center space-x-2 mb-2">
                <div class="w-2 h-2 bg-blue-900 rounded-full"></div>
                <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Bank Balances</p>
            </div>
            <ul class="text-xs text-slate-700 font-medium space-y-1">
                @foreach($bankBalances as $bank)
                    <li>{{ $bank['bank_name'] }} ({{ $bank['account_number'] }}): <span class="font-bold">TZS {{ number_format($bank['balance']) }}</span></li>
                @endforeach
            </ul>
        </div>
        <div class="group bg-white rounded-2xl shadow-lg border border-slate-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center space-x-2 mb-2">
                <div class="w-2 h-2 bg-blue-900 rounded-full"></div>
                <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Cashbook Balance</p>
            </div>
            <p class="text-2xl font-bold text-blue-900 mb-1">TZS {{ number_format($cashbookBalance) }}</p>
            <p class="text-xs text-slate-500 font-medium">Pending Reconciliations: <span class="text-red-600 font-bold">{{ $pendingReconciliations }}</span></p>
        </div>
    </div>

    {{-- Expense Breakdown Pie Chart --}}
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 mb-8">
        <div class="flex items-center space-x-3 mb-4">
            <div class="p-2 bg-red-600 rounded-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-slate-900">Expense Breakdown</h3>
        </div>
        <div id="expenseBreakdownChart" class="h-80"></div>
    </div>

    {{-- Transactions Section --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
            <h4 class="text-lg font-bold text-blue-900 mb-4">Recent Payments</h4>
            <ul class="divide-y divide-slate-100">
                @foreach($recentPayments as $payment)
                    <li class="py-2 flex flex-col">
                        <span class="font-semibold text-slate-800">TZS {{ number_format($payment['amount']) }}</span>
                        <span class="text-xs text-slate-500">{{ $payment['description'] }}</span>
                        <span class="text-xs text-slate-400">{{ $payment['transaction_date'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
            <h4 class="text-lg font-bold text-blue-900 mb-4">Unreconciled Entries</h4>
            <ul class="divide-y divide-slate-100">
                @foreach($unreconciledEntries as $entry)
                    <li class="py-2 flex flex-col">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-800">TZS {{ number_format($entry['amount']) }}</span>
                            <button wire:click="markAsReconciled({{ $entry['id'] }})" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200 transition-colors">
                                Mark Reconciled
                            </button>
                        </div>
                        <span class="text-xs text-slate-500">{{ $entry['description'] }}</span>
                        <span class="text-xs text-slate-400">{{ $entry['transaction_date'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
            <h4 class="text-lg font-bold text-blue-900 mb-4">Pending Expense Claims</h4>
            <ul class="divide-y divide-slate-100">
                @foreach($pendingExpenseClaims as $claim)
                    <li class="py-2 flex flex-col">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-800">TZS {{ number_format($claim['amount']) }}</span>
                            <button wire:click="approveExpenseClaim({{ $claim['id'] }})" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200 transition-colors">
                                Approve
                            </button>
                        </div>
                        <span class="text-xs text-slate-500">{{ $claim['description'] }}</span>
                        <span class="text-xs text-slate-400">{{ $claim['expense_date'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="p-2 bg-blue-900 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Daily Receipts vs. Payments</h3>
            </div>
            <div id="dailyReceiptsPaymentsChart" class="h-80"></div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="p-2 bg-blue-900 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Monthly Expense Comparison</h3>
            </div>
            <div id="monthlyExpenseComparisonChart" class="h-80"></div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 lg:col-span-2">
            <div class="flex items-center space-x-3 mb-4">
                <div class="p-2 bg-blue-900 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Cash Flow Projection</h3>
            </div>
            <div id="cashFlowProjectionChart" class="h-80"></div>
        </div>
    </div>

    {{-- Export Modal --}}
    @if($showExportModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md">
                <h3 class="text-xl font-bold text-slate-900 mb-4">Export Data</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Export Type</label>
                        <select wire:model="selectedExportType" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                            <option value="transactions">Transactions</option>
                            <option value="expenses">Expenses</option>
                            <option value="reconciliations">Reconciliations</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Date Range</label>
                        <select wire:model="exportDateRange" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                            <option value="today">Today</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                        </select>
                    </div>
                </div>
                <div class="flex space-x-3 mt-6">
                    <button wire:click="generateExport" class="flex-1 bg-blue-900 text-white py-2 px-4 rounded-lg hover:bg-blue-800 transition-colors">
                        Generate Export
                    </button>
                    <button wire:click="$set('showExportModal', false)" class="flex-1 bg-slate-300 text-slate-700 py-2 px-4 rounded-lg hover:bg-slate-400 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script nonce="{{ csp_nonce() }}">
    // Expense Breakdown Pie Chart
    const expenseBreakdownChartOptions = {
        series: @json(array_column($expenseBreakdown, 'amount')),
        chart: {
            type: 'pie',
            height: 320,
            fontFamily: 'Inter, sans-serif'
        },
        labels: @json(array_column($expenseBreakdown, 'category')),
        colors: ['#1E3A8A', '#DC2626', '#1E40AF', '#B91C1C', '#1D4ED8'],
        legend: {
            position: 'bottom',
            fontFamily: 'Inter, sans-serif'
        },
        title: {
            text: 'Expense Breakdown',
            align: 'center',
            style: {
                fontFamily: 'Inter, sans-serif',
                fontWeight: 600
            }
        },
        dataLabels: {
            style: {
                fontFamily: 'Inter, sans-serif'
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '0%'
                }
            }
        }
    };
    new ApexCharts(document.querySelector("#expenseBreakdownChart"), expenseBreakdownChartOptions).render();

    // Daily Receipts vs Payments Chart
    const dailyReceiptsPaymentsChartOptions = {
        series: [
            {
                name: 'Receipts',
                data: @json($dailyReceiptsPaymentsData['receipts'])
            },
            {
                name: 'Payments',
                data: @json($dailyReceiptsPaymentsData['payments'])
            }
        ],
        chart: {
            type: 'line',
            height: 320,
            fontFamily: 'Inter, sans-serif',
            toolbar: { show: false }
        },
        colors: ['#1E3A8A', '#DC2626'],
        xaxis: {
            categories: @json($dailyReceiptsPaymentsData['labels']),
            labels: { style: { fontFamily: 'Inter, sans-serif', fontSize: '12px' } }
        },
        yaxis: {
            labels: {
                style: { fontFamily: 'Inter, sans-serif', fontSize: '12px' },
                formatter: function (val) { return "TZS " + (val / 1000000).toFixed(1) + "M" }
            }
        },
        legend: { fontFamily: 'Inter, sans-serif', position: 'top' },
        tooltip: {
            style: { fontFamily: 'Inter, sans-serif' },
            y: { formatter: function (val) { return "TZS " + val.toLocaleString() } }
        },
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: false }
    };
    new ApexCharts(document.querySelector("#dailyReceiptsPaymentsChart"), dailyReceiptsPaymentsChartOptions).render();

    // Monthly Expense Comparison Chart
    const monthlyExpenseComparisonChartOptions = {
        series: [{
            name: 'Expenses',
            data: @json($monthlyExpenseComparisonData['expenses'])
        }],
        chart: {
            type: 'bar',
            height: 320,
            fontFamily: 'Inter, sans-serif',
            toolbar: { show: false }
        },
        colors: ['#DC2626'],
        xaxis: {
            categories: @json($monthlyExpenseComparisonData['labels']),
            labels: { style: { fontFamily: 'Inter, sans-serif', fontSize: '12px' } }
        },
        yaxis: {
            labels: {
                style: { fontFamily: 'Inter, sans-serif', fontSize: '12px' },
                formatter: function (val) { return "TZS " + (val / 1000000).toFixed(1) + "M" }
            }
        },
        legend: { fontFamily: 'Inter, sans-serif', position: 'top' },
        tooltip: {
            style: { fontFamily: 'Inter, sans-serif' },
            y: { formatter: function (val) { return "TZS " + val.toLocaleString() } }
        },
        dataLabels: { enabled: false }
    };
    new ApexCharts(document.querySelector("#monthlyExpenseComparisonChart"), monthlyExpenseComparisonChartOptions).render();

    // Cash Flow Projection Chart
    const cashFlowProjectionChartOptions = {
        series: [
            {
                name: 'Projected Income',
                data: @json($cashFlowProjectionData['projected_income'])
            },
            {
                name: 'Projected Expenses',
                data: @json($cashFlowProjectionData['projected_expenses'])
            }
        ],
        chart: {
            type: 'area',
            height: 320,
            fontFamily: 'Inter, sans-serif',
            toolbar: { show: false }
        },
        colors: ['#1E3A8A', '#DC2626'],
        xaxis: {
            categories: @json($cashFlowProjectionData['labels']),
            labels: { style: { fontFamily: 'Inter, sans-serif', fontSize: '12px' } }
        },
        yaxis: {
            labels: {
                style: { fontFamily: 'Inter, sans-serif', fontSize: '12px' },
                formatter: function (val) { return "TZS " + (val / 1000000).toFixed(1) + "M" }
            }
        },
        legend: { fontFamily: 'Inter, sans-serif', position: 'top' },
        tooltip: {
            style: { fontFamily: 'Inter, sans-serif' },
            y: { formatter: function (val) { return "TZS " + val.toLocaleString() } }
        },
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: false },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1 } }
    };
    new ApexCharts(document.querySelector("#cashFlowProjectionChart"), cashFlowProjectionChartOptions).render();
</script>
@endpush
@endsection

