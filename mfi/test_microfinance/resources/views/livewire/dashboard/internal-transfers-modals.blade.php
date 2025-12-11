{{-- Internal Transfers Modals --}}

<!-- New Transfer Modal -->
@if($showTransferModal)
<div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">New Internal Transfer</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Member Verification Section -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-lg font-semibold text-gray-800 mb-3">Member Verification</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Membership Number</label>
                        <div class="flex gap-2">
                            <input wire:model="membershipNumber" type="text" 
                                   class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                   placeholder="Enter membership number">
                            <button wire:click="verifyMembership" 
                                    class="px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Verify
                            </button>
                        </div>
                        @error('membershipNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                @if($verifiedMember)
                <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-green-800 font-medium">{{ $verifiedMember['name'] ?? 'Member Verified' }}</span>
                    </div>
                </div>
                @endif
            </div>

            @if($verifiedMember)
            <!-- Transfer Details -->
            <form wire:submit.prevent="submitTransfer">
                <div class="space-y-6">
                    <!-- Transfer Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Transfer Type</label>
                        <select wire:model="transferType" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="member_to_member">Member to Member</option>
                            <option value="member_to_bank">Member to Bank</option>
                        </select>
                        @error('transferType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Source Account -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Source Account</label>
                        <select wire:model="fromAccount" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="">Choose source account</option>
                            @foreach($memberAccounts as $account)
                                <option value="{{ $account->account_number }}">
                                    {{ $account->account_name }} - TZS {{ number_format($account->balance, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('fromAccount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        
                        @if($fromAccountBalance > 0)
                        <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded">
                            <span class="text-red-800 text-sm font-medium">Available Balance: TZS {{ number_format($fromAccountBalance, 2) }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Destination Account -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            @if($transferType === 'member_to_bank')
                                Destination Bank
                            @else
                                Destination Account
                            @endif
                        </label>
                        
                        @if($transferType === 'member_to_bank')
                            <select wire:model="toAccount" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Choose destination bank</option>
                                @foreach($bankAccounts as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->bank_name }}</option>
                                @endforeach
                            </select>
                        @else
                            <input wire:model="toAccount" type="text" 
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                   placeholder="Enter destination account number">
                        @endif
                        @error('toAccount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        
                        @if($toAccountBalance > 0 && $transferType === 'member_to_member')
                        <div class="mt-2 p-2 bg-green-50 border border-green-200 rounded">
                            <span class="text-green-800 text-sm font-medium">Destination Balance: TZS {{ number_format($toAccountBalance, 2) }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Transfer Amount</label>
                        <input wire:model="amount" type="number" step="0.01" min="0.01"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Enter amount">
                        @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Narration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Transfer Description</label>
                        <textarea wire:model="narration" rows="3"
                                  class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Enter transfer description"></textarea>
                        @error('narration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Date and Reference -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Transfer Date</label>
                            <input wire:model="transferDate" type="date"
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('transferDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                            <input wire:model="referenceNumber" type="text" readonly
                                   class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50">
                            @error('referenceNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Transfer Summary -->
                    @if($amount && $fromAccount && $toAccount)
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <h5 class="font-medium text-gray-800 mb-3">Transfer Summary</h5>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">From:</span>
                                <span class="font-medium">{{ $fromAccount }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">To:</span>
                                <span class="font-medium">{{ $toAccount }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Amount:</span>
                                <span class="font-medium text-red-600">TZS {{ number_format($amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Type:</span>
                                <span class="font-medium">{{ ucwords(str_replace('_', ' ', $transferType)) }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                    <button type="button" wire:click="closeModal" 
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="submitTransfer">Process Transfer</span>
                        <span wire:loading wire:target="submitTransfer">Processing...</span>
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Transfer History Modal -->
@if($showTransferHistoryModal)
<div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-6xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Transfer History</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Transfer History Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transferHistory as $transfer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $transfer->reference_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $transfer->transfer_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $transfer->transfer_type === 'member_to_member' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucwords(str_replace('_', ' ', $transfer->transfer_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $transfer->fromAccount->account_name ?? 'N/A' }}
                                <div class="text-xs text-gray-400">{{ $transfer->fromAccount->account_number ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $transfer->toAccount->account_name ?? 'N/A' }}
                                <div class="text-xs text-gray-400">{{ $transfer->toAccount->account_number ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                TZS {{ number_format($transfer->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $transfer->status === 'posted' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($transfer->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $transfer->creator->name ?? 'System' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-900 mb-2">No transfers found</p>
                                    <p class="text-gray-500">No internal transfers have been processed yet.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end mt-6 pt-6 border-t border-gray-200">
                <button wire:click="closeModal" 
                        class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif
