<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Loan_sub_products;
use App\Models\LoansModel;
use Illuminate\Support\Facades\DB;

use Livewire\Component;
use Asantibanez\LivewireCharts\Facades\LivewireCharts;
use Asantibanez\LivewireCharts\Models\RadarChartModel;
use Asantibanez\LivewireCharts\Models\TreeMapChartModel;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;

use Asantibanez\LivewireCharts\Models\LineChartModel;
use Asantibanez\LivewireCharts\Models\MultiLineChartModel;
use Asantibanez\LivewireCharts\Models\MultiColumnChartModel;
use Asantibanez\LivewireCharts\Models\PieChartModel;
use Asantibanez\LivewireCharts\Models\AreaChartModel;
use App\Models\ReleasesModel;

class Management extends Component
{
    public $firstRun = true;

    public $showDataLabels = false;

    public $data;
    public $chartValue=false;
    public $changeDisplay;

    public $applicationSummary;



    public function boot()
    {
        // Initialize data array
        $this->data = [
            'columns' => [
                ['label' => 'principle', 'field' => 'principle'],
                ['label' => 'interest', 'field' => 'interest'],
                ['label' => 'offer_status', 'field' => 'offer_status'],
                ['label' => 'tenure', 'field' => 'tenure'],
            ],
            'rows' => [],
        ];
    }

    public function ChangeDisplay()
    {

        $this->chartValue=!$this->chartValue;

        $this->render();


    }
    #[On('onColumnClick')]
    public function handleOnColumnClick($event)
    {

        dd($event);
        $this->ChangeDisplay();

    }

public $loan_summary;
public $loa_product;

    public function mount()
    {
        $this->applicationSummary = [
            'Total' => DB::table('clients')->count(),
            'Pending' => DB::table('clients')->where('client_status', 'PENDING')->count(),
            'Onprogress' => DB::table('clients')->where('client_status', 'ONPROGRESS')->count(),
            'Offered' => DB::table('clients')->where('client_status', 'CLOSED')->count(),
            'Accepted' => DB::table('clients')->where('client_status', 'ACCEPTED')->count(),
            'Declined' => DB::table('clients')->where('client_status', 'NOT ACCEPTED')->count(),
            'Administration' => DB::table('clients')->where('client_status', 'ADMINISTRATION')->count(),
            'Active' => DB::table('clients')->where('client_status', 'ACTIVE')->count(),
            'Rejected' => DB::table('clients')->where('client_status', 'REJECTED')->count(),
        ];

        //loan summary

        $loan=LoansModel::query();

        $this->loan_summary=[

       'disbursed'=>LoansModel::where('status','DISBURSED')->count(),
        'Onprogress'=>LoansModel::where('status','ONPROGRESS')->count(),
        'Offered'=>LoansModel::where('status','APPROVED') ->count(),
        'completed'=>LoansModel::where('status','CLOSED')  ->count(),
        'Declined'=>LoansModel::where('status','DECLINED') ->count(),
        'arrears'=>LoansModel::where('status','ARREARS') ->count(),
        'Active'=>LoansModel::where('status','ACTIVE') ->count(),
        'Rejected'=>LoansModel::where('status','DECLINED') ->count(),
        ];



        //product performance

        $loaProductCounts = [];

        // Fetch all loan sub-products
        $subProducts = Loan_sub_products::all();

        // Loop through each sub-product
        foreach ($subProducts as $product) {
            // Count the number of loans for each sub-product
            $loanCount = LoansModel::where('loan_sub_product', $product->sub_product_id)->count();

            // Add the count to the array with the sub-product ID as the key
            $loaProductCounts[$product->sub_product_id] = $loanCount;
        }

      $array=   $this->getTopAndBottomTwo($loaProductCounts);


      $this->loa_product = [
        'smallest' => isset($array['smallest_two'][0])  ? $array['smallest_two'][0] : 0,
        'small'    => isset($array['smallest_two'][1]) ? $array['smallest_two'][1] : 0,
        'high'     => isset($array['largest_two'][0]) ? $array['largest_two'][0] : 0,
        'highest'  => isset($array['largest_two'][1]) ? $array['largest_two'][1] : 0,
    ];




    }


    function getTopAndBottomTwo($array) {
        // Handle empty array or array with fewer than four elements
        if (empty($array)) {
            return [
                'smallest_two' => [],
                'largest_two' => []
            ];
        }

        if (count($array) < 2) {
            return [
                'smallest_two' => $array,
                'largest_two' => $array
            ];
        }

        // Remove duplicate values and sort the array in ascending order
        $uniqueArray = array_values(array_unique($array));
        sort($uniqueArray);

        // Handle cases where there are fewer than two unique values
        if (count($uniqueArray) < 2) {
            return [
                'smallest_two' => $uniqueArray,
                'largest_two' => $uniqueArray
            ];
        }

        // Get the two smallest numbers
        $smallestTwo = array_slice($uniqueArray, 0, 2);

        // Get the two largest numbers
        $largestTwo = array_slice($uniqueArray, -2);

        return [
            'smallest_two' => $smallestTwo,
            'largest_two' => $largestTwo,
        ];
    }


    public function render()
    {





        return view('livewire.dashboard.management');
    }
}
