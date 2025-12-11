<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Luku Payment</h2>

        <!-- Include Enhanced Error Handler Component -->
        @include('livewire.payments.error-handler')

        @if(!$showPaymentForm)
            <!-- Meter Lookup Form -->
            <form wire:submit.prevent="lookupMeter" class="space-y-4" wire:loading.class="opacity-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="meterNumber" class="block text-sm font-medium text-gray-700">Meter Number</label>
                        <input type="text" wire:model.defer="meterNumber" id="meterNumber"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               wire:keydown.enter="$emit('logUserInteraction', 'Meter number input', { meterNumber: $event.target.value })">
                        @error('meterNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="debitAccountNo" class="block text-sm font-medium text-gray-700">Debit Account Number</label>
                        <input type="text" wire:model.defer="debitAccountNo" id="debitAccountNo"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               wire:keydown.enter="$emit('logUserInteraction', 'Debit account input', { debitAccountNo: $event.target.value })">
                        @error('debitAccountNo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-blue-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-900 focus:bg-blue-900 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            wire:loading.attr="disabled"
                            wire:click="$emit('logUserInteraction', 'Lookup meter button clicked', { meterNumber: '{{ $meterNumber }}', debitAccountNo: '{{ $debitAccountNo }}' })">
                        <span wire:loading.remove wire:target="lookupMeter">Lookup Meter</span>
                        <span wire:loading wire:target="lookupMeter">Processing...</span>
                    </button>
                </div>
            </form>
        @else
            <!-- Lookup Results and Payment Form -->
            <div class="space-y-6">
                <!-- Lookup Results -->
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Meter Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Meter Number</p>
                            <p class="font-medium text-gray-900">{{ is_array($lookupResponse['meter']) ? json_encode($lookupResponse['meter']) : ($lookupResponse['meter'] ?? 'N/A') }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600">Owner Name</p>
                            <p class="font-medium text-gray-900">{{ is_array($lookupResponse['owner']) ? json_encode($lookupResponse['owner']) : ($lookupResponse['owner'] ?? 'N/A') }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">Reference</p>
                            <p class="font-medium text-gray-900">{{ is_array($lookupResponse['reference']) ? json_encode($lookupResponse['reference']) : ($lookupResponse['reference'] ?? 'N/A') }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="font-medium text-gray-900">{{ is_array($lookupResponse['statusDescription']) ? json_encode($lookupResponse['statusDescription']) : ($lookupResponse['statusDescription'] ?? 'N/A') }}</p>
                        </div>
                    </div>

                    @if(!empty($lookupResponse['debts']))
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Expected Deductions</h4>
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($lookupResponse['debts'] as $debt)
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ is_array($debt['DebtType']) ? json_encode($debt['DebtType']) : ($debt['DebtType'] ?? 'N/A') }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ is_array($debt['DebtName']) ? json_encode($debt['DebtName']) : ($debt['DebtName'] ?? 'N/A') }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ is_array($debt['DebtRate']) ? json_encode($debt['DebtRate']) : ($debt['DebtRate'] ?? 'N/A') }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ is_array($debt['DebtBalance']) ? json_encode($debt['DebtBalance']) : (number_format($debt['DebtBalance'] ?? 0, 2)) }} TZS</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Payment Form -->
                <form wire:submit.prevent="processPayment" class="space-y-4" wire:loading.class="opacity-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Amount (TZS)</label>
                            <input type="number" wire:model.defer="amount" id="amount"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   wire:keydown.enter="$emit('logUserInteraction', 'Amount input', { amount: $event.target.value })">
                            @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="customerName" class="block text-sm font-medium text-gray-700">Customer Name</label>
                            <input type="text" wire:model.defer="customerName" id="customerName"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   wire:keydown.enter="$emit('logUserInteraction', 'Customer name input', { customerName: $event.target.value })">
                            @error('customerName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="customerMsisdn" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="text" wire:model.defer="customerMsisdn" id="customerMsisdn"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   wire:keydown.enter="$emit('logUserInteraction', 'Phone number input', { customerMsisdn: $event.target.value })">
                            @error('customerMsisdn') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="customerEmail" class="block text-sm font-medium text-gray-700">Email (Optional)</label>
                            <input type="email" wire:model.defer="customerEmail" id="customerEmail"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   wire:keydown.enter="$emit('logUserInteraction', 'Email input', { customerEmail: $event.target.value })">
                            @error('customerEmail') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-4">
                        <button type="button" 
                                class="text-sm text-gray-600 hover:text-gray-900"
                                wire:click="$set('showPaymentForm', false)"
                                wire:click="$emit('logUserInteraction', 'Back to lookup button clicked')">
                            Back to Lookup
                        </button>

                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                wire:loading.attr="disabled"
                                wire:click="$emit('logUserInteraction', 'Pay now button clicked', { amount: '{{ $amount }}', customerName: '{{ $customerName }}', customerMsisdn: '{{ $customerMsisdn }}', customerEmail: '{{ $customerEmail }}' })">
                            <span wire:loading.remove wire:target="processPayment">Pay Now</span>
                            <span wire:loading wire:target="processPayment">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    document.addEventListener('livewire:load', function () {
        Livewire.on('logUserInteraction', (action, data = {}) => {
            console.log('User Interaction:', {
                action,
                data,
                timestamp: new Date().toISOString()
            });
        });
    });
</script>
@endpush 