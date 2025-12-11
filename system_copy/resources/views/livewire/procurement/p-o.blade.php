<div>
    {{-- The best athlete wants his opponent at his best. --}}
    <div class="bg-white leading-tight intro-y mx-4">


    <div class="border-b-1 border-gray-200 dark:border-gray-700">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-gray-400 w-full">
            <div class=" flex space-x-4 w-1/3">
                <li class="mr-2 gap-2" wire:click="setView(1)">
                    <a href="#" class="inline-flex p-2   text-base  @if($selected == 1) text-blue-400   border-b-2 border-blue-600
                    active dark:text-blue-400 dark:border-blue-500 @else border-transparent hover:text-gray-600
                    hover:border-gray-300 dark:hover:text-gray-300 @endif rounded-t-lg
                      group" @if($selected == 1) aria-current="page" @endif>
                        <svg aria-hidden="true" class="w-6 h-6 mr-2 mx-2 @if($selected == 1) text-blue-600 dark:text-blue-500
                        @else text-gray-400 group-hover:text-gray-500
                        dark:text-gray-500 dark:group-hover:text-gray-300 @endif " fill="none" stroke="#60A5FA"
                             viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">

                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />

{{--                            <path --}}
{{--                                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5--}}
{{--                             5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0--}}
{{--                             0010 11z" clip-rule="evenodd"></path>--}}

                        </svg>
                            PENDING ORDERS ( <div class="text-red-500 ">{{$this->pendingPo}} </div>)



                    </a>
                </li>
            </div>
            <div class=" flex space-x-4 w-1/3">
                <li class="mr-2" wire:click="setView(2)">
                    <a href="#" class="inline-flex p-2 text-base  @if($selected == 2)   text-blue-400  border-b-2 border-blue-600
                    active dark:text-blue-400 dark:border-blue-500 @else border-transparent hover:text-gray-600
                    hover:border-gray-300 dark:hover:text-gray-300 @endif rounded-t-lg
                      group" @if($selected == 2) aria-current="page" @endif >
                        <svg aria-hidden="true" class="w-6 h-6 mr-2 @if($selected == 2) text-blue-600 dark:text-blue-500
                        @else text-gray-400 group-hover:text-gray-500
                        dark:text-gray-500 dark:group-hover:text-gray-300 @endif "
                             fill="none" stroke="#60A5FA" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">

                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />



                        </svg>APPROVED ORDERS ({{$this->approvedPO}})
                    </a>
                </li>

            </div>
            <div class=" flex space-x-4 w-1/3">
                <li class="mr-2" wire:click="setView(3)">
                    <a href="#" class="inline-flex p-2 text-base @if($selected == 3) text-blue-400 border-b-2 border-blue-600
                    active dark:text-blue-500 dark:border-blue-500 @else border-transparent hover:text-gray-600
                    hover:border-gray-300 dark:hover:text-gray-300 @endif rounded-t-lg
                      group" @if($selected == 3) aria-current="page" @endif >
                        <svg aria-hidden="true" class="w-6 h-6 mr-2 @if($selected == 3) text-blue-600 dark:text-blue-500
                        @else text-gray-400 group-hover:text-gray-500
                        dark:text-gray-500 dark:group-hover:text-gray-300 @endif "
                             fill="none" stroke="#60A5FA" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">

                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />


                        </svg>COMPLETED ORDERS ({{$this->completed}})
                    </a>
                </li>
            </div>
        </ul>
    </div>

        <div class="mt-2">
            @switch($this->selected)
                @case('1')
            @if($this->viewPoBoolean)
                    <div class="flex justify-between bg-blue-100 rounded-lg text-white p-2 ">
                       <div class="float-right flex">
                        <button type="button" class="rounded-full  flex justify-end item-end bg-white p-1 text-gray-400 hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                            <svg wire:click="$toggle('viewPoBoolean')" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 " fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                       </div>
                    </div>

{{--                    <div class="p-2">--}}
{{--                        <!-- Full-width card content goes here -->--}}
{{--                        <div class="bg-white p-6 rounded s">--}}
{{--                            <!-- Content inside the card -->--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <livewire:procurement.p-o-view />
                @else
            <livewire:procurement.p-o-table/>
                @endif

                @break
                @case('2')
            <livewire:procurement.p-o-table/>
                @break
                @case('3')
            <livewire:procurement.p-o-table/>
                @break
            @endswitch
        </div>


    </div>







    {{--approve po--}}
    <div class="w-full container-fluid">
        @if($this->approveModalBoolean)
            <div class="fixed z-10 inset-0 overflow-y-auto"  >
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                    <div class="fixed inset-0 transition-opacity">
                        <div class="absolute inset-0 bg-gray-500 opacity-0"></div>
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <!-- Your form elements go here -->
                        <div class="p-4">
                            <div>
                                @if (session()->has('message'))
                                    <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
                                        <div class="flex">
                                            <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                                            <div>
                                                <p class="font-bold">The process is completed</p>
                                                <p class="text-sm">{{ session('message') }} </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="header-elements text-center justify-center font-bold  stroke-current">
                                <h3 class="fw-bold">
                                   SELECT VENDORS
                                </h3>
                            </div>
                            {{--                                                        organization name--}}

                            <div class="mt-2"></div>
                            <div class="mt-2"></div>


                            <div class="grid grid-cols-2 gap-4">
                                @foreach(DB::table('vendors')->where('status',"ACTIVE")->get() as $vendor)
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="vendorId"  value="{{$vendor->id}}"
                                           class="
                                mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block  p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    >
                                    <label for="checkbox1" class="ml-2 text-gray-700"> {{$vendor->organization_name}} </label>
                                </div>
                                <!-- Add more checkbox items here in the same format -->
                                @endforeach



                            </div>


                            @error('vendorId')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>vendor is mandatory.</p>
                            </div>
                        @enderror
                            <div class="mt-2"></div>
                            <div class="mt-2"></div>
                            <label for="checkbox1" class="ml-2 text-gray-700"> End Of Submissions </label>


                            <input type="date" wire:model="endDate"
                                   class="
                                w-full mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block  p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            >



                            <div class="mt-2"></div>
                            <div class="mt-2"></div>

                        <!-- Add more form fields as needed -->
                        <div class="flex items-center bg-gray-200 justify-end py-2 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                            <button type="button" wire:click="$toggle('approveModalBoolean')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium   border border-transparent rounded-md  focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2  bg-white">
                                Cancel
                            </button>

                            <button type="submit" wire:click="approve" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-400 border border-transparent rounded-md  focus-visible:ring-2 focus-visible:ring-offset-2 ">
                                Proceed
                            </button>


                        </div>
                    </div>
                </div>
            </div>
            {{--                                            <livewire:procurement.create-vendor/>--}}
        @endif
    </div>
    {{--                            END OF CREATE VENDOR MODAL--}}




</div>
