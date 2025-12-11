@php
    // Initialize Livewire component properties
    $showForm = false;
    $isEdit = false;
    $payModal = false;
    $search = '';
    $statusFilter = '';
    $customer_name = '';
    $invoice_number = '';
    $amount = 0;
    $due_date = '';
    $income_sub_category_code = '';
    $asset_sub_category_code = '';
    $payment_amount = 0;
    $payment_date = '';
    $payment_method = '';
    $reference_number = '';
    $payment_notes = '';

    // Calculate total receivables from accounts table
    $totalReceivables = DB::table('accounts')
        ->where('major_category_code', '1000') // Asset accounts
        ->where('category_code', '1100') // Receivables category
        ->sum('balance');

    // Calculate paid receivables from general_ledger
    $paidReceivables = DB::table('general_ledger')
        ->where('transaction_type', 'IFT')
        ->where('trans_status', 'Successful')
        ->where('payment_status', 'Successful')
        ->where('credit', '>', 0)
        ->sum('credit');

    // Calculate overdue receivables (due date passed and not paid)
    $overdueReceivables = DB::table('accounts')
        ->where('major_category_code', '1000')
        ->where('category_code', '1100')
        ->where('status', 'OVERDUE')
        ->sum('balance');

    // Calculate bad debt (written off receivables)
    $badDebt = DB::table('accounts')
        ->where('major_category_code', '1000')
        ->where('category_code', '1100')
        ->where('status', 'BAD_DEBT')
        ->sum('balance');

    // Get paginated accounts receivable data with customer information
    $accountsReceivable = DB::table('accounts as a')
        ->leftJoin('clients as c', 'a.client_number', '=', 'c.client_number')
        ->leftJoin('general_ledger as gl', function($join) {
            $join->on('a.account_number', '=', 'gl.record_on_account_number')
                ->where('gl.trans_status', '=', 'Successful');
        })
        ->select([
            'a.id',
            'a.account_number',
            'a.account_name',
            'a.balance as amount',
            'a.status',
            'a.created_at as due_date',
            DB::raw("CONCAT(c.first_name, ' ', COALESCE(c.middle_name, ''), ' ', c.last_name) as customer_name"),
            DB::raw('a.account_number as invoice_number'),
            DB::raw("CASE 
                WHEN a.status = 'PAID' THEN 1 
                ELSE 0 
            END as is_paid"),
            DB::raw("CASE 
                WHEN a.status = 'OVERDUE' THEN 1 
                ELSE 0 
            END as is_overdue")
        ])
        ->where('a.major_category_code', '1000')
        ->where('a.category_code', '1100')
        ->when($search ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('a.account_name', 'like', '%' . $search . '%')
                    ->orWhere('a.account_number', 'like', '%' . $search . '%')
                    ->orWhere('c.first_name', 'like', '%' . $search . '%')
                    ->orWhere('c.last_name', 'like', '%' . $search . '%');
            });
        })
        ->when($statusFilter ?? null, function ($query, $status) {
            if ($status === 'paid') {
                $query->where('a.status', 'PAID');
            } elseif ($status === 'unpaid') {
                $query->where('a.status', 'PENDING');
            } elseif ($status === 'overdue') {
                $query->where('a.status', 'OVERDUE');
            }
        })
        ->orderBy('a.created_at', 'desc')
        ->paginate(10);
@endphp

<div>
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Accounts Receivable </h2>
                    <p class="mt-1 text-sm text-gray-600">Manage and track money owed by customers for goods or services provided</p>
                </div>
                <div class="flex space-x-3">
                    <button wire:click="exportToExcel" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export
                    </button>
                    <button wire:click="resetForm" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-900 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Receivable
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Receivables</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($totalReceivables, 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Paid Receivables</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($paidReceivables, 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Overdue</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($overdueReceivables, 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Bad Debt</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($badDebt, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex space-x-4">
                        <div class="relative">
                            <input type="text" wire:model.debounce.300ms="search" placeholder="Search receivables..." class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <svg class="w-5 h-5 text-gray-400 absolute right-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <select type="text" wire:model="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Showing {{ $accountsReceivable->count() }} of {{ $totalReceivables }} records</span>
                    </div>
                </div>

                <!-- Receivables Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($accountsReceivable as $account)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $account->customer_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $account->invoice_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($account->amount, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $account->due_date }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($account->is_paid)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Paid
                                            </span>
                                        @elseif($account->is_overdue)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Overdue
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($account->is_paid)
                                            <button class="text-blue-600 hover:text-blue-900 mr-3" wire:click="viewPaymentDetails({{ $account->id }})">
                                                View Details
                                            </button>
                                        @else
                                            <button class="text-yellow-600 hover:text-yellow-900 mr-3" wire:click="edit({{ $account->id }})">
                                                Edit
                                            </button>
                                            <button class="text-green-600 hover:text-green-900 mr-3" wire:click="markAsPaid({{ $account->id }})">
                                                Pay
                                            </button>
                                            <button class="text-red-600 hover:text-red-900" wire:click="delete({{ $account->id }})">
                                                Delete
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $accountsReceivable->links() }}
                </div>
            </div>
        </div>

        <!-- Form Modal -->
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" 
             x-data="{ show: @entangle('showForm') }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $isEdit ? 'Edit' : 'New' }} Receivable</h3>
                    <button wire:click="$set('showForm', false)" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="parent_account_number" class="block text-sm font-medium text-gray-700">Parent Receivables Account</label>
                            <select type="text" wire:model="parent_account_number" id="parent_account_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select Parent Account</option>
                                @foreach($parentAccounts as $account)
                                    <option value="{{ $account->account_number }}">{{ $account->account_name }} ({{ $account->account_number }})</option>
                                @endforeach
                            </select>
                            @error('parent_account_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="income_account" class="block text-sm font-medium text-gray-700">Income Account</label>
                            <select type="text" wire:model="income_account" id="income_account" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select Income Account</option>
                                @foreach($incomeAccounts as $account)
                                    <option value="{{ $account->account_number }}">{{ $account->account_name }} ({{ $account->account_number }})</option>
                                @endforeach
                            </select>
                            @error('income_account') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name</label>
                            <input type="text" wire:model="customer_name" id="customer_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('customer_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="invoice_number" class="block text-sm font-medium text-gray-700">Invoice Number</label>
                            <input type="text" wire:model="invoice_number" id="invoice_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('invoice_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="number" step="0.01" wire:model="amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Due Date</label>
                            <input type="date" wire:model="due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('due_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Financial Documentation -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Upload Invoice</label>
                            <input type="file" wire:model="invoice_file" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100">
                            @error('invoice_file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @if($invoice_file)
                                <p class="mt-1 text-sm text-gray-500">Selected: {{ $invoice_file->getClientOriginalName() }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Upload Payment Receipt</label>
                            <input type="file" wire:model="payment_receipt_file" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100">
                            @error('payment_receipt_file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @if($payment_receipt_file)
                                <p class="mt-1 text-sm text-gray-500">Selected: {{ $payment_receipt_file->getClientOriginalName() }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Upload Credit Note</label>
                            <input type="file" wire:model="credit_note_file" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100">
                            @error('credit_note_file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @if($credit_note_file)
                                <p class="mt-1 text-sm text-gray-500">Selected: {{ $credit_note_file->getClientOriginalName() }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Upload Collection Letter</label>
                            <input type="file" wire:model="collection_letter_file" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100">
                            @error('collection_letter_file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @if($collection_letter_file)
                                <p class="mt-1 text-sm text-gray-500">Selected: {{ $collection_letter_file->getClientOriginalName() }}</p>
                            @endif
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea wire:model="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Add any additional notes..."></textarea>
                            @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-4">
                        <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-900 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ $isEdit ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payment Modal -->
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" x-show="payModal" style="display: none;">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Record Payment</h3>
                    <button wire:click="$set('payModal', false)" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="makePayment" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Amount</label>
                        <input type="number" step="0.01" wire:model="payment_amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('payment_amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Date</label>
                        <input type="date" wire:model="payment_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('payment_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <select type="text" wire:model="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="mobile_payment">Mobile Payment</option>
                        </select>
                        @error('payment_method') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reference Number</label>
                        <input type="text" wire:model="reference_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('reference_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea wire:model="payment_notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 mt-4">
                        <button type="button" wire:click="$set('payModal', false)" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-900 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Record Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payment Details Modal -->
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" x-data="{ showPaymentDetails: false, paymentData: null }" 
             x-show="showPaymentDetails" 
             @show-payment-details.window="showPaymentDetails = true; paymentData = $event.detail"
             style="display: none;">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Payment Details</h3>
                    <button @click="showPaymentDetails = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4" x-show="paymentData">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Customer Name</label>
                        <p class="mt-1 text-sm text-gray-900" x-text="paymentData.customer_name"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Invoice Number</label>
                        <p class="mt-1 text-sm text-gray-900" x-text="paymentData.invoice_number"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                        <p class="mt-1 text-sm text-gray-900" x-text="'$' + paymentData.amount"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Date</label>
                        <p class="mt-1 text-sm text-gray-900" x-text="new Date(paymentData.payment_date).toLocaleDateString()"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <p class="mt-1 text-sm text-gray-900" x-text="paymentData.payment_method"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reference Number</label>
                        <p class="mt-1 text-sm text-gray-900" x-text="paymentData.reference_number"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <p class="mt-1 text-sm text-gray-900" x-text="paymentData.narration"></p>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button @click="showPaymentDetails = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Notification Component -->
        <div x-data="{ show: false, message: '', type: '' }"
             x-show="show"
             x-init="
                window.addEventListener('notify', (e) => {
                    show = true;
                    message = e.detail.message;
                    type = e.detail.type;
                    setTimeout(() => show = false, 5000);
                });
             "
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-50"
             style="display: none;">
            <div class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
                 :class="{
                    'bg-red-50': type === 'error',
                    'bg-green-50': type === 'success',
                    'bg-yellow-50': type === 'warning'
                 }">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <!-- Error Icon -->
                            <svg x-show="type === 'error'" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <!-- Success Icon -->
                            <svg x-show="type === 'success'" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Warning Icon -->
                            <svg x-show="type === 'warning'" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium"
                               :class="{
                                    'text-red-800': type === 'error',
                                    'text-green-800': type === 'success',
                                    'text-yellow-800': type === 'warning'
                               }"
                               x-text="message"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="show = false" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
