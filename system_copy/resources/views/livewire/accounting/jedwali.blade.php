<div class="bg-white p-4 rounded-lg">

    <!-- Title Section -->
    <div class="text-center">
        <h2 class="text-lg font-bold uppercase">NBC SACCOS LTD</h2>
        <h3 class="text-base font-semibold uppercase">TAARIFA YA MAPATO NA MATUMIZI KWA KIPINDI KINACHOISHIA y
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


    <div class="w-full flex gap-4 p-2 bg-gray-100  rounded-lg grid grid-cols-2 gap-4">

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
                            JED L1 : 1
                        </div>
                        <div>
                            ASSETS CATEGORY
                        </div>

                        </th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
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
                <tr class="bg-gray-100 text-black uppercase w-full">
                    <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total Assets</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                </tr>


            </table>
        </div>


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
                            JED L1 : 2
                        </div>
                        <div>
                            LIABILITY CATEGORY
                        </div>
                        </th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
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
                    <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total Liability</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                </tr>


            </table>
        </div>


        <div class="overflow-x-auto rounded-lg bg-white p-4">

            <!-- Session Messages -->
            @if (session()->has('message'))
                <div class="bg-green-500 text-white p-4 rounded">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-500 text-white p-4 rounded">
                    {{ session('error') }}
                </div>
            @endif

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
                            JED L1 : 3
                        </div>
                        <div>
                            CAPITAL CATEGORY
                        </div>
                        </th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($capital_accounts as $income_account)
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
                    <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total Capital</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                </tr>


            </table>
        </div>


        <div class="overflow-x-auto rounded-lg bg-white p-4">

            <!-- Session Messages -->
            @if (session()->has('message'))
                <div class="bg-green-500 text-white p-4 rounded">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-500 text-white p-4 rounded">
                    {{ session('error') }}
                </div>
            @endif

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
                            JED L1 : 4
                        </div>
                        <div>
                            INCOME CATEGORY
                        </div>
                        </th>
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


            </table>
        </div>


        <div class="overflow-x-auto rounded-lg bg-white p-4">

            <!-- Session Messages -->
            @if (session()->has('message'))
                <div class="bg-green-500 text-white p-4 rounded">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-500 text-white p-4 rounded">
                    {{ session('error') }}
                </div>
            @endif

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
                            JED L1 : 5
                        </div>
                        <div>
                            EXPENSES CATEGORY
                        </div>
                        </th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                    <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($expense_accounts as $income_account)
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
                    <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                </tr>


            </table>
        </div>


    </div>






    <div class="w-full flex gap-4 p-2 bg-gray-100  rounded-lg grid grid-cols-2 gap-4 mt-6">

        <div class="overflow-x-auto rounded-lg bg-white p-4">



            <!-- Initialize overall totals -->
            @php
                $overall_this_year_total = 0;
                $overall_last_year_total = 0;
                $overall_year_before_last_total = 0;
                $key = 1;
            @endphp

                <!-- Main Asset Accounts Table -->

            @foreach($asset_accounts as $income_account)
                @php

                    $category_accounts = DB::table($income_account->category_name)->get();

                @endphp




                <table class="w-full text-sm text-left text-blue-50 mb-4">
                    <thead class="text-xs text-black uppercase bg-blue-50 dark:text-white">
                    <tr>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">
                            <div>
                                JED L2 : {{$key}}
                            </div>
                            <div>
                                {{ ucwords(str_replace('_', ' ', $income_account->category_name)) }}
                            </div>

                        </th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($category_accounts as $category_account)


                        @php

                            $this_year_amount = 0;
                            $last_year_amount = 0;
                            $year_before_last_amount = 0;


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

                            $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;
                            $overall_year_before_last_total += $year_before_last_amount;
                        @endphp

                        <tr class="border-b border-blue-200 text-black">
                            <td class="whitespace-nowrap border-r px-4 py-2 dark:border-neutral-500 text-sm w-1/4">
                                <a wire:click="setSubCode2({{ $category_account->sub_category_code }},'{{$category_account->sub_category_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">
                                    {{ ucwords(str_replace('_', ' ', $category_account->sub_category_name)) }}
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
                        <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                    </tr>


                </table>



                @php
                    $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;
                            $overall_year_before_last_total += $year_before_last_amount;

                @endphp
                @php $key++; @endphp
            @endforeach


        </div>

        <div class="overflow-x-auto rounded-lg bg-white p-4">



            <!-- Initialize overall totals -->
            @php
                $overall_this_year_total = 0;
                $overall_last_year_total = 0;
                $overall_year_before_last_total = 0;
                $key = 1;
            @endphp

                <!-- Main Asset Accounts Table -->

            @foreach($liability_accounts as $income_account)
                @php

                    $category_accounts = DB::table($income_account->category_name)->get();

                @endphp




                <table class="w-full text-sm text-left text-blue-50 mb-4">
                    <thead class="text-xs text-black uppercase bg-blue-50 dark:text-white">
                    <tr>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">
                            <div>
                                JED L2 : {{$key}}
                            </div>
                            <div>
                                {{ ucwords(str_replace('_', ' ', $income_account->category_name)) }}
                            </div>

                        </th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($category_accounts as $category_account)


                        @php

                            $this_year_amount = 0;
                            $last_year_amount = 0;
                            $year_before_last_amount = 0;


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

                            $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;
                            $overall_year_before_last_total += $year_before_last_amount;
                        @endphp

                        <tr class="border-b border-blue-200 text-black">
                            <td class="whitespace-nowrap border-r px-4 py-2 dark:border-neutral-500 text-sm w-1/4">
                                <a wire:click="setSubCode2({{ $category_account->sub_category_code }},'{{$category_account->sub_category_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">
                                    {{ ucwords(str_replace('_', ' ', $category_account->sub_category_name)) }}
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
                        <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                    </tr>


                </table>



                @php
                    $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;
                            $overall_year_before_last_total += $year_before_last_amount;

                @endphp
                @php $key++; @endphp
            @endforeach


        </div>

        <div class="overflow-x-auto rounded-lg bg-white p-4">



            <!-- Initialize overall totals -->
            @php
                $overall_this_year_total = 0;
                $overall_last_year_total = 0;
                $overall_year_before_last_total = 0;
                $key = 1;
            @endphp

                <!-- Main Asset Accounts Table -->

            @foreach($capital_accounts as $income_account)
                @php

                    $category_accounts = DB::table($income_account->category_name)->get();

                @endphp




                <table class="w-full text-sm text-left text-blue-50 mb-4">
                    <thead class="text-xs text-black uppercase bg-blue-50 dark:text-white">
                    <tr>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">
                            <div>
                                JED L2 : {{$key}}
                            </div>
                            <div>
                                {{ ucwords(str_replace('_', ' ', $income_account->category_name)) }}
                            </div>

                        </th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($category_accounts as $category_account)


                        @php

                            $this_year_amount = 0;
                            $last_year_amount = 0;
                            $year_before_last_amount = 0;


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

                            $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;
                            $overall_year_before_last_total += $year_before_last_amount;
                        @endphp

                        <tr class="border-b border-blue-200 text-black">
                            <td class="whitespace-nowrap border-r px-4 py-2 dark:border-neutral-500 text-sm w-1/4">
                                <a wire:click="setSubCode2({{ $category_account->sub_category_code }},'{{$category_account->sub_category_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">
                                    {{ ucwords(str_replace('_', ' ', $category_account->sub_category_name)) }}
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
                        <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                    </tr>


                </table>



                @php
                    $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;
                            $overall_year_before_last_total += $year_before_last_amount;

                @endphp
                @php $key++; @endphp
            @endforeach


        </div>

        <div class="overflow-x-auto rounded-lg bg-white p-4">



            <!-- Initialize overall totals -->
            @php
                $overall_this_year_total = 0;
                $overall_last_year_total = 0;
                $overall_year_before_last_total = 0;
                $key = 1;
            @endphp

                <!-- Main Asset Accounts Table -->

            @foreach($income_accounts as $income_account)
                @php

                    $category_accounts = DB::table($income_account->category_name)->get();

                @endphp




                <table class="w-full text-sm text-left text-blue-50 mb-4">
                    <thead class="text-xs text-black uppercase bg-blue-50 dark:text-white">
                    <tr>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">
                            <div>
                                JED L2 : {{$key}}
                            </div>
                            <div>
                                {{ ucwords(str_replace('_', ' ', $income_account->category_name)) }}
                            </div>

                        </th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                        <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($category_accounts as $category_account)


                        @php

                            $this_year_amount = 0;
                            $last_year_amount = 0;
                            $year_before_last_amount = 0;


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

                            $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;
                            $overall_year_before_last_total += $year_before_last_amount;
                        @endphp

                        <tr class="border-b border-blue-200 text-black">
                            <td class="whitespace-nowrap border-r px-4 py-2 dark:border-neutral-500 text-sm w-1/4">
                                <a wire:click="setSubCode2({{ $category_account->sub_category_code }},'{{$category_account->sub_category_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">
                                    {{ ucwords(str_replace('_', ' ', $category_account->sub_category_name)) }}
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
                        <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                    </tr>


                </table>



                @php
                    $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;
                            $overall_year_before_last_total += $year_before_last_amount;

                @endphp
                @php $key++; @endphp
            @endforeach


        </div>

        <div class="overflow-x-auto rounded-lg bg-white p-4">



            <!-- Initialize overall totals -->
            @php
                $overall_this_year_total = 0;
                $overall_last_year_total = 0;
                $overall_year_before_last_total = 0;
                $key = 1;
            @endphp

                <!-- Main Asset Accounts Table -->

            @foreach($expense_accounts as $income_account)
                @php

                    $category_accounts = DB::table($income_account->category_name)->get();

                @endphp




                    <table class="w-full text-sm text-left text-blue-50 mb-4">
                        <thead class="text-xs text-black uppercase bg-blue-50 dark:text-white">
                        <tr>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">
                                <div>
                                    JED L2 : {{$key}}
                                </div>
                                <div>
                                    {{ ucwords(str_replace('_', ' ', $income_account->category_name)) }}
                                </div>

                            </th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($category_accounts as $category_account)


                            @php

                                $this_year_amount = 0;
                                $last_year_amount = 0;
                                $year_before_last_amount = 0;


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

                                $overall_this_year_total += $this_year_amount;
                                $overall_last_year_total += $last_year_amount;
                                $overall_year_before_last_total += $year_before_last_amount;
                            @endphp

                            <tr class="border-b border-blue-200 text-black">
                                <td class="whitespace-nowrap border-r px-4 py-2 dark:border-neutral-500 text-sm w-1/4">
                                    <a wire:click="setSubCode2({{ $category_account->sub_category_code }},'{{$category_account->sub_category_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">
                                       {{ ucwords(str_replace('_', ' ', $category_account->sub_category_name)) }}
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
                            <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                        </tr>


                    </table>



                @php
                    $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;
                            $overall_year_before_last_total += $year_before_last_amount;

                @endphp
                @php $key++; @endphp
            @endforeach


        </div>


    </div>





    <div class="w-full flex gap-4 p-2 bg-gray-100  rounded-lg grid grid-cols-2 gap-4 mt-6">


        <div class="overflow-x-auto rounded-lg bg-white p-4">



            <!-- Initialize overall totals -->
            @php
                $overall_this_year_total = 0;
                $overall_last_year_total = 0;
                $overall_year_before_last_total = 0;
            @endphp

                <!-- Main Asset Accounts Table -->

            @foreach($asset_accounts as $income_account)
                @php

                    $category_accounts = DB::table($income_account->category_name)->get();

                @endphp

                @foreach ($category_accounts as $category_account)


                    <table class="w-full text-sm text-left text-blue-50 mb-4">
                        <thead class="text-xs text-black uppercase bg-blue-50 dark:text-white">
                        <tr>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">{{ ucwords(str_replace('_', ' ', $category_account->sub_category_name)) }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach(\Illuminate\Support\Facades\DB::table('sub_accounts')->where('sub_category_code',$category_account->sub_category_code)->get() as $account)


                            @php

                                $this_year_amount = 0;
                                $last_year_amount = 0;
                                $year_before_last_amount = 0;


                                // This year
                                $this_year_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y'))
                                ->sum('balance');

                                // Last year
                                $last_year_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y') - 1)
                                ->sum('balance');

                                // Year before last
                                $year_before_last_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y') - 2)
                                ->sum('balance');

                                $overall_this_year_total += $this_year_amount;
                                $overall_last_year_total += $last_year_amount;
                                $overall_year_before_last_total += $year_before_last_amount;
                            @endphp

                            <tr class="border-b border-blue-200 text-black">
                                <td class="whitespace-nowrap border-r px-4 py-2 dark:border-neutral-500 text-sm w-1/4">
                                    <a wire:click="setSubCode3({{ $account->sub_category_code }},'{{$account->account_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">

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
                            <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                        </tr>


                    </table>

                @endforeach

                @php
                    $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;
                            $overall_year_before_last_total += $year_before_last_amount;

                @endphp
            @endforeach


        </div>

        <div class="overflow-x-auto rounded-lg bg-white p-4">



            <!-- Initialize overall totals -->
            @php
                $overall_this_year_total = 0;
                $overall_last_year_total = 0;
                $overall_year_before_last_total = 0;
            @endphp

                <!-- Main Asset Accounts Table -->

            @foreach($liability_accounts as $income_account)
                @php

                    $category_accounts = DB::table($income_account->category_name)->get();

                @endphp

                @foreach ($category_accounts as $category_account)


                    <table class="w-full text-sm text-left text-blue-50 mb-4">
                        <thead class="text-xs text-black uppercase bg-blue-50 dark:text-white">
                        <tr>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">{{ ucwords(str_replace('_', ' ', $category_account->sub_category_name)) }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach(\Illuminate\Support\Facades\DB::table('sub_accounts')->where('sub_category_code',$category_account->sub_category_code)->get() as $account)


                            @php

                                $this_year_amount = 0;
                                $last_year_amount = 0;
                                $year_before_last_amount = 0;


                                // This year
                                $this_year_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y'))
                                ->sum('balance');

                                // Last year
                                $last_year_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y') - 1)
                                ->sum('balance');

                                // Year before last
                                $year_before_last_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y') - 2)
                                ->sum('balance');

                                $overall_this_year_total += $this_year_amount;
                                $overall_last_year_total += $last_year_amount;
                                $overall_year_before_last_total += $year_before_last_amount;
                            @endphp

                            <tr class="border-b border-blue-200 text-black">
                                <td class="whitespace-nowrap border-r px-4 py-2 dark:border-neutral-500 text-sm w-1/4">
                                    <a wire:click="setSubCode3({{ $account->sub_category_code }},'{{$account->account_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">

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
                            <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                        </tr>


                    </table>

                @endforeach

                @php
                    $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;
                            $overall_year_before_last_total += $year_before_last_amount;

                @endphp
            @endforeach


        </div>

        <div class="overflow-x-auto rounded-lg bg-white p-4">



            <!-- Initialize overall totals -->
            @php
                $overall_this_year_total = 0;
                $overall_last_year_total = 0;
                $overall_year_before_last_total = 0;
            @endphp

                <!-- Main Asset Accounts Table -->

            @foreach($capital_accounts as $income_account)
                @php

                    $category_accounts = DB::table($income_account->category_name)->get();

                @endphp

                @foreach ($category_accounts as $category_account)


                    <table class="w-full text-sm text-left text-blue-50 mb-4">
                        <thead class="text-xs text-black uppercase bg-blue-50 dark:text-white">
                        <tr>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">{{ ucwords(str_replace('_', ' ', $category_account->sub_category_name)) }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach(\Illuminate\Support\Facades\DB::table('sub_accounts')->where('sub_category_code',$category_account->sub_category_code)->get() as $account)


                            @php

                                $this_year_amount = 0;
                                $last_year_amount = 0;
                                $year_before_last_amount = 0;


                                // This year
                                $this_year_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y'))
                                ->sum('balance');

                                // Last year
                                $last_year_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y') - 1)
                                ->sum('balance');

                                // Year before last
                                $year_before_last_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y') - 2)
                                ->sum('balance');

                                $overall_this_year_total += $this_year_amount;
                                $overall_last_year_total += $last_year_amount;
                                $overall_year_before_last_total += $year_before_last_amount;
                            @endphp

                            <tr class="border-b border-blue-200 text-black">
                                <td class="whitespace-nowrap border-r px-4 py-2 dark:border-neutral-500 text-sm w-1/4">
                                    <a wire:click="setSubCode3({{ $account->sub_category_code }},'{{$account->account_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">

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
                            <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                        </tr>


                    </table>

                @endforeach

                @php
                    $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;
                            $overall_year_before_last_total += $year_before_last_amount;

                @endphp
            @endforeach


        </div>

        <div class="overflow-x-auto rounded-lg bg-white p-4">



            <!-- Initialize overall totals -->
            @php
                $overall_this_year_total = 0;
                $overall_last_year_total = 0;
                $overall_year_before_last_total = 0;
            @endphp

                <!-- Main Asset Accounts Table -->

            @foreach($income_accounts as $income_account)
                @php

                    $category_accounts = DB::table($income_account->category_name)->get();

                @endphp

                @foreach ($category_accounts as $category_account)


                    <table class="w-full text-sm text-left text-blue-50 mb-4">
                        <thead class="text-xs text-black uppercase bg-blue-50 dark:text-white">
                        <tr>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">{{ ucwords(str_replace('_', ' ', $category_account->sub_category_name)) }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach(\Illuminate\Support\Facades\DB::table('sub_accounts')->where('sub_category_code',$category_account->sub_category_code)->get() as $account)


                            @php

                                $this_year_amount = 0;
                                $last_year_amount = 0;
                                $year_before_last_amount = 0;


                                // This year
                                $this_year_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y'))
                                ->sum('balance');

                                // Last year
                                $last_year_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y') - 1)
                                ->sum('balance');

                                // Year before last
                                $year_before_last_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y') - 2)
                                ->sum('balance');

                                $overall_this_year_total += $this_year_amount;
                                $overall_last_year_total += $last_year_amount;
                                $overall_year_before_last_total += $year_before_last_amount;
                            @endphp

                            <tr class="border-b border-blue-200 text-black">
                                <td class="whitespace-nowrap border-r px-4 py-2 dark:border-neutral-500 text-sm w-1/4">
                                    <a wire:click="setSubCode3({{ $account->sub_category_code }},'{{$account->account_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">

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
                            <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                        </tr>


                    </table>

                @endforeach

                @php
                    $overall_this_year_total += $this_year_amount;
                            $overall_last_year_total += $last_year_amount;
                            $overall_year_before_last_total += $year_before_last_amount;

                @endphp
            @endforeach


        </div>

        <div class="overflow-x-auto rounded-lg bg-white p-4">



            <!-- Initialize overall totals -->
            @php
                $overall_this_year_total = 0;
                $overall_last_year_total = 0;
                $overall_year_before_last_total = 0;
            @endphp

                <!-- Main Asset Accounts Table -->

                @foreach($expense_accounts as $income_account)
                    @php

                          $category_accounts = DB::table($income_account->category_name)->get();

                    @endphp

                    @foreach ($category_accounts as $category_account)


                    <table class="w-full text-sm text-left text-blue-50 mb-4">
                        <thead class="text-xs text-black uppercase bg-blue-50 dark:text-white">
                        <tr>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 w-1/4">{{ ucwords(str_replace('_', ' ', $category_account->sub_category_name)) }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 1 }}</th>
                            <th scope="col" class="border-r px-6 py-4 dark:border-neutral-500 text-right w-1/4">{{ date('Y') - 2 }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach(\Illuminate\Support\Facades\DB::table('sub_accounts')->where('sub_category_code',$category_account->sub_category_code)->get() as $account)


                            @php

                                $this_year_amount = 0;
                                $last_year_amount = 0;
                                $year_before_last_amount = 0;


                                // This year
                                $this_year_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y'))
                                ->sum('balance');

                                // Last year
                                $last_year_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y') - 1)
                                ->sum('balance');

                                // Year before last
                                $year_before_last_amount += DB::table('sub_accounts')
                                ->where('sub_category_code', $account->sub_category_code)
                                ->whereYear('created_at', date('Y') - 2)
                                ->sum('balance');

                                $overall_this_year_total += $this_year_amount;
                                $overall_last_year_total += $last_year_amount;
                                $overall_year_before_last_total += $year_before_last_amount;
                            @endphp

                        <tr class="border-b border-blue-200 text-black">
                            <td class="whitespace-nowrap border-r px-4 py-2 dark:border-neutral-500 text-sm w-1/4">
                                <a wire:click="setSubCode3({{ $account->sub_category_code }},'{{$account->account_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">

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
                            <td class="whitespace-nowrap px-6 py-4 font-medium w-1/4">Total</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_this_year_total, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_last_year_total, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold w-1/4">{{ number_format($overall_year_before_last_total, 2) }}</td>
                        </tr>


                    </table>

                    @endforeach

                    @php
                        $overall_this_year_total += $this_year_amount;
                                $overall_last_year_total += $last_year_amount;
                                $overall_year_before_last_total += $year_before_last_amount;

                    @endphp
                @endforeach


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
