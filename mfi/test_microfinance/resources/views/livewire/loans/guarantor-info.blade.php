<div class="w-full p-2" >


    <style nonce="{{ csp_nonce() }}">
        .gridx {
            display: grid;
            grid-gap: 0.5rem;
            grid-template-columns: repeat(12, 1fr);
        }
        .gridx a img {
            transition: all .2s ease-in;
            filter: grayscale(10%);
        }
        .gridx a:hover img {
            filter: grayscale(0);
        }
        @media screen and (min-width: 768px) {
            .gridx {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media screen and (min-width: 992px) {
            .gridx {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>


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







        <div class="flex gap-2 pt-2 pl-2 pr-2 pb-2">

            <div class="w-1/4 bg-white rounded px-6 rounded-lg shadow-sm   pt-4 pb-4 " >

                <button
                    wire:click="setView('a')"
                    class="flex hover:text-white text-center items-center w-full mb-2
                        @if ($this->tab_id == 'a') bg-customPurple @else bg-gray-100 @endif
                            @if ($this->tab_id == 'a') text-white font-bold @else text-gray-400 font-semibold @endif
                                    py-2 px-4 rounded-lg"

                    onmouseover="this.style.backgroundColor='#844494'; this.style.color='#333333';"
                    onmouseout="this.style.backgroundColor=''; this.style.color='';"
                >

                    <div wire:loading wire:target="setView('a')">
                        <svg aria-hidden="true" class="w-8 h-8 mr-2 text-gray-200 animate-spin dark:text-gray-900 fill-green-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                        </svg>
                    </div>
                    <div wire:loading.remove wire:target="setView('a')">

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="green"
                             class="w-4 h-4 mr-2 fill-current">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008p.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008p.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008p.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>

                    </div>

                    Add New Guarantor
                </button>

                @foreach (\Illuminate\Support\Facades\DB::table('collaterals')->where('loan_id', session('currentloanID'))->get() as $menuItem)
                    <button
                        wire:click="setView({{ $menuItem->id }})"
                        class="flex hover:text-white text-center items-center w-full mb-2
                        @if ($this->tab_id == $menuItem->id) bg-customPurple @else bg-gray-100 @endif
                            @if ($this->tab_id == $menuItem->id) text-white font-bold @else text-gray-400 font-semibold @endif
                                    py-2 px-4 rounded-lg"

                        onmouseover="this.style.backgroundColor='#844494'; this.style.color='#333333';"
                        onmouseout="this.style.backgroundColor=''; this.style.color='';"
                    >

                        <div wire:loading wire:target="setView({{ $menuItem->id}})">
                            <svg aria-hidden="true" class="w-8 h-8 mr-2 text-gray-200 animate-spin dark:text-gray-900 fill-green-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                            </svg>
                        </div>
                        <div wire:loading.remove wire:target="setView({{ $menuItem->id }})">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="green"
                                 class="w-4 h-4 mr-2 fill-current">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008p.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008p.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008p.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>

                        </div>

                        {{ $menuItem->collateral_type ?? 'Collateral ' . $menuItem->id }}
                    </button>
                @endforeach


            </div>


            <div class="w-3/4 bg-white rounded px-6 rounded-lg shadow-sm  pt-4 pb-4  " >


                @if($this->tab_id == 'a')
                <div class="justify-center pt-4">

                    <select wire:model="guaranteeType" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                        <option value="">Select type of guarantee</option>
                        <option value="self_guarantee">Self Guarantee</option>
                        <option value="third_party_guarantee">Third Party Guarantee</option>
                        <option value="salary">Salary</option>
                        <option value="savings">Savings</option>
                        <option value="deposits">Deposits</option>
                    </select>

                @if($this->guaranteeType == 'third_party_guarantee')

                <section class=" w-full bg-white-300 flex flex-col items-center justify-center">
                        @if ($this->photo)
                            <img class="object-fill w-5/5 rounded-lg" src="{{ $photo->temporaryUrl() }}" height="150px" width="150px">
                        @else
                            @if ($this->profile_photo_path)
                                <img class="object-fill w-5/5 rounded-lg" src="{{$this->profile_photo_path}}" height="150px" width="150px">
                            @else

                            @endif

                        @endif
                    </section>



                <div class="  w-full  flex items-center justify-center mt-4">

                        <label class="flex flex-col w-1/4 h-19 cursor-pointer hover:bg-gray-100 hover:border-gray-300 rounded-full p-4">
                            <div class="flex flex-col items-center justify-center pt-7">

                                <div wire:loading wire:target="photo" class="" >

                                    <svg style="width: 50%; margin: 0 auto;" xmlns="http://www.w3.org/2000/svg" class="animate-spin  w-8 h-8 text-gray-400 group-hover:text-gray-600" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12pm15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

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
                            <input type="file" class="opacity-0" wire:model="photo" accept=".png, .jpeg, .jpg" />

                        </label>
                </div>
                @error('photo') <span class="error">{{ $message }}</span> @enderror

                <x-jet-section-border />

                <div class="w-full pt-4 pb-4">
                    <form wire:submit.prevent="submit">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6">
                            <!-- Column 1 -->
                            <div class="mb-4">
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Name</label>
                                <input type="text" id="name" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" wire:model.lazy="name" @disabled(Session::get('disableInputs')) required>
                                @error('name') <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="dob" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Date of Birth</label>
                                <input type="date" id="dob" name="dob" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" wire:model.lazy="dob" @disabled(Session::get('disableInputs')) required>
                                @error('dob') <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="nationality" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Nationality</label>
                                <input type="text" id="nationality" name="nationality" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" wire:model.lazy="nationality" @disabled(Session::get('disableInputs')) required>
                                @error('nationality') <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- Column 2 -->
                            <div class="mb-4">
                                <label for="address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Residential Address</label>
                                <input type="text" id="address" name="address" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" wire:model.lazy="address" @disabled(Session::get('disableInputs')) required>
                                @error('address') <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Phone</label>
                                <input type="text" id="phone" name="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" wire:model.lazy="phone" @disabled(Session::get('disableInputs')) required>
                                @error('phone') <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Email</label>
                                <input type="email" id="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" wire:model.lazy="email" @disabled(Session::get('disableInputs')) required>
                                @error('email') <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- Column 3 -->
                            <div class="mb-4">
                                <label for="id_number" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">ID Number</label>
                                <input type="text" id="id_number" name="id_number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" wire:model.lazy="id_number" @disabled(Session::get('disableInputs')) required>
                                @error('id_number') <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">{{ $message }}</div> @enderror
                            </div>

                        </div>



                    </form>
                </div>

                @endif

                @if($this->guaranteeType == 'self_guarantee')

                @endif

                <x-jet-section-border />

                <div class="w-full pt-4 pb-4">

                            <div class="mb-4">
                                @if($collaterals)
                                @foreach ($collaterals as $index => $collateral)
                                    <div class="mb-4">
                                        <!-- Select type of collateral -->
                                        <select wire:model="collaterals.{{ $index }}.type" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                            <option value="">Select type of collateral </option>
                                            <option value="title_deed">Title Deed</option>
                                            <option value="sales_agreement">Sales Agreement</option>
                                            <option value="motor_vehicle">Motor Vehicle</option>
                                            <option value="chattels">Chattels</option>
                                        </select>


                                        <!-- Depending on the type selected, show relevant fields -->
                                        @if (isset($collateral['type']) && ($collateral['type'] == 'title_deed' || $collateral['type'] == 'sales_agreement'))
                                            <div class="rounded-lg shadow-sm bg-gray-50 p-4">
                                                <div class="w-full flex justify-end">
                                                    <!-- Button to remove collateral -->

                                                    <button type="button" class="rounded-full bg-white p-1 space-x-2 text-gray-400 hover:text-red-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                                                        <svg wire:click="removeCollateral({{ $index }})" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 " fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6">


                                                                        <div class="px-4">
                                                                            <div class="relative mb-2">
                                                                                <label class="text-sm font-medium mb-2" for="type_of_owner_{{ $index }}">
                                                                                    Type of Owner
                                                                                </label>
                                                                                <select id="type_of_owner_{{ $index }}" wire:model="collaterals.{{ $index }}.type_of_owner"
                                                                                        class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                                    <option value="">Select Owner Type</option>
                                                                                    <option value="Individual">Individual</option>
                                                                                    <option value="Company">Company</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>


                                                                    <!-- Individual Owner Fields -->
                                                                    @if (isset($collateral['type_of_owner']) && $collateral['type_of_owner'] == 'Individual')

                                                                            <div class="w-full lg:w-6/12 px-4">
                                                                                <div class="relative w-full mb-2">
                                                                                    <label class="text-sm font-medium mb-2" for="collateral_owner_full_name_{{ $index }}">
                                                                                        Collateral Owner Full Name
                                                                                    </label>
                                                                                    <input id="collateral_owner_full_name_{{ $index }}" wire:model="collaterals.{{ $index }}.collateral_owner_full_name" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                                </div>
                                                                            </div>
                                                                            <div class="w-full lg:w-6/12 px-4">
                                                                                <div class="relative w-full mb-2">
                                                                                    <label class="text-sm font-medium mb-2" for="collateral_owner_nida_{{ $index }}">
                                                                                        Collateral Owner NIDA
                                                                                    </label>
                                                                                    <input id="collateral_owner_nida_{{ $index }}" wire:model="collaterals.{{ $index }}.collateral_owner_nida" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                                </div>
                                                                            </div>
                                                                            <div class="w-full lg:w-6/12 px-4">
                                                                                <div class="relative w-full mb-2">
                                                                                    <label class="text-sm font-medium mb-2" for="collateral_owner_contact_number_{{ $index }}">
                                                                                        Collateral Owner Contact Number
                                                                                    </label>
                                                                                    <input id="collateral_owner_contact_number_{{ $index }}" wire:model="collaterals.{{ $index }}.collateral_owner_contact_number" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                                </div>
                                                                            </div>
                                                                            <div class="w-full lg:w-6/12 px-4">
                                                                                <div class="relative w-full mb-2">
                                                                                    <label class="text-sm font-medium mb-2" for="collateral_owner_residential_address_{{ $index }}">
                                                                                        Collateral Owner Residential Address
                                                                                    </label>
                                                                                    <input id="collateral_owner_residential_address_{{ $index }}" wire:model="collaterals.{{ $index }}.collateral_owner_residential_address" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                                </div>
                                                                            </div>
                                                                            <div class="w-full lg:w-6/12 px-4">
                                                                                <div class="relative w-full mb-2">
                                                                                    <label class="text-sm font-medium mb-2" for="collateral_owner_spouse_full_name_{{ $index }}">
                                                                                        Collateral Owner Spouse Full Name
                                                                                    </label>
                                                                                    <input id="collateral_owner_spouse_full_name_{{ $index }}" wire:model="collaterals.{{ $index }}.collateral_owner_spouse_full_name" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                                </div>
                                                                            </div>
                                                                            <div class="w-full lg:w-6/12 px-4">
                                                                                <div class="relative w-full mb-2">
                                                                                    <label class="text-sm font-medium mb-2" for="collateral_owner_spouse_nida_{{ $index }}">
                                                                                        Collateral Owner Spouse NIDA
                                                                                    </label>
                                                                                    <input id="collateral_owner_spouse_nida_{{ $index }}" wire:model="collaterals.{{ $index }}.collateral_owner_spouse_nida" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                                </div>
                                                                            </div>
                                                                            <div class="w-full lg:w-6/12 px-4">
                                                                                <div class="relative w-full mb-2">
                                                                                    <label class="text-sm font-medium mb-2" for="collateral_owner_spouse_contact_number_{{ $index }}">
                                                                                        Collateral Owner Spouse Contact Number
                                                                                    </label>
                                                                                    <input id="collateral_owner_spouse_contact_number_{{ $index }}" wire:model="collaterals.{{ $index }}.collateral_owner_spouse_contact_number" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                                </div>
                                                                            </div>
                                                                            <div class="w-full lg:w-6/12 px-4">
                                                                                <div class="relative w-full mb-2">
                                                                                    <label class="text-sm font-medium mb-2" for="collateral_owner_spouse_residential_address_{{ $index }}">
                                                                                        Collateral Owner Spouse Residential Address
                                                                                    </label>
                                                                                    <input id="collateral_owner_spouse_residential_address_{{ $index }}" wire:model="collaterals.{{ $index }}.collateral_owner_spouse_residential_address" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                                </div>
                                                                            </div>

                                                                    @endif

                                                                    <!-- Company Owner Fields -->
                                                                    @if (isset($collateral['type_of_owner']) && $collateral['type_of_owner'] == 'Company')

                                                                            <div class="w-full lg:w-6/12 px-4">
                                                                                <div class="relative w-full mb-2">
                                                                                    <label class="text-sm font-medium mb-2" for="company_registered_name_{{ $index }}">
                                                                                        Company Registered Name
                                                                                    </label>
                                                                                    <input id="company_registered_name_{{ $index }}" wire:model="collaterals.{{ $index }}.company_registered_name" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                                </div>
                                                                            </div>
                                                                            <div class="w-full lg:w-6/12 px-4">
                                                                                <div class="relative w-full mb-2">
                                                                                    <label class="text-sm font-medium mb-2" for="business_licence_number_{{ $index }}">
                                                                                        Business Licence Number
                                                                                    </label>
                                                                                    <input id="business_licence_number_{{ $index }}" wire:model="collaterals.{{ $index }}.business_licence_number" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                                </div>
                                                                            </div>
                                                                            <div class="w-full lg:w-6/12 px-4">
                                                                                <div class="relative w-full mb-2">
                                                                                    <label class="text-sm font-medium mb-2" for="TIN_{{ $index }}">
                                                                                        Tax Identification Number (TIN) Certificate
                                                                                    </label>
                                                                                    <input id="TIN_{{ $index }}" wire:model="collaterals.{{ $index }}.TIN" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                                </div>
                                                                            </div>
                                                                            <div class="w-full lg:w-6/12 px-4">
                                                                                <div class="relative w-full mb-2">
                                                                                    <label class="text-sm font-medium mb-2" for="company_contact_number_{{ $index }}">
                                                                                        Company Contact Number
                                                                                    </label>
                                                                                    <input id="company_contact_number_{{ $index }}" wire:model="collaterals.{{ $index }}.company_contact_number" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                                </div>
                                                                            </div>
                                                                            <div class="w-full lg:w-6/12 px-4">
                                                                                <div class="relative w-full mb-2">
                                                                                    <label class="text-sm font-medium mb-2" for="company_address_{{ $index }}">
                                                                                        Company Address
                                                                                    </label>
                                                                                    <input id="company_address_{{ $index }}" wire:model="collaterals.{{ $index }}.company_address" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                                </div>
                                                                            </div>

                                                                    @endif

                                                                        <div class="px-4">
                                                                            <div class="relative w-full mb-2">
                                                                                <label class="text-sm font-medium mb-2" for="collateral_value_{{ $index }}">
                                                                                    Current market value of the collateral
                                                                                </label>
                                                                                <input id="collateral_value_{{ $index }}" wire:model="collaterals.{{ $index }}.collateral_value" type="number" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                            </div>
                                                                        </div>

                                                                        <div class="px-4">
                                                                            <div class="relative w-full mb-2">
                                                                                <label class="text-sm font-medium mb-2" for="policy_number_{{ $index }}">
                                                                                    Insurance Policy Number
                                                                                </label>
                                                                                <input id="policy_number_{{ $index }}" wire:model="collaterals.{{ $index }}.policy_number" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                            </div>
                                                                        </div>
                                                                        <div class="px-4">
                                                                            <div class="relative w-full mb-2">
                                                                                <label class="text-sm font-medium mb-2" for="insurance_company_name_{{ $index }}">
                                                                                    Insurance Company Name
                                                                                </label>
                                                                                <input id="insurance_company_name_{{ $index }}" wire:model="collaterals.{{ $index }}.insurance_company_name" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                            </div>
                                                                        </div>
                                                                        <div class="px-4">
                                                                            <div class="relative w-full mb-2">
                                                                                <label class="text-sm font-medium mb-2" for="insurance_validity_period_{{ $index }}">
                                                                                    Insurance Validity Period
                                                                                </label>
                                                                                <input id="insurance_validity_period_{{ $index }}" wire:model="collaterals.{{ $index }}.insurance_validity_period" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5
                                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                                                                            </div>
                                                                        </div>



                                                </div>

                                                <div class="w-full">
                                                    <form class="w-full" wire:submit.prevent="save" @disabled(Session::get('disableInputs'))>
                                                        <div class="flex justify-center m-8 w-full">
                                                            <div class="w-full">
                                                                <div class="m-4 w-full">
                                                                    <section class="bg-white-300 flex flex-col items-center justify-center">
                                                                        @if (isset($this->photos[$index]['photo2']))
                                                                            @php
                                                                            $this->save($index);
                                                                            @endphp
                                                                        @endif
                                                                    </section>

                                                                    <div class="flex items-center justify-center w-full">
                                                                        <label class="flex flex-col w-full h-19 hover:bg-gray-100 hover:border-gray-300 pt-4">
                                                                            <div class="flex flex-col items-center justify-center">
                                                                                <p>
                                                                                <div wire:loading.remove wire:target="photos.{{ $index }}.photo2" class="flex flex-col items-center justify-center mt-0 mb-8">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400 group-hover:text-gray-600"
                                                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                                                    </svg>
                                                                                    <p class="pt-1 text-sm tracking-wider text-gray-400 group-hover:text-gray-600">
                                                                                        Attach Collateral Images
                                                                                    </p>
                                                                                </div>
                                                                                </p>
                                                                            </div>
                                                                            <div wire:loading wire:target="photos.{{ $index }}.photo2">
                                                                                <svg style="width: 50%; margin: 0 auto;" xmlns="http://www.w3.org/2000/svg" class="animate-spin w-8 h-8 text-gray-400 group-hover:text-gray-600" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12pm15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                                                                </svg>
                                                                                <p class="pt-1 text-sm tracking-wider text-gray-400 group-hover:text-gray-600">Please wait...</p>
                                                                            </div>
                                                                            <input type="file" class="opacity-0" wire:model="photos.{{ $index }}.photo2" @disabled(Session::get('disableInputs')) />
                                                                        </label>
                                                                    </div>

                                                                    @if (isset($this->photos[$index]['photo2']))
                                                                        <div class="flex justify-end px-4 pt-4">
                                                                            <a wire:click="save({{ $index }})" class="ml-2 inline-flex items-center py-2 px-4 text-sm font-medium text-center text-gray-900 bg-white rounded-lg border border-gray-300 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700" style="cursor: pointer" @disabled(Session::get('disableInputs'))>
                                                                                Set Image
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @error("photos.{{ $index }}.photo2") <span class="error">{{ $message }}</span> @enderror

                                                    </form>

                                                    <div class="w-full">
                                                        <div class="gridx max-w-4xl mx-auto">

                                                            @php
                                                                // Access the common_id
                                                                $common_id = $collateral['common_id'];
                                                                $images = DB::table('loan_images')->where('category',$common_id)->get();
                                                            @endphp
                                                            @foreach ($images as $image)
                                                                <div class="bg-white rounded h-full text-grey-darkest no-underline shadow-md w-64">
                                                                    <div class="w-full justify-items-right">
                                                                        <svg wire:click="close({{ $image->id }})" xmlns="http://www.w3.org/2000/svg" class="object-right h-12 w-12 bg-slate-50 rounded-full stroke-red-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>
                                                                    </div>
                                                                    <img class="block rounded-b w-64" src="{{ $image->url }}" alt="">
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        @elseif (isset($collateral['type']) && $collateral['type'] == 'motor_vehicle')
                                                    <div class="rounded-lg shadow-sm bg-gray-50 p-4">
                                                        <div class="w-full flex justify-end">
                                                            <!-- Button to remove collateral -->

                                                            <button type="button" class="rounded-full bg-white p-1 space-x-2 text-gray-400 hover:text-red-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                                                                <svg wire:click="removeCollateral({{ $index }})" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 " fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                            <!-- File input for collateral document -->
                                            <input type="file" wire:model="collaterals.{{ $index }}.document" class="form-control-file">
                                                    </div>
                                        @endif





                                    </div>
                                @endforeach
                                @endif

                                @if(isset($this->guaranteeType))
                                <button
                                        wire:click="addCollateral"
                                        class="flex text-center items-center
                        bg-customPurple text-white text-xs font-bold
                                    py-1 px-2 rounded-lg p-1.5"
                                    >

                                        <div wire:loading wire:target="addCollateral">
                                            <svg aria-hidden="true" class="w-4 h-4 mr-2 text-gray-200 animate-spin dark:text-gray-900 fill-green-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                                            </svg>
                                        </div>
                                        <div wire:loading.remove wire:target="addCollateral">

                                            <svg class="w-4 h-4 mr-2 fill-current" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                                            </svg>

                                        </div>



                                    Add Collateral
                                    </button>
                                    @endif
                            </div>

                </div>
                <!-- Submit Button -->
                <div class="mt-2 w-full flex justify-end">
                    @if(isset($this->guaranteeType))
                    <button wire:click="saveGuarantor" class="bg-customPurple hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" @disabled(Session::get('disableInputs'))>
                        Submit
                    </button>
                    @endif
                </div>

                </div>

                @else

                    <div class="w-full mx-auto p-6">
                        <div class="w-full">
                            @php
                                $loan = \App\Models\LoansModel::find(session('currentloanID'));
                                $guarantorData = $loan ? json_decode($loan->guarantor, true) : null;
                            @endphp
                            
                            @if($guarantorData)
                                <div class="bg-white w-full">
                                    <div class="flex items-center justify-center mb-4 w-full mx-auto">
                                        @if(isset($guarantorData['image']) && $guarantorData['image'])
                                            <img class="w-24 h-24 rounded-full object-cover" src="{{ asset('storage/' . $guarantorData['image']) }}" alt="{{ $guarantorData['name'] ?? 'Guarantor' }}">
                                        @else
                                            <div class="w-24 h-24 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-gray-600">No Image</span>
                                            </div>
                                        @endif
                                    </div>
                                    <h2 class="text-2xl font-bold text-center text-gray-800">{{ $guarantorData['name'] ?? 'Guarantor' }}</h2>

                                    <div class="mt-4 mb-4 flex gap-2 w-full">
                                        <div class="card w-1/3">
                                            <div class="card-header"><strong>Personal Info</strong></div>
                                            <div class="card-body text-xs">
                                                <p><strong>DOB:</strong> {{ $guarantorData['dob'] ?? 'N/A' }}</p>
                                                <p><strong>Nationality:</strong> {{ $guarantorData['nationality'] ?? 'N/A' }}</p>
                                                <p><strong>Address:</strong> {{ $guarantorData['address'] ?? 'N/A' }}</p>
                                                <p><strong>Phone:</strong> {{ $guarantorData['phone'] ?? 'N/A' }}</p>
                                                <p><strong>Email:</strong> {{ $guarantorData['email'] ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div class="card  w-1/3">
                                            <div class="card-header"><strong>Employment Info</strong></div>
                                            <div class="card-body text-xs">
                                                <p><strong>ID Number:</strong> {{ $guarantorData['id_number'] ?? 'N/A' }}</p>
                                                <p><strong>Employment Status:</strong> {{ $guarantorData['employment_status'] ?? 'N/A' }}</p>
                                                <p><strong>Employer Details:</strong> {{ $guarantorData['employer_details'] ?? 'N/A' }}</p>
                                                <p><strong>Income:</strong> {{ $guarantorData['income'] ?? 'N/A' }}</p>
                                                <p><strong>Assets:</strong> {{ $guarantorData['assets'] ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div class="card  w-1/3">
                                            <div class="card-header"><strong>Financial Info</strong></div>
                                            <div class="card-body text-xs">
                                                <p><strong>Liabilities:</strong> {{ $guarantorData['liabilities'] ?? 'N/A' }}</p>
                                                <p><strong>Credit Score:</strong> {{ $guarantorData['credit_score'] ?? 'N/A' }}</p>
                                                <p><strong>Guarantee Type:</strong> {{ $guarantorData['guaranteeType'] ?? 'N/A' }}</p>
                                                <p><strong>Type:</strong> {{ $guarantorData['type'] ?? 'N/A' }}</p>
                                                <p><strong>Loan ID:</strong> {{ $loan->loan_id ?? 'N/A' }}</p>
                                                <p><strong>Common ID:</strong> {{ $guarantorData['common_id'] ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <style nonce="{{ csp_nonce() }}">
                                        .card {
                                            border: 1px solid #e5e5e5;
                                            border-radius: 8px;
                                            padding: 16px;
                                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                        }
                                        .card-header {
                                            font-size: 1.25em;
                                            margin-bottom: 8px;
                                        }
                                        .card-body p {
                                            margin: 4px 0;
                                        }
                                    </style>

                                </div>
                            @else
                                <div class="bg-white w-full p-6 text-center">
                                    <p class="text-gray-600">No guarantor information found for this loan.</p>
                                </div>
                            @endif
                        </div>
                    </div>



                @endif


            </div>



        </div>




    </div>


</div>

