{{-- LOAN REPAYMENT SCHEDULE SECTION --}}
<div class="w-full bg-white border border-gray-200 rounded-lg mt-4">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-sm font-semibold text-gray-900">Loan Repayment Schedule</h3>
        <div class="flex space-x-2">
            <button onclick="exportSchedule()" class="px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-500">
                Export
            </button>
            <button onclick="printSchedule()" class="px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-500">
                Print
            </button>
        </div>
    </div>

    @if(isset($schedule) && count($schedule) > 0)
    <div class="p-4 space-y-4">
        <!-- Schedule Summary Grid -->
        <div>
            <h4 class="text-xs font-medium text-gray-700 mb-2">Schedule Summary</h4>
            <div class="grid grid-cols-4 gap-2 text-xs">
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Total Payments</div>
                    <div class="font-semibold text-blue-900">{{ count($schedule) }}</div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Monthly Payment</div>
                    <div class="font-semibold text-blue-900">
                        @php
                            $regularMonthlyPayment = 0;
                            if (isset($schedule) && count($schedule) > 0) {
                                $regularMonthlyPayment = $schedule[0]['payment'] ?? 0;
                                if (isset($schedule[0]['principal']) && $schedule[0]['principal'] == 0 && count($schedule) > 1) {
                                    $regularMonthlyPayment = $schedule[1]['payment'] ?? 0;
                                }
                            }
                        @endphp
                        {{ number_format($regularMonthlyPayment, 2) }} TZS
                    </div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Total Interest</div>
                    <div class="font-semibold text-blue-900">{{ number_format($footer['total_interest'] ?? 0, 2) }} TZS</div>
                </div>
                <div class="p-2 border border-gray-200 rounded">
                    <div class="text-gray-600">Total Repayment</div>
                    <div class="font-semibold text-blue-900">{{ number_format($footer['total_payment'] ?? 0, 2) }} TZS</div>
                </div>
            </div>
        </div>

        <!-- Schedule Controls -->
        <div class="flex justify-between items-center">
            <div class="flex space-x-4">
                <div class="flex items-center space-x-2">
                    <label class="text-xs text-gray-600">Show:</label>
                    <select id="scheduleFilter" class="text-xs border border-gray-300 rounded px-2 py-1 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="all">All Installments</option>
                        <option value="first5">First 5</option>
                        <option value="last5">Last 5</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-xs text-gray-600">Sort by:</label>
                    <select id="scheduleSort" class="text-xs border border-gray-300 rounded px-2 py-1 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="date">Date</option>
                        <option value="payment">Payment Amount</option>
                        <option value="balance">Balance</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Schedule Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs border border-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-1 text-left border-b border-r border-gray-200">#</th>
                        <th class="px-2 py-1 text-left border-b border-r border-gray-200">Due Date</th>
                        <th class="px-2 py-1 text-right border-b border-r border-gray-200">Opening Balance</th>
                        <th class="px-2 py-1 text-right border-b border-r border-gray-200">Payment</th>
                        <th class="px-2 py-1 text-right border-b border-r border-gray-200">Principal</th>
                        <th class="px-2 py-1 text-right border-b border-r border-gray-200">Interest</th>
                        <th class="px-2 py-1 text-right border-b border-gray-200">Closing Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedule as $index => $installment)
                    @php
                    $isOverdue = false;
                    $isCurrent = false;
                    $isFuture = false;

                    if (isset($installment['installment_date'])) {
                        $installmentDate = \Carbon\Carbon::parse($installment['installment_date']);
                        $today = \Carbon\Carbon::today();

                        if ($installmentDate->lt($today)) {
                            $isOverdue = true;
                        } elseif ($installmentDate->eq($today)) {
                            $isCurrent = true;
                        } else {
                            $isFuture = true;
                        }
                    }
                    @endphp
                    <tr class="border-b {{ $isOverdue ? 'bg-red-50' : ($isCurrent ? 'bg-yellow-50' : '') }}">
                        <td class="px-2 py-1 font-medium border-r border-gray-200">{{ $index + 1 }}</td>
                        <td class="px-2 py-1 border-r border-gray-200">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $installment['installment_date'] ?? '-' }}</span>
                                @if($isOverdue)
                                <span class="text-xs text-red-600">Overdue</span>
                                @elseif($isCurrent)
                                <span class="text-xs text-yellow-600">Due Today</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-blue-900">{{ number_format((float)($installment['opening_balance'] ?? 0), 2) }}</td>
                        <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-blue-900">{{ number_format((float)($installment['payment'] ?? 0), 2) }}</td>
                        <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-blue-900">{{ number_format((float)($installment['principal'] ?? 0), 2) }}</td>
                        <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-blue-900">{{ number_format((float)($installment['interest'] ?? 0), 2) }}</td>
                        <td class="px-2 py-1 text-right font-semibold text-blue-900">{{ number_format((float)($installment['closing_balance'] ?? 0), 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-2 py-4 text-center text-gray-500">
                            No repayment schedule available.
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                <!-- Footer with Totals -->
                @if(isset($footer) && count($schedule) > 0)
                <tfoot>
                    <tr class="bg-gray-50 font-semibold border-t-2 border-gray-300">
                        <td colspan="3" class="px-2 py-1 border-r border-gray-200">Totals</td>
                        <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-blue-900">{{ number_format((float)($footer['total_payment'] ?? 0), 2) }}</td>
                        <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-blue-900">{{ number_format((float)($footer['total_principal'] ?? 0), 2) }}</td>
                        <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-blue-900">{{ number_format((float)($footer['total_interest'] ?? 0), 2) }}</td>
                        <td class="px-2 py-1 text-right font-semibold text-blue-900">{{ number_format((float)($footer['final_closing_balance'] ?? 0), 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

       
    </div>

    @else
    <!-- No Schedule Available -->
    <div class="p-4 text-center">
        <div class="text-gray-500 text-sm">
            <p>No repayment schedule available.</p>
            <p class="text-xs mt-1">The repayment schedule will be generated once the loan is approved and disbursed.</p>
        </div>
    </div>
    @endif
</div>

<!-- JavaScript for Schedule Functionality -->
<script nonce="{{ csp_nonce() }}">
    function exportSchedule() {
        alert('Export functionality will be implemented here');
    }

    function printSchedule() {
        window.print();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const filterSelect = document.getElementById('scheduleFilter');
        const sortSelect = document.getElementById('scheduleSort');

        if (filterSelect) {
            filterSelect.addEventListener('change', function() {
                console.log('Filter changed to:', this.value);
            });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                console.log('Sort changed to:', this.value);
            });
        }
    });
</script> 