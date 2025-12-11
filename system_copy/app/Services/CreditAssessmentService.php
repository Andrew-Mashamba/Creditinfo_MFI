<?php

namespace App\Services\LoanApplication;

use App\Models\MembersModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Service for handling loan assessment and credit scoring
 */
class LoanAssessmentService
{
    protected LoanCalculationService $calculationService;
    protected LoanExceptionService $exceptionService;
    
    public function __construct(
        LoanCalculationService $calculationService,
        LoanExceptionService $exceptionService
    ) {
        $this->calculationService = $calculationService;
        $this->exceptionService = $exceptionService;
    }
    
    /**
     * Run complete loan assessment
     */
    public function assessLoan(LoanApplicationData $data): array
    {
        try {
            Log::info('Starting loan assessment', ['member_id' => $data->member_id]);
            
            // Step 1: Fetch member financial information
            $memberFinancialInfo = $this->fetchMemberFinancialInfo($data->member_id);
            
            // Step 2: Calculate credit score
            $creditScore = $this->calculateCreditScore($data);
            
            // Step 3: Perform loan calculations
            $calculations = $this->calculationService->calculateAssessment($data);
            
            // Step 4: Check for exceptions
            $exceptions = $this->exceptionService->evaluateExceptions($data, $calculations, $creditScore);
            
            // Step 5: Determine loan decision
            $decision = $this->determineLoanDecision($exceptions, $creditScore);
            
            // Step 6: Generate settlement data if applicable
            $settlementData = $this->generateSettlementData($data);
            
            // Compile complete assessment
            $assessment = [
                'member_financial_info' => $memberFinancialInfo,
                'credit_score' => $creditScore,
                'calculations' => $calculations,
                'exceptions' => $exceptions,
                'settlement_data' => $settlementData,
                'decision' => $decision,
                'assessment_date' => now(),
                'assessed_by' => Auth::id(),
            ];
            
            // Cache the assessment
            $this->cacheAssessment($data->member_id, $assessment);
            
            Log::info('Loan assessment completed', [
                'member_id' => $data->member_id,
                'decision' => $decision['recommendation'],
            ]);
            
            return $assessment;
            
        } catch (\Exception $e) {
            Log::error('Error during loan assessment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Fetch member financial information
     */
    protected function fetchMemberFinancialInfo(int $memberId): array
    {
        $member = MembersModel::find($memberId);
        
        if (!$member) {
            throw new \Exception('Member not found');
        }
        
        // Fetch account balances
        $accounts = DB::table('accounts')
            ->where('client_number', $member->client_number)
            ->get();
            
        $totalSavings = $accounts->where('account_type', 'SAVINGS')->sum(\DB::raw('CAST(balance AS DECIMAL(15,2))'));
        $totalDeposits = $accounts->where('account_type', 'DEPOSITS')->sum(\DB::raw('CAST(balance AS DECIMAL(15,2))'));
        $totalShares = $accounts->where('account_type', 'SHARES')->sum(\DB::raw('CAST(balance AS DECIMAL(15,2))'));
        
        // Fetch existing loans
        $existingLoans = DB::table('loans')
            ->where('client_number', $member->client_number)
            ->whereIn('loan_status', ['ACTIVE', 'DISBURSED'])
            ->get();
            
        $totalLoanBalance = $existingLoans->sum(\DB::raw('CAST(balance AS DECIMAL(15,2))'));
        $monthlyLoanPayments = $existingLoans->sum('monthly_installment');
        
        // Calculate debt service ratio
        $monthlyIncome = $member->monthly_income ?? 0;
        $debtServiceRatio = $monthlyIncome > 0 ? ($monthlyLoanPayments / $monthlyIncome) * 100 : 0;
        
        return [
            'member_id' => $memberId,
            'client_number' => $member->client_number,
            'membership_duration_days' => now()->diffInDays($member->created_at),
            'accounts' => [
                'savings' => $totalSavings,
                'deposits' => $totalDeposits,
                'shares' => $totalShares,
                'total' => $totalSavings + $totalDeposits + $totalShares,
            ],
            'existing_loans' => [
                'count' => $existingLoans->count(),
                'total_balance' => $totalLoanBalance,
                'monthly_payments' => $monthlyLoanPayments,
            ],
            'financial_ratios' => [
                'debt_service_ratio' => round($debtServiceRatio, 2),
                'available_income' => $monthlyIncome - $monthlyLoanPayments,
            ],
            'employment' => [
                'status' => $member->employment_status,
                'employer' => $member->employer_name,
                'duration' => $member->employment_duration,
            ],
        ];
    }
    
    /**
     * Calculate credit score
     */
    public function calculateCreditScore(LoanApplicationData $data): array
    {
        // Check cache first
        $cacheKey = "credit_score_{$data->member_id}";
        $cached = Cache::get($cacheKey);
        
        if ($cached && $cached['calculated_at'] > now()->subHours(24)) {
            Log::info('Using cached credit score', ['member_id' => $data->member_id]);
            return $cached;
        }
        
        try {
            // Try external CRB first
            $crbScore = $this->fetchCRBScore($data->member_id);
            
            if ($crbScore) {
                $score = $crbScore;
            } else {
                // Fall back to internal calculation
                $score = $this->calculateInternalCreditScore($data);
            }
            
            // Cache the score
            Cache::put($cacheKey, $score, now()->addDays(7));
            
            return $score;
            
        } catch (\Exception $e) {
            Log::error('Error calculating credit score', [
                'error' => $e->getMessage(),
                'member_id' => $data->member_id,
            ]);
            
            // Return default score on error
            return [
                'score' => 500,
                'grade' => 'C',
                'source' => 'DEFAULT',
                'factors' => ['Error calculating score'],
                'calculated_at' => now(),
            ];
        }
    }
    
    /**
     * Fetch CRB score from external service
     */
    protected function fetchCRBScore(int $memberId): ?array
    {
        $member = MembersModel::find($memberId);
        
        if (!$member || !$member->national_id) {
            return null;
        }
        
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.crb.api_key'),
                    'Content-Type' => 'application/json',
                ])
                ->post(config('services.crb.endpoint'), [
                    'national_id' => $member->national_id,
                    'consent' => true,
                ]);
                
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'score' => $data['credit_score'] ?? 500,
                    'grade' => $data['credit_grade'] ?? 'C',
                    'source' => 'CRB',
                    'factors' => $data['factors'] ?? [],
                    'report_date' => $data['report_date'] ?? now(),
                    'calculated_at' => now(),
                ];
            }
        } catch (\Exception $e) {
            Log::warning('CRB score fetch failed', [
                'error' => $e->getMessage(),
                'member_id' => $memberId,
            ]);
        }
        
        return null;
    }
    
    /**
     * Calculate internal credit score
     */
    protected function calculateInternalCreditScore(LoanApplicationData $data): array
    {
        $member = MembersModel::find($data->member_id);
        $baseScore = 500;
        $factors = [];
        
        // Factor 1: Membership duration (max +100)
        $membershipMonths = now()->diffInMonths($member->created_at);
        $membershipScore = min($membershipMonths * 2, 100);
        $baseScore += $membershipScore;
        $factors[] = "Membership duration: +{$membershipScore}";
        
        // Factor 2: Account activity (max +50)
        $accountActivity = $this->calculateAccountActivityScore($member->client_number);
        $baseScore += $accountActivity;
        $factors[] = "Account activity: +{$accountActivity}";
        
        // Factor 3: Loan repayment history (max +150, min -200)
        $repaymentScore = $this->calculateRepaymentHistoryScore($member->client_number);
        $baseScore += $repaymentScore;
        $factors[] = "Repayment history: " . ($repaymentScore >= 0 ? "+{$repaymentScore}" : $repaymentScore);
        
        // Factor 4: Current debt burden (max -100)
        $debtScore = $this->calculateDebtBurdenScore($data);
        $baseScore += $debtScore;
        $factors[] = "Debt burden: {$debtScore}";
        
        // Factor 5: Savings behavior (max +50)
        $savingsScore = $this->calculateSavingsScore($member->client_number);
        $baseScore += $savingsScore;
        $factors[] = "Savings behavior: +{$savingsScore}";
        
        // Ensure score is within bounds
        $finalScore = max(300, min(850, $baseScore));
        
        // Determine grade
        $grade = $this->determineGrade($finalScore);
        
        return [
            'score' => $finalScore,
            'grade' => $grade,
            'source' => 'INTERNAL',
            'factors' => $factors,
            'calculated_at' => now(),
        ];
    }
    
    /**
     * Calculate account activity score
     */
    protected function calculateAccountActivityScore(string $clientNumber): int
    {
        $recentTransactions = DB::table('transactions')
            ->where('client_number', $clientNumber)
            ->where('created_at', '>=', now()->subMonths(6))
            ->count();
            
        return min($recentTransactions / 10, 50);
    }
    
    /**
     * Calculate repayment history score
     */
    protected function calculateRepaymentHistoryScore(string $clientNumber): int
    {
        $loans = DB::table('loans')
            ->where('client_number', $clientNumber)
            ->whereIn('loan_status', ['CLOSED', 'ACTIVE'])
            ->get();
            
        if ($loans->isEmpty()) {
            return 0;
        }
        
        $score = 0;
        
        foreach ($loans as $loan) {
            $payments = DB::table('loan_payments')
                ->where('loan_id', $loan->loan_id)
                ->get();
                
            $onTimePayments = $payments->where('days_late', 0)->count();
            $latePayments = $payments->where('days_late', '>', 0)->count();
            $defaultedPayments = $payments->where('days_late', '>', 90)->count();
            
            $loanScore = ($onTimePayments * 5) - ($latePayments * 10) - ($defaultedPayments * 50);
            $score += $loanScore;
        }
        
        return max(-200, min(150, $score));
    }
    
    /**
     * Calculate debt burden score
     */
    protected function calculateDebtBurdenScore(LoanApplicationData $data): int
    {
        $monthlyIncome = $data->salaryTakeHome + ($data->monthlyIncome ?? 0);
        $monthlyObligations = $data->otherLoansAmount ?? 0;
        
        if ($monthlyIncome <= 0) {
            return -100;
        }
        
        $debtRatio = $monthlyObligations / $monthlyIncome;
        
        if ($debtRatio <= 0.2) {
            return 0;
        } elseif ($debtRatio <= 0.4) {
            return -25;
        } elseif ($debtRatio <= 0.6) {
            return -50;
        } else {
            return -100;
        }
    }
    
    /**
     * Calculate savings score
     */
    protected function calculateSavingsScore(string $clientNumber): int
    {
        $avgBalance = DB::table('accounts')
            ->where('client_number', $clientNumber)
            ->where('account_type', 'SAVINGS')
            ->avg('balance') ?? 0;
            
        $consistentSaving = DB::table('transactions')
            ->where('client_number', $clientNumber)
            ->where('transaction_type', 'DEPOSIT')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->havingRaw('COUNT(*) >= 2')
            ->count();
            
        $balanceScore = min($avgBalance / 100000, 25); // Max 25 points for balance
        $consistencyScore = $consistentSaving * 5; // 5 points per consistent month
        
        return min($balanceScore + $consistencyScore, 50);
    }
    
    /**
     * Determine credit grade
     */
    protected function determineGrade(int $score): string
    {
        if ($score >= 750) return 'A';
        if ($score >= 700) return 'B';
        if ($score >= 650) return 'C';
        if ($score >= 600) return 'D';
        if ($score >= 550) return 'E';
        return 'F';
    }
    
    /**
     * Determine loan decision based on assessment
     */
    protected function determineLoanDecision(array $exceptions, array $creditScore): array
    {
        $recommendation = 'PENDING';
        $reasons = [];
        $conditions = [];
        
        // Check exceptions first
        if ($exceptions['summary']['overall_status'] === 'REJECTED') {
            $recommendation = 'REJECT';
            $reasons[] = 'Failed critical exception checks';
        } elseif ($exceptions['summary']['overall_status'] === 'REVIEW_REQUIRED') {
            $recommendation = 'CONDITIONAL_APPROVAL';
            $reasons[] = 'Requires exception approval';
            $conditions[] = 'Exception approval required from credit committee';
        } elseif ($exceptions['summary']['overall_status'] === 'APPROVED') {
            // Check credit score
            if ($creditScore['score'] < 400) {
                $recommendation = 'REJECT';
                $reasons[] = 'Credit score below minimum threshold';
            } elseif ($creditScore['score'] < 500) {
                $recommendation = 'CONDITIONAL_APPROVAL';
                $reasons[] = 'Low credit score';
                $conditions[] = 'Requires additional collateral or guarantor';
            } else {
                $recommendation = 'APPROVE';
                $reasons[] = 'Meets all requirements';
            }
        }
        
        return [
            'recommendation' => $recommendation,
            'reasons' => $reasons,
            'conditions' => $conditions,
            'decided_at' => now(),
        ];
    }
    
    /**
     * Generate settlement data for existing loans
     */
    protected function generateSettlementData(LoanApplicationData $data): array
    {
        $existingLoans = DB::table('loans')
            ->where('client_number', Auth::user()->client_number)
            ->whereIn('loan_status', ['ACTIVE', 'DISBURSED'])
            ->get();
            
        $settlements = [];
        $totalSettlementAmount = 0;
        
        foreach ($existingLoans as $loan) {
            if ($loan->can_be_settled) {
                $settlementAmount = $loan->balance;
                $settlements[] = [
                    'loan_id' => $loan->loan_id,
                    'loan_account' => $loan->loan_account_number,
                    'institution' => 'INTERNAL',
                    'account' => $loan->loan_account_number,
                    'amount' => $settlementAmount,
                    'balance' => $loan->balance,
                    'monthly_payment' => $loan->monthly_installment,
                    'status' => $loan->loan_status,
                ];
                $totalSettlementAmount += $settlementAmount;
            }
        }
        
        return [
            'has_settlements' => count($settlements) > 0,
            'count' => count($settlements),
            'total_amount' => $totalSettlementAmount,
            'settlements' => $settlements,
        ];
    }
    
    /**
     * Cache assessment results
     */
    protected function cacheAssessment(int $memberId, array $assessment): void
    {
        $cacheKey = "loan_assessment_{$memberId}";
        Cache::put($cacheKey, $assessment, now()->addHours(2));
    }
}