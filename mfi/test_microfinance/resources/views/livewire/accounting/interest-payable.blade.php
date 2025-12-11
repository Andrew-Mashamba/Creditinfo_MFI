<div>
    @if($this->show_register_modal)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity">
                    <div class="absolute inset-0 bg-gray-500 opacity-0"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div>
                        <div class="max-w-lg mx-auto mt-8 p-4">
                            @if (session()->has('message'))
                                <div class="bg-green-500 text-white p-4 mb-4 rounded">
                                    {{ session('message') }}
                                </div>
                            @endif

                            <h2 class="text-2xl font-bold mb-4">Interest Payable Form</h2>

                            <div class="mb-4">
                                <label for="member_id" class="block text-sm font-medium text-gray-700">Member ID</label>
                                <input type="number" wire:model="member_id" id="member_id" class="mt-1 block w-full border-gray-300 rounded-md p-2">
                                @error('member_id') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="account_type" class="block text-sm font-medium text-gray-700">Account Type</label>
                                <input type="text" wire:model="account_type" id="account_type" class="mt-1 block w-full border-gray-300 rounded-md p-2">
                                @error('account_type') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="category_type" class="block text-sm font-medium text-gray-700">Category Type</label>
                                <select wire:model="category_type" id="category_type" class="mt-1 block w-full border-gray-300 rounded-md p-2">
                                    <option value="Savings">Savings/Deposit</option>
                                    <option value="Loan">Loan</option>
                                </select>
                            </div>

                            {{-- Account Selection - Corrected Flow --}}
                            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Account Selection</h3>
                                <p class="text-sm text-gray-600 mb-4">Select where to create the interest payable account and the other account for double-entry posting</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="parent_account_number" class="block text-sm font-medium text-gray-700 mb-2">
                                            Parent Account (Create Interest Payable Under) *
                                        </label>
                                        <select wire:model="parent_account_number" id="parent_account_number" 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                            <option value="">-- Select Parent Account --</option>
                                            @foreach($parentAccounts as $account)
                                                <option value="{{ $account->account_number }}">
                                                    {{ $account->account_number }} - {{ $account->account_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">New interest payable account will be created under this parent</p>
                                        @error('parent_account_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="other_account_id" class="block text-sm font-medium text-gray-700 mb-2">
                                            Other Account (Interest Expense) *
                                        </label>
                                        <select wire:model="other_account_id" id="other_account_id" 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                            <option value="">-- Select Expense Account --</option>
                                            @foreach($otherAccounts as $account)
                                                <option value="{{ $account->internal_mirror_account_number }}">
                                                    {{ $account->bank_name }} - {{ $account->account_number }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Account to be debited (Interest Expense account)</p>
                                        @error('other_account_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                                <input type="number" wire:model="amount" id="amount" class="mt-1 block w-full border-gray-300 rounded-md p-2" step="0.01">
                                @error('amount') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                            @if($category_type === 'Savings')
                                <div class="mb-4">
                                    <label for="interest_rate" class="block text-sm font-medium text-gray-700">Interest Rate (%)</label>
                                    <input type="number" wire:model="interest_rate" id="interest_rate" class="mt-1 block w-full border-gray-300 rounded-md p-2" step="0.01">
                                    @error('interest_rate') <span class="text-red-500">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="deposit_date" class="block text-sm font-medium text-gray-700">Deposit Date</label>
                                    <input type="date" wire:model="deposit_date" id="deposit_date" class="mt-1 block w-full border-gray-300 rounded-md p-2">
                                    @error('deposit_date') <span class="text-red-500">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="maturity_date" class="block text-sm font-medium text-gray-700">Maturity Date</label>
                                    <input type="date" wire:model="maturity_date" id="maturity_date" class="mt-1 block w-full border-gray-300 rounded-md p-2">
                                    @error('maturity_date') <span class="text-red-500">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            @if($category_type === 'Loan')
                                <div class="mb-4">
                                    <label for="loan_provider" class="block text-sm font-medium text-gray-700">Loan Provider</label>
                                    <input type="text" wire:model="loan_provider" id="loan_provider" class="mt-1 block w-full border-gray-300 rounded-md p-2">
                                    @error('loan_provider') <span class="text-red-500">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="loan_interest_rate" class="block text-sm font-medium text-gray-700">Loan Interest Rate (%)</label>
                                    <input type="number" wire:model="loan_interest_rate" id="loan_interest_rate" class="mt-1 block w-full border-gray-300 rounded-md p-2" step="0.01">
                                    @error('loan_interest_rate') <span class="text-red-500">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="loan_start_date" class="block text-sm font-medium text-gray-700">Loan Start Date</label>
                                    <input type="date" wire:model="loan_start_date" id="loan_start_date" class="mt-1 block w-full border-gray-300 rounded-md p-2">
                                    @error('loan_start_date') <span class="text-red-500">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center bg-gray-200 justify-end py-3 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                        <button type="button" wire:click="$toggle('show_register_modal')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium border border-transparent rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 bg-white">
                            Cancel
                        </button>
                        <button type="submit" wire:click="register" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md focus-visible:ring-2 focus-visible:ring-offset-2">
                            Proceed
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="w-full p-4 bg-white rounded-lg shadow">
        {{-- Header with Add Button and Search --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <button wire:click="registerModal" type="button" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">
                    <svg class="w-4 h-4 me-2" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                    </svg>
                    New Interest
                </button>
            </div>
            
            <div class="flex items-center space-x-4">
                {{-- Search Input --}}
                <div class="relative">
                    <input type="text" 
                           wire:model.debounce.300ms="search" 
                           placeholder="Search..." 
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                {{-- Per Page Selector --}}
                <select wire:model="perPage" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-red-500 focus:border-red-500">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" 
                            wire:click="sortBy('member_id')">
                            <div class="flex items-center">
                                Member ID
                                @if($sortField === 'member_id')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('account_type')">
                            <div class="flex items-center">
                                Account Type
                                @if($sortField === 'account_type')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('amount')">
                            <div class="flex items-center">
                                Amount
                                @if($sortField === 'amount')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest Rate</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest Payable</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Accrued Interest</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Frequency</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deposit Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Maturity Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Provider</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Interest Rate</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Start Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Term</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($interestPayables as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ ($interestPayables->currentPage() - 1) * $interestPayables->perPage() + $index + 1 }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item->member_id }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item->account_type }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->amount ?? 0, 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                @if($item->interest_rate)
                                    {{ $item->interest_rate }}%
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->interest_payable ?? 0, 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->accrued_interest ?? 0, 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item->payment_frequency ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->deposit_date ? \Carbon\Carbon::parse($item->deposit_date)->format('Y-m-d') : '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->maturity_date ? \Carbon\Carbon::parse($item->maturity_date)->format('Y-m-d') : '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item->loan_provider ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                @if($item->loan_interest_rate)
                                    {{ $item->loan_interest_rate }}%
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->loan_start_date ? \Carbon\Carbon::parse($item->loan_start_date)->format('Y-m-d') : '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item->loan_term ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <button wire:click="approveAction({{ $item->id }})" 
                                        class="rounded-full bg-green-100 hover:bg-green-200 p-2 w-8 h-8 inline-flex items-center justify-center cursor-pointer">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="px-4 py-8 text-center text-gray-500">
                                No interest payable records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($interestPayables->hasPages())
            <div class="mt-4">
                {{ $interestPayables->links() }}
            </div>
        @endif
    </div>
</div>