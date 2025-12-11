<?php

namespace App\Services\LoanApplication;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoanAssessmentService
{
    public function assessCredit($clientNumber, $loanAmount, $salaryTakeHome)
    {
        try {
            Log::info('LoanAssessmentService: Starting credit assessment', [
                'client_number' => $clientNumber,
                'loan_amount' => $loanAmount,
                'salary_take_home' => $salaryTakeHome
            ]);

            // Get client details
            $client = DB::table('clients')->where('client_number', $clientNumber)->first();
            
            if (!$client) {
                Log::warning('LoanAssessmentService: Client not found', ['client_number' => $clientNumber]);
                return $this->getDefaultCreditScore();
            }

            // First try to get CRB score (if CRB service exists)
            $crbScore = $this->fetchCrbScore($client);
            
            if ($crbScore && isset($crbScore['score'])) {
                // Use CRB score
                $score = intval($crbScore['score']);
                $grade = $crbScore['grade'] ?? 'XX';
                $risk = $crbScore['risk_description'] ?? 'Unknown';
                $factors = [];
                
                // Add CRB-specific factors
                if (isset($crbScore['contracts'])) {
                    $factors['crb_active_loans'] = count($crbScore['contracts']) . ' active loans in CRB';
                }
                if (isset($crbScore['past_due_amount'])) {
                    $factors['crb_past_due'] = 'Past due: TZS ' . number_format($crbScore['past_due_amount'], 2);
                }
                if (isset($crbScore['inquiries_last_month'])) {
                    $factors['crb_inquiries'] = $crbScore['inquiries_last_month'] . ' inquiries last month';
                }
                
                return [
                    'score' => $score,
                    'grade' => $grade,
                    'risk' => $risk,
                    'factors' => $factors,
                    'source' => 'CRB',
                    'probability_of_default' => $this->calculateProbabilityOfDefault($score),
                    'crb_data' => [
                        'past_due_amount' => $crbScore['past_due_amount'] ?? 0,
                        'inquiries_last_month' => $crbScore['inquiries_last_month'] ?? 0
                    ]
                ];
            } else {
                // Fallback to internal calculation
                return $this->calculateInternalCreditScore($clientNumber, $loanAmount, $salaryTakeHome);
            }
            
        } catch (\Exception $e) {
            Log::error('LoanAssessmentService: Error during credit assessment', [
                'error' => $e->getMessage(),
                'client_number' => $clientNumber
            ]);
            
            return $this->getDefaultCreditScore();
        }
    }
    
    protected function fetchCrbScore($client)
    {
        try {
            // Check if CRB service exists
            if (!class_exists('\App\Services\CrbService')) {
                Log::info('LoanAssessmentService: CRB service not available, using internal scoring');
                return null;
            }
            
            $crbService = new \App\Services\CrbService();
            
            // Prepare client data for CRB
            $clientData = [
                'first_name' => $client->first_name ?? '',
                'surname' => $client->last_name ?? '',
                'full_name' => trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? '')),
                'id_number' => $client->national_id ?? $client->identification_number ?? '',
                'date_of_birth' => $client->date_of_birth ?? '',
                'phone_number' => $client->mobile_phone_number ?? '',
                'id_type' => 'NationalID',
            ];
            
            Log::info('LoanAssessmentService: Fetching CRB score', ['client_data' => $clientData]);
            
            $response = $crbService->getCreditScore($clientData);
            
            if ($response && isset($response['score'])) {
                Log::info('LoanAssessmentService: CRB score retrieved successfully', [
                    'score' => $response['score'],
                    'grade' => $response['grade'] ?? 'Unknown'
                ]);
                return $response;
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('LoanAssessmentService: Error fetching CRB score', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    protected function calculateInternalCreditScore($clientNumber, $loanAmount, $salaryTakeHome)
    {
        Log::info('LoanAssessmentService: Calculating internal credit score');
        
        // Cast parameters to ensure they are numeric
        $loanAmount = (float) $loanAmount;
        $salaryTakeHome = (float) $salaryTakeHome;
        
        $score = 500; // Base score
        $factors = [];
        
        // Get existing loans
        $existingLoans = DB::table('loans')
            ->where('client_number', $clientNumber)
            ->whereIn('status', ['ACTIVE', 'PENDING'])
            ->get();
            
        // Get member savings
        $totalSavings = (float) DB::table('accounts')
            ->where('client_number', $clientNumber)
            ->where('account_type', 'like', '%savings%')
            ->sum('balance');
        
        // Payment history (35%)
        $loanCount = count($existingLoans);
        if ($loanCount == 0) {
            $score += 50;
            $factors['payment_history'] = 'No loan history';
        } else {
            // Check for defaults or arrears
            $defaultedLoans = DB::table('loans')
                ->where('client_number', $clientNumber)
                ->whereIn('status', ['DEFAULTED', 'WRITTEN_OFF'])
                ->count();
            
            if ($defaultedLoans > 0) {
                $score -= 100;
                $factors['payment_history'] = 'Previous defaults';
            } else {
                $score += 100;
                $factors['payment_history'] = 'Good payment history';
            }
        }
        
        // Debt utilization (30%)
        $monthlyInstallment = $this->calculateMonthlyInstallment($loanAmount, 12, 15); // Assuming 15% interest, 12 months
        $debtToIncomeRatio = $salaryTakeHome > 0 ? ($monthlyInstallment / $salaryTakeHome) * 100 : 0;
        
        if ($debtToIncomeRatio < 30) {
            $score += 90;
            $factors['debt_utilization'] = 'Low debt utilization';
        } elseif ($debtToIncomeRatio < 50) {
            $score += 45;
            $factors['debt_utilization'] = 'Moderate debt utilization';
        } else {
            $score -= 60;
            $factors['debt_utilization'] = 'High debt utilization';
        }
        
        // Credit history length (15%)
        $client = DB::table('clients')->where('client_number', $clientNumber)->first();
        if ($client && $client->created_at) {
            $membershipDuration = now()->diffInMonths($client->created_at);
            if ($membershipDuration > 24) {
                $score += 45;
                $factors['credit_length'] = 'Long membership';
            } elseif ($membershipDuration > 12) {
                $score += 30;
                $factors['credit_length'] = 'Moderate membership';
            } else {
                $score += 15;
                $factors['credit_length'] = 'New member';
            }
        }
        
        // Member funds (20%)
        $fundsRatio = $loanAmount > 0 ? ($totalSavings / $loanAmount) * 100 : 0;
        if ($fundsRatio > 50) {
            $score += 60;
            $factors['member_funds'] = 'Strong member funds';
        } elseif ($fundsRatio > 25) {
            $score += 40;
            $factors['member_funds'] = 'Good member funds';
        } else {
            $score += 20;
            $factors['member_funds'] = 'Low member funds';
        }
        
        // Cap score between 300 and 850
        $score = max(300, min(850, $score));
        
        // Determine grade and risk
        $gradeData = $this->getGradeFromScore($score);
        
        Log::info('LoanAssessmentService: Internal credit score calculated', [
            'score' => $score,
            'grade' => $gradeData['grade'],
            'factors' => $factors
        ]);
        
        return [
            'score' => $score,
            'grade' => $gradeData['grade'],
            'risk' => $gradeData['description'],
            'factors' => $factors,
            'source' => 'Internal',
            'probability_of_default' => $this->calculateProbabilityOfDefault($score),
            'crb_data' => [
                'past_due_amount' => 0,
                'inquiries_last_month' => 0
            ]
        ];
    }
    
    protected function calculateMonthlyInstallment($loanAmount, $termMonths, $interestRate)
    {
        // Cast parameters to ensure they are numeric
        $loanAmount = (float) $loanAmount;
        $termMonths = (int) $termMonths;
        $interestRate = (float) $interestRate;
        
        $monthlyRate = $interestRate / (100 * 12);
        if ($monthlyRate == 0) {
            return $loanAmount / $termMonths;
        }
        
        return $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) / (pow(1 + $monthlyRate, $termMonths) - 1);
    }
    
    protected function calculateProbabilityOfDefault($score)
    {
        // Simple probability calculation based on score
        if ($score >= 750) return 5;
        if ($score >= 700) return 10;
        if ($score >= 650) return 15;
        if ($score >= 600) return 25;
        if ($score >= 550) return 35;
        if ($score >= 500) return 50;
        return 75;
    }
    
    protected function getGradeFromScore($score)
    {
        if ($score >= 800) return ['grade' => 'A+', 'description' => 'Excellent'];
        if ($score >= 750) return ['grade' => 'A', 'description' => 'Very Good'];
        if ($score >= 700) return ['grade' => 'B+', 'description' => 'Good'];
        if ($score >= 650) return ['grade' => 'B', 'description' => 'Fair'];
        if ($score >= 600) return ['grade' => 'C+', 'description' => 'Average'];
        if ($score >= 550) return ['grade' => 'C', 'description' => 'Below Average'];
        if ($score >= 500) return ['grade' => 'D', 'description' => 'Poor'];
        return ['grade' => 'E', 'description' => 'Very Poor'];
    }
    
    protected function getDefaultCreditScore()
    {
        return [
            'score' => 500,
            'grade' => 'C',
            'risk' => 'Average',
            'factors' => ['assessment_error' => 'Unable to complete full assessment'],
            'source' => 'Default',
            'probability_of_default' => 50,
            'crb_data' => [
                'past_due_amount' => 0,
                'inquiries_last_month' => 0
            ]
        ];
    }
}