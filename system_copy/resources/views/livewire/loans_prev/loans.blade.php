
<div>
    <div class="p-2">
        <!-- Welcome banner -->

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
                        LOANS MANAGEMENT

                    </div>

                </div>
                <div>

                    <ul class="max-w-md space-y-1 text-gray-500 list-inside dark:text-gray-400">
                        <li class="flex items-center">
                            <svg class="w-3.5 h-3.5 mr-2 text-blue-900 dark:text-blue-900 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                            </svg>
                            Total Loans: {{ App\Models\LoansModel::count() }}
                        </li>
                        <li class="flex items-center">
                            <svg class="w-3.5 h-3.5 mr-2 text-blue-900 dark:text-blue-900 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                            </svg>
                            Active Loans: {{ $this->activeLoansCount }}
                        </li>
                        <li class="flex items-center">
                            <svg class="w-3.5 h-3.5 mr-2 text-blue-900 dark:text-blue-900 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                            </svg>
                            Inactive Loans:

                            <span>
                                <span class="ml-2 bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-red-400 border border-red-400"> {{ $this->inactiveLoansCount }}</span>
                            </span>



                        </li>
                    </ul>
                </div>

            </div>

        </div>





        <div class="bg-white p-4 sm:p-6 overflow-x-auto  mb-2 rounded-lg">

{{--            @if (in_array( "Set loan parameters and interest rates" , session()->get('permission_items')))--}}

            <div class="grid grid-cols-4 gap-2 p-2">
                @php
                    $menuItems = [
                        ['id' => 2, 'label' => 'Loans in progress'],
                        ['id' => 3, 'label' => 'Loans committee'],
                        ['id' => 4, 'label' => 'Loans arrears'],
                        ['id' => 5, 'label' => 'Loans recovery'],
                        ['id' => 6, 'label' => ' Loans writer off'],

                    ];
                @endphp


                @foreach ($menuItems as $menuItem)
                    <button
                            wire:click="setView({{ $menuItem['id'] }})"
                            class="flex hover:text-white text-center items-center w-full
            @if ($this->tab_id == $menuItem['id']) bg-blue-900 @else bg-gray-100 @endif
                            @if ($this->tab_id == $menuItem['id']) text-white font-bold @else text-gray-400 font-semibold @endif
                                    py-2 px-4 rounded-lg"

                            onmouseover="this.style.backgroundColor='#2D3D88'; this.style.color='white';"
                            onmouseout="this.style.backgroundColor=''; this.style.color='';"
                    >

                        <div wire:loading wire:target="setView({{ $menuItem['id'] }})">
                            <svg aria-hidden="true" class="w-8 h-8 mr-2 text-gray-200 animate-spin dark:text-gray-900 fill-red-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                            </svg>
                        </div>
                        <div wire:loading.remove wire:target="setView({{ $menuItem['id'] }})">
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

{{--            @endif--}}

            <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">

            <div class="tab-pane fade " id="tabs-homeJustify"
                 role="tabpanel" aria-labelledby="tabs-home-tabJustify">
                <div class="mt-2"></div>
                <div class="w-full flex items-center justify-center">
                    <div wire:loading wire:target="setView">
                        <div class="h-96 m-auto flex items-center justify-center">
                            <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-white"></div>
                        </div>
                    </div>
                </div>





            </div>
                    @if(auth()->user()->branch == DB::table('branches')->where('name','HQ')->value('id') && auth()->user()->department == DB::table('departments')->where('department_name', 'MANAGEMENT')->value('id'))
                   <div class="flex gap-4">
                       <div class=" w-56 mb-6">
                           <label for="sortByBranch" class="block mb-2 dark:text-gray-400 space-x-2 text-sm  spacing-sm text-slate-600">
                               Sort by branch
                           </label>
                           <div class="relative ">
                               <select wire:model.bounce="sortByBranch" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                   <option value="">select</option>
                                   @foreach(DB::table('branches')->get() as $branch)
                                       <option value={{ $branch->id }} >{{ $branch->name }}</option>

                                   @endforeach

                               </select>
                           </div>
                       </div>

                       <div class=" w-56 mb-6">
                           <label for="filterLoanOfficer" class="block mb-2 dark:text-gray-400 space-x-2 text-sm  spacing-sm text-slate-600">
                               Search Loan Officer
                           </label>
                           <div class="relative ">
                               <select wire:model.bounce="filterLoanOfficer" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                   <option value="">select</option>
                                   @foreach(DB::table('employees')->get() as $employee)
                                       <option value={{ $employee->employee_number }} >{{ $employee->first_name.' '.$employee->last_name }}</option>

                                   @endforeach

                               </select>
                           </div>
                       </div>
                   </div>
                    @endif










{{--            @if($this->tab_id == 1 )--}}
{{--                <livewire:loans.loan-application/>--}}
{{--            @endif--}}

            @if($this->tab_id == 2 )
                <livewire:loans.loans-status/>
            @endif

            @if($this->tab_id == 3 )
                <livewire:loans.committee/>
            @endif

            @if($this->tab_id == 4 )
                <livewire:loans.loans-status/>
            @endif

            @if($this->tab_id == 5 )
                <livewire:loans.loans-status/>
            @endif

            @if($this->tab_id == 6 )
                <livewire:loans.loans-status/>
            @endif


        </div>


    </div>










<!-- Log Out Other Devices Confirmation Modal -->
<x-jet-dialog-modal wire:model="showCreateNewLoansAccount">
    <x-slot name="title">

    </x-slot>

    <x-slot name="content">


        <div>
            @if (session()->has('message'))

                @if (session('alert-class') == 'alert-success')
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
            @endif

                <div class="w-full bg-white  p-4">

                    <div class="w-full">
                        <div class="mb-4">
                            <h5 >
                                CREATE LoansAccount
                            </h5>
                        </div>


                        <div class="col-span-6 sm:col-span-4 mb-4">


                            <p for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">LoansAccount Name</p>
                            <x-jet-input id="name" type="text" name="name" class="mt-1 block w-full" wire:model.defer="name" autofocus />
                            @error('name')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>The name is mandatory and should be more than three characters.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>


                            <p for="region" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Region</p>
                            <x-jet-input id="region" name="region" type="text" class="mt-1 block w-full" wire:model.defer="region" autofocus />
                            @error('region')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>The region is mandatory and should be more than three characters.</p>
                            </div>
                            @enderror

                            <div class="mt-2"></div>

                            <p for="wilaya" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Wilaya</p>
                            <x-jet-input id="wilaya" name="wilaya" type="text" class="mt-1 block w-full" wire:model.defer="wilaya" autofocus />
                            @error('wilaya')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>The wilaya is mandatory and should be more than three characters.</p>
                            </div>
                            @enderror

                            <div class="mt-2"></div>

                            <p for="membershipNumber" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Clientship Number</p>
                            <x-jet-input id="membershipNumber" name="membershipNumber" type="text" class="mt-1 block w-full" wire:model.defer="membershipNumber" autofocus />
                            @error('membershipNumber')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>The membership number is mandatory, it should be more than three characters and unique.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>


                            <p for="parentLoansAccount" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Parent LoansAccount</p>
                            <select wire:model.bounce="parentLoansAccount" name="parentLoansAccount" id="parentLoansAccount" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option tab_id value="">Select</option>
                                @foreach(App\Models\LoansModel::all() as $LoansAccount)
                                <option value="{{$LoansAccount->id}}">{{$LoansAccount->name}}</option>
                                @endforeach

                            </select>
                            @error('parentLoansAccount')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>The Parent LoansAccount is mandatory.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>

                        </div>






                    </div>




                </div>



        </div>



    </x-slot>

    <x-slot name="footer">
        <x-jet-secondary-button wire:click="$toggle('showCreateNewLoansAccount')" wire:loading.attr="disabled">
            {{ __('Cancel') }}
        </x-jet-secondary-button>
        <div wire:loading.remove>
        <x-jet-button class="ml-3"
                      wire:click="submit"
                      wire:loading.attr="disabled">
            {{ __('Create user') }}
        </x-jet-button>
        </div>
        <div wire:loading>
        <x-jet-button class="ml-3 "  >
            <svg aria-hidden="true" role="status" class="inline w-4 h-4 mr-3 text-white animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="#E5E7EB"/>
                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentColor"/>
            </svg>
            Please wait...
        </x-jet-button>
        </div>

    </x-slot>
</x-jet-dialog-modal>



<!-- Log Out Other Devices Confirmation Modal -->
<x-jet-dialog-modal wire:model="showDeleteLoansAccount">
    <x-slot name="title">

    </x-slot>

    <x-slot name="content">
        <div>
            @if (session()->has('message'))

                @if (session('alert-class') == 'alert-success')
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
                @if (session('alert-class') == 'alert-warning')
                    <div class="bg-yellow-100 border-t-4 border-yellow-500 rounded-b text-yellow-900 px-4 py-3 shadow-md mb-8" role="alert">
                        <div class="flex">
                            <div class="py-1"><svg class="fill-current h-6 w-6 text-yellow-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                            <div>
                                <p class="font-bold">Error</p>
                                <p class="text-sm">{{ session('message') }} </p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <div class="flex w-full">
                <!-- message container -->


                <div class="w-full p-4 ">




                    @if($this->LoansAccountSelected)




                        <p  class="block mb-1 text-sm capitalize text-slate-400 dark:text-white ">LoansAccount SELECTED</p>
                        <div class="flex items-center mb-2 text-sm spacing-sm text-slate-600 mt-2" >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <p>{{ App\Models\LoansModel::where('id', $this->LoansAccountSelected)->value('name') }}</p>

                        </div>

                        <div class="mt-4 w-full">
                            <p for="LoansAccountSelected" class="block mb-1 text-sm capitalize text-slate-400 dark:text-white ">SELECT ACTION</p>

                            <div class="flex gap-4 items-center text-center">
                                <input  wire:model="permission" name="setSubMenuPermission" type="radio" value="BLOCKED" checked  > Block
                                <input  wire:model="permission" name="setSubMenuPermission" type="radio" value="ACTIVE" /> Activate
                                <input  wire:model="permission" name="setSubMenuPermission" type="radio" value="DELETED" /> Delete
                            </div>



                        </div>



                        <p for="password" class="block mb-1 mt-4 text-sm capitalize text-slate-400 dark:text-white ">ENTER PASSWORD TO CONFIRM</p>
                        <input wire:model.defer="password" id="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                        <x-jet-input-error for="current_password" class="mt-2" />


                    @endif
                </div>

            </div>


            <div class="mt-8"></div>






        </div>


    </x-slot>

    <x-slot name="footer">
        <x-jet-secondary-button wire:click="$toggle('showDeleteLoansAccount')" wire:loading.attr="disabled">
            {{ __('Cancel') }}
        </x-jet-secondary-button>
        <div wire:loading.remove wire:target="confirmPassword" >
            <x-jet-button class="ml-3"
                          wire:click="confirmPassword"
                          wire:loading.attr="disabled">
                {{ __('Proceed') }}
            </x-jet-button>
        </div>
        <div wire:loading wire:target="confirmPassword">
            <x-jet-button class="ml-3 "  >
                <svg aria-hidden="true" role="status" class="inline w-4 h-4 mr-3 text-white animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="#E5E7EB"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentColor"/>
                </svg>
                Please wait...
            </x-jet-button>
        </div>

    </x-slot>
</x-jet-dialog-modal>




<!-- Log Out Other Devices Confirmation Modal -->
<x-jet-dialog-modal wire:model="showEditLoansAccount">
    <x-slot name="title">

    </x-slot>

    <x-slot name="content">











        <div>
            @if (session()->has('message'))

                @if (session('alert-class') == 'alert-success')
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
            @endif


                <div class="bg-white p-4">

                    <div class="mb-4">
                        <h5 >
                            EDIT LoansAccount : {{$this->pendingLoansAccountname}}
                        </h5>
                    </div>



                    @if($this->LoansAccount)

                    <div >

                        <div class="col-span-6 sm:col-span-4">
                            <x-jet-label for="membershipNumber" value="{{ __('Clientship Number') }}" />
                            <x-jet-input id="membershipNumber" type="text" class="mt-1 block w-full" wire:model.defer="membershipNumber" disabled/>
                            <x-jet-input-error for="membershipNumber" class="mt-2" />
                        </div>
                        <!-- Name -->
                        <div class="col-span-6 sm:col-span-4">
                            <x-jet-label for="name" value="{{ __('LoansAccount Name') }}" />
                            <x-jet-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="name" autocomplete="name" />

                            <x-jet-input-error for="name" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <x-jet-label for="region" value="{{ __('Region') }}" />
                            <x-jet-input id="region" type="text" class="mt-1 block w-full" wire:model.defer="region" autocomplete="region" />
                            <x-jet-input-error for="region" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <x-jet-label for="wilaya" value="{{ __('Wilaya') }}" />
                            <x-jet-input id="wilaya" type="text" class="mt-1 block w-full" wire:model.defer="wilaya" autocomplete="wilaya" />
                            <x-jet-input-error for="wilaya" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <x-jet-label for="email" value="{{ __('Email') }}" />
                            <x-jet-input id="email" type="email" class="mt-1 block w-full" wire:model.defer="email" />
                            <x-jet-input-error for="email" class="mt-2" />
                        </div>



                    </div>


                    @endif

                </div>
        </div>



    </x-slot>

    <x-slot name="footer">
        <x-jet-secondary-button wire:click="$toggle('showEditLoansAccount')" wire:loading.attr="disabled">
            {{ __('Cancel') }}
        </x-jet-secondary-button>
        <div wire:loading.remove wire:target="updateLoansAccount" >
            <x-jet-button class="ml-3"
                          wire:click="updateLoansAccount"
                          wire:loading.attr="disabled">
                {{ __('Proceed') }}
            </x-jet-button>
        </div>
        <div wire:loading wire:target="updateLoansAccount">
            <x-jet-button class="ml-3 "  >
                <svg aria-hidden="true" role="status" class="inline w-4 h-4 mr-3 text-white animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="#E5E7EB"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentColor"/>
                </svg>
                Please wait...
            </x-jet-button>
        </div>

    </x-slot>
</x-jet-dialog-modal>


<!-- Log Out Other Devices Confirmation Modal -->
<x-jet-dialog-modal wire:model="showAddLoansAccount">
    <x-slot name="title">

    </x-slot>

    <x-slot name="content">











        <div>
            @if (session()->has('message'))

                @if (session('alert-class') == 'alert-success')
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
            @endif


                <div class="bg-white p-4">

                    <div class="mb-4">
                        <h5 >
                           CREATE NEW DEPOSITS ACCOUNT
                        </h5>
                    </div>



                    <div class="w-full">
                        <!-- message container -->
                        <div>
                            @if (session()->has('message'))

                                @if (session('alert-class') == 'alert-success')
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
                            @endif
                        </div>


                        <div class="bg-gray-100 rounded px-6 rounded-lg shadow-sm  pt-4 pb-4 ">


                            <div class="flex items-stretch">

                                <div class="w-1/2 mr-2" >

                                    <p for="member" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Select Client</p>
                                    <select wire:model.bounce="member" name="member" id="member" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                        <option tab_id value="">Select</option>
                                        @foreach(App\Models\ClientsModel::all() as $members)
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
                                        <div class="flex justify-between">
                                            <p for="member" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Existing loans accounts</p>
                                            <div>
                                                @foreach(App\Models\AccountsModel::where('member_number',$this->member)->where('product_number','11')->get() as $account)
                                                    @php
                                                        $this->account_number = $account->account_number;
                                                    @endphp
                                                    <p class="block mb-2 text-sm font-medium text-red-500 dark:text-gray-400">{{$account->account_number}}</p>
                                                @endforeach
                                            </div>


                                        </div>

                                        <div class="mt-2"></div>
                                    @endif


                                    <p for="product" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Product</p>
                                    <select wire:model.bounce="product" name="product" id="product" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                        <option tab_id value="">Select</option>
                                        @foreach(App\Models\sub_products::where('product_id','11')->get() as $product)
                                            <option value="{{$product->sub_product_id}}">{{$product->sub_product_name}}</option>
                                        @endforeach

                                    </select>
                                    @error('product')
                                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                        <p>Branch is mandatory.</p>
                                    </div>
                                    @enderror
                                    <div class="mt-2"></div>


                                </div>



                            </div>



                        </div>


                    </div>




                </div>
        </div>



    </x-slot>

    <x-slot name="footer">
        <x-jet-secondary-button wire:click="$toggle('showAddLoansAccount')" wire:loading.attr="disabled">
            {{ __('Cancel') }}
        </x-jet-secondary-button>
        <div wire:loading.remove wire:target="addLoansAccount" >
            <x-jet-button class="ml-3"
                          wire:click="addLoansAccount"
                          wire:loading.attr="disabled">
                {{ __('Proceed') }}
            </x-jet-button>
        </div>
        <div wire:loading wire:target="addLoansAccount">
            <x-jet-button class="ml-3 "  >
                <svg aria-hidden="true" role="status" class="inline w-4 h-4 mr-3 text-white animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="#E5E7EB"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentColor"/>
                </svg>
                Please wait...
            </x-jet-button>
        </div>

    </x-slot>
</x-jet-dialog-modal>


<!-- Log Out Other Devices Confirmation Modal -->
<x-jet-dialog-modal wire:model="showIssueNewLoans">
    <x-slot name="title">

    </x-slot>

    <x-slot name="content">











        <div class="w-full">
            <!-- message container -->
            <div>
                @if (session()->has('message'))

                    @if (session('alert-class') == 'alert-success')
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
                @endif
            </div>


            <div class="bg-gray-100 rounded px-6 rounded-lg shadow-sm  pt-4 pb-4 ">


                <div class="flex items-stretch">

                    <div class="w-1/2 mr-2" >

                        <p for="member" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Select Client</p>
                        <select wire:model.bounce="member" name="member" id="member" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option tab_id value="">Select</option>
                            @foreach(App\Models\ClientsModel::all() as $client)
                                <option value="{{$client->client_number}}">{{$client->first_name}} {{$client->middle_name}} {{$client->last_name}}</option>
                            @endforeach

                        </select>
                        @error('member')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Branch is mandatory.</p>
                        </div>
                        @enderror
                        <div class="mt-2"></div>

                        @if($this->member)





                            <p for="member" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Saving Account</p>


                            <table class="w-full">

                                @foreach(App\Models\AccountsModel::where('member_number',$this->member)->where('product_number','11')->get() as $account)
                                    @php
                                        $this->account_number = $account->account_number;
                                    @endphp

                                    <tr>
                                        <td class="text-left">
                                            <p class="block text-sm font-medium text-red-500 dark:text-gray-400 capitalize">
                                                {{strtolower(App\Models\sub_products::where('sub_product_id',$account->sub_product_number)->value('sub_product_name')) }}
                                            </p>
                                        </td>
                                        <td class="pl-4 text-right"><p class="block text-sm font-medium text-red-500 dark:text-gray-400"><p>{{$account->account_number}}</p></td>
                                        <td class="text-right">



                                            <div wire:loading wire:target="setAccount({{$account->account_number}})" >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-9 w-9 stroke-gray-400 rounded-full p-2" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                                                </svg>
                                            </div>

                                            @if($this->accountSelected == $account->account_number)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-green-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @else
                                                <div wire:loading.remove wire:target="setAccount({{$account->account_number}})">
                                                    <svg wire:click="setAccount({{$account->account_number}})" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-slate-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </div>
                                            @endif

                                        </td>
                                    </tr>

                                @endforeach



                            </table>


                        @endif

                        <div class="mt-2"></div>

                        @if($this->product)
                            <div class="flex justify-between">
                                <p class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Price per share</p>
                                @foreach(App\Models\sub_products::where('sub_product_id',$this->product)->get() as $product)
                                    @php
                                        $this->nominal_price = $product->nominal_price;
                                    @endphp
                                    <p class="block mb-2 text-sm font-medium text-red-500 dark:text-gray-400">{{number_format($product->nominal_price,2)}}</p>
                                @endforeach

                            </div>
                            <div class="flex justify-between">
                                <p class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Available loans for this user</p>
                                @php
                                            $minimum_loans = App\Models\institutions::where('id',Session::get('institution_id'))->value('minimum_loans');
                                            $institution_total_loans = App\Models\issured_loans::where('institution_id',Session::get('institution_id'))->sum('number_of_loans');
                                            $user_number_of_loans = App\Models\issured_loans::where('member',$this->member)->sum('number_of_loans');


                                            if($user_number_of_loans > $minimum_loans ){

                                            $percent = $institution_total_loans/$user_number_of_loans *100;


                                            if($percent > 20 ){
                                                $this->loansAvailable = 0;
                                            }else{
                                               $this->loansAvailable = $percent;
                                            }

                                            }else{

                                               $this->loansAvailable = $minimum_loans - $user_number_of_loans;
                                            }



                                @endphp
                                    <p class="block mb-2 text-sm font-medium text-red-500 dark:text-gray-400">
                                        {{$this->loansAvailable }}


                                    </p>


                            </div>

                            <div class="mt-2"></div>


                        <x-jet-label for="number_of_loans" value="{{ __('Number of loans') }}" />
                        <x-jet-input id="number_of_loans" type="number" name="number_of_loans" class="mt-1 block w-full" wire:model.bounce="number_of_loans" autofocus />
                        @error('number_of_loans')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Number of loans is mandatory and should be more than two characters.</p>
                        </div>
                        @enderror
                        <div class="mt-2"></div>
                        @if($this->number_of_loans)
                            <div class="flex justify-between">
                                <p class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Total price</p>

                                <p class="block mb-2 text-sm font-medium text-red-500 dark:text-gray-400">{{number_format($this->number_of_loans * $this->nominal_price,2)}}</p>
                            </div>

                            <div class="mt-2"></div>
                        @endif



                        <p for="linked_loans_account" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Purchase from linked Loans Account</p>
                        <select wire:model.bounce="linked_loans_account" name="linked_loans_account" id="linked_loans_account" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option tab_id value="">Select</option>

                            @foreach(App\Models\AccountsModel::where('member_number',$this->member)->where('product_number','12')->get() as $account)

                                <option value="{{$account->account_number}}">{{$account->account_number}}</option>
                            @endforeach

                        </select>
                        @error('product')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Linked Loans Account is mandatory.</p>
                        </div>
                        @enderror
                        <div class="mt-2"></div>


                        @if($this->linked_loans_account)
                            <div class="flex justify-between">
                                <p for="member" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Account balance</p>
                                @foreach(App\Models\AccountsModel::where('account_number',$this->linked_loans_account)->get() as $account)
                                    @php
                                        $this->balance = $account->balance;
                                    @endphp
                                    <p class="block mb-2 text-sm font-medium text-red-500 dark:text-gray-400">{{number_format($account->balance,2)}}</p>
                                @endforeach

                            </div>

                            <div class="mt-2"></div>
                        @endif


                    </div>



                </div>




                @endif

            </div>


        </div>









    </x-slot>

    <x-slot name="footer">
        <x-jet-secondary-button wire:click="$toggle('showIssueNewLoans')" wire:loading.attr="disabled">
            {{ __('Cancel') }}
        </x-jet-secondary-button>
        <div wire:loading.remove wire:target="issueLoans" >
            <x-jet-button class="ml-3"
                          wire:click="issueLoans"
                          wire:loading.attr="disabled">
                {{ __('Proceed') }}
            </x-jet-button>
        </div>
        <div wire:loading wire:target="issueLoans">
            <x-jet-button class="ml-3 "  >
                <svg aria-hidden="true" role="status" class="inline w-4 h-4 mr-3 text-white animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="#E5E7EB"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentColor"/>
                </svg>
                Please wait...
            </x-jet-button>
        </div>

    </x-slot>
</x-jet-dialog-modal>










</div>

