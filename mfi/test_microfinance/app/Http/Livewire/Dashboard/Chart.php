<?php

namespace App\Http\Livewire\Dashboard;

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


class Chart extends Component
{

    public $variable;

    public $firstRun = true;

    public $showDataLabels = false;

    public $data;

    public $types = ['PLEDGED','AVAILABLE','COLLATERAL RELEASE REQUESTED','ACTIVE', 'NEW CLIENT','APPROVED', 'REJECTED','PENDING','ACCEPTED','RELEASED','DECLINED','COLLATERAL RELEASED'];

    public $colors = [
        'APPROVED' => '#003300',
        'REJECTED' => '#004c00',
        'PENDING' => '#006600',
        'ACCEPTED' => '#018001',
        'RELEASED' => '#33cc33',
        'DECLINED' => '#66ff66',
        'COLLATERAL RELEASED' => '#99ff99',
        'ACTIVE' => '#003300',
        'NEW CLIENT' => '#004c00',
        'COLLATERAL RELEASE REQUESTED' => '#006600',
        'PLEDGED' => '#018001',
        'AVAILABLE' => '#004c00',
    ];



    public function render()
    {



        $stocks = DB::table('users')->where('id',$this->variable)->where('status','ACTIVE')->get();
        //dd($stocks);

        $theChartModel = collect($stocks)->reduce(function ($theChartModel, $data) {
            $commodityId = $data->commodity_id;

            // Extract pledged kilos and available kilos from the stdClass object
//            $pledgedKilos = $data->kilos;
            $availableKilos = $data->kilos;

            // Add slices for pledged kilos and available kilos
            $theChartModel = $theChartModel
//                ->addSlice(" Kilos (Commodity $commodityId)", $pledgedKilos, $this->colors['AVAILABLE'])
                ->addSlice("Available Kilos", $availableKilos, $this->colors['AVAILABLE']);

            return $theChartModel;
        }, LivewireCharts::pieChartModel()
            ->setAnimated($this->firstRun)
            ->setType('donut')
            ->withOnSliceClickEvent('onSliceClick')
            ->withoutLegend()
            ->legendPositionBottom()
            ->legendHorizontallyAlignedCenter()
            ->setDataLabelsEnabled($this->showDataLabels)
            ->setColors([$this->colors['AVAILABLE']])); // Set colors for slices


        return view('livewire.dashboard.chart')
            ->with([
                'theChartModel' => $theChartModel,
            ]);
    }
}
