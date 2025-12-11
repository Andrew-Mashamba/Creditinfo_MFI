<!-- Analytics Tab Content -->
<div class="space-y-6">
    @php
        $analytics = $this->getPortfolioAnalytics();
        $trends = $analytics['trends'] ?? [];
        $coverageTrend = $analytics['coverage_trend'] ?? [];
        $riskDistribution = $analytics['portfolio_risk_distribution'] ?? [];
    @endphp

    <!-- Key Metrics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Current NPL Ratio</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $nplRatio ?? 0 }}%</p>
                </div>
                <div class="p-3 bg-red-100 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Target: < 5%</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Coverage Ratio</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $analytics['current_coverage_ratio'] ?? 0 }}%</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Target: ≥ 100%</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Avg Recovery Rate</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $analytics['average_recovery_rate'] ?? 0 }}%</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Last 12 months</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Provision Gap</p>
                    <p class="text-2xl font-bold {{ $provisionGap > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format(abs($provisionGap), 2) }}
                    </p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ $provisionGap > 0 ? 'Under-provisioned' : 'Adequate' }}</p>
        </div>
    </div>

    <!-- NPL Trend Analysis -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">NPL Ratio Trend (12 Months)</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left text-xs font-medium text-gray-700 uppercase px-2 py-2">Month</th>
                        @foreach($trends as $trend)
                            <th class="text-right text-xs font-medium text-gray-700 px-2 py-2">{{ $trend['month'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-left text-sm font-medium text-gray-700 px-2 py-2">NPL %</td>
                        @foreach($trends as $trend)
                            <td class="text-right text-sm px-2 py-2">
                                <span class="{{ $trend['npl_ratio'] > 5 ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                    {{ $trend['npl_ratio'] }}%
                                </span>
                            </td>
                        @endforeach
                    </tr>
                    <tr class="border-t">
                        <td class="text-left text-sm font-medium text-gray-700 px-2 py-2">Portfolio (M)</td>
                        @foreach($trends as $trend)
                            <td class="text-right text-sm text-gray-600 px-2 py-2">
                                {{ number_format($trend['total_portfolio'] / 1000000, 1) }}
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Portfolio Risk Distribution -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Portfolio Risk Distribution</h3>
        <div class="space-y-4">
            @foreach($riskDistribution as $category)
                @php
                    $totalPortfolio = collect($riskDistribution)->sum('outstanding_amount');
                    $percentage = $totalPortfolio > 0 ? ($category->outstanding_amount / $totalPortfolio) * 100 : 0;
                    $colorClass = match($category->risk_category) {
                        'Current' => 'bg-green-500',
                        '1-30 days' => 'bg-yellow-400',
                        '31-60 days' => 'bg-yellow-500',
                        '61-90 days' => 'bg-orange-500',
                        default => 'bg-red-500'
                    };
                @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700">{{ $category->risk_category }}</span>
                        <span class="text-gray-600">
                            {{ $category->loan_count }} loans | {{ number_format($category->outstanding_amount, 2) }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="{{ $colorClass }} h-2 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Provision Coverage Analysis -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Provision Coverage by Category</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Standard (0-30 days)</span>
                    <span class="text-sm font-medium">1%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Watch (31-90 days)</span>
                    <span class="text-sm font-medium">5%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Substandard (91-180 days)</span>
                    <span class="text-sm font-medium">25%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Doubtful (181-365 days)</span>
                    <span class="text-sm font-medium">50%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Loss (>365 days)</span>
                    <span class="text-sm font-medium">100%</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recovery Performance</h3>
            @php
                $recoveryStats = $this->getRecoveryStatistics();
            @endphp
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Written-off Loans</span>
                    <span class="text-sm font-medium">{{ $recoveryStats->total_written_off ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Written-off Amount</span>
                    <span class="text-sm font-medium">{{ number_format($recoveryStats->total_written_off_amount ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Loans with Recoveries</span>
                    <span class="text-sm font-medium">{{ $recoveryStats->loans_with_recoveries ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Recovered</span>
                    <span class="text-sm font-medium">{{ number_format($recoveryStats->total_recovered_amount ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center border-t pt-3">
                    <span class="text-sm font-semibold text-gray-700">Recovery Rate</span>
                    <span class="text-lg font-bold text-green-600">{{ $recoveryStats->recovery_rate ?? 0 }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>