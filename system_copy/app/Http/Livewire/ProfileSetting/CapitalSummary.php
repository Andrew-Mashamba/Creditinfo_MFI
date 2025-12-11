<?php

namespace App\Http\Livewire\ProfileSetting;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CapitalSummary extends Component
{

    public $yearlyData = [];

    public function mount()
    {
        $this->fetchCapitalSummaryData();
    }

    public function fetchCapitalSummaryData()
    {
        // Get the current year
         //= Carbon::now()->year;

        // Fetch data for the current year only
        $this->yearlyData = [
            'member_shares' => $this->getMemberShares(),
            'member_savings' => $this->getMemberSavings(),
            'additional_reserves' => $this->getAdditionalReserves(),
            'miscellaneous_savings' => $this->getMiscellaneousSavings(),
            'valuation_of_fixed_assets' => $this->getValuationOfFixedAssets(),
        ];

        //dd($this->yearlyData);
    }

    public function getMemberShares()
    {
        return DB::table('accounts')
            ->whereBetween('sub_category_code', ['3000', '3040'])
            ->sum('balance');
    }

    public function getMemberSavings()
    {
        return DB::table('accounts')
            ->whereBetween('sub_category_code', ['2200', '2240'])
            ->sum('balance');
    }

    public function getAdditionalReserves()
    {
        return DB::table('accounts')
            ->whereBetween('sub_category_code', ['1300', '1399'])
            ->sum('balance');
    }

    public function getMiscellaneousSavings()
    {
        return DB::table('accounts')
            ->whereBetween('sub_category_code', ['2200', '2240'])
            ->sum('balance');
    }

    public function getValuationOfFixedAssets()
    {
        return DB::table('accounts')
            ->whereBetween('sub_category_code', ['1700', '1730'])
            ->sum('balance');
    }


    public function render()
    {
        return view('livewire.profile-setting.capital-summary');
    }
}
