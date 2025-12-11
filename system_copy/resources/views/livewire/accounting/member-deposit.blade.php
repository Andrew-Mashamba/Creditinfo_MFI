<div class=" bg-white p-4 mb-4 ">



    <div class="container mx-auto mt-8   ">
        <h2 class="text-2xl font-bold mb-4  flex justify-center item-center  p-4 "> MEMBER DEPOSIT BALANCE AS  {{ now()->format("Y-M-d") }}, NBC SACCOS LTD</h2>


        @foreach ($products as $product)
        <div class="overflow-x-auto mt-4 ">

            <h4 class="font-bold p-4 underline uppercase"> Product :   {{ $product->sub_product_name }} </h4>
            <table class="min-w-full bg-white border border-gray-200 text-sm ">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">#</th>
                        <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Member No </th>
                        <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Names</th>
                        <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200"> Account Number  </th>

                        <th class="px-4 py-2 text-right bg-blue-100 border border-gray-200">Balance</th>
                    </tr>
                </thead>

                @php


                $account_number = $product->product_account;

                // Retrieve the main account details
                $account = DB::table('accounts')->where('account_number', $account_number)->first();

                // Ensure that the account exists before proceeding
                if ($account) {
                    // Fetch sub-accounts based on account details
                    $sub_accounts = DB::table('sub_accounts')
                                    ->where('major_category_code', $account->major_category_code)
                                    ->where('category_code', $account->category_code)
                                    ->where('sub_category_code', $account->sub_category_code)
                                    ->where('parent_account_number',$account_number)
                                    ->get();

                    // Calculate the sum of balances in sub-accounts for the same category
                    $product_summary = DB::table('sub_accounts')
                                        ->where('major_category_code', $account->major_category_code)
                                        ->where('category_code', $account->category_code)
                                        ->where('sub_category_code', $account->sub_category_code)
                                        ->where('parent_account_number',$account_number)
                                        ->sum('balance');
                } else {
                    // Handle the case where no account is found
                    $sub_accounts = []; // Empty collection if no account is found
                    $product_summary = 0;      // Set summary balance to 0 if no account is found
                }


                @endphp

                <tbody>
                    @foreach ($sub_accounts as $sub_account)
                    <tr>
                        <td class="px-4 py-2 border-blue-200  border ">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2 border-blue-200 border ">{{ $sub_account->client_number }}</td>
                        <td class="px-4 py-2 border-blue-200 border ">{{ $sub_account->account_name }}</td>
                        <td class="px-4 py-2 border-blue-200 border ">{{ $sub_account->account_number }}</td>
                        <td class="px-4 py-2 text-right border-blue-200 border">{{ number_format($sub_account->balance, 2) }} TZS</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-blue-100">
                        <td colspan="4" class="px-4 py-2 text-center font-bold border border-gray-200">Total </td>
                        <td class="px-4 py-2 text-right font-bold border border-gray-200">{{ number_format($product_summary, 2) }} TZS</td>
                    </tr>
                </tfoot>
            </table>


        </div>
        @endforeach


    </div>




</div>
