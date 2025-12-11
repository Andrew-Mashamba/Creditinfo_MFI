<div class="flex space-x-1 justify-around">

    {{--    <div class="flex items-center space-x-4 flex-lg-row">--}}
    {{--        <style nonce="{{ csp_nonce() }}">--}}
    {{--            .hoverable:hover .hidden {--}}
    {{--                display: block;--}}
    {{--            }--}}
    {{--        </style>--}}
    {{--        <button wire:click="assign({{$id}})" type="button" class="hoverable text-white bg-gray-100 hover:bg-blue-100 hover:text-blue focus:ring-4 focus:outline-none focus:ring-blue-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center mr-2 dark:bg-blue-200 dark:hover:bg-blue-200 dark:focus:ring-blue-200">--}}
    {{--            <svg xmlns="http://www.w3.org/2000/svg" fill="blue" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">--}}
    {{--                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />--}}
    {{--            </svg>--}}
    {{--            <span class="hidden text-blue-800 m-2">Assign</span>--}}
    {{--        </button>--}}
    {{--    </div>--}}


    <button wire:click="edit({{$id}})" class="p-1 text-blue-600 hover:bg-blue-900 hover:bg-blue-200 rounded">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
    </button>

    <button wire:click="delete({{$id}})" class=" m-1 py-2 px-1 text-sm font-medium text-center text-gray-900
                            bg-white rounded-md hover:bg-red-100 focus:ring-4   text-red-500 hover:text-red-700 hover:bg-red-100
                            focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                            dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700
                            dark:placeholder-gray-400 dark:text-red dark:focus:ring-blue-500 dark:focus:border-blue-500">
        <svg class="w-5 h-5 text-red-500 hover:text-red-700 hover:bg-red-100 focus:ring-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>

    </button>




</div>

