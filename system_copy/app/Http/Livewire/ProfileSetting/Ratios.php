<?php
namespace App\Http\Livewire\ProfileSetting;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Ratios extends Component
{
    public $ratios;
    public $distinctYears;

    public function mount()
    {
        // Get the current date
        $currentDate = Carbon::now();

        // Get the last date of the current year
        $lastDateOfYear = $currentDate->endOfYear()->format('Y-m-d');

        // Define major category codes
        $assetCategoryCodes = ['1000'];
        $liabilityCategoryCodes = ['2000'];
        $expenseCategoryCodes = ['5000'];
        $incomeCategoryCodes = ['4000', '3000'];

        // Retrieve accounts data for the current year
        $accounts = DB::table('accounts')->get();



        // Calculate total assets
        $totalAssets = $accounts->whereIn('major_category_code', $assetCategoryCodes)->sum('balance');

        // Calculate total liabilities
        $totalLiabilities = $accounts->whereIn('major_category_code', $liabilityCategoryCodes)->sum('balance');

        // Calculate core capital
        $coreCapital = $accounts->where('category_code', '3000')->sum('balance');

        // Calculate net capital
        $netCapital = $coreCapital - $totalLiabilities;

        // Calculate short-term assets
        $shortTermAssets = $accounts->where('major_category_code', '1000')->sum('balance');

        // Calculate short-term liabilities
        $shortTermLiabilities = $accounts->where('major_category_code', '2000')->sum('balance');

        // Calculate expenses
        $expenses = $accounts->whereIn('major_category_code', $expenseCategoryCodes)->sum('balance');

        // Calculate income
        $income = $accounts->whereIn('major_category_code', $incomeCategoryCodes)->sum('balance');

        // Check if financial ratios for the current year already exist
        $financialRatio = DB::table('financial_ratios')->where('end_of_financial_year_date', $lastDateOfYear)->first();
        //dd($financialRatio);

        if ($financialRatio) {

            // Update existing record
            DB::table('financial_ratios')
                ->where('end_of_financial_year_date', $lastDateOfYear)
                ->update([
                    'core_capital' => $coreCapital,
                    'total_assets' => $totalAssets,
                    'net_capital' => $netCapital,
                    'short_term_assets' => $shortTermAssets,
                    'short_term_liabilities' => $shortTermLiabilities,
                    'expenses' => $expenses,
                    'income' => $income,
                ]);

        } else {
            // Insert new record


            DB::table('financial_ratios')->insert([
                'end_of_financial_year_date' => $lastDateOfYear,
                'core_capital' => $coreCapital,
                'total_assets' => $totalAssets,
                'net_capital' => $netCapital,
                'short_term_assets' => $shortTermAssets,
                'short_term_liabilities' => $shortTermLiabilities,
                'expenses' => $expenses,
                'income' => $income,
            ]);
        }





        // Fetch all ratios ordered by year
        $this->ratios = DB::table('financial_ratios')
            ->orderBy('end_of_financial_year_date', 'desc')
            ->get();

        // Fetch distinct years
        $this->distinctYears = DB::table('financial_ratios')
            ->pluck('end_of_financial_year_date')
            ->unique()
            ->sortDesc();
    }

    public function render()
    {
        return view('livewire.profile-setting.ratios', [
            'ratios' => $this->ratios,
            'distinctYears' => $this->distinctYears,
        ]);
    }
}
