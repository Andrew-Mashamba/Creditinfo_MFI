<div class="bg-white rounded-lg shadow-sm">
    <!-- Header Section -->
    <div class="border-b border-gray-200 px-6 py-4">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Loan Loss Reserve Management</h2>
                <p class="text-sm text-gray-600 mt-1">Manage provisions for potential loan losses in compliance with IFRS 9</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Reporting Period</p>
                <p class="text-lg font-semibold">{{ $currentYear }} - Q{{ ceil($currentMonth / 3) }}</p>
            </div>
        </div>
    </div>
    
    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200 px-6">
        <nav class="-mb-px flex space-x-8">
            <button wire:click="changeViewMode('dashboard')" 
                    class="py-2 px-1 border-b-2 {{ $viewMode === 'dashboard' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} font-medium text-sm">
                Dashboard
            </button>
            <button wire:click="changeViewMode('provision')" 
                    class="py-2 px-1 border-b-2 {{ $viewMode === 'provision' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} font-medium text-sm">
                Make Provision
            </button>
            <button wire:click="changeViewMode('writeoff')" 
                    class="py-2 px-1 border-b-2 {{ $viewMode === 'writeoff' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} font-medium text-sm">
                Write-offs
            </button>
            <button wire:click="changeViewMode('history')" 
                    class="py-2 px-1 border-b-2 {{ $viewMode === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} font-medium text-sm">
                History
            </button>
        </nav>
    </div>
    
    <!-- Main Content Area -->
    <div class="p-6">
        @if($viewMode === 'dashboard')
            <!-- Dashboard View -->
            <div class="space-y-6">
                <!-- Key Metrics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-blue-600 font-medium">Loan Portfolio</p>
                                <p class="text-2xl font-bold text-blue-900">{{ number_format($loanPortfolioValue, 2) }}</p>
                                <p class="text-xs text-blue-600 mt-1">TZS</p>
                            </div>
                            <svg class="w-10 h-10 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-green-600 font-medium">Current Reserve</p>
                                <p class="text-2xl font-bold text-green-900">{{ number_format($currentReserveBalance, 2) }}</p>
                                <p class="text-xs text-green-600 mt-1">{{ number_format($stats['coverage_ratio'] ?? 0, 2) }}% Coverage</p>
                            </div>
                            <svg class="w-10 h-10 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-yellow-600 font-medium">Required Reserve</p>
                                <p class="text-2xl font-bold text-yellow-900">{{ number_format($requiredReserve, 2) }}</p>
                                <p class="text-xs text-yellow-600 mt-1">Based on aging</p>
                            </div>
                            <svg class="w-10 h-10 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="{{ $provisionGap > 0 ? 'bg-red-50' : 'bg-gray-50' }} rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm {{ $provisionGap > 0 ? 'text-red-600' : 'text-gray-600' }} font-medium">Provision Gap</p>
                                <p class="text-2xl font-bold {{ $provisionGap > 0 ? 'text-red-900' : 'text-gray-900' }}">{{ number_format($provisionGap, 2) }}</p>
                                <p class="text-xs {{ $provisionGap > 0 ? 'text-red-600' : 'text-gray-600' }} mt-1">{{ $provisionGap > 0 ? 'Under-provisioned' : 'Adequate' }}</p>
                            </div>
                            <svg class="w-10 h-10 {{ $provisionGap > 0 ? 'text-red-300' : 'text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Loan Aging Analysis Table -->
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Loan Portfolio Aging Analysis</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-b border-gray-200">Category</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-b border-gray-200">Days Overdue</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-b border-gray-200">Count</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider border-b border-gray-200">Outstanding Amount</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-b border-gray-200">Provision Rate</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider border-b border-gray-200">Required Provision</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach(['current' => 'Current/Performing', 'watch' => 'Watch List', 'substandard' => 'Substandard', 'doubtful' => 'Doubtful', 'loss' => 'Loss'] as $key => $label)
                                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $label }}</td>
                                        <td class="px-4 py-3 text-sm text-center text-gray-600">
                                            @if($key === 'current') 0-30
                                            @elseif($key === 'watch') 31-60
                                            @elseif($key === 'substandard') 61-90
                                            @elseif($key === 'doubtful') 91-180
                                            @else >180
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center">{{ $loanAging[$key]['count'] ?? 0 }}</td>
                                        <td class="px-4 py-3 text-sm text-right">{{ number_format($loanAging[$key]['amount'] ?? 0, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-center">{{ $loanAging[$key]['provision_rate'] ?? 0 }}%</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium">{{ number_format($loanAging[$key]['required_provision'] ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-100 font-bold">
                                    <td class="px-4 py-3 text-sm border-t-2 border-gray-300">TOTAL</td>
                                    <td class="px-4 py-3 text-sm text-center border-t-2 border-gray-300">-</td>
                                    <td class="px-4 py-3 text-sm text-center border-t-2 border-gray-300">
                                        {{ array_sum(array_column($loanAging, 'count')) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right border-t-2 border-gray-300">{{ number_format($loanPortfolioValue, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-center border-t-2 border-gray-300">-</td>
                                    <td class="px-4 py-3 text-sm text-right text-blue-600 border-t-2 border-gray-300">{{ number_format($requiredReserve, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
        @elseif($viewMode === 'provision')
            <!-- Make Provision View -->
            <div class="max-w-3xl mx-auto">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Create Loan Loss Provision</h3>
                    
                    <div class="space-y-4">
                        <!-- Calculation Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Calculation Method</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" 
                                        wire:click="$set('percentage', 5)"
                                        class="px-4 py-2 border {{ $percentage == 5 ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300' }} rounded-lg text-sm font-medium">
                                    Automatic (Based on Aging)
                                </button>
                                <button type="button"
                                        class="px-4 py-2 border {{ $percentage != 5 ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300' }} rounded-lg text-sm font-medium">
                                    Manual (% of Profits)
                                </button>
                            </div>
                        </div>
                        
                        <!-- Provision Details -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Profits (TZS)</label>
                                <input type="number" 
                                       wire:model="profits" 
                                       wire:change="calculateLLR"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Percentage (%)</label>
                                <input type="number" 
                                       wire:model="percentage" 
                                       wire:change="calculateLLR"
                                       min="0" max="100" step="0.5"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                        </div>
                        
                        <!-- Reserve Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reserve Amount (TZS)</label>
                            <input type="text" 
                                   wire:model="reserve_amount" 
                                   readonly
                                   class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-md shadow-sm sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">
                                @if($provisionGap > 0)
                                    Recommended minimum provision: {{ number_format($provisionGap, 2) }} TZS
                                @else
                                    Current reserve is adequate
                                @endif
                            </p>
                        </div>
                        
                        <!-- Source Account -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Source of Funds</label>
                            <select wire:model="source" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                <option value="">Select Account</option>
                                @php
                                    // Get cash or income accounts for funding source
                                    $sourceAccounts = DB::table('accounts')
                                        ->whereIn('major_category_code', ['1000', '4000'])
                                        ->where('account_type', '!=', 'LOAN')
                                        ->orderBy('account_name')
                                        ->get();
                                @endphp
                                @foreach($sourceAccounts as $account)
                                    <option value="{{ $account->account_number }}">
                                        {{ $account->account_name }} ({{ $account->account_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Summary Box -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-blue-900 mb-2">Provision Summary</h4>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-blue-700">Current Reserve Balance:</span>
                                    <span class="font-medium text-blue-900">{{ number_format($currentReserveBalance, 2) }} TZS</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-blue-700">New Provision Amount:</span>
                                    <span class="font-medium text-blue-900">{{ number_format($reserve_amount, 2) }} TZS</span>
                                </div>
                                <div class="flex justify-between border-t border-blue-200 pt-1 mt-1">
                                    <span class="text-blue-700 font-medium">New Reserve Balance:</span>
                                    <span class="font-bold text-blue-900">{{ number_format($currentReserveBalance + $reserve_amount, 2) }} TZS</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" 
                                    wire:click="resetForm"
                                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="button" 
                                    wire:click="makeProvision"
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-900 hover:bg-blue-700">
                                Record Provision
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
        @elseif($viewMode === 'writeoff')
            <!-- Write-off View -->
            <div class="space-y-4">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-yellow-800">
                            Write-offs will reduce the loan loss reserve balance. Ensure proper authorization before proceeding.
                        </p>
                    </div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Loans Eligible for Write-off</h3>
                    </div>
                    
                    @if(isset($writeOffCandidates) && count($writeOffCandidates) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-3 text-left">
                                            <input type="checkbox" class="rounded border-gray-300">
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Loan Account</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Client</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Balance</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Days in Arrears</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Amount in Arrears</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Due Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($writeOffCandidates as $loan)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <input type="checkbox" 
                                                       wire:model="selectedLoans" 
                                                       value="{{ $loan->id }}"
                                                       class="rounded border-gray-300">
                                            </td>
                                            <td class="px-4 py-3 text-sm">{{ $loan->loan_account_number }}</td>
                                            <td class="px-4 py-3 text-sm">{{ $loan->client_number }}</td>
                                            <td class="px-4 py-3 text-sm text-right">{{ number_format($loan->balance, 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-center">
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                    {{ $loan->days_in_arrears ?? 0 }} days
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right text-red-600 font-medium">
                                                {{ number_format($loan->amount_in_arrears ?? 0, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-center">
                                                {{ $loan->installment_date ? \Carbon\Carbon::parse($loan->installment_date)->format('d/m/Y') : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="p-4 border-t border-gray-200">
                            <div class="max-w-xl">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Write-off Reason</label>
                                <textarea wire:model="writeOffReason" 
                                          rows="3"
                                          class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm"
                                          placeholder="Provide justification for write-off..."></textarea>
                            </div>
                            
                            <div class="mt-4 flex justify-end">
                                <button wire:click="processWriteOff" 
                                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                                    Process Write-off
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="p-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="mt-2">No loans currently eligible for write-off</p>
                            <p class="text-sm mt-1">Loans must be overdue by more than 180 days</p>
                        </div>
                    @endif
                </div>
            </div>
            
        @else
            <!-- History View -->
            <div class="space-y-4">
                <!-- Provision History -->
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Provision History</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Transaction ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Description</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($provisionHistory as $provision)
                                    <tr>
                                        <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($provision->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $provision->transaction_id ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $provision->description }}</td>
                                        <td class="px-4 py-3 text-sm text-right">{{ number_format($provision->credit, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-3 text-center text-gray-500">No provision history available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Write-off History -->
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Write-off History</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Transaction ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Description</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($writeOffHistory as $writeOff)
                                    <tr>
                                        <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($writeOff->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $writeOff->transaction_id ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $writeOff->description }}</td>
                                        <td class="px-4 py-3 text-sm text-right">{{ number_format($writeOff->debit, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-3 text-center text-gray-500">No write-off history available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Year-End Adjustment Section -->
    @if($viewMode === 'dashboard')
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Year-End Finalization</h4>
                    <p class="text-sm text-gray-600">Compare actual losses with provisions</p>
                </div>
                <div class="flex items-center space-x-3">
                    <input type="number" 
                           wire:model="actualLoanLosses" 
                           placeholder="Actual losses (TZS)"
                           class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <button wire:click="finalizeYearEnd" 
                            class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-900">
                        Finalize Year
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Notification Messages -->
@if (session()->has('message'))
    <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
        {{ session('message') }}
    </div>
@endif

@if (session()->has('error'))
    <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
        {{ session('error') }}
    </div>
@endif