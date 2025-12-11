@php use Illuminate\Support\Facades\DB; @endphp
<div>

    <div class="w-full flex gap-2">
        <div class="w-full">
            <div class="flex items-center justify-center p-2">
                <div aria-label="card" class="p-6 rounded-3xl bg-white w-full ">

                  <div class="flex  item-center justify-center" >
                    <div id="loanApplicationsChart3" class="bg-gray-100 p-4  mt-6 overflow-hidden" style="height: 400px; width: 100%;">

                    </div>

                  </div>

                    <div class="flex">
                    <div id="loanApplicationsChart1" class="bg-gray-100 p-4  mt-6 overflow-hidden" style="height: 350px; width: 50%;">

                    </div>


                    <div id="loanApplicationsChart2" class="bg-gray-100 p-4  mt-6 overflow-hidden" style="height: 350px; width: 50%;">

                    </div>

                    </div>









                    </div>

                </div>


            </div>


        </div>


    </div>




                        </div>




                    </div>

                </div>


            </div>


        </div>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script nonce="{{ csp_nonce() }}">
        //document.addEventListener('DOMContentLoaded', function () {
        var options = {
            chart: {
                type: 'donut',
                height: 300,
                width : '100%'
            },


            series: [
                {{ $loan_summary['disbursed'] }},
                {{ $loan_summary['Onprogress'] }},
                {{ $loan_summary['Offered'] }},
                {{ $loan_summary['completed'] }},
                {{ $loan_summary['Declined'] }},
                {{ $loan_summary['arrears'] }},
                {{ $loan_summary['Active'] }},
                {{ $loan_summary['Rejected'] }}
            ],
            labels: [
                'disbursed',
                'Onprogress',
                'Offered',
                'completed',
                'Declined',
                'arrears',
                'Active',
                'Rejected'
            ],
            colors: [
          // '#5968B0', // Darker Blue
            '#5468E8', // Lighter Blue
            '#4D6DB0',  // Lighter Orange
            '#2D88A3',
            '#C44823', // Darker Blue
            '#2D3D6F', // Lighter Blue
            '#2D3D88',  // Lighter Orange
            '#FC0314'
            ],
            title: {
                text: ' Loan Portfolio Overview',




            }
        };

        var chart = new ApexCharts(document.querySelector("#loanApplicationsChart1"), options);
        chart.render();
        //});
    </script>



<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script nonce="{{ csp_nonce() }}">
    //document.addEventListener('DOMContentLoaded', function () {
    var options = {
        chart: {
            type: 'donut',
            height: 300,
            width : '100%'
        },


        series: [
                {{ $loa_product['smallest'] }},
                {{ $loa_product['small'] }},
                {{ $loa_product['high'] }},
                {{ $loa_product['highest'] }},


        ],
        labels: [
            'Very Low',
            'Low',
            'High',
            'Very High'
        ],
        colors: [
            '#C44823',
            '#2D88A3',
         // Darker Blue
            '#2D3D6F', // Lighter Blue
            '#2D3D88',  // Lighter Orange

        ],
        title: {
            text: 'Product Performance ',



        }
    };

    var chart = new ApexCharts(document.querySelector("#loanApplicationsChart2"), options);
    chart.render();
    //});
</script>


<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script nonce="{{ csp_nonce() }}">
    //document.addEventListener('DOMContentLoaded', function () {
    var options = {
        chart: {
            type: 'donut',
            height: 390,
            width : '95%'
        },


        series: [
            {{ $applicationSummary['Pending'] }},
            {{ $applicationSummary['Onprogress'] }},
            {{ $applicationSummary['Offered'] }},
            {{ $applicationSummary['Accepted'] }},
            {{ $applicationSummary['Declined'] }},
            {{ $applicationSummary['Administration'] }},
            {{ $applicationSummary['Active'] }},
            {{ $applicationSummary['Rejected'] }}
        ],
        labels: [
            'Pending',
            'Onprogress',
            'Offered',
            'Accepted',

            'Declined',
            'Administration',
            'Active',
            'Rejected'
        ],
        colors: [
            '#5968B0', // Darker Blue
            '#5468E8', // Lighter Blue
            '#4D6DB0',  // Lighter Orange
            '#2D88A3',
            '#C44823', // Darker Blue
            '#2D3D6F', // Lighter Blue
            '#2D3D88',  // Lighter Orange
            '#FC0314'
        ],
        title: {
            text: 'Clients Status Summary ',



        }
    };

    var chart = new ApexCharts(document.querySelector("#loanApplicationsChart3"), options);
    chart.render();
    //});
</script>






</div>

