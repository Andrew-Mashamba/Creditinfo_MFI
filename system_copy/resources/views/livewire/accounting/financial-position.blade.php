<div CLASS="bg-white">


    <!-- Title Section -->
    <div class="text-center">
        <h2 class="text-lg font-bold uppercase">NBC SACCOS LTD</h2>
        <h3 class="text-base font-semibold uppercase">TAARIFA YA MAPATO NA MATUMIZI KWA KIPINDI KINACHOISHIA x
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



    <div class="w-full flex p-4 gap-4">

        <div class="w-2/3 p-2 bg-gray-100  rounded-lg ">

            <h3 class="text-base font-semibold uppercase mt-4 mb-4">ASSETS</h3>

            <div class="overflow-x-auto rounded-lg bg-white p-4">


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
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">

                            <div>
                                CURRENT AND NON CURRENT ASSETS
                            </div>

                        </th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>

                    </tr>
                    </thead>
                    <tbody>
                    @foreach($asset_accounts as $income_account)
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


                            }

                            // Update the overall totals
                            $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;

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

                        </tr>
                    @endforeach

                    <!-- Income Totals -->
                    <tr class="bg-gray-100 text-black uppercase">
                        <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total Assets</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>

                    </tr>


                </table>
            </div>


            <h3 class="text-base font-semibold uppercase mt-4 mb-4">EQUITY AND LIABILITIES</h3>


            <div class="overflow-x-auto rounded-lg bg-white p-4">


                <!-- Initialize overall totals -->
                @php
                    $overall_this_year_total = 0;
                    $overall_last_year_total = 0;

                @endphp

                    <!-- Main Asset Accounts Table -->
                <table class="w-full text-sm text-left text-blue-50 mb-4">
                    <thead class="text-xs text-black uppercase bg-blue-50 dark:text-white">
                    <tr>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">

                            <div>
                                LIABILITIES
                            </div>
                        </th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>

                    </tr>
                    </thead>
                    <tbody>
                    @foreach($liability_accounts as $income_account)
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


                            }

                            // Update the overall totals
                            $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;

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

                        </tr>
                    @endforeach

                    <!-- Income Totals -->
                    <tr class="bg-gray-100 text-black uppercase">
                        <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total Liability</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>

                    </tr>


                </table>
            </div>


            <div class="overflow-x-auto rounded-lg bg-white p-4">


                <!-- Initialize overall totals -->
                @php
                    $overall_this_year_total1 = 0;
                    $overall_last_year_total1 = 0;

                @endphp

                    <!-- Main Asset Accounts Table -->
                <table class="w-full text-sm text-left text-blue-50 mb-4">
                    <thead class="text-xs text-black uppercase bg-blue-50 dark:text-white">
                    <tr>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">

                            <div>
                                EQUITY
                            </div>
                        </th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>

                    </tr>
                    </thead>
                    <tbody>
                    @foreach($capital_accounts as $income_account)
                        @php
                            $this_year_amount = 0;
                            $last_year_amount = 0;


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


                            }

                            // Update the overall totals
                            $overall_this_year_total1 += $this_year_amount;
                            $overall_last_year_total1 += $last_year_amount;

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

                        </tr>
                    @endforeach

                    <!-- Income Totals -->
                    <tr class="bg-gray-100 text-black uppercase">
                        <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total Capital</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total1, 2) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total1, 2) }}</td>

                    </tr>


                </table>
            </div>


            <div class="overflow-x-auto rounded-lg bg-white p-4">


                    <!-- Main Asset Accounts Table -->
                <table class="w-full text-sm text-left text-blue-50 mb-4">
                        <tr class="bg-gray-100 text-black uppercase">
                            <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">TOTAL EQUITY AND LIABILITIES x</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total1 + $overall_this_year_total, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total1 + $overall_last_year_total, 2) }}</td>

                        </tr>

                </table>

            </div>


        </div>

        <div class="w-1/3 bg-gray-100  rounded-lg p-2">



            <div class="bg-white p-2">

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
    </div>





































</div>
