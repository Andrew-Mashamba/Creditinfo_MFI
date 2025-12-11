<div>
    <div class="p-4">
        <p class="text-sm font-bold text-gray-600 mb-6">Key Financial Ratios (Uwiano Muhimu)</p>

        <!-- Capital Adequacy Ratio -->
        <!-- Capital Adequacy Ratio -->
        <table class="w-full table-fixed min-w-full border-collapse border border-gray-300 rounded-3xl mb-8" style="border-radius: 10px">
            <thead>
            <tr class="bg-blue-100">
                <th colspan="4" class="text-left p-4 text-xs font-semibold text-gray-800">
                    i) Capital Adequacy Ratio (Uwiano wa Mtaji Toshelezi)
                </th>
            </tr>
            <tr>
                <td class="p-4 border-collapse border border-gray-300 text-xs ">Ratio</td>
                @foreach ($distinctYears as $year)
                    <td class="p-4 border-collapse border border-gray-300 text-xs ">{{ $year }}</td>
                @endforeach
            </tr>
            </thead>
            <tbody class="">
            <!-- Core Capital Ratio -->
            <tr>
                <td class="p-4 border-collapse border border-gray-300">
                    <div class="border-b-2 border-red-500 inline-block text-xs ">Core Capital</div>
                    <div class="text-xs ">Assets</div>
                </td>
                @foreach($distinctYears as $year)
                    @php
                        $ratio = $ratios->where('end_of_financial_year_date', $year)->first();
                    @endphp
                    <td class="p-4 border-collapse border border-gray-300">
                        @if($ratio)
                            <div class="w-full flex text-xs ">
                                <div>
                                    <div class="border-b-2 border-red-500 text-xs ">{{ number_format($ratio->core_capital) }}</div>
                                    <div class="text-xs ">{{ number_format($ratio->total_assets) }}</div>
                                </div>
                                <div class="m-auto text-xs ">=</div>
                                <div class="m-auto text-xs ">{{ $ratio->total_assets > 0 ? number_format(($ratio->core_capital / $ratio->total_assets) * 100, 2) : 'N/A' }}%</div>
                            </div>
                        @else
                            <div class="w-full flex">
                                <div class="text-xs ">N/A</div>
                            </div>
                        @endif
                    </td>
                @endforeach
            </tr>

            <!-- Net Capital Ratio -->
            <tr>
                <td class="p-4 border-collapse border border-gray-300">
                    <div class="border-b-2 border-red-500 inline-block text-xs ">Net Capital</div>
                    <div class="text-xs ">Assets</div>
                </td>
                @foreach($distinctYears as $year)
                    @php
                        $ratio = $ratios->where('end_of_financial_year_date', $year)->first();
                    @endphp
                    <td class="p-4 border-collapse border border-gray-300">
                        @if($ratio)
                            <div class="w-full flex">
                                <div>
                                    <div class="border-b-2 border-red-500 text-xs ">{{ number_format($ratio->net_capital) }}</div>
                                    <div class="text-xs">{{ number_format($ratio->total_assets) }}</div>
                                </div>
                                <div class="m-auto text-xs ">=</div>
                                <div class="m-auto text-xs ">{{ $ratio->total_assets > 0 ? number_format(($ratio->net_capital / $ratio->total_assets) * 100, 2) : 'N/A' }}%</div>
                            </div>
                        @else
                            <div class="w-full flex">
                                <div class="text-xs ">N/A</div>
                            </div>
                        @endif
                    </td>
                @endforeach
            </tr>
            </tbody>
        </table>


        <!-- Liquidity Ratio -->
        <!-- Liquidity Ratio -->
        <table class="w-full table-fixed border-collapse border-collapse border border-gray-300 mb-8">
            <thead>
            <tr class="bg-blue-100">
                <th colspan="4" class="text-left p-4 text-lg font-semibold text-gray-800 text-xs ">
                    ii) Liquidity Ratio (Uwiano wa Ukwasi)
                </th>
            </tr>
            <tr>
                <td class="p-4 border-collapse border border-gray-300 text-xs ">Ratio</td>
                @foreach ($distinctYears as $year)
                    <td class="p-4 border-collapse border border-gray-300 text-xs ">{{ $year }}</td>
                @endforeach
            </tr>
            </thead>
            <tbody class="">
            <!-- Short-Term Assets Ratio -->
            <tr>
                <td class="p-4 border-collapse border border-gray-300">
                    <div class="border-b-2 border-red-500 inline-block text-xs ">Short-Term Assets</div>
                    <div class="text-xs ">Short-Term Liabilities</div>
                </td>
                @foreach($distinctYears as $year)
                    @php
                        $ratio = $ratios->where('end_of_financial_year_date', $year)->first();
                    @endphp
                    <td class="p-4 border-collapse border border-gray-300">
                        @if($ratio)
                            <div class="w-full flex text-xs ">
                                <div>
                                    <div class="border-b-2 border-red-500 text-xs ">{{ number_format($ratio->short_term_assets) }}</div>
                                    <div class="text-xs ">{{ number_format($ratio->short_term_liabilities) }}</div>
                                </div>
                                <div class="m-auto text-xs ">=</div>
                                <div class="m-auto text-xs ">{{ $ratio->short_term_liabilities > 0 ? number_format(($ratio->short_term_assets / $ratio->short_term_liabilities) * 100, 2) : 'N/A' }}%</div>
                            </div>
                        @else
                            <div class="w-full flex">
                                <div class="text-xs ">N/A</div>
                            </div>
                        @endif
                    </td>
                @endforeach
            </tr>
            </tbody>
        </table>


        <!-- Operational Efficiency Ratio -->
        <!-- Operational Efficiency Ratio -->
        <table class="w-full table-fixed border-collapse border-collapse border border-gray-300 mb-8 text-xs">
            <thead>
            <tr class="bg-blue-100">
                <th colspan="4" class="text-left p-4 text-xs font-semibold text-gray-800">
                    iii) Operational Efficiency Ratio (Ufanisi wa Uendeshaji)
                </th>
            </tr>
            <tr>
                <td class="p-4 border-collapse border border-gray-300 text-xs">Ratio</td>
                @foreach ($distinctYears as $year)
                    <td class="p-4 border-collapse border border-gray-300 text-xs">{{ $year }}</td>
                @endforeach
            </tr>
            </thead>
            <tbody class="">
            <!-- Expenses to Income Ratio -->
            <tr>
                <td class="p-4 border-collapse border border-gray-300">
                    <div class="border-b-2 border-red-500 inline-block text-xs">Expenses</div>
                    <div class="text-xs">Income</div>
                </td>
                @foreach($distinctYears as $year)
                    @php
                        $ratio = $ratios->where('end_of_financial_year_date', $year)->first();
                    @endphp
                    <td class="p-4 border-collapse border border-gray-300">
                        @if($ratio)
                            <div class="w-full flex">
                                <div>
                                    <div class="border-b-2 border-red-500 text-xs">{{ number_format($ratio->expenses) }}</div>
                                    <div>{{ number_format($ratio->income) }}</div>
                                </div>
                                <div class="m-auto text-xs">=</div>
                                <div class="m-auto text-xs">{{ $ratio->income > 0 ? number_format(($ratio->expenses / $ratio->income) * 100, 2) : 'N/A' }}%</div>
                            </div>
                        @else
                            <div class="w-full flex">
                                <div>N/A</div>
                            </div>
                        @endif
                    </td>
                @endforeach
            </tr>
            </tbody>
        </table>


    </div>
</div>
