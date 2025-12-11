



<div>{{-- In work, do what you enjoy. --}}
    <div>
        <nav class="very-light-shade   rounded-lg pl-2 pr-2 shadow-2xl">
            <div class="relative flex h-16 items-center justify-between">
                <div class="flex flex-1 items-center justify-between">
                    <div class="flex flex-shrink-0 items-start">
                        <div class="flex items-center justify-between">
                            <div wire:loading wire:target="menuItemClicked" >
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-gray-400" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                                    </svg>
                                    <p>Please wait...</p>
                                </div>

                            </div>
                            <div wire:loading.remove wire:target="menuItemClicked">

                                <div class="flex items-center justify-between">
                                    <div>
                                        <button type="button" class="flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                            <?php
                                            $urlValue=  \App\Models\ClientsModel::where('id',Session::get('viewClientId_details'))->value('profile_photo_path');
                                            ?>
                                            @if($urlValue)
                                                <img src="{{$urlValue}}">
                                            @else
                                                <img class="h-8 w-8 rounded-full" src="{{asset('/images/download-1.png')}}" alt="">
                                            @endif

                                        </button>

                                    </div>
                                    <p class="font-semibold ml-3 text-slate-600 text-white"> {{App\Models\ClientsModel::where('id',Session::get('viewClientId_details'))->value('first_name').' '.App\Models\ClientsModel::where('id',Session::get('viewClientId_details'))->value('middle_name').' '.App\Models\ClientsModel::where('id',Session::get('viewClientId_details'))->value('last_name')}}</p>

                                </div>
                                <div class="text-red-400 ml-4 mr-5 ">
                                    <div class="ml-12 text-blue-900 font-bold">
                                        {{App\Models\ClientsModel::where('id',Session::get('viewClientId_details'))->value('member_status')}}
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                    <div class="flex">
                        <div class="flex space-x-4">
                            <!-- Current: "bg-gray-900 text-white", Default:"text-gray-300 hover:bg-gray-700 hover:text-white" -->
                        </div>

                        <button type="button" class="rounded-full bg-white p-1 text-gray-400 hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">

                            <svg wire:click="close" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 " fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

            </div>

        </nav>

        <div class="relative flex py-5 items-center">
            <div class="flex-grow border-t border-gray-400"></div>
            <span class="flex-shrink mx-4 text-gray-400">Client Data</span>
            <div class="flex-grow border-t border-gray-400"></div>
        </div>
        <div class="w-full h-full grid justify-items-center">

            <?php        $employees=\App\Models\ClientsModel::where('id',session()->get('viewClientId_details'))->get();                      ?>
            @foreach($employees as $employee)
                <div class="w-fit m-auto grid justify-items-center">
                    <div class="w-fit text-center m-4" >
                        @if($employee->profile_photo_path)
                            <div style="display: flex; justify-content: center;">
                                <img class="mb-3 w-32 h-32 rounded-full shadow-lg"
                                     src="{{$employee->profile_photo_path}}"
                                     alt="{{$employee->first_name}}"/>
                            </div>
                        @else
                            <div style="display: flex; justify-content: center;">
                                <img class="mb-3 w-32 h-32 rounded-full shadow-lg"
                                     src="{{asset('/images/download-1.png')}}"
                                     alt="{{$employee->first_name}}"/>
                            </div>
                        @endif
                        <p class="text-2xl mt-4 border-b-2 border-b-blue-400 ">{{$employee->first_name}} {{$employee->middle_name}} {{$employee->last_name}}</p>
                        <p class="m-4">{{$employee->address}}</p>
                    </div>
                    <div class="w-fit bg-gray-200 rounded-lg pl-2 pr-2 pt-2 pb-2 ">
                        <!-- message container -->


                        @if(session()->has('message'))

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

                        @if(session()->has('message_fail'))
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
                                        <p class="font-bold">The process is
                                            completed</p>
                                        <p class="text-sm">{{ session('message_fail') }} </p>
                                    </div>
                                </div>
                            </div>
                        @endif



                        <div>


                            <div class="flex">
                                <div class="container mx-auto ">
                                    <div class="flex flex-col w-full" >
                                        <div class="grid gap-2 grid-cols-1 sm:grid-cols-2 mb-2">
                                            <div class="metric-card  dark:bg-gray-900 border bg-white  border-gray-200 dark:border-gray-800 rounded-lg p-4 max-w-72 w-full" >
                                                <table>
                                                    <tbody class="bg-white">
                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-2 text-sm text-gray-500 font-semibold">
                                                            <p> Gender</p>
                                                        </td>
                                                        <td class="px-6 py-2">
                                                            <p class="text-sm text-gray-900">
                                                                {{$employee->gender}}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-2 text-sm text-gray-500 font-semibold">
                                                            <p>Date of birth</p>
                                                        </td>
                                                        <td class="px-6 py-2">
                                                            <p class="text-sm text-gray-900">
                                                                {{$employee->date_of_birth}}
                                                            </p>
                                                        </td>

                                                    </tr>
                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-2 text-sm text-gray-500 font-semibold">
                                                            <p> Marital status</p>
                                                        </td>
                                                        <td class="px-6 py-2">
                                                            <p class="text-sm text-gray-900">
                                                                {{$employee->marital_status}}
                                                            </p>
                                                        </td>

                                                    </tr>
                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-2 text-sm text-gray-500 font-semibold">
                                                            <p>Next of the kin phone number</p>
                                                        </td>
                                                        <td class="px-6 py-2">
                                                            <p class="text-sm text-gray-900">
                                                                {{$employee->next_of_kin_phone}}
                                                            </p>
                                                        </td>

                                                    </tr>


                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-2 text-sm text-gray-500 font-semibold">
                                                            <p>Email</p>
                                                        </td>
                                                        <td class="px-6 py-2">
                                                            <p class="text-sm text-gray-900">
                                                                {{$employee->email}}
                                                            </p>
                                                        </td>

                                                    </tr>

                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-2 text-sm text-gray-500 font-semibold">
                                                            <p> Clientship Number</p>
                                                        </td>
                                                        <td class="px-6 py-2">
                                                            <p class="text-sm text-gray-900">
                                                                {{$employee->client_number}}
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
                                                        <td class="px-6 py-2 text-sm text-gray-500 font-semibold">
                                                            <p> Incorporation Number</p>
                                                        </td>
                                                        <td class="px-6 py-2">
                                                            <p class="text-sm text-gray-900">
                                                                {{$employee->incorporation_number}}
                                                            </p>
                                                        </td>
                                                    </tr>

                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-2 text-sm text-gray-500 font-semibold">
                                                            <p> Address</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="text-sm text-gray-900 whitespace-normal">
                                                                {{$employee->address}}
                                                            </p>
                                                        </td>

                                                    </tr>

                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-2 text-sm text-gray-500 font-semibold">
                                                            <p> Street</p>
                                                        </td>
                                                        <td class="px-6 py-2">
                                                            <p class="text-sm text-gray-900 whitespace-normal">
                                                                {{$employee->street}}
                                                            </p>
                                                        </td>

                                                    </tr>

                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-2 text-sm text-gray-500 font-semibold">
                                                            <p> Notes</p>
                                                        </td>
                                                        <td class="px-6 py-2">
                                                            <p class="text-sm text-gray-900 whitespace-normal">
                                                                {{$employee->notes}}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div id="stability" class="w-full  font-bold bg-gray-50 rounded rounded-lg shadow-sm   p-1 " >
                                            <div class="flex space-x-4 ">
                                                <div class="w-full bg-white  font-bold rounded rounded-lg shadow-sm   p-2 " >
                                                    <table class="w-full">
                                                        <tr>
                                                            <th class="text-left">REGISTRATION FEE PAYMENT</th>
                                                            <th class="text-right"></th>
                                                        </tr>
                                                        <tr>
                                                            <th class="text-xs text-slate-400 dark:text-white capitalize text-left ">ACCOUNT NUMBER</th>
                                                            <th class="text-xs text-slate-400 dark:text-white capitalize  text-left">ACCOUNT NAME</th>
                                                            <th class="text-right"></th>
                                                            <th class="text-xs text-slate-400 dark:text-white capitalize  text-left">AMOUNT</th>
                                                        </tr>



                                                        @foreach(DB::table('pending_registrations')->where('phone_number',$employee->phone_number)->get() as $account)
                                                            <tr class="text-right">
                                                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left "> {{$account->phone_number}}  </td>
                                                                <td class="text-xs text-slate-400 dark:text-white capitalize   text-left"> {{$employee->first_name.'  '.$employee->middle_name.'  '.$employee->last_name}}  </td>
                                                                <td class="text-xs text-slate-400 dark:text-white capitalize   text-left"> </td>
                                                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left"> {{$account->amount}}  </td>
                                                            </tr>
                                                            @endforeach
                                                            </td>
                                                            </tr>
                                                    </table>
                                                    <table class="w-full mt-4 ">
                                                        <tr>
                                                            <th class="text-left">SHARE PAYMENT</th>
                                                            <th class="text-right"></th>
                                                        </tr>
                                                        <tr>
                                                            <th class="text-xs text-slate-400 dark:text-white capitalize text-left ">ACCOUNT NUMBER</th>
                                                            <th class="text-xs text-slate-400 dark:text-white capitalize  text-left">ACCOUNT NAME</th>
                                                            <th class="text-right"></th>
                                                            <th class="text-xs text-slate-400 dark:text-white capitalize  text-left">AMOUNT</th>
                                                        </tr>



                                                        @foreach(DB::table('pending_registrations')->where('phone_number',$employee->phone_number)->get() as $account)
                                                            <tr class="text-right">
                                                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left "> {{$account->phone_number}}  </td>
                                                                <td class="text-xs text-slate-400 dark:text-white capitalize   text-left"> {{$employee->first_name.'  '.$employee->middle_name.'  '.$employee->last_name}}  </td>
                                                                <td class="text-xs text-slate-400 dark:text-white capitalize   text-left"> </td>
                                                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left"> {{$account->amount}}  </td>
                                                            </tr>
                                                            @endforeach
                                                            </td>
                                                            </tr>
                                                    </table>
                                                </div>


                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                 <div class="bg-white items-end justify-end p-3 m-3 rounded-2xl">

                     @if (session()->has('message_endClientship'))
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
                                     <p class="text-sm">{{ session('message_endClientship') }} </p>
                                 </div>
                             </div>
                         </div>
                     @endif

                         @if (session()->has('success_message'))
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
                                     <p class="text-sm">{{ session('success_message') }} </p>
                                 </div>
                             </div>
                         </div>
                     @endif

                 @if(DB::table('clients')->where('id',session()->get('viewClientId_details'))->value('member_status')=="EXIT MEMBER")
                         @if($this->declineEndClientship)

                             <div class=" justify-end text-right ">
                                 <div class="">
                                     <textarea  wire:model="end_membership_description" class="form-input mt-1 block w-full" row="4"></textarea>
                                 </div>
                             </div>
                             <div class="flex justify-end" row="6">
                                 <div class="">
                                 <button wire:click="$toggle('declineEndClientship')" class=" p-2 m-4 mt-n2 bg-gray-500 hover:bg-gray-500 text-white py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                     Cancel
                                 </button>
                                 </div>
                                 <div class="">
                                     <button wire:click="declineEndClientship({{session()->get('viewClientId_details')}})" class=" p-2 m-4 mt-n2 bg-blue-900 hover:bg-blue-800 text-white py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                         Confirm
                                     </button>
                                 </div>
                             </div>

                         @else

                   <div class="space-x-4 p-4 flex justify-end items-end">
                       <div class="fw-bold justify-start">
                         Exit  document
                       <livewire:accounting.exit-member-action id="{{session()->get('viewClientId_details')}}" />
                     </div>
                        <div class="justify-end  text-right  ">

                            <button  wire:click="declineEndClientshipModal" class=" mt-n2 bg-red-900 hover:bg-red-800 text-white py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Decline
                            </button>

                            <button wire:click="exitClient({{session()->get('viewClientId_details')}})" class=" mt-n2 bg-blue-900 hover:bg-blue-800 text-white py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Confirm
                            </button>
                            @endif
                        </div>
                   </div>

                     @endif

                     <div class="flex space-x-4 justify-end">
                         @if(DB::table('clients')->where('id',session()->get('viewClientId_details'))->value('member_status')=="PENDING")
                         <button  wire:click="reject"  class="bg-red-400 hover:bg-red-600 text-white py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                             Reject
                         </button>
                         </svg>
                         <button wire:click="accept" class="bg-blue-500 hover:bg-green-600 text-white py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                             Accept
                         </button>
                         @endif

                     </div>


                 </div>
                    </div>

                </div>


                @endforeach

        </div>
    </div>
</div>
