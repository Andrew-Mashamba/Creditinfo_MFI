<div class="min-h-screen bg-gray-50 p-6">
    <!-- Success Message -->
    @if($showSuccessMessage)
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">Your leave request has been submitted successfully.</span>
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
            <h2 class="text-2xl font-bold text-gray-900">Leave Management</h2>
            <p class="text-gray-600 mt-1">Request leave, view history, and check your leave balance</p>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button wire:click="$set('selectedTab', 'request')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'request' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Request Leave
                </button>
                <button wire:click="$set('selectedTab', 'history')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'history' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Leave History
                </button>
                <button wire:click="$set('selectedTab', 'balance')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'balance' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Leave Balance
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            @switch($selectedTab)
                @case('request')
                    <!-- Leave Request Form -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">New Leave Request</h3>
                        <form wire:submit.prevent="submitLeaveRequest" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Leave Type -->
                                <div>
                                    <label for="leaveType" class="block text-sm font-medium text-gray-700 mb-1">
                                        Leave Type <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="leaveType" id="leaveType" 
                                            class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                                        <option value="">Select leave type</option>
                                        <option value="annual">Annual Leave</option>
                                        <option value="sick">Sick Leave</option>
                                        <option value="maternity">Maternity Leave</option>
                                        <option value="paternity">Paternity Leave</option>
                                        <option value="compassionate">Compassionate Leave</option>
                                        <option value="study">Study Leave</option>
                                    </select>
                                    @error('leaveType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Start Date -->
                                <div>
                                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">
                                        Start Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model="startDate" id="startDate" 
                                           class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                                    @error('startDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- End Date -->
                                <div>
                                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">
                                        End Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model="endDate" id="endDate" 
                                           class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                                    @error('endDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Reason -->
                            <div>
                                <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                                    Reason for Leave <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="reason" id="reason" rows="4" 
                                          class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                                          placeholder="Please provide a detailed reason for your leave request..."></textarea>
                                @error('reason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button type="submit" 
                                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-lg transition duration-150 ease-in-out">
                                    Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                    @break

                @case('history')
                    <!-- Leave History -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Leave History</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Leave Type
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Duration
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Days
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Approved By
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reason
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($leaveHistory as $leave)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $leave['type'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ \Carbon\Carbon::parse($leave['start_date'])->format('M d') }} - 
                                            {{ \Carbon\Carbon::parse($leave['end_date'])->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $leave['days'] }} days
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ ucfirst($leave['status']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $leave['approved_by'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $leave['reason'] }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                            No leave history found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @break

                @case('balance')
                    <!-- Leave Balance -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Leave Balance</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($leaveBalance as $balance)
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <h4 class="font-medium text-gray-900 mb-3">{{ $balance['type'] }}</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Entitled:</span>
                                        <span class="font-medium">{{ $balance['entitled'] }} days</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Used:</span>
                                        <span class="font-medium text-red-600">{{ $balance['used'] }} days</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Remaining:</span>
                                        <span class="font-medium text-green-600">{{ $balance['remaining'] }} days</span>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="mt-3">
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-red-600 h-2 rounded-full" 
                                                 style="width: {{ ($balance['used'] / $balance['entitled']) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @break
            @endswitch
        </div>
    </div>
</div>