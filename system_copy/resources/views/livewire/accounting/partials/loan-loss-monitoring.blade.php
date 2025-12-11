<!-- Monitoring Tab Content -->
<div class="space-y-6">
    @php
        $alerts = $this->getMonitoringAlerts();
        $complianceStatus = $this->getComplianceStatus();
    @endphp

    <!-- System Alerts -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">System Alerts & Notifications</h3>
        
        @if(count($alerts) > 0)
            <div class="space-y-3">
                @foreach($alerts as $alert)
                    <div class="border-l-4 {{ $alert['type'] === 'danger' ? 'border-red-500 bg-red-50' : ($alert['type'] === 'warning' ? 'border-yellow-500 bg-yellow-50' : 'border-blue-500 bg-blue-50') }} p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                @if($alert['type'] === 'danger')
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($alert['type'] === 'warning')
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium {{ $alert['type'] === 'danger' ? 'text-red-800' : ($alert['type'] === 'warning' ? 'text-yellow-800' : 'text-blue-800') }}">
                                    {{ $alert['title'] }}
                                </h3>
                                <div class="mt-2 text-sm {{ $alert['type'] === 'danger' ? 'text-red-700' : ($alert['type'] === 'warning' ? 'text-yellow-700' : 'text-blue-700') }}">
                                    <p>{{ $alert['message'] }}</p>
                                </div>
                                <div class="mt-4">
                                    <div class="-mx-2 -my-1.5 flex">
                                        <button type="button" class="px-2 py-1.5 rounded-md text-sm font-medium {{ $alert['type'] === 'danger' ? 'text-red-800 hover:bg-red-100' : ($alert['type'] === 'warning' ? 'text-yellow-800 hover:bg-yellow-100' : 'text-blue-800 hover:bg-blue-100') }}">
                                            {{ $alert['action'] }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="mt-2 text-sm text-gray-600">No alerts at this time. System is operating normally.</p>
            </div>
        @endif
    </div>

    <!-- Compliance Dashboard -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Regulatory Compliance Status</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Requirement</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Standard</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Current Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Compliance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($complianceStatus as $requirement)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $requirement['requirement'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $requirement['standard'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $requirement['current'] }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($requirement['status'] === 'compliant')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Compliant
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Non-Compliant
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Key Performance Indicators</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Provision Utilization -->
            <div class="border rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Provision Utilization Rate</h4>
                <div class="relative pt-1">
                    @php
                        $writeOffStats = $this->getRecoveryStatistics();
                        $totalWrittenOff = $writeOffStats->total_written_off_amount ?? 0;
                        $utilizationRate = $currentReserveBalance > 0 ? min(($totalWrittenOff / $currentReserveBalance) * 100, 100) : 0;
                    @endphp
                    <div class="flex mb-2 items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-blue-600 bg-blue-200">
                                {{ number_format($utilizationRate, 1) }}%
                            </span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                        <div style="width:{{ $utilizationRate }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500"></div>
                    </div>
                </div>
            </div>

            <!-- Write-off Efficiency -->
            <div class="border rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Write-off Processing Time</h4>
                <div class="text-center">
                    <p class="text-3xl font-bold text-gray-900">45</p>
                    <p class="text-sm text-gray-600">Average days</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 30 days</p>
                </div>
            </div>

            <!-- Recovery Efficiency -->
            <div class="border rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Recovery Efficiency</h4>
                <div class="text-center">
                    @php
                        $recoveryStats = $this->getRecoveryStatistics();
                        $recoveryEfficiency = $recoveryStats->loans_with_recoveries > 0 && $recoveryStats->total_written_off > 0
                            ? round(($recoveryStats->loans_with_recoveries / $recoveryStats->total_written_off) * 100, 1)
                            : 0;
                    @endphp
                    <p class="text-3xl font-bold {{ $recoveryEfficiency >= 50 ? 'text-green-600' : 'text-orange-600' }}">
                        {{ $recoveryEfficiency }}%
                    </p>
                    <p class="text-sm text-gray-600">of written-off loans</p>
                    <p class="text-xs text-gray-500 mt-2">have recoveries</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Actions -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pending Actions</h3>
        <div class="space-y-3">
            @php
                $pendingProvisions = $this->getPendingCyclesCount();
                $pendingWriteoffs = count($this->writtenOffLoans ?? []);
                $loansForReview = DB::table('loans as l')
                    ->join('loans_schedules as ls', function($join) {
                        $join->on(DB::raw('CAST(l.id AS VARCHAR)'), '=', 'ls.loan_id');
                    })
                    ->where('l.status', 'ACTIVE')
                    ->where(DB::raw('(CURRENT_DATE - ls.installment_date::date)'), '>', 180)
                    ->where(DB::raw('(CURRENT_DATE - ls.installment_date::date)'), '<=', 365)
                    ->count();
            @endphp
            
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <span class="flex-shrink-0 w-2 h-2 bg-orange-400 rounded-full"></span>
                    <span class="ml-3 text-sm font-medium text-gray-900">Provision cycles pending adjustment</span>
                </div>
                <span class="text-sm font-semibold text-orange-600">{{ $pendingProvisions }}</span>
            </div>
            
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <span class="flex-shrink-0 w-2 h-2 bg-red-400 rounded-full"></span>
                    <span class="ml-3 text-sm font-medium text-gray-900">Loans ready for write-off</span>
                </div>
                <span class="text-sm font-semibold text-red-600">{{ $pendingWriteoffs }}</span>
            </div>
            
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <span class="flex-shrink-0 w-2 h-2 bg-yellow-400 rounded-full"></span>
                    <span class="ml-3 text-sm font-medium text-gray-900">Loans requiring review (180-365 days)</span>
                </div>
                <span class="text-sm font-semibold text-yellow-600">{{ $loansForReview }}</span>
            </div>
        </div>
    </div>
</div>