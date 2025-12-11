<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Active Loans Management</h2>
                <p class="text-gray-600 mt-1">Manage and monitor all active loans in the system</p>
            </div>
            <div class="flex items-center gap-3">
                <button wire:click="toggleFilters" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                    </svg>
                    {{ $showFilters ? 'Hide' : 'Show' }} Filters
                </button>
                <button wire:click="clearFilters" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input wire:model.debounce.300ms="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search by loan number, client name, or phone...">
            </div>
        </div>

        <!-- Filters Section -->
        @if($showFilters)
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Branch Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select wire:model="selectedBranch" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select wire:model="selectedStatus" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Statuses</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Product Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                <select wire:model="selectedProduct" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Products</option>
                    @foreach($loanProducts as $product)
                        <option value="{{ $product->sub_product_id }}">{{ $product->sub_product_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Officer Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Loan Officer</label>
                <select wire:model="selectedOfficer" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Officers</option>
                    @foreach($loanOfficers as $officer)
                        <option value="{{ $officer->id }}">{{ $officer->first_name }} {{ $officer->last_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                <input wire:model="dateRange" type="text" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Start date to End date">
            </div>

            <!-- Amount Range Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount Range</label>
                <input wire:model="amountRange" type="text" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Min - Max amount">
            </div>

            <!-- Arrears Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Arrears Status</label>
                <select wire:model="arrearsFilter" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Arrears</option>
                    @foreach($arrearsFilterOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Per Page Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Per Page</label>
                <select wire:model="perPage" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        @endif
    </div>

    <!-- Bulk Actions Section -->
    @if(is_array($selectedLoans) && count($selectedLoans) > 0)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-sm font-medium text-blue-900">{{ count($selectedLoans) }} loan(s) selected</span>
            </div>
            <div class="flex items-center gap-3">
                <select wire:model="bulkAction" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Select Action</option>
                    <option value="export">Export Selected</option>
                    <option value="status_change">Change Status</option>
                    <option value="assign_officer">Assign Officer</option>
                </select>
                <button wire:click="performBulkAction" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-900 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Apply
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Loans Table -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" wire:click="selectAll" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('loan_account_number')">
                            <div class="flex items-center">
                                Loan Number
                                @if($sortField === 'loan_account_number')
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('client_number')">
                            <div class="flex items-center">
                                Client
                                @if($sortField === 'client_number')
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('principle')">
                            <div class="flex items-center">
                                Principal Amount
                                @if($sortField === 'principle')
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arrears</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                            <div class="flex items-center">
                                Status
                                @if($sortField === 'status')
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days in Arrears</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('created_at')">
                            <div class="flex items-center">
                                Created Date
                                @if($sortField === 'created_at')
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($loans as $loan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" wire:model="selectedLoans" value="{{ $loan->id }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $loan->loan_account_number }}</div>
                            <div class="text-sm text-gray-500">{{ $loan->loanProduct->sub_product_name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full" src="{{ $loan->client->profile_photo_url ?? asset('images/avatar.png') }}" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $loan->client->first_name ?? '' }} {{ $loan->client->last_name ?? '' }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $loan->client->phone_number ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ number_format($loan->principle, 2) }}</div>
                            <div class="text-sm text-gray-500">{{ $loan->loanBranch->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ number_format($loan->outstanding_balance ?? 0, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($loan->total_arrears > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ number_format($loan->total_arrears, 2) }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Current
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($loan->status === 'ACTIVE')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            @elseif($loan->status === 'IN_ARREAR')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    In Arrears
                                </span>
                            @elseif($loan->status === 'DELINQUENT')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Delinquent
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $loan->status }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($loan->days_in_arrears > 0)
                                <span class="text-sm text-red-600 font-medium">{{ $loan->days_in_arrears }} days</span>
                            @else
                                <span class="text-sm text-green-600">Current</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $loan->created_at ? $loan->created_at->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button wire:click="downloadRepaymentSchedule({{ $loan->id }})" class="text-blue-600 hover:text-blue-900 mr-3" title="Download Repayment Schedule">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </button>
                            <button wire:click="editLoan({{ $loan->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit Loan">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button wire:click="showLoanDetails({{ $loan->id }})" class="text-green-600 hover:text-green-900" title="View Details">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No loans found</h3>
                                <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($loans->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    @if($loans->onFirstPage())
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white cursor-not-allowed">
                            Previous
                        </span>
                    @else
                        <button wire:click="previousPage" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </button>
                    @endif

                    @if($loans->hasMorePages())
                        <button wire:click="nextPage" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </button>
                    @else
                        <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white cursor-not-allowed">
                            Next
                        </span>
                    @endif
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ $loans->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $loans->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $loans->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            @if($loans->onFirstPage())
                                <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-not-allowed">
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @else
                                <button wire:click="previousPage" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif

                            @foreach($loans->getUrlRange(1, $loans->lastPage()) as $page => $url)
                                @if($page == $loans->currentPage())
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button wire:click="gotoPage({{ $page }})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        {{ $page }}
                                    </button>
                                @endif
                            @endforeach

                            @if($loans->hasMorePages())
                                <button wire:click="nextPage" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @else
                                <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-not-allowed">
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @endif
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Loan Details Modal -->
    @if($showLoanDetails && $loanDetails)
    <div class="fixed inset-0 overflow-y-auto" style="z-index: 9999;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeLoanDetails"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Loan Details - {{ $loanDetails->loan_account_number }}
                            </h3>
                            
                            <!-- Loan Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Loan Information</h4>
                                    <dl class="space-y-2">
                                        <div>
                                            <dt class="text-sm text-gray-500">Principal Amount</dt>
                                            <dd class="text-sm font-medium text-gray-900">{{ number_format($loanDetails->principle, 2) }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Outstanding Balance</dt>
                                            <dd class="text-sm font-medium text-gray-900">{{ number_format($loanDetails->outstanding_balance ?? 0, 2) }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Interest Rate</dt>
                                            <dd class="text-sm font-medium text-gray-900">{{ $loanDetails->interest_rate ?? 'N/A' }}%</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Status</dt>
                                            <dd class="text-sm font-medium text-gray-900">{{ $loanDetails->status }}</dd>
                                        </div>
                                    </dl>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Client Information</h4>
                                    <dl class="space-y-2">
                                        <div>
                                            <dt class="text-sm text-gray-500">Name</dt>
                                            <dd class="text-sm font-medium text-gray-900">
                                                {{ $loanDetails->client->first_name ?? '' }} {{ $loanDetails->client->last_name ?? '' }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Phone</dt>
                                            <dd class="text-sm font-medium text-gray-900">{{ $loanDetails->client->phone_number ?? 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Branch</dt>
                                            <dd class="text-sm font-medium text-gray-900">{{ $loanDetails->loanBranch->name ?? 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Days in Arrears</dt>
                                            <dd class="text-sm font-medium text-gray-900">{{ $loanDetails->days_in_arrears ?? 0 }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <!-- Loan Schedule -->
                            @if($loanSchedule && count($loanSchedule) > 0)
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Payment Schedule</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Arrears</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($loanSchedule->take(5) as $schedule)
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                                    {{ \Carbon\Carbon::parse($schedule->installment_date)->format('M d, Y') }}
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($schedule->installment_amount, 2) }}
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    @if($schedule->completion_status === 'PAID')
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Paid
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            Unpaid
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($schedule->amount_in_arrears ?? 0, 2) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if(count($loanSchedule) > 5)
                                    <p class="text-sm text-gray-500 mt-2">Showing first 5 installments of {{ count($loanSchedule) }} total</p>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="closeLoanDetails" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Loan Modal -->
    @if($showEditModal && $selectedLoan)
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[95vh] flex flex-col relative z-50">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 flex-shrink-0">
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Edit Loan - {{ $selectedLoan->loan_account_number }}</h3>
                <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-6">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-yellow-800">Edit Mode</h4>
                            <p class="text-sm text-yellow-700">You are editing loan details. Changes will be saved when you click Update.</p>
                        </div>
                    </div>
                </div>

                <!-- Edit Form Content -->
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Loan Account Number</label>
                                <input type="text" wire:model="editForm.loan_account_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select wire:model="editForm.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    <option value="ACTIVE">Active</option>
                                    <option value="IN_ARREAR">In Arrears</option>
                                    <option value="DELINQUENT">Delinquent</option>
                                    <option value="RECOVERY">Recovery</option>
                                    <option value="WRITEN_OFF">Written Off</option>
                                    <option value="CLOSED">Closed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Principal Amount</label>
                                <input type="number" step="0.01" wire:model="editForm.principle" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Interest Rate (%)</label>
                                <input type="number" step="0.01" wire:model="editForm.interest_rate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Additional Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tenure (Months)</label>
                                <input type="number" wire:model="editForm.tenure" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                <select wire:model="editForm.branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between gap-3 p-4 sm:p-6 border-t border-gray-200 flex-shrink-0">
                <div class="text-sm text-gray-600">
                    <span>Last updated: {{ $selectedLoan->updated_at ? $selectedLoan->updated_at->format('M d, Y H:i') : 'N/A' }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <button wire:click="closeEditModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button wire:click="updateLoan" class="inline-flex items-center px-6 py-2 font-semibold text-white bg-blue-900 rounded-lg shadow-lg hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Update Loan
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Detailed Loan Information Modal -->
    @if($showDetailsModal && $selectedLoan)
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-20 " style="margin-top: -50px;">
        <div class="bg-white rounded-2xl shadow-xl w-2/3 max-h-[95vh] flex flex-col relative z-50">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 flex-shrink-0">
                @php
                    $loanControlNumber = null;
                    if ($selectedLoan && $selectedLoan->client && $selectedLoan->client->client_number) {
                        $loanControlNumber = DB::table('bills')->where('client_number', $selectedLoan->client->client_number)->where('amount_due', '>', 0)->first();
                    }
                @endphp
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Loan Details - {{ $loanControlNumber->control_number ?? $selectedLoan->loan_number }}</h3>
                <button wire:click="closeDetailsModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-6" style="max-height: calc(95vh - 140px);">
                <!-- Client & Loan Summary -->
                <div class="mb-6">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                            {{ strtoupper(substr(optional($selectedLoan->client)->first_name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-bold text-gray-900">
                                {{ optional($selectedLoan->client)->first_name ?? '' }} {{ optional($selectedLoan->client)->middle_name ?? '' }} {{ optional($selectedLoan->client)->last_name ?? '' }}
                            </h4>
                            <p class="text-sm text-gray-600">ID: {{ optional($selectedLoan->client)->client_number ?? 'N/A' }} | {{ optional($selectedLoan->client)->phone_number ?? 'N/A' }}</p>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $selectedLoan->loanProduct->sub_product_name ?? 'N/A' }}
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $selectedLoan->status }}
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ optional($selectedLoan->client)->membership_type ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                        <!-- Loan Status Indicator -->
                        <div class="text-right">
                            <div class="text-xs text-gray-500 mb-1">Loan Status</div>
                            <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                @php
                                    $progressWidth = 0;
                                    if ($selectedLoan->status === 'ACTIVE') $progressWidth = 100;
                                    elseif ($selectedLoan->status === 'IN_ARREAR') $progressWidth = 75;
                                    elseif ($selectedLoan->status === 'DELINQUENT') $progressWidth = 50;
                                    elseif ($selectedLoan->status === 'RECOVERY') $progressWidth = 25;
                                    else $progressWidth = 0;
                                @endphp
                                <div class="h-full bg-green-500 rounded-full" style="width: {{ $progressWidth }}%"></div>
                            </div>
                            <div class="text-xs text-green-600 font-medium mt-1">{{ $selectedLoan->status }}</div>
                        </div>
                    </div>
                </div>

                <!-- General Loan Information -->
                <div class="mb-6">
                    <div class="relative flex py-3 items-center mb-4">
                        <div class="flex-grow border-t border-gray-400"></div>
                        <span class="flex-shrink mx-4 text-gray-500 font-semibold">General Loan Information</span>
                        <div class="flex-grow border-t border-gray-400"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 bg-gray-50 rounded-xl p-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Principal Amount</label>
                            <div class="text-sm font-semibold text-gray-900">{{ number_format($selectedLoan->principle, 2) }} TZS</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Interest Rate</label>
                            <div class="text-sm font-semibold text-gray-900">{{ $selectedLoan->interest_rate ?? 'N/A' }}%</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tenure</label>
                            <div class="text-sm font-semibold text-gray-900">{{ $selectedLoan->tenure ?? 'N/A' }} months</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Outstanding Balance</label>
                            <div class="text-sm font-semibold text-gray-900">
                                {{ number_format(DB::table('loans_schedules')->where('loan_id', $selectedLoan->loan_id)->where('completion_status', '!=', 'CLOSED')->sum('installment'), 2) }} TZS
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Collateral Value</label>
                            <div class="text-sm font-semibold text-gray-900">
                                {{ number_format(DB::table('collaterals')->where('loan_id', $selectedLoan->id)->sum(DB::raw('CAST(collateral_value AS DECIMAL(10,2))')), 2) }} TZS
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Branch</label>
                            <div class="text-sm font-semibold text-gray-900">{{ $selectedLoan->loanBranch->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Client Information -->
                <div class="mb-6">
                    <div class="relative flex py-3 items-center mb-4">
                        <div class="flex-grow border-t border-gray-400"></div>
                        <span class="flex-shrink mx-4 text-gray-500 font-semibold">Client Information</span>
                        <div class="flex-grow border-t border-gray-400"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 bg-gray-50 rounded-xl p-4">
                        <!-- Basic Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Membership Type</label>
                            <div class="text-sm font-semibold text-gray-900">{{ optional($selectedLoan->client)->membership_type ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <div class="text-sm font-semibold text-gray-900">{{ optional($selectedLoan->client)->phone_number ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <div class="text-sm font-semibold text-gray-900">{{ optional($selectedLoan->client)->email ?? 'N/A' }}</div>
                        </div>
                        
                        <!-- Individual Specific Fields -->
                        @if((optional($selectedLoan->client)->membership_type ?? '') === 'Individual')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
                                <div class="text-sm font-semibold text-gray-900">{{ optional($selectedLoan->client)->date_of_birth ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Gender</label>
                                <div class="text-sm font-semibold text-gray-900">{{ optional($selectedLoan->client)->gender ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Marital Status</label>
                                <div class="text-sm font-semibold text-gray-900">{{ optional($selectedLoan->client)->marital_status ?? 'N/A' }}</div>
                            </div>
                        @endif
                        
                        <!-- Business Specific Fields -->
                        @if((optional($selectedLoan->client)->membership_type ?? '') === 'Business' || (optional($selectedLoan->client)->membership_type ?? '') === 'Group')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Business Name</label>
                                <div class="text-sm font-semibold text-gray-900">{{ optional($selectedLoan->client)->business_name ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Incorporation Number</label>
                                <div class="text-sm font-semibold text-gray-900">{{ optional($selectedLoan->client)->incorporation_number ?? 'N/A' }}</div>
                            </div>
                        @endif
                        
                        <!-- Common Fields -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <div class="text-sm font-semibold text-gray-900">{{ optional($selectedLoan->client)->address ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">TIN Number</label>
                            <div class="text-sm font-semibold text-gray-900">{{ optional($selectedLoan->client)->tin_number ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nationality</label>
                            <div class="text-sm font-semibold text-gray-900">{{ optional($selectedLoan->client)->nationarity ?? 'N/A' }}</div>
                        </div>
                        
                        <!-- Financial Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Income Available</label>
                            <div class="text-sm font-semibold text-gray-900">{{ number_format(optional($selectedLoan->client)->income_available ?? 0, 2) }} TZS</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Income Source</label>
                            <div class="text-sm font-semibold text-gray-900">{{ optional($selectedLoan->client)->income_source ?? 'N/A' }}</div>
                        </div>
                        
                        <!-- Guarantor Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Guarantor Name</label>
                            <div class="text-sm font-semibold text-gray-900">
                                {{ (optional($selectedLoan->client)->guarantor_first_name ?? '') . ' ' . (optional($selectedLoan->client)->guarantor_last_name ?? '') }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Guarantor Phone</label>
                            <div class="text-sm font-semibold text-gray-900">{{ optional($selectedLoan->client)->guarantor_full_name ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Guarantor Email</label>
                            <div class="text-sm font-semibold text-gray-900">{{ optional($selectedLoan->client)->guarantor_email ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Collateral Information -->
                @php
                    $collaterals = DB::table('collaterals')->where('loan_id', $selectedLoan->loan_id)->get();
                @endphp
                @if($collaterals && count($collaterals) > 0)
                <div class="mb-6">
                    <div class="relative flex py-3 items-center mb-4">
                        <div class="flex-grow border-t border-gray-400"></div>
                        <span class="flex-shrink mx-4 text-gray-500 font-semibold">Collateral Information</span>
                        <div class="flex-grow border-t border-gray-400"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($collaterals as $collateral)
                        <div class="bg-white border-2 border-blue-900 rounded-xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <span class="bg-blue-900 text-white rounded-full px-3 py-1 text-sm font-medium">
                                    Collateral #{{ $loop->iteration }}
                                </span>
                                <span class="text-sm text-gray-500">{{ str_replace("_", " ", $collateral->main_collateral_type ?? 'N/A') }}</span>
                            </div>
                            
                            <!-- Basic Collateral Information -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Collateral Type</label>
                                    <div class="text-sm font-semibold text-gray-900">{{ str_replace("_", " ", $collateral->main_collateral_type ?? 'N/A') }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Category</label>
                                    <div class="text-sm font-semibold text-gray-900">{{ $collateral->collateral_category ?? 'N/A' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Value</label>
                                    <div class="text-sm font-semibold text-gray-900">{{ number_format($collateral->collateral_value ?? 0, 2) }} TZS</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Account Number</label>
                                    <div class="text-sm font-semibold text-gray-900">{{ DB::table('accounts')->where('id', $collateral->account_id)->value('account_number') ?? 'N/A' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <div class="text-sm font-semibold text-gray-900">{{ $collateral->description ?? 'N/A' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <div class="text-sm font-semibold text-gray-900">{{ $collateral->release_status ?? 'Held' }}</div>
                                </div>
                            </div>

                            <!-- Owner Information -->
                            @if($collateral->type_of_owner || $collateral->collateral_owner_full_name)
                            <div class="border-t border-gray-200 pt-4 mb-4">
                                <div class="font-semibold text-gray-700 mb-3">Owner Information</div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Type of Owner</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->type_of_owner ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Relationship</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->relationship ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Owner Name</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->collateral_owner_full_name ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">NIDA Number</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->collateral_owner_nida ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->collateral_owner_contact_number ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Address</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->collateral_owner_residential_address ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Spouse Information -->
                            @if($collateral->collateral_owner_spouse_full_name)
                            <div class="border-t border-gray-200 pt-4 mb-4">
                                <div class="font-semibold text-gray-700 mb-3">Spouse Information</div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Spouse Name</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->collateral_owner_spouse_full_name ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Spouse NIDA</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->collateral_owner_spouse_nida ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Spouse Contact</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->collateral_owner_spouse_contact_number ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Spouse Address</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->collateral_owner_spouse_residential_address ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Company Information -->
                            @if($collateral->company_registered_name)
                            <div class="border-t border-gray-200 pt-4 mb-4">
                                <div class="font-semibold text-gray-700 mb-3">Company Information</div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Company Name</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->company_registered_name ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">License Number</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->business_licence_number ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">TIN Number</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->tin ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Director NIDA</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->director_nida ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Director Contact</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->director_contact ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Business Address</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->business_address ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Property Information -->
                            @if($collateral->address || $collateral->region)
                            <div class="border-t border-gray-200 pt-4 mb-4">
                                <div class="font-semibold text-gray-700 mb-3">Property Information</div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Region</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->region ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">District</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->district ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Ward</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->ward ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Building Number</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->building_number ?? 'N/A' }}</div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Full Address</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->address ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Insurance Information -->
                            @if($collateral->policy_number || $collateral->company_name)
                            <div class="border-t border-gray-200 pt-4 mb-4">
                                <div class="font-semibold text-gray-700 mb-3">Insurance Information</div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Policy Number</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->policy_number ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Insurance Company</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->company_name ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Coverage Details</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->coverage_details ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Expiration Date</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->expiration_date ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Valuation Information -->
                            @if($collateral->date_of_valuation || $collateral->name_of_valuer)
                            <div class="border-t border-gray-200 pt-4">
                                <div class="font-semibold text-gray-700 mb-3">Valuation Information</div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Date of Valuation</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->date_of_valuation ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Valuation Method</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->valuation_method_used ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Name of Valuer</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->name_of_valuer ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Physical Condition</label>
                                        <div class="text-sm font-semibold text-gray-900">{{ $collateral->physical_condition ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Member SACCO Financial Details -->
                @php
                    $memberLoans = collect();
                    if ($selectedLoan && $selectedLoan->client && $selectedLoan->client->client_number) {
                        $memberLoans = DB::table('loans')->where('client_number', $selectedLoan->client->client_number)->where('status', 'active')->get();
                    }
                @endphp
                <div class="mb-6">
                    <div class="relative flex py-3 items-center mb-4">
                        <div class="flex-grow border-t border-gray-400"></div>
                        <span class="flex-shrink mx-4 text-gray-500 font-semibold">Other Active Loans</span>
                        <div class="flex-grow border-t border-gray-400"></div>
                    </div>

                    <!-- Ongoing Loans Section -->
                    <div class="mb-6">
                        <div class="font-semibold text-gray-700 mb-3">Member's Other Active Loans</div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-gray-500 text-xs">
                                        <th class="py-1 px-2 text-left">Loan #</th>
                                        <th class="py-1 px-2 text-left">Type</th>
                                        <th class="py-1 px-2 text-left">Amount</th>
                                        <th class="py-1 px-2 text-left">Balance</th>
                                        <th class="py-1 px-2 text-left">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($memberLoans as $loan)
                                        <tr>
                                            <td class="py-1 px-2">{{ $loan->loan_number }}</td>
                                            <td class="py-1 px-2">{{ $loan->loan_type }}</td>
                                            <td class="py-1 px-2">{{ number_format($loan->amount,2) }}</td>
                                            <td class="py-1 px-2">{{ number_format($loan->balance,2) }}</td>
                                            <td class="py-1 px-2">
                                                <span class="inline-block rounded px-2 py-0.5 text-xs {{ $loan->status==='active'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ $loan->status }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-gray-400 py-2">No other active loans</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payment Schedule -->
                @if($loanSchedule && count($loanSchedule) > 0)
                <div class="mb-6">
                    <div class="font-semibold text-gray-700 mb-3">Complete Payment Schedule ({{ count($loanSchedule) }} Installments)</div>
                    <div class="overflow-x-auto border border-gray-300 rounded-lg">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-700 text-xs font-semibold">
                                    <th class="py-2 px-2 text-left border-r border-gray-300">Due Date</th>
                                    <th class="py-2 px-2 text-left border-r border-gray-300">Installment</th>
                                    <th class="py-2 px-2 text-left border-r border-gray-300">Principal</th>
                                    <th class="py-2 px-2 text-left border-r border-gray-300">Interest</th>
                                    <th class="py-2 px-2 text-left border-r border-gray-300">Opening Balance</th>
                                    <th class="py-2 px-2 text-left border-r border-gray-300">Closing Balance</th>
                                    <th class="py-2 px-2 text-left border-r border-gray-300">Payment</th>
                                    <th class="py-2 px-2 text-left border-r border-gray-300">Status</th>
                                    <th class="py-2 px-2 text-left border-r border-gray-300">Amount Arrears</th>
                                    <th class="py-2 px-2 text-left">Days Arrears</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loanSchedule as $schedule)
                                    <tr class="{{ $loop->odd ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition-colors border-b border-gray-200">
                                        <td class="py-2 px-2 border-r border-gray-200">{{ \Carbon\Carbon::parse($schedule->installment_date)->format('M d, Y') }}</td>
                                        <td class="py-2 px-2 border-r border-gray-200 font-semibold">{{ number_format($schedule->installment, 2) }}</td>
                                        <td class="py-2 px-2 border-r border-gray-200">{{ number_format($schedule->principle, 2) }}</td>
                                        <td class="py-2 px-2 border-r border-gray-200">{{ number_format($schedule->interest, 2) }}</td>
                                        <td class="py-2 px-2 border-r border-gray-200">{{ number_format($schedule->opening_balance, 2) }}</td>
                                        <td class="py-2 px-2 border-r border-gray-200">{{ number_format($schedule->closing_balance, 2) }}</td>
                                        <td class="py-2 px-2 border-r border-gray-200 font-medium {{ ($schedule->payment ?? 0) > 0 ? 'text-green-600' : 'text-gray-500' }}">
                                            {{ number_format($schedule->payment ?? 0, 2) }}
                                        </td>
                                        <td class="py-2 px-2 border-r border-gray-200">
                                            <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium {{ $schedule->completion_status==='PAID'?'bg-green-100 text-green-700 border border-green-300':'bg-yellow-100 text-yellow-700 border border-yellow-300' }}">
                                                {{ $schedule->completion_status }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-2 border-r border-gray-200 {{ ($schedule->amount_in_arrears ?? 0) > 0 ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                            {{ number_format($schedule->amount_in_arrears ?? 0, 2) }}
                                        </td>
                                        <td class="py-2 px-2 {{ ($schedule->days_in_arrears ?? 0) > 0 ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                            @if(($schedule->days_in_arrears ?? 0) > 0)
                                                {{ $schedule->days_in_arrears }} days
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between gap-3 p-4 sm:p-6 border-t border-gray-200 flex-shrink-0">
                <div class="text-sm text-gray-600">
                    <span>Loan ID: {{ $selectedLoan->loan_id }} | Account: {{ $selectedLoan->loan_account_number }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <button wire:click="closeDetailsModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Close
                    </button>
                    <button wire:click="editLoan({{ $selectedLoan->id }})" class="inline-flex items-center px-4 py-2 font-semibold text-white bg-indigo-600 rounded-lg shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Loan
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
