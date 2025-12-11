<div>
    {{-- Loading Overlay --}}
    @if($isLoading)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-600">{{ $processingMessage }}</p>
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
            <h2 class="text-2xl font-bold text-gray-800">Mandatory Savings Management</h2>
            <div class="flex space-x-2">
                <button wire:click="showSettings" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Settings
                </button>
                <button wire:click="showArrearsReport" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Arrears Report
                </button>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-blue-800">Total Records</h3>
                <p class="text-2xl font-bold text-blue-900">{{ number_format($summaryStats['total_records']) }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-green-800">Paid Records</h3>
                <p class="text-2xl font-bold text-green-900">{{ number_format($summaryStats['paid_records']) }}</p>
                <p class="text-sm text-green-600">{{ number_format($summaryStats['compliance_rate'], 1) }}% compliance</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-yellow-800">Unpaid Records</h3>
                <p class="text-2xl font-bold text-yellow-900">{{ number_format($summaryStats['unpaid_records']) }}</p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-red-800">Overdue Records</h3>
                <p class="text-2xl font-bold text-red-900">{{ number_format($summaryStats['overdue_records']) }}</p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <button wire:click="generateTrackingRecords" 
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-900 disabled:opacity-50"
                    @if($isLoading) disabled @endif>
                Generate Tracking Records
            </button>
            <button wire:click="updateFromPayments" 
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50"
                    @if($isLoading) disabled @endif>
                Update from Payments
            </button>
            <button wire:click="generateNotifications" 
                    class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 disabled:opacity-50"
                    @if($isLoading) disabled @endif>
                Generate Notifications
            </button>
            <button wire:click="processOverdueRecords" 
                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 disabled:opacity-50"
                    @if($isLoading) disabled @endif>
                Process Overdue
            </button>
        </div>

        {{-- Filters --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Year</label>
                <select wire:model="selectedYear" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach(range(2020, 2030) as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Month</label>
                <select wire:model="selectedMonth" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}">{{ Carbon\Carbon::create()->month($month)->format('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select wire:model="selectedStatus" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="PAID">Paid</option>
                    <option value="PARTIAL">Partial</option>
                    <option value="UNPAID">Unpaid</option>
                    <option value="OVERDUE">Overdue</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" wire:model.debounce.300ms="searchTerm" 
                       placeholder="Search members..." 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <label class="flex items-center">
                    <input type="checkbox" wire:model="showArrearsOnly" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Show Arrears Only</span>
                </label>
            </div>
        </div>

        {{-- Tracking Records Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($trackingRecords as $record)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $record->client->first_name ?? '' }} {{ $record->client->last_name ?? '' }}
                                </div>
                                <div class="text-sm text-gray-500">{{ $record->client_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $record->account_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $record->period }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                TZS {{ number_format($record->required_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                TZS {{ number_format($record->paid_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                TZS {{ number_format($record->balance, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($record->status === 'PAID') bg-green-100 text-green-800
                                    @elseif($record->status === 'PARTIAL') bg-yellow-100 text-yellow-800
                                    @elseif($record->status === 'OVERDUE') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $record->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $record->due_date->format('d/m/Y') }}
                                @if($record->isOverdue())
                                    <div class="text-xs text-red-600">{{ $record->days_overdue }} days overdue</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button wire:click="viewMemberDetails('{{ $record->client_number }}')" 
                                        class="text-blue-600 hover:text-blue-900">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $trackingRecords->links() }}
        </div>
    </div>

    {{-- Settings Modal --}}
    @if($showSettingsModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="saveSettings">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Mandatory Savings Settings</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Monthly Amount (TZS)</label>
                                    <input type="number" step="0.01" wire:model="monthlyAmount" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('monthlyAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Due Day of Month</label>
                                    <input type="number" wire:model="dueDay" min="1" max="31"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('dueDay') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Grace Period (Days)</label>
                                    <input type="number" wire:model="gracePeriodDays" min="0" max="30"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('gracePeriodDays') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="enableNotifications" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <label class="ml-2 text-sm text-gray-700">Enable Notifications</label>
                                </div>

                                @if($enableNotifications)
                                    <div class="space-y-2 pl-4 border-l-2 border-gray-200">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">First Reminder (Days Before Due)</label>
                                            <input type="number" wire:model="firstReminderDays" min="1" max="30"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('firstReminderDays') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Second Reminder (Days Before Due)</label>
                                            <input type="number" wire:model="secondReminderDays" min="1" max="30"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('secondReminderDays') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Final Reminder (Days Before Due)</label>
                                            <input type="number" wire:model="finalReminderDays" min="1" max="30"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('finalReminderDays') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Save Settings
                            </button>
                            <button type="button" wire:click="$set('showSettingsModal', false)"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Arrears Modal --}}
    @if($showArrearsModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-4/5">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Arrears Report</h3>
                        
                        @if($selectedMember)
                            <div class="mb-4 p-4 bg-gray-50 rounded-md">
                                <h4 class="text-sm font-medium text-gray-900">Member Details</h4>
                                <p class="mt-1 text-sm text-gray-600">{{ $selectedMember->first_name }} {{ $selectedMember->last_name }}</p>
                                <p class="mt-1 text-sm text-gray-600">Client Number: {{ $selectedMember->client_number }}</p>
                            </div>
                        @endif

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Arrears</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Months in Arrears</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php
                                        $service = new \App\Services\MandatorySavingsService();
                                        $arrearsData = $service->calculateArrears();
                                    @endphp
                                    @foreach($arrearsData as $arrear)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $arrear['client_name'] }}</div>
                                                <div class="text-sm text-gray-500">{{ $arrear['client_number'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                TZS {{ number_format($arrear['total_arrears'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $arrear['months_in_arrears'] }} months
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button wire:click="viewMemberDetails('{{ $arrear['client_number'] }}')" 
                                                        class="text-blue-600 hover:text-blue-900">
                                                    View Details
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="$set('showArrearsModal', false)"
                                class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div> 