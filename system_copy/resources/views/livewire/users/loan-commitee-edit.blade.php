<div>
    @if (session()->has('message'))
        @if (session('alert-class') == 'alert-success')
            <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
                <div class="flex">
                    <div class="py-1">
                        <svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold">The process is completed</p>
                        <p class="text-sm">{{ session('message') }}</p>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <div class="col-span-6 sm:col-span-4">
        <div class="w-full flex gap-4">
            <div class="w-1/2">
                <div class="form-group col-span-6 sm:col-span-4">
                    <x-jet-label for="selectedrole" value="{{ __('Select Committee') }}" />
                    <select id="selectedrole" wire:model="committee_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                        <option value="" selected>Select</option>
                        @foreach($this->committee_list as $value)
                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                        @endforeach
                    </select>
                    @error('committee_id')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Please select a committee</p>
                    </div>
                    @enderror
                </div>

                <div class="form-group col-span-6 sm:col-span-4">
                    <label class="block text-sm font-medium mb-1" for="loan_category">Select Committee Category</label>
                    <select id="loan_category" wire:model="loan_category" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                        <option value="" selected>Select</option>
                        <option value="loan">Loan</option>
                        <option value="payment">Payment</option>
                    </select>
                </div>
            </div>

            <div class="w-1/2">
                <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Role description</label>
                <textarea wire:model="description" id="description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter description..."></textarea>
                @error('description')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>Description is mandatory and should be more than two characters.</p>
                </div>
                @enderror
            </div>
        </div>
    </div>

    @if ($this->committee_id)

        <div class="flex flex-wrap mt-4 w-full">

                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Select Member</label>
                <div class="flex flex-wrap mt-4 w-full">
                    <div>
                        @foreach($users as $user)
                            <div class="w-1/3 mt-1">
                                <label class="inline-flex items-center">
                                    <input type="checkbox"
                                           value="{{ $user->id }}"
                                           wire:click="toggleUser({{ $user->id }})"
                                           @if(in_array($user->id, $user_list)) checked @endif
                                           class="form-checkbox h-6 w-6 text-blue-500">
                                    <span class="ml-3 text-sm">{{ $user->name }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

        </div>

        <div class="flex justify-end w-auto">
            <div wire:loading wire:target="save">
                <button class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center mr-2 dark:bg-blue-900 dark:hover:bg-blue-900 dark:focus:ring-blue-800 inline-flex items-center">
                    <svg aria-hidden="true" role="status" class="inline mr-2 w-4 h-4 text-white animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M100 50.59c0 27.614-22.385 50-50 50S0 78.205 0 50.59c0-21.32 13.805-39.547 33.095-46.08" fill="#000"/>
                    </svg>
                    Processing...
                </button>
            </div>
            <div wire:loading.remove wire:target="save">
                <button type="submit" wire:click="save" class="text-white bg-blue-500 hover:bg-blue-400 focus:ring-4
                focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center mr-2
                dark:bg-blue-900 dark:hover:bg-blue-900 dark:focus:ring-blue-800">Save</button>
            </div>
        </div>
        @endif
</div>

