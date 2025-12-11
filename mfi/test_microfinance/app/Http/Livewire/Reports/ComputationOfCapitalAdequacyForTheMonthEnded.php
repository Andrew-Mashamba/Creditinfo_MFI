<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComputationOfCapitalAdequacyForTheMonthEnded extends Component
{
    public $startDate;
    public $endDate;
    public $capitalData = [];
    public $totalTier1Capital = 0;
    public $totalTier2Capital = 0;
    public $totalCapital = 0;
    public $totalRiskWeightedAssets = 0;
    public $capitalAdequacyRatio = 0;
    public $minimumRequiredRatio = 10; // 10% minimum CAR requirement

    public function mount()
    {
        $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        // Get Tier 1 Capital (Core Capital)
        $tier1Capital = DB::table('capital_accounts')
            ->where('capital_type', 'tier1')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->select(
                'account_name',
                'amount',
                'description',
                'created_at'
            )
            ->get();

        // Get Tier 2 Capital (Supplementary Capital)
        $tier2Capital = DB::table('capital_accounts')
            ->where('capital_type', 'tier2')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->select(
                'account_name',
                'amount',
                'description',
                'created_at'
            )
            ->get();

        // Get Risk-Weighted Assets
        $riskWeightedAssets = DB::table('risk_weighted_assets')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->select(
                'asset_category',
                'amount',
                'risk_weight',
                'weighted_amount',
                'created_at'
            )
            ->get();

        // Combine all capital data
        $this->capitalData = [
            'tier1' => $tier1Capital,
            'tier2' => $tier2Capital,
            'risk_weighted_assets' => $riskWeightedAssets
        ];

        // Calculate totals
        $this->totalTier1Capital = $tier1Capital->sum('amount');
        $this->totalTier2Capital = $tier2Capital->sum('amount');
        $this->totalCapital = $this->totalTier1Capital + $this->totalTier2Capital;
        $this->totalRiskWeightedAssets = $riskWeightedAssets->sum('weighted_amount');

        // Calculate Capital Adequacy Ratio
        $this->capitalAdequacyRatio = $this->totalRiskWeightedAssets > 0 
            ? ($this->totalCapital / $this->totalRiskWeightedAssets) * 100 
            : 0;
    }

    public function updatedStartDate()
    {
        $this->loadData();
    }

    public function updatedEndDate()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.reports.computation-of-capital-adequacy-for-the-month-ended');
    }
} 