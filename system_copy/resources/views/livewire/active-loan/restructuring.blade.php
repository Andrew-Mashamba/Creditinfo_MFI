<div>
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Loan Restructuring & Rescheduling</h2>
                <p class="text-gray-600 mt-1">Manage loan restructuring requests and payment rescheduling</p>
            </div>
            <div class="flex gap-3">
                <select wire:model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="eligible">Eligible Loans</option>
                    <option value="pending">Pending Approval</option>
                    <option value="approved">Approved</option>
                    <option value="all">All Restructures</option>
                </select>
                <button wire:click="exportRestructures" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    Export Report
                </button>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex gap-4">
            <div class="flex-1">
                <input type="text" wire:model.debounce.300ms="searchTerm" 
                       placeholder="Search by Loan ID, Client Number, or Name..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <input type="date" wire:model="dateFrom" 
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <input type="date" wire:model="dateTo" 
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    <!-- Eligible Loans Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Loans Eligible for Restructuring</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Arrears</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Days Overdue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classification</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($eligibleLoans as $loan)
                        <tr class="{{ $loop->odd ? 'bg-white' : 'bg-gray-50' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $loan->loan_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $loan->first_name ?? '' }} {{ $loan->last_name ?? '' }}
                                <br>
                                <span class="text-xs text-gray-400">{{ $loan->client_number }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $loan->loan_sub_product }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                TZS {{ number_format($loan->outstanding, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <span class="text-red-600 font-medium">
                                    TZS {{ number_format($loan->total_arrears, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $loan->days_in_arrears > 90 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $loan->days_in_arrears }} days
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $loan->loan_classification === 'WATCH' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $loan->loan_classification === 'SUBSTANDARD' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $loan->loan_classification === 'DOUBTFUL' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $loan->loan_classification }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button wire:click="initiateRestructure({{ $loan->id }})" 
                                        class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700 transition">
                                    Restructure
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                No loans eligible for restructuring
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-4">
            {{ $eligibleLoans->links() }}
        </div>
    </div>

    <!-- Restructured Loans Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Restructured Loans History</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved By</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($restructuredLoans as $restructure)
                        <tr class="{{ $loop->odd ? 'bg-white' : 'bg-gray-50' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ Carbon\Carbon::parse($restructure->restructure_date)->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $restructure->loan_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $restructure->first_name ?? '' }} {{ $restructure->last_name ?? '' }}
                                <br>
                                <span class="text-xs text-gray-400">{{ $restructure->client_number }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ str_replace('_', ' ', ucwords($restructure->restructure_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ Str::limit($restructure->reason, 50) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($restructure->status === 'pending_approval')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @elseif($restructure->status === 'approved')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Approved
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ ucfirst($restructure->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $restructure->approved_by_name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($restructure->status === 'pending_approval')
                                    <button wire:click="approveRestructure({{ $restructure->id }})" 
                                            class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition">
                                        Approve
                                    </button>
                                @else
                                    <button wire:click="viewDetails({{ $restructure->id }})" 
                                            class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700 transition">
                                        View
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                No restructured loans found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-4">
            {{ $restructuredLoans->links() }}
        </div>
    </div>

    <!-- Restructure Modal -->
    @if($showRestructureModal && $selectedLoan)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showRestructureModal', false)"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Loan Restructuring Options
                        </h3>
                        
                        <!-- Loan Details -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Loan ID:</span>
                                    <span class="font-medium ml-2">{{ $selectedLoan->loan_id }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Client:</span>
                                    <span class="font-medium ml-2">{{ $selectedLoan->first_name }} {{ $selectedLoan->last_name }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Outstanding:</span>
                                    <span class="font-medium text-red-600 ml-2">TZS {{ number_format($selectedLoan->outstanding, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Current Term:</span>
                                    <span class="font-medium ml-2">{{ $selectedLoan->repayment_period }} months</span>
                                </div>
                            </div>
                        </div>

                        <!-- Restructure Type Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Restructure Type</label>
                            <select wire:model="restructureType" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="reschedule">Complete Reschedule</option>
                                <option value="extend_term">Extend Loan Term</option>
                                <option value="reduce_interest">Reduce Interest Rate</option>
                                <option value="payment_holiday">Payment Holiday</option>
                            </select>
                        </div>

                        <!-- Dynamic Fields Based on Restructure Type -->
                        @if($restructureType === 'extend_term')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">New Term (months)</label>
                                <input type="number" wire:model="newTerms.new_term" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       min="{{ $selectedLoan->repayment_period + 1 }}">
                                @error('newTerms.new_term') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @elseif($restructureType === 'reduce_interest')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">New Interest Rate (%) - Current: {{ $selectedLoan->interest }}%</label>
                                <input type="number" wire:model="newTerms.new_interest_rate" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       step="0.01" max="{{ $selectedLoan->interest - 0.01 }}">
                                @error('newTerms.new_interest_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @elseif($restructureType === 'payment_holiday')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Holiday Period (months)</label>
                                <select wire:model="newTerms.holiday_months" 
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select period</option>
                                    <option value="1">1 Month</option>
                                    <option value="2">2 Months</option>
                                    <option value="3">3 Months</option>
                                    <option value="6">6 Months</option>
                                </select>
                                @error('newTerms.holiday_months') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @elseif($restructureType === 'reschedule')
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">New Term (months)</label>
                                    <input type="number" wire:model="newTerms.new_term" 
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">New Interest Rate (%)</label>
                                    <input type="number" wire:model="newTerms.new_interest_rate" 
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                           step="0.01">
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="newTerms.clear_arrears" 
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Clear existing arrears</span>
                                    </label>
                                </div>
                            </div>
                        @endif

                        <!-- Effective Date -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Effective Date</label>
                            <input type="date" wire:model="newTerms.effective_date" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <!-- Reason -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Reason for Restructuring</label>
                            <textarea wire:model="newTerms.reason" rows="3"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                      placeholder="Provide detailed reason for restructuring..."></textarea>
                            @error('newTerms.reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="processRestructure" type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Submit Restructure Request
                        </button>
                        <button wire:click="$set('showRestructureModal', false)" type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>