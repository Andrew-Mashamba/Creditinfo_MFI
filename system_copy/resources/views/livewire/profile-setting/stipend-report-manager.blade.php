<div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mt-4">
    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
        <svg class="w-6 h-6 mr-2 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Stipend Report
    </h2>
    <div class="mb-4 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Leader</label>
            <select wire:model="leader_id" class="w-48 px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option value="">All</option>
                @foreach($leaders as $leader)
                    <option value="{{ $leader->id }}">{{ $leader->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Meeting</label>
            <select wire:model="meeting_id" class="w-48 px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option value="">All</option>
                @foreach($meetings as $meeting)
                    <option value="{{ $meeting->id }}">{{ $meeting->title }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Paid Status</label>
            <select wire:model="paid_status" class="w-32 px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option value="">All</option>
                <option value="1">Paid</option>
                <option value="0">Unpaid</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
            <input type="date" wire:model="date_from" class="w-40 px-3 py-2 border border-gray-300 rounded-md text-sm" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
            <input type="date" wire:model="date_to" class="w-40 px-3 py-2 border border-gray-300 rounded-md text-sm" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input type="text" wire:model.debounce.300ms="search" placeholder="Search leader..." class="w-48 px-3 py-2 border border-gray-300 rounded-md text-sm" />
        </div>
        <div class="flex flex-col gap-2">
            <button wire:click="openExportModal" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Export</button>
        </div>
    </div>
    <div class="mb-4 flex gap-8">
        <div class="bg-blue-50 rounded-lg p-4 flex-1">
            <div class="text-xs text-blue-700">Total Paid</div>
            <div class="text-2xl font-bold text-blue-900">{{ number_format($totalPaid, 2) }}</div>
            <div class="text-xs text-blue-700">({{ $countPaid }} records)</div>
        </div>
        <div class="bg-yellow-50 rounded-lg p-4 flex-1">
            <div class="text-xs text-yellow-700">Total Unpaid</div>
            <div class="text-2xl font-bold text-yellow-900">{{ number_format($totalUnpaid, 2) }}</div>
            <div class="text-xs text-yellow-700">({{ $countUnpaid }} records)</div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <div class="mb-2 flex items-center gap-2">
            <input type="checkbox" wire:model="selectAll" class="form-checkbox h-4 w-4 text-blue-600">
            <span class="text-sm">Select All</span>
            <button wire:click="markSelectedAsPaid" class="ml-4 px-4 py-2 bg-blue-900 text-white rounded hover:bg-blue-800 disabled:opacity-50" @if(count($selectedRecords) == 0) disabled @endif>Mark Selected as Paid</button>
            <button wire:click="openPaymentModal" class="ml-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50" @if(count($selectedRecords) == 0) disabled @endif>Prepare Payment</button>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 py-3"><input type="checkbox" wire:model="selectAll" class="form-checkbox h-4 w-4 text-blue-600"></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leader</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meeting</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stipend</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($records as $record)
                    <tr>
                        <td class="px-2 py-4"><input type="checkbox" wire:model="selectedRecords" value="{{ $record->id }}" class="form-checkbox h-4 w-4 text-blue-600"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record->leader->full_name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record->meeting->title ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record->stipend_amount ? number_format($record->stipend_amount, 2) : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($record->stipend_paid)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Paid</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Unpaid</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $record->created_at ? $record->created_at->format('M d, Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>No stipend records found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $records->links() }}
        </div>
    </div>
    {{-- Export Modal --}}
    @if($showExportModal)
        <div class="fixed z-20 inset-0 overflow-y-auto flex items-center justify-center bg-black bg-opacity-30">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
                <h3 class="text-lg font-bold mb-4">Export Stipend Report</h3>
                <form wire:submit.prevent="exportStipends">
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
    @if($showPaymentModal)
        <div class="fixed z-30 inset-0 overflow-y-auto flex items-center justify-center bg-black bg-opacity-30">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl">
                <h3 class="text-lg font-bold mb-4">Prepare Stipend Payments</h3>
                <form wire:submit.prevent="processStipendPayments">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <select wire:model="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            @foreach($paymentMethods as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($paymentMethod === 'internal_transfer')
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">SACCO Account Number</label>
                            <input type="text" wire:model="paymentDetails.sacco_account" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Enter SACCO account number" />
                        </div>
                    @endif
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-700 mb-2">Selected Stipends</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leader</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meeting</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stipend</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach(\App\Models\MeetingAttendance::with(['leader', 'meeting'])->whereIn('id', $selectedRecords)->get() as $record)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $record->leader->full_name ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $record->meeting->title ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $record->stipend_amount ? number_format($record->stipend_amount, 2) : '-' }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                @if($record->stipend_paid)
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Paid</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Unpaid</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="closePaymentModal" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Process Payment</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div> 