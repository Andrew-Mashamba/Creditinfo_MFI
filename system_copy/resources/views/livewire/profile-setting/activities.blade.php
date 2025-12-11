<div>
    <div class="overflow-x-auto p-4">
        <p class="text-sm font-bold text-gray-600 mb-6">Financial Position (Shughuli za Chama)</p>

        <table class="min-w-full border-collapse border border-gray-300 rounded-3xl table-auto">
            <thead class="bg-gray-100 rounded-3xl">
            <tr class="rounded-3xl">
                <th class="border border-gray-300 px-4 py-2 text-left text-gray-600">Financial Position</th>
                @foreach ($distinctYears as $year)
                    <th class="border border-gray-300 px-4 py-2 text-left text-gray-600 text-xs">{{ $year }} (Tshs.)</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @php
                $sections = [
                    'Financial' => [
                        'interest_on_loans' => 'Interest on Loans',
                        'other_income' => 'Other Income',
                        'total_income' => 'Total Income',
                        'expenses' => 'Expenses',
                        'annual_surplus' => 'Annual Surplus',
                    ]
                ];

                // Ensure financialData is not null and is a collection
                $data = $financialPositions ? $financialPositions->keyBy('end_of_business_year') : collect();
            @endphp

            @foreach ($sections as $section => $descriptions)
                <tr class="bg-gray-50">
                    <td class="border border-gray-300 px-4 py-2 font-semibold text-xs">{{ $section }}</td>
                    @foreach ($distinctYears as $year)
                        <td class="border border-gray-300 px-4 py-2"></td>
                    @endforeach
                </tr>

                @foreach ($descriptions as $key => $description)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-right text-xs">{{ $description }}</td>
                        @foreach ($distinctYears as $year)
                            <td class="border border-gray-300 px-4 py-2 text-right text-xs">
                                {{ number_format($data->get($year)->{$key} ?? 0, 2) }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>
</div>
