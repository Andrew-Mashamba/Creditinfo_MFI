

    <div>


        <div class="w-full">
            <!-- message container -->

            <div class="flex justify-center px-4 pt-4">
                <div>
                    <input wire:model.bounce="member_number" type="text" id="first_name" class="inline-flex items-center py-2 px-4 text-sm font-medium text-center text-gray-900
                            bg-white rounded-lg border border-gray-300 hover:bg-gray-100 focus:ring-4
                            focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                            dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700
                         dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Client Number" >


                    <select wire:model.bounce="product_number" name="product_number" id="product_number" class="ml-2 inline-flex items-center py-2 px-4 text-sm font-medium text-center text-gray-900
                            bg-white rounded-lg border border-gray-300 hover:bg-gray-100 focus:ring-4
                            focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                            dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700
                         dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option value="" selected>Select Product</option>
                        @foreach(App\Models\Loan_sub_products::get() as $product)
                            <option value="{{$product->product_id}}">{{$product->sub_product_name}}</option>
                        @endforeach

                    </select>
                    @error('branch')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Branch is mandatory.</p>
                    </div>
                    @enderror


                    <a wire:click="set" class="pointer-cursor ml-2 inline-flex items-center py-2 px-4 text-sm font-medium text-center text-gray-900
                            bg-white rounded-lg border border-gray-300 hover:bg-gray-100 focus:ring-4
                            focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                            dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700" style="cursor: pointer">xSet Application</a>
                </div>


            </div>

            <div class="relative flex py-5 items-center">
                <div class="flex-grow border-t border-gray-400"></div>
                <span class="flex-shrink mx-4 text-gray-400">Client Data</span>
                <div class="flex-grow border-t border-gray-400"></div>
            </div>


            <div class="w-full h-full grid justify-items-center">
                @foreach($this->member as $currentClient)
                <div class="w-fit m-auto grid justify-items-center">
                    <div class="w-fit text-center m-4" >

                        <div style="display: flex; justify-content: center;">
                            <img class="mb-3 w-32 h-32 rounded-full shadow-lg"
                                 src="http://96.46.181.165/projects/pamojacbs/storage/app/public/{{$currentClient->profile_photo_path}}"
                                 alt="{{$currentClient->first_name}}"/>
                        </div>

                        <p class="text-2xl mt-4 border-b-2 border-b-blue-400 ">{{$currentClient->first_name}} {{$currentClient->middle_name}} {{$currentClient->last_name}}</p>
                        <p class="m-4">{{$currentClient->address}}</p>
                    </div>
                    <div class="w-fit bg-gray-200 rounded-lg pl-2 pr-2 pt-2 pb-2 ">
                        <!-- message container -->

                        <div>

                            <div class="flex">

                                <div class="container mx-auto ">
                                    <div class="flex flex-col w-full" >

                                        <div class="grid gap-2 grid-cols-1 sm:grid-cols-2 mb-2">


                                            <div class="metric-card  dark:bg-gray-900 border bg-white  border-gray-200 dark:border-gray-800 rounded-lg p-4 max-w-72 w-full" >


                                                <table>

                                                    <tbody class="bg-white">
                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                            <p> Gender</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="text-sm text-gray-900">
                                                                {{$currentClient->gender}}
                                                            </p>
                                                        </td>

                                                    </tr>
                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                            <p> Marital status</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="text-sm text-gray-900">
                                                                {{$currentClient->marital_status}}
                                                            </p>
                                                        </td>

                                                    </tr>

                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                            <p>Date of birth</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="text-sm text-gray-900">
                                                                {{$currentClient->date_of_birth}}
                                                            </p>
                                                        </td>

                                                    </tr>


                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                            <p>Mobile phone number</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="text-sm text-gray-900">
                                                                {{$currentClient->mobile_phone_number}}
                                                            </p>
                                                        </td>

                                                    </tr>


                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                            <p>Email</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="text-sm text-gray-900">
                                                                {{$currentClient->email}}
                                                            </p>
                                                        </td>

                                                    </tr>

                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                            <p> Clientship Number</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="text-sm text-gray-900">
                                                                {{$currentClient->client_number}}
                                                            </p>
                                                        </td>

                                                    </tr>





                                                    </tbody>
                                                </table>


                                            </div>


                                            <div class="metric-card  dark:bg-gray-900 border bg-white  border-gray-200 dark:border-gray-800 rounded-lg p-4 max-w-72 w-full" >


                                                <table>

                                                    <tbody class="bg-white">
                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                            <p> Clientship Type</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="text-sm text-gray-900">
                                                                {{$currentClient->membership_type}}
                                                            </p>
                                                        </td>

                                                    </tr>
                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                            <p> Business Name</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="text-sm text-gray-900 whitespace-normal">
                                                                {{$currentClient->business_name}}
                                                            </p>
                                                        </td>

                                                    </tr>

                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                            <p> Incorporation Number</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="text-sm text-gray-900">
                                                                {{$currentClient->incorporation_number}}
                                                            </p>
                                                        </td>

                                                    </tr>

                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                            <p> Address</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="text-sm text-gray-900 whitespace-normal">
                                                                {{$currentClient->address}}
                                                            </p>
                                                        </td>

                                                    </tr>

                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                            <p> Street</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="text-sm text-gray-900 whitespace-normal">
                                                                {{$currentClient->street}}
                                                            </p>
                                                        </td>

                                                    </tr>

                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                            <p> Notes</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="text-sm text-gray-900 whitespace-normal">
                                                                {{$currentClient->notes}}
                                                            </p>
                                                        </td>

                                                    </tr>


                                                    </tbody>
                                                </table>


                                            </div>



                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-1 w-full">

                                            <div class="metric-card  dark:bg-gray-900 border bg-white  border-gray-200 dark:border-gray-800 rounded-lg p-4 max-w-72 w-full" >


                                                <div class="flex justify-between items-center w-full">
                                                    <div class="flex items-center">

                                                        <p class="flex items-center text-l font-semibold spacing-sm text-slate-600">Client Shares

                                                        </p>


                                                    </div>

                                                </div>



                                                <table class="w-full" >RRRRRRRRRR
                                                    @foreach(App\Models\AccountsModel::where('member_number',$this->member_number)->where('product_number','11')->get() as $account)

                                                        <tr >
                                                            <td class="mt-2 text-sm font-semibold  text-slate-400 dark:text-white capitalize  ">
                                                                <p> {{App\Models\sub_products::where('product_id','11')->value('sub_product_name')}}</p>
                                                            </td>
                                                            <td class="pl-2 mt-2 text-sm font-semibold spacing-sm  text-slate-400 dark:text-white">

                                                                <p>{{strtolower($account -> account_number)}}</p>
                                                            </td>
                                                            <td class="pl-2 mt-2 text-l font-bold font-semibold spacing-sm  text-black dark:text-white text-right">

                                                                <p>{{number_format($account->balance,2)}} TZS</p>
                                                            </td>
                                                        </tr>


                                                    @endforeach
                                                    <tr >
                                                        <td class="mt-2 text-sm font-semibold  text-slate-400 dark:text-white capitalize  ">
                                                            <p class="flex items-center text-l font-semibold spacing-sm text-slate-600">
                                                                Client Savings
                                                            </p>
                                                        </td>


                                                    </tr>
                                                    @foreach(App\Models\AccountsModel::where('member_number',$this->member_number)->where('product_number','12')->get() as $account)

                                                        <tr >
                                                            <td class="mt-2 text-sm font-semibold  text-slate-400 dark:text-white capitalize  ">
                                                                <p> {{App\Models\sub_products::where('product_id','12')->value('sub_product_name')}}</p>
                                                            </td>
                                                            <td class="pl-2 mt-2 text-sm font-semibold spacing-sm  text-slate-400 dark:text-white">

                                                                <p> {{strtolower($account -> account_number)}}</p>
                                                            </td>
                                                            <td class="pl-2 mt-2 text-l font-bold font-semibold spacing-sm  text-black dark:text-white text-right">

                                                                <p> {{number_format($account->balance,2)}} TZS</p>
                                                            </td>
                                                        </tr>
                                                    @endforeach

                                                    <tr >
                                                        <td class="mt-2 text-sm font-semibold  text-slate-400 dark:text-white capitalize  ">
                                                            <p class="flex items-center text-l font-semibold spacing-sm text-slate-600">
                                                                Client Deposits
                                                            </p>
                                                        </td>


                                                    </tr>

                                                    @foreach(App\Models\AccountsModel::where('member_number',$this->member_number)->where('product_number','13')->get() as $account)

                                                        <tr >
                                                            <td class="mt-2 text-sm font-semibold  text-slate-400 dark:text-white capitalize  ">
                                                                <p> {{App\Models\sub_products::where('product_id','13')->value('sub_product_name')}}</p>
                                                            </td>
                                                            <td class="pl-2 mt-2 text-sm font-semibold spacing-sm  text-slate-400 dark:text-white">

                                                                <p> {{strtolower($account -> account_number)}}</p>
                                                            </td>
                                                            <td class="pl-2 mt-2 text-l font-bold font-semibold spacing-sm  text-black dark:text-white text-right">

                                                                <p>  {{number_format($account->balance,2)}} TZS</p>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    <tr >
                                                        <td class="mt-2 text-sm font-semibold  text-slate-400 dark:text-white capitalize  ">
                                                            <p class="flex items-center text-l font-semibold spacing-sm text-slate-600">
                                                                Client Loans
                                                            </p>
                                                        </td>


                                                    </tr>


                                                    @foreach(App\Models\AccountsModel::where('member_number',$this->member_number)->where('product_number','14')->get() as $account)

                                                        <tr >
                                                            <td class="mt-2 text-sm font-semibold  text-slate-400 dark:text-white capitalize  ">
                                                                <p>  {{App\Models\sub_products::where('product_id','14')->value('sub_product_name')}}</p>
                                                            </td>
                                                            <td class="pl-2 mt-2 text-sm font-semibold spacing-sm  text-slate-400 dark:text-white">

                                                                <p> {{strtolower($account -> account_number)}}</p>
                                                            </td>
                                                            <td class="pl-2 mt-2 text-l font-bold font-semibold spacing-sm  text-black dark:text-white text-right">

                                                                <p> {{number_format($account->balance,2)}} TZS </p>
                                                            </td>
                                                        </tr>
                                                    @endforeach


                                                </table>
                                            </div>




                                        </div>




                                    </div>
                                </div>


                            </div>

                        </div>

                    </div>
                </div>
                @endforeach

            </div>




        </div>

    </div>

