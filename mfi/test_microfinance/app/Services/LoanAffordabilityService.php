<?php

namespace App\Services;

use App\Models\LoansModel;
use App\Models\ClientsModel;
use Illuminate\Support\Facades\DB;

class LoanAffordabilityService
{
    public function calculate(LoansModel $loan)
    {
        $monthlyIncome = $this->calculateMonthlyIncome($loan);
        $monthlyExpenses = $this->calculateMonthlyExpenses($loan);
        $monthlyLoanPayment = $this->calculateMonthlyLoanPayment($loan);
        $existingLoanPayments = $this->calculateExistingLoanPayments($loan);

        $disposableIncome = $monthlyIncome - $monthlyExpenses;
        $totalLoanPayments = $monthlyLoanPayment + $existingLoanPayments;
        
        $affordabilityRatio = $disposableIncome > 0 ? ($totalLoanPayments / $disposableIncome) * 100 : 100;
        $debtServiceRatio = $monthlyIncome > 0 ? ($totalLoanPayments / $monthlyIncome) * 100 : 100;

        return [
            'monthly_income' => $monthlyIncome,
            'monthly_expenses' => $monthlyExpenses,
            'disposable_income' => $disposableIncome,
            'monthly_loan_payment' => $monthlyLoanPayment,
            'existing_loan_payments' => $existingLoanPayments,
            'total_loan_payments' => $totalLoanPayments,
            'affordability_ratio' => $affordabilityRatio,
            'debt_service_ratio' => $debtServiceRatio,
            'is_affordable' => $this->isAffordable($affordabilityRatio, $debtServiceRatio),
            'recommendations' => $this->getAffordabilityRecommendations($affordabilityRatio, $debtServiceRatio)
        ];
    }

    protected function calculateMonthlyIncome(LoansModel $loan)
    {
        $income = 0;

        // Business income
        if ($loan->daily_sales > 0) {
            $income += $loan->daily_sales * 30; // Monthly sales
        }

        // Other income sources
        if ($loan->available_funds > 0) {
            $income += $loan->available_funds * 2; // Assuming available funds represent monthly income
        }

        // Additional income sources (could be enhanced)
        $additionalIncome = $this->getAdditionalIncome($loan->client_number);
        $income += $additionalIncome;

        return $income;
    }

    protected function calculateMonthlyExpenses(LoansModel $loan)
    {
        $expenses = 0;

        // Business expenses
        $expenses += $loan->cost_of_goods_sold ?? 0;
        $expenses += $loan->operating_expenses ?? 0;
        $expenses += $loan->monthly_taxes ?? 0;
        $expenses += $loan->other_expenses ?? 0;

        // Personal expenses (estimated)
        $personalExpenses = $this->estimatePersonalExpenses($loan);
        $expenses += $personalExpenses;

        return $expenses;
    }

    protected function calculateMonthlyLoanPayment(LoansModel $loan)
    {
        if ($loan->principle <= 0 || $loan->interest <= 0 || $loan->tenure <= 0) {
            return 0;
        }

        $monthlyRate = $loan->interest / 12 / 100;
        $numerator = $loan->principle * $monthlyRate * pow(1 + $monthlyRate, $loan->tenure);
        $denominator = pow(1 + $monthlyRate, $loan->tenure) - 1;

        return $denominator > 0 ? $numerator / $denominator : 0;
    }

    protected function calculateExistingLoanPayments(LoansModel $loan)
    {
        $existingLoans = DB::table('loans')
            ->where('client_number', $loan->client_number)
            ->where('id', '!=', $loan->id)
            ->where('status', 'ACTIVE')
            ->get();

        $totalPayments = 0;

        foreach ($existingLoans as $existingLoan) {
            $monthlyRate = $existingLoan->interest / 12 / 100;
            $numerator = $existingLoan->principle * $monthlyRate * pow(1 + $monthlyRate, $existingLoan->tenure);
            $denominator = pow(1 + $monthlyRate, $existingLoan->tenure) - 1;
            
            if ($denominator > 0) {
                $totalPayments += $numerator / $denominator;
            }
        }

        return $totalPayments;
    }

    protected function getAdditionalIncome($clientNumber)
    {
        // This could be enhanced to pull from additional income sources
        // For now, return a base amount
        return 0;
    }

    protected function estimatePersonalExpenses(LoansModel $loan)
    {
        // Estimate personal expenses based on available data
        $baseExpenses = 50000; // Base monthly personal expenses
        
        // Adjust based on family size (if available)
        $client = ClientsModel::where('client_number', $loan->client_number)->first();
        if ($client) {
            $familySize = ($client->number_of_spouse ?? 0) + ($client->number_of_children ?? 0) + 1;
            $baseExpenses *= $familySize;
        }

        return $baseExpenses;
    }

    protected function isAffordable($affordabilityRatio, $debtServiceRatio)
    {
        // Standard affordability thresholds
        return $affordabilityRatio <= 70 && $debtServiceRatio <= 40;
    }

    protected function getAffordabilityRecommendations($affordabilityRatio, $debtServiceRatio)
    {
        $recommendations = [];

        if ($affordabilityRatio > 70) {
            $recommendations[] = 'Reduce loan amount to improve affordability';
            $recommendations[] = 'Consider longer loan term to reduce monthly payments';
        }

        if ($debtServiceRatio > 40) {
            $recommendations[] = 'Total debt service ratio is too high';
            $recommendations[] = 'Consider debt consolidation';
        }

        if ($affordabilityRatio > 50 && $affordabilityRatio <= 70) {
            $recommendations[] = 'Monitor affordability closely';
            $recommendations[] = 'Consider additional income sources';
        }

        if ($affordabilityRatio <= 30) {
            $recommendations[] = 'Loan appears affordable';
            $recommendations[] = 'Standard terms can be applied';
        }

        return $recommendations;
    }

    public function calculateMaximumLoanAmount($clientNumber, $interestRate, $tenure)
    {
        $client = ClientsModel::where('client_number', $clientNumber)->first();
        if (!$client) {
            return 0;
        }

        // Get client's income and expenses
        $monthlyIncome = $this->getClientMonthlyIncome($clientNumber);
        $monthlyExpenses = $this->getClientMonthlyExpenses($clientNumber);
        $existingLoanPayments = $this->getClientExistingLoanPayments($clientNumber);

        $disposableIncome = $monthlyIncome - $monthlyExpenses;
        $availableForNewLoan = $disposableIncome - $existingLoanPayments;

        // Use 70% of available income for new loan
        $maxMonthlyPayment = $availableForNewLoan * 0.7;

        if ($maxMonthlyPayment <= 0) {
            return 0;
        }

        // Calculate maximum principal based on monthly payment
        $monthlyRate = $interestRate / 12 / 100;
        $numerator = $maxMonthlyPayment * (pow(1 + $monthlyRate, $tenure) - 1);
        $denominator = $monthlyRate * pow(1 + $monthlyRate, $tenure);

        return $denominator > 0 ? $numerator / $denominator : 0;
    }

    protected function getClientMonthlyIncome($clientNumber)
    {
        // This would be enhanced to pull actual income data
        return 200000; // Default monthly income
    }

    protected function getClientMonthlyExpenses($clientNumber)
    {
        // This would be enhanced to pull actual expense data
        return 100000; // Default monthly expenses
    }

    protected function getClientExistingLoanPayments($clientNumber)
    {
        $existingLoans = DB::table('loans')
            ->where('client_number', $clientNumber)
            ->where('status', 'ACTIVE')
            ->get();

        $totalPayments = 0;

        foreach ($existingLoans as $loan) {
            $monthlyRate = $loan->interest / 12 / 100;
            $numerator = $loan->principle * $monthlyRate * pow(1 + $monthlyRate, $loan->tenure);
            $denominator = pow(1 + $monthlyRate, $loan->tenure) - 1;
            
            if ($denominator > 0) {
                $totalPayments += $numerator / $denominator;
            }
        }

        return $totalPayments;
    }
} 