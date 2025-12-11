<div>
    <!-- Header Section -->
         <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="fixed bottom-0 right-0 m-6">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed bottom-0 right-0 m-6">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        </div>
    @endif
    <div class="bg-white shadow-sm border-b border-gray-100 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Savings Products Management</h2>
                <button wire:click="viewNewSavingProduct" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-900 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    New Savings Product
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Search and Filter Section -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <input type="text" wire:model.debounce.300ms="search" placeholder="Search savings products..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-4">
                    <button wire:click="$toggle('showFilters')" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filters
                    </button>
                    <button wire:click="resetFilters" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Reset
                    </button>
                </div>
            </div>

            <!-- Filters Panel -->
            @if($showFilters)
            <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select wire:model="filters.status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                        <option value="">All Status</option>
                        <option value="ACTIVE">Active</option>
                        <option value="INACTIVE">Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <select wire:model="filters.type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                        <option value="">All Types</option>
                        @foreach($savingsTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Min Balance</label>
                    <input type="number" wire:model="filters.min_balance" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Interest Rate</label>
                    <input type="number" wire:model="filters.interest_rate" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
            </div>
            @endif
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th wire:click="sortBy('product_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                                Product Name
                                @if($sortField === 'product_name')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @endif
                                @endif
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest Rate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($products as $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $product->product_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $product->savingsType->type }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($product->interest_rate, 2) }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($product->min_balance, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $product->status == 'ACTIVE' 
                                        ? 'bg-green-100 text-green-800' 
                                        : ($product->status == 'INACTIVE' 
                                            ? 'bg-red-100 text-red-800' 
                                            : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $product->status == 'ACTIVE' 
                                        ? 'Active' 
                                        : ($product->status == 'INACTIVE' 
                                            ? 'Inactive' 
                                            : 'Pending') }}
                                </span>    
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($product->status !== 'PENDING')
                                    <button wire:click="editProduct({{ $product->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>                                    
                                    <button wire:click="deleteProduct({{ $product->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                                @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No savings products found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Product Modal -->
    @if($showAddModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full">
                <form wire:submit.prevent="{{ $editingProduct ? 'updateProduct' : 'createProduct' }}">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <!-- Basic Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label for="product_name" class="block text-sm font-medium text-gray-700">Product Name</label>
                                    <input readonly type="text" wire:model="form.product_name" id="product_name" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.product_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="savings_type_id" class="block text-sm font-medium text-gray-700">Savings Type</label>
                                    <select wire:model="form.savings_type_id" id="savings_type_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                                        <option value="">Select Savings Type</option>
                                        @foreach($savingsTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->type }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.savings_type_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                                    <select wire:model="form.currency" id="currency" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                                        <option value="KES">Tanzania Shilling (TZS)</option>
                                        <option value="USD">US Dollar (USD)</option>
                                        <option value="EUR">Euro (EUR)</option>
                                    </select>
                                    @error('form.currency') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="product_account" class="block text-sm font-medium text-gray-700">Product Account</label>
                                    <select wire:model="form.product_account" id="product_account" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                                        <option value="">Select Product Account</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->account_number }}">{{ $account->account_number }} - {{ $account->account_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.product_account') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                    <select wire:model="form.status" id="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                                        @if($editingProduct)
                                        <option value="ACTIVE">Active</option>
                                        <option value="INACTIVE">Inactive</option>
                                        @else
                                        <option value="PENDING">Pending</option>                                        
                                        @endif
                                    </select>
                                    @error('form.status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Interest and Fees -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Interest and Fees</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label for="interest_rate" class="block text-sm font-medium text-gray-700">Interest Rate (%)</label>
                                    <input type="number" step="0.01" wire:model="form.interest_rate" id="interest_rate" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.interest_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="interest_value" class="block text-sm font-medium text-gray-700">Interest Value</label>
                                    <input type="number" step="0.01" wire:model="form.interest_value" id="interest_value" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.interest_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="interest_tenure" class="block text-sm font-medium text-gray-700">Interest Tenure (months)</label>
                                    <input type="number" wire:model="form.interest_tenure" id="interest_tenure" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.interest_tenure') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="min_balance" class="block text-sm font-medium text-gray-700">Minimum Balance</label>
                                    <input type="number" step="0.01" wire:model="form.min_balance" id="min_balance" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.min_balance') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="maintenance_fees" class="block text-sm font-medium text-gray-700">Maintenance Fees (%)</label>
                                    <input type="number" step="0.01" wire:model="form.maintenance_fees" id="maintenance_fees" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.maintenance_fees') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="maintenance_fees_value" class="block text-sm font-medium text-gray-700">Maintenance Fees Value</label>
                                    <input type="number" step="0.01" wire:model="form.maintenance_fees_value" id="maintenance_fees_value" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.maintenance_fees_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="ledger_fees" class="block text-sm font-medium text-gray-700">Ledger Fees (%)</label>
                                    <input type="number" step="0.01" wire:model="form.ledger_fees" id="ledger_fees" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.ledger_fees') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="ledger_fees_value" class="block text-sm font-medium text-gray-700">Ledger Fees Value</label>
                                    <input type="number" step="0.01" wire:model="form.ledger_fees_value" id="ledger_fees_value" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.ledger_fees_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Deposit Settings -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Deposit Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label for="deposit" class="block text-sm font-medium text-gray-700">Allow Deposits</label>
                                    <select wire:model="form.deposit" id="deposit" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    @error('form.deposit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="deposit_charge" class="block text-sm font-medium text-gray-700">Deposit Charge (%)</label>
                                    <input type="number" step="0.01" wire:model="form.deposit_charge" id="deposit_charge" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.deposit_charge') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="deposit_charge_min_value" class="block text-sm font-medium text-gray-700">Min Charge Value</label>
                                    <input type="number" step="0.01" wire:model="form.deposit_charge_min_value" id="deposit_charge_min_value" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.deposit_charge_min_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="deposit_charge_max_value" class="block text-sm font-medium text-gray-700">Max Charge Value</label>
                                    <input type="number" step="0.01" wire:model="form.deposit_charge_max_value" id="deposit_charge_max_value" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.deposit_charge_max_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Withdrawal Settings -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Withdrawal Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label for="withdraw" class="block text-sm font-medium text-gray-700">Allow Withdrawals</label>
                                    <select wire:model="form.withdraw" id="withdraw" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    @error('form.withdraw') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="withdraw_charge" class="block text-sm font-medium text-gray-700">Withdrawal Charge (%)</label>
                                    <input type="number" step="0.01" wire:model="form.withdraw_charge" id="withdraw_charge" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.withdraw_charge') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="withdraw_charge_min_value" class="block text-sm font-medium text-gray-700">Min Charge Value</label>
                                    <input type="number" step="0.01" wire:model="form.withdraw_charge_min_value" id="withdraw_charge_min_value" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.withdraw_charge_min_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="withdraw_charge_max_value" class="block text-sm font-medium text-gray-700">Max Charge Value</label>
                                    <input type="number" step="0.01" wire:model="form.withdraw_charge_max_value" id="withdraw_charge_max_value" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.withdraw_charge_max_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Account Settings -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Account Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label for="collection_account_withdraw_charges" class="block text-sm font-medium text-gray-700">Withdrawal Charges Account</label>
                                    <select wire:model="form.collection_account_withdraw_charges" id="collection_account_withdraw_charges" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                                        <option value="">Select Account</option>
                                        @foreach(DB::table('accounts')->where('category_code', '4200')->get() as $account)
                                            <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.collection_account_withdraw_charges') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="collection_account_deposit_charges" class="block text-sm font-medium text-gray-700">Deposit Charges Account</label>
                                    <select wire:model="form.collection_account_deposit_charges" id="collection_account_deposit_charges" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                                        <option value="">Select Account</option>
                                        @foreach(DB::table('accounts')->where('category_code', '4200')->get() as $account)
                                            <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.collection_account_deposit_charges') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="collection_account_interest_charges" class="block text-sm font-medium text-gray-700">Interest Charges Account</label>
                                    <select wire:model="form.collection_account_interest_charges" id="collection_account_interest_charges" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                                        <option value="">Select Account</option>
                                        @foreach(DB::table('accounts')->where('category_code', '4200')->get() as $account)
                                            <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.collection_account_interest_charges') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="profit_account" class="block text-sm font-medium text-gray-700">Profit Account</label>
                                    <select wire:model="form.profit_account" id="profit_account" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                                        <option value="">Select Account</option>
                                        @foreach(DB::table('accounts')->where('category_code', '4200')->get() as $account)
                                            <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.profit_account') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Additional Settings -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label for="inactivity" class="block text-sm font-medium text-gray-700">Inactivity Period (days)</label>
                                    <input type="number" wire:model="form.inactivity" id="inactivity" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.inactivity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-span-3">
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea wire:model="form.notes" id="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                    @error('form.notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Feature Flags -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Feature Flags</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="form.create_during_registration" id="create_during_registration" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="create_during_registration" class="ml-2 block text-sm text-gray-900">Create During Registration</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="form.activated_by_lower_limit" id="activated_by_lower_limit" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="activated_by_lower_limit" class="ml-2 block text-sm text-gray-900">Activated by Lower Limit</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="form.requires_approval" id="requires_approval" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="requires_approval" class="ml-2 block text-sm text-gray-900">Requires Approval</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="form.generate_atm_card_profile" id="generate_atm_card_profile" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="generate_atm_card_profile" class="ml-2 block text-sm text-gray-900">Generate ATM Card Profile</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="form.allow_statement_generation" id="allow_statement_generation" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="allow_statement_generation" class="ml-2 block text-sm text-gray-900">Allow Statement Generation</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="form.send_notifications" id="send_notifications" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="send_notifications" class="ml-2 block text-sm text-gray-900">Send Notifications</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="form.require_image_member" id="require_image_member" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="require_image_member" class="ml-2 block text-sm text-gray-900">Require Member Image</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="form.require_image_id" id="require_image_id" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="require_image_id" class="ml-2 block text-sm text-gray-900">Require ID Image</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="form.require_mobile_number" id="require_mobile_number" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="require_mobile_number" class="ml-2 block text-sm text-gray-900">Require Mobile Number</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="form.generate_mobile_profile" id="generate_mobile_profile" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="generate_mobile_profile" class="ml-2 block text-sm text-gray-900">Generate Mobile Profile</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ $editingProduct ? 'Update' : 'Create' }} Product
                        </button>
                        <button type="button" wire:click="$set('showAddModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif


</div>
