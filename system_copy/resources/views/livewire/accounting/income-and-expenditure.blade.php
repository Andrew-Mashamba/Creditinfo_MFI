<div>

<div class="p-4 shadow-md sm:rounded-lg bg-gray-100 flex">










    <div class="w-2/3 mx-auto bg-white p-6 shadow-md">
        <!-- Title Section -->
        <div class="text-center">
            <h2 class="text-lg font-bold uppercase text-xs">NBC SACCOS LTD</h2>
            <h3 class="text-base font-semibold uppercase text-xs">MCHANGANUO WA MAPATO NA MATUMIZI KWA KIPINDI KINACHOISHIA 30.09. 2024</h3>
        </div>

        <!-- Date and Currency Section -->
        <div class="flex justify-end mt-4">
            <table class="border border-black text-xs text-right text-xs">
                <thead>
                <tr class="border border-black">
                    <th class="px-4 py-2 border text-xs">31.12.2024</th>
                    <th class="px-4 py-2 border text-xs">31.12.2023</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="px-4 py-2 border text-xs">TSHS</td>
                    <td class="px-4 py-2 border text-xs">TSHS</td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Financial Data Table -->
        <div class="mt-6">
            <table class="w-full text-left border-collapse text-sm">
                <tbody>
                <!-- MTIRIRIKO WA FEDHA TOKANA NA SHUGHULI ZA UENDESHAJI Section -->
                <tr>
                    <td colspan="2" class="bg-blue-50 font-bold px-4 py-2 border"></td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border"></td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-xs">ESTIMATES 75%</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-xs">ACTUAL 75%</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-xs">DIFFERENCE</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-xs">%</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-xs">BUDGET 2024</td>
                </tr>
                <tr>
                    <td colspan="2" class="bg-blue-50 font-bold px-4 py-2 border text-xs">INCOMES</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-xs">CODE</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-xs">YEAR 2024</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-xs"></td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-xs">Tshs</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-xs">(%)</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-xs">Tshs</td>
                </tr>

                @php
                    $income_accounts = DB::table('accounts')
                        ->where('major_category_code', 4000)
                        ->get();

                    $expense_accounts = DB::table('accounts')
                        ->where('major_category_code', 5000)
                        ->get();

                    $total_income = 0;
                    $total_expenses = 0;
                @endphp

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th>Current Year</th>
                                <th>Last Year</th>
                                <th>Year Before Last</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="font-weight-bold">Income</td>
                            </tr>
                            @foreach($income_accounts as $account)
                                @php
                                    $current_year = DB::table('general_ledger')
                                        ->where('account_code', $account->account_code)
                                        ->whereYear('transaction_date', date('Y'))
                                        ->sum('credit') - DB::table('general_ledger')
                                        ->where('account_code', $account->account_code)
                                        ->whereYear('transaction_date', date('Y'))
                                        ->sum('debit');

                                    $last_year = DB::table('general_ledger')
                                        ->where('account_code', $account->account_code)
                                        ->whereYear('transaction_date', date('Y') - 1)
                                        ->sum('credit') - DB::table('general_ledger')
                                        ->where('account_code', $account->account_code)
                                        ->whereYear('transaction_date', date('Y') - 1)
                                        ->sum('debit');

                                    $year_before_last = DB::table('general_ledger')
                                        ->where('account_code', $account->account_code)
                                        ->whereYear('transaction_date', date('Y') - 2)
                                        ->sum('credit') - DB::table('general_ledger')
                                        ->where('account_code', $account->account_code)
                                        ->whereYear('transaction_date', date('Y') - 2)
                                        ->sum('debit');

                                    $total_income += $current_year;
                                @endphp
                                <tr>
                                    <td>{{ $account->account_name }}</td>
                                    <td>{{ number_format($current_year, 2) }}</td>
                                    <td>{{ number_format($last_year, 2) }}</td>
                                    <td>{{ number_format($year_before_last, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="font-weight-bold">
                                <td>Total Income</td>
                                <td>{{ number_format($total_income, 2) }}</td>
                                <td></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td colspan="4" class="font-weight-bold">Expenses</td>
                            </tr>
                            @foreach($expense_accounts as $account)
                                @php
                                    $current_year = DB::table('general_ledger')
                                        ->where('account_code', $account->account_code)
                                        ->whereYear('transaction_date', date('Y'))
                                        ->sum('debit') - DB::table('general_ledger')
                                        ->where('account_code', $account->account_code)
                                        ->whereYear('transaction_date', date('Y'))
                                        ->sum('credit');

                                    $last_year = DB::table('general_ledger')
                                        ->where('account_code', $account->account_code)
                                        ->whereYear('transaction_date', date('Y') - 1)
                                        ->sum('debit') - DB::table('general_ledger')
                                        ->where('account_code', $account->account_code)
                                        ->whereYear('transaction_date', date('Y') - 1)
                                        ->sum('credit');

                                    $year_before_last = DB::table('general_ledger')
                                        ->where('account_code', $account->account_code)
                                        ->whereYear('transaction_date', date('Y') - 2)
                                        ->sum('debit') - DB::table('general_ledger')
                                        ->where('account_code', $account->account_code)
                                        ->whereYear('transaction_date', date('Y') - 2)
                                        ->sum('credit');

                                    $total_expenses += $current_year;
                                @endphp
                                <tr>
                                    <td>{{ $account->account_name }}</td>
                                    <td>{{ number_format($current_year, 2) }}</td>
                                    <td>{{ number_format($last_year, 2) }}</td>
                                    <td>{{ number_format($year_before_last, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="font-weight-bold">
                                <td>Total Expenses</td>
                                <td>{{ number_format($total_expenses, 2) }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr class="font-weight-bold">
                                <td>Net Income/(Loss)</td>
                                <td>{{ number_format($total_income - $total_expenses, 2) }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <tr>
                    <td colspan="8" class="bg-gray-50 font-bold px-4 py-2 border"></td>
                </tr>

                <tr>
                    <td colspan="2" class="bg-blue-50 font-bold px-4 py-2 border text-xs">ZIADA KABLA YA KODI</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-right text-xs"></td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border  text-right text-xs">{{ number_format($total_income - $total_expenses, 2) }}</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border  text-right text-xs"> {{ number_format($total_income - $total_expenses, 2) }}</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border  text-right text-xs"> {{ number_format($total_income - $total_expenses, 2) }} </td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border  text-right text-xs">{{ number_format($total_income - $total_expenses, 2) }}</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-right text-xs">{{ number_format($total_income - $total_expenses, 2) }}</td>

                </tr>


                <tr>
                    <td colspan="2" class="bg-blue-50 font-bold px-4 py-2 border text-xs">Gharama za Kodi</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border"></td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-right text-xs">{{ number_format(($total_income - $total_expenses)*30/100, 2) }}</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-right text-xs">{{ number_format(($total_income - $total_expenses)*30/100, 2) }}</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-right text-xs">{{ number_format(($total_income - $total_expenses)*30/100, 2) }}</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-right text-xs">{{ number_format(($total_income - $total_expenses)*30/100, 2) }}</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-right text-xs">{{ number_format(($total_income - $total_expenses)*30/100, 2) }}</td>
                </tr>

                <tr>
                    <td colspan="2" class="bg-blue-50 font-bold px-4 py-2 border text-xs">ZIADA BAADA KODI</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border"></td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-right text-xs">{{ number_format(($total_income - $total_expenses)-($total_income - $total_expenses)*30/100, 2) }}</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-right text-xs">{{ number_format(($total_income - $total_expenses)-($total_income - $total_expenses)*30/100, 2) }}</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-right text-xs">{{ number_format(($total_income - $total_expenses)-($total_income - $total_expenses)*30/100, 2) }}</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-right text-xs">{{ number_format(($total_income - $total_expenses)-($total_income - $total_expenses)*30/100, 2) }}</td>
                    <td class="bg-blue-50 font-bold px-4 py-2 border text-right text-xs">{{ number_format(($total_income - $total_expenses)-($total_income - $total_expenses)*30/100, 2) }}</td>
                </tr>






                </tbody>
            </table>



        </div>
    </div>






    <div class="w-1/3 mx-auto bg-white p-6 shadow-md">

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
