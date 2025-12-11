<div>
    @if (session()->has('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    
    @if (session()->has('info'))
        <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('info') }}</span>
        </div>
    @endif

    <!-- Request Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Pending Requests -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalPending }}</p>
                </div>
            </div>
        </div>

        <!-- Processing -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Processing</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalProcessing }}</p>
                </div>
            </div>
        </div>

        <!-- Approved -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Approved (Month)</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalApproved }}</p>
                </div>
            </div>
        </div>

        <!-- Rejected -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Rejected (Month)</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalRejected }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Type Counts -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        @foreach($requestCounts as $type => $count)
            @if($count > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                    <span class="text-lg font-bold text-orange-600">{{ $count }}</span>
                </div>
            </div>
            @endif
        @endforeach
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <input type="text" wire:model.debounce.300ms="search" 
                   placeholder="Search by employee or subject..." 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            
            <select wire:model="filterType" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">All Types</option>
                <option value="materials">Working Materials</option>
                <option value="resignation">Resignation</option>
                <option value="travel">Travel</option>
                <option value="advance">Advance</option>
                <option value="training">Training</option>
                <option value="workshop">Workshop</option>
                <option value="overtime">Overtime</option>
                <option value="payslip">Payslip</option>
                <option value="hr_docs">HR Documents</option>
                <option value="general">General</option>
            </select>
            
            <select wire:model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="completed">Completed</option>
            </select>
            
            <input type="date" wire:model="filterDateFrom" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            
            <input type="date" wire:model="filterDateTo" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
    </div>

    <!-- Requests Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Employee Requests</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employee
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Subject
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($requests as $request)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $request->created_at->format('M d, Y') }}
                            <div class="text-xs text-gray-500">{{ $request->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                @if($request->employee)
                                    {{ $request->employee->first_name }} {{ $request->employee->last_name }}
                                    <div class="text-xs text-gray-500">{{ $request->employee->employee_number }}</div>
                                @else
                                    Unknown Employee
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                                {{ $request->type_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-xs truncate">
                                {{ $request->subject ?? 'No subject' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                bg-{{ $request->status_color }}-100 text-{{ $request->status_color }}-800">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex items-center space-x-2">
                                <button wire:click="viewDetails({{ $request->id }})" 
                                        class="text-blue-600 hover:text-blue-900" title="View Details">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 116 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                
                                @if($request->status === 'pending')
                                    <button wire:click="approveRequest({{ $request->id }})" 
                                            class="text-green-600 hover:text-green-900" title="Approve">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <button wire:click="viewDetails({{ $request->id }})" 
                                            class="text-red-600 hover:text-red-900" title="Reject">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                @elseif($request->status === 'approved')
                                    <button wire:click="markAsProcessing({{ $request->id }})" 
                                            class="text-blue-600 hover:text-blue-900" title="Mark as Processing">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                @elseif($request->status === 'processing')
                                    <button wire:click="markAsCompleted({{ $request->id }})" 
                                            class="text-purple-600 hover:text-purple-900" title="Mark as Completed">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No requests found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $requests->links() }}
        </div>
    </div>

    <!-- Request Details Modal -->
    @if($showDetailsModal && $selectedRequest)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="flex justify-between items-start mb-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    Request Details
                </h3>
                <button wire:click="closeDetailsModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <!-- Request Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Employee</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @if($selectedRequest->employee)
                                {{ $selectedRequest->employee->first_name }} {{ $selectedRequest->employee->last_name }}
                                ({{ $selectedRequest->employee->employee_number }})
                            @else
                                Unknown Employee
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Request Type</label>
                        <p class="mt-1">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                                {{ $selectedRequest->type_label }}
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Department</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $selectedRequest->department ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date Submitted</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $selectedRequest->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Current Status</label>
                        <p class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                bg-{{ $selectedRequest->status_color }}-100 text-{{ $selectedRequest->status_color }}-800">
                                {{ ucfirst($selectedRequest->status) }}
                            </span>
                        </p>
                    </div>
                    
                    @if($selectedRequest->approver)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Handled By</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $selectedRequest->approver->name }}</p>
                    </div>
                    @endif
                </div>
                
                <!-- Subject -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Subject</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $selectedRequest->subject ?? 'No subject provided' }}</p>
                </div>
                
                <!-- Details -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Details</label>
                    <div class="mt-1 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $selectedRequest->details ?? 'No details provided' }}</p>
                    </div>
                </div>
                
                @if($selectedRequest->rejection_reason)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Rejection Reason</label>
                    <div class="mt-1 p-3 bg-red-50 rounded-lg">
                        <p class="text-sm text-red-900">{{ $selectedRequest->rejection_reason }}</p>
                    </div>
                </div>
                @endif
                
                @if($selectedRequest->attachments && count($selectedRequest->attachments) > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Attachments</label>
                    <div class="mt-1 space-y-2">
                        @foreach($selectedRequest->attachments as $attachment)
                        <div class="flex items-center space-x-2 text-sm text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span>{{ $attachment }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Actions -->
                @if($selectedRequest->status === 'pending')
                <div class="border-t pt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason (if rejecting)</label>
                    <textarea wire:model="rejectionReason" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Provide reason for rejection..."></textarea>
                    @error('rejectionReason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button wire:click="closeDetailsModal" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Close
                    </button>
                    <button wire:click="rejectRequest({{ $selectedRequest->id }})" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Reject Request
                    </button>
                    <button wire:click="approveRequest({{ $selectedRequest->id }})" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Approve Request
                    </button>
                </div>
                @else
                <div class="flex justify-end pt-4 border-t">
                    <button wire:click="closeDetailsModal" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Close
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>