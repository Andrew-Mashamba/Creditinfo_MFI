<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClientInformationService
{
    /**
     * Get comprehensive client information
     */
    public function getClientInformation($clientNumber)
    {
        try {
            $client = DB::table('clients')->where('client_number', $clientNumber)->first();
            
            if (!$client) {
                return $this->getDefaultClientInfo();
            }

            return [
                'basic_info' => $this->getBasicInfo($client),
                'contact_info' => $this->getContactInfo($client),
                'employment_info' => $this->getEmploymentInfo($client),
                'financial_info' => $this->getFinancialInfo($client),
                'risk_indicators' => $this->getRiskIndicators($client),
                'demographics' => $this->getDemographics($client),
                'status_indicators' => $this->getStatusIndicators($client)
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching client information: ' . $e->getMessage());
            return $this->getDefaultClientInfo();
        }
    }

    /**
     * Get basic client information
     */
    private function getBasicInfo($client)
    {
        $fullName = trim(implode(' ', array_filter([
            $client->first_name ?? '',
            $client->middle_name ?? '',
            $client->last_name ?? ''
        ])));

        $age = null;
        $yearsToRetirement = null;
        $retirementDate = null;
        $monthsToRetirement = null;

        if ($client->date_of_birth) {
            $dob = Carbon::parse($client->date_of_birth);
            $age = $dob->age;
            $retirementAge = 60; // Configurable
            $yearsToRetirement = max(0, $retirementAge - $age);
            $retirementDate = $dob->copy()->addYears($retirementAge);
            $monthsToRetirement = now()->diffInMonths($retirementDate);
        }

        return [
            'full_name' => $fullName ?: 'N/A',
            'first_name' => $client->first_name ?? 'N/A',
            'middle_name' => $client->middle_name ?? '',
            'last_name' => $client->last_name ?? 'N/A',
            'date_of_birth' => $client->date_of_birth ? Carbon::parse($client->date_of_birth)->format('d/m/Y') : 'N/A',
            'age' => $age,
            'years_to_retirement' => $yearsToRetirement,
            'months_to_retirement' => $monthsToRetirement,
            'retirement_date' => $retirementDate ? $retirementDate->format('d/m/Y') : null,
            'gender' => $client->gender ?? 'N/A',
            'marital_status' => $client->marital_status ?? 'N/A',
            'nationality' => $client->nationality ?? 'N/A',
            'client_number' => $client->client_number ?? 'N/A',
            'member_number' => $client->member_number ?? 'N/A'
        ];
    }

    /**
     * Get contact information
     */
    private function getContactInfo($client)
    {
        return [
            'phone_number' => $client->phone_number ?? $client->mobile_phone_number ?? $client->contact_number ?? 'N/A',
            'mobile_phone' => $client->mobile_phone_number ?? $client->mobile_phone ?? 'N/A',
            'email' => $client->email ?? 'N/A',
            'address' => $this->formatAddress($client),
            'city' => $client->city ?? 'N/A',
            'region' => $client->region ?? 'N/A',
            'district' => $client->district ?? 'N/A',
            'ward' => $client->ward ?? 'N/A'
        ];
    }

    /**
     * Get employment information
     */
    private function getEmploymentInfo($client)
    {
        return [
            'employment_status' => $client->employment ?? 'N/A',
            'employer_name' => $client->employer_name ?? 'N/A',
            'occupation' => $client->occupation ?? 'N/A',
            'business_name' => $client->business_name ?? 'N/A',
            'income_source' => $client->income_source ?? 'N/A',
            'education_level' => $client->education_level ?? $client->education ?? 'N/A',
            'basic_salary' => (float)($client->basic_salary ?? 0),
            'gross_salary' => (float)($client->gross_salary ?? 0),
            'annual_income' => (float)($client->annual_income ?? 0)
        ];
    }

    /**
     * Get financial information
     */
    private function getFinancialInfo($client)
    {
        // Get savings balance
        $savings = DB::table('accounts')
            ->where('product_number', '20000')
            ->where('client_number', $client->client_number)
            ->sum('balance');

        // Get active loans count
        $activeLoans = DB::table('loans')
            ->where('client_number', $client->client_number)
            ->where('status', 'ACTIVE')
            ->count();

        // Get total loan balance
        $totalLoanBalance = DB::table('loans')
            ->join('sub_accounts', 'loans.loan_account_number', '=', 'sub_accounts.account_number')
            ->where('loans.client_number', $client->client_number)
            ->where('loans.status', 'ACTIVE')
            ->sum('sub_accounts.balance');

        return [
            'savings_balance' => (float)$savings,
            'active_loans_count' => $activeLoans,
            'total_loan_balance' => (float)$totalLoanBalance,
            'shares' => (float)($client->hisa ?? 0),
            'akiba' => (float)($client->akiba ?? 0),
            'amana' => (float)($client->amana ?? 0),
            'monthly_expenses' => (float)($client->monthly_expenses ?? 0),
            'income_available' => (float)($client->income_available ?? 0)
        ];
    }

    /**
     * Get risk indicators
     */
    private function getRiskIndicators($client)
    {
        $indicators = [];

        // Age risk
        if ($client->date_of_birth) {
            $age = Carbon::parse($client->date_of_birth)->age;
            if ($age > 55) {
                $indicators[] = ['type' => 'age', 'level' => 'high', 'message' => 'Approaching retirement age'];
            } elseif ($age > 50) {
                $indicators[] = ['type' => 'age', 'level' => 'medium', 'message' => 'Near retirement age'];
            }
        }

        // Employment risk
        if (empty($client->employer_name) && empty($client->business_name)) {
            $indicators[] = ['type' => 'employment', 'level' => 'high', 'message' => 'No employment information'];
        }

        // Income risk
        if (($client->basic_salary ?? 0) < 500000) {
            $indicators[] = ['type' => 'income', 'level' => 'medium', 'message' => 'Low basic salary'];
        }

        // Multiple loans risk
        $activeLoans = DB::table('loans')
            ->where('client_number', $client->client_number)
            ->where('status', 'ACTIVE')
            ->count();
        
        if ($activeLoans > 2) {
            $indicators[] = ['type' => 'loans', 'level' => 'high', 'message' => 'Multiple active loans'];
        }

        return $indicators;
    }

    /**
     * Get demographic information
     */
    private function getDemographics($client)
    {
        return [
            'dependent_count' => $client->dependent_count ?? 0,
            'number_of_children' => $client->number_of_children ?? 0,
            'number_of_spouse' => $client->number_of_spouse ?? 0,
            'religion' => $client->religion ?? 'N/A',
            'place_of_birth' => $client->place_of_birth ?? 'N/A',
            'country_of_birth' => $client->country_of_birth ?? 'N/A'
        ];
    }

    /**
     * Get status indicators
     */
    private function getStatusIndicators($client)
    {
        return [
            'client_status' => $client->client_status ?? 'N/A',
            'membership_type' => $client->membership_type ?? 'N/A',
            'member_category' => $this->getMemberCategoryName($client->member_category),
            'registration_date' => $client->registration_date ? Carbon::parse($client->registration_date)->format('d/m/Y') : 'N/A',
            'accept_terms' => $client->accept_terms ?? false,
            'application_type' => $client->application_type ?? 'N/A'
        ];
    }

    /**
     * Format address
     */
    private function formatAddress($client)
    {
        $addressParts = array_filter([
            $client->main_address,
            $client->street,
            $client->number_of_building,
            $client->city,
            $client->region,
            $client->district
        ]);

        return $addressParts ? implode(', ', $addressParts) : 'N/A';
    }

    /**
     * Get member category name
     */
    private function getMemberCategoryName($categoryId)
    {
        if (!$categoryId) return 'N/A';
        
        return DB::table('member_categories')
            ->where('id', $categoryId)
            ->value('name') ?? 'N/A';
    }

    /**
     * Get default client information
     */
    private function getDefaultClientInfo()
    {
        return [
            'basic_info' => [
                'full_name' => 'N/A',
                'first_name' => 'N/A',
                'middle_name' => '',
                'last_name' => 'N/A',
                'date_of_birth' => 'N/A',
                'age' => null,
                'years_to_retirement' => null,
                'months_to_retirement' => null,
                'retirement_date' => null,
                'gender' => 'N/A',
                'marital_status' => 'N/A',
                'nationality' => 'N/A',
                'client_number' => 'N/A',
                'member_number' => 'N/A'
            ],
            'contact_info' => [
                'phone_number' => 'N/A',
                'mobile_phone' => 'N/A',
                'email' => 'N/A',
                'address' => 'N/A',
                'city' => 'N/A',
                'region' => 'N/A',
                'district' => 'N/A',
                'ward' => 'N/A'
            ],
            'employment_info' => [
                'employment_status' => 'N/A',
                'employer_name' => 'N/A',
                'occupation' => 'N/A',
                'business_name' => 'N/A',
                'income_source' => 'N/A',
                'education_level' => 'N/A',
                'basic_salary' => 0,
                'gross_salary' => 0,
                'annual_income' => 0
            ],
            'financial_info' => [
                'savings_balance' => 0,
                'active_loans_count' => 0,
                'total_loan_balance' => 0,
                'shares' => 0,
                'akiba' => 0,
                'amana' => 0,
                'monthly_expenses' => 0,
                'income_available' => 0
            ],
            'risk_indicators' => [],
            'demographics' => [
                'dependent_count' => 0,
                'number_of_children' => 0,
                'number_of_spouse' => 0,
                'religion' => 'N/A',
                'place_of_birth' => 'N/A',
                'country_of_birth' => 'N/A'
            ],
            'status_indicators' => [
                'client_status' => 'N/A',
                'membership_type' => 'N/A',
                'member_category' => 'N/A',
                'registration_date' => 'N/A',
                'accept_terms' => false,
                'application_type' => 'N/A'
            ]
        ];
    }
}
