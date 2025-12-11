<div class="w-full flex">


    <div class="w-2/3 bg-white p-6 shadow-md">


        <button id="exportBtn" type="button" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5
        me-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">Export to Excel</button>

        <button id="exportBtnPDF" type="button" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5
        me-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">Export to PDF</button>



        <div id="element-to-print" class=" mx-auto bg-white p-6 shadow-md">
            <!-- Title Section -->
            <div class="text-center">
                <h2 class="text-lg font-bold uppercase">NBC SACCOS LTD</h2>
                <h3 class="text-base font-semibold uppercase">STATEMENT  OF  CHANGES  IN  EQUITY  FOR  THE  YEAR  ENDED  31.12.2023</h3>
            </div>

            @php
                $currentDate = date('Y-m-d');

                $endOfLastYear = (date('Y') - 1) . '-12-31';
                @endphp

            <!-- Date and Currency Section -->
            <div class="flex justify-end mt-4">
                <table class="border border-black text-xs text-right">
                    <thead>
                    <tr class="border border-black">
                        <th class="px-4 py-2 border">{{$currentDate}}</th>
                        <th class="px-4 py-2 border">{{$endOfLastYear}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="px-4 py-2 border">TSHS</td>
                        <td class="px-4 py-2 border">TSHS</td>
                    </tr>
                    </tbody>
                </table>
            </div>







            <!-- Financial Data Table -->
            <div class="mt-6" id="element-to-print">
                <table id="dataTable" class="w-full text-left border-collapse text-sm">
                    <tbody>

                    <tr>
                        @php

                            //$accounts = DB::table('accounts')->where('major_category_code','3000')->where('account_level', 2)->orderBy('id', 'asc')->get();

                            $accounts = DB::table('accounts')
                                ->where('major_category_code', '3000')
                                ->where('account_level', 2)
                                ->orderBy('id', 'asc')
                                ->get();

                            // Sort the collection to move 'Retained Surplus' to the end
                            $sortedAccounts = $accounts->sortBy(function ($account) {
                                return $account->account_name === 'Retained Surplus' ? 1 : 0;
                            });

                            // If you need a sorted collection to be indexed from 0 again, you can use values():
                            $accounts = $sortedAccounts->values();

                        @endphp
                        <td class="font-bold px-4 py-2 border"></td>
                        @foreach($accounts as $account)
                            <td class="px-4 py-2 border">{{$account->account_name}}</td>
                        @endforeach
                        <td class="font-bold px-4 py-2 border">Total Equity</td>

                    </tr>
                    <tr>
                        <td class="font-bold px-4 py-2 border">Percentage</td>
                        @foreach($accounts as $account)
                        <td class="px-4 py-2 border">{{$account->percent}}</td>
                        @endforeach
                        <td class="font-bold px-4 py-2 border"></td>
                    </tr>

                    <tr>
                        <td class="font-bold px-4 py-2 border">Details</td>
                        @foreach($accounts as $account)
                        <td class="px-4 py-2 border">TZS</td>
                        @endforeach
                        <td class="font-bold px-4 py-2 border"></td>
                    </tr>

                    <tr>
                        <td class="font-bold px-4 py-2 border">Opening Balance</td>
                        @php
                            $total_balance = 0;
                            @endphp
                        @foreach($accounts as $account)
                            @php


                                $currentYear = Carbon\Carbon::now()->year;
                                $balance = DB::table('general_ledger')
                                    ->where('sub_category_code', $account->sub_category_code)
                                    ->where('created_at', '<=', Carbon\Carbon::create($currentYear, 1, 1, 1, 0, 0)) // January 1st, 01:00 AM
                                    ->select(DB::raw('SUM(debit) - SUM(credit) as balance'))
                                    ->value('balance');

                                $total_balance = $total_balance + $balance;
                            @endphp
                            <td class="px-4 py-2 border">{{number_format($balance,2)}}</td>
                        @endforeach
                        <td class="font-bold px-4 py-2 border">{{number_format($total_balance,2)}}</td>

                    </tr>


                    @foreach(DB::table('entries')->get() as $entryt)
                        <tr>
                            <td class="px-4 py-2 border">{{$entryt->content}}</td>
                            @foreach($accounts as $account)

                                <td class="px-4 py-2 border">{{number_format(DB::table('entries_amount')->where('entry_id',$entryt->id)->where('account_id',$account->id)->value('amount'),2)}}</td>
                            @endforeach
                            <td class="font-bold px-4 py-2 border"></td>
                        </tr>
                    @endforeach

                    <tr>
                        <td class="font-bold px-4 py-2 border">Profit for year {{(date('Y'))}}</td>
                        @foreach($accounts as $account)
                            @if( $account->account_name== 'Retained Surplus')
                                @php
                                    $profit = $this->calculateSurplusAfterTax();
                                @endphp
                                <td class="px-4 py-2 border">{{ number_format($profit['this_year'], 2) }}</td>
                            @else
                                <td class="px-4 py-2 border"></td>
                            @endif

                        @endforeach
                        <td class="font-bold px-4 py-2 border"></td>
                    </tr>

                    <tr>
                        <td class="font-bold px-4 py-2 border">Appropriation for the year</td>
                        @php
                        $TotalAppropriation = 0;
                            @endphp
                        @foreach($accounts as $account)
                            @if( $account->account_name== 'Retained Surplus')
                                @php
                                    $total_percent = DB::table('accounts')->where('major_category_code','3000')->where('account_level', 2)->sum('percent');
                                    $surplus = (($total_percent/100 * $profit['this_year']));
                                    $TotalAppropriation = $TotalAppropriation + $surplus;
                                @endphp
                                <td class="px-4 py-2 border">{{ number_format($surplus, 2) }}</td>
                            @else
                                <td class="px-4 py-2 border">{{number_format((float)$profit['this_year'] * (float)$account->percent/100,2)}}</td>
                            @endif

                        @endforeach
                        <td class="font-bold px-4 py-2 border"></td>
                    </tr>


                    <tr class="font-bold">
                        <td class="px-4 py-2 border">Closing Balances: {{(date('Y'))}}</td>
                        @php
                            $total_closing_balance = 0;
                        @endphp
                        @foreach($accounts as $account)
                            @php

                                $currentYear = Carbon\Carbon::now()->year;
                                    $openingBalance = DB::table('general_ledger')
                                        ->where('sub_category_code', $account->sub_category_code)
                                        ->where('created_at', '<=', Carbon\Carbon::create($currentYear, 1, 1, 1, 0, 0)) // January 1st, 01:00 AM
                                        ->select(DB::raw('SUM(debit) - SUM(credit) as balance'))
                                        ->value('balance');

                                $entriesSumAmount = DB::table('entries_amount')->where('account_id',$account->id)->value('amount');
                                $totalAmount = $openingBalance + $entriesSumAmount + ((float)$profit['this_year'] * (float)$account->percent/100);

                                //$total_closing_balance = $total_closing_balance + $totalAmount;
                            @endphp

                            @if( $account->account_name== 'Retained Surplus')
                                @php
                                $surplusClosingBalance = (float)$profit['this_year'] - $TotalAppropriation;

                                $total_closing_balance = $total_closing_balance + $surplusClosingBalance;
                                @endphp
                                <td class="px-4 py-2 border">{{number_format($surplusClosingBalance,2)}}</td>
                            @else
                                @php
                                $total_closing_balance = $total_closing_balance + $totalAmount;
                                @endphp
                                <td class="px-4 py-2 border">{{number_format($totalAmount,2)}}</td>
                            @endif


                        @endforeach
                        <td class="font-bold px-4 py-2 border">{{number_format($total_closing_balance,2)}}</td>
                    </tr>


                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="w-1/3">

        <div>
            <div  class="space-y-4 max-w-md mx-auto p-4">
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700">Entry</label>
                    <div class="mt-1">
                        <input
                            type="text"
                            id="content"
                            wire:model="content"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            placeholder="Enter your content"
                        >
                    </div>
                    @error('content')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button
                        wire:click="save"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-900 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Save Entry
                    </button>
                </div>

                @if (session()->has('message'))
                    <div class="rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    {{ session('message') }}
                                </p>
                            </div>
                        </div>
                    </div>
            @endif
        </div>


        <div class="space-y-4 max-w-md mx-auto p-4">
            <label for="entry" class="block text-sm font-medium text-gray-700">Select Entry</label>
            <select id="entry" wire:model="selectedEntryToSet" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="">Select an option</option>
                @foreach(DB::table('entries')->get() as $entryt)
                    <option value="{{ $entryt->id }}">{{ $entryt->content }}</option>
                @endforeach
            </select>

            @if($loading)
                <p class="mt-2 text-sm text-blue-500">Loading entries...</p>
            @elseif($entries->isEmpty())
                <p class="mt-2 text-sm text-red-500">No entries available to select.</p>
            @elseif($selectedEntry)
                <p class="mt-2 text-sm text-gray-500">You selected: {{ $entries->firstWhere('id', $selectedEntry)->content }}</p>
            @endif
        </div>


        <div class="space-y-4 max-w-md mx-auto p-4">
            <label for="entry" class="block text-sm font-medium text-gray-700">Select Account</label>
            <select id="entry" wire:model="selectedAccountToSet" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="">Select an option</option>
                @foreach(DB::table('accounts')->where('major_category_code','3000')->where('account_level', 2)->orderBy('id', 'asc')->get() as $entryt)
                    <option value="{{ $entryt->id }}">{{ $entryt->account_name }}</option>
                @endforeach
            </select>

            @if($loading)
                <p class="mt-2 text-sm text-blue-500">Loading entries...</p>
            @elseif($entries->isEmpty())
                <p class="mt-2 text-sm text-red-500">No entries available to select.</p>
            @elseif($selectedEntry)
                <p class="mt-2 text-sm text-gray-500">You selected: {{ $entries->firstWhere('id', $selectedEntry)->content }}</p>
            @endif
        </div>

        <div class="space-y-4 max-w-md mx-auto p-4">
            <label for="content" class="block text-sm font-medium text-gray-700">Amount</label>
            <div class="mt-1">
                <input
                    type="text"
                    id="content"
                    wire:model="amount"
                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    placeholder="Enter amount"
                >
            </div>
            @error('content')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-4 max-w-md mx-auto p-4">
            <button
                wire:click="saveAmount"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-900 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Save Amount
            </button>
        </div>

        @if (session()->has('message'))
            <div class="rounded-md bg-green-50 p-4 ">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('message') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif



    </div>

    </div>





    <script nonce="{{ csp_nonce() }}">

        const exportButton = document.getElementById("exportBtn");
        //dd(exportButton);

        exportButton.addEventListener("click", function () {

            //dd('hhhh');
            // Grab table rows and columns data
            const tableRows = document.querySelectorAll("table tr");
            let tableData = [];

            tableRows.forEach((row, index) => {
                let rowData = [];
                row.querySelectorAll("td, th").forEach(cell => {
                    rowData.push(cell.innerText.trim());
                });
                if (rowData.length) {
                    tableData.push(rowData);
                }
            });

           // window.Livewire.on('getContent', () => {

                window.Livewire.emit('generateExcel', tableData);
            //})

        });
    </script>

    <script nonce="{{ csp_nonce() }}">
        const exportButtonPDF = document.getElementById("exportBtnPDF");

        exportButtonPDF.addEventListener("click", function () {
            // Get the content of the div to print
            var content = document.getElementById('element-to-print').innerHTML;

            // Create a new window for printing
            var printWindow = window.open('', '', 'height=600,width=800');

            // Add content and style to the new window

            printWindow.document.write(`
            <style nonce="{{ csp_nonce() }}">
                body {
                    font-family: Arial, sans-serif;

                }
                .mx-auto {
                    margin-left: auto;
                    margin-right: auto;
                }
                .bg-white {
                    background-color: white;
                }
                .p-6 {
                    padding: 1.5rem;
                }
                .shadow-md {
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
                .text-center {
                    text-align: center;
                }
                .text-lg {
                    font-size: 1.125rem;
                }
                .font-bold {
                    font-weight: bold;
                }
                .uppercase {
                    text-transform: uppercase;
                }
                .text-base {
                    font-size: 1rem;
                }
                .font-semibold {
                    font-weight: 600;
                }
                .flex {
                    display: flex;
                }
                .justify-end {
                    justify-content: flex-end;
                }
                .mt-4 {
                    margin-top: 1rem;
                }
                .border {
                    border: 1px solid #000;
                }
                .border-black {
                    border-color: black;
                }
                .px-4 {
                    padding-left: 1rem;
                    padding-right: 1rem;
                }
                .py-2 {
                    padding-top: 0.5rem;
                    padding-bottom: 0.5rem;
                }
                .text-xs {
                    font-size: 1rem;
                }
                .text-right {
                    text-align: right;
                }
                .w-full {
                    width: 100%;
                }
                .text-left {
                    text-align: left;
                }
                .border-collapse {
                    border-collapse: collapse;
                }
                .text-sm {
                    font-size: 1rem;
                }
                .font-bold {
                    font-weight: bold;
                }
                .mt-6 {
                    margin-top: 1.5rem;
                }
                /* Button styles */
                .btn {
                    display: inline-block;
                    padding: 0.5rem 1rem;
                    font-size: 1rem;
                    font-weight: 600;
                    text-align: center;
                    cursor: pointer;
                    border-radius: 0.375rem;
                }
                .btn-primary {
                    background-color: #3490dc;
                    color: white;
                    border: none;
                }
                .btn-primary:hover {
                    background-color: #2779bd;
                }
                /* Table cell styles */
                td {
                    padding: 0.5rem 1rem;
                    border: 1px solid #ccc;
                    text-align: left;
                }
                .font-bold {
                    font-weight: bold;
                }
                .mt-6 {
                    margin-top: 1.5rem;
                }
                /* Responsive */
                @media print {
                    body {
                        font-size: 6px;
                    }
                    table, td, th {
                        font-size: 6px;
                    }
                }
            </style>
        `); // All CSS here

            // Write the content into the new window
            printWindow.document.write('</head><body>');
            printWindow.document.write(content);  // Add the div content
            printWindow.document.write('</body></html>');

            // Close the document to finish writing
            printWindow.document.close();

            // Trigger the print dialog
            printWindow.print();
        });
    </script>




</div>
