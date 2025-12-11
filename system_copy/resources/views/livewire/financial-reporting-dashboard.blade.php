{{-- 
    ===============================================================================
    FINANCIAL REPORTING DASHBOARD - COMPREHENSIVE REPORTING SYSTEM
    ===============================================================================
    
    Centralized reporting system accessible from anywhere in the application
    Supports all financial reports with consistent UI/UX and functionality
    
    Features:
    • Unified reporting interface for all report types
    • BOT regulatory reports and general financial reports
    • Quick actions and advanced controls
    • Report history and scheduling
    • Export capabilities (PDF, Excel)
    
    Author: NBC SACCOS Development Team
    Version: 1.0 - Centralized Financial Reporting System
    ===============================================================================
--}}

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-blue-100 rounded-xl">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Financial Reporting System</h1>
                        <p class="text-gray-600">Comprehensive reporting dashboard for all financial reports</p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="text-right">
                    <div class="text-sm text-gray-500">Available Reports</div>
                    <div class="text-2xl font-bold text-blue-600">{{ count($availableReports) }}</div>
                    <div class="text-xs text-gray-400">BOT Compliant</div>
                </div>
            </div>

            <!-- Flash Messages -->
            @if (session()->has('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Global Report Controls -->
            <x-financial-report-controls />
        </div>

        <!-- Report Categories -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Regulatory Reports (BOT) -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-purple-900">BOT Regulatory Reports</h2>
                    <span class="px-3 py-1 bg-purple-100 text-purple-600 rounded-full text-sm font-medium">
                        {{ count($regulatoryReports) }} Reports
                    </span>
                </div>
                
                <div class="space-y-3">
                    @forelse($regulatoryReports as $key => $report)
                        <div class="flex items-center justify-between p-4 border border-purple-100 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <span class="text-purple-600 font-bold text-sm">{{ $report['id'] }}</span>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $report['name'] }}</h3>
                                    <p class="text-sm text-gray-500">{{ $report['description'] }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <button wire:click="quickGenerate('{{ $key }}')" 
                                        class="px-3 py-1 bg-purple-600 text-white rounded text-sm hover:bg-purple-700">
                                    Generate
                                </button>
                                <button wire:click="quickExportPDF('{{ $key }}')" 
                                        class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                                    PDF
                                </button>
                                <button wire:click="selectReport('{{ $key }}')" 
                                        class="px-3 py-1 border border-gray-300 text-gray-700 rounded text-sm hover:bg-gray-50">
                                    Details
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <p>No regulatory reports available</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- General Reports -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-blue-900">General Financial Reports</h2>
                    <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-sm font-medium">
                        {{ count($generalReports) }} Reports
                    </span>
                </div>
                
                <div class="space-y-3">
                    @forelse($generalReports as $key => $report)
                        <div class="flex items-center justify-between p-4 border border-blue-100 rounded-lg hover:bg-blue-50 transition-colors duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <span class="text-blue-600 font-bold text-sm">{{ $report['id'] }}</span>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $report['name'] }}</h3>
                                    <p class="text-sm text-gray-500">{{ $report['description'] }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <button wire:click="quickGenerate('{{ $key }}')" 
                                        class="px-3 py-1 bg-blue-900 text-white rounded text-sm hover:bg-blue-700">
                                    Generate
                                </button>
                                <button wire:click="quickExportPDF('{{ $key }}')" 
                                        class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                                    PDF
                                </button>
                                <button wire:click="selectReport('{{ $key }}')" 
                                        class="px-3 py-1 border border-gray-300 text-gray-700 rounded text-sm hover:bg-gray-50">
                                    Details
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <p>No general reports available</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Report History -->
        @if(count($reportHistory) > 0)
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Report History</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportHistory as $history)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $history['report_type'] ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $history['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($history['status'] ?? 'pending') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($history['created_at'])->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900">View</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- Report Detail Modal -->
    @if($showReportModal && $selectedReportType)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $availableReports[$selectedReportType]['name'] ?? 'Report Details' }}
                    </h3>
                    <button wire:click="closeReportModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Report Controls in Modal -->
                <x-financial-report-controls />
                
                <!-- Report Actions in Modal -->
                <x-financial-report-actions />
                
                <!-- Report Data Display -->
                @if(!empty($reportData))
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-md font-medium text-gray-900 mb-3">Report Summary</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        @if(isset($reportData['totalAssets']))
                            <div>
                                <span class="text-gray-500">Total Assets:</span>
                                <div class="font-medium">{{ $currency }} {{ number_format($reportData['totalAssets'], 2) }}</div>
                            </div>
                        @endif
                        @if(isset($reportData['totalLiabilities']))
                            <div>
                                <span class="text-gray-500">Total Liabilities:</span>
                                <div class="font-medium">{{ $currency }} {{ number_format($reportData['totalLiabilities'], 2) }}</div>
                            </div>
                        @endif
                        @if(isset($reportData['totalEquity']))
                            <div>
                                <span class="text-gray-500">Total Equity:</span>
                                <div class="font-medium">{{ $currency }} {{ number_format($reportData['totalEquity'], 2) }}</div>
                            </div>
                        @endif
                        @if(isset($reportData['netIncome']))
                            <div>
                                <span class="text-gray-500">Net Income:</span>
                                <div class="font-medium">{{ $currency }} {{ number_format($reportData['netIncome'], 2) }}</div>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div> 