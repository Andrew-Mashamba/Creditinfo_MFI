<div class=" bg-white p-4 mb-4 ">

    <div class="container mx-auto mt-8   ">
        <h4 class="text-xl font-bold mb-4  flex justify-center item-center  p-4 "> LOAN PROVISION   {{ now()->format("Y-M-d") }}, NBC SACCOS LTD</h4>
        <div class="overflow-x-auto mt-4 ">

            <table class="min-w-full bg-white border border-gray-200 text-sm ">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200"> S/N </th>
                        <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Name </th>
                        <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200"> Loan Amount </th>
                        <th class="px-4 py-2 text-right bg-blue-100 border border-gray-200"> Last Payment </th>
                        <th class="px-4 py-2 text-right bg-blue-100 border border-gray-200"> PROVISION (PERCENTAGE)</th>
                        <th class="px-4 py-2 text-right bg-blue-100 border border-gray-200"> </th>
                        <th class="px-4 py-2 text-right bg-blue-100 border border-gray-200"> Amount Provided </th>



                    </tr>
                </thead>

                <tbody>
                    @foreach ($loan as  $data)


                    <tr>
                        <td class="px-4 py-2 border-blue-200  border ">{{ $loop->iteration }} </td>
                        <td class="px-4 py-2 border-blue-200 border "> {{ $data->name }} </td>
                        <td class="px-4 py-2 border-blue-200 border ">{{number_format( $data->loan_amount,2 ) }}  TZS</td>
                        <td class="px-4 py-2 border-blue-200 border "> {{ $data->date }} </td>
                        <td class="px-4 py-2 border-blue-200 border ">  {{ $data->provision_rate ?? 0  }}  % </td>
                        <td class="px-4 py-2 border-blue-200 border "> {{ number_format($data->out_standing_amount,2) }} TZS   </td>

                        <td class="px-4 py-2 text-right border-blue-200 border">

                        <button type="button" class="text-white bg-yellow-400 hover:bg-yellow-500 focus:outline-none focus:ring-4 focus:ring-yellow-300 font-medium rounded-full text-sm px-5 py-2.5 text-center me-2 mb-2 dark:focus:ring-yellow-900"> Cancel </button>
                        <button type="button" class="text-white bg-purple-700 hover:bg-purple-800 focus:outline-none focus:ring-4 focus:ring-purple-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-900"> Provide </button>

                        </td>
                    </tr>

                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-blue-100">
                        <td colspan="5" class="px-4 py-2 text-center font-bold border border-gray-200">Total</td>
                        <td colspan="2" class="px-4 py-2 text-right font-bold border border-gray-200"> {{ number_format($summary,2) }} TZS</td>
                    </tr>
                </tfoot>
            </table>


        </div>
    </div>


    <div class=" bg-white  mb-4 ">

        <div class="container mx-auto mt-8   ">
            <div class="overflow-x-auto mt-4 ">

                <table class="min-w-full bg-white border border-gray-200 text-sm ">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">  </th>
                            <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">  </th>
                            <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200"> </th>
                            <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">  </th>
                            <th class="px-4 py-2 text-right bg-blue-100 border border-gray-200"> JV./2023 </th>
                        </tr>

                        <tr>
                            <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200"> S/N </th>
                            <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200"> Kasma </th>
                            <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Folio</th>
                            <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200"> DR </th>


                            <th class="px-4 py-2 text-right bg-blue-100 border border-gray-200"> CR </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="px-4 py-2 border-blue-200  border "> 1 </td>
                            <td class="px-4 py-2 border-blue-200 border "> PROFIT AND LOSS </td>
                            <td class="px-4 py-2 border-blue-200 border "> </td>
                            <td class="px-4 py-2 border-blue-200 border "> </td>


                            <td class="px-4 py-2 text-right border-blue-200 border"> {{ number_format($summary,2) }} TZS</td>
                        </tr>

                        <tr>
                            <td class="px-4 py-2 border-blue-200  border "> 2 </td>
                            <td class="px-4 py-2 border-blue-200 border "> PROVISION FOR DOUBTFUL DEBT </td>
                            <td class="px-4 py-2 border-blue-200 border "> </td>
                            <td class="px-4 py-2 border-blue-200 border ">  {{ number_format($summary,2) }}   TZS   </td>


                            <td class="px-4 py-2 text-right border-blue-200 border">  </td>
                        </tr>



                    </tbody>

                </table>


            </div>
        </div>





    </div>





</div>
