<div class="rounded-lg overflow-hidden shadow-b-lg">


    @if(Session::get('currentloanID'))

        <div class="relative w-full mb-4">
            <div class="min-w-full text-center text-sm font-light">
                <div class="text-lg text-slate-400 font-bold mb-1 ">
                    TOP LOANS ASSESSMENT AND APPROVAL

                </div>

            </div>

            <div>

                <div class="w-1/6">
                    <div class="text-sm text-slate-400 font-bold">
                        PROGRESS OF THE LOAN

                    </div>
                    <hr class="border-b-0 my-2"/>
                </div>

                <ul class="max-w-md space-y-1 text-gray-500 list-inside dark:text-gray-400 ">
                    @php
                        // Get current loan details
                        $loan = App\Models\LoansModel::find(Session::get('currentloanID'));
                        $status = $loan->status;

                        $current_loans_stages = DB::table('current_loans_stages')
                                                ->where('loan_id', Session::get('currentloanID'))
                                                ->get();

                        //dd(Session::get('currentloanID'));
                    @endphp

                    @foreach($current_loans_stages as $index => $stage)
                        <li class="flex items-centerc text-sm">



                            @if($status == $stage->stage_name)

                                <svg class="w-3.5 h-3.5 mr-2 text-customPurple dark:text-green-400 flex-shrink-0 my-auto"
                                     {{--                                 stroke="red"--}}
                                     aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="red"
                                     viewBox="0 0 20 20">
                                    <path
                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                </svg>

                                <div class="w-full flex gap-2 my-auto">

                                    <svg class="w-4 h-4 my-auto" data-slot="icon" fill="none" stroke-width="1.5" stroke="red" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5"></path>
                                    </svg>

                                    <p class="text-red-900 font-semibold my-auto">
                                        {{ $stage->stage_name }}
                                    </p>
                                </div>
                            @else

                                <svg class="w-3.5 h-3.5 mr-2 text-customPurple dark:text-green-400 flex-shrink-0 my-auto"
                                     {{--                                 stroke="red"--}}
                                     aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="gray"
                                     viewBox="0 0 20 20">
                                    <path
                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                </svg>

                                {{ $stage->stage_name }}
                            @endif



                        </li>
                    @endforeach
                </ul>


            </div>

        </div>

        {{--        <hr class="border-b-0 my-6"/>--}}
        @livewire('loans.loan-process')
    @else

        <div class="relative w-full">
            <div class="min-w-full text-center text-sm font-light">
                <div class="text-l text-slate-400 font-bold mb-1 ">
                    TOP LOANS ASSESSMENT AND APPROVAL

                </div>

            </div>

            <div>

                <ul class="max-w-md space-y-1 text-gray-500 list-inside dark:text-gray-400">

                    <li class="flex items-center text-sm">
                        <svg class="w-3.5 h-3.5 mr-2 text-customPurple dark:text-green-400 flex-shrink-0"
                             aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                             viewBox="0 0 20 20">
                            <path
                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                        </svg>
                        Total Loans: {{ App\Models\LoansModel::where('loan_type_2','TopUp')->count() }}
                    </li>

                    @php
                        $distinctLoanStatuses = App\Models\LoansModel::where('loan_type_2', 'TopUp')
                                                ->select('status', DB::raw('count(*) as total'))
                                                ->groupBy('status')
                                                ->get();
                    @endphp

                    @foreach($distinctLoanStatuses as $distinctLoanStatus)
                        <li class="flex items-center text-sm">
                            <svg class="w-3.5 h-3.5 mr-2 text-customPurple dark:text-green-400 flex-shrink-0"
                                 aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                 viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                            </svg>
                            {{ ucwords(strtolower($distinctLoanStatus->status)) }} : {{ ucwords(strtolower($distinctLoanStatus->total)) }}
                        </li>
                    @endforeach



                </ul>

            </div>

        </div>

        <hr class="border-b-0 my-6"/>
        @livewire('loans.top-up-loans-table')
    @endif



</div>
