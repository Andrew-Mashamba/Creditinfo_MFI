<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Report Header --}}
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Loans to Insiders Report</h1>
                    <p class="mt-1 text-sm text-gray-500">Loans to insiders and related parties disclosure</p>
                    <div class="mt-2 flex items-center space-x-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            BOT Required
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Monthly Report
                        </span>
                    </div>
                </div>
                <div class="flex items-end space-x-4">
                    <div class="flex-shrink-0">
                        <label for="reportDate" class="block text-sm font-medium text-gray-700 mb-2">
                            Report Date
                        </label>
                        <input type="date" 
                               wire:model.live="reportDate" 
                               class="block w-48 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <button wire:click="exportToPdf" 
                            wire:loading.attr="disabled"
                            wire:target="exportToPdf"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <div wire:loading.remove wire:target="exportToPdf">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <span wire:loading.remove wire:target="exportToPdf">Export PDF</span>
                        <span wire:loading wire:target="exportToPdf">Generating PDF...</span>
                    </button>
                    <button wire:click="exportToExcel" 
                            wire:loading.attr="disabled"
                            wire:target="exportToExcel"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <div wire:loading.remove wire:target="exportToExcel">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <span wire:loading.remove wire:target="exportToExcel">Export Excel</span>
                        <span wire:loading wire:target="exportToExcel">Generating Excel...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-blue-50 p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $insiderLoanCount }}</h4>
                    <p class="text-sm text-gray-500">Total Insider Loans</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($totalInsiderLoanAmount, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">Total Loan Amount</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($averageInsiderLoanAmount, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">Average Loan Amount</p>
                </div>
            </div>
        </div>

        <div class="bg-{{ $complianceStatus === 'COMPLIANT' ? 'green' : 'red' }}-50 p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-{{ $complianceStatus === 'COMPLIANT' ? 'green' : 'red' }}-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-{{ $complianceStatus === 'COMPLIANT' ? 'green' : 'red' }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($complianceStatus === 'COMPLIANT')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            @endif
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $complianceStatus }}</h4>
                    <p class="text-sm text-gray-500">Compliance Status</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Insider Categories Breakdown --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Employees</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Count:</span>
                    <span class="text-sm font-medium">{{ $insiderCategories['employees']['count'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Total Amount:</span>
                    <span class="text-sm font-medium">{{ number_format($insiderCategories['employees']['total_amount'], 2) }} TZS</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Average:</span>
                    <span class="text-sm font-medium">{{ number_format($insiderCategories['employees']['average_amount'], 2) }} TZS</span>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Board Members</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Count:</span>
                    <span class="text-sm font-medium">{{ $insiderCategories['board_members']['count'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Total Amount:</span>
                    <span class="text-sm font-medium">{{ number_format($insiderCategories['board_members']['total_amount'], 2) }} TZS</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Average:</span>
                    <span class="text-sm font-medium">{{ number_format($insiderCategories['board_members']['average_amount'], 2) }} TZS</span>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Family Members</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Count:</span>
                    <span class="text-sm font-medium">{{ $insiderCategories['family_members']['count'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Total Amount:</span>
                    <span class="text-sm font-medium">{{ number_format($insiderCategories['family_members']['total_amount'], 2) }} TZS</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Average:</span>
                    <span class="text-sm font-medium">{{ number_format($insiderCategories['family_members']['average_amount'], 2) }} TZS</span>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Other Related Parties</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Count:</span>
                    <span class="text-sm font-medium">{{ $insiderCategories['other_related_parties']['count'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Total Amount:</span>
                    <span class="text-sm font-medium">{{ number_format($insiderCategories['other_related_parties']['total_amount'], 2) }} TZS</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Average:</span>
                    <span class="text-sm font-medium">{{ number_format($insiderCategories['other_related_parties']['average_amount'], 2) }} TZS</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Insider Loans Table --}}
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Insider Loans Details</h3>
            <p class="text-sm text-gray-500">Detailed breakdown of all insider loans</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Relationship</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position/Department</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Interest Rate</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Days in Arrears</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Approval</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($insiderLoans as $loan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $loan['loan_id'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $loan['client_name'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $loan['relationship_type'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex flex-col">
                                    <span>{{ $loan['employee_position'] }}</span>
                                    <span class="text-xs text-gray-500">{{ $loan['employee_department'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                {{ number_format($loan['loan_amount'], 2) }} TZS
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                {{ number_format($loan['outstanding_balance'], 2) }} TZS
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $loan['interest_rate'] }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($loan['loan_status'] === 'ACTIVE') bg-green-100 text-green-800
                                    @elseif($loan['loan_status'] === 'PENDING') bg-yellow-100 text-yellow-800
                                    @elseif($loan['loan_status'] === 'COMPLETED') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $loan['loan_status'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($loan['days_in_arrears'] > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ $loan['days_in_arrears'] }} days
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Current
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($loan['approval_status'] === 'Approved') bg-green-100 text-green-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $loan['approval_status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">
                                No insider loans found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Related Party Loans Table --}}
    @if(count($relatedPartyLoans) > 0)
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Related Party Loans</h3>
                <p class="text-sm text-gray-500">Loans to related parties (non-employees)</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Relationship Type</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Interest Rate</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Days in Arrears</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($relatedPartyLoans as $loan)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $loan['loan_id'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $loan['client_name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $loan['relationship_type'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {{ number_format($loan['loan_amount'], 2) }} TZS
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {{ number_format($loan['outstanding_balance'], 2) }} TZS
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                    {{ $loan['interest_rate'] }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($loan['loan_status'] === 'ACTIVE') bg-green-100 text-green-800
                                        @elseif($loan['loan_status'] === 'PENDING') bg-yellow-100 text-yellow-800
                                        @elseif($loan['loan_status'] === 'COMPLETED') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $loan['loan_status'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($loan['days_in_arrears'] > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ $loan['days_in_arrears'] }} days
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Current
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Compliance Summary --}}
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Compliance Summary</h3>
            <p class="text-sm text-gray-500">BOT Insider Loan Requirements</p>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($maximumInsiderLoanLimit, 2) }} TZS</div>
                    <div class="text-sm text-gray-500">Maximum Loan Limit</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ $insiderLoanCount }}</div>
                    <div class="text-sm text-gray-500">Total Insider Loans</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold {{ $complianceStatus === 'COMPLIANT' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $complianceStatus }}
                    </div>
                    <div class="text-sm text-gray-500">Compliance Status</div>
                </div>
            </div>
            
            <div class="mt-6 p-4 rounded-lg {{ $complianceStatus === 'COMPLIANT' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($complianceStatus === 'COMPLIANT')
                            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium {{ $complianceStatus === 'COMPLIANT' ? 'text-green-800' : 'text-red-800' }}">
                            {{ $complianceStatus === 'COMPLIANT' ? 'Compliant with BOT Insider Loan Requirements' : 'Non-Compliant with BOT Insider Loan Requirements' }}
                        </h3>
                        <div class="mt-2 text-sm {{ $complianceStatus === 'COMPLIANT' ? 'text-green-700' : 'text-red-700' }}">
                            @if($complianceStatus === 'COMPLIANT')
                                All insider loans are within the prescribed limits and properly disclosed as required by the Bank of Tanzania.
                            @else
                                Some insider loans exceed the prescribed limits. Immediate review and corrective action is required to ensure compliance with BOT regulations.
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if (session()->has('success'))
        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif
</div>
