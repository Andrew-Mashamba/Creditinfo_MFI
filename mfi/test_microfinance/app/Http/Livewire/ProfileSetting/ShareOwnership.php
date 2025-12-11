<?php

namespace App\Http\Livewire\ProfileSetting;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ShareOwnership extends Component
{
    public $financialData;
    public $financialPositions;
    public $ratios;
    public $distinctYears;

    public function boot()
    {
        // Get the current year's end date
        $currentYearEndDate = Carbon::now()->endOfYear()->toDateString();

        // Fetch financial data
        $financialData = $this->fetchFinancialData();

        // Prepare data for update/insert
        $shareData = array_merge($financialData, [
            'end_business_year_date' => $currentYearEndDate
        ]);

        // Check if financial data for the current year already exists
        $this->updateOrInsertFinancialPosition($shareData, $currentYearEndDate);

        // Fetch financial positions and distinct years
        $this->fetchFinancialPositionsAndYears();
    }

    /**
     * Fetch financial data such as active members count, shares, savings, deposits, and interest-free loans.
     *
     * @return array
     */
    protected function fetchFinancialData()
    {
        // Active members count
        $activeMembersCount = DB::table('clients')->where('client_status', 'ACTIVE')->count();

        // Shares (Capital Accounts)
        $shares = DB::table('accounts')
            ->where('major_category_code', '3000')
            ->whereIn('sub_category_code', ['3003', '3009', '3010', '3030', '3040'])
            ->sum('balance');

        // Savings (Liability Accounts)
        $savings = DB::table('accounts')
            ->where('major_category_code', '2000')
            ->whereIn('sub_category_code', ['2210', '2220', '2230', '2240'])
            ->sum('balance');

        // Deposits (Liability Accounts)
        $deposits = DB::table('accounts')
            ->where('major_category_code', '2000')
            ->whereIn('sub_category_code', ['2030', '2040'])
            ->sum('balance');

        // Interest-Free Loans (Asset Accounts)
        $interestFreeLoans = DB::table('accounts')
            ->where('major_category_code', '1000')
            ->whereIn('sub_category_code', ['2320', '2322', '2330', '2350'])
            ->sum('balance');

        return [
            'number_of_members' => $activeMembersCount,
            'shares' => $shares,
            'savings' => $savings,
            'deposits' => $deposits,
            'interest_free_loans' => $interestFreeLoans,
        ];
    }

    /**
     * Update or insert the financial position data for the current year.
     *
     * @param array $shareData
     * @param string $currentYearEndDate
     */
    protected function updateOrInsertFinancialPosition(array $shareData, $currentYearEndDate)
    {
        $financialPosition = DB::table('share_ownership')
            ->where('end_business_year_date', $currentYearEndDate)
            ->first();

        if ($financialPosition) {
            // Update existing record
            DB::table('share_ownership')
                ->where('id', $financialPosition->id)
                ->update($shareData);
        } else {
            // Insert new record
            DB::table('share_ownership')->insert($shareData);
        }
    }

    /**
     * Fetch financial positions ordered by year and distinct years.
     */
    protected function fetchFinancialPositionsAndYears()
    {
        // Fetch financial positions ordered by year
        $this->financialPositions = DB::table('share_ownership')
            ->orderBy('end_business_year_date', 'desc')
            ->get();

        // Ensure financialPositions is a collection and key by 'end_business_year_date'
        $this->ratios = $this->financialPositions->isNotEmpty()
            ? $this->financialPositions->keyBy('end_business_year_date')
            : collect();

        // Fetch distinct years
        $this->distinctYears = DB::table('share_ownership')
            ->pluck('end_business_year_date')
            ->unique()
            ->sortDesc();

        // Ensure distinctYears is a collection
        $this->distinctYears = $this->distinctYears->isNotEmpty()
            ? $this->distinctYears
            : collect();
    }

    public function render()
    {
        return view('livewire.profile-setting.share-ownership');
    }
}
