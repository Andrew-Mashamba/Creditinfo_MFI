<div>
    <!-- Recovery Tracking Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Recovery Tracking</h3>
            <button wire:click="$set('showRecoveryModal', true)" 
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Record Recovery
            </button>
        </div>

        <!-- Recovery Statistics Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4">
                <p class="text-sm text-green-600 font-medium">Total Recovered</p>
                <p class="text-2xl font-bold text-green-800">
                    TZS {{ number_format($writeoffSummary['total_recovered'] ?? 0, 2) }}
                </p>
                <p class="text-xs text-green-600 mt-1">This period</p>
            </div>
            
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4">
                <p class="text-sm text-blue-600 font-medium">Recovery Rate</p>
                <p class="text-2xl font-bold text-blue-800">
                    {{ $writeoffSummary['recovery_rate'] ?? 0 }}%
                </p>
                <p class="text-xs text-blue-600 mt-1">Average</p>
            </div>
            
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4">
                <p class="text-sm text-purple-600 font-medium">Active Recoveries</p>
                <p class="text-2xl font-bold text-purple-800">
                    {{ \App\Models\LoanWriteoffRecovery::where('status', 'pending')->count() }}
                </p>
                <p class="text-xs text-purple-600 mt-1">In progress</p>
            </div>
            
            <div class="bg-gradient-to-r from-amber-50 to-amber-100 rounded-lg p-4">
                <p class="text-sm text-amber-600 font-medium">Recovery Methods</p>
                <p class="text-2xl font-bold text-amber-800">
                    {{ \App\Models\LoanWriteoffRecovery::distinct('recovery_method')->count('recovery_method') }}
                </p>
                <p class="text-xs text-amber-600 mt-1">Methods used</p>
            </div>
        </div>

        <!-- Recovery Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse(\App\Models\LoanWriteoffRecovery::with(['writeOff', 'recorder'])
                        ->latest('recovery_date')
                        ->take(10)
                        ->get() as $recovery)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $recovery->recovery_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $recovery->loan_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                TZS {{ number_format($recovery->recovery_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $recovery->recovery_method_badge['class'] }}">
                                    {{ $recovery->recovery_method_badge['text'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ ucfirst($recovery->recovery_source ?? 'N/A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $recovery->status_badge['class'] }}">
                                    {{ $recovery->status_badge['text'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($recovery->status === 'pending')
                                    <button wire:click="approveRecovery({{ $recovery->id }})" 
                                            class="text-green-600 hover:text-green-900 mr-2">
                                        Approve
                                    </button>
                                    <button wire:click="rejectRecovery({{ $recovery->id }})" 
                                            class="text-red-600 hover:text-red-900">
                                        Reject
                                    </button>
                                @else
                                    <button wire:click="viewRecoveryDetails({{ $recovery->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900">
                                        View
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No recovery records found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recovery Modal -->
    @if($showRecoveryModal && $selectedWriteOff)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showRecoveryModal', false)"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Record Recovery</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recovery Amount</label>
                            <input type="number" wire:model="recoveryAmount" step="0.01"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('recoveryAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recovery Method</label>
                            <select wire:model="recoveryMethod" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="cash">Cash Payment</option>
                                <option value="collateral_sale">Collateral Sale</option>
                                <option value="legal_settlement">Legal Settlement</option>
                                <option value="insurance_claim">Insurance Claim</option>
                                <option value="debt_forgiveness">Debt Forgiveness</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recovery Source</label>
                            <select wire:model="recoverySource" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="client">Client</option>
                                <option value="guarantor">Guarantor</option>
                                <option value="collateral">Collateral</option>
                                <option value="legal">Legal Process</option>
                                <option value="insurance">Insurance</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recovery Date</label>
                            <input type="date" wire:model="recoveryDate" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Recovery Description</label>
                            <textarea wire:model="recoveryDescription" rows="3"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                      placeholder="Provide details about the recovery..."></textarea>
                            @error('recoveryDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="processRecovery" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Record Recovery
                    </button>
                    <button wire:click="$set('showRecoveryModal', false)" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recovery Analytics Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recovery Analytics</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recovery by Method Chart -->
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-2">Recovery by Method</h4>
                <canvas id="recoveryByMethodChart" height="200"></canvas>
            </div>
            
            <!-- Recovery Trend Chart -->
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-2">Recovery Trend</h4>
                <canvas id="recoveryTrendChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script nonce="{{ csp_nonce() }}">
    // Recovery by Method Chart
    const recoveryMethodCtx = document.getElementById('recoveryByMethodChart');
    if (recoveryMethodCtx) {
        new Chart(recoveryMethodCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cash', 'Collateral Sale', 'Legal Settlement', 'Insurance', 'Other'],
                datasets: [{
                    data: [30, 25, 20, 15, 10],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(156, 163, 175, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Recovery Trend Chart
    const recoveryTrendCtx = document.getElementById('recoveryTrendChart');
    if (recoveryTrendCtx) {
        new Chart(recoveryTrendCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Recovery Amount',
                    data: [1200000, 1500000, 1800000, 1600000, 2000000, 2200000],
                    borderColor: 'rgba(34, 197, 94, 1)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
</script>
@endpush