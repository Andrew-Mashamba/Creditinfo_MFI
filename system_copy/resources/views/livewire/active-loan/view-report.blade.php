<div>


    <div>{{-- In work, do what you enjoy. --}}
        <div>
            <nav class="bg-blue-100   rounded-lg pl-2 pr-2 ">
                <div class="relative flex h-16 items-center justify-between">
                    <div class="flex flex-1 items-center justify-between">
                        <div class="flex flex-shrink-0 items-start">
                            <div class="">
                                <div class="flex items-center justify-between">

                                    <p class="font-semibold ml-3 text-slate-600 text-red-900">
                                        {{ App\Models\ClientsModel::where('client_number', Session::get('client_number'))->value('first_name') . ' ' . App\Models\ClientsModel::where('client_number', Session::get('client_number'))->value('middle_name') . ' ' . App\Models\ClientsModel::where('client_number', Session::get('client_number'))->value('last_name') }}
                                    </p>

                                </div>
                                <div class="text-red-400 ml-4 mr-5 ">
                                    <div class="text-blue-900 font-bold">
                                        {{ App\Models\ClientsModel::where('client_number', Session::get('client_number'))->value('client_status') }}
                                    </div>
                                </div>


                            </div>

                        </div>
                        <div class="flex">
                            <div class="flex space-x-4">
                                <!-- Current: "bg-gray-900 text-white", Default:"text-gray-300 hover:bg-gray-700 hover:text-white" -->
                            </div>
                            <button type="button"  class="rounded-full bg-white p-1 text-gray-400 hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                                <svg wire:click="closeView"
                                    xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 " fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </nav>
            <div class="relative flex py-5 items-center">
                <div class="flex-grow border-t border-gray-400"></div>
                <span class="flex-shrink mx-4 text-gray-400"> General Info  </span>
                <div class="flex-grow border-t border-gray-400"></div>
            </div>

            @foreach ($loan_general_info as $info )
                <div
                class="grid grid-cols-1 sm:grid-cols-2 p-4 border-blue-800 border-2 md:grid-cols-3 gap-6 pb-4 bg-gray-50 rounded-xl p-4 mt-4">

                <div>
                    <label for="hisa"
                        class="block text-sm font-medium text-gray-700"> Principle Amount</label>
                    <div class="text-sm font-medium text-gray-500"> {{ number_format($info->principle,2) }} TZS </div>
                </div>

                <div>
                    <label for="akiba"
                        class="block text-sm font-medium text-gray-700"> Interest </label>
                    <div class="text-sm font-medium text-gray-500">  {{ $info->interest }} %</div>
                </div>

                <div>
                    <label for="amana"
                        class="block text-sm font-medium text-gray-700"> Tenure </label>
                    <div class="text-sm font-medium text-gray-500"> {{ $info->tenure }} months</div>
                </div>

                <div>
                    <label for="amana"
                        class="block text-sm font-medium text-gray-700"> Outstanding balance </label>
                    <div class="text-sm font-medium text-gray-500">
                        {{
                        number_format(
                        DB::table('loans_schedules')
                            ->where('loan_id', $info->loan_id)
                            ->where('completion_status', '!=', "CLOSED")
                            ->sum('installment'),2)
                        }} TZS </div>
                </div>



                <div>
                    <label for="amana"
                        class="block text-sm font-medium text-gray-700"> Collateral Value </label>
                    <div class="text-sm font-medium text-gray-500"> {{ number_format(DB::table('collaterals')
                        ->where('loan_id', $info->id)
                        ->sum(DB::raw('CAST(collateral_value AS DECIMAL(10,2))'))
                    ,2) }} TZS</div>
                </div>




                </div>




            @endforeach

            <div class="relative flex py-5 items-center">
                <div class="flex-grow border-t border-gray-400"></div>
                <span class="flex-shrink mx-4 text-gray-400"> Client  Data </span>
                <div class="flex-grow border-t border-gray-400"></div>
            </div>

            <div class="w-full">
                @php        $members=\App\Models\ClientsModel::where('client_number',session()->get('client_number'))->get();   @endphp
                @foreach ($members as $member)
                    {{-- <div class="  w-full  items-center justify-center ">
                        <section
                            class="bg-white-300 flex flex-col items-center justify-center rounded-full mx-auto"
                            style="width: 200px; height: 200px;">
                            @if ($member->profile_photo_path)
                                <img class="object-fill  rounded-full"
                                    src="{{ $member->profile_photo_path }}"
                                    style="width: 200px; height: 200px;">
                            @else
                                <img class="object-fill  rounded-full"
                                    src="{{ asset('images/avatar.png') }}"
                                    style="width: 200px; height: 200px;">
                            @endif
                        </section>

                    </div> --}}


                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 pb-4 bg-gray-50 rounded-xl p-4 mt-4">

                        <div>
                            <label for="hisa"
                                class="block text-sm font-medium text-gray-700">Hisa</label>
                            <div class="text-sm font-medium text-gray-500">{{ $member->hisa }}</div>
                        </div>

                        <div>
                            <label for="akiba"
                                class="block text-sm font-medium text-gray-700">Akiba</label>
                            <div class="text-sm font-medium text-gray-500">{{ $member->akiba }}</div>
                        </div>

                        <div>
                            <label for="amana"
                                class="block text-sm font-medium text-gray-700">Amana</label>
                            <div class="text-sm font-medium text-gray-500">{{ $member->amana }}</div>
                        </div>

                    </div>



                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 pb-4 bg-gray-50 rounded-xl p-4 mt-4">
                        <div>
                            <label for="membership_type"
                                class="block text-sm font-medium text-gray-700">Membership Type</label>
                            <div class="text-sm font-medium text-gray-500">{{ $member->membership_type }}
                            </div>
                        </div>

                        <div>
                            <label for="branch"
                                class="block text-sm font-medium text-gray-700">Branch</label>
                            <div class="text-sm font-medium text-gray-500">
                                {{ App\Models\BranchesModel::find($member->branch)->name }}</div>
                        </div>

                        <div>
                            <label for="phone_number"
                                class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <div class="text-sm font-medium text-gray-500">{{ $member->phone_number }}
                            </div>
                        </div>

                        @if ($member->membership_type == 'Business' or $member->membership_type == 'Group')
                            <div>
                                <label for="business_name"
                                    class="block text-sm font-medium text-gray-700">Business Name</label>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $member->business_name }}</div>
                            </div>
                        @endif

                        @if ($member->membership_type == 'Individual')
                            <div>
                                <label for="first_name"
                                    class="block text-sm font-medium text-gray-700">First Name</label>
                                <div class="text-sm font-medium text-gray-500">{{ $member->first_name }}
                                </div>
                            </div>
                            <div>
                                <label for="middle_name"
                                    class="block text-sm font-medium text-gray-700">Middle Name</label>
                                <div class="text-sm font-medium text-gray-500">{{ $member->middle_name }}
                                </div>
                            </div>
                            <div>
                                <label for="last_name"
                                    class="block text-sm font-medium text-gray-700">Last Name</label>
                                <div class="text-sm font-medium text-gray-500">{{ $member->last_name }}
                                </div>
                            </div>
                        @endif

                        @if ($member->membership_type == 'Business' or $member->membership_type == 'Group')
                            <div>
                                <label for="incorporation_number"
                                    class="block text-sm font-medium text-gray-700">Incorporation
                                    Number</label>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $member->incorporation_number }}</div>
                            </div>
                        @endif

                        <div>
                            <label for="email"
                                class="block text-sm font-medium text-gray-700">Email</label>
                            <div class="text-sm font-medium text-gray-500">{{ $member->email }}</div>
                        </div>

                        @if ($member->membership_type == 'Individual')
                            <div>
                                <label for="place_of_birth"
                                    class="block text-sm font-medium text-gray-700">Place of Birth</label>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $member->place_of_birth }}</div>
                            </div>
                            <div>
                                <label for="marital_status"
                                    class="block text-sm font-medium text-gray-700">Marital Status</label>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $member->marital_status }}</div>
                            </div>
                        @endif

                        <div>
                            <label for="address"
                                class="block text-sm font-medium text-gray-700">Address</label>
                            <div class="text-sm font-medium text-gray-500">{{ $member->address }}</div>
                        </div>

                        @if ($member->membership_type == 'Individual')
                            <div>
                                <label for="next_of_kin_name"
                                    class="block text-sm font-medium text-gray-700">Next of Kin
                                    Name</label>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $member->next_of_kin_name }}</div>
                            </div>
                            <div>
                                <label for="next_of_kin_phone"
                                    class="block text-sm font-medium text-gray-700">Next of Kin
                                    Phone</label>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $member->next_of_kin_phone }}</div>
                            </div>
                        @endif

                        <div>
                            <label for="tin_number" class="block text-sm font-medium text-gray-700">TIN
                                Number</label>
                            <div class="text-sm font-medium text-gray-500">{{ $member->tin_number }}</div>
                        </div>

                        <div>
                            <label for="nationarity"
                                class="block text-sm font-medium text-gray-700">Nationality</label>
                            <div class="text-sm font-medium text-gray-500">{{ $member->nationarity }}
                            </div>
                        </div>

                        @if ($member->membership_type == 'Individual')
                            <div>
                                <label for="number_of_spouse"
                                    class="block text-sm font-medium text-gray-700">Number of
                                    Spouse</label>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $member->number_of_spouse }}</div>
                            </div>
                            <div>
                                <label for="number_of_children"
                                    class="block text-sm font-medium text-gray-700">Number of
                                    Children</label>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $member->number_of_children }}</div>
                            </div>
                            <div>
                                <label for="gender"
                                    class="block text-sm font-medium text-gray-700">Gender</label>
                                <div class="text-sm font-medium text-gray-500">{{ $member->gender }}</div>
                            </div>
                            <div>
                                <label for="date_of_birth"
                                    class="block text-sm font-medium text-gray-700">Date of Birth</label>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $member->date_of_birth }}</div>
                            </div>
                        @endif

                        <div>
                            <label for="citizenship"
                                class="block text-sm font-medium text-gray-700">Citizenship</label>
                            <div class="text-sm font-medium text-gray-500">{{ $member->citizenship }}
                            </div>
                        </div>

                        @if ($member->membership_type == 'Individual')
                            <div>
                                <label for="employment"
                                    class="block text-sm font-medium text-gray-700">Employment</label>
                                <div class="text-sm font-medium text-gray-500">{{ $member->employment }}
                                </div>
                            </div>
                            <div>
                                <label for="employer_name"
                                    class="block text-sm font-medium text-gray-700">Employer Name</label>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $member->employer_name }}</div>
                            </div>
                            <div>
                                <label for="education"
                                    class="block text-sm font-medium text-gray-700">Education</label>
                                <div class="text-sm font-medium text-gray-500">{{ $member->education }}
                                </div>
                            </div>
                        @endif

                        <div>
                            <label for="income_available"
                                class="block text-sm font-medium text-gray-700">Income Available</label>
                            <div class="text-sm font-medium text-gray-500">{{ $member->income_available }}
                            </div>
                        </div>

                        <div>
                            <label for="income_source"
                                class="block text-sm font-medium text-gray-700">Income Source</label>
                            <div class="text-sm font-medium text-gray-500">{{ $member->income_source }}
                            </div>
                        </div>

                        <div>
                            <label for="guarantor_first_name"
                                class="block text-sm font-medium text-gray-700">Guarantor First
                                Name</label>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $member->guarantor_first_name }}</div>
                        </div>

                        <div>
                            <label for="guarantor_middle_name"
                                class="block text-sm font-medium text-gray-700">Guarantor Middle
                                Name</label>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $member->guarantor_middle_name }}</div>
                        </div>

                        <div>
                            <label for="guarantor_last_name"
                                class="block text-sm font-medium text-gray-700">Guarantor Last Name</label>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $member->guarantor_last_name }}</div>
                        </div>

                        <div>
                            <label for="guarantor_full_name"
                                class="block text-sm font-medium text-gray-700">Guarantor Membership
                                Number</label>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $member->guarantor_full_name }}</div>
                        </div>

                        <div>
                            <label for="guarantor_email"
                                class="block text-sm font-medium text-gray-700">Guarantor Email</label>
                            <div class="text-sm font-medium text-gray-500">{{ $member->guarantor_email }}
                            </div>
                        </div>


                        <div>
                            <label for="barua" class="block text-sm font-medium text-gray-700">Barua Ya
                                Maombi</label>
                            <!-- Input type file does not have a direct display option -->
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>








    <div class="relative flex py-5 items-center">
        <div class="flex-grow border-t border-gray-400"></div>
        <span class="flex-shrink mx-4 text-gray
        -400"> Collateral Information </span>
        <div class="flex-grow border-t border-gray-400"></div>
    </div>


    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-6 pb-2 bg-gray-50 rounded-xl p-4 mt-4">

    @foreach ($collaterals as $data )

    <div class="grid grid-cols-2 border-blue-900 border-2 sm:grid-cols-2 md:grid-cols-2 gap-6 pb-4 bg-white rounded-xl p-4 mt-4">

        <div>
            <span class="bg-blue-900 text-white rounded-full px-3 py-1 text-center">
                {{ $loop->iteration }}
              </span>

          <label for="membership_type"
              class="block text-sm font-medium text-gray-700"> Collateral Type  </label>
          <div class="text-sm font-medium text-gray-500">{{ str_replace("_"," ", $data->main_collateral_type) }}
          </div>
       </div>

       <div>
        <span class="text-white  px-3 py-1 text-center">

          </span>

          <label for="membership_type"
          class="block text-sm font-medium text-gray-700"> Collateral Category  </label>
         <div class="text-sm font-medium text-gray-500">{{ $data->collateral_category }} </div>
      </div>

      <div>
      <label for="membership_type"
        class="block text-sm font-medium text-gray-700"> Collateral Value  </label>
        <div class="text-sm font-medium text-gray-500">{{  number_format( $data->collateral_value )}} TZS  </div>
      </div>

        <div>
        <label for="membership_type"
        class="block text-sm font-medium text-gray-700"> Account number   </label>
        <div class="text-sm font-medium text-gray-500">{{DB::table('accounts')->where('id',$data->account_id)->value('account_number') }}
        </div>
        </div>

        @if($data->main_collateral_type=="self_guaranted")

        <div>
            <label for="membership_type"
            class="block text-sm font-medium text-gray-700"> Member Id   </label>
            <div class="text-sm font-medium text-gray-500">{{ $data->member_number }}
            </div>
            </div>


            <div>
                <label for="membership_type"
                class="block text-sm font-medium text-gray-700"> Member Name    </label>
                <div class="text-sm font-medium text-gray-500">
                     @php
                         $member=DB::table('clients')->where('client_number',$data->member_number)->first();

                         $name = $member->first_name." ". $member->middle_name ." ".  $member->last_name;
                     @endphp
                    {{$name  }}
                </div>
                </div>
        @endif
    </div>
    @endforeach
    </div>
    <div class="relative flex py-5 items-center">
        <div class="flex-grow border-t border-gray-400"></div>
        <span class="flex-shrink mx-4 text-gray-400"> Business Information </span>
        <div class="flex-grow border-t border-gray-400"></div>
    </div>


    @foreach ($businessInfo as $value )


    <div class="grid grid-cols-2 border-blue-900 border-2 sm:grid-cols-2 md:grid-cols-2 gap-6 pb-4 bg-white rounded-xl p-4 mt-4">

        <div>
          <label for="membership_type"
              class="block text-sm font-medium text-gray-700">  Business Name  </label>
          <div class="text-sm font-medium text-gray-500">{{ $value->business_name }}
          </div>
       </div>

       <div>


        <label for="membership_type"
            class="block text-sm font-medium text-gray-700">  Age of the business
           </label>
        <div class="text-sm font-medium text-gray-500">{{ $value->business_name }}
        </div>
     </div>


     <div>


        <label for="membership_type"
            class="block text-sm font-medium text-gray-700">   Business Type

        </label>
        <div class="text-sm font-medium text-gray-500">{{ $value->business_type }}
        </div>
     </div>

     <div>


        <label for="membership_type"
            class="block text-sm font-medium text-gray-700">   Business Category

        </label>
        <div class="text-sm font-medium text-gray-500">{{ $value->business_category }}
        </div>
     </div>


     <div>


        <label for="membership_type"
            class="block text-sm font-medium text-gray-700">   Business Licence Number
        </label>
        <div class="text-sm font-medium text-gray-500">{{ $value->business_licence_number}}
        </div>
     </div>



     <div>
        <label for="membership_type"
            class="block text-sm font-medium text-gray-700">
            Business TIN Number
        </label>
        <div class="text-sm font-medium text-gray-500">{{ $value->business_tin_number }}
        </div>
     </div>



     <div>
        <label for="membership_type"
            class="block text-sm font-medium text-gray-700">
            Business Inventory
        </label>
        <div class="text-sm font-medium text-gray-500">{{ number_format($value->business_inventory,2) }}TZS
        </div>
     </div>

     <div>
        <label for="membership_type"
            class="block text-sm font-medium text-gray-700">
            Cash At Hand
        </label>
        <div class="text-sm font-medium text-gray-500">{{ number_format($value->cash_at_hand,2) }} TZS
        </div>
     </div>

     <div>
        <label for="membership_type"
            class="block text-sm font-medium text-gray-700">
            Daily Average Sales
        </label>
        <div class="text-sm font-medium text-gray-500">{{  number_format($value->daily_sales ,2)}} TZS
        </div>
     </div>

     <div>
        <label for="membership_type"
            class="block text-sm font-medium text-gray-700">
            Cost Of Sales Per Month
        </label>
        <div class="text-sm font-medium text-gray-500">{{ number_format($value->cost_of_goods_sold ,2) }} TZS
        </div>
     </div>

     <div>
        <label for="membership_type"
            class="block text-sm font-medium text-gray-700">
            Operating Expenses
        </label>
        <div class="text-sm font-medium text-gray-500">{{ number_format($value->operating_expenses ,2)}} TZS
        </div>
     </div>

     <div>
        <label for="membership_type"
            class="block text-sm font-medium text-gray-700">
            Taxes Per Month
        </label>
        <div class="text-sm font-medium text-gray-500">{{ number_format($value->monthly_taxes,2) }} TZS
        </div>
     </div>

     <div>
        <label for="membership_type"
            class="block text-sm font-medium text-gray-700">
            Other Expenses
        </label>
        <div class="text-sm font-medium text-gray-500">{{ number_format($value->other_expenses,2 ) }}
        </div>
     </div>

    </div>
    @endforeach


    <div class="relative flex py-5 items-center">
        <div class="flex-grow border-t border-gray-400"></div>
        <span class="flex-shrink mx-4 text-gray-400"> Repayment Scedule  </span>
        <div class="flex-grow border-t border-gray-400"></div>
    </div>




    <div class="flex justify-end gap-2">
        <div class="flex space-x-4">
            <!-- Current: "bg-gray-900 text-white", Default:"text-gray-300 hover:bg-gray-700 hover:text-white" -->
        </div>
        <button type="button" wire:click="calculateArrearsAndPenalties()" class="flex items-center gap-2 rounded-full bg-white p-1 text-gray-400 hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
            <svg data-slot="icon" class="h-8 w-8" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"></path>
            </svg>
            <span>Run Arrears</span>
        </button>
    </div>

    <table class="min-w-full border-collapse border-2 border-blue-900 bg-white rounded-xl mt-4">
        <thead class="bg-gray-50">
        <tr>
            <th class="px-4 py-2 text-sm font-medium text-gray-700 border-b border-blue-900">Installment Date</th>
            <th class="px-4 py-2 text-sm font-medium text-gray-700 border-b border-blue-900">Opening Balance</th>
            <th class="px-4 py-2 text-sm font-medium text-gray-700 border-b border-blue-900">Installment</th>
            <th class="px-4 py-2 text-sm font-medium text-gray-700 border-b border-blue-900">Interest</th>
            <th class="px-4 py-2 text-sm font-medium text-gray-700 border-b border-blue-900">Principle</th>
            <th class="px-4 py-2 text-sm font-medium text-gray-700 border-b border-blue-900">Closing Balance</th>
            <th class="px-4 py-2 text-sm font-medium text-gray-700 border-b border-blue-900">Payment</th>
            <th class="px-4 py-2 text-sm font-medium text-gray-700 border-b border-blue-900">Completion Status</th>
            <th class="px-4 py-2 text-sm font-medium text-gray-700 border-b border-blue-900">Penalties</th>
            <th class="px-4 py-2 text-sm font-medium text-gray-700 border-b border-blue-900">Amount in Arrears</th>
            <th class="px-4 py-2 text-sm font-medium text-gray-700 border-b border-blue-900">Days in Arrears</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($loan_schedules as $data)
            <tr>
                <td class="px-4 py-2 text-sm font-medium text-gray-500 border-b border-blue-900">{{ $data->installment_date }}</td>
                <td class="px-4 py-2 text-sm font-medium text-gray-500 border-b border-blue-900">{{ number_format($data->opening_balance, 2) }}</td>
                <td class="px-4 py-2 text-sm font-medium text-gray-500 border-b border-blue-900">{{ number_format($data->installment, 2) }}</td>
                <td class="px-4 py-2 text-sm font-medium text-gray-500 border-b border-blue-900">{{ number_format($data->interest, 2) }}</td>
                <td class="px-4 py-2 text-sm font-medium text-gray-500 border-b border-blue-900">{{ number_format($data->principle, 2) }}</td>
                <td class="px-4 py-2 text-sm font-medium text-gray-500 border-b border-blue-900">{{ number_format($data->closing_balance, 2) }}</td>
                <td class="px-4 py-2 text-sm font-medium text-gray-500 border-b border-blue-900">{{ number_format($data->payment, 2) }}</td>
                <td class="px-4 py-2 text-sm font-medium text-gray-500 border-b border-blue-900">{{ $data->completion_status }}</td>
                <td class="px-4 py-2 text-sm font-medium text-gray-500 border-b border-blue-900">{{ number_format($data->penalties, 2) }}</td>
                <td class="px-4 py-2 text-sm font-medium text-gray-500 border-b border-blue-900">{{ number_format($data->amount_in_arrears, 2) }}</td>
                <td class="px-4 py-2 text-sm font-medium text-gray-500 border-b border-blue-900">{{ $data->days_in_arrears }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>









</div>
