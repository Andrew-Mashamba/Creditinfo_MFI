<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Loan Loss Provision Management</h2>
                <p class="text-gray-600 mt-1">IFRS 9 Expected Credit Loss (ECL) Model</p>
            </div>
            <div class="flex gap-3">
                <button wire:click="$set('showCalculationModal', true)" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Calculate Provisions
                </button>
                <button wire:click="$set('showSettingsModal', true)" 
                        class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </button>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button wire:click="$set('activeTab', 'overview')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm 
                        {{ $activeTab === 'overview' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Overview
                </button>
                <button wire:click="$set('activeTab', 'loans')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm 
                        {{ $activeTab === 'loans' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Loan Details
                </button>
                <button wire:click="$set('activeTab', 'staging')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm 
                        {{ $activeTab === 'staging' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    ECL Staging
                </button>
                <button wire:click="$set('activeTab', 'journal')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm 
                        {{ $activeTab === 'journal' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Journal Entries
                </button>
                <button wire:click="$set('activeTab', 'analytics')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm 
                        {{ $activeTab === 'analytics' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Analytics
                </button>
                <button wire:click="$set('activeTab', 'reports')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm 
                        {{ $activeTab === 'reports' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Reports
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    <div>
        @if($activeTab === 'overview')
            <!-- Overview Tab -->
            <div class="space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Total Outstanding</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">
                                    TZS {{ number_format($provisionSummary->total_outstanding ?? 0, 2) }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">{{ $provisionSummary->total_loans ?? 0 }} loans</p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Total Provisions</p>
                                <p class="text-2xl font-bold text-red-600 mt-1">
                                    TZS {{ number_format($provisionSummary->total_provisions ?? 0, 2) }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">{{ number_format($provisionSummary->provision_coverage ?? 0, 2) }}% coverage</p>
                            </div>
                            <div class="p-3 bg-red-100 rounded-lg">
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Stage 3 (NPL)</p>
                                <p class="text-2xl font-bold text-orange-600 mt-1">
                                    TZS {{ number_format($provisionSummary->stage3_exposure ?? 0, 2) }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">{{ $provisionSummary->stage3_count ?? 0 }} loans</p>
                            </div>
                            <div class="p-3 bg-orange-100 rounded-lg">
                                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">NPL Ratio</p>
                                <p class="text-2xl font-bold text-purple-600 mt-1">
                                    {{ number_format(($provisionSummary->stage3_exposure ?? 0) / max(1, $provisionSummary->total_outstanding ?? 1) * 100, 2) }}%
                                </p>
                                <p class="text-xs text-gray-500 mt-1">Non-performing</p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-lg">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ECL Stage Summary -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">ECL Stage Summary</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Stage 1 -->
                        <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                            <h4 class="text-sm font-medium text-green-800 mb-3">Stage 1 - Performing</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Loans:</span>
                                    <span class="text-sm font-medium">{{ $provisionSummary->stage1_count ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Exposure:</span>
                                    <span class="text-sm font-medium">TZS {{ number_format($provisionSummary->stage1_exposure ?? 0, 0) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Provision:</span>
                                    <span class="text-sm font-medium">TZS {{ number_format($provisionSummary->stage1_provisions ?? 0, 0) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Coverage:</span>
                                    <span class="text-sm font-medium">{{ number_format($provisionSummary->stage1_coverage ?? 0, 2) }}%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Stage 2 -->
                        <div class="border border-yellow-200 rounded-lg p-4 bg-yellow-50">
                            <h4 class="text-sm font-medium text-yellow-800 mb-3">Stage 2 - Underperforming</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Loans:</span>
                                    <span class="text-sm font-medium">{{ $provisionSummary->stage2_count ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Exposure:</span>
                                    <span class="text-sm font-medium">TZS {{ number_format($provisionSummary->stage2_exposure ?? 0, 0) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Provision:</span>
                                    <span class="text-sm font-medium">TZS {{ number_format($provisionSummary->stage2_provisions ?? 0, 0) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Coverage:</span>
                                    <span class="text-sm font-medium">{{ number_format($provisionSummary->stage2_coverage ?? 0, 2) }}%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Stage 3 -->
                        <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                            <h4 class="text-sm font-medium text-red-800 mb-3">Stage 3 - Non-Performing</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Loans:</span>
                                    <span class="text-sm font-medium">{{ $provisionSummary->stage3_count ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Exposure:</span>
                                    <span class="text-sm font-medium">TZS {{ number_format($provisionSummary->stage3_exposure ?? 0, 0) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Provision:</span>
                                    <span class="text-sm font-medium">TZS {{ number_format($provisionSummary->stage3_provisions ?? 0, 0) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Coverage:</span>
                                    <span class="text-sm font-medium">{{ number_format($provisionSummary->stage3_coverage ?? 0, 2) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($activeTab === 'loans')
            <!-- Loan Details Tab -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <!-- Search and Filters -->
                <div class="flex gap-4 mb-6">
                    <div class="flex-1">
                        <input type="text" wire:model.debounce.300ms="searchTerm" 
                               placeholder="Search by Loan ID, Client Number, or Name..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <select wire:model="filterStage" class="px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="all">All Stages</option>
                        <option value="1">Stage 1</option>
                        <option value="2">Stage 2</option>
                        <option value="3">Stage 3</option>
                    </select>
                    <select wire:model="filterProduct" class="px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="all">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                        @endforeach
                    </select>
                    <button wire:click="exportProvisionReport" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        Export
                    </button>
                </div>

                <!-- Loans Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Days Arrears</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">ECL Stage</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Provision Rate</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Provision Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($loans as $loan)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $loan->loan_id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $loan->first_name }} {{ $loan->last_name }}
                                    <br><span class="text-xs text-gray-400">{{ $loan->client_number }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $loan->loan_sub_product }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {{ number_format($loan->loan_balance, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $loan->days_in_arrears == 0 ? 'bg-green-100 text-green-800' : 
                                           ($loan->days_in_arrears <= 30 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $loan->days_in_arrears }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $loan->calculated_stage == 'Stage 1' ? 'bg-green-100 text-green-800' : 
                                           ($loan->calculated_stage == 'Stage 2' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $loan->ecl_stage ?? $loan->calculated_stage }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {{ number_format($loan->provision_rate ?? ($loan->calculated_provision / max(1, $loan->loan_balance) * 100), 2) }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                    {{ number_format($loan->provision_amount ?? $loan->calculated_provision, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $loans->links() }}
                </div>
            </div>
        @elseif($activeTab === 'staging')
            <!-- ECL Staging Tab -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">ECL Stage Migration Analysis</h3>
                
                <!-- Stage Migration Matrix -->
                <div class="mb-6">
                    <h4 class="text-md font-medium text-gray-800 mb-3">Stage Migration (Current Month)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">From \ To</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stage 1</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stage 2</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stage 3</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Written Off</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-3 font-medium text-gray-900">Stage 1</td>
                                    <td class="px-6 py-3 text-center">-</td>
                                    <td class="px-6 py-3 text-center text-yellow-600 font-medium">15</td>
                                    <td class="px-6 py-3 text-center text-red-600 font-medium">2</td>
                                    <td class="px-6 py-3 text-center">0</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-3 font-medium text-gray-900">Stage 2</td>
                                    <td class="px-6 py-3 text-center text-green-600 font-medium">8</td>
                                    <td class="px-6 py-3 text-center">-</td>
                                    <td class="px-6 py-3 text-center text-red-600 font-medium">12</td>
                                    <td class="px-6 py-3 text-center">1</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-3 font-medium text-gray-900">Stage 3</td>
                                    <td class="px-6 py-3 text-center text-green-600 font-medium">1</td>
                                    <td class="px-6 py-3 text-center text-green-600 font-medium">3</td>
                                    <td class="px-6 py-3 text-center">-</td>
                                    <td class="px-6 py-3 text-center text-gray-600 font-medium">5</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SICR Indicators -->
                <div>
                    <h4 class="text-md font-medium text-gray-800 mb-3">Significant Increase in Credit Risk (SICR) Indicators</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h5 class="text-sm font-medium text-gray-700 mb-2">Quantitative Indicators</h5>
                            <ul class="space-y-1 text-sm text-gray-600">
                                <li>• Days past due > 30 days</li>
                                <li>• PD increased by 100% from origination</li>
                                <li>• Credit score deterioration > 20%</li>
                                <li>• Debt service ratio > 50%</li>
                            </ul>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h5 class="text-sm font-medium text-gray-700 mb-2">Qualitative Indicators</h5>
                            <ul class="space-y-1 text-sm text-gray-600">
                                <li>• Loan restructuring requested</li>
                                <li>• Significant adverse business changes</li>
                                <li>• Regulatory or legal changes</li>
                                <li>• Forbearance measures granted</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($activeTab === 'journal')
            <!-- Journal Entries Tab -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Provision Journal Entries</h3>
                    <div class="flex gap-3">
                        @if($provisionSummary->total_provisions > 0)
                        <button wire:click="$set('showPostingModal', true)" 
                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                            Post to GL
                        </button>
                        @endif
                        <button wire:click="$set('showReversalModal', true)" 
                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            Reverse Entry
                        </button>
                    </div>
                </div>

                <!-- Sample Journal Entry -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Date: {{ $calculationDate }}</p>
                        <p class="text-sm text-gray-600">Reference: PROV-{{ Carbon\Carbon::parse($calculationDate)->format('Ymd') }}</p>
                        <p class="text-sm text-gray-600">Description: Loan loss provisions for {{ $calculationDate }}</p>
                    </div>
                    
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account Name</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">5010</td>
                                <td class="px-6 py-4 text-sm text-gray-900">Loan Loss Provision Expense</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                    {{ number_format($provisionSummary->total_provisions ?? 0, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">-</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">1290</td>
                                <td class="px-6 py-4 text-sm text-gray-900">Allowance for Loan Losses</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">-</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                    {{ number_format($provisionSummary->total_provisions ?? 0, 2) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="2" class="px-6 py-3 text-sm font-medium text-gray-900">Total</td>
                                <td class="px-6 py-3 text-sm font-medium text-gray-900 text-right">
                                    {{ number_format($provisionSummary->total_provisions ?? 0, 2) }}
                                </td>
                                <td class="px-6 py-3 text-sm font-medium text-gray-900 text-right">
                                    {{ number_format($provisionSummary->total_provisions ?? 0, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @elseif($activeTab === 'analytics')
            <!-- Analytics Tab -->
            <div class="space-y-6">
                <!-- Provision Movement Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Provision Movement Analysis</h3>
                    <canvas id="provisionMovementChart" height="100"></canvas>
                </div>

                <!-- Coverage Ratio Trends -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Coverage Ratio Trends</h3>
                    <canvas id="coverageRatioChart" height="100"></canvas>
                </div>
            </div>
        @elseif($activeTab === 'reports')
            <!-- Reports Tab -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Provision Reports</h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-md font-medium text-gray-800 mb-3">Standard Reports</h4>
                        <div class="space-y-2">
                            <button class="w-full text-left px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm">
                                📊 ECL Calculation Report
                            </button>
                            <button class="w-full text-left px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm">
                                📈 Stage Migration Report
                            </button>
                            <button class="w-full text-left px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm">
                                📉 Coverage Ratio Analysis
                            </button>
                            <button class="w-full text-left px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm">
                                📋 Regulatory Compliance Report
                            </button>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-md font-medium text-gray-800 mb-3">Custom Report Generation</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">From Date</label>
                                <input type="date" wire:model="reportFromDate" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">To Date</label>
                                <input type="date" wire:model="reportToDate" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Report Type</label>
                                <select wire:model="reportType" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="summary">Summary Report</option>
                                    <option value="detailed">Detailed Report</option>
                                    <option value="movement">Movement Report</option>
                                    <option value="staging">Staging Report</option>
                                </select>
                            </div>
                            <button wire:click="generateCustomReport" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                                Generate Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Calculation Modal -->
    @if($showCalculationModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showCalculationModal', false)"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Calculate Loan Provisions</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Calculation Date</label>
                            <input type="date" wire:model="calculationDate" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                            @error('calculationDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Provision Method</label>
                            <select wire:model="provisionMethod" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                <option value="ifrs9">IFRS 9 ECL Model</option>
                                <option value="regulatory">Regulatory (Traditional)</option>
                                <option value="hybrid">Hybrid (Higher of Both)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Economic Scenario</label>
                            <select wire:model="economicScenario" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                <option value="optimistic">Optimistic (-20% provisions)</option>
                                <option value="base">Base Case</option>
                                <option value="pessimistic">Pessimistic (+30% provisions)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="includeForwardLooking" class="mr-2">
                                <span class="text-sm text-gray-700">Include forward-looking adjustments</span>
                            </label>
                        </div>
                        
                        <div class="border-t pt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Stage Provision Rates</h4>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-600">Stage 1 (%)</label>
                                    <input type="number" wire:model="stage1Rate" step="0.1" 
                                           class="mt-1 block w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600">Stage 2 (%)</label>
                                    <input type="number" wire:model="stage2Rate" step="0.1" 
                                           class="mt-1 block w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600">Stage 3 (%)</label>
                                    <input type="number" wire:model="stage3Rate" step="0.1" 
                                           class="mt-1 block w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="calculateProvisions" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Calculate Provisions
                    </button>
                    <button wire:click="$set('showCalculationModal', false)" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Settings Modal -->
    @if($showSettingsModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showSettingsModal', false)"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Provision Settings</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Stage Classification Thresholds (Days)</h4>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-600">Stage 1</label>
                                    <input type="number" wire:model="stage1DaysThreshold" 
                                           class="mt-1 block w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600">Stage 2</label>
                                    <input type="number" wire:model="stage2DaysThreshold" 
                                           class="mt-1 block w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600">Stage 3</label>
                                    <input type="number" wire:model="stage3DaysThreshold" 
                                           class="mt-1 block w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Default Provision Rates (%)</h4>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-600">Stage 1</label>
                                    <input type="number" wire:model="stage1Rate" step="0.1" 
                                           class="mt-1 block w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    @error('stage1Rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600">Stage 2</label>
                                    <input type="number" wire:model="stage2Rate" step="0.1" 
                                           class="mt-1 block w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    @error('stage2Rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600">Stage 3</label>
                                    <input type="number" wire:model="stage3Rate" step="0.1" 
                                           class="mt-1 block w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    @error('stage3Rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="updateProvisionSettings" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Save Settings
                    </button>
                    <button wire:click="$set('showSettingsModal', false)" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script nonce="{{ csp_nonce() }}">
    // Provision Movement Chart
    const movementCtx = document.getElementById('provisionMovementChart');
    if (movementCtx) {
        new Chart(movementCtx, {
            type: 'line',
            data: {
                labels: @json($provisionMovement->pluck('provision_date')),
                datasets: [{
                    label: 'Stage 1',
                    data: @json($provisionMovement->pluck('stage1_provision')),
                    borderColor: 'rgba(34, 197, 94, 1)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Stage 2',
                    data: @json($provisionMovement->pluck('stage2_provision')),
                    borderColor: 'rgba(251, 191, 36, 1)',
                    backgroundColor: 'rgba(251, 191, 36, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Stage 3',
                    data: @json($provisionMovement->pluck('stage3_provision')),
                    borderColor: 'rgba(239, 68, 68, 1)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'TZS ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Coverage Ratio Chart
    const coverageCtx = document.getElementById('coverageRatioChart');
    if (coverageCtx) {
        new Chart(coverageCtx, {
            type: 'bar',
            data: {
                labels: ['Stage 1', 'Stage 2', 'Stage 3', 'Overall'],
                datasets: [{
                    label: 'Coverage Ratio (%)',
                    data: [
                        {{ $provisionSummary->stage1_coverage ?? 0 }},
                        {{ $provisionSummary->stage2_coverage ?? 0 }},
                        {{ $provisionSummary->stage3_coverage ?? 0 }},
                        {{ $provisionSummary->provision_coverage ?? 0 }}
                    ],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(99, 102, 241, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush