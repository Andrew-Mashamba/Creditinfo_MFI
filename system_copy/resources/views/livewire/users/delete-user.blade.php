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


        <div class="w-1/3 p-4 ">


            <p for="userSelected" class="block mb-1 text-sm capitalize text-slate-400 dark:text-white ">SELECT USER</p>
            <select wire:model="userSelected" name="userSelected" id="userSelected" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                <option selected value="">Select</option>

                @foreach($this->usersList as $user)

                    <option selected value="{{$user->id}}">{{$user->name}}</option>
                @endforeach

            </select>
            @error('userSelected')
            <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                <p>Branch is mandatory.</p>
            </div>
            @enderror

            @if($this->userSelected)





                <div class="flex items-center mb-2 text-sm spacing-sm text-slate-600 mt-4" >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p>User selected: {{ App\Models\User::where('id', $this->userSelected)->value('name') }}</p>

                </div>

                <div class="mt-4 w-full">
                    <p for="userSelected" class="block mb-1 text-sm capitalize text-slate-400 dark:text-white ">SELECT ACTION</p>

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

    @if($this->userSelected)
        <div class="p-4">

            <div class="flex justify-end w-auto" >
                <div wire:loading wire:target="confirmPassword" >
                    <button class="px-4 py-2 text-sm font-medium text-white bg-red-400 rounded-lg hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-400 dark:hover:bg-red-400 dark:focus:ring-red-400" disabled>
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 animate-spin stroke-white-800" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                            </svg>
                            <p>Please wait...</p>
                        </div>
                    </button>
                </div>

            </div>


            <div class="flex justify-end w-auto" >
                <div wire:loading.remove wire:target="confirmPassword" >
                    <button wire:click="confirmPassword" class="px-4 py-2 text-sm font-medium text-white bg-red-400 rounded-lg hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-400 dark:hover:bg-red-400 dark:focus:ring-red-400">
                        Proceed
                    </button>

                </div>
            </div>
        </div>

    @endif

    <div class="mt-8"></div>






</div>
