<?php

namespace App\Http\Livewire\Clients;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BulkMembersImport;
use App\Models\ClientsModel;
use App\Models\BranchesModel;
use App\Models\approvals;
use App\Services\MemberNumberGeneratorService;
use App\Services\AccountCreationService;
use App\Services\BillingService;
use App\Services\MembershipVerificationService;
use App\Jobs\ProcessMemberNotifications;
use Carbon\Carbon;

class BulkImport extends Component
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
        return view('livewire.clients.bulk-import');
    }

    public function updatedUploadFile()
    {
        $this->resetValidation();
        $this->validateOnly('uploadFile');
        
        if ($this->uploadFile) {
            $this->isUploading = false;
            $this->processingStatus = 'File uploaded successfully. Click Preview to continue.';
            
            // Reset validation state when new file is uploaded
            $this->resetValidationState();
            
            // Reset to step 1 if we were on a later step
            if ($this->currentStep > 1) {
                $this->currentStep = 1;
            }
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

            // Reset validation state before processing new file
            $this->resetValidationState();

            // Read the first few rows for preview
            $import = new BulkMembersImport();
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

            // Validate preview data
            $this->validatePreviewData();

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
        // Reset counters before validation
        $this->validationErrors = [];
        $this->successCount = 0;
        $this->errorCount = 0;
        $this->skippedCount = 0;
        
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
        
        Log::info('Validation completed', [
            'total' => $this->totalRecords,
            'success' => $this->successCount,
            'errors' => $this->errorCount,
            'skipped' => $this->skippedCount
        ]);
        
        return $this->errorCount === 0;
    }

    public function validateRow($row, $rowNumber)
    {
        $errors = [];
        
        // Check required fields based on column mapping
        $requiredFields = [
            0 => 'Membership Type',
            1 => 'Branch Number',
            3 => 'Phone Number',
            4 => 'Address',
            5 => 'Nationality',
            6 => 'Citizenship',
            // 6 => 'Income Available',
            7 => 'Income Source',
            8 => 'Hisa Amount',
            9 => 'Akiba Amount',
            // 10 => 'Amana Amount',
            // 11 => 'Guarantor Member Number',
            // 12 => 'Guarantor Relationship'
        ];

        foreach ($requiredFields as $index => $fieldName) {
            if (empty($row[$index])) {
                $errors[] = $fieldName . ' is required';
            }
        }

        if (!empty($errors)) return $errors;

        // Validate membership type
        if (!in_array(strtolower($row[0]), ['individual', 'group', 'business'])) {
            $errors[] = 'Invalid membership type. Must be Individual, Group, or Business';
        }

        // Validate branch exists
        $branch = BranchesModel::where('branch_number', $row[1])->first();
        // dd($branch);
        if (!$branch) {
            $errors[] = 'Branch not found';
        }

        // Validate phone number format (Tanzanian format)
        if (!preg_match('/^0[0-9]{9,10}$/', $row[3])) {
            $errors[] = 'Invalid phone number format. Must be 0XXXXXXXXX';
        }

        // Validate email if provided
        if (!empty($row[10]) && !filter_var($row[10], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        // Validate numeric fields
        $numericFields = [
            // 6 => 'Income Available',
            8 => 'Hisa Amount',
            9 => 'Akiba Amount',
            // 10 => 'Amana Amount'
        ];

        foreach ($numericFields as $index => $fieldName) {
            if (!is_numeric($row[$index]) || $row[$index] < 0) {
                $errors[] = $fieldName . ' must be a positive number';
            }
        }

        // Validate minimum amounts
        if ($row[8] < 1000) $errors[] = 'Hisa Amount must be at least 1,000 TZS';
        if ($row[9] < 1000) $errors[] = 'Akiba Amount must be at least 1,000 TZS';
        // if ($row[10] < 1000) $errors[] = 'Amana Amount must be at least 1,000 TZS';

        // Validate individual-specific fields
        if (strtolower($row[0]) === 'individual') {
            $individualFields = [
                11 => 'First Name',
                //12 => 'Middle Name',
                13 => 'Last Name',
                14 => 'Gender',
                15 => 'Date of Birth',
                16 => 'Marital Status',
                // 20 => 'Next of Kin Name',
                // 21 => 'Next of Kin Phone'
            ];

            foreach ($individualFields as $index => $fieldName) {
                if (empty($row[$index])) {
                    $errors[] = $fieldName . ' is required for Individual members';
                }
            }

            // Validate gender
            if (!empty($row[14]) && !in_array(strtolower($row[14]), ['male', 'female'])) {
                $errors[] = 'Gender must be Male or Female';
            }

            // Validate marital status
            if (!empty($row[16]) && !in_array(strtolower($row[16]), ['single', 'married', 'divorced', 'widowed'])) {
                $errors[] = 'Marital Status must be Single, Married, Divorced, or Widowed';
            }

            // Validate date of birth
            if (!empty($row[15])) {
                try {
                    $dob = Carbon::parse($row[15]);
                    if ($dob->isFuture()) {
                        $errors[] = 'Date of Birth cannot be in the future';
                    }
                    if ($dob->age < 18) {
                        $errors[] = 'Member must be at least 18 years old';
                    }
                } catch (\Exception $e) {
                    $errors[] = 'Invalid Date of Birth format';
                }
            }

            // Validate next of kin phone
            // if (!empty($row[20]) && !preg_match('/^0[0-9]{9,10}$/', $row[20])) {
            //     $errors[] = 'Next of Kin Phone must be in format 0XXXXXXXXX';
            // }
        }

        // Validate business/group-specific fields
        if (in_array(strtolower($row[0]), ['group', 'business'])) {
            $businessFields = [
                17 => 'Business/Group Name',
                18 => 'Incorporation Number'
            ];

            foreach ($businessFields as $index => $fieldName) {
                if (empty($row[$index])) {
                    $errors[] = $fieldName . ' is required for ' . ucfirst($row[0]) . ' members';
                }
            }
        }

        // Validate guarantor exists and is active
        // if (!empty($row[11])) {
        //     $guarantor = ClientsModel::where('client_number', $row[11])
        //         ->where('status', 'ACTIVE')
        //         ->first();
            
        //     if (!$guarantor) {
        //         $errors[] = 'Guarantor not found or not active';
        //     }
        // }

        return $errors;
    }

    public function processBulkUpload()
    {
        if ($this->errorCount > 0) {
            session()->flash('error', 'Please fix all validation errors before proceeding.');
            return;
        }

        // Reset processing counters
        $this->processedRecords = 0;
        $this->successCount = 0;
        $this->errorCount = 0;
        $this->skippedCount = 0;
        $this->processingResults = [];

        $this->currentStep = 3;
        $this->processingStatus = 'Starting bulk import...';
        $this->uploadProgress = 0;

        try {
            // Read all data from file
            $import = new BulkMembersImport();
            $allData = Excel::toArray($import, $this->uploadFile)[0] ?? [];
            array_shift($allData); // Remove header

            $this->totalRecords = count($allData);
            $this->processingResults = [];

            foreach ($allData as $index => $row) {
                $rowNumber = $index + 2; // Excel row number (accounting for header)
                
                $this->processingStatus = "Processing row {$rowNumber} of {$this->totalRecords}";
                $this->uploadProgress = (($index + 1) / $this->totalRecords) * 100;

                try {
                    $result = $this->processMemberRegistration($row, $rowNumber);
                    $this->processingResults[] = $result;
                    
                    if ($result['status'] === 'success') {
                        $this->successCount++;
                    } else {
                        $this->errorCount++;
                    }
                } catch (\Exception $e) {
                    $this->processingResults[] = [
                        'row' => $rowNumber,
                        'status' => 'error',
                        'message' => 'Processing error: ' . $e->getMessage(),
                        'reference' => '',
                        'member' => $row[14] ?? $row[21] ?? 'Unknown',
                        'membership_type' => $row[0] ?? 'Unknown',
                        'phone' => $row[3] ?? 'Unknown'
                    ];
                    $this->errorCount++;
                }

                $this->processedRecords++;
            }

            $this->currentStep = 4;
            $this->processingStatus = 'Bulk import completed';

        } catch (\Exception $e) {
            Log::error('Bulk import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Bulk import failed: ' . $e->getMessage());
            $this->currentStep = 2;
        }
    }

    public function processMemberRegistration($row, $rowNumber)
    {
        try {
            DB::beginTransaction();

            // Generate member number
            $memberNumberGenerator = new MemberNumberGeneratorService();
            //$clientNumber = $memberNumberGenerator->generate();
            $clientNumber = $row[21];

            $branch = BranchesModel::where('branch_number', $row[1])->first();

            // Prepare client data
            $clientData = [
                'client_number' => $clientNumber,
                'account_number' => $row[22] ?? $clientNumber, // NBC account or generated
                'membership_type' => ucfirst(strtolower($row[0])),
                'branch' => $branch->id,  
                'branch_id' => $branch->id,       
                'phone_number' => $row[3],
                'email' => $row[10] ?? null,
                'address' => $row[4],
                'nationality' => $row[5],
                'citizenship' => $row[6],
                'nida_number' => $row[2] ?? null,
                //'income_available' => $row[6],
                'income_source' => $row[7],
                //'tin_number' => $row[24] ?? null,
                'hisa' => $row[8],
                'akiba' => $row[9],
                // 'amana' => $row[10],
                'status' => 'ACTIVE',
                'created_by' => auth()->id()
            ];

            // Add type-specific data
            if (strtolower($row[0]) === 'individual') {
                $clientData = array_merge($clientData, [
                    'first_name' => strtoupper($row[11]),
                    'middle_name' => strtoupper($row[12] ?? ''),
                    'last_name' => strtoupper($row[13]),
                    'gender' => strtolower($row[14]),
                    'date_of_birth' => $row[15],
                    'marital_status' => strtolower($row[16]),
                    // 'next_of_kin_name' => $row[19]   ,
                    // 'next_of_kin_phone' => $row[20],
                ]);
            } else {
                $clientData = array_merge($clientData, [
                    'business_name' => $row[17],
                    'incorporation_number' => $row[18],
                ]);
            }

            // Create client record
            $client = ClientsModel::create($clientData);

            // Create guarantor record
            if (!empty($row[11])) {
                $guarantorMember = ClientsModel::where('client_number', $row[11])
                    ->where('status', 'ACTIVE')
                    ->first();

                if ($guarantorMember) {
                    DB::table('guarantors')->insert([
                        'client_id' => $client->id,
                        'guarantor_member_id' => $guarantorMember->id,
                        'relationship' => $row[12],
                        'notes' => null,
                        'is_active' => true,
                        'guarantee_start_date' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Create approval request
            // $approvalData = [
            //     'institution_id' => "1",
            //     'process_name' => 'new_member_registration',
            //     'process_description' => auth()->user()->name . ' has requested to register a new member: ' . 
            //         (strtolower($row[0]) === 'individual' ? 
            //             $row[14] . ' ' . $row[15] : 
            //             $row[21]),
            //     'approval_process_description' => 'New member registration approval required',
            //     'process_code' => 'MEMBER_REG',
            //     'process_id' => $client->id,
            //     'process_status' => 'PENDING',
            //     'user_id' => auth()->user()->id,
            //     'team_id' => auth()->user()->current_team_id,
            //     'approver_id' => null,
            //     'approval_status' => 'PENDING',
            //     'edit_package' => null
            // ];

            //$approval = approvals::create($approvalData);

            // Create accounts
            $institution = DB::table('institutions')->where('id', 1)->first();
            $accountService = new AccountCreationService();

            $memberName = strtolower($row[0]) === 'individual' ? 
                $row[11] . ' ' . ($row[12] ?? '') . ' ' . $row[13] : 
                $row[17];

            $sharesAccount = $accountService->createAccount([
                'account_use' => 'external',
                'account_name' => 'MANDATORY SHARES: ' . $memberName,
                'type' => 'capital_accounts',
                'product_number' => '1000',
                'member_number' => $clientNumber,
                'branch_number' => auth()->user()->branch,
                'status' => 'ACTIVE',   
                'status' => 'ACTIVE',             
            ], $institution->mandatory_shares_account);

            $savingsAccount = $accountService->createAccount([
                'account_use' => 'external',
                'account_name' => 'MANDATORY SAVINGS: ' . $memberName,
                'type' => 'liability_accounts',
                'product_number' => '2000',
                'member_number' => $clientNumber,
                'branch_number' => auth()->user()->branch,
                'status' => 'ACTIVE',
                'status' => 'ACTIVE',
            ], $institution->mandatory_savings_account);

            $depositsAccount = $accountService->createAccount([
                'account_use' => 'external',
                'account_name' => 'MANDATORY DEPOSITS: ' . $memberName,
                'type' => 'liability_accounts',
                'product_number' => '3000',
                'member_number' => $clientNumber,
                'branch_number' => auth()->user()->branch,
                'status' => 'ACTIVE',
                'status' => 'ACTIVE',
            ], $institution->mandatory_deposits_account);

            // Generate control numbers and create bills
            $billingService = new BillingService();
            $services = DB::table('services')
                ->whereIn('code', ['REG', 'SHC'])
                ->select('id', 'code', 'name', 'is_recurring', 'payment_mode', 'lower_limit')
                ->get()
                ->keyBy('code');

            $generatedControlNumbers = [];
            foreach (['REG', 'SHC'] as $serviceCode) {
                $service = $services[$serviceCode];
                $controlNumber = $billingService->generateControlNumber(
                    $clientNumber,
                    $service->id,
                    $service->is_recurring,
                    $service->payment_mode
                );

                $generatedControlNumbers[] = [
                    'service_code' => $service->code,
                    'control_number' => $controlNumber,
                    'amount' => $service->lower_limit
                ];

                // Create bill
                $bill = $billingService->createBill(
                    $clientNumber,
                    $service->id,
                    $service->is_recurring,
                    $service->payment_mode,
                    $controlNumber,
                    $service->lower_limit
                );
            }

            DB::commit();

            // Dispatch notifications
            $guarantorMember = null;
            if (!empty($row[11])) {
                $guarantorMember = ClientsModel::where('client_number', $row[11])
                    ->where('status', 'ACTIVE')
                    ->first();
            }

            $institution_id = DB::table('institutions')->where('id', 1)->value('institution_id');
            $saccos = preg_replace('/[^0-9]/', '', $institution_id);

            ProcessMemberNotifications::dispatch($client, $generatedControlNumbers, env('PAYMENT_LINK').'/'.$saccos.'/'.$clientNumber)
                ->onQueue('notifications');

            return [
                'row' => $rowNumber,
                'status' => 'success',
                'message' => 'Member registered successfully',
                'reference' => $clientNumber,
                'member' => strtolower($row[0]) === 'individual' ? $row[11] . ' ' . $row[12] . ' ' . $row[13] : $row[17],
                'membership_type' => ucfirst(strtolower($row[0])),
                'phone' => $row[2],
                'control_numbers' => count($generatedControlNumbers)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Member registration failed in bulk import', [
                'row' => $rowNumber,
                'error' => $e->getMessage(),
                'data' => $row
            ]);

            return [
                'row' => $rowNumber,
                'status' => 'error',
                'message' => $e->getMessage(),
                'reference' => '',
                'member' => strtolower($row[0]) === 'individual' ? $row[11] . ' ' . $row[12] . ' ' . $row[13] : $row[17],
                'membership_type' => ucfirst(strtolower($row[0])),
                'phone' => $row[2],
                'control_numbers' => 0
            ];
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Membership Type*',
            'Branch Number*',
            'NIDA Number',
            'Phone Number*',
            'Address*',
            'Nationality*',
            'Citizenship*',
            //'Income Available*',
            'Income Source*',
            'Hisa Amount*',
            'Akiba Amount*',
            //'Amana Amount*',
            //'Guarantor Member Number*',
            //'Guarantor Relationship*',
            'Email',
            'First Name*',
            'Middle Name',
            'Last Name*',
            'Gender*',
            'Date of Birth*',
            'Marital Status*',
            //'Next of Kin Name*',
            //'Next of Kin Phone*',
            'Business/Group Name*',
            'Incorporation Number*',
            'NBC Account Number',
            'NMB Account Number',
            //'TIN Number',
            
        ];

        $sampleData = [
            'Individual',
            '1',
            'XXXXXXXX-XXXXX-XXXX-XX',
            '0755123456',
            '123 Main Street, Dar es Salaam',
            'Tanzanian',
            'Tanzanian',
            //'500000',
            'Salary',
            '50000',
            '100000',
            //'50000',
            //'00001',
            //'Family',
            'john.doe@email.com',
            'John',
            'Michael',
            'Doe',
            'Male',
            'YYYY-MM-DD',
            'Married',
            //'Jane Doe',
            //'0755123457',
            '',
            '',
            '',
            '',            
        ];

        $filename = 'member_bulk_import_template.xlsx';
        
        return Excel::download(new class($headers, [$sampleData]) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $headers;
            private $sampleData;
            
            public function __construct($headers, $sampleData) {
                $this->headers = $headers;
                $this->sampleData = $sampleData;
            }
            
            public function array(): array {
                return array_merge([$this->headers], $this->sampleData);
            }
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
        $this->currentStep = 1;
        $this->previewData = [];
        $this->selectedRecords = [];
        $this->selectAll = false;
        
        // Clear any validation errors
        $this->resetValidation();
    }

    public function resetValidationState()
    {
        $this->validationErrors = [];
        $this->successCount = 0;
        $this->errorCount = 0;
        $this->skippedCount = 0;
        $this->processedRecords = 0;
        $this->processingResults = [];
        
        // Clear any validation errors
        $this->resetValidation();
    }

    public function goBack()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            
            // Reset validation state when going back to step 2
            if ($this->currentStep === 2) {
                $this->resetValidationState();
                // Re-validate the current preview data
                $this->validatePreviewData();
            }
        }
    }

    public function startNewUpload()
    {
        $this->resetUploadState();
    }
}
