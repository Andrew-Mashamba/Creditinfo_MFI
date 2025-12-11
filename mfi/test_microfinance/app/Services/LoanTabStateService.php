<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\LoansModel;
use App\Models\loan_images;
use App\Models\ClientsModel;

class LoanTabStateService
{
    /**
     * Save tab state data for a specific tab
     */
    public function saveTabState($loanId, $tabName, $data)
    {
        try {
            switch ($tabName) {
                case 'client':
                    return $this->saveClientState($loanId, $data);
                case 'guarantor':
                    return $this->saveGuarantorState($loanId, $data);
                case 'addDocument':
                    return $this->saveDocumentState($loanId, $data);
                case 'assessment':
                    return $this->saveAssessmentState($loanId, $data);
                default:
                    throw new \Exception("Unknown tab: {$tabName}");
            }
        } catch (\Exception $e) {
            Log::error("Error saving tab state for {$tabName}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Load tab state data for a specific tab
     */
    public function loadTabState($loanId, $tabName)
    {
        try {
            switch ($tabName) {
                case 'client':
                    return $this->loadClientState($loanId);
                case 'guarantor':
                    return $this->loadGuarantorState($loanId);
                case 'addDocument':
                    return $this->loadDocumentState($loanId);
                case 'assessment':
                    return $this->loadAssessmentState($loanId);
                default:
                    throw new \Exception("Unknown tab: {$tabName}");
            }
        } catch (\Exception $e) {
            Log::error("Error loading tab state for {$tabName}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if a tab is completed based on actual data
     */
    public function isTabCompleted($loanId, $tabName)
    {
        try {
            switch ($tabName) {
                case 'client':
                    return $this->isClientTabCompleted($loanId);
                case 'guarantor':
                    return $this->isGuarantorTabCompleted($loanId);
                case 'addDocument':
                    return $this->isDocumentTabCompleted($loanId);
                case 'assessment':
                    return $this->isAssessmentTabCompleted($loanId);
                default:
                    return false;
            }
        } catch (\Exception $e) {
            Log::error("Error checking tab completion for {$tabName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save client tab state
     */
    private function saveClientState($loanId, $data)
    {
        $loan = LoansModel::find($loanId);
        if (!$loan) {
            throw new \Exception("Loan not found: {$loanId}");
        }

        // Client data is stored in the clients table
        // This method can be used to validate or update client information
        $client = ClientsModel::where('client_number', $loan->client_number)->first();
        
        if ($client) {
            // Update client with any additional data if needed
            $client->update($data);
        }

        return [
            'client_number' => $loan->client_number,
            'client_data' => $client ? $client->toArray() : null,
            'saved_at' => now()
        ];
    }

    /**
     * Save guarantor tab state
     */
    private function saveGuarantorState($loanId, $data)
    {
        // Guarantor data is stored in loan_guarantors and loan_collaterals tables
        // This method can be used to validate or update guarantor information
        
        $guarantors = DB::table('loan_guarantors')
            ->where('loan_id', $loanId)
            ->where('status', 'active')
            ->get();

        $collaterals = DB::table('loan_collaterals')
            ->join('loan_guarantors', 'loan_collaterals.loan_guarantor_id', '=', 'loan_guarantors.id')
            ->where('loan_guarantors.loan_id', $loanId)
            ->where('loan_guarantors.status', 'active')
            ->where('loan_collaterals.status', 'active')
            ->get();

        $guarantorDocuments = DB::table('loan_images')
            ->where('loan_id', $loanId)
            ->where('category', 'guarantor')
            ->get();

        return [
            'guarantors' => $guarantors,
            'collaterals' => $collaterals,
            'guarantor_documents' => $guarantorDocuments,
            'saved_at' => now()
        ];
    }

    /**
     * Save document tab state
     */
    private function saveDocumentState($loanId, $data)
    {
        // Document data is stored in loan_images table with category 'add-document'
        $documents = DB::table('loan_images')
            ->where('loan_id', $loanId)
            ->where('category', 'add-document')
            ->get();

        return [
            'documents' => $documents,
            'document_count' => $documents->count(),
            'saved_at' => now()
        ];
    }

    /**
     * Save assessment tab state
     */
    private function saveAssessmentState($loanId, $data)
    {
        $loan = LoansModel::find($loanId);
        if (!$loan) {
            throw new \Exception("Loan not found: {$loanId}");
        }

        // Assessment data is stored in loans table assessment_data column
        $assessmentData = [
            'approved_loan_value' => $data['approved_loan_value'] ?? $loan->approved_loan_value,
            'approved_term' => $data['approved_term'] ?? $loan->approved_term,
            'monthly_installment' => $data['monthly_installment'] ?? $loan->monthly_installment,
            'take_home' => $data['take_home'] ?? null,
            'exception_data' => $data['exception_data'] ?? null,
            'assessed_at' => now(),
            'assessed_by' => auth()->id() ?? null
        ];

        $loan->update([
            'assessment_data' => json_encode($assessmentData),
            'approved_loan_value' => $assessmentData['approved_loan_value'],
            'approved_term' => $assessmentData['approved_term'],
            'monthly_installment' => $assessmentData['monthly_installment']
        ]);

        return [
            'assessment_data' => $assessmentData,
            'saved_at' => now()
        ];
    }

    /**
     * Load client tab state
     */
    private function loadClientState($loanId)
    {
        $loan = LoansModel::find($loanId);
        if (!$loan) {
            return null;
        }

        $client = ClientsModel::where('client_number', $loan->client_number)->first();
        
        return [
            'client_number' => $loan->client_number,
            'client_data' => $client ? $client->toArray() : null,
            'is_completed' => $this->isClientTabCompleted($loanId)
        ];
    }

    /**
     * Load guarantor tab state
     */
    private function loadGuarantorState($loanId)
    {
        $guarantors = DB::table('loan_guarantors')
            ->where('loan_id', $loanId)
            ->where('status', 'active')
            ->get();

        $collaterals = DB::table('loan_collaterals')
            ->join('loan_guarantors', 'loan_collaterals.loan_guarantor_id', '=', 'loan_guarantors.id')
            ->where('loan_guarantors.loan_id', $loanId)
            ->where('loan_guarantors.status', 'active')
            ->where('loan_collaterals.status', 'active')
            ->get();

        $guarantorDocuments = DB::table('loan_images')
            ->where('loan_id', $loanId)
            ->where('category', 'guarantor')
            ->get();

        return [
            'guarantors' => $guarantors,
            'collaterals' => $collaterals,
            'guarantor_documents' => $guarantorDocuments,
            'is_completed' => $this->isGuarantorTabCompleted($loanId)
        ];
    }

    /**
     * Load document tab state
     */
    private function loadDocumentState($loanId)
    {
        $documents = DB::table('loan_images')
            ->where('loan_id', $loanId)
            ->where('category', 'add-document')
            ->get();

        return [
            'documents' => $documents,
            'document_count' => $documents->count(),
            'is_completed' => $this->isDocumentTabCompleted($loanId)
        ];
    }

    /**
     * Load assessment tab state
     */
    private function loadAssessmentState($loanId)
    {
        $loan = LoansModel::find($loanId);
        if (!$loan) {
            return null;
        }

        $assessmentData = $loan->assessment_data ? json_decode($loan->assessment_data, true) : null;

        return [
            'assessment_data' => $assessmentData,
            'approved_loan_value' => $loan->approved_loan_value,
            'approved_term' => $loan->approved_term,
            'monthly_installment' => $loan->monthly_installment,
            'is_completed' => $this->isAssessmentTabCompleted($loanId)
        ];
    }

    /**
     * Check if client tab is completed
     */
    private function isClientTabCompleted($loanId)
    {
        $loan = LoansModel::find($loanId);
        if (!$loan) {
            return false;
        }

        $client = ClientsModel::where('client_number', $loan->client_number)->first();
        
        return $client && 
               $client->first_name && 
               $client->last_name && 
               $client->phone_number;
    }

    /**
     * Check if guarantor tab is completed
     */
    private function isGuarantorTabCompleted($loanId)
    {
        // Check if there are active guarantors with collaterals
        $guarantorCount = DB::table('loan_guarantors')
            ->where('loan_id', $loanId)
            ->where('status', 'active')
            ->count();

        if ($guarantorCount === 0) {
            return false;
        }

        // Check if there are active collaterals for this loan
        $collateralCount = DB::table('loan_collaterals')
            ->join('loan_guarantors', 'loan_collaterals.loan_guarantor_id', '=', 'loan_guarantors.id')
            ->where('loan_guarantors.loan_id', $loanId)
            ->where('loan_guarantors.status', 'active')
            ->where('loan_collaterals.status', 'active')
            ->count();

        return $collateralCount > 0;
    }

    /**
     * Check if document tab is completed
     */
    private function isDocumentTabCompleted($loanId)
    {
        $documentCount = DB::table('loan_images')
            ->where('loan_id', $loanId)
            ->where('category', 'add-document')
            ->count();

        return $documentCount > 0;
    }

    /**
     * Check if assessment tab is completed
     */
    private function isAssessmentTabCompleted($loanId)
    {
        $loan = LoansModel::find($loanId);
        if (!$loan) {
            return false;
        }

        // Check if basic assessment data exists
        $hasBasicData = $loan->approved_loan_value && 
                       $loan->approved_term && 
                       $loan->monthly_installment;

        // Check if assessment_data JSON exists and has required fields
        $hasAssessmentData = false;
        if ($loan->assessment_data) {
            $assessmentData = json_decode($loan->assessment_data, true);
            $hasAssessmentData = $assessmentData && 
                               isset($assessmentData['approved_loan_value']) && 
                               isset($assessmentData['approved_term']) && 
                               isset($assessmentData['assessed_at']);
        }

        // Check if loan has been sent for approval (indicates assessment is complete)
        $hasBeenSentForApproval = in_array($loan->status, [
            'PENDING_DISBURSEMENT', 
            'PENDING_EXCEPTION_APPROVAL', 
            'APPROVED', 
            'DISBURSED'
        ]);

        return $hasBasicData || $hasAssessmentData || $hasBeenSentForApproval;
    }

    /**
     * Get comprehensive tab status for all tabs
     */
    public function getAllTabStatus($loanId)
    {
        $tabs = ['client', 'guarantor', 'addDocument', 'assessment'];
        $status = [];

        foreach ($tabs as $tabName) {
            $status[$tabName] = [
                'is_completed' => $this->isTabCompleted($loanId, $tabName),
                'data' => $this->loadTabState($loanId, $tabName)
            ];
        }

        return $status;
    }

    /**
     * Save tab completion status to progress table
     */
    public function saveTabCompletionStatus($loanId, $completedTabs)
    {
        DB::table('loan_process_progress')->updateOrInsert(
            ['loan_id' => $loanId],
            [
                'completed_tabs' => json_encode($completedTabs),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Get all completed tabs for a loan
     * This method combines both data-based completion and manually marked completion
     */
    public function getCompletedTabs($loanId)
    {
        try {
            // First, get manually marked completed tabs from the progress table
            $progressRecord = DB::table('loan_process_progress')
                ->where('loan_id', $loanId)
                ->first();
            
            $manuallyCompletedTabs = [];
            if ($progressRecord && $progressRecord->completed_tabs) {
                $decoded = json_decode($progressRecord->completed_tabs, true);
                $manuallyCompletedTabs = is_array($decoded) ? $decoded : [];
            }

            // Then, get data-based completed tabs
            $tabs = ['client', 'guarantor', 'addDocument', 'assessment'];
            $dataCompletedTabs = [];

            foreach ($tabs as $tabName) {
                if ($this->isTabCompleted($loanId, $tabName)) {
                    $dataCompletedTabs[] = $tabName;
                }
            }

            // Merge both arrays and remove duplicates
            $allCompletedTabs = array_unique(array_merge($manuallyCompletedTabs, $dataCompletedTabs));
            
            return array_values($allCompletedTabs); // Re-index array
        } catch (\Exception $e) {
            Log::error('Error getting completed tabs: ' . $e->getMessage(), [
                'loanId' => $loanId
            ]);
            return []; // Return empty array on error
        }
    }

    /**
     * Check if a tab is manually marked as completed in the progress table
     */
    public function isTabManuallyCompleted($loanId, $tabName)
    {
        try {
            $progressRecord = DB::table('loan_process_progress')
                ->where('loan_id', $loanId)
                ->first();
            
            if (!$progressRecord || !$progressRecord->completed_tabs) {
                return false;
            }

            $decoded = json_decode($progressRecord->completed_tabs, true);
            $completedTabs = is_array($decoded) ? $decoded : [];
            return in_array($tabName, $completedTabs);
        } catch (\Exception $e) {
            Log::error('Error checking manual tab completion: ' . $e->getMessage(), [
                'loanId' => $loanId,
                'tabName' => $tabName
            ]);
            return false;
        }
    }

    /**
     * Check if all tabs are completed
     */
    public function areAllTabsCompleted($loanId)
    {
        $completedTabs = $this->getCompletedTabs($loanId);
        return count($completedTabs) === 4; // All 4 tabs completed
    }
}
