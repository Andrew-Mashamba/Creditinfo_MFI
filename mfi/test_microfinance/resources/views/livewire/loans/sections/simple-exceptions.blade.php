<div class="w-full bg-white border border-gray-200 rounded-lg">
    <div class="p-4 border-b border-gray-200">
        <h3 class="text-sm font-semibold text-gray-900">Policy Exceptions</h3>
    </div>

    @if(isset($exceptionData['summary']['error_message']))
    <div class="p-4 border-b border-gray-200">
        <div class="text-xs text-red-600 bg-red-50 p-2 rounded border border-red-200">
            <strong>Error:</strong> {{ $exceptionData['summary']['error_message'] }}
        </div>
    </div>
    @endif

    <div class="p-4">
        @if(isset($exceptionData) && !empty($exceptionData))
        <div class="overflow-x-auto">
            <table class="w-full text-xs border border-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-1 text-left border-b border-r border-gray-200">Policy Check</th>
                        <th class="px-2 py-1 text-center border-b border-gray-200">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($exceptionData['loan_amount']))
                    <tr class="border-b">
                        <td class="px-2 py-1 border-r border-gray-200">{{ $exceptionData['loan_amount']['name'] ?? 'Loan Amount Check' }}</td>
                        <td class="px-2 py-1 text-center">
                            @if($exceptionData['loan_amount']['is_exceeded'] ?? false)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">BREACH</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">PASS</span>
                            @endif
                        </td>
                    </tr>
                    @endif

                    @if(isset($exceptionData['term']))
                    <tr class="border-b">
                        <td class="px-2 py-1 border-r border-gray-200">{{ $exceptionData['term']['name'] ?? 'Term Check' }}</td>
                        <td class="px-2 py-1 text-center">
                            @if($exceptionData['term']['is_exceeded'] ?? false)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">BREACH</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">PASS</span>
                            @endif
                        </td>
                    </tr>
                    @endif

                    @if(isset($exceptionData['credit_score']))
                    <tr class="border-b">
                        <td class="px-2 py-1 border-r border-gray-200">{{ $exceptionData['credit_score']['name'] ?? 'Credit Score Check' }}</td>
                        <td class="px-2 py-1 text-center">
                            @if($exceptionData['credit_score']['is_exceeded'] ?? false)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">BREACH</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">PASS</span>
                            @endif
                        </td>
                    </tr>
                    @endif

                    @if(isset($exceptionData['salary_installment']))
                    <tr class="border-b">
                        <td class="px-2 py-1 border-r border-gray-200">{{ $exceptionData['salary_installment']['name'] ?? 'Salary/Installment Check' }}</td>
                        <td class="px-2 py-1 text-center">
                            @if($exceptionData['salary_installment']['is_exceeded'] ?? false)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">BREACH</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">PASS</span>
                            @endif
                        </td>
                    </tr>
                    @endif

                    @if(isset($exceptionData['collateral']))
                    <tr class="border-b">
                        <td class="px-2 py-1 border-r border-gray-200">{{ $exceptionData['collateral']['name'] ?? 'Collateral Check' }}</td>
                        <td class="px-2 py-1 text-center">
                            @if($exceptionData['collateral']['is_exceeded'] ?? false)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">BREACH</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">PASS</span>
                            @endif
                        </td>
                    </tr>
                    @endif

                    @if(isset($exceptionData['ltv']) && isset($exceptionData['ltv']['product_ltv']) && $exceptionData['ltv']['product_ltv'] > 0)
                    <tr class="border-b">
                        <td class="px-2 py-1 border-r border-gray-200">{{ $exceptionData['ltv']['name'] ?? 'Loan-to-Value (LTV) Check' }}</td>
                        <td class="px-2 py-1 text-center">
                            @if($exceptionData['ltv']['is_exceeded'] ?? false)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">BREACH</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">PASS</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <p class="text-xs text-gray-500">No exceptions found</p>
        </div>
        @endif
    </div>
</div>

