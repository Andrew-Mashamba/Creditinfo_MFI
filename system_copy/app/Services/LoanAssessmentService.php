<?php

namespace App\Services;

use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\loans_schedules;
use App\Services\LoanRiskAssessmentService;
use App\Services\LoanAffordabilityService;
use App\Services\LoanRecommendationService;
use App\Services\LoanConditionsService;
use App\Exceptions\LoanAssessmentException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LoanAssessmentService
{
    protected $riskService;
    protected $affordabilityService;
    protected $recommendationService;
    protected $conditionsService;

    public function __construct(
        LoanRiskAssessmentService $riskService,
        LoanAffordabilityService $affordabilityService,
        LoanRecommendationService $recommendationService,
        LoanConditionsService $conditionsService
    ) {
        $this->riskService = $riskService;
        $this->affordabilityService = $affordabilityService;
        $this->recommendationService = $recommendationService;
        $this->conditionsService = $conditionsService;
    }

    public function assessLoan($loanId)
    {
        try {
            Log::info('Starting loan assessment', ['loan_id' => $loanId]);
            
            $loan = $this->getLoanDetails($loanId);
            
            if (!$loan) {
                throw new LoanAssessmentException("Loan not found: {$loanId}");
            }

            $assessment = [
                'loan_id' => $loanId,
                'risk_score' => $this->riskService->calculate($loan),
                'affordability' => $this->affordabilityService->calculate($loan),
                'recommendation' => $this->recommendationService->generate($loan),
                'conditions' => $this->conditionsService->determine($loan),
                'assessed_at' => now(),
                'assessor_id' => auth()->id()
            ];

            Log::info('Loan assessment completed', [
                'loan_id' => $loanId,
                'risk_score' => $assessment['risk_score'],
                'recommendation' => $assessment['recommendation']
            ]);

            return $assessment;

        } catch (LoanAssessmentException $e) {
            Log::error('Loan assessment failed', [
                'loan_id' => $loanId,
                'error' => $e->getMessage(),
                'context' => $e->getContext() ?? []
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error in loan assessment', [
                'loan_id' => $loanId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new LoanAssessmentException("Assessment failed: " . $e->getMessage());
        }
    }

    public function getLoanDetails($loanId)
    {
        return Cache::remember("loan_details_{$loanId}", 300, function() use ($loanId) {
            return LoansModel::with(['client', 'schedules', 'collateral', 'loanProduct'])
                ->findOrFail($loanId);
        });
    }

    public function getClientCreditHistory($clientNumber)
    {
        return Cache::remember("credit_history_{$clientNumber}", 600, function() use ($clientNumber) {
            return $this->calculateCreditHistory($clientNumber);
        });
    }

    protected function calculateCreditHistory($clientNumber)
    {
        $loans = LoansModel::where('client_number', $clientNumber)->get();
        
        $history = [
            'total_loans' => $loans->count(),
            'active_loans' => $loans->where('status', 'ACTIVE')->count(),
            'paid_loans' => $loans->where('status', 'PAID')->count(),
            'defaulted_loans' => $loans->where('status', 'DEFAULTED')->count(),
            'total_borrowed' => $loans->sum('principle'),
            'total_paid' => $loans->where('status', 'PAID')->sum('principle'),
            'average_loan_amount' => $loans->avg('principle'),
            'days_in_arrears' => $loans->max('days_in_arrears'),
            'credit_score' => $this->calculateCreditScore($loans)
        ];

        return $history;
    }

    protected function calculateCreditScore($loans)
    {
        $score = 300; // Base score

        // Payment history (40% weight)
        $paymentHistory = $loans->where('status', 'PAID')->count() / max($loans->count(), 1);
        $score += $paymentHistory * 200;

        // Amount owed (30% weight)
        $totalOwed = $loans->where('status', 'ACTIVE')->sum('principle');
        $totalBorrowed = $loans->sum('principle');
        $utilizationRate = $totalBorrowed > 0 ? $totalOwed / $totalBorrowed : 0;
        $score += (1 - $utilizationRate) * 150;

        // Length of credit history (15% weight)
        $oldestLoan = $loans->min('created_at');
        $creditAge = $oldestLoan ? now()->diffInYears($oldestLoan) : 0;
        $score += min($creditAge * 10, 75);

        // Recent activity (15% weight)
        $recentLoans = $loans->where('created_at', '>=', now()->subYears(2))->count();
        $score += min($recentLoans * 5, 75);

        return min(max($score, 300), 850);
    }

    public function validateAssessment($assessment)
    {
        $errors = [];

        if (!isset($assessment['risk_score'])) {
            $errors[] = 'Risk score is required';
        }

        if (!isset($assessment['affordability'])) {
            $errors[] = 'Affordability calculation is required';
        }

        if (!isset($assessment['recommendation'])) {
            $errors[] = 'Recommendation is required';
        }

        if (empty($assessment['conditions'])) {
            $errors[] = 'At least one condition must be specified';
        }

        return $errors;
    }

    public function saveAssessment($loanId, $assessment)
    {
        DB::transaction(function() use ($loanId, $assessment) {
            $loan = LoansModel::findOrFail($loanId);
            
            $loan->update([
                'heath' => $assessment['risk_score']['level'],
                'approved_loan_value' => $assessment['recommendation']['approved_amount'] ?? $loan->principle,
                'approved_term' => $assessment['recommendation']['approved_term'] ?? $loan->tenure
            ]);

            // Save assessment details to audit log
            $this->logAssessment($loanId, $assessment);
        });
    }

    protected function logAssessment($loanId, $assessment)
    {
        // This would be implemented to save assessment details
        // to a dedicated assessment log table
        Log::info('Assessment saved', [
            'loan_id' => $loanId,
            'assessment' => $assessment
        ]);
    }
} 