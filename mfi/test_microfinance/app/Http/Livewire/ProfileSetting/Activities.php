<?php

namespace App\Http\Livewire\ProfileSetting;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Activities extends Component
{

    public $financialData;
    public $financialPositions;

    public function boot()
    {
        // Get the current year's end date
        $currentYearEndDate = Carbon::now()->endOfYear()->toDateString();

        // Fetch financial data from the database
        $interestOnLoans = DB::table('accounts')
            ->where('major_category_code', '4000')
            ->where('sub_category_code', '4010')
            ->sum('balance');

        // Sum all income excluding interest on loans
        $totalIncome = DB::table('accounts')
            ->where('major_category_code', '4000')
            ->sum('balance');

        // Calculate other income by excluding interest on loans
        $otherIncome = DB::table('accounts')
            ->where('major_category_code', '4000')
            ->whereNotIn('sub_category_code', ['4010', '4020', '4040'])
            ->sum('balance');

        $expenses = DB::table('accounts')
            ->where('major_category_code', '5000')
            ->sum('balance');

        $annualSurplus = $totalIncome - $expenses;

        // Check if financial ratios for the current year already exist
        $financialPosition = DB::table('financial_position')
            ->where('end_of_business_year', $currentYearEndDate)
            ->first();

        // Prepare data for update/insert
        $financialData = [
            'interest_on_loans' => $interestOnLoans,
            'other_income' => $otherIncome,
            'total_income' => $totalIncome,
            'expenses' => $expenses,
            'annual_surplus' => $annualSurplus,
            'end_of_business_year' => $currentYearEndDate,
        ];

        if ($financialPosition) {
            // Update existing record
            DB::table('financial_position')
                ->where('id', $financialPosition->id)
                ->update($financialData);
        } else {
            // Insert new record
            DB::table('financial_position')->insert($financialData);
        }

//        // Fetch all financial positions ordered by year
//        $this->ratios = DB::table('financial_position')
//            ->orderBy('end_of_business_year', 'desc')
//            ->get();
//
//        // Fetch distinct years
//        $this->distinctYears = DB::table('financial_position')
//            ->pluck('end_of_business_year')
//            ->unique()
//            ->sortDesc();




        // Fetch financial positions ordered by year
        $this->financialPositions = DB::table('financial_position')
            ->orderBy('end_of_business_year', 'desc')
            ->get();

        // Ensure financialPositions is a collection
        if ($this->financialPositions->isNotEmpty()) {
            // Use keyBy safely
            $this->ratios = $this->financialPositions->keyBy('end_of_business_year');
        } else {
            // Initialize as an empty collection if no records are found
            $this->ratios = collect();
        }

        // Fetch distinct years
        $distinctYears = DB::table('financial_position')
            ->pluck('end_of_business_year')
            ->unique()
            ->sortDesc();

        // Ensure distinctYears is a collection
        if ($distinctYears->isNotEmpty()) {
            $this->distinctYears = $distinctYears;
        } else {
            // Initialize as an empty collection if no records are found
            $this->distinctYears = collect();
        }



    }




    public function render()
    {
        return view('livewire.profile-setting.activities');
    }
}
