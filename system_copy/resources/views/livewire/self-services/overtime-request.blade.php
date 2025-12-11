<div class="min-h-screen bg-gray-50 p-6">
    <!-- Success Message -->
    @if($showSuccessMessage)
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">Your overtime request has been submitted successfully for approval.</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
            <svg wire:click="$set('showSuccessMessage', false)" class="fill-current h-6 w-6 text-green-500 cursor-pointer" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <title>Close</title>
                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
            </svg>
        </span>
    </div>
    @endif

    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Overtime Request</h2>
            <p class="text-gray-600 mt-1">Submit overtime requests, view history, and track earnings</p>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button wire:click="$set('selectedTab', 'request')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'request' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    New Request
                </button>
                <button wire:click="$set('selectedTab', 'history')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Overtime History
                </button>
                <button wire:click="$set('selectedTab', 'summary')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'summary' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Summary & Rates
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            @switch($selectedTab)
                @case('request')
                    <!-- Overtime Request Form -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Submit Overtime Request</h3>
                        
                        <!-- Monthly Limit Alert -->
                        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm text-blue-800">
                                    You have <span class="font-bold">{{ $overtimeSummary['hours_remaining'] }} hours</span> remaining from your monthly limit of {{ $monthlyLimit }} hours.
                                </p>
                            </div>
                        </div>

                        <form wire:submit.prevent="submitOvertimeRequest" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Overtime Date -->
                                <div>
                                    <label for="overtimeDate" class="block text-sm font-medium text-gray-700 mb-1">
                                        Overtime Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model="overtimeDate" id="overtimeDate" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                           max="{{ date('Y-m-d') }}">
                                    @error('overtimeDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Overtime Type -->
                                <div>
                                    <label for="overtimeType" class="block text-sm font-medium text-gray-700 mb-1">
                                        Overtime Type <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="overtimeType" id="overtimeType" 
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select type</option>
                                        <option value="weekday">Weekday</option>
                                        <option value="weekend">Weekend</option>
                                        <option value="holiday">Public Holiday</option>
                                        <option value="night">Night Shift</option>
                                    </select>
                                    @error('overtimeType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Start Time -->
                                <div>
                                    <label for="startTime" class="block text-sm font-medium text-gray-700 mb-1">
                                        Start Time <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" wire:model="startTime" id="startTime" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    @error('startTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- End Time -->
                                <div>
                                    <label for="endTime" class="block text-sm font-medium text-gray-700 mb-1">
                                        End Time <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" wire:model="endTime" id="endTime" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    @error('endTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Total Hours (Auto-calculated) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Total Hours
                                    </label>
                                    <input type="text" value="{{ $totalHours }}" 
                                           class="w-full rounded-lg bg-gray-100 border-gray-300" 
                                           readonly>
                                    @error('totalHours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Project/Task Code -->
                                <div>
                                    <label for="projectCode" class="block text-sm font-medium text-gray-700 mb-1">
                                        Project/Task Code <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="projectCode" id="projectCode" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="Enter project or task code">
                                    @error('projectCode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Approving Supervisor -->
                                <div class="md:col-span-2">
                                    <label for="supervisor" class="block text-sm font-medium text-gray-700 mb-1">
                                        Approving Supervisor <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="supervisor" id="supervisor" 
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select supervisor</option>
                                        <option value="john_manager">John Manager - Operations</option>
                                        <option value="jane_supervisor">Jane Supervisor - Finance</option>
                                        <option value="mike_director">Mike Director - IT</option>
                                        <option value="sarah_lead">Sarah Lead - HR</option>
                                    </select>
                                    @error('supervisor') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Reason/Justification -->
                            <div>
                                <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                                    Reason/Justification <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="reason" id="reason" rows="4" 
                                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Provide detailed justification for overtime work..."></textarea>
                                @error('reason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button type="submit" 
                                        class="bg-blue-900 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-150 ease-in-out">
                                    Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                    @break

                @case('history')
                    <!-- Overtime History -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Overtime History</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Time
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Hours
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reason
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($overtimeHistory as $overtime)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($overtime['date'])->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $overtime['start'] }} - {{ $overtime['end'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ $overtime['hours'] }} hrs
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $overtime['type'] }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($overtime['status'] == 'approved')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Approved
                                                </span>
                                            @elseif($overtime['status'] == 'pending')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Rejected
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            @if($overtime['amount'] > 0)
                                                TSH {{ number_format($overtime['amount']) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">
                                            {{ $overtime['reason'] }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                            No overtime history found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @break

                @case('summary')
                    <!-- Summary & Rates -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Overtime Summary</h3>
                        
                        <!-- Monthly Summary -->
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">{{ $overtimeSummary['current_month'] }} Summary</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-blue-600 font-medium">Total Hours</p>
                                            <p class="text-2xl font-bold text-blue-900">{{ $overtimeSummary['hours_worked'] }}</p>
                                        </div>
                                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="bg-green-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-green-600 font-medium">Approved</p>
                                            <p class="text-2xl font-bold text-green-900">{{ $overtimeSummary['hours_approved'] }}</p>
                                        </div>
                                        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="bg-yellow-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-yellow-600 font-medium">Pending</p>
                                            <p class="text-2xl font-bold text-yellow-900">{{ $overtimeSummary['hours_pending'] }}</p>
                                        </div>
                                        <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Earnings -->
                            <div class="mt-4 bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-600 font-medium">Total Overtime Earnings</p>
                                        <p class="text-2xl font-bold text-gray-900">TSH {{ number_format($overtimeSummary['total_earnings']) }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mt-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Monthly Limit Usage</span>
                                    <span>{{ $overtimeSummary['hours_worked'] + $overtimeSummary['hours_pending'] }}/{{ $monthlyLimit }} hours</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-900 h-2 rounded-full" 
                                         style="width: {{ (($overtimeSummary['hours_worked'] + $overtimeSummary['hours_pending']) / $monthlyLimit) * 100 }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Overtime Rates -->
                        <div>
                            <h4 class="text-md font-semibold text-gray-800 mb-3">Overtime Rates</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Type
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Rate Multiplier
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Per Hour Rate
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($overtimeRates as $rate)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ $rate['type'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ $rate['rate'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                TSH {{ number_format($rate['per_hour']) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Policy Note -->
                        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Important Policy</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>Maximum overtime allowed: {{ $monthlyLimit }} hours per month</li>
                                            <li>All overtime must be pre-approved by your supervisor</li>
                                            <li>Overtime pay is processed with the monthly payroll</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @break
            @endswitch
        </div>
    </div>
</div>