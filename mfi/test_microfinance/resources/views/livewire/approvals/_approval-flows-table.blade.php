<div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-4">
    <div class="flex flex-wrap gap-2 items-end">
        <div>
            <label for="filterStatus" class="block text-xs font-medium text-gray-700">Status</label>
            <select id="filterStatus" wire:model="filterStatus" class="border-gray-300 rounded-md text-sm py-1 px-2">
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filterRole" class="block text-xs font-medium text-gray-700">Role</label>
            <select id="filterRole" wire:model="filterRole" class="border-gray-300 rounded-md text-sm py-1 px-2">
                <option value="all">All</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="selectedCategory" class="block text-xs font-medium text-gray-700">Process Type</label>
            <select id="selectedCategory" wire:model="selectedCategory" class="border-gray-300 rounded-md text-sm py-1 px-2">
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <button wire:click="exportConfigs" class="px-3 py-1 rounded bg-red-600 text-white text-xs font-semibold hover:bg-red-700">Export</button>
        <button wire:click="bulkActivate" class="px-3 py-1 rounded bg-green-600 text-white text-xs font-semibold hover:bg-green-700 disabled:opacity-50" @if(empty($selectedConfigs)) disabled @endif>Activate</button>
        <button wire:click="bulkDeactivate" class="px-3 py-1 rounded bg-yellow-500 text-white text-xs font-semibold hover:bg-yellow-600 disabled:opacity-50" @if(empty($selectedConfigs)) disabled @endif>Deactivate</button>
        <button wire:click="bulkDelete" class="px-3 py-1 rounded bg-red-600 text-white text-xs font-semibold hover:bg-red-700 disabled:opacity-50" @if(empty($selectedConfigs)) disabled @endif>Delete</button>
        <label for="perPage" class="text-sm text-gray-700 ml-4 mr-2">Show</label>
        <select id="perPage" wire:model="perPage" class="border-gray-300 rounded-md text-sm py-1 px-2">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span class="text-sm text-gray-700 ml-2">entries</span>
    </div>
</div>
<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50 sticky top-0 z-10">
            <tr>
                <th class="px-2 py-3 text-center">
                    <input type="checkbox" wire:model="selectAll" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none" wire:click="sortBy('process_code')">
                    Process Code
                    @if($sortField === 'process_code')
                        <span class="ml-1">@if($sortDirection === 'asc') &uarr; @else &darr; @endif</span>
                    @endif
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none" wire:click="sortBy('process_name')">
                    Process Name
                    @if($sortField === 'process_name')
                        <span class="ml-1">@if($sortDirection === 'asc') &uarr; @else &darr; @endif</span>
                    @endif
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none" wire:click="sortBy('min_amount')">
                    Amount Range
                    @if($sortField === 'min_amount')
                        <span class="ml-1">@if($sortDirection === 'asc') &uarr; @else &darr; @endif</span>
                    @endif
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Checkers</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none" wire:click="sortBy('is_active')">
                    Status
                    @if($sortField === 'is_active')
                        <span class="ml-1">@if($sortDirection === 'asc') &uarr; @else &darr; @endif</span>
                    @endif
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @if($configs->count() === 0)
                <tr>
                    <td colspan="7" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>No approval flows found for your current filters.</span>
                        </div>
                    </td>
                </tr>
            @else
                @foreach($configs as $config)
                    <tr class="hover:bg-gray-50">
                        <td class="px-2 py-4 text-center align-top">
                            <input type="checkbox" wire:model="selectedConfigs" value="{{ $config->id }}" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 align-top">
                            {{ $config->process_code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 align-top">
                            {{ $config->process_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 align-top">
                            @if($config->min_amount || $config->max_amount)
                                {{ number_format($config->min_amount ?? 0, 2) }} - {{ $config->max_amount ? number_format($config->max_amount, 2) : '∞' }}
                            @else
                                No limit
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 align-top">
                            @if($config->requires_first_checker)
                                <div class="mb-1">
                                    <span class="font-medium text-gray-700">First Checker:</span>
                                    @if($config->first_checker_roles)
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($config->first_checker_roles as $roleId)
                                                @php $role = $roles->firstWhere('id', $roleId); @endphp
                                                @if($role)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800" title="First checker role: {{ $role->name }}">
                                                        {{ \Illuminate\Support\Str::limit($role->name, 16) }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">No roles assigned</span>
                                    @endif
                                </div>
                            @endif
                            @if($config->requires_second_checker)
                                <div class="mb-1">
                                    <span class="font-medium text-gray-700">Second Checker:</span>
                                    @if($config->second_checker_roles)
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($config->second_checker_roles as $roleId)
                                                @php $role = $roles->firstWhere('id', $roleId); @endphp
                                                @if($role)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800" title="Second checker role: {{ $role->name }}">
                                                        {{ \Illuminate\Support\Str::limit($role->name, 16) }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">No roles assigned</span>
                                    @endif
                                </div>
                            @endif
                            @if($config->requires_approver)
                                <div>
                                    <span class="font-medium text-gray-700">Approver:</span>
                                    @if($config->approver_roles)
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($config->approver_roles as $roleId)
                                                @php $role = $roles->firstWhere('id', $roleId); @endphp
                                                @if($role)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800" title="Approver role: {{ $role->name }}">
                                                        {{ \Illuminate\Support\Str::limit($role->name, 16) }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">No roles assigned</span>
                                    @endif
                                </div>
                            @endif
                            @if(!$config->requires_first_checker && !$config->requires_second_checker && !$config->requires_approver)
                                <span class="text-gray-400">No approval required</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($config->is_active) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif"
                                title="{{ $config->is_active ? 'This approval flow is active and available for use.' : 'This approval flow is inactive.' }}">
                                {{ $config->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium align-top">
                            <button wire:click="edit({{ $config->id }})" class="inline-block px-2 py-1 text-xs text-red-700 bg-red-100 rounded hover:bg-red-200 mr-1">Edit</button>
                            <button wire:click="toggleStatus({{ $config->id }})" class="inline-block px-2 py-1 text-xs text-yellow-700 bg-yellow-100 rounded hover:bg-yellow-200 mr-1">
                                {{ $config->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button wire:click="delete({{ $config->id }})" class="inline-block px-2 py-1 text-xs text-red-700 bg-red-100 rounded hover:bg-red-200">Delete</button>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
<div class="mt-4 flex justify-between items-center">
    <div class="text-sm text-gray-600">
        Showing {{ $configs->firstItem() }} to {{ $configs->lastItem() }} of {{ $configs->total() }} entries
    </div>
    <div>
        {{ $configs->links() }}
    </div>
</div>
<div wire:loading.flex >
<div class="absolute inset-0 bg-white bg-opacity-60 z-50 flex items-center justify-center">

    <svg class="animate-spin h-8 w-8 text-red-600 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <span class="text-red-700 font-medium">Loading...</span>
    </div>
</div> 