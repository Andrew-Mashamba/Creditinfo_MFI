<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Portal User Management</h2>
                <p class="text-gray-600">Manage member portal access, credentials, and permissions</p>
            </div>
            <div class="flex space-x-3">
                <button wire:click="bulkEnablePortalAccess" 
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-900 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    {{ empty($selectedMembers) ? 'disabled' : '' }}>
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Bulk Enable Portal Access
                </button>
                <button wire:click="loadStatistics" 
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Members</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_members']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Portal Enabled</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['portal_enabled']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['active_users']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Activation</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['pending_activations']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Members</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input wire:model.debounce.300ms="search" id="search" type="text"
                            class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 sm:text-sm border-gray-300 rounded-md"
                            placeholder="Search by member number, name, email, or phone...">
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="filterStatus" class="block text-sm font-medium text-gray-700 mb-2">Filter by Status</label>
                    <select wire:model="filterStatus" id="filterStatus"
                        class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        <option value="all">All Members</option>
                        <option value="portal_enabled">Portal Enabled</option>
                        <option value="portal_disabled">Portal Disabled</option>
                        <option value="recently_registered">Recently Registered</option>
                        <option value="never_logged_in">Never Logged In</option>
                    </select>
                </div>
            </div>

            <!-- Selection Actions -->
            @if(!empty($selectedMembers))
            <div class="mt-4 flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                <div class="flex items-center">
                    <span class="text-sm font-medium text-blue-900">
                        {{ count($selectedMembers) }} member(s) selected
                    </span>
                </div>
                <div class="flex space-x-2">
                    <button wire:click="deselectAllMembers" 
                        class="text-sm text-blue-600 hover:text-blue-800">Clear Selection</button>
                    <button wire:click="bulkEnablePortalAccess" 
                        class="inline-flex items-center px-3 py-1 text-sm font-medium text-white bg-blue-900 border border-transparent rounded hover:bg-blue-700">
                        Enable Portal Access
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Members Table -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Members List</h3>
                <div class="flex items-center space-x-2">
                    <button wire:click="selectAllMembers" 
                        class="text-sm text-blue-600 hover:text-blue-800">Select All</button>
                    <span class="text-gray-300">|</span>
                    <select wire:model="perPage" class="text-sm border-gray-300 rounded">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" wire:model="selectAll" class="rounded border-gray-300">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Portal Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($members as $member)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" wire:click="toggleMemberSelection({{ $member->id }})"
                                {{ in_array($member->id, $selectedMembers) ? 'checked' : '' }}
                                class="rounded border-gray-300">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-600 font-medium text-sm">
                                            {{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $member->getFullNameAttribute() }}</div>
                                    <div class="text-sm text-gray-500">Member #{{ $member->client_number }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $member->email }}</div>
                            <div class="text-sm text-gray-500">{{ $member->mobile_phone_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($member->portal_user_id)
                                @if($member->portal_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Active
                                    </span>
                                @elseif($member->portal_locked)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-red-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Locked
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Inactive
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Disabled
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $member->portal_last_login ? \Carbon\Carbon::parse($member->portal_last_login)->diffForHumans() : 'Never' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                @if(!$member->portal_user_id)
                                    <button wire:click="enablePortalAccess({{ $member->id }})"
                                        class="text-green-600 hover:text-green-900 font-medium">
                                        Enable Portal
                                    </button>
                                @else
                                    @if(!$member->portal_active)
                                        <button wire:click="enablePortalAccess({{ $member->id }})"
                                            class="text-green-600 hover:text-green-900 font-medium">
                                            Re-Enable Portal
                                        </button>
                                    @else
                                        @if($member->portal_locked)
                                            <button wire:click="unlockPortalAccess({{ $member->id }})"
                                                class="text-green-600 hover:text-green-900 font-medium">
                                                Unlock Account
                                            </button>
                                        @endif
                                        <button wire:click="resetMemberPassword({{ $member->id }})"
                                            class="text-blue-600 hover:text-blue-900 font-medium">
                                            Reset Password
                                        </button>
                                        <button wire:click="disablePortalAccess({{ $member->id }})"
                                            class="text-red-600 hover:text-red-900 font-medium">
                                            Disable
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No members found</h3>
                            <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($members->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $members->links() }}
        </div>
        @endif
    </div>

    <!-- Enable Portal Access Modal -->
    @if($showEnablePortalModal && $selectedMember)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Enable Portal Access</h3>
                    <button wire:click="closeModals" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        Enable portal access for <strong>{{ $selectedMember->getFullNameAttribute() }}</strong> 
                        (Member #{{ $selectedMember->client_number }})
                    </p>
                </div>

                <!-- Password Options -->
                <div class="space-y-4">
                    <div>
                        <label class="flex items-center">
                            <input wire:model="portalData.auto_generate_password" type="checkbox" class="rounded border-gray-300">
                            <span class="ml-2 text-sm font-medium text-gray-700">Auto-generate secure password</span>
                        </label>
                    </div>

                    @if(!$portalData['auto_generate_password'])
                    <div>
                        <label for="custom_password" class="block text-sm font-medium text-gray-700 mb-2">Custom Password</label>
                        <input wire:model.defer="portalData.custom_password" type="password" id="custom_password"
                            class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            placeholder="Enter custom password (min 8 characters)">
                        @error('portalData.custom_password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <!-- Communication Options -->
                    <div class="border-t pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Send Credentials Via:</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input wire:model="portalData.send_credentials_email" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">Email ({{ $selectedMember->email }})</span>
                            </label>
                            <label class="flex items-center">
                                <input wire:model="portalData.send_credentials_sms" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">SMS ({{ $selectedMember->mobile_phone_number }})</span>
                            </label>
                        </div>
                    </div>

                    <!-- Portal Permissions -->
                    <div class="border-t pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Portal Permissions:</h4>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <label class="flex items-center">
                                <input wire:model="portalData.portal_permissions.view_accounts" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-gray-700">View Accounts</span>
                            </label>
                            <label class="flex items-center">
                                <input wire:model="portalData.portal_permissions.view_transactions" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-gray-700">View Transactions</span>
                            </label>
                            <label class="flex items-center">
                                <input wire:model="portalData.portal_permissions.view_loans" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-gray-700">View Loans</span>
                            </label>
                            <label class="flex items-center">
                                <input wire:model="portalData.portal_permissions.view_shares" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-gray-700">View Shares</span>
                            </label>
                            <label class="flex items-center">
                                <input wire:model="portalData.portal_permissions.download_statements" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-gray-700">Download Statements</span>
                            </label>
                            <label class="flex items-center">
                                <input wire:model="portalData.portal_permissions.update_profile" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-gray-700">Update Profile</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end space-x-3 mt-6">
                    <button wire:click="closeModals" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="processPortalAccess" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-900 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="processPortalAccess">Enable Portal Access</span>
                        <span wire:loading wire:target="processPortalAccess">Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Credentials Display Modal -->
    @if($showCredentialsModal && $generatedCredentials)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Portal Access Credentials</h3>
                    <button wire:click="closeModals" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Portal Access Enabled Successfully!</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p>The credentials have been generated and sent to the member.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Member Number</label>
                            <div class="mt-1 p-2 bg-gray-50 border rounded-md">
                                <code class="text-sm">{{ $generatedCredentials['member_number'] }}</code>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <div class="mt-1 p-2 bg-gray-50 border rounded-md">
                                <code class="text-sm">{{ $generatedCredentials['email'] }}</code>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="mt-1 p-2 bg-yellow-50 border border-yellow-200 rounded-md">
                            <code class="text-sm font-mono">{{ $generatedCredentials['password'] }}</code>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Please securely share this password with the member.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Portal URL</label>
                        <div class="mt-1 p-2 bg-gray-50 border rounded-md">
                            <a href="{{ $generatedCredentials['portal_url'] }}" target="_blank" 
                                class="text-sm text-blue-600 hover:text-blue-800">
                                {{ $generatedCredentials['portal_url'] }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end space-x-3 mt-6">
                    <button wire:click="closeModals" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-900 border border-transparent rounded-md hover:bg-blue-700">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Bulk Enable Modal -->
    @if($showBulkEnableModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Bulk Enable Portal Access</h3>
                    <button wire:click="closeModals" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        You are about to enable portal access for <strong>{{ count($selectedMembers) }}</strong> selected member(s).
                        Secure passwords will be auto-generated and credentials will be sent via email.
                    </p>
                </div>

                <!-- Bulk Options -->
                <div class="space-y-4">
                    <div>
                        <label class="flex items-center">
                            <input wire:model="portalData.send_credentials_email" type="checkbox" class="rounded border-gray-300">
                            <span class="ml-2 text-sm font-medium text-gray-700">Send credentials via email</span>
                        </label>
                    </div>

                    <!-- Bulk Portal Permissions -->
                    <div class="border-t pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Default Portal Permissions:</h4>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <label class="flex items-center">
                                <input wire:model="portalData.portal_permissions.view_accounts" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-gray-700">View Accounts</span>
                            </label>
                            <label class="flex items-center">
                                <input wire:model="portalData.portal_permissions.view_transactions" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-gray-700">View Transactions</span>
                            </label>
                            <label class="flex items-center">
                                <input wire:model="portalData.portal_permissions.view_loans" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-gray-700">View Loans</span>
                            </label>
                            <label class="flex items-center">
                                <input wire:model="portalData.portal_permissions.view_shares" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-gray-700">View Shares</span>
                            </label>
                            <label class="flex items-center">
                                <input wire:model="portalData.portal_permissions.download_statements" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-gray-700">Download Statements</span>
                            </label>
                            <label class="flex items-center">
                                <input wire:model="portalData.portal_permissions.update_profile" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-gray-700">Update Profile</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end space-x-3 mt-6">
                    <button wire:click="closeModals" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="processBulkPortalAccess" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-900 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="processBulkPortalAccess">Enable Portal Access</span>
                        <span wire:loading wire:target="processBulkPortalAccess">Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- JavaScript for handling UI interactions -->
<script nonce="{{ csp_nonce() }}">
    // Listen for Livewire events
    window.addEventListener('portal-access-enabled', event => {
        showNotification('success', event.detail.message);
    });

    window.addEventListener('portal-access-disabled', event => {
        showNotification('success', event.detail.message);
    });

    window.addEventListener('password-reset', event => {
        showNotification('success', event.detail.message);
    });

    window.addEventListener('bulk-process-complete', event => {
        showNotification('info', event.detail.message);
    });

    window.addEventListener('error', event => {
        showNotification('error', event.detail.message);
    });

    // Simple notification function
    function showNotification(type, message) {
        const notification = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : 
                       type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        
        notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 5000);
    }
</script>
