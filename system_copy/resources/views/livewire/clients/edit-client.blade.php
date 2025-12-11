<div>
    <div class="h-16 border flex justify-between items-center w-full px-5 py-2 shadow-sm mb-4">

        <div class="flex items-center space-x-5" hidden>

            <svg xmlns="http://www.w3.org/2000/svg" wire:click="back" class="cursor-pointer h-12 bg-slate-50 rounded-full stroke-blue-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </div>
        <div wire:loading wire:target="back" >

            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-white-800" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                </svg>
                <p>Please wait...</p>
            </div>

        </div>

        <div wire:loading.remove wire:target="back" >
            <p class="font-semibold ml-3 text-slate-600">Edit user : {{Session::get('memberToEditName')}}</p>

        </div>

    </div>

    <div class="lg:w-3/3">
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


        <div class="bg-gray-100 rounded rounded-lg shadow-sm ">


            <div class="">
                <div class="bg-gray-50 overflow-hidden shadow-xl sm:rounded-lg">

                    <div>
                        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updateImage">
                                    <x-slot name="title">
                                        {{ __('Client Image') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Client image for visual identification.') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <div class="col-span-6 sm:col-span-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ __('Current member image.') }}
                                            </h3>

                                            <div class="mt-3 max-w-xl text-sm text-gray-600">
                                                <p>
                                                    {{ __('This is an official member image for visual identification.') }}
                                                </p>
                                            </div>
                                        </div>
                                        <br>


                                        <div class="col-span-12 sm:col-span-12">


                                                <div class="flex justify-center" >
                                                    <div class="rounded-lg shadow-lg bg-gray-50" >
                                                        <div class="flex" >

                                                            <section class=" w-1/2 bg-white-300 flex flex-col items-center justify-center">
                                                                @if ($photo)
                                                                    <img class="object-fill w-5/5 rounded-l-lg" src="{{ $photo->temporaryUrl() }}">
                                                                @else

                                                                    <img class="object-fill w-5/5 rounded-l-lg" src="http://zima.services/pamojacbs/storage/app/photos/{{$this->profile_photo_path}}">
                                                                @endif
                                                            </section>




                                                            <div class="  w-1/2  flex items-center justify-center hover:bg-gray-100 hover:border-gray-300">



                                                                <label class="flex flex-col w-full h-19 cursor-pointer">
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
                                                                                Select new image</p>

                                                                        </div>

                                                                    </div>



                                                                    <input type="file" class="opacity-0" wire:model="photo"/>




                                                                </label>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>



                                                @error('photo') <span class="error">{{ $message }}</span> @enderror


                                        </div>
                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>


                            <x-jet-section-border />





                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updateNames">
                                    <x-slot name="title">
                                        {{ __('Names of the member') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Update names of the member') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <!-- Name -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="first_name" value="{{ __('First Name') }}" />
                                            <x-jet-input id="first_name" type="text" class="mt-1 block w-full" wire:model.defer="first_name" autocomplete="first_name" />

                                            <x-jet-input-error for="first_name" class="mt-2" />
                                        </div>

                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="middle_name" value="{{ __('Middle Name') }}" />
                                            <x-jet-input id="middle_name" type="text" class="mt-1 block w-full" wire:model.defer="middle_name" autocomplete="middle_name" />
                                            <x-jet-input-error for="middle_name" class="mt-2" />
                                        </div>

                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="last_name" value="{{ __('Last Name') }}" />
                                            <x-jet-input id="last_name" type="text" class="mt-1 block w-full" wire:model.defer="last_name" autocomplete="last_name" />
                                            <x-jet-input-error for="last_name" class="mt-2" />
                                        </div>


                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>
                            <x-jet-section-border />



                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updateIncorporationNumber">
                                    <x-slot name="title">
                                        {{ __('Incorporation Number') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Edit the incorporation number of the member.') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <div class="col-span-6 sm:col-span-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ __('Current incorporation number.') }}
                                            </h3>


                                        </div>
                                        <br>

                                        <!-- Email -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="incorporation_number" value="{{ __('Incorporation Number') }}" />
                                            <x-jet-input id="incorporation_number" type="text" class="mt-1 block w-full" wire:model.defer="incorporation_number" />
                                            <x-jet-input-error for="incorporation_number" class="mt-2" />
                                        </div>
                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>


                            <x-jet-section-border />






                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updateBusinessName">
                                    <x-slot name="title">
                                        {{ __('Business Name') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Edit the business name for his business.') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <div class="col-span-6 sm:col-span-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ __('Current business name.') }}
                                            </h3>


                                        </div>
                                        <br>

                                        <!-- Email -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="business_name" value="{{ __('Business Name') }}" />

                                            <x-jet-input id="business_name" name="business_name" type="text" class="mt-1 block w-full" wire:model.defer="business_name" />
                                            @error('business_name')
                                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                                <p>Business Name is mandatory, it should be more than three characters.</p>
                                            </div>
                                            @enderror
                                        </div>
                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>


                            <x-jet-section-border />










                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updatePhoneNumber">
                                    <x-slot name="title">
                                        {{ __('Office Phone Number') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Edit the office phone number of the member.') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <div class="col-span-6 sm:col-span-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ __('Current office phone number .') }}
                                            </h3>


                                        </div>
                                        <br>

                                        <!-- Email -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="phone_number" value="{{ __('Incorporation Number') }}" />
                                            <x-jet-input id="phone_number" type="text" class="mt-1 block w-full bg-gray-100 text-gray-500 cursor-not-allowed" wire:model.defer="phone_number" disabled />
                                            <x-jet-input-error for="phone_number" class="mt-2" />
                                        </div>
                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>


                            <x-jet-section-border />


                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updateMobilePhoneNumber">
                                    <x-slot name="title">
                                        {{ __('Mobile Phone Number') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Edit the mobile phone number of the member.') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <div class="col-span-6 sm:col-span-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ __('Current mobile phone number .') }}
                                            </h3>
                                            <div class="mt-3 max-w-xl text-sm text-gray-600">
                                                <p>
                                                    {{ __('The system sends user activity reports, system activity reports, payment and disbursement reports to this member mobile phone number.') }}
                                                </p>
                                            </div>

                                        </div>
                                        <br>

                                        <!-- Email -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="mobile_phone_number" value="{{ __('Mobile Phone Number') }}" />
                                            <x-jet-input id="mobile_phone_number" type="text" class="mt-1 block w-full bg-gray-100 text-gray-500 cursor-not-allowed" wire:model.defer="mobile_phone_number" disabled />
                                            <x-jet-input-error for="mobile_phone_number" class="mt-2" />
                                        </div>
                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>


                            <x-jet-section-border />

                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updateEmail">
                                    <x-slot name="title">
                                        {{ __('Email') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Update member default email.') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <div class="col-span-6 sm:col-span-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ __('Current default email.') }}
                                            </h3>

                                            <div class="mt-3 max-w-xl text-sm text-gray-600">
                                                <p>
                                                    {{ __('The system sends user activity reports, system activity reports, payment and disbursement reports to this member default email.') }}
                                                </p>
                                            </div>
                                        </div>
                                        <br>

                                        <!-- Email -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="email" value="{{ __('Email') }}" />
                                            <x-jet-input id="email" type="email" class="mt-1 block w-full bg-gray-100 text-gray-500 cursor-not-allowed" wire:model.defer="email" disabled />
                                            <x-jet-input-error for="email" class="mt-2" />
                                        </div>
                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>


                            <x-jet-section-border />







                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updateDateOfBirth">
                                    <x-slot name="title">
                                        {{ __('Date Of Birth') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Edit the date of birth of the member.') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <div class="col-span-6 sm:col-span-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ __('Current date of birth  .') }}
                                            </h3>


                                        </div>
                                        <br>

                                        <!-- Email -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="date_of_birth" value="{{ __('Date Of Birth') }}" />
                                            <x-jet-input id="date_of_birth" type="date" class="mt-1 block w-full" wire:model.defer="date_of_birth" />
                                            <x-jet-input-error for="date_of_birth" class="mt-2" />
                                        </div>
                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>


                            <x-jet-section-border />




                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updateGender">
                                    <x-slot name="title">
                                        {{ __('Gender') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Edit the gender of the member.') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <div class="col-span-6 sm:col-span-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ __('Current gender .') }}
                                            </h3>


                                        </div>
                                        <br>

                                        <!-- Email -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="gender" value="{{ __('Gender') }}" />
                                            <select wire:model.defer="gender" name="gender" id="gender" class="mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Other">Other</option>

                                            </select>
                                            @error('gender')
                                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                                <p>Gender is mandatory.</p>
                                            </div>
                                            @enderror

                                        </div>
                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>


                            <x-jet-section-border />



                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updateMaritalStatus">
                                    <x-slot name="title">
                                        {{ __('Marital Status') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Edit marital status of the member.') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <div class="col-span-6 sm:col-span-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ __('Current marital status.') }}
                                            </h3>


                                        </div>
                                        <br>

                                        <!-- Email -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="marital_status" value="{{ __('Marital Status') }}" />
                                            <select wire:model.defer="marital_status" name="marital_status" id="marital_status" class="mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

                                                <option  value="Married">Married</option>
                                                <option  value="Single">Single</option>
                                                <option  value="Divorced">Divorced</option>
                                                <option  value="Widow">Widow</option>

                                            </select>
                                            @error('marital_status')
                                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                                <p>Marital Status is mandatory.</p>
                                            </div>
                                            @enderror

                                        </div>
                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>


                            <x-jet-section-border />



                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updateBranch">
                                    <x-slot name="title">
                                        {{ __('Branch Transfers') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Change the branch of the member.') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <div class="col-span-6 sm:col-span-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ __('Current branch.') }}
                                            </h3>

                                            <div class="mt-3 max-w-xl text-sm text-gray-600">
                                                <p>
                                                    {{ __('By changing the branch of the member, you are transferring the member from the previous branch to newly selected branch. A new Account will be created for this member .') }}
                                                </p>
                                            </div>
                                        </div>
                                        <br>

                                        <!-- Email -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <label for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Branch</label>
                                            <select wire:model.bounce="branch" name="branch" id="branch" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

                                                @foreach(App\Models\Branches::all() as $branch)
                                                    <option value="{{$branch->id}}">{{$branch->name}}</option>
                                                @endforeach

                                            </select>
                                            @error('branch')
                                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                                <p>Branch is mandatory.</p>
                                            </div>
                                            @enderror

                                        </div>
                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>


                            <x-jet-section-border />




                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updateClientshipType">
                                    <x-slot name="title">
                                        {{ __('Clientship Type') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Change the membership type of the member.') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <div class="col-span-6 sm:col-span-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ __('Current membership.') }}
                                            </h3>

                                            <div class="mt-3 max-w-xl text-sm text-gray-600">
                                                <p>
                                                    {{ __('By changing membership type, the system will prompt you add or remove items from the member record when required.') }}
                                                </p>
                                            </div>
                                        </div>
                                        <br>

                                        <!-- Email -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <label for="membership_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Clientship Type</label>
                                            <select wire:model.bounce="membership_type" name="membership_type" id="membership_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

                                                <option value="Individual">Individual</option>
                                                <option value="Group">Group</option>
                                                <option value="Business">Business</option>


                                            </select>
                                            @error('membership_type')
                                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                                <p>Clientship Type is mandatory.</p>
                                            </div>
                                            @enderror

                                        </div>
                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>


                            <x-jet-section-border />





                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updateStreet">
                                    <x-slot name="title">
                                        {{ __('Residential Street') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Edit the residential street of the member.') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <div class="col-span-6 sm:col-span-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ __('Current residential street.') }}
                                            </h3>


                                        </div>
                                        <br>

                                        <!-- Email -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="street" value="{{ __('Street') }}" />
                                            <x-jet-input id="street" name="street" type="text" class="mt-1 block w-full" wire:model.defer="street" />
                                            @error('street')
                                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                                <p>Street is mandatory, it should be more than three characters and unique.</p>
                                            </div>
                                            @enderror
                                        </div>
                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>


                            <x-jet-section-border />






                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updateAddress">
                                    <x-slot name="title">
                                        {{ __('Residential Address') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Edit the residential address of the member.') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <div class="col-span-6 sm:col-span-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ __('Current residential address.') }}
                                            </h3>


                                        </div>
                                        <br>

                                        <!-- Email -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="address" value="{{ __('Residential Address') }}" />
                                            <x-jet-input id="address" name="address" type="text" class="mt-1 block w-full" wire:model.defer="address" />
                                            @error('address')
                                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                                <p>Address is mandatory, it should be more than three characters.</p>
                                            </div>
                                            @enderror
                                        </div>
                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>


                            <x-jet-section-border />




                            <div class="mt-10 sm:mt-0">
                                <x-jet-form-section submit="updateNotes">
                                    <x-slot name="title">
                                        {{ __('Notes') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Add/Edit notes for this member.') }}
                                    </x-slot>

                                    <x-slot name="form">

                                        <div class="col-span-6 sm:col-span-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ __('Available notes.') }}
                                            </h3>


                                        </div>
                                        <br>

                                        <!-- Email -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="notes" value="{{ __('Notes') }}" />
                                            <textarea id="notes" name="notes" wire:model.defer="notes" rows="4" class="mt-1 block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Your notes..."></textarea>
                                            @error('notes')
                                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                                <p>Notes is mandatory, it should be more than three characters.</p>
                                            </div>
                                            @enderror
                                        </div>
                                    </x-slot>

                                    <x-slot name="actions">
                                        <x-jet-action-message class="mr-3" on="saved">
                                            {{ __('Saved.') }}
                                        </x-jet-action-message>

                                        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                                            {{ __('Save') }}
                                        </x-jet-button>
                                    </x-slot>
                                </x-jet-form-section>
                            </div>


                            <x-jet-section-border />









                            <div class="mt-10 sm:mt-0">
                                <x-jet-action-section>
                                    <x-slot name="title">
                                        {{ __('Suspend a member') }}
                                    </x-slot>

                                    <x-slot name="description">
                                        {{ __('Suspend this member.') }}
                                    </x-slot>

                                    <x-slot name="content">
                                        <div class="max-w-xl text-sm text-gray-600">
                                            {{ __('Once this member is suspend, all of his/her/its resources and data will continue to available upon request. Automatic payments and manual payments services, will not be available for this member') }}
                                        </div>

                                        <div class="mt-5">
                                            <x-jet-danger-button wire:click="confirmUserDeletion" wire:loading.attr="disabled">
                                                {{ __('Suspend This Client') }}
                                            </x-jet-danger-button>
                                        </div>

                                        <!-- Delete User Confirmation Modal -->
                                        <x-jet-dialog-modal wire:model="confirmingUserDeletion">
                                            <x-slot name="title">
                                                {{ __('Suspend This Client') }}
                                            </x-slot>

                                            <x-slot name="content">
                                                {{ __('Are you sure you want to suspend this member? Please enter your password to confirm.') }}

                                                <div class="mt-4" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                                                    <x-jet-input type="password" class="mt-1 block w-3/4"
                                                                 placeholder="{{ __('Password') }}"
                                                                 x-ref="password"
                                                                 wire:model.defer="password"
                                                                 wire:keydown.enter="suspendClient" />

                                                    <x-jet-input-error for="password" class="mt-2" />
                                                </div>
                                            </x-slot>

                                            <x-slot name="footer">
                                                <x-jet-secondary-button wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled">
                                                    {{ __('Cancel') }}
                                                </x-jet-secondary-button>

                                                <x-jet-danger-button class="ml-3" wire:click="suspendClient" wire:loading.attr="disabled">
                                                    {{ __('Suspend This Client') }}
                                                </x-jet-danger-button>
                                            </x-slot>
                                        </x-jet-dialog-modal>
                                    </x-slot>
                                </x-jet-action-section>
                            </div>


                        </div>
                    </div>

                </div>
            </div>

        </div>


    </div>
</div>

