<?php

namespace App\Http\Livewire\ProfileSetting;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Statistics extends Component
{

    public $financialData;

    public function boot()
    {
        // Get the current year's date
        $currentYearEndDate = Carbon::now()->endOfYear()->toDateString();

        // Get the count of female employees
        $activeMembersCount = DB::table('clients')->where('client_status', 'ACTIVE')->count();

        // Check if the record exists
        $recordExists = DB::table('financial_data')
            ->where('end_of_business_year', $currentYearEndDate)
            ->where('description', 'Active Members')
            ->exists();

        if ($recordExists) {
            // Update the existing record
            DB::table('financial_data')
                ->where('end_of_business_year', $currentYearEndDate)
                ->where('description', 'Active Members')
                ->update(['value' => $activeMembersCount]);
        } else {
            // Create a new record
            DB::table('financial_data')->insert([
                'description' => 'Active Members',
                'category' => 'Members',
                'value' => $activeMembersCount,
                'end_of_business_year' => $currentYearEndDate,
            ]);
        }

        /////////////////////////////////////////////////////////////////////////


        // Get the count of female employees
        $activeMembersCount = DB::table('clients')->where('client_status', 'INACTIVE')->count();

        // Check if the record exists
        $recordExists = DB::table('financial_data')
            ->where('end_of_business_year', $currentYearEndDate)
            ->where('description', 'Inactive Members')
            ->exists();

        if ($recordExists) {
            // Update the existing record
            DB::table('financial_data')
                ->where('end_of_business_year', $currentYearEndDate)
                ->where('description', 'Inactive Members')
                ->update(['value' => $activeMembersCount]);
        } else {
            // Create a new record
            DB::table('financial_data')->insert([
                'description' => 'Inactive Members',
                'category' => 'Members',
                'value' => $activeMembersCount,
                'end_of_business_year' => $currentYearEndDate,
            ]);
        }


        ///////////////////////////////////////////////////////////////////////////


        // Get the count of female employees
        $femaleEmployeesCount =  DB::table('employees')
        ->where(function ($query) {
            $query->where('gender', 'Female')
                  ->orWhere('gender', 'female');
        })
        ->count();



        // Check if the record exists
        $recordExists = DB::table('financial_data')
            ->where('end_of_business_year', $currentYearEndDate)
            ->where('description', 'Female Employees')
            ->exists();

        if ($recordExists) {
            // Update the existing record
            DB::table('financial_data')
                ->where('end_of_business_year', $currentYearEndDate)
                ->where('description', 'Female Employees')
                ->update(['value' => $femaleEmployeesCount]);
        } else {
            // Create a new record
            DB::table('financial_data')->insert([
                'description' => 'Female Employees',
                'category' => 'Members',
                'value' => $femaleEmployeesCount,
                'end_of_business_year' => $currentYearEndDate,
            ]);
        }

        /////////////////////////////////////////////////////////////////////////


        // Get the count of female employees
        $maleEmployeesCount = DB::table('employees')
        ->where(function ($query) {
            $query->where('gender', 'Male')
                  ->orWhere('gender', 'male');
        })
        ->count();


        // Check if the record exists
        $recordExists = DB::table('financial_data')
            ->where('end_of_business_year', $currentYearEndDate)
            ->where('description', 'Male Employees')
            ->exists();

        if ($recordExists) {
            // Update the existing record
            DB::table('financial_data')
                ->where('end_of_business_year', $currentYearEndDate)
                ->where('description', 'Male Employees')
                ->update(['value' => $maleEmployeesCount]);
        } else {
            // Create a new record
            DB::table('financial_data')->insert([
                'description' => 'Male Employees',
                'category' => 'Members',
                'value' => $maleEmployeesCount,
                'end_of_business_year' => $currentYearEndDate,
            ]);
        }


        //////////////////////////////////////////////////////////////////////////////////////
        $totalAssets = DB::table('accounts')->where('major_category_code', '1000')->sum('balance');
        // Check if the record exists
        $recordExists = DB::table('financial_data')
            ->where('end_of_business_year', $currentYearEndDate)
            ->where('description', 'Total Assets')
            ->exists();
        if ($recordExists) {
            // Update the existing record
            DB::table('financial_data')
                ->where('end_of_business_year', $currentYearEndDate)
                ->where('description', 'Total Assets')
                ->update(['value' => $totalAssets]);
        } else {
            // Create a new record
            DB::table('financial_data')->insert([
                'description' => 'Total Assets',
                'category' => 'Members',
                'value' => $totalAssets,
                'end_of_business_year' => $currentYearEndDate,
            ]);
        }


        /////////////////////////////////////////////////////////////////////////////
        ///
        // Calculate totals (the same calculations as before)
        $shortTermAssets = DB::table('accounts')
            ->where('major_category_code', '1000')
            ->whereIn('category_code', ['1000', '1100'])
            ->sum('balance');

        $shortTermLiabilities = DB::table('accounts')
            ->where('major_category_code', '2000')
            ->whereIn('category_code', ['2000', '2100', '2200', '2300'])
            ->sum('balance');

        $memberSavingsAndDeposits = DB::table('accounts')
            ->where('major_category_code', '2000')
            ->whereIn('category_code', ['2200'])
            ->sum('balance');

        $loansToMembers = DB::table('accounts')
            ->where('major_category_code', '1000')
            ->whereIn('category_code', ['1200'])
            ->sum('balance');

        $coreCapital = DB::table('accounts')
            ->where('major_category_code', '3000')
            ->sum('balance');

        $memberShares = DB::table('accounts')
            ->where('major_category_code', '3000')
            ->whereIn('category_code', ['3000'])
            ->sum('balance');

        $institutionalCapital = DB::table('accounts')
            ->where('major_category_code', '3000')
            ->whereIn('category_code', ['3000'])
            ->sum('balance'); // Adjust based on specific sub_category_code if available

        $totalIncome = DB::table('accounts')
            ->where('major_category_code', '4000')
            ->sum('balance');

        $totalInterestOnLoans = DB::table('accounts')
            ->where('major_category_code', '4000')
            ->whereIn('category_code', ['4000'])
            ->sum('balance');

        $totalExpenses = DB::table('accounts')
            ->where('major_category_code', '5000')
            ->sum('balance');

        // Get current year end date
        $currentYearEndDate = now()->endOfYear()->format('Y-m-d');

        // Totals array
        $totals = [
            'Short-Term Assets' => $shortTermAssets,
            'Short-Term Liabilities' => $shortTermLiabilities,
            'Member Savings and Deposits' => $memberSavingsAndDeposits,
            'Loans to Members' => $loansToMembers,
            'Core Capital' => $coreCapital,
            'Member Shares' => $memberShares,
            'Institutional Capital' => $institutionalCapital,
            'Total Income' => $totalIncome,
            'Total Interest on Loans' => $totalInterestOnLoans,
            'Total Expenses' => $totalExpenses,
        ];

        // Insert or update records
        foreach ($totals as $description => $value) {
            // Check if the record exists
            $recordExists = DB::table('financial_data')
                ->where('end_of_business_year', $currentYearEndDate)
                ->where('description', $description)
                ->exists();

            if ($recordExists) {
                // Update the existing record
                DB::table('financial_data')
                    ->where('end_of_business_year', $currentYearEndDate)
                    ->where('description', $description)
                    ->update(['value' => $value]);
            } else {
                // Create a new record
                DB::table('financial_data')->insert([
                    'description' => $description,
                    'value' => $value,
                    'end_of_business_year' => $currentYearEndDate,
                ]);
            }
        }



        $this->financialData = DB::table('financial_data')
            ->get();

    }


    public function render()
    {
        return view('livewire.profile-setting.statistics');
    }
}
