<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif
    
    @if (session()->has('info'))
        <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">
            {{ session('info') }}
        </div>
    @endif

    <!-- Journal Advice Alert -->
    @if($journalAdvice && $journalAdvice['status'] === 'pending_approval')
        <div class="mb-6 bg-yellow-50 border-2 border-yellow-300 rounded-lg p-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-yellow-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-semibold text-yellow-900">Journal Entry Required</h3>
                    <p class="mt-2 text-sm text-yellow-800">{{ $journalAdvice['narrative'] }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium">Date:</span> {{ $journalAdvice['date'] }}
                        </div>
                        <div>
                            <span class="font-medium">Amount:</span> TZS {{ number_format($journalAdvice['amount'], 2) }}
                        </div>
                        <div>
                            <span class="font-medium">Debit:</span> {{ str_replace('_', ' ', ucfirst($journalAdvice['debit_account'])) }}
                        </div>
                        <div>
                            <span class="font-medium">Credit:</span> {{ str_replace('_', ' ', ucfirst($journalAdvice['credit_account'])) }}
                        </div>
                    </div>
                    <div class="mt-4">
                        <button wire:click="approveJournalEntry" 
                                class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition">
                            Approve & Post Manually
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Header and Controls -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Loan Loss Provisions</h2>
                <p class="text-gray-600 mt-1">Manage and monitor loan loss provisions and risk reserves</p>
            </div>
            <div class="flex gap-3">
                <div>
                    <label class="text-sm text-gray-600">Select Date:</label>
                    <input type="date" wire:model="selectedDate" 
                           class="ml-2 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <button wire:click="recalculateProvisions" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                    Recalculate
                </button>
                <button wire:click="exportProvisionReport" 
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    Export Report
                </button>
            </div>
        </div>
    </div>

    <!-- Current Status -->
    @if($currentStatus)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Total Provisions Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Provisions</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            TZS {{ number_format($currentStatus['total_provisions'], 2) }}
                        </p>
                        <div class="mt-2 text-sm">
                            <span class="text-gray-600">General:</span> 
                            <span class="font-medium">TZS {{ number_format($currentStatus['general_provisions'], 2) }}</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-gray-600">Specific:</span> 
                            <span class="font-medium">TZS {{ number_format($currentStatus['specific_provisions'], 2) }}</span>
                        </div>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-lg">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- NPL Ratio Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">NPL Ratio</p>
                        <p class="text-2xl font-bold {{ $currentStatus['npl_ratio'] > 5 ? 'text-red-600' : 'text-gray-900' }} mt-1">
                            {{ number_format($currentStatus['npl_ratio'], 2) }}%
                        </p>
                        @if($currentStatus['npl_ratio'] > 5)
                            <p class="text-xs text-red-600 mt-2">⚠️ Above 5% threshold</p>
                        @else
                            <p class="text-xs text-green-600 mt-2">✓ Within acceptable range</p>
                        @endif
                    </div>
                    <div class="p-3 {{ $currentStatus['npl_ratio'] > 5 ? 'bg-red-100' : 'bg-green-100' }} rounded-lg">
                        <svg class="w-8 h-8 {{ $currentStatus['npl_ratio'] > 5 ? 'text-red-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Coverage Ratio Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Provision Coverage</p>
                        <p class="text-2xl font-bold {{ $currentStatus['provision_coverage_ratio'] < 100 ? 'text-yellow-600' : 'text-gray-900' }} mt-1">
                            {{ number_format($currentStatus['provision_coverage_ratio'], 2) }}%
                        </p>
                        @if($currentStatus['provision_coverage_ratio'] < 100)
                            <p class="text-xs text-yellow-600 mt-2">⚠️ Below 100% coverage</p>
                        @else
                            <p class="text-xs text-green-600 mt-2">✓ Adequate coverage</p>
                        @endif
                    </div>
                    <div class="p-3 {{ $currentStatus['provision_coverage_ratio'] < 100 ? 'bg-yellow-100' : 'bg-green-100' }} rounded-lg">
                        <svg class="w-8 h-8 {{ $currentStatus['provision_coverage_ratio'] < 100 ? 'text-yellow-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Portfolio Classification -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Portfolio Classification & Provisions</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classification</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding Balance</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Provision Rate</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Provision Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $classifications = [
                                'PERFORMING' => ['balance' => $currentStatus['performing_balance'], 'rate' => 1],
                                'WATCH' => ['balance' => $currentStatus['watch_balance'], 'rate' => 5],
                                'SUBSTANDARD' => ['balance' => $currentStatus['substandard_balance'], 'rate' => 25],
                                'DOUBTFUL' => ['balance' => $currentStatus['doubtful_balance'], 'rate' => 50],
                                'LOSS' => ['balance' => $currentStatus['loss_balance'], 'rate' => 100],
                            ];
                        @endphp
                        @foreach($classifications as $class => $data)
                            <tr class="{{ $loop->odd ? 'bg-white' : 'bg-gray-50' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $class === 'PERFORMING' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $class === 'WATCH' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $class === 'SUBSTANDARD' ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $class === 'DOUBTFUL' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $class === 'LOSS' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ $class }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900">
                                    TZS {{ number_format($data['balance'], 2) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900">
                                    {{ $data['rate'] }}%
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                    TZS {{ number_format($data['balance'] * $data['rate'] / 100, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
            <p class="text-gray-500">No provision data available. Run daily activities to generate provisions.</p>
        </div>
    @endif

    <!-- Provision Rates Configuration -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Provision Rates Configuration</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classification</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Range</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($provisionRates as $rate)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $rate['classification'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $rate['min_days'] }} - {{ $rate['max_days'] ?? '∞' }} days
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                {{ number_format($rate['provision_rate'], 2) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $rate['provision_type'] === 'general' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ ucfirst($rate['provision_type']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $rate['description'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Trends Section -->
    <div class="flex gap-4 mb-4">
        <button wire:click="$toggle('showTrends')" 
                class="px-4 py-2 {{ $showTrends ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700' }} rounded-lg transition">
            {{ $showTrends ? 'Hide' : 'Show' }} 30-Day Trends
        </button>
        <button wire:click="$toggle('showDetails')" 
                class="px-4 py-2 {{ $showDetails ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700' }} rounded-lg transition">
            {{ $showDetails ? 'Hide' : 'Show' }} Top Provisions
        </button>
    </div>

    <!-- 30-Day Trends -->
    @if($showTrends && count($trends) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">30-Day Provision Trends</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Provisions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">NPL Ratio</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Coverage</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($trends as $trend)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $trend['summary_date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    TZS {{ number_format($trend['total_provisions'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {{ number_format($trend['npl_ratio'], 2) }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {{ number_format($trend['provision_coverage_ratio'], 2) }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Top Provisions Details -->
    @if($showDetails && count($topProvisions) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Provisions by Amount</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classification</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Days in Arrears</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Provision</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topProvisions as $provision)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $provision['loan_id'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $provision['client_number'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $provision['loan_classification'] === 'PERFORMING' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $provision['loan_classification'] === 'WATCH' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $provision['loan_classification'] === 'SUBSTANDARD' ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $provision['loan_classification'] === 'DOUBTFUL' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $provision['loan_classification'] === 'LOSS' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ $provision['loan_classification'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {{ $provision['days_in_arrears'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    TZS {{ number_format($provision['outstanding_balance'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {{ number_format($provision['provision_rate'], 2) }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                    TZS {{ number_format($provision['provision_amount'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>