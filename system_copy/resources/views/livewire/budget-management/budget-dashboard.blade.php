<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Budget Monitoring Dashboard</h2>
        <p class="text-gray-600 dark:text-gray-400">Real-time budget tracking and performance monitoring</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Allocated -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Allocated</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">
                        {{ number_format($summary['total_allocated'] ?? 0, 2) }}
                    </p>
                </div>
                <div class="text-blue-500">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Spent -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Spent</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">
                        {{ number_format($summary['total_spent'] ?? 0, 2) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ round(($summary['total_spent'] / max($summary['total_allocated'], 1)) * 100, 1) }}% utilized
                    </p>
                </div>
                <div class="text-green-500">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Available Balance -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Available Balance</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">
                        {{ number_format($summary['total_available'] ?? 0, 2) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Excluding {{ number_format($summary['total_committed'] ?? 0, 2) }} committed
                    </p>
                </div>
                <div class="text-purple-500">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Budget Health -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Budget Health</p>
                    <div class="flex items-center space-x-2 mt-2">
                        <span class="flex items-center text-green-500">
                            <span class="text-lg">✅</span>
                            <span class="ml-1 text-sm">{{ $summary['healthy_count'] ?? 0 }}</span>
                        </span>
                        <span class="flex items-center text-yellow-500">
                            <span class="text-lg">⚠️</span>
                            <span class="ml-1 text-sm">{{ $summary['at_risk_count'] ?? 0 }}</span>
                        </span>
                        <span class="flex items-center text-red-500">
                            <span class="text-lg">❌</span>
                            <span class="ml-1 text-sm">{{ $summary['over_budget_count'] ?? 0 }}</span>
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Avg. utilization: {{ round($summary['average_utilization'] ?? 0, 1) }}%
                    </p>
                </div>
                <div class="text-indigo-500">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    @if(count($recentAlerts) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                Recent Alerts 
                <span class="ml-2 px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">
                    {{ count($recentAlerts) }}
                </span>
            </h3>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                @foreach($recentAlerts as $alert)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <span class="text-2xl">
                            @if($alert->alert_type == 'OVERSPENT') ❌
                            @elseif($alert->alert_type == 'CRITICAL') 🔴
                            @elseif($alert->alert_type == 'WARNING') ⚠️
                            @else ℹ️
                            @endif
                        </span>
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $alert->budget->budget_name ?? 'Unknown Budget' }}
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                {{ $alert->message }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $alert->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="viewAlertDetails({{ $alert->id }})"
                                class="px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-900">
                            View
                        </button>
                        <button wire:click="acknowledgeAlert({{ $alert->id }})"
                                class="px-3 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600">
                            Acknowledge
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Budgets Needing Attention -->
    @if(count($budgetsNeedingAttention) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                Budgets Needing Attention
            </h3>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Budget</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Allocated</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Spent</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Available</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Utilization</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($budgetsNeedingAttention as $budget)
                        <tr>
                            <td class="px-4 py-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white">
                                        {{ $budget->budget_name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $budget->expenseAccount->account_name ?? 'N/A' }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ number_format($budget->allocated_amount, 2) }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ number_format($budget->spent_amount, 2) }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ number_format($budget->available_amount, 2) }}
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex items-center">
                                    <div class="flex-1 mr-2">
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-{{ $getUtilizationColor($budget->utilization_percentage) }}-500 h-2 rounded-full"
                                                 style="width: {{ min($budget->utilization_percentage, 100) }}%"></div>
                                        </div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                        {{ round($budget->utilization_percentage, 1) }}%
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($budget->health_status == 'OVERSPENT') bg-red-100 text-red-800
                                    @elseif($budget->health_status == 'CRITICAL') bg-orange-100 text-orange-800
                                    @elseif($budget->health_status == 'WARNING') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    {{ $budget->health_status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Top Spenders -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Spenders Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Top Budget Utilization
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($topSpenders as $budget)
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $budget->budget_name }}
                            </span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ round($budget->utilization_percentage, 1) }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full
                                @if($budget->utilization_percentage > 100) bg-red-500
                                @elseif($budget->utilization_percentage >= 90) bg-orange-500
                                @elseif($budget->utilization_percentage >= 80) bg-yellow-500
                                @else bg-blue-500
                                @endif"
                                style="width: {{ min($budget->utilization_percentage, 100) }}%"></div>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-xs text-gray-500">
                                Spent: {{ number_format($budget->spent_amount, 0) }}
                            </span>
                            <span class="text-xs text-gray-500">
                                Budget: {{ number_format($budget->allocated_amount, 0) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Quick Actions
                </h3>
            </div>
            <div class="p-6 space-y-3">
                <button wire:click="recalculateAllBudgets"
                        class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-900 transition">
                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Recalculate All Budgets
                </button>
                
                <button wire:click="$emitUp('menuItemClicked', 2)"
                        class="w-full px-4 py-2 bg-green-500 text-white text-center rounded hover:bg-green-600 transition">
                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Manage Budgets
                </button>
                
                <button wire:click="$emitUp('menuItemClicked', 4)"
                        class="w-full px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 transition">
                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v8m5-5h4m-4 0h4m-4 0v4m0-4v4"></path>
                    </svg>
                    Generate Report
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Details Modal -->
    @if($showAlertDetails && $selectedAlert)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Alert Details
                </h3>
                <div class="mt-4 space-y-3">
                    <div>
                        <label class="text-sm text-gray-500">Budget:</label>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            {{ $selectedAlert->budget->budget_name ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Alert Type:</label>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            {{ $selectedAlert->alert_type }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Message:</label>
                        <p class="text-sm text-gray-800 dark:text-gray-200">
                            {{ $selectedAlert->message }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Threshold:</label>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            {{ $selectedAlert->threshold_value }}%
                        </p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Actual:</label>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            {{ $selectedAlert->actual_value }}%
                        </p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Created:</label>
                        <p class="text-sm text-gray-800 dark:text-gray-200">
                            {{ $selectedAlert->created_at->format('Y-m-d H:i:s') }}
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex justify-end space-x-3">
                    <button wire:click="acknowledgeAlert({{ $selectedAlert->id }})"
                            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                        Acknowledge
                    </button>
                    <button wire:click="closeAlertDetails"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Success Message -->
    @if(session()->has('message'))
    <div class="fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50"
         x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 3000)">
        {{ session('message') }}
    </div>
    @endif
</div>