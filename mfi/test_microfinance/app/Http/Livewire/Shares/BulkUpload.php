<?php

namespace App\Http\Livewire\Shares;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BulkShareIssuanceImport;
use App\Models\approvals;
use App\Models\ClientsModel;
use App\Models\sub_products;
use App\Models\AccountsModel;
use App\Services\TransactionPostingService;
use Carbon\Carbon;

class BulkUpload extends Component
{
    use WithFileUploads;

    // File upload properties
    public $uploadFile;
    public $isUploading = false;
    public $uploadProgress = 0;
    public $processingStatus = '';
    
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
        'uploadFile' => 'required|file|mimes:xlsx,xls,csv|max:10240'
    ];

    protected $messages = [
        'uploadFile.required' => 'Please select a file to upload.',
        'uploadFile.file' => 'The uploaded file is invalid.',
        'uploadFile.mimes' => 'Please upload a valid Excel or CSV file.',
        'uploadFile.max' => 'File size must not exceed 10MB.'
    ];

    public function mount()
    {
        $this->resetUploadState();
    }

    public function render()
    {
        return view('livewire.shares.bulk-upload');
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
            $import = new BulkShareIssuanceImport();
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
        
        // Check required fields - updated column mapping
        if (empty($row[0])) $errors[] = 'Member ID is required';
        if (empty($row[1])) $errors[] = 'Linked Savings Account is required';
        if (empty($row[2])) $errors[] = 'Linked Share Account is required';
        if (empty($row[3])) $errors[] = 'Number of Shares is required';

        if (!empty($errors)) return $errors;

        // Validate member exists
        $member = ClientsModel::where('client_number', trim($row[0]))->first();
        if (!$member) {
            $errors[] = 'Member not found';
        }

        // Validate linked savings account exists
        $savingsAccount = AccountsModel::where('account_number', trim($row[1]))->first();
        if (!$savingsAccount) {
            $errors[] = 'Linked savings account not found';
        }

        // Validate linked share account exists and get product details
        $shareAccount = AccountsModel::where('account_number', trim($row[2]))->first();
        if (!$shareAccount) {
            $errors[] = 'Linked share account not found';
        } else {
            // Get parent account from share account
           
            $parentAccount = AccountsModel::where('account_number', $shareAccount->parent_account_number)->first();

            //dd($parentAccount);
            if (!$parentAccount) {
                $errors[] = 'Parent account not found for share account';
            } else {
                // Get product details from sub_products table
                $product = DB::table('sub_products')->where('product_account', $parentAccount->account_number)->first();
                if (!$product) {
                    $errors[] = 'Share product not found for this account';
                }
            }

            // Validate if the amount is greater than the balance of the savings account
            if ($row[3] * $product->nominal_price > $savingsAccount->balance) {
                $errors[] = 'Not enough balance to purchase shares. Reduce the number of shares or increase the balance of the savings account';
            }
        }

        // Validate number of shares
        if (!is_numeric($row[3]) || $row[3] <= 0) {
            $errors[] = 'Number of shares must be a positive number';
        }

        return $errors;
    }

    public function processBulkUpload()
    {
        try {
            $this->isUploading = true;
            $this->currentStep = 3;
            $this->processingStatus = 'Processing bulk upload...';
            $this->uploadProgress = 0;

            // Validate all data first
            if (!$this->validatePreviewData()) {
                $this->addError('validation', 'Please fix validation errors before proceeding.');
                $this->currentStep = 2;
                $this->isUploading = false;
                return;
            }

            $this->uploadProgress = 30;
            $this->processingStatus = 'Creating share issuance records...';

            // Process each valid record
            $processedRecords = [];
            $rowNumber = 2;

            foreach ($this->previewData as $index => $row) {
                if (isset($this->validationErrors[$rowNumber])) {
                    $rowNumber++;
                    continue;
                }

                $result = $this->processShareIssuance($row, $rowNumber);
                $processedRecords[] = $result;
                
                $this->processedRecords++;
                $this->uploadProgress = 30 + (($this->processedRecords / $this->successCount) * 60);
                $this->processingStatus = "Processed {$this->processedRecords} of {$this->successCount} records...";
                
                $rowNumber++;
            }

            $this->uploadProgress = 100;
            $this->processingStatus = 'Upload completed successfully!';
            
            // Store results for display
            $this->processingResults = $processedRecords;
            $this->currentStep = 4;
            $this->showResults = true;

            session()->flash('success', "Bulk upload completed! {$this->successCount} records processed successfully.");

        } catch (\Exception $e) {
            Log::error('Error in bulk upload processing', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            $this->addError('processing', 'Error processing bulk upload: ' . $e->getMessage());
            $this->currentStep = 2;
        } finally {
            $this->isUploading = false;
        }
    }

    public function processShareIssuance($row, $rowNumber)
    {
        try {
            DB::beginTransaction();

            $memberId = trim($row[0]);
            $linkedSavingsAccount = trim($row[1]);
            $linkedShareAccount = trim($row[2]);
            $numberOfShares = (int)$row[3];

            // Get member details
            $member = ClientsModel::where('client_number', $memberId)->first();
            $savingsAccount = AccountsModel::where('account_number', $linkedSavingsAccount)->first();
            $shareAccount = AccountsModel::where('account_number', $linkedShareAccount)->first();

            // Get parent account from share account
            $parentAccount = AccountsModel::where('account_number', $shareAccount->parent_account_number)->first();
            
            // Get product details from sub_products table
            $product = DB::table('sub_products')->where('product_account', $parentAccount->account_number)->first();
            
            $pricePerShare = $product->nominal_price ?? 0;
            $totalValue = $numberOfShares * $pricePerShare;

            // Generate reference number
            $referenceNumber = 'BULK' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Create share issuance record
            $issuanceId = DB::table('issued_shares')->insertGetId([
                'reference_number' => $referenceNumber,
                'share_id' => $product->id ?? null,
                'member' => trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name),
                'product' => $product->id ?? null,
                'account_number' => $linkedShareAccount,
                'price' => $pricePerShare,
                'branch' => Auth::user()->branch ?? null,
                'client_number' => $memberId,
                'number_of_shares' => $numberOfShares,
                'nominal_price' => $pricePerShare,
                'total_value' => $totalValue,
                'linked_savings_account' => $linkedSavingsAccount,
                'linked_share_account' => $linkedShareAccount,
                'status' => 'PENDING',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create approval request
            $newAccountData = [
                'type' => 'bulk_share_issuance',
                'reference_number' => $referenceNumber,
                'member_id' => $memberId,
                'member_name' => trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name),
                'product_id' => $product->id ?? null,
                'product_name' => $product->product_name ?? 'Unknown Product',
                'number_of_shares' => $numberOfShares,
                'nominal_price' => $pricePerShare,
                'total_amount' => $totalValue,
                'linked_savings_account' => $linkedSavingsAccount,
                'share_account' => $linkedShareAccount,
                'transaction_type' => 'PURCHASE',
                'transaction_date' => now()->format('Y-m-d'),
                'status' => 'PENDING',
                'created_by' => Auth::id()
            ];

            $editPackage = json_encode($newAccountData);
            
            approvals::create([
                'process_name' => 'bulk_share_issuance',
                'process_description' => Auth::user()->name . ' has issued ' . $numberOfShares . ' shares to ' . $member->first_name . ' ' . $member->last_name,
                'approval_process_description' => 'Bulk share issuance approval required',
                'process_code' => 'SHARE_ISS',
                'process_id' => $issuanceId,
                'process_status' => 'PENDING',
                'user_id' => Auth::id(),
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => $editPackage,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return [
                'row' => $rowNumber,
                'status' => 'success',
                'message' => 'Share issuance created successfully',
                'reference' => $referenceNumber,
                'member' => $member->first_name . ' ' . $member->last_name,
                'shares' => $numberOfShares,
                'value' => $totalValue
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing share issuance for row', [
                'row' => $rowNumber,
                'error' => $e->getMessage(),
                'data' => $row
            ]);

            return [
                'row' => $rowNumber,
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => $row
            ];
        }
    }

    public function downloadTemplate()
    {
        // Get members with their client numbers
        $members = DB::table('clients')
            ->whereNotNull('client_number')
            ->where('client_number', '!=', '')
            ->select('client_number', 'first_name', 'last_name')
            ->limit(10) // Limit to 10 sample records
            ->get();

        // Get savings accounts (product_number = 2000)
        $savingsAccounts = DB::table('accounts')
            ->where('product_number', '2000')
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->select('account_number', 'account_name', 'client_number')
            ->limit(10)
            ->get();

        // Get share accounts (product_number = 1000)
        $shareAccounts = DB::table('accounts')
            ->where('product_number', '1000')
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->select('account_number', 'account_name', 'client_number')
            ->limit(10)
            ->get();

        $headers = [
            'Member ID',
            'Linked Savings Account',
            'Linked Share Account', 
            'Number of Shares'
        ];

        // Create sample data with real member and account data
        $sampleData = [];
        
        foreach ($members as $index => $member) {
            // Find matching savings account for this member
            $savingsAccount = $savingsAccounts->where('client_number', $member->client_number)->first();
            
            // Find matching share account for this member
            $shareAccount = $shareAccounts->where('client_number', $member->client_number)->first();
            
            if ($savingsAccount && $shareAccount) {
                $sampleData[] = [
                    $member->client_number, // Already padded string from database
                    $savingsAccount->account_number,
                    $shareAccount->account_number,
                    '100' // Default number of shares
                ];
            }
        }

        // If no matching data found, create sample data with available accounts
        if (empty($sampleData)) {
            $sampleData = [
                ['M001', 'SAV001', 'SHARE001', '100'],
                ['M002', 'SAV002', 'SHARE002', '50'],
            ];
        }

        $filename = 'bulk_share_issuance_template_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($headers, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($sampleData as $row) {
                // Ensure all values are treated as strings to preserve leading zeros
                $formattedRow = array_map(function($value) {
                    return (string) $value;
                }, $row);
                fputcsv($file, $formattedRow);
            }
            fclose($file);
        }, $filename);
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
        $this->previewData = [];
        $this->selectedRecords = [];
        $this->selectAll = false;
        $this->currentStep = 1;
        $this->resetErrorBag();
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
