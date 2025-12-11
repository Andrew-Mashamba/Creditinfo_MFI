<div>

        <div  class="w-full bg-white p-8" >
            @php
                $score = \Illuminate\Support\Facades\DB::table('scores')
                    ->where('client_id', 111222333)
                    ->first();
            @endphp


            <!-- Scoring Display -->
            <div class="w-full flex justify-center items-center mt-8">
                <div class="bg-[#2D3D88] rounded-full flex justify-center items-center" style="width: 150px; height: 150px;">
                    <div class="bg-white rounded-full flex justify-center items-center" style="width: 140px; height: 140px;">
                        <div class="font-bold text-[#2D3D88] text-7xl">{{ $score->grade }}</div>
                    </div>
                </div>
            </div>

            <!-- Scoring Info -->
            <div class="p-4 flex justify-between text-xs w-full">
                <div class="w-1/3 flex flex-col justify-center items-center text-center">
                    <dt class="font-bold">Scoring Date</dt>
                    <dd>{{ $score->date }}</dd>
                </div>
                <div class="w-1/3 flex flex-col justify-center items-center text-center">
                    <dt class="font-bold text-2xl text-red-600">Score</dt>
                    <dd class="font-bold text-2xl text-red-600">{{ $score->score }}</dd>
                    <dd class="font-bold text-2xl text-red-600">Missing Information</dd>
                </div>
                <div class="w-1/3 flex flex-col justify-center items-center text-center">
                    <dt class="font-bold">Trend</dt>
                    <dd>{{ $score->trend }}</dd>
                </div>
            </div>


            <div class="p-4 border-b">
                <h2 class="text-sm font-bold">Score Trend</h2>

            </div>

            <div class="mt-2 p-4 ">
                <div id="chart"></div>

            </div>



            <!-- Active and Closed Contracts -->
            @php
                $records = \Illuminate\Support\Facades\DB::table('query_responses')
                    ->where('CheckNumber', 111222333)
                    ->get();
            @endphp

            <div class="p-4 border-b">
                <h2 class="text-sm font-bold">Active and Closed Contracts</h2>
            </div>

            <!-- Contracts List -->
            @foreach($records as $record)
                @php
                    $response_data = json_decode($record->response_data, true);
                    $contracts = $response_data['response']['CustomReport']['Contracts']['ContractList']['Contract'] ?? [];
                @endphp

                <div class="p-4 space-y-4 w-full">
                    <div class="p-2 space-y-4 bg-gray-100 rounded-3xl w-full">
                        @forelse($contracts as $key=>$contract)
                            <div class="bg-white p-4 rounded-3xl">

                                <div class="bg-red-600 text-white font-bold px-4 py-2 rounded-full mb-2 mt-2 inline-block">
                                    {{(int)$key + 1}}
                                </div>
                                <div class="flex justify-between">
                                    <div>
                                        <p class="text-sm font-bold">Contract Details</p>
                                        <p class="text-xs"><strong >Subscriber:</strong> {{ $contract['Subscriber'] }}</p>
                                        <p class="text-xs"><strong >Start Date:</strong> {{ \Carbon\Carbon::parse($contract['StartDate'])->format('Y-m-d') }}</p>
                                        <p class="text-xs"><strong >Real End Date:</strong> {{ \Carbon\Carbon::parse($contract['RealEndDate'])->format('Y-m-d') }}</p>
                                        <p class="text-xs"><strong >Expected End Date:</strong> {{ \Carbon\Carbon::parse($contract['ExpectedEndDate'])->format('Y-m-d') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs"><strong>Total Amount:</strong> {{ number_format($contract['TotalAmount']['Value'], 2) }} {{ $contract['TotalAmount']['Currency'] }}</p>
                                        <p class="text-xs"><strong>Past Due Days:</strong> {{ $contract['PastDueDays'] }}</p>
                                        <p class="text-xs"><strong>Contract Status:</strong> {{ $contract['ContractStatus'] }}</p>
                                        <p class="text-xs"><strong>Role Of Client:</strong> {{ $contract['RoleOfClient'] }}</p>
                                    </div>
                                </div>

                                <!-- Payment Calendar -->
                                <div class="mt-4">
                                    <p class="text-sm font-semibold">Payment Calendar</p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($contract['PaymentCalendarList']['CalendarItem'] as $calendarItem)
                                            <div class="bg-gray-100 p-2 rounded-lg">
                                                <p class="text-xs"><strong>Date:</strong> {{ \Carbon\Carbon::parse($calendarItem['Date'])->format('Y-m-d') }}</p>
                                                <p class="text-xs"><strong>Delinquency Status:</strong> {{ $calendarItem['DelinquencyStatus'] }}</p>
                                                @if(isset($calendarItem['PastDueDays']))
                                                    <p class="text-xs"><strong>Past Due Days:</strong> {{ $calendarItem['PastDueDays'] }}</p>
                                                @endif
                                                @if(isset($calendarItem['PastDueAmount']))
                                                    <p class="text-xs"><strong>Past Due Amount:</strong> {{ number_format($calendarItem['PastDueAmount']['Value'], 2) }} {{ $calendarItem['PastDueAmount']['Currency'] }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-red-500 text-xs">No contracts found.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach


        </div>

    <script nonce="{{ csp_nonce() }}">


        const chartData = @json($this->stockPrices);
        //console.log(chartData);
        const chartConfig = {
            series: [
                {
                    name: "Trend",
                    data: chartData.map(item => item.data),
                },
            ],
            chart: {
                type: "line",
                height: 240,
                toolbar: {
                    show: false,
                },
            },
            title: {
                show: "",
            },
            dataLabels: {
                enabled: false,
            },
            colors: ["rgba(0,34,65,0.9)"],
            stroke: {
                lineCap: "round",
                curve: "smooth",
            },
            markers: {
                size: 0,
            },
            xaxis: {
                axisTicks: {
                    show: false,
                },
                axisBorder: {
                    show: false,
                },
                labels: {
                    style: {
                        colors: "#616161",
                        fontSize: "12px",
                        fontFamily: "inherit",
                        fontWeight: 400,
                    },
                },
                categories: chartData.map(item => item.month),
            },
            yaxis: {
                labels: {
                    style: {
                        colors: "#616161",
                        fontSize: "12px",
                        fontFamily: "inherit",
                        fontWeight: 400,
                    },
                },
            },
            grid: {
                show: true,
                borderColor: "#dddddd",
                strokeDashArray: 5,
                xaxis: {
                    lines: {
                        show: true,
                    },
                },
                padding: {
                    top: 5,
                    right: 20,
                },
            },
            fill: {
                opacity: 0.8,
            },
            tooltip: {
                theme: "dark",
            },
        };

        const chart = new ApexCharts(document.querySelector("#chart"), chartConfig);

        chart.render();
    </script>


</div>
