<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Share Statement</h2>
                <p class="text-gray-600 mt-1">View share transactions and balances for members</p>
            </div>
            <div class="flex space-x-2">
                @if($viewMode === 'statement' || $viewMode === 'consolidated')
                    <button
                        wire:click="backToSearch"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Search
                    </button>
                @endif
                <button
                    wire:click="refreshData"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span wire:loading.remove wire:target="refreshData">Refresh</span>
                    <span wire:loading wire:target="refreshData">Loading...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Messages -->
    @if ($successMessage)
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 text-green-700 rounded">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $successMessage }}</span>
                </div>
                <button wire:click="clearMessages" class="text-green-700 hover:text-green-900">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if ($errorMessage)
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 text-red-700 rounded">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errorMessage }}</span>
                </div>
                <button wire:click="clearMessages" class="text-red-700 hover:text-red-900">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if($viewMode === 'search')
        <!-- Summary Cards -->
        @if($summary)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Members</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($summary['total_members'] ?? 0) }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Shares</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($summary['total_shares'] ?? 0) }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Value</p>
                        <p class="text-2xl font-bold text-gray-800">TZS {{ number_format($summary['total_value'] ?? 0, 2) }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Search/Filter Section -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Generate Statement</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Member</label>
                    <input
                        type="text"
                        wire:model="searchTerm"
                        placeholder="Search by name or client number..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input
                        type="date"
                        wire:model="startDate"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input
                        type="date"
                        wire:model="endDate"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Share Product (Optional)</label>
                    <select
                        wire:model="selectedProduct"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Share Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button
                        wire:click="generateAllMembersStatement"
                        class="w-full px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Generate All Members Report
                    </button>
                </div>

                <div class="flex items-end">
                    <button
                        wire:click="clearFilters"
                        class="w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Members List -->
            @if($members && count($members) > 0)
                <div class="mt-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Select a Member:</h4>
                    <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client No.</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member Name</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accounts</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Shares</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($members as $member)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ is_object($member) ? $member->client_number : $member['client_number'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ is_object($member) ? $member->full_name : $member['full_name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ is_object($member) ? $member->share_accounts_count : $member['share_accounts_count'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">{{ number_format(is_object($member) ? $member->total_shares : $member['total_shares']) }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex justify-center space-x-2">
                                                <button
                                                    wire:click="selectMember('{{ is_object($member) ? $member->client_number : $member['client_number'] }}')"
                                                    class="inline-flex items-center px-3 py-1 bg-blue-900 text-white text-xs rounded hover:bg-blue-700 transition-colors"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    View Statement
                                                </button>
                                                <button
                                                    wire:click="generateCertificate('{{ is_object($member) ? $member->client_number : $member['client_number'] }}')"
                                                    class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Generate Certificate
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($searchTerm !== '')
                <div class="mt-4 p-4 bg-gray-50 rounded-md text-center text-gray-600">
                    <p>No members found matching your search.</p>
                </div>
            @endif
        </div>
    @endif

    @if($viewMode === 'statement' && $statementData)
        <!-- Statement Header -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">{{ $statementData['member']['full_name'] }}</h3>
                    <p class="text-gray-600">Client Number: {{ $statementData['member']['client_number'] }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Statement Period</p>
                    <p class="font-semibold text-gray-800">
                        {{ $statementData['period']['start_date_formatted'] }} - {{ $statementData['period']['end_date_formatted'] }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Generated: {{ \Carbon\Carbon::parse($statementData['generated_at'])->format('d M Y H:i') }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                <button
                    wire:click="backToSearch"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Close Statement
                </button>

                <div class="flex space-x-2">
                    <button
                        wire:click="printStatement"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </button>
                    <button
                        wire:click="exportToPDF"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Export PDF
                    </button>
                    <button
                        wire:click="exportToExcel"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Statement Details -->
        @foreach($statementData['statements'] as $statement)
            <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                <!-- Product Header -->
                <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-800">{{ $statement['product_name'] }}</h4>
                            <p class="text-sm text-gray-600">Account: {{ $statement['share_account_number'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Nominal Price</p>
                            <p class="text-lg font-semibold text-gray-800">TZS {{ number_format($statement['nominal_price'], 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Opening Balance -->
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Opening Balance</span>
                            <span class="text-xs text-gray-500 ml-2">(as of {{ $statementData['period']['start_date_formatted'] }})</span>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-gray-800">{{ number_format($statement['opening_balance']) }} shares</p>
                            <p class="text-sm text-gray-600">TZS {{ number_format($statement['opening_value'], 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Transactions -->
                @if(count($statement['transactions']) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Shares Issued</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Shares Redeemed</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($statement['transactions'] as $transaction)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $transaction->reference_number }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $transaction->description }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            @if($transaction->transaction_type === 'ISSUE')
                                                <span class="text-green-600 font-medium">{{ number_format($transaction->shares) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            @if($transaction->transaction_type === 'REDEMPTION')
                                                <span class="text-red-600 font-medium">{{ number_format($transaction->shares) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                            TZS {{ number_format($transaction->total_value, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-gray-500">
                        <p>No transactions during this period</p>
                    </div>
                @endif

                <!-- Closing Balance -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Total Shares Issued:</span>
                            <span class="font-medium text-green-600">{{ number_format($statement['total_issued']) }} shares</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Total Shares Redeemed:</span>
                            <span class="font-medium text-red-600">{{ number_format($statement['total_redeemed']) }} shares</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Net Change:</span>
                            <span class="font-medium {{ $statement['net_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $statement['net_change'] >= 0 ? '+' : '' }}{{ number_format($statement['net_change']) }} shares
                            </span>
                        </div>
                        <div class="pt-2 mt-2 border-t border-gray-300">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Closing Balance</span>
                                    <span class="text-xs text-gray-500 ml-2">(as of {{ $statementData['period']['end_date_formatted'] }})</span>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-800">{{ number_format($statement['closing_balance']) }} shares</p>
                                    <p class="text-sm text-gray-600">TZS {{ number_format($statement['closing_value'], 2) }}</p>
                                    <p class="text-xs text-blue-600">Market Value: TZS {{ number_format($statement['market_value'], 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Grand Totals -->
        @if(count($statementData['statements']) > 1)
            <div class="bg-blue-900 text-white rounded-lg shadow p-6">
                <h4 class="text-lg font-semibold mb-4">Grand Totals (All Products)</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-blue-200 text-sm">Opening Shares</p>
                        <p class="text-2xl font-bold">{{ number_format($statementData['grand_totals']['opening_shares']) }}</p>
                        <p class="text-blue-200 text-xs">TZS {{ number_format($statementData['grand_totals']['opening_value'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-blue-200 text-sm">Total Issued</p>
                        <p class="text-2xl font-bold text-green-300">{{ number_format($statementData['grand_totals']['total_issued']) }}</p>
                    </div>
                    <div>
                        <p class="text-blue-200 text-sm">Total Redeemed</p>
                        <p class="text-2xl font-bold text-red-300">{{ number_format($statementData['grand_totals']['total_redeemed']) }}</p>
                    </div>
                    <div>
                        <p class="text-blue-200 text-sm">Closing Shares</p>
                        <p class="text-2xl font-bold">{{ number_format($statementData['grand_totals']['closing_shares']) }}</p>
                        <p class="text-blue-200 text-xs">TZS {{ number_format($statementData['grand_totals']['closing_value'], 2) }}</p>
                    </div>
                </div>
            </div>
        @endif
    @endif

    @if($viewMode === 'consolidated' && $consolidatedData)
        <!-- Consolidated Statement Header -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">All Members Share Statement</h3>
                    <p class="text-gray-600">Consolidated Report for All Members</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Statement Period</p>
                    <p class="font-semibold text-gray-800">
                        {{ $consolidatedData['period']['start_date_formatted'] }} - {{ $consolidatedData['period']['end_date_formatted'] }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Generated: {{ \Carbon\Carbon::parse($consolidatedData['generated_at'])->format('d M Y H:i') }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                <button
                    wire:click="backToSearch"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Close Report
                </button>

                <div class="flex space-x-2">
                    <button
                        wire:click="printStatement"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </button>
                    <button
                        wire:click="exportToPDF"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Export PDF
                    </button>
                    <button
                        wire:click="exportToExcel"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                <p class="text-sm text-gray-600">Total Members</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($consolidatedData['total_members']) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                <p class="text-sm text-gray-600">Opening Shares</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($consolidatedData['grand_totals']['opening_shares']) }}</p>
                <p class="text-xs text-gray-500">TZS {{ number_format($consolidatedData['grand_totals']['opening_value'], 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
                <p class="text-sm text-gray-600">Net Change</p>
                <p class="text-2xl font-bold {{ ($consolidatedData['grand_totals']['total_issued'] - $consolidatedData['grand_totals']['total_redeemed']) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ ($consolidatedData['grand_totals']['total_issued'] - $consolidatedData['grand_totals']['total_redeemed']) >= 0 ? '+' : '' }}{{ number_format($consolidatedData['grand_totals']['total_issued'] - $consolidatedData['grand_totals']['total_redeemed']) }}
                </p>
                <p class="text-xs text-gray-500">Issued: {{ number_format($consolidatedData['grand_totals']['total_issued']) }} | Redeemed: {{ number_format($consolidatedData['grand_totals']['total_redeemed']) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-indigo-500">
                <p class="text-sm text-gray-600">Closing Shares</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($consolidatedData['grand_totals']['closing_shares']) }}</p>
                <p class="text-xs text-gray-500">TZS {{ number_format($consolidatedData['grand_totals']['closing_value'], 2) }}</p>
            </div>
        </div>

        <!-- Consolidated Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Name</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Opening Balance</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Shares Issued</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Shares Redeemed</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Change</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Closing Balance</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Closing Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($consolidatedData['members'] as $member)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $member['client_number'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $member['full_name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                    {{ number_format($member['opening_balance']) }}
                                    <span class="text-xs text-gray-500 block">TZS {{ number_format($member['opening_value'], 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-green-600 font-medium">
                                    {{ number_format($member['total_issued']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600 font-medium">
                                    {{ number_format($member['total_redeemed']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm {{ $member['net_change'] >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    {{ $member['net_change'] >= 0 ? '+' : '' }}{{ number_format($member['net_change']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 font-bold">
                                    {{ number_format($member['closing_balance']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                    TZS {{ number_format($member['closing_value'], 2) }}
                                    <span class="text-xs text-blue-600 block">Market: {{ number_format($member['market_value'], 2) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-blue-50">
                        <tr class="font-bold">
                            <td colspan="2" class="px-6 py-4 text-sm text-gray-900">GRAND TOTALS</td>
                            <td class="px-6 py-4 text-right text-sm text-gray-900">
                                {{ number_format($consolidatedData['grand_totals']['opening_shares']) }}
                                <span class="text-xs text-gray-600 block font-normal">TZS {{ number_format($consolidatedData['grand_totals']['opening_value'], 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-green-600">
                                {{ number_format($consolidatedData['grand_totals']['total_issued']) }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-red-600">
                                {{ number_format($consolidatedData['grand_totals']['total_redeemed']) }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm {{ ($consolidatedData['grand_totals']['total_issued'] - $consolidatedData['grand_totals']['total_redeemed']) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ ($consolidatedData['grand_totals']['total_issued'] - $consolidatedData['grand_totals']['total_redeemed']) >= 0 ? '+' : '' }}{{ number_format($consolidatedData['grand_totals']['total_issued'] - $consolidatedData['grand_totals']['total_redeemed']) }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-gray-900">
                                {{ number_format($consolidatedData['grand_totals']['closing_shares']) }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-gray-900">
                                TZS {{ number_format($consolidatedData['grand_totals']['closing_value'], 2) }}
                                <span class="text-xs text-blue-600 block font-normal">Market: {{ number_format($consolidatedData['grand_totals']['market_value'], 2) }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    window.addEventListener('print-statement', event => {
        window.print();
    });
</script>
@endpush
