<div>
    <div class="flex flex-col">
        <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">
                <div class="overflow-hidden">
                    <p>SACCOS INCOME ESTIMATES</p>
                    <table
                            class="min-w-full border text-center text-sm font-light dark:border-neutral-500">
                        <thead class="border-b font-medium dark:border-neutral-500">
                        <tr>

                            <th
                                    scope="col"
                                    class="border-r px-6 py-4 dark:border-neutral-500">
                                INCOME
                            </th>
                            <th
                                    scope="col"
                                    class="border-r px-6 py-4 dark:border-neutral-500">

                            </th>
                            <th
                                    scope="col"
                                    class="border-r px-6 py-4 dark:border-neutral-500">
                                ESTIMATE INCOME FOR CURRENT FINANCIAL YEAR
                            </th>
                            <th
                                    scope="col"
                                    class="border-r px-6 py-4 dark:border-neutral-500">
                                ACTUAL INCOME FOR CURRENT FINANCIAL YEAR
                            </th>
                            <th
                                    scope="col"
                                    class="border-r px-6 py-4 dark:border-neutral-500">
                                ESTIMATE INCOME FOR NEXT FINANCIAL YEAR
                            </th>

                        </tr>
                        </thead>
                        <tbody>

                        @foreach($income_accounts as $income_account)
                            <tr class="border-b dark:border-neutral-500">

                                <td class="whitespace-nowrap border-r px-6 py-4 dark:border-neutral-500">
                                    {{ucwords(str_replace('_', ' ', $income_account->category_name))}}
                                </td>
                                <td class="whitespace-nowrap border-r px-6 py-4 dark:border-neutral-500">
                                    @php
                                        $category_accounts = Illuminate\Support\Facades\DB::table($income_account->category_name)->get();
                                    @endphp
                                    @foreach($category_accounts as $category_account)

                                        <table class="table-auto">

                                            <tbody>
                                            <tr>
                                                <td
                                                        class="whitespace-nowrap border-r px-6 py-4 font-medium dark:border-neutral-500">
                                                    {{$category_account->sub_category_code}}
                                                </td>

                                                <td class="whitespace-nowrap px-6 py-4 dark:border-neutral-500">
                                                    {{
                                                    Illuminate\Support\Facades\DB::table('accounts')->where('institution_number',auth()->user()->institution_id)->where('sub_category_code',$category_account->sub_category_code)->value('account_name')
                                                    }}
                                                </td>
                                            </tr>

                                            </tbody>
                                        </table>



                                    @endforeach
                                </td>

                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium dark:border-neutral-500">
                                    @foreach($category_accounts as $category_account)
                                        <table class="table-auto">

                                            <tbody>
                                            <tr>

                                                <td class="whitespace-nowrap px-6 py-4 dark:border-neutral-500 flex text-right  w-full">
                                                    {{
                                                    number_format(Illuminate\Support\Facades\DB::table('accounts')->where('institution_number',auth()->user()->institution_id)->where('sub_category_code',$category_account->sub_category_code)->value('balance'),2)
                                                    }}

                                                </td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    @endforeach

                                </td>
                                <td class="whitespace-nowrap border-r px-6 py-4 dark:border-neutral-500">
                                    @foreach($category_accounts as $category_account)
                                        <table class="table-auto">

                                            <tbody>
                                            <tr>

                                                <td class="whitespace-nowrap px-6 py-4 dark:border-neutral-500 flex text-right  w-full">
                                                    {{
                                                    number_format(Illuminate\Support\Facades\DB::table('accounts')->where('institution_number',auth()->user()->institution_id)->where('sub_category_code',$category_account->sub_category_code)->value('balance'),2)
                                                    }}

                                                </td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    @endforeach

                                </td>
                                <td class="whitespace-nowrap border-r px-6 py-4 dark:border-neutral-500">
                                    @foreach($category_accounts as $category_account)
                                        <table class="table-auto">

                                            <tbody>
                                            <tr>

                                                <td class="whitespace-nowrap px-6 py-4 dark:border-neutral-500 flex text-right  w-full">
                                                    {{
                                                    number_format(Illuminate\Support\Facades\DB::table('accounts')->where('institution_number',auth()->user()->institution_id)->where('sub_category_code',$category_account->sub_category_code)->value('balance'),2)
                                                    }}

                                                </td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    @endforeach

                                </td>

                            </tr>
                        @endforeach

                        <tr class="border-b dark:border-neutral-500 bg-black">

                            <td class="whitespace-nowrap px-6 py-4 dark:border-neutral-500">

                            </td>
                        </tr>


                        @foreach($expense_accounts as $expense_account)
                            <tr class="border-b dark:border-neutral-500">

                                <td class="whitespace-nowrap border-r px-6 py-4 dark:border-neutral-500">
                                    {{ucwords(str_replace('_', ' ', $expense_account->category_name))}}
                                </td>
                                <td class="whitespace-nowrap border-r px-6 py-4 dark:border-neutral-500">
                                    @php
                                        $category_accounts = Illuminate\Support\Facades\DB::table($expense_account->category_name)->get();
                                    @endphp
                                    @foreach($category_accounts as $category_account)

                                        <table class="table-auto">

                                            <tbody>
                                            <tr>
                                                <td
                                                        class="whitespace-nowrap border-r px-6 py-4 font-medium dark:border-neutral-500">
                                                    {{$category_account->sub_category_code}}
                                                </td>

                                                <td class="whitespace-nowrap px-6 py-4 dark:border-neutral-500">
                                                    {{
                                                    Illuminate\Support\Facades\DB::table('accounts')->where('institution_number',auth()->user()->institution_id)->where('sub_category_code',$category_account->sub_category_code)->value('account_name')
                                                    }}
                                                </td>
                                            </tr>

                                            </tbody>
                                        </table>



                                    @endforeach
                                </td>

                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium dark:border-neutral-500">
                                    @foreach($category_accounts as $category_account)
                                        <table class="table-auto">

                                            <tbody>
                                            <tr>

                                                <td class="whitespace-nowrap px-6 py-4 dark:border-neutral-500 flex text-right  w-full">
                                                    {{
                                                    number_format(Illuminate\Support\Facades\DB::table('accounts')->where('institution_number',auth()->user()->institution_id)->where('sub_category_code',$category_account->sub_category_code)->value('balance'),2)
                                                    }}

                                                </td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    @endforeach

                                </td>
                                <td class="whitespace-nowrap border-r px-6 py-4 dark:border-neutral-500">
                                    @foreach($category_accounts as $category_account)
                                        <table class="table-auto">

                                            <tbody>
                                            <tr>

                                                <td class="whitespace-nowrap px-6 py-4 dark:border-neutral-500 flex text-right  w-full">
                                                    {{
                                                    number_format(Illuminate\Support\Facades\DB::table('accounts')->where('institution_number',auth()->user()->institution_id)->where('sub_category_code',$category_account->sub_category_code)->value('balance'),2)
                                                    }}

                                                </td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    @endforeach

                                </td>
                                <td class="whitespace-nowrap border-r px-6 py-4 dark:border-neutral-500">
                                    @foreach($category_accounts as $category_account)
                                        <table class="table-auto">

                                            <tbody>
                                            <tr>

                                                <td class="whitespace-nowrap px-6 py-4 dark:border-neutral-500 flex text-right  w-full">
                                                    {{
                                                    number_format(Illuminate\Support\Facades\DB::table('accounts')->where('institution_number',auth()->user()->institution_id)->where('sub_category_code',$category_account->sub_category_code)->value('balance'),2)
                                                    }}

                                                </td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    @endforeach

                                </td>

                            </tr>
                        @endforeach

                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>
