<div class="bg-white rounded-lg shadow-sm">
    <!-- Configuration Error Alert -->
    @if($hasConfigurationErrors)
        <div class="bg-red-50 border-l-4 border-red-500 p-4 m-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Configuration Required</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p class="mb-2">The following accounts need to be configured in institution settings:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($configurationErrors as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-3 font-medium">To configure these accounts:</p>
                        <ol class="list-decimal list-inside mt-1 space-y-1">
                            <li>Go to Settings → Institution Configuration</li>
                            <li>Navigate to the Accounting section</li>
                            <li>Set the following accounts:
                                <ul class="list-disc list-inside ml-4 mt-1">
                                    <li><strong>Loan Loss Reserve Account</strong> - A liability account for reserves</li>
                                    <li><strong>Loan Loss Expense Account</strong> - An expense account for provisions</li>
                                    <li><strong>Loan Recovery Income Account</strong> - An income account for recoveries</li>
                                </ul>
                            </li>
                            <li>Save the configuration and click the refresh button below</li>
                        </ol>
                        <div class="mt-4">
                            <button type="button" 
                                    wire:click="refreshConfiguration"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Refresh Configuration
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Disable the rest of the interface if configuration errors exist -->
        <div class="px-6 py-8 text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-lg font-medium text-gray-900 mb-2">Module Disabled</p>
            <p>Please configure the required GL accounts before using this module.</p>
        </div>
    @else
        <!-- Header Section with Enhanced Metrics -->
        <div class="border-b border-gray-200 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Loan Loss Reserve Management</h2>
                    <p class="text-sm text-gray-600 mt-1">IFRS 9 Expected Credit Loss (ECL) Model Compliance</p>
                </div>
                <div class="grid grid-cols-2 gap-4 text-right">
                    <div>
                        <p class="text-xs text-gray-600">Reporting Period</p>
                        <p class="text-lg font-semibold">{{ $currentYear }} - Q{{ ceil($currentMonth / 3) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600">ECL Model Stage</p>
                        <p class="text-lg font-semibold text-blue-600">Stage {{ $this->getECLStage() }}</p>
                    </div>
                </div>
            </div>
        </div>
    
    <!-- Enhanced Navigation Tabs -->
    <div class="border-b border-gray-200 px-6">
        <nav class="-mb-px flex space-x-8">
            <button wire:click="changeViewMode('dashboard')" 
                    class="py-2 px-1 border-b-2 {{ $viewMode === 'dashboard' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} font-medium text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Dashboard
                </div>
            </button>
            <button wire:click="changeViewMode('provision')" 
                    class="py-2 px-1 border-b-2 {{ $viewMode === 'provision' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} font-medium text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Make Provision
                    @if($provisionGap > 0)
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800">Gap: {{ number_format((float)$provisionGap, 0) }}</span>
                    @endif
                </div>
            </button>
            <button wire:click="changeViewMode('writeoff')" 
                    class="py-2 px-1 border-b-2 {{ $viewMode === 'writeoff' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} font-medium text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Write-offs
                    @if($this->getWriteOffCandidatesCount() > 0)
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">{{ $this->getWriteOffCandidatesCount() }}</span>
                    @endif
                </div>
            </button>
            <button wire:click="changeViewMode('recovery')" 
                    class="py-2 px-1 border-b-2 {{ $viewMode === 'recovery' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} font-medium text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Recovery
                    @if($this->getWrittenOffLoansCount() > 0)
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">{{ $this->getWrittenOffLoansCount() }}</span>
                    @endif
                </div>
            </button>
            <button wire:click="changeViewMode('history')" 
                    class="py-2 px-1 border-b-2 {{ $viewMode === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} font-medium text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    History
                </div>
            </button>
            <button wire:click="changeViewMode('analytics')" 
                    class="py-2 px-1 border-b-2 {{ $viewMode === 'analytics' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} font-medium text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Analytics
                </div>
            </button>
            <button wire:click="changeViewMode('monitoring')" 
                    class="py-2 px-1 border-b-2 {{ $viewMode === 'monitoring' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} font-medium text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Monitoring
                    @if($this->getPendingCyclesCount() > 0)
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-orange-100 text-orange-800">{{ $this->getPendingCyclesCount() }}</span>
                    @endif
                </div>
            </button>
            <button wire:click="changeViewMode('reports')" 
                    class="py-2 px-1 border-b-2 {{ $viewMode === 'reports' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} font-medium text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Reports
                </div>
            </button>
        </nav>
    </div>
    
    <!-- Main Content Area -->
    <div class="p-6">
        @if($viewMode === 'dashboard')
            <!-- Enhanced Dashboard View -->
            <div class="space-y-6">
    <!-- Key Metrics Cards with Trend Indicators -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm text-blue-600 font-medium">Loan Portfolio</p>
                    <p class="text-2xl font-bold text-blue-900">{{ number_format($loanPortfolioValue, 2) }}</p>
                    <p class="text-xs text-blue-600 mt-1">
                        <span class="flex items-center">
                            @if($this->getPortfolioTrend() > 0)
                                <svg class="w-3 h-3 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                +{{ number_format($this->getPortfolioTrend(), 1) }}%
                            @else
                                <svg class="w-3 h-3 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                {{ number_format($this->getPortfolioTrend(), 1) }}%
                            @endif
                            vs last month
                        </span>
                    </p>
                </div>
                <svg class="w-10 h-10 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        
        <div class="bg-green-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm text-green-600 font-medium">Current Reserve</p>
                    <p class="text-2xl font-bold text-green-900">{{ number_format($currentReserveBalance, 2) }}</p>
                    <p class="text-xs text-green-600 mt-1">{{ number_format($stats['coverage_ratio'] ?? 0, 2) }}% Coverage</p>
                </div>
                <svg class="w-10 h-10 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
        </div>
        
        <div class="bg-yellow-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm text-yellow-600 font-medium">Required Reserve</p>
                    <p class="text-2xl font-bold text-yellow-900">{{ number_format($requiredReserve, 2) }}</p>
                    <p class="text-xs text-yellow-600 mt-1">ECL Based</p>
                </div>
                <svg class="w-10 h-10 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
        
        <div class="{{ $provisionGap > 0 ? 'bg-red-50' : 'bg-gray-50' }} rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm {{ $provisionGap > 0 ? 'text-red-600' : 'text-gray-600' }} font-medium">Provision Gap</p>
                    <p class="text-2xl font-bold {{ $provisionGap > 0 ? 'text-red-900' : 'text-gray-900' }}">{{ number_format(abs($provisionGap), 2) }}</p>
                    <p class="text-xs {{ $provisionGap > 0 ? 'text-red-600' : 'text-gray-600' }} mt-1">
                        {{ $provisionGap > 0 ? 'Under-provisioned' : ($provisionGap < 0 ? 'Over-provisioned' : 'Adequate') }}
                    </p>
                </div>
                <svg class="w-10 h-10 {{ $provisionGap > 0 ? 'text-red-300' : 'text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>
        
        <div class="bg-purple-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm text-purple-600 font-medium">NPL Ratio</p>
                    <p class="text-2xl font-bold text-purple-900">{{ number_format($stats['npl_ratio'] ?? 0, 2) }}%</p>
                    <p class="text-xs text-purple-600 mt-1">Non-Performing</p>
                </div>
                <svg class="w-10 h-10 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>


    </div>

    <!-- Aging Analysis Section -->
    <div class="mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Loan Portfolio Aging Analysis</h3>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aging Category
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Days in Arrears
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            # of Loans
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Outstanding Amount
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Provision Rate
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Required Provision
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $categories = [
                            'current' => ['name' => 'Standard', 'days' => '0-30 days', 'color' => 'green'],
                            'watch' => ['name' => 'Watch', 'days' => '31-90 days', 'color' => 'yellow'],
                            'substandard' => ['name' => 'Substandard', 'days' => '91-180 days', 'color' => 'orange'],
                            'doubtful' => ['name' => 'Doubtful', 'days' => '181-365 days', 'color' => 'red'],
                            'loss' => ['name' => 'Loss', 'days' => '>365 days', 'color' => 'purple']
                        ];
                        $totalOutstanding = 0;
                        $totalProvision = 0;
                    @endphp
                    
                    @foreach($categories as $key => $category)
                        @php
                            $data = $loanAging[$key] ?? ['count' => 0, 'amount' => 0, 'provision_rate' => 0, 'required_provision' => 0];
                            $totalOutstanding += $data['amount'];
                            $totalProvision += $data['required_provision'];
                            $portfolioPercentage = $loanPortfolioValue > 0 ? ($data['amount'] / $loanPortfolioValue) * 100 : 0;
                        @endphp
                        <tr class="{{ $data['count'] > 0 ? 'hover:bg-gray-50' : 'bg-gray-50' }} transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $category['color'] }}-100 text-{{ $category['color'] }}-800">
                                        {{ $category['name'] }}
                                    </span>
                                    @if($portfolioPercentage > 0)
                                        <span class="text-xs text-gray-500">({{ number_format($portfolioPercentage, 1) }}%)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $category['days'] }}</div>
                                @if($data['amount'] > 0)
                                    <div class="mt-1 w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-{{ $category['color'] }}-500 h-1.5 rounded-full" style="width: {{ min(100, $portfolioPercentage * 2) }}%"></div>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <span class="font-semibold {{ $data['count'] > 0 ? 'text-gray-900' : 'text-gray-400' }}">
                                    {{ $data['count'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <span class="font-semibold {{ $data['amount'] > 0 ? 'text-gray-900' : 'text-gray-400' }}">
                                    {{ number_format($data['amount'], 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-{{ $category['color'] }}-50 text-{{ $category['color'] }}-700">
                                    {{ $data['provision_rate'] }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <span class="font-bold {{ $data['required_provision'] > 0 ? 'text-gray-900' : 'text-gray-400' }}">
                                    {{ number_format($data['required_provision'], 2) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    
                    <!-- Total Row -->
                    <tr class="bg-blue-50 border-t-2 border-blue-300 font-bold">
                        <td colspan="2" class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm text-blue-900">TOTAL PORTFOLIO</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-900 text-right">
                            {{ array_sum(array_column($loanAging, 'count')) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-900 text-right">
                            {{ number_format($totalOutstanding, 2) }} TZS
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-900 text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-100">
                                {{ $totalOutstanding > 0 ? number_format(($totalProvision / $totalOutstanding) * 100, 2) : '0.00' }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-900 text-right">
                            <span class="text-base">{{ number_format($totalProvision, 2) }} TZS</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Provision Rates Legend -->
        <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-blue-900 mb-2">IFRS 9 ECL Provision Rates Applied:</h4>
            <div class="grid grid-cols-5 gap-4 text-xs">
                <div class="text-center">
                    <span class="block font-medium text-green-700">Standard (0-30)</span>
                    <span class="text-green-600">1%</span>
                </div>
                <div class="text-center">
                    <span class="block font-medium text-yellow-700">Watch (31-90)</span>
                    <span class="text-yellow-600">5%</span>
                </div>
                <div class="text-center">
                    <span class="block font-medium text-orange-700">Substandard (91-180)</span>
                    <span class="text-orange-600">25%</span>
                </div>
                <div class="text-center">
                    <span class="block font-medium text-red-700">Doubtful (181-365)</span>
                    <span class="text-red-600">50%</span>
                </div>
                <div class="text-center">
                    <span class="block font-medium text-purple-700">Loss (>365)</span>
                    <span class="text-purple-600">100%</span>
                </div>
            </div>
        </div>

        <!-- Portfolio Quality Summary -->
        @php
            $performingLoans = ($loanAging['current']['count'] ?? 0);
            $nonPerformingLoans = ($loanAging['watch']['count'] ?? 0) +
                                  ($loanAging['substandard']['count'] ?? 0) +
                                  ($loanAging['doubtful']['count'] ?? 0) +
                                  ($loanAging['loss']['count'] ?? 0);
            $totalLoans = $performingLoans + $nonPerformingLoans;
            $nplRatio = $totalLoans > 0 ? ($nonPerformingLoans / $totalLoans) * 100 : 0;

            $performingAmount = ($loanAging['current']['amount'] ?? 0);
            $nonPerformingAmount = ($loanAging['watch']['amount'] ?? 0) +
                                   ($loanAging['substandard']['amount'] ?? 0) +
                                   ($loanAging['doubtful']['amount'] ?? 0) +
                                   ($loanAging['loss']['amount'] ?? 0);
            $nplRatioAmount = $loanPortfolioValue > 0 ? ($nonPerformingAmount / $loanPortfolioValue) * 100 : 0;
        @endphp

        <div class="mt-4 grid grid-cols-4 gap-4">
            <!-- Performing Loans -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-green-700">Performing Loans</span>
                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-green-900">{{ $performingLoans }}</div>
                <div class="text-xs text-green-600 mt-1">{{ number_format($performingAmount, 0) }} TZS</div>
            </div>

            <!-- Non-Performing Loans -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-red-700">Non-Performing</span>
                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-red-900">{{ $nonPerformingLoans }}</div>
                <div class="text-xs text-red-600 mt-1">{{ number_format($nonPerformingAmount, 0) }} TZS</div>
            </div>

            <!-- NPL Ratio (Count) -->
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-orange-700">NPL Ratio (Count)</span>
                    <svg class="w-4 h-4 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-2xl font-bold {{ $nplRatio > 5 ? 'text-red-900' : ($nplRatio > 3 ? 'text-orange-900' : 'text-green-900') }}">
                    {{ number_format($nplRatio, 1) }}%
                </div>
                <div class="text-xs text-orange-600 mt-1">Target: < 5%</div>
            </div>

            <!-- NPL Ratio (Amount) -->
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-purple-700">NPL Ratio (Value)</span>
                    <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-2xl font-bold {{ $nplRatioAmount > 5 ? 'text-red-900' : ($nplRatioAmount > 3 ? 'text-orange-900' : 'text-green-900') }}">
                    {{ number_format($nplRatioAmount, 1) }}%
                </div>
                <div class="text-xs text-purple-600 mt-1">of portfolio value</div>
            </div>
        </div>
    </div>

    <!-- GL Accounts Configuration Status -->
    <div class="mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">GL Accounts Configuration</h3>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="grid grid-cols-3 gap-4">
                <div class="flex items-center space-x-2">
                    @if($this->isAccountConfigured('reserve'))
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Reserve Account</p>
                            <p class="text-xs text-gray-500">{{ $reserveAccount }}</p>
                        </div>
                    @else
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-red-900">Reserve Account</p>
                            <p class="text-xs text-red-500">Not configured</p>
                        </div>
                    @endif
                </div>
                
                <div class="flex items-center space-x-2">
                    @if($this->isAccountConfigured('expense'))
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Expense Account</p>
                            <p class="text-xs text-gray-500">{{ $expenseAccount }}</p>
                        </div>
                    @else
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-red-900">Expense Account</p>
                            <p class="text-xs text-red-500">Not configured</p>
                        </div>
                    @endif
                </div>
                
                <div class="flex items-center space-x-2">
                    @if($this->isAccountConfigured('recovery'))
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Recovery Income</p>
                            <p class="text-xs text-gray-500">{{ $recoveryAccount }}</p>
                        </div>
                    @else
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-red-900">Recovery Income</p>
                            <p class="text-xs text-red-500">Not configured</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

            
        @elseif($viewMode === 'provision')
            <!-- Enhanced Make Provision View -->
            <div class="max-w-4xl mx-auto">
                <!-- Pending Approvals Section (for approvers) -->
                @if($this->canApproveProvisions() && count($pendingApprovals) > 0)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <h3 class="text-lg font-semibold text-yellow-900 mb-3">Pending Provision Approvals</h3>
                    <div class="space-y-3">
                        @foreach($pendingApprovals->where('status', 'PENDING') as $approval)
                        <div class="bg-white border border-yellow-300 rounded-lg p-3">
                            <div class="grid grid-cols-4 gap-4">
                                <div>
                                    <p class="text-xs text-gray-600">Amount</p>
                                    <p class="font-semibold">{{ number_format($approval->amount, 2) }} TZS</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">Method</p>
                                    <p class="text-sm">{{ ucfirst($approval->provision_method) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">Requested By</p>
                                    <p class="text-sm">{{ $approval->requested_by_name }}</p>
                                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($approval->requested_at)->diffForHumans() }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">Reason</p>
                                    <p class="text-sm">{{ $approval->reason }}</p>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center space-x-2">
                                <input type="text" 
                                       wire:model="approvalComments" 
                                       placeholder="Comments (optional)"
                                       class="flex-1 text-sm border-gray-300 rounded">
                                <button wire:click="approveProvision({{ $approval->id }})" 
                                        wire:confirm="Are you sure you want to approve this provision of {{ number_format($approval->amount, 2) }} TZS?"
                                        class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                    Approve
                                </button>
                                <button wire:click="rejectProvision({{ $approval->id }})" 
                                        wire:confirm="Are you sure you want to reject this provision?"
                                        class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                    Reject
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Approval Threshold Notice -->
                @if($reserve_amount >= $approvalThreshold)
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-orange-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm text-orange-800 font-medium">Approval Required</p>
                            <p class="text-xs text-orange-700 mt-1">
                                Provisions above {{ number_format($approvalThreshold, 2) }} TZS require approval from authorized personnel.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Create Loan Loss Provision</h3>
                    
                    <!-- Provision Recommendation Based on Schedules -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h4 class="text-sm font-medium text-blue-900 mb-2">Recommended Provision (Based on Loan Schedules Analysis)</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-blue-700">Overdue Installments Total:</span>
                                <span class="font-medium text-blue-900 float-right">{{ number_format($this->getOverdueInstallmentsTotal(), 2) }} TZS</span>
                            </div>
                            <div>
                                <span class="text-blue-700">Expected Loss (ECL Model):</span>
                                <span class="font-medium text-blue-900 float-right">{{ number_format($requiredReserve, 2) }} TZS</span>
                            </div>
                            <div>
                                <span class="text-blue-700">Current Reserve:</span>
                                <span class="font-medium text-blue-900 float-right">{{ number_format($currentReserveBalance, 2) }} TZS</span>
                            </div>
                            <div>
                                <span class="text-blue-700 font-semibold">Minimum Provision Needed:</span>
                                <span class="font-bold text-blue-900 float-right">{{ number_format((float)$provisionGap, 2) }} TZS</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Calculation Method Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Provision Calculation Method</label>
                            <div class="grid grid-cols-3 gap-4">
                                <button type="button" 
                                        wire:click="setProvisionMethod('ecl')"
                                        class="px-4 py-2 border {{ $provisionMethod == 'ecl' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300' }} rounded-lg text-sm font-medium">
                                    ECL Model (IFRS 9)
                                </button>
                                <button type="button"
                                        wire:click="setProvisionMethod('aging')"
                                        class="px-4 py-2 border {{ $provisionMethod == 'aging' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300' }} rounded-lg text-sm font-medium">
                                    Aging Based
                                </button>
                                <button type="button"
                                        wire:click="setProvisionMethod('manual')"
                                        class="px-4 py-2 border {{ $provisionMethod == 'manual' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300' }} rounded-lg text-sm font-medium">
                                    Manual (% of Profits)
                                </button>
                            </div>
                        </div>
                        
                        @if($provisionMethod === 'manual')
                            <!-- Manual Provision Details -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Profits (TZS)</label>
                                    <input type="number" 
                                           wire:model="profits" 
                                           wire:change="calculateLLR"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Percentage (%)</label>
                                    <input type="number" 
                                           wire:model="percentage" 
                                           wire:change="calculateLLR"
                                           min="0" max="100" step="0.5"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>
                            </div>
                        @endif
                        
                        <!-- Reserve Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Provision Amount (TZS)</label>
                            <input type="text" 
                                   wire:model="reserve_amount" 
                                   {{ $provisionMethod !== 'manual' ? 'readonly' : '' }}
                                   class="mt-1 block w-full {{ $provisionMethod !== 'manual' ? 'bg-gray-50' : '' }} border-gray-300 rounded-md shadow-sm sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">
                                @if($provisionGap > 0)
                                    <span class="text-red-600">⚠️ Minimum provision needed: {{ number_format((float)$provisionGap, 2) }} TZS to meet regulatory requirements</span>
                                @else
                                    <span class="text-green-600">✓ Current reserve meets regulatory requirements</span>
                                @endif
                            </p>
                        </div>
                        
                        <!-- Source Account Selection with Balance Check -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Source of Funds</label>
                            <select wire:model="source" 
                                    wire:change="checkSourceBalance"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                <option value="">Select Account</option>
                                @foreach($this->getSourceAccounts() as $account)
                                    <option value="{{ $account->account_number }}">
                                        {{ $account->account_name }} ({{ $account->account_number }}) - Balance: {{ number_format($account->balance, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @if($sourceAccountBalance !== null && $sourceAccountBalance < $reserve_amount)
                                <p class="mt-1 text-xs text-red-600">⚠️ Insufficient balance in source account</p>
                            @endif
                        </div>
                        
                        <!-- Journal Entry Preview -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Journal Entry Preview</h4>
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-1">Account</th>
                                        <th class="text-right py-1">Debit</th>
                                        <th class="text-right py-1">Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="py-1">Loan Loss Expense ({{ $this->getExpenseAccountDetails()['account_number'] ?? '' }})</td>
                                        <td class="text-right py-1">{{ number_format((float)$reserve_amount, 2) }}</td>
                                        <td class="text-right py-1">-</td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">Loan Loss Reserve ({{ $this->getReserveAccountDetails()['account_number'] ?? '' }})</td>
                                        <td class="text-right py-1">-</td>
                                        <td class="text-right py-1">{{ number_format((float)$reserve_amount, 2) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="border-t font-semibold">
                                        <td class="py-1">Total</td>
                                        <td class="text-right py-1">{{ number_format((float)$reserve_amount, 2) }}</td>
                                        <td class="text-right py-1">{{ number_format((float)$reserve_amount, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <!-- Summary Box -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-blue-900 mb-2">Provision Summary</h4>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-blue-700">Current Reserve Balance:</span>
                                    <span class="font-medium text-blue-900">{{ number_format($currentReserveBalance, 2) }} TZS</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-blue-700">New Provision Amount:</span>
                                    <span class="font-medium text-blue-900">{{ number_format((float)$reserve_amount, 2) }} TZS</span>
                                </div>
                                <div class="flex justify-between border-t border-blue-200 pt-1 mt-1">
                                    <span class="text-blue-700 font-medium">New Reserve Balance:</span>
                                    <span class="font-bold text-blue-900">{{ number_format($currentReserveBalance + (float)$reserve_amount, 2) }} TZS</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-blue-700">Coverage Ratio After Provision:</span>
                                    <span class="font-medium {{ ($currentReserveBalance + $reserve_amount) >= $requiredReserve ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $loanPortfolioValue > 0 ? number_format((($currentReserveBalance + $reserve_amount) / $loanPortfolioValue) * 100, 2) : '0.00' }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" 
                                    wire:click="resetForm"
                                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="button" 
                                    wire:click="validateProvision"
                                    class="px-4 py-2 border border-blue-300 rounded-md shadow-sm text-sm font-medium text-blue-700 bg-white hover:bg-blue-50">
                                Validate Entry
                            </button>
                            <button type="button" 
                                    wire:click="makeProvision"
                                    wire:confirm="Are you sure you want to record this provision of {{ number_format((float)$reserve_amount, 2) }} TZS?"
                                    @if(!$this->canMakeProvision()) disabled @endif
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $this->canMakeProvision() ? 'bg-blue-900 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed' }}">
                                Record Provision
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
        @elseif($viewMode === 'writeoff')
            <!-- Enhanced Write-off View with Schedules Integration -->
            <div class="space-y-4">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm text-yellow-800 font-medium">Write-off Authorization Required</p>
                            <p class="text-xs text-yellow-700 mt-1">
                                Write-offs will reduce the loan loss reserve balance and close loan schedules. 
                                Ensure proper authorization and documentation before proceeding.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Loans Eligible for Write-off (Based on Schedules)</h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600">Filter:</span>
                            <select wire:model="writeOffFilter" class="text-sm border-gray-300 rounded">
                                <option value="180">Over 180 days</option>
                                <option value="360">Over 360 days</option>
                                <option value="all">All NPL</option>
                            </select>
                        </div>
                    </div>
                    
                    @if($this->getWriteOffCandidates()->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-3 text-left">
                                            <input type="checkbox" 
                                                   wire:click="toggleAllLoans"
                                                   @if(count($selectedLoans) === $this->getWriteOffCandidates()->count()) checked @endif
                                                   class="rounded border-gray-300">
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Loan Account</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Client</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Outstanding Principal</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Outstanding Interest</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Total Due</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Overdue Installments</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Max Days Overdue</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Last Payment</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($this->getWriteOffCandidates() as $candidate)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3">
                                                <input type="checkbox" 
                                                       wire:click="toggleLoanSelection('{{ $candidate->loan_id }}')"
                                                       @if(in_array((string)$candidate->loan_id, $selectedLoans)) checked @endif
                                                       class="rounded border-gray-300">
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <div class="font-medium">{{ $candidate->loan_account_number }}</div>
                                                <div class="text-xs text-gray-500">ID: {{ $candidate->loan_id }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <div>{{ $candidate->client_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $candidate->client_number }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right">{{ number_format($candidate->outstanding_principal, 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-right">{{ number_format($candidate->outstanding_interest, 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-semibold">
                                                {{ number_format($candidate->total_outstanding, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-center">
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                    {{ $candidate->overdue_installments }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-center">
                                                <span class="px-2 py-1 text-xs rounded-full {{ $candidate->max_days_overdue > 360 ? 'bg-red-600 text-white' : 'bg-red-100 text-red-800' }}">
                                                    {{ $candidate->max_days_overdue }} days
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-center">
                                                {{ $candidate->last_payment_date ? \Carbon\Carbon::parse($candidate->last_payment_date)->format('d/m/Y') : 'Never' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-sm font-semibold">
                                            Selected: {{ count($selectedLoans) }} loans
                                            @if(config('app.debug'))
                                                <div class="text-xs text-gray-500 mt-1">
                                                    IDs: {{ json_encode($selectedLoans) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-semibold">
                                            {{ number_format($this->getSelectedLoansTotal()['principal'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-semibold">
                                            {{ number_format($this->getSelectedLoansTotal()['interest'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold text-red-600">
                                            {{ number_format($this->getSelectedLoansTotal()['total'], 2) }}
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="p-4 border-t border-gray-200">
                            <div class="space-y-4">
                                <!-- Write-off Impact Analysis -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Write-off Impact Analysis</h4>
                                    <div class="grid grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-600">Current Reserve:</span>
                                            <span class="font-medium float-right">{{ number_format($currentReserveBalance, 2) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Write-off Amount:</span>
                                            <span class="font-medium text-red-600 float-right">-{{ number_format($this->getSelectedLoansTotal()['total'], 2) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Reserve After Write-off:</span>
                                            <span class="font-bold {{ ($currentReserveBalance - $this->getSelectedLoansTotal()['total']) < 0 ? 'text-red-600' : '' }} float-right">
                                                {{ number_format($currentReserveBalance - $this->getSelectedLoansTotal()['total'], 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    @if(($currentReserveBalance - $this->getSelectedLoansTotal()['total']) < 0)
                                        <p class="text-xs text-red-600 mt-2">⚠️ Insufficient reserve balance for selected write-offs</p>
                                    @endif
                                </div>
                                
                                <!-- Write-off Documentation -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Write-off Authorization</label>
                                    <div class="grid grid-cols-2 gap-4 mb-2">
                                        <input type="text" 
                                               wire:model="authorizationNumber"
                                               placeholder="Authorization/Board Resolution Number"
                                               class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                        <input type="date" 
                                               wire:model="authorizationDate"
                                               class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                    </div>
                                    <textarea wire:model="writeOffReason" 
                                              rows="3"
                                              class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm"
                                              placeholder="Provide detailed justification for write-off, including recovery efforts made..."></textarea>
                                </div>
                                
                                <div class="flex justify-end space-x-3">
                                    <button wire:click="exportWriteOffReport" 
                                            class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Export Report
                                    </button>
                                    <button wire:click="processWriteOff" 
                                            wire:confirm="Are you sure you want to write off {{ count($selectedLoans) }} loans totaling {{ number_format($this->getSelectedLoansTotal()['total'], 2) }} TZS? This action cannot be undone."
                                            @if(!$this->canProcessWriteOff()) disabled title="Select loans and fill all required fields" @endif
                                            class="px-4 py-2 text-sm font-medium rounded-md text-white {{ $this->canProcessWriteOff() ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-400 cursor-not-allowed' }}">
                                        Process Write-off
                                    </button>
                                </div>
                                
                                @if(!$this->canProcessWriteOff())
                                    <div class="mt-2 text-sm text-red-600">
                                        @if(count($selectedLoans) == 0)
                                            ⚠️ Please select at least one loan to write off
                                        @elseif(empty($writeOffReason))
                                            ⚠️ Please enter a reason for write-off
                                        @elseif(empty($authorizationNumber))
                                            ⚠️ Please enter authorization number
                                        @elseif($this->getSelectedLoansTotal()['total'] > $currentReserveBalance)
                                            ⚠️ Insufficient reserve balance for write-off
                                        @endif
                                    </div>
                                @endif
                                
                                <!-- Debug Info (Remove in production) -->
                                @if(config('app.debug'))
                                    <div class="mt-2 p-2 bg-gray-100 rounded text-xs">
                                        <p class="font-semibold mb-1">Debug Info:</p>
                                        <div class="space-y-1">
                                            <p>Selected Loans: <span class="font-mono">{{ json_encode($selectedLoans) }}</span></p>
                                            <p>Count: {{ count($selectedLoans) }}</p>
                                            <p>Write-off Reason: {{ $writeOffReason ?: '(empty)' }}</p>
                                            <p>Authorization #: {{ $authorizationNumber ?: '(empty)' }}</p>
                                            <p>Authorization Date: {{ $authorizationDate ?: '(empty)' }}</p>
                                            <p>Reserve Balance: {{ number_format($currentReserveBalance, 2) }}</p>
                                            <p>Selected Total: {{ number_format($this->getSelectedLoansTotal()['total'], 2) }}</p>
                                            <p>Can Process: <span class="{{ $this->canProcessWriteOff() ? 'text-green-600' : 'text-red-600' }}">{{ $this->canProcessWriteOff() ? 'Yes' : 'No' }}</span></p>
                                        </div>
                                        <button wire:click="debugWriteOffState" type="button" class="mt-2 px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-900">
                                            Log State to Console
                                        </button>
                                    </div>
                                @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="mt-2">No loans currently eligible for write-off</p>
                            <p class="text-sm mt-1">Loans must be overdue by more than {{ $writeOffFilter }} days</p>
                        </div>
                    @endif
                </div>
            </div>
            
        @elseif($viewMode === 'recovery')
            <!-- Loan Recovery View (Feature 3: Recovery of Written-off Loans) -->
            <div class="space-y-6">
                <!-- Instructions -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm text-green-800 font-medium">Recovery of Written-off Loans</p>
                            <p class="text-xs text-green-700 mt-1">
                                Record payments received for loans that were previously written off. 
                                This creates income and improves the P&L statement.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Written-off Loans Available for Recovery -->
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Written-off Loans</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Loan Account</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Client</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Written-off Amount</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Already Recovered</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Write-off Date</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($this->getWrittenOffLoans() as $loan)
                                    <tr>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="font-medium">{{ $loan->loan_account_number }}</div>
                                            <div class="text-xs text-gray-500">ID: {{ $loan->id }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ $loan->client_name }}</td>
                                        <td class="px-4 py-3 text-sm text-right">{{ number_format($loan->written_off_amount, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-right">
                                            @if($loan->total_recovered > 0)
                                                <span class="text-green-600 font-medium">{{ number_format($loan->total_recovered, 2) }}</span>
                                            @else
                                                <span class="text-gray-400">0.00</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right">{{ \Carbon\Carbon::parse($loan->write_off_date)->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            <button wire:click="openRecoveryModal({{ $loan->id }})"
                                                    class="text-green-600 hover:text-green-800 font-medium">
                                                Record Recovery
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <p>No written-off loans available for recovery</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Recovery Recording Form (shown in modal) -->
                @if($showRecoveryModal ?? false)
                    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                        <div class="bg-white rounded-lg max-w-2xl w-full p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Record Loan Recovery</h3>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Loan Account</label>
                                        <input type="text" readonly value="{{ $selectedLoanForRecovery['loan_account_number'] ?? '' }}" 
                                               class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-md shadow-sm sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Client Name</label>
                                        <input type="text" readonly value="{{ $selectedLoanForRecovery['client_name'] ?? '' }}" 
                                               class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-md shadow-sm sm:text-sm">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Recovery Amount (TZS)</label>
                                        <input type="number" wire:model="recoveryAmount" min="0" step="0.01"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                        <select wire:model="recoveryMethod" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                            <option value="cash">Cash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="cheque">Cheque</option>
                                            <option value="mobile_money">Mobile Money</option>
                                        </select>
                                    </div>
                                </div>
                                
                                @if($recoveryMethod === 'bank_transfer' || $recoveryMethod === 'cheque')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bank Account</label>
                                        <select wire:model="recoveryBankAccount" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                            <option value="">Select Bank Account</option>
                                            @foreach($this->getBankAccounts() as $account)
                                                <option value="{{ $account->account_number }}">
                                                    {{ $account->account_name }} ({{ $account->account_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Receipt Number (Optional)</label>
                                    <input type="text" wire:model="recoveryReceiptNumber"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea wire:model="recoveryNotes" rows="3"
                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm"></textarea>
                                </div>
                                
                                <!-- Journal Entry Preview -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Journal Entry Preview</h4>
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="border-b">
                                                <th class="text-left py-1">Account</th>
                                                <th class="text-right py-1">Debit</th>
                                                <th class="text-right py-1">Credit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="py-1">{{ $recoveryMethod === 'cash' ? 'Cash' : 'Bank Account' }}</td>
                                                <td class="text-right py-1">{{ number_format((float)($recoveryAmount ?? 0), 2) }}</td>
                                                <td class="text-right py-1">-</td>
                                            </tr>
                                            <tr>
                                                <td class="py-1">Recovery Income ({{ $this->getRecoveryAccountDetails()['account_number'] ?? '' }})</td>
                                                <td class="text-right py-1">-</td>
                                                <td class="text-right py-1">{{ number_format((float)($recoveryAmount ?? 0), 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-3 mt-6">
                                <button wire:click="closeRecoveryModal" 
                                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button wire:click="processRecovery" 
                                        @if(!$recoveryAmount || $recoveryAmount <= 0) disabled @endif
                                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $recoveryAmount > 0 ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed' }}">
                                    Record Recovery
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            
        @elseif($viewMode === 'analytics')
            <!-- Analytics View -->
            @include('livewire.accounting.partials.loan-loss-analytics')
            
        @elseif($viewMode === 'monitoring')
            <!-- Monitoring View -->
            @include('livewire.accounting.partials.loan-loss-monitoring')
            
        @elseif($viewMode === 'reports')
            <!-- Reports View -->
            @include('livewire.accounting.partials.loan-loss-reports')
            
        @elseif($viewMode === 'history')
            <!-- Enhanced History View -->
            <div class="space-y-4">
                <!-- Date Range Filter -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div>
                                <label class="text-sm text-gray-600">From Date</label>
                                <input type="date" wire:model="historyDateFrom" 
                                       wire:change="loadProvisionHistory"
                                       class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">To Date</label>
                                <input type="date" wire:model="historyDateTo" 
                                       wire:change="loadProvisionHistory"
                                       class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                            <div class="flex items-center space-x-2 pt-5">
                                <button wire:click="filterHistory('all')" 
                                        class="px-3 py-1 text-sm rounded {{ $historyFilter === 'all' ? 'bg-blue-900 text-white' : 'bg-gray-200 text-gray-700' }}">
                                    All
                                </button>
                                <button wire:click="filterHistory('provisions')" 
                                        class="px-3 py-1 text-sm rounded {{ $historyFilter === 'provisions' ? 'bg-blue-900 text-white' : 'bg-gray-200 text-gray-700' }}">
                                    Provisions
                                </button>
                                <button wire:click="filterHistory('writeoffs')" 
                                        class="px-3 py-1 text-sm rounded {{ $historyFilter === 'writeoffs' ? 'bg-blue-900 text-white' : 'bg-gray-200 text-gray-700' }}">
                                    Write-offs
                                </button>
                                <button wire:click="filterHistory('adjustments')" 
                                        class="px-3 py-1 text-sm rounded {{ $historyFilter === 'adjustments' ? 'bg-blue-900 text-white' : 'bg-gray-200 text-gray-700' }}">
                                    Adjustments
                                </button>
                            </div>
                        </div>
                        <button wire:click="exportProvisionHistory" 
                                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Export All
                        </button>
                    </div>
                    
                    <!-- History Summary -->
                    @php
                        $summary = $this->getProvisionHistorySummary();
                    @endphp
                    <div class="mt-4 grid grid-cols-5 gap-4">
                        <div class="text-center">
                            <p class="text-xs text-gray-600">Total Provisions</p>
                            <p class="text-lg font-semibold text-green-600">{{ number_format($summary['total_provisions'], 2) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-600">Total Reversals</p>
                            <p class="text-lg font-semibold text-orange-600">{{ number_format($summary['total_reversals'], 2) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-600">Total Write-offs</p>
                            <p class="text-lg font-semibold text-red-600">{{ number_format($summary['total_write_offs'], 2) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-600">Total Adjustments</p>
                            <p class="text-lg font-semibold text-blue-600">{{ number_format($summary['total_adjustments'], 2) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-600">Net Movement</p>
                            <p class="text-lg font-semibold {{ $summary['net_movement'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($summary['net_movement'], 2) }}</p>
                        </div>
                    </div>
                </div>
                <!-- Provision History with Details -->
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Provision History</h3>
                        <button wire:click="exportProvisionHistory" class="text-sm text-blue-600 hover:text-blue-800">
                            Export CSV
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Transaction ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Method</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Description</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Amount</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Balance After</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Posted By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($provisionHistory as $provision)
                                    <tr>
                                        <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($provision->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-sm font-mono">{{ $provision->transaction_id ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                {{ $provision->calculation_method ?? 'Manual' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ $provision->description }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium">{{ number_format($provision->credit, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-right">{{ number_format($provision->balance_after ?? 0, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-center">{{ $provision->posted_by_name ?? 'System' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-3 text-center text-gray-500">No provision history available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Write-off History with Loan Details -->
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Write-off History</h3>
                        <button wire:click="exportWriteOffHistory" class="text-sm text-blue-600 hover:text-blue-800">
                            Export CSV
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Transaction ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Loan Details</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Authorization</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Principal</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Interest</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($writeOffHistory as $writeOff)
                                    <tr>
                                        <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($writeOff->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-sm font-mono">{{ $writeOff->transaction_id ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <div>{{ $writeOff->loan_account_number }}</div>
                                            <div class="text-xs text-gray-500">{{ $writeOff->client_name }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="text-xs">{{ $writeOff->authorization_number }}</div>
                                            <div class="text-xs text-gray-500">{{ $writeOff->authorization_date }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right">{{ number_format($writeOff->principal_amount ?? 0, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-right">{{ number_format($writeOff->interest_amount ?? 0, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium">{{ number_format($writeOff->debit, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                Written Off
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-3 text-center text-gray-500">No write-off history available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Recovery History -->
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Recovery History (Written-off Loans)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Loan Account</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Client</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Recovered Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Recovery Method</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($this->getRecoveryHistory() as $recovery)
                                    <tr>
                                        <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($recovery->created_at)->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $recovery->loan_account_number }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $recovery->client_name }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-green-600">{{ number_format($recovery->amount, 2) }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $recovery->method }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-3 text-center text-gray-500">No recovery history available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Year-End Adjustment Section -->
    @if($viewMode === 'dashboard')
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Year-End Reconciliation</h4>
                    <p class="text-sm text-gray-600">Compare actual losses with provisions and adjust reserve</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div>
                        <label class="text-xs text-gray-600">Actual Losses (TZS)</label>
                        <div class="flex items-center space-x-2">
                            <input type="number" 
                                   wire:model.lazy="actualLoanLosses" 
                                   class="px-3 py-2 border border-gray-300 rounded-md text-sm bg-gray-50"
                                   readonly
                                   title="Auto-calculated from write-off transactions">
                            <button type="button" 
                                    wire:click="refreshActualLosses" 
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-md"
                                    title="Refresh actual losses from accounts">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Auto-calculated from GL {{ $writeOffAccount ?? '0101500055005510' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Adjustments (TZS)</label>
                        <input type="number" 
                               wire:model="adjustments" 
                               placeholder="0.00"
                               class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                    </div>
                    <button wire:click="finalizeYearEnd" 
                            wire:confirm="Are you sure you want to finalize the year-end reconciliation? This will adjust the reserve balance based on actual losses."
                            class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-900">
                        Finalize Year
                    </button>
                </div>
            </div>
            @if($actualLoanLosses > 0 || $currentReserveBalance > 0)
                <div class="mt-3 p-3 bg-white rounded-lg border border-gray-200">
                    <div class="grid grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Provisions Made:</span>
                            <span class="font-medium float-right">{{ number_format($currentReserveBalance, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Actual Losses:</span>
                            <span class="font-medium float-right">{{ number_format($actualLoanLosses, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Variance:</span>
                            <span class="font-medium {{ ($currentReserveBalance - $actualLoanLosses) >= 0 ? 'text-green-600' : 'text-red-600' }} float-right">
                                {{ number_format($currentReserveBalance - $actualLoanLosses, 2) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-600">Adjustment Needed:</span>
                            <span class="font-bold float-right">{{ number_format(abs($currentReserveBalance - $actualLoanLosses), 2) }}</span>
                        </div>
                    </div>
                    
                    @if(($currentReserveBalance - $actualLoanLosses) > 0)
                        <div class="mt-2 pt-2 border-t border-gray-200">
                            <p class="text-xs text-green-600">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Over-provisioned: Release {{ number_format($currentReserveBalance - $actualLoanLosses, 2) }} TZS back to income
                            </p>
                        </div>
                    @elseif(($currentReserveBalance - $actualLoanLosses) < 0)
                        <div class="mt-2 pt-2 border-t border-gray-200">
                            <p class="text-xs text-red-600">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Under-provisioned: Additional provision of {{ number_format(abs($currentReserveBalance - $actualLoanLosses), 2) }} TZS required
                            </p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
    @endif <!-- End of configuration check -->
</div>

<!-- Enhanced Notification Messages -->
@if (session()->has('message'))
    <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('message') }}
    </div>
@endif

@if (session()->has('error'))
    <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        {{ session('error') }}
    </div>
@endif

@if (session()->has('warning'))
    <div class="fixed bottom-4 right-4 bg-yellow-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        {{ session('warning') }}
    </div>
@endif