<div>
    {{-- Statement Header --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900 text-center">STATEMENT OF FINANCIAL POSITION</h2>
        <p class="text-sm text-gray-600 text-center">As at 31 December {{ $selectedYear }}</p>
    </div>

    {{-- Currency Header --}}
    @if($showComparison)
    <div class="mb-4">
        <table class="ml-auto text-xs border-collapse border border-gray-300">
            <thead>
                <tr>
                    @foreach($comparisonYears as $year)
                    <th class="border border-gray-300 px-3 py-1 bg-gray-50">31.12.{{ $year }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach($comparisonYears as $year)
                    <td class="border border-gray-300 px-3 py-1 text-center">TZS</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    {{-- Main Statement Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse border border-gray-300">
            {{-- ASSETS Section --}}
            <thead>
                <tr class="bg-blue-900 text-white">
                    <th class="border border-gray-300 px-3 py-2 text-left font-bold" colspan="{{ $showComparison ? count($comparisonYears) + 1 : 2 }}">
                        ASSETS
                    </th>
                </tr>
            </thead>
            <tbody>
                {{-- Current Assets --}}
                <tr class="bg-blue-100">
                    <td class="border border-gray-300 px-3 py-1 font-semibold">Current Assets</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-1 text-right"></td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-1 text-right"></td>
                    @endif
                </tr>
                
                @foreach($balanceSheetData['assets']['current'] ?? [] as $asset)
                <tr>
                    <td class="border border-gray-300 px-3 py-1 pl-6">{{ $asset['account_name'] ?? '' }}</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($asset['amount'] ?? 0) }}
                        </td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($asset['amount'] ?? 0) }}
                        </td>
                    @endif
                </tr>
                @endforeach
                
                {{-- Total Current Assets --}}
                <tr class="font-semibold bg-gray-50">
                    <td class="border border-gray-300 px-3 py-1 pl-4">Total Current Assets</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($balanceSheetData['assets']['current_total'] ?? array_sum(array_column($balanceSheetData['assets']['current'] ?? [], 'amount'))) }}
                        </td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($balanceSheetData['assets']['current_total'] ?? array_sum(array_column($balanceSheetData['assets']['current'] ?? [], 'amount'))) }}
                        </td>
                    @endif
                </tr>

                {{-- Non-Current Assets --}}
                <tr class="bg-blue-100">
                    <td class="border border-gray-300 px-3 py-1 font-semibold">Non-Current Assets</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-1 text-right"></td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-1 text-right"></td>
                    @endif
                </tr>
                
                @foreach($balanceSheetData['assets']['non_current'] ?? [] as $asset)
                <tr>
                    <td class="border border-gray-300 px-3 py-1 pl-6">{{ $asset['account_name'] ?? '' }}</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($asset['amount'] ?? 0) }}
                        </td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($asset['amount'] ?? 0) }}
                        </td>
                    @endif
                </tr>
                @endforeach
                
                {{-- Total Non-Current Assets --}}
                <tr class="font-semibold bg-gray-50">
                    <td class="border border-gray-300 px-3 py-1 pl-4">Total Non-Current Assets</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($balanceSheetData['assets']['non_current_total'] ?? array_sum(array_column($balanceSheetData['assets']['non_current'] ?? [], 'amount'))) }}
                        </td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($balanceSheetData['assets']['non_current_total'] ?? array_sum(array_column($balanceSheetData['assets']['non_current'] ?? [], 'amount'))) }}
                        </td>
                    @endif
                </tr>

                {{-- TOTAL ASSETS --}}
                <tr class="font-bold bg-blue-200">
                    <td class="border border-gray-300 px-3 py-2">TOTAL ASSETS</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber($balanceSheetData['total_assets'] ?? 0) }}
                        </td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber($balanceSheetData['total_assets'] ?? 0) }}
                        </td>
                    @endif
                </tr>

                {{-- LIABILITIES Section --}}
                <tr class="bg-blue-900 text-white">
                    <th class="border border-gray-300 px-3 py-2 text-left font-bold" colspan="{{ $showComparison ? count($comparisonYears) + 1 : 2 }}">
                        LIABILITIES
                    </th>
                </tr>

                {{-- Current Liabilities --}}
                <tr class="bg-blue-100">
                    <td class="border border-gray-300 px-3 py-1 font-semibold">Current Liabilities</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-1 text-right"></td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-1 text-right"></td>
                    @endif
                </tr>
                
                @foreach($balanceSheetData['liabilities']['current'] ?? [] as $liability)
                <tr>
                    <td class="border border-gray-300 px-3 py-1 pl-6">{{ $liability['account_name'] ?? '' }}</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($liability['amount'] ?? 0) }}
                        </td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($liability['amount'] ?? 0) }}
                        </td>
                    @endif
                </tr>
                @endforeach
                
                {{-- Total Current Liabilities --}}
                <tr class="font-semibold bg-gray-50">
                    <td class="border border-gray-300 px-3 py-1 pl-4">Total Current Liabilities</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($balanceSheetData['liabilities']['current_total'] ?? array_sum(array_column($balanceSheetData['liabilities']['current'] ?? [], 'amount'))) }}
                        </td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($balanceSheetData['liabilities']['current_total'] ?? array_sum(array_column($balanceSheetData['liabilities']['current'] ?? [], 'amount'))) }}
                        </td>
                    @endif
                </tr>

                {{-- Non-Current Liabilities --}}
                <tr class="bg-blue-100">
                    <td class="border border-gray-300 px-3 py-1 font-semibold">Non-Current Liabilities</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-1 text-right"></td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-1 text-right"></td>
                    @endif
                </tr>
                
                @foreach($balanceSheetData['liabilities']['non_current'] ?? [] as $liability)
                <tr>
                    <td class="border border-gray-300 px-3 py-1 pl-6">{{ $liability['account_name'] ?? '' }}</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($liability['amount'] ?? 0) }}
                        </td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($liability['amount'] ?? 0) }}
                        </td>
                    @endif
                </tr>
                @endforeach
                
                {{-- Total Non-Current Liabilities --}}
                <tr class="font-semibold bg-gray-50">
                    <td class="border border-gray-300 px-3 py-1 pl-4">Total Non-Current Liabilities</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($balanceSheetData['liabilities']['non_current_total'] ?? array_sum(array_column($balanceSheetData['liabilities']['non_current'] ?? [], 'amount'))) }}
                        </td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($balanceSheetData['liabilities']['non_current_total'] ?? array_sum(array_column($balanceSheetData['liabilities']['non_current'] ?? [], 'amount'))) }}
                        </td>
                    @endif
                </tr>

                {{-- TOTAL LIABILITIES --}}
                <tr class="font-bold bg-red-100">
                    <td class="border border-gray-300 px-3 py-2">TOTAL LIABILITIES</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber($balanceSheetData['liabilities']['total'] ?? 0) }}
                        </td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber($balanceSheetData['liabilities']['total'] ?? 0) }}
                        </td>
                    @endif
                </tr>

                {{-- EQUITY Section --}}
                <tr class="bg-blue-900 text-white">
                    <th class="border border-gray-300 px-3 py-2 text-left font-bold" colspan="{{ $showComparison ? count($comparisonYears) + 1 : 2 }}">
                        EQUITY
                    </th>
                </tr>
                
                @foreach($balanceSheetData['equity']['items'] ?? [] as $equity)
                <tr>
                    <td class="border border-gray-300 px-3 py-1 pl-4">{{ $equity['account_name'] ?? '' }}</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($equity['amount'] ?? 0) }}
                        </td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber($equity['amount'] ?? 0) }}
                        </td>
                    @endif
                </tr>
                @endforeach

                {{-- TOTAL EQUITY --}}
                <tr class="font-bold bg-green-100">
                    <td class="border border-gray-300 px-3 py-2">TOTAL EQUITY</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber($balanceSheetData['equity']['total'] ?? 0) }}
                        </td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber($balanceSheetData['equity']['total'] ?? 0) }}
                        </td>
                    @endif
                </tr>

                {{-- TOTAL LIABILITIES AND EQUITY --}}
                <tr class="font-bold bg-blue-200">
                    <td class="border border-gray-300 px-3 py-2">TOTAL LIABILITIES AND EQUITY</td>
                    @if($showComparison)
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber($balanceSheetData['total_liabilities_equity'] ?? 0) }}
                        </td>
                        @endforeach
                    @else
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber($balanceSheetData['total_liabilities_equity'] ?? 0) }}
                        </td>
                    @endif
                </tr>
            </tbody>
        </table>
    </div>
</div>