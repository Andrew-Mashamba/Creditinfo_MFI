{{-- Receive Deposits Modal --}}
@if($showReceiveDepositsModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form wire:submit.prevent="submitReceiveDeposits">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Receive Deposits</h3>
                        <p class="mt-1 text-sm text-gray-500">Process member deposit transactions</p>
                    </div>

                    {{-- Membership Number Verification --}}
                    <div class="mb-4">
                        <label for="membershipNumber" class="block text-sm font-medium text-gray-700">Membership Number</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" wire:model.defer="membershipNumber" id="membershipNumber" class="focus:ring-blue-500 focus:border-blue-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300" placeholder="Enter membership number, phone, or name">
                            <button type="button" wire:click="verifyMembership" wire:loading.attr="disabled" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-900 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                <span wire:loading.remove wire:target="verifyMembership">Verify</span>
                                <span wire:loading wire:target="verifyMembership">Verifying...</span>
                            </button>
                        </div>
                        @error('membershipNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    @if($verifiedMember)
                        {{-- Member Details --}}
                        <div class="mb-4 p-4 bg-blue-50 rounded-md border border-blue-200">
                            <h4 class="text-sm font-medium text-blue-900">Member Details</h4>
                            <p class="mt-1 text-sm text-blue-800 font-medium">{{ $verifiedMember['name'] }}</p>
                            <p class="mt-1 text-sm text-blue-700">Membership Type: {{ $verifiedMember['membership_type'] }}</p>
                            <p class="mt-1 text-sm text-blue-700">Client Number: {{ $verifiedMember['client_number'] }}</p>
                        </div>

                        {{-- Account Selection --}}
                        <div class="mb-4">
                            <label for="selectedAccount" class="block text-sm font-medium text-gray-700">Select Account</label>
                            <select wire:model.defer="selectedAccount" id="selectedAccount" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">Select an account</option>
                                @foreach($memberAccounts as $account)
                                    <option value="{{ $account->account_number }}">{{ $account->account_name }} ({{ $account->account_number }}) - Balance: {{ number_format($account->balance, 2) }}</option>
                                @endforeach
                            </select>
                            @error('selectedAccount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Amount --}}
                        <div class="mb-4">
                            <label for="amount" class="block text-sm font-medium text-gray-700">Amount (TZS)</label>
                            <div class="mt-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">TZS</span>
                                </div>
                                <input type="number" step="0.01" wire:model.defer="amount" id="amount" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-12 shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="0.00">
                            </div>
                            @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Payment Method --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <div class="mt-2 space-y-4">
                                <div class="flex items-center">
                                    <input name="paymentMethod" type="radio" wire:model="paymentMethod" value="cash" id="cash" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    <label for="cash" class="ml-3 block text-sm font-medium text-gray-700">Cash</label>
                                </div>
                                <div class="flex items-center">
                                    <input name="paymentMethod" type="radio" wire:model="paymentMethod" value="bank" id="bank" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    <label for="bank" class="ml-3 block text-sm font-medium text-gray-700">Bank Deposit</label>
                                </div>
                            </div>
                            @error('paymentMethod') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Bank Details Section (conditional) --}}
                        @if($paymentMethod === 'bank')
                            {{-- Bank Selection --}}
                            <div class="mb-4">
                                <label for="selectedBank" class="block text-sm font-medium text-gray-700">Select Bank</label>
                                <select wire:model.defer="selectedBank" id="selectedBank" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="">Select a bank</option>
                                    @foreach($bankAccounts as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedBank') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            @if($selectedBankDetails)
                                {{-- Bank Account Details --}}
                                <div class="mb-4 p-4 bg-gray-50 rounded-md border border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-900">Bank Account Details</h4>
                                    <p class="mt-1 text-sm text-gray-600">Bank: {{ $selectedBankDetails->bank_name }}</p>
                                    <p class="mt-1 text-sm text-gray-600">Account Name: {{ $selectedBankDetails->account_name }}</p>
                                    <p class="mt-1 text-sm text-gray-600">Account Number: {{ $selectedBankDetails->account_number }}</p>
                                    @if($selectedBankDetails->branch_name)
                                        <p class="mt-1 text-sm text-gray-600">Branch: {{ $selectedBankDetails->branch_name }}</p>
                                    @endif
                                </div>
                            @endif

                            {{-- Reference Number --}}
                            <div class="mb-4">
                                <label for="referenceNumber" class="block text-sm font-medium text-gray-700">Reference Number</label>
                                <div class="mt-1">
                                    <input type="text" wire:model.defer="referenceNumber" id="referenceNumber" class="focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter reference number">
                                </div>
                                @error('referenceNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Deposit Date and Time --}}
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="depositDate" class="block text-sm font-medium text-gray-700">Deposit Date</label>
                                    <div class="mt-1">
                                        <input type="date" wire:model.defer="depositDate" id="depositDate" class="focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    @error('depositDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="depositTime" class="block text-sm font-medium text-gray-700">Deposit Time</label>
                                    <div class="mt-1">
                                        <input type="time" wire:model.defer="depositTime" id="depositTime" class="focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    @error('depositTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif

                        {{-- Depositor Name --}}
                        <div class="mb-4">
                            <label for="depositorName" class="block text-sm font-medium text-gray-700">Name of Depositor</label>
                            <div class="mt-1">
                                <input type="text" wire:model.defer="depositorName" id="depositorName" class="focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter depositor name">
                            </div>
                            @error('depositorName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Narration --}}
                        <div class="mb-4">
                            <label for="narration" class="block text-sm font-medium text-gray-700">Narration</label>
                            <div class="mt-1">
                                <textarea wire:model.defer="narration" id="narration" rows="3" class="focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter transaction narration (optional)"></textarea>
                            </div>
                            @error('narration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span wire:loading.remove wire:target="submitReceiveDeposits">Submit</span>
                        <span wire:loading wire:target="submitReceiveDeposits">Processing...</span>
                    </button>
                    <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Receive Savings Modal --}}
@if($showReceiveSavingsModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form wire:submit.prevent="submitReceiveSavings">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Receive Savings</h3>
                        <p class="mt-1 text-sm text-gray-500">Process member savings transactions</p>
                    </div>

                    {{-- Membership Number Verification --}}
                    <div class="mb-4">
                        <label for="savingsMembershipNumber" class="block text-sm font-medium text-gray-700">Membership Number</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" wire:model.defer="membershipNumber" id="savingsMembershipNumber" class="focus:ring-green-500 focus:border-green-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300" placeholder="Enter membership number, phone, or name">
                            <button type="button" wire:click="verifyMembership" wire:loading.attr="disabled" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                <span wire:loading.remove wire:target="verifyMembership">Verify</span>
                                <span wire:loading wire:target="verifyMembership">Verifying...</span>
                            </button>
                        </div>
                        @error('membershipNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    @if($verifiedMember)
                        {{-- Member Details --}}
                        <div class="mb-4 p-4 bg-green-50 rounded-md border border-green-200">
                            <h4 class="text-sm font-medium text-green-900">Member Details</h4>
                            <p class="mt-1 text-sm text-green-800 font-medium">{{ $verifiedMember['name'] }}</p>
                            <p class="mt-1 text-sm text-green-700">Membership Type: {{ $verifiedMember['membership_type'] }}</p>
                            <p class="mt-1 text-sm text-green-700">Client Number: {{ $verifiedMember['client_number'] }}</p>
                        </div>

                        {{-- Account Selection --}}
                        <div class="mb-4">
                            <label for="savingsSelectedAccount" class="block text-sm font-medium text-gray-700">Select Account</label>
                            <select wire:model.defer="selectedAccount" id="savingsSelectedAccount" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                                <option value="">Select an account</option>
                                @foreach($memberAccounts as $account)
                                    <option value="{{ $account->account_number }}">{{ $account->account_name }} ({{ $account->account_number }}) - Balance: {{ number_format($account->balance, 2) }}</option>
                                @endforeach
                            </select>
                            @error('selectedAccount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Amount --}}
                        <div class="mb-4">
                            <label for="savingsAmount" class="block text-sm font-medium text-gray-700">Amount (TZS)</label>
                            <div class="mt-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">TZS</span>
                                </div>
                                <input type="number" step="0.01" wire:model.defer="amount" id="savingsAmount" class="focus:ring-green-500 focus:border-green-500 block w-full pl-12 shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="0.00">
                            </div>
                            @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Payment Method --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <div class="mt-2 space-y-4">
                                <div class="flex items-center">
                                    <input name="savingsPaymentMethod" type="radio" wire:model="paymentMethod" value="cash" id="savingsCash" class="focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300">
                                    <label for="savingsCash" class="ml-3 block text-sm font-medium text-gray-700">Cash</label>
                                </div>
                                <div class="flex items-center">
                                    <input name="savingsPaymentMethod" type="radio" wire:model="paymentMethod" value="bank" id="savingsBank" class="focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300">
                                    <label for="savingsBank" class="ml-3 block text-sm font-medium text-gray-700">Bank Deposit</label>
                                </div>
                            </div>
                            @error('paymentMethod') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Bank Details Section (conditional) --}}
                        @if($paymentMethod === 'bank')
                            {{-- Bank Selection --}}
                            <div class="mb-4">
                                <label for="savingsSelectedBank" class="block text-sm font-medium text-gray-700">Select Bank</label>
                                <select wire:model.defer="selectedBank" id="savingsSelectedBank" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                                    <option value="">Select a bank</option>
                                    @foreach($bankAccounts as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedBank') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            @if($selectedBankDetails)
                                {{-- Bank Account Details --}}
                                <div class="mb-4 p-4 bg-gray-50 rounded-md border border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-900">Bank Account Details</h4>
                                    <p class="mt-1 text-sm text-gray-600">Bank: {{ $selectedBankDetails->bank_name }}</p>
                                    <p class="mt-1 text-sm text-gray-600">Account Name: {{ $selectedBankDetails->account_name }}</p>
                                    <p class="mt-1 text-sm text-gray-600">Account Number: {{ $selectedBankDetails->account_number }}</p>
                                    @if($selectedBankDetails->branch_name)
                                        <p class="mt-1 text-sm text-gray-600">Branch: {{ $selectedBankDetails->branch_name }}</p>
                                    @endif
                                </div>
                            @endif

                            {{-- Reference Number --}}
                            <div class="mb-4">
                                <label for="savingsReferenceNumber" class="block text-sm font-medium text-gray-700">Reference Number</label>
                                <div class="mt-1">
                                    <input type="text" wire:model.defer="referenceNumber" id="savingsReferenceNumber" class="focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter reference number">
                                </div>
                                @error('referenceNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Deposit Date and Time --}}
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="savingsDepositDate" class="block text-sm font-medium text-gray-700">Deposit Date</label>
                                    <div class="mt-1">
                                        <input type="date" wire:model.defer="depositDate" id="savingsDepositDate" class="focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    @error('depositDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="savingsDepositTime" class="block text-sm font-medium text-gray-700">Deposit Time</label>
                                    <div class="mt-1">
                                        <input type="time" wire:model.defer="depositTime" id="savingsDepositTime" class="focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    @error('depositTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif

                        {{-- Depositor Name --}}
                        <div class="mb-4">
                            <label for="savingsDepositorName" class="block text-sm font-medium text-gray-700">Name of Depositor</label>
                            <div class="mt-1">
                                <input type="text" wire:model.defer="depositorName" id="savingsDepositorName" class="focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter depositor name">
                            </div>
                            @error('depositorName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Narration --}}
                        <div class="mb-4">
                            <label for="savingsNarration" class="block text-sm font-medium text-gray-700">Narration</label>
                            <div class="mt-1">
                                <textarea wire:model.defer="narration" id="savingsNarration" rows="3" class="focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter transaction narration (optional)"></textarea>
                            </div>
                            @error('narration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span wire:loading.remove wire:target="submitReceiveSavings">Submit</span>
                        <span wire:loading wire:target="submitReceiveSavings">Processing...</span>
                    </button>
                    <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif 