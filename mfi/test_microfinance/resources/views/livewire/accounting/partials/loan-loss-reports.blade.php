<!-- Reports Tab Content -->
<div class="space-y-6">
    <!-- Report Generation -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Generate Reports</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Provision Report -->
            <div class="border rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h4 class="ml-3 text-lg font-medium text-gray-900">Provision Report</h4>
                </div>
                <p class="text-sm text-gray-600 mb-4">Comprehensive report on loan loss provisions, reserves, and adequacy analysis.</p>
                <button wire:click="generateProvisionReport('pdf')" 
                        class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                    Generate PDF Report
                </button>
            </div>

            <!-- Write-off Report -->
            <div class="border rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <h4 class="ml-3 text-lg font-medium text-gray-900">Write-off Report</h4>
                </div>
                <p class="text-sm text-gray-600 mb-4">Detailed report of all written-off loans with authorization and recovery status.</p>
                <button wire:click="exportWriteOffReport" 
                        class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                    Generate Excel Report
                </button>
            </div>

            <!-- Recovery Report -->
            <div class="border rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h4 class="ml-3 text-lg font-medium text-gray-900">Recovery Report</h4>
                </div>
                <p class="text-sm text-gray-600 mb-4">Analysis of loan recoveries including trends, efficiency, and forecasts.</p>
                <button wire:click="generateRecoveryReport" 
                        class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                    Generate PDF Report
                </button>
            </div>

            <!-- Compliance Report -->
            <div class="border rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <h4 class="ml-3 text-lg font-medium text-gray-900">Compliance Report</h4>
                </div>
                <p class="text-sm text-gray-600 mb-4">Regulatory compliance status and audit trail for loan loss management.</p>
                <button wire:click="generateComplianceReport" 
                        class="w-full px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700">
                    Generate PDF Report
                </button>
            </div>

            <!-- Analytics Report -->
            <div class="border rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h4 class="ml-3 text-lg font-medium text-gray-900">Analytics Report</h4>
                </div>
                <p class="text-sm text-gray-600 mb-4">Portfolio quality trends, NPL analysis, and predictive insights.</p>
                <button wire:click="generateAnalyticsReport" 
                        class="w-full px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    Generate PDF Report
                </button>
            </div>

            <!-- Custom Report -->
            <div class="border rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    <h4 class="ml-3 text-lg font-medium text-gray-900">Custom Report</h4>
                </div>
                <p class="text-sm text-gray-600 mb-4">Configure and generate custom reports based on specific criteria.</p>
                <button wire:click="$set('showCustomReportModal', true)" 
                        class="w-full px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                    Configure Report
                </button>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Reports</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Report Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Generated Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Generated By</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Period</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">Provision Report</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ now()->subDays(2)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ auth()->user()->name ?? 'System' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ now()->subMonth()->format('M Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <button class="text-red-600 hover:text-red-800 text-sm font-medium">Download</button>
                            <span class="mx-2 text-gray-400">|</span>
                            <button class="text-gray-600 hover:text-gray-800 text-sm font-medium">View</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">Write-off Report</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ now()->subDays(5)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ auth()->user()->name ?? 'System' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">Q4 2024</td>
                        <td class="px-4 py-3 text-center">
                            <button class="text-red-600 hover:text-red-800 text-sm font-medium">Download</button>
                            <span class="mx-2 text-gray-400">|</span>
                            <button class="text-gray-600 hover:text-gray-800 text-sm font-medium">View</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">Recovery Report</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ now()->subWeek()->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ auth()->user()->name ?? 'System' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">YTD 2025</td>
                        <td class="px-4 py-3 text-center">
                            <button class="text-red-600 hover:text-red-800 text-sm font-medium">Download</button>
                            <span class="mx-2 text-gray-400">|</span>
                            <button class="text-gray-600 hover:text-gray-800 text-sm font-medium">View</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Export Options -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Export</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <button wire:click="exportHistoryData('provisions')" 
                    class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                Export Provision History (CSV)
            </button>
            <button wire:click="exportHistoryData('writeoffs')" 
                    class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                Export Write-off History (CSV)
            </button>
            <button wire:click="exportHistoryData('all')" 
                    class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                Export All Data (CSV)
            </button>
        </div>
    </div>
</div>

<!-- Custom Report Modal -->
@if($showCustomReportModal ?? false)
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Configure Custom Report</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Report Period</label>
                    <select wire:model="customReportPeriod" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                        <option value="month">Current Month</option>
                        <option value="quarter">Current Quarter</option>
                        <option value="year">Current Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                
                @if($customReportPeriod === 'custom')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" wire:model="customReportStartDate" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" wire:model="customReportEndDate" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                        </div>
                    </div>
                @endif
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Include Sections</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="customReportSections.provisions" class="mr-2">
                            <span class="text-sm text-gray-700">Provisions</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="customReportSections.writeoffs" class="mr-2">
                            <span class="text-sm text-gray-700">Write-offs</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="customReportSections.recoveries" class="mr-2">
                            <span class="text-sm text-gray-700">Recoveries</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="customReportSections.analytics" class="mr-2">
                            <span class="text-sm text-gray-700">Analytics</span>
                        </label>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Report Format</label>
                    <select wire:model="customReportFormat" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                        <option value="csv">CSV</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button wire:click="$set('showCustomReportModal', false)" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    Cancel
                </button>
                <button wire:click="generateCustomReport" 
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                    Generate Report
                </button>
            </div>
        </div>
    </div>
@endif