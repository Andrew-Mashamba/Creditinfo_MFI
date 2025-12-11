<div>
    <style nonce="{{ csp_nonce() }}">
        .formula {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .formula .line {
            width: 50px;
            border-top-width: 2px;
        }
    </style>
    <div class="w-full bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y border border-gray-300 divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider border">ID</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider border">Financial Ratio</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider border">Formula</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider border">Value</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <!-- Example Row 1 -->
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border">1</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">Current Ratio</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">
                        <div class="formula flex p-4">
                            <span class=" ">Current Ratio</span>
                            <span class="mx-2">=</span>
                            <div class="relative gap-4">
                                <span class=" ml-4">Current Assets</span>
                                <hr class="w-[150px] border-gray-400 my-1" />
                                <span class=" ml-4">Current Liabilities</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border"> {{ round($this->current_ratio ,3)}}</td>
                </tr>
                <!-- Example Row 2 -->
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border">2</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">Quick Ratio</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">
                        <div class="formula flex p-4">
                            <span class="">Quick Ratio</span>
                            <span class="mx-2">=</span>
                            <div class="relative gap-4">
                                <span class=" ">Current Assets - Inventory</span>
                                <hr class="w-[150px] border-gray-400 my-1" />
                                <span class=" ml-4">Current Liabilities</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border"> @empty($this->quick_ratio)
                        nill
                    @endempty </td>
                </tr>
                <!-- Example Row 3 -->
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border">3</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">Net Profit Margin</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">
                        <div class="formula flex p-4">
                            <span class="">Net Profit Margin</span>
                            <span class="mx-2">=</span>
                            <div class="relative gap-4">
                                <span class=" ml-4">Net Profit</span>
                                <hr class="w-[150px] border-gray-400 my-1" />
                                <span class=" ml-4">Revenue</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">{{ $this->net_profit_margin *100}}%</td>
                </tr>
                <!-- Example Row 4 -->
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border">4</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">Debt to Equity Ratio</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">
                        <div class="formula flex p-4">
                            <span class="">Debt to Equity</span>
                            <span class="mx-2">=</span>
                            <div class="relative gap-4">
                                <span class=" ml-4">Total Debt</span>
                                <hr class="w-[150px] border-gray-400 my-1" />
                                <span class=" ml-4">Total Equity</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">{{ round($dept_to_equity_ratio ,2) }} </td>
                </tr>
                <!-- Example Row 5 -->
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border">5</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">Return on Assets (ROA)</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">
                        <div class="formula flex p-4">
                            <span class=" ">ROA</span>
                            <span class="mx-2">=</span>
                            <div class="relative gap-4">
                                <span class="  ml-4">Net Income</span>
                                <hr class="w-[150px] border-gray-400 my-1" />
                                <span class="  ml-4">Total Assets</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">{{ round($this->return_of_assets,2) }}%</td>
                </tr>
                <!-- Example Row 6 -->
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border">6</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">Return on Equity (ROE)</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">
                        <div class="formula flex p-4">
                            <span class="">ROE</span>
                            <span class="mx-2">=</span>
                            <div class="relative gap-4">
                                <span class="  ml-4">Net Income</span>
                                <hr class="w-[150px] border-gray-400 my-1" />
                                <span class="  ml-4">Total Equity</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">{{ round($this->return_of_equity, 2) }}%</td>                </tr>
                <!-- Example Row 7 -->
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border">7</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">Gross Profit Margin</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">
                        <div class="formula flex p-4">
                            <span class="">Gross Profit Margin</span>
                            <span class="mx-2">=</span>
                            <div class="relative gap-4">
                                <span class=" ml-4">Gross Profit</span>
                                <hr class="w-[150px] border-gray-400 my-1" />
                                <span class="  ml-4">Revenue</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border">{{ round($this->gross_profit_margin ,2) }}%</td>
                </tr>

            </tbody>
        </table>
    </div>



</div>
