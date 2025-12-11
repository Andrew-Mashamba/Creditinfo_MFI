<div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mt-4">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-900 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Meetings for {{ $committee->name }}
        </h2>
        <button wire:click="resetForm" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition-colors">Add Meeting</button>
    </div>
    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('message') }}</div>
    @endif
    <div class="flex items-center justify-between mb-4">
        <input type="text" wire:model.debounce.300ms="search" placeholder="Search by title, agenda, or location..." class="w-64 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agenda</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($meetings as $meeting)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $meeting->title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 max-w-xs truncate" title="{{ $meeting->agenda }}">
                            {{ \Illuminate\Support\Str::limit($meeting->agenda, 40) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $meeting->meeting_date ? \Carbon\Carbon::parse($meeting->meeting_date)->format('M d, Y H:i') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $meeting->location }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 max-w-xs truncate" title="{{ $meeting->notes }}">
                            {{ \Illuminate\Support\Str::limit($meeting->notes, 40) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button wire:click="editMeeting({{ $meeting->id }})" class="text-blue-600 hover:text-blue-900 font-semibold mr-2">Edit</button>
                            <button wire:click="deleteMeeting({{ $meeting->id }})" class="text-red-600 hover:text-red-900 font-semibold">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>No meetings found for this committee.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $meetings->links() }}
        </div>
    </div>

    {{-- Modal for Add/Edit Meeting --}}
    <div @if(!$editMode && !$meeting_id && !$title && !$agenda && !$meeting_date && !$location && !$notes) style="display:none;" @endif class="fixed z-10 inset-0 overflow-y-auto flex items-center justify-center bg-black bg-opacity-30">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">{{ $editMode ? 'Edit Meeting' : 'Add Meeting' }}</h3>
            <form wire:submit.prevent="{{ $editMode ? 'updateMeeting' : 'createMeeting' }}">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" wire:model.defer="title" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('title') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agenda</label>
                    <textarea wire:model.defer="agenda" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    @error('agenda') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date & Time</label>
                    <input type="datetime-local" wire:model.defer="meeting_date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('meeting_date') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" wire:model.defer="location" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('location') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea wire:model.defer="notes" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    @error('notes') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end">
                    <button type="button" wire:click="resetForm" class="mr-2 px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-900 text-white rounded hover:bg-blue-800">{{ $editMode ? 'Update' : 'Create' }}</button>
                </div>
            </form>
        </div>
    </div>
</div> 