<div>
    {{-- Statement Header --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900 text-center">STATEMENT OF CHANGES IN EQUITY b</h2>
        <p class="text-sm text-gray-600 text-center">For the year ended 31 December {{ $selectedYear }}</p>
    </div>

    {{-- Main Statement Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse border border-gray-300">
            <thead>
                <tr class="bg-blue-900 text-white">
                    <th class="border border-gray-300 px-3 py-2 text-left">Components</th>
                    @foreach($equityStatementData['components'] ?? [] as $component)
                    <th class="border border-gray-300 px-3 py-2 text-right text-xs">
                        {{ Str::limit($component['name'], 20) }}
                    </th>
                    @endforeach
                    <th class="border border-gray-300 px-3 py-2 text-right font-bold">Total Equity</th>
                </tr>
            </thead>
            <tbody>
                {{-- Beginning Balance --}}
                <tr class="bg-gray-50">
                    <td class="border border-gray-300 px-3 py-2 font-semibold">Balance at Beginning of Year</td>
                    @foreach($equityStatementData['components'] ?? [] as $component)
                    <td class="border border-gray-300 px-3 py-2 text-right">
                        {{ $this->formatNumber($component['beginning_balance'] ?? 0) }}
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-3 py-2 text-right font-semibold">
                        {{ $this->formatNumber($equityStatementData['total_beginning'] ?? 0) }}
                    </td>
                </tr>

                {{-- Changes During the Year --}}
                <tr class="bg-blue-100">
                    <td class="border border-gray-300 px-3 py-1 font-semibold italic" colspan="6">Changes During the Year:</td>
                </tr>

                {{-- Net Income --}}
                <tr>
                    <td class="border border-gray-300 px-3 py-1 pl-6">Net Income for the Year</td>
                    @foreach($equityStatementData['components'] ?? [] as $component)
                    <td class="border border-gray-300 px-3 py-1 text-right">
                        @if(strpos($component['name'], 'RETAINED EARNINGS') !== false)
                            {{ $this->formatNumber($equityStatementData['net_income'] ?? 0) }}
                        @else
                            -
                        @endif
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-3 py-1 text-right">
                        {{ $this->formatNumber($equityStatementData['net_income'] ?? 0) }}
                    </td>
                </tr>

                {{-- Dividends --}}
                @if(($equityStatementData['dividends'] ?? 0) != 0)
                <tr>
                    <td class="border border-gray-300 px-3 py-1 pl-6">Dividends Declared</td>
                    @foreach($equityStatementData['components'] ?? [] as $component)
                    <td class="border border-gray-300 px-3 py-1 text-right">
                        @if(strpos($component['name'], 'RETAINED EARNINGS') !== false || strpos($component['name'], 'DIVIDENDS') !== false)
                            <span class="text-red-600">({{ $this->formatNumber(abs($equityStatementData['dividends'] ?? 0)) }})</span>
                        @else
                            -
                        @endif
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-3 py-1 text-right text-red-600">
                        ({{ $this->formatNumber(abs($equityStatementData['dividends'] ?? 0)) }})
                    </td>
                </tr>
                @endif

                {{-- Share Issues --}}
                @if((array_sum($equityStatementData['share_issues'] ?? []) ?? 0) != 0)
                <tr>
                    <td class="border border-gray-300 px-3 py-1 pl-6">Issue of Share Capital</td>
                    @foreach($equityStatementData['components'] ?? [] as $component)
                    <td class="border border-gray-300 px-3 py-1 text-right">
                        @if(strpos($component['name'], 'SHARE CAPITAL') !== false)
                            {{ $this->formatNumber($equityStatementData['share_issues']['capital'] ?? 0) }}
                        @elseif(strpos($component['name'], 'SHARE PREMIUM') !== false)
                            {{ $this->formatNumber($equityStatementData['share_issues']['premium'] ?? 0) }}
                        @else
                            -
                        @endif
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-3 py-1 text-right">
                        {{ $this->formatNumber(array_sum($equityStatementData['share_issues'] ?? [])) }}
                    </td>
                </tr>
                @endif

                {{-- Share Buybacks --}}
                @if(($equityStatementData['share_buybacks'] ?? 0) != 0)
                <tr>
                    <td class="border border-gray-300 px-3 py-1 pl-6">Share Buybacks</td>
                    @foreach($equityStatementData['components'] ?? [] as $component)
                    <td class="border border-gray-300 px-3 py-1 text-right">
                        @if(strpos($component['name'], 'TREASURY') !== false || strpos($component['name'], 'SHARE CAPITAL') !== false)
                            <span class="text-red-600">({{ $this->formatNumber(abs($equityStatementData['share_buybacks'] ?? 0)) }})</span>
                        @else
                            -
                        @endif
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-3 py-1 text-right text-red-600">
                        ({{ $this->formatNumber(abs($equityStatementData['share_buybacks'] ?? 0)) }})
                    </td>
                </tr>
                @endif

                {{-- Other Comprehensive Income --}}
                @if(($equityStatementData['other_comprehensive_income'] ?? 0) != 0)
                <tr>
                    <td class="border border-gray-300 px-3 py-1 pl-6">Other Comprehensive Income</td>
                    @foreach($equityStatementData['components'] ?? [] as $component)
                    <td class="border border-gray-300 px-3 py-1 text-right">
                        @if(strpos($component['name'], 'OTHER COMPREHENSIVE') !== false || strpos($component['name'], 'REVALUATION') !== false)
                            {{ $this->formatNumber($equityStatementData['other_comprehensive_income'] ?? 0) }}
                        @else
                            -
                        @endif
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-3 py-1 text-right">
                        {{ $this->formatNumber($equityStatementData['other_comprehensive_income'] ?? 0) }}
                    </td>
                </tr>
                @endif

                {{-- Transfer to Reserves --}}
                @if(($equityStatementData['transfer_to_reserves'] ?? 0) != 0)
                <tr>
                    <td class="border border-gray-300 px-3 py-1 pl-6">Transfer to Reserves</td>
                    @foreach($equityStatementData['components'] ?? [] as $component)
                    <td class="border border-gray-300 px-3 py-1 text-right">
                        @if(strpos($component['name'], 'RETAINED EARNINGS') !== false)
                            <span class="text-red-600">({{ $this->formatNumber(abs($equityStatementData['transfer_to_reserves'] ?? 0)) }})</span>
                        @elseif(strpos($component['name'], 'RESERVES') !== false && strpos($component['name'], 'OTHER COMPREHENSIVE') === false)
                            {{ $this->formatNumber($equityStatementData['transfer_to_reserves'] ?? 0) }}
                        @else
                            -
                        @endif
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-3 py-1 text-right">-</td>
                </tr>
                @endif

                {{-- Total Changes --}}
                <tr class="font-semibold bg-gray-100">
                    <td class="border border-gray-300 px-3 py-2">Total Changes During the Year</td>
                    @foreach($equityStatementData['components'] ?? [] as $component)
                    <td class="border border-gray-300 px-3 py-2 text-right">
                        {{ $this->formatNumber($component['changes'] ?? 0) }}
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-3 py-2 text-right">
                        {{ $this->formatNumber($equityStatementData['total_changes'] ?? 0) }}
                    </td>
                </tr>

                {{-- Ending Balance --}}
                <tr class="font-bold bg-green-100">
                    <td class="border border-gray-300 px-3 py-2">Balance at End of Year</td>
                    @foreach($equityStatementData['components'] ?? [] as $component)
                    <td class="border border-gray-300 px-3 py-2 text-right">
                        {{ $this->formatNumber($component['current_balance'] ?? 0) }}
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-3 py-2 text-right text-green-900">
                        {{ $this->formatNumber($equityStatementData['total_ending'] ?? 0) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Relationship Notes --}}
    <div class="mt-4 space-y-2">
        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-xs text-blue-700">
                <strong>Note 1:</strong> Net Income of {{ $this->formatNumber($equityStatementData['net_income'] ?? 0) }} 
                flows from the Statement of Comprehensive Income.
            </p>
        </div>
        <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-xs text-green-700">
                <strong>Note 2:</strong> Total Equity of {{ $this->formatNumber($equityStatementData['total_ending'] ?? 0) }} 
                flows to the Equity section of the Statement of Financial Position.
            </p>
        </div>
    </div>

    {{-- Additional Disclosures --}}
    @if(count($equityStatementData['disclosures'] ?? []) > 0)
    <div class="mt-4">
        <h3 class="text-sm font-semibold text-gray-900 mb-2">Additional Disclosures:</h3>
        <div class="bg-gray-50 p-3 rounded-lg text-xs text-gray-700 space-y-1">
            @foreach($equityStatementData['disclosures'] ?? [] as $disclosure)
            <p>• {{ $disclosure }}</p>
            @endforeach
        </div>
    </div>
    @endif
</div>