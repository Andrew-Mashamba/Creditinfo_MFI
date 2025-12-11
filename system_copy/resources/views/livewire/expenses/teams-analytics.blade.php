<div>
    <div class="p-6 bg-white rounded-lg shadow">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Team Analytics: {{ $team->name }}</h2>
            
            <div class="flex items-center space-x-4">
                <!-- Period Selector -->
                <select wire:model="period" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="365">Last year</option>
                </select>
                
                <!-- Export Buttons -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export
                    </button>
                    
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <a href="{{ route('teams.analytics.export', ['team' => $team, 'format' => 'csv', 'period' => $period]) }}" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export as CSV</a>
                            <a href="{{ route('teams.analytics.export', ['team' => $team, 'format' => 'pdf', 'period' => $period]) }}" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export as PDF</a>
                            <a href="{{ route('teams.analytics.export', ['team' => $team, 'format' => 'json', 'period' => $period]) }}" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export as JSON</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-indigo-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-600">Total Emails</p>
                        <p class="text-2xl font-bold text-indigo-900">{{ number_format($analytics['overview']['total_emails']) }}</p>
                    </div>
                    <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600">Sent Emails</p>
                        <p class="text-2xl font-bold text-green-900">{{ number_format($analytics['overview']['sent_emails']) }}</p>
                    </div>
                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </div>
            </div>
            
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600">Active Members</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $analytics['overview']['active_members'] }}/{{ $analytics['overview']['total_members'] }}</p>
                    </div>
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
            
            <div class="bg-yellow-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-600">Avg Response Time</p>
                        <p class="text-2xl font-bold text-yellow-900">{{ $analytics['response_times']['average'] }}h</p>
                    </div>
                    <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Email Volume Chart -->
        <div class="bg-gray-50 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Email Volume Trend</h3>
            <canvas id="emailVolumeChart" width="400" height="100"></canvas>
        </div>
        
        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Top Senders -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Senders</h3>
                <div class="space-y-3">
                    @foreach(array_slice($analytics['top_senders'], 0, 5) as $sender)
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-sm font-medium text-indigo-600">{{ substr($sender['from_name'] ?? $sender['from_email'], 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $sender['from_name'] ?? 'Unknown' }}</p>
                                <p class="text-sm text-gray-500">{{ $sender['from_email'] }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $sender['count'] }} emails</span>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Folder Distribution -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Folder Distribution</h3>
                <canvas id="folderChart" width="200" height="200"></canvas>
            </div>
        </div>
        
        <!-- Member Stats Table -->
        <div class="bg-gray-50 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Member Activity</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Emails</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($analytics['members'] as $member)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full" src="{{ $member['user']->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($member['user']->name) }}" alt="">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $member['user']->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $member['user']->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $member['role'] === 'owner' ? 'purple' : ($member['role'] === 'admin' ? 'red' : 'green') }}-100 text-{{ $member['role'] === 'owner' ? 'purple' : ($member['role'] === 'admin' ? 'red' : 'green') }}-800">
                                    {{ ucfirst($member['role']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($member['emails_count']) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($member['sent_count']) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($member['received_count']) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($member['comments_count']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Additional Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Approval Metrics -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Approval Metrics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Requests</span>
                        <span class="font-medium">{{ $analytics['approval_metrics']['total_requests'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Approved</span>
                        <span class="font-medium text-green-600">{{ $analytics['approval_metrics']['approved'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Rejected</span>
                        <span class="font-medium text-red-600">{{ $analytics['approval_metrics']['rejected'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Pending</span>
                        <span class="font-medium text-yellow-600">{{ $analytics['approval_metrics']['pending'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Avg Approval Time</span>
                        <span class="font-medium">{{ $analytics['approval_metrics']['avg_approval_time'] }}h</span>
                    </div>
                </div>
            </div>
            
            <!-- DLP Violations -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">DLP Violations</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Violations</span>
                        <span class="font-medium">{{ $analytics['dlp_violations']['total'] }}</span>
                    </div>
                    @foreach($analytics['dlp_violations']['by_action'] as $action => $count)
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ ucfirst($action) }}</span>
                        <span class="font-medium">{{ $count }}</span>
                    </div>
                    @endforeach
                    <div class="flex justify-between">
                        <span class="text-gray-600">Overridden</span>
                        <span class="font-medium">{{ $analytics['dlp_violations']['overridden'] }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Template Usage -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Templates</h3>
                <div class="space-y-3">
                    @foreach(array_slice($analytics['templates_usage'], 0, 5) as $template)
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $template['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $template['category'] }}</p>
                        </div>
                        <span class="text-sm font-medium">{{ $template['usage_count'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', function() {
            // Email Volume Chart
            const volumeCtx = document.getElementById('emailVolumeChart').getContext('2d');
            new Chart(volumeCtx, {
                type: 'line',
                data: {
                    labels: @json($analytics['email_volume']['dates']),
                    datasets: [{
                        label: 'Sent',
                        data: @json($analytics['email_volume']['sent']),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Received',
                        data: @json($analytics['email_volume']['received']),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Folder Distribution Chart
            const folderCtx = document.getElementById('folderChart').getContext('2d');
            const folderData = @json($analytics['folder_distribution']);
            new Chart(folderCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(folderData),
                    datasets: [{
                        data: Object.values(folderData),
                        backgroundColor: [
                            'rgba(99, 102, 241, 0.8)',
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(251, 191, 36, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(156, 163, 175, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</div>