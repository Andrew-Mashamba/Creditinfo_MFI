<div>
    {{-- Success is as dangerous as failure. --}}

    <div class="grid gap-4 grid-cols-1 grid-cols-4 my-2 w-full">

        <div class="metric-card @if($this->selected==10) rounded-lg p-4  bg-blue-900 text-white @else   dark:bg-gray-900 border  bg-white  border-gray-200 dark:border-gray-800  rounded-lg p-4   w-full @endif ">

            <div class="flex justify-between items-center w-full">
                <div class="flex items-center">
                    <div wire:loading="" wire:target="visit(10)">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-gray-400" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <p>Please wait...</p>
                        </div>

                    </div>
                    <div wire:loading.remove="" wire:target="visit(10)">
                        <div class="flex items-center text-l font-semibold spacing-sm text-slate-600"> PAR 1 - 10 (Days)
                        </div>
                    </div>

                </div>
                <div class="flex items-center space-x-5">

                    <svg wire:click="visit(10)" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-slate-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>


                </div>
            </div>
            <table>

                <tbody><tr>
                    <td class="mt-2 text-sm font-semibold    text-slate-400  dark:text-white capitalize  ">Number of Loans </td>
                    <td class="pl-2 mt-2 text-sm font-semibold spacing-sm   text-slate-400   dark:text-white text-right">

                        {{ $count10 }}
                    </td>
                </tr>

                <tr>
                    <td class="mt-2 text-sm font-semibold    text-slate-400  dark:text-white capitalize  ">Total amount </td>
                    <td class="pl-2 mt-2 text-sm font-semibold spacing-sm   text-slate-400   dark:text-white text-right">

                        {{ number_format($below10,1) }} TZS
                    </td>
                </tr>


                </tbody>
            </table>
        </div>

        <div class="metric-card  @if($this->selected==30) rounded-lg p-4  bg-blue-900 text-white @else   dark:bg-gray-900 border  bg-white  border-gray-200 dark:border-gray-800  rounded-lg p-4  w-full  @endif ">
            <div class="flex justify-between items-center w-full">
                <div class="flex items-center">
                    <div wire:loading="" wire:target="visit(30)">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-gray-400" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <p>Please wait...</p>
                        </div>
                     </div>
                    <div wire:loading.remove="" wire:target="visit(30)">
                        <div class="flex items-center text-l font-semibold spacing-sm text-slate-600"> PAR 10 - 30 (Days)
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-5">
                    <svg wire:click="visit(30)" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-slate-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <table>
                <tbody>
                    <tr>
                    <td class="mt-2 text-sm font-semibold    text-slate-400  dark:text-white capitalize  "> Total Loan </td>
                    <td class="pl-2 mt-2 text-sm font-semibold spacing-sm   text-slate-400   dark:text-white text-right">
                     {{$count30}}
                    </td>
                </tr>

                <tr>
                    <td class="mt-2 text-sm font-semibold    text-slate-400  dark:text-white capitalize  ">Total amount</td>
                    <td class="pl-2 mt-2 text-sm font-semibold spacing-sm   text-slate-400   dark:text-white text-right">

                       {{ number_format($below30,1) }} TZS
                    </td>
                </tr>
            </tbody>
        </table>
        </div>


        <div class="metric-card @if($this->selected==40) rounded-lg p-4  bg-blue-900 text-white @else   dark:bg-gray-900 border  bg-white  border-gray-200 dark:border-gray-800  rounded-lg p-4   w-full @endif ">

            <div class="flex justify-between items-center w-full">
                <div class="flex items-center">
                    <div wire:loading="" wire:target="visit(40)">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-gray-400" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>

                            </svg>


                            <p>Please wait...</p>
                        </div>

                    </div>
                    <div wire:loading.remove="" wire:target="visit(40)">


                        <div class="flex items-center text-l font-semibold spacing-sm text-slate-600"> PAR 30 - 90 (Days)

                        </div>

                    </div>

                </div>
                <div class="flex items-center space-x-5">

                    <svg wire:click="visit(40)" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-slate-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>


                </div>
            </div>


            <table>

                <tbody><tr>
                    <td class="mt-2 text-sm font-semibold    text-slate-400  dark:text-white capitalize  ">Total Number</td>
                    <td class="pl-2 mt-2 text-sm font-semibold spacing-sm   text-slate-400   dark:text-white text-right">

                       {{$count60 }}
                    </td>
                </tr>

                <tr>
                    <td class="mt-2 text-sm font-semibold    text-slate-400  dark:text-white capitalize  ">Total  Amount </td>
                    <td class="pl-2 mt-2 text-sm font-semibold spacing-sm   text-slate-400   dark:text-white text-right">

                       {{  number_format($below60,1) }} TZS
                    </td>
                </tr>
            </tbody>
        </table>

    </div>

        <div class="metric-card  @if($this->selected==50) rounded-lg p-4  bg-blue-900 text-white @else   dark:bg-gray-900 border  bg-white  border-gray-200 dark:border-gray-800  rounded-lg p-4   w-full @endif ">

            <div class="flex justify-between items-center w-full">
                <div class="flex items-center">
                    <div wire:loading="" wire:target="visit(50)">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-gray-400" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>

                            </svg>
                            <p>Please wait...</p>
                        </div>

                    </div>
                    <div wire:loading.remove="" wire:target="visit(50)">

                        <div class="flex items-center text-l font-semibold spacing-sm text-slate-600"> PAR Above 90 (Days)

                        </div>

                    </div>

                </div>
                <div class="flex items-center space-x-5">

                    <svg wire:click="visit(50)" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-slate-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>


                </div>
            </div>


            <table>
                <tbody><tr>
                    <td class="mt-2 text-sm font-semibold    text-slate-400  dark:text-white capitalize  "> Total Number </td>
                    <td class="pl-2 mt-2 text-sm font-semibold spacing-sm   text-slate-400   dark:text-white text-right">

                       {{ $count90 }}
                    </td>
                </tr>

                <tr>
                    <td class="mt-2 text-sm font-semibold    text-slate-400  dark:text-white capitalize  ">Total Amont  </td>
                    <td class="pl-2 mt-2 text-sm font-semibold spacing-sm   text-slate-400   dark:text-white text-right">

                        {{ number_format($below90,1) }} TZS
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    </div>
    
    <hr class="w-full"/>

    {{-- Detailed Loans Table --}}
    <div class="mt-6">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Portfolio at Risk Details</h3>
                        <p class="text-sm text-gray-500">Detailed view of loans in the selected risk category</p>
                    </div>
                    <div class="flex space-x-2">
                    <button wire:click="exportToExcel" 
                                wire:loading.attr="disabled"
                                wire:target="exportToExcel"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="exportToExcel">Export Excel</span>
                            <span wire:loading wire:target="exportToExcel">Exporting Excel...</span>
                        </button>
                        <button wire:click="exportToPdf" 
                                wire:loading.attr="disabled"
                                wire:target="exportToPdf"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="exportToPdf">Export PDF</span>
                            <span wire:loading wire:target="exportToPdf">Exporting PDF...</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days in Arrears</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($loans as $index => $loan)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loan->client_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($loan->principle, 2) }} TZS</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loan->start_date ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loan->due_date ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($loan->interest, 2) }} TZS</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($loan->outstanding_amount, 2) }} TZS</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($loan->days_in_arrears >= 90) bg-red-100 text-red-800
                                        @elseif($loan->days_in_arrears >= 30) bg-yellow-100 text-yellow-800
                                        @elseif($loan->days_in_arrears >= 10) bg-orange-100 text-orange-800
                                        @else bg-green-100 text-green-800 @endif">
                                        {{ $loan->days_in_arrears }} days
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($loan->status === 'ACTIVE') bg-green-100 text-green-800
                                        @elseif($loan->status === 'OVERDUE') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $loan->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="downloadSchedule({{ $loan->id }})" 
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-900 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Download
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No loans found for the selected criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($loans->count() > 0)
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ $loans->count() }} loan(s) in the selected risk category
                        </div>
                        <div class="text-sm text-gray-500">
                            Total Outstanding: {{ number_format($loans->sum('outstanding_amount'), 2) }} TZS
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
