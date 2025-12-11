<div class="lg:w-2/3">
    <!-- message container -->

    @if(Session::get('memberToViewId'))
        <livewire:members.member-view/>
    @elseif(Session::get('memberToEditId'))
        <livewire:members.edit-member/>
    @else

        <div>

            <div class="flex">

                <div class="mb-12 shadow-sm w-4/5">
                    <label for="default-search" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-gray-300">Search</label>
                    <div class="relative">



                        <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input wire:model.debounce="term" type="search" id="default-search" class="block p-4 pl-10 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-400 focus:border-blue-400 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400" placeholder="Search by name, number or phone or branch..." required>
                        <button wire:click="resetSearch" class="text-white absolute right-1.5 bottom-1.5 top-1.5 bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-2 py-2 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400">
                            <div wire:loading.remove wire:target="term">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 stroke-white-800" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div wire:loading wire:target="term" >
                                <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-6 w-6 stroke-white-800" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                                </svg>
                            </div>
                        </button>

                    </div>
                </div>

                <select wire:model.bounce="memberStatus" name="memberStatus" id="memberStatus" class="w-1/5 mb-12 ml-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option selected value="All">All</option>
                    <option value="Active">Active</option>
                    <option value="Pending Approval">Pending Approval</option>
                    <option value="Suspended">Suspended</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Rejected">Rejected</option>

                </select>


            </div>


            <div class="bg-white rounded rounded-lg shadow-sm">


                <livewire:members.members-table
                        exportable
                />

            </div>
        </div>

    @endif

</div>
