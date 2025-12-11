<div>
    <div class="p-6 bg-white rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6">Deposits and Loans in Banks and Financial Institutions</h2>

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

        <!-- Cash Balances Table -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Cash Balances</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">As of Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($cashBalances as $balance)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $balance['name'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                TZS {{ number_format($balance['balance'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($balance['date'])->format('M d, Y') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-blue-800">Total Cash Balance</h3>
                <p class="text-2xl font-bold text-blue-600">TZS {{ number_format(array_sum(array_column($cashBalances, 'balance')), 2) }}</p>
            </div>
        </div>

        <!-- Chart -->
        <div class="mt-8">
            <h3 class="text-lg font-semibold mb-4">Cash Balances Distribution</h3>
            <canvas id="cashBalancesChart" class="w-full h-64"></canvas>
        </div>
    </div>

    @push('scripts')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('livewire:load', function () {
            // Cash Balances Chart
            const ctx = document.getElementById('cashBalancesChart').getContext('2d');
            const data = @json($cashBalances);
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(item => item.name),
                    datasets: [{
                        label: 'Cash Balance',
                        data: data.map(item => item.balance),
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgb(59, 130, 246)',
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
                                    return 'TZS ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            Livewire.on('dataUpdated', () => {
                // Update chart when data changes
                location.reload();
            });
        });
    </script>
    @endpush
</div> 