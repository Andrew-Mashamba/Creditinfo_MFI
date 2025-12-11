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

    <!-- Board Approval Workflow Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900">Board Approval Workflow</h3>
            <div class="flex items-center gap-4">
                <div class="text-sm text-gray-600">
                    Threshold: <span class="font-semibold">TZS {{ number_format($boardApprovalThreshold, 0) }}</span>
                </div>
                <button wire:click="openThresholdModal" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                    Update Thresholds
                </button>
            </div>
        </div>

        <!-- Pending Board Approvals -->
        <div class="mb-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Pending Board Approvals</h4>
            @if($pendingBoardApprovals->count() > 0)
            <div class="grid gap-4">
                @foreach($pendingBoardApprovals as $writeOff)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                    REQUIRES BOARD APPROVAL
                                </span>
                                <span class="ml-2 text-sm text-gray-600">
                                    Initiated {{ $writeOff->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600">Loan ID</p>
                                    <p class="font-semibold">{{ $writeOff->loan_id }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Client</p>
                                    <p class="font-semibold">{{ $writeOff->client_number }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Amount</p>
                                    <p class="font-semibold text-red-600">TZS {{ number_format($writeOff->total_amount, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Initiated By</p>
                                    <p class="font-semibold">{{ $writeOff->initiator->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="text-sm text-gray-600">Reason:</p>
                                <p class="text-sm text-gray-800">{{ $writeOff->reason }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 ml-4">
                            <button wire:click="approveBoardWriteOff({{ $writeOff->id }})" 
                                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm">
                                Approve
                            </button>
                            <button wire:click="rejectBoardWriteOff({{ $writeOff->id }})" 
                                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm">
                                Reject
                            </button>
                            <button wire:click="viewWriteOffDetails({{ $writeOff->id }})" 
                                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition text-sm">
                                Details
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="bg-gray-50 rounded-lg p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-2 text-sm text-gray-600">No writeoffs pending board approval</p>
            </div>
            @endif
        </div>

        <!-- Approval Workflow Status -->
        <div class="mb-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Approval Workflow Status</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Writeoff ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approval Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approver</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action Date</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach(\App\Models\WriteoffApprovalWorkflow::with(['writeOff', 'approver'])
                            ->latest('assigned_date')
                            ->take(10)
                            ->get() as $workflow)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $workflow->writeOff->loan_id ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $workflow->approval_level_badge['class'] }}">
                                    {{ $workflow->approval_level_badge['text'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $workflow->approver->name ?? 'Unassigned' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $workflow->status_badge['class'] }}">
                                    {{ $workflow->status_badge['text'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $workflow->assigned_date->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $workflow->action_date ? $workflow->action_date->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($workflow->status === 'pending')
                                    <button wire:click="processApproval({{ $workflow->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 text-sm">
                                        Process
                                    </button>
                                @else
                                    <button wire:click="viewWorkflowDetails({{ $workflow->id }})" 
                                            class="text-gray-600 hover:text-gray-900 text-sm">
                                        View
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Approval Statistics -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-sm text-blue-600 font-medium">Total Pending</p>
                <p class="text-2xl font-bold text-blue-800">
                    {{ \App\Models\WriteoffApprovalWorkflow::where('status', 'pending')->count() }}
                </p>
                <p class="text-xs text-blue-600 mt-1">Awaiting approval</p>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
                <p class="text-sm text-green-600 font-medium">Approved</p>
                <p class="text-2xl font-bold text-green-800">
                    {{ \App\Models\WriteoffApprovalWorkflow::where('status', 'approved')->count() }}
                </p>
                <p class="text-xs text-green-600 mt-1">This month</p>
            </div>
            
            <div class="bg-red-50 rounded-lg p-4">
                <p class="text-sm text-red-600 font-medium">Rejected</p>
                <p class="text-2xl font-bold text-red-800">
                    {{ \App\Models\WriteoffApprovalWorkflow::where('status', 'rejected')->count() }}
                </p>
                <p class="text-xs text-red-600 mt-1">This month</p>
            </div>
            
            <div class="bg-purple-50 rounded-lg p-4">
                <p class="text-sm text-purple-600 font-medium">Avg Processing Time</p>
                <p class="text-2xl font-bold text-purple-800">
                    {{ round(\App\Models\WriteoffApprovalWorkflow::whereNotNull('action_date')
                        ->selectRaw('AVG(EXTRACT(EPOCH FROM (action_date - assigned_date))/3600) as avg_hours')
                        ->value('avg_hours') ?? 0, 1) }} hrs
                </p>
                <p class="text-xs text-purple-600 mt-1">Average</p>
            </div>
        </div>
    </div>

    <!-- Threshold Configuration Modal -->
    @if($showThresholdModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showThresholdModal', false)"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Update Approval Thresholds</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Board Approval Threshold (TZS)</label>
                            <input type="number" wire:model="boardApprovalThreshold" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="1000000">
                            @error('boardApprovalThreshold') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Manager Approval Threshold (TZS)</label>
                            <input type="number" wire:model="managerApprovalThreshold" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="100000">
                            @error('managerApprovalThreshold') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Minimum Collection Efforts Required</label>
                            <input type="number" wire:model="minimumCollectionEfforts" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="3" min="1">
                            @error('minimumCollectionEfforts') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recovery Tracking Period (Months)</label>
                            <input type="number" wire:model="recoveryTrackingPeriod" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="12" min="1">
                            @error('recoveryTrackingPeriod') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="updateThresholds" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save Changes
                    </button>
                    <button wire:click="$set('showThresholdModal', false)" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>