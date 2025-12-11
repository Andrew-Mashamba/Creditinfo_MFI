<div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mt-4">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-900 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            Leadership Table
        </h2>
        <span class="text-sm text-gray-500">All current and past leaders</span>
    </div>
    <div class="flex items-center justify-between mb-4">
        <input type="text" wire:model.debounce.300ms="search" placeholder="Search by name or position..." class="w-64 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Is Signatory</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($leaders as $leader)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($leader->image)
                                <img src="{{ asset($leader->image) }}" alt="photo" class="w-12 h-12 rounded-full object-cover border border-gray-200" />
                            @else
                                <img src="{{ asset('images/avatar.png') }}" alt="photo" class="w-12 h-12 rounded-full object-cover border border-gray-200" />
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $leader->full_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $leader->position }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $leader->startDate ? \Carbon\Carbon::parse($leader->startDate)->format('M d, Y') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $leader->endDate ? \Carbon\Carbon::parse($leader->endDate)->format('M d, Y') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($leader->is_signatory)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">YES</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">NO</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 max-w-xs truncate" title="{{ $leader->leaderDescriptions }}">
                            {{ \Illuminate\Support\Str::limit($leader->leaderDescriptions, 40) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            <div>No leaders found for this institution.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $leaders->links() }}
        </div>
    </div>
</div>
