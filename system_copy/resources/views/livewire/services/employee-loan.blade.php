<div>
    <div class="w-full metric-card   border bg-white  border-gray-200  rounded-lg p-4 ">
        <div>
            <p class="flex items-center text-l font-semibold spacing-sm text-slate-600">
                Loan Application
            </p>
            <div>
                @if (session()->has('message_2'))

                    {{--                                @if (session()->has('alert-class'))--}}
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
                                <p class="text-sm">{{ session('message_2') }} </p>
                            </div>
                        </div>
                    </div>
                    {{--                                @endif--}}
                @endif

                 @if (session()->has('message_fail2'))

                    {{--                                @if (session()->has('alert-class'))--}}
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
                                <p class="font-bold">The process has
                                    failed</p>
                                <p class="text-sm">{{ session('message_fail2') }} </p>
                            </div>
                        </div>
                    </div>
                    {{--                                @endif--}}
                @endif



            </div>

            <hr class="border-b-0 my-4"/>

            <div class="">


                <p for="national_id"
                   class="block mb-2 text-sm font-medium text-slate-600 ">
                    NIDA Number</p>

                <x-jet-input id="national_id" type="number" name="national_id"
                             class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                             wire:model.bounce="national_id" autofocus/>

                @error('national_id')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>{{$message}}</p>
                </div>
                @enderror
                <div class="mt-2"></div>

                @if($this->national_id)
                    @if(DB::table('clients')->where('nida_number',$this->national_id)->exists() )
                        <div class="mt-1 mx-2">
                            <div class="fw-bold ">
                                <div class="max-w-md bg-green-100 mx-auto bg-white p-4 rounded-lg shadow">
                                    <div class="fw-bold"> This is existing client </div>
                                    <tr class="text-black"> <th> Full Name</th> <th>{{DB::table('clients')->where('national_id',$this->national_id)->value('first_name') }} </th> <th>{{DB::table('clients')->where('national_id',$this->national_id)->value('middle_name')}} </th> <th> {{DB::table('clients')->where('national_id',$this->national_id)->value('last_name')}} </th> </tr>
                                </div>
                            </div>
                        </div>
                    @else

                        <div class="mt-1 mx-2">
                            <div class="fw-bold ">
                                <div class=" text-red-400 max-w-md bg-green-100 mx-auto bg-white p-4 rounded-lg shadow">
                                    Nida Number is not found

                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <p for="amount2"
                   class="block mb-2 text-sm font-medium text-slate-600 ">
                    Loan Product </p>
                <select wire:model.bounce="loan_product" name="loan_product" id="loan_product"
                        class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                    <option selected value="">Select</option>
                    @foreach(DB::table('loan_sub_products')->get() as $loan_product)
                        <option value="{{$loan_product->sub_product_id}}">{{$loan_product->sub_product_name}}</option>
                    @endforeach
                </select>
                @error('loan_product')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>Loan Product field is required</p>
                </div>
                @enderror
                <div class="mt-2"></div>


                @if($this->loan_product)
                    <p for="amount2"
                       class="block mb-2 text-sm font-medium text-slate-600 ">
                        Amount </p>
                    <x-jet-input id="amount2" type="text" name="amount2"
                                 class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                                 wire:model.bounce="amount2" autofocus placeholder="{{'max  '.number_format(DB::table('loan_sub_products')->where('sub_product_id',$this->loan_product)->value('principle_max_value')).'TZS   min-value '.number_format(DB::table('loan_sub_products')->where('sub_product_id',$this->loan_product)->value('principle_min_value')).'TZS'}}"/>
                    @error('amount2')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Amount is mandatory</p>
                    </div>
                    @enderror
                    <div class="mt-2"></div>
                    {{--                                @if((double)$this->amount2 >  number_format(DB::table('loan_sub_products')->where('id',$this->loan_product)->value('principle_max_value')) || (double)$this->amount2 < number_format(DB::table('loan_sub_products')->where('id',$this->loan_product)->value('principle_min_value')))--}}
                    {{--                                      <div class="w-full bg-danger">--}}
                    {{--                                          Invalid amount--}}
                    {{--                                      </div>--}}
                    {{--                                  @endif--}}
                @endif
                <p for="pay_method"
                   class="block mb-2 text-sm font-medium text-slate-600 ">
                    Payment Method. </p>
                <select wire:model.bounce="pay_method" name="pay_method" id="pay_type"
                        class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                    <option selected value="">Select</option>
                    <option value="CASH"> CASH</option>
                    <option value="MOBILE"> MOBILE</option>
                    <option value="BANK"> BANK</option>
                </select>
                @error('pay_method')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>Payment method is mandatory</p>
                </div>
                @enderror
                <div class="mt-2"></div>

                @if($this->pay_method==="BANK")
                    <p for="bank5"
                       class="block mb-2 text-sm font-medium text-slate-600 ">
                        Select Bank</p>
                    <select wire:model.bounce="bank5" name="bank5" id="bank5"
                            class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                        <option selected value="">Select</option>
                        @foreach(DB::table('accounts')->where('category_code',1000)->get() as $bank)
                            <option value="{{$bank->id}}">{{$bank->account_name.'('.$bank->account_number.')'}}
                            </option>
                        @endforeach

                    </select>
                    @error('bank5')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Bank is mandatory.</p>
                    </div>
                    @enderror

                    <p
                       class="block mb-2 text-sm font-medium text-slate-600 ">
                        Bank Account</p>
                    <x-jet-input id="bankAcc" type="number" name="bankAcc"
                                 class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                                 wire:model.bounce="bankAcc" autofocus />
                    @error('bankAcc')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Bank account is mandatory</p>
                    </div>
                    @enderror
                    <div class="mt-2"></div>


                @endif

                @if($this->pay_method==="MOBILE")
                    <p
                       class="block mb-2 text-sm font-medium text-slate-600 ">
                        Phone Number</p>
                    <x-jet-input id="LoanPhoneNo" type="number" name="LoanPhoneNo"
                                 class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                                 wire:model.bounce="LoanPhoneNo" autofocus />
                    @error('LoanPhoneNo')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Phone number is mandatory</p>
                    </div>
                    @enderror
                    <p for="bank5"
                       class="block mb-2 text-sm font-medium text-slate-600 ">
                        Select Bank</p>
                    <select wire:model.bounce="bank5" name="bank5" id="bank5"
                            class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                        <option selected value="">Select</option>
                        @foreach(DB::table('accounts')->where('category_code',1000)->get() as $bank)
                            <option value="{{$bank->id}}">{{$bank->account_name.'('.$bank->account_number.')'}}
                            </option>
                        @endforeach

                    </select>
                    @error('bank5')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Bank is mandatory.</p>
                    </div>
                    @enderror


                    <div class="mt-2"></div>

                @endif


                <p for="member1"
                   class="block mb-2 text-sm font-medium text-gray-900 ">
                    Assign Loan Officer</p>
                <select wire:model.bounce="loan_officer" name="member1" id="member1"
                        class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm">
                    <option selected value="">Select</option>
                    @foreach(App\Models\Employee::all() as $members)
                        <option value="{{$members->id}}">{{$members->first_name}} {{$members->middle_name}} {{$members->last_name}}</option>
                    @endforeach

                </select>
                @error('loan_officer')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>Loan Officer is mandatory.</p>
                </div>
                @enderror
                <div class="mt-2"></div>

                <div class="mt-2"></div>


                @if($this->accountSelected1)

                    <p for="amount1"
                       class="block mb-2 text-sm font-medium text-slate-600 ">
                        Enter Amount'</p>
                    <x-jet-input id="amount1" type="number" name="amount1"
                                 class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                                 wire:model.bounce="amount1" autofocus/>
                    @error('amount1')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Amount is mandatory and should be more than two
                            characters.</p>
                    </div>
                    @enderror
                    <div class="mt-2"></div>


                    <p for="notes1"
                       class="block mb-2 text-sm font-medium text-slate-600 ">
                        Enter Notes'</p>
                    <x-jet-input id="notes1" type="text" name="notes1"
                                 class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                                 wire:model.bounce="notes1" autofocus/>
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
                <div wire:loading wire:target="LoanProcess">
                    <x-jet-button  disabled>
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
                <div wire:loading.remove wire:target="LoanProcess">
                    <x-jet-button wire:click="LoanProcess"
                            class="text-white bg-green-400 hover:bg-green-500 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2  ">
                        <p class="text-white">Apply Loan</p>
                    </x-jet-button>
                </div>
            </div>

        </div>

    </div>
</div>
