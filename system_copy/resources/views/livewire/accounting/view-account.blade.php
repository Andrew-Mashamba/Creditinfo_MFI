<div class="h-full w-full bg-blue-25">
    <div class="bg-white px-6 py-10">

        <!-- Header Section -->
        <div class="bg-gray-50 rounded-lg shadow-sm p-6 mb-8">
            <div class="flex items-center justify-between">
                <div class="text-gray-600">
                    @foreach ($accounts as $account)
                    <div class="space-y-2 text-md">
                        <div class="flex items-center space-x-4">
                            <p class="font-medium">Date:</p>
                            <p>{{ now()->format('Y-M-d') }}</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <p class="font-medium">Branch:</p>
                            <p>{{ DB::table('branches')->where('id', $account->branch_number)->value('name') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="bg-white rounded-lg mb-8">            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($accounts as $account)
                <div class="space-y-3">
                    <div class="bg-gray-50 p-4 shadow-sm rounded-lg">
                        <h3 class="text-sm font-medium text-gray-800 mb-3">Account Information</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Account Name:</span>
                                <span class="font-medium text-gray-600">{{ $account->account_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Account Number:</span>
                                <span class="font-medium text-gray-600">{{ $account->account_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Start Date:</span>
                                <span class="font-medium text-gray-600">{{ $account->created_at->format('Y-M-d') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Category:</span>
                                <span class="font-medium text-gray-600">{{ ucwords(str_replace('_', ' ', $account->type)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="bg-gray-50 p-4 shadow-sm rounded-lg">
                        <h3 class="text-sm font-medium text-gray-800 mb-3">Financial Summary</h3>
                        <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                                <span class="text-gray-600">Currency:</span>
                                <span class="font-medium text-gray-600">TZS</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Credit:</span>
                                <span class="font-medium text-gray-600">{{ number_format($account->credit, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Debit:</span>
                                <span class="font-medium text-gray-600">{{ number_format($account->debit, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Balance:</span>
                                <span class="font-medium text-gray-600">{{ number_format($account->balance, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Transactions Table -->
        <h2 class="text-lg font-bold mb-4 text-gray-800">Transactions</h2>
        <div class="overflow-auto">

            {{-- <table class="w-full text-left mb-8 border border-gray-300 rounded-lg shadow-md">
                <thead>
                    <tr class="bg-blue-100 text-gray-700 text-sm font-semibold">
                        <th class="py-3 px-4 border-r">S/N</th>
                        <th class="py-3 px-4 border-r">Account Number</th>
                        <th class="py-3 px-4 border-r">Outstanding Balance</th>
                        <th class="py-3 px-4 border-r">Sender Name</th>
                        <th class="py-3 px-4 border-r">Credit</th>
                        <th class="py-3 px-4 border-r">Debit</th>
                        <th class="py-3 px-4 border-r">Narration</th>
                        <th class="py-3 px-4 border-r">Transaction Date</th>
                        <th class="py-3 px-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        <tr class="border-b text-gray-800 hover:bg-blue-50">
                            <td class="py-3 px-4 text-center border-r">{{ $loop->iteration }}</td>
                            <td class="py-3 px-4 border-r">{{ $transaction->record_on_account_number }}</td>
                            <td class="py-3 px-4 border-r">{{ number_format($transaction->record_on_account_number_balance, 1) }} TZS</td>
                            <td class="py-3 px-4 border-r">{{ $transaction->sender_name }}</td>
                            <td class="py-3 px-4 border-r">{{ number_format($transaction->credit, 2) }}</td>
                            <td class="py-3 px-4 border-r">{{ number_format($transaction->debit, 2) }}</td>
                            <td class="py-3 px-4 border-r">{{ $transaction->narration }}</td>
                            <td class="py-3 px-4 border-r">{{ $transaction->created_at->format('Y-M-d') }}</td>
                            <td class="py-3 px-4">
                                <button wire:click="reverseTransaction({{ $transaction->reference_number }})" class="text-blue-500 hover:text-blue-700">
                                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 0 0 3.7 3.7 48.656 48.656 0 0 0 7.324 0 4.006 4.006 0 0 0 3.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3-3 3"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table> --}}


            <livewire:accounting.view-account-table />

        </div>
    </div>
</div>
