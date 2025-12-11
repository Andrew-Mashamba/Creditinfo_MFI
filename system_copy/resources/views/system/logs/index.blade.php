<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Logs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Filters -->
                <div class="mb-6">
                    <form action="{{ route('system.logs') }}" method="GET" class="flex flex-wrap gap-4">
                        <div>
                            <label for="days" class="block text-sm font-medium text-gray-700">Days</label>
                            <select name="days" id="days" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="1" {{ $days == 1 ? 'selected' : '' }}>Last 24 hours</option>
                                <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
                            </select>
                        </div>

                        <div>
                            <label for="resource" class="block text-sm font-medium text-gray-700">Resource</label>
                            <select name="resource" id="resource" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Resources</option>
                                @foreach($resources as $key => $label)
                                    <option value="{{ $key }}" {{ $resource == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Logs Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resource</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($logs as $log)
                                @foreach($log as $resource => $value)
                                    @if($resource !== 'signature')
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($log['timestamp'])->format('Y-m-d H:i:s') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $resources[$resource] ?? $resource }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $value }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $status = app(\App\Http\Controllers\SystemLogsController::class)->getStatus($resource, $value);
                                                    $statusClass = [
                                                        'Normal' => 'bg-green-100 text-green-800',
                                                        'Warning' => 'bg-yellow-100 text-yellow-800',
                                                        'Critical' => 'bg-red-100 text-red-800',
                                                        'N/A' => 'bg-gray-100 text-gray-800'
                                                    ][$status] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                    {{ $status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No logs found for the specified criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 