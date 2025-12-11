{{-- Default Journey View (For roles without specific journey or fallback) --}}

@switch($activeTab)
    @case('dashboard')
        <div class="space-y-6">
            {{-- Welcome Section --}}
            <div class="bg-gradient-to-r from-red-500 to-indigo-600 rounded-2xl p-8 text-white shadow-xl">
                <h2 class="text-3xl font-bold mb-2">Welcome to Cash Management System</h2>
                <p class="text-gray-500 text-lg">Your comprehensive solution for managing cash operations</p>
            </div>
            
            {{-- Quick Stats Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-xl">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Cash</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalCash ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-xl">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Today's Volume</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($todayVolume ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-xl">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Users</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $activeUsers ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-3 bg-orange-100 rounded-xl">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Tasks</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $pendingTasks ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Recent Activity --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Activity</h3>
                <div class="space-y-3">
                    @forelse($recentActivities ?? [] as $activity)
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                                <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-8">No recent activity</p>
                    @endforelse
                </div>
            </div>
        </div>
        @break
    
    @case('cash-status')
        <div class="space-y-6">
            {{-- Cash Position Overview --}}
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6 border border-green-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Current Cash Position</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Vault Balance</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($vaultBalance ?? 0) }}</p>
                    </div>
                    
                    <div class="bg-white rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Till Balance</p>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($tillBalance ?? 0) }}</p>
                    </div>
                    
                    <div class="bg-white rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Total Cash</p>
                        <p class="text-2xl font-bold text-purple-600">{{ number_format(($vaultBalance ?? 0) + ($tillBalance ?? 0)) }}</p>
                    </div>
                </div>
            </div>
            
            {{-- Denomination Breakdown --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Denomination Breakdown</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 text-gray-600">Denomination</th>
                                <th class="text-center py-2 text-gray-600">Quantity</th>
                                <th class="text-right py-2 text-gray-600">Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['50000', '20000', '10000', '5000', '2000', '1000'] as $denom)
                                <tr class="border-b border-gray-100">
                                    <td class="py-2 text-gray-900">{{ number_format($denom) }}</td>
                                    <td class="py-2 text-center text-gray-700">{{ $denominations[$denom] ?? 0 }}</td>
                                    <td class="py-2 text-right font-semibold text-gray-900">
                                        {{ number_format(($denominations[$denom] ?? 0) * $denom) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @break
    
    @case('transactions')
        <div class="space-y-6">
            {{-- Transaction Filters --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Transaction History</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <select class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                            <option>Today</option>
                            <option>This Week</option>
                            <option>This Month</option>
                            <option>Custom Range</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                            <option>All Types</option>
                            <option>Deposits</option>
                            <option>Withdrawals</option>
                            <option>Transfers</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                            <option>All Status</option>
                            <option>Completed</option>
                            <option>Pending</option>
                            <option>Failed</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                        <button class="w-full bg-red-600 text-white rounded-lg px-4 py-2 hover:bg-red-700">
                            Apply Filters
                        </button>
                    </div>
                </div>
                
                {{-- Transaction Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 text-gray-600">Date/Time</th>
                                <th class="text-left py-2 text-gray-600">Reference</th>
                                <th class="text-left py-2 text-gray-600">Type</th>
                                <th class="text-right py-2 text-gray-600">Amount</th>
                                <th class="text-center py-2 text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions ?? [] as $transaction)
                                <tr class="border-b border-gray-100">
                                    <td class="py-2 text-gray-900">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="py-2 text-gray-700">{{ $transaction->reference }}</td>
                                    <td class="py-2">
                                        <span class="px-2 py-1 rounded text-xs font-semibold
                                            @if($transaction->type === 'deposit') bg-green-100 text-green-700
                                            @else bg-red-100 text-red-700
                                            @endif">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td class="py-2 text-right font-semibold">{{ number_format($transaction->amount) }}</td>
                                    <td class="py-2 text-center">
                                        <span class="px-2 py-1 rounded text-xs font-semibold
                                            @if($transaction->status === 'completed') bg-green-100 text-green-700
                                            @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-700
                                            @else bg-red-100 text-red-700
                                            @endif">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500">
                                        No transactions found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @break
    
    @case('reports')
        <div class="space-y-6">
            {{-- Report Selection --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Generate Reports</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach([
                        ['name' => 'Daily Cash Summary', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'red'],
                        ['name' => 'Transaction Report', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'color' => 'green'],
                        ['name' => 'Denomination Report', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1', 'color' => 'purple'],
                        ['name' => 'Vault Audit Report', 'icon' => 'M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z', 'color' => 'orange'],
                        ['name' => 'Performance Report', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'color' => 'indigo'],
                        ['name' => 'Compliance Report', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color' => 'red'],
                    ] as $report)
                        <button class="p-6 bg-{{ $report['color'] }}-50 hover:bg-{{ $report['color'] }}-100 rounded-xl border border-{{ $report['color'] }}-200 transition-colors">
                            <svg class="w-8 h-8 text-{{ $report['color'] }}-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $report['icon'] }}"></path>
                            </svg>
                            <p class="text-sm font-semibold text-gray-900">{{ $report['name'] }}</p>
                        </button>
                    @endforeach
                </div>
            </div>
            
            {{-- Recent Reports --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Reports</h3>
                
                <div class="space-y-3">
                    @forelse($recentReports ?? [] as $report)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $report->name }}</p>
                                    <p class="text-sm text-gray-500">Generated {{ $report->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold">
                                Download
                            </button>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-8">No recent reports</p>
                    @endforelse
                </div>
            </div>
        </div>
        @break
    
    @case('help')
        <div class="space-y-6">
            {{-- Help Categories --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">How can we help you?</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([
                        ['title' => 'Getting Started', 'description' => 'Learn the basics of the cash management system'],
                        ['title' => 'Transaction Processing', 'description' => 'How to process deposits and withdrawals'],
                        ['title' => 'Reports & Analytics', 'description' => 'Generate and understand reports'],
                        ['title' => 'Security & Compliance', 'description' => 'Security protocols and compliance requirements'],
                        ['title' => 'Troubleshooting', 'description' => 'Common issues and solutions'],
                        ['title' => 'Contact Support', 'description' => 'Get in touch with our support team'],
                    ] as $helpItem)
                        <div class="p-4 bg-gray-50 hover:bg-gray-100 rounded-lg cursor-pointer transition-colors">
                            <h4 class="font-semibold text-gray-900 mb-1">{{ $helpItem['title'] }}</h4>
                            <p class="text-sm text-gray-600">{{ $helpItem['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- FAQs --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Frequently Asked Questions</h3>
                
                <div class="space-y-4">
                    @foreach([
                        ['q' => 'How do I reset my password?', 'a' => 'Contact your system administrator to reset your password.'],
                        ['q' => 'What are the cash limits?', 'a' => 'Cash limits vary by role and branch. Check with your supervisor.'],
                        ['q' => 'How do I generate reports?', 'a' => 'Navigate to Reports section and select the report type you need.'],
                        ['q' => 'What should I do if my till doesn\'t balance?', 'a' => 'Follow the reconciliation procedures and report to your supervisor.'],
                    ] as $faq)
                        <div class="border-b border-gray-200 pb-4">
                            <h4 class="font-semibold text-gray-900 mb-2">{{ $faq['q'] }}</h4>
                            <p class="text-sm text-gray-600">{{ $faq['a'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @break
    
    @default
        <div class="text-center py-12">
            <svg class="w-20 h-20 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">{{ ucfirst(str_replace('-', ' ', $activeTab)) }}</h3>
            <p class="text-gray-500">This section is under development</p>
        </div>
@endswitch