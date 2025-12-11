<div>
    {{-- Be like water. --}}
    <div class="h-full overflow-y-auto px-6 md:px-auto">
        <div class="ml-12 md:ml-0 my-5 text-2xl font-semibold text-stone-700">
            My Payslip
        </div>

        <div class="grid grid-cols-1 md:grid-cols-10 gap-5">

            {{-- PAYSLIPS --}}
            <div class="md:col-span-12">

                {{-- Table header --}}
                <div class="flex justify-between my-4 space-x-4">
                    <p class="font-bold">Payslip History</p>
                </div>

                {{-- table --}}
                <div class="w-full overflow-hidden rounded-lg shadow-xs">
                    <div class="w-full overflow-x-auto">
                        <table class="w-full whitespace-no-wrap">
                            <thead>
                                <tr class="text-xs font-semibold tracking-wide text-left text-stone-500 uppercase border-b rounded-t-md bg-gray-50">
                                    <th class="px-2 md:px-4 py-3 text-left">Pay Date</th>
                                    <th class="px-2 md:px-4 py-3 text-right">Basic Pay</th>
                                    <th class="px-2 md:px-4 py-3 text-right">Gross Pay</th>
                                    <th class="px-2 md:px-4 py-3 text-right"> Deductions Pay</th>
                                    <th class="px-2 md:px-4 py-3 text-right">Deductions</th>
                                    <th class="px-2 md:px-4 py-3 text-right">Net Pay</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-x">
                         @foreach ($payroll_summary as  $value)

                                    <tr wire:click="selectPayslip(8)">
                                        <td class="px-2 md:px-4 py-3 align-top">
                                            <p class=" text-xs text-left text-stone-600 font-bold">
                                                {{-- {{ Carbon\Carbon::parse($payslip->payroll_period->payout_date)->format('M d, Y') }} --}}
                                            {{ $value->created_at->format('Y-m-d') }}
                                            </p>
                                        </td>

                                        <td class="px-2 md:px-4 py-3 align-top">
                                            <p class=" text-xs text-right text-stone-600 font-bold">
                                               {{ number_format($value,2) }} Tzs
                                            </p>
                                        </td>
                                        <td class="px-2 md:px-4 py-3 align-top">
                                            <p class=" text-xs text-right text-stone-600 font-bold">
                                                {{ number_format($basic_pay,2) }}
                                            </p>
                                        </td>
                                        <td class="px-2 md:px-4 py-3 align-top">
                                            <p class=" text-xs text-right text-stone-600 font-bold">
                                                ₱ 6,00,000
                                            </p>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{--  --}}
                </div>


            </div>
            {{--  --}}


            {{-- Payslip selected --}}
            <div class="col-span-12 md:-mt-10 my-6"> <br>

                @foreach ($payroll_summary as $summary )



                    <table class="col-span-12 ">
                            <tr>
                                <td class="px-4 text-xs font-bold text-stone-500 border border-stone-300">Pay Date</td>
                                <td class="px-4 text-xs font-bold text-stone-500 border border-stone-300">Payroll Period</td>

                                <td class="px-4 text-xs font-bold text-stone-500 border border-stone-300" colspan="1">Net Pay :</td>
                                <td class="px-4 text-xs font-bold text-stone-500 border border-stone-300" colspan="1"> {{ Number_format($summary->net_salary,2) }} TZS </td>

                            </tr>
                            <tr>
                                <td class="px-4 text-xs font-bold text-stone-500 border border-stone-300"> {{ $summary->payment_date }} </td>
                                <td class="px-4 text-xs font-bold text-stone-500 border border-stone-300">1 month</td>

                            </tr>
                            <tr class="">
                                <td colspan="7" class="h-6"></td>
                            </tr>
                            {{--  --}}
                            <tr>
                                <td class="border border-stone-300 text-xs text-stone-800 uppercase h-6 font-extrabold px-4" colspan="2">EARNINGS</td>
                                <td class="border border-stone-300 text-xs text-stone-800 uppercase h-6 font-extrabold px-4" colspan="1">HRS</td>
                                <td class="border border-stone-300 text-xs text-stone-800 uppercase h-6 font-extrabold px-4" colspan="2">AMOUNT</td>
                            </tr>
                            {{-- basic --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">Basic Pay</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" >{{  $summary->hours_worked }} </td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1"> {{ number_format( $summary->basic_pay,2) }} TZS  </td>
                            </tr>
                            {{-- ot --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">Overtime Pay</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" > {{  $summary->overtime_hours }}</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1">  </td>
                            </tr>
                            {{-- restday --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">Restday Pay</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" >  </td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1"> </td>
                            </tr>
                            {{-- restday ot --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">Restday OT Pay</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" >  </td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1">  </td>
                            </tr>
                            {{-- night differential --}}
                            {{-- @if($night_differential_pay != 0) --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">Night Differential Pay</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" >  </td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1">  </td>
                            </tr>
                            {{-- holiday pay --}}
                            {{-- @if($holiday_pay != 0) --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">Holiday Pay</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" ></td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1">  </td>
                            </tr>
                            {{-- @endif --}}
                            {{-- others pay --}}


<tr>
                                <td class="border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">Others</td>
                                <td class="border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" ></td>
                                <td class="border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1">   </td>
                            </tr>
                            {{-- gross pay --}}
                            <tr>
                                <td class="border border-stone-200 text-xs font-bold text-stone-900 h-6 px-4" colspan="2">Gross Pay</td>
                                <td class="border border-stone-200 text-xs font-bold text-stone-900 h-6 px-4 text-right" ></td>
                                <td class="border border-stone-200 text-xs font-bold text-stone-900 h-6 px-4 text-right" colspan="1"> {{  $summary->gross_salary }} </td>
                            </tr>
                            <tr class="">
                                <td colspan="7" class="h-6"></td>
                            </tr>
                            {{-- DEDUCTIONS --}}
                            <tr>
                                <td class="border border-stone-300 text-xs text-stone-800 uppercase h-6 font-extrabold px-4" colspan="2">DEDUCTIONS</td>
                                <td class="border border-stone-300 text-xs text-stone-800 uppercase h-6 font-extrabold px-4 text-right" colspan="1">HRS</td>
                                <td class="border border-stone-300 text-xs text-stone-800 uppercase h-6 font-extrabold px-4" colspan="2">AMOUNT</td>
                            </tr>
                            {{-- late pay --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">Late</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" > </td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1">  </td>
                            </tr>
                            {{-- undertime pay --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">Undertime</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" > </td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1">  </td>
                            </tr>
                            {{-- absences pay --}}
                            {{-- @if($absences != 0) --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">Absences</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" ></td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1"> </td>
                            </tr>
                            {{-- @endif --}}
                            {{-- cash_advance pay --}}
                            {{-- @if($cash_advance != 0) --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">Cash Advance Pay</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" ></td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1"> </td>
                            </tr>
                            {{-- @endif --}}
                            {{-- sss_loan pay --}}
                            {{-- @if($sss_loan != 0) --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">SSS Loan</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" ></td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1">  </td>
                            </tr>
                            {{-- @endif --}}
                            {{-- hdmf_loan pay --}}
                            {{-- @if($hdmf_loan != 0) --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">HMDF Loan</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" ></td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1">  </td>
                            </tr>
                            {{-- @endif --}}
                            {{-- sss pay --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">SSS</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" ></td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1">  </td>
                            </tr>
                            {{-- phic pay --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">PHIC</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" ></td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1">  </td>
                            </tr>
                            {{-- hdmf pay --}}
                            <tr>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" colspan="2">Other Deductions</td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4" ></td>
                                <td class="border-b border-x border-stone-200 text-xs font-semibold text-stone-600 h-6 px-4 text-right" colspan="1">{{ number_format( $summary->other_deductions,2) }} TZS   </td>
                            </tr>
                            {{-- deductions pay --}}
                            <tr>
                                <td class="border border-stone-200 text-xs font-bold text-stone-900 h-6 px-4" colspan="2">Total Deductions</td>
                                <td class="border border-stone-200 text-xs font-bold text-stone-900 h-6 px-4 text-right" ></td>
                                <td class="border border-stone-200 text-xs font-bold text-stone-900 h-6 px-4 text-right" colspan="1">{{  number_format( $summary->total_deductions) }} </td>
                            </tr>
                    </table>
                    @endforeach

                    <div>
                        <button wire:click="downloadPayslip" class="cursor-pointer text-blue-500 text-xs font-semibold pt-5">
                            Download Payslip  <i class="ml-2 fa-solid fa-download"></i>
                        </button>
                    </div>
            </div>
            {{--  --}}

        </div>
    </div>
</div>
