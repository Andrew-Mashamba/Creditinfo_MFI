<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

/**
 * Loan Legal Document Service
 *
 * Generates and manages legal documents for loan recovery:
 * - Warning Letters (60 days in arrears)
 * - Demand Letters (90 days in arrears)
 * - Final Notices (120 days in arrears)
 * - Legal/Statutory Notices (180+ days in arrears)
 *
 * @author System
 * @version 1.0
 */
class LoanLegalDocumentService
{
    protected $storagePath = 'loan-legal-documents';

    /**
     * Generate a PDF for a legal document
     */
    public function generatePdf(int $documentId): array
    {
        try {
            $document = DB::table('loan_legal_documents')
                ->where('id', $documentId)
                ->first();

            if (!$document) {
                return ['status' => 'error', 'message' => 'Document not found'];
            }

            // Get related data
            $loan = DB::table('loans')
                ->where('loan_id', $document->loan_id)
                ->orWhere('id', $document->loan_id)
                ->first();

            $client = DB::table('clients')
                ->where('client_number', $document->client_number)
                ->first();

            $organization = $this->getOrganizationDetails();
            $officer = $this->getRecoveryOfficer($document->branch_id);
            $guarantor = $this->getGuarantor($loan);
            $previousDocument = $this->getPreviousDocument($document);

            // Prepare view data
            $viewData = [
                'document' => $document,
                'loan' => $loan,
                'client' => $client,
                'organization' => $organization,
                'officer' => $officer,
                'guarantor' => $guarantor,
                'previousDocument' => $previousDocument
            ];

            // For legal notice, also get warning, demand, and final notice references
            if ($document->document_type === 'legal_notice') {
                $viewData['warningLetter'] = $this->getDocumentByType($document->loan_id, 'warning_letter');
                $viewData['demandLetter'] = $this->getDocumentByType($document->loan_id, 'demand_letter');
                $viewData['finalNotice'] = $this->getDocumentByType($document->loan_id, 'final_notice');
                $viewData['advocate'] = $this->getLegalAdvocate();
                $viewData['lawFirm'] = null; // Use internal legal department
            }

            // Determine template
            $template = $this->getTemplateForDocumentType($document->document_type);

            // Generate PDF
            $pdf = Pdf::loadView($template, $viewData);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Times New Roman'
            ]);

            // Generate filename
            $filename = sprintf(
                '%s_%s_%s.pdf',
                $document->document_number,
                $document->loan_id,
                Carbon::parse($document->generated_date)->format('Ymd')
            );

            // Store PDF
            $storagePath = "{$this->storagePath}/{$document->loan_id}";
            $fullPath = "{$storagePath}/{$filename}";

            Storage::disk('local')->makeDirectory($storagePath);
            Storage::disk('local')->put($fullPath, $pdf->output());

            // Update document record
            DB::table('loan_legal_documents')
                ->where('id', $documentId)
                ->update([
                    'document_path' => $fullPath,
                    'updated_at' => now()
                ]);

            // Log history
            $this->logDocumentHistory($documentId, 'pdf_generated', null, null, "PDF generated: {$filename}");

            Log::info("[LOAN_LEGAL_DOC] PDF generated: {$filename}");

            return [
                'status' => 'success',
                'path' => $fullPath,
                'filename' => $filename
            ];

        } catch (\Exception $e) {
            Log::error("[LOAN_LEGAL_DOC] PDF generation failed: " . $e->getMessage(), [
                'document_id' => $documentId,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Download a legal document PDF
     */
    public function downloadPdf(int $documentId)
    {
        $document = DB::table('loan_legal_documents')
            ->where('id', $documentId)
            ->first();

        if (!$document || !$document->document_path) {
            // Generate if not exists
            $result = $this->generatePdf($documentId);
            if ($result['status'] === 'error') {
                return null;
            }
            $document = DB::table('loan_legal_documents')
                ->where('id', $documentId)
                ->first();
        }

        if (!Storage::disk('local')->exists($document->document_path)) {
            return null;
        }

        return Storage::disk('local')->download($document->document_path);
    }

    /**
     * Get PDF stream for preview
     */
    public function streamPdf(int $documentId)
    {
        $document = DB::table('loan_legal_documents')
            ->where('id', $documentId)
            ->first();

        if (!$document || !$document->document_path) {
            $result = $this->generatePdf($documentId);
            if ($result['status'] === 'error') {
                return null;
            }
            $document = DB::table('loan_legal_documents')
                ->where('id', $documentId)
                ->first();
        }

        if (!Storage::disk('local')->exists($document->document_path)) {
            return null;
        }

        return Storage::disk('local')->get($document->document_path);
    }

    /**
     * Approve a legal document
     */
    public function approveDocument(int $documentId, int $approvedBy, ?string $notes = null): array
    {
        $document = DB::table('loan_legal_documents')
            ->where('id', $documentId)
            ->first();

        if (!$document) {
            return ['status' => 'error', 'message' => 'Document not found'];
        }

        if ($document->status !== 'draft') {
            return ['status' => 'error', 'message' => 'Only draft documents can be approved'];
        }

        $oldStatus = $document->status;

        DB::table('loan_legal_documents')
            ->where('id', $documentId)
            ->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'approval_notes' => $notes,
                'updated_at' => now()
            ]);

        $this->logDocumentHistory($documentId, 'approved', $oldStatus, 'approved', $notes, $approvedBy);

        // Generate PDF if not exists
        if (!$document->document_path) {
            $this->generatePdf($documentId);
        }

        return ['status' => 'success', 'message' => 'Document approved successfully'];
    }

    /**
     * Mark document as sent
     */
    public function markAsSent(int $documentId, string $deliveryMethod, ?string $deliveryReference = null, int $sentBy = null): array
    {
        $document = DB::table('loan_legal_documents')
            ->where('id', $documentId)
            ->first();

        if (!$document) {
            return ['status' => 'error', 'message' => 'Document not found'];
        }

        if (!in_array($document->status, ['approved', 'draft'])) {
            return ['status' => 'error', 'message' => 'Document must be approved before sending'];
        }

        $oldStatus = $document->status;

        DB::table('loan_legal_documents')
            ->where('id', $documentId)
            ->update([
                'status' => 'sent',
                'sent_date' => now()->toDateString(),
                'delivery_method' => $deliveryMethod,
                'delivery_reference' => $deliveryReference,
                'delivery_status' => 'sent',
                'updated_at' => now()
            ]);

        $this->logDocumentHistory($documentId, 'sent', $oldStatus, 'sent', "Sent via {$deliveryMethod}", $sentBy);

        return ['status' => 'success', 'message' => 'Document marked as sent'];
    }

    /**
     * Mark document as acknowledged
     */
    public function markAsAcknowledged(int $documentId, Carbon $acknowledgedDate = null, ?string $notes = null): array
    {
        $document = DB::table('loan_legal_documents')
            ->where('id', $documentId)
            ->first();

        if (!$document) {
            return ['status' => 'error', 'message' => 'Document not found'];
        }

        $oldStatus = $document->status;

        DB::table('loan_legal_documents')
            ->where('id', $documentId)
            ->update([
                'status' => 'acknowledged',
                'acknowledged_date' => ($acknowledgedDate ?? now())->toDateString(),
                'delivery_status' => 'acknowledged',
                'notes' => $notes,
                'updated_at' => now()
            ]);

        $this->logDocumentHistory($documentId, 'acknowledged', $oldStatus, 'acknowledged', $notes);

        return ['status' => 'success', 'message' => 'Document acknowledged'];
    }

    /**
     * Escalate document to next level
     */
    public function escalateDocument(int $documentId, string $reason, int $escalatedBy): array
    {
        $document = DB::table('loan_legal_documents')
            ->where('id', $documentId)
            ->first();

        if (!$document) {
            return ['status' => 'error', 'message' => 'Document not found'];
        }

        $oldStatus = $document->status;

        DB::table('loan_legal_documents')
            ->where('id', $documentId)
            ->update([
                'status' => 'escalated',
                'escalated' => true,
                'escalated_at' => now(),
                'escalation_reason' => $reason,
                'updated_at' => now()
            ]);

        $this->logDocumentHistory($documentId, 'escalated', $oldStatus, 'escalated', $reason, $escalatedBy);

        return ['status' => 'success', 'message' => 'Document escalated'];
    }

    /**
     * Get documents for a loan
     */
    public function getDocumentsForLoan(string $loanId): \Illuminate\Support\Collection
    {
        return DB::table('loan_legal_documents')
            ->where('loan_id', $loanId)
            ->orderBy('generated_date', 'desc')
            ->get();
    }

    /**
     * Get document history
     */
    public function getDocumentHistory(int $documentId): \Illuminate\Support\Collection
    {
        return DB::table('loan_legal_document_history as h')
            ->leftJoin('users as u', 'h.performed_by', '=', 'u.id')
            ->where('h.document_id', $documentId)
            ->select('h.*', 'u.name as performed_by_name')
            ->orderBy('h.created_at', 'desc')
            ->get();
    }

    /**
     * Get pending documents (drafts awaiting approval)
     */
    public function getPendingDocuments(?int $branchId = null): \Illuminate\Support\Collection
    {
        $query = DB::table('loan_legal_documents as d')
            ->leftJoin('clients as c', 'd.client_number', '=', 'c.client_number')
            ->leftJoin('loans as l', function($join) {
                $join->on('d.loan_id', '=', 'l.loan_id')
                     ->orOn('d.loan_id', '=', DB::raw('l.id::text'));
            })
            ->where('d.status', 'draft')
            ->select(
                'd.*',
                'c.first_name',
                'c.middle_name',
                'c.last_name',
                'c.phone_number',
                'l.loan_account_number'
            );

        if ($branchId) {
            $query->where('d.branch_id', $branchId);
        }

        return $query->orderBy('d.generated_date', 'asc')->get();
    }

    // ==================== HELPER METHODS ====================

    protected function getTemplateForDocumentType(string $documentType): string
    {
        $templates = [
            'warning_letter' => 'pdf.legal-documents.warning-letter',
            'demand_letter' => 'pdf.legal-documents.demand-letter',
            'final_notice' => 'pdf.legal-documents.final-notice',
            'legal_notice' => 'pdf.legal-documents.legal-notice'
        ];

        return $templates[$documentType] ?? 'pdf.legal-documents.warning-letter';
    }

    protected function getOrganizationDetails(): object
    {
        $org = DB::table('organizations')->first();

        return (object)[
            'name' => $org->name ?? 'NBC SACCO ORGANIZATION',
            'address' => $org->address ?? 'P.O. Box XXXX, Dar es Salaam, Tanzania',
            'phone' => $org->phone ?? '+255 XXX XXX XXX',
            'email' => $org->email ?? 'info@nbcsacco.co.tz',
            'logo' => $org->logo ?? null
        ];
    }

    protected function getRecoveryOfficer(?int $branchId): object
    {
        // Try to get a user with recovery/credit role from the branch
        $officer = DB::table('users')
            ->where('branch_id', $branchId)
            ->where(function($q) {
                $q->where('role', 'like', '%recovery%')
                  ->orWhere('role', 'like', '%credit%')
                  ->orWhere('role', 'like', '%loan%');
            })
            ->first();

        return (object)[
            'name' => $officer->name ?? 'Credit & Recovery Department',
            'title' => $officer->role ?? 'Loan Recovery Officer'
        ];
    }

    protected function getGuarantor($loan): ?object
    {
        if (!$loan) return null;

        $guarantor = DB::table('guarantors')
            ->where('loan_id', $loan->loan_id ?? $loan->id)
            ->first();

        if (!$guarantor) return null;

        return (object)[
            'name' => $guarantor->name ?? $guarantor->first_name . ' ' . $guarantor->last_name,
            'phone' => $guarantor->phone_number ?? null,
            'address' => $guarantor->address ?? null
        ];
    }

    protected function getPreviousDocument($document): ?object
    {
        $previousTypes = [
            'demand_letter' => 'warning_letter',
            'final_notice' => 'demand_letter',
            'legal_notice' => 'final_notice'
        ];

        $previousType = $previousTypes[$document->document_type] ?? null;

        if (!$previousType) return null;

        return DB::table('loan_legal_documents')
            ->where('loan_id', $document->loan_id)
            ->where('document_type', $previousType)
            ->orderBy('generated_date', 'desc')
            ->first();
    }

    protected function getDocumentByType(string $loanId, string $documentType): ?object
    {
        return DB::table('loan_legal_documents')
            ->where('loan_id', $loanId)
            ->where('document_type', $documentType)
            ->orderBy('generated_date', 'desc')
            ->first();
    }

    protected function getLegalAdvocate(): object
    {
        return (object)[
            'name' => 'Legal Counsel',
            'firm' => 'Legal Department'
        ];
    }

    protected function logDocumentHistory(int $documentId, string $action, ?string $oldStatus, ?string $newStatus, ?string $notes = null, ?int $performedBy = null): void
    {
        DB::table('loan_legal_document_history')->insert([
            'document_id' => $documentId,
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'performed_by' => $performedBy,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Generate all pending PDFs for documents
     */
    public function generatePendingPdfs(): array
    {
        $documents = DB::table('loan_legal_documents')
            ->whereNull('document_path')
            ->get();

        $generated = 0;
        $errors = 0;

        foreach ($documents as $document) {
            $result = $this->generatePdf($document->id);
            if ($result['status'] === 'success') {
                $generated++;
            } else {
                $errors++;
            }
        }

        return [
            'generated' => $generated,
            'errors' => $errors
        ];
    }
}
