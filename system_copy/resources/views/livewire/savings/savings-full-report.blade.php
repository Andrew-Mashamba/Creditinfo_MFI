<div class="min-h-screen bg-gray-50 p-6">
    <!-- Professional Header -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-blue-900 to-blue-700 rounded-lg shadow-lg p-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Savings Deposits Full Report</h1>
                    <p class="text-blue-100">Comprehensive savings analysis and member deposit tracking</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-blue-100">Report Period</p>
                    <p class="text-lg font-semibold">{{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if($successMessage)
        <div class="bg-green-50 border-l-4 border-green-400 rounded-lg p-4 mb-6 shadow">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-green-800">{{ $successMessage }}</p>
                </div>
                <button wire:click="$set('successMessage', '')" class="text-green-500 hover:text-green-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if($errorMessage)
        <div class="bg-red-50 border-l-4 border-red-400 rounded-lg p-4 mb-6 shadow">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-red-800">{{ $errorMessage }}</p>
                </div>
                <button wire:click="$set('errorMessage', '')" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Quick Search Bar -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <!-- Search Input -->
            <div class="lg:col-span-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-2 text-blue-600"></i>Quick Search
                </label>
                <input type="text"
                       wire:model.debounce.500ms="quickSearch"
                       placeholder="Search by member name, account number, or member number..."
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">Search by name, account #, or member #</p>
            </div>

            <!-- Amount Range -->
            <div class="lg:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-dollar-sign mr-2 text-green-600"></i>Min Amount (TZS)
                </label>
                <input type="number"
                       wire:model.debounce.500ms="minAmount"
                       placeholder="0.00"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <div class="lg:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-dollar-sign mr-2 text-green-600"></i>Max Amount (TZS)
                </label>
                <input type="number"
                       wire:model.debounce.500ms="maxAmount"
                       placeholder="999999999.00"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
        </div>
    </div>

    <!-- Quick Date Presets & Actions -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <!-- Date Presets -->
            <div class="flex flex-wrap gap-2">
                <span class="text-sm font-medium text-gray-700 self-center">Quick Dates:</span>
                <button wire:click="applyDatePreset('today')" class="px-3 py-1.5 text-xs font-medium rounded-full {{ $datePreset === 'today' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">
                    Today
                </button>
                <button wire:click="applyDatePreset('yesterday')" class="px-3 py-1.5 text-xs font-medium rounded-full {{ $datePreset === 'yesterday' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">
                    Yesterday
                </button>
                <button wire:click="applyDatePreset('this_week')" class="px-3 py-1.5 text-xs font-medium rounded-full {{ $datePreset === 'this_week' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">
                    This Week
                </button>
                <button wire:click="applyDatePreset('this_month')" class="px-3 py-1.5 text-xs font-medium rounded-full {{ $datePreset === 'this_month' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">
                    This Month
                </button>
                <button wire:click="applyDatePreset('last_month')" class="px-3 py-1.5 text-xs font-medium rounded-full {{ $datePreset === 'last_month' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">
                    Last Month
                </button>
                <button wire:click="applyDatePreset('this_quarter')" class="px-3 py-1.5 text-xs font-medium rounded-full {{ $datePreset === 'this_quarter' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">
                    This Quarter
                </button>
                <button wire:click="applyDatePreset('this_year')" class="px-3 py-1.5 text-xs font-medium rounded-full {{ $datePreset === 'this_year' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">
                    This Year
                </button>
                <button wire:click="applyDatePreset('last_30_days')" class="px-3 py-1.5 text-xs font-medium rounded-full {{ $datePreset === 'last_30_days' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">
                    Last 30 Days
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button wire:click="refreshReport"
                        class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700 transition-colors shadow">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
                <button wire:click="exportReport('excel')"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow">
                    <i class="fas fa-file-excel mr-2"></i>Excel
                </button>
                <button wire:click="exportReport('pdf')"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow">
                    <i class="fas fa-file-pdf mr-2"></i>PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    @if(isset($summaryStatistics) && $summaryStatistics !== null)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-lg border-l-4 border-blue-600 shadow-md hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-700 uppercase">Total Members</p>
                        <p class="text-3xl font-bold text-blue-900 mt-2">
                            {{ number_format($summaryStatistics->member_count ?? 0) }}
                        </p>
                    </div>
                    <div class="p-4 bg-blue-900 rounded-full">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-lg border-l-4 border-green-600 shadow-md hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-700 uppercase">Total Deposits</p>
                        <p class="text-3xl font-bold text-green-900 mt-2">
                            TZS {{ number_format($summaryStatistics->total_balance ?? 0, 2) }}
                        </p>
                    </div>
                    <div class="p-4 bg-green-600 rounded-full">
                        <i class="fas fa-coins text-white text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-lg border-l-4 border-purple-600 shadow-md hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-700 uppercase">Average Balance</p>
                        <p class="text-3xl font-bold text-purple-900 mt-2">
                            TZS {{ number_format($summaryStatistics->avg_balance ?? 0, 2) }}
                        </p>
                    </div>
                    <div class="p-4 bg-purple-600 rounded-full">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-lg border-l-4 border-orange-600 shadow-md hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-orange-700 uppercase">Total Accounts</p>
                        <p class="text-3xl font-bold text-orange-900 mt-2">
                            {{ number_format($summaryStatistics->account_count ?? 0) }}
                        </p>
                    </div>
                    <div class="p-4 bg-orange-600 rounded-full">
                        <i class="fas fa-wallet text-white text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Advanced Filters (Collapsible) -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <button wire:click="$toggle('showAdvancedFilters')" class="w-full px-6 py-4 flex justify-between items-center text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center">
                <i class="fas fa-filter mr-3 text-blue-600"></i>
                <span class="font-semibold text-gray-800">Advanced Filters</span>
                <span class="ml-3 text-sm text-gray-500">(Click to {{ $showAdvancedFilters ? 'collapse' : 'expand' }})</span>
            </div>
            <i class="fas fa-chevron-down transition-transform {{ $showAdvancedFilters ? 'transform rotate-180' : '' }}"></i>
        </button>

        @if($showAdvancedFilters)
            <div class="border-t border-gray-200 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Branch Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building mr-2 text-gray-500"></i>Branch
                    </label>
                    <select wire:model="selectedBranch" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->branch_number }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Member Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-gray-500"></i>Member
                    </label>
                    <select wire:model="selectedMember" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Members</option>
                        @foreach($members as $member)
                            <option value="{{ $member->client_number }}">
                                @if($member->business_name)
                                    {{ $member->business_name }} ({{ $member->client_number }})
                                @else
                                    {{ $member->first_name }} {{ $member->last_name }} ({{ $member->client_number }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Product Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag mr-2 text-gray-500"></i>Product
                    </label>
                    <select wire:model="selectedProduct" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->sub_product_id }}">
                                {{ $product->product_name }} - {{ $product->sub_product_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-flag mr-2 text-gray-500"></i>Status
                    </label>
                    <select wire:model="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">All Status</option>
                        <option value="ACTIVE">Active</option>
                        <option value="INACTIVE">Inactive</option>
                        <option value="PENDING">Pending</option>
                        <option value="BLOCKED">Blocked</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Custom Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-2 text-gray-500"></i>Date From
                    </label>
                    <input type="date" wire:model="dateFrom" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-2 text-gray-500"></i>Date To
                    </label>
                    <input type="date" wire:model="dateTo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Report Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file-alt mr-2 text-gray-500"></i>Report Type
                    </label>
                    <select wire:model="reportType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="summary">Summary Report</option>
                        <option value="detailed">Detailed Report</option>
                        <option value="comparative">Comparative Report</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                <button wire:click="resetFilters"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-undo mr-2"></i>Reset All Filters
                </button>

                <div class="text-sm text-gray-600">
                    <span class="font-medium">Results per page:</span>
                    <select wire:model="perPage" class="ml-2 px-3 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            </div>
        @endif
    </div>

    <!-- Loading Indicator -->
    @if($isLoading || $isExporting)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 shadow">
            <div class="flex items-center">
                <i class="fas fa-spinner fa-spin text-blue-600 text-xl mr-3"></i>
                <p class="text-sm font-medium text-blue-800">
                    @if($isExporting)
                        Generating report, please wait...
                    @else
                        Loading data...
                    @endif
                </p>
            </div>
        </div>
    @endif

    <!-- Main Data Table -->
    @if($reportType === 'detailed')
        @include('livewire.savings.partials.detailed-report-table')
    @elseif($reportType === 'summary')
        @include('livewire.savings.partials.summary-report-table')
    @elseif($reportType === 'comparative')
        @include('livewire.savings.partials.comparative-report-section')
    @endif
</div>

<!-- Account Details Modal -->
@if($showAccountDetailsModal && $selectedAccountData)
    @include('livewire.savings.partials.account-details-modal')
@endif

<!-- Account Transactions Modal -->
@if($showTransactionsModal)
    @include('livewire.savings.partials.account-transactions-modal')
@endif

<script nonce="{{ csp_nonce() }}">
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('[id^="dropdown-"]');
    dropdowns.forEach(dropdown => {
        if (!dropdown.contains(event.target) && !event.target.classList.contains('dropdown-toggle')) {
            dropdown.classList.add('hidden');
        }
    });
});
</script>
