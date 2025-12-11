{{-- View Member Modal --}}
<div class="fixed inset-0 z-50 bg-black bg-opacity-30 overflow-y-auto">
    <div class="min-h-full flex items-start justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-8xl shadow-lg mt-8 mb-8">
        <div class="flex flex-col md:flex-row gap-0">
            {{-- Profile and Basic Info --}}
            <div class="md:w-1/3 bg-gray-50 flex flex-col items-center justify-center p-8">
                <div class="w-32 h-32 mb-4">
                    @if($member->profile_photo)
                        <img src="{{ Storage::url($member->profile_photo) }}" alt="Profile" class="rounded-full w-full h-full object-cover border-4 border-white">
                    @else
                        <div class="rounded-full w-full h-full bg-gray-200 flex items-center justify-center text-4xl text-gray-400">
                            {{ strtoupper(substr($member->first_name,0,1).substr($member->last_name,0,1)) }}
                        </div>
                    @endif
                </div>
                <div class="text-center">
                    <div class="text-xl font-bold text-gray-900">{{ $member->first_name }} {{ $member->last_name }}</div>
                    <div class="text-gray-500 text-sm mt-1">{{ $member->client_number }}</div>
                    <div class="text-gray-400 text-xs mt-1">{{ $member->membership_type }}</div>
                </div>
            </div>
            {{-- Details --}}
            <div class="md:w-2/3 p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <div class="font-semibold text-gray-700 mb-2">Personal Information</div>
                        <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-sm">
                            <div class="text-gray-500">DOB</div><div>{{ $member->date_of_birth }}</div>
                            <div class="text-gray-500">Gender</div><div>{{ $member->gender }}</div>
                            <div class="text-gray-500">Marital</div><div>{{ $member->marital_status }}</div>
                            <div class="text-gray-500">Nationality</div><div>{{ $member->nationality }}</div>
                        </div>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-700 mb-2">Contact</div>
                        <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-sm">
                            <div class="text-gray-500">Phone</div><div>{{ $member->phone_number }}</div>
                            <div class="text-gray-500">Email</div><div>{{ $member->email }}</div>
                            <div class="text-gray-500">Address</div><div>{{ $member->address }}</div>
                        </div>
                    </div>
                    <button wire:click="showAllData" class="px-2 py-1 text-xs rounded bg-gray-700 text-white hover:bg-gray-800 ml-auto">Show All Data</button>
                </div>


                <div class="mb-6">
                    <div class="font-semibold text-gray-700 mb-2">Accounts</div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @forelse($member->accounts as $account)
                            <div class="bg-gray-100 rounded-lg p-4">
                            @if($account->parentAccount)
                                    <div class="text-xs text-gray-800 font-bold mt-1">{{ $account->parentAccount->account_name }}</div>
                                @endif
                                <div class="text-xs text-gray-500 mb-1">{{ $account->account_name }}</div>
                                <div class="text-sm text-gray-600 mb-1">#{{ $account->account_number }}</div>
                                <div class="text-lg font-bold text-gray-900">{{ number_format($account->balance, 2) }}</div>
                                
                                @if($account->locked_amount > 0)
                                    <div class="text-xs text-red-600 mt-1">Locked: {{ number_format($account->locked_amount, 2) }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="col-span-3 text-center text-gray-500 py-4">No accounts found</div>
                        @endforelse
                    </div>
                </div>

                <div class="mb-6">
                    <div class="font-semibold text-gray-700 mb-2">Member Revenue</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Dividends Card -->
                        <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm font-medium text-green-800">Dividends</div>
                                <div class="text-xs text-green-600 bg-green-200 px-2 py-1 rounded">Annual</div>
                            </div>
                            <div class="text-2xl font-bold text-green-900 mb-1">
                                {{ number_format($member->dividends->sum('amount'), 2) }}
                            </div>
                            <div class="text-xs text-green-700">
                                @php
                                    $pendingDividends = $member->dividends->where('status', 'PENDING')->sum('amount');
                                    $paidDividends = $member->dividends->where('status', 'PAID')->sum('amount');
                                @endphp
                                <span class="font-medium">Pending:</span> {{ number_format($pendingDividends, 2) }} | 
                                <span class="font-medium">Paid:</span> {{ number_format($paidDividends, 2) }}
                            </div>
                        </div>

                        <!-- Interest on Savings Card -->
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm font-medium text-blue-800">Interest on Savings</div>
                                <div class="text-xs text-blue-600 bg-blue-200 px-2 py-1 rounded">Accrued</div>
                            </div>
                            <div class="text-2xl font-bold text-blue-900 mb-1">
                                {{ number_format($member->interestPayables->sum('interest_payable'), 2) }}
                            </div>
                            <div class="text-xs text-blue-700">
                                @php
                                    $totalSavingsBalance = $member->accounts->where('account_name', 'like', '%SAVINGS%')->sum('balance');
                                    $totalDepositsBalance = $member->accounts->where('account_name', 'like', '%DEPOSITS%')->sum('balance');
                                @endphp
                                <span class="font-medium">Savings:</span> {{ number_format($totalSavingsBalance, 2) }} | 
                                <span class="font-medium">Deposits:</span> {{ number_format($totalDepositsBalance, 2) }}
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="mb-6">
                    <div class="font-semibold text-gray-700 mb-2">Ongoing Loans</div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-gray-500 text-xs">
                                    <th class="py-1 px-2 text-left">Loan #</th>
                                    <th class="py-1 px-2 text-left">Product</th>
                                    <th class="py-1 px-2 text-left">Amount</th>
                                    <th class="py-1 px-2 text-left">Balance</th>
                                    <th class="py-1 px-2 text-left">Status</th>
                                    <th class="py-1 px-2 text-left">Days In Arrears</th>
                                    <th class="py-1 px-2 text-left">Amount In Arrears</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($member->loans as $loan)
                                    <tr>
                                        <td class="py-1 px-2">{{ $loan->loan_id }}</td>
                                        <td class="py-1 px-2">{{ $loan->loanProduct->sub_product_name ?? 'N/A' }}</td>
                                        <td class="py-1 px-2">{{ number_format($loan->principle,2) }}</td>
                                        <td class="py-1 px-2">{{ number_format($loan->loanAccount->balance ?? 0,2) }}</td>
                                        <td class="py-1 px-2">
                                            <span class="inline-block rounded px-2 py-0.5 text-xs {{ $loan->status==='active'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ $loan->status }}</span>
                                        </td>
                                        <td class="py-1 px-2">{{ $loan->max_days_in_arrears }}</td>
                                        <td class="py-1 px-2">{{ number_format($loan->total_amount_in_arrears, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-gray-400 py-2">No active loans</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>
                    <div class="font-semibold text-gray-700 mb-2">Available Control Numbers</div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-gray-500 text-xs">
                                    <th class="py-1 px-2 text-left">Control #</th>
                                    <th class="py-1 px-2 text-left">Service</th>
                                    <th class="py-1 px-2 text-left">Amount</th>
                                    <th class="py-1 px-2 text-left">Due Date</th>
                                    <th class="py-1 px-2 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($member->bills as $bill)
                                    <tr>
                                        <td class="py-1 px-2">{{ $bill->control_number }}</td>
                                        <td class="py-1 px-2">{{ $bill->service->name ?? 'N/A' }}</td>
                                        <td class="py-1 px-2">{{ number_format($bill->amount_due,2) }}</td>
                                        <td class="py-1 px-2">{{ $bill->due_date }}</td>
                                        <td class="py-1 px-2">
                                            <span class="inline-block rounded px-2 py-0.5 text-xs {{ $bill->status==='PAID'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ $bill->status }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-gray-400 py-2">No control numbers</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Close Button - Fixed at bottom -->
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 p-4 rounded-b-2xl">
            <div class="flex justify-end">
                <button wire:click="closeModal" class="px-4 py-2 rounded bg-gray-700 text-white hover:bg-gray-800">Close</button>
            </div>
        </div>
    </div>
</div> 