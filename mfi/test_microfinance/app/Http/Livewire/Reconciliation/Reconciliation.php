<?php

namespace App\Http\Livewire\Reconciliation;

use App\Models\AnalysisSession;
use App\Models\BankTransaction;
use App\Models\Transaction;
use App\Services\BankReconciliationService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Traits\Livewire\WithModulePermissions;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\DB;

class Reconciliation extends Component
{
    use WithFileUploads;
    use WithPagination;
    use WithModulePermissions;

    // File upload properties
    public $bankStatement;
    public $pdfFile;

    // Validation rules
    protected $rules = [
        'pdfFile' => 'required|mimes:pdf|max:10240', // Max 10MB
        'bankStatement' => 'required|in:im,crdb,nmb,abbsa'
    ];

    // Data properties
    public $sessions;
    public $activeSessionId = null;
    public $showData = false;
    public $reconciliationResults = null;
    public $selectedBankTransactions = [];
    public $availableTransactions = [];

    // UI state properties
    public $isProcessing = false;
    public $processingMessage = '';
    public $showReconciliationModal = false;
    public $selectedBankTransactionId = null;
    public $activeTab = 'matched'; // matched, cashbook, or bank_account_{id}

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        
        $this->loadSessions();
    }
    
    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'reconciliation';
    }

    public function loadSessions()
    {
        $this->sessions = AnalysisSession::with('bankTransactions')
            ->latest()
            ->get();
    }

    public function setActiveSession($sessionId)
    {
        if (!$this->authorize('view', 'You do not have permission to view reconciliation sessions')) {
            return;
        }
        $this->activeSessionId = $sessionId;
        $this->showData = true;
        $this->reconciliationResults = null;
    }

    public function uploadFile()
    {
        if (!$this->authorize('upload', 'You do not have permission to upload bank statements')) {
            return;
        }

        try {
            $this->validate();

            if (!$this->pdfFile) {
                session()->flash('error', 'No file selected.');
                return;
            }

            // Calculate file hash for duplicate detection
            $fileHash = hash_file('sha256', $this->pdfFile->getRealPath());

            Log::info('Starting bank statement upload', [
                'bank' => $this->bankStatement,
                'file_size' => $this->pdfFile->getSize(),
                'file_hash' => $fileHash
            ]);

            // Check for duplicate file upload
            $existingSession = AnalysisSession::where('file_hash', $fileHash)->first();
            if ($existingSession) {
                Log::warning('Duplicate file detected', [
                    'file_hash' => $fileHash,
                    'existing_session_id' => $existingSession->id
                ]);

                session()->flash('error', 'This statement has already been uploaded. Session: ' . $this->getSessionDisplayName($existingSession));
                return;
            }

            // Store the file
            $filePath = $this->pdfFile->store('bank_statements', 'public');
            Log::info('File stored successfully', ['path' => $filePath]);

            // Parse the PDF
            $parser = new Parser();
            $pdf = $parser->parseFile(storage_path("app/public/" . $filePath));
            $text = $pdf->getText();

            if (empty($text)) {
                throw new Exception('PDF is empty or could not be parsed. Please ensure it contains extractable text.');
            }

            Log::info('PDF parsed successfully', ['content_length' => strlen($text)]);

            // Process based on bank type
            $result = $this->processBankStatement($text, $filePath, $fileHash);

            if ($result['success']) {
                $this->loadSessions();
                $this->setActiveSession($result['session_id']);

                $message = "Bank statement uploaded successfully! ";
                $message .= "Session: {$this->getSessionDisplayName(AnalysisSession::find($result['session_id']))} - ";
                $message .= "{$result['transaction_count']} transactions imported.";

                session()->flash('success', $message);
                $this->reset(['pdfFile', 'bankStatement']);

                Log::info('Upload completed successfully', [
                    'session_id' => $result['session_id'],
                    'transactions' => $result['transaction_count']
                ]);
            } else {
                throw new Exception($result['error'] ?? 'Failed to process bank statement.');
            }

        } catch (Exception $e) {
            Log::error("Upload error", [
                'error' => $e->getMessage(),
                'bank' => $this->bankStatement,
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    private function processBankStatement($text, $filePath, $fileHash)
    {
        try {
            switch ($this->bankStatement) {
                case 'im':
                    return $this->processIMBankStatement($text, $filePath, $fileHash);
                case 'crdb':
                    return $this->processCRDBBankStatement($text, $filePath, $fileHash);
                case 'nmb':
                    return $this->processNMBBankStatement($text, $filePath, $fileHash);
                case 'abbsa':
                    return $this->processABBSABankStatement($text, $filePath, $fileHash);
                default:
                    return [
                        'success' => false,
                        'error' => 'Unsupported bank type: ' . $this->bankStatement
                    ];
            }
        } catch (Exception $e) {
            Log::error('Bank statement processing error', [
                'bank' => $this->bankStatement,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to parse ' . strtoupper($this->bankStatement) . ' statement: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate transaction balances
     */
    private function validateBalances(array $transactions, $openingBalance = null): array
    {
        $errors = [];
        $previousBalance = $openingBalance;

        foreach ($transactions as $index => $txn) {
            if ($previousBalance !== null && isset($txn['balance'])) {
                $expectedBalance = $previousBalance + ($txn['deposit_amount'] ?? 0) - ($txn['withdrawal_amount'] ?? 0);
                $actualBalance = $txn['balance'];

                // Allow small rounding differences (0.01)
                if (abs($expectedBalance - $actualBalance) > 0.01) {
                    $errors[] = [
                        'line' => $index + 1,
                        'expected' => $expectedBalance,
                        'actual' => $actualBalance,
                        'difference' => $actualBalance - $expectedBalance
                    ];
                }
            }

            $previousBalance = $txn['balance'] ?? $previousBalance;
        }

        return $errors;
    }

    /**
     * Check for duplicate transactions in the same session
     */
    private function checkDuplicateTransactions(int $sessionId, array $transactions): int
    {
        $duplicateCount = 0;

        foreach ($transactions as $txn) {
            $exists = BankTransaction::where('session_id', $sessionId)
                ->where('transaction_date', $txn['transaction_date'])
                ->where('reference_number', $txn['reference_number'] ?? null)
                ->where(function($query) use ($txn) {
                    $query->where('deposit_amount', $txn['deposit_amount'] ?? 0)
                          ->orWhere('withdrawal_amount', $txn['withdrawal_amount'] ?? 0);
                })
                ->exists();

            if ($exists) {
                $duplicateCount++;
            }
        }

        return $duplicateCount;
    }

    private function processIMBankStatement($text, $filePath, $fileHash)
    {
        // Extract account info
        preg_match('/ACCOUNT NAME\s*:\s*(.*)/', $text, $accountMatches);
        preg_match('/STATEMENT FOR THE PERIOD OF\s*:\s*(.*)/', $text, $periodMatches);
        preg_match('/ACCOUNT NUMBER\s*:\s*([\d\-]+)/', $text, $accountNumberMatches);

        $accountName = trim($accountMatches[1] ?? 'Unknown Account');
        $statementPeriod = trim($periodMatches[1] ?? 'Unknown Period');
        $accountNumber = trim($accountNumberMatches[1] ?? null);

        // Extract opening and closing balances
        preg_match('/OPENING BALANCE.*?([\d,]+\.?\d*)/i', $text, $openingMatch);
        preg_match('/CLOSING BALANCE.*?([\d,]+\.?\d*)/i', $text, $closingMatch);

        $openingBalance = isset($openingMatch[1]) ? floatval(str_replace(',', '', $openingMatch[1])) : null;
        $closingBalance = isset($closingMatch[1]) ? floatval(str_replace(',', '', $closingMatch[1])) : null;

        // Create analysis session
        $session = AnalysisSession::create([
            'account_name' => $accountName,
            'account_number' => $accountNumber,
            'statement_period' => $statementPeriod,
            'bank' => 'I&M BANK',
            'status' => 'processing',
            'file_path' => $filePath,
            'file_hash' => $fileHash,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'uploaded_by' => auth()->id() ?? 1
        ]);

        // Extract transactions
        $lines = explode("\n", $text);
        $pattern = '/^\|\d{2}-\d{2}-\d{2}\s*\|\d{2}-\d{2}-\d{2}/';
        $transactions = [];
        $lineNumber = 0;

        foreach ($lines as $line) {
            if (preg_match($pattern, $line)) {
                $lineNumber++;
                $cleanedLine = trim($line, "| ");
                $columns = array_map('trim', explode('|', $cleanedLine));

                if (count($columns) >= 7) {
                    $withdrawalAmount = !empty($columns[4]) ? floatval(str_replace([',', 'Cr'], '', $columns[4])) : 0.00;
                    $depositAmount = !empty($columns[5]) ? floatval(str_replace([',', 'Cr'], '', $columns[5])) : 0.00;
                    $balance = isset($columns[6]) ? floatval(str_replace([',', 'Cr'], '', $columns[6])) : null;

                    // Extract reference from narration (usually in format: REF:123456 or #123456)
                    $narration = $columns[3] ?? '';
                    $reference = null;
                    if (preg_match('/(?:REF:|#|REF\s+)(\w+)/i', $narration, $refMatch)) {
                        $reference = $refMatch[1];
                    } else {
                        // Use column 2 if it looks like a reference
                        $reference = $columns[2] ?? 'IM' . str_pad($lineNumber, 6, '0', STR_PAD_LEFT);
                    }

                    $transactions[] = [
                        'session_id' => $session->id,
                        'transaction_date' => $this->parseDate($columns[0]),
                        'value_date' => $this->parseDate($columns[1]),
                        'reference_number' => $reference,
                        'narration' => $narration,
                        'withdrawal_amount' => $withdrawalAmount,
                        'deposit_amount' => $depositAmount,
                        'balance' => $balance,
                        'reconciliation_status' => 'unreconciled',
                        'transaction_type' => $depositAmount > 0 ? 'credit' : 'debit',
                        'raw_data' => json_encode($columns)
                    ];
                }
            }
        }

        if (empty($transactions)) {
            $session->update(['status' => 'failed']);
            return [
                'success' => false,
                'error' => 'No transactions found. Please ensure the PDF format is correct for I&M Bank.'
            ];
        }

        // Validate balances
        $balanceErrors = $this->validateBalances($transactions, $openingBalance);
        if (!empty($balanceErrors) && count($balanceErrors) > 5) {
            Log::warning('I&M statement has balance calculation errors', [
                'session_id' => $session->id,
                'error_count' => count($balanceErrors)
            ]);
        }

        // Insert transactions
        BankTransaction::insert($transactions);

        // Update session
        $session->update([
            'total_transactions' => count($transactions),
            'status' => 'completed',
            'metadata' => [
                'balance_validation_errors' => count($balanceErrors),
                'uploaded_at' => now()->toIso8601String()
            ]
        ]);

        Log::info('I&M statement processed successfully', [
            'session_id' => $session->id,
            'transaction_count' => count($transactions),
            'balance_errors' => count($balanceErrors)
        ]);

        return [
            'success' => true,
            'session_id' => $session->id,
            'transaction_count' => count($transactions),
            'balance_errors' => count($balanceErrors)
        ];
    }

    private function processCRDBBankStatement($text, $filePath, $fileHash)
    {
        // Extract account info
        preg_match('/^([A-Z\s]+)\n/m', $text, $accountMatches);
        preg_match('/Account:\s+(\d+)/', $text, $accountNumberMatches);
        preg_match('/Period:\s+([\d\/]+)\s*-\s*([\d\/]+)/', $text, $periodMatches);

        $accountName = trim($accountMatches[1] ?? 'Unknown Account');
        $accountNumber = $accountNumberMatches[1] ?? null;
        $statementPeriod = ($periodMatches[1] ?? '') . ' - ' . ($periodMatches[2] ?? '');

        // Extract balances
        preg_match('/Opening Balance.*?([\d,]+\.?\d*)/i', $text, $openingMatch);
        preg_match('/Closing Balance.*?([\d,]+\.?\d*)/i', $text, $closingMatch);
        $openingBalance = isset($openingMatch[1]) ? $this->normalizeAmount($openingMatch[1]) : null;
        $closingBalance = isset($closingMatch[1]) ? $this->normalizeAmount($closingMatch[1]) : null;

        // Create analysis session
        $session = AnalysisSession::create([
            'account_name' => $accountName,
            'account_number' => $accountNumber,
            'statement_period' => $statementPeriod,
            'bank' => 'CRDB BANK',
            'status' => 'processing',
            'file_path' => $filePath,
            'file_hash' => $fileHash,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'uploaded_by' => auth()->id() ?? 1
        ]);

        // Extract transactions using regex pattern
        $pattern = '/(\d{2}\.\d{2}\.\d{4})\s*(\d{2}:\d{2}:\d{2})\s*(.*?)(\d{2}\.\d{2}\.\d{4})\s*(\d{2}:\d{2}:\d{2})\s*([\d,]+\.\d{2})\s*([\d,]+\.\d{2})\s*([\d,]+)/s';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        $transactions = [];
        foreach ($matches as $index => $match) {
            $debitAmount = $this->normalizeAmount($match[6]);
            $creditAmount = $this->normalizeAmount($match[7]);
            $balance = $this->normalizeAmount($match[8]);
            $narration = trim($match[3]);

            // Extract reference from narration or generate one
            $reference = null;
            if (preg_match('/(?:REF[:.\s]+|#)(\w+)/i', $narration, $refMatch)) {
                $reference = $refMatch[1];
            } else {
                $reference = 'CRDB' . str_pad($index + 1, 6, '0', STR_PAD_LEFT);
            }

            $transactions[] = [
                'session_id' => $session->id,
                'transaction_date' => $this->convertDateFormat($match[1]),
                'value_date' => $this->convertDateFormat($match[4]),
                'reference_number' => $reference,
                'narration' => $narration,
                'withdrawal_amount' => $debitAmount,
                'deposit_amount' => $creditAmount,
                'balance' => $balance,
                'reconciliation_status' => 'unreconciled',
                'transaction_type' => $creditAmount > 0 ? 'credit' : 'debit',
                'raw_data' => json_encode($match)
            ];
        }

        if (empty($transactions)) {
            $session->update(['status' => 'failed']);
            return [
                'success' => false,
                'error' => 'No transactions found in CRDB statement. Please check PDF format.'
            ];
        }

        // Validate and insert
        $balanceErrors = $this->validateBalances($transactions, $openingBalance);
        BankTransaction::insert($transactions);

        $session->update([
            'total_transactions' => count($transactions),
            'status' => 'completed',
            'metadata' => ['balance_validation_errors' => count($balanceErrors)]
        ]);

        return [
            'success' => true,
            'session_id' => $session->id,
            'transaction_count' => count($transactions),
            'balance_errors' => count($balanceErrors)
        ];
    }

    private function processNMBBankStatement($text, $filePath, $fileHash)
    {
        // Extract account info
        preg_match('/Name\s*:\s*(.*?)\s*Value Date/', $text, $accountMatches);
        preg_match('/Account\s*:\s*([\d\-]+)/', $text, $accountNumberMatches);
        preg_match('/From Date\s*\t(.*?)\t/', $text, $fromDateMatches);
        preg_match('/To Date\s*\t(.*?)\n/', $text, $toDateMatches);

        $accountName = trim($accountMatches[1] ?? 'Unknown Account');
        $accountNumber = trim($accountNumberMatches[1] ?? null);
        $statementPeriod = ($fromDateMatches[1] ?? '') . ' - ' . ($toDateMatches[1] ?? '');

        // Extract opening and closing balances
        preg_match('/Opening Balance.*?([\d,]+\.?\d*)/i', $text, $openingMatch);
        preg_match('/Closing Balance.*?([\d,]+\.?\d*)/i', $text, $closingMatch);
        $openingBalance = isset($openingMatch[1]) ? $this->normalizeAmount($openingMatch[1]) : null;
        $closingBalance = isset($closingMatch[1]) ? $this->normalizeAmount($closingMatch[1]) : null;

        // Create analysis session
        $session = AnalysisSession::create([
            'account_name' => $accountName,
            'account_number' => $accountNumber,
            'statement_period' => $statementPeriod,
            'bank' => 'NMB BANK',
            'status' => 'processing',
            'file_path' => $filePath,
            'file_hash' => $fileHash,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'uploaded_by' => auth()->id() ?? 1
        ]);

        // Extract transactions
        $transactionStart = strpos($text, "Book Date Value Date");
        if ($transactionStart !== false) {
            $transactionData = substr($text, $transactionStart);
            $lines = explode("\n", $transactionData);

            $transactions = [];
            $currentTransaction = null;
            $lineNumber = 0;

            foreach ($lines as $line) {
                $line = trim($line);
                if (preg_match('/^\d{2}\s\w{3}\s\d{4}/', $line)) {
                    if ($currentTransaction) {
                        $transactions[] = $currentTransaction;
                    }
                    $lineNumber++;
                    $currentTransaction = $this->parseNMBTransactionLine($line, $session->id, $lineNumber);
                } elseif ($currentTransaction && !empty($line)) {
                    $currentTransaction['narration'] .= ' ' . $line;
                }
            }

            if ($currentTransaction) {
                $transactions[] = $currentTransaction;
            }

            if (empty($transactions)) {
                $session->update(['status' => 'failed']);
                return [
                    'success' => false,
                    'error' => 'No transactions found in NMB statement. Please check PDF format.'
                ];
            }

            // Validate balances
            $balanceErrors = $this->validateBalances($transactions, $openingBalance);

            // Add reconciliation fields to all transactions
            foreach ($transactions as &$txn) {
                $txn['reconciliation_status'] = 'unreconciled';
                $txn['transaction_type'] = ($txn['deposit_amount'] ?? 0) > 0 ? 'credit' : 'debit';
            }

            // Save transactions
            BankTransaction::insert($transactions);

            // Update session
            $session->update([
                'total_transactions' => count($transactions),
                'status' => 'completed',
                'metadata' => [
                    'balance_validation_errors' => count($balanceErrors),
                    'uploaded_at' => now()->toIso8601String()
                ]
            ]);

            Log::info('NMB statement processed successfully', [
                'session_id' => $session->id,
                'transaction_count' => count($transactions),
                'balance_errors' => count($balanceErrors)
            ]);

            return [
                'success' => true,
                'session_id' => $session->id,
                'transaction_count' => count($transactions),
                'balance_errors' => count($balanceErrors)
            ];
        }

        $session->update(['status' => 'failed']);
        return [
            'success' => false,
            'error' => 'Could not find transaction section in NMB statement.'
        ];
    }

    private function processABBSABankStatement($text, $filePath, $fileHash)
    {
        // Extract account info
        preg_match('/Account name:\s*(.*?)\s*(?:Account number:|Statement Period)/s', $text, $accountMatches);
        preg_match('/Account number:\s*(\d+)/', $text, $accountNumberMatches);
        preg_match('/Statement Period:\s*(.+?)\n/', $text, $periodMatches);

        $accountName = trim($accountMatches[1] ?? 'Unknown Account');
        $accountNumber = trim($accountNumberMatches[1] ?? null);
        $statementPeriod = trim($periodMatches[1] ?? 'Unknown Period');

        // Extract opening and closing balances
        preg_match('/Opening Balance.*?([\d,]+\.?\d*)/i', $text, $openingMatch);
        preg_match('/Closing Balance.*?([\d,]+\.?\d*)/i', $text, $closingMatch);
        $openingBalance = isset($openingMatch[1]) ? $this->normalizeAmount($openingMatch[1]) : null;
        $closingBalance = isset($closingMatch[1]) ? $this->normalizeAmount($closingMatch[1]) : null;

        // Create analysis session
        $session = AnalysisSession::create([
            'account_name' => $accountName,
            'account_number' => $accountNumber,
            'statement_period' => $statementPeriod,
            'bank' => 'ABBSA BANK',
            'status' => 'processing',
            'file_path' => $filePath,
            'file_hash' => $fileHash,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'uploaded_by' => auth()->id() ?? 1
        ]);

        // Extract transactions
        $pattern = '/(\d{2}-\d{2}-\d{4}\s\d{2}:\d{2})\s*(\d{2}-\d{2}-\d{4}\s\d{2}:\d{2})\s*(.*?)\s*\t0\t([\d.]+)\s([\d.]+)\s([\d.]+)\n/s';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        $transactions = [];
        foreach ($matches as $index => $match) {
            $withdrawalAmount = floatval($match[4]);
            $depositAmount = floatval($match[5]);
            $balance = floatval($match[6]);
            $narration = trim($match[3]);

            // Extract reference from narration or generate one
            $reference = null;
            if (preg_match('/(?:REF[:.\s]+|#|Ref\s+)(\w+)/i', $narration, $refMatch)) {
                $reference = $refMatch[1];
            } else {
                $reference = 'ABSA' . str_pad($index + 1, 6, '0', STR_PAD_LEFT);
            }

            $transactions[] = [
                'session_id' => $session->id,
                'transaction_date' => $this->parseDateTime($match[1]),
                'value_date' => $this->parseDateTime($match[2]),
                'reference_number' => $reference,
                'narration' => $narration,
                'withdrawal_amount' => $withdrawalAmount,
                'deposit_amount' => $depositAmount,
                'balance' => $balance,
                'reconciliation_status' => 'unreconciled',
                'transaction_type' => $depositAmount > 0 ? 'credit' : 'debit',
                'raw_data' => json_encode($match)
            ];
        }

        if (empty($transactions)) {
            $session->update(['status' => 'failed']);
            return [
                'success' => false,
                'error' => 'No transactions found in ABBSA statement. Please check PDF format.'
            ];
        }

        // Validate balances
        $balanceErrors = $this->validateBalances($transactions, $openingBalance);

        // Save transactions
        BankTransaction::insert($transactions);

        // Update session
        $session->update([
            'total_transactions' => count($transactions),
            'status' => 'completed',
            'metadata' => [
                'balance_validation_errors' => count($balanceErrors),
                'uploaded_at' => now()->toIso8601String()
            ]
        ]);

        Log::info('ABBSA statement processed successfully', [
            'session_id' => $session->id,
            'transaction_count' => count($transactions),
            'balance_errors' => count($balanceErrors)
        ]);

        return [
            'success' => true,
            'session_id' => $session->id,
            'transaction_count' => count($transactions),
            'balance_errors' => count($balanceErrors)
        ];
    }

    public function runReconciliation()
    {
        if (!$this->authorize('reconcile', 'You do not have permission to run reconciliation')) {
            return;
        }
        if (!$this->activeSessionId) {
            session()->flash('error', 'No session selected for reconciliation.');
            return;
        }

        try {
            $this->isProcessing = true;
            $this->processingMessage = 'Running reconciliation...';

            // Get the active session
            $session = AnalysisSession::find($this->activeSessionId);
            if (!$session) {
                throw new Exception('Session not found');
            }

            // Run the reconciliation process using the same logic as daily NBC reconciliation
            $matchedCount = 0;
            $unmatchedBankCount = 0;
            $unmatchedCashbookCount = 0;

            // Get all unreconciled bank transactions for this session
            $bankTransactions = BankTransaction::where('session_id', $this->activeSessionId)
                ->where('reconciliation_status', 'unreconciled')
                ->get();

            foreach ($bankTransactions as $bankTxn) {
                $match = $this->findMatchingCashbookTransaction($bankTxn);

                if ($match) {
                    // Create matching record
                    $this->createMatchRecord($this->activeSessionId, $bankTxn, $match['transaction'], $match['confidence'], $match['match_type']);

                    // Update bank transaction status
                    $bankTxn->update([
                        'reconciliation_status' => 'matched',
                        'matched_transaction_id' => $match['transaction']->id,
                        'match_confidence' => $match['confidence'],
                        'reconciliation_notes' => $match['notes'],
                        'recon_match_type' => $match['match_type']
                    ]);

                    $matchedCount++;
                } else {
                    // Create non-matching record for bank transaction (only if not already exists)
                    $exists = \App\Models\ReconNonMatchingTransaction::where('transaction_id', $bankTxn->id)
                        ->where('source', 'bank')
                        ->exists();

                    if (!$exists) {
                        $this->createNonMatchRecord($this->activeSessionId, $bankTxn, 'bank');
                    }
                    $unmatchedBankCount++;
                }
            }

            // Find unmatched cashbook transactions
            $unmatchedCashbookCount = $this->findUnmatchedCashbookTransactions($this->activeSessionId);

            // Update session with results
            $totalTransactions = BankTransaction::where('session_id', $this->activeSessionId)->count();
            $session->update([
                'status' => 'completed',
                'matched_count' => $matchedCount,
                'unmatched_count' => $unmatchedBankCount + $unmatchedCashbookCount,
                'reconciliation_summary' => [
                    'total' => $totalTransactions,
                    'matched' => $matchedCount,
                    'unmatched_bank' => $unmatchedBankCount,
                    'unmatched_cashbook' => $unmatchedCashbookCount,
                    'reconciliation_rate' => $totalTransactions > 0
                        ? round(($matchedCount / $totalTransactions) * 100, 2)
                        : 0
                ],
                'reconciled_by' => auth()->id() ?? 1,
                'reconciled_at' => now()
            ]);

            // Set results for display
            $this->reconciliationResults = [
                'total_processed' => $totalTransactions,
                'matched' => $matchedCount,
                'partial_matches' => 0,
                'unmatched' => $unmatchedBankCount + $unmatchedCashbookCount
            ];

            // Reload sessions to reflect changes
            $this->loadSessions();

            session()->flash('success', "Reconciliation completed! Matched: {$matchedCount}, Unmatched: " . ($unmatchedBankCount + $unmatchedCashbookCount));

        } catch (Exception $e) {
            Log::error("Reconciliation error: " . $e->getMessage());
            session()->flash('error', 'Reconciliation failed: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
            $this->processingMessage = '';
        }
    }

    /**
     * Find matching cashbook transaction for a bank transaction (3-tier matching)
     * Only searches within 35 days before the bank transaction date
     */
    private function findMatchingCashbookTransaction(BankTransaction $bankTxn): ?array
    {
        $amount = $bankTxn->deposit_amount ?: $bankTxn->withdrawal_amount;
        $transactionDate = $bankTxn->transaction_date;

        // Define 35-day lookback period (for monthly statements)
        $lookbackStartDate = Carbon::parse($transactionDate)->subDays(35);
        $lookbackEndDate = Carbon::parse($transactionDate)->addDay(); // Allow 1 day forward

        // Tier 1: Exact match on reference, amount, and date (within 35-day window)
        $exactMatch = Transaction::where('status', 'COMPLETED')
            ->where('external_reference', $bankTxn->reference_number)
            ->where('amount', $amount)
            ->whereDate('initiated_at', $transactionDate)
            ->whereBetween('initiated_at', [$lookbackStartDate, $lookbackEndDate])
            ->first();

        if ($exactMatch) {
            return [
                'transaction' => $exactMatch,
                'confidence' => 100.00,
                'match_type' => 'exact',
                'notes' => 'Exact match: reference, amount, and date'
            ];
        }

        // Tier 2: Match on amount and date (within +/- 1 day, but still within 35-day window)
        $dateMatch = Transaction::where('status', 'COMPLETED')
            ->where('amount', $amount)
            ->whereBetween('initiated_at', [
                Carbon::parse($transactionDate)->subDay(),
                Carbon::parse($transactionDate)->addDay()
            ])
            ->whereBetween('initiated_at', [$lookbackStartDate, $lookbackEndDate])
            ->first();

        if ($dateMatch) {
            return [
                'transaction' => $dateMatch,
                'confidence' => 85.00,
                'match_type' => 'partial',
                'notes' => 'Partial match: amount and date (±1 day)'
            ];
        }

        // Tier 3: Match on reference only (within 35-day window)
        if ($bankTxn->reference_number) {
            $referenceMatch = Transaction::where('status', 'COMPLETED')
                ->where('external_reference', $bankTxn->reference_number)
                ->whereBetween('initiated_at', [$lookbackStartDate, $lookbackEndDate])
                ->first();

            if ($referenceMatch) {
                return [
                    'transaction' => $referenceMatch,
                    'confidence' => 70.00,
                    'match_type' => 'partial',
                    'notes' => 'Partial match: reference only'
                ];
            }
        }

        return null;
    }

    /**
     * Create a matching transaction record
     * Also cleans up any existing non-matching records for these transactions
     */
    private function createMatchRecord(int $sessionId, BankTransaction $bankTxn, Transaction $cashbookTxn, float $confidence, string $matchType): void
    {
        $bankAmount = $bankTxn->deposit_amount ?: $bankTxn->withdrawal_amount;
        $cashbookAmount = $cashbookTxn->amount;
        $variance = abs($bankAmount - $cashbookAmount);

        $cashbookDate = $cashbookTxn->initiated_at ?? $cashbookTxn->completed_at ?? $cashbookTxn->created_at;
        $dateVariance = Carbon::parse($bankTxn->transaction_date)->diffInDays(
            Carbon::parse($cashbookDate)
        );

        // Remove any existing non-matching records for these transactions
        // They were previously marked as non-matching but now we found matches
        \App\Models\ReconNonMatchingTransaction::where('transaction_id', $bankTxn->id)
            ->where('source', 'bank')
            ->delete();

        \App\Models\ReconNonMatchingTransaction::where('transaction_id', $cashbookTxn->id)
            ->where('source', 'cashbook')
            ->delete();

        \App\Models\ReconMatchingTransaction::create([
            'session_id' => $sessionId,
            'bank_transaction_id' => $bankTxn->id,
            'cashbook_transaction_id' => $cashbookTxn->id,
            'match_type' => $matchType,
            'match_confidence' => $confidence,
            'match_criteria' => [
                'reference_match' => $bankTxn->reference_number === $cashbookTxn->external_reference,
                'amount_match' => $bankAmount == $cashbookAmount,
                'date_match' => $dateVariance == 0
            ],
            'bank_amount' => $bankAmount,
            'cashbook_amount' => $cashbookAmount,
            'variance' => $variance,
            'bank_date' => $bankTxn->transaction_date,
            'cashbook_date' => Carbon::parse($cashbookDate)->format('Y-m-d'),
            'date_variance_days' => $dateVariance,
            'status' => $variance > 0 || $confidence < 100 ? 'pending_approval' : 'approved',
            'matched_by' => auth()->id() ?? 1,
            'matched_at' => now(),
            'notes' => "Matched via manual reconciliation button with {$confidence}% confidence"
        ]);

        Log::info('Non-matching records cleaned up for matched transactions', [
            'bank_transaction_id' => $bankTxn->id,
            'cashbook_transaction_id' => $cashbookTxn->id,
            'session_id' => $sessionId
        ]);
    }

    /**
     * Create a non-matching transaction record
     */
    private function createNonMatchRecord(int $sessionId, $transaction, string $source): void
    {
        if ($source === 'bank') {
            $amount = $transaction->deposit_amount ?: $transaction->withdrawal_amount;
            $debitAmount = $transaction->withdrawal_amount;
            $creditAmount = $transaction->deposit_amount;
            $transactionDate = $transaction->transaction_date;
            $reference = $transaction->reference_number;
            $narration = $transaction->narration;
        } else {
            $amount = $transaction->amount;
            $debitAmount = $transaction->debit ?? 0;
            $creditAmount = $transaction->credit ?? 0;
            $transactionDate = $transaction->initiated_at ?? $transaction->completed_at ?? $transaction->created_at;
            $reference = $transaction->external_reference ?? $transaction->transaction_uuid;
            $narration = $transaction->narration ?? $transaction->description;
        }

        \App\Models\ReconNonMatchingTransaction::create([
            'session_id' => $sessionId,
            'source' => $source,
            'transaction_id' => $transaction->id,
            'transaction_date' => $transactionDate,
            'reference_number' => $reference,
            'narration' => $narration,
            'debit_amount' => $debitAmount,
            'credit_amount' => $creditAmount,
            'amount' => $amount,
            'non_match_reason' => $source === 'bank' ? 'not_in_cashbook' : 'not_in_bank',
            'reason_details' => "Transaction found in {$source} but no matching transaction found in " . ($source === 'bank' ? 'cashbook' : 'bank statement'),
            'resolution_status' => 'pending',
            'requires_action' => true,
            'action_type' => 'investigate'
        ]);
    }

    /**
     * Find unmatched cashbook transactions
     * Only looks back 35 days from the oldest bank transaction date
     */
    private function findUnmatchedCashbookTransactions(int $sessionId): int
    {
        $session = AnalysisSession::find($sessionId);
        $bankTransactions = BankTransaction::where('session_id', $sessionId)->get();

        if ($bankTransactions->isEmpty()) {
            return 0;
        }

        $minDate = $bankTransactions->min('transaction_date');
        $maxDate = $bankTransactions->max('transaction_date');

        // Apply 35-day lookback from the earliest bank transaction
        $lookbackStartDate = Carbon::parse($minDate)->subDays(35);
        $lookbackEndDate = Carbon::parse($maxDate)->addDay(); // Allow 1 day forward

        // Find cashbook transactions in the 35-day window that aren't matched
        $unmatchedCashbook = Transaction::where('status', 'COMPLETED')
            ->whereBetween('initiated_at', [$lookbackStartDate, $lookbackEndDate])
            ->whereNotIn('id', function($query) use ($sessionId) {
                $query->select('cashbook_transaction_id')
                      ->from('recon_matching_transactions')
                      ->where('session_id', $sessionId);
            })
            ->get();

        foreach ($unmatchedCashbook as $txn) {
            // Only create non-match record if it doesn't already exist
            // This prevents duplicates when running reconciliation multiple times
            $exists = \App\Models\ReconNonMatchingTransaction::where('transaction_id', $txn->id)
                ->where('source', 'cashbook')
                ->exists();

            if (!$exists) {
                $this->createNonMatchRecord($sessionId, $txn, 'cashbook');
            }
        }

        return $unmatchedCashbook->count();
    }

    public function showManualReconciliation($bankTransactionId)
    {
        if (!$this->authorize('reconcile', 'You do not have permission to perform manual reconciliation')) {
            return;
        }
        $this->selectedBankTransactionId = $bankTransactionId;
        $this->showReconciliationModal = true;
        
        // Load available transactions for manual matching
        $bankTransaction = BankTransaction::find($bankTransactionId);
        $this->availableTransactions = Transaction::where('amount', $bankTransaction->amount)
            ->where('status', 'completed')
            ->whereNull('matched_transaction_id')
            ->limit(10)
            ->get();
    }

    public function manualReconcile($transactionId, $notes = null)
    {
        if (!$this->authorize('reconcile', 'You do not have permission to perform manual reconciliation')) {
            return;
        }
        try {
            $reconciliationService = new BankReconciliationService();
            $result = $reconciliationService->manualReconcile($this->selectedBankTransactionId, $transactionId, $notes);
            
            session()->flash('success', $result['message']);
            $this->showReconciliationModal = false;
            $this->selectedBankTransactionId = null;
            
        } catch (Exception $e) {
            session()->flash('error', 'Manual reconciliation failed: ' . $e->getMessage());
        }
    }

    // Utility methods
    private function parseDate($dateString)
    {
        try {
            return Carbon::createFromFormat('d-m-y', trim($dateString))->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    private function convertDateFormat($date)
    {
        try {
            return Carbon::createFromFormat('d.m.Y', $date)->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    private function parseDateTime($dateTimeString)
    {
        try {
            return Carbon::createFromFormat('d-m-Y H:i', $dateTimeString)->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    private function normalizeAmount($amount)
    {
        return (float) str_replace(',', '', $amount);
    }

    private function parseNMBTransactionLine($line, $sessionId, $lineNumber)
    {
        if (preg_match('/(\d{2}\s\w{3}\s\d{4})(\d{2}\s\w{3}\s\d{4})(\w+)\s*(\w+)\s*(.*?)$/', $line, $matches)) {
            $narration = trim($matches[5]);

            // Extract reference from narration or use the one from the line or generate one
            $reference = null;
            if (preg_match('/(?:REF[:.\s]+|#)(\w+)/i', $narration, $refMatch)) {
                $reference = $refMatch[1];
            } else {
                $reference = $matches[3] ?? 'NMB' . str_pad($lineNumber, 6, '0', STR_PAD_LEFT);
            }

            return [
                'session_id' => $sessionId,
                'transaction_date' => $this->parseNMBDate($matches[1]),
                'value_date' => $this->parseNMBDate($matches[2]),
                'reference_number' => $reference,
                'branch' => $matches[4],
                'narration' => $narration,
                'withdrawal_amount' => 0,
                'deposit_amount' => 0,
                'balance' => null,
                'raw_data' => json_encode($matches)
            ];
        }
        return null;
    }

    private function parseNMBDate($dateString)
    {
        try {
            return Carbon::createFromFormat('d M Y', $dateString)->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get session display name
     */
    public function getSessionDisplayName($session)
    {
        $date = \Carbon\Carbon::parse($session->created_at)->format('dmY');
        return "SESSION {$date}-" . str_pad($session->id, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get session status based on transactions
     */
    public function getSessionStatus($session)
    {
        $transactionCount = $session->bankTransactions()->count();

        if ($transactionCount === 0) {
            return 'PENDING';
        }

        $reconciled = $session->bankTransactions()
            ->whereIn('reconciliation_status', ['reconciled', 'matched'])
            ->count();

        if ($reconciled === $transactionCount) {
            return 'COMPLETED';
        }

        return 'PENDING';
    }

    /**
     * Switch active tab
     */
    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    /**
     * Get active bank accounts
     */
    public function getBankAccounts()
    {
        return DB::table('bank_accounts')
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
    }

    /**
     * Get matched transactions for active session
     */
    public function getMatchedTransactions()
    {
        if (!$this->activeSessionId) {
            return collect();
        }

        // Get matching records with both bank and cashbook transactions
        return DB::table('recon_matching_transactions as rmt')
            ->join('bank_transactions as bt', 'rmt.bank_transaction_id', '=', 'bt.id')
            ->join('transactions as ct', 'rmt.cashbook_transaction_id', '=', 'ct.id')
            ->where('rmt.session_id', $this->activeSessionId)
            ->select(
                'rmt.id as match_id',
                'rmt.match_confidence',
                'rmt.match_type',
                'rmt.variance',
                // Bank transaction columns
                'bt.id as bank_id',
                'bt.transaction_date as bank_date',
                'bt.reference_number as bank_reference',
                'bt.narration as bank_narration',
                'bt.withdrawal_amount as bank_debit',
                'bt.deposit_amount as bank_credit',
                'bt.balance as bank_balance',
                // Cashbook transaction columns
                'ct.id as cashbook_id',
                'ct.initiated_at as cashbook_date',
                'ct.external_reference as cashbook_reference',
                'ct.description as cashbook_description',
                'ct.amount as cashbook_amount'
            )
            ->orderBy('bt.transaction_date', 'desc')
            ->paginate(20);
    }

    /**
     * Get cashbook entries that don't match any bank transaction
     */
    public function getCashbookNonMatching()
    {
        if (!$this->activeSessionId) {
            return collect();
        }

        // Query the recon_non_matching_transactions table for cashbook entries
        return DB::table('recon_non_matching_transactions as rnmt')
            ->join('transactions as t', 'rnmt.transaction_id', '=', 't.id')
            ->where('rnmt.session_id', $this->activeSessionId)
            ->where('rnmt.source', 'cashbook')
            ->where('rnmt.resolution_status', 'pending')
            ->select(
                'rnmt.id as non_match_id',
                'rnmt.transaction_date',
                'rnmt.reference_number',
                'rnmt.narration',
                'rnmt.amount',
                'rnmt.debit_amount',
                'rnmt.credit_amount',
                'rnmt.non_match_reason',
                'rnmt.reason_details',
                't.id as transaction_id',
                't.external_reference',
                't.description',
                't.initiated_at'
            )
            ->orderBy('rnmt.transaction_date', 'desc')
            ->paginate(20);
    }

    /**
     * Get bank account non-matching transactions
     */
    public function getBankAccountNonMatching($bankAccountId)
    {
        if (!$this->activeSessionId) {
            return collect();
        }

        // Find the bank account
        $bankAccount = DB::table('bank_accounts')->where('id', $bankAccountId)->first();
        if (!$bankAccount) {
            return collect();
        }

        // Get unmatched bank transactions for this session
        // that mention the bank account in the narration or match the account number
        return BankTransaction::where('session_id', $this->activeSessionId)
            ->where('reconciliation_status', 'unreconciled')
            ->where(function($query) use ($bankAccount) {
                $query->where('narration', 'LIKE', '%' . $bankAccount->account_number . '%')
                      ->orWhere('narration', 'LIKE', '%' . $bankAccount->account_name . '%');
            })
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);
    }

    public function render()
    {
        // Ensure sessions are loaded
        if (!$this->sessions) {
            $this->loadSessions();
        }

        $activeSession = null;
        $bankTransactions = collect();
        $reconciliationSummary = null;

        if ($this->activeSessionId) {
            $activeSession = AnalysisSession::with('bankTransactions')->find($this->activeSessionId);
            if ($activeSession) {
                $bankTransactions = $activeSession->bankTransactions()
                    ->orderBy('transaction_date', 'desc')
                    ->get();
                $reconciliationSummary = $activeSession->reconciliation_summary;
            }
        }

        // Get bank accounts for tabs
        $bankAccounts = $this->getBankAccounts();

        // Get tab-specific data based on active tab
        $tabData = collect();
        if ($this->activeTab === 'matched') {
            $tabData = $this->getMatchedTransactions();
        } elseif ($this->activeTab === 'cashbook') {
            $tabData = $this->getCashbookNonMatching();
        } elseif (str_starts_with($this->activeTab, 'bank_account_')) {
            $bankAccountId = str_replace('bank_account_', '', $this->activeTab);
            $tabData = $this->getBankAccountNonMatching($bankAccountId);
        }

        return view('livewire.reconciliation.reconciliation', array_merge(
            $this->permissions,
            [
                'sessions' => $this->sessions ?? collect(),
                'activeSession' => $activeSession,
                'bankTransactions' => $bankTransactions,
                'reconciliationSummary' => $reconciliationSummary,
                'activeSessionId' => $this->activeSessionId ?? null,
                'showData' => $this->showData ?? false,
                'reconciliationResults' => $this->reconciliationResults ?? null,
                'showReconciliationModal' => $this->showReconciliationModal ?? false,
                'availableTransactions' => $this->availableTransactions ?? collect(),
                'bankAccounts' => $bankAccounts,
                'activeTab' => $this->activeTab,
                'tabData' => $tabData,
                'permissions' => $this->permissions
            ]
        ));
    }
}
