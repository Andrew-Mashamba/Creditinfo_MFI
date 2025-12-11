<div>
    <div>
        {{-- Nothing in the world is as soft and yielding as water. --}}
        <div class=" p-4">
            <div class="flex items-center space-x-5" hidden>

                <svg xmlns="http://www.w3.org/2000/svg" wire:click="back()" class="cursor-pointer h-12 bg-slate-50 rounded-full stroke-blue-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                <div class="text-uppercase fw-bold">
                    Back
                </div>
            </div>
        </div>
        <div class="w-full flex">

            <div class="w-full">
                <!-- message container -->
                <div>
                    @if (session()->has('message'))

                        {{--                    @if (session('alert-class') == 'alert-success')--}}
                        <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
                            <div class="flex">
                                <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                                <div>
                                    <p class="font-bold">The process is completed</p>
                                    <p class="text-sm">{{ session('message') }} </p>
                                </div>
                            </div>
                        </div>
                        {{--                    @endif--}}
                    @endif
                </div>


                <div class="bg-gray-100 rounded px-6 rounded-lg shadow-sm  pt-4 pb-4 ">




                    <form wire:submit.prevent="save">


                        <div class="flex justify-center m-8">
                            <div class="max-w-2xl rounded-lg shadow-sm bg-gray-50">
                                <div class="m-4">

                                    <section class="bg-white-300 flex flex-col items-center justify-center">
                                        @if ($photo)
                                            <img class="object-fill w-2/9  max-h-20 " src="{{ $photo->temporaryUrl() }}">
                                        @endif
                                    </section>




                                    <div class="flex items-center justify-center w-full">



                                        <label class="flex flex-col w-full h-19 hover:bg-gray-100 hover:border-gray-300">
                                            <div class="flex flex-col items-center justify-center pt-7">

                                                <div wire:loading wire:target="photo" class="" >

                                                    <svg style="width: 50%; margin: 0 auto;" xmlns="http://www.w3.org/2000/svg" class="animate-spin  w-8 h-8 text-gray-400 group-hover:text-gray-600" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                                                    </svg>
                                                    <p class="pt-1 text-sm tracking-wider text-gray-400 group-hover:text-gray-600">Please wait...</p>

                                                </div>

                                                <div wire:loading.remove wire:target="photo" class="flex flex-col items-center justify-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400 group-hover:text-gray-600"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                    <p class="pt-1 text-sm tracking-wider text-gray-400 group-hover:text-gray-600">
                                                        Attach member image</p>

                                                </div>

                                            </div>
                                            <input type="file" class="opacity-0" wire:model="photo"/>
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>
                        @error('photo') <span class="error">{{ $message }}</span> @enderror
                    </form>
                    <div class="flex items-stretch">
                        <div class="w-1/2 mr-2" >
                            <p for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Branch</p>
                            <select wire:model="branch" name="branch" id="branch" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="" selected>Select</option>
                                @foreach(DB::table('branches')->get() as $branch)
                                    <option value="{{$branch->id}}">{{$branch->name}}</option>
                                @endforeach
                            </select>
                            @error('branch')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Branch is mandatory.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>

                            <p for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Department</p>
                            <select wire:model="department" name="branch" id="branch" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="" selected>Select</option>
                                @foreach(DB::table('departments')->get() as $branch)
                                    <option value="{{$branch->id}}">{{$branch->department_name}}</option>
                                @endforeach
                            </select>
                            @error('branch')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Branch is mandatory.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>
                            <p for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">First Name</p>
                            <x-jet-input id="first_name" type="text" name="first_name" class="mt-1 block w-full" wire:model.defer="first_name" autofocus />
                            @error('first_name')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>The first name is mandatory and should be more than three characters.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>
                            <p for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Job Title</p>
                            <x-jet-input id="job_title" type="text" name="job_title" class="mt-1 block w-full" wire:model.defer="job_title" autofocus />
                            @error('job_title')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>The first name is mandatory and should be more than three characters.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>

                            <x-jet-label for="middle_name" value="{{ __('Middle Name') }}" />
                            <x-jet-input id="middle_name" name="middle_name" type="text" class="mt-1 block w-full" wire:model.defer="middle_name" autofocus />
                            @error('middle_name')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>The middle name is mandatory and should be more than three characters.</p>
                            </div>
                            @enderror

                            <div class="mt-2"></div>
                            <x-jet-label for="last_name" value="{{ __('Last Name') }}" />
                            <x-jet-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" wire:model.defer="last_name" autofocus />
                            @error('last_name')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>The last name is mandatory and should be more than three characters.</p>
                            </div>
                            @enderror

                            <div class="mt-2"></div>
                            <x-jet-label for="address" value="{{ __('Address') }}" />
                            <x-jet-input id="address" name="address" type="text" class="mt-1 block w-full" wire:model.defer="address" autofocus />
                            @error('address')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Address is mandatory, it should be more than three characters and unique.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>

                            <x-jet-label for="street" value="{{ __('Street') }}" />
                            <x-jet-input id="street" name="street" type="text" class="mt-1 block w-full" wire:model.defer="street" autofocus />
                            @error('street')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Street is mandatory, it should be more than three characters and unique.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>



                            <x-jet-label for="next_of_kin_name" value="{{ __('Next of kin full name') }}" />
                            <x-jet-input id="next_of_kin_name" type="text" name="next_of_kin_name" class="mt-1 w-full" wire:model.defer="next_of_kin_name" autofocus />
                            @error('next_of_kin_name')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Date of birth is mandatory.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>

                            <x-jet-label for="next_of_kin_phone" value="{{ __('Next of kin phone number') }}" />
                            <x-jet-input id="next_of_kin_phone" type="text" name="next_of_kin_phone" class="mt-1 w-full" wire:model.defer="next_of_kin_phone" autofocus />
                            @error('next_of_kin_phone')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Date of birth is mandatory.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>
                        </div>
                        <div class="w-1/2 ml-2" >

                            <x-jet-label for="gender" value="{{ __('Gender') }}" />
                            <select wire:model.bounce="gender" name="gender" id="gender" class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option selected value="">Select</option>
                                <option  value="Male">Male</option>
                                <option  value="Female">Female</option>
                                <option  value="Other">Other</option>

                            </select>
                            @error('gender')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Gender is mandatory.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>

                            <x-jet-label for="marital_status" value="{{ __('Marital status') }}" />
                            <select wire:model.bounce="marital_status" name="marital_status" id="marital_status" class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option selected value="">Select</option>
                                <option  value="Married">Married</option>
                                <option  value="Single">Single</option>
                                <option  value="Divorced">Divorced</option>
                                <option  value="Widow">Widow</option>

                            </select>
                            @error('marital_status')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Marital status is mandatory.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>

                            <x-jet-label for="date_of_birth" value="{{ __('Date of birth') }}" />
                            <x-jet-input id="date_of_birth" type="date" name="date_of_birth" class="mt-1 w-full" wire:model.defer="date_of_birth" autofocus />
                            @error('date_of_birth')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Date of birth is mandatory.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>

                            <x-jet-label for="place_of_birth" value="{{ __('Place of birth') }}" />
                            <x-jet-input id="place_of_birth" type="text" name="place_of_birth" class="mt-1 w-full" wire:model.defer="place_of_birth" autofocus />
                            @error('place_of_birth')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Place of birth is mandatory.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>

                            <x-jet-label for="tin_number" value="{{ __('TIN Number') }}" />
                            <x-jet-input id="tin_number" type="text" name="tin_number" class="mt-1 w-full" wire:model.defer="tin_number" autofocus />
                            @error('tin_number')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>TIN Number is mandatory.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>

                            <x-jet-label for="nida_number" value="{{ __('NIDA Number') }}" />
                            <x-jet-input id="nida_number" type="text" name="nida_number" class="mt-1 w-full" wire:model.defer="nida_number" autofocus />
                            @error('nida_number')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>NIDA Number is mandatory.</p>
                            </div>
                            @enderror




                            <div class="mt-2"></div>


                            <x-jet-label for="phone_number" value="{{ __('Phone number') }}" />
                            <x-jet-input id="phone_number" type="text" name="phone_number" class="mt-1 w-full" wire:model.defer="phone_number" autofocus />
                            @error('phone_number')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Phone number is mandatory and should be more than three characters.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>
                            <x-jet-label for="notes" value="{{ __('Notes') }}" />
                            <textarea id="notes" name="notes" wire:model.defer="notes" rows="5" class="mt-1 block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Your notes..."></textarea>
                            @error('notes')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Notes is mandatory, it should be more than three characters and unique.</p>
                            </div>
                            @enderror
                            <div class="mt-2"></div>
                        </div>
                    </div>
                    <div class="flex justify-end w-auto" >
                        <div wire:loading wire:target="save" >
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


                    <div class="flex justify-end w-auto" >
                        <div wire:loading.remove wire:target="edit" >
                            <button wire:click="edit" class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400">
                                <p class="text-white">Proceed</p>
                            </button>

                        </div>
                    </div>

                </div>


            </div>

        </div>


    </div>


</div>
