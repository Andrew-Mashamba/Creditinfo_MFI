<div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mt-4">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-900 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Committees
        </h2>
        <div class="flex gap-2">
            <button wire:click="$toggle('showFilters')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">Filters</button>
            <button wire:click="openExportModal" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Export</button>
            <button wire:click="$set('editMode', false); $set('committee_id', null); $set('name', ''); $set('type', 'committee'); $set('description', '');" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition-colors">Add Committee</button>
        </div>
    </div>
    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('message') }}</div>
    @endif
    @if($showFilters)
        <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200 flex flex-col md:flex-row md:items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select wire:model="filterType" class="w-48 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All</option>
                    <option value="committee">Committee</option>
                    <option value="subcommittee">Subcommittee</option>
                    <option value="board">Board</option>
                    <option value="management">Management</option>
                    <option value="chairman">Chairman</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Created From</label>
                <input type="date" wire:model="filterCreatedFrom" class="w-40 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Created To</label>
                <input type="date" wire:model="filterCreatedTo" class="w-40 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
            <div class="flex gap-2 mt-4 md:mt-0">
                <button wire:click="applyFilters" class="px-4 py-2 bg-blue-900 text-white rounded hover:bg-blue-800">Apply Filters</button>
                <button wire:click="resetFilters" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Reset</button>
            </div>
        </div>
    @endif
    <div class="flex items-center justify-between mb-4">
        <input type="text" wire:model.debounce.300ms="search" placeholder="Search by name or type..." class="w-64 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        <div>
            <label class="text-sm text-gray-500 mr-2">Per page:</label>
            <select wire:model="perPage" class="border border-gray-300 rounded-md text-sm px-2 py-1">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($committees as $committee)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $committee->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">{{ $committee->type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 max-w-xs truncate" title="{{ $committee->description }}">
                            {{ \Illuminate\Support\Str::limit($committee->description, 40) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button wire:click="editCommittee({{ $committee->id }})" class="text-blue-600 hover:text-blue-900 font-semibold mr-2">Edit</button>
                            <button wire:click="deleteCommittee({{ $committee->id }})" class="text-red-600 hover:text-red-900 font-semibold">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>No committees found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $committees->links() }}
        </div>
    </div>

    {{-- Modal for Add/Edit Committee --}}
    <div @if(!$editMode && !$committee_id && !$name && !$type && !$description) style="display:none;" @endif class="fixed z-10 inset-0 overflow-y-auto flex items-center justify-center bg-black bg-opacity-30">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">{{ $editMode ? 'Edit Committee' : 'Add Committee' }}</h3>
            <form wire:submit.prevent="{{ $editMode ? 'updateCommittee' : 'createCommittee' }}">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" wire:model.defer="name" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('name') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select wire:model.defer="type" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="committee">Committee</option>
                        <option value="subcommittee">Subcommittee</option>
                        <option value="board">Board</option>
                        <option value="management">Management</option>
                        <option value="chairman">Chairman</option>
                    </select>
                    @error('type') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model.defer="description" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    @error('description') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department (optional)</label>
                    <select wire:model.defer="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">None</option>
                        @foreach(\App\Models\departmentsList::orderBy('department_name')->get() as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="button" wire:click="resetForm" class="mr-2 px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-900 text-white rounded hover:bg-blue-800">{{ $editMode ? 'Update' : 'Create' }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Export Modal --}}
    @if($showExportModal)
        <div class="fixed z-20 inset-0 overflow-y-auto flex items-center justify-center bg-black bg-opacity-30">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
                <h3 class="text-lg font-bold mb-4">Export Committees</h3>
                <form wire:submit.prevent="exportCommittees">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Fields</label>
                        <div class="flex flex-col gap-2">
                            @foreach($exportFields as $field)
                                <label class="inline-flex items-center">
                                    <input type="checkbox" wire:model="selectedExportFields" value="{{ $field }}" class="form-checkbox h-4 w-4 text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700">{{ ucfirst(str_replace('_', ' ', $field)) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Format</label>
                        <select wire:model="exportFormat" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="csv">CSV</option>
                            <option value="xlsx">Excel</option>
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" wire:click="closeExportModal" class="mr-2 px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Export</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div> 