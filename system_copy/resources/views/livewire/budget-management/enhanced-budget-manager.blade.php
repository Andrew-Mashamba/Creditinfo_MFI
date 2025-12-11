<div class="p-6 space-y-6">
    {{-- Header with tabs --}}
    <div class="bg-white rounded-lg shadow-sm">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button wire:click="changeTab('overview')" class="@if($activeTab === 'overview') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Overview
                </button>
                <button wire:click="changeTab('allocations')" class="@if($activeTab === 'allocations') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Allocations
                </button>
                <button wire:click="changeTab('commitments')" class="@if($activeTab === 'commitments') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Commitments
                </button>
                <button wire:click="changeTab('transfers')" class="@if($activeTab === 'transfers') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Transfers
                </button>
                <button wire:click="changeTab('versions')" class="@if($activeTab === 'versions') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Versions
                </button>
                <button wire:click="changeTab('scenarios')" class="@if($activeTab === 'scenarios') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Scenarios
                </button>
                <button wire:click="changeTab('gl-linking')" class="@if($activeTab === 'gl-linking') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    GL Linking
                </button>
                <button wire:click="changeTab('departments')" class="@if($activeTab === 'departments') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Departments
                </button>
                <button wire:click="changeTab('reports')" class="@if($activeTab === 'reports') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Reports
                </button>
                <button wire:click="changeTab('transactions')" class="@if($activeTab === 'transactions') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    GL Transactions
                </button>
                <button wire:click="changeTab('user-guide')" class="@if($activeTab === 'user-guide') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    User Guide
                </button>
            </nav>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" wire:model.debounce.300ms="searchTerm" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Search budgets...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Budget Type</label>
                <select wire:model="budgetTypeFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Types</option>
                    <option value="OPERATING">Operating</option>
                    <option value="CAPITAL">Capital</option>
                    <option value="PROJECT">Project</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <select wire:model="departmentFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select wire:model="statusFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Status</option>
                    <option value="DRAFT">Draft</option>
                    <option value="APPROVED">Approved</option>
                    <option value="ACTIVE">Active</option>
                    <option value="FROZEN">Frozen</option>
                    <option value="CLOSED">Closed</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Tab Content --}}
    <div class="bg-white rounded-lg shadow-sm p-6">
        @if($activeTab === 'overview')
            {{-- Overview Tab --}}
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900">Budget Overview</h3>
                
                {{-- Budget List --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allocated</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spent</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Committed</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilization</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Health</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($budgets as $budget)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $budget->budget_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $budget->budget_code }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $budget->budgetDepartment->department_name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($budget->allocated_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($budget->spent_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($budget->committed_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($budget->available_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="mr-2 text-sm text-gray-900">{{ $budget->utilization_percentage }}%</div>
                                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                                <div class="bg-{{ $budget->utilization_percentage > 90 ? 'red' : ($budget->utilization_percentage > 75 ? 'yellow' : 'green') }}-500 h-2 rounded-full" style="width: {{ min($budget->utilization_percentage, 100) }}%"></div>
                                            </div>
                                        </div>
                                        @if($budget->expense_account_id && $budget->expenseAccount)
                                            <div class="text-xs text-gray-500 mt-1">
                                                Linked to: {{ $budget->expenseAccount->account_name }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $budget->budget_health === 'HEALTHY' ? 'green' : ($budget->budget_health === 'WARNING' ? 'yellow' : 'red') }}-100 text-{{ $budget->budget_health === 'HEALTHY' ? 'green' : ($budget->budget_health === 'WARNING' ? 'yellow' : 'red') }}-800">
                                            {{ $budget->budget_health }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button wire:click="selectBudget({{ $budget->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">View</button>
                                        <button wire:click="openCommitmentModal({{ $budget->id }})" class="text-green-600 hover:text-green-900 mr-2">Commitment</button>
                                        <button wire:click="openTransferModal({{ $budget->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Transfer</button>
                                        <button wire:click="openVersionModal({{ $budget->id }})" class="text-purple-600 hover:text-purple-900">Version</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $budgets->links() }}
            </div>

        @elseif($activeTab === 'allocations')
            {{-- Allocations Tab --}}
            @include('livewire.budget-management.budget-allocations')

        @elseif($activeTab === 'commitments')
            {{-- Commitments Tab --}}
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Budget Commitments</h3>
                    <button wire:click="openCommitmentModal(null)" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        New Commitment
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commitment #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Committed</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilized</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($commitments as $commitment)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $commitment->commitment_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $commitment->commitment_type }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $commitment->vendor_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $commitment->budget->budget_name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($commitment->committed_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($commitment->utilized_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($commitment->remaining_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $commitment->status === 'COMMITTED' ? 'blue' : ($commitment->status === 'PARTIALLY_UTILIZED' ? 'yellow' : 'green') }}-100">
                                            {{ $commitment->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button wire:click="utilizeCommitment({{ $commitment->id }}, 0)" class="text-green-600 hover:text-green-900 mr-2">Utilize</button>
                                        <button wire:click="cancelCommitment({{ $commitment->id }})" class="text-red-600 hover:text-red-900">Cancel</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif($activeTab === 'transfers')
            {{-- Transfers Tab --}}
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Budget Transfers</h3>
                    <button wire:click="openTransferModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        New Transfer
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From Budget</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">To Budget</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($transfers as $transfer)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $transfer->transfer_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $transfer->fromBudget->budget_name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $transfer->toBudget->budget_name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($transfer->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ Str::limit($transfer->reason, 30) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $transfer->status === 'APPROVED' ? 'green' : ($transfer->status === 'PENDING' ? 'yellow' : 'red') }}-100">
                                            {{ $transfer->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $transfer->created_at->format('Y-m-d') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif($activeTab === 'versions')
            {{-- Versions Tab --}}
            @include('livewire.budget-management.enhanced-budget-versions')

        @elseif($activeTab === 'scenarios')
            {{-- Scenarios Tab --}}
            @include('livewire.budget-management.enhanced-budget-scenarios')

        @elseif($activeTab === 'gl-linking')
            {{-- GL Linking Tab --}}
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900">General Ledger Linking</h3>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <p class="text-sm text-yellow-800">
                        Unlinked GL entries will be automatically linked to appropriate budgets. You can also manually link entries below.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GL Entry</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($unlinkedGLEntries as $entry)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $entry->reference_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $entry->account_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ Str::limit($entry->description, 30) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($entry->debit_amount ?: $entry->credit_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $entry->transaction_date }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <select wire:change="linkGLTransaction({{ $entry->id }}, $event.target.value)" class="text-sm rounded-md border-gray-300">
                                            <option value="">Link to Budget...</option>
                                            @foreach($budgets as $budget)
                                                <option value="{{ $budget->id }}">{{ $budget->budget_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif($activeTab === 'departments')
            {{-- Departments Tab --}}
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Budget Departments</h3>
                    <button wire:click="$set('showDepartmentModal', true)" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        New Department
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost Center</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Budget</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($departments as $department)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $department->department_code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $department->department_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $department->parentDepartment->department_name ?? 'None' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $department->cost_center ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($department->total_budget, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $department->is_active ? 'green' : 'gray' }}-100 text-{{ $department->is_active ? 'green' : 'gray' }}-800">
                                            {{ $department->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif($activeTab === 'transactions')
            {{-- GL Transactions Tab --}}
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900">General Ledger Transactions</h3>
                
                @if($selectedBudget && $selectedBudget->expense_account_id)
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900">{{ $selectedBudget->budget_name }}</h4>
                        <p class="text-sm text-gray-600">Account: {{ $selectedBudget->expenseAccount->account_name }} ({{ $selectedBudget->expenseAccount->account_number }})</p>
                        <p class="text-sm text-gray-600 mt-1">Account Balance: {{ number_format(abs(floatval($selectedBudget->expenseAccount->balance)), 2) }} TZS</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Beneficiary</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $runningBalance = 0;
                                @endphp
                                @foreach($selectedBudget->getUtilizationDetails() as $transaction)
                                    @php
                                        $runningBalance += ($transaction->debit_amount ?? 0) - ($transaction->credit_amount ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $transaction->reference_number ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ Str::limit($transaction->description ?? 'N/A', 50) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $transaction->beneficiary_name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">
                                            {{ $transaction->debit_amount ? number_format($transaction->debit_amount, 2) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                                            {{ $transaction->credit_amount ? number_format($transaction->credit_amount, 2) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            {{ number_format($runningBalance, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-900">Total Utilized:</span>
                            <span class="font-bold text-lg text-gray-900">{{ number_format($runningBalance, 2) }} TZS</span>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="font-medium text-gray-900">Budget Allocated:</span>
                            <span class="font-bold text-lg text-gray-900">{{ number_format($selectedBudget->allocated_amount, 2) }} TZS</span>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="font-medium text-gray-900">Remaining:</span>
                            <span class="font-bold text-lg {{ $selectedBudget->available_amount < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($selectedBudget->available_amount, 2) }} TZS
                            </span>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500">Select a budget from the Overview tab to view GL transactions.</p>
                @endif
            </div>
            
        @elseif($activeTab === 'reports')
            {{-- Reports Tab --}}
            @include('livewire.budget-management.budget-reports')
            
        @elseif($activeTab === 'user-guide')
            {{-- User Guide Tab --}}
            @include('livewire.budget-management.budget-user-guide')
            
        @elseif($activeTab === 'reports-old')
            {{-- Old Reports Tab (deprecated) --}}
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900">Budget Reports</h3>
                
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <form wire:submit.prevent="generateReport">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                                <select wire:model="reportType" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="BUDGET_VS_ACTUAL">Budget vs Actual</option>
                                    <option value="VARIANCE_ANALYSIS">Variance Analysis</option>
                                    <option value="COMMITMENT_SUMMARY">Commitment Summary</option>
                                    <option value="DEPARTMENT_ROLLUP">Department Roll-up</option>
                                    <option value="TREND_ANALYSIS">Trend Analysis</option>
                                    <option value="FORECAST">Forecast Report</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                <select wire:model="reportDepartmentId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="date" wire:model="reportStartDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="date" wire:model="reportEndDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                                Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    {{-- Commitment Modal --}}
    @if($showCommitmentModal)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showCommitmentModal', false)"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="createCommitment">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Create Commitment</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Commitment Type</label>
                                    <select wire:model="commitmentType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="PURCHASE_ORDER">Purchase Order</option>
                                        <option value="CONTRACT">Contract</option>
                                        <option value="SERVICE_AGREEMENT">Service Agreement</option>
                                        <option value="OTHER">Other</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Amount</label>
                                    <input type="number" step="0.01" wire:model="commitmentAmount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('commitmentAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Vendor</label>
                                    <select wire:model="vendorName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Select Vendor...</option>
                                        @foreach(\App\Models\Vendor::where('status', 'ACTIVE')->orderBy('organization_name')->get() as $vendor)
                                            <option value="{{ $vendor->organization_name }}">{{ $vendor->organization_name }} (TIN: {{ $vendor->organization_tin_number }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea wire:model="commitmentDescription" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    @error('commitmentDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Expected Delivery Date</label>
                                    <input type="date" wire:model="expectedDeliveryDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Expiry Date</label>
                                    <input type="date" wire:model="expiryDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Create
                            </button>
                            <button type="button" wire:click="$set('showCommitmentModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Transfer Modal --}}
    @if($showTransferModal)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showTransferModal', false)"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="createTransfer">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Create Budget Transfer</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">From Budget</label>
                                    <select wire:model="fromBudgetId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Select Budget...</option>
                                        @foreach($budgets as $budget)
                                            <option value="{{ $budget->id }}">{{ $budget->budget_name }} (Available: {{ number_format($budget->available_amount, 2) }})</option>
                                        @endforeach
                                    </select>
                                    @error('fromBudgetId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">To Budget</label>
                                    <select wire:model="toBudgetId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Select Budget...</option>
                                        @foreach($budgets as $budget)
                                            @if($budget->id != $fromBudgetId)
                                                <option value="{{ $budget->id }}">{{ $budget->budget_name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('toBudgetId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Transfer Amount</label>
                                    <input type="number" step="0.01" wire:model="transferAmount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('transferAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Reason for Transfer</label>
                                    <textarea wire:model="transferReason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    @error('transferReason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Submit Transfer Request
                            </button>
                            <button type="button" wire:click="$set('showTransferModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Version Modal --}}
    @if($showVersionModal)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showVersionModal', false)"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="createVersion">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Create Budget Version</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Version Name</label>
                                    <input type="text" wire:model="versionName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('versionName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Version Type</label>
                                    <select wire:model="versionType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="ORIGINAL">Original</option>
                                        <option value="REVISED">Revised</option>
                                        <option value="AMENDED">Amended</option>
                                        <option value="SUPPLEMENTARY">Supplementary</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">New Allocated Amount</label>
                                    <input type="number" step="0.01" wire:model="newAllocatedAmount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @if($selectedBudget)
                                        <p class="text-sm text-gray-500 mt-1">Current: {{ number_format($selectedBudget->allocated_amount, 2) }}</p>
                                    @endif
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Revision Reason</label>
                                    <textarea wire:model="revisionReason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    @error('revisionReason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Create Version
                            </button>
                            <button type="button" wire:click="$set('showVersionModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Scenario Modal --}}
    @if($showScenarioModal)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showScenarioModal', false)"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="createScenario">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Create Budget Scenario</h3>
                            
                            <div class="space-y-4">
                                @if(!$selectedBudgetId)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Select Budget</label>
                                        <select wire:model="selectedBudgetId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                            <option value="">Choose a budget...</option>
                                            @foreach($budgets as $budget)
                                                <option value="{{ $budget->id }}">{{ $budget->budget_name }} ({{ $budget->budget_code }})</option>
                                            @endforeach
                                        </select>
                                        @error('selectedBudgetId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                @else
                                    <div class="bg-gray-50 p-3 rounded">
                                        <p class="text-sm text-gray-600">Budget: <span class="font-medium text-gray-900">{{ $budgets->find($selectedBudgetId)->budget_name ?? 'Selected Budget' }}</span></p>
                                    </div>
                                @endif
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Scenario Name</label>
                                    <input type="text" wire:model="scenarioName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('scenarioName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Scenario Type</label>
                                    <select wire:model="scenarioType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="BEST_CASE">Best Case</option>
                                        <option value="EXPECTED">Expected</option>
                                        <option value="WORST_CASE">Worst Case</option>
                                        <option value="CUSTOM">Custom</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Adjustment Percentage (%)</label>
                                    <input type="number" step="0.1" wire:model="adjustmentPercentage" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <p class="text-sm text-gray-500 mt-1">Positive for increase, negative for decrease</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Assumptions (JSON format)</label>
                                    <textarea wire:model="scenarioAssumptions" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder='{"inflation_rate": 3.5, "growth_rate": 5}'></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Create Scenario
                            </button>
                            <button type="button" wire:click="$set('showScenarioModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Department Modal --}}
    @if($showDepartmentModal)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showDepartmentModal', false)"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="createDepartment">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Create Department</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Department Code</label>
                                    <input type="text" wire:model="departmentCode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('departmentCode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Department Name</label>
                                    <input type="text" wire:model="departmentName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('departmentName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Parent Department (Optional)</label>
                                    <select wire:model="parentDepartmentId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">None (Top Level)</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cost Center</label>
                                    <input type="text" wire:model="costCenter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Create Department
                            </button>
                            <button type="button" wire:click="$set('showDepartmentModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Allocation Modal --}}
    @if($showAllocationModal)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showAllocationModal', false)"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit.prevent="saveCustomAllocation">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Custom Budget Allocation</h3>
                            
                            @if($selectedBudget)
                                <p class="text-sm text-gray-500 mb-4">Budget: {{ $selectedBudget->budget_name }} - Total: {{ number_format($selectedBudget->allocated_amount, 2) }}</p>
                            @endif
                            
                            <div class="grid grid-cols-3 gap-4">
                                @foreach($allocations as $month => $allocation)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Month {{ $month }}</label>
                                        <input type="number" step="0.01" wire:model="allocations.{{ $month }}.percentage" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="%">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Save Allocation
                            </button>
                            <button type="button" wire:click="$set('showAllocationModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    
    {{-- Success/Error Messages --}}
    @if (session()->has('message'))
        <div class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    
    {{-- Allocation Setup Modal --}}
    @if($showAllocationSetupModal)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showAllocationSetupModal', false)"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <form wire:submit.prevent="setupAllocations">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Setup Budget Allocations</h3>
                            
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Budget</label>
                                    <select wire:model="selectedBudgetId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                        <option value="">Select Budget...</option>
                                        @foreach($budgets as $budget)
                                            <option value="{{ $budget->id }}">{{ $budget->budget_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Year</label>
                                    <select wire:model="selectedYear" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                        @for($y = date('Y') - 1; $y <= date('Y') + 2; $y++)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Allocation Type</label>
                                <select wire:model="allocationType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="MONTHLY">Monthly (Equal Distribution)</option>
                                    <option value="QUARTERLY">Quarterly</option>
                                    <option value="CUSTOM">Custom</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500">
                                    @if($allocationType === 'MONTHLY')
                                        Budget will be divided equally across all 12 months (8.33% per month)
                                    @elseif($allocationType === 'QUARTERLY')
                                        Budget will be allocated quarterly (25% in March, June, September, and December)
                                    @elseif($allocationType === 'CUSTOM')
                                        Specify custom percentage for each month (must total 100%)
                                    @endif
                                </p>
                            </div>
                            
                            @if($allocationType === 'CUSTOM')
                                <div class="grid grid-cols-4 gap-4">
                                    @foreach($allocationSetupForm as $month => $data)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">
                                                {{ Carbon\Carbon::create(null, $month)->format('F') }}
                                            </label>
                                            <input type="number" step="0.01" wire:model="allocationSetupForm.{{ $month }}.percentage" 
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" 
                                                   placeholder="%" min="0" max="100">
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-2 text-sm text-gray-600">
                                    Total: <span class="font-semibold">{{ collect($allocationSetupForm)->sum('percentage') }}%</span>
                                    @if(collect($allocationSetupForm)->sum('percentage') != 100)
                                        <span class="text-red-600 ml-2">Must equal 100%</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm"
                                    @if($allocationType === 'CUSTOM' && collect($allocationSetupForm)->sum('percentage') != 100) disabled @endif>
                                Create Allocations
                            </button>
                            <button type="button" wire:click="$set('showAllocationSetupModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    
    {{-- Advance Request Modal --}}
    @if($showAdvanceRequestModal)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showAdvanceRequestModal', false)"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="requestAdvance">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Request Budget Advance</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Advance Amount</label>
                                    <input type="number" step="0.01" wire:model="advanceAmount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    @error('advanceAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">From Month</label>
                                        <select wire:model="advanceFromPeriod" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            @for($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}">{{ Carbon\Carbon::create(null, $m)->format('F') }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">From Year</label>
                                        <select wire:model="advanceFromYear" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            @for($y = now()->year; $y <= now()->year + 2; $y++)
                                                <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Reason for Advance</label>
                                    <textarea wire:model="advanceReason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></textarea>
                                    @error('advanceReason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Repayment Plan</label>
                                    <select wire:model="advanceRepaymentPlan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="NEXT_MONTH">Next Month</option>
                                        <option value="INSTALLMENTS">Monthly Installments</option>
                                        <option value="END_OF_YEAR">End of Year</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Request Advance
                            </button>
                            <button type="button" wire:click="$set('showAdvanceRequestModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    
    {{-- Supplementary Request Modal --}}
    @if($showSupplementaryRequestModal)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showSupplementaryRequestModal', false)"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="requestSupplementary">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Request Supplementary Budget</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Supplementary Amount</label>
                                    <input type="number" step="0.01" wire:model="supplementaryAmount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    @error('supplementaryAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Justification</label>
                                    <textarea wire:model="supplementaryJustification" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required placeholder="Provide detailed justification for the supplementary budget request..."></textarea>
                                    @error('supplementaryJustification') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Urgency Level</label>
                                    <select wire:model="supplementaryUrgency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="LOW">Low - Can wait for normal approval</option>
                                        <option value="MEDIUM">Medium - Needed within a week</option>
                                        <option value="HIGH">High - Needed within 2-3 days</option>
                                        <option value="CRITICAL">Critical - Needed immediately</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Supporting Documents</label>
                                    <input type="file" wire:model="supplementaryDocuments" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    <p class="text-xs text-gray-500 mt-1">Attach quotes, invoices, or other supporting documents</p>
                                </div>
                                
                                @if($supplementaryUrgency === 'CRITICAL')
                                    <div class="bg-red-50 border border-red-200 rounded-md p-3">
                                        <p class="text-sm text-red-800">
                                            <strong>Critical Request:</strong> This will be escalated to senior management for immediate approval.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Submit Request
                            </button>
                            <button type="button" wire:click="$set('showSupplementaryRequestModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>