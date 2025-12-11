<?php

namespace App\Http\Livewire\ProfileSetting;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Divident extends Component
{
    public $end_of_year;
    public $start_of_year;
    public $income;
    public $expenses;
    public $dividend_amount;
    public $carry_amount;


    public function render()
    {

        $this->income= (double) DB::table('accounts')->whereBetween('sub_category_code',[4010,4520])->sum('balance');
        $this->expenses=  (double)DB::table('accounts')->where('sub_category_code','>',5010)->sum('balance');


          $this->carry_amount=(double) ($this->income-$this->expenses) - (double)$this->dividend_amount;
          $this->carry_amount= $this->carry_amount?:0;

        $get_start_date = DB::table('institutions')
            ->value('startDate');
        $this->start_of_year=$get_start_date;

        if ($get_start_date) {
            // Check if a valid startDate is retrieved
            $date = Carbon::parse($get_start_date)->addYear();

            // Now $date contains the startDate + 1 year
            $this->end_of_year=$date;
        } else {
            // Handle the case where startDate is not found or invalid
           $this->end_of_year='';
        }

        return view('livewire.profile-setting.divident');
    }


    public function endOfYear(){

        if($this->start_of_year ==$this->end_of_year->format('y-m-d')){
            "make changes";
        }
        else
        {
            session()->flash('message_fail','Sorry you cannot end for now');

        }
    }
}
