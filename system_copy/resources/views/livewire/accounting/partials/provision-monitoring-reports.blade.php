{{-- MONITORING VIEW --}}
@if($viewType === 'monitoring')
    <div class="space-y-6">
        <!-- Pending Provision Cycles -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Provision Cycle Monitoring</h3>
                <p class="text-sm text-gray-600 mt-1">Track provision cycles awaiting manual adjustment</p>
            </div>
            
            <!-- Current Pending Cycle -->
            @php
                $pendingCycle = $this->getCurrentCycleStatus();
            @endphp
            
            @if($pendingCycle && $pendingCycle->status == 'COMPARED')
                <div class="p-6 bg-yellow-50 border-b border-yellow-200">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-yellow-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="ml-4 flex-1">
                            <h4 class="text-lg font-medium text-yellow-900">Provision Adjustment Required</h4>
                            <p class="text-sm text-yellow-700 mt-1">Cycle {{ $pendingCycle->cycle_id }} - Calculated on {{ \Carbon\Carbon::parse($pendingCycle->calculated_at)->format('M d, Y H:i') }}</p>
                            
                            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-xs text-yellow-600">Portfolio Value</p>
                                    <p class="text-lg font-semibold text-yellow-900">{{ number_format($pendingCycle->portfolio_value, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-yellow-600">Required Reserve</p>
                                    <p class="text-lg font-semibold text-yellow-900">{{ number_format($pendingCycle->required_reserve, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-yellow-600">Current Reserve</p>
                                    <p class="text-lg font-semibold text-yellow-900">{{ number_format($pendingCycle->current_reserve, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-yellow-600">Adjustment Needed</p>
                                    <p class="text-lg font-semibold {{ $pendingCycle->provision_gap > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $pendingCycle->provision_gap > 0 ? '+' : '' }}{{ number_format($pendingCycle->provision_gap, 2) }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="mt-4 flex space-x-3">
                                <button wire:click="executeProvisionAdjustment({{ $pendingCycle->id }})" 
                                        class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                    Approve & Execute Adjustment
                                </button>
                                <button wire:click="viewCycleDetails({{ $pendingCycle->id }})" 
                                        class="px-4 py-2 border border-yellow-600 text-yellow-600 rounded-md hover:bg-yellow-50">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Coverage Ratio Monitoring -->
            <div class="p-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Coverage Ratio Monitoring</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Coverage Ratio Gauge -->
                    <div class="text-center">
                        <div class="relative inline-flex items-center justify-center">
                            <svg class="transform -rotate-90 w-32 h-32">
                                <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none" class="text-gray-200"/>
                                <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none" 
                                        stroke-dasharray="{{ 351.86 * ($stats['coverage_ratio'] ?? 0) / 100 }} 351.86"
                                        class="{{ ($stats['coverage_ratio'] ?? 0) >= 100 ? 'text-green-500' : (($stats['coverage_ratio'] ?? 0) >= 80 ? 'text-yellow-500' : 'text-red-500') }}"/>
                            </svg>
                            <span class="absolute text-2xl font-bold">{{ number_format($stats['coverage_ratio'] ?? 0, 1) }}%</span>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">Coverage Ratio</p>
                        <p class="text-xs text-gray-500">Target: ≥100%</p>
                    </div>
                    
                    <!-- NPL Ratio Gauge -->
                    <div class="text-center">
                        <div class="relative inline-flex items-center justify-center">
                            <svg class="transform -rotate-90 w-32 h-32">
                                <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none" class="text-gray-200"/>
                                <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none" 
                                        stroke-dasharray="{{ min(351.86 * ($stats['npl_ratio'] ?? 0) / 100, 351.86) }} 351.86"
                                        class="{{ ($stats['npl_ratio'] ?? 0) <= 5 ? 'text-green-500' : (($stats['npl_ratio'] ?? 0) <= 10 ? 'text-yellow-500' : 'text-red-500') }}"/>
                            </svg>
                            <span class="absolute text-2xl font-bold">{{ number_format($stats['npl_ratio'] ?? 0, 1) }}%</span>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">NPL Ratio</p>
                        <p class="text-xs text-gray-500">Target: ≤5%</p>
                    </div>
                    
                    <!-- Provision Coverage Gauge -->
                    <div class="text-center">
                        <div class="relative inline-flex items-center justify-center">
                            <svg class="transform -rotate-90 w-32 h-32">
                                <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none" class="text-gray-200"/>
                                <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none" 
                                        stroke-dasharray="{{ min(351.86 * ($stats['provision_coverage'] ?? 0) / 100, 351.86) }} 351.86"
                                        class="{{ ($stats['provision_coverage'] ?? 0) >= 100 ? 'text-green-500' : (($stats['provision_coverage'] ?? 0) >= 75 ? 'text-yellow-500' : 'text-red-500') }}"/>
                            </svg>
                            <span class="absolute text-2xl font-bold">{{ number_format($stats['provision_coverage'] ?? 0, 1) }}%</span>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">Provision Coverage</p>
                        <p class="text-xs text-gray-500">Reserve/Required</p>
                    </div>
                </div>
            </div>
            
            <!-- Key Monitoring Metrics -->
            <div class="px-6 pb-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Key Risk Indicators</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-600">Portfolio at Risk (>30 days)</p>
                        <p class="text-lg font-semibold {{ $this->calculatePortfolioAtRisk() > 10 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ number_format($this->calculatePortfolioAtRisk(), 2) }}%
                        </p>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-600">Write-off Ratio (YTD)</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($this->calculateWriteOffRatio(), 2) }}%</p>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-600">Recovery Ratio</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($this->calculateRecoveryRatio(), 2) }}%</p>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-600">ECL Stage</p>
                        <p class="text-lg font-semibold text-blue-600">Stage {{ $this->getECLStage() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- REPORTS VIEW --}}
@elseif($viewType === 'reports')
    <div class="space-y-6">
        <!-- Report Generation -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Board & Regulatory Reports</h3>
                <p class="text-sm text-gray-600 mt-1">Generate and download provision reports for board and regulatory submission</p>
            </div>
            
            <!-- Report Types -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Board Report -->
                <div class="border rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="ml-4 flex-1">
                            <h4 class="text-md font-medium text-gray-900">Board Report</h4>
                            <p class="text-sm text-gray-600 mt-1">Comprehensive provision analysis for board review</p>
                            
                            <div class="mt-3 space-y-2">
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Portfolio aging analysis
                                </div>
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    ECL calculation details
                                </div>
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Coverage ratio trends
                                </div>
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Risk assessment summary
                                </div>
                            </div>
                            
                            <button wire:click="generateBoardReport" 
                                    class="mt-4 px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Generate Board Report
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Regulatory Report -->
                <div class="border rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v7m3-2h6l2 2v5a2 2 0 01-2 2H7a2 2 0 01-2-2v-5l2-2z"/>
                        </svg>
                        <div class="ml-4 flex-1">
                            <h4 class="text-md font-medium text-gray-900">Regulatory Report</h4>
                            <p class="text-sm text-gray-600 mt-1">Standard format for regulatory submission</p>
                            
                            <div class="mt-3 space-y-2">
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    IFRS 9 compliance details
                                </div>
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Loan classification summary
                                </div>
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Provision adequacy analysis
                                </div>
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Audit trail documentation
                                </div>
                            </div>
                            
                            <button wire:click="generateRegulatoryReport" 
                                    class="mt-4 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                Generate Regulatory Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Reports -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Reports</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Report Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Generated Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Generated By</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($this->getProvisionCycles(5) as $cycle)
                            @if($cycle->board_report_generated || $cycle->regulatory_report_submitted)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        @if($cycle->board_report_generated)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Board Report
                                            </span>
                                        @endif
                                        @if($cycle->regulatory_report_submitted)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Regulatory
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $cycle->year }} - {{ $cycle->frequency == 'MONTHLY' ? 'Month ' . $cycle->period : 'Q' . $cycle->period }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($cycle->reported_at)->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        System Generated
                                    </td>
                                    <td class="px-6 py-4 text-sm text-center">
                                        <button wire:click="downloadReport('{{ $cycle->cycle_id }}')" 
                                                class="text-blue-600 hover:text-blue-900">
                                            Download
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

{{-- ENHANCED HISTORY VIEW --}}
@elseif($viewMode === 'history')
    <div class="space-y-6">
        <!-- Provision Cycles History -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Provision Cycle History</h3>
                <p class="text-sm text-gray-600 mt-1">Complete audit trail of all provision cycles</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cycle ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Frequency</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Portfolio Value</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Required Reserve</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Adjustment</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($this->getProvisionCycles() as $cycle)
                            <tr class="{{ $cycle->status == 'COMPARED' ? 'bg-yellow-50' : '' }}">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $cycle->cycle_id }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $cycle->frequency }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $cycle->year }}-{{ str_pad($cycle->period, 2, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                    {{ number_format($cycle->portfolio_value, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                    {{ number_format($cycle->required_reserve, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-right">
                                    @if($cycle->adjustment_type == 'PROVISION')
                                        <span class="text-red-600">+{{ number_format($cycle->adjustment_amount, 2) }}</span>
                                    @elseif($cycle->adjustment_type == 'REVERSAL')
                                        <span class="text-green-600">-{{ number_format($cycle->adjustment_amount, 2) }}</span>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $statusColors = [
                                            'INITIATED' => 'gray',
                                            'CALCULATED' => 'blue',
                                            'COMPARED' => 'yellow',
                                            'ADJUSTED' => 'indigo',
                                            'MONITORED' => 'purple',
                                            'REPORTED' => 'green',
                                            'COMPLETED' => 'green',
                                            'FAILED' => 'red'
                                        ];
                                        $color = $statusColors[$cycle->status] ?? 'gray';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                        {{ $cycle->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <button wire:click="viewCycleDetails({{ $cycle->id }})" 
                                            class="text-blue-600 hover:text-blue-900">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif