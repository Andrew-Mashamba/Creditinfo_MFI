<div>
    <div class="overflow-x-auto p-4">
        <p class="text-sm font-bold text-gray-600 mb-6">Statistics (Takwimu)</p>

        <table class="min-w-full border-collapse border border-gray-300 rounded-3xl table-auto">
            <thead class="bg-gray-100 rounded-3xl">
            <tr class="rounded-3xl">
                <th class="border border-gray-300 px-4 py-2 text-left text-gray-600">Statistics</th>
                @php
                    $distinctYears = $this->financialData->pluck('end_of_business_year')->unique()->sortDesc();
                @endphp
                @foreach ($distinctYears as $year)
                    <th class="border border-gray-300 px-4 py-2 text-left text-gray-600 text-xs">{{ $year }} (Tshs.)</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @php
                $sections = [
                    'Members' => ['Active Members', 'Inactive Members'],
                    'Employees' => ['Male Employees', 'Female Employees'],
                    'Financial' => [
                        'Total Assets',
                        'Short-Term Assets',
                        'Short-Term Liabilities',
                        'Member Savings and Deposits',
                        'Loans to Members',
                        'Core Capital',
                        'Member Shares',
                        'Institutional Capital',
                        'Total Income',
                        'Total Interest on Loans',
                        'Total Expenses'
                    ]
                ];
            @endphp

            @foreach ($sections as $section => $descriptions)
                <tr class="bg-gray-50">
                    <td class="border border-gray-300 px-4 py-2 font-semibold text-xs">{{ $section }}</td>
                    @foreach ($distinctYears as $year)
                        <td class="border border-gray-300 px-4 py-2"></td>
                    @endforeach
                </tr>

                @foreach ($descriptions as $description)
                    @php
                        $data = $this->financialData->where('description', $description)->keyBy('end_of_business_year');
                    @endphp

                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-right text-xs">{{ $description }}</td>
                        @foreach ($distinctYears as $year)
                            <td class="border border-gray-300 px-4 py-2 text-right text-xs">
                                {{ number_format($data[$year]->value ?? '0',2) }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach

            </tbody>
        </table>
    </div>
</div>
