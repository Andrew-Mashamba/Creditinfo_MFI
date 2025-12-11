<div>

        <div class="p-1">

                <div class="w-full mb-1 flex gap-1 bg-gray-200 p-1 rounded-2xl ">

                    <div class="w-full bg-white rounded-2xl p-4">

                        <div class="flex justify-center px-4 pt-4 pb-4">

                            <div class=" flex justify-between flex-row gap-2 mx-4">

                                <div class="">
                                {{-- xxxxx --}}
                                </div>
                                <div class=" ">

                                    <div class="flex items-center mb-4">
                                        <input wire:model="client_type" id="default-checkbox" type="radio"
                                            name="radion" value="ALL"
                                            class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500   focus:ring-2  ">
                                        <label for="default-checkbox"
                                            class="ml-2 text-sm font-medium text-gray-900 "> General Report </label>
                                    </div>

                                    {{-- <div class="flex items-center">
                                        <input wire:model="client_type" checked="" id="checked-checkbox"
                                            name="radion" type="radio" value="MULTIPLE"
                                            class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500   focus:ring-2  ">
                                        <label for="checked-checkbox"
                                            class="ml-2 text-sm font-medium text-gray-900 ">
                                           Custom Report
                                        </label>
                                    </div> --}}

                                </div>

                            </div>


                            <div class="flex justify-end w-auto">
                            </div>

                            <div class="flex justify-end w-auto">
                                <div wire:loading="" wire:target="exportReport">

                                    <button
                                        class="ml-2 inline-flex items-center py-2 px-4 text-sm font-medium text-center text-gray-900
                                                bg-white rounded-lg border border-gray-300 hover:bg-gray-100 focus:ring-4
                                                focus:outline-none focus:ring-gray-200
                                            ">
                                                                    <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="animate-spin  h-5 w-5 mr-2 stroke-white-800" fill="white"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                                                </path>
                                            </svg>
                                            <p>Please wait...</p>
                                        </div>
                                    </button>


                                </div>

                            </div>


                            <div class="flex justify-end w-auto">
                                <div wire:loading.remove="" wire:target="setValue">

                                    <button wire:click="exportReport" type="button"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700       ">
                                        <svg class="w-3 h-3 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M14.707 7.793a1 1 0 0 0-1.414 0L11 10.086V1.5a1 1 0 0 0-2 0v8.586L6.707 7.793a1 1 0 1 0-1.414 1.414l4 4a1 1 0 0 0 1.416 0l4-4a1 1 0 0 0-.002-1.414Z">
                                            </path>
                                            <path
                                                d="M18 12h-2.55l-2.975 2.975a3.5 3.5 0 0 1-4.95 0L4.55 12H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2Zm-3 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z">
                                            </path>
                                        </svg>
                                        Download Excel
                                    </button>

                                </div>
                            </div>


                        </div>

                    </div>

                </div>

            <!-- Livewire Component wire-end:oAt9hjTd12cL4GwIs4fp -->
        </div>

 </div>
