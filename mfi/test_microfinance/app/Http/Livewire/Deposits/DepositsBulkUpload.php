<?php

namespace App\Http\Livewire\Deposits;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BulkDepositsImport;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use App\Models\BankAccount;
use App\Services\TransactionPostingService;
use Carbon\Carbon;
use Exception;
use App\Models\general_ledger;
use App\Models\SubProducts;

class DepositsBulkUpload extends Component
{
    use WithFileUploads;

    // File upload properties
    public $uploadFile;
    public $isUploading = false;
    public $uploadProgress = 0;
    public $processingStatus = '';
    
    // Bank account selection properties
    public $selectedBankAccountId;
    public $selectedInternalMirrorAccountId;
    public $bankAccounts = [];
    public $internalMirrorAccounts = [];
    
    // Validation and processing properties
    public $validationErrors = [];
    public $processedRecords = 0;
    public $totalRecords = 0;
    public $successCount = 0;
    public $errorCount = 0;
    public $skippedCount = 0;
    
    // Results display
    public $showResults = false;
    public $processingResults = [];
    public $downloadTemplate = false;
    
    // UI state
    public $currentStep = 1; // 1: Upload, 2: Preview, 3: Process, 4: Results
    public $previewData = [];
    public $selectedRecords = [];
    public $selectAll = false;
    
    // Configuration
    public $maxFileSize = 10240; // 10MB
    public $allowedExtensions = ['xlsx', 'xls', 'csv'];
    
    protected $rules = [
        'uploadFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        'selectedBankAccountId' => 'required|exists:bank_accounts,id',
        'selectedInternalMirrorAccountId' => 'required|exists:bank_accounts,id'
    ];

    protected $messages = [
        'uploadFile.required' => 'Please select a file to upload.',
        'uploadFile.file' => 'The uploaded file is invalid.',
        'uploadFile.mimes' => 'Please upload a valid Excel or CSV file.',
        'uploadFile.max' => 'File size must not exceed 10MB.',
        'selectedBankAccountId.required' => 'Please select a bank account.',
        'selectedBankAccountId.exists' => 'Selected bank account is invalid.',
        'selectedInternalMirrorAccountId.required' => 'Please select an internal mirror account.',
        'selectedInternalMirrorAccountId.exists' => 'Selected internal mirror account is invalid.'
    ];

    public function mount()
    {
        $this->resetUploadState();
        $this->loadBankAccounts();
    }

    public function loadBankAccounts()
    {
        $this->bankAccounts = BankAccount::active()->get();
        $this->internalMirrorAccounts = BankAccount::active()->get();
    }

    public function render()
    {
        return view('livewire.deposits.deposits-bulk-upload');
    }

    public function updatedUploadFile()
    {
        $this->resetValidation();
        $this->validateOnly('uploadFile');
        
        if ($this->uploadFile) {
            $this->isUploading = false;
            $this->processingStatus = 'File uploaded successfully. Click Preview to continue.';
        }
    }

    public function processFilePreview()
    {
        try {
            // Validate that file exists and is properly uploaded
            if (!$this->uploadFile) {
                $this->addError('uploadFile', 'Please select a file to upload.');
                return;
            }

            $this->validate();
            $this->isUploading = true;
            $this->processingStatus = 'Reading file...';
            $this->uploadProgress = 10;

            // Read the first few rows for preview
            $import = new BulkDepositsImport();
            $import->setPreviewMode(true);
            
            $this->previewData = Excel::toArray($import, $this->uploadFile)[0] ?? [];
            
            // Remove header row and limit to 10 rows for preview
            $headers = array_shift($this->previewData);
            $this->previewData = array_slice($this->previewData, 0, 10);
            
            $this->totalRecords = count($this->previewData);
            $this->currentStep = 2;
            $this->uploadProgress = 100;
            $this->processingStatus = 'File preview ready';
            $this->isUploading = false;

        } catch (\Exception $e) {
            Log::error('Error processing file preview', [
                'error' => $e->getMessage(),
                'file' => $this->uploadFile ? $this->uploadFile->getClientOriginalName() : 'No file'
            ]);
            
            $this->addError('uploadFile', 'Error reading file: ' . $e->getMessage());
            $this->resetUploadState();
        }
    }

    public function validatePreviewData()
    {
        $this->validationErrors = [];
        $this->processingStatus = 'Validating data...';
        $this->uploadProgress = 20;

        $rowNumber = 2;
        foreach ($this->previewData as $index => $row) {
            $rowErrors = $this->validateRow($row, $rowNumber);
            
            if (!empty($rowErrors)) {
                $this->validationErrors[$rowNumber] = $rowErrors;
                $this->errorCount++;
            } else {
                $this->successCount++;
            }
            $rowNumber++;
        }

        $this->skippedCount = $this->totalRecords - $this->successCount - $this->errorCount;
        $this->uploadProgress = 100;
        $this->processingStatus = 'Validation complete';
        return $this->errorCount === 0;
    }

    /**
     * Enhanced validation for deposits transactions
     */
    public function validateRow($row, $rowNumber)
    {
        $errors = [];
        
        // Check required fields - Member ID, Account Number, and Amount are required from file
        if (empty($row[0])) $errors[] = 'Member ID is required';
        if (empty($row[1])) $errors[] = 'Account Number is required';
        if (empty($row[2])) $errors[] = 'Amount is required';

        if (!empty($errors)) return $errors;

        $memberId = trim($row[0]);
        $accountNumber = trim($row[1]);
        $amount = floatval($row[2]);

        // Validate member exists and is active
        $member = ClientsModel::where('client_number', $memberId)->first();
        if (!$member) {
            $errors[] = 'Member not found';
        } elseif ($member->status !== 'ACTIVE') {
            $errors[] = 'Member account is not active';
        }

        // Validate account exists and belongs to member
        $account = AccountsModel::where('account_number', $accountNumber)
            ->where('client_number', $memberId)
            ->where('major_category_code', 2000) // Deposits category
            ->first();

        if (!$account) {
            $errors[] = 'Account not found or doesn\'t belong to member';
        } elseif ($account->status !== 'ACTIVE') {
            $errors[] = 'Account is not active for transactions';
        }

        // Validate amount with business rules
        if (!is_numeric($amount) || $amount <= 0) {
            $errors[] = 'Amount must be a positive number';
        } elseif ($amount < 100) {
            $errors[] = 'Minimum deposit amount is 100 TZS';
        } elseif ($amount > 10000000) {
            $errors[] = 'Maximum deposit amount is 10,000,000 TZS';
        }

        // Check for suspicious transaction patterns
        if ($amount > 1000000) { // 1M TZS threshold
            $recentLargeTransactions = general_ledger::whereHas('account', function($q) use ($accountNumber) {
                $q->where('account_number', $accountNumber);
            })
            ->where('credit', '>', 1000000)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

            if ($recentLargeTransactions >= 5) {
                $errors[] = 'Account has multiple large transactions - requires review';
            }
        }

        // Validate daily transaction limits
        $todayTransactions = general_ledger::whereHas('account', function($q) use ($accountNumber) {
            $q->where('account_number', $accountNumber);
        })
        ->where('credit', '>', 0)
        ->whereDate('created_at', Carbon::today())
        ->sum('credit');

        if (($todayTransactions + $amount) > 5000000) { // 5M TZS daily limit
            $errors[] = 'Daily transaction limit would be exceeded';
        }

        return $errors;
    }

    public function processBulkUpload()
    {
        try {
            $this->currentStep = 3;
            $this->processingStatus = 'Starting bulk upload process...';
            $this->uploadProgress = 0;
            $this->processingResults = [];
            $this->successCount = 0;
            $this->errorCount = 0;
            $this->skippedCount = 0;

            // Read all data from file
            $import = new BulkDepositsImport();
            $allData = Excel::toArray($import, $this->uploadFile)[0] ?? [];
            
            // Remove header row
            $headers = array_shift($allData);
            $this->totalRecords = count($allData);

            $this->processingStatus = 'Processing ' . $this->totalRecords . ' records...';

            foreach ($allData as $index => $row) {
                $rowNumber = $index + 2; // +2 because we removed header and arrays are 0-indexed
                
                try {
                    $this->processingStatus = "Processing row {$rowNumber} of {$this->totalRecords}...";
                    $this->uploadProgress = (($index + 1) / $this->totalRecords) * 100;

                    $result = $this->processDepositsDeposit($row, $rowNumber);
                    $this->processingResults[] = $result;
                    
                    if ($result['success']) {
                        $this->successCount++;
                    } else {
                        $this->errorCount++;
                    }

                } catch (\Exception $e) {
                    Log::error('Error processing row', [
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                        'data' => $row
                    ]);
                    
                    $this->processingResults[] = [
                        'row' => $rowNumber,
                        'status' => 'error',
                        'message' => $e->getMessage(),
                        'member' => $row[0] ?? 'N/A',
                        'account' => $row[1] ?? 'N/A',
                        'amount' => $row[2] ?? 'N/A',
                        'reference' => 'N/A'
                    ];
                    $this->errorCount++;
                }
            }

            $this->skippedCount = $this->totalRecords - $this->successCount - $this->errorCount;
            $this->currentStep = 4;
            $this->processingStatus = 'Bulk upload completed';

        } catch (\Exception $e) {
            Log::error('Error in bulk upload process', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->addError('uploadFile', 'Bulk upload failed: ' . $e->getMessage());
            $this->currentStep = 2;
        }
    }

    /**
     * Enhanced deposit processing with business logic
     */
    public function processDepositsDeposit($row, $rowNumber)
    {
        try {
            // Validate required fields
            if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                return [
                    'success' => false,
                    'message' => "Row {$rowNumber}: Missing required fields (Member ID, Account Number, Amount)"
                ];
            }

            $memberId = trim($row[0]);
            $accountNumber = trim($row[1]);
            $amount = floatval($row[2]);
            $narration = isset($row[3]) ? trim($row[3]) : '';

            // Enhanced validation
            $validationResult = $this->validateDepositTransaction([
                'member_id' => $memberId,
                'account_number' => $accountNumber,
                'amount' => $amount,
                'narration' => $narration
            ]);

            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'message' => "Row {$rowNumber}: " . implode(', ', $validationResult['errors'])
                ];
            }

            // Check if member exists and is active
            $member = ClientsModel::where('client_number', $memberId)
                ->where('status', 'ACTIVE')
                ->first();
            
            if (!$member) {
                return [
                    'success' => false,
                    'message' => "Row {$rowNumber}: Member not found or inactive"
                ];
            }

            // Check if account exists and belongs to member
            $account = AccountsModel::where('account_number', $accountNumber)
                ->where('client_number', $memberId)
                ->where('major_category_code', 2000) // Deposits category
                ->where('status', 'ACTIVE')
                ->first();

            if (!$account) {
                return [
                    'success' => false,
                    'message' => "Row {$rowNumber}: Account not found or inactive"
                ];
            }

            // Check minimum balance requirement
            $minBalanceCheck = $this->checkMinimumBalance($account, $amount);
            if (!$minBalanceCheck['valid']) {
                return [
                    'success' => false,
                    'message' => "Row {$rowNumber}: " . $minBalanceCheck['message']
                ];
            }

            // Generate transaction reference with proper format
            $reference = $this->generateTransactionReference($rowNumber);

            // Create standardized narration
            $narration = $this->createStandardizedNarration($memberId, $accountNumber, $amount, $narration);

            // Get cash account dynamically
            $cashAccount = $this->getCashAccount();
            if (!$cashAccount) {
                return [
                    'success' => false,
                    'message' => "Row {$rowNumber}: Cash account not configured"
                ];
            }

            // Post transaction with enhanced data
            $transactionData = [
                'reference_number' => $reference,
                'transaction_date' => now()->format('Y-m-d'),
                'narration' => $narration,
                'amount' => $amount,
                'first_account' => $cashAccount->account_number, // Dynamic cash account
                'second_account' => $accountNumber, // Credit account (deposits)
                'transaction_type' => 'credit',
                'action' => 'deposits_deposit',
                'member_id' => $memberId,
                'account_holder_name' => $member->first_name . ' ' . $member->last_name,
                'payment_method' => 'bulk_upload',
                'processed_by' => Auth::user()->name ?? 'System',
                'institution_id' => $this->getCurrentInstitution()
            ];

            Log::info('Posting deposits deposit transaction', [
                'reference' => $reference,
                'member' => $memberId,
                'account' => $accountNumber,
                'amount' => $amount,
                'processed_by' => Auth::user()->name ?? 'System'
            ]);

            // Post the transaction
            $transactionService = new TransactionPostingService();
            $transactionServiceData = [
                'first_account' => $cashAccount->account_number,
                'second_account' => $accountNumber,
                'amount' => $amount,
                'narration' => 'Deposits deposit for ' . $memberId . ' - ' . $accountNumber . ' - ' . $amount,
                // 'action' => 'deposits_deposit'
            ];
            $result = $transactionService->postTransaction($transactionServiceData);

            if ($result['status'] == 'success') {
                // Update account balance
                $this->updateAccountBalance($account, $amount);
                
                // Log successful transaction
                Log::info('Deposits deposit processed successfully', [
                    'reference' => $reference,
                    'member' => $memberId,
                    'account' => $accountNumber,
                    'amount' => $amount,
                    'new_balance' => $account->balance + $amount
                ]);

                // Check for suspicious activity
                $this->monitorSuspiciousTransactions($transactionData);

                return [
                    'success' => true,
                    'message' => 'Deposits deposit processed successfully',
                    'reference' => $reference,
                    'new_balance' => $account->balance + $amount
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Row {$rowNumber}: " . $result['message']
                ];
            }

        } catch (Exception $e) {
            Log::error('Error processing deposits deposit', [
                'row' => $rowNumber,
                'data' => $row,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => "Row {$rowNumber}: Processing error - " . $e->getMessage()
            ];
        }
    }

    /**
     * Enhanced validation for deposit transactions
     */
    private function validateDepositTransaction($data)
    {
        $errors = [];
        
        // Basic validation rules
        $rules = [
            'amount' => 'required|numeric|min:100|max:10000000',
            'member_id' => 'required|string|max:50',
            'account_number' => 'required|string|max:50',
            'narration' => 'nullable|string|max:255'
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);
        
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
        }

        // Business rule validations
        if (empty($errors)) {
            // Check if reference number already exists
            $existingReference = general_ledger::where('reference_number', $data['reference_number'] ?? '')->exists();
            if ($existingReference) {
                $errors[] = 'Transaction reference already exists';
            }

            // Check for duplicate transactions
            $duplicateCheck = general_ledger::whereHas('account', function($q) use ($data) {
                $q->where('account_number', $data['account_number']);
            })
            ->where('credit', $data['amount'])
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->exists();

            if ($duplicateCheck) {
                $errors[] = 'Possible duplicate transaction detected';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Check minimum balance requirement
     */
    private function checkMinimumBalance($account, $amount)
    {
        try {
            // Get product minimum balance requirement
            $product = $account->shareProduct;
            $minBalance = $product->min_balance ?? 0;
            
            $newBalance = $account->balance + $amount;
            
            if ($newBalance < $minBalance) {
                return [
                    'valid' => false,
                    'message' => "Transaction would violate minimum balance requirement of " . number_format($minBalance, 2) . " TZS"
                ];
            }

            return ['valid' => true, 'message' => ''];
        } catch (Exception $e) {
            Log::error('Error checking minimum balance: ' . $e->getMessage());
            return ['valid' => true, 'message' => '']; // Allow transaction if check fails
        }
    }

    /**
     * Generate transaction reference with proper format
     */
    private function generateTransactionReference($rowNumber)
    {
        return sprintf(
            'BULK_DEP_%s_%s_%s',
            date('YmdHis'),
            str_pad($rowNumber, 4, '0', STR_PAD_LEFT),
            strtoupper(substr(md5(uniqid()), 0, 8))
        );
    }

    /**
     * Create standardized narration
     */
    private function createStandardizedNarration($memberId, $accountNumber, $amount, $customNarration = '')
    {
        $baseNarration = sprintf(
            'DEPOSIT|%s|%s|%s|BULK_UPLOAD|%s',
            $memberId,
            $accountNumber,
            number_format($amount, 2),
            date('Y-m-d H:i:s')
        );

        if (!empty($customNarration)) {
            return $baseNarration . '|' . substr($customNarration, 0, 100);
        }

        return $baseNarration;
    }

    /**
     * Get cash account dynamically
     */
    private function getCashAccount()
    {
        $operationAccount = DB::table('institutions')->where('id', '1')->value('operations_account');
        return AccountsModel::where('account_number', $operationAccount)
            ->first();
    }

    /**
     * Get current institution
     */
    private function getCurrentInstitution()
    {
        return Auth::user()->institution_id ?? '1';
    }

    /**
     * Update account balance
     */
    private function updateAccountBalance($account, $amount)
    {
        try {
            $account->balance += $amount;
            $account->save();
            
            Log::info('Account balance updated', [
                'account_number' => $account->account_number,
                'old_balance' => $account->balance - $amount,
                'new_balance' => $account->balance,
                'amount_added' => $amount
            ]);
        } catch (Exception $e) {
            Log::error('Error updating account balance: ' . $e->getMessage());
            throw new Exception('Failed to update account balance');
        }
    }

    /**
     * Monitor suspicious transactions
     */
    private function monitorSuspiciousTransactions($transactionData)
    {
        try {
            $thresholds = [
                'large_amount' => 1000000, // 1M TZS
                'frequent_transactions' => 10, // per day
                'unusual_pattern' => true
            ];

            // Check for large amounts
            if ($transactionData['amount'] > $thresholds['large_amount']) {
                $this->flagForReview($transactionData, 'Large amount transaction');
            }

            // Check for frequent transactions
            $todayTransactions = general_ledger::whereHas('account', function($q) use ($transactionData) {
                $q->where('account_number', $transactionData['second_account']);
            })
            ->whereDate('created_at', Carbon::today())
            ->count();

            if ($todayTransactions > $thresholds['frequent_transactions']) {
                $this->flagForReview($transactionData, 'Frequent transactions detected');
            }

        } catch (Exception $e) {
            Log::error('Error monitoring suspicious transactions: ' . $e->getMessage());
        }
    }

    /**
     * Flag transaction for review
     */
    private function flagForReview($transactionData, $reason)
    {
        try {
            // Log suspicious activity
            Log::warning('Suspicious transaction flagged for review', [
                'transaction_data' => $transactionData,
                'reason' => $reason,
                'flagged_by' => Auth::user()->name ?? 'System',
                'flagged_at' => now()
            ]);

            // Here you could also send notifications to management
            // or create a review queue entry

        } catch (Exception $e) {
            Log::error('Error flagging transaction for review: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Member ID',
            'Account Number',
            'Amount'
        ];

        $sampleData = [
            [
                'M001',
                'ACC001',
                '50000.00'
            ],
            [
                'M002',
                'ACC002',
                '25000.00'
            ]
        ];

        $filename = 'deposits_bulk_upload_template_' . date('Y-m-d') . '.csv';
        
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);
        
        foreach ($sampleData as $row) {
            fputcsv($handle, $row);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function resetUploadState()
    {
        $this->uploadFile = null;
        $this->isUploading = false;
        $this->uploadProgress = 0;
        $this->processingStatus = '';
        $this->validationErrors = [];
        $this->processedRecords = 0;
        $this->totalRecords = 0;
        $this->successCount = 0;
        $this->errorCount = 0;
        $this->skippedCount = 0;
        $this->showResults = false;
        $this->processingResults = [];
        $this->currentStep = 1;
        $this->previewData = [];
        $this->selectedRecords = [];
        $this->selectAll = false;
    }

    public function goBack()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRecords = range(0, count($this->previewData) - 1);
        } else {
            $this->selectedRecords = [];
        }
    }

    public function updatedSelectedRecords()
    {
        $this->selectAll = count($this->selectedRecords) === count($this->previewData);
    }
}
