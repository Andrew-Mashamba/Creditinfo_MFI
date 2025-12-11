<div class="bg-white rounded-lg shadow-sm">
    <!-- Notification Area -->
    @if (session()->has('message'))
        <div class="mx-6 mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-start">
            <svg class="flex-shrink-0 w-5 h-5 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <h3 class="font-semibold">Success!</h3>
                <p class="mt-1">{{ session('message') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="ml-4 text-green-700 hover:text-green-900">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mx-6 mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-start">
            <svg class="flex-shrink-0 w-5 h-5 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <h3 class="font-semibold">Error!</h3>
                <p class="mt-1 whitespace-pre-line">{{ session('error') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="ml-4 text-red-700 hover:text-red-900">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="mx-6 mt-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg flex items-start">
            <svg class="flex-shrink-0 w-5 h-5 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <h3 class="font-semibold">Warning!</h3>
                <p class="mt-1">{{ session('warning') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="ml-4 text-yellow-700 hover:text-yellow-900">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="border-b border-gray-200 px-6 py-4">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $companyName }}</h2>
                <h3 class="text-lg font-semibold text-gray-700 mt-1">Asset Depreciation Management</h3>
            </div>
            
            <!-- Export Buttons -->
            <div class="flex space-x-2">
                <button wire:click="exportToExcel" 
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Excel
                </button>
                <button wire:click="exportToPDF" 
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Export PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Controls Section -->
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Year Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                <select wire:model="selectedYear" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    @for($year = date('Y'); $year >= date('Y') - 10; $year--)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>

            <!-- Month Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                <select wire:model="selectedMonth" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    @foreach(['1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May', '6' => 'June', 
                             '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'] as $value => $month)
                        <option value="{{ $value }}">{{ $month }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Period Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Period</label>
                <select wire:model="depreciationPeriod" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>

            <!-- Asset Category -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select wire:model="assetCategory" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="all">All Categories</option>
                    <option value="ppe">Property, Plant & Equipment</option>
                    <option value="intangible">Intangible Assets</option>
                    <option value="other">Other Assets</option>
                </select>
            </div>

            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" 
                       wire:model.debounce.300ms="searchTerm" 
                       placeholder="Search assets..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>
        </div>

        <!-- View Mode Tabs -->
        <div class="flex space-x-1 mt-4">
            <button wire:click="changeViewMode('summary')" 
                    class="px-4 py-2 rounded-lg transition-colors {{ $viewMode === 'summary' ? 'bg-red-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                Summary
            </button>
            <button wire:click="changeViewMode('detailed')" 
                    class="px-4 py-2 rounded-lg transition-colors {{ $viewMode === 'detailed' ? 'bg-red-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                Detailed
            </button>
            <button wire:click="changeViewMode('journal')" 
                    class="px-4 py-2 rounded-lg transition-colors {{ $viewMode === 'journal' ? 'bg-red-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                Journal Entries
            </button>
            <button wire:click="changeViewMode('history')" 
                    class="px-4 py-2 rounded-lg transition-colors flex items-center {{ $viewMode === 'history' ? 'bg-red-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Posting History
                @if($this->hasPostedDepreciation())
                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full {{ $viewMode === 'history' ? 'bg-white text-red-500' : 'bg-red-500 text-white' }}">
                        {{ count($this->getPostingHistory()) }}
                    </span>
                @endif
            </button>
        </div>
    </div>

    <!-- Content Section -->
    <div class="p-6">
        @if($viewMode === 'summary')
            <!-- Summary View -->
            <div class="space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-red-50 rounded-lg p-4">
                        <p class="text-sm text-red-600 font-medium">Total Asset Cost</p>
                        <p class="text-2xl font-bold text-red-900">{{ $this->formatNumber($totals['asset_cost']) }}</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <p class="text-sm text-yellow-600 font-medium">Accumulated Depreciation</p>
                        <p class="text-2xl font-bold text-yellow-900">{{ $this->formatNumber($totals['accumulated_depreciation']) }}</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4">
                        <p class="text-sm text-red-600 font-medium">Current Period</p>
                        <p class="text-2xl font-bold text-red-900">{{ $this->formatNumber($totals['current_depreciation']) }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-sm text-green-600 font-medium">Net Book Value</p>
                        <p class="text-2xl font-bold text-green-900">{{ $this->formatNumber($totals['net_book_value']) }}</p>
                    </div>
                </div>

                <!-- Summary Table by Category -->
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Category</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Assets</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Total Cost</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Accum. Depreciation</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Current Period</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Net Book Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($depreciationSummary as $summary)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 border border-gray-300">{{ $summary['category'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-900 border border-gray-300">{{ $summary['asset_count'] }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900 border border-gray-300">{{ $this->formatNumber($summary['total_cost']) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900 border border-gray-300">{{ $this->formatNumber($summary['accumulated_depreciation']) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900 border border-gray-300">{{ $this->formatNumber($summary['current_depreciation']) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900 border border-gray-300">{{ $this->formatNumber($summary['net_book_value']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-center text-gray-500 border border-gray-300">No depreciation data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-100 font-bold">
                                <td class="px-4 py-3 text-sm border border-gray-300">TOTAL</td>
                                <td class="px-4 py-3 text-sm text-center border border-gray-300">{{ count($assets) }}</td>
                                <td class="px-4 py-3 text-sm text-right border border-gray-300">{{ $this->formatNumber($totals['asset_cost']) }}</td>
                                <td class="px-4 py-3 text-sm text-right border border-gray-300">{{ $this->formatNumber($totals['accumulated_depreciation']) }}</td>
                                <td class="px-4 py-3 text-sm text-right border border-gray-300">{{ $this->formatNumber($totals['current_depreciation']) }}</td>
                                <td class="px-4 py-3 text-sm text-right border border-gray-300">{{ $this->formatNumber($totals['net_book_value']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        @elseif($viewMode === 'detailed')
            <!-- Detailed Asset View -->
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Asset Name</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Account</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Category</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Purchase Date</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Life (Years)</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Method</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Initial Value</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Salvage Value</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Monthly Depr.</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Accum. Depr.</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Current Period</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">NBV</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-sm text-gray-900 border border-gray-300">{{ $asset['name'] }}</td>
                                <td class="px-3 py-2 text-sm text-gray-900 border border-gray-300">{{ $asset['account_number'] }}</td>
                                <td class="px-3 py-2 text-sm text-gray-900 border border-gray-300">{{ $asset['category'] }}</td>
                                <td class="px-3 py-2 text-sm text-center text-gray-900 border border-gray-300">{{ $asset['purchase_date'] }}</td>
                                <td class="px-3 py-2 text-sm text-center text-gray-900 border border-gray-300">{{ $asset['useful_life'] }}</td>
                                <td class="px-3 py-2 text-sm text-center text-gray-900 border border-gray-300">{{ $asset['depreciation_method'] }}</td>
                                <td class="px-3 py-2 text-sm text-right text-gray-900 border border-gray-300">{{ $this->formatNumber($asset['initial_value']) }}</td>
                                <td class="px-3 py-2 text-sm text-right text-gray-900 border border-gray-300">{{ $this->formatNumber($asset['salvage_value']) }}</td>
                                <td class="px-3 py-2 text-sm text-right text-gray-900 border border-gray-300">{{ $this->formatNumber($asset['monthly_depreciation']) }}</td>
                                <td class="px-3 py-2 text-sm text-right text-gray-900 border border-gray-300">{{ $this->formatNumber($asset['accumulated_depreciation']) }}</td>
                                <td class="px-3 py-2 text-sm text-right text-gray-900 border border-gray-300">{{ $this->formatNumber($asset['current_period_depreciation']) }}</td>
                                <td class="px-3 py-2 text-sm text-right text-gray-900 border border-gray-300">{{ $this->formatNumber($asset['net_book_value']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-4 py-3 text-center text-gray-500 border border-gray-300">No assets found</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="6" class="px-3 py-2 text-sm border border-gray-300">TOTAL</td>
                            <td class="px-3 py-2 text-sm text-right border border-gray-300">{{ $this->formatNumber($totals['asset_cost']) }}</td>
                            <td class="px-3 py-2 text-sm text-right border border-gray-300">-</td>
                            <td class="px-3 py-2 text-sm text-right border border-gray-300">-</td>
                            <td class="px-3 py-2 text-sm text-right border border-gray-300">{{ $this->formatNumber($totals['accumulated_depreciation']) }}</td>
                            <td class="px-3 py-2 text-sm text-right border border-gray-300">{{ $this->formatNumber($totals['current_depreciation']) }}</td>
                            <td class="px-3 py-2 text-sm text-right border border-gray-300">{{ $this->formatNumber($totals['net_book_value']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        @elseif($viewMode === 'journal')
            <!-- Journal Entries View -->
            <div class="space-y-6">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Review the journal entries below before posting. Once posted, these entries will be recorded in the general ledger.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Journal Entries Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Entry #</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Account Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Account Code</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Debit</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Credit</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border border-gray-300">Description</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($journalEntries as $entry)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 border border-gray-300">{{ $entry['entry_number'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 border border-gray-300">{{ $entry['account_name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 border border-gray-300">{{ $entry['account_code'] }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900 border border-gray-300">
                                        {{ $entry['debit'] > 0 ? $this->formatNumber($entry['debit']) : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900 border border-gray-300">
                                        {{ $entry['credit'] > 0 ? $this->formatNumber($entry['credit']) : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 border border-gray-300">{{ $entry['description'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-center text-gray-500 border border-gray-300">No journal entries to display</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($journalEntries) > 0)
                            <tfoot>
                                <tr class="bg-gray-100 font-bold">
                                    <td colspan="3" class="px-4 py-3 text-sm border border-gray-300">TOTAL</td>
                                    <td class="px-4 py-3 text-sm text-right border border-gray-300">
                                        {{ $this->formatNumber(collect($journalEntries)->sum('debit')) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right border border-gray-300">
                                        {{ $this->formatNumber(collect($journalEntries)->sum('credit')) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm border border-gray-300"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                <!-- Post Depreciation Button -->
                @if(count($journalEntries) > 0)
                    <div class="flex justify-end mt-4">
                        <button wire:click="runDepreciation" 
                                wire:confirm="Are you sure you want to post these depreciation entries to the general ledger? This action cannot be undone for this period."
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg wire:loading.remove class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <svg wire:loading class="animate-spin w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove>Post Depreciation to General Ledger</span>
                            <span wire:loading>Processing...</span>
                        </button>
                    </div>
                @endif
            </div>
        @elseif($viewMode === 'history')
            <!-- Posting History View -->
            @php
                $hasPosted = $this->hasPostedDepreciation();
            @endphp
            
            <div class="space-y-6">
                @if($hasPosted)
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Depreciation Posting History for {{ $this->getPeriodDescription() }}</h3>
                            <p class="text-sm text-gray-600 mt-1">View and manage posted depreciation entries</p>
                        </div>
                        <button wire:click="reverseDepreciation" 
                                wire:confirm="Are you sure you want to reverse the posted depreciation for {{ $this->getPeriodDescription() }}? This will create reversal entries in the general ledger."
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg wire:loading.remove class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            <svg wire:loading class="animate-spin w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove>Reverse Posted Depreciation</span>
                            <span wire:loading>Processing...</span>
                        </button>
                    </div>
                @endif
                
                @if(count($postingHistory) > 0)
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-red-50 rounded-lg p-4">
                            <p class="text-sm text-red-600 font-medium">Total Posted</p>
                            <p class="text-2xl font-bold text-red-900">
                                {{ $this->formatNumber($postingHistory->where('status', 'posted')->sum('amount_posted')) }}
                            </p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-sm text-green-600 font-medium">Assets Depreciated</p>
                            <p class="text-2xl font-bold text-green-900">
                                {{ $postingHistory->where('status', 'posted')->unique('asset_id')->count() }}
                            </p>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-4">
                            <p class="text-sm text-yellow-600 font-medium">Reversed Amount</p>
                            <p class="text-2xl font-bold text-yellow-900">
                                {{ $this->formatNumber($postingHistory->where('status', 'reversed')->sum('amount_posted')) }}
                            </p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4">
                            <p class="text-sm text-purple-600 font-medium">Status</p>
                            <p class="text-lg font-bold {{ $hasPosted ? 'text-green-900' : 'text-gray-900' }}">
                                {{ $hasPosted ? 'Posted' : 'Not Posted' }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- History Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asset</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posted Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posted By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($postingHistory as $history)
                                <tr class="{{ $history->status === 'reversed' ? 'bg-gray-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $history->asset_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $history->asset_category }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        {{ number_format($history->amount_posted, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($history->posting_date)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $history->posted_by_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($history->status === 'posted')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Posted
                                            </span>
                                        @elseif($history->status === 'reversed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Reversed
                                            </span>
                                            @if($history->reversed_at)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    by {{ $history->reversed_by_name }}<br>
                                                    on {{ \Carbon\Carbon::parse($history->reversed_at)->format('d/m/Y H:i') }}
                                                    @if($history->reversal_reason && $history->reversal_reason !== 'Manual reversal by user')
                                                        <br>Reason: {{ $history->reversal_reason }}
                                                    @endif
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $history->reference_number ?? 'N/A' }}
                                        @if($history->reversal_reference)
                                            <br><span class="text-xs text-red-600">Rev: {{ $history->reversal_reference }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">
                                        Total (Posted Only)
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900">
                                        {{ number_format($postingHistory->where('status', 'posted')->sum('amount_posted'), 2) }}
                                    </td>
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No Posting History</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            No depreciation has been posted for {{ $this->getPeriodDescription() }} yet.
                        </p>
                        <div class="mt-6">
                            <button wire:click="changeViewMode('journal')" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M12 4v16m8-8H4"/>
                                </svg>
                                Post Depreciation
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Old Posting History Section (Remove if exists) -->
    @if(false)
    <div class="px-6 py-4 border-t border-gray-200">
        <div class="mb-4 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Depreciation Posting History</h3>
            @if($hasPosted)
                <button wire:click="reverseDepreciation" 
                        wire:confirm="Are you sure you want to reverse the posted depreciation for {{ $this->getPeriodDescription() }}? This will create reversal entries in the general ledger."
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg wire:loading.remove class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                    <svg wire:loading class="animate-spin w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove>Reverse Depreciation</span>
                    <span wire:loading>Processing...</span>
                </button>
            @endif
        </div>
        
        @if($postingHistory->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asset</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posted Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posted By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($postingHistory as $history)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $history->asset_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $history->asset_category }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                            {{ number_format($history->amount_posted, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($history->posting_date)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $history->posted_by_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($history->status === 'posted')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Posted
                                </span>
                            @elseif($history->status === 'reversed')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Reversed
                                </span>
                                @if($history->reversed_at)
                                    <div class="text-xs text-gray-500 mt-1">
                                        by {{ $history->reversed_by_name }}<br>
                                        on {{ \Carbon\Carbon::parse($history->reversed_at)->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $history->reference_number ?? 'N/A' }}
                            @if($history->reversal_reference)
                                <br><span class="text-xs">Rev: {{ $history->reversal_reference }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">
                            Total
                        </td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900">
                            {{ number_format($postingHistory->where('status', 'posted')->sum('amount_posted'), 2) }}
                        </td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            No depreciation has been posted for this period yet.
        </div>
        @endif
    </div>
    @endif

    <!-- Footer Section -->
    <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
        <div class="flex justify-between items-center text-sm text-gray-600">
            <div>
                <span class="font-medium">Report Generated:</span> {{ now()->format('d/m/Y H:i') }}
            </div>
            <div>
                <span class="font-medium">Period:</span> {{ ucfirst($depreciationPeriod) }} - 
                @if($depreciationPeriod === 'monthly')
                    {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}
                @elseif($depreciationPeriod === 'quarterly')
                    Q{{ ceil($selectedMonth / 3) }} {{ $selectedYear }}
                @else
                    Year {{ $selectedYear }}
                @endif
            </div>
        </div>
    </div>
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

