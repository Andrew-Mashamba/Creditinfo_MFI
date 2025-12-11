<div class="w-full p-2" >

    <!-- message container -->
    <div>
        @if (session()->has('loan_commit'))

            @if (session('alert-class') == 'alert-success')
                <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
                    <div class="flex">
                        <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                        <div>
                            <p class="font-bold">The process is completed</p>
                            <p class="text-sm">{{ session('loan_commit') }} </p>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>


    <div class="bg-gray-100 rounded rounded-lg shadow-sm ">
        <div class="flex gap-2 pt-2 pl-2 pr-2 pb-2">
            <div class="w-1/3 bg-white rounded px-6 rounded-lg shadow-sm   pt-4 pb-4 " >


                <p for="collateral_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Loan Product</p>
                <div class="w-full bg-gray-50 rounded rounded-lg shadow-sm   p-1 " >
                    <div class="w-full bg-white rounded rounded-lg shadow-sm   p-2 " >
                        <table class="w-full">
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Name</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p>{{App\Models\Loan_sub_products::where('product_id',$this->loan_sub_product)->value('sub_product_name') }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Interest</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p>{{App\Models\Loan_sub_products::where('product_id',$this->loan_sub_product)->value('interest_value') }} %</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Amortization Method</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white capitalize text-right">
                                    <p>{{App\Models\Loan_sub_products::where('product_id',$this->loan_sub_product)->value('amortization_method') }}</p>
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>


                <p for="collateral_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Interest Method
                </p>
                <div class="w-full bg-gray-50 rounded rounded-lg shadow-sm   p-1 " >
                    <div class="w-full bg-white rounded rounded-lg shadow-sm   p-2 " >
                        <table class="w-full">
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p> Reducing  Balance</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-left">
                                    <p>
                                   flat
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  ">
                                    <p>    <input type="radio" class="form-radio text-indigo-600" name="decline_balance" wire:model="interest_method" value="decline_balance" @disabled(Session::get('disableInputs'))>
                                    </p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white ">
                                    <p>
                                        <input type="radio" class="form-radio text-indigo-600" wire:model="interest_method" name="flat" value="flat" @disabled(Session::get('disableInputs'))>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>


                <div class="mt-2"></div>


                <p for="principle_amount" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Principle Amount</p>
                <input type="number" id="principle_amount" name="principle_amount"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                       wire:model.lazy="principle" @disabled(Session::get('disableInputs')) required>
                @error('principle_amount')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>Business Description is mandatory and should be more than two characters.</p>
                </div>
                @enderror
                <div class="mt-2"></div>


               <div class="flex w-full space-x-4">
                   <div class="w-1/2">
                       <p for="tenure" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Interest</p>
                       <input type="number" id="tenure" name="tenure" value="12" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                              wire:model.lazy="interest" @disabled(Session::get('disableInputs')) required>
                       @error('interest')
                       <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                           <p>interest is mandatory </p>
                       </div>
                       @enderror
                   </div>

                   <div class="w-1/2">
                       <p for="tenure" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Tenure</p>
                       <input type="number" id="tenure" name="tenure" value="12" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                              wire:model.lazy="tenure" @disabled(Session::get('disableInputs')) required>
                       @error('tenure')
                       <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                           <p>Tenure is mandatory and should be more than two characters.</p>
                       </div>
                       @enderror
                   </div>
               </div>

                <div class="mt-2"></div>



              @if($this->futureInterest)

                    @if (session()->has('message_fail'))

                            <div class="bg-red-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
                                <div class="flex">
                                    <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                                    <div>
                                        <p class="font-bold">The process is uncompleted</p>
                                        <p class="text-sm">{{ session('message_fail') }} </p>
                                    </div>
                                </div>
                            </div>
                    @endif

                <div class="w-full bg-gray-50 rounded rounded-lg shadow-sm   p-1 " >
                    Forgive Future Interest
                    <div class="w-full bg-white rounded rounded-lg shadow-sm   p-2 " >
                        <table class="w-full">
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Yes</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-left">
                                    <p>
                                        No
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  ">
                                    <p>    <input type="radio" class="form-radio text-indigo-600" name="name" wire:model="future_interests" value="YES" >
                                    </p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white ">
                                    <p>
                                        <input type="radio" class="form-radio text-indigo-600" wire:model="future_interests" name="name" value="NO" >
                                    </p>
                                </td>
                            </tr>
                        </table>
                        @if($this->future_interests=="NO")
                        <p for="principle_amount" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400"></p>
                        <input type="number" id="principle_amount" name="principle_amount"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                               wire:model.lazy="futureInsteresAmount" required>
                        @error('futureInsteresAmount')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Business Description is mandatory and should be more than two characters.</p>
                        </div>
                        @enderror
                        @endif
                    </div>
                </div>
                @endif


                @if($this->topUpBoolena)

                    @if (session()->has('message_fail'))

                            <div class="bg-red-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
                                <div class="flex">
                                    <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                                    <div>
                                        <p class="font-bold">The process is uncompleted</p>
                                        <p class="text-sm">{{ session('message_fail') }} </p>
                                    </div>
                                </div>
                            </div>
                    @endif

                <div class="w-full bg-gray-50 rounded rounded-lg shadow-sm   p-1 " >
                    Forgive Future Interest
                    <div class="w-full bg-white rounded rounded-lg shadow-sm   p-2 " >
                        <table class="w-full">
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Yes</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-left">
                                    <p>
                                        No
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  ">
                                    <p>    <input type="radio" class="form-radio text-indigo-600" name="name" wire:model="future_interests" value="YES" >
                                    </p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white ">
                                    <p>
                                        <input type="radio" class="form-radio text-indigo-600" wire:model="future_interests" name="name" value="NO" >
                                    </p>
                                </td>
                            </tr>
                        </table>
                        <p for="principle_amount" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">New Principle</p>
                        <input type="number" id="principle_amount" name="principle_amount"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                               wire:model.lazy="new_principle" required>


                        @if($this->future_interests=="NO")
                        <p for="principle_amount" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Total Interest</p>
                        <input type="number" id="principle_amount" name="principle_amount"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                               wire:model.lazy="futureInsteresAmount" required>
                        @error('futureInsteresAmount')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Business Description is mandatory and should be more than two characters.</p>
                        </div>
                        @enderror
                        @endif
                    </div>
                </div>
                @endif





                <div class="mt-2"></div>


                <hr class="boder-b-0 my-6"/>

                <p for="collateral_type" class="block mt-6 text-sm font-bold @if($this->recommended) text-red-400 dark:text-red-400  @else text-red-400 dark:text-red-400 @endif ">@if($this->recommended) Recommendations @else Conclusion @endif </p>
                <div class="w-full @if($this->recommended) bg-red-200  @else bg-blue-200 @endif   rounded rounded-lg shadow-sm   p-1 " >
                    <div class="w-full bg-white rounded rounded-lg shadow-sm   p-2 " >
                        <table class="w-full">

                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Loan to be given</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p> {{number_format($this->principle,2)}} TZS</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Interest</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p> {{$this->interest_value }} %</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Tenure ( Months )</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p>@if($this->recommended) {{$this->recommended_tenure}} @else {{$this->tenure}} @endif </p>
                                </td>
                            </tr>

                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Installment</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p>As per schedule</p>
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>


                <div class="mt-2"></div>





                <div class="flex justify-end w-auto" >
                    <div wire:loading wire:target="actionBtns" >
                        <button class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400" disabled>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-white-800" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                                </svg>
                                <p>Please wait...</p>
                            </div>
                        </button>
                    </div>

                </div>

                @if(Session::get('loanStatus') == 'ONPROGRESS')

                <div class="flex justify-end w-auto" >
                    <div wire:loading.remove wire:target="actionBtns" >

                        @if($this->recommended)
                            <button wire:click="actionBtns(1)" class="text-white bg-red-400 hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-400 dark:hover:bg-red-400 dark:focus:ring-red-400">
                                <p class="text-white">Override</p>
                            </button>
                        @else
                            <button wire:click="actionBtns(2)" class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400">
                                <p class="text-white">Use Recommendations</p>
                            </button>
                        @endif



                        <button wire:click="actionBtns(3)" class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400">
                            <p class="text-white">Commit</p>
                        </button>

                    </div>
                </div>

                @endif

                @if(Session::get('loanStatus') == 'ACTIVE')
                    <div class="flex justify-end w-auto" >
                        <div wire:loading.remove wire:target="actionBtns" >

                                <button wire:click="actionBtns(33)" class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400">
                                    <p class="text-white">Top Up</p>
                                </button>

                            <button wire:click="actionBtns(44)" class="text-white bg-red-400 hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-400 dark:hover:bg-red-400 dark:focus:ring-red-400">
                                <p class="text-white">Restructure</p>
                            </button>

                            <button wire:click="actionBtns(55)" class="text-white bg-green-400 hover:bg-green-400 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-green-400 dark:hover:bg-green-400 dark:focus:ring-green-400">
                                <p class="text-white">Close Loan</p>
                            </button>

                        </div>
                    </div>
                @endif

                @if(Session::get('loanStatus') == 'AWAITING APPROVAL')
                    <div class="flex justify-end w-auto" >
                        <div wire:loading.remove wire:target="actionBtns" >

                            @if (in_array( "Approve loans" , session()->get('permission_items')))

                            <button wire:click="actionBtns(4)" class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400">
                                <p class="text-white">Approve</p>
                            </button>

                            <button wire:click="actionBtns(5)" class="text-white bg-red-400 hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-400 dark:hover:bg-red-400 dark:focus:ring-red-400">
                                <p class="text-white">Reject</p>
                            </button>
                            @endif


                        </div>
                    </div>
                @endif


                @if(Session::get('loanStatus') == 'APPROVED')

                    <div class="mt-2"></div>

                    <p for="bank1" class="block mb-2 text-sm font-medium text-slate-600 dark:text-gray-400">Select Bank</p>
                    <select wire:model.bounce="bank1" name="bank1" id="bank1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected value="">Select</option>
                        @foreach(App\Models\AccountsModel::where('sub_product_number', '91')->get() as $bank)
                            <option value="{{$bank->account_number}}">{{$bank->account_name}} - {{$bank->account_number}}</option>
                        @endforeach
                    </select>
                    @error('member')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Branch is mandatory.</p>
                    </div>
                    @enderror
                    <div class="mt-3"></div>



                    <div class="flex justify-end w-auto" >
                        <div wire:loading.remove wire:target="actionBtns" >

                            <button wire:click="actionBtns(6)" class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400">
                                <p class="text-white">Close Loan</p>
                            </button>
                            <button wire:click="actionBtns( )" class="text-white bg-red-700   hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-400 dark:hover:bg-red-400 dark:focus:ring-red-400">
                                <p class="text-white">Top Up</p>
                            </button>
                            <button wire:click="actionBtns( )" class="text-white bg-red-400 hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-400 dark:hover:bg-red-400 dark:focus:ring-red-400">
                                <p class="text-white">Restructure</p>
                            </button>


                        </div>
                    </div>
                @endif

            </div>

                {{Session::get('viewLoansWithCategory')}}
            <div class="w-2/3 bg-white rounded px-6 rounded-lg shadow-sm  pt-4 pb-4  " >


                <p for="stability" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">STABILITY OF THE BUSINESS</p>
                <div id="stability" class="w-full bg-gray-50 rounded rounded-lg shadow-sm   p-1 " >
                    <div class="w-full bg-white rounded rounded-lg shadow-sm   p-2 " >
                        <table class="w-full">

                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Age of the business</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p>{{$this->business_age }} Years</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Registration status</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p>@if($this->business_licence_number) Registered @else Not Registered @endif </p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Working capital</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p>{{number_format($this->business_inventory,2) }}</p>
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>


                <p for="stability" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">SECURITY COVERAGE</p>
                <div id="stability" class="w-full bg-gray-50 rounded rounded-lg shadow-sm   p-1 " >
                    <div class="w-full bg-white rounded rounded-lg shadow-sm   p-2 " >
                        <table class="w-full">

                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Security</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p>{{$this->collateral_type}}</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Security value</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p>{{ number_format($this->collateral_value,2)}} </p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p>Coverage</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p>  </p>
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>

                <p for="stability2" class="block mt-2 text-sm font-medium text-gray-900 dark:text-gray-400">ABILITY TO PAY</p>
                <div id="stability2" class="w-full bg-gray-50 rounded rounded-lg shadow-sm   p-1 " >
                    <div class="w-full bg-white rounded rounded-lg shadow-sm   p-2 " >
                        <table class="w-full">

                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white text-left">
                                    <p>Sales per month</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p>{{number_format($this->monthly_sales,2)}}</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white text-left">
                                    <p>Cost of sales per month</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p>- {{number_format($this->cost_of_goods_sold,2)}} </p>
                                </td>
                            </tr>
                            <tr class="mt-4">
                                <td class="pt-4 text-xs text-slate-400 dark:text-white text-left">
                                    <p>GROSS PROFIT</p>
                                </td>
                                <td class="pt-4 text-xs text-slate-400 dark:text-white text-right">
                                    <p>{{number_format($this->gross_profit,2)}} </p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white text-left">
                                    <p>Taxes per month</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p>- {{number_format($this->monthly_taxes,2)}} </p>
                                </td>
                            </tr>

                            <tr class="mt-4">
                                <td class="pt-4 text-xs text-slate-400 dark:text-white text-left">
                                    <p>NET PROFIT</p>
                                </td>
                                <td class="pt-4 text-xs text-slate-400 dark:text-white text-right">
                                    <p>{{number_format($this->net_profit,2) }} </p>
                                </td>
                            </tr>

                            <tr>
                                <td class="text-xs text-slate-400 dark:text-white text-left">
                                    <p>Other Expenses (Household expenses)</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">
                                    <p>- {{number_format($this->other_expenses,2)}} </p>
                                </td>
                            </tr>

                            <tr class="">
                                <td class="pt-4 text-xs text-slate-400 dark:text-white text-left">
                                    <p>AVAILABLE FUNDS FOR REPAYMENT (50% of NET)</p>
                                </td>
                                <td class="pt-4 text-sm text-red-400 font-bold dark:text-white text-right">
                                    <p>{{number_format($this->available_funds,2) }} </p>
                                </td>
                            </tr>


                        </table>
                    </div>
                </div>

                <p for="stability3" class="block mb-2 mt-2 text-sm font-medium text-gray-900 dark:text-gray-400">PROPOSITION</p>
                <div id="stability3" class="w-full bg-gray-50 rounded rounded-lg shadow-sm   p-1 " >
                    <div class="w-full bg-white rounded rounded-lg shadow-sm   p-2 " >

                        <table class="w-full">
                            <thead>
                            <tr>
                                <th>INSTALLMENT</th>
                                <th>INTEREST</th>
                                <th>PRINCIPLE</th>
                                <th>BALANCE</th>

                            </tr>
                            </thead>
                            <tbody>



                            @foreach($this->table as $tr)
                                <tr>
                                    <td class="text-xs text-slate-400 dark:text-white text-right">
                                        <p>{{number_format($tr['Payment'],2)}}</p>
                                    </td>

                                    <td class="text-xs text-slate-400 dark:text-white text-right">
                                        <p>{{number_format($tr['Interest'],2)}}</p>
                                    </td>

                                    <td class="text-xs text-slate-400 dark:text-white text-right">
                                        <p>{{number_format($tr['Principle'],2)}}</p>
                                    </td>

                                    <td class="text-xs text-slate-400 dark:text-white text-right">
                                        <p>{{number_format($tr['balance'],2)}}</p>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>

                            <tfoot>
                            @foreach($this->tablefooter as $tr)
                                <tr>
                                    <td class="text-sm font-bold text-black dark:text-white text-right">
                                        <p class="text-sm font-bold text-black">{{number_format($tr['Payment'],2)}}</p>
                                    </td>
                                    <td class="text-xs text-slate-400 dark:text-white text-right">
                                        <p class="text-sm font-bold text-black">{{number_format($tr['Interest'],2)}}</p>
                                    </td>
                                    <td class="text-xs text-slate-400 dark:text-white text-right">
                                        <p class="text-sm font-bold text-black">{{number_format($tr['Principle'],2)}}</p>
                                    </td>
                                    <td class="text-xs text-slate-400 dark:text-white text-right">
                                        <p class="text-sm font-bold text-black">{{number_format($tr['balance'],2)}}</p>
                                    </td>
                                </tr>
                            @endforeach
                            </tfoot>

                        </table>


                    </div>
                </div>


            </div>



        </div>




    </div>


</div>
