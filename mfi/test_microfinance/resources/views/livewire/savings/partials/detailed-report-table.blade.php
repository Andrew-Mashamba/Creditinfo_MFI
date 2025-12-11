<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800">
            <i class="fas fa-table mr-2 text-red-600"></i>Detailed Savings Deposits Report
        </h3>
        <p class="text-sm text-gray-600 mt-1">Complete list of all member savings accounts and deposits</p>
    </div>

    @if(isset($detailedData) && $detailedData !== null && (is_object($detailedData) && method_exists($detailedData, 'count') ? $detailedData->count() : (is_array($detailedData) ? count($detailedData) : 0)) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Account
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Member
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Branch
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('balance')" class="hover:text-gray-700 flex items-center ml-auto">
                                Balance (TZS)
                                @if($sortBy === 'balance')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($detailedData as $item)
                        <tr class="hover:bg-red-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center shadow">
                                            <i class="fas fa-piggy-bank text-white"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->account_number }}</div>
                                        <div class="text-xs text-gray-500 truncate max-w-xs" title="{{ $item->account_name }}">{{ $item->account_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->client)
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center shadow">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $item->client->first_name }} {{ $item->client->last_name }}
                                            </div>
                                            <div class="text-xs text-gray-500">{{ $item->client_number }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->shareProduct)
                                    <div class="text-sm text-gray-900 font-medium">{{ $item->shareProduct->product_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->shareProduct->sub_product_name }}</div>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $branch = collect($branches)->firstWhere('branch_number', $item->branch_number);
                                @endphp
                                @if($branch)
                                    <div class="text-sm text-gray-900">{{ $branch->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->branch_number }}</div>
                                @else
                                    <span class="text-xs text-gray-500">{{ $item->branch_number }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-gray-900">
                                    {{ number_format($item->balance, 2) }}
                                </div>
                                <div class="text-xs text-gray-500">TZS</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusColors = [
                                        'ACTIVE' => 'bg-green-100 text-green-800 border-green-200',
                                        'INACTIVE' => 'bg-red-100 text-red-800 border-red-200',
                                        'PENDING' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                        'BLOCKED' => 'bg-gray-100 text-gray-800 border-gray-200',
                                        'SUSPENDED' => 'bg-orange-100 text-orange-800 border-orange-200'
                                    ];
                                    $statusColor = $statusColors[$item->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                @endphp
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $statusColor }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-3">
                                    <button wire:click="viewAccountDetails('{{ $item->account_number }}')"
                                            class="text-red-600 hover:text-red-900 transition-colors"
                                            title="View Details">
                                        <i class="fas fa-eye text-lg"></i>
                                    </button>
                                    <button wire:click="viewAccountTransactions('{{ $item->account_number }}')"
                                            class="text-green-600 hover:text-green-900 transition-colors"
                                            title="View Transactions">
                                        <i class="fas fa-list text-lg"></i>
                                    </button>
                                    <div class="relative inline-block text-left">
                                        <button type="button"
                                                class="text-purple-600 hover:text-purple-900 transition-colors dropdown-toggle"
                                                title="Download Statement"
                                                @if($isExporting) disabled @endif
                                                onclick="toggleDropdown('dropdown-{{ $item->account_number }}')">
                                            <i class="fas fa-download text-lg @if($isExporting) animate-pulse @endif"></i>
                                        </button>
                                        <div id="dropdown-{{ $item->account_number }}"
                                             class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 border border-gray-200">
                                            <div class="py-1">
                                                <button wire:click="exportStatement('{{ $item->account_number }}', 'pdf')"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                        @if($isExporting) disabled @endif>
                                                    <i class="fas fa-file-pdf mr-2 text-red-600"></i>Download PDF
                                                </button>
                                                <button wire:click="exportStatement('{{ $item->account_number }}', 'excel')"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                        @if($isExporting) disabled @endif>
                                                    <i class="fas fa-file-excel mr-2 text-green-600"></i>Download Excel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing {{ $detailedData->firstItem() ?? 0 }} to {{ $detailedData->lastItem() ?? 0 }} of {{ $detailedData->total() }} results
            </div>
            <div>
                @if(isset($detailedData) && $detailedData !== null && is_object($detailedData) && method_exists($detailedData, 'links'))
                    {{ $detailedData->links() }}
                @endif
            </div>
        </div>
    @else
        <div class="p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                <i class="fas fa-search text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Results Found</h3>
            <p class="text-gray-500 mb-4">No savings deposits match your current filters.</p>
            <button wire:click="resetFilters" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-800 transition-colors">
                <i class="fas fa-undo mr-2"></i>Reset Filters
            </button>
        </div>
    @endif
</div>
