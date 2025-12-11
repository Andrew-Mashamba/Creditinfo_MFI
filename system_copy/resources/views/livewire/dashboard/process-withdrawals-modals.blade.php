{{-- Process Withdrawals Modals --}}

<!-- Withdraw Deposits Modal -->
@if($showWithdrawDepositsModal)
<div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Withdraw Deposits</h3>
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
            <!-- Transaction Details -->
            <form wire:submit.prevent="submitWithdrawDeposits">
                <div class="space-y-6">
                    <!-- Account Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Account</label>
                        <select wire:model="selectedAccount" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="">Choose an account</option>
                            @foreach($memberAccounts as $account)
                                <option value="{{ $account->account_number }}">
                                    {{ $account->account_name }} - TZS {{ number_format($account->balance, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('selectedAccount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        
                        @if($selectedAccountBalance > 0)
                        <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded">
                            <span class="text-blue-800 text-sm font-medium">Available Balance: TZS {{ number_format($selectedAccountBalance, 2) }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Withdrawal Amount</label>
                        <input wire:model="amount" type="number" step="0.01" min="0.01"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Enter amount">
                        @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select wire:model="paymentMethod" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="internal_transfer">Internal Transfer</option>
                        </select>
                        @error('paymentMethod') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Bank Selection (if bank transfer) -->
                    @if($paymentMethod === 'bank')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Bank</label>
                        <select wire:model="selectedBank" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="">Choose a bank</option>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }}</option>
                            @endforeach
                        </select>
                        @error('selectedBank') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <!-- OTP Section (for cash withdrawals) -->
                    @if($paymentMethod === 'cash')
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <h5 class="font-medium text-yellow-800 mb-3">OTP Verification Required</h5>
                        <div class="flex gap-2">
                            <button type="button" wire:click="sendOTP" 
                                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm">
                                Send OTP
                            </button>
                            @if($otpSent)
                            <input wire:model="otpCode" type="text" maxlength="6"
                                   class="flex-1 p-2 border border-yellow-300 rounded-lg focus:ring-2 focus:ring-yellow-500"
                                   placeholder="Enter 6-digit OTP">
                            <button type="button" wire:click="verifyOTP" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                                Verify
                            </button>
                            @endif
                        </div>
                        @if($otpVerified)
                        <div class="mt-2 flex items-center text-green-600">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm">OTP Verified</span>
                        </div>
                        @endif
                        @error('otpCode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <!-- Withdrawer Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Withdrawer Name</label>
                        <input wire:model="withdrawerName" type="text"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Enter withdrawer's full name">
                        @error('withdrawerName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Narration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea wire:model="narration" rows="3"
                                  class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Enter withdrawal description"></textarea>
                        @error('narration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Date and Time -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Withdrawal Date</label>
                            <input wire:model="withdrawDate" type="date"
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('withdrawDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Withdrawal Time</label>
                            <input wire:model="withdrawTime" type="time"
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('withdrawTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Reference Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                        <input wire:model="referenceNumber" type="text" readonly
                               class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50">
                        @error('referenceNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                    <button type="button" wire:click="closeModal" 
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="submitWithdrawDeposits">Process Withdrawal</span>
                        <span wire:loading wire:target="submitWithdrawDeposits">Processing...</span>
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Withdraw Savings Modal -->
@if($showWithdrawSavingsModal)
<div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Withdraw Savings</h3>
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
                                   class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="Enter membership number">
                            <button wire:click="verifyMembership" 
                                    class="px-4 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500">
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
            <!-- Transaction Details -->
            <form wire:submit.prevent="submitWithdrawSavings">
                <div class="space-y-6">
                    <!-- Account Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Account</label>
                        <select wire:model="selectedAccount" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="">Choose an account</option>
                            @foreach($memberAccounts as $account)
                                <option value="{{ $account->account_number }}">
                                    {{ $account->account_name }} - TZS {{ number_format($account->balance, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('selectedAccount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        
                        @if($selectedAccountBalance > 0)
                        <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded">
                            <span class="text-blue-800 text-sm font-medium">Available Balance: TZS {{ number_format($selectedAccountBalance, 2) }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Withdrawal Amount</label>
                        <input wire:model="amount" type="number" step="0.01" min="0.01"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                               placeholder="Enter amount">
                        @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select wire:model="paymentMethod" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="internal_transfer">Internal Transfer</option>
                        </select>
                        @error('paymentMethod') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Bank Selection (if bank transfer) -->
                    @if($paymentMethod === 'bank')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Bank</label>
                        <select wire:model="selectedBank" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="">Choose a bank</option>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }}</option>
                            @endforeach
                        </select>
                        @error('selectedBank') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <!-- OTP Section (for cash withdrawals) -->
                    @if($paymentMethod === 'cash')
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <h5 class="font-medium text-yellow-800 mb-3">OTP Verification Required</h5>
                        <div class="flex gap-2">
                            <button type="button" wire:click="sendOTP" 
                                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm">
                                Send OTP
                            </button>
                            @if($otpSent)
                            <input wire:model="otpCode" type="text" maxlength="6"
                                   class="flex-1 p-2 border border-yellow-300 rounded-lg focus:ring-2 focus:ring-yellow-500"
                                   placeholder="Enter 6-digit OTP">
                            <button type="button" wire:click="verifyOTP" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                                Verify
                            </button>
                            @endif
                        </div>
                        @if($otpVerified)
                        <div class="mt-2 flex items-center text-green-600">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm">OTP Verified</span>
                        </div>
                        @endif
                        @error('otpCode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <!-- Withdrawer Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Withdrawer Name</label>
                        <input wire:model="withdrawerName" type="text"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                               placeholder="Enter withdrawer's full name">
                        @error('withdrawerName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Narration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea wire:model="narration" rows="3"
                                  class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                  placeholder="Enter withdrawal description"></textarea>
                        @error('narration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Date and Time -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Withdrawal Date</label>
                            <input wire:model="withdrawDate" type="date"
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            @error('withdrawDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Withdrawal Time</label>
                            <input wire:model="withdrawTime" type="time"
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            @error('withdrawTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Reference Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                        <input wire:model="referenceNumber" type="text" readonly
                               class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50">
                        @error('referenceNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                    <button type="button" wire:click="closeModal" 
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="submitWithdrawSavings">Process Withdrawal</span>
                        <span wire:loading wire:target="submitWithdrawSavings">Processing...</span>
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endif
