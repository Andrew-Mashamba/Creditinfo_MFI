<div class="bg-white rounded-lg shadow-lg p-6">
    <h3 class="text-xl font-semibold text-gray-800 mb-6">
        <i class="fas fa-chart-line mr-2 text-blue-600"></i>Comparative Analysis
    </h3>

    @if(isset($comparativeData) && isset($comparativeData['current_period']) && isset($comparativeData['previous_period']))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Current Period -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-lg border-l-4 border-blue-600 shadow">
                <h4 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-check mr-2"></i>Current Period
                </h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-blue-700 font-medium">Accounts:</span>
                        <span class="font-bold text-blue-900 text-lg">{{ number_format($comparativeData['current_period']->account_count) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-blue-700 font-medium">Members:</span>
                        <span class="font-bold text-blue-900 text-lg">{{ number_format($comparativeData['current_period']->member_count) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-blue-200">
                        <span class="text-blue-700 font-medium">Total Balance:</span>
                        <span class="font-bold text-blue-900 text-lg">TZS {{ number_format($comparativeData['current_period']->total_balance, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-blue-700 font-medium">Average Balance:</span>
                        <span class="font-bold text-blue-900 text-lg">TZS {{ number_format($comparativeData['current_period']->avg_balance, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Previous Period -->
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-6 rounded-lg border-l-4 border-gray-400 shadow">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>Previous Period
                </h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-medium">Accounts:</span>
                        <span class="font-bold text-gray-900 text-lg">{{ number_format($comparativeData['previous_period']->account_count) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-medium">Members:</span>
                        <span class="font-bold text-gray-900 text-lg">{{ number_format($comparativeData['previous_period']->member_count) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-300">
                        <span class="text-gray-700 font-medium">Total Balance:</span>
                        <span class="font-bold text-gray-900 text-lg">TZS {{ number_format($comparativeData['previous_period']->total_balance, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-medium">Average Balance:</span>
                        <span class="font-bold text-gray-900 text-lg">TZS {{ number_format($comparativeData['previous_period']->avg_balance, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Changes -->
        @if(isset($comparativeData['changes']))
            <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-lg border-l-4 border-green-600 shadow">
                <h4 class="text-lg font-semibold text-green-800 mb-4 flex items-center">
                    <i class="fas fa-chart-bar mr-2"></i>Percentage Changes
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-sm text-gray-600 mb-2">Account Count</p>
                        <p class="text-2xl font-bold {{ $comparativeData['changes']['account_count_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $comparativeData['changes']['account_count_change'] >= 0 ? '+' : '' }}{{ $comparativeData['changes']['account_count_change'] }}%
                        </p>
                        <i class="fas fa-arrow-{{ $comparativeData['changes']['account_count_change'] >= 0 ? 'up' : 'down' }} {{ $comparativeData['changes']['account_count_change'] >= 0 ? 'text-green-600' : 'text-red-600' }} mt-2"></i>
                    </div>
                    <div class="text-center bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-sm text-gray-600 mb-2">Member Count</p>
                        <p class="text-2xl font-bold {{ $comparativeData['changes']['member_count_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $comparativeData['changes']['member_count_change'] >= 0 ? '+' : '' }}{{ $comparativeData['changes']['member_count_change'] }}%
                        </p>
                        <i class="fas fa-arrow-{{ $comparativeData['changes']['member_count_change'] >= 0 ? 'up' : 'down' }} {{ $comparativeData['changes']['member_count_change'] >= 0 ? 'text-green-600' : 'text-red-600' }} mt-2"></i>
                    </div>
                    <div class="text-center bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-sm text-gray-600 mb-2">Total Balance</p>
                        <p class="text-2xl font-bold {{ $comparativeData['changes']['total_balance_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $comparativeData['changes']['total_balance_change'] >= 0 ? '+' : '' }}{{ $comparativeData['changes']['total_balance_change'] }}%
                        </p>
                        <i class="fas fa-arrow-{{ $comparativeData['changes']['total_balance_change'] >= 0 ? 'up' : 'down' }} {{ $comparativeData['changes']['total_balance_change'] >= 0 ? 'text-green-600' : 'text-red-600' }} mt-2"></i>
                    </div>
                    <div class="text-center bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-sm text-gray-600 mb-2">Average Balance</p>
                        <p class="text-2xl font-bold {{ $comparativeData['changes']['avg_balance_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $comparativeData['changes']['avg_balance_change'] >= 0 ? '+' : '' }}{{ $comparativeData['changes']['avg_balance_change'] }}%
                        </p>
                        <i class="fas fa-arrow-{{ $comparativeData['changes']['avg_balance_change'] >= 0 ? 'up' : 'down' }} {{ $comparativeData['changes']['avg_balance_change'] >= 0 ? 'text-green-600' : 'text-red-600' }} mt-2"></i>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="p-12 text-center">
            <i class="fas fa-chart-line text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600">No comparative data available.</p>
        </div>
    @endif
</div>
