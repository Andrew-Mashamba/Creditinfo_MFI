<?php

namespace App\Listeners;

use App\Events\LoanApproved;
use App\Models\LoanAuditLog;
use App\Models\User;
use App\Notifications\LoanApprovalNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class HandleLoanApproved implements ShouldQueue
{
    use InteractsWithQueue;

    public $delay = 10;

    public function handle(LoanApproved $event)
    {
        try {
            Log::info('Processing loan approval event', [
                'loan_id' => $event->loan->id,
                'approver_id' => $event->approverId
            ]);

            // Log the approval action
            $this->logApprovalAction($event);

            // Send notifications
            $this->sendNotifications($event);

            // Update related systems
            $this->updateRelatedSystems($event);

            // Generate documents
            $this->generateDocuments($event);

            Log::info('Loan approval event processed successfully', [
                'loan_id' => $event->loan->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing loan approval event', [
                'loan_id' => $event->loan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    protected function logApprovalAction($event)
    {
        LoanAuditLog::create([
            'loan_id' => $event->loan->id,
            'action' => 'LOAN_APPROVED',
            'user_id' => $event->approverId,
            'new_values' => [
                'status' => 'APPROVED',
                'approved_at' => now()->toISOString(),
                'approver_id' => $event->approverId,
                'approval_data' => $event->approvalData
            ],
            'description' => 'Loan approved by user ID: ' . $event->approverId
        ]);
    }

    protected function sendNotifications($event)
    {
        // Notify the loan officer
        $loanOfficer = User::find($event->loan->supervisor_id);
        if ($loanOfficer) {
            $loanOfficer->notify(new LoanApprovalNotification($event->loan, 'officer'));
        }

        // Notify the client (if they have an account)
        $client = $event->loan->client;
        if ($client && $client->email) {
            // You would create a client notification here
            // $client->notify(new LoanApprovalNotification($event->loan, 'client'));
        }

        // Notify management (if configured)
        $managementUsers = User::whereHas('roles', function($query) {
            $query->where('name', 'manager');
        })->get();

        if ($managementUsers->isNotEmpty()) {
            Notification::send($managementUsers, new LoanApprovalNotification($event->loan, 'management'));
        }
    }

    protected function updateRelatedSystems($event)
    {
        // Update loan status in external systems
        // This could include CRM, accounting systems, etc.
        
        // Update loan portfolio statistics
        $this->updatePortfolioStats($event->loan);

        // Update client credit history
        $this->updateClientCreditHistory($event->loan);
    }

    protected function generateDocuments($event)
    {
        // Generate approval letter
        $this->generateApprovalLetter($event->loan);

        // Generate loan agreement
        $this->generateLoanAgreement($event->loan);

        // Generate disbursement instructions
        $this->generateDisbursementInstructions($event->loan);
    }

    protected function updatePortfolioStats($loan)
    {
        // Update portfolio statistics in cache or database
        $stats = cache()->get('loan_portfolio_stats', []);
        
        $stats['total_approved_loans'] = ($stats['total_approved_loans'] ?? 0) + 1;
        $stats['total_approved_amount'] = ($stats['total_approved_amount'] ?? 0) + $loan->principle;
        $stats['average_loan_amount'] = $stats['total_approved_amount'] / $stats['total_approved_loans'];
        
        cache()->put('loan_portfolio_stats', $stats, 3600); // Cache for 1 hour
    }

    protected function updateClientCreditHistory($loan)
    {
        // Update client's credit history and score
        // This could involve calling external credit bureau APIs
        // or updating internal credit scoring systems
    }

    protected function generateApprovalLetter($loan)
    {
        // Generate approval letter PDF
        // This would use a PDF generation library like DomPDF
    }

    protected function generateLoanAgreement($loan)
    {
        // Generate loan agreement document
        // This would use a document generation library
    }

    protected function generateDisbursementInstructions($loan)
    {
        // Generate disbursement instructions
        // This would include bank transfer details, etc.
    }

    public function failed(LoanApproved $event, $exception)
    {
        Log::error('Loan approval event failed', [
            'loan_id' => $event->loan->id,
            'approver_id' => $event->approverId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // You could send an alert to administrators here
        // or retry the job after a delay
    }
} 