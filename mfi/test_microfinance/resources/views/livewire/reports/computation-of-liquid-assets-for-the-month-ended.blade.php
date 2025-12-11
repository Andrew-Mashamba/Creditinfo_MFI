<div>
    <div class="p-6 bg-white rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6">Computation of Liquid Assets</h2>

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
                <h3 class="text-lg font-semibold text-red-800">Total Cash</h3>
                <p class="text-2xl font-bold text-red-600">TZS {{ number_format($totalCash, 2) }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-green-800">Total Bank Deposits</h3>
                <p class="text-2xl font-bold text-green-600">TZS {{ number_format($totalBankDeposits, 2) }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-yellow-800">Total Investments</h3>
                <p class="text-2xl font-bold text-yellow-600">TZS {{ number_format($totalInvestments, 2) }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-purple-800">Total Liquid Assets</h3>
                <p class="text-2xl font-bold text-purple-600">TZS {{ number_format($totalLiquidAssets, 2) }}</p>
            </div>
        </div>

        <!-- Cash Balances Section -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Cash Balances</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($liquidAssets['cash'] as $cash)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cash['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($cash['balance'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cash['date'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>        

        <!-- Investments Section -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Investments</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Investment Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest Rate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Maturity Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($liquidAssets['investments'] as $investment)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $investment['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($investment['balance'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $investment['interest_rate'] }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $investment['maturity_date'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $investment['date'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Short-term Liabilities Section -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Short-term Liabilities</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Liability Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>                            
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($liquidAssets['liabilities'] as $liability)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $liability['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($liability['balance'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $liability['date'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Liquidity Analysis -->
        <div class="bg-gray-50 p-6 rounded-lg">
            <h3 class="text-xl font-semibold mb-4">Liquidity Analysis</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Total Liquid Assets</p>
                    <p class="text-2xl font-bold text-gray-900">TZS {{ number_format($totalLiquidAssets, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Short-term Liabilities</p>
                    <p class="text-2xl font-bold text-gray-900">TZS {{ number_format($totalLiabilities, 2) }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-600">Liquidity Ratio</p>
                    <p class="text-2xl font-bold {{ $liquidityRatio >= 100 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($liquidityRatio, 2) }}%
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        {{ $liquidityRatio >= 100 
                            ? 'The institution has sufficient liquid assets to cover its short-term liabilities.' 
                            : 'The institution may need to increase its liquid assets to cover short-term liabilities.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
            <!-- Asset Distribution Chart -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Liquid Assets Distribution</h3>
                <canvas id="assetsChart" class="w-full h-64"></canvas>
            </div>

            <!-- Liquidity Trend Chart -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Liquidity Ratio Trend</h3>
                <canvas id="liquidityChart" class="w-full h-64"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('livewire:load', function () {
            // Assets Distribution Chart
            const assetsCtx = document.getElementById('assetsChart').getContext('2d');
            const assetsChart = new Chart(assetsCtx, {
                type: 'pie',
                data: {
                    labels: ['Cash', 'Bank Deposits', 'Investments'],
                    datasets: [{
                        data: [
                            {{ $totalCash }},
                            {{ $totalBankDeposits }},
                            {{ $totalInvestments }}
                        ],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.5)',
                            'rgba(16, 185, 129, 0.5)',
                            'rgba(245, 158, 11, 0.5)'
                        ],
                        borderColor: [
                            'rgb(59, 130, 246)',
                            'rgb(16, 185, 129)',
                            'rgb(245, 158, 11)'
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
                                    return 'TZS ' + context.raw.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Liquidity Ratio Chart
            const liquidityCtx = document.getElementById('liquidityChart').getContext('2d');
            const liquidityChart = new Chart(liquidityCtx, {
                type: 'line',
                data: {
                    labels: ['Current'],
                    datasets: [{
                        label: 'Liquidity Ratio',
                        data: [{{ $liquidityRatio }}],
                        borderColor: 'rgb(59, 130, 246)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });

            Livewire.on('dataUpdated', () => {
                assetsChart.data.datasets[0].data = [
                    {{ $totalCash }},
                    {{ $totalBankDeposits }},
                    {{ $totalInvestments }}
                ];
                assetsChart.update();

                liquidityChart.data.datasets[0].data = [{{ $liquidityRatio }}];
                liquidityChart.update();
            });
        });
    </script>
    @endpush
</div> 