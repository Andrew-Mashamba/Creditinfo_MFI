<div class="max-w-6xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">

    <!-- Header -->
    <div class="bg-red-600 text-white text-center p-4 font-bold text-lg">
        NBCSACOSS LENDING FRAMEWORK 2025
    </div>
    <div class="bg-red-700 text-white text-center p-2 font-semibold">
        Limit Assignments for all Unsecured and Secured Loans (Figures in TZS "000")
    </div>

    <table class="w-full text-xs border border-gray-200">
        <thead>
            <tr class="bg-gray-50">
                <th class="px-2 py-1 text-left border-b border-r border-gray-200">Category</th>
                <th class="px-2 py-1 text-left border-b border-r border-gray-200">Loan Type</th>
                <th class="px-2 py-1 text-right border-b border-gray-200">Value</th>
            </tr>
        </thead>
        <tbody>

            <!-- Policy Breaches -->
            <tr class="bg-gray-100 font-bold">
                <td colspan="3" class="px-2 py-1 border-b border-gray-200">Policy Breaches</td>
            </tr>
         
            <!-- Debt Service Ratio -->
            <tr class="bg-red-100 font-bold">
                <td colspan="3" class="px-2 py-1 border-b border-gray-200">Debt Service Ratio (DSR)</td>
            </tr>
            @foreach($loanProducts as $index => $product)
                <tr class="border-b">
                    <td class="px-2 py-1 border-r border-gray-200"></td>
                    <td class="px-2 py-1 border-r border-gray-200">{{ $product->sub_product_name }}</td>
                    <td class="px-2 py-1 text-right font-semibold text-red-900">{{ $product->ltv ?? 67 }}%</td>
                </tr>
            @endforeach

            <!-- Maximum Unsecured and Secured Exposure -->
            <tr class="bg-red-200 font-bold">
                <td colspan="3" class="px-2 py-1 border-b border-gray-200">Maximum Unsecured and Secured Exposure (MUE)</td>
            </tr>
            @foreach($loanProducts as $index => $product)
                <tr class="border-b">
                    <td class="px-2 py-1 border-r border-gray-200"></td>
                    <td class="px-2 py-1 border-r border-gray-200">{{ $product->sub_product_name }}</td>
                    <td class="px-2 py-1 text-right font-semibold text-red-900">{{ number_format($product->principle_max_value / 1000, 0) }}</td>
                </tr>
            @endforeach

            <!-- Maximum Tenor -->
            <tr class="bg-red-50 font-bold">
                <td colspan="3" class="px-2 py-1 border-b border-gray-200">Maximum Tenor (months)</td>
            </tr>
            @foreach($loanProducts as $index => $product)
                <tr class="border-b">
                    <td class="px-2 py-1 border-r border-gray-200"></td>
                    <td class="px-2 py-1 border-r border-gray-200">{{ $product->sub_product_name }}</td>
                    <td class="px-2 py-1 text-right font-semibold text-red-900">{{ $product->max_term }}</td>
                </tr>
            @endforeach

        </tbody>
    </table>
    
  
</div>