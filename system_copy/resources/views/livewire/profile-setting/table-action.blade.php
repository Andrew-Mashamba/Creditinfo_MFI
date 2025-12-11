
    <div class="space-x-4 flex">
        <button wire:click ="approveInstitution({{$id}})" class="hoverable text-white bg-gray-100 hover:bg-blue-100 hover:text-blue-200 focus:ring-4 focus:outline-none focus:ring-blue-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center mr-2 dark:bg-blue-200 dark:hover:bg-blue-200 dark:focus:ring-blue-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="blue" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="hidden text-blue-800 m-1">approve</span>
        </button>

        <button wire:click="declineRequest({{$id}})" class=" m-1 py-2 px-1 text-sm font-medium text-center text-gray-900
                            bg-white rounded-md hover:bg-red-100 focus:ring-4   text-red-500 hover:text-red-700 hover:bg-red-100
                            focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                            dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700
                            dark:placeholder-gray-400 dark:text-red dark:focus:ring-blue-500 dark:focus:border-blue-500">
            <svg class="w-5 h-5 text-red-500 hover:text-red-700 hover:bg-red-100 focus:ring-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>

        </button>
    </div>


