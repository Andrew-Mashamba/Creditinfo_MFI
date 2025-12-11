<div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mt-4">
    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
        <svg class="w-6 h-6 mr-2 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Attendance for {{ $meeting->title }}
    </h2>
    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('message') }}</div>
    @endif
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form wire:submit.prevent="markAttendance" class="flex flex-col md:flex-row gap-2 items-center">
            <select wire:model.defer="leader_id" class="w-48 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Select Leader</option>
                @foreach($availableLeaders as $leader)
                    <option value="{{ $leader->id }}">{{ $leader->full_name }}</option>
                @endforeach
            </select>
            <select wire:model.defer="status" class="w-40 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="present">Present</option>
                <option value="absent">Absent</option>
                <option value="excused">Excused</option>
            </select>
            <input type="number" wire:model.defer="stipend_amount" placeholder="Stipend (optional)" class="w-32 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" step="0.01" min="0" />
            <input type="text" wire:model.defer="notes" placeholder="Notes (optional)" class="w-64 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            <button type="submit" class="px-4 py-2 bg-blue-900 text-white rounded hover:bg-blue-800">Mark</button>
        </form>
        <input type="text" wire:model.debounce.300ms="search" placeholder="Search attendance..." class="w-64 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stipend</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($attendance as $record)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record->leader->full_name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($record->status == 'present')
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Present</span>
                            @elseif($record->status == 'absent')
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Absent</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Excused</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record->stipend_amount ? number_format($record->stipend_amount, 2) : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($record->stipend_paid)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Paid</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Unpaid</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 max-w-xs truncate" title="{{ $record->notes }}">
                            {{ \Illuminate\Support\Str::limit($record->notes, 40) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button wire:click="toggleStipendPaid({{ $record->id }})" class="text-blue-600 hover:text-blue-900 font-semibold">Toggle Paid</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>No attendance records found for this meeting.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $attendance->links() }}
        </div>
    </div>
</div> 