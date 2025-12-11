<div>
    <!-- Enhanced Reporting & Analytics Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900">Writeoff Analytics & Reporting</h3>
            <div class="flex gap-3">
                <select wire:model="analyticsType" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="yearly">Yearly</option>
                </select>
                <button wire:click="exportAnalytics" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Report
                </button>
            </div>
        </div>

        <!-- Key Metrics Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-red-600 font-medium">Total Writeoffs</p>
                        <p class="text-2xl font-bold text-red-800">
                            TZS {{ number_format($analytics['summary']['total_writeoffs_amount'] ?? 0, 0) }}
                        </p>
                        <p class="text-xs text-red-600 mt-1">{{ $analytics['summary']['total_writeoffs_count'] ?? 0 }} loans</p>
                    </div>
                    <div class="p-3 bg-red-200 rounded-lg">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-600 font-medium">Recovery Rate</p>
                        <p class="text-2xl font-bold text-green-800">
                            {{ $analytics['summary']['recovery_rate'] ?? 0 }}%
                        </p>
                        <p class="text-xs text-green-600 mt-1">
                            TZS {{ number_format($analytics['summary']['total_recovered_amount'] ?? 0, 0) }}
                        </p>
                    </div>
                    <div class="p-3 bg-green-200 rounded-lg">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">NPL Ratio</p>
                        <p class="text-2xl font-bold text-blue-800">
                            {{ $analytics['regulatory_metrics']['npl_ratio'] ?? 0 }}%
                        </p>
                        <p class="text-xs text-blue-600 mt-1">
                            @if(($analytics['regulatory_metrics']['npl_ratio'] ?? 0) <= 5)
                                <span class="text-green-600">✓ Within limit</span>
                            @else
                                <span class="text-red-600">⚠ Above 5% limit</span>
                            @endif
                        </p>
                    </div>
                    <div class="p-3 bg-blue-200 rounded-lg">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 font-medium">Portfolio Impact</p>
                        <p class="text-2xl font-bold text-purple-800">
                            {{ $analytics['portfolio_impact']['writeoffs_as_percentage_of_portfolio'] ?? 0 }}%
                        </p>
                        <p class="text-xs text-purple-600 mt-1">Of total portfolio</p>
                    </div>
                    <div class="p-3 bg-purple-200 rounded-lg">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trend Analysis Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Writeoff Trends -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Writeoff Trends</h4>
                <canvas id="writeoffTrendsChart" height="250"></canvas>
            </div>

            <!-- Recovery Analysis -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Recovery Analysis</h4>
                <canvas id="recoveryAnalysisChart" height="250"></canvas>
            </div>
        </div>

        <!-- Breakdown Analysis Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- By Loan Product -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Breakdown by Loan Product</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Count</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">%</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach(($analytics['breakdown']['by_loan_product'] ?? []) as $product => $data)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $product }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ $data['count'] ?? 0 }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 text-right">
                                    {{ number_format($data['amount'] ?? 0, 0) }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900 text-right">
                                    {{ $data['percentage'] ?? 0 }}%
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- By Amount Range -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Breakdown by Amount Range</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Range</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Count</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">%</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach(($analytics['breakdown']['by_amount_range'] ?? []) as $range => $data)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $range }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ $data['count'] ?? 0 }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 text-right">
                                    {{ number_format($data['amount'] ?? 0, 0) }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900 text-right">
                                    {{ $data['percentage'] ?? 0 }}%
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Regulatory Compliance Metrics -->
        <div class="bg-yellow-50 rounded-lg p-4 mb-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Regulatory Compliance Metrics</h4>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-600">Board Approval Compliance</p>
                    <div class="flex items-center mt-1">
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" 
                                 style="width: {{ $analytics['regulatory_metrics']['board_approval_compliance']['compliance_rate'] ?? 0 }}%"></div>
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700">
                            {{ $analytics['regulatory_metrics']['board_approval_compliance']['compliance_rate'] ?? 0 }}%
                        </span>
                    </div>
                </div>
                
                <div>
                    <p class="text-xs text-gray-600">Documentation Compliance</p>
                    <div class="flex items-center mt-1">
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-900 h-2 rounded-full" 
                                 style="width: {{ $analytics['regulatory_metrics']['documentation_compliance']['compliance_rate'] ?? 0 }}%"></div>
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700">
                            {{ $analytics['regulatory_metrics']['documentation_compliance']['compliance_rate'] ?? 0 }}%
                        </span>
                    </div>
                </div>
                
                <div>
                    <p class="text-xs text-gray-600">Provision Coverage</p>
                    <div class="flex items-center mt-1">
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" 
                                 style="width: {{ min(100, $analytics['portfolio_impact']['provision_coverage'] ?? 0) }}%"></div>
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700">
                            {{ $analytics['portfolio_impact']['provision_coverage'] ?? 0 }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Automated Recommendations -->
        @if(!empty($analytics['recommendations']))
        <div class="bg-blue-50 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Automated Recommendations</h4>
            <div class="space-y-3">
                @foreach($analytics['recommendations'] as $recommendation)
                <div class="bg-white rounded-lg p-3 border-l-4 
                    @if($recommendation['priority'] === 'high') border-red-500
                    @elseif($recommendation['priority'] === 'medium') border-yellow-500
                    @else border-green-500
                    @endif">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            @if($recommendation['priority'] === 'high')
                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            @elseif($recommendation['priority'] === 'medium')
                                <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </div>
                        <div class="ml-3">
                            <h5 class="text-sm font-medium text-gray-900">{{ $recommendation['title'] }}</h5>
                            <p class="text-xs text-gray-600 mt-1">{{ $recommendation['description'] }}</p>
                            @if(!empty($recommendation['suggested_actions']))
                            <ul class="mt-2 text-xs text-gray-500 list-disc list-inside">
                                @foreach($recommendation['suggested_actions'] as $action)
                                <li>{{ $action }}</li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script nonce="{{ csp_nonce() }}">
    // Writeoff Trends Chart
    const trendsData = @json($analytics['trends'] ?? []);
    const writeoffTrendsCtx = document.getElementById('writeoffTrendsChart');
    if (writeoffTrendsCtx && trendsData.length > 0) {
        new Chart(writeoffTrendsCtx, {
            type: 'line',
            data: {
                labels: trendsData.map(t => t.period_label),
                datasets: [{
                    label: 'Writeoffs',
                    data: trendsData.map(t => t.writeoffs_amount),
                    borderColor: 'rgba(239, 68, 68, 1)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Recoveries',
                    data: trendsData.map(t => t.recoveries_amount),
                    borderColor: 'rgba(34, 197, 94, 1)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'TZS ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Recovery Analysis Chart
    const recoveryData = @json($analytics['recovery_analysis'] ?? []);
    const recoveryAnalysisCtx = document.getElementById('recoveryAnalysisChart');
    if (recoveryAnalysisCtx) {
        new Chart(recoveryAnalysisCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(recoveryData.by_method || {}),
                datasets: [{
                    label: 'Recovery Amount',
                    data: Object.values(recoveryData.by_method || {}).map(m => m.amount),
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'TZS ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush