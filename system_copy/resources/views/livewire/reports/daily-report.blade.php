<div>
    {{-- Do your work, then step back. --}}
    <div class="w-full flex ">
        <script nonce="{{ csp_nonce() }}">
            flatpickr('[datepicker]', {
                dateFormat: "Y-m-d"
                , autoHide: true
                , allowInput: true
                , minDate: "2000-01-01"
                , maxDate: new Date().fp_incr(14)
            });
        </script>

        <div class="flex w-full gap-2 mb-2 mb-2">
            <div class="ml-2">
                <label for="category" class="block mb-2 dark:text-gray-400 space-x-2 text-sm font-semibold spacing-sm text-slate-600">
                     Date
                </label>
                <div class="relative ">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input datepicker="" datepicker-autohide="" wire:model="day_date" type="date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 flatpickr-input" placeholder="Select date">
                </div>
            </div>
            {{-- <div class="">
                <label for="category" class="block mb-2 dark:text-gray-400 space-x-2 text-sm font-semibold spacing-sm text-slate-600">
                    End Date
                </label>
                <div class="relative ">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input datepicker="" datepicker-autohide="" wire:model="endDate" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 flatpickr-input" placeholder="Select date">
                </div>
            </div> --}}

        </div>

    </div>

    <div class="w-full flex items-center justify-center ">
        <div wire:loading wire:target="selectedBranch">
            <div class="h-96 m-auto flex items-center justify-center">
                <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500"></div>
            </div>
        </div>
    </div>
    <div wire:loading.remove wire:target="selectedBranch">
    </div>

    <div class="bg-gray-100 p-2 rounded-lg">


    <div class="w-full mb-1 flex gap-1">
        <div class="w-1/3 bg-white rounded-2xl p-4">
            <table class="w-full rounded-2xl">
                <thead>
                <tr>
                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 tracking-wider">Institution Branches</th>
                </tr>
                </thead>
                <tbody>
                @foreach(DB::table('branches')->get() as $branch )

                <tr wire:click="selectedBranch({{$branch->id}})" class=" cursor-pointer @if($this->tab_id == $branch->id) text-white bg-blue-900 @endif
                        hover:bg-blue-900 rounded-lg">
                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                        {{$branch->name}}
                    </td>
                </tr>
                    @endforeach


                </tbody>
            </table>
        </div>



        <div class="w-2/3 bg-white rounded-2xl p-4 relative">

            <div class="w-full mb-1 flex gap-1 bg-gray-200 p-1 rounded-2xl ">

                <div class="w-full bg-white rounded-2xl p-4">
                   @if($this->tab_id)



                        <div class="fw-bold">

                            <div class="text-center text-underline justify-center align-center text-base lighting item-center flex font-bold uppercase">
                                {{DB::table('branches')->where('id',$this->tab_id)->value('name')}}
                            </div>

                            <div class="w-full mx-auto bg-white p-4 rounded-lg shadow">
                                <table class="w-full">
                                    <thead>
                                    <tr>
                                        <td class="py-2">Total Principle :</td>
                                        <td class="py-2 fw-light">{{ number_format($this->total_principle,2) }} TZS</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="border-t-2">
                                        <td class="py-2">Total Disbursement  :</td>
                                        <td class="py-2">{{ number_format($this->total_disbursement_amount,2) }} TZS</td>
                                    </tr>
                                    <tr class="border-t-2">
                                        <td class="py-2"> Total Loan Application:</td>
                                        <td class="py-2">{{ $this->loan_applications }}</td>
                                    </tr>


                                    </tbody>
                                </table>
                            </div>
                        </div>
                       @else
                        <p class="font-semibold ">no data to display </p>

                       @endif

                </div>

            </div>


            <div class="flex items-end w-full absolute bottom-0 mb-4 ">

            </div>

        </div>

    </div>
    </div>

    <div class="w-full overflow-x-auto  p-2 ">

     <livewire:reports.daily-table />

    </div>










</div>
