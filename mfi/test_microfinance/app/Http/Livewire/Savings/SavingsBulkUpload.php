<?php

namespace App\Http\Livewire\Savings;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BulkSavingsDepositImport;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use App\Models\BankAccount;
use App\Services\TransactionPostingService;
use Carbon\Carbon;

class SavingsBulkUpload extends Component
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
        return view('livewire.savings.savings-bulk-upload');
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
            $import = new BulkSavingsDepositImport();
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

    public function validateRow($row, $rowNumber)
    {
        $errors = [];
        
        // Check required fields - Member ID, Account Number, and Amount are required from file
        if (empty($row[0])) $errors[] = 'Member ID is required';
        if (empty($row[1])) $errors[] = 'Account Number is required';
        if (empty($row[2])) $errors[] = 'Amount is required';

        if (!empty($errors)) return $errors;

        // Validate member exists
        $member = ClientsModel::where('client_number', trim($row[0]))->first();
        if (!$member) {
            $errors[] = 'Member not found';
        }

        // Validate account exists
        $account = AccountsModel::where('account_number', trim($row[1]))->first();
        if (!$account) {
            $errors[] = 'Account not found';
        }

        // Validate that account belongs to the member
        if ($member && $account && $member->client_number !== $account->client_number) {
            $errors[] = 'Account does not belong to the specified member';
        }

        // Validate amount
        if (!is_numeric($row[2]) || $row[2] <= 0) {
            $errors[] = 'Amount must be a positive number';
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
            $import = new BulkSavingsDepositImport();
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

                    $result = $this->processSavingsDeposit($row, $rowNumber);
                    $this->processingResults[] = $result;
                    
                    if ($result['status'] === 'success') {
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

    public function processSavingsDeposit($row, $rowNumber)
    {
        $memberId = trim($row[0]);
        $accountNumber = trim($row[1]);
        $amount = (float)$row[2];
        
        // Set automatic values
        $paymentMethod = 'bank'; // Always bank
        $depositDate = now()->format('Y-m-d');
        $depositTime = now()->format('H:i');
        $referenceNumber = 'SAV-' . time() . '-' . $rowNumber;
        $narration = 'Bulk savings deposit - ' . $memberId . ' - ' . $accountNumber;
        $depositorName = 'Unknown';

        // Get member details
        $member = ClientsModel::where('client_number', $memberId)->first();
        if (!$member) {
            throw new \Exception('Member not found');
        }

        // Get account details
        $account = AccountsModel::where('account_number', $accountNumber)->first();
        if (!$account) {
            throw new \Exception('Account not found');
        }

        // Validate that account belongs to the member
        if ($member->client_number !== $account->client_number) {
            throw new \Exception('Account does not belong to the specified member');
        }

        // Get selected bank accounts
        $bankAccount = BankAccount::find($this->selectedBankAccountId);
        $internalMirrorAccount = BankAccount::find($this->selectedInternalMirrorAccountId);
        
        if (!$bankAccount) {
            throw new \Exception('Selected bank account not found');
        }
        
        if (!$internalMirrorAccount) {
            throw new \Exception('Selected internal mirror account not found');
        }

        // Post the transaction directly using TransactionPostingService
        $transactionService = new TransactionPostingService();
        $transactionData = [
            'first_account' => $internalMirrorAccount->internal_mirror_account_number, // Debit account (bank)
            'second_account' => $accountNumber, // Credit account (savings)
            'amount' => $amount,
            'narration' => $narration,
            'action' => 'savings_deposit'
        ];

        Log::info('Posting savings deposit transaction', [
            'transaction_data' => $transactionData,
            'member_id' => $memberId,
            'row' => $rowNumber
        ]);

        $result = $transactionService->postTransaction($transactionData);
        
        if ($result['status'] !== 'success') {
            throw new \Exception('Failed to post transaction: ' . ($result['message'] ?? 'Unknown error'));
        }

        Log::info('Savings deposit processed successfully', [
            'transaction_reference' => $result['reference_number'] ?? null,
            'amount' => $amount,
            'member_id' => $memberId,
            'row' => $rowNumber
        ]);

        return [
            'row' => $rowNumber,
            'status' => 'success',
            'message' => 'Savings deposit processed successfully',
            'member' => $memberId,
            'account' => $accountNumber,
            'amount' => number_format($amount, 2),
            'reference' => $result['reference_number'] ?? 'N/A',
            'payment_method' => ucfirst($paymentMethod)
        ];
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

        $filename = 'savings_bulk_upload_template_' . date('Y-m-d') . '.csv';
        
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
