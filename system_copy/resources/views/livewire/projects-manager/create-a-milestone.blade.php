<div>
    <div class="bg-white">

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


        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="bg-gray-50 p-2 rounded-xl mb-4">


                @if($pageOnFocus == 1)
                <div class="bg-white p-4 rounded-xl">

                    <div class="text-lighter-shade">ENTER TASK DETAILS</div>
                    <div class="h-4"></div>
                    <div class=" flex w-full gap-4">
                        <div class="w-1/2 mb-4">
                            <div class="form-group col-span-6 sm:col-span-4 mt-4">

                                <div class="col-span-6 sm:col-span-4 mt-4">
                                    <x-jet-label for="tender_no" value="{{ __('Tender No') }}" />
                                    <x-jet-input id="tender_no" type="text" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 block w-full text-sm text-gray-900 bg-gray-50 rounded-2xl" wire:model.defer="tender_no" autocomplete="tender_no" />
                                    <x-jet-input-error for="tender_no" class="mt-2" />
                                </div>

                                <div class="col-span-6 sm:col-span-4 mt-4">
                                    <x-jet-label for="supplier_name" value="{{ __('Supplier Name') }}" />
                                    <x-jet-input id="supplier_name" type="text" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 block w-full text-sm text-gray-900 bg-gray-50 rounded-2xl" wire:model.defer="supplier_name" autocomplete="supplier_name" />
                                    <x-jet-input-error for="supplier_name" class="mt-2" />
                                </div>

                                <div class="col-span-6 sm:col-span-4 mt-4">
                                    <x-jet-label for="award_amount" value="{{ __('Award Amount') }}" />
                                    <x-jet-input id="award_amount" type="text" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 block w-full text-sm text-gray-900 bg-gray-50 rounded-2xl" wire:model.defer="award_amount" autocomplete="award_amount" />
                                    <x-jet-input-error for="award_amount" class="mt-2" />
                                </div>

                                <div class="col-span-6 sm:col-span-4 mt-4">
                                    <x-jet-label for="expected_end_date" value="{{ __('Expected End Date') }}" />
                                    <x-jet-input id="expected_end_date" type="text" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 block w-full text-sm text-gray-900 bg-gray-50 rounded-2xl" wire:model.defer="expected_end_date" autocomplete="expected_end_date" />
                                    <x-jet-input-error for="expected_end_date" class="mt-2" />
                                </div>

                            </div>                            

                        </div>  
                        
                        <div class="w-1/2">

                            <!-- Name -->
                            <div class="col-span-6 sm:col-span-4 mt-4">
                                <x-jet-label for="procuring_entity" value="{{ __('Procuring Entity') }}" />
                                <x-jet-input id="procuring_entity" type="text" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 block w-full text-sm text-gray-900 bg-gray-50 rounded-2xl" wire:model.defer="procuring_entity" autocomplete="procuring_entity" />
                                <x-jet-input-error for="procuring_entity" class="mt-2" />
                            </div>
                            <!-- Name -->
                            <div class="col-span-6 sm:col-span-4 mt-4">
                                <x-jet-label for="award_date" value="{{ __('Award Date') }}" />
                                <x-jet-input id="award_date" type="text" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 block w-full text-sm text-gray-900 bg-gray-50 rounded-2xl" wire:model.defer="award_date" autocomplete="award_date" />
                                <x-jet-input-error for="award_date" class="mt-2" />
                            </div>
                            <div class="col-span-6 sm:col-span-4 mt-4">
                                <x-jet-label for="lot_name" value="{{ __('Lot Name') }}" />
                                <x-jet-input id="lot_name" type="text" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 block w-full text-sm text-gray-900 bg-gray-50 rounded-2xl" wire:model.defer="lot_name" autocomplete="lot_name" />
                                <x-jet-input-error for="lot_name" class="mt-2" />
                            </div>
                            <div class="col-span-6 sm:col-span-4 mt-4">
                                <x-jet-label for="project_summary" value="{{ __('Project Summary') }}" />
                                <textarea wire:model.defer="project_summary" autocomplete="project_summary" id="project_summary" rows="5" class="mt-1 block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-2xl border border-gray-300
                             focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                             dark:focus:ring-green-500 dark:focus:border-green-500" placeholder="Write your thoughts here..."></textarea>


                                <x-jet-input-error for="project_summary" class="mt-2" />
                            </div>

                        </div>
                    </div>




               
                    <div class="w-full mt-16 flex items-center text-center justify-center ">
                        <button wire:click="viewChanger(2)" type="button" class="flex items-center justify-center w-1/3 p-4 lighter-shade text-white  hover:bg-green-800 focus:outline-none focus:ring-4
                        focus:ring-green-300 rounded-full text-lg font-semibold px-5 py-2.5 text-center mr-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700
                        dark:focus:ring-green-800" style="color: white">

                                Next
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                     class="ml-4 w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>


                        </button>
                    </div>

                </div>
                @endif

                    @if($pageOnFocus == 2)
                    
                    <div class="bg-white p-4 rounded-xl">

                    <div class="text-lighter-shade">ENTER MILESTONE DETAILS</div>
                    <div class="h-4"></div>
                    <div class=" flex w-full gap-4">
                    <div class="w-full mb-4">
                            <div class="form-group col-span-6 sm:col-span-4 mt-4">

                                <div class="col-span-6 sm:col-span-4 mt-4">
                                    <x-jet-label for="department" value="{{ __('Project') }}" />

                                    <select id="Project" wire:model="Project" class="text-sm text-gray-900 bg-gray-50 rounded-2xl block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:px-3 sm:text-sm h-10" required>
                                        <option >Select</option>
                                        @foreach($this->projectList as $Project)
                                            <option value="{{$Project->id}}">{{$Project->tender_no}}</option>

                                        @endforeach
                                    </select>
                                    @error('Project')
                                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                        <p>Please select a Project.</p>
                                    </div>
                                    @enderror
                                </div>
                                <div class="col-span-6 sm:col-span-4 mt-4">
                                    <x-jet-label for="milestone_name" value="{{ __('Milestone') }}" />
                                    <x-jet-input id="milestone_name" type="text" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 block w-full text-sm text-gray-900 bg-gray-50 rounded-2xl" wire:model.defer="milestone_name" autocomplete="milestone_name" />
                                    <x-jet-input-error for="milestone_name" class="mt-2" />
                                </div>
                                <div class="col-span-6 sm:col-span-4 mt-4">
                                    <x-jet-label for="start_date" value="{{ __('Start Date') }}" />
                                    <x-jet-input id="start_date" type="date" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 block w-full text-sm text-gray-900 bg-gray-50 rounded-2xl" wire:model.defer="start_date" autocomplete="start_date" />
                                    <x-jet-input-error for="start_date" class="mt-2" />
                                </div>
                                <div class="col-span-6 sm:col-span-4 mt-4">
                                    <x-jet-label for="end_date" value="{{ __('End Date') }}" />
                                    <x-jet-input id="end_date" type="date" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 block w-full text-sm text-gray-900 bg-gray-50 rounded-2xl" wire:model.defer="end_date" autocomplete="end_date" />
                                    <x-jet-input-error for="end_date" class="mt-2" />
                                </div>

                            </div>                            

                    </div>  
                        
                    </div>


                </div>
   









                       

                            <div class="w-full mt-16 flex items-center text-center justify-center ">
                                <!-- <button wire:click="viewChanger(1)" type="button" class="flex items-center justify-center w-1/3 p-4 lighter-shade text-white  hover:bg-green-800 focus:outline-none focus:ring-4
                        focus:ring-green-300 rounded-full text-lg font-semibold px-5 py-2.5 text-center mr-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700
                        dark:focus:ring-green-800" style="color: white">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-4 w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                    </svg>

                                    Back

                                </button> -->

                                <button wire:click="store" type="button" class="flex items-center justify-center w-1/3 p-4 lighter-shade text-white  hover:bg-blue-300 focus:outline-none focus:ring-4
                        focus:ring-green-300 rounded-full text-lg font-semibold px-5 py-2.5 text-center mr-2 mb-2 bg-blue-400 dark:hover:bg-green-700
                        dark:focus:ring-green-800" style="color: white">

                                    Submit

                                </button>
                            </div>



                    </div>




                </div>
                    @endif





            </div>
        </div>
    </div>
</div>
