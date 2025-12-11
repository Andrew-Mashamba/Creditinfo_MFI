<div class="p-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-gray-500 text-sm font-medium">Total Dividends</h3>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_dividends'], 2) }} TZS</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-gray-500 text-sm font-medium">Total Paid</h3>
            <p class="text-2xl font-bold text-green-600">{{ number_format($summary['total_paid'], 2) }} TZS</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-gray-500 text-sm font-medium">Total Pending</h3>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($summary['total_pending'], 2) }} TZS</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-gray-500 text-sm font-medium">Members</h3>
            <p class="text-2xl font-bold text-gray-900">{{ $summary['member_count'] }}</p>
        </div>
    </div>

    <!-- Actions Bar -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 flex flex-wrap items-center justify-between gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <input type="text" wire:model.debounce.300ms="search" placeholder="Search by member number or name..." 
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Filters -->
            <div class="flex gap-4">
                <select wire:model="yearFilter" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Years</option>
                    @foreach($summary['years'] as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>

                <select wire:model="statusFilter" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4">
                <button wire:click="$set('showDeclareModal', true)" 
                    class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Declare Dividend
                </button>
                
                <!-- Process Payments Button with Progress -->
                <div class="relative">
                    <button wire:click="processPayments" 
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        @if($isProcessing) disabled @endif
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        
                     
                        @if($isProcessing)  
                        <span class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                        @else 

                        <span wire:loading.remove wire:target="processPayments">
                            Process Payments
                        </span>

                      
                        @endif
                        
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Processing Progress Indicator -->
    @if($isProcessing)
    <div class="bg-white rounded-lg shadow mb-6 border-l-4 border-green-500" wire:poll.2s>
        <div class="p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-medium text-gray-900">Processing Dividend Payments</h3>
                <span class="text-sm text-gray-500">{{ $processingProgress }}%</span>
            </div>
            <p class="text-sm text-gray-600 mb-3">{{ $processingMessage }}</p>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                <div class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: {{ $processingProgress }}%"></div>
            </div>
            
            <!-- Progress Stats -->
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Total:</span>
                    <span class="font-medium ml-1">{{ $totalToProcess }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Processed:</span>
                    <span class="font-medium text-green-600 ml-1">{{ $processedCount }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Failed:</span>
                    <span class="font-medium text-red-600 ml-1">{{ $failedCount }}</span>
                </div>
            </div>
            
            @if($currentProcessId)
            <div class="mt-2 text-xs text-gray-500">
                Process ID: {{ $currentProcessId }}
            </div>
            @endif
            
            @if($processingStatus === 'completed' || $processingStatus === 'error')
            <div class="mt-3 flex justify-end">
                <button wire:click="resetProcessingState" 
                    class="px-3 py-1 text-sm bg-gray-600 text-white rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Close
                </button>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Dividends Table -->
    <div class="bg-white rounded-lg shadow custom-scrollbar overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th wire:click="sortBy('member_number')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Member Number
                        @if($sortField === 'member_number')
                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('member_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Member Name
                        @if($sortField === 'member_name')
                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('total_shares')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Shares
                        @if($sortField === 'total_shares')
                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('share_value')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Share Value
                        @if($sortField === 'share_value')
                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('dividend_rate')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Rate (%)
                        @if($sortField === 'dividend_rate')
                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('dividend_amount')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Accumulated
                        @if($sortField === 'dividend_amount')
                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('total_paid_dividends')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Paid
                        @if($sortField === 'total_paid_dividends')
                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('total_pending_dividends')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Pending
                        @if($sortField === 'total_pending_dividends')
                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Status
                        @if($sortField === 'status')
                            @if($sortDirection === 'asc') ↑ @else ↓ @endif
                        @endif
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($dividends as $dividend)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $dividend->member_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $dividend->member_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($dividend->total_shares) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($dividend->share_value, 2) }} TZS
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $dividend->dividend_rate ? number_format($dividend->dividend_rate, 2) . '%' : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($dividend->dividend_amount, 2) }} TZS
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                            {{ number_format($dividend->total_paid_dividends, 2) }} TZS
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                            {{ number_format($dividend->total_pending_dividends, 2) }} TZS
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($dividend->status === 'paid') bg-green-100 text-green-800
                                @elseif($dividend->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($dividend->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="viewDetails({{ $dividend->id }})" 
                                class="text-blue-600 hover:text-blue-900 focus:outline-none focus:underline">
                                View Details
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $dividends->links() }}
        </div>
    </div>

    <!-- Declare Dividend Modal -->
    @if($showDeclareModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="declareDividend">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Declare Dividend
                                </h3>
                                <div class="mt-4 space-y-4 text-left">
                                    <div>
                                        <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                                        <input type="number" wire:model="year" id="year" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        @error('year') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="dividendRate" class="block text-sm font-medium text-gray-700">Dividend Rate (%)</label>
                                        <input type="number" step="0.01" wire:model="dividendRate" id="dividendRate" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        @error('dividendRate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="paymentMode" class="block text-sm font-medium text-gray-700">Payment Mode</label>
                                        <select wire:model="paymentMode" id="paymentMode" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="bank">Bank Transfer</option>
                                            <option value="cash">Cash</option>
                                            <option value="shares">Reinvest in Shares</option>
                                        </select>
                                        @error('paymentMode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="narration" class="block text-sm font-medium text-gray-700">Narration</label>
                                        <textarea wire:model="narration" id="narration" rows="3" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                        @error('narration') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Declare
                        </button>
                        <button type="button" wire:click="$set('showDeclareModal', false)"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif


    <!-- Dividend Details Modal -->
    @if($showDetailsModal && $selectedDividend)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
            wire:click.self="$set('showDetailsModal', false)">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4 md:mx-0 overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">Dividend Details</h2>
                    <button wire:click="$set('showDetailsModal', false)"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Member Info -->
                        <div class="col-span-1">
                            <p class="text-sm font-medium text-gray-500">Member Number</p>
                            <p class="text-sm text-gray-800">{{ $selectedDividend->member_number }}</p>
                        </div>
                        <div class="col-span-1">
                            <p class="text-sm font-medium text-gray-500">Member Name</p>
                            <p class="text-sm text-gray-800">{{ $selectedDividend->member_name }}</p>
                        </div>

                        <!-- Share Info -->
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Shares</p>
                            <p class="text-sm text-gray-800">{{ number_format($selectedDividend->total_shares) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Share Value</p>
                            <p class="text-sm text-gray-800">{{ number_format($selectedDividend->share_value, 2) }} TZS</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Dividend Rate</p>
                            <p class="text-sm text-gray-800">
                                {{ $selectedDividend->dividend_rate ? number_format($selectedDividend->dividend_rate, 2) . '%' : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Accumulated Dividends</p>
                            <p class="text-sm text-blue-700 font-medium">
                                {{ number_format($selectedDividend->dividend_amount, 2) }} TZS</p>
                        </div>

                        <!-- Payment Info -->
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Paid</p>
                            <p class="text-sm text-green-600 font-medium">
                                {{ number_format($selectedDividend->total_paid_dividends, 2) }} TZS</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Pending</p>
                            <p class="text-sm text-yellow-600 font-medium">
                                {{ number_format($selectedDividend->total_pending_dividends, 2) }} TZS</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="text-sm text-gray-800">{{ ucfirst($selectedDividend->status) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Payment Mode</p>
                            <p class="text-sm text-gray-800">{{ ucfirst($selectedDividend->payment_mode ?? 'Not Set') }}</p>
                        </div>

                        <!-- Date Info -->
                        <div>
                            <p class="text-sm font-medium text-gray-500">Last Dividend Date</p>
                            <p class="text-sm text-gray-800">
                                {{ $selectedDividend->calculated_at ? Carbon\Carbon::parse($selectedDividend->calculated_at)->format('Y-m-d H:i:s') : 'Not Available' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Last Payment Date</p>
                            <p class="text-sm text-gray-800">
                                {{ $selectedDividend->paid_at ? Carbon\Carbon::parse($selectedDividend->paid_at)->format('Y-m-d H:i:s') : 'Not Paid' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <button wire:click="$set('showDetailsModal', false)"
                        class="inline-flex items-center px-4 py-2 bg-blue-900 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif


    <!-- Processing Progress Modal -->
    @if($isProcessing)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="progress-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            @if($processingStatus === 'completed')
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @elseif($processingStatus === 'error')
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            @else
                                <svg class="animate-spin h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="progress-modal-title">
                                Processing Dividend Payments
                            </h3>
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 mb-4">{{ $processingMessage }}</p>
                                
                                <!-- Progress Bar -->
                                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
                                    <div class="bg-green-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $processingProgress }}%"></div>
                                </div>
                                
                                <!-- Progress Details -->
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Progress:</span>
                                        <span class="font-medium">{{ $processingProgress }}%</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Total:</span>
                                        <span class="font-medium">{{ $totalToProcess }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Processed:</span>
                                        <span class="font-medium text-green-600">{{ $processedCount }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Failed:</span>
                                        <span class="font-medium text-red-600">{{ $failedCount }}</span>
                                    </div>
                                </div>
                                
                                @if($currentProcessId)
                                <div class="mt-3 p-2 bg-gray-50 rounded text-xs text-gray-600">
                                    Process ID: {{ $currentProcessId }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    @if($processingStatus === 'completed' || $processingStatus === 'error')
                        <button wire:click="$set('isProcessing', false)" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <style nonce="{{ csp_nonce() }}">
        .custom-scrollbar::-webkit-scrollbar {
            width: 2px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #2563eb;
        }
    </style>
</div>
