<div>
    <div class="p-6 bg-white rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6">Computation of Capital Adequacy</h2>

        <!-- Date Range Selection -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" wire:model="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" wire:model="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-red-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-red-800">Tier 1 Capital</h3>
                <p class="text-2xl font-bold text-red-600">TZS {{ number_format($totalTier1Capital, 2) }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-green-800">Tier 2 Capital</h3>
                <p class="text-2xl font-bold text-green-600">TZS {{ number_format($totalTier2Capital, 2) }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-yellow-800">Total Capital</h3>
                <p class="text-2xl font-bold text-yellow-600">TZS {{ number_format($totalCapital, 2) }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-purple-800">Risk-Weighted Assets</h3>
                <p class="text-2xl font-bold text-purple-600">TZS {{ number_format($totalRiskWeightedAssets, 2) }}</p>
            </div>
        </div>

        <!-- Tier 1 Capital Section -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Tier 1 Capital (Core Capital)</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($capitalData['tier1'] as $capital)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $capital->account_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($capital->amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $capital->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $capital->created_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tier 2 Capital Section -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Tier 2 Capital (Supplementary Capital)</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($capitalData['tier2'] as $capital)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $capital->account_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($capital->amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $capital->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $capital->created_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Risk-Weighted Assets Section -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Risk-Weighted Assets</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asset Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Weight</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weighted Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($capitalData['risk_weighted_assets'] as $asset)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $asset->asset_category }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($asset->amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($asset->risk_weight, 2) }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($asset->weighted_amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $asset->created_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Capital Adequacy Analysis -->
        <div class="bg-gray-50 p-6 rounded-lg">
            <h3 class="text-xl font-semibold mb-4">Capital Adequacy Analysis</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Total Capital</p>
                    <p class="text-2xl font-bold text-gray-900">TZS {{ number_format($totalCapital, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Risk-Weighted Assets</p>
                    <p class="text-2xl font-bold text-gray-900">TZS {{ number_format($totalRiskWeightedAssets, 2) }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-600">Capital Adequacy Ratio (CAR)</p>
                    <p class="text-2xl font-bold {{ $capitalAdequacyRatio >= $minimumRequiredRatio ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($capitalAdequacyRatio, 2) }}%
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        {{ $capitalAdequacyRatio >= $minimumRequiredRatio 
                            ? 'The institution meets the minimum capital adequacy requirement of ' . $minimumRequiredRatio . '%.' 
                            : 'The institution does not meet the minimum capital adequacy requirement of ' . $minimumRequiredRatio . '%.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
            <!-- Capital Structure Chart -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Capital Structure</h3>
                <canvas id="capitalChart" class="w-full h-64"></canvas>
            </div>

            <!-- Risk-Weighted Assets Distribution Chart -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Risk-Weighted Assets Distribution</h3>
                <canvas id="assetsChart" class="w-full h-64"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('livewire:load', function () {
            // Capital Structure Chart
            const capitalCtx = document.getElementById('capitalChart').getContext('2d');
            const capitalChart = new Chart(capitalCtx, {
                type: 'pie',
                data: {
                    labels: ['Tier 1 Capital', 'Tier 2 Capital'],
                    datasets: [{
                        data: [
                            {{ $totalTier1Capital }},
                            {{ $totalTier2Capital }}
                        ],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.5)',
                            'rgba(16, 185, 129, 0.5)'
                        ],
                        borderColor: [
                            'rgb(59, 130, 246)',
                            'rgb(16, 185, 129)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'TZS' + context.raw.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Risk-Weighted Assets Chart
            const assetsCtx = document.getElementById('assetsChart').getContext('2d');
            const assetsChart = new Chart(assetsCtx, {
                type: 'bar',
                data: {
                    labels: @json($capitalData['risk_weighted_assets']->pluck('asset_category')),
                    datasets: [{
                        label: 'Weighted Amount',
                        data: @json($capitalData['risk_weighted_assets']->pluck('weighted_amount')),
                        backgroundColor: 'rgba(245, 158, 11, 0.5)',
                        borderColor: 'rgb(245, 158, 11)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'TZS' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            Livewire.on('dataUpdated', () => {
                capitalChart.data.datasets[0].data = [
                    {{ $totalTier1Capital }},
                    {{ $totalTier2Capital }}
                ];
                capitalChart.update();

                assetsChart.data.labels = @json($capitalData['risk_weighted_assets']->pluck('asset_category'));
                assetsChart.data.datasets[0].data = @json($capitalData['risk_weighted_assets']->pluck('weighted_amount'));
                assetsChart.update();
            });
        });
    </script>
    @endpush
</div> 