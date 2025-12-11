

<div class="accordion-body py-4 px-5">
    {{--    <div class="flex space-x-4 mb-4">--}}
    {{--        <button wire:click="makePaymentForm" class="mr-4 flex text-center items-center    very-light-shade   text-red-400 font-bold   bg-gray-100   text-gray-400 font-semibold    py-2 px-4 rounded-lg" onmouseover="this.style.backgroundColor='#B4E4FC'; this.style.color='#60A5FA';" onmouseout="this.style.backgroundColor=''; this.style.color='';">--}}

    {{--            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2 fill-current">--}}
    {{--                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />--}}
    {{--            </svg>--}}
    {{--            Money Transfer--}}
    {{--        </button>--}}
    {{--    </div>--}}





    {{--    @if($this->makeTransaction)--}}

    @if(Session::get('userRole') == 'Teller')
        <div class="flex flex-col w-full">
            <div class="w-full flex space-x-1">
                <div class="w-1/2 metric-card  dark:bg-gray-900 border bg-white  border-gray-200 dark:border-gray-800 rounded-lg p-4 ">
                    <div>
                        <p class="flex items-center text-l font-semibold spacing-sm text-slate-600">
                            Teller/Strong Room  Transactions
                        </p>
                        <div>
                            @if (session()->has('message'))
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
                                            <p class="text-sm">{{ session('message') }} </p>
                                        </div>
                                    </div>
                                </div>

                            @endif

                            @if (session()->has('message_fail'))
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
                                            <p class="font-bold">The process failed
                                            </p>
                                            <p class="text-sm">{{ session('message_fail') }} </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <hr class="boder-b-0 my-4"/>
                        <div class="">

                            <p for="member"
                               class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">
                                Select from   teller account </p>
                            <select wire:model="source_teller_account" name="account_number" id="account_number"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                <option selected value="">Select</option>
                                @foreach(App\Models\Teller::where('branch_id', auth()->user()->branch)->where('institution_id',auth()->user()->institution_id)->get()  as $teller)
                                    <option value="{{DB::table('accounts')->where('id',$teller->account_id)->value('account_number')}}"> {{DB::table('accounts')->where('id',$teller->account_id)->value('account_name')}} ({{DB::table('accounts')->where('id',$teller->account_id)->value('account_number')}})</option>
                                @endforeach

                                @foreach(App\Models\AccountsModel::where('institution_number',auth()->user()->institution_id)->where('sub_category_code','1025')->get() as  $account)
                                    <option value="{{$account->account_number}}"> {{$account->account_name}} ({{$account->account_number}})</option>
                                @endforeach


                            </select>

                            </select>
                            @error('account_number')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Teller details</p>
                            </div>
                            @enderror

                            @if($this->source_teller_account)
                                <div class="bg-white shadow-md rounded-md overflow-hidden">
                                    <table class="min-w-full">
                                        <td class="">
                                            <tr>
                                        <td class="py-2 px-4 text-left">Teller Name</td>
                                        <td class="py-2 px-4  text-left">

                                            @php
                                                $userSelected1=DB::table('employees')->where('id',
                                                                                               DB::table('tellers')->where('account_id',
                                                                                                                             DB::table('accounts')->where('account_number',$this->source_teller_account)->value('id'))->value('employee_id')
                                                                                                                                                                                                            )->get();
                                            @endphp

                                            @foreach($userSelected1 as $user1)
                                                {{$user1->first_name}}
                                                {{$user1->middle_name}}
                                                {{$user1->last_name}}
                                            @endforeach

                                            </tr>
                                        </td>
                                        <tbody>

                                        <tr class="border-t">
                                            <td class="py-2 px-4 text-left">Available Amount</td>
                                            <td class="py-2 px-4 text-left">{{ number_format(DB::table('accounts')->where('account_number',$this->source_teller_account)->value('balance'))}} TZS</td>

                                        </tr>
                                        <!-- Add more rows as needed -->
                                        </tbody>
                                    </table>
                                </div>



                            @endif




                            <div class="mt-2"></div>


                            <p for="reference_number"
                               class="block mb-2 text-sm font-medium text-slate-600 dark:text-gray-400">
                                Amount to be transfered</p>
                            <x-jet-input id="reference_number" type="text"
                                         name="reference_number" class="mt-1 block w-full"  wire:model="source_teller_amount" autofocus/>


                            <p for="member"
                               class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">
                                Select destination teller account </p>
                            <select wire:model="destination_teller_account" name="account_number" id="account_number"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                <option selected value="">Select</option>
                                @foreach($this->tellerAccount as  $account)
                                    <option value="{{$account->account_number}}"> {{$account->account_name}} ({{$account->account_number}})</option>
                                @endforeach


                            </select>
                            @error('account_number')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>destination teller account is required.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>


                            @if($this->destination_teller_account)
                                <div class="bg-white shadow-md rounded-md overflow-hidden">
                                    <table class="min-w-full">
                                        <td class="">
                                            <tr>
                                        <td class="py-2 px-4 text-left">Teller Name</td>
                                        <td class="py-2 px-4  text-left">
                                            @php
                                                $userSelected=DB::table('employees')->where('id',
                                                                                               DB::table('tellers')->where('account_id',
                                                                                                                             DB::table('accounts')->where('account_number',$this->destination_teller_account)->value('id'))->value('employee_id')

                                                                                                                             )->get();
                                            @endphp

                                            @foreach($userSelected as $user)
                                                {{$user->first_name}}
                                                {{$user->middle_name}}
                                                {{$user->last_name}}

                                            @endforeach




                                            </tr>
                                        </td>
                                        <tbody>

                                        <tr class="border-t">
                                            <td class="py-2 px-4 text-left">Available Amount</td>
                                            <td class="py-2 px-4 text-left">{{ number_format(DB::table('accounts')->where('account_number',$this->destination_teller_account)->value('balance'))}} TZS</td>

                                        </tr>
                                        <!-- Add more rows as needed -->
                                        </tbody>
                                    </table>
                                </div>



                            @endif

                            <div class="mt-2"></div>

                            <p for="notes"
                               class="block mb-2 text-sm font-medium text-slate-600 dark:text-gray-400">
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
                            {{--                                @endif--}}

                            <div class="mt-2"></div>


                        </div>

                        <hr class="border-b-0 my-6"/>

                        <div class="flex justify-end w-auto">
                            <div wire:loading wire:target="process">
                                <button class="text-white bg-red-400 hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-400 dark:hover:bg-red-400 dark:focus:ring-red-400"
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
                                        class="text-white bg-red-600 hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-400 dark:hover:bg-red-400 dark:focus:ring-red-400">
                                    <p class="text-white">Send</p>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>






                {{--                     teller to strong room account--}}
                <div class="w-1/2 metric-card  dark:bg-gray-900 border bg-white  border-gray-200 dark:border-gray-800 rounded-lg p-4 ">
                    <div>
                        <p class="flex items-center text-l font-semibold spacing-sm text-slate-600">
                            Strong Room Deposits
                        </p>
                        <div>
                            @if (session()->has('message2'))

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
                                            <p class="text-sm">{{ session('message2') }} </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if (session()->has('message_fail2'))

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
                                            <p class="font-bold">The process has  failed
                                            </p>
                                            <p class="text-sm">{{ session('message_fail2') }} </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <hr class="boder-b-0 my-4"/>

                        <p for="member"
                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">
                            Select Bank  account (MIRROR ACCOUNT) </p>
                        <select wire:model="mirror_account_number" name="account_number" id="account_number"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                            <option selected value="">Select</option>
                            @foreach(App\Models\AccountsModel::where('institution_number',auth()->user()->institution_id)->where('category_code',1000)->get()  as $account2)
                                <option value="{{$account2->account_number}}"> {{ $account2->account_name }} ({{$account2->account_number}})</option>
                            @endforeach
                        </select>
                        @error('teller_account_number')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Account details</p>
                        </div>
                        @enderror

                        <p for="reference_number"
                           class="block mb-2 text-sm font-medium text-slate-600 dark:text-gray-400">
                            Reference Number</p>
                        <x-jet-input id="mirror_account_amount" min="0" type="text" name="amount1"
                                     class="mt-1 block w-full"
                                     wire:model.bounce="reference_number" autofocus/>
                        @error('reference_number')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>reference number is mandatory</p>
                        </div>
                        @enderror
                        <div class="mt-2"></div>
                        <p for="mirror_account_amount"
                           class="block mb-2 text-sm font-medium text-slate-600 dark:text-gray-400">
                            Enter Amount'</p>
                        <x-jet-input id="mirror_account_amount" min="0" type="number" name="amount1"
                                     class="mt-1 block w-full"
                                     wire:model.bounce="mirror_account_amount" autofocus/>
                        @error('mirror_account_amount')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Amount is mandatory and should be more than two
                                characters.</p>
                        </div>
                        @enderror
                        <div class="mt-2"></div>

                        <p for="destination_account_number"
                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">
                            Select destination Strong room account </p>
                        <select wire:model="destination_account_number" name="destination_account_number" id="account_number"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                            <option selected value="">Select</option>
                            @foreach(App\Models\AccountsModel::where('institution_number',auth()->user()->institution_id)->where('sub_category_code',1025)->get()  as $account3)
                                <option value="{{$account3->account_number}}"> {{ $account3->account_name }} ({{$account3->account_number}})</option>
                            @endforeach
                        </select>
                        @error('destination_account_number')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Account details is mandatory</p>
                        </div>
                        @enderror


                        <p for="notes1"
                           class="block mb-2 text-sm font-medium text-slate-600 dark:text-gray-400">
                            Enter Notes'</p>
                        <x-jet-input id="strong_room_note" type="text" name="note_to_strong_room"
                                     class="mt-1 block w-full"
                                     wire:model.bounce="strong_room_note" autofocus/>
                        @error('note_to_strong_room')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Notes is mandatory and should be more than two
                                characters.</p>
                        </div>
                        @enderror

                        <div class="mt-2"></div>


                    </div>

                    <hr class="border-b-0 my-6"/>

                    <div class="flex justify-end w-auto">
                        <div wire:loading wire:target="process1">
                            <button class="text-white bg-red-400 hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-400 dark:hover:bg-red-400 dark:focus:ring-red-400"
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
                        <div wire:loading.remove wire:target="strongRoomToTeller">
                            <button wire:click="strongRoomToTeller"
                                    class="text-white bg-red-600 hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-400 dark:hover:bg-red-400 dark:focus:ring-red-400">
                                <p class="text-white">Send</p>
                            </button>

                        </div>
                    </div>

                </div>

            </div>


        </div>
        <div class="w-full flex space-x-1">

            <div class="w-1/2 metric-card  dark:bg-gray-900 border @if($this->item == 7) bg-red-200 border-red-200 dark:border-red-800  @else bg-white  border-gray-200 dark:border-gray-800 @endif rounded-lg p-4 ">

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
                                Total Transfer
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
                        <td class="mt-2 text-sm font-semibold  text-slate-400 dark:text-white capitalize  text-left">
                            <p>At {{date("Y-m-d H:i:s")}}</p>
                        </td>
                        <td class="pl-2 mt-2 text-sm font-semibold spacing-xl text-slate-400 dark:text-white text-right">
                            <p> {{DB::table('general_ledger')->select()->where('created_at','>=',date("Y-m-d H:i:s"))->sum('debit')}}TZS </p>
                        </td>
                    </tr>

                </table>
            </div>
            <div class="w-1/2  metric-card  dark:bg-gray-900 border @if($this->item == 7) bg-red-200 border-red-200 dark:border-red-800  @else bg-white  border-gray-200 dark:border-gray-800 @endif rounded-lg p-4 ">

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
                                Total Transfer

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
                        <td class="mt-2 text-sm font-semibold  text-slate-400 dark:text-white capitalize  text-left">
                            <p>At {{date("Y-m-d H:i:s")}}</p>
                        </td>
                        <td class="pl-2 mt-2 text-sm font-semibold spacing-xl text-slate-400 dark:text-white text-right">
                            <p>{{DB::table('general_ledger')->where('narration','withdraw')->sum('credit')}} TZS</p>
                        </td>
                    </tr>

                </table>
            </div>
        </div>


</div>


@endif

{{--    @endif--}}


</div>
