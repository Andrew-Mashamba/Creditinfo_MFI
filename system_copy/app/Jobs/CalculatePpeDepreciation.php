<?php

namespace App\Jobs;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Models\PPE;
use App\Services\TransactionPostingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CalculatePpeDepreciation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 300;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = 60;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public $maxExceptions = 5;

    /**
     * Institution ID for depreciation accounts
     */
    private int $institutionId;

    /**
     * Current date for calculations
     */
    private Carbon $currentDate;

    /**
     * Transaction posting service
     */
    private TransactionPostingService $transactionService;

    /**
     * Create a new job instance.
     */
    public function __construct(int $institutionId = 1)
    {
        $this->institutionId = $institutionId;
        $this->currentDate = Carbon::now();
        $this->transactionService = new TransactionPostingService();
        
        Log::info('CalculatePpeDepreciation job initialized', [
            'institution_id' => $this->institutionId,
            'current_date' => $this->currentDate->toDateString()
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();
            
            $this->validateInstitutionAccounts();
            $this->processPpeDepreciation();
            
            DB::commit();
            
            Log::info('PPE depreciation calculation completed successfully', [
                'institution_id' => $this->institutionId,
                'processed_at' => $this->currentDate->toDateTimeString()
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('PPE depreciation calculation failed', [
                'institution_id' => $this->institutionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Validate that institution has required depreciation accounts
     */
    private function validateInstitutionAccounts(): void
    {
        $institution = DB::table('institutions')
            ->where('id', $this->institutionId)
            ->select('depreciation_expense_account', 'accumulated_depreciation_account')
            ->first();

        if (!$institution) {
            throw new Exception("Institution with ID {$this->institutionId} not found");
        }

        if (empty($institution->depreciation_expense_account) || empty($institution->accumulated_depreciation_account)) {
            throw new Exception("Institution {$this->institutionId} missing required depreciation accounts");
        }

        // Validate that the accounts exist
        $depreciationExpenseAccount = AccountsModel::where('account_number', $institution->depreciation_expense_account)->first();
        $accumulatedDepreciationAccount = AccountsModel::where('account_number', $institution->accumulated_depreciation_account)->first();

        if (!$depreciationExpenseAccount || !$accumulatedDepreciationAccount) {
            throw new Exception("One or both depreciation accounts not found in accounts table");
        }
    }

    /**
     * Process depreciation for all PPE records
     */
    private function processPpeDepreciation(): void
    {
        Log::info('Starting PPE depreciation processing', ['institution_id' => $this->institutionId]);

        $processedCount = 0;
        $errorCount = 0;
        $totalDepreciation = 0;

        // Fetch PPE records in chunks for better memory management
        PPE::chunk(100, function ($ppes) use (&$processedCount, &$errorCount, &$totalDepreciation) {
            foreach ($ppes as $ppe) {
                try {
                    $depreciationAmount = $this->processSinglePpe($ppe);
                    $processedCount++;
                    $totalDepreciation += $depreciationAmount;
                } catch (Exception $e) {
                    $errorCount++;
                    Log::error('Failed to process PPE', [
                        'ppe_id' => $ppe->id,
                        'ppe_name' => $ppe->name,
                        'error' => $e->getMessage()
                    ]);
                    
                    // Continue with next PPE instead of failing entire job
                    continue;
                }
            }
        });

        Log::info('PPE depreciation processing completed', [
            'institution_id' => $this->institutionId,
            'processed_count' => $processedCount,
            'error_count' => $errorCount,
            'total_depreciation' => $totalDepreciation
        ]);
    }

    /**
     * Process depreciation for a single PPE record
     * 
     * @return float The depreciation amount processed
     */
    private function processSinglePpe(PPE $ppe): float
    {
        Log::info('Processing PPE depreciation', [
            'ppe_id' => $ppe->id,
            'ppe_name' => $ppe->name,
            'category' => $ppe->category
        ]);

        // Check if depreciation already calculated for current period
        if ($this->isDepreciationAlreadyCalculated($ppe)) {
            Log::info('Depreciation already calculated for current period', [
                'ppe_id' => $ppe->id,
                'period' => $this->currentDate->format('Y-m')
            ]);
            return 0;
        }

        // Calculate depreciation values
        $depreciationData = $this->calculateDepreciation($ppe);
        
        // Update PPE record
        $this->updatePpeRecord($ppe, $depreciationData);
        
        // Post depreciation transaction
        $this->postDepreciationTransaction($ppe, $depreciationData);

        return $depreciationData['depreciation_per_month'];
    }

    /**
     * Calculate depreciation values for a PPE
     */
    private function calculateDepreciation(PPE $ppe): array
    {
        $purchaseDate = Carbon::parse($ppe->purchase_date);
        $monthsInUse = max($this->currentDate->diffInMonths($purchaseDate), 0);
        $initialValue = $ppe->purchase_price * $ppe->quantity;

        // Validate useful life and salvage value
        if ($ppe->useful_life <= 0 || $ppe->salvage_value < 0) {
            Log::warning('Invalid useful life or salvage value for PPE', [
                'ppe_id' => $ppe->id,
                'useful_life' => $ppe->useful_life,
                'salvage_value' => $ppe->salvage_value
            ]);
            
            return [
                'depreciation_per_month' => 0,
                'depreciation_per_year' => 0,
                'accumulated_depreciation' => 0,
                'closing_value' => $initialValue
            ];
        }

        $depreciationPerYear = ($ppe->purchase_price - $ppe->salvage_value) / $ppe->useful_life;
        $depreciationPerMonth = $depreciationPerYear / 12;
        $accumulatedDepreciation = $depreciationPerMonth * $monthsInUse;
        $closingValue = max($initialValue - $accumulatedDepreciation, $ppe->salvage_value);

        Log::info('Depreciation calculated', [
            'ppe_id' => $ppe->id,
            'months_in_use' => $monthsInUse,
            'depreciation_per_month' => $depreciationPerMonth,
            'accumulated_depreciation' => $accumulatedDepreciation,
            'closing_value' => $closingValue
        ]);

        return [
            'depreciation_per_month' => $depreciationPerMonth,
            'depreciation_per_year' => $depreciationPerYear,
            'accumulated_depreciation' => $accumulatedDepreciation,
            'closing_value' => $closingValue
        ];
    }

    /**
     * Update PPE record with calculated depreciation values
     */
    private function updatePpeRecord(PPE $ppe, array $depreciationData): void
    {
        $updateData = [
            'accumulated_depreciation' => $depreciationData['accumulated_depreciation'],
            'depreciation_for_year' => $depreciationData['depreciation_per_year'],
            'depreciation_for_month' => $depreciationData['depreciation_per_month'],
            'closing_value' => $depreciationData['closing_value'],
            'last_depreciation_calculation' => $this->currentDate,
        ];

        $ppe->update($updateData);

        Log::info('PPE record updated', [
            'ppe_id' => $ppe->id,
            'updated_fields' => array_keys($updateData)
        ]);
    }

    /**
     * Post depreciation transaction to general ledger
     */
    private function postDepreciationTransaction(PPE $ppe, array $depreciationData): void
    {
        // Skip if no depreciation to post
        if ($depreciationData['depreciation_per_month'] <= 0) {
            Log::info('Skipping transaction posting - no depreciation amount', ['ppe_id' => $ppe->id]);
            return;
        }

        try {
            // Get institution accounts
            $institution = DB::table('institutions')
                ->where('id', $this->institutionId)
                ->select('depreciation_expense_account', 'accumulated_depreciation_account')
                ->first();

            // Get account details
            $debitAccount = AccountsModel::where('account_number', $institution->depreciation_expense_account)->first();
            $creditAccount = AccountsModel::where('account_number', $institution->accumulated_depreciation_account)->first();

            if (!$debitAccount || !$creditAccount) {
                throw new Exception("Depreciation accounts not found for institution {$this->institutionId}");
            }

            // Prepare transaction data
            $transactionData = [
                'first_account' => $debitAccount,
                'second_account' => $creditAccount,
                'amount' => $depreciationData['depreciation_per_month'],
                'narration' => $this->generateNarration($ppe),
                'reference_number' => $this->generateReferenceNumber($ppe),
                'transaction_date' => $this->currentDate,
            ];

            // Post transaction
            $response = $this->transactionService->postTransaction($transactionData);

            Log::info('Depreciation transaction posted successfully', [
                'ppe_id' => $ppe->id,
                'amount' => $depreciationData['depreciation_per_month'],
                'response' => $response
            ]);

        } catch (Exception $e) {
            Log::error('Failed to post depreciation transaction', [
                'ppe_id' => $ppe->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Generate narration for depreciation transaction
     */
    private function generateNarration(PPE $ppe): string
    {
        return sprintf(
            'Depreciation Expense - Category: %s, Name: %s, Period: %s',
            $ppe->category,
            $ppe->name,
            $this->currentDate->format('F Y')
        );
    }

    /**
     * Check if depreciation has already been calculated for the current period
     */
    private function isDepreciationAlreadyCalculated(PPE $ppe): bool
    {
        // If last_depreciation_calculation is null, it hasn't been calculated
        if (!$ppe->last_depreciation_calculation) {
            return false;
        }

        $lastCalculation = Carbon::parse($ppe->last_depreciation_calculation);
        
        // Check if the last calculation was in the same month and year
        return $lastCalculation->format('Y-m') === $this->currentDate->format('Y-m');
    }

    /**
     * Generate unique reference number for transaction
     */
    private function generateReferenceNumber(PPE $ppe): string
    {
        return sprintf(
            'DEP-%d-%s-%s',
            $ppe->id,
            $this->currentDate->format('Ymd'),
            substr(md5(uniqid() . $ppe->id . $this->currentDate->timestamp), 0, 8)
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('CalculatePpeDepreciation job failed permanently', [
            'institution_id' => $this->institutionId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
