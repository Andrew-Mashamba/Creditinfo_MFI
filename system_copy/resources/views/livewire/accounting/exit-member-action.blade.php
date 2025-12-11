<div class="p-6">
    <!-- Flash Messages -->
    @if(session()->has('message'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if($member)
        <!-- Member Status Badge -->
        @if($member->status === 'EXIT')
            <div class="mb-6 bg-gray-100 border border-gray-300 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <div class="text-lg font-semibold text-gray-800">Member Already Exited</div>
                        <div class="text-sm text-gray-600">Exit Date: {{ $member->exit_date ? \Carbon\Carbon::parse($member->exit_date)->format('M d, Y H:i') : 'N/A' }}</div>
                    </div>
                </div>
            </div>
        @endif

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Member Exit Calculation</h2>
            <p class="text-gray-600">Member: <span class="font-semibold">{{ $member->first_name }} {{ $member->last_name }}</span> ({{ $member->client_number }})</p>
        </div>

        <!-- Exit Amount Summary Card -->
        <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-6 border border-purple-200 mb-6">
            <div class="text-center">
                <div class="text-sm text-purple-700 mb-2">Final Exit Amount</div>
                <div class="text-4xl font-bold text-purple-900">
                    {{ number_format($exitCalculation['exit_amount'] ?? 0, 2) }}
                </div>
                <div class="mt-3">
                    @if(($exitCalculation['exit_type'] ?? '') === 'refund')
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-800 border border-green-300">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" />
                            </svg>
                            Refund Exit - Member will receive this amount
                        </span>
                    @elseif(($exitCalculation['exit_type'] ?? '') === 'settlement')
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-orange-100 text-orange-800 border border-orange-300">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" />
                            </svg>
                            Settlement Exit - Member owes this amount
                        </span>
                    @else
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-blue-100 text-blue-800 border border-blue-300">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Clean Exit - No settlement needed
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Calculation Breakdown -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Credits Section -->
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <h3 class="text-lg font-semibold text-green-800 mb-4">Credits (+)</h3>
                
                <!-- Dividends -->
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                        <div>
                            <div class="text-sm font-medium text-green-800">Dividends</div>
                            <div class="text-xs text-green-600">{{ $exitCalculation['dividends_count'] ?? 0 }} records</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-green-900">{{ number_format($exitCalculation['total_dividends'] ?? 0, 2) }}</div>
                    </div>
                </div>

                <!-- Interest on Savings -->
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                        <div>
                            <div class="text-sm font-medium text-green-800">Interest on Savings</div>
                            <div class="text-xs text-green-600">{{ $exitCalculation['interest_records_count'] ?? 0 }} records</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-green-900">{{ number_format($exitCalculation['total_interest'] ?? 0, 2) }}</div>
                    </div>
                </div>

                <!-- Accounts Balance -->
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                        <div>
                            <div class="text-sm font-medium text-green-800">Accounts Balance</div>
                            <div class="text-xs text-green-600">{{ $exitCalculation['accounts_count'] ?? 0 }} accounts</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-green-900">{{ number_format($exitCalculation['total_accounts_balance'] ?? 0, 2) }}</div>
                    </div>
                </div>

                <!-- Total Credits -->
                <div class="border-t border-green-300 pt-3 mt-3">
                    <div class="flex justify-between items-center">
                        <div class="text-sm font-semibold text-green-800">Total Credits</div>
                        <div class="text-lg font-bold text-green-900">
                            {{ number_format(($exitCalculation['total_dividends'] ?? 0) + ($exitCalculation['total_interest'] ?? 0) + ($exitCalculation['total_accounts_balance'] ?? 0), 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Debits Section -->
            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                <h3 class="text-lg font-semibold text-red-800 mb-4">Debits (-)</h3>
                
                <!-- Loan Balance -->
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                        <div>
                            <div class="text-sm font-medium text-red-800">Loan Account Balance</div>
                            <div class="text-xs text-red-600">{{ $exitCalculation['loans_count'] ?? 0 }} loans</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-red-900">{{ number_format($exitCalculation['total_loan_balance'] ?? 0, 2) }}</div>
                    </div>
                </div>

                <!-- Unpaid Bills -->
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                        <div>
                            <div class="text-sm font-medium text-red-800">Unpaid Control Numbers</div>
                            <div class="text-xs text-red-600">{{ $exitCalculation['unpaid_bills_count'] ?? 0 }} bills</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-red-900">{{ number_format($exitCalculation['total_unpaid_bills'] ?? 0, 2) }}</div>
                    </div>
                </div>

                <!-- Total Debits -->
                <div class="border-t border-red-300 pt-3 mt-3">
                    <div class="flex justify-between items-center">
                        <div class="text-sm font-semibold text-red-800">Total Debits</div>
                        <div class="text-lg font-bold text-red-900">
                            {{ number_format(($exitCalculation['total_loan_balance'] ?? 0) + ($exitCalculation['total_unpaid_bills'] ?? 0), 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formula Display -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Calculation Formula</h3>
            <div class="text-sm text-gray-700 font-mono">
                Exit Amount = Dividends + Interest on Savings + Accounts Balance - Loan Balance - Unpaid Bills
            </div>
            <div class="text-sm text-gray-600 mt-2">
                {{ number_format($exitCalculation['total_dividends'] ?? 0, 2) }} + 
                {{ number_format($exitCalculation['total_interest'] ?? 0, 2) }} + 
                {{ number_format($exitCalculation['total_accounts_balance'] ?? 0, 2) }} - 
                {{ number_format($exitCalculation['total_loan_balance'] ?? 0, 2) }} - 
                {{ number_format($exitCalculation['total_unpaid_bills'] ?? 0, 2) }} = 
                <span class="font-bold">{{ number_format($exitCalculation['exit_amount'] ?? 0, 2) }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <!-- Exit Member Button - Show for all exit types if member not already exited -->
                @if($member->status !== 'EXIT')
                    <button type="button" wire:click="initiateExit"
                        class="hoverable text-white bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center shadow-lg hover:shadow-xl transition-all"
                        onclick="return confirm('Are you sure you want to initiate exit for this member?')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        Initiate Member Exit
                    </button>
                @endif

                <button type="button" wire:click="download()" class="hoverable text-white bg-blue-900 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Download Exit Document
                </button>
            </div>

            <div class="text-sm text-gray-500">
                Calculated on: {{ now()->format('M d, Y H:i:s') }}
            </div>
        </div>

        <!-- Settlement Modal -->
        @if($showSettlementModal)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="p-6">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-2xl font-bold text-gray-900">
                                @if(($exitCalculation['exit_type'] ?? '') === 'refund')
                                    Process Member Exit Refund
                                @else
                                    Process Member Exit Settlement
                                @endif
                            </h3>
                            <button wire:click="cancelSettlement" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Amount Display -->
                        <div class="mb-6 p-4 rounded-lg {{ ($exitCalculation['exit_type'] ?? '') === 'refund' ? 'bg-green-50 border border-green-200' : 'bg-orange-50 border border-orange-200' }}">
                            <div class="text-center">
                                <div class="text-sm {{ ($exitCalculation['exit_type'] ?? '') === 'refund' ? 'text-green-700' : 'text-orange-700' }} mb-2">
                                    {{ ($exitCalculation['exit_type'] ?? '') === 'refund' ? 'Refund Amount' : 'Settlement Amount' }}
                                </div>
                                <div class="text-3xl font-bold {{ ($exitCalculation['exit_type'] ?? '') === 'refund' ? 'text-green-900' : 'text-orange-900' }}">
                                    {{ number_format(abs($exitCalculation['exit_amount'] ?? 0), 2) }} TZS
                                </div>
                            </div>
                        </div>

                        <!-- Settlement Method -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Settlement Method</label>
                            <select wire:model="settlementMethod" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                @if(($exitCalculation['exit_type'] ?? '') === 'refund')
                                    <option value="cash">Cash Payment</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="internal_transfer">Internal Transfer</option>
                                @else
                                    <option value="cash">Cash Collection</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="offset">Offset Against Assets</option>
                                    <option value="write_off">Write-off (Requires Approval)</option>
                                @endif
                            </select>
                            @error('settlementMethod') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Settlement Notes -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes/Reason</label>
                            <textarea wire:model="settlementNotes" rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Provide details about the exit and settlement..."></textarea>
                            @error('settlementNotes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end space-x-3">
                            <button type="button" wire:click="cancelSettlement"
                                class="px-5 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium rounded-lg text-sm">
                                Cancel
                            </button>
                            @if(($exitCalculation['exit_type'] ?? '') === 'refund')
                                <button type="button" wire:click="processRefundExit"
                                    class="px-5 py-2.5 text-white bg-green-600 hover:bg-green-700 font-medium rounded-lg text-sm">
                                    Process Refund & Exit Member
                                </button>
                            @else
                                <button type="button" wire:click="processSettlementExit"
                                    class="px-5 py-2.5 text-white bg-orange-600 hover:bg-orange-700 font-medium rounded-lg text-sm">
                                    Process Settlement & Exit Member
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-8">
            <div class="text-gray-500 text-lg">No member selected for exit calculation</div>
            <div class="text-gray-400 text-sm mt-2">Please select a member to view their exit calculation</div>
        </div>
    @endif
</div>
