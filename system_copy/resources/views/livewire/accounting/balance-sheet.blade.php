<div>


    <div class="w-full flex gap-4">

        <div class="w-2/3 mx-auto bg-white p-6 shadow-md">
            <!-- Title Section -->
            <div class="text-center">
                <h2 class="text-lg font-bold uppercase">NBC SACCOS LTD</h2>
                <h3 class="text-base font-semibold uppercase">OPENING BALANCE SHEET AS AT 01 JANUARY 2024</h3>
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

            <!-- Financial Data Table -->
            <div class="mt-6">
                <table class="w-full text-left border-collapse text-sm">
                    <tbody>
                    <!-- MTIRIRIKO WA FEDHA TOKANA NA SHUGHULI ZA UENDESHAJI Section -->
                    <tr>
                        <td colspan="1" class="bg-blue-50 font-bold px-4 py-2 border">ASSETS</td>
                        <td class="bg-blue-50 font-bold px-4 py-2 border">DEBIT</td>
                        <td class="bg-blue-50 font-bold px-4 py-2 border">CREDIT</td>
                    </tr>
                    @php
                        $overall_this_year_total = 0;
                    @endphp

                    @foreach($asset_accounts as $income_account)
                        @php
                            $this_year_amount = 0;
                            $last_year_amount = 0;
                            $year_before_last_amount = 0;

                            // Fetch the sub-category accounts for the current income category
                            $category_accounts = DB::table($income_account->category_name)->get();

                            foreach ($category_accounts as $category_account) {
                                // This year
                                $this_year_debit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y'))
                                    ->sum('debit');
                                $this_year_credit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y'))
                                    ->sum('credit');
                                $this_year_amount += ($this_year_debit - $this_year_credit);

                                // Last year
                                $last_year_debit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y') - 1)
                                    ->sum('debit');
                                $last_year_credit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y') - 1)
                                    ->sum('credit');
                                $last_year_amount += ($last_year_debit - $last_year_credit);

                                // Year before last
                                $year_before_last_debit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y') - 2)
                                    ->sum('debit');
                                $year_before_last_credit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y') - 2)
                                    ->sum('credit');
                                $year_before_last_amount += ($year_before_last_credit - $year_before_last_debit);
                            }

                            // Update the overall totals
                            $overall_this_year_total += $this_year_amount;
                        @endphp



                        <tr>
                            <td class="px-4 py-2 border">
                                <a  wire:click="setSubCode({{ $income_account->category_code }},'{{$income_account->category_name}}')" class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm">
                                    {{ ucwords(str_replace('_', ' ', $income_account->category_name)) }}
                                </a>
                            </td>
                            <td class="px-4 py-2 border text-right">
                                {{ number_format($this_year_amount, 2) }}
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





                    <tr>
                        <td colspan="1" class="bg-blue-50 font-bold px-4 py-2 border">LIABILITIES</td>
                        <td class="bg-blue-50 font-bold px-4 py-2 border">DEBIT</td>
                        <td class="bg-blue-50 font-bold px-4 py-2 border">CREDIT</td>
                    </tr>
                    @php
                        $overall_this_year_total = 0;
                    @endphp

                    @foreach($liability_accounts as $income_account)
                        @php
                            $this_year_amount = 0;
                            $last_year_amount = 0;
                            $year_before_last_amount = 0;

                            // Fetch the sub-category accounts for the current income category
                            $category_accounts = DB::table($income_account->category_name)->get();

                            foreach ($category_accounts as $category_account) {
                                // This year
                                $this_year_debit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y'))
                                    ->sum('debit');
                                $this_year_credit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y'))
                                    ->sum('credit');
                                $this_year_amount += ($this_year_credit - $this_year_debit);

                                // Last year
                                $last_year_debit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y') - 1)
                                    ->sum('debit');
                                $last_year_credit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y') - 1)
                                    ->sum('credit');
                                $last_year_amount += ($last_year_credit - $last_year_debit);

                                // Year before last
                                $year_before_last_debit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y') - 2)
                                    ->sum('debit');
                                $year_before_last_credit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y') - 2)
                                    ->sum('credit');
                                $year_before_last_amount += ($year_before_last_credit - $year_before_last_debit);
                            }

                            // Update the overall totals
                            $overall_this_year_total += $this_year_amount;
                        @endphp



                        <tr>
                            <td class="px-4 py-2 border">
                                <a href="javascript:void(0);" onclick="scrollToCategory('{{ $income_account->category_name }}')" class="text-blue-600 hover:text-blue-800 text-sm">
                                    {{ ucwords(str_replace('_', ' ', $income_account->category_name)) }}
                                </a>
                            </td>
                            <td class="px-4 py-2 border text-right">

                            </td>
                            <td class="px-4 py-2 border text-right">
                                {{ number_format($this_year_amount, 2) }}
                            </td>
                        </tr>

                    @endforeach

                    <tr class="font-bold">
                        <td class="px-4 py-2 border">TOTAL</td>
                        <td class="px-4 py-2 border text-right">
                            0.00
                        </td>
                        <td class="px-4 py-2 border text-right">
                            {{ number_format($overall_this_year_total, 2) }}
                        </td>
                    </tr>

                    <tr>
                        <td colspan="3" class="bg-gray-50 font-bold px-4 py-2 border"></td>
                    </tr>




                    <tr>
                        <td colspan="1" class="bg-blue-50 font-bold px-4 py-2 border">EQUITY</td>
                        <td class="bg-blue-50 font-bold px-4 py-2 border">DEBIT</td>
                        <td class="bg-blue-50 font-bold px-4 py-2 border">CREDIT</td>
                    </tr>
                    @php
                        $overall_this_year_total = 0;
                    @endphp

                    @foreach($this->capital_accounts as $income_account)
                        @php
                            $this_year_amount = 0;
                            $last_year_amount = 0;
                            $year_before_last_amount = 0;

                            // Fetch the sub-category accounts for the current income category
                            $category_accounts = DB::table($income_account->category_name)->get();

                            foreach ($category_accounts as $category_account) {
                                // This year
                                $this_year_debit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y'))
                                    ->sum('debit');
                                $this_year_credit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y'))
                                    ->sum('credit');
                                $this_year_amount += ($this_year_credit - $this_year_debit);

                                // Last year
                                $last_year_debit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y') - 1)
                                    ->sum('debit');
                                $last_year_credit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y') - 1)
                                    ->sum('credit');
                                $last_year_amount += ($last_year_credit - $last_year_debit);

                                // Year before last
                                $year_before_last_debit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y') - 2)
                                    ->sum('debit');
                                $year_before_last_credit = DB::table('general_ledger')
                                    ->where('sub_category_code', $category_account->sub_category_code)
                                    ->whereYear('created_at', date('Y') - 2)
                                    ->sum('credit');
                                $year_before_last_amount += ($year_before_last_credit - $year_before_last_debit);
                            }

                            // Update the overall totals
                            $overall_this_year_total += $this_year_amount;
                        @endphp



                        <tr>
                            <td class="px-4 py-2 border">
                                <a href="javascript:void(0);" onclick="scrollToCategory('{{ $income_account->category_name }}')" class="text-blue-600 hover:text-blue-800 text-sm">
                                    {{ ucwords(str_replace('_', ' ', $income_account->category_name)) }}
                                </a>
                            </td>
                            <td class="px-4 py-2 border text-right">

                            </td>
                            <td class="px-4 py-2 border text-right">
                                {{ number_format($this_year_amount, 2) }}
                            </td>
                        </tr>

                    @endforeach

                    <tr class="font-bold">
                        <td class="px-4 py-2 border">TOTAL</td>
                        <td class="px-4 py-2 border text-right">
                            0.00
                        </td>
                        <td class="px-4 py-2 border text-right">
                            {{ number_format($overall_this_year_total, 2) }}
                        </td>
                    </tr>

                    <tr>
                        <td colspan="3" class="bg-gray-50 font-bold px-4 py-2 border"></td>
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
