<div class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-gradient-to-r from-red-900 to-red-700 px-6 py-4 rounded-t-lg z-10">
            <div class="flex items-center justify-between text-white">
                <h3 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-file-invoice-dollar mr-3"></i>Account Details
                </h3>
                <button wire:click="closeAccountDetailsModal" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <!-- Account Header Card -->
            <div class="bg-gradient-to-br from-red-50 to-indigo-50 p-6 rounded-lg border-l-4 border-red-600 shadow-md mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 rounded-full bg-red-600 flex items-center justify-center shadow-lg">
                                <i class="fas fa-piggy-bank text-white text-2xl"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-2xl font-bold text-gray-900">{{ $selectedAccountData->account_name }}</h4>
                            <p class="text-lg text-gray-600">{{ $selectedAccountData->account_number }}</p>
                            <div class="mt-2">
                                @php
                                    $statusColors = [
                                        'ACTIVE' => 'bg-green-100 text-green-800 border-green-200',
                                        'INACTIVE' => 'bg-red-100 text-red-800 border-red-200',
                                        'PENDING' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                        'BLOCKED' => 'bg-gray-100 text-gray-800 border-gray-200',
                                    ];
                                    $statusColor = $statusColors[$selectedAccountData->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                @endphp
                                <span class="px-3 py-1 text-sm font-semibold rounded-full border {{ $statusColor }}">
                                    {{ $selectedAccountData->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 uppercase">Current Balance</p>
                        <p class="text-3xl font-bold text-red-900">{{ number_format($selectedAccountData->balance, 2) }}</p>
                        <p class="text-sm text-gray-500">TZS</p>
                    </div>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Member Information -->
                <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                    <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center border-b border-gray-200 pb-2">
                        <i class="fas fa-user text-red-600 mr-2"></i>
                        Member Information
                    </h5>
                    @if($selectedAccountData->client)
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 text-sm">Member Name:</span>
                                <span class="font-medium text-gray-900">{{ $selectedAccountData->client->first_name }} {{ $selectedAccountData->client->last_name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 text-sm">Member Number:</span>
                                <span class="font-medium text-gray-900">{{ $selectedAccountData->client_number }}</span>
                            </div>
                            @if($selectedAccountData->client->business_name)
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 text-sm">Business Name:</span>
                                    <span class="font-medium text-gray-900">{{ $selectedAccountData->client->business_name }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-gray-500 italic text-center py-4">
                            <i class="fas fa-info-circle mr-2"></i>No member information available
                        </div>
                    @endif
                </div>

                <!-- Product Information -->
                <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                    <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center border-b border-gray-200 pb-2">
                        <i class="fas fa-tag text-purple-600 mr-2"></i>
                        Product Information
                    </h5>
                    @if($selectedAccountData->shareProduct)
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 text-sm">Product Name:</span>
                                <span class="font-medium text-gray-900">{{ $selectedAccountData->shareProduct->product_name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 text-sm">Sub Product:</span>
                                <span class="font-medium text-gray-900">{{ $selectedAccountData->shareProduct->sub_product_name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 text-sm">Product ID:</span>
                                <span class="font-medium text-gray-900">{{ $selectedAccountData->sub_product_number }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-gray-500 italic text-center py-4">
                            <i class="fas fa-info-circle mr-2"></i>No product information available
                        </div>
                    @endif
                </div>

                <!-- Account Details -->
                <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                    <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center border-b border-gray-200 pb-2">
                        <i class="fas fa-info-circle text-green-600 mr-2"></i>
                        Account Details
                    </h5>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 text-sm">Branch:</span>
                            <span class="font-medium text-gray-900">{{ $selectedAccountData->branch_number }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 text-sm">Created Date:</span>
                            <span class="font-medium text-gray-900">{{ $selectedAccountData->created_at ? $selectedAccountData->created_at->format('M d, Y') : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 text-sm">Last Updated:</span>
                            <span class="font-medium text-gray-900">{{ $selectedAccountData->updated_at ? $selectedAccountData->updated_at->format('M d, Y') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-lg border-l-4 border-orange-600 shadow-sm hover:shadow-md transition-shadow">
                    <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center border-b border-orange-200 pb-2">
                        <i class="fas fa-chart-line text-orange-600 mr-2"></i>
                        Financial Summary
                    </h5>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 text-sm">Current Balance:</span>
                            <span class="font-bold text-lg text-gray-900">{{ number_format($selectedAccountData->balance, 2) }} TZS</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 text-sm">Account Status:</span>
                            <span class="font-medium text-gray-900">{{ $selectedAccountData->status }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 text-sm">Product Type:</span>
                            <span class="font-medium text-gray-900">{{ $selectedAccountData->product_number }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <button wire:click="closeAccountDetailsModal" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors shadow">
                    <i class="fas fa-times mr-2"></i>Close
                </button>
                <button wire:click="downloadAccountStatement('{{ $selectedAccountData->account_number }}')" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow">
                    <i class="fas fa-download mr-2"></i>Download Statement
                </button>
            </div>
        </div>
    </div>
</div>
