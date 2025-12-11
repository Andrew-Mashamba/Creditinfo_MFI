<div>
    <!-- Collection Documentation Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900">Collection Efforts Documentation</h3>
            <button wire:click="$set('showCollectionModal', true)" 
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Collection Effort
            </button>
        </div>

        <!-- Collection Statistics -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-lg p-4">
                <p class="text-sm text-red-600 font-medium">Total Efforts</p>
                <p class="text-2xl font-bold text-red-800">
                    {{ \App\Models\LoanCollectionEffort::count() }}
                </p>
                <p class="text-xs text-red-600 mt-1">All time</p>
            </div>
            
            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4">
                <p class="text-sm text-green-600 font-medium">Success Rate</p>
                <p class="text-2xl font-bold text-green-800">
                    {{ \App\Models\LoanCollectionEffort::whereIn('outcome', ['promise_to_pay', 'payment_made'])->count() * 100 / max(1, \App\Models\LoanCollectionEffort::count()) }}%
                </p>
                <p class="text-xs text-green-600 mt-1">Successful outcomes</p>
            </div>
            
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4">
                <p class="text-sm text-purple-600 font-medium">Avg Cost/Effort</p>
                <p class="text-2xl font-bold text-purple-800">
                    TZS {{ number_format(\App\Models\LoanCollectionEffort::avg('cost_incurred') ?? 0, 0) }}
                </p>
                <p class="text-xs text-purple-600 mt-1">Average cost</p>
            </div>
            
            <div class="bg-gradient-to-r from-amber-50 to-amber-100 rounded-lg p-4">
                <p class="text-sm text-amber-600 font-medium">Active Promises</p>
                <p class="text-2xl font-bold text-amber-800">
                    {{ \App\Models\LoanCollectionEffort::where('outcome', 'promise_to_pay')
                        ->where('promised_payment_date', '>=', now())->count() }}
                </p>
                <p class="text-xs text-amber-600 mt-1">Pending payment</p>
            </div>
        </div>

        <!-- Collection Efforts Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outcome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Promise</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cost</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach(\App\Models\LoanCollectionEffort::with(['loan', 'staff'])
                        ->latest('effort_date')
                        ->take(10)
                        ->get() as $effort)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $effort->effort_date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $effort->loan_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $effort->effort_type_badge['class'] }}">
                                {{ $effort->effort_type_badge['text'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $effort->outcome_badge['class'] }}">
                                {{ $effort->outcome_badge['text'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($effort->promised_payment_date)
                                {{ $effort->promised_payment_date->format('d/m/Y') }}
                                <br>
                                <span class="text-xs">TZS {{ number_format($effort->promised_amount, 0) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $effort->staff->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                            {{ number_format($effort->cost_incurred, 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <button wire:click="viewEffortDetails({{ $effort->id }})" 
                                    class="text-indigo-600 hover:text-indigo-900 text-sm">
                                View
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Staff Performance Analysis -->
        <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Staff Performance Analysis</h4>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h5 class="text-sm font-medium text-gray-700 mb-3">Top Performers</h5>
                    <div class="space-y-2">
                        @foreach(\App\Models\LoanCollectionEffort::getEffortivenessByStaff() as $staff)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ $staff->staff->name ?? 'Staff ' . $staff->staff_id }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500">{{ $staff->total_efforts }} efforts</span>
                                <span class="text-sm font-medium text-green-600">{{ $staff->success_rate }}%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h5 class="text-sm font-medium text-gray-700 mb-3">Effort Types Effectiveness</h5>
                    <canvas id="effortTypesChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Collection Effort Modal -->
    @if($showCollectionModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showCollectionModal', false)"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Record Collection Effort</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Loan ID</label>
                            <input type="text" wire:model="collectionLoanId" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Effort Type</label>
                            <select wire:model="effortType" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="call">Phone Call</option>
                                <option value="sms">SMS</option>
                                <option value="email">Email</option>
                                <option value="visit">Physical Visit</option>
                                <option value="letter">Written Letter</option>
                                <option value="legal_notice">Legal Notice</option>
                                <option value="court_summons">Court Summons</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Outcome</label>
                            <select wire:model="effortOutcome" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="promise_to_pay">Promise to Pay</option>
                                <option value="payment_made">Payment Made</option>
                                <option value="dispute">Dispute Raised</option>
                                <option value="no_response">No Response</option>
                                <option value="unreachable">Unreachable</option>
                                <option value="partial_payment">Partial Payment</option>
                                <option value="request_extension">Extension Requested</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cost Incurred</label>
                            <input type="number" wire:model="costIncurred" step="0.01"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        
                        @if($effortOutcome === 'promise_to_pay')
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Promised Date</label>
                            <input type="date" wire:model="promisedDate" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Promised Amount</label>
                            <input type="number" wire:model="promisedAmount" step="0.01"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        @endif
                        
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Effort Description</label>
                            <textarea wire:model="effortDescription" rows="3"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                      placeholder="Describe the collection effort..."></textarea>
                            @error('effortDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Client Response</label>
                            <textarea wire:model="clientResponse" rows="2"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                      placeholder="Client's response or feedback..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="processCollectionEffort" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Record Effort
                    </button>
                    <button wire:click="$set('showCollectionModal', false)" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script nonce="{{ csp_nonce() }}">
    // Effort Types Effectiveness Chart
    const effortTypesCtx = document.getElementById('effortTypesChart');
    if (effortTypesCtx) {
        new Chart(effortTypesCtx, {
            type: 'bar',
            data: {
                labels: ['Call', 'SMS', 'Email', 'Visit', 'Letter', 'Legal'],
                datasets: [{
                    label: 'Success Rate %',
                    data: [65, 45, 30, 80, 40, 70],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(156, 163, 175, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    }
</script>
@endpush