<div>
    <!-- Header Section -->
     @if(session()->has('message'))
     <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-2 rounded flex items-center text-sm" role="alert">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('message') }}</span>
    </div>
     @endif
     @if(session()->has('error'))
     <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-2 rounded flex items-center text-sm" role="alert">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
     @endif
    <div class="bg-white shadow-sm border-b border-gray-100 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Shares Management</h2>
                <button wire:click="test(1)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    New Share Product
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Filters Section -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Filters</h3>
                <button wire:click="$toggle('showFilters')" class="text-sm text-gray-600 hover:text-gray-900">
                    {{ $showFilters ? 'Hide Filters' : 'Show Filters' }}
                </button>
            </div>

            @if($showFilters)
            <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select wire:model="filters.status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-lg">
                        <option value="">All Status</option>
                        <option value="ACTIVE">Active</option>
                        <option value="INACTIVE">Inactive</option>
                        <option value="PENDING">Pending</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <select wire:model="filters.type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-lg">
                        <option value="">All Types</option>
                        <option value="ordinary">Ordinary</option>
                        <option value="preferred">Preferred</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Min Shares</label>
                    <input type="number" wire:model="filters.min_shares" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Price Range</label>
                    <input type="text" wire:model="filters.price_range" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
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
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-1">
                                    <span>Product Name</span>
                                    <button wire:click="sortBy('sub_product_name')" class="focus:outline-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </button>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allocated Shares</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal Price</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($products as $product)                        
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->sub_product_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->shareType->type ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($product->shares_allocated) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($product->nominal_price, 2) }}</td>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($product->status !== 'PENDING')
                                    <button wire:click="editProduct({{ $product->id }})" class="text-red-600 hover:text-red-900 mr-3">Edit</button>                                    
                                    <button wire:click="deleteProduct({{ $product->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No share products found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- New/Edit Product Modal -->
    @if($selectedAction)
    <div class="fixed inset-0 overflow-y-auto" style="display: block;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                {{ $selectedAction === 1 ? 'New Share Product' : 'Edit Share Product' }}
                            </h3>

                            <form wire:submit.prevent="saveProduct" class="space-y-6">
                                <!-- Basic Information -->
                                <div class="bg-red-50 p-4 rounded-lg">
                                    <h4 class="text-lg font-medium text-red-900 mb-4">Basic Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="sub_product_name" class="block text-sm font-medium text-gray-700">Product Name</label>
                                            <input readonly type="text" wire:model="sub_product_name" id="sub_product_name" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                            @error('sub_product_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                       

                                        <div>
                                            <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                                            <select wire:model="currency" id="currency" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-lg">
                                                <option value="TZS">TZS</option>
                                                <option value="USD">USD</option>
                                                <option value="EUR">EUR</option>
                                            </select>
                                            @error('currency') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="productStatus" class="block text-sm font-medium text-gray-700">Status</label>
                                            <select wire:model="productStatus" id="productStatus" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-lg">
                                               @if($selectedAction == 1)
                                                <option value="PENDING">Pending</option>
                                                @else
                                                <option value="ACTIVE">Active</option>
                                                <option value="INACTIVE">Inactive</option>
                                                @endif
                                            </select>
                                            @error('productStatus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="product_account" class="block text-sm font-medium text-gray-700">
                                                Product Account
                                                <span class="ml-1 text-gray-400 cursor-help" title="The account where share transactions will be recorded">ⓘ</span>
                                            </label>
                                            <select wire:model="product_account" id="product_account" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-lg">
                                                <option value="">Select Account</option>
                                                @foreach($shareAccounts as $account)
                                                    <option value="{{ $account['account_number'] }}">{{ $account['account_number'] }} - {{ $account['account_name'] }}</option>
                                                @endforeach
                                            </select>
                                            @error('product_account') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Share Settings -->
                                <div class="bg-red-50 p-4 rounded-lg">
                                    <h4 class="text-lg font-medium text-red-900 mb-4">Share Settings</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="allocated_shares" class="block text-sm font-medium text-gray-700">
                                                Allocated Shares
                                                <span class="ml-1 text-gray-400 cursor-help" title="Total number of shares available for allocation to members">ⓘ</span>
                                            </label>
                                            <input type="number" wire:model="allocated_shares" id="allocated_shares" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                            @error('allocated_shares') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="nominal_price" class="block text-sm font-medium text-gray-700">
                                                Nominal Price
                                                <span class="ml-1 text-gray-400 cursor-help" title="The face value or initial price of each share">ⓘ</span>
                                            </label>
                                            <input type="number" step="0.01" wire:model="nominal_price" id="nominal_price" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                            @error('nominal_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="minimum_required_shares" class="block text-sm font-medium text-gray-700">
                                                Minimum Required Shares
                                                <span class="ml-1 text-gray-400 cursor-help" title="The minimum number of shares a member must hold to maintain active membership">ⓘ</span>
                                            </label>
                                            <input type="number" wire:model="minimum_required_shares" id="minimum_required_shares" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                            @error('minimum_required_shares') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="lock_in_period" class="block text-sm font-medium text-gray-700">
                                                Lock-in Period (Days)
                                                <span class="ml-1 text-gray-400 cursor-help" title="The minimum period members must hold their shares before they can be sold or transferred">ⓘ</span>
                                            </label>
                                            <input type="number" wire:model="lock_in_period" id="lock_in_period" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                            @error('lock_in_period') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="dividend_eligibility_period" class="block text-sm font-medium text-gray-700">
                                                Dividend Eligibility Period (Days)
                                                <span class="ml-1 text-gray-400 cursor-help" title="The minimum period shares must be held to be eligible for dividend payments">ⓘ</span>
                                            </label>
                                            <input type="number" wire:model="dividend_eligibility_period" id="dividend_eligibility_period" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                            @error('dividend_eligibility_period') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="dividend_payment_frequency" class="block text-sm font-medium text-gray-700">
                                                Dividend Payment Frequency
                                                <span class="ml-1 text-gray-400 cursor-help" title="How often dividends will be calculated and paid to members">ⓘ</span>
                                            </label>
                                            <select wire:model="dividend_payment_frequency" id="dividend_payment_frequency" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-lg">
                                                <option value="monthly">Monthly</option>
                                                <option value="quarterly">Quarterly</option>
                                                <option value="semi_annual">Semi-Annual</option>
                                                <option value="annual">Annual</option>
                                            </select>
                                            @error('dividend_payment_frequency') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Settings -->
                                <div class="bg-red-50 p-4 rounded-lg">
                                    <h4 class="text-lg font-medium text-red-900 mb-4">Additional Settings</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="payment_methods" class="block text-sm font-medium text-gray-700">
                                                Allowed Payment Methods
                                                <span class="ml-1 text-gray-400 cursor-help" title="Select the payment methods that members can use to purchase shares">ⓘ</span>
                                            </label>
                                            <select wire:model="payment_methods" id="payment_methods" multiple class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-lg">
                                                <option value="bank">Bank Transfer</option>
                                                <option value="mobile_money">Mobile Money</option>
                                                <option value="cash">Cash</option>
                                            </select>
                                            @error('payment_methods') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="withdrawal_approval_level" class="block text-sm font-medium text-gray-700">
                                                Withdrawal Approval Level
                                                <span class="ml-1 text-gray-400 cursor-help" title="Number of approval levels required for share withdrawal requests">ⓘ</span>
                                            </label>
                                            <select wire:model="withdrawal_approval_level" id="withdrawal_approval_level" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-lg">
                                                <option value="1">Single Level</option>
                                                <option value="2">Two Levels</option>
                                                <option value="3">Three Levels</option>
                                            </select>
                                            @error('withdrawal_approval_level') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Checkboxes -->
                                <div class="bg-red-50 p-4 rounded-lg">
                                    <h4 class="text-lg font-medium text-red-900 mb-4">Additional Options</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="create_during_registration" id="create_during_registration" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <label for="create_during_registration" class="ml-2 block text-sm text-gray-900">Create During Registration</label>
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="activated_by_lower_limit" id="activated_by_lower_limit" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <label for="activated_by_lower_limit" class="ml-2 block text-sm text-gray-900">Activated by Lower Limit</label>
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="requires_approval" id="requires_approval" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <label for="requires_approval" class="ml-2 block text-sm text-gray-900">Requires Approval</label>
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="generate_atm_card_profile" id="generate_atm_card_profile" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <label for="generate_atm_card_profile" class="ml-2 block text-sm text-gray-900">Generate ATM Card Profile</label>
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="allow_statement_generation" id="allow_statement_generation" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <label for="allow_statement_generation" class="ml-2 block text-sm text-gray-900">Allow Statement Generation</label>
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="send_notifications" id="send_notifications" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <label for="send_notifications" class="ml-2 block text-sm text-gray-900">Send Notifications</label>
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="require_image_member" id="require_image_member" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <label for="require_image_member" class="ml-2 block text-sm text-gray-900">Require Member Image</label>
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="require_image_id" id="require_image_id" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <label for="require_image_id" class="ml-2 block text-sm text-gray-900">Require ID Image</label>
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="require_mobile_number" id="require_mobile_number" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <label for="require_mobile_number" class="ml-2 block text-sm text-gray-900">Require Mobile Number</label>
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="generate_mobile_profile" id="generate_mobile_profile" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <label for="generate_mobile_profile" class="ml-2 block text-sm text-gray-900">Generate Mobile Profile</label>
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="allow_share_transfer" id="allow_share_transfer" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <label for="allow_share_transfer" class="ml-2 block text-sm text-gray-900">
                                                Allow Share Transfer
                                                <span class="ml-1 text-gray-400 cursor-help" title="Enable members to transfer their shares to other members">ⓘ</span>
                                            </label>
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="allow_share_withdrawal" id="allow_share_withdrawal" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <label for="allow_share_withdrawal" class="ml-2 block text-sm text-gray-900">
                                                Allow Share Withdrawal
                                                <span class="ml-1 text-gray-400 cursor-help" title="Enable members to withdraw their shares and receive payment">ⓘ</span>
                                            </label>
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="enable_dividend_calculation" id="enable_dividend_calculation" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <label for="enable_dividend_calculation" class="ml-2 block text-sm text-gray-900">
                                                Enable Dividend Calculation
                                                <span class="ml-1 text-gray-400 cursor-help" title="Enable automatic calculation and distribution of dividends to members">ⓘ</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- SMS Settings -->
                                <div class="bg-red-50 p-4 rounded-lg">
                                    <h4 class="text-lg font-medium text-red-900 mb-4">SMS Settings</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="sms_sender_name" class="block text-sm font-medium text-gray-700">
                                                SMS Sender Name
                                                <span class="ml-1 text-gray-400 cursor-help" title="The name that will appear as the sender of SMS notifications">ⓘ</span>
                                            </label>
                                            <input type="text" wire:model="sms_sender_name" id="sms_sender_name" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                            @error('sms_sender_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="sms_api_key" class="block text-sm font-medium text-gray-700">
                                                SMS API Key
                                                <span class="ml-1 text-gray-400 cursor-help" title="API key for the SMS service provider to enable sending notifications">ⓘ</span>
                                            </label>
                                            <input type="text" wire:model="sms_api_key" id="sms_api_key" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                            @error('sms_api_key') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="sms_enabled" id="sms_enabled" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <label for="sms_enabled" class="ml-2 block text-sm text-gray-900">
                                                Enable SMS Notifications
                                                <span class="ml-1 text-gray-400 cursor-help" title="Enable sending SMS notifications for important share-related events">ⓘ</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <!-- handle save and edit -->
                                    @if($selectedAction === 1)
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                            Save
                                        </button>
                                    @elseif($selectedAction === 2)
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                            Update
                                        </button>
                                    @endif
                                    <button type="button" wire:click="test(0)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:w-auto sm:text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Add this script section at the end of the file, before the closing </div> -->
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('livewire:load', function () {
            Livewire.on('closeModal', () => {
                $('#editProductModal').modal('hide');
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Get all number input fields
            const numberInputs = document.querySelectorAll('input[type="text"][id]');
            
            numberInputs.forEach(input => {
                // Add event listeners for input and blur
                input.addEventListener('input', formatNumber);
                input.addEventListener('blur', formatNumber);
                
                // Initial format if there's a value
                if (input.value) {
                    formatNumber.call(input);
                }
            });
        });

        function formatNumber() {
            // Get the input value and remove any non-digit characters except decimal point
            let value = this.value.replace(/[^\d.]/g, '');
            
            // Ensure only one decimal point
            let parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Format the whole number part with commas
            if (parts[0]) {
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
            
            // Update the input value
            this.value = parts.join('.');
            
            // Get the wire:model attribute
            const model = this.getAttribute('wire:model');
            if (model) {
                // Get the raw numeric value
                const rawValue = parseFloat(value.replace(/,/g, ''));
                if (!isNaN(rawValue)) {
                    // Find the Livewire component
                    const component = document.querySelector('[wire\\:id]');
                    if (component) {
                        const wireId = component.getAttribute('wire:id');
                        window.Livewire.find(wireId).set(model, rawValue);
                    }
                }
            }
        }
    </script>
</div>
