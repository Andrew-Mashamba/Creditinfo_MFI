<div>
    {{-- The Master doesn't talk, he acts. --}}

    <div>
        {{-- In work, do what you enjoy. --}}


            <div wire:loading.remove wire:target="menuItemClicked" class="mt-4   rounded-lg flex  ">

                @php
                    $menuItems = [
                        ['id' => 3, 'label' => 'New Contract'],

                    ];
                @endphp

                @foreach ($menuItems as $menuItem)
                    <button
                            wire:click="subMenuItemClicked({{ $menuItem['id']}})"
                            class="flex hover:text-white text-center items-center
            @if ($this->tab_id == $menuItem['id']) bg-blue-900 @else bg-gray-100 @endif
                            @if ($this->tab_id == $menuItem['id']) text-white font-bold @else text-gray-400 font-semibold @endif
                                    py-2 px-4 rounded-lg"

                            onmouseover="this.style.backgroundColor='#2D3D88'; this.style.color='white';"
                            onmouseout="this.style.backgroundColor=''; this.style.color='';"
                    >

                        <div wire:loading wire:target="subMenuItemClicked({{ $menuItem['id']}})">
                            <svg aria-hidden="true" class="w-8 h-8 mr-2 text-gray-200 animate-spin dark:text-gray-900 fill-red-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                            </svg>
                        </div>
                        <div wire:loading.remove wire:target="subMenuItemClicked({{ $menuItem['id']}})">
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

            <div class="mt-2     mt-4   w-full  ">
                <livewire:procurement.contract-table/>

            </div>






        {{--CREATE CONTRACT--}}
        <div class="w-full container-fluid">
            @if($this->createContractModal)
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
                                        CREATE CONTRACT
                                    </h3>
                                </div>
                                <div class="mt-2"></div>

                                <div class="mt-2"></div>
                                <x-jet-label for="leave_type" value="{{ __('Contract Name') }}" />
                                <x-jet-input id="contract_name" wire:model="contract_name" name="contract_name" type="text" class="
                                mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500
                                   "     autofocus />
                                @error('contract_name')
                                <div class=" rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p> Contract name is mandatory</p>
                                </div>
                                @enderror


                                <x-jet-label for="branch_id" value="{{ __('Select Vendor') }}" />
                                <select wire:model="vendorId" name="vendorId" id="vendorId" class="
                                mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500
                                  ">
                                    <option selected unselected >Select ....</option>
                                    @foreach(DB::table('vendors')->get() as $vendor)
                                        <option  value=" {{$vendor->id}}">{{$vendor->organization_name}}</option>
                                    @endforeach
                                </select>
                                @error('vendorId')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>vendor is mandatory.</p>
                                </div>
                                @enderror



                                <div class="mt-2"></div>
                                <x-jet-label for="startDate" value="{{ __('Start Date') }}" />
                                <x-jet-input id="startDate" name="startDate" type="date" class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                             wire:model.defer="startDate" autofocus />
                                @error('startDate')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>  date  is mandatory</p>
                                </div>
                                @enderror
                                <div class="mt-2"></div>

                                <x-jet-label for="endDate" value="{{ __('End  Date') }}" />
                                <x-jet-input id="endDate" name="endDate" type="date" class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                             wire:model.defer="endDate" autofocus />
                                @error('endDate')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>  date  is mandatory</p>
                                </div>
                                @enderror
                                <div class="mt-2"></div>


                                <x-jet-label for="contract_description" value="{{ __('Contract Description ') }}" />
                                <textarea id="contract_description" name="contract_description" type="text" class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                          wire:model.defer="contract_description" autofocus >
                                                          </textarea>
                                @error('contract_description')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p> Contract description  is mandatory</p>
                                </div>
                                @enderror
                                <div class="mt-2"></div>
                                <x-jet-label for="contract_file_path" value="{{ __('Upload File') }}" />
                                <x-jet-input id="contract_file_path" wire:model="contract_file_path" name="contract_file_path" type="file" class="mt-1 block w-full"     autofocus />
                                @error('contract_file_path')
                                <div class=" rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p> File name is mandatory</p>
                                </div>
                                @enderror

                            </div>
                            <!-- Add more form fields as needed -->
                            <div class="flex items-center bg-gray-200 justify-end py-3 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                                <button type="button" wire:click="$toggle('createContractModal')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium   border border-transparent rounded-md  focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2  bg-white">
                                    Cancel
                                </button>
                                <button type="submit" wire:click="createContract" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-400 border border-transparent rounded-md  focus-visible:ring-2 focus-visible:ring-offset-2 ">
                                    Proceed
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                {{--                                            <livewire:procurement.create-vendor/>--}}
            @endif


        </div>
        {{--  END OF CONTRACT--}}


        {{--    $editPurchaseModalBoolean--}}



        {{--Contract EDIT MODAL--}}

        <div class="w-full container-fluid">
            @if($this->editContractModalBoolean)
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
                                        EDIT CONTRACT
                                    </h3>
                                </div>
                                <div class="mt-2"></div>

                                <div class="mt-2"></div>
                                <x-jet-label for="leave_type" value="{{ __('Contract Name') }}" />
                                <x-jet-input id="contract_name" wire:model="contract_name" name="contract_name" type="text" class="
                                mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500
                                   "     autofocus />
                                @error('contract_name')
                                <div class=" rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p> Contract name is mandatory</p>
                                </div>
                                @enderror


                                <x-jet-label for="branch_id" value="{{ __('Select Vendor') }}" />
                                <select wire:model="vendorId" name="vendorId" id="vendorId" class="
                                mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500
                                  ">
                                    <option selected unselected >Select ....</option>
                                    @foreach(DB::table('vendors')->get() as $vendor)
                                        <option  value=" {{$vendor->id}}">{{$vendor->organization_name}}</option>
                                    @endforeach
                                </select>
                                @error('vendorId')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>vendor is mandatory.</p>
                                </div>
                                @enderror



                                <div class="mt-2"></div>
                                <x-jet-label for="startDate" value="{{ __('Start Date') }}" />
                                <x-jet-input id="startDate" name="startDate" type="date" class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                             wire:model.defer="startDate" autofocus />
                                @error('startDate')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>  date  is mandatory</p>
                                </div>
                                @enderror
                                <div class="mt-2"></div>

                                <x-jet-label for="endDate" value="{{ __('End  Date') }}" />
                                <x-jet-input id="endDate" name="endDate" type="date" class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                             wire:model.defer="endDate" autofocus />
                                @error('endDate')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>  date  is mandatory</p>
                                </div>
                                @enderror
                                <div class="mt-2"></div>


                                <x-jet-label for="contract_description" value="{{ __('Contract Description ') }}" />
                                <textarea id="contract_description" name="contract_description" type="text" class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                          wire:model.defer="contract_description" autofocus >
                                                          </textarea>
                                @error('contract_description')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p> Contract description  is mandatory</p>
                                </div>
                                @enderror
                                <div class="mt-2"></div>
                                <x-jet-label for="contract_file_path" value="{{ __('Upload File') }}" />
                                <x-jet-input id="contract_file_path" wire:model="contract_file_path" name="contract_file_path" type="file" class="mt-1 block w-full"     autofocus />
                                @error('contract_file_path')
                                <div class=" rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p> File name is mandatory</p>
                                </div>
                                @enderror

                            </div>
                            <!-- Add more form fields as needed -->
                            <div class="flex items-center bg-gray-200 justify-end py-3 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                                <button type="button" wire:click="$toggle('editContractModalBoolean')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium   border border-transparent rounded-md  focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2  bg-white">
                                    Cancel
                                </button>
                                <button type="submit" wire:click="createContract" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-400 border border-transparent rounded-md  focus-visible:ring-2 focus-visible:ring-offset-2 ">
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

            @if($this->deleteContractModal)
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
                                     END CONTRACT
                                    </h3>
                                </div>
                                <div class="mt-5"> </div>
                                <div class="p-4 m-4">
                                    <div class="flex gap-4 items-center text-center">
                                        <input  wire:model="permission" name="setSubMenuPermission" type="checkbox" value="DELETE" class="
                                                       mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                                    " /> Accept End
                                    </div>
                                </div>
                                @error('permission')
                                <div class="text-red-500">
                                    Accept before you proceed
                                </div>
                                @enderror

                                <div class="mt-2"></div>

                            </div>
                            <!-- Add more form fields as needed -->
                            <div class="flex items-center bg-gray-200 justify-end py-3 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                                <button type="button" wire:click="$toggle('deleteContractModal')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium   border border-transparent rounded-md  focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2  bg-white">
                                    Cancel
                                </button>
                                <button type="submit" wire:click="action" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-400 border border-transparent rounded-md  focus-visible:ring-2 focus-visible:ring-offset-2 ">
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

</div>
