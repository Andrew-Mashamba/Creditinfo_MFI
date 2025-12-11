<div>
    {{-- Success is as dangerous as failure. --}}
    <div class="flex w-full gap-2 mb-2 mb-2">
        <div class="ml-2">
            <label for="category" class="block mb-2 dark:text-gray-400 space-x-2 text-sm font-semibold spacing-sm text-slate-600">
                 Loan Officer
            </label>
            <div class="relative ">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg data-slot="icon" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"></path>
                      </svg>

                </div>
                <select  wire:model="loanOfficer" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500 flatpickr-input" >

                    <option value="" > select </option>
                    @foreach (DB::table('employees')->get() as $employee )

                    <option value="{{$employee->id}}" > {{ $employee->first_name.' '.$employee->middle_name.' '.$employee->last_name }} </option>

                    @endforeach
                </select>
            </div>
        </div>

    </div>

    <livewire:reports.active-loan-by-officer-table />

</div>
