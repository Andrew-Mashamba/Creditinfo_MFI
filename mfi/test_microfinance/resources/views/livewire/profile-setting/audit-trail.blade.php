<div class="space-y-6">
    {{-- Header with Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-red-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Total Logs</p>
                    <p class="text-3xl font-bold mt-2">{{ number_format($totalLogs) }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-green-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Active Users</p>
                    <p class="text-3xl font-bold mt-2">{{ number_format($totalUsers) }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-red-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Errors</p>
                    <p class="text-3xl font-bold mt-2">{{ number_format($totalErrors) }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-yellow-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm font-medium">Warnings</p>
                    <p class="text-3xl font-bold mt-2">{{ number_format($totalWarnings) }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3l-6.928-12c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
            <button wire:click="clearFilters" class="text-sm text-red-600 hover:text-red-700 font-medium">
                Clear All Filters
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" wire:model="dateFrom" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" wire:model="dateTo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Log Level</label>
                <select wire:model="logLevel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                    <option value="">All Levels</option>
                    <option value="INFO">INFO</option>
                    <option value="WARNING">WARNING</option>
                    <option value="ERROR">ERROR</option>
                    <option value="DEBUG">DEBUG</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">User ID</label>
                <input type="text" wire:model="selectedUser" placeholder="Filter by user ID" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" wire:model.debounce.500ms="searchTerm" placeholder="Search logs..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing {{ count($logs) }} of {{ $totalRecords }} records
            </div>
            <div class="flex gap-2">
                <button wire:click="exportLogs('csv')" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </button>
            </div>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Timestamp
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Level
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Action
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Message
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $index => $log)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($log['timestamp'])->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $levelColors = [
                                        'INFO' => 'bg-red-100 text-red-800',
                                        'WARNING' => 'bg-yellow-100 text-yellow-800',
                                        'ERROR' => 'bg-red-100 text-red-800',
                                        'DEBUG' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $colorClass = $levelColors[strtoupper($log['level'])] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClass }}">
                                    {{ strtoupper($log['level']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 font-medium text-xs">
                                            {{ $log['user_id'] ? substr($log['user_id'], 0, 2) : 'SY' }}
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $log['user_name'] ?? 'System' }}</p>
                                        @if($log['user_id'])
                                            <p class="text-xs text-gray-500">ID: {{ $log['user_id'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($log['action'])
                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs font-medium">
                                        {{ $log['action'] }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-md truncate">
                                    {{ $log['message'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button wire:click="showDetails({{ $index }})" class="text-red-600 hover:text-red-900">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-gray-500 text-lg font-medium">No audit logs found</p>
                                    <p class="text-gray-400 text-sm mt-1">Try adjusting your filters or date range</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Log Details Modal --}}
    @if($showingDetails && $selectedLog)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" wire:click="closeDetails">
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden" wire:click.stop>
                <div class="bg-red-600 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-white">Log Details</h3>
                    <button wire:click="closeDetails" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Timestamp</label>
                            <p class="mt-1 text-gray-900">{{ $selectedLog['timestamp'] }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Level</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 bg-{{ $selectedLog['level'] === 'ERROR' ? 'red' : ($selectedLog['level'] === 'WARNING' ? 'yellow' : 'red') }}-100 text-{{ $selectedLog['level'] === 'ERROR' ? 'red' : ($selectedLog['level'] === 'WARNING' ? 'yellow' : 'red') }}-800 rounded text-sm font-medium">
                                    {{ strtoupper($selectedLog['level']) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">User</label>
                            <p class="mt-1 text-gray-900">{{ $selectedLog['user_name'] ?? 'System' }} (ID: {{ $selectedLog['user_id'] ?? 'N/A' }})</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Environment</label>
                            <p class="mt-1 text-gray-900">{{ strtoupper($selectedLog['environment']) }}</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="text-sm font-medium text-gray-500">Message</label>
                        <p class="mt-2 p-4 bg-gray-50 rounded-lg text-gray-900">{{ $selectedLog['message'] }}</p>
                    </div>

                    @if(!empty($selectedLog['context']))
                        <div class="mb-6">
                            <label class="text-sm font-medium text-gray-500">Context</label>
                            <pre class="mt-2 p-4 bg-gray-900 text-green-400 rounded-lg text-xs overflow-x-auto">{{ json_encode($selectedLog['context'], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif

                    <div>
                        <label class="text-sm font-medium text-gray-500">Raw Log</label>
                        <pre class="mt-2 p-4 bg-gray-900 text-gray-300 rounded-lg text-xs overflow-x-auto">{{ $selectedLog['raw'] }}</pre>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <button wire:click="closeDetails" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
