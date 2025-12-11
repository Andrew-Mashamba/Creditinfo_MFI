<div>
    {{-- Loading Overlay --}}
    @if($isLoading)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-600">Loading...</p>
            </div>
        </div>
    @endif

    {{-- Messages --}}
    @if($successMessage)
        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
            <div class="flex justify-between items-center">
                <span>{{ $successMessage }}</span>
                <button wire:click="clearMessages" class="text-green-500 hover:text-green-700">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if($errorMessage)
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
            <div class="flex justify-between items-center">
                <span>{{ $errorMessage }}</span>
                <button wire:click="clearMessages" class="text-red-500 hover:text-red-700">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg p-6">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Monthly Savings Report</h2>
            <div class="flex space-x-2">
                {{-- Export Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 flex items-center"
                            @if($isLoading) disabled @endif>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Export to Excel</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-1">
                            <button wire:click="exportSummary" @click="open = false"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <span wire:loading.remove wire:target="exportSummary">Export Summary Report</span>
                                <span wire:loading wire:target="exportSummary">Exporting...</span>
                            </button>
                            <button wire:click="exportAccounts" @click="open = false"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <span wire:loading.remove wire:target="exportAccounts">Export Accounts Report</span>
                                <span wire:loading wire:target="exportAccounts">Exporting...</span>
                            </button>
                            <button wire:click="exportTransactions" @click="open = false"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <span wire:loading.remove wire:target="exportTransactions">Export Transactions Report</span>
                                <span wire:loading wire:target="exportTransactions">Exporting...</span>
                            </button>
                            <button wire:click="exportMembers" @click="open = false"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <span wire:loading.remove wire:target="exportMembers">Export Members Report</span>
                                <span wire:loading wire:target="exportMembers">Exporting...</span>
                            </button>
                            <button wire:click="exportMonthlyTrends" @click="open = false"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <span wire:loading.remove wire:target="exportMonthlyTrends">Export Monthly Trends</span>
                                <span wire:loading wire:target="exportMonthlyTrends">Exporting...</span>
                            </button>
                            <button wire:click="exportFullReport" @click="open = false"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-t">
                                <span wire:loading.remove wire:target="exportFullReport">Export Full Report</span>
                                <span wire:loading wire:target="exportFullReport">Exporting...</span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <button wire:click="sendBulkNotifications" 
                        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 disabled:opacity-50"
                        @if($isLoading) disabled @endif>
                    <span wire:loading.remove wire:target="sendBulkNotifications">Send Bulk Notifications</span>
                    <span wire:loading wire:target="sendBulkNotifications">Sending...</span>
                </button>
                <button wire:click="refreshData" 
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-900 disabled:opacity-50"
                        @if($isLoading) disabled @endif>
                    <span wire:loading.remove wire:target="refreshData">Refresh Data</span>
                    <span wire:loading wire:target="refreshData">Refreshing...</span>
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label for="month" class="block text-sm font-medium text-gray-700">Month</label>
                <select wire:model="selectedMonth" id="month" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}">{{ Carbon\Carbon::create()->month($month)->format('F') }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                <select wire:model="selectedYear" id="year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach(range(2000, date('Y') + 1) as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="product" class="block text-sm font-medium text-gray-700">Product</label>
                <select wire:model="selectedProduct" id="product" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Savings Products</option>
                    @if(!$isLoading && isset($productsData) && is_object($productsData) && method_exists($productsData, 'count') && $productsData->count() > 0)
                        @foreach($productsData as $product)
                            @if(isset($product) && is_object($product) && isset($product->sub_product_id) && isset($product->product_name) && isset($product->status))
                                <option value="{{ $product->sub_product_id }}">{{ $product->product_name }} ({{ $product->status }})</option>
                            @endif
                        @endforeach
                    @elseif($isLoading)
                        <option value="" disabled>Loading products...</option>
                    @else
                        <option value="" disabled>No products available</option>
                    @endif
                </select>
            </div>

            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" wire:model.debounce.300ms="searchTerm" id="search" 
                       placeholder="Search members or accounts..." 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>

        {{-- Tabs --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <button wire:click="$set('activeTab', 'summary')" 
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'summary' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Summary
                </button>
                <button wire:click="$set('activeTab', 'accounts')" 
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'accounts' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Accounts ({{ count($accountsData) }})
                </button>
                <button wire:click="$set('activeTab', 'members')" 
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'members' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Members ({{ count($membersData) }})
                </button>
                <button wire:click="$set('activeTab', 'notifications')" 
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'notifications' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Non-Compliant ({{ count($nonCompliantMembers) }})
                </button>
            </nav>
        </div>

        {{-- Summary Tab --}}
        @if($activeTab === 'summary')
            <div>
                {{-- Summary Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-blue-800">Total Savings Balance</h3>
                        <p class="text-2xl font-bold text-blue-900">TZS {{ number_format($summaryData['total_balance'] ?? 0, 2) }}</p>
                        <p class="text-sm text-blue-600">{{ $summaryData['active_accounts'] ?? 0 }} active accounts</p>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-green-800">Monthly Deposits</h3>
                        <p class="text-2xl font-bold text-green-900">TZS {{ number_format($summaryData['monthly_deposits'] ?? 0, 2) }}</p>
                        <p class="text-sm text-green-600">{{ $summaryData['transaction_count'] ?? 0 }} transactions</p>
                    </div>
                    
                    <div class="bg-red-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-red-800">Monthly Withdrawals</h3>
                        <p class="text-2xl font-bold text-red-900">TZS {{ number_format($summaryData['monthly_withdrawals'] ?? 0, 2) }}</p>
                        <p class="text-sm text-red-600">Net: TZS {{ number_format($summaryData['monthly_net_change'] ?? 0, 2) }}</p>
                    </div>
                    
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-purple-800">Compliance Rate</h3>
                        <p class="text-2xl font-bold text-purple-900">{{ $summaryData['compliance_rate'] ?? 0 }}%</p>
                        <p class="text-sm text-purple-600">{{ $summaryData['members_with_savings'] ?? 0 }}/{{ $summaryData['total_members'] ?? 0 }} members</p>
                    </div>
                </div>

                {{-- Products Summary --}}
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Savings Products Summary</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Accounts</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Balance</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Balance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if(!$isLoading && isset($productsData) && is_object($productsData) && method_exists($productsData, 'count') && $productsData->count() > 0)
                                    @foreach($productsData as $product)
                                        @if(isset($product) && is_object($product) && isset($product->product_name) && isset($product->status) && isset($product->account_count) && isset($product->total_balance) && isset($product->average_balance))
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $product->product_name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($product->status === 'ACTIVE') bg-green-100 text-green-800
                                                        @elseif($product->status === 'PENDING') bg-yellow-100 text-yellow-800
                                                        @else bg-red-100 text-red-800 @endif">
                                                        {{ $product->status }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($product->account_count) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    TZS {{ number_format($product->total_balance, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    TZS {{ number_format($product->average_balance, 2) }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Monthly Trends --}}
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Trends ({{ $selectedYear }})</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deposits</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Withdrawals</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Change</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($monthlyTotals as $month)
                                    <tr class="{{ $month['month'] == $selectedMonth ? 'bg-blue-50' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ Carbon\Carbon::create()->month($month['month'])->format('F') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                            TZS {{ number_format($month['total_deposits'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                            TZS {{ number_format($month['total_withdrawals'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            TZS {{ number_format($month['total_deposits'] - $month['total_withdrawals'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($month['transaction_count']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Accounts Tab --}}
        @if($activeTab === 'accounts')
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Savings Accounts</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($accountsData as $account)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $account['account_number'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $account['first_name'] }} {{ $account['last_name'] }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $account['client_number'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $account['product_name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        TZS {{ number_format($account['balance'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($account['status'] === 'ACTIVE') bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ $account['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ Carbon\Carbon::parse($account['created_at'])->format('d/m/Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No savings accounts found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Members Tab --}}
        @if($activeTab === 'members')
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Members Savings Status</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Savings</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Accounts</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($membersData as $member)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $member['first_name'] }} {{ $member['last_name'] }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $member['client_number'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $member['phone_number'] ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $member['email'] ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        TZS {{ number_format($member['total_savings'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $member['savings_accounts'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($member['compliance_status'] === 'COMPLIANT') bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ $member['compliance_status'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($member['member_status'] === 'ACTIVE') bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ $member['member_status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No members found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Notifications Tab --}}
        @if($activeTab === 'notifications')
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Non-Compliant Members</h3>
                    <div class="text-sm text-gray-500">
                        {{ count($nonCompliantMembers) }} members without savings
                    </div>
                </div>
                
                @if(count($nonCompliantMembers) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($nonCompliantMembers as $member)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $member['first_name'] }} {{ $member['last_name'] }}
                                            </div>
                                            <div class="text-sm text-gray-500">{{ $member['client_number'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $member['phone_number'] ?? 'N/A' }}</div>
                                            <div class="text-sm text-gray-500">{{ $member['email'] ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($member['status'] === 'ACTIVE') bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ $member['status'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button wire:click="sendNotification('{{ $member['client_number'] }}')" 
                                                    class="text-blue-600 hover:text-blue-900">
                                                Send Notification
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-green-500 mb-2">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">All Members Are Compliant!</h3>
                        <p class="text-gray-500">All members have savings accounts with positive balances.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
