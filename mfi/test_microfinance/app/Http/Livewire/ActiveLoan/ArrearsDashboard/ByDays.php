<?php

namespace App\Http\Livewire\ActiveLoan\ArrearsDashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ByDays extends Component
{
    public $arrears1to7 = 0;
    public $arrears8to15 = 0;
    public $arrears16to30 = 0;
    public $arrears31to60 = 0;
    public $arrears61to90 = 0;
    public $arrears90plus = 0;
    public $totalArrears = 0;
    
    // Risk classification data
    public $watchLoans = 0;
    public $substandardLoans = 0;
    public $doubtfulLoans = 0;
    public $lossLoans = 0;
    
    public function mount()
    {
        $this->loadArrearsByDays();
    }
    
    private function loadArrearsByDays()
    {
        // Get arrears distribution by days from loans_schedules
        $this->arrears1to7 = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 0)
            ->where('days_in_arrears', '<=', 7)
            ->count();
            
        $this->arrears8to15 = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 7)
            ->where('days_in_arrears', '<=', 15)
            ->count();
            
        $this->arrears16to30 = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 15)
            ->where('days_in_arrears', '<=', 30)
            ->count();
            
        $this->arrears31to60 = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 30)
            ->where('days_in_arrears', '<=', 60)
            ->count();
            
        $this->arrears61to90 = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 60)
            ->where('days_in_arrears', '<=', 90)
            ->count();
            
        $this->arrears90plus = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 90)
            ->count();
            
        $this->totalArrears = $this->arrears1to7 + $this->arrears8to15 + $this->arrears16to30 + 
                             $this->arrears31to60 + $this->arrears61to90 + $this->arrears90plus;
        
        // Risk classification based on days in arrears
        $this->watchLoans = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 0)
            ->where('days_in_arrears', '<=', 30)
            ->count();
            
        $this->substandardLoans = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 30)
            ->where('days_in_arrears', '<=', 90)
            ->count();
            
        $this->doubtfulLoans = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 90)
            ->where('days_in_arrears', '<=', 180)
            ->count();
            
        $this->lossLoans = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 180)
            ->count();
    }
    
    public function getPercentage($count)
    {
        if ($this->totalArrears == 0) {
            return 0;
        }
        return ($count / $this->totalArrears) * 100;
    }

    public function render()
    {
        return view('livewire.active-loan.arrears-dashboard.by-days');
    }
}
