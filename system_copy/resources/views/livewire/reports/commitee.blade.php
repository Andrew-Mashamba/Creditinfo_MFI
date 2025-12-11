<div>
    <div class="flex gap-2 mb-8 ">
        <button wire:click="createNewMinute()" class="group box-button flex hover:main-color-bg text-center items-center
                                        bg-gray-100 text-gray-400 font-semibold
                                        py-2 px-4 rounded-lg">

            <div wire:loading="" wire:target="createNewMinute()">
                <svg aria-hidden="true" class="w-8 h-8 mr-2 text-gray-200 animate-spin dark:text-gray-900 fill-red-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"></path>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"></path>
                </svg>
            </div>
            <div class="mr-2" wire:loading.remove="" wire:target="createNewMinute()">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path class="group-hover:text-white  icon-color  " stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
             New Minute
        </button>
</div>
<livewire:reports.commitee-minute-table />


@if($this->enableModal)

<div class="fixed z-10 inset-0 overflow-y-auto w-full bg-black bg-opacity-50"  >
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center ">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-0"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-1/2 p-2">
            <div class="flex justify-center item-center ">
       New Commitee Minute
            </div>

            <div class="p-6 mt-6">
                @if (session()->has('message'))
                    <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
                        {{ session('message') }}
                    </div>
                @endif

                <form wire:submit.prevent="save">
                    <!-- Committee Name -->
                    <div class="mb-4">
                        <label for="committee_name" class="block text-sm font-medium text-gray-700">Committee Name</label>
                        <input type="text" id="committee_name" wire:model="committee_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Enter committee name">
                        @error('committee_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Meeting Date -->
                    <div class="mb-4">
                        <label for="meeting_date" class="block text-sm font-medium text-gray-700">Meeting Date</label>
                        <input type="date" id="meeting_date" wire:model="meeting_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @error('meeting_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Descriptions -->
                    <div class="mb-4">
                        <label for="descriptions" class="block text-sm font-medium text-gray-700">Descriptions</label>
                        <textarea id="descriptions" wire:model="descriptions" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Enter minutes here"></textarea>
                        @error('descriptions') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- File Path -->
                    <div class="mb-4">
                        <label for="file_path" class="block text-sm font-medium text-gray-700">Upload Document</label>
                        <input type="file" id="file_path" wire:model="file_path" class="mt-1 block w-full text-sm text-gray-500 file:py-2 file:px-4 file:border file:border-gray-300 file:rounded-md file:text-sm file:font-medium file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100" accept=".pdf,.doc,.docx,.txt">
                        @error('file_path') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex space-x-4 mb-4 mr-4 items-center justify-end">
                        <button wire:click="$toggle('enableModal')" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Cancel
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $isEdit ? 'Update' : 'Submit' }}
                        </button>
                    </div>
                </form>
            </div>
    </div>
@endif
</div>
