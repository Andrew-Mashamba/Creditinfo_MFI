<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">General Report</h2>
                <p class="text-gray-600">General operational overview and summary reports</p>
            </div>
            <div class="flex space-x-3">
                <button wire:click="exportToExcel" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">                       
                        <span wire:loading.remove wire:target="exportToExcel">Export Excel</span>
                        <span wire:loading wire:target="exportToExcel">Generating Excel...</span>
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Report Period</label>
                <select wire:model="reportPeriod" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                <select wire:model="selectedMonth" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                <select wire:model="selectedYear" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                <select wire:model="selectedBranch" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch['id'] }}">{{ $branch['branch_name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Overview Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $totalClients }}</h4>
                    <p class="text-sm text-gray-500">Total Members</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($totalLoanAmount, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">Total Loan Portfolio</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($totalDeposits, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">Total Deposits</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $activeBranches }}</h4>
                    <p class="text-sm text-gray-500">Active Branches</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Client Summary --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Member Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="space-y-2">
                <h4 class="font-medium text-gray-900">By Type</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Individual:</span>
                        <span class="font-medium">{{ $clientSummary['by_type']['individual']['count'] ?? 0 }} ({{ $clientSummary['by_type']['individual']['percentage'] ?? 0 }}%)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Group:</span>
                        <span class="font-medium">{{ $clientSummary['by_type']['group']['count'] ?? 0 }} ({{ $clientSummary['by_type']['group']['percentage'] ?? 0 }}%)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Corporate:</span>
                        <span class="font-medium">{{ $clientSummary['by_type']['corporate']['count'] ?? 0 }} ({{ $clientSummary['by_type']['corporate']['percentage'] ?? 0 }}%)</span>
                    </div>
                </div>
            </div>
            <div class="space-y-2">
                <h4 class="font-medium text-gray-900">By Age Group</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">18-25:</span>
                        <span class="font-medium">{{ $clientSummary['by_age_group']['18-25']['count'] ?? 0 }} ({{ $clientSummary['by_age_group']['18-25']['percentage'] ?? 0 }}%)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">26-35:</span>
                        <span class="font-medium">{{ $clientSummary['by_age_group']['26-35']['count'] ?? 0 }} ({{ $clientSummary['by_age_group']['26-35']['percentage'] ?? 0 }}%)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">36-45:</span>
                        <span class="font-medium">{{ $clientSummary['by_age_group']['36-45']['count'] ?? 0 }} ({{ $clientSummary['by_age_group']['36-45']['percentage'] ?? 0 }}%)</span>
                    </div>
                </div>
            </div>
            <div class="space-y-2">
                <h4 class="font-medium text-gray-900">By Gender</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Male:</span>
                        <span class="font-medium">{{ $clientSummary['by_gender']['male']['count'] ?? 0 }} ({{ $clientSummary['by_gender']['male']['percentage'] ?? 0 }}%)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Female:</span>
                        <span class="font-medium">{{ $clientSummary['by_gender']['female']['count'] ?? 0 }} ({{ $clientSummary['by_gender']['female']['percentage'] ?? 0 }}%)</span>
                    </div>
                </div>
            </div>
            <div class="space-y-2">
                <h4 class="font-medium text-gray-900">Geographical</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Urban:</span>
                        <span class="font-medium">{{ $clientSummary['geographical_distribution']['urban']['count'] ?? 0 }} ({{ $clientSummary['geographical_distribution']['urban']['percentage'] ?? 0 }}%)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Rural:</span>
                        <span class="font-medium">{{ $clientSummary['geographical_distribution']['rural']['count'] ?? 0 }} ({{ $clientSummary['geographical_distribution']['rural']['percentage'] ?? 0 }}%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Financial Summary --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-2">
                <h4 class="font-medium text-gray-900">Income</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Interest Income:</span>
                        <span class="font-medium">{{ number_format($financialSummary['income']['interest_income'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Fee Income:</span>
                        <span class="font-medium">{{ number_format($financialSummary['income']['fee_income'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between border-t pt-1">
                        <span class="text-gray-900 font-medium">Total Income:</span>
                        <span class="font-bold">{{ number_format($financialSummary['income']['total_income'] ?? 0, 2) }} TZS</span>
                    </div>
                </div>
            </div>
            <div class="space-y-2">
                <h4 class="font-medium text-gray-900">Expenses</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Operating:</span>
                        <span class="font-medium">{{ number_format($financialSummary['expenses']['operating_expenses'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Staff Costs:</span>
                        <span class="font-medium">{{ number_format($financialSummary['expenses']['staff_costs'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between border-t pt-1">
                        <span class="text-gray-900 font-medium">Total Expenses:</span>
                        <span class="font-bold">{{ number_format($financialSummary['expenses']['total_expenses'] ?? 0, 2) }} TZS</span>
                    </div>
                </div>
            </div>
            <div class="space-y-2">
                <h4 class="font-medium text-gray-900">Profitability</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Net Profit:</span>
                        <span class="font-medium">{{ number_format($financialSummary['profitability']['net_profit'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Profit Margin:</span>
                        <span class="font-medium">{{ $financialSummary['profitability']['profit_margin'] ?? 0 }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">ROA:</span>
                        <span class="font-medium">{{ $financialSummary['profitability']['roa'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>