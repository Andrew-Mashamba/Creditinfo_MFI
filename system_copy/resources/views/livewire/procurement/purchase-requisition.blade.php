<div class="p-4">
    {{-- In work, do what you enjoy. --}}


    <div class="relative p-4 mb-2 overflow-hidden rounded-lg bg-white" >

        <!-- Background illustration -->
        <div class="absolute right-0 top-0 -mt-4 mr-16 pointer-events-none hidden xl:block" aria-hidden="true">
            <svg width="319" height="198" xmlns:xlink="http://www.w3.org/1999/xlink">
                <defs>
                    <path id="welcome-a" d="M64 0l64 128-64-20-64 20z" />
                    <path id="welcome-e" d="M40 0l40 80-40-12.5L0 80z" />
                    <path id="welcome-g" d="M40 0l40 80-40-12.5L0 80z" />
                    <linearGradient x1="50%" y1="0%" x2="50%" y2="100%" id="welcome-b">
                        <stop stop-color="#2D3D88" offset="0%" /> <!-- Dark Blue -->
                        <stop stop-color="#2D3D88" offset="100%" /> <!-- Light Blue -->
                    </linearGradient>
                    <linearGradient x1="50%" y1="24.537%" x2="50%" y2="100%" id="welcome-c">
                        <stop stop-color="#2D3D88" offset="0%" /> <!-- Light Blue -->
                        <stop stop-color="#2D3D88" stop-opacity="0" offset="100%" /> <!-- Dark Blue -->
                    </linearGradient>
                </defs>
                <g fill="none" fill-rule="evenodd">
                    <g transform="rotate(64 36.592 105.604)">
                        <mask id="welcome-d" fill="#fff">
                            <use xlink:href="#welcome-a" />
                        </mask>
                        <use fill="url(#welcome-b)" xlink:href="#welcome-a" />
                        <path fill="url(#welcome-c)" mask="url(#welcome-d)" d="M64-24h80v152H64z" />
                    </g>
                    <g transform="rotate(-51 91.324 -105.372)">
                        <mask id="welcome-f" fill="#fff">
                            <use xlink:href="#welcome-e" />
                        </mask>
                        <use fill="url(#welcome-b)" xlink:href="#welcome-e" />
                        <path fill="url(#welcome-c)" mask="url(#welcome-f)" d="M40.333-15.147h50v95h-50z" />
                    </g>
                    <g transform="rotate(44 61.546 392.623)">
                        <mask id="welcome-h" fill="#fff">
                            <use xlink:href="#welcome-g" />
                        </mask>
                        <use fill="url(#welcome-b)" xlink:href="#welcome-g" />
                        <path fill="url(#welcome-c)" mask="url(#welcome-h)" d="M40.333-15.147h50v95h-50z" />
                    </g>
                </g>
            </svg>
        </div>

        <!-- Content -->
        <div class="relative w-full">
            <div class="min-w-full text-center text-sm font-light">
                <div class="text-xl text-slate-400 font-bold mb-1 ">
                   PERSONAL SELF SERVICES

                </div>

            </div>
            <div>

                <ul class="max-w-md space-y-1 text-gray-500 list-inside dark:text-gray-400">
                    <li class="flex items-center">
                        <svg class="w-3.5 h-3.5 mr-2 text-blue-900 dark:text-blue-900 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>
                        Total Purchase Order : {{$this->totalPurchases}}
                    </li>
                    <li class="flex items-center">
                        <svg class="w-3.5 h-3.5 mr-2 text-blue-900 dark:text-blue-900 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>
                        Completed Orders  : {{$this->totalCompletedPurchases}}
                    </li>
                    <li class="flex items-center">
                        <svg class="w-3.5 h-3.5 mr-2 text-blue-900 dark:text-blue-900 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>
                        Rejected Orders :

                        <span>
                                <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-blue-400 border border-blue-400"> {{$this->rejectedPurchases}}</span>
                            </span>
                    </li>
                </ul>
            </div>

        </div>

    </div>


    <div class="bg-white  overflow-hidden  rounded-lg">


        <div class="grid p-2 grid-cols-6 gap-2 p-2">
            @php
                $menuItems = [
                    ['id' => 1, 'label' => 'Purchase Requisition'],
                    ['id' => 2, 'label' => 'Allocated Inventory'],
//                    ['id' => 3, 'label' => 'Leaves'],


                ];
            @endphp


            @foreach ($menuItems as $menuItem)
                <button
                        wire:click="menuItemClicked({{ $menuItem['id'] }})"
                        class="flex hover:text-white text-center items-center w-full
            @if ($this->tab_id == $menuItem['id']) bg-blue-900 @else bg-gray-100 @endif
                        @if ($this->tab_id == $menuItem['id']) text-white font-bold @else text-gray-400 font-semibold @endif
                                py-2 px-4 rounded-lg"

                        onmouseover="this.style.backgroundColor='#2D3D88'; this.style.color='white';"
                        onmouseout="this.style.backgroundColor=''; this.style.color='';"
                >

                    <div wire:loading wire:target="menuItemClicked({{ $menuItem['id'] }})">
                        <svg aria-hidden="true" class="w-8 h-8 mr-2 text-gray-200 animate-spin dark:text-gray-900 fill-red-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                        </svg>
                    </div>
                    <div wire:loading.remove wire:target="menuItemClicked({{ $menuItem['id'] }})">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="red"
                             class="w-4 h-4 mr-2 fill-current">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </div>
                    {{ $menuItem['label'] }}
                </button>
            @endforeach
        </div>

        <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">



    <div class="   ">
        @switch($this->tab_id)


            @case('1')

            @php
                $menuItems = [
                    ['id' => 3, 'label' => ' New Purchase'],
                ];
            @endphp

            @foreach ($menuItems as $menuItem)
                <div wire:loading.remove wire:target="menuItemClicked" class="mb-2   rounded-lg   ">

                <button
                        wire:click="subMenuItemClicked({{ $menuItem['id'] }})"
                        class="flex hover:text-white text-center items-center ml-2
            @if ($this->tab_id == $menuItem['id']) bg-blue-500 @else bg-gray-100 @endif
                        @if ($this->tab_id == $menuItem['id']) text-white font-bold @else text-gray-400 font-semibold @endif
                                py-2 px-4 rounded-lg"

                        onmouseover="this.style.backgroundColor='#2D3D88'; this.style.color='white';"
                        onmouseout="this.style.backgroundColor=''; this.style.color='';"
                >

                    <div wire:loading wire:target="subMenuItemClicked({{ $menuItem['id'] }})">
                        <svg aria-hidden="true" class="w-8 h-8 mr-2 text-gray-200 animate-spin dark:text-gray-900 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                        </svg>
                    </div>
                    <div wire:loading.remove wire:target="subMenuItemClicked({{ $menuItem['id'] }})">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>


                    </div>



                    {{ $menuItem['label'] }}
                </button>
    </div>
            @endforeach





            <livewire:procurement.purchase-table/>
            @break
            @case('2')
                <livewire:procurement.assets/>
                @break

        @endswitch

    </div>

    </div>





    {{--CREATE PURCHASE REQUISITION--}}


    <div class="w-full container-fluid">

        @if($this->createPurchaseModal)

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
                                    CREATE PURCHASE REQUISITION
                                </h3>
                            </div>
                            <div class="mt-2"></div>
                            <x-jet-label for="requisition_description" value="{{ __('Write  Requisition ') }}" />
                            <textarea id="requisition_description" name="requisition_description" type="text" class="
                            mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500
                             " wire:model="requisition_description" autofocus >
                                                     </textarea>
                            @error('requisition_description')
                            <div class="border border-red-400 rounded-b text-red-100 px-4 py-3 text-red-700 mt-1">
                                <p> Description  is mandatory</p>
                            </div>
                            @enderror


                            <x-jet-label for="leave_type" value="{{ __('Quantity') }}" />
                            <x-jet-input id="quantity" wire:model="quantity" name="quantity" type="text" class="mt-1 block w-full"     autofocus />
                            @error('quantity')
                            <div class=" rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p> quantity is mandatory</p>
                            </div>
                            @enderror



                            <div class="mt-2"></div>
                        </div>
                        <!-- Add more form fields as needed -->
                        <div class="flex items-center bg-gray-200 justify-end py-3 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                            <button type="button" wire:click="$toggle('createPurchaseModal')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium   border border-transparent rounded-md  focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2  bg-white">
                                Cancel
                            </button>
                            <button type="submit" wire:click="createPurchase" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-400 border border-transparent rounded-md  focus-visible:ring-2 focus-visible:ring-offset-2 ">
                                Proceed
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    {{--  END OF REQUISITION--}}


{{--    $editPurchaseModalBoolean--}}



    {{--PURCHASE REQUISITION EDIT MODAL--}}

    <div class="w-full container-fluid">
        @if($this->editPurchaseModalBoolean)

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

                                    @if (session()->has('message_fail'))
                                    <div class="bg-red-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
                                        <div class="flex">
                                            <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                                            <div>
                                                <p class="font-bold">The process has failed</p>
                                                <p class="text-sm">{{ session('message_fail') }} </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="header-elements text-center justify-center font-bold  stroke-current">
                                <h3 class="fw-bold">
                                    EDIT PURCHASE REQUISITION
                                </h3>
                            </div>
                            <div class="mt-2"></div>
                            <x-jet-label for="requisition_description" value="{{ __('Write  Requisition ') }}" />
                            <textarea id="requisition_description" name="requisition_description" type="text" class="
                            mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500
                             " wire:model="requisition_description" autofocus >
                                                     </textarea>
                            @error('requisition_description')
                            <div class="border border-red-400 rounded-b text-red-100 px-4 py-3 text-red-700 mt-1">
                                <p> Description  is mandatory</p>
                            </div>
                            @enderror


                            <x-jet-label for="leave_type" value="{{ __('Quantity') }}" />
                            <x-jet-input id="quantity" wire:model="quantity" name="quantity" type="text" class="mt-1 block w-full"     autofocus />
                            @error('quantity')
                            <div class=" rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p> quantity is mandatory</p>
                            </div>
                            @enderror

                            <div class="mt-2"></div>
                        </div>
                        <!-- Add more form fields as needed -->
                        <div class="flex items-center bg-gray-200 justify-end py-3 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                            <button type="button" wire:click="$toggle('editPurchaseModalBoolean')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium   border border-transparent rounded-md  focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2  bg-white">
                                Cancel
                            </button>
                            <button type="submit" wire:click="update" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-400 border border-transparent rounded-md  focus-visible:ring-2 focus-visible:ring-offset-2 ">
                                Proceed
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {{--                                            <livewire:procurement.create-vendor/>--}}
        @endif

    </div>

    {{--                            END EDIT MODAL--}}




    {{--delete  modal--}}
        <div class="w-full container-fluid">

            @if($this->deletePurchaseModal)
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
                                </div>
                                <div class="header-elements text-center justify-center font-bold  stroke-current">
                                    <h3 class="fw-bold">
                                        PURCHASE REQUISITION ACTION
                                    </h3>
                                </div>
                                <div class="mt-5"> </div>
                                <div class="p-4 m-4">
                                    <div class="flex gap-4 items-center text-center">
                                        <input  wire:model="permission" name="setSubMenuPermission" type="radio" value="DELETE" /> DELETE
                                    </div>
                                </div>

                                <div class="mt-2"></div>

                            </div>
                            <!-- Add more form fields as needed -->
                            <div class="flex items-center bg-gray-200 justify-end py-3 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                                <button type="button" wire:click="$toggle('deletePurchaseModal')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium   border border-transparent rounded-md  focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2  bg-white">
                                    Cancel
                                </button>
                                <button type="submit" wire:click="action" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-400 border border-transparent rounded-md  focus-visible:ring-2 focus-visible:ring-offset-2 ">
                                    Proceed
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    {{--end delete  modal--}}

</div>
