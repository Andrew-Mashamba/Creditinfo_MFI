<?php

namespace App\Http\Livewire\Dashboard;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;

use Livewire\Component;
use Illuminate\Support\Facades\DB;


class ColumnChart extends Component
{
    public $changeChat=false;

    protected $listeners=[
      'changeGraph'=>'changeGraphContent'
    ];


  public  function changeGraphContent()
    {
        dd('pass');
    }
    public function render()
    {

        $commoditiesChartModel = (new ColumnChartModel())
            ->setTitle('Kilograms')
            ->withOnColumnClickEventName('onColumnClick');

        $commodities = DB::table('users')->get();

        foreach ($commodities as $commodity) {

            if($this->changeChat){

                $pledgedKilos=  DB::table('users')->where('id', $commodity->id)->where('status', 'ACTIVE')->value('id');

            }else{
                $pledgedKilos = DB::table('users')->where('id', $commodity->id)->sum('id');

            }

            if($commodity->id == 7){
                //dd($commodity->name,$pledgedKilos);
            }

            if($this->changeChat) {
                $commoditiesChartModel->addColumn( " Total Value of".$commodity->name, $pledgedKilos, '#4682B4'); // Use a color based on the commodity name


            }else{
                $commoditiesChartModel->addColumn($commodity->name, $pledgedKilos, '#018001'); // Use a color based on the commodity name

            }

        }


        return view('livewire.dashboard.column-chart')->with([ 'commoditiesChartModel' => $commoditiesChartModel]);
    }
}
