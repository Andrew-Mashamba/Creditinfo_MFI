<div class="space-y-8">

    <!-- Search and Pagination Controls -->
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text" wire:model.debounce.500ms="search"
                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64"
                    placeholder="Search branches..." />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <label for="perPage" class="text-sm font-medium text-gray-700">Show:</label>
            <select wire:model="perPage" id="perPage"
                class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="5">5 per page</option>
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
            </select>
        </div>
    </div>

    <!-- Branches Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-x-auto branches-table-scroll">
        <table class="max-w-2/3 divide-y divide-gray-200" aria-label="Branches Table">
            <thead class="bg-gray-50">
                <tr>
                    @foreach ([
                            'branch_number' => 'Branch Number',
                            'name' => 'Branch Name',
                            'region' => 'Region',
                            'wilaya' => 'District',
                            'status' => 'Status',
                            'branch_type' => 'Type',
                            'branch_manager' => 'Manager',
                            'created_at' => 'Created At',
                        ] as $field => $label)
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-150"
                            wire:click="sortBy('{{ $field }}')">
                            <div class="flex items-center space-x-1">
                                <span>{{ $label }}</span>
                                @if ($sortField === $field)
                                    @if ($sortDirection === 'asc')
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>
                    @endforeach
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($branches as $branch)
                    <tr class="hover:bg-blue-50 focus-within:bg-blue-100 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap align-top">
                            <div class="text-sm font-semibold text-gray-900">#{{ $branch->branch_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top">
                            <div class="text-sm font-semibold text-gray-900">{{ $branch->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top">
                            <div class="text-sm text-gray-900">{{ $branch->region }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top">
                            <div class="text-sm text-gray-900">{{ $branch->wilaya }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top">
                            @php
                                $statusColors = [
                                    'ACTIVE' => 'bg-green-100 text-green-800',
                                    'INACTIVE' => 'bg-red-100 text-red-800',
                                    'PENDING' => 'bg-yellow-100 text-yellow-800',
                                    'SUSPENDED' => 'bg-gray-100 text-gray-800',
                                ];
                                $statusColor = $statusColors[strtoupper($branch->status)] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusColor }}"
                                aria-label="Status: {{ ucfirst(strtolower($branch->status)) }}">
                                {{ ucfirst(strtolower($branch->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top">
                            <div class="text-sm text-gray-900">{{ $branch->branch_type }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center"
                                    aria-hidden="true">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ DB::table('users')->where('id', $branch->branch_manager)->first()->name ?? 'Not Assigned' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top text-sm text-gray-700">
                            <span>{{ $branch->created_at->format('M d, Y') }}</span>
                            <div class="text-xs text-gray-400 mt-1">{{ $branch->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top text-sm font-medium">
                            @include('livewire.branches.branch-actions', [
                                'id' => $branch->id,
                                'canView' => $canView,
                                'canEdit' => $canEdit,
                                'canDelete' => $canDelete,
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-400 text-base">No branches found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $branches->links() }}
    </div>

    <!-- View Modal -->
    @if($showViewModal && $selectedBranch)
    <div class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-4xl w-full p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Branch Details</h3>
                    <button wire:click="closeViewModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-3">Basic Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Branch Number</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->branch_number ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Branch Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->name ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Region</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->region ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">District (Wilaya)</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->wilaya ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    @if($selectedBranch->status == 'ACTIVE') bg-green-100 text-green-800
                                    @elseif($selectedBranch->status == 'INACTIVE') bg-red-100 text-red-800
                                    @elseif($selectedBranch->status == 'PENDING') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $selectedBranch->status ?: 'N/A' }}
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Type</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->branch_type ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-3">Contact Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->email ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->phone_number ?: 'N/A' }}</p>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->address ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Operational Details -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-3">Operational Details</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Opening Date</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->opening_date ? \Carbon\Carbon::parse($selectedBranch->opening_date)->format('M d, Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Operating Hours</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->operating_hours ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Branch Manager</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ DB::table('users')->where('id', $selectedBranch->branch_manager)->first()->name ?? 'Not Assigned' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">CIT Provider</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $selectedBranch->cit_provider_id ? DB::table('cash_in_transit_providers')->where('id', $selectedBranch->cit_provider_id)->first()->name ?? 'N/A' : 'N/A' }}
                                </p>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Services Offered</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->services_offered ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-3">Account Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Vault Account</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->vault_account ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Till Account</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->till_account ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Petty Cash Account</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->petty_cash_account ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-3">System Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Created At</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->created_at ? $selectedBranch->created_at->format('M d, Y h:i A') : 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedBranch->updated_at ? $selectedBranch->updated_at->format('M d, Y h:i A') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button wire:click="closeViewModal" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Modal -->
    @if($showEditModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-4xl w-full p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Branch</h3>
                    <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-3">Basic Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Branch Number</label>
                                <input type="text" wire:model="editBranchNumber" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Branch Name</label>
                                <input type="text" wire:model="editName" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Region</label>
                                <input type="text" wire:model="editRegion" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">District (Wilaya)</label>
                                <input type="text" wire:model="editWilaya" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select wire:model="editStatus" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="ACTIVE">Active</option>
                                    <option value="INACTIVE">Inactive</option>
                                    <option value="PENDING">Pending</option>
                                    <option value="SUSPENDED">Suspended</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Branch Type</label>
                                <select wire:model="editBranchType" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="MAIN">Main Branch</option>
                                    <option value="SUB">Sub Branch</option>
                                    <option value="SERVICE">Service Center</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-3">Contact Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" wire:model="editEmail" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input type="text" wire:model="editPhoneNumber" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <textarea wire:model="editAddress" rows="2"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Operational Details -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-3">Operational Details</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Opening Date</label>
                                <input type="date" wire:model="editOpeningDate" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Operating Hours</label>
                                <input type="text" wire:model="editOperatingHours" 
                                    placeholder="e.g., Mon-Fri: 9AM-5PM"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Branch Manager ID</label>
                                <input type="text" wire:model="editBranchManager" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">CIT Provider ID</label>
                                <input type="number" wire:model="editCitProviderId" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Services Offered (JSON format)</label>
                                <textarea wire:model="editServicesOffered" rows="3"
                                    placeholder='e.g., ["Savings", "Loans", "Transfers"]'
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-3">Account Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Vault Account</label>
                                <input type="text" wire:model="editVaultAccount" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Till Account</label>
                                <input type="text" wire:model="editTillAccount" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Petty Cash Account</label>
                                <input type="text" wire:model="editPettyCashAccount" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button wire:click="closeEditModal" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        Cancel
                    </button>
                    <button wire:click="updateBranch" class="px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-700">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal && $selectedBranch)
    <div class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="mt-3 text-center">
                    <h3 class="text-lg font-medium text-gray-900">Delete Branch</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to delete branch "{{ $selectedBranch->name }}"? This action cannot be undone.
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex justify-center space-x-3">
                    <button wire:click="closeDeleteModal" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        Cancel
                    </button>
                    <button wire:click="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Success Message -->
    @if (session()->has('message'))
    <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('message') }}
    </div>
    @endif

    <!-- Custom scrollbar styles -->
    <style nonce="{{ csp_nonce() }}">
        /* Custom red vertical scrollbar for branches table */
        .branches-table-scroll::-webkit-scrollbar {
            width: 2px;
        }
        .branches-table-scroll::-webkit-scrollbar-thumb {
            background: #e3342f;
            border-radius: 2px;
        }
        .branches-table-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        /* For Firefox */
        .branches-table-scroll {
            scrollbar-width: thin;
            scrollbar-color: #e3342f transparent;
        }
    </style>
</div>
