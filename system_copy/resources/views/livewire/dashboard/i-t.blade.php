<div>
    {{-- Do your work, then step back. --}}


    <div class="w-full p-2" >

        <!-- Welcome banner -->
        <div class="flex justify-between p-4 overflow-hidden bg-white shadow-md sm:p-6 rounded-2xl shadow-gray-200" style="height: 116px;"  >


            <div class="w-full text-center m-2">
                <p class="text-2xl mt-2 border-b-2 border-b-blue-400 ">
                    {{DB::table('institutions')->where('id',auth()->user()->institution_id)->value('name')}}
                    {{--                        {{App\Models\institutions::where('institution_id', '1001')->value('name')}} --}}
                </p>
                <p class="text-2xl mt-1  border-b-blue-400 ">
                    {{DB::table('branches')->where('id',auth()->user()->branch)->value('name')}}  - BRANCH
                    {{--                        {{App\Models\institutions::where('institution_id', '1001')->value('name')}} --}}
                </p>
                <p class="m-4">

                    {{DB::table('institutions')->where('id',auth()->user()->institution_id)->value('region')}},

                    {{DB::table('institutions')->where('id',auth()->user()->institution_id)->value('wilaya')}}
                    {{--                        {{App\Models\institutions::where('institution_id', Session::get('institution'))->value('region')}}--}}
                    {{--                        , {{App\Models\institutions::where('institution_id', Session::get('institution'))->value('wilaya')}}  --}}

                </p>
            </div>

        </div>



    </div>


    <div class="flex w-full " >


        <!-- First section with a single card and 70% width -->
        <div class="w-3/4 p-2">
            <div class="bg-white w-full shadow-md rounded-2xl shadow-gray-200" style="height: 290px">
                <!-- Content for the card in the first section -->
                <div class="flex items-end">
                    <div class="p-4 lg:w-7/12">
                        <h5 class="text-xl font-semibold text-lighter-shade">Welcome {{Auth::user()->name}}!</h5>
                        <p class="mb-4">Here is the current status: </p>
                        <p class="mb-4">
                            Total Loan Applicants
                            <span class="font-bold">.              2000</span>
                        </p>
                        <p class="mb-4">
                            Active Loan
                            <span class="font-bold">.               1800 </span>

                        </p>

                        <p class="mb-4">
                            Loan Progress
                            <span class="font-bold">.                   High</span>
                        </p>

                    </div>
                    <div class="text-center lg:w-5/12 lg:text-left">

                        <div class="w-4/5 mx-auto overflow-hidden text-center"> <!-- Add "text-center" class to center the content -->
                            <div id="x" style="height: 280px;width: 280px;margin:auto;"> <!-- Add "margin:auto;" to center the div horizontally -->
                                <canvas
                                    data-te-chart="doughnut"
                                    data-te-dataset-label="Percentage"
                                    data-te-labels="['M', 'NM' , 'RT']"
                                    data-te-dataset-data="[10, 10, 40]"
                                    data-te-dataset-background-color="['rgba(15, 134, 186, 1)', 'rgba(158, 223, 247, 1)', 'rgba(180, 228, 252, 1)']"
                                    style="height: 100%;"
                                >
                                </canvas>
                            </div>
                        </div>

                    </div>
                </div>





            </div>
        </div>

        <!-- Second section with four cards in a grid formation and 30% width -->
        <div class="w-1/4 p-2" >

            <div class="p-4 bg-white rounded-lg shadow" style="height: 290px">
                <!-- Content for card 1 in the second section -->
                <div class="">
                    <!-- Content for card 1 in the second section -->
                    <div class="flex items-center justify-between mb-4">
                        <h5 class="text-lg font-semibold">System Information</h5>
                        <div class="relative inline-block text-left">


                        </div>
                    </div>
                    <ul class="p-0 m-0">
                        <li class="flex pb-1 mb-4">
                            <div class="flex-shrink-0 me-3 " >

                                <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-10 h-10 text-extra-light-shade">
                                    <path class="text-extra-light-shade" stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                                </svg>

                            </div>
                            <div class="">


                                <div class="max-w-lg ">

                                    <ul>
                                        <li>
                                            <strong>CPU Usage:</strong> {{ $cpuUsage ?? 'N/A' }}
                                        </li>
                                        <li>
                                            <strong>Used RAM:</strong> {{ $usedRam ?? 'N/A' }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <!-- Rest of the list items -->
                    </ul>
                </div>
            </div>


        </div>








    </div>



</div>
