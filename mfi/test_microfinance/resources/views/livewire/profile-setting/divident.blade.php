<div>
    {{-- A good traveler has no fixed plans and is not intent upon arriving. --}}

    <div class="bg-white p-4 " >

        @if (session()->has('message_fail'))
            {{--                                                            @if (session()->has('alert-class'))--}}
            <div class="bg-red-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8"
                 role="alert">
                <div class="flex">
                    <div class="py-1">
                        <svg class="fill-current h-6 w-6 text-teal-500 mr-4"
                             xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 20 20">
                            <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold">The process has failed
                        </p>
                        <p class="text-sm">{{ session('message_fail') }} </p>
                    </div>
                </div>
            </div>
    @endif


    <!-- Content -->
            <div>

                <div class="flex text-sm font-semibold text-black-600 spacing-sm w-full justify-between">
                    <div class="p-2  ">
                        <label> START OF YEAR DATE</label>

                        <input datepicker="" datepicker-autohide="" disabled  type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full pl-10 p-2.5      flatpickr-input" placeholder="{{$this->start_of_year}}">
                    </div>
                    <div class="p-2  ">
                        <label> END OF YEAR DATE</label>

                        <input datepicker="" datepicker-autohide="" disabled  type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full pl-10 p-2.5      flatpickr-input" placeholder="{{$this->end_of_year}}">
                    </div>
                </div>


            </div>




    </div>





<div class="w-full mt-3 bg-gray-100 flex space-x-2">

<div class="w-1/2 p-2 mt-2 ml-2 mb-2 metric-card  text-sm font-semibold spacing-sm text-slate-600  border bg-white  border-gray-200  rounded-lg p-4 max-w-72">
        <table class="w-full">
        <tr>
            <td> CURRENT DATE</td>
            <td class="float-right"> {{now()}}</td>
        </tr>

        <tr>
            <td> INCOME UP TO DATE</td>
            <td class="float-right">
               {{ number_format($this->income)}} TZS</td>
        </tr>
         <tr>
            <td> EXPENSES UP TO DATE</td>
             <td class="float-right">
               {{ number_format($this->expenses)}}  TZS</td>
        </tr>

        <tr>
            <td> GROSS PROFIT UP TO DATE</td>
            <td class="float-right">
       {{ number_format($this->income - $this->expenses)}}TZS</td>
        </tr>


         <tr class="mb-2">
            <td>DIVIDEND  AMOUNT</td>
            <td>
                <div class="w-full max-w-md mx-auto">
                    <input type="number" id="input" wire:model="dividend_amount" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full pl-10 p-2.5      flatpickr-input" >
                </div>
            </td>
        </tr>
        <tr>
            <td>  CARRY FORWARD AMOUNT </td>
            <td>
                <div class="w-full max-w-md mx-auto justify-end flex">
                    <input type="text"  disabled value="{{number_format($this->carry_amount)}}TZS" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full pl-10 p-2.5      flatpickr-input" >
                </div>
            </td>
        </tr>
         <tr>
             <td></td>
             <td class="float-right flex mt-3 ">
                 <x-jet-button wire:click="endOfYear">END YEAR</x-jet-button>
             </td>
         </tr>
        </table>
    </div>


<div class="w-1/2 mt-2 ml-2 mb-2 metric-card   border bg-white  border-gray-200  rounded-lg p-4 max-w-72">



    </div>

</div>


    <script nonce="{{ csp_nonce() }}">
        flatpickr('[datepicker]', {
            dateFormat: "Y-m-d"
            , autoHide: true
            , allowInput: true
            , minDate: "2000-01-01"
            , maxDate: new Date().fp_incr(14)
        });
    </script>

</div>

