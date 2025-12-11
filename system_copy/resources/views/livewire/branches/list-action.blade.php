
<div>
    <div class="flex gap-4">


        <button wire:click="edit({{$id}})" type="button" class="hoverable text-white bg-gray-100 hover:bg-blue-100 hover:text-blue focus:ring-4 focus:outline-none focus:ring-blue-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center mr-2 dark:bg-blue-200 dark:hover:bg-blue-200 dark:focus:ring-blue-200">


            <svg xmlns="http://www.w3.org/2000/svg" fill="blue" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
            <span class="hidden text-blue-800 m-2">Edit</span>
        </button>
        <button wire:click="block({{$id}})" type="button" class="hoverable text-white bg-gray-100 hover:bg-red-200 hover:text-red focus:ring-4 focus:outline-none focus:ring-red-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center dark:bg-red-600 dark:hover:bg-red-200 dark:focus:ring-red-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="red" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="hidden text-blue-800 m-2">Block</span>
        </button>
    </div>
</div>
