<div class="w-full bg-white border border-gray-200 rounded-lg mt-4">
    <div class="p-4 border-b border-gray-200">
        <h3 class="text-sm font-semibold text-gray-900">Product Information</h3>
    </div>

    <div class="p-4 space-y-4">
        <!-- Product Basic Info -->
        <div>
           
            <div class="overflow-x-auto">
                <table class="w-full text-xs border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-1 text-left border-b border-r border-gray-200">Product</th>
                            <th class="px-2 py-1 text-left border-b border-gray-200">{{ $productBasicInfo['sub_product_name'] ?? 'Product' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($productLoanLimits) && !empty($productLoanLimits))
                        <tr class="border-b">
                            <td class="px-2 py-1 border-r border-gray-200">Maximum Unsecured and Secured Exposure (MUE)</td>
                            <td class="px-2 py-1 font-semibold text-blue-900">{{ number_format($productLoanLimits['max_amount'] ?? 0, 2) }} TZS</td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-2 py-1 border-r border-gray-200">Maximum Tenor (months)</td>
                            <td class="px-2 py-1 font-semibold text-blue-900">{{ $productLoanLimits['max_term'] ?? 0 }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-2 py-1 border-r border-gray-200">Debt Service Ratio (DSR)</td>
                            <td class="px-2 py-1 font-semibold text-blue-900">{{ $productLoanLimits['ltv'] ?? 0 }}%</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Requirements -->
        @if(isset($productRequirements) && !empty($productRequirements['requirements']))
        <div>
            <h4 class="text-xs font-medium text-gray-700 mb-2">Requirements</h4>
            <div class="space-y-1">
                @foreach($productRequirements['requirements'] as $requirement)
                <div class="flex items-center text-xs p-2 border border-gray-200 rounded">
                    <span class="w-1.5 h-1.5 rounded-full mr-2 bg-blue-900"></span>
                    <span class="text-gray-700">{{ $requirement['description'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Validation Rules -->
        @if(isset($productValidation) && !empty($productValidation['validation_rules']))
        <div>
            <h4 class="text-xs font-medium text-gray-700 mb-2">Validation Rules</h4>
            <div class="space-y-1">
                @foreach($productValidation['validation_rules'] as $rule)
                <div class="flex items-center text-xs p-2 border border-gray-200 rounded">
                    <span class="w-1.5 h-1.5 rounded-full mr-2 bg-blue-900"></span>
                    <span class="text-gray-700">{{ $rule['description'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
