<div class="bg-white p-4 rounded-lg">

    <!-- Title Section -->
    <div class="text-center">
        <h2 class="text-lg font-bold uppercase">NBC SACCOS LTD</h2>
        <h3 class="text-base font-semibold uppercase">TAARIFA YA MAPATO NA MATUMIZI KWA KIPINDI KINACHOISHIA z
            {{date('Y-m-d')}}</h3>
    </div>

    <!-- Date and Currency Section -->
    <div class="flex justify-end mt-4">
        <table class="border border-black text-xs text-right">
            <thead>
            <tr class="border border-black">
                <th class="px-4 py-2 border">31.12.2023</th>
                <th class="px-4 py-2 border">31.12.2022</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="px-4 py-2 border">TSHS</td>
                <td class="px-4 py-2 border">TSHS</td>
            </tr>
            </tbody>
        </table>
    </div>


    <div class="w-full flex gap-4 p-2 bg-gray-100  rounded-lg">
        <div class="relative w-1/2 overflow-x-auto rounded-lg bg-white p-4">



            <!-- Initialize overall totals -->
            @php
                $overall_this_year_total = 0;
                $overall_last_year_total = 0;
                $overall_year_before_last_total = 0;
            @endphp

                <!-- Main Asset Accounts Table -->
            <table class="w-full text-sm text-left text-blue-50 mb-4">
                <thead class="text-xs text-black uppercase bg-blue-50 dark:text-white">
                <tr>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">INCOME CATEGORY</th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($income_accounts as $income_account)
                    @php
                        $this_year_amount = 0;
                        $last_year_amount = 0;
                        $year_before_last_amount = 0;

                        // Fetch the sub-category accounts for the current income category
                        $category_accounts = DB::table($income_account->category_name)->get();

                        foreach ($category_accounts as $category_account) {
                            // This year
                            $this_year_amount += DB::table('accounts')
                                ->where('sub_category_code', $category_account->sub_category_code)
                                ->whereYear('created_at', date('Y'))
                                ->sum('balance');

                            // Last year
                            $last_year_amount += DB::table('accounts')
                                ->where('sub_category_code', $category_account->sub_category_code)
                                ->whereYear('created_at', date('Y') - 1)
                                ->sum('balance');

                            // Year before last
                            $year_before_last_amount += DB::table('accounts')
                                ->where('sub_category_code', $category_account->sub_category_code)
                                ->whereYear('created_at', date('Y') - 2)
                                ->sum('balance');
                        }

                        // Update the overall totals
                        $overall_this_year_total += $this_year_amount;
                        $overall_last_year_total += $last_year_amount;
                        $overall_year_before_last_total += $year_before_last_amount;
                    @endphp

                    <tr class="border-b border-blue-200 text-black">
                        <td class="whitespace-nowrap border-r px-4 py-2 dark:border-neutral-500 text-sm w-1/4">
                            <a wire:click="setSubCode({{ $income_account->category_code }},'{{$income_account->category_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">
                                {{ ucwords(str_replace('_', ' ', $income_account->category_name)) }}
                            </a>
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 dark:border-neutral-500 text-right font-medium w-1/4">
                            {{ number_format($this_year_amount, 2) }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 dark:border-neutral-500 text-right font-medium w-1/4">
                            {{ number_format($last_year_amount, 2) }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 dark:border-neutral-500 text-right font-medium w-1/4">
                            {{ number_format($year_before_last_amount, 2) }}
                        </td>
                    </tr>
                @endforeach

                <!-- Income Totals -->
                <tr class="bg-gray-100 text-black uppercase">
                    <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total Income</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                </tr>

                <!-- Expense Categories Header -->
                <tr class="bg-blue-50 text-black uppercase">
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">EXPENSE CATEGORY</th>
                    <th colspan="3" class="px-6 py-4 dark:border-neutral-500 text-right w-3/4"></th>
                </tr>

                @foreach($expense_accounts as $expense_account)
                    @php
                        $category_accounts = DB::table($expense_account->category_name)->get();
                        $this_year_total = 0;
                        $last_year_total = 0;
                        $year_before_last_total = 0;

                        foreach ($category_accounts as $category_account) {
                            // This year's balance
                            $this_year_total += DB::table('accounts')
                                ->where('sub_category_code', $category_account->sub_category_code)
                                ->whereYear('created_at', now()->year)
                                ->value('balance');

                            // Last year's balance
                            $last_year_total += DB::table('accounts')
                                ->where('sub_category_code', $category_account->sub_category_code)
                                ->whereYear('created_at', now()->subYear(1)->year)
                                ->value('balance');

                            // Year before last's balance
                            $year_before_last_total += DB::table('accounts')
                                ->where('sub_category_code', $category_account->sub_category_code)
                                ->whereYear('created_at', now()->subYear(2)->year)
                                ->value('balance');
                        }

                        // Update overall totals
                        $overall_this_year_total += $this_year_total;
                        $overall_last_year_total += $last_year_total;
                        $overall_year_before_last_total += $year_before_last_total;
                    @endphp

                    <tr class="border-b border-blue-200 text-black">
                        <td class="whitespace-nowrap border-r px-4 py-2 dark:border-neutral-500 text-sm w-2/5">
                            <a wire:click="setSubCode({{ $income_account->category_code }},'{{$income_account->category_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">
                                {{ ucwords(str_replace('_', ' ', $expense_account->category_name)) }}
                            </a>
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 dark:border-neutral-500 text-right font-medium w-1/5">
                            {{ number_format($this_year_total, 2) }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 dark:border-neutral-500 text-right font-medium w-1/5">
                            {{ number_format($last_year_total, 2) }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 dark:border-neutral-500 text-right font-medium w-1/5">
                            {{ number_format($year_before_last_total, 2) }}
                        </td>
                    </tr>
                @endforeach

                <!-- Expense Totals -->
                <tr class="bg-gray-100 text-black uppercase">
                    <td class="whitespace-nowrap px-6 py-4 font-medium w-2/5">Total Expenses</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/5">{{ number_format($overall_this_year_total, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/5">{{ number_format($overall_last_year_total, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/5">{{ number_format($overall_year_before_last_total, 2) }}</td>
                </tr>
                </tbody>

                <!-- Summary Totals -->
                <tfoot>
                <!-- Overall Total Before Tax -->
                <tr class="bg-gray-100 text-black uppercase">
                    <td class="whitespace-nowrap px-6 py-4 font-medium w-2/5">Overall Total Before Tax</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/5">{{ number_format($overall_this_year_total, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/5">{{ number_format($overall_last_year_total, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/5">{{ number_format($overall_year_before_last_total, 2) }}</td>
                </tr>

                <!-- Taxes (Assuming 10% tax rate for example) -->
                @php
                    $tax_rate = 0.30; // Example tax rate
                    $tax_this_year = $overall_this_year_total * $tax_rate;
                    $tax_last_year = $overall_last_year_total * $tax_rate;
                    $tax_year_before_last = $overall_year_before_last_total * $tax_rate;
                @endphp
                <tr class="bg-gray-200 text-black uppercase">
                    <td class="whitespace-nowrap px-6 py-4 font-medium w-2/5">Total Tax (30%)</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/5">{{ number_format($tax_this_year, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/5">{{ number_format($tax_last_year, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/5">{{ number_format($tax_year_before_last, 2) }}</td>
                </tr>

                <!-- Surplus After Tax -->
                @php
                    $surplus_this_year = $overall_this_year_total - $tax_this_year;
                    $surplus_last_year = $overall_last_year_total - $tax_last_year;
                    $surplus_year_before_last = $overall_year_before_last_total - $tax_year_before_last;
                @endphp
                <tr class="bg-gray-100 text-black uppercase">
                    <td class="whitespace-nowrap px-6 py-4 font-medium w-2/5">Surplus After Tax</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/5">{{ number_format($surplus_this_year, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/5">{{ number_format($surplus_last_year, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/5">{{ number_format($surplus_year_before_last, 2) }}</td>
                </tr>
                </tfoot>


            </table>
        </div>


        <div class="relative w-1/2 overflow-x-auto rounded-lg bg-white p-4">

            @if($this->selected_sub_category_code)

                <table class="w-full text-left border-collapse text-sm">
                    <tbody>
                    <!-- MTIRIRIKO WA FEDHA TOKANA NA SHUGHULI ZA UENDESHAJI Section -->
                    <tr>
                        <td colspan="1" class="bg-blue-50 font-bold px-4 py-2 border">{{ucwords(str_replace('_', ' ', $this->category_name))}}</td>
                        <td class="bg-blue-50 font-bold px-4 py-2 border">DEBIT</td>
                        <td class="bg-blue-50 font-bold px-4 py-2 border">CREDIT</td>
                    </tr>
                    @php
                        $overall_this_year_total = DB::table('accounts')
                                    ->where('sub_category_code', $this->selected_sub_category_code)
                                    ->where('account_level',2)
                                    ->whereYear('created_at', date('Y'))
                                    ->sum('balance');
                        $second_level = DB::table('accounts')
                                    ->where('category_code', $this->selected_sub_category_code)
                                    ->where('account_level',2)
                                    ->whereYear('created_at', date('Y'))->get();

                        //dd($second_level);
                    @endphp

                    @foreach($second_level as $income_account)


                        <tr>
                            <td class="px-4 py-2 border">
                                <a wire:click="setSubCode2({{ $income_account->sub_category_code }},'{{$income_account->account_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">
                                    {{ ucwords(str_replace('_', ' ', $income_account->account_name)) }}
                                </a>
                            </td>
                            <td class="px-4 py-2 border text-right">
                                {{ number_format($income_account->balance, 2) }}
                            </td>
                            <td class="px-4 py-2 border text-right">

                            </td>
                        </tr>

                    @endforeach

                    <tr class="font-bold">
                        <td class="px-4 py-2 border">TOTAL</td>
                        <td class="px-4 py-2 border text-right">
                            {{ number_format($overall_this_year_total, 2) }}
                        </td>
                        <td class="px-4 py-2 border text-right">0.00</td>
                    </tr>

                    <tr>
                        <td colspan="3" class="bg-gray-50 font-bold px-4 py-2 border"></td>
                    </tr>
                    </tbody>
                </table>

            @endif




            @if($this->selected_sub_category_code2)
                <table class="w-full text-left border-collapse text-sm">
                    <tbody>
                    <!-- MTIRIRIKO WA FEDHA TOKANA NA SHUGHULI ZA UENDESHAJI Section -->
                    <tr>
                        <td colspan="1" class="bg-blue-50 font-bold px-4 py-2 border">{{ucwords(str_replace('_', ' ', $this->category_name2))}} - {{$this->selected_sub_category_code2}}</td>
                        <td class="bg-blue-50 font-bold px-4 py-2 border">DEBIT</td>
                        <td class="bg-blue-50 font-bold px-4 py-2 border">CREDIT</td>
                    </tr>
                    @php
                        $overall_this_year_total = DB::table('sub_accounts')
                                    ->where('sub_category_code', $this->selected_sub_category_code2)
                                    //->where('account_level',3)
                                    ->whereYear('created_at', date('Y'))
                                    ->sum('balance');
                        $second_level2 = DB::table('sub_accounts')
                                    ->where('sub_category_code', $this->selected_sub_category_code2)
                                    //->where('account_level',3)
                                    ->whereYear('created_at', date('Y'))->get();

                        //dd($second_level);
                    @endphp

                    @foreach($second_level2 as $income_account)


                        <tr>
                            <td class="px-4 py-2 border">
                                <a wire:click="setSubCode3({{ $income_account->sub_category_code }},'{{$income_account->account_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">
                                    {{ ucwords(str_replace('_', ' ', $income_account->account_name)) }}
                                </a>
                            </td>
                            <td class="px-4 py-2 border text-right">
                                {{ number_format($income_account->balance, 2) }}
                            </td>
                            <td class="px-4 py-2 border text-right">

                            </td>
                        </tr>

                    @endforeach

                    <tr class="font-bold">
                        <td class="px-4 py-2 border">TOTAL</td>
                        <td class="px-4 py-2 border text-right">
                            {{ number_format($overall_this_year_total, 2) }}
                        </td>
                        <td class="px-4 py-2 border text-right">0.00</td>
                    </tr>

                    <tr>
                        <td colspan="3" class="bg-gray-50 font-bold px-4 py-2 border"></td>
                    </tr>
                    </tbody>
                </table>
            @endif



        </div>


    </div>




    @if($showPostingModel)
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-2/3">
                <h2 class="text-sm font-bold">Transactions</h2>
                <livewire:accounting.view-transactions/>

                <div class="mt-4 flex justify-end">
                    <button wire:click="closePostingModel" class="bg-gray-300 text-black text-xs py-2 px-4 rounded mr-2">
                        Close
                    </button>

                </div>
            </div>
        </div>

    @endif


</div>
