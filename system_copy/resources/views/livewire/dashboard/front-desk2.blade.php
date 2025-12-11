<div class="w-full">
    {{-- A good traveler has no fixed plans and is not intent upon arriving. --}}
    <div class="w-full">


        <div class="w-full  metric-card   border bg-white  border-gray-200   rounded-lg p-4 shadow-lg">
            <p class="flex items-center text-l font-semibold spacing-sm text-slate-600">
                Generate Statement
            </p>
            <div class="flex justify-between items-center w-full">

                <div class="flex gap-4">
                    <div>
                        <label for="available_shares" class="block mb-2 sm:text-xs font-medium text-gray-900
                                       ">Account Number</label>
                        <input wire:model.bounce="check_account_number" type="check_account_number" id="check_account_number" class="sm:text-xs block w-full p-2 text-gray-900 border
                                        border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500
                                        ">
                    </div>

                    <div>
                        <label for="available_shares" class="block mb-2 sm:text-xs font-medium text-gray-900
                                                    ">Date Range</label>
                        <div class="relative flex items-center">


                            <input type="text" name="daterange" value="{{$start_date_input }} - {{$end_date_input}}"
                                   class="cursor-pointer sm:text-xs block w-full p-2 text-gray-900 border
                                                        border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500
                                                        " placeholder="Select date start">

                        </div>
                    </div>


                </div>

                @if(session()->has('error1'))
                    <div class="alert alert-danger">
                        <ul>
                           {{session()->get('error1')}}
                        </ul>
                    </div>
                @endif



                <div class="inline-flex rounded-md shadow-sm" role="group">


                    <button wire:click="downloadExcelFile" type="button" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700      ">
                        <svg class="w-3 h-3 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M14.707 7.793a1 1 0 0 0-1.414 0L11 10.086V1.5a1 1 0 0 0-2 0v8.586L6.707 7.793a1 1 0 1 0-1.414 1.414l4 4a1 1 0 0 0 1.416 0l4-4a1 1 0 0 0-.002-1.414Z"></path>
                            <path d="M18 12h-2.55l-2.975 2.975a3.5 3.5 0 0 1-4.95 0L4.55 12H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2Zm-3 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"></path>
                        </svg>
                        Download Excel
                    </button>

                    <button   wire:click="downloadPDFFile"  type="button" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-r-md hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700     ">
                        <svg class="w-3 h-3 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M14.707 7.793a1 1 0 0 0-1.414 0L11 10.086V1.5a1 1 0 0 0-2 0v8.586L6.707 7.793a1 1 0 1 0-1.414 1.414l4 4a1 1 0 0 0 1.416 0l4-4a1 1 0 0 0-.002-1.414Z"></path>
                            <path d="M18 12h-2.55l-2.975 2.975a3.5 3.5 0 0 1-4.95 0L4.55 12H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2Zm-3 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"></path>
                        </svg>
                        Downloads PDF
                    </button>
                </div>




            </div>

        </div>




        <div class="w-full flex gap-2">
            <div class="w-1/3 metric-card   border bg-white  border-gray-200  rounded-lg p-4 ">
                @if($this->enableCheque)
                    <div class="container mx-auto px-1 py-6">
                        <div class="max-w-lg mx-auto bg-white shadow-md rounded-lg overflow-hidden">
                            <div class="px-1 py-4">
                                <div class="font-bold text-l mb-4 justify-center item-center flex">Cheque Details</div>
                                <table class="w-full border-collapse">

                                    @foreach($this->cheque_values as $value)
                                        <tr>
                                            <td class="py-2 px-4 border-b">Cheque Number:</td>
                                            <td class="py-2 px-4 border-b font-semibold">{{$value->cheque_number}}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b">Customer Account:</td>
                                            <td class="py-2 px-4 border-b font-semibold">{{$value->customer_account}}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b">Amount:</td>
                                            <td class="py-2 px-4 border-b font-semibold">{{number_format($value->amount,2)}} TZS </td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b">Branch:</td>
                                            <td class="py-2 px-4 border-b font-semibold">{{DB::table('branches')->where('id',$value->branch)->value('name')}}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b">Issued By:</td>
                                            <td class="py-2 px-4 border-b font-semibold">{{DB::table('employees')->where('id',$value->finance_approver)->value('first_name')}} </td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b">Issued date:</td>
                                            <td class="py-2 px-4 border-b font-semibold">{{$value->created_at}}</td>
                                        </tr>

                                        <tr>
                                            <td class="py-2 px-4 border-b">Status:</td>
                                            <td class="py-2 px-4 border-b font-semibold"> {{$value->status}}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b">Bank Account:</td>
                                            <td class="py-2 px-4 border-b font-semibold">{{$value->bank_account}}</td>
                                        </tr>
                                        <!-- Add more rows for other cheque details -->
                                    @endforeach
                                </table>


                                <button wire:click="$toggle('enableCheque')" type="button" class="text-white justify-end item-end flex mt-4 bg-blue-800 hover:bg-blue-400 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-full text-sm px-5 py-2.5 text-center me-2 mb-2 "> Close </button>


                            </div>
                        </div>

                    </div>


                @else

                    <div>
                        <p class="flex items-center text-l font-semibold spacing-sm text-slate-600">
                            Withdraw
                        </p>
                        <div>

                            @if (session()->has('message_3'))

                                <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8"
                                     role="alert">
                                    <div class="flex">
                                        <div class="py-1">
                                            <svg class="fill-current h-6 w-6 text-teal-500 mr-4"
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 viewBox="0 0 20 20">
                                                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold">The process is
                                                completed</p>
                                            <p class="text-sm">{{ session('message_3') }} </p>
                                        </div>
                                    </div>
                                </div>
                                {{--                                @endif--}}
                            @endif

                       @if (session()->has('message_fail_withdraw'))
                            {{--                                                            @if (session()->has('alert-class'))--}}
                            <div class="bg-red-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8"
                                 role="alert">
                                <div class="flex">
                                    <div class="py-1">
                                        <svg class="fill-current h-6 w-6 text-teal-500 mr-4"
                                             xmlns="http://www.w3.org/2000/svg"
                                             viewBox="0 0 20 20">
                                            <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold">The process has failed
                                        </p>
                                        <p class="text-sm">{{ session('message_fail_withdraw') }} </p>
                                    </div>
                                </div>
                            </div>
                        @endif


                        </div>
                        <hr class="boder-b-0 my-4"/>
                        <div class="">


                            <div class="mt-2"></div>
                            <p for="payment_method"
                            class="block mb-2 text-sm font-medium text-slate-600 ">
                             Payment Method</p>

                      <select wire:model.bounce="service_category" name="service_category" id="service_category"
                            class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                        <option selected value="">Select</option>
                        <option value="Member_withdraw"> Member Withdraw</option>
                        <option value="Office_expenses"> Office Expenses </option>

                    </select>

                    @if($this->service_category=="Office_expenses")


                    <p for="bank"
                    class="block mb-2 text-sm font-medium text-slate-600 ">
                     Select Cash Account </p>
                 <select wire:model.bounce="cash_bank_account" name="cash_bank_account" id="cash_bank_account"
                         class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                     <option selected value="">Select</option>
                     @foreach(DB::table('accounts')->where('category_code',1000)->get() as $bank)
                         <option value="{{$bank->account_number}}">{{$bank->account_name.'('.$bank->account_number.')'}}
                         </option>
                     @endforeach
                 </select>
                 <div class="mt-2"></div>



                     <p for="bank" class="block mb-2 text-sm font-medium text-slate-600 ">  Select Expense Account </p>
                  <select wire:model.bounce="expense_account" name="expense_account" id="expense_account"
                          class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">

                          <option selected value="">Select</option>

                      @foreach(DB::table('accounts')->where('category_code',5000)->get() as $bank)
                          <option value="{{$bank->account_number}}">
                            {{$bank->account_name.'('.$bank->account_number.')'}}
                          </option>
                      @endforeach

                  </select>




                      <p for="bank" class="block mb-2 text-sm font-medium text-slate-600 ">
                        Enter Amount
                    </p>
                    <input id="cash_amount" type="number" name="cash_amount"
                                 class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                                 wire:model.bounce="cash_amount" autofocus/>
                    @error('cash_amount')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Amount is mandatory.</p>
                    </div>
                    @enderror

                    <div class="mt-2"></div>



                    <p for="description" class="block mb-2 text-sm font-medium text-slate-600 ">
                        Descriptions
                    </p>
                    <input id="description" type="text" name="description"
                                 class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                                 wire:model.bounce="description" autofocus/>
                    @error('description')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>description is mandatory.</p>
                    </div>
                    @enderror

                    <div class="mt-2"></div>



                    @else



                            <div class="mt-2"></div>

                            <p for="nidaNumber"
                               class="block mb-2 text-sm font-medium text-slate-600 ">
                               Member Number </p>
                            <x-jet-input id="nidaNumber" type="number" name="nidaNumber"
                                         class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                                         wire:model.bounce="member_number" autofocus/>
                            @error('member_number')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Member number is mandatory</p>
                            </div>
                            @enderror

                            @error('accountSelected')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>{{$message}}</p>
                            </div>
                            @enderror

                            <div class="mt-2"></div>

                            <table class="table-fixed w-full">

                                <tbody class="w-full">
                                @if( $this->member_number)
                                    @foreach(\Illuminate\Support\Facades\DB::table('accounts')->where('client_number',$this->member_number)->get() as $account1)
                                         <tr class="flex w-full">
                                                <td class="w-1/3">Account</td>
                                                <td class="w-1/3">Balance</td>
                                                <td class="w-1/3"></td>
                                            </tr>
                                            <tr class="flex w-full">
                                                <td class="w-1/3">
                                                    <p class="block text-sm font-medium text-red-500 ">{{App\Models\AccountsModel::where('account_number',$account1->parent_account_number)->value('account_name')}} - {{$account1->account_number}}</p>
                                                </td>
                                                <td class="w-1/3">
                                                    <p class="block text-sm font-medium text-red-500  capitalize">
                                                        {{number_format($account1->balance,2)}} TZS
                                                    </p>
                                                </td>
                                                <td class="w-1/3 flex justify-end text-center">
                                                    <div wire:loading
                                                         wire:target="setAccount({{$account1->id}})">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                             class="animate-spin  h-9 w-9 stroke-gray-400 rounded-full p-2"
                                                             fill="white" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round"
                                                                  stroke-linejoin="round"
                                                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                        </svg>
                                                    </div>

                                                    @if($this->accountSelected == $account1->account_number)

                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                             class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-green-400 p-2"
                                                             fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round"
                                                                  stroke-linejoin="round"
                                                                  d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    @else
                                                        <div wire:loading.remove
                                                             wire:target="setAccount({{$account1->id}})">
                                                            <svg wire:click="setAccount({{$account1->id}})"
                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                 class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-slate-400 p-2"
                                                                 fill="none" viewBox="0 0 24 24"
                                                                 stroke="currentColor"
                                                                 stroke-width="2">
                                                                <path stroke-linecap="round"
                                                                      stroke-linejoin="round"
                                                                      d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>

                                    @endforeach

                                    @if(\Illuminate\Support\Facades\DB::table('sub_accounts')->where('client_number',$this->member_number)->exists())

                                    @else
                                        <div class="text-red-500 text-sm text-center"> Invalid Number </div>


                                    @endif

                                @endif
                                </tbody>
                            </table>

                                <p for="bank"
                                   class="block mb-2 text-sm font-medium text-slate-600 ">
                                    Select Bank</p>
                                <select wire:model.bounce="bankz" name="bankz" id="bankz"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  ">
                                    <option selected value="">Select</option>


                                    {{-- 
                                        This code block is responsible for populating a dropdown/select element with cash accounts.
                                        It follows these steps:
                                        1. Retrieves the table name for cash accounts from setup_accounts table
                                        2. Iterates through all cash accounts in the retrieved table
                                        3. For each account:
                                            - Gets the account number from the accounts table using sub_category_code
                                            - Formats the display text by combining sub_category_code and sub_category_name
                                            - Replaces underscores with spaces and converts to title case
                                        
                                        Note: Currently commented out as it might be replaced with a different implementation
                                        or is under maintenance.
                                    --}}

                                    {{-- @php
                                        $cash_table = DB::table('setup_accounts')->where('item','cash')->value('table_name');
                                    @endphp

                                    @foreach(DB::table($cash_table)->get() as $bank)
                                        <option value="{{ DB::table('accounts')->where('sub_category_code',$bank->sub_category_code)->value('account_number') }}">
                                            {{ Str::of($bank->sub_category_code.'-'.$bank->sub_category_name)->replace('_', ' ')->title() }}
                                        </option>
                                    @endforeach --}}



                                </select>
                                @error('bank')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>Bank is mandatory.</p>
                                </div>
                                @enderror
                                <div class="mt-2"></div>

                            <div class="mt-2"></div>





                            <p for="payment_method"
                               class="block mb-2 text-sm font-medium text-slate-600 ">
                                Payment Method</p>
                            <select wire:model.bounce="payment_method" name="payment_method" id="payment_method"
                                    class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                                <option selected value="">Select</option>
                                <option value="cash">CASH</option>
                                <option value="internal_transfer">NBC BANK</option>
                                <option value="tips_mno">MOBILE</option>
                                <option value="tips_bank">OTHER BANK</option>
                            </select>
                            @error('payment_method')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>payment method is mandatory</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>


                            @if($this->payment_method=="CHEQUE")
                                <p for="bank3"
                                   class="block mb-2 text-sm font-medium text-slate-600 ">
                                    Select Bank</p>
                                <select wire:model.bounce="bank3" name="bank3" id="bank3"
                                        class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                                    <option selected value="">Select</option>
                                    @foreach(DB::table('accounts')->where('category_code',1000)->get() as $bank)
                                        <option value="{{$bank->account_number}}">{{$bank->account_name.'('.$bank->account_number.')'}}
                                        </option>
                                    @endforeach

                                </select>
                                @error('bank3')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>Bank is mandatory.</p>
                                </div>
                                @enderror

                                <div class="mt-2"></div>
                            @endif





                            @if($this->payment_method=="BANK" || $this->payment_method=="MOBILE")
                                <p for="bank"
                                   class="block mb-2 text-sm font-medium text-slate-600 ">
                                    Select Bank</p>
                                <select wire:model.bounce="bank" name="bank" id="bank"
                                        class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                                    <option selected value="">Select</option>
                                    @foreach(DB::table('accounts')->where('category_code',1000)->get() as $bank)
                                        <option value="{{$bank->account_number}}">{{$bank->account_name.'('.$bank->account_number.')'}}
                                        </option>
                                    @endforeach

                                </select>
                                @error('bank')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>Bank is mandatory.</p>
                                </div>
                                @enderror
                                <div class="mt-2"></div>
                                    <p for="phone_number4"
                                       class="block mb-2 text-sm font-medium text-slate-600 ">
                                        Destination Account <a class="text-xs"> (or phone number) </a>
                                    </p>
                                    <x-jet-input id="phone_number4" type="text" name="amount"
                                                 class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                                                 wire:model.bounce="phone_number" autofocus/>
                                    @error('phone_number')
                                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                        <p>{{$message}}</p>
                                    </div>

                                    @enderror
                                    <div class="mt-2"></div>
                                @endif





                                <p for="bank" class="block mb-2 text-sm font-medium text-slate-600 ">
                                    Enter Amount
                                </p>
                                <input id="amount3" type="number" name="amount3"
                                             class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                                             wire:model.bounce="amount4" autofocus/>
                                @error('amount4')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>Amount is mandatory.</p>
                                </div>
                                @enderror

                                <div class="mt-2"></div>
                            <div class="mt-2"></div>

                            @endif

                        </div>
                        <hr class="border-b-0 my-6"/>
                        <div class="flex justify-end w-auto">
                            <div wire:loading wire:target="process3">
                                <x-jet-button
                                    class="text-white bg-blue-800 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 " disabled>
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="animate-spin  h-5 w-5 mr-2 stroke-white-800"
                                             fill="white" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                        <p>Please wait...</p>
                                    </div>
                                </x-jet-button>
                            </div>
                        </div>


                        <div class="flex justify-end w-auto">
                            <div wire:loading.remove wire:target="process3">
                                <x-jet-button wire:click="process3"
                                              class="text-white bg-blue-800 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2  ">
                                    <p class="text-white">Withdraw </p>
                                </x-jet-button>

                            </div>
                        </div>

                    </div>


                    @endif
            </div>

            <div class="w-1/3 metric-card   border bg-white  border-gray-200  rounded-lg p-4 ">
                    <div>
                        <p class="flex items-center text-l font-semibold spacing-sm text-slate-600">
                            Deposits
                        </p>

                        <div>
                            @if (session()->has('message1'))
                                {{--                                                            @if (session()->has('alert-class'))--}}
                                <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8"
                                     role="alert">
                                    <div class="flex">
                                        <div class="py-1">
                                        <svg class="fill-current h-6 w-6 text-teal-500 mr-4"
                                             xmlns="http://www.w3.org/2000/svg"
                                             viewBox="0 0 20 20">
                                            <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                                        </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold">The process is
                                                completed</p>
                                            <p class="text-sm">{{ session('message1') }} </p>
                                        </div>
                                    </div>
                                </div>
                                {{--                                                            @endif--}}
                            @endif
                            @if (session()->has('message_fail'))
                                {{--                                                            @if (session()->has('alert-class'))--}}
                                <div class="bg-red-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8"
                                     role="alert">
                                    <div class="flex">
                                        <div class="py-1">
                                            <svg class="fill-current h-6 w-6 text-teal-500 mr-4"
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 viewBox="0 0 20 20">
                                                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold">The process has failed
                                            </p>
                                            <p class="text-sm">{{ session('message_fail') }} </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <hr class="boder-b-0 my-4"/>

                        <div class="">
                            <p for="bank"
                               class="block mb-2 text-sm font-medium text-slate-600 ">
                                Deposit Type</p>
                            <select wire:model.bounce="deposit_type" name="deposit_type" id="deposit_type"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  ">
                                <option selected value="">Select</option>
                                <option value="BANK">BANK</option>
                                <option value="CASH">CASH</option>

                            </select>
                            @error('deposit_type')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>{{$message}}</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>

                            @if($this->deposit_type=="BANK")
                                <p for="bank"
                                   class="block mb-2 text-sm font-medium text-slate-600 ">
                                    Select Bank</p>
                                <select wire:model.bounce="bank" name="bank" id="bank"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  ">
                                    <option selected value="">Select</option>


                                    @php
                                    $cash_table = DB::table('setup_accounts')->where('item','cash')->value('table_name');
                                    @endphp

                                    @foreach(DB::table($cash_table)->get() as $bank)
                                        <option value="{{ DB::table('accounts')->where('sub_category_code',$bank->sub_category_code)->value('id') }}">
                                            {{ Str::of($bank->sub_category_code.'-'.$bank->sub_category_name)->replace('_', ' ')->title() }}

                                            {{--                                                                    - {{$bank->account_number}}--}}
                                        </option>

                                    @endforeach



                                </select>
                                @error('bank')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>Bank is mandatory.</p>
                                </div>
                                @enderror
                                <div class="mt-2"></div>


                                <p for="reference_number"
                                   class="block mb-2 text-sm font-medium text-slate-600 ">
                                    Enter Reference Number</p>
                                <x-jet-input id="reference_number" type="text"
                                             name="reference_number" class="mt-1 block w-full"
                                             wire:model.bounce="reference_number" autofocus/>

                                @error('reference_number')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>Reference Number is mandatory and should be more than two
                                        characters.</p>
                                </div>
                                @enderror
                                <div class="mt-2"></div>
                            @endif


                            <p for="member"
                               class="block mb-2 text-sm font-medium text-gray-900 ">
                                Select Member</p>
                            <select wire:model.bounce="member" name="member" id="member"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  ">
                                <option selected value="">Select</option>

                                <option value="new">New Member</option>
                                @foreach(App\Models\ClientsModel::where('client_number','!=',null)->get() as $members)

                                    <option value="{{$members->client_number}}">{{$members->first_name}} {{$members->middle_name}} {{$members->last_name}}</option>
                                @endforeach

                            </select>
                            @error('member')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Branch is mandatory.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>

                            @if($this->member)

                                @if($this->member=='new')
                                    <p for="member"
                                       class="block mb-2 text-sm font-medium text-gray-900 ">
                                        Select Deposit Type</p>
                                    <select wire:model.bounce="depositType" name="depositType" id="depositType"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  ">
                                        <option selected value="">Select</option>
                                        <option selected value="RegistrationFee">Registration Fee</option>
                                        <option selected value="MandatoryShares">Mandatory Shares</option>

                                    </select>

                                    @error('depositType')
                                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                        <p>Deposit Type is mandatory.</p>
                                    </div>
                                    @enderror
                                    <div class="mt-2"></div>

                                    @if($this->depositType=='RegistrationFee')
                                        <p for="registrationFee"
                                           class="block mb-2 text-sm font-medium text-slate-600 ">
                                            Amount</p>
                                        <x-jet-input disabled id="registrationFee" type="number" name="registrationFee"
                                                     class="mt-1 block w-full placeholder-slate-400 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500
                                                                                  disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none
                                                                                  invalid:border-pink-500 invalid:text-pink-600
                                                                                  focus:invalid:border-pink-500 focus:invalid:ring-pink-500"
                                                     wire:model.bounce="registrationFee" />

                                        <p for="new_member_deposit_notes"
                                           class="block mb-2 text-sm font-medium text-slate-600 ">
                                            NIDA Number</p>
                                        <x-jet-input id="nida_number4" type="text" name="nida_number4"
                                                     class="mt-1 block w-full" wire:model.bounce="nida_number4"
                                        />
                                        @error('nida_number4')
                                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                            <p>{{$message}}</p>
                                        </div>
                                        @enderror

                                        <p for="phone_number"
                                        class="block mb-2 text-sm font-medium text-slate-600 ">
                                         Phone Number  </p>
                                     <x-jet-input id="phone_number" type="text" name="phone_number"
                                                  class="mt-1 block w-full"
                                                  wire:model.bounce="phone_number4" autofocus/>
                                     @error('phone_number4')
                                     <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                         <p> Phone is mandatory and should be more than two
                                             characters.</p>
                                     </div>
                                     @enderror
                                     <div class="mt-2"></div>


                                    @endif
                                    @if($this->depositType=='MandatoryShares')
                                        <p for="amount"
                                           class="block mb-2 text-sm font-medium text-slate-600 ">
                                            Amount</p>
                                        <x-jet-input disabled id="initial_shares_value" type="number" name="initial_shares_value"
                                                     class="mt-1 block w-full placeholder-slate-400 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500
                                                                                  disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none
                                                                                  invalid:border-pink-500 invalid:text-pink-600
                                                                                  focus:invalid:border-pink-500 focus:invalid:ring-pink-500"
                                                     wire:model.bounce="initial_shares_value" />

                                        <div class="mt-2"></div>

                                        <p for="new_member_deposit_notes"
                                           class="block mb-2 text-sm font-medium text-slate-600 ">
                                            NIDA Number</p>
                                        <x-jet-input id="nida_number" type="text" name="phone_number"
                                                     class="mt-1 block w-full" wire:model.bounce="nida_number"
                                        />
                                        @error('phone_number')
                                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                            <p>{{$message}}</p>
                                        </div>
                                        @enderror


                                        <p for="phone_number2"
                                        class="block mb-2 text-sm font-medium text-slate-600 ">
                                         Phone Number </p>
                                     <x-jet-input id="phone_number2" type="text" name="phone_number2"
                                                  class="mt-1 block w-full"
                                                  wire:model.bounce="phone_number4" autofocus/>
                                     @error('phone_number4')
                                     <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                         <p> Phone is mandatory and should be more than two
                                             characters.</p>
                                     </div>
                                     @enderror
                                     <div class="mt-2"></div>


                                    @endif

                                    <div class="mt-2"></div>

                                    <p for="new_member_deposit_notes"
                                       class="block mb-2 text-sm font-medium text-slate-600 ">
                                        Enter Notes</p>
                                    <x-jet-input id="new_member_deposit_notes" type="text" name="new_member_deposit_notes"
                                                 class="mt-1 block w-full" wire:model.bounce="new_member_deposit_notes"
                                    />
                                    @error('new_member_deposit_notes')
                                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                        <p>Notes is mandatory and should be more than two
                                            characters.</p>
                                    </div>
                                    @enderror


                                @else
                                    <p for="member"
                                       class="block mb-2 text-sm font-medium text-gray-900 ">
                                    </p>




                                    @if(DB::table('clients')->where('client_number', $this->member)->where('client_status','ONPROGRESS')->exists())



                                    <table class="w-full">

                                        @foreach(App\Models\AccountsModel::where('sub_category_code', '4209')->get() as $account)


                                            @php
                                                $this->account_number = $account->account_number;

                                            @endphp

                                            <tr>
                                                <td class="text-left">
                                                    <p class="block text-sm font-medium text-red-500  capitalize">

                                                      Registration Fee ({{ number_format( $this->registrationFee,2 ) }})
                                                    </p>

                                                </td>

                                                <td class="pl-4 text-right"><p
                                                            class="block text-sm font-medium text-red-500 ">{{$account->account_number}}</p>
                                                </td>
                                                <td class="text-right">


                                                    <div wire:loading
                                                         wire:target="setAccount({{$account->id}})">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                             class="animate-spin  h-9 w-9 stroke-gray-400 rounded-full p-2"
                                                             fill="white" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round"
                                                                  stroke-linejoin="round"
                                                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>

                                                        </svg>
                                                    </div>

                                                    @if($this->accountSelected == $account->account_number)
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                             class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-green-400 p-2"
                                                             fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round"
                                                                  stroke-linejoin="round"
                                                                  d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    @else
                                                        <div wire:loading.remove
                                                             wire:target="setAccount({{$account->id}})">
                                                            <svg wire:click="setAccount({{$account->id}})"
                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                 class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-slate-400 p-2"
                                                                 fill="none" viewBox="0 0 24 24"
                                                                 stroke="currentColor"
                                                                 stroke-width="2">
                                                                <path stroke-linecap="round"
                                                                      stroke-linejoin="round"
                                                                      d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                        </div>
                                                    @endif

                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>



                                    @endif



                                    <table class="w-full">

                                        @foreach(App\Models\AccountsModel::where('client_number',$this->member)->get() as $account)


                                            @php
                                                $this->account_number = $account->account_number;

                                            @endphp

                                            <tr>
                                                <td class="text-left">
                                                    <p class="block text-sm font-medium text-red-500  capitalize">

                                                        @if($account->product_number==2000)
                                                            Saving Account
                                                        @elseif($account->product_number==3000)
                                                            Deposits Account
                                                        @elseif($account->product_number==1000)
                                                            Shares Account
                                                        @elseif($account->product_number==5000)
                                                            Installment Payment
                                                        @endif
                                                    </p>

                                                </td>

                                                <td class="pl-4 text-right"><p
                                                            class="block text-sm font-medium text-red-500 ">{{$account->account_number}}</p>
                                                </td>
                                                <td class="text-right">


                                                    <div wire:loading
                                                         wire:target="setAccount({{$account->id}},'LOAN')">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                             class="animate-spin  h-9 w-9 stroke-gray-400 rounded-full p-2"
                                                             fill="white" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round"
                                                                  stroke-linejoin="round"
                                                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>

                                                        </svg>
                                                    </div>

                                                    @if($this->accountSelected == $account->account_number)
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                             class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-green-400 p-2"
                                                             fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round"
                                                                  stroke-linejoin="round"
                                                                  d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    @else
                                                        <div wire:loading.remove
                                                             wire:target="setAccount({{$account->id}},'LOAN')">
                                                            <svg wire:click="setAccount({{$account->id}},'LOAN')"
                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                 class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-slate-400 p-2"
                                                                 fill="none" viewBox="0 0 24 24"
                                                                 stroke="currentColor"
                                                                 stroke-width="2">
                                                                <path stroke-linecap="round"
                                                                      stroke-linejoin="round"
                                                                      d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                        </div>
                                                    @endif

                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>




                                    <table class="w-full">

                                        @foreach(App\Models\AccountsModel::where('client_number',$this->member)->where(function($q) {$q->where('category_code', '=', '1200'); })->get() as $account)


                                            @php
                                                $this->account_number = $account->account_number;

                                            @endphp

                                            <tr>
                                                <td class="text-left">
                                                    <p class="block text-sm font-medium text-red-500  capitalize">

                                                        {{$account->account_name}}
                                                    </p>

                                                </td>

                                                <td class="pl-4 text-right"><p
                                                        class="block text-sm font-medium text-red-500 ">{{$account->account_number}}</p>
                                                </td>
                                                <td class="text-right">


                                                    <div wire:loading
                                                         wire:target="setAccount('{{$account->id}}','LOAN')">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                             class="animate-spin  h-9 w-9 stroke-gray-400 rounded-full p-2"
                                                             fill="white" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round"
                                                                  stroke-linejoin="round"
                                                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>

                                                        </svg>
                                                    </div>

                                                    @if($this->accountSelected == $account->account_number)
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                             class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-green-400 p-2"
                                                             fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round"
                                                                  stroke-linejoin="round"
                                                                  d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    @else
                                                        <div wire:loading.remove
                                                             wire:target="setAccount('{{$account->id}}','LOAN')">
                                                            <svg wire:click="setAccount('{{$account->id}}','LOAN')"
                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                 class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-slate-400 p-2"
                                                                 fill="none" viewBox="0 0 24 24"
                                                                 stroke="currentColor"
                                                                 stroke-width="2">
                                                                <path stroke-linecap="round"
                                                                      stroke-linejoin="round"
                                                                      d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                        </div>
                                                    @endif

                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>





                                @endif
                                <div class="mt-2"></div>
                            @endif
                            <div class="mt-2"></div>


                            @if($this->accountSelected)

                                <p for="amount"
                                   class="block mb-2 text-sm font-medium text-slate-600 ">
                                    Enter Amount</p>
                                <x-jet-input id="amount" type="number" name="amount"
                                             class="mt-1 block w-full"
                                             wire:model.bounce="amount" autofocus/>
                                @error('amount')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>Amount is mandatory and should be more than two
                                        characters.</p>
                                </div>
                                @enderror
                                <div class="mt-2"></div>



                                <p for="notes"
                                   class="block mb-2 text-sm font-medium text-slate-600 ">
                                    Enter Notes</p>
                                <x-jet-input id="notes" type="text" name="notes"
                                             class="mt-1 block w-full" wire:model.bounce="notes"
                                             autofocus/>
                                @error('notes')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>Notes is mandatory and should be more than two
                                        characters.</p>
                                </div>
                                @enderror
                            @endif

                            <div class="mt-2"></div>

                        </div>

                        <hr class="border-b-0 my-6"/>

                        <div class="flex justify-end w-auto">
                            <div wire:loading wire:target="process">
                                <button class="text-white bg-blue-800 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 "
                                        disabled>
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="animate-spin  h-5 w-5 mr-2 stroke-white-800"
                                             fill="white" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>

                                        </svg>
                                        <p>Please wait...</p>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end w-auto">
                            <div wire:loading.remove wire:target="process">
                                <button wire:click="process"
                                        class="text-white justify-end item-end flex mt-4 bg-red-700 hover:bg-red-400 focus:outline-none focus:ring-4
                                        focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 ">
                                    <p class="text-white">Deposit</p>
                                </button>

                            </div>
                        </div>

                    </div>

            </div>





            <div class="w-1/3 metric-card border bg-white border-gray-200 rounded-lg p-4">
                <div>
                    <p class="flex items-center text-l font-semibold spacing-sm text-slate-600 mb-4">
                        Loan Application
                    </p>

                    {{-- Success/Error Messages --}}
                    @if (session()->has('message_2'))
                        <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-4" role="alert">
                            <div class="flex">
                                <div class="py-1">
                                    <svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold">Success!</p>
                                    <p class="text-sm">{{ session('message_2') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session()->has('message_fail2'))
                        <div class="bg-red-100 border-t-4 border-red-500 rounded-b text-red-900 px-4 py-3 shadow-md mb-4" role="alert">
                            <div class="flex">
                                <div class="py-1">
                                    <svg class="fill-current h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold">Error!</p>
                                    <p class="text-sm">{{ session('message_fail2') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-4">
                        {{-- Step 1: Member Verification --}}
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Step 1: Member Verification</h3>
                            
                            <div class="mb-3">
                                <label for="member_number1" class="block text-sm font-medium text-slate-600 mb-1">
                                    Member Number
                                </label>
                                <x-jet-input id="member_number1" type="number" name="member_number"
                                    class="w-full border-gray-300 focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                                    wire:model.bounce="member_number1" autofocus/>
                                @error('member_number1')
                                    <p class="text-red-500 text-sm mt-1">{{$message}}</p>
                                @enderror
                            </div>

                            @if($this->member_number1)
                                @if(DB::table('clients')->where('client_number',$this->member_number1)->exists())
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                        <p class="text-green-700 font-medium">Existing Member</p>
                                        <p class="text-sm text-gray-600">
                                            {{DB::table('clients')->where('client_number',$this->member_number1)->value('first_name')}}
                                            {{DB::table('clients')->where('client_number',$this->member_number1)->value('middle_name')}}
                                            {{DB::table('clients')->where('client_number',$this->member_number1)->value('last_name')}}
                                        </p>
                                    </div>
                                @else
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                        <p class="text-red-700">Member not found</p>
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- Step 2: Loan Details --}}
                        @if($this->member_number1 && DB::table('clients')->where('client_number',$this->member_number1)->exists())
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Step 2: Loan Details</h3>

                                <div class="mb-3">
                                    <label for="loan_type_2" class="block text-sm font-medium text-slate-600 mb-1">
                                        Type of Loan
                                    </label>
                                    <select wire:model.bounce="loan_type_2" name="loan_type_2" id="loan_type_2"
                                            class="w-full border-gray-300 focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                                        <option value="">Select loan type</option>
                                        <option value="New">New Loan</option>
                                        <option value="TopUp">Top Up</option>
                                        <option value="Restructuring">Restructuring</option>
                                    </select>
                                    @error('loan_type_2')
                                        <p class="text-red-500 text-sm mt-1">Please select a loan type</p>
                                    @enderror
                                </div>

                                {{-- Show existing loans for TopUp/Restructuring --}}
                                @if($this->loan_type_2 == "TopUp" || $this->loan_type_2 == "Restructuring")
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-slate-600 mb-2">
                                            @if($this->loan_type_2 == "TopUp")
                                                Select Loan to Top-Up (Same Product Only)
                                            @else
                                                Select Loan to Restructure
                                            @endif
                                        </label>
                                        <div class="bg-white rounded-lg border border-gray-200 p-3">
                                            @php
                                                $existingLoans = App\Models\LoansModel::where('client_number',$this->member_number1)
                                                ->where('status','ACTIVE')
                                                    ->get();
                                            @endphp
                                            
                                            @if($existingLoans->count() > 0)
                                                @foreach($existingLoans as $account)
                                                    @php
                                                        $productName = DB::table('loan_sub_products')->where('sub_product_id',$account->loan_sub_product)->value('sub_product_name');
                                                        $currentBalance = DB::table('accounts')->where('account_number', $account->loan_account_number)->value('balance') ?? 0;
                                                        $isSameProduct = $this->loan_product == $account->loan_sub_product;
                                                    @endphp
                                                    
                                                    <div class="flex items-center justify-between py-3 border-b last:border-b-0 {{ $this->loan_type_2 == 'TopUp' && !$isSameProduct ? 'opacity-50' : '' }}">
                                                        <div class="flex-1">
                                                            <div class="flex items-center justify-between mb-1">
                                                                <p class="text-sm font-medium text-gray-900">
                                                                    {{ $productName }}
                                                                </p>
                                                                @if($this->loan_type_2 == "TopUp" && !$isSameProduct)
                                                                    <span class="text-xs text-red-500 bg-red-50 px-2 py-1 rounded">Different Product</span>
                                                                @endif
                                                            </div>
                                                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-500">
                                                                <div>
                                                                    <span class="font-medium">Original Amount:</span>
                                                                    <span>{{number_format($account->principle)}} TZS</span>
                                                                </div>
                                                                <div>
                                                                    <span class="font-medium">Current Balance:</span>
                                                                    <span>{{number_format($currentBalance)}} TZS</span>
                                                                </div>
                                                                <div>
                                                                    <span class="font-medium">Term:</span>
                                                                    <span>{{$account->tenure}} months</span>
                                                                </div>
                                                                <div>
                                                                    <span class="font-medium">Interest Rate:</span>
                                                                    <span>{{$account->interest_rate}}%</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button wire:click="setAccount({{$account->id}},'LOAN')"
                                                                class="p-2 rounded-full {{$this->accountSelected == $account->id ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600'}} {{ $this->loan_type_2 == 'TopUp' && !$isSameProduct ? 'cursor-not-allowed' : '' }}"
                                                                {{ $this->loan_type_2 == 'TopUp' && !$isSameProduct ? 'disabled' : '' }}>
                                                            @if($this->accountSelected == $account->id)
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                </svg>
                                                            @else
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                                </svg>
                                                            @endif
                                                        </button>
                                                    </div>
                                                @endforeach
                                                
                                                @if($this->loan_type_2 == "TopUp")
                                                    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                                        <div class="flex items-start">
                                                            <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            <div>
                                                                <h6 class="text-sm font-semibold text-blue-800">Top-Up Requirements</h6>
                                                                <ul class="text-sm text-blue-700 mt-1 space-y-1">
                                                                    <li>• Only loans with the same product can be topped up</li>
                                                                    <li>• The existing loan balance will be deducted from the new loan amount</li>
                                                                    <li>• Interest rates and terms will match the selected product</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="text-center py-4 text-gray-500">
                                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <p class="text-sm">No active loans found for this member.</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Loan Product Selection (except for Restructuring) --}}
                                @if($this->loan_type_2 != "Restructuring")
                                    <div class="mb-3">
                                        <label for="loan_product" class="block text-sm font-medium text-slate-600 mb-1">
                                            Loan Product
                                        </label>
                                        <select wire:model.bounce="loan_product" name="loan_product" id="loan_product"
                                                class="w-full border-gray-300 focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                                            <option value="">Select loan product</option>
                                            @foreach(DB::table('loan_sub_products')->get() as $loan_product)
                                                <option value="{{$loan_product->sub_product_id}}">{{$loan_product->sub_product_name}}</option>
                                            @endforeach
                                        </select>
                                        @error('loan_product')
                                            <p class="text-red-500 text-sm mt-1">Please select a loan product</p>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="amount2" class="block text-sm font-medium text-slate-600 mb-1">
                                            Loan Amount
                                        </label>
                                        <x-jet-input id="amount2" type="text" name="amount2"
                                            class="w-full border-gray-300 focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                                            wire:model.bounce="amount2" 
                                            placeholder="{{'Max: '.number_format(DB::table('loan_sub_products')->where('sub_product_id',$this->loan_product)->value('principle_max_value')).' TZS | Min: '.number_format(DB::table('loan_sub_products')->where('sub_product_id',$this->loan_product)->value('principle_min_value')).' TZS'}}"/>
                                        @error('amount2')
                                            <p class="text-red-500 text-sm mt-1">{{$message}}</p>
                                        @enderror
                                    </div>

                                    {{-- Loan Term --}}
                                    <div class="mb-3">
                                        <label for="tenure" class="block text-sm font-medium text-slate-600 mb-1">
                                            Loan Term (Months)
                                        </label>
                                        <x-jet-input id="tenure" type="number" name="tenure"
                                            class="w-full border-gray-300 focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                                            wire:model.bounce="tenure" 
                                            placeholder="12"
                                            min="1" max="60"/>
                                        @error('tenure')
                                            <p class="text-red-500 text-sm mt-1">{{$message}}</p>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            {{-- Step 3: Payment Details --}}
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Step 3: Payment Details</h3>

                                <div class="mb-3">
                                    <label for="pay_method" class="block text-sm font-medium text-slate-600 mb-1">
                                        Payment Method
                                    </label>
                                    <select wire:model.bounce="pay_method" name="pay_method" id="pay_method"
                                            class="w-full border-gray-300 focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                                        <option value="">Select payment method</option>
                                        <option value="cash">CASH</option>
                                        <option value="internal_transfer">NBC BANK</option>
                                        <option value="tips_mno">MOBILE</option>
                                        <option value="tips_bank">OTHER BANK</option>
                                    </select>
                                    @error('pay_method')
                                        <p class="text-red-500 text-sm mt-1">Please select a payment method</p>
                                    @enderror
                                </div>

                                {{-- Additional Information for Other Banks Only --}}
                                @if($this->pay_method === "tips_bank")
                                    <div class="space-y-3">
                                        <div>
                                            <label for="tips_bank_code" class="block text-sm font-medium text-slate-600 mb-1">
                                                Select Bank
                                            </label>
                                            <select wire:model.bounce="tips_bank_code" name="tips_bank_code" id="tips_bank_code"
                                                    class="w-full border-gray-300 focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                                                <option value="">Select bank</option>
                                                <option value="CRDB">CRDB Bank</option>
                                                <option value="NMB">NMB Bank</option>
                                                <option value="NBC">NBC Bank</option>
                                                <option value="TPB">TPB Bank</option>
                                                <option value="BOA">Bank of Africa</option>
                                                <option value="DCB">DCB Bank</option>
                                            </select>
                                            @error('tips_bank_code')
                                                <p class="text-red-500 text-sm mt-1">Please select a bank</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="tips_bank_account" class="block text-sm font-medium text-slate-600 mb-1">
                                                Bank Account Number
                                            </label>
                                            <x-jet-input id="tips_bank_account" type="text" name="tips_bank_account"
                                                class="w-full border-gray-300 focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                                                wire:model.bounce="tips_bank_account"/>
                                            @error('tips_bank_account')
                                                <p class="text-red-500 text-sm mt-1">Please enter bank account number</p>
                                            @enderror
                                        </div>
                                    </div>
                                @endif

                                {{-- Payment Method Information Display --}}
                                @if($this->pay_method && $this->pay_method !== "tips_bank")
                                    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                            <div>
                                                <h6 class="text-sm font-semibold text-blue-800">
                                                    @if($this->pay_method === 'cash')
                                                        Cash Disbursement
                                                    @elseif($this->pay_method === 'internal_transfer')
                                                        NBC Bank Transfer
                                                    @elseif($this->pay_method === 'tips_mno')
                                                        Mobile Money Transfer
                                                    @endif
                                                </h6>
                                                <p class="text-sm text-blue-700">
                                                    @if($this->pay_method === 'cash')
                                                        Loan will be disbursed to member's deposit account
                                                    @elseif($this->pay_method === 'internal_transfer')
                                                        Using member's NBC account details from profile
                                                    @elseif($this->pay_method === 'tips_mno')
                                                        Using member's phone number from profile
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Step 4: Loan Officer Assignment --}}
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Step 4: Loan Officer Assignment</h3>

                                <div class="mb-3">
                                    <label for="loan_officer" class="block text-sm font-medium text-slate-600 mb-1">
                                        Assign Loan Officer
                                    </label>
                                    <select wire:model.bounce="loan_officer" name="loan_officer" id="loan_officer"
                                            class="w-full border-gray-300 focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                                        <option value="">Select loan officer</option>
                                        @foreach(App\Models\User::all() as $members)
                                            <option value="{{$members->id}}">
                                                {{$members->name}} 
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('loan_officer')
                                        <p class="text-red-500 text-sm mt-1">Please assign a loan officer</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Submit Button --}}
                            <div class="flex justify-end mt-6">
                                <div wire:loading wire:target="LoanProcess">
                                    <button class="bg-blue-800 hover:bg-blue-400 text-white font-bold py-2 px-4 rounded-lg">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="animate-spin h-5 w-5 mr-2"
                                                fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                            </svg>
                                            Processing...
                                        </div>
                                    </button>
                                </div>

                                <div wire:loading.remove wire:target="LoanProcess">
                                    <x-jet-button wire:click="LoanProcess"
                                        class="bg-blue-800 hover:bg-blue-400 text-white font-bold py-2 px-4 rounded-lg">
                                        Submit Loan Application
                                    </x-jet-button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
        <div class="w-full flex gap-2">    

            <div class="w-1/3 metric-card   border @if($this->item == 7) bg-green-200 border-green-200   @else bg-white  border-gray-200  @endif rounded-lg p-4 ">

                <div class="flex justify-between items-center w-full">
                    <div class="flex items-center">
                        <div wire:loading wire:target="visit(7)">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="animate-spin  h-5 w-5 mr-2 stroke-gray-400"
                                     fill="white" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>

                                </svg>


                                <p>Please wait...</p>
                            </div>

                        </div>
                        <div wire:loading.remove wire:target="visit(7)">


                            <p class="flex items-center text-l font-semibold spacing-sm text-slate-600">
                                Total Withdraw
                            </p>

                        </div>

                    </div>
                    <div class="flex items-center space-x-5">

                        <svg wire:click="visit(7)" xmlns="http://www.w3.org/2000/svg"
                             class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-slate-400 p-2"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>


                    </div>
                </div>

                <table class="w-full">
                    <tr>
                        <td class="mt-2 text-sm font-semibold  text-slate-400  capitalize  text-left">
                            <p>At {{date("Y-m-d H:i:s")}}</p>
                        </td>
                        <td class="pl-2 mt-2 text-sm font-semibold spacing-xl text-slate-400  text-right">
                            <p>
                                {{ DB::table('general_ledger')
                                        ->where('record_on_account_number', function ($query) {
                                            $query->select('account_number')
                                                ->from('accounts')
                                                ->where('id', function ($subquery) {
                                                    $subquery->select('account_id')
                                                        ->from('tellers')
                                                        ->where('employee_id', auth()->user()->employeeId);
                                                });
                                        })
                                        ->whereDate('created_at', now())
                                        ->sum('debit')
                                      }}TZS
                            </p>
                        </td>
                    </tr>

                </table>
            </div>

            <div class="w-1/3  metric-card   border @if($this->item == 4) bg-blue-200 border-blue-200   @else bg-white  border-gray-200  @endif rounded-lg p-4 ">

                <div class="flex justify-between items-center w-full">
                    <div class="flex items-center">
                        <div wire:loading wire:target="visit(4)">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="animate-spin  h-5 w-5 mr-2 stroke-gray-400"
                                     fill="white" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>

                                </svg>


                                <p>Please wait...</p>
                            </div>

                        </div>
                        <div wire:loading.remove wire:target="visit(4)">


                            <p class="flex items-center text-l font-semibold spacing-sm text-slate-600">
                                Total Deposits



                            </p>

                        </div>

                    </div>
                    <div class="flex items-center space-x-5">

                        <svg wire:click="visit(4)" xmlns="http://www.w3.org/2000/svg"
                             class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-slate-400 p-2"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>


                    </div>
                </div>


                <table class="w-full">

                    <tr>
                        <td class="mt-2 text-sm font-semibold  text-slate-400  capitalize  text-left">
                            <p>At {{date("Y-m-d H:i:s")}}</p>
                        </td>
                        <td class="pl-2 mt-2 text-sm font-semibold spacing-xl text-slate-400  text-right">
                            <p>

                                {{
                                        DB::table('general_ledger')
                                                    ->where('record_on_account_number', function ($query) {
                                                        $query->select('account_number')
                                                            ->from('accounts')
                                                            ->where('id', function ($subquery) {
                                                                $subquery->select('account_id')
                                                                    ->from('tellers')
                                                                    ->where('employee_id', auth()->user()->employeeId);
                                                            });
                                                    })
                                                    ->whereDate('created_at',  now())
                                                    ->sum('credit')
                                      }}TZS
                            </p>
                        </td>
                    </tr>

                </table>
            </div>

            <div class="w-1/3  metric-card   border @if($this->item == 5) bg-blue-200 border-blue-200   @else bg-white  border-gray-200  @endif rounded-lg p-4 ">

                <div class="flex justify-between items-center w-full">
                    <div class="flex items-center">
                        <div wire:loading wire:target="visit(4)">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="animate-spin  h-5 w-5 mr-2 stroke-gray-400"
                                     fill="white" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <p>Please wait...</p>
                            </div>
                        </div>
                        <div wire:loading.remove wire:target="visit(4)">
                            <p class="flex items-center text-l font-semibold spacing-sm text-slate-600"> Application Amount </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-5">
                        <svg wire:click="visit(4)" xmlns="http://www.w3.org/2000/svg"
                             class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-slate-400 p-2"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <table class="w-full">

                    <tr>
                        <td class="mt-2 text-sm font-semibold  text-slate-400  capitalize  text-left">
                            <p>At {{date("Y-m-d H:i:s")}}</p>
                        </td>
                        <td class="pl-2 mt-2 text-sm font-semibold spacing-xl text-slate-400  text-right">
                            <p>

                                {{
                                        DB::table('loans')->whereDate('created_at',now())->sum('principle')

                                      }}TZS
                            </p>
                        </td>
                    </tr>

                </table>
            </div>

        </div>
    </div>




</div>
