 <div>
    {{-- Close your eyes. Count to one. That is how long forever feels. --}}



            @foreach($values as $value)

                    @if($this->loanId==$value->loan_id)


{{--                        @if($this->selectPaymentSchedule==false || $this->selectPaymentSchedule==true)--}}
{{--                        @endif--}}

             @if($this->selectPaymentSchedule==false)
                 <div class="flex space-x-4">
                     <tr>
                         <td>
                             <button   wire:click="Installment" class="inline-flex @if($this->selectPaymentSchedule==false) bg-customPurple text-white @endif items-center px-4 py-2 text-sm font-medium text-gray-900  border border-gray-900 rounded-l-lg hover:bg-customPurple hover:text-white focus:z-0 focus:ring-2 focus:ring-red-500 focus:bg-red-900 focus:text-white dark:border-white dark:text-white dark:hover:text-white dark:hover:bg-red-700 dark:focus:bg-red-700">
                                 <svg class="w-3 h-3 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                     <path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z"/>
                                 </svg>
                                 Installment
                             </button>

                         </td>
                         <td>
                             <button  wire:click="LoanRepayment" class="inline-flex @if($this->selectPaymentSchedule) bg-customPurple text-white @endif items-center px-4 py-2 text-sm font-medium text-gray-900 bg-transparent border border-gray-900 rounded-r-md hover:bg-customPurple hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-gray-900 focus:text-white dark:border-white dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:bg-gray-700">
                                 <svg class="w-3 h-3 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                     <path d="M14.707 7.793a1 1 0 0 0-1.414 0L11 10.086V1.5a1 1 0 0 0-2 0v8.586L6.707 7.793a1 1 0 1 0-1.414 1.414l4 4a1 1 0 0 0 1.416 0l4-4a1 1 0 0 0-.002-1.414Z"/>
                                     <path d="M18 12h-2.55l-2.975 2.975a3.5 3.5 0 0 1-4.95 0L4.55 12H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2Zm-3 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"/>
                                 </svg>
                                 Payment history
                             </button>

                         </td>
                         <td>
                             <button  wire:click="downloadExcel({{$value->loan_id}})"  type="button" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 hover:text-red-100 focus:z-10 focus:ring-2 focus:ring-red-100 focus:text-red-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-red-100 dark:focus:ring-red-500 dark:focus:text-white">
                                 <svg class="w-3 h-3 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                     <path d="M14.707 7.793a1 1 0 0 0-1.414 0L11 10.086V1.5a1 1 0 0 0-2 0v8.586L6.707 7.793a1 1 0 1 0-1.414 1.414l4 4a1 1 0 0 0 1.416 0l4-4a1 1 0 0 0-.002-1.414Z"></path>
                                     <path d="M18 12h-2.55l-2.975 2.975a3.5 3.5 0 0 1-4.95 0L4.55 12H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2Zm-3 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"></path>
                                 </svg>
                                 Downloads Excel
                             </button>
                         </td>
                         <td>
{{--                             <button type="button" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-r-md hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-red-500 dark:focus:text-white">--}}
{{--                                 <svg class="w-3 h-3 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">--}}
{{--                                     <path d="M14.707 7.793a1 1 0 0 0-1.414 0L11 10.086V1.5a1 1 0 0 0-2 0v8.586L6.707 7.793a1 1 0 1 0-1.414 1.414l4 4a1 1 0 0 0 1.416 0l4-4a1 1 0 0 0-.002-1.414Z"></path>--}}
{{--                                     <path d="M18 12h-2.55l-2.975 2.975a3.5 3.5 0 0 1-4.95 0L4.55 12H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2Zm-3 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"></path>--}}
{{--                                 </svg>--}}
{{--                                 Downloads PDF--}}
{{--                             </button>--}}
                         </td>
                         <td>
                             <button wire:click="closeInstallment({{$this->loanId}})" class="mr-2">

                                 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                     <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                 </svg>
                             </button>
                         </td>
                     </tr>

                 </div>

                          <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-xs border border-gray-200">


                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-2 py-1 text-left border-b border-r border-gray-200">
                                    Installment
                                </th>
                                <th scope="col" class="px-2 py-1 text-right border-b border-r border-gray-200">
                                    Interest
                                </th>
                                <th scope="col" class="px-2 py-1 text-right border-b border-r border-gray-200">
                                    Principle
                                </th>
                                <th scope="col" class="px-2 py-1 text-left border-b border-r border-gray-200">
                                    Installment Date
                                </th>
                                <th scope="col" class="px-2 py-1 text-right border-b border-r border-gray-200">
                                    Payment
                                </th>

                                <th scope="col" class="px-2 py-1 text-right border-b border-r border-gray-200">
                                    Penalties
                                </th>
                                <th scope="col" class="px-2 py-1 text-left border-b border-gray-200">
                                    Status
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($this->installments as  $installment)
                            <tr class="border-b">
                                <td class="px-2 py-1 border-r border-gray-200 font-semibold text-blue-900">
                                    {{round($installment->installment,2)}} TZS
                                </td>

                                <td class="px-2 py-1 text-right border-r border-gray-200">
                                    {{round($installment->interest,2)}}  TZS
                                </td>

                                <td class="px-2 py-1 text-right border-r border-gray-200">
                                    {{round($installment->principle,2)}} TZS
                                </td>
                                <td class="px-2 py-1 border-r border-gray-200">
                                    {{$installment->installment_date}}
                                </td>

                                    <td class="px-2 py-1 text-right border-r border-gray-200">
                                    {{$installment->payment}}
                                </td>
                                    <td class="px-2 py-1 text-right border-r border-gray-200">
                                    {{round($installment->penaties,2)}} TZS
                                </td>

                                    <td class="px-2 py-1">
                                    {{$installment->completion_status}}
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        
                        <!-- Summary Table -->
                        <table class="w-full text-xs border border-gray-200 mt-4">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-2 py-1 text-right border-b border-r border-gray-200"> TOTAL INSTALLMENT </th>
                                <th scope="col" class="px-2 py-1 text-right border-b border-r border-gray-200"> TOTAL INTEREST </th>
                                <th scope="col" class="px-2 py-1 text-right border-b border-r border-gray-200"> TOTAL PRINCIPLE </th>
                                <th scope="col" class="px-2 py-1 text-right border-b border-r border-gray-200"> TOTAL PENALTIES </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="bg-gray-50 font-semibold">
                              <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-blue-900"> {{round( $this->totalInstallment,2)}} TZS</td>
                              <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-blue-900"> {{round( $this->totalinterest,2)}} TZS </td>
                              <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-blue-900"> {{round( $this->totalPrinciple,2)}} TZS  </td>
                              <td class="px-2 py-1 text-right border-gray-200 font-semibold text-blue-900"> {{round( $this->totalPenalties,2)}} TZS  </td>
                            </tr>
                            </tbody>
                        </table>
                            </tbody>
                        </table>
                    </div>
             @else
                 <div class="flex space-x-4">
                     <tr>
                         <td> <button   wire:click="Installment" class="inline-flex @if($this->selectPaymentSchedule==false) bg-customPurple text-white @endif items-center px-4 py-2 text-sm font-medium text-gray-900 bg-transparent border border-gray-900 rounded-l-lg hover:bg-customPurple hover:text-white focus:z-10 focus:ring-2 focus:ring-red-500 focus:bg-red-900 focus:text-white dark:border-white dark:text-white dark:hover:text-white dark:hover:bg-red-700 dark:focus:bg-red-700">
                                 <svg class="w-3 h-3 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                     <path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z"/>
                                 </svg>
                                 Installment
                             </button> </td>
                         <td>                 <button  wire:click="LoanRepayment" class="inline-flex @if($this->selectPaymentSchedule) bg-customPurple text-white @endif items-center px-4 py-2 text-sm font-medium text-gray-900  border border-gray-900 rounded-r-md hover:bg-customPurple hover:text-white focus:z-0 focus:ring-2 focus:ring-gray-500 focus:bg-red-900 focus:text-white dark:border-white dark:text-white dark:hover:text-white  dark:focus:bg-gray-700">
                                 <svg class="w-3 h-3 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                     <path d="M14.707 7.793a1 1 0 0 0-1.414 0L11 10.086V1.5a1 1 0 0 0-2 0v8.586L6.707 7.793a1 1 0 1 0-1.414 1.414l4 4a1 1 0 0 0 1.416 0l4-4a1 1 0 0 0-.002-1.414Z"/>
                                     <path d="M18 12h-2.55l-2.975 2.975a3.5 3.5 0 0 1-4.95 0L4.55 12H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2Zm-3 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"/>
                                 </svg>
                                 Payment history
                             </button>
                         </td>
                         <td>                         <button  wire:click="downloadExcel(12)"  type="button" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 hover:text-red-100 focus:z-10 focus:ring-2 focus:ring-red-100 focus:text-red-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-red-100 dark:focus:ring-red-500 dark:focus:text-white">
                                 <svg class="w-3 h-3 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                     <path d="M14.707 7.793a1 1 0 0 0-1.414 0L11 10.086V1.5a1 1 0 0 0-2 0v8.586L6.707 7.793a1 1 0 1 0-1.414 1.414l4 4a1 1 0 0 0 1.416 0l4-4a1 1 0 0 0-.002-1.414Z"></path>
                                     <path d="M18 12h-2.55l-2.975 2.975a3.5 3.5 0 0 1-4.95 0L4.55 12H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2Zm-3 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"></path>
                                 </svg>
                                 Downloads Excel
                             </button>
                         </td>

                         <td>
{{--                             <button type="button" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-r-md hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-red-500 dark:focus:text-white">--}}
{{--                                 <svg class="w-3 h-3 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">--}}
{{--                                     <path d="M14.707 7.793a1 1 0 0 0-1.414 0L11 10.086V1.5a1 1 0 0 0-2 0v8.586L6.707 7.793a1 1 0 1 0-1.414 1.414l4 4a1 1 0 0 0 1.416 0l4-4a1 1 0 0 0-.002-1.414Z"></path>--}}
{{--                                     <path d="M18 12h-2.55l-2.975 2.975a3.5 3.5 0 0 1-4.95 0L4.55 12H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2Zm-3 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"></path>--}}
{{--                                 </svg>--}}
{{--                                 Downloads PDF--}}
{{--                             </button>--}}
                         </td>


                         <td class="justify-end flex item-end">
                             <button wire:click="closeInstallment({{session()->get('clientLoanId')}})" class="mr-2">
                                 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                     <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                 </svg>
                             </button>
                         </td>
                     </tr>

                 </div>


                 <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                     <table class="w-full text-sm text-left text-black dark:text-red-100">
                         <thead class="text-xs text-black uppercase bg-red-100 dark:text-black">
                         <tr>
                             <th scope="col" class="px-6 py-3">
                                 Loan Product
                             </th>
                             <th scope="col" class="px-6 py-3">
                                 Member Acc No
                             </th>
                             <th scope="col" class="px-6 py-3">
                                 Destination Acc No
                             </th>
                             <th scope="col" class="px-6 py-3">
                                 Payment Amount
                             </th>
                             <th scope="col" class="px-6 py-3">
                                 Running Balance
                             </th>
                         </tr>
                         </thead>
                         <tbody>

                          @if($this->repayment==null)
                          @else
                         @foreach($this->repayment as $payment)
                                 <tr class="bg-red-100 border-b border-red-500">
                                     <th scope="row" class="px-6 py-4 font-medium text-red-50 whitespace-nowrap dark:text-red-100">
                                         {{$this->product_name}}
                                     </th>
                                     <td class="px-6 py-4">
                                         {{$payment->record_on_account_number}}
                                     </td>
                                     <td class="px-6 py-4">
                                         {{$payment->record_on_account_number}}
                                     </td>
                                     <td class="px-6 py-4">
                                         {{$payment->debit}}
                                     </td>
                                     <td class="px-6 py-4">
                                         {{$payment->record_on_account_number_balance}}
                                     </td>
                                 </tr>
                         @endforeach
                          @endif
                         </tbody>
                     </table>
                 </div>



             @endif


                       @else

            <div class="flex justify-center item-center" wire:click="viewInstallment({{$value->loan_id}})">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>

            </div>

                @endif



             @endforeach





</div>
