{{-- Teller Journey Implementation --}}
{{-- Based on docs/cash-management-user-journeys.md --}}

@switch($activeTab)
    {{-- Morning Setup (6:30 AM - 8:00 AM) --}}
    @case('morning-setup')
        <div class="space-y-6">
            {{-- Progress Timeline --}}
            <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-2xl p-6 border border-blue-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Morning Setup Progress</h3>
                <div class="relative">
                    <div class="absolute left-4 top-8 bottom-0 w-0.5 bg-gray-300"></div>
                    
                    @php
                        $setupSteps = [
                            ['id' => 'login', 'label' => 'System Login', 'status' => $morningSetup['login'] ?? 'completed'],
                            ['id' => 'vault_access', 'label' => 'Vault Access Request', 'status' => $morningSetup['vault_access'] ?? 'pending'],
                            ['id' => 'cash_requisition', 'label' => 'Cash Requisition', 'status' => $morningSetup['cash_requisition'] ?? 'pending'],
                            ['id' => 'drawer_prep', 'label' => 'Drawer Preparation', 'status' => $morningSetup['drawer_prep'] ?? 'pending'],
                            ['id' => 'balance_verify', 'label' => 'Balance Verification', 'status' => $morningSetup['balance_verify'] ?? 'pending'],
                            ['id' => 'ready', 'label' => 'Ready for Customers', 'status' => $morningSetup['ready'] ?? 'pending'],
                        ];
                    @endphp
                    
                    <div class="space-y-6 relative">
                        @foreach($setupSteps as $index => $step)
                            <div class="flex items-center">
                                <div class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center
                                    @if($step['status'] === 'completed') bg-green-500
                                    @elseif($step['status'] === 'in_progress') bg-blue-500 animate-pulse
                                    @else bg-gray-300
                                    @endif">
                                    @if($step['status'] === 'completed')
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @elseif($step['status'] === 'in_progress')
                                        <div class="w-3 h-3 bg-white rounded-full"></div>
                                    @else
                                        <span class="text-white text-xs font-bold">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                                <div class="ml-4 flex-1">
                                    <h4 class="font-semibold text-gray-900">{{ $step['label'] }}</h4>
                                    @if($step['status'] === 'in_progress')
                                        <p class="text-sm text-blue-600">In Progress...</p>
                                    @elseif($step['status'] === 'completed')
                                        <p class="text-sm text-green-600">Completed</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            {{-- Till Opening Request Form --}}
            @if($tillStatus === 'closed')
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                        </svg>
                        Request Till Opening
                    </h3>
                    
                    <form wire:submit.prevent="requestTillOpening" class="space-y-4">
                        {{-- Till Selection --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Select Till</label>
                            <select wire:model="selectedTillId" class="w-full rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 p-3">
                                <option value="">Choose a till...</option>
                                @foreach($availableTills as $till)
                                    <option value="{{ $till->id }}">{{ $till->name }} - {{ $till->location }}</option>
                                @endforeach
                            </select>
                            @error('selectedTillId') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Opening Balance Request --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Opening Balance (TZS)</label>
                            <input type="number" wire:model="tillOpeningBalance" step="1000" min="100000" 
                                   class="w-full rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 p-3"
                                   placeholder="Enter requested opening balance">
                            @error('tillOpeningBalance') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Denomination Breakdown --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Denomination Preference</label>
                            <div class="grid grid-cols-3 gap-3">
                                @foreach(['1000', '2000', '5000', '10000', '20000', '50000'] as $denom)
                                    <div class="flex items-center space-x-2">
                                        <label class="text-sm text-gray-600">{{ number_format($denom) }}</label>
                                        <input type="number" wire:model="denominations.{{ $denom }}" min="0" 
                                               class="w-20 rounded border-gray-300 text-sm"
                                               placeholder="0">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        {{-- Submit Button --}}
                        <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-6 rounded-lg hover:from-green-600 hover:to-green-700 font-semibold shadow-lg transition-all duration-200">
                            Request Till Opening
                        </button>
                    </form>
                </div>
            @else
                {{-- Till Already Open Status --}}
                <div class="bg-green-50 border-2 border-green-200 rounded-2xl p-6">
                    <div class="flex items-center">
                        <svg class="w-12 h-12 text-green-600 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="text-lg font-bold text-green-900">Till is Open</h3>
                            <p class="text-green-700">Your till is ready for operations. Current balance: TZS {{ number_format($tillCurrentBalance) }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @break
    
    {{-- Customer Transactions (8:00 AM - 5:00 PM) --}}
    @case('customer-transactions')
        <div class="space-y-6">
            {{-- Queue Management --}}
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Customer Queue</h3>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Average Wait Time:</span>
                        <span class="font-bold text-blue-600">{{ $avgWaitTime ?? '3' }} mins</span>
                    </div>
                </div>
                
                {{-- Queue Display --}}
                <div class="grid grid-cols-5 gap-3">
                    @for($i = 1; $i <= 5; $i++)
                        <div class="bg-white rounded-lg p-3 text-center border-2 
                            @if($i === 1) border-blue-500 shadow-lg @else border-gray-200 @endif">
                            <span class="text-2xl font-bold @if($i === 1) text-blue-600 @else text-gray-400 @endif">
                                {{ str_pad($currentQueueNumber + $i - 1, 3, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                    @endfor
                </div>
                
                {{-- Call Next Customer --}}
                <button wire:click="callNextCustomer" class="mt-4 w-full bg-blue-900 text-white py-3 rounded-lg hover:bg-blue-700 font-semibold">
                    Call Next Customer
                </button>
            </div>
            
            {{-- Transaction Processing Form --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Process Transaction</h3>
                
                <form wire:submit.prevent="processTransaction" class="space-y-4">
                    {{-- Transaction Type --}}
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" wire:click="$set('transactionType', 'deposit')" 
                                class="p-4 rounded-lg border-2 transition-all
                                @if($transactionType === 'deposit') 
                                    border-green-500 bg-green-50 
                                @else 
                                    border-gray-300 hover:border-green-300 
                                @endif">
                            <svg class="w-8 h-8 mx-auto mb-2 @if($transactionType === 'deposit') text-green-600 @else text-gray-400 @endif" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                            </svg>
                            <span class="font-semibold @if($transactionType === 'deposit') text-green-700 @else text-gray-600 @endif">
                                Deposit
                            </span>
                        </button>
                        
                        <button type="button" wire:click="$set('transactionType', 'withdrawal')" 
                                class="p-4 rounded-lg border-2 transition-all
                                @if($transactionType === 'withdrawal') 
                                    border-red-500 bg-red-50 
                                @else 
                                    border-gray-300 hover:border-red-300 
                                @endif">
                            <svg class="w-8 h-8 mx-auto mb-2 @if($transactionType === 'withdrawal') text-red-600 @else text-gray-400 @endif" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                            </svg>
                            <span class="font-semibold @if($transactionType === 'withdrawal') text-red-700 @else text-gray-600 @endif">
                                Withdrawal
                            </span>
                        </button>
                    </div>
                    
                    {{-- Customer Identification --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Member Number</label>
                            <input type="text" wire:model.lazy="memberNumber" wire:blur="fetchMemberDetails"
                                   class="w-full rounded-lg border-2 border-gray-300 focus:border-blue-500 p-3"
                                   placeholder="Enter member number">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Account Number</label>
                            <input type="text" wire:model="accountNumber" 
                                   class="w-full rounded-lg border-2 border-gray-300 focus:border-blue-500 p-3"
                                   placeholder="Enter account number">
                        </div>
                    </div>
                    
                    {{-- Member Details Display --}}
                    @if($memberDetails)
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="grid grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Name:</span>
                                    <span class="font-semibold text-gray-900 ml-2">{{ $memberDetails['name'] }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">ID Type:</span>
                                    <span class="font-semibold text-gray-900 ml-2">{{ $memberDetails['id_type'] }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Balance:</span>
                                    <span class="font-semibold text-green-600 ml-2">TZS {{ number_format($memberDetails['balance']) }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    {{-- Amount Entry --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Amount (TZS)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-gray-500">TZS</span>
                            <input type="number" wire:model="transactionAmount" step="1000" min="1000"
                                   class="w-full rounded-lg border-2 border-gray-300 focus:border-blue-500 p-3 pl-12 text-xl font-bold"
                                   placeholder="0">
                        </div>
                        @error('transactionAmount') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Denomination Display for Withdrawals --}}
                    @if($transactionType === 'withdrawal' && $transactionAmount > 0)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="font-semibold text-gray-700 mb-2">Denomination Breakdown</h4>
                            <div class="grid grid-cols-3 gap-2 text-sm">
                                @foreach($suggestedDenominations as $denom => $count)
                                    @if($count > 0)
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">{{ number_format($denom) }} x</span>
                                            <span class="font-semibold">{{ $count }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    {{-- Reference & Narration --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Transaction Reference</label>
                        <input type="text" wire:model="transactionReference" 
                               class="w-full rounded-lg border-2 border-gray-300 focus:border-blue-500 p-3"
                               placeholder="Optional reference">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Narration</label>
                        <textarea wire:model="transactionNarration" rows="2"
                                  class="w-full rounded-lg border-2 border-gray-300 focus:border-blue-500 p-3"
                                  placeholder="Transaction description"></textarea>
                    </div>
                    
                    {{-- Action Buttons --}}
                    <div class="flex space-x-4">
                        <button type="submit" class="flex-1 bg-gradient-to-r 
                                @if($transactionType === 'deposit') 
                                    from-green-500 to-green-600 hover:from-green-600 hover:to-green-700
                                @else 
                                    from-red-500 to-red-600 hover:from-red-600 hover:to-red-700
                                @endif 
                                text-white py-3 px-6 rounded-lg font-semibold shadow-lg transition-all duration-200">
                            Process {{ ucfirst($transactionType) }}
                        </button>
                        
                        <button type="button" wire:click="resetTransaction" 
                                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">
                            Clear
                        </button>
                    </div>
                </form>
            </div>
            
            {{-- Recent Transactions --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Transactions</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 text-gray-600">Time</th>
                                <th class="text-left py-2 text-gray-600">Type</th>
                                <th class="text-left py-2 text-gray-600">Customer</th>
                                <th class="text-right py-2 text-gray-600">Amount</th>
                                <th class="text-center py-2 text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $transaction)
                                <tr class="border-b border-gray-100">
                                    <td class="py-2 text-gray-900">{{ $transaction->created_at->format('H:i') }}</td>
                                    <td class="py-2">
                                        <span class="px-2 py-1 rounded text-xs font-semibold
                                            @if($transaction->type === 'deposit') bg-green-100 text-green-700
                                            @else bg-red-100 text-red-700
                                            @endif">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td class="py-2 text-gray-900">{{ $transaction->customer_name }}</td>
                                    <td class="py-2 text-right font-semibold text-gray-900">{{ number_format($transaction->amount) }}</td>
                                    <td class="py-2 text-center">
                                        <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-700">
                                            Completed
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @break
    
    {{-- Cash Management (Buy/Sell from Vault) --}}
    @case('cash-management')
        <div class="space-y-6">
            {{-- Current Till Status --}}
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl p-6 border border-blue-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Till Cash Position</h3>
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600 mb-1">Current Balance</p>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($tillCurrentBalance) }}</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600 mb-1">Till Limit</p>
                        <p class="text-2xl font-bold text-gray-700">{{ number_format($tillLimit) }}</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600 mb-1">% Utilized</p>
                        <p class="text-2xl font-bold 
                           @if($tillUtilization > 80) text-red-600 
                           @elseif($tillUtilization > 60) text-yellow-600
                           @else text-green-600
                           @endif">
                            {{ round($tillUtilization) }}%
                        </p>
                    </div>
                    <div class="bg-white rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600 mb-1">Status</p>
                        <p class="text-lg font-bold 
                           @if($tillCurrentBalance > $tillLimit) text-red-600
                           @elseif($tillCurrentBalance < $tillMinimum) text-orange-600
                           @else text-green-600
                           @endif">
                            @if($tillCurrentBalance > $tillLimit)
                                Over Limit
                            @elseif($tillCurrentBalance < $tillMinimum)
                                Low Balance
                            @else
                                Optimal
                            @endif
                        </p>
                    </div>
                </div>
                
                {{-- Denomination Breakdown --}}
                <div class="mt-4 bg-white rounded-lg p-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Current Denominations</h4>
                    <div class="grid grid-cols-6 gap-2 text-sm">
                        @foreach($tillDenominations as $denom => $count)
                            <div class="text-center">
                                <p class="text-gray-600">{{ number_format($denom) }}</p>
                                <p class="font-bold text-gray-900">{{ $count }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            {{-- Buy from Vault --}}
            @if($tillCurrentBalance < $tillLimit * 0.5)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Buy Cash from Vault
                    </h3>
                    
                    <form wire:submit.prevent="buyCashFromVault" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Amount Needed (TZS)</label>
                            <input type="number" wire:model="vaultBuyAmount" step="100000" min="100000"
                                   class="w-full rounded-lg border-2 border-gray-300 focus:border-green-500 p-3"
                                   placeholder="Enter amount to buy">
                            @error('vaultBuyAmount') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Denomination Requirements</label>
                            <div class="grid grid-cols-3 gap-3">
                                @foreach(['1000', '2000', '5000', '10000', '20000', '50000'] as $denom)
                                    <div class="flex items-center space-x-2">
                                        <label class="text-sm text-gray-600">{{ number_format($denom) }}</label>
                                        <input type="number" wire:model="buyDenominations.{{ $denom }}" min="0" 
                                               class="w-20 rounded border-gray-300 text-sm"
                                               placeholder="0">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Reason</label>
                            <textarea wire:model="vaultBuyReason" rows="2"
                                      class="w-full rounded-lg border-2 border-gray-300 focus:border-green-500 p-3"
                                      placeholder="Reason for buying cash"></textarea>
                        </div>
                        
                        <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-6 rounded-lg hover:from-green-600 hover:to-green-700 font-semibold shadow-lg">
                            Request Cash from Vault
                        </button>
                    </form>
                </div>
            @endif
            
            {{-- Sell to Vault --}}
            @if($tillCurrentBalance > $tillLimit * 0.8)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                        Sell Cash to Vault
                    </h3>
                    
                    <form wire:submit.prevent="sellCashToVault" class="space-y-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                            <p class="text-yellow-800 text-sm">
                                <strong>Alert:</strong> Your till balance exceeds {{ round($tillUtilization) }}% of the limit. 
                                Consider transferring excess cash to the vault.
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Amount to Transfer (TZS)</label>
                            <input type="number" wire:model="vaultSellAmount" step="100000" min="100000"
                                   class="w-full rounded-lg border-2 border-gray-300 focus:border-red-500 p-3"
                                   placeholder="Enter amount to sell">
                            @error('vaultSellAmount') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Denominations to Transfer</label>
                            <div class="grid grid-cols-3 gap-3">
                                @foreach($tillDenominations as $denom => $available)
                                    <div class="flex items-center space-x-2">
                                        <label class="text-sm text-gray-600">{{ number_format($denom) }}</label>
                                        <input type="number" wire:model="sellDenominations.{{ $denom }}" min="0" max="{{ $available }}"
                                               class="w-20 rounded border-gray-300 text-sm"
                                               placeholder="0">
                                        <span class="text-xs text-gray-500">({{ $available }})</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white py-3 px-6 rounded-lg hover:from-red-600 hover:to-red-700 font-semibold shadow-lg">
                            Transfer Cash to Vault
                        </button>
                    </form>
                </div>
            @endif
        </div>
        @break
    
    {{-- End of Day Process --}}
    @case('end-of-day')
        <div class="space-y-6">
            {{-- EOD Checklist --}}
            <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-2xl p-6 border border-orange-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4">End of Day Checklist</h3>
                
                <div class="space-y-3">
                    @php
                        $eodChecklist = [
                            ['id' => 'last_customer', 'label' => 'Process last customer', 'completed' => $eodSteps['last_customer'] ?? false],
                            ['id' => 'run_tape', 'label' => 'Run adding machine tape on checks', 'completed' => $eodSteps['run_tape'] ?? false],
                            ['id' => 'count_cash', 'label' => 'Count cash by denomination (twice)', 'completed' => $eodSteps['count_cash'] ?? false],
                            ['id' => 'system_balance', 'label' => 'Enter closing balance in system', 'completed' => $eodSteps['system_balance'] ?? false],
                            ['id' => 'print_report', 'label' => 'Print balancing report', 'completed' => $eodSteps['print_report'] ?? false],
                            ['id' => 'investigate', 'label' => 'Investigate discrepancies', 'completed' => $eodSteps['investigate'] ?? false],
                            ['id' => 'bundle_cash', 'label' => 'Bundle excess cash for vault', 'completed' => $eodSteps['bundle_cash'] ?? false],
                            ['id' => 'vault_deposit', 'label' => 'Deposit to vault', 'completed' => $eodSteps['vault_deposit'] ?? false],
                            ['id' => 'signoff', 'label' => 'Complete signoff', 'completed' => $eodSteps['signoff'] ?? false],
                        ];
                    @endphp
                    
                    @foreach($eodChecklist as $item)
                        <div class="flex items-center p-3 bg-white rounded-lg border border-gray-200">
                            <input type="checkbox" 
                                   wire:model="eodSteps.{{ $item['id'] }}" 
                                   class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                            <label class="ml-3 flex-1 text-gray-700 @if($item['completed']) line-through text-gray-400 @endif">
                                {{ $item['label'] }}
                            </label>
                            @if($item['completed'])
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- Cash Counting Form --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Cash Count & Reconciliation</h3>
                
                <form wire:submit.prevent="submitEndOfDay" class="space-y-4">
                    {{-- Denomination Count --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Count Cash by Denomination</label>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach(['1000', '2000', '5000', '10000', '20000', '50000'] as $denom)
                                <div class="flex items-center space-x-3 bg-gray-50 rounded-lg p-3">
                                    <label class="text-sm text-gray-600 w-20">{{ number_format($denom) }}</label>
                                    <input type="number" wire:model.lazy="eodDenominations.{{ $denom }}" min="0" 
                                           wire:change="calculateEodTotal"
                                           class="flex-1 rounded border-gray-300 text-sm"
                                           placeholder="Count">
                                    <span class="text-sm text-gray-600">= {{ number_format(($eodDenominations[$denom] ?? 0) * $denom) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    {{-- Coins Count --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Coins</label>
                        <div class="grid grid-cols-4 gap-3">
                            @foreach(['50', '100', '200', '500'] as $coin)
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600">{{ $coin }}</label>
                                    <input type="number" wire:model.lazy="eodCoins.{{ $coin }}" min="0" 
                                           wire:change="calculateEodTotal"
                                           class="w-20 rounded border-gray-300 text-sm"
                                           placeholder="0">
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    {{-- Total Calculation --}}
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">System Balance</p>
                                <p class="text-xl font-bold text-gray-900">{{ number_format($systemBalance) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Physical Count</p>
                                <p class="text-xl font-bold text-blue-600">{{ number_format($eodPhysicalCount) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Variance</p>
                                <p class="text-xl font-bold 
                                   @if($eodVariance == 0) text-green-600
                                   @elseif(abs($eodVariance) < 1000) text-yellow-600
                                   @else text-red-600
                                   @endif">
                                    {{ $eodVariance > 0 ? '+' : '' }}{{ number_format($eodVariance) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Variance Explanation --}}
                    @if($eodVariance != 0)
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Variance Explanation <span class="text-red-500">*</span>
                            </label>
                            <textarea wire:model="eodVarianceExplanation" rows="3" required
                                      class="w-full rounded-lg border-2 border-red-300 focus:border-red-500 p-3"
                                      placeholder="Explain the variance..."></textarea>
                            @error('eodVarianceExplanation') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    
                    {{-- Confirmation --}}
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="eodConfirmed" 
                                   class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                            <span class="ml-3 text-sm text-gray-700">
                                I confirm that I have counted the cash twice and the above figures are accurate
                            </span>
                        </label>
                    </div>
                    
                    {{-- Submit Button --}}
                    <button type="submit" 
                            @if(!$eodConfirmed) disabled @endif
                            class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white py-3 px-6 rounded-lg hover:from-red-600 hover:to-red-700 font-semibold shadow-lg transition-all duration-200
                            @if(!$eodConfirmed) opacity-50 cursor-not-allowed @endif">
                        Submit End of Day Report
                    </button>
                </form>
            </div>
            
            {{-- Daily Summary --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Today's Summary</h3>
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">Transaction Summary</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Deposits:</span>
                                <span class="font-semibold text-green-600">{{ number_format($todayDeposits) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Withdrawals:</span>
                                <span class="font-semibold text-red-600">{{ number_format($todayWithdrawals) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Net Movement:</span>
                                <span class="font-semibold text-blue-600">{{ number_format($todayDeposits - $todayWithdrawals) }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t">
                                <span class="text-gray-600">Transaction Count:</span>
                                <span class="font-semibold">{{ $todayTransactionCount }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">Performance Metrics</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Customers Served:</span>
                                <span class="font-semibold">{{ $customersServed }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Avg Service Time:</span>
                                <span class="font-semibold">{{ $avgServiceTime }} mins</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Accuracy Rate:</span>
                                <span class="font-semibold text-green-600">{{ $accuracyRate }}%</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t">
                                <span class="text-gray-600">Rating:</span>
                                <span class="font-semibold text-yellow-600">★★★★☆</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @break
    
    {{-- Default case for other tabs --}}
    @default
        <div class="text-center py-12">
            <svg class="w-20 h-20 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">{{ ucfirst(str_replace('-', ' ', $activeTab)) }}</h3>
            <p class="text-gray-500">This section is under development</p>
        </div>
@endswitch