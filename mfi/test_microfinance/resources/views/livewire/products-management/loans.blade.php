<div class="min-h-screen bg-gradient-to-br from-slate-50 to-red-50">
    <div class="p-6">
        <!-- Enhanced Header with Statistics -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-red-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Loan Products Management</h1>
                        <p class="text-gray-600 mt-1">Create, manage, and configure loan products with advanced settings</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button wire:click="openModal" 
                            class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add New Loan Product
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Products</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $this->products->total() }}</p>
                        </div>
                        <div class="p-3 bg-red-50 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Active Products</p>
                            <p class="text-3xl font-bold text-green-600 mt-1">{{ $this->products->where('sub_product_status', 1)->count() }}</p>
                        </div>
                        <div class="p-3 bg-green-50 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Avg Interest Rate</p>
                            <p class="text-3xl font-bold text-orange-600 mt-1">{{ number_format($this->products->avg('interest_value'), 1) }}%</p>
                        </div>
                        <div class="p-3 bg-orange-50 rounded-full">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Inactive Products</p>
                            <p class="text-3xl font-bold text-red-600 mt-1">{{ $this->products->where('sub_product_status', 0)->count() }}</p>
                        </div>
                        <div class="p-3 bg-red-50 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Search and Filter Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 mb-8">
            <div class="p-6">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0 lg:space-x-4">
                    <!-- Search -->
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" 
                                   wire:model.debounce.300ms="search" 
                                   placeholder="Search loan products..." 
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"/>
                        </div>
                    </div>

                    <!-- Filter Controls -->
                    <div class="flex items-center space-x-3">
                        <button wire:click="$toggle('showFilters')" 
                                class="inline-flex items-center px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            {{ $showFilters ? 'Hide Filters' : 'Show Filters' }}
                        </button>

                        @if($showFilters)
                            <button wire:click="resetFilters" 
                                    class="inline-flex items-center px-4 py-3 bg-red-100 text-red-700 rounded-xl hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reset Filters
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Advanced Filters -->
                @if($showFilters)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select wire:model="filters.status" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Amount</label>
                                <input type="number" wire:model="filters.min_amount" placeholder="0.00" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"/>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Interest Rate (%)</label>
                                <input type="number" wire:model="filters.interest_rate" placeholder="0.00" step="0.1" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"/>
                            </div>
                            <div class="flex items-end">
                                <button wire:click="resetFilters" class="w-full px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-800 transition-all duration-200">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Enhanced Products Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-red-50">
                <h3 class="text-lg font-semibold text-gray-900">Loan Products ({{ $this->products->total() }})</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th wire:click="sortBy('sub_product_name')" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-1">
                                    <span>Product Name</span>
                                    @if($sortField === 'sub_product_name')
                                        <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'text-red-600' : 'text-red-600 rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('interest_value')" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-1">
                                    <span>Interest Rate</span>
                                    @if($sortField === 'interest_value')
                                        <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'text-red-600' : 'text-red-600 rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('principle_min_value')" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-1">
                                    <span>Amount Range</span>
                                    @if($sortField === 'principle_min_value')
                                        <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'text-red-600' : 'text-red-600 rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term (Months)</th>
                            <th wire:click="sortBy('sub_product_status')" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-1">
                                    <span>Status</span>
                                    @if($sortField === 'sub_product_status')
                                        <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'text-red-600' : 'text-red-600 rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($this->products as $product)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 flex-shrink-0">
                                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $product->sub_product_name }}</div>
                                            <div class="text-sm text-gray-500">ID: {{ $product->sub_product_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            {{ $product->interest_value }}%
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($product->interest_method ?? 'N/A') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ number_format($product->principle_min_value, 0) }}</div>
                                    <div class="text-xs text-gray-500">to {{ number_format($product->principle_max_value, 0) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $product->min_term }} - {{ $product->max_term }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($product->repayment_frequency ?? 'monthly') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->sub_product_status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <svg class="w-1.5 h-1.5 mr-1.5 {{ $product->sub_product_status ? 'text-green-400' : 'text-red-400' }}" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                        {{ $product->sub_product_status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button wire:click="editProduct({{ $product->id }})" 
                                                class="inline-flex items-center p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-all duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button wire:click="deleteProduct({{ $product->id }})" 
                                                onclick="return confirm('Are you sure you want to delete this product?')"
                                                class="inline-flex items-center p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-all duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-1">No loan products found</h3>
                                        <p class="text-gray-500 mb-6">Get started by creating your first loan product.</p>
                                        <button wire:click="openModal" 
                                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-800 transition-all duration-200">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                            Add Loan Product
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Enhanced Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $this->products->links() }}
            </div>
        </div>
    </div>

    {{-- Comprehensive Enhanced Modal with All Required Fields --}}
    @if($showAddModal)
        <div class="fixed inset-0 z-50 overflow-y-auto " aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                
               
                <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle max-w-9xl mx-auto sm:w-full modal-panel" wire:click.stop>
                    
                    <!-- Enhanced Modal Header with Progress -->
                    <div class="px-8 py-6 border-b border-gray-200 bg-gradient-to-r from-red-50 via-indigo-50 to-purple-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 bg-red-600 rounded-xl shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">
                                        {{ $editingProduct ? 'Edit Loan Product' : 'Create New Loan Product' }}
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">Configure comprehensive loan product settings and parameters</p>
                                </div>
                            </div>
                            <button wire:click="closeModal" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Comprehensive Form Body -->
                    <div class="px-8 py-6 max-h-[75vh] overflow-y-auto">
                        <form wire:submit.prevent="{{ $editingProduct ? 'updateProduct' : 'createProduct' }}">
                            
                            <!-- Tab Navigation -->
                            <div class="mb-8">
                                <div class="border-b border-gray-200">
                                    <nav class="-mb-px flex space-x-8">
                                        @php
                                            $tabs = [
                                                ['id' => 'basic', 'label' => 'Basic Information', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                                ['id' => 'amounts', 'label' => 'Amount & Terms', 'icon' => 'M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z'],
                                                ['id' => 'interest', 'label' => 'Interest & Repayment', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
                                                ['id' => 'accounts', 'label' => 'Account Mapping', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                                                ['id' => 'fees', 'label' => 'Fees & Charges', 'icon' => 'M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 10c-4.418 0-8-1.79-8-4V7a2 2 0 012-2h2m12 0h2a2 2 0 012 2v7c0 2.21-3.582 4-8 4z'],
                                                ['id' => 'advanced', 'label' => 'Advanced Settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                                                ['id' => 'requirements', 'label' => 'Member Requirements', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z']
                                            ];
                                        @endphp
                                        @foreach($tabs as $tab)
                                            <button type="button" wire:click="setActiveTab('{{ $tab['id'] }}')"
                                               class="tab-link whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-all duration-200 flex items-center space-x-2 {{ $activeTab === $tab['id'] ? 'active' : '' }}"
                                               data-tab="{{ $tab['id'] }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                                                </svg>
                                                <span>{{ $tab['label'] }}</span>
                                            </button>
                                        @endforeach
                                    </nav>
                                </div>
                            </div>

                            <!-- Tab Content -->
                            <div class="tab-content">
                                
                                <!-- Basic Information Tab -->
                                @if($activeTab === 'basic')
                                <div id="basic-tab" class="tab-pane">
                                    <div class="bg-gradient-to-r from-red-50 to-indigo-50 p-6 rounded-xl border border-red-100 mb-6">
                                        <h3 class="text-xl font-bold text-red-700 mb-6 flex items-center">
                                            <svg class="w-6 h-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Basic Product Information
                                        </h3>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Product Name <span class="text-red-500">*</span></label>
                                                <input type="text" wire:model.defer="form.sub_product_name" 
                                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all duration-200" 
                                                       placeholder="Enter product name"/>
                                                @error('form.sub_product_name') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Product ID <span class="text-red-500">*</span></label>
                                                <input type="text" wire:model.defer="form.sub_product_id" 
                                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-gray-50 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all duration-200" 
                                                       placeholder="Auto-generated" readonly/>
                                                @error('form.sub_product_id') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Product Prefix</label>
                                                <input type="text" wire:model.defer="form.prefix" 
                                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all duration-200" 
                                                       placeholder="LN, SL, etc."/>
                                                @error('form.prefix') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                                                <select wire:model.defer="form.sub_product_status" 
                                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all duration-200">
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                </select>
                                                @error('form.sub_product_status') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Currency <span class="text-red-500">*</span></label>
                                                <select wire:model.defer="form.currency" 
                                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all duration-200">
                                                    <option value="TZS">TZS - Tanzanian Shilling</option>
                                                    <option value="USD">USD - US Dollar</option>
                                                    <option value="EUR">EUR - Euro</option>
                                                </select>
                                                @error('form.currency') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Requires Approval <span class="text-red-500">*</span></label>
                                                <select wire:model.defer="form.requires_approval" 
                                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all duration-200">
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                                @error('form.requires_approval') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="mt-6">
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Product Notes</label>
                                            <textarea wire:model.defer="form.notes" rows="4" 
                                                      class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all duration-200" 
                                                      placeholder="Add detailed description about this loan product..."></textarea>
                                            @error('form.notes') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Amount & Terms Tab -->
                                @if($activeTab === 'amounts')
                                <div id="amounts-tab" class="tab-pane">
                                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-xl border border-green-100 mb-6">
                                        <h3 class="text-xl font-bold text-green-700 mb-6 flex items-center">
                                            <svg class="w-6 h-6 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"/>
                                            </svg>
                                            Loan Amount & Term Limits
                                        </h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="col-span-2">
                                                <h4 class="text-lg font-semibold text-green-600 mb-4">Principal Amount Range</h4>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Minimum Amount <span class="text-red-500">*</span></label>
                                                        <div class="relative">
                                                            <input type="number" wire:model.defer="form.principle_min_value" 
                                                                   class="w-full px-4 py-3 pl-12 rounded-lg border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200" 
                                                                   placeholder="0.00" min="0" step="0.01"/>
                                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                                <span class="text-gray-500 sm:text-sm">TZS</span>
                                                            </div>
                                                        </div>
                                                        @error('form.principle_min_value') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Maximum Amount <span class="text-red-500">*</span></label>
                                                        <div class="relative">
                                                            <input type="number" wire:model.defer="form.principle_max_value" 
                                                                   class="w-full px-4 py-3 pl-12 rounded-lg border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200" 
                                                                   placeholder="0.00" min="0" step="0.01"/>
                                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                                <span class="text-gray-500 sm:text-sm">TZS</span>
                                                            </div>
                                                        </div>
                                                        @error('form.principle_max_value') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-span-2">
                                                <h4 class="text-lg font-semibold text-green-600 mb-4 mt-6">Term Limits</h4>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Minimum Term (Months) <span class="text-red-500">*</span></label>
                                                        <input type="number" wire:model.defer="form.min_term" 
                                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200" 
                                                               placeholder="1" min="1"/>
                                                        @error('form.min_term') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Maximum Term (Months) <span class="text-red-500">*</span></label>
                                                        <input type="number" wire:model.defer="form.max_term" 
                                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200" 
                                                               placeholder="36" min="1"/>
                                                        @error('form.max_term') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Interest & Repayment Tab -->
                                @if($activeTab === 'interest')
                                <div id="interest-tab" class="tab-pane">
                                    <div class="space-y-6">
                                        <!-- Interest Configuration -->
                                        <div class="bg-gradient-to-r from-orange-50 to-amber-50 p-6 rounded-xl border border-orange-100">
                                            <h3 class="text-xl font-bold text-orange-700 mb-6 flex items-center">
                                                <svg class="w-6 h-6 mr-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                                </svg>
                                                Interest Configuration
                                            </h3>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Interest Rate (%) <span class="text-red-500">*</span></label>
                                                    <input type="text" wire:model.defer="form.interest_value" 
                                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200" 
                                                           placeholder="15.5"/>
                                                    @error('form.interest_value') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Interest Tenure</label>
                                                    <select wire:model.defer="form.interest_tenure" 
                                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200">
                                                        <option value="monthly">Monthly</option>
                                                        <option value="quarterly">Quarterly</option>
                                                        <option value="annually">Annually</option>
                                                    </select>
                                                    @error('form.interest_tenure') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Interest Method <span class="text-red-500">*</span></label>
                                                    <select wire:model.defer="form.interest_method" 
                                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200">
                                                        <option value="flat">Flat Rate</option>
                                                        <option value="reducing">Reducing Balance</option>
                                                        <option value="compound">Compound Interest</option>
                                                    </select>
                                                    @error('form.interest_method') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Repayment Configuration -->
                                        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 p-6 rounded-xl border border-purple-100">
                                            <h3 class="text-xl font-bold text-purple-700 mb-6 flex items-center">
                                                <svg class="w-6 h-6 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Repayment Configuration
                                            </h3>
                                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Repayment Frequency <span class="text-red-500">*</span></label>
                                                    <select wire:model.defer="form.repayment_frequency" 
                                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200">
                                                        <option value="daily">Daily</option>
                                                        <option value="weekly">Weekly</option>
                                                        <option value="monthly">Monthly</option>
                                                        <option value="quarterly">Quarterly</option>
                                                    </select>
                                                    @error('form.repayment_frequency') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Amortization Method <span class="text-red-500">*</span></label>
                                                    <select wire:model.defer="form.amortization_method" 
                                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200">
                                                        <option value="equal_installments">Equal Installments</option>
                                                        <option value="equal_principle">Equal Principal</option>
                                                        <option value="balloon_payment">Balloon Payment</option>
                                                    </select>
                                                    @error('form.amortization_method') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Days in Year <span class="text-red-500">*</span></label>
                                                    <select wire:model.defer="form.days_in_a_year" 
                                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200">
                                                        <option value="360">360 Days</option>
                                                        <option value="365">365 Days</option>
                                                        <option value="366">366 Days (Leap Year)</option>
                                                    </select>
                                                    @error('form.days_in_a_year') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Days in Month <span class="text-red-500">*</span></label>
                                                    <select wire:model.defer="form.days_in_a_month" 
                                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200">
                                                        <option value="28">28 Days</option>
                                                        <option value="29">29 Days</option>
                                                        <option value="30">30 Days</option>
                                                        <option value="31">31 Days</option>
                                                    </select>
                                                    @error('form.days_in_a_month') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="mt-6">
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Repayment Strategy <span class="text-red-500">*</span></label>
                                                <select wire:model.defer="form.repayment_strategy" 
                                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200">
                                                    <option value="standard">Standard Repayment</option>
                                                    <option value="flexible">Flexible Repayment</option>
                                                    <option value="grace_period">Grace Period Allowed</option>
                                                </select>
                                                @error('form.repayment_strategy') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Account Mapping Tab -->
                                @if($activeTab === 'accounts')
                                    @include('livewire.products-management.loans-account-selector')
                                @endif

                                <!-- Fees & Charges Tab -->
                                @if($activeTab === 'fees')
                                <div id="fees-tab" class="tab-pane">
                                    <div class="space-y-6">
                                        
                                        <!-- Product Charges Section -->
                                        <div class="bg-gradient-to-r from-red-50 to-pink-50 p-6 rounded-xl border border-red-100">
                                            <div class="flex items-center justify-between mb-6">
                                                <h3 class="text-xl font-bold text-red-700 flex items-center">
                                                    <svg class="w-6 h-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 10c-4.418 0-8-1.79-8-4V7a2 2 0 012-2h2m12 0h2a2 2 0 012 2v7c0 2.21-3.582 4-8 4z"/>
                                                    </svg>
                                                    Loan Product Charges
                                                </h3>
                                                <button wire:click="addCharge" type="button"
                                                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-200">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                    </svg>
                                                    Add Charge
                                                </button>
                                            </div>

                                            <!-- Add New Charge Form -->
                                            <div class="mb-6 p-4 bg-white rounded-lg border">
                                                <h4 class="text-lg font-semibold text-gray-700 mb-4">
                                                    {{ $editingChargeIndex === null ? 'Add New Charge' : 'Edit Charge' }}
                                                </h4>
                                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Charge Name</label>
                                                        <input type="text" wire:model="newCharge.name" 
                                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" 
                                                               placeholder="Processing fee"/>
                                                        @error('newCharge.name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                                                        <select wire:model="newCharge.type" 
                                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                                                            <option value="charge">Charge</option>
                                                            <option value="insurance">Insurance</option>
                                                        </select>
                                                        @error('newCharge.type') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Value Type</label>
                                                        <select wire:model="newCharge.value_type" 
                                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                                                            <option value="fixed">Fixed Amount</option>
                                                            <option value="percentage">Percentage</option>
                                                        </select>
                                                        @error('newCharge.value_type') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                                            Value {{ $newCharge['value_type'] === 'percentage' ? '(%)' : '(TZS)' }}
                                                        </label>
                                                        <input type="number" wire:model="newCharge.value" step="0.01" min="0"
                                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" 
                                                               placeholder="{{ $newCharge['value_type'] === 'percentage' ? '2.5' : '10000' }}"/>
                                                        @error('newCharge.value') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="{{ $this->showCapFields ? '' : 'hidden' }}">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Min Cap (TZS)</label>
                                                        <input type="number" wire:model="newCharge.min_cap" step="0.01" min="0"
                                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" 
                                                               placeholder="1000"/>
                                                        @error('newCharge.min_cap') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="{{ $this->showCapFields ? '' : 'hidden' }}">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Cap (TZS)</label>
                                                        <input type="number" wire:model="newCharge.max_cap" step="0.01" min="0"
                                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" 
                                                               placeholder="50000"/>
                                                        @error('newCharge.max_cap') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                                                    <div class="md:col-span-2 lg:col-span-1">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">GL Account</label>
                                                        <select wire:model="newCharge.account_id" 
                                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                                                            <option value="">Select Account</option>
                                                            @foreach($charge_accounts as $account)
                                                                <option value="{{ $account->account_number }}">{{ $account->account_name }} ({{ $account->account_number }})</option>
                                                            @endforeach
                                                        </select>
                                                        @error('newCharge.account_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="mt-4 flex items-center justify-end space-x-2">
                                                    @if($editingChargeIndex === null)
                                                        <button type="button" wire:click="addCharge" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Add</button>
                                                    @else
                                                        <button type="button" wire:click="updateCharge" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Update</button>
                                                        <button type="button" wire:click="resetChargeForm" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancel</button>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Charges List -->
                                            @if(count($charges) > 0)
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-sm">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th class="px-4 py-3 text-left font-medium text-gray-700">Type</th>
                                                                <th class="px-4 py-3 text-left font-medium text-gray-700">Name</th>
                                                                <th class="px-4 py-3 text-left font-medium text-gray-700">Value Type</th>
                                                                <th class="px-4 py-3 text-left font-medium text-gray-700">Value</th>
                                                                <th class="px-4 py-3 text-left font-medium text-gray-700">Caps</th>
                                                                <th class="px-4 py-3 text-left font-medium text-gray-700">Account</th>
                                                                <th class="px-4 py-3 text-left font-medium text-gray-700">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-200">
                                                            @foreach($charges as $index => $charge)
                                                                <tr class="hover:bg-gray-50">
                                                                    <td class="px-4 py-3">
                                                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $charge['type'] === 'charge' ? 'bg-red-100 text-red-800' : 'bg-red-100 text-red-800' }}">
                                                                            {{ ucfirst($charge['type']) }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $charge['name'] }}</td>
                                                                    <td class="px-4 py-3 text-gray-600">{{ ucfirst($charge['value_type']) }}</td>
                                                                    <td class="px-4 py-3 text-gray-600">
                                                                        {{ $charge['value_type'] === 'percentage' ? $charge['value'] . '%' : number_format($charge['value'], 2) . ' TZS' }}
                                                                    </td>
                                                                    <td class="px-4 py-3 text-gray-600">
                                                                        @if($charge['value_type'] === 'percentage' && (!empty($charge['min_cap']) || !empty($charge['max_cap'])))
                                                                            <div class="text-xs">
                                                                                @if(!empty($charge['min_cap']))
                                                                                    <div class="text-green-600">Min: {{ number_format($charge['min_cap'], 2) }} TZS</div>
                                                                                @endif
                                                                                @if(!empty($charge['max_cap']))
                                                                                    <div class="text-red-600">Max: {{ number_format($charge['max_cap'], 2) }} TZS</div>
                                                                                @endif
                                                                            </div>
                                                                        @else
                                                                            <span class="text-gray-400 text-xs">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-4 py-3 text-gray-600">{{ $charge['account_id'] }}</td>
                                                                    <td class="px-4 py-3 space-x-3">
                                                                        <button type="button" wire:click="startEditCharge({{ $index }})" class="text-red-600 hover:text-red-800 transition-colors duration-200">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-6 14h10a2 2 0 002-2V7a2 2 0 00-2-2h-5l-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                            </svg>
                                                                        </button>
                                                                        <button type="button" wire:click="removeCharge({{ $index }})" class="text-red-600 hover:text-red-800 transition-colors duration-200">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                            </svg>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center py-8">
                                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 10c-4.418 0-8-1.79-8-4V7a2 2 0 012-2h2m12 0h2a2 2 0 012 2v7c0 2.21-3.582 4-8 4z"/>
                                                    </svg>
                                                    <p class="text-gray-500">No charges or insurance configured yet. Add your first charge above.</p>
                                                </div>
                                            @endif
                                        </div>

                                     
                                    </div>
                                </div>
                                @endif

                                <!-- Advanced Settings Tab -->
                                @if($activeTab === 'advanced')
                                <div id="advanced-tab" class="tab-pane">
                                    <div class="space-y-6">
                                        <!-- Risk Management -->
                                        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 p-6 rounded-xl border border-yellow-100">
                                            <h3 class="text-xl font-bold text-yellow-700 mb-6 flex items-center">
                                                <svg class="w-6 h-6 mr-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                                Risk Management & Limits
                                            </h3>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Loan Multiplier</label>
                                                    <input type="text" wire:model.defer="form.loan_multiplier" 
                                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all duration-200" 
                                                           placeholder="e.g., 3x savings"/>
                                                    @error('form.loan_multiplier') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Loan-to-Value Ratio (LTV)</label>
                                                    <input type="text" wire:model.defer="form.ltv" 
                                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all duration-200" 
                                                           placeholder="e.g., 80%"/>
                                                    @error('form.ltv') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Score Limit</label>
                                                    <input type="text" wire:model.defer="form.score_limit" 
                                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all duration-200" 
                                                           placeholder="Minimum credit score"/>
                                                    @error('form.score_limit') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- System Settings -->
                                        <div class="bg-gradient-to-r from-gray-50 to-slate-50 p-6 rounded-xl border border-gray-100">
                                            <h3 class="text-xl font-bold text-gray-700 mb-6 flex items-center">
                                                <svg class="w-6 h-6 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                </svg>
                                                System Settings
                                            </h3>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Inactivity Period (Days) <span class="text-red-500">*</span></label>
                                                    <input type="number" wire:model.defer="form.inactivity" 
                                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-gray-500 focus:ring-2 focus:ring-gray-200 transition-all duration-200" 
                                                           placeholder="30" min="0"/>
                                                    @error('form.inactivity') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Early Settlement Penalty (%)</label>
                                                    <input type="number" wire:model.defer="form.penalty_value" 
                                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-gray-500 focus:ring-2 focus:ring-gray-200 transition-all duration-200" 
                                                           placeholder="5.0" min="0" max="100" step="0.1"/>
                                                    <p class="text-xs text-gray-500 mt-1">Percentage penalty for early loan settlement</p>
                                                    @error('form.penalty_value') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Additional Charges</label>
                                                    <input type="text" wire:model.defer="form.charges" 
                                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-gray-500 focus:ring-2 focus:ring-gray-200 transition-all duration-200" 
                                                           placeholder="Specify additional charges"/>
                                                    @error('form.charges') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Member Requirements Tab -->
                                @if($activeTab === 'requirements')
                                <div id="requirements-tab" class="tab-pane">
                                    <div class="bg-gradient-to-r from-teal-50 to-cyan-50 p-6 rounded-xl border border-teal-100">
                                        <h3 class="text-xl font-bold text-teal-700 mb-6 flex items-center">
                                            <svg class="w-6 h-6 mr-3 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                            </svg>
                                            Member Requirements & Features
                                        </h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Allow Statement Generation</label>
                                                <select wire:model.defer="form.allow_statement_generation" 
                                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all duration-200">
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Send Notifications</label>
                                                <select wire:model.defer="form.send_notifications" 
                                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all duration-200">
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Require Member Image</label>
                                                <select wire:model.defer="form.require_image_member" 
                                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all duration-200">
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Require ID Image</label>
                                                <select wire:model.defer="form.require_image_id" 
                                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all duration-200">
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>
                                            <div class="col-span-2">
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Require Mobile Number</label>
                                                <select wire:model.defer="form.require_mobile_number" 
                                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all duration-200">
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                            </div>

                        </form>
                    </div>

                    <!-- Enhanced Modal Footer -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <button wire:click="closeModal" 
                                    class="inline-flex items-center px-6 py-3 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Cancel
                            </button>
                        </div>
                        <div class="flex items-center space-x-3">
                            @if($editingProduct)
                                <button wire:click="updateProduct" 
                                        class="inline-flex items-center px-8 py-3 bg-red-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 transition-all duration-200 shadow-lg">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    Update Loan Product
                                </button>
                            @else
                                <button wire:click="createProduct" 
                                        class="inline-flex items-center px-8 py-3 bg-red-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 transition-all duration-200 shadow-lg">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Create Loan Product
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif


</div>

<style nonce="{{ csp_nonce() }}">
    /* Enhanced Custom Styles */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Tab Styles */
    .tab-link {
        border-bottom-color: transparent;
        color: #6B7280;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .tab-link:hover {
        color: #3B82F6;
        background-color: rgba(59, 130, 246, 0.05);
    }
    
    .tab-link.active {
        border-bottom-color: #3B82F6;
        color: #3B82F6;
        background-color: rgba(59, 130, 246, 0.1);
        font-weight: 600;
    }
    
    .tab-link.active::before {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #3B82F6, #1D4ED8);
        border-radius: 2px;
    }
    
    .tab-pane {
        display: block;
    }

    /* Modal Animations */
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .modal-panel {
        animation: modalSlideIn 0.3s ease-out;
    }

    /* Form Enhancements */
    .form-section {
        transition: all 0.3s ease;
    }
    
    .form-section:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    /* Loading Animation */
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }
    
    .loading-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>




