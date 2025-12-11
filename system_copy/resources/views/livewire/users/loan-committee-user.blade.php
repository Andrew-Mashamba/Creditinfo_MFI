<div>

    @if (session()->has('message'))

        @if (session('alert-class') == 'alert-success')
            <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8"
                role="alert">
                <div class="flex">
                    <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path
                                d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z" />
                        </svg></div>
                    <div>
                        <p class="font-bold">The process is completed</p>
                        <p class="text-sm">{{ session('message') }} </p>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <div class="col-span-6 sm:col-span-4">






        <div class="w-full flex gap-4">

            <div class="w-1/2">

                <div class="form-group col-span-6 sm:col-span-4 ">
                    <label class="block text-sm font-medium mb-1" for="selectedrole">
                        Select Committee Category
                    </label>
                    <select id="selectedrole" wire:model="loan_category"
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10"
                        required="">
                        <option value="" selected="">Select</option>
                        <option value="loan"> Loan </option>
                        <option value="payment">Payment</option>
                    </select>
                </div>


                <label for="role_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Enter
                    Committee Name</label>
                <input wire:model="name" type="text" id="role_name"
                    class="bg-gray-50 border border-gray-300 text-gray-900
                                    text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                                    dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    placeholder="" required>


                @error('name')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Role name is mandatory and should be more than two characters.</p>
                    </div>
                @enderror

            </div>

            <div class="w-1/2">
                <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    description</label>
                <textarea wire:model="description" id="description" rows="4"
                    class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    placeholder="Enter description..."></textarea>
                <div class="mt-4"></div>

                @error('description')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Description is mandatory and should be more than two characters.</p>
                    </div>
                @enderror
                <div class="mt-4"></div>
            </div>


        </div>




    </div>
    @if ($this->name)

        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Select Committee members </label>
        <div class="flex flex-wrap mt-4 w-full">

            @foreach ($this->users as $key => $user)
                <div class="mt-1 flex-shrink-0 w-1/3">
                    <label class="inline-flex items-center" for="permission_{{ $key }}">
                        <input type="checkbox" name="user[]" id="user_{{ $key }}" value="{{ $key }}"
                            wire:model="user_list" @if ($user) checked @endif
                            wire:click="togglePermission({{ $key }},'{{ $user }}')"
                            class="form-checkbox h-6 w-6 text-red-500 bg-gray-100
                   rounded border-gray-300 focus:ring-red-500 dark:focus:ring-red-600
                   dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">


                        <span class="ml-3 text-sm">{{ $user->name ?? 'N/A' }}</span>

                    </label>
                </div>
            @endforeach


        </div>







        <div class="flex justify-end w-auto">
            <div wire:loading wire:target="save">
                <button
                    class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400"
                    disabled>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-white-800"
                            fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                        </svg>
                        <p>Please wait...</p>
                    </div>
                </button>
            </div>

        </div>


        <div class="flex justify-end w-auto">
            <div wire:loading.remove wire:target="save">
                <button wire:click="save"
                    class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400">
                    Save
                </button>

            </div>
        </div>



    @endif
</div>
