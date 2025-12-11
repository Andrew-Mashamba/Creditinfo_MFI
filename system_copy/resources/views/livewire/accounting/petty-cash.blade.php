<div class="bg-white p-4 ">
    <div class="flex justify-between">
        <div class="inline-flex rounded-md shadow-sm" role="group">
            <button type="button" wire:click="changeMenu(1)"
                class="inline-flex  items-center px-4 py-2 text-sm font-medium  @if ($this->action_button == 1) bg-blue-900 text-white hover:bg-blue-800 @else  text-gray-900 bg-transparent border border-gray-900 hover:bg-gray-900 @endif  rounded-s-lg  hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-blue-900 focus:text-white dx:border-white dx:text-white dx:hover:text-white dx:hover:bg-gray-700 dx:focus:bg-gray-700 ">
                <svg class="w-3 h-3 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M14.707 7.793a1 1 0 0 0-1.414 0L11 10.086V1.5a1 1 0 0 0-2 0v8.586L6.707 7.793a1 1 0 1 0-1.414 1.414l4 4a1 1 0 0 0 1.416 0l4-4a1 1 0 0 0-.002-1.414Z" />
                    <path
                        d="M18 12h-2.55l-2.975 2.975a3.5 3.5 0 0 1-4.95 0L4.55 12H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2Zm-3 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z" />
                </svg>

                Top Up
            </button>
            <button type="button" wire:click="changeMenu(2)"
                class="inline-flex  items-center px-4 py-2 text-sm font-medium  @if ($this->action_button == 2) bg-blue-900 text-white hover:bg-blue-800 @else  text-gray-900 bg-transparent border border-gray-900 hover:bg-gray-900 @endif  rounded-e-lg   hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-blue-900 focus:text-white dx:border-white dx:text-white dx:hover:text-white dx:hover:bg-gray-700 dx:focus:bg-gray-700 ">
                <svg class="w-3 h-3 me-2" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor"
                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941">
                    </path>
                </svg>
                CashOut
            </button>
            </div>
            <div id="date-range-picker" date-rangepicker class="flex items-center">
        </div>
    </div>


    <div class=" flex w-full mt-4  bg-white  justify-between mx-auto">
        @if ($action_button == 1)
            <div class="w-1/2 metric-card   border bg-white  border-gray-200  rounded-lg p-4 ">
                @if (session('message'))
                    <div id="alert-border-1"
                        class="flex items-center p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-blue-50 dx:text-blue-400 dx:bg-gray-800 dx:border-blue-800"
                        role="alert">
                        <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                        </svg>
                        <div class="ms-3 text-sm font-medium">
                            {{ session('message') }}
                        </div>

                        <button type="button"
                            class="ms-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex items-center justify-center h-8 w-8 dx:bg-gray-800 dx:text-blue-400 dx:hover:bg-gray-700"
                            data-dismiss-target="#alert-border-1" aria-label="Close">
                            <span class="sr-only">Dismiss</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                        </button>


                    </div>
                @endif

                @if (session()->has('message_fail'))

                <div id="alert-border-1"
                        class="flex items-center p-4 mb-4 text-red-800 border-t-4 border-red-300 bg-red-50 dx:text-blue-400 dx:bg-gray-800 dx:border-blue-800"
                        role="alert">
                        <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                        </svg>
                        <div class="ms-3 text-sm font-medium">
                            {{ session('message_fail') }}
                        </div>
                        <button type="button"
                            class="ms-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex items-center justify-center h-8 w-8 dx:bg-gray-800 dx:text-blue-400 dx:hover:bg-gray-700"
                            data-dismiss-target="#alert-border-1" aria-label="Close">
                            <span class="sr-only">Dismiss</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                        </button>

                </div>


                @endif

                <div>
                    <div>
                    </div>

                    <div class="">
                        <p for="bank" class="block mb-2 text-sm font-medium text-slate-600 ">
                            Source Account
                        </p>
                        @error('source_account_id')
                            <div class="text-red-500 text-xs"> {{ $message }} </div>
                        @enderror
                        <select wire:model.bounce="source_account_id" name="deposit_type" id="deposit_type"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  ">
                            <option value="">Select</option>
                            @foreach ($this->source_account as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->account_name . ' - ' . $account->account_number }}</option>
                            @endforeach
                        </select>
                        <div class="mt-2"></div>
                        <p for="" class="block mb-2 text-sm font-medium text-gray-900 ">
                            Destination Account </p>
                        @error('destination_account_id')
                            <div class="text-red-500 text-xs"> {{ $message }} </div>
                        @enderror
                        <select wire:model="destination_account_id"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  ">
                            <option value="">Select</option>
                            @foreach ($this->destination_account as $accountx)
                                <option value="{{ $accountx->id }}">
                                    {{ $accountx->account_name . ' - ' . $accountx->account_number }}</option>
                            @endforeach

                        </select>
                        <div class="mt-2"></div>
                        <p for="bank" class="block mb-2 text-sm font-medium text-slate-600 ">
                            Enter Amount
                        </p>
                        @error('amount')
                            <div class="text-red-500 text-xs"> {{ $message }} </div>
                        @enderror


                        <input id="amount3" type="number" name="amount"
                            class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                            wire:model.bounce="amount" autofocus="">

                        <div class="mt-2"></div>

                        <div class="mt-2"></div>

                    </div>

                    <hr class="border-b-0 my-2">

                    <div class="flex justify-end w-auto">
                        <div wire:loading="" wire:target="depositPettyAccount">
                            <button
                                class="text-white bg-red-700 hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 "
                                disabled="">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="animate-spin  h-5 w-5 mr-2 stroke-white-800" fill="white"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                                        </path>
                                    </svg>
                                    <p>Please wait...</p>
                                </div>
                            </button>
                        </div>

                    </div>

                    <div class="flex justify-end w-auto">
                        <div wire:loading.remove="" wire:target="depositPettyAccount">
                            <button wire:click="depositPettyAccount"
                                class="text-white justify-end item-end flex mt-4 bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 ">
                                <p class="text-white"> Top Up</p>
                            </button>

                        </div>
                    </div>

            </div>
        @endif



        @if ($action_button == 2)

            <div class="w-1/2 metric-card   border bg-white  border-gray-200  rounded-lg p-4 ">

                @if (session('message'))
                    <div id="alert-border-1"
                        class="flex items-center p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-blue-50 dx:text-blue-400 dx:bg-gray-800 dx:border-blue-800"
                        role="alert">
                        <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                        </svg>
                        <div class="ms-3 text-sm font-medium">
                            {{ session('message') }}
                        </div>
                        <button type="button"
                            class="ms-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex items-center justify-center h-8 w-8 dx:bg-gray-800 dx:text-blue-400 dx:hover:bg-gray-700"
                            data-dismiss-target="#alert-border-1" aria-label="Close">
                            <span class="sr-only">Dismiss</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                        </button>
                    </div>
                @endif


                @if (session()->has('message_fail'))
                    <div id="alert-border-1"
                        class="flex items-center p-4 mb-4 text-red-800 border-t-4 border-red-300 bg-red-50 dx:text-blue-400 dx:bg-gray-800 dx:border-blue-800"
                        role="alert">
                        <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                        </svg>
                        <div class="ms-3 text-sm font-medium">
                            {{ session('message_fail') }}
                        </div>
                        <button type="button"
                            class="ms-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex items-center justify-center h-8 w-8 dx:bg-gray-800 dx:text-blue-400 dx:hover:bg-gray-700"
                            data-dismiss-target="#alert-border-1" aria-label="Close">
                            <span class="sr-only">Dismiss</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                        </button>
                    </div>
                @endif


                    <div class="">
                        <p for="bank" class="block mb-2 text-sm font-medium text-slate-600 ">
                            Source Account

                        </p>
                        @error('source_account_id')
                            <div class="text-red-500 text-xs"> {{ $message }} </div>
                        @enderror
                        <select wire:model.bounce="source_account_id" name="deposit_type" id="deposit_type"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  ">
                            <option value="">Select</option>
                            @foreach ($this->destination_account as $accountx)
                                <option value="{{ $accountx->id }}">
                                    {{ $accountx->account_name . ' - ' . $accountx->account_number }}</option>
                            @endforeach



                        </select>
                        <div class="mt-2"></div>
                        <p for="" class="block mb-2 text-sm font-medium text-gray-900 ">
                            Destination Account </p>
                        @error('destination_account_id')
                            <div class="text-red-500 text-xs"> {{ $message }} </div>
                        @enderror
                        <select wire:model="destination_account_id"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  ">
                            <option value="">Select</option>
                            @foreach ($this->source_account as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->account_name . ' - ' . $account->account_number }}</option>
                            @endforeach

                        </select>
                        <div class="mt-2"></div>
                        <p for="bank" class="block mb-2 text-sm font-medium text-slate-600 ">
                            Enter Amount
                        </p>
                        @error('amount')
                            <div class="text-red-500 text-xs"> {{ $message }} </div>
                        @enderror

                        <input id="amount3" type="number" name="amount"
                            class="w-full border-gray-300  focus:border-red-500 focus:ring rounded-lg focus:ring-red-200 focus:ring-opacity-50 p-2.5 rounded-md shadow-sm text-sm"
                            wire:model.bounce="amount" autofocus="">

                        <div class="mt-2"></div>



                        <div class="mt-2"></div>

                    </div>

                    <hr class="border-b-0 my-2">

                    <div class="flex justify-end w-auto">
                        <div wire:loading="" wire:target="discharge">
                            <button
                                class="text-white bg-red-700 hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 "
                                disabled="">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="animate-spin  h-5 w-5 mr-2 stroke-white-800" fill="white"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                                        </path>
                                    </svg>
                                    <p>Please wait...</p>
                                </div>
                            </button>
                        </div>

                    </div>

                    <div class="flex justify-end w-auto">
                        <div wire:loading.remove="" wire:target="discharge">
                            <button wire:click="discharge"
                                class="text-white justify-end item-end flex mt-4 bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 ">
                                <p class="text-white"> CashOut </p>
                            </button>

                        </div>
                    </div>

            </div>
        @endif



    </div>



    <div class="relative">

        <div class="flex w-full">
            <div id="petty_graph" class="bg-gray-100 p-4  mt-2  overflow-hidden" style="height: 200px; width: 90%;">

            </div>
        </div>

        <div
            class="grid grid-cols-1 sm:grid-cols-2 p-4 my-auto border-blue-800 border-2 md:grid-cols-2 gap-6 pb-4 bg-gray-50 rounded-xl p-4 mt-4">

            <div>
                <label for="hisa" class="block text-sm font-medium text-gray-700"> Total Balance </label>
                <div class="text-sm font-medium text-gray-500"> {{ number_format($total_balance, 2) }} TZS </div>
            </div>

            <div>
                <label for="akiba" class="block text-sm font-medium text-gray-700"> Expenses </label>
                <div class="text-sm font-medium text-gray-500"> {{ number_format($total_expenses, 2) }} TZS </div>
            </div>

            <div>
                <label for="amana" class="block text-sm font-medium text-gray-700"> Date </label>
            </div>


            <div>
                <label for="amana" class="block text-sm font-medium text-gray-700"> 22-January-2024 to Today
                </label>
            </div>








        </div>

        </div>

    </div>





<hr class="border-2 border-blue-900 mt-4 w-full" />
<div class="fon-semibold  mt-4 border-1 border-blue-900 mb-4  uppercase">

    TRansaction Summary
</div>

<div class="w-full overflow-x-auto mt-10">

    <livewire:accounting.petty-cash-table />

</div>

{{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}



<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script nonce="{{ csp_nonce() }}">
    //document.addEventListener('DOMContentLoaded', function () {
    var options = {
        chart: {
            type: 'donut',
            height: 250,
            width: '90%'
        },


        series: [
            {{ $pettySummary['balance'] }},
            {{ $pettySummary['expenses'] }},

        ],
        labels: [
            'Balance',
            'Expenses',

        ],
        colors: [
            '#2D3D6F', // Lighter Blue
            '#FC0314'
        ],
        title: {
            text: 'Petty Cash Summary',



        }
    };

    var chart = new ApexCharts(document.querySelector("#petty_graph"), options);
    chart.render();
    //});
</script>




</div>
