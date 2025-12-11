<div>
    <div class="bg-white shadow rounded-lg p-4 mb-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Approval Requests</h3>
            <div class="flex space-x-4">
                <div class="relative">
                    <input type="text" wire:model.debounce.300ms="searchTerm" placeholder="Search approvals..." class="w-64 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <select wire:model="statusFilter" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="PENDING">Pending</option>
                    <option value="APPROVED">Approved</option>
                    <option value="REJECTED">Rejected</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Process</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Maker</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pendingApprovals as $approval)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $approval->process_name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $approval->process_description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $approval->maker->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($approval->approval_status === 'PENDING') bg-yellow-100 text-yellow-800
                                    @elseif($approval->approval_status === 'APPROVED') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($approval->approval_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($approval->checker_level === 1)
                                    First Checker
                                @elseif($approval->checker_level === 2)
                                    Second Checker
                                @elseif($approval->checker_level === 3)
                                    Approver
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($approval->approval_status === 'PENDING' && $this->canApprove($approval))
                                    @if(Auth::user()->isAdmin())
                                        <button wire:click="showApprovalModal({{ $approval->id }})" class="text-green-600 hover:text-green-900 mr-3">
                                            Approve All
                                        </button>
                                    @else
                                        <button wire:click="showApprovalModal({{ $approval->id }})" class="text-green-600 hover:text-green-900 mr-3">
                                            Approve
                                        </button>
                                        <button wire:click="showRejectionModal({{ $approval->id }})" class="text-red-600 hover:text-red-900">
                                            Reject
                                        </button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                No approval requests found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($selectedApproval)
        <div class="bg-white shadow rounded-lg p-4 mb-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Approval Details</h3>
                <span class="px-2 py-1 text-sm font-semibold rounded-full
                    @if($selectedApproval->approval_status === 'PENDING') bg-yellow-100 text-yellow-800
                    @elseif($selectedApproval->approval_status === 'APPROVED') bg-green-100 text-green-800
                    @else bg-red-100 text-red-800
                    @endif">
                    {{ ucfirst($selectedApproval->approval_status) }}
                </span>
            </div>

            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600">Process</p>
                    <p class="text-sm font-medium">{{ $selectedApproval->process_name }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Description</p>
                    <p class="text-sm font-medium">{{ $selectedApproval->process_description }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Maker</p>
                    <p class="text-sm font-medium">{{ $selectedApproval->maker->name }}</p>
                </div>

                @if($selectedApproval->first_checker_id)
                    <div>
                        <p class="text-sm text-gray-600">First Checker</p>
                        <p class="text-sm font-medium">
                            {{ $selectedApproval->firstChecker->name }}
                            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full
                                @if($selectedApproval->first_checker_status === 'APPROVED') bg-green-100 text-green-800
                                @elseif($selectedApproval->first_checker_status === 'REJECTED') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ $selectedApproval->first_checker_status }}
                            </span>
                        </p>
                    </div>
                @endif

                @if($selectedApproval->second_checker_id)
                    <div>
                        <p class="text-sm text-gray-600">Second Checker</p>
                        <p class="text-sm font-medium">
                            {{ $selectedApproval->secondChecker->name }}
                            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full
                                @if($selectedApproval->second_checker_status === 'APPROVED') bg-green-100 text-green-800
                                @elseif($selectedApproval->second_checker_status === 'REJECTED') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ $selectedApproval->second_checker_status }}
                            </span>
                        </p>
                    </div>
                @endif

                @if($selectedApproval->approver_id)
                    <div>
                        <p class="text-sm text-gray-600">Approver</p>
                        <p class="text-sm font-medium">
                            {{ $selectedApproval->approver->name }}
                            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full
                                @if($selectedApproval->approval_status === 'APPROVED') bg-green-100 text-green-800
                                @elseif($selectedApproval->approval_status === 'REJECTED') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ $selectedApproval->approval_status }}
                            </span>
                        </p>
                    </div>
                @endif

                @if($selectedApproval->comments)
                    <div>
                        <p class="text-sm text-gray-600">Comments</p>
                        <p class="text-sm font-medium">{{ $selectedApproval->comments }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Approval Chain</h3>
            <div class="space-y-4">
                @foreach($approvalChain as $level)
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center
                                @if($level['level'] < $selectedApproval->checker_level) bg-green-100 text-green-800
                                @elseif($level['level'] === $selectedApproval->checker_level) bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $level['level'] }}
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium">{{ $level['title'] }}</p>
                            <p class="text-sm text-gray-500">{{ $level['roles'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Approval Modal -->
    <div x-data="{ show: @entangle('showApprovalModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="approve">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    @if(Auth::user()->isAdmin())
                                        Approve All Levels
                                    @else
                                        Approve Request
                                    @endif
                                </h3>
                                @if(Auth::user()->isAdmin())
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            As a Systems Administrator, this will approve all levels of the approval chain at once.
                                        </p>
                                    </div>
                                @endif
                                <div class="mt-4">
                                    <label for="comment" class="block text-sm font-medium text-gray-700">Comments (Optional)</label>
                                    <textarea wire:model="comment" id="comment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    @error('comment') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            @if(Auth::user()->isAdmin())
                                Approve All
                            @else
                                Approve
                            @endif
                        </button>
                        <button type="button" wire:click="$set('showApprovalModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div x-data="{ show: @entangle('showRejectionModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="reject">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Reject Request</h3>
                                <div class="mt-4">
                                    <label for="comment" class="block text-sm font-medium text-gray-700">Comments (Required)</label>
                                    <textarea wire:model="comment" id="comment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    @error('comment') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Reject
                        </button>
                        <button type="button" wire:click="$set('showRejectionModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 