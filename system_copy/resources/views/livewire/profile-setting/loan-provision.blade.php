<div class=" bg-white p-4 mb-4 ">



    @if($this->has_clicked)

        <div class="fixed z-10 inset-0 overflow-y-auto"  >
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity">
                    <div class="absolute inset-0 bg-gray-500 opacity-0"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <!-- Your form elements go here -->
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
                                  Loan   Provision Setting
                                </h5>
                            </div>




                                <div >
                                    <!-- Name -->
                                    <div class="col-span-6 sm:col-span-4">
                                        <x-jet-label for="account_name" value="{{ __('Per') }}" />
                                        <x-jet-input id="account_name" type="number" class="mt-1 block w-full" wire:model.defer="per" autocomplete="per" />

                                        <x-jet-input-error for="account_name" class="mt-2" />
                                    </div>

                                    <div class="col-span-6 sm:col-span-4">
                                        <x-jet-label for="account_number" value="{{ __('Percentage') }}" />
                                        <x-jet-input id="account_number" type="number" class="mt-1 block w-full" wire:model.defer="percent" />
                                        <x-jet-input-error for="percent" class="mt-2" />
                                    </div>

                                    <div class="col-span-6 sm:col-span-4">
                                        <x-jet-label for="discription" value="{{ __('Discription') }}" />
                                        <textarea id="description" wire:model="description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:borde"></textarea>
                                        <x-jet-input-error for="description" class="mt-2" />
                                    </div>



                                </div>



                        </div>
                    </div>


                    <!-- Add more form fields as needed -->
                    <div class="flex items-center bg-gray-200 justify-end py-3 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                        <button type="button" wire:click="$toggle('has_clicked')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium   border border-transparent rounded-md  focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2  bg-white">
                            Cancel
                        </button>
                        <button type="submit" wire:click="register" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-400 border border-transparent rounded-md  focus-visible:ring-2 focus-visible:ring-offset-2 ">
                            Proceed
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @endif

    @if($this->has_clicked_delete)

    <div class="fixed z-10 inset-0 overflow-y-auto"  >
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
            <div class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-500 opacity-0"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <!-- Your form elements go here -->
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

                        <div class="mb-4 flex justify-center text-center">
                            <h5 >
                              Delete Action
                            </h5>
                        </div>




                            <div class="flex justify-center item-center " >
                                <!-- Name -->
                                    <svg data-slot="icon"  class="w-12 h-12  " fill="none" stroke-width="1.5" stroke="red" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"></path>
                                      </svg>







                            </div>

                            <div class="font-bold flex justify-center item-center  ">
                                Are You Sure You Want to delete
                              </div>


                    </div>
                </div>


                <!-- Add more form fields as needed -->
                <div class="flex items-center bg-gray-200 justify-end py-3 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                    <button type="button" wire:click="$toggle('has_clicked_delete')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium   border border-transparent rounded-md  focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2  bg-white">
                        Cancel
                    </button>
                    <button type="submit" wire:click="delete" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-500 border border-transparent rounded-md  focus-visible:ring-2 focus-visible:ring-offset-2 ">
                        Proceed
                    </button>
                </div>
            </div>
        </div>
    </div>

@endif


    @if($this->has_clicked_update)

    <div class="fixed z-10 inset-0 overflow-y-auto"  >
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
            <div class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-500 opacity-0"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <!-- Your form elements go here -->
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

                    @if (session()->has('message_fail'))
                    {{-- @if (session('alert-class') == 'alert-success') --}}
                        <div class="bg-red-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
                            <div class="flex">
                                <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                                <div>
                                    <p class="font-bold">The process is failed</p>
                                    <p class="text-sm">{{ session('message_fail') }} </p>
                                </div>
                            </div>
                        </div>
                    {{-- @endif --}}
                @endif


                    <div class="bg-white p-4">

                        <div class="mb-4">
                            <h5 >
                              Loan   Provision Setting
                            </h5>
                        </div>




                            <div >
                                <!-- Name -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-jet-label for="account_name" value="{{ __('Per') }}" />
                                    <x-jet-input id="account_name" type="number" class="mt-1 block w-full" wire:model.defer="per" autocomplete="per" />

                                    <x-jet-input-error for="account_name" class="mt-2" />
                                </div>

                                <div class="col-span-6 sm:col-span-4">
                                    <x-jet-label for="account_number" value="{{ __('Percentage') }}" />
                                    <x-jet-input id="account_number" type="number" class="mt-1 block w-full" wire:model.defer="percent" />
                                    <x-jet-input-error for="percent" class="mt-2" />
                                </div>

                                <div class="col-span-6 sm:col-span-4">
                                    <x-jet-label for="discription" value="{{ __('Discription') }}" />
                                    <textarea id="description" wire:model="description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:borde"></textarea>
                                    <x-jet-input-error for="description" class="mt-2" />
                                </div>



                            </div>



                    </div>
                </div>


                <!-- Add more form fields as needed -->
                <div class="flex items-center bg-gray-200 justify-end py-3 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                    <button type="button" wire:click="$toggle('has_clicked_update')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium   border border-transparent rounded-md  focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2  bg-white">
                        Cancel
                    </button>
                    <button type="submit" wire:click="update" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-400 border border-transparent rounded-md  focus-visible:ring-2 focus-visible:ring-offset-2 ">
                        Proceed
                    </button>
                </div>
            </div>
        </div>
    </div>

@endif



    <div class=" bg-white  mb-4 ">

        <div class="container mx-auto mt-8   ">
            <div class="overflow-x-auto mt-4 ">

                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left  ">  </th>
                            <th class="px-4 py-2 text-left   ">  </th>
                            <th class="px-4 py-2 text-left  "> JV./2023 </th>
                            <th class="px-4 py-2 text-left  ">  </th>
                            <th class="px-4 py-2 text-right flex justify-end item-end   border-gray-200">
                                <svg wire:click="createModal()" data-slot="icon" fill="none" class="bg-white rounded-full  w-10 h-10 " stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                                  </svg>

                            </th>
                        </tr>

                        <tr>
                            <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200"> S/N </th>
                            <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200"> Description </th>
                            <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200"> Per    </th>
                            <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200"> Percent  </th>
                            <th class="px-4 py-2 text-right bg-blue-100 border border-gray-200"> Action  </th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($provisions as $provision )


                        <tr>
                            <td class="px-4 py-2 border-blue-200  border "> {{ $loop->iteration }} </td>
                            <td class="px-4 py-2 border-blue-200 border "> {{ $provision->description }} </td>
                            <td class="px-4 py-2 border-blue-200 border "> {{ $provision->per }} </td>
                            <td class="px-4 py-2 border-blue-200 border ">  {{ $provision->percent }} % </td>


                            <td class="px-4 py-2 text-right border-blue-200 border">

                                <div class="flex gap-4 ">
                                    <svg wire:click="editModal({{$provision->id}})" data-slot="icon" class="w-6 h-6 " fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path>
                                      </svg>

                                      <svg wire:click="deleteModal({{$provision->id}})" data-slot="icon"   class="w-6 h-6 "  fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path>
                                      </svg>


                                </div>

                            </td>


                        </tr>

                        @endforeach




                    </tbody>

                </table>


            </div>
        </div>





    </div>





</div>
