<?php

namespace App\Http\Livewire\ProfileSetting;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use App\Services\AccountCreationService;
use App\Services\MemberNumberGeneratorService;
use App\Services\TransactionPostingService;
use App\Services\BillingService;
use Exception;

class DataMigration extends Component
{
    use WithFileUploads;

    // File upload properties
    public $migrationFile;
    public $fileType = 'csv';
    public $dataType = 'combined';
    public $importMode = 'append';
    
    // Status properties
    public $isUploading = false;
    public $uploadProgress = 0;
    public $isProcessing = false;
    public $processingProgress = 0;
    public $processingMessage = '';
    
    // Results properties
    public $importResults = null;
    public $showResults = false;
    public $errors = [];
    public $warnings = [];
    
    // File validation
    public $maxFileSize = 10240; // 10MB in KB
    public $allowedExtensions = ['csv', 'xlsx', 'xls', 'json'];
    
    // Preview data
    public $previewData = [];
    public $showPreview = false;
    public $columnMapping = [];
    
    // Logger instance
    protected $migrationLogger;
    protected $logFileName;
    
    protected $rules = [
        'migrationFile' => 'required|file|max:10240',
        'fileType' => 'required|in:csv,xlsx,xls,json',
        'dataType' => 'required|in:members,accounts,loans,savings,shares,transactions,combined',
        'importMode' => 'required|in:append,replace,update'
    ];

    protected $messages = [
        'migrationFile.required' => 'Please select a file to upload',
        'migrationFile.file' => 'Please select a valid file',
        'migrationFile.max' => 'File size must not exceed 10MB',
        'fileType.required' => 'Please select a file type',
        'dataType.required' => 'Please select the type of data to import',
        'importMode.required' => 'Please select an import mode'
    ];

    public function mount()
    {
        $this->resetImport();
        $this->initializeMigrationLogger();
    }
    
    /**
     * Initialize a dedicated logger for migration operations
     */
    protected function initializeMigrationLogger()
    {
        // Create log filename with timestamp
        $this->logFileName = 'migration_' . date('Y-m-d_His') . '.log';
        $logPath = storage_path('logs/migrations/' . $this->logFileName);
        
        // Ensure the migrations log directory exists
        if (!file_exists(storage_path('logs/migrations'))) {
            mkdir(storage_path('logs/migrations'), 0755, true);
        }
        
        // Create a new logger instance
        $this->migrationLogger = new Logger('data_migration');
        
        // Create a handler with custom formatting
        $handler = new StreamHandler($logPath, Logger::DEBUG);
        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($output, $dateFormat);
        $handler->setFormatter($formatter);
        
        $this->migrationLogger->pushHandler($handler);
        
        // Log initialization
        $this->migrationLogger->info('Data Migration Logger Initialized', [
            'user' => Auth::user()->name ?? 'System',
            'user_id' => Auth::user()->id ?? 0,
            'session_id' => session()->getId(),
            'ip_address' => request()->ip()
        ]);
    }
    
    /**
     * Log migration events with context
     */
    protected function logMigration($level, $message, array $context = [])
    {
        // Add default context
        $context = array_merge([
            'timestamp' => now()->toIso8601String(),
            'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB',
            'peak_memory' => memory_get_peak_usage(true) / 1024 / 1024 . ' MB'
        ], $context);
        
        // Log to migration logger
        $this->migrationLogger->$level($message, $context);
        
        // Also log errors to Laravel's default logger
        if (in_array($level, ['error', 'critical', 'emergency'])) {
            Log::$level('[Migration] ' . $message, $context);
        }
    }

    public function updatedMigrationFile()
    {
        $this->validateOnly('migrationFile');
        
        if ($this->migrationFile) {
            // Detect file type from extension
            $extension = strtolower($this->migrationFile->getClientOriginalExtension());
            if (in_array($extension, $this->allowedExtensions)) {
                $this->fileType = $extension;
                
                // Preview first few rows
                $this->loadPreview();
            } else {
                $this->addError('migrationFile', 'Invalid file type. Allowed types: ' . implode(', ', $this->allowedExtensions));
            }
        }
    }

    public function loadPreview()
    {
        try {
            $this->showPreview = false;
            $this->previewData = [];
            
            if (!$this->migrationFile) {
                return;
            }
            
            $path = $this->migrationFile->getRealPath();
            
            switch ($this->fileType) {
                case 'csv':
                    $this->previewCsv($path);
                    break;
                case 'xlsx':
                case 'xls':
                    $this->previewExcel($path);
                    break;
                case 'json':
                    $this->previewJson($path);
                    break;
            }
            
            $this->showPreview = count($this->previewData) > 0;
            
        } catch (Exception $e) {
            Log::error('Error loading preview: ' . $e->getMessage());
            $this->addError('migrationFile', 'Error loading file preview: ' . $e->getMessage());
        }
    }

    private function previewCsv($path, $limit = 5)
    {
        $file = fopen($path, 'r');
        $headers = fgetcsv($file);
        
        if ($headers) {
            $this->previewData[] = $headers;
            
            $count = 0;
            while (($row = fgetcsv($file)) !== false && $count < $limit) {
                $this->previewData[] = $row;
                $count++;
            }
        }
        
        fclose($file);
    }

    private function previewExcel($path, $limit = 5)
    {
        // This would require PHPSpreadsheet or similar library
        // For now, we'll just show a message
        $this->previewData[] = ['Excel preview requires additional setup'];
    }

    private function previewJson($path, $limit = 5)
    {
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            $this->previewData = array_slice($data, 0, $limit + 1);
        }
    }

    public function startImport()
    {
        $this->validate();
        
        // Initialize new logger for this import session
        $this->initializeMigrationLogger();
        
        $this->logMigration('info', '=' . str_repeat('=', 60));
        $this->logMigration('info', 'DATA MIGRATION SESSION STARTED');
        $this->logMigration('info', '=' . str_repeat('=', 60));
        $this->logMigration('info', 'Import Configuration:', [
            'data_type' => $this->dataType,
            'file_type' => $this->fileType,
            'import_mode' => $this->importMode,
            'file_name' => $this->migrationFile->getClientOriginalName(),
            'file_size' => $this->migrationFile->getSize() . ' bytes',
            'user' => Auth::user()->name ?? 'System',
            'user_id' => Auth::user()->id ?? 0,
            'branch' => Auth::user()->branch ?? 'N/A',
            'started_at' => now()->toDateTimeString()
        ]);
        
        try {
            $this->isProcessing = true;
            $this->processingProgress = 0;
            $this->processingMessage = 'Initializing import...';
            $this->errors = [];
            $this->warnings = [];
            
            // Store the file
            $filename = 'migration_' . time() . '.' . $this->fileType;
            $path = $this->migrationFile->storeAs('migrations', $filename, 'local');
            
            $this->logMigration('debug', 'File stored successfully', [
                'filename' => $filename,
                'path' => $path,
                'storage_disk' => 'local'
            ]);
            
            // Process based on data type
            $this->logMigration('info', "Starting {$this->dataType} import process");
            
            switch ($this->dataType) {
                case 'members':
                    $this->importMembers($path);
                    break;
                case 'accounts':
                    $this->importAccounts($path);
                    break;
                case 'loans':
                    $this->importLoans($path);
                    break;
                case 'savings':
                    $this->importSavings($path);
                    break;
                case 'shares':
                    $this->importShares($path);
                    break;
                case 'transactions':
                    $this->importTransactions($path);
                    break;
                case 'combined':
                    $this->importCombined($path);
                    break;
            }
            
            // Clean up uploaded file
            if (Storage::disk('local')->exists('migrations/' . $filename)) {
                Storage::disk('local')->delete('migrations/' . $filename);
                $this->logMigration('debug', 'Temporary file cleaned up', ['filename' => $filename]);
            }
            
            $this->showResults = true;
            
            $this->logMigration('info', '=' . str_repeat('=', 60));
            $this->logMigration('info', 'DATA MIGRATION SESSION COMPLETED SUCCESSFULLY');
            $this->logMigration('info', 'Log file available at: storage/logs/migrations/' . $this->logFileName);
            $this->logMigration('info', '=' . str_repeat('=', 60));
            
            session()->flash('success', 'Data migration completed successfully. Log: ' . $this->logFileName);
            
        } catch (Exception $e) {
            $this->logMigration('critical', 'DATA MIGRATION FAILED', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            Log::error('Migration error: ' . $e->getMessage());
            $this->errors[] = 'Migration failed: ' . $e->getMessage();
            session()->flash('error', 'Data migration failed. Check log: ' . $this->logFileName);
        } finally {
            $this->isProcessing = false;
            $this->processingProgress = 100;
            
            $this->logMigration('info', 'Session ended at: ' . now()->toDateTimeString());
        }
    }

    private function importMembers($path)
    {
        $this->processingMessage = 'Importing members data...';
        
        // Implementation for members import
        $successCount = 0;
        $errorCount = 0;
        $skipCount = 0;
        
        // Read and process file
        // This is a placeholder - actual implementation would process the file
        
        $this->importResults = [
            'total' => $successCount + $errorCount + $skipCount,
            'success' => $successCount,
            'errors' => $errorCount,
            'skipped' => $skipCount
        ];
    }

    private function importAccounts($path)
    {
        $this->processingMessage = 'Importing accounts data...';
        // Implementation for accounts import
    }

    private function importLoans($path)
    {
        $this->processingMessage = 'Importing loans data...';
        // Implementation for loans import
    }

    private function importSavings($path)
    {
        $this->processingMessage = 'Importing savings data...';
        // Implementation for savings import
    }

    private function importShares($path)
    {
        $this->processingMessage = 'Importing shares data...';
        // Implementation for shares import
    }

    private function importTransactions($path)
    {
        $this->processingMessage = 'Importing transactions data...';
        // Implementation for transactions import
    }

    private function importCombined($path)
    {
        $this->processingMessage = 'Importing combined member, shares, and savings data...';
        
        // Log import start
        $this->logMigration('info', 'Combined import started', [
            'file_path' => $path,
            'import_mode' => $this->importMode,
            'user' => Auth::user()->name ?? 'System'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Initialize services
            $accountService = new AccountCreationService();
            $memberNumberService = new MemberNumberGeneratorService();
            $transactionService = new TransactionPostingService();
            $billingService = new BillingService();
            
            $successCount = 0;
            $errorCount = 0;
            $skipCount = 0;
            $rowNumber = 0;
            $successfulMembers = [];
            $failedMembers = [];
            
            $fullPath = storage_path('app/' . $path);
            
            $this->logMigration('debug', 'Services initialized', [
                'file_path' => $fullPath
            ]);
            
            // Get institution settings
            $institution = DB::table('institutions')->where('id', 1)->first();
            if (!$institution) {
                throw new Exception('Institution not configured');
            }
            
            $mandatorySharesAccount = $institution->mandatory_shares_account;
            $mandatorySavingsAccount = $institution->mandatory_savings_account;
            $mandatoryDepositsAccount = $institution->mandatory_deposits_account;
            
            if (($handle = fopen($fullPath, 'r')) !== false) {
                // Skip BOM if present
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }
                
                // Read header
                $header = fgetcsv($handle);
                
                // Process each row
                while (($row = fgetcsv($handle)) !== false) {
                    $rowNumber++;
                    $this->processingProgress = min(90, ($rowNumber / 100) * 90);
                    
                    try {
                        // Parse the combined data
                        if (count($row) >= 10) {
                            // Extract and clean member data
                            $memberData = [
                                's_n' => $row[0],
                                'full_name' => trim($row[1]),
                                'member_id' => trim($row[2]),
                                'no_of_shares' => intval($row[3]),
                                'share_value' => $this->cleanAmount($row[4]),
                                'savings_balance' => $this->cleanAmount($row[5]),
                                'gender' => trim($row[6]),
                                'dob' => $this->formatDate($row[7]),
                                'phone_no' => $this->formatPhoneNumber($row[8]),
                                'nin' => trim($row[9] ?? ''),
                                'bank_account' => trim($row[10] ?? '') // External bank account for loan disbursements
                            ];
                            
                            $this->logMigration('debug', "Processing row {$rowNumber}", [
                                'member_name' => $memberData['full_name'],
                                'member_id' => $memberData['member_id'],
                                'shares' => $memberData['no_of_shares'],
                                'savings' => $memberData['savings_balance'],
                                'bank_account' => $memberData['bank_account'] ?: 'NOT PROVIDED'
                            ]);
                            
                            // Parse name parts
                            $nameParts = explode(' ', $memberData['full_name']);
                            $firstName = $nameParts[0] ?? '';
                            $lastName = end($nameParts) ?: '';
                            $middleName = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : '';
                            
                            // Generate member number using the service
                            $clientNumber = $memberNumberService->generate();
                            
                            // Check if member exists (by NIN or phone)
                            $existingMember = null;
                            if ($memberData['nin']) {
                                $existingMember = DB::table('clients')
                                    ->where('national_id', $memberData['nin'])
                                    ->first();
                            }
                            
                            if (!$existingMember && $memberData['phone_no']) {
                                $existingMember = DB::table('clients')
                                    ->where('phone_number', $memberData['phone_no'])
                                    ->first();
                            }
                            
                            if ($this->importMode === 'append' && $existingMember) {
                                $skipCount++;
                                $warningMessage = "Row {$rowNumber}: Member already exists (NIN: {$memberData['nin']}, Phone: {$memberData['phone_no']})";
                                $this->warnings[] = $warningMessage;
                                $this->logMigration('warning', $warningMessage, [
                                    'row' => $rowNumber,
                                    'member_name' => $memberData['full_name'],
                                    'existing_member_id' => $existingMember->id ?? null
                                ]);
                                continue;
                            }
                            
                            if ($this->importMode === 'replace' || !$existingMember) {
                                // Step 1: Create Member Profile (Active immediately for migration)
                                $this->processingMessage = "Creating member: {$memberData['full_name']}";
                                
                                $memberId = DB::table('clients')->insertGetId([
                                    'client_number' => $clientNumber,
                                    'account_number' => $memberData['bank_account'] ?: '000000000000', // External bank account or PENDING if not provided
                                    'first_name' => $firstName,
                                    'middle_name' => $middleName,
                                    'last_name' => $lastName,
                                    'full_name' => $memberData['full_name'],
                                    'gender' => strtolower($memberData['gender']),
                                    'date_of_birth' => $memberData['dob'],
                                    'phone_number' => $memberData['phone_no'],
                                    'mobile_phone_number' => $memberData['phone_no'],
                                    'national_id' => $memberData['nin'],
                                    'membership_type' => 'Individual',
                                    'branch_id' => Auth::user()->branch ?? 1,
                                    'status' => 'ACTIVE', // Active immediately for data migration
                                    'client_status' => 'ACTIVE',
                                    'registering_officer' => Auth::user()->id ?? 1,
                                    'registration_date' => now(),
                                    // Set mandatory financial commitments
                                    'hisa' => $memberData['share_value'],
                                    'akiba' => $memberData['savings_balance'],
                                    'amana' => 0, // Default minimum for deposits
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                                
                                $this->logMigration('info', "Member created successfully", [
                                    'member_id' => $memberId,
                                    'client_number' => $clientNumber,
                                    'member_name' => $memberData['full_name'],
                                    'phone' => $memberData['phone_no'],
                                    'nin' => $memberData['nin']
                                ]);
                                
                                // Step 2: Create Mandatory Accounts
                                $this->processingMessage = "Creating accounts for: {$memberData['full_name']}";
                                
                                // Create Shares Account
                                $sharesAccount = $accountService->createAccount([
                                    'account_use' => 'external',
                                    'account_name' => 'MANDATORY SHARES: ' . $memberData['full_name'],
                                    'type' => 'capital_accounts',
                                    'product_number' => '1000',
                                    'member_number' => $clientNumber,
                                    'branch_number' => Auth::user()->branch ?? '1'
                                ], $mandatorySharesAccount);
                                
                                // Create Savings Account
                                $savingsAccount = $accountService->createAccount([
                                    'account_use' => 'external',
                                    'account_name' => 'MANDATORY SAVINGS: ' . $memberData['full_name'],
                                    'type' => 'liability_accounts',
                                    'product_number' => '2000',
                                    'member_number' => $clientNumber,
                                    'branch_number' => Auth::user()->branch ?? '1'
                                ], $mandatorySavingsAccount);
                                
                                // Create Deposits Account
                                $depositsAccount = $accountService->createAccount([
                                    'account_use' => 'external',
                                    'account_name' => 'MANDATORY DEPOSITS: ' . $memberData['full_name'],
                                    'type' => 'liability_accounts',
                                    'product_number' => '3000',
                                    'member_number' => $clientNumber,
                                    'branch_number' => Auth::user()->branch ?? '1'
                                ], $mandatoryDepositsAccount);
                                
                                $this->logMigration('info', "Accounts created successfully", [
                                    'client_number' => $clientNumber,
                                    'shares_account' => $sharesAccount->account_number ?? 'N/A',
                                    'savings_account' => $savingsAccount->account_number ?? 'N/A',
                                    'deposits_account' => $depositsAccount->account_number ?? 'N/A'
                                ]);
                                
                                // Step 3: Process Initial Share Purchase
                                if ($memberData['no_of_shares'] > 0 && $memberData['share_value'] > 0) {
                                    $this->processingMessage = "Processing shares for: {$memberData['full_name']}";
                                    
                                    // Create share register entry
                                    DB::table('share_registers')->insert([
                                        'member_id' => $memberId,
                                        'member_number' => $clientNumber,
                                        'member_name' => $memberData['full_name'],
                                        'share_account_number' => $sharesAccount->account_number,
                                        'nominal_price' => $memberData['share_value'] / $memberData['no_of_shares'],
                                        'current_price' => $memberData['share_value'] / $memberData['no_of_shares'],
                                        'total_shares_issued' => $memberData['no_of_shares'],
                                        'total_shares_redeemed' => 0,
                                        'total_shares_transferred_in' => 0,
                                        'total_shares_transferred_out' => 0,
                                        'current_share_balance' => $memberData['no_of_shares'],
                                        'total_share_value' => $memberData['share_value'],
                                        'institution_id' => 1,
                                        'branch_id' => Auth::user()->branch ?? 1,
                                        'status' => 'ACTIVE',
                                        'created_at' => now(),
                                        'updated_at' => now()
                                    ]);
                                    
                                    // Post share purchase transaction to GL
                                    // This assumes shares are paid from an external source during migration
                                    $referenceNumber = 'MIG-SHR-' . time() . '-' . $clientNumber;
                                    
                                    
                                    
                                    // Update share account balance
                                    DB::table('accounts')
                                        ->where('account_number', $sharesAccount->account_number)
                                        ->update([
                                            'balance' => DB::raw('balance + ' . $memberData['share_value']),
                                            'updated_at' => now()
                                        ]);
                                    
                                    $this->logMigration('info', "Shares processed successfully", [
                                        'client_number' => $clientNumber,
                                        'reference' => $referenceNumber,
                                        'shares_count' => $memberData['no_of_shares'],
                                        'share_value' => $memberData['share_value'],
                                        'share_account' => $sharesAccount->account_number
                                    ]);
                                }
                                
                                // Step 4: Process Initial Savings Deposit
                                if ($memberData['savings_balance'] > 0) {
                                    $this->processingMessage = "Processing savings for: {$memberData['full_name']}";
                                    
                                    // Post savings deposit transaction to GL
                                    $referenceNumber = 'MIG-SAV-' . time() . '-' . $clientNumber;
                                    
                                    
                                    
                                  
                                    // Update savings account balance
                                    DB::table('accounts')
                                        ->where('account_number', $savingsAccount->account_number)
                                        ->update([
                                            'balance' => DB::raw('balance + ' . $memberData['savings_balance']),
                                            'updated_at' => now()
                                        ]);
                                    
                                    $this->logMigration('info', "Savings processed successfully", [
                                        'client_number' => $clientNumber,
                                        'reference' => $referenceNumber,
                                        'savings_amount' => $memberData['savings_balance'],
                                        'savings_account' => $savingsAccount->account_number
                                    ]);
                                }
                                
                                // Step 5: Create Registration Bills (optional - can be waived for migration)
                                if ($institution->charge_registration_fee ?? false) {
                                    // Registration service
                                    $regService = DB::table('services')
                                        ->where('code', 'REG')
                                        ->first();
                                    
                                    if ($regService) {
                                        $billingService->createBill(
                                            $clientNumber,
                                            $regService->id,
                                            $regService->is_recurring,
                                            $regService->payment_mode,
                                            'MIG-' . $clientNumber,
                                            $regService->lower_limit
                                        );
                                        
                                        $this->logMigration('debug', "Registration fee created", [
                                            'client_number' => $clientNumber,
                                            'service_id' => $regService->id,
                                            'amount' => $regService->lower_limit
                                        ]);
                                    }
                                }
                                
                                $successCount++;
                                $successfulMembers[] = [
                                    'name' => $memberData['full_name'],
                                    'client_number' => $clientNumber,
                                    'shares' => $memberData['no_of_shares'],
                                    'savings' => $memberData['savings_balance']
                                ];
                                
                                $this->logMigration('info', "Member migration completed", [
                                    'row' => $rowNumber,
                                    'client_number' => $clientNumber,
                                    'member_name' => $memberData['full_name'],
                                    'status' => 'SUCCESS'
                                ]);
                                
                                $this->processingMessage = "Successfully migrated: {$memberData['full_name']} (Active)";
                                
                            } else if ($this->importMode === 'update' && $existingMember) {
                                // Update existing member
                                $clientNumber = $existingMember->client_number;
                                
                                $this->logMigration('info', "Updating existing member", [
                                    'row' => $rowNumber,
                                    'client_number' => $clientNumber,
                                    'member_name' => $memberData['full_name']
                                ]);
                                
                                DB::table('clients')
                                    ->where('id', $existingMember->id)
                                    ->update([
                                        'first_name' => $firstName,
                                        'middle_name' => $middleName,
                                        'last_name' => $lastName,
                                        'full_name' => $memberData['full_name'],
                                        'gender' => strtolower($memberData['gender']),
                                        'date_of_birth' => $memberData['dob'],
                                        'phone_number' => $memberData['phone_no'],
                                        'mobile_phone_number' => $memberData['phone_no'],
                                        'national_id' => $memberData['nin'],
                                        'hisa' => $memberData['share_value'],
                                        'akiba' => $memberData['savings_balance'],
                                        'updated_at' => now()
                                    ]);
                                
                                // Update savings balance
                                $savingsAccount = DB::table('accounts')
                                    ->where('client_number', $clientNumber)
                                    ->where('product_number', '2000')
                                    ->first();
                                
                                if ($savingsAccount) {
                                    // Calculate balance difference
                                    $balanceDiff = $memberData['savings_balance'] - $savingsAccount->balance;
                                    
                                    if ($balanceDiff != 0) {
                                        // Post adjustment transaction
                                        $referenceNumber = 'MIG-ADJ-' . time() . '-' . $clientNumber;
                                        
                                     
                                        
                                        // Update account balance
                                        DB::table('accounts')
                                            ->where('account_number', $savingsAccount->account_number)
                                            ->update([
                                                'balance' => $memberData['savings_balance'],
                                                'updated_at' => now()
                                            ]);
                                    }
                                }
                                
                                // Update shares
                                DB::table('share_registers')
                                    ->where('member_number', $clientNumber)
                                    ->update([
                                        'current_share_balance' => $memberData['no_of_shares'],
                                        'total_share_value' => $memberData['share_value'],
                                        'updated_at' => now()
                                    ]);
                                
                                $successCount++;
                                $this->processingMessage = "Successfully updated: {$memberData['full_name']}";
                                
                                $this->logMigration('info', "Member update completed", [
                                    'row' => $rowNumber,
                                    'client_number' => $clientNumber,
                                    'member_name' => $memberData['full_name'],
                                    'status' => 'UPDATED'
                                ]);
                            }
                        }
                    } catch (Exception $e) {
                        $errorCount++;
                        $errorMessage = "Row {$rowNumber}: " . $e->getMessage();
                        $this->errors[] = $errorMessage;
                        
                        $failedMembers[] = [
                            'row' => $rowNumber,
                            'name' => $memberData['full_name'] ?? 'Unknown',
                            'error' => $e->getMessage()
                        ];
                        
                        $this->logMigration('error', "Failed to process member", [
                            'row' => $rowNumber,
                            'member_name' => $memberData['full_name'] ?? 'Unknown',
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'data' => $row
                        ]);
                        
                        Log::error('Combined import error on row ' . $rowNumber, [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'data' => $row
                        ]);
                    }
                }
                
                fclose($handle);
            }
            
            DB::commit();
            
            $this->importResults = [
                'total' => $successCount + $errorCount + $skipCount,
                'success' => $successCount,
                'errors' => $errorCount,
                'skipped' => $skipCount,
                'successful_members' => $successfulMembers,
                'failed_members' => $failedMembers
            ];
            
            $this->processingMessage = 'Combined import completed successfully';
            
            // Log comprehensive import summary
            $this->logMigration('info', '=' . str_repeat('=', 60));
            $this->logMigration('info', 'IMPORT SUMMARY');
            $this->logMigration('info', '=' . str_repeat('=', 60));
            $this->logMigration('info', "Total Rows Processed: " . ($successCount + $errorCount + $skipCount));
            $this->logMigration('info', "✅ Successfully Imported: $successCount");
            $this->logMigration('info', "❌ Failed: $errorCount");
            $this->logMigration('info', "⏭️  Skipped (Duplicates): $skipCount");
            $this->logMigration('info', '=' . str_repeat('=', 60));
            
            if (count($successfulMembers) > 0) {
                $this->logMigration('info', 'SUCCESSFULLY IMPORTED MEMBERS:');
                foreach ($successfulMembers as $member) {
                    $this->logMigration('info', "  - {$member['name']} (#{$member['client_number']}) - Shares: {$member['shares']}, Savings: {$member['savings']}");
                }
            }
            
            if (count($failedMembers) > 0) {
                $this->logMigration('error', 'FAILED IMPORTS:');
                foreach ($failedMembers as $failed) {
                    $this->logMigration('error', "  - Row {$failed['row']}: {$failed['name']} - Error: {$failed['error']}");
                }
            }
            
            $this->logMigration('info', '=' . str_repeat('=', 60));
            $this->logMigration('info', 'Import completed at: ' . now()->toDateTimeString());
            $this->logMigration('info', 'Log file: ' . $this->logFileName);
            $this->logMigration('info', '=' . str_repeat('=', 60));
            
            // Also log to Laravel's default logger
            Log::info('Combined data import completed', [
                'user' => Auth::user()->name ?? 'System',
                'results' => $this->importResults,
                'timestamp' => now()
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            
            $this->logMigration('critical', 'IMPORT FAILED - Transaction rolled back', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->processingMessage = 'Import failed: ' . $e->getMessage();
            $this->errors[] = 'Critical error: ' . $e->getMessage();
            
            throw $e;
        }
    }
    
    private function cleanAmount($amount)
    {
        // Remove spaces, commas, quotes, and currency symbols from amount strings
        $cleaned = preg_replace('/[^0-9.]/', '', str_replace(',', '', $amount));
        return floatval($cleaned);
    }
    
    private function formatDate($date)
    {
        // Convert date format from M/D/Y or DD/MM/YYYY to Y-m-d
        try {
            if (empty($date)) {
                return null;
            }
            
            // Handle different date formats
            if (strpos($date, '/') !== false) {
                $parts = explode('/', $date);
                if (count($parts) === 3) {
                    // Check if it's M/D/Y or D/M/Y format
                    $month = intval($parts[0]);
                    $day = intval($parts[1]);
                    $year = intval($parts[2]);
                    
                    // Handle 2-digit years
                    if ($year < 100) {
                        $year = $year > 50 ? 1900 + $year : 2000 + $year;
                    }
                    
                    // Validate date components
                    if ($month > 0 && $month <= 12 && $day > 0 && $day <= 31) {
                        return sprintf('%04d-%02d-%02d', $year, $month, $day);
                    }
                }
            }
            
            return $date;
        } catch (Exception $e) {
            Log::warning('Date formatting error: ' . $e->getMessage(), ['date' => $date]);
            return null;
        }
    }
    
    private function formatPhoneNumber($phone)
    {
        // Clean and format phone number to match system requirements
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ensure it starts with 0 and is 10 digits
        if (strlen($phone) === 9 && substr($phone, 0, 1) !== '0') {
            $phone = '0' . $phone;
        }
        
        return $phone;
    }

    public function downloadTemplate()
    {
        // Generate and download a template file based on selected data type
        $headers = $this->getTemplateHeaders($this->dataType);
        
        $filename = $this->dataType . '_template.csv';
        $handle = fopen('php://temp', 'r+');
        
        // Add BOM for Excel compatibility
        fwrite($handle, "\xEF\xBB\xBF");
        
        fputcsv($handle, $headers);
        
        // Add sample row for combined template
        if ($this->dataType === 'combined') {
            fputcsv($handle, [
                '1',
                'JOHN DOE SAMPLE',
                '9999',
                '100',
                '500000.00',
                '1000000.00',
                'Male',
                '01/15/1990',
                '0712345678',
                '19900115-12345-00001-23',
                '01234567890' // Sample bank account number
            ]);
        }
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename);
    }
    
    /**
     * Download the migration log file
     */
    public function downloadLogFile()
    {
        if (!$this->logFileName) {
            session()->flash('error', 'No log file available');
            return;
        }
        
        $logPath = storage_path('logs/migrations/' . $this->logFileName);
        
        if (!file_exists($logPath)) {
            session()->flash('error', 'Log file not found');
            return;
        }
        
        $this->logMigration('info', 'Log file downloaded', [
            'user' => Auth::user()->name ?? 'System',
            'file' => $this->logFileName
        ]);
        
        return response()->download($logPath, $this->logFileName, [
            'Content-Type' => 'text/plain',
        ]);
    }

    private function getTemplateHeaders($dataType)
    {
        $templates = [
            'members' => ['client_number', 'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth', 'phone_number', 'email', 'address', 'membership_type', 'branch_id', 'status'],
            'accounts' => ['account_number', 'client_number', 'account_name', 'product_number', 'balance', 'status'],
            'loans' => ['loan_id', 'client_number', 'loan_product', 'principal_amount', 'interest_rate', 'loan_period', 'status'],
            'savings' => ['account_number', 'client_number', 'balance', 'product_type', 'status'],
            'shares' => ['client_number', 'number_of_shares', 'share_price', 'total_value', 'status'],
            'transactions' => ['transaction_id', 'account_number', 'transaction_type', 'amount', 'transaction_date', 'status'],
            'combined' => ['s/n', 'JINA KAMILI', 'ID', 'NO OF SHARES', 'VALUE', 'AKIBA', 'GENDER', 'DOB', 'PHONE NO', 'NIN', 'BANK ACCOUNT']
        ];
        
        return $templates[$dataType] ?? ['column1', 'column2', 'column3'];
    }

    public function resetImport()
    {
        $this->reset(['migrationFile', 'showPreview', 'previewData', 'importResults', 'showResults', 'errors', 'warnings']);
        $this->fileType = 'csv';
        $this->dataType = 'combined';
        $this->importMode = 'append';
    }

    public function cancelImport()
    {
        $this->isProcessing = false;
        $this->processingProgress = 0;
        $this->processingMessage = '';
    }

    public function render()
    {
        return view('livewire.profile-setting.data-migration');
    }
}