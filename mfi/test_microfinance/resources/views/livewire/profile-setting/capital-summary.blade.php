<div>
    <div class="overflow-x-auto p-4">
        <p class="text-sm font-bold text-gray-600 mb-6">Capital Structure (Muundo Wa Mtaji Wa Asasi)</p>

        <table class="min-w-full border-collapse border border-gray-300 rounded-3xl table-auto">
            <thead class="bg-gray-100 rounded-3xl">
            <tr class="rounded-3xl">
                <th class="border border-gray-300 px-4 py-2 text-left text-gray-600 text-sm">Capital Structure</th>
                <th class="border border-gray-300 px-4 py-2 text-right text-gray-600 font-semibold text-xs">{{ date('Y') }} (Tshs.)</th>
            </tr>
            </thead>
            <tbody>
            <tr class="bg-gray-50">
                <td class="border border-gray-300 px-4 py-2 font-semibold font-semibold text-xs">Hisa za Wanachama</td>
                <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-xs">
                    {{ number_format($yearlyData['member_shares'] ?? 0, 2) }}
                </td>
            </tr>
            <tr>
                <td class="border border-gray-300 px-4 py-2 font-semibold text-xs">Akiba za Wanachama</td>
                <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-xs">
                    {{ number_format($yearlyData['member_savings'] ?? 0, 2) }}
                </td>
            </tr>
            <tr>
                <td class="border border-gray-300 px-4 py-2 font-semibold text-xs">Malimbikizo ya Ziada</td>
                <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-xs">
                    {{ number_format($yearlyData['additional_reserves'] ?? 0, 2) }}
                </td>
            </tr>
            <tr>
                <td class="border border-gray-300 px-4 py-2 font-semibold text-xs">Akiba Mbali Mbali</td>
                <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-xs">
                    {{ number_format($yearlyData['miscellaneous_savings'] ?? 0, 2) }}
                </td>
            </tr>
            <tr>
                <td class="border border-gray-300 px-4 py-2 font-semibold text-xs">Uthamini wa Mali za Kudumu</td>
                <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-xs">
                    {{ number_format($yearlyData['valuation_of_fixed_assets'] ?? 0, 2) }}
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
