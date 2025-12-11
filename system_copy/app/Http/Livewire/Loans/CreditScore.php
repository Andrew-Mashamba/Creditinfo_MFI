<?php

namespace App\Http\Livewire\Loans;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CreditScoreService;

class CreditScore extends Component
{
    // Credit score data properties (protected to avoid Livewire type restrictions)
    protected $creditScoreData;
    protected $assessmentData;
    protected $loan;
    
    // Extracted credit score values
    public $score = 500;
    public $grade = 'N/A';
    public $risk = 'Unknown';
    public $source = 'Internal';
    public $probabilityOfDefault = 50;
    public $pastDueAmount = 0;
    public $inquiriesLastMonth = 0;
    public $factors = [];
    
    // Simplified display properties
    public $creditScoreValue = 500;
    public $creditScoreGrade = 'N/A';
    public $creditScoreRisk = 'Unknown';
    public $creditScoreTrend = 'Stable';
    public $creditScore = [];
    
    // Display properties
    public $scoreStatus = 'Poor';
    public $scoreColorClass = 'bg-red-100 text-red-800';
    public $riskColorClass = 'text-gray-600';
    public $recommendationClass = 'bg-gray-50 border-gray-200';
    public $recommendationIconColor = 'text-gray-600';
    public $recommendationTextColor = 'text-gray-900';
    public $recommendationText = 'Under Review';
    public $recommendationDetails = 'Credit assessment in progress';
    
    protected $creditScoreService;
    
    public function boot()
    {
        Log::info('=== CREDIT SCORE COMPONENT BOOT START ===', [
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
            'currentloanID' => Session::get('currentloanID')
        ]);
        
        // Initialize credit score service
        $this->creditScoreService = new CreditScoreService();
        
        // Load credit score data
        $this->loadCreditScoreData();
        
        Log::info('=== CREDIT SCORE COMPONENT BOOT END ===', [
            'score' => $this->score,
            'grade' => $this->grade,
            'source' => $this->source
        ]);
    }
    
    public function mount()
    {
        Log::info('=== CREDIT SCORE COMPONENT MOUNT ===', [
            'timestamp' => now()->toISOString()
        ]);
        
        // Data is already loaded in boot, just process display properties
        $this->processDisplayProperties();
    }
    
    /**
     * Load credit score data from loan assessment data
     */
    private function loadCreditScoreData(): void
    {
        try {
            // Get loan ID from session
            $loanId = Session::get('currentloanID');
            
            if (!$loanId) {
                Log::warning('No loan ID found in session');
                return;
            }
            
            // Load loan data
            $this->loan = DB::table('loans')->where('id', $loanId)->first();
            
            if (!$this->loan || empty($this->loan->assessment_data)) {
                Log::warning('No loan or assessment data found', ['loan_id' => $loanId]);
                return;
            }
            
            // Parse assessment data
            $this->assessmentData = json_decode($this->loan->assessment_data, true);
            
            // Try multiple possible locations for credit score data
            if (isset($this->assessmentData['credit_score_data'])) {
                $this->creditScoreData = $this->assessmentData['credit_score_data'];
            } elseif (isset($this->assessmentData['assessment_result']['credit_score'])) {
                $this->creditScoreData = $this->assessmentData['assessment_result']['credit_score'];
            }
            
            // Extract credit score values
            if ($this->creditScoreData) {
                $this->extractCreditScoreValues();
            }
            
            Log::info('Credit score data loaded successfully', [
                'loan_id' => $loanId,
                'score' => $this->score,
                'source' => $this->source
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading credit score data: ' . $e->getMessage());
        }
    }
    
    /**
     * Extract credit score values from the data
     */
    private function extractCreditScoreValues(): void
    {
        $this->score = $this->creditScoreData['score'] ?? 500;
        $this->grade = $this->creditScoreData['grade'] ?? 'N/A';
        $this->risk = $this->creditScoreData['risk'] ?? 
                      $this->creditScoreData['risk_description'] ?? 'Unknown';
        $this->source = $this->creditScoreData['source'] ?? 'Internal';
        $this->probabilityOfDefault = $this->creditScoreData['probability_of_default'] ?? 
                                      $this->creditScoreData['crb_data']['probability_of_default'] ?? 50;
        
        // CRB specific data
        $this->pastDueAmount = $this->creditScoreData['crb_data']['past_due_amount'] ?? 0;
        $this->inquiriesLastMonth = $this->creditScoreData['crb_data']['inquiries_last_month'] ?? 0;
        
        // Credit factors
        $this->factors = $this->creditScoreData['factors'] ?? [];
        
        // Set simplified display properties
        $this->creditScoreValue = $this->score;
        $this->creditScoreGrade = $this->grade;
        $this->creditScoreRisk = $this->risk;
        
        // Determine trend based on score
        if ($this->score >= 700) {
            $this->creditScoreTrend = 'Improving ↑';
        } elseif ($this->score >= 500) {
            $this->creditScoreTrend = 'Stable →';
        } else {
            $this->creditScoreTrend = 'Declining ↓';
        }
        
        // Set credit score array for reasons
        $this->creditScore = [
            'reasons' => $this->creditScoreData['reasons'] ?? 
                        $this->creditScoreData['factors'] ?? 
                        ['Payment history', 'Credit utilization']
        ];
    }
    
    /**
     * Process display properties based on credit score
     */
    private function processDisplayProperties(): void
    {
        // Determine score status and color
        $this->determineScoreStatus();
        
        // Determine risk color
        $this->determineRiskColor();
        
        // Determine recommendation
        $this->determineRecommendation();
    }
    
    /**
     * Determine score status and color class
     */
    private function determineScoreStatus(): void
    {
        if ($this->score >= 750) {
            $this->scoreStatus = 'Excellent';
            $this->scoreColorClass = 'bg-green-100 text-green-800';
        } elseif ($this->score >= 700) {
            $this->scoreStatus = 'Very Good';
            $this->scoreColorClass = 'bg-green-100 text-green-800';
        } elseif ($this->score >= 650) {
            $this->scoreStatus = 'Good';
            $this->scoreColorClass = 'bg-blue-100 text-blue-800';
        } elseif ($this->score >= 600) {
            $this->scoreStatus = 'Fair';
            $this->scoreColorClass = 'bg-yellow-100 text-yellow-800';
        } elseif ($this->score >= 550) {
            $this->scoreStatus = 'Below Average';
            $this->scoreColorClass = 'bg-orange-100 text-orange-800';
        } else {
            $this->scoreStatus = 'Poor';
            $this->scoreColorClass = 'bg-red-100 text-red-800';
        }
    }
    
    /**
     * Determine risk color class
     */
    private function determineRiskColor(): void
    {
        if ($this->probabilityOfDefault <= 10) {
            $this->riskColorClass = 'text-green-600';
        } elseif ($this->probabilityOfDefault <= 25) {
            $this->riskColorClass = 'text-blue-600';
        } elseif ($this->probabilityOfDefault <= 50) {
            $this->riskColorClass = 'text-yellow-600';
        } else {
            $this->riskColorClass = 'text-red-600';
        }
    }
    
    /**
     * Determine recommendation based on score
     */
    private function determineRecommendation(): void
    {
        if ($this->score >= 650) {
            $this->recommendationClass = 'bg-green-50 border-green-200';
            $this->recommendationIconColor = 'text-green-600';
            $this->recommendationTextColor = 'text-green-900';
            $this->recommendationText = 'Approved for Loan';
            $this->recommendationDetails = 'Client meets credit requirements';
        } elseif ($this->score >= 550) {
            $this->recommendationClass = 'bg-yellow-50 border-yellow-200';
            $this->recommendationIconColor = 'text-yellow-600';
            $this->recommendationTextColor = 'text-yellow-900';
            $this->recommendationText = 'Conditional Approval';
            $this->recommendationDetails = 'Additional collateral or guarantor required';
        } else {
            $this->recommendationClass = 'bg-red-50 border-red-200';
            $this->recommendationIconColor = 'text-red-600';
            $this->recommendationTextColor = 'text-red-900';
            $this->recommendationText = 'High Risk';
            $this->recommendationDetails = 'Does not meet minimum credit requirements';
        }
    }
    
    /**
     * Calculate score position for visual indicator
     */
    public function getScorePosition(): float
    {
        return min(100, max(0, (($this->score - 300) / 550) * 100));
    }
    
    /**
     * Calculate factor percentages for progress bars
     */
    public function getFactorPercentages(): array
    {
        return [
            'payment_history' => min(100, max(0, ($this->score - 300) / 5.5)),
            'credit_util' => min(100, max(0, ($this->score - 350) / 5)),
            'credit_length' => min(100, max(0, ($this->score - 400) / 4.5)),
            'credit_mix' => min(100, max(0, ($this->score - 300) / 5.5)),
            'new_credit' => min(100, max(0, ($this->score - 250) / 6))
        ];
    }
    
    public function render()
    {
        return view('livewire.loans.credit-score');
    }
}