<?php

namespace App\Http\Livewire\Clients;

use App\Models\ClientsModel;

use App\Models\PendingRegistration;
use App\Models\TeamUser;
use App\Models\User;
use App\Services\AccountCreationService;

use Exception;
use Illuminate\Support\Facades\Mail;

use Livewire\Component;
use App\Traits\Livewire\WithModulePermissions;

use App\Models\approvals;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Mail\ControlNumberGenerated;
use App\Models\Member;
use App\Services\MembershipVerificationService;
use App\Services\MemberNumberGeneratorService;
use App\Services\BillingService;
use App\Services\PaymentLinkService;
use App\Notifications\NewMemberWelcomeNotification;
use App\Notifications\GuarantorNotification;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ProcessMemberNotifications;


class Clients extends Component
{
    use WithPagination;
    use WithFileUploads;
    use WithModulePermissions;


    public $membership_type = 'Individual';
    public $branch;
    public $phone_number;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $incorporation_number;
    public $email;
    public $place_of_birth;
    public $marital_status;
    public $onboarding_process;
    public $address;
    public $next_of_kin_name;
    public $next_of_kin_phone;
    public $tin_number;
    public $nationarity;
    public $number_of_spouse;
    public $number_of_children;
    public $gender;
    public $date_of_birth;
    public $id_type;
    public $citizenship;
    public $employer_name;
    public $education;
    public $business_name;
    public $income_available;
    public $hisa;
    public $akiba;
    public $amana;
    public $barua;
    public $uthibitisho;
    public $accept_terms;
    public $application_type;
    public $guarantor_region;
    public $guarantor_ward;
    public $guarantor_district;
    public $guarantor_relationship;
    public $guarantor_membership_number;
    public $guarantor_full_name;
    public $guarantor_email;





    public $tab_id = '1';
    public $title = 'Members list';
    public $selected;
    public $activeClientsCount;
    public $inactiveClientsCount;
    public $showCreateNewMember;
    public $membershipNumber;
    public $parentMember;
    public $showDeleteClient;
    public $clientSelected;
    
    // Member Exit properties
    public $exitMemberNumber;
    public $exitPhoneNumber;
    public $exitMemberDetails;
    public $exitReason;
    public $exitNotes;
    public $exitHistory = [];
    public $showExitConfirmModal = false;
    public $showEditClient;
    public $pendingMember;
    public $MembersList;
    public $pendingClientname;
    public $client;
    public $showAddClient;
    public $Member_status;
    public $permission = 'BLOCKED';
    public $password;

    public $photo;
    public $registering_officer;
    public $supervising_officer;
    public $approving_officer;
    public $membership_number;
    public $registration_date;
    public $street;
    public $notes;
    public $current_team_id;
    public $profile_photo_path;
    public $branch_id;
    public $name;
    public $member;
    public $loan_officer;

    public $confirmingUserDeletion = false;
    public $branches;


    public $sub_product_number_shares ='1199';
    public $sub_product_number_savings='1279';
    public $sub_product_number_deposits='1321';
    public $nida_number;

    public $ref_number;
    public $shares_ref_number;
    public $member_exit_document;

    // vie members modal
    public $viewClientDetails=false;
    public $loanStatus;


    public $account_number;
    public $institution_id;

    public $client_number;


    public $created_at;
    public $updated_at;

    public $end_membership_description;
    public $amount;
    public $national_id;
    public $client_id;
    public $customer_code;
    public $present_surname;
    public $birth_surname;
    public $classification_of_individual;

    public $country_of_birth;
    public $fate_status;
    public $social_status;
    public $residency;
    public $nationality;
    public $monthly_expenses;
    public $negative_status_of_individual;
    public $tax_identification_number;
    public $passport_number;
    public $passport_issuer_country;
    public $driving_license_number;
    public $voters_id;
    public $foreign_unique_id;
    public $custom_id_number_1;
    public $custom_id_number_2;
    public $main_address;
    public $number_of_building;
    public $postal_code;
    public $region;
    public $district;
    public $country;
    public $viewpaid = false;
    public $viewnotpaid = false;
    public $allMembers = true;
    public $mobile_phone;
    public $fixed_line;
    public $web_page;
    public $trade_name;
    public $legal_form;
    public $establishment_date;
    public $registration_country;
    public $industry_sector;
    public $registration_number;
    public $variables;
    public $middle_names;
    public $viewClientLoanData=false;


    public $full_name;

    public $member_number;
    public $member_group_id;
    public $contact_number;
    public $occupation;
    public $education_level;
    public $dependent_count;
    public $annual_income;
    public $city;
    public $status;
    public $member_second_phone_number;
    public $religion;
    public $building_number;
    public $ward;
    public $guarantor_id;
    public $income_source;
    public $guarantor_first_name;
    public $guarantor_middle_name;
    public $guarantor_last_name;
    public $errors;
    public $pendingClient;
    public $photo2;
    public $selectedMemberCategory;
    public $memberCategories = [];

    public $activeTab = 'dashboard';

    public $showGenerateControlNumbers = false;
    public $client_type = 'Individual';
    public $showDeleteMember = false;
    public $generatedControlNumbers = [];
    public $showControlNumbersModal = false;
    public $currentStep = 1;

    public $additionalDocuments = [];

    public $guarantor_member_number;
    public $guarantorVerification = null;
    public $guarantorVerificationMessage = '';
    public $guarantorVerificationStatus = '';

    public $nbc_account_number;

    protected $listeners = [
        'showUsersList' => 'showUsersList',
        'blockClient' => 'blockClientModal',
        'editClient' => 'editClientModal',
        'viewClientDetails'=>'viewClientDetails',
        'viewClientLoans'=>'viewClientLoan',
        'verifyMembership' => 'verifyMembership'
    ];

    protected $rules = [
        // Step 1: Personal Information
        'membership_type' => 'required|in:Individual,Group,Business',
        'branch' => 'required|exists:branches,id',
        'photo' => 'nullable|image|max:10240',
        'member_group_id' => 'nullable|exists:member_groups,id',
        
        // Individual member specific fields
        'first_name' => 'required_if:membership_type,Individual|string|max:100',
        'middle_name' => 'nullable|string|max:100',
        'last_name' => 'required_if:membership_type,Individual|string|max:100',
        'gender' => 'required_if:membership_type,Individual|in:male,female',
        'date_of_birth' => 'required_if:membership_type,Individual|date|before:today',
        'marital_status' => 'required_if:membership_type,Individual|in:single,married,divorced,widowed',
        'nida_number' => 'nullable|string|unique:clients,nida_number',
        
        // Business/Group specific fields
        'business_name' => 'required_if:membership_type,Business,Group|string|max:100',
        'incorporation_number' => 'required_if:membership_type,Business,Group|string|max:50',
        
        // Step 2: Contact Details
        'phone_number' => 'required|string|regex:/^0[0-9]{9,10}$/',
        'email' => 'nullable|email|max:100',
        'address' => 'required|string|max:255',
        'nationality' => 'required|string|max:100',
        'citizenship' => 'required|string|max:100',
        'next_of_kin_name' => 'required_if:membership_type,Individual|string|max:100',
        'next_of_kin_phone' => 'required_if:membership_type,Individual|string|regex:/^0[0-9]{9,10}$/',
        
        // Step 3: Financial Information
        'income_available' => 'required|numeric|min:0|max:999999999.99',
        'income_source' => 'required|string|max:100',
        'nbc_account_number' => 'nullable|string|max:50',
        
        // Step 4: Guarantor & Documents
        'guarantor_member_number' => 'nullable|string|max:100',
        'guarantor_relationship' => 'required_with:guarantor_member_number|string|max:255',
        'additionalDocuments.*.description' => 'required|string|max:255',
        'additionalDocuments.*.file' => 'required|file|max:10240'
    ];

    protected $messages = [
        // Step 1: Personal Information
        'membership_type.required' => 'Please select a membership type',
        'membership_type.in' => 'Invalid membership type selected',
        'member_group_id.exists' => 'Selected member group does not exist',
        'id_type.required' => 'Please select an ID type',
        'id_type.in' => 'Invalid ID type selected',
        'nida_number.required' => 'NIDA number is required',
        'nida_number.regex' => 'Invalid NIDA format. Please enter 20 digits (e.g., 19900115123456789012)',
        'nida_number.unique' => 'Member already exists with this NIDA number',
        'driving_license_number.required' => 'Driving license number is required',
        'driving_license_number.unique' => 'Member already exists with this driving license number',
        'driving_license_number.max' => 'Driving license number cannot exceed 50 characters',
        'branch.required' => 'Please select a branch',
        'branch.exists' => 'Selected branch does not exist',
        'photo.image' => 'The file must be an image',
        'photo.max' => 'The photo must not exceed 10MB',
        
        // Individual member specific messages
        'first_name.required_if' => 'First name is required for individual members',
        'first_name.max' => 'First name cannot exceed 100 characters',
        'middle_name.max' => 'Middle name cannot exceed 100 characters',
        'last_name.required_if' => 'Last name is required for individual members',
        'last_name.max' => 'Last name cannot exceed 100 characters',
        'gender.required_if' => 'Gender is required for individual members',
        'gender.in' => 'Invalid gender selected',
        'date_of_birth.required_if' => 'Date of birth is required for individual members',
        'date_of_birth.date' => 'Invalid date format',
        'date_of_birth.before' => 'Date of birth must be before today',
        'marital_status.required_if' => 'Marital status is required for individual members',
        'marital_status.in' => 'Invalid marital status selected',
        
        // Business/Group specific messages
        'business_name.required_if' => 'Business/Group name is required',
        'business_name.max' => 'Business/Group name cannot exceed 100 characters',
        'incorporation_number.required_if' => 'Incorporation number is required',
        'incorporation_number.max' => 'Incorporation number cannot exceed 50 characters',
        
        // Step 2: Contact Details
        'phone_number.required' => 'Mobile phone number is mandatory',
        'phone_number.regex' => 'Phone number must start with 0 and be 10-11 digits',
        'phone_number.unique' => 'Member already exists with this phone number',
        'email.email' => 'Please enter a valid email address',
        'email.max' => 'Email cannot exceed 100 characters',
        'address.required' => 'Address is required',
        'address.max' => 'Address cannot exceed 255 characters',
        'nationality.required' => 'Nationality is required',
        'nationality.max' => 'Nationality cannot exceed 100 characters',
        'citizenship.required' => 'Citizenship is required',
        'citizenship.max' => 'Citizenship cannot exceed 100 characters',
        'next_of_kin_name.required_if' => 'Next of kin name is required for individual members',
        'next_of_kin_name.max' => 'Next of kin name cannot exceed 100 characters',
        'next_of_kin_phone.required_if' => 'Next of kin phone number is required for individual members',
        'next_of_kin_phone.regex' => 'Next of kin phone number must start with 0 and be 10-11 digits',
        
        // Step 3: Financial Information
        'income_available.required' => 'Income available is required',
        'income_available.numeric' => 'Income available must be a number',
        'income_available.min' => 'Income available must be greater than or equal to 0',
        'income_available.max' => 'Income available cannot exceed 999,999,999.99',
        'income_source.required' => 'Income source is required',
        'income_source.max' => 'Income source cannot exceed 100 characters',
        'nbc_account_number.max' => 'NBC account number cannot exceed 50 characters',
        'nbc_account_number.string' => 'NBC account number must be a string',


        // Step 4: Guarantor & Documents
        'guarantor_member_number.exists' => 'Invalid guarantor membership number or member is not active',
        'guarantor_relationship.required_with' => 'Relationship with guarantor is required when guarantor is provided',
        'guarantor_relationship.max' => 'Relationship description cannot exceed 255 characters',
        'additionalDocuments.*.description.required' => 'Document description is required',
        'additionalDocuments.*.description.max' => 'Document description cannot exceed 255 characters',
        'additionalDocuments.*.file.required' => 'Document file is required',
        'additionalDocuments.*.file.max' => 'Document file must not exceed 10MB'
    ];

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        
        // Initialize with one empty document slot
        $this->additionalDocuments = [
            ['file' => null, 'description' => 'Application Letter']
        ];
        
        // Fetch the categories when the component is mounted
        $this->memberCategories = DB::table('member_categories')->get();
    }
    
    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'clients';
    }


    public function viewPaidClients()
    {
        Log::info('Viewing paid clients', [
            'user_id' => auth()->id()
        ]);

        try {
            $this->resetViewClientsFlags();
            $this->viewpaid = true;

            // Load paid clients data
            $this->MembersList = ClientsModel::where('status', 'ACTIVE')
                ->whereHas('loans', function($query) {
                    $query->where('loan_status', 'PAID');
                })
                ->with(['loans', 'branch'])
                ->get();

        } catch (\Exception $e) {
            Log::error('Error loading paid clients', [
                'error' => $e->getMessage()
            ]);
            session()->flash('message', 'Failed to load paid clients: ' . $e->getMessage());
            session()->flash('alert-class', 'alert-danger');
        }
    }

    public function viewNotPaidClients()
    {
        if (!$this->can('view')) {
            session()->flash('error', 'You do not have permission to view clients');
            return;
        }
        
        Log::info('Viewing unpaid clients', [
            'user_id' => auth()->id()
        ]);

        try {
            $this->resetViewClientsFlags();
            $this->viewnotpaid = true;

            // Load unpaid clients data
            $this->MembersList = ClientsModel::where('status', 'ACTIVE')
                ->whereHas('loans', function($query) {
                    $query->where('loan_status', '!=', 'PAID');
                })
                ->with(['loans', 'branch'])
                ->get();

        } catch (\Exception $e) {
            Log::error('Error loading unpaid clients', [
                'error' => $e->getMessage()
            ]);
            session()->flash('message', 'Failed to load unpaid clients: ' . $e->getMessage());
            session()->flash('alert-class', 'alert-danger');
        }
    }

    public function viewAllClients()
    {
        Log::info('Viewing all clients', [
            'user_id' => auth()->id()
        ]);

        try {
            $this->resetViewClientsFlags();
            $this->allMembers = true;

            // Load all clients data
            $this->MembersList = ClientsModel::with(['loans', 'branch'])
                ->get();

        } catch (\Exception $e) {
            Log::error('Error loading all clients', [
                'error' => $e->getMessage()
            ]);
            session()->flash('message', 'Failed to load clients: ' . $e->getMessage());
            session()->flash('alert-class', 'alert-danger');
        }
    }

    public function viewClientLoan($client_number)
    {
        Log::info('Viewing client loans', [
            'user_id' => auth()->id(),
            'client_number' => $client_number
        ]);

        try {
            $this->resetClientLoanFlags();
            $this->viewClientDetails = true;
            $this->viewClientLoanData = true;

            // Load detailed loan data
            $this->loanStatus = DB::table('loans')
                ->where('client_number', $client_number)
                ->select([
                    'id',
                    'loan_number',
                    'loan_amount',
                    'loan_status',
                    'disbursement_date',
                    'due_date',
                    'amount_paid',
                    'remaining_amount',
                    'created_at',
                    'updated_at'
                ])
                ->get();

            // Store in session for persistence
            session()->put('viewMemberLoan', $client_number);

        } catch (\Exception $e) {
            Log::error('Error loading client loans', [
                'error' => $e->getMessage(),
                'client_number' => $client_number
            ]);
            session()->flash('message', 'Failed to load client loans: ' . $e->getMessage());
            session()->flash('alert-class', 'alert-danger');
        }
    }

    public function viewClientDetails($id)
    {
        Log::info('Viewing client details', [
            'user_id' => auth()->id(),
            'client_id' => $id
        ]);

        try {
            $client = ClientsModel::with(['loans', 'branch', 'savings', 'shares'])
                ->findOrFail($id);

            // Store client ID in session
            session()->put('viewClientId', $id);

            // Get client number and load loan status
            $client_number = $client->client_number;
            $this->loanStatus = DB::table('loans')
                ->where('client_number', $client_number)
                ->select([
                    'id',
                    'loan_number',
                    'loan_amount',
                    'loan_status',
                    'disbursement_date',
                    'due_date',
                    'amount_paid',
                    'remaining_amount'
                ])
                ->get();

            // Load client data into component properties
            $this->loadClientData($client);

            $this->toggleViewClientDetails();

        } catch (\Exception $e) {
            Log::error('Error loading client details', [
                'error' => $e->getMessage(),
                'client_id' => $id
            ]);
            session()->flash('message', 'Failed to load client details: ' . $e->getMessage());
            session()->flash('alert-class', 'alert-danger');
        }
    }

    protected function loadClientData($client)
    {
        $this->client = $client->id;
        $this->membership_type = $client->membership_type;
        $this->branch = $client->branch;
        $this->phone_number = $client->phone_number;
        $this->first_name = $client->first_name;
        $this->middle_name = $client->middle_name;
        $this->last_name = $client->last_name;
        $this->place_of_birth = $client->place_of_birth;
        $this->marital_status = $client->marital_status;
        $this->address = $client->address;
        $this->next_of_kin_name = $client->next_of_kin_name;
        $this->next_of_kin_phone = $client->next_of_kin_phone;
        $this->nationarity = $client->nationarity;
        $this->number_of_spouse = $client->number_of_spouse;
        $this->number_of_children = $client->number_of_children;
        $this->gender = $client->gender;
        $this->date_of_birth = $client->date_of_birth;
        $this->citizenship = $client->citizenship;
        $this->nida_number = $client->nida_number;
        $this->status = $client->status;
        $this->email = $client->email;
        $this->client_number = $client->client_number;
    }

    protected function resetViewClientsFlags() {
        $this->viewpaid = false;
        $this->viewnotpaid = false;
        $this->allMembers = false;
    }

    protected function resetClientLoanFlags() {
        $this->viewClientDetails = false;
        $this->viewClientLoanData = false;
    }

    protected function toggleViewClientDetails() {
        $this->viewClientDetails = !$this->viewClientDetails;
    }







    public function showAddClientModal($tabId)
    {
        if ($tabId === 3) {
            $this->showGenerateControlNumbers = true;
            return;
        }
        $randomNumber = rand(9000, 9999);
        $this->membershipNumber = str_pad($randomNumber, 4, '0', STR_PAD_LEFT);
        $this->selected = $tabId;
        $this->showAddClient = true;
        
        // Initialize additionalDocuments with a default document
        $this->additionalDocuments = [
            ['file' => null, 'description' => 'Application Letter']
        ];
    }




    public function updatedMember()
    {
        $memberData = ClientsModel::findOrFail($this->client); // Use findOrFail for better error handling
        $this->fill($memberData->toArray()); // Fill Livewire properties with retrieved model data
    }

    protected function membershipTypeRules()
    {
        // Define the validation rules based on membership type
        if ($this->membership_type === 'Individual') {
            return $this->individual;
        } else {
            return $this->business;
        }
    }

    public function updateClient()
    {
        if (!$this->authorize('edit', 'You do not have permission to edit clients')) {
            return;
        }
        
        Log::info('Attempting to update client', [
            'user_id' => auth()->id(),
            'client_id' => $this->client
        ]);

        try {
            DB::beginTransaction();

            $client = ClientsModel::findOrFail($this->client);

            // Check for duplicates (excluding current client)
            $duplicateCheck = $this->checkForDuplicatesOnUpdate($this->client);
            if ($duplicateCheck['isDuplicate']) {
                throw new \Exception($duplicateCheck['message']);
            }

            // Validate based on membership type
            $this->validate($this->membershipTypeRules());

            // Prepare update data
            $updateData = [
                'membership_type' => $this->membership_type,
                'branch' => $this->branch,
                'phone_number' => $this->phone_number,
                'first_name' => strtoupper($this->first_name),
                'middle_name' => strtoupper($this->middle_name),
                'last_name' => strtoupper($this->last_name),
                'place_of_birth' => $this->place_of_birth,
                'marital_status' => $this->marital_status,
                'address' => $this->address,
                'next_of_kin_name' => $this->next_of_kin_name,
                'next_of_kin_phone' => $this->next_of_kin_phone,
                'nationarity' => $this->nationarity,
                'number_of_spouse' => $this->number_of_spouse,
                'number_of_children' => $this->number_of_children,
                'gender' => $this->gender,
                'date_of_birth' => $this->date_of_birth,
                'citizenship' => $this->citizenship,
                'id_type' => $this->id_type,
                'nida_number' => $this->id_type === 'nida' ? $this->nida_number : null,
                'driving_license_number' => $this->id_type === 'driving_license' ? $this->driving_license_number : null,
                'updated_by' => auth()->id()
            ];

            // Create approval record
            approvals::create([
                'institution' => $client->id,
                'process_name' => 'editClient',
                'process_description' => auth()->user()->name . ' has requested to edit client: ' . $client->first_name . ' ' . $client->last_name,
                'approval_process_description' => 'has approved client information update',
                'process_code' => '03',
                'process_id' => $client->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'team_id' => 1,
                'approver_id' => 1,
                'approval_status' => 'PENDING',
                'edit_package' => json_encode($updateData)
            ]);

            DB::commit();

            Log::info('Client update submitted successfully', [
                'client_id' => $client->id,
                'client_number' => $client->client_number
            ]);

            session()->flash('message', 'Client update submitted for approval');
            session()->flash('alert-class', 'alert-success');

            $this->resetData();
            $this->closeModal();
            $this->emit('refreshClientsList');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating client', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('message', 'Failed to update client: ' . $e->getMessage());
            session()->flash('alert-class', 'alert-danger');
        }
    }

    public function addClient()
    {
        Log::info('Attempting to add new client', [
            'user_id' => auth()->id(),
            'client_type' => $this->membership_type
        ]);

        try {
            DB::beginTransaction();

            // Check for duplicates before validation
            $duplicateCheck = $this->checkForDuplicates();
            if ($duplicateCheck['isDuplicate']) {
                throw new \Exception($duplicateCheck['message']);
            }

            // Validate based on membership type
            $this->validate($this->membershipTypeRules());

            // Generate client number
            $latestClient = ClientsModel::latest()->first();
            $this->client_number = $latestClient ? $latestClient->client_number + 1 : 1001;

            // Generate account number (initially client number as base as base)
            //now if nbc account number is provided, use it as base
            $this->account_number = $this->nbc_account_number ?? $this->client_number;

            // Prepare client data
            $clientData = [
                'client_number' => $this->client_number,
                'account_number' => $this->account_number,
                'membership_type' => $this->membership_type,
                'branch' => $this->branch,
                'member_group_id' => $this->member_group_id,
                'phone_number' => $this->phone_number,
                'first_name' => strtoupper($this->first_name),
                'middle_name' => strtoupper($this->middle_name),
                'last_name' => strtoupper($this->last_name),
                'place_of_birth' => $this->place_of_birth,
                'marital_status' => $this->marital_status,
                'address' => $this->address,
                'next_of_kin_name' => $this->next_of_kin_name,
                'next_of_kin_phone' => $this->next_of_kin_phone,
                'nationarity' => $this->nationarity,
                'number_of_spouse' => $this->number_of_spouse,
                'number_of_children' => $this->number_of_children,
                'gender' => $this->gender,
                'date_of_birth' => $this->date_of_birth,
                'citizenship' => $this->citizenship,
                'id_type' => $this->id_type,
                'nida_number' => $this->id_type === 'nida' ? $this->nida_number : null,
                'driving_license_number' => $this->id_type === 'driving_license' ? $this->driving_license_number : null,
                'status' => 'PENDING',
                'created_by' => auth()->id(),
                'branch_id' => $this->branch
            ];

            // Create client record
            $client = ClientsModel::create($clientData);

            // Create approval record
            approvals::create([
                'institution' => $client->id,
                'process_name' => 'addClient',
                'process_description' => auth()->user()->name . ' has requested to add new client: ' . $client->first_name . ' ' . $client->last_name,
                'approval_process_description' => 'has approved new client registration',
                'process_code' => '03',
                'process_id' => $client->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'team_id' => 1,
                'approver_id' => 1,
                'approval_status' => 'PENDING',
                'edit_package' => json_encode($clientData)
            ]);

            DB::commit();

            Log::info('Client registration submitted successfully', [
                'client_id' => $client->id,
                'client_number' => $client->client_number
            ]);

            session()->flash('message', 'Client registration submitted for approval');
            session()->flash('alert-class', 'alert-success');

            $this->resetData();
            $this->closeModal();
            $this->emit('refreshClientsList');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding client', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('message', 'Failed to add client: ' . $e->getMessage());
            session()->flash('alert-class', 'alert-danger');
        }
    }

    public function delete()
    {
        if (!$this->authorize('delete', 'You do not have permission to delete clients')) {
            return;
        }
        
        Log::info('Attempting to delete/change client status', [
            'user_id' => auth()->id(),
            'client_id' => $this->clientSelected,
            'action' => $this->permission
        ]);

        try {
            DB::beginTransaction();

            $client = ClientsModel::findOrFail($this->clientSelected);

            // Check for dependencies
            $dependencies = [
                'loans' => DB::table('loans')->where('client_number', $client->client_number)->exists(),
                'savings' => DB::table('savings')->where('client_number', $client->client_number)->exists(),
                'shares' => false // Removed shares table reference
            ];

            $hasDependencies = collect($dependencies)->contains(true);

            if ($hasDependencies) {
                $dependencyList = collect($dependencies)
                    ->filter()
                    ->keys()
                    ->implode(', ');
                throw new \Exception("Cannot delete client: Has associated records in: {$dependencyList}");
            }

            $action = '';
            $description = '';

            switch ($this->permission) {
                case 'BLOCKED':
                    $action = 'blockClient';
                    $description = 'Block client';
                    break;
                case 'ACTIVE':
                    $action = 'activateClient';
                    $description = 'Activate client';
                    break;
                case 'DELETED':
                    $action = 'deleteClient';
                    $description = 'Delete client';
                    break;
                default:
                    throw new \Exception('Invalid permission status');
            }

            // Create approval record
            approvals::create([
                'institution' => $client->id,
                'process_name' => $action,
                'process_description' => auth()->user()->name . ' has requested to ' . strtolower($description) . ': ' . $client->first_name . ' ' . $client->last_name,
                'approval_process_description' => 'has approved ' . strtolower($description) . ' for client: ' . $client->first_name . ' ' . $client->last_name,
                'process_code' => '03',
                'process_id' => $client->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'team_id' => 1,
                'approver_id' => 1,
                'approval_status' => 'PENDING',
                'edit_package' => json_encode([
                    'client_id' => $client->id,
                    'client_number' => $client->client_number,
                    'client_name' => $client->first_name . ' ' . $client->last_name,
                    'current_status' => $client->status,
                    'requested_status' => $this->permission,
                    'action' => $action,
                    'requested_by' => auth()->user()->name,
                    'requested_at' => now()->toDateTimeString()
                ])
            ]);

            DB::commit();

            Log::info('Client status change submitted successfully', [
                'client_id' => $client->id,
                'action' => $action
            ]);

            session()->flash('message', 'Client status change submitted for approval');
            session()->flash('alert-class', 'alert-success');

            $this->closeModal();
            $this->emit('refreshClientsList');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error changing client status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('message', 'Failed to change client status: ' . $e->getMessage());
            session()->flash('alert-class', 'alert-danger');
        }
    }

    public function blockClientModal($id)
    {
        Log::info('Opening block client modal', [
            'user_id' => auth()->id(),
            'client_id' => $id
        ]);

        $this->clientSelected = $id;
        $this->showDeleteClient = true;
    }

    public function editClientModal($id)
    {
        Log::info('Opening edit client modal', [
            'user_id' => auth()->id(),
            'client_id' => $id
        ]);

        try {
            $client = ClientsModel::findOrFail($id);

            $this->client = $id;
            $this->membership_type = $client->membership_type;
            $this->branch = $client->branch;
            $this->phone_number = $client->phone_number;
            $this->first_name = $client->first_name;
            $this->middle_name = $client->middle_name;
            $this->last_name = $client->last_name;
            $this->place_of_birth = $client->place_of_birth;
            $this->marital_status = $client->marital_status;
            $this->address = $client->address;
            $this->next_of_kin_name = $client->next_of_kin_name;
            $this->next_of_kin_phone = $client->next_of_kin_phone;
            $this->nationarity = $client->nationarity;
            $this->number_of_spouse = $client->number_of_spouse;
            $this->number_of_children = $client->number_of_children;
            $this->gender = $client->gender;
            $this->date_of_birth = $client->date_of_birth;
            $this->citizenship = $client->citizenship;
            $this->nida_number = $client->nida_number;

            $this->showEditClient = true;

        } catch (\Exception $e) {
            Log::error('Error loading client data for edit', [
                'error' => $e->getMessage(),
                'client_id' => $id
            ]);
            session()->flash('message', 'Failed to load client data: ' . $e->getMessage());
            session()->flash('alert-class', 'alert-danger');
        }
    }

    public function generateControlNumber()
    {
        Log::info('Generating control number', [
            'user_id' => auth()->id()
        ]);

        try {
            $controlNumber = strtoupper(uniqid('CN'));

            // Store control number
            $this->generatedControlNumbers[] = [
                'number' => $controlNumber,
                'generated_at' => now()->toDateTimeString()
            ];

            // Create member object for notification
            $member = (object)[
                'name' => $this->first_name . ' ' . $this->last_name,
                'email' => $this->email,
                'phone_number' => $this->phone_number
            ];

            // Send notifications
            if ($this->phone_number) {
                $this->sendSMS($this->phone_number, "Welcome to MFI Management System! Your control number is: {$controlNumber}. Please make payment to complete your registration.");
            }

            if ($this->email) {
                // Get account information from session
                $sharesAccount = session()->get('saved_credit_account');
                
                // Send welcome notification with control number and account information
                $member->notify(new NewMemberWelcomeNotification(
                    $member, 
                    $controlNumber,
                    $sharesAccount,
                    $savingsAccount ?? null,
                    $depositsAccount ?? null
                ));
            }

            $this->showControlNumbersModal = true;

        } catch (\Exception $e) {
            Log::error('Error generating control number', [
                'error' => $e->getMessage()
            ]);
            session()->flash('message', 'Failed to generate control number: ' . $e->getMessage());
            session()->flash('alert-class', 'alert-danger');
        }
    }

    private function sendSMS($phoneNumber, $message)
    {
        try {
            // Implement your SMS sending logic here
            // Example using a hypothetical SMS service:
            $response = Http::post('your-sms-service-url', [
                'phone' => $phoneNumber,
                'message' => $message
            ]);

            if (!$response->successful()) {
                throw new \Exception('SMS sending failed: ' . $response->body());
            }

            Log::info('SMS sent successfully', [
                'phone' => $phoneNumber
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending SMS', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber
            ]);
            throw $e;
        }
    }

    private function sendEmail($email, $subject, $message)
    {
        try {
            Mail::to($email)->send(new ControlNumberGenerated($subject, $message));

            Log::info('Email sent successfully', [
                'email' => $email
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending email', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
            throw $e;
        }
    }

    public function closeControlNumbersModal()
    {
        $this->showControlNumbersModal = false;
        $this->generatedControlNumbers = [];
        $this->client_type = 'Individual';
        $this->first_name = null;
        $this->middle_name = null;
        $this->last_name = null;
        $this->full_name = null;
        $this->phone_number = null;
        $this->email = null;
    }

    public function nextStep()
    {
        Log::info('Starting nextStep', [
            'current_step' => $this->currentStep,
            'membership_type' => $this->membership_type,
            'user_id' => auth()->id()
        ]);

        try {
            Log::info('Validating current step', ['step' => $this->currentStep]);
            $this->validateStep();
            
            if ($this->currentStep === 4) {
                Log::info('Generating member number for step 4');
                // Generate member number first
                $memberNumberGenerator = new MemberNumberGeneratorService();
                $this->client_number = $memberNumberGenerator->generate();
                Log::info('Member number generated', ['client_number' => $this->client_number]);
                
                // Then generate control numbers
                Log::info('Generating control numbers');
                $this->generateControlNumbers();
                Log::info('Control numbers generated', ['count' => count($this->generatedControlNumbers)]);
                
                session()->flash('success', 'Member number and control numbers generated successfully!');
            }
            
            $this->currentStep++;
            Log::info('Moving to next step', ['new_step' => $this->currentStep]);
            
            // Scroll to top of form
            $this->dispatchBrowserEvent('scrollToTop');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in nextStep', [
                'step' => $this->currentStep,
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            session()->flash('error', 'Please check the form for errors: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error in nextStep', [
                'step' => $this->currentStep,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
            throw $e;
        }
    }

    public function previousStep()
    {
        Log::info('Moving to previous step', [
            'current_step' => $this->currentStep,
            'new_step' => $this->currentStep - 1,
            'user_id' => auth()->id()
        ]);

        $this->currentStep--;
        
        // Scroll to top of form
        $this->dispatchBrowserEvent('scrollToTop');
    }

    /**
     * Validates the current step of the multi-step form based on the current step number.
     * Each step has its own set of validation rules for different form fields.
     *
     * @return void
     */
    public function validateStep()
    {
        Log::info('Starting step validation', [
            'step' => $this->currentStep,
            'membership_type' => $this->membership_type
        ]);

        $rules = [];
        $messages = [];

        switch ($this->currentStep) {
            case 1:
                Log::info('Validating step 1 - Personal Information');
                $rules = [
                    'membership_type' => 'required|in:Individual,Group,Business',
                    'branch' => 'required|exists:branches,id',
                ];

                if ($this->membership_type === 'Individual') {
                    Log::info('Adding individual member validation rules');
                    $rules = array_merge($rules, [
                        'first_name' => 'required|string|max:100',
                        'middle_name' => 'nullable|string|max:100',
                        'last_name' => 'required|string|max:100',
                        'gender' => 'required|in:male,female',
                        'date_of_birth' => 'required|date|before:today',
                        'id_type' => 'required|in:nida,driving_license',
                        'marital_status' => 'required|in:single,married,divorced,widowed',
                    ]);
                    
                    // Add conditional validation based on ID type
                    if ($this->id_type === 'nida') {
                        $rules['nida_number'] = 'required|string|regex:/^[0-9]{20}$/|unique:clients,nida_number';
                    } elseif ($this->id_type === 'driving_license') {
                        $rules['driving_license_number'] = 'required|string|max:50|unique:clients,driving_license_number';
                    }
                } else {
                    Log::info('Adding business/group validation rules');
                    $rules = array_merge($rules, [
                        'business_name' => 'required|string|max:100',
                        'incorporation_number' => 'required|string|max:50',
                    ]);
                }
                break;

            case 2:
                Log::info('Validating step 2 - Contact Details');
                $rules = [
                    'phone_number' => 'required|string|regex:/^0[0-9]{9,10}$/',
                    'email' => 'nullable|email|max:100',
                    'address' => 'required|string|max:255',
                    'nationality' => 'required|string|max:100',
                    'citizenship' => 'required|string|max:100',
                ];

                if ($this->membership_type === 'Individual') {
                    Log::info('Adding individual member contact validation rules');
                    $rules = array_merge($rules, [
                        'next_of_kin_name' => 'required|string|max:100',
                        'next_of_kin_phone' => 'required|string|regex:/^0[0-9]{9,10}$/',
                    ]);
                }
                break;

            case 3:
                Log::info('Validating step 3 - Financial Information');
                $rules = [
                    'income_available' => 'required|numeric|min:0|max:999999999.99',
                    'income_source' => 'required|string|max:100',
                    'nbc_account_number' => 'nullable|string|max:50',
                ];
                break;

            case 4:
                Log::info('Validating step 4 - Documents and Guarantor (Optional)');
                
                // Initialize rules array for Step 4
                $rules = [];
                
                // Make photo optional
                if ($this->photo) {
                    Log::info('Validating photo upload');
                    $rules['photo'] = 'nullable|image|max:10240';
                }

                // Make guarantor information optional
                if ($this->guarantor_member_number) {
                    Log::info('Validating guarantor information', [
                        'guarantor_number' => $this->guarantor_member_number
                    ]);

                    // Only validate if guarantor number is provided
                    $rules['guarantor_member_number'] = 'nullable|exists:clients,client_number,status,ACTIVE';
                    $rules['guarantor_relationship'] = 'required_with:guarantor_member_number|string|max:255';
                    
                    // Check if guarantor exists and is active
                    $guarantor = ClientsModel::where('client_number', $this->guarantor_member_number)
                        ->where('status', 'ACTIVE')
                        ->first();

                    Log::info('Guarantor validation check', [
                        'exists' => (bool)$guarantor,
                        'status' => $guarantor ? $guarantor->status : null
                    ]);
                } else {
                    // No guarantor provided - that's okay, it's optional
                    Log::info('No guarantor provided - proceeding without guarantor');
                }

                // Documents are also optional now
                Log::info('Checking document uploads (optional)', [
                    'document_count' => count($this->additionalDocuments),
                    'has_first_document' => isset($this->additionalDocuments[0]['file'])
                ]);
                // No longer throw exception if no documents
                break;
                
            default:
                Log::info('Unknown step - no validation', ['step' => $this->currentStep]);
                $rules = [];
                break;
        }

        Log::info('Validation rules prepared', [
            'step' => $this->currentStep,
            'rule_count' => count($rules)
        ]);

        // Only validate if there are rules to validate
        if (!empty($rules)) {
            try {
                $this->validate($rules, $this->messages);
                Log::info('Step validation successful', ['step' => $this->currentStep]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('Validation failed', [
                    'step' => $this->currentStep,
                    'errors' => $e->errors(),
                    'message' => $e->getMessage()
                ]);
                session()->flash('error', 'Please check the form for errors.');
                throw $e;
            }
        } else {
            Log::info('No validation rules for step - proceeding', ['step' => $this->currentStep]);
        }
    }

    public function resetForm()
    {
        $this->reset([
            'membership_type', 'branch', 'phone_number', 'email', 'address', 'nationarity',
            'citizenship', 'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth',
            'id_type', 'nida_number', 'driving_license_number',
            'marital_status', 'next_of_kin_name', 'next_of_kin_phone', 'business_name',
            'incorporation_number', 'income_available', 'income_source', 'nbc_account_number',
            'photo', 'guarantor_first_name',
            'guarantor_middle_name', 'guarantor_last_name', 'guarantor_full_name',
            'guarantor_email'
        ]);
        
        // Initialize additionalDocuments with a default document
        $this->additionalDocuments = [
            ['file' => null, 'description' => 'Application Letter']
        ];
        
        $this->currentStep = 1;
    }

    public function updatedMembershipType($value)
    {
        // Reset fields when membership type changes
        if ($value === 'Individual') {
            $this->reset(['business_name', 'incorporation_number']);
        } else {
            $this->reset(['first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth', 'marital_status']);
        }
    }

    public function save()
    {
        if (!$this->authorize('create', 'You do not have permission to create clients')) {
            return;
        }
        
        Log::info('Starting member registration process', [
            'user_id' => auth()->id(),
            'membership_type' => $this->membership_type,
            'current_step' => $this->currentStep,
            'guarantor_number' => $this->guarantor_member_number
        ]);

        try {
            DB::beginTransaction();
            Log::info('Database transaction started');
            
            // Generate client number using the service
            Log::info('Generating member number');
            $memberNumberGenerator = new MemberNumberGeneratorService();
            $this->client_number = $memberNumberGenerator->generate();
            Log::info('Member number generated', ['client_number' => $this->client_number]);
            
            // Generate account number (initially client number as base as base)
            //now if nbc account number is provided, use it as base
            $this->account_number = $this->nbc_account_number ?? $this->client_number;
            
            // Prepare client data
            Log::info('Preparing client data', ['membership_type' => $this->membership_type]);
            $clientData = [
                'client_number' => $this->client_number,
                'account_number' => $this->nbc_account_number ?: $this->account_number,
                'membership_type' => $this->membership_type,
                'branch' => $this->branch,
                'phone_number' => $this->phone_number,
                'email' => $this->email,
                'address' => $this->address,
                'nationarity' => $this->nationarity,
                'citizenship' => $this->citizenship,
                'income_available' => $this->income_available,
                'income_source' => $this->income_source,
                'status' => 'PENDING',
                'branch_id' => $this->branch,
                'created_by' => auth()->id()
            ];
            
            // Add type-specific data
            if ($this->membership_type === 'Individual') {
                Log::info('Adding individual member specific data');
                $clientData = array_merge($clientData, [
                    'first_name' => strtoupper($this->first_name),
                    'middle_name' => strtoupper($this->middle_name),
                    'last_name' => strtoupper($this->last_name),
                    'gender' => $this->gender,
                    'date_of_birth' => $this->date_of_birth,
                    'id_type' => $this->id_type,
                    'nida_number' => $this->id_type === 'nida' ? $this->nida_number : null,
                    'driving_license_number' => $this->id_type === 'driving_license' ? $this->driving_license_number : null,
                    'marital_status' => $this->marital_status,
                    'next_of_kin_name' => $this->next_of_kin_name,
                    'next_of_kin_phone' => $this->next_of_kin_phone,
                ]);
            } else {
                Log::info('Adding business/group specific data');
                $clientData = array_merge($clientData, [
                    'business_name' => $this->business_name,
                    'incorporation_number' => $this->incorporation_number,
                ]);
            }
            
            // Create client record
            Log::info('Creating client record');
            $client = ClientsModel::create($clientData);
            Log::info('Client record created', ['client_id' => $client->id]);
            
            // Handle photo upload if exists
            if ($this->photo) {
                Log::info('Processing profile photo upload');
                $photoPath = $this->photo->store('profile-photos', 'public');
                DB::table('client_documents')->insert([
                    'client_id' => $client->id,
                    'document_type' => 'profile_photo',
                    'file_path' => $photoPath,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                Log::info('Profile photo uploaded', ['photo_path' => $photoPath]);
            }
            
            // Handle all documents
            Log::info('Processing additional documents', ['document_count' => count($this->additionalDocuments)]);
            foreach ($this->additionalDocuments as $index => $document) {
                if ($document['file']) {
                    Log::info('Processing document', ['index' => $index, 'description' => $document['description']]);
                    $filePath = $document['file']->store('client-documents', 'public');
                    DB::table('client_documents')->insert([
                        'client_id' => $client->id,
                        'document_type' => strtolower($document['description']),
                        'file_path' => $filePath,
                        'description' => $document['description'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    Log::info('Document uploaded', ['file_path' => $filePath]);
                }
            }
            
            // Create guarantor record if exists
            $guarantorMember = null;
            if ($this->guarantor_member_number) {
                Log::info('Processing guarantor information', [
                    'guarantor_number' => $this->guarantor_member_number
                ]);

                $guarantorMember = ClientsModel::where('client_number', $this->guarantor_member_number)
                    ->where('status', 'ACTIVE')
                    ->first();

                Log::info('Guarantor lookup result', [
                    'found' => (bool)$guarantorMember,
                    'status' => $guarantorMember ? $guarantorMember->status : null
                ]);

                if (!$guarantorMember) {
                    Log::error('Invalid guarantor membership number', [
                        'guarantor_number' => $this->guarantor_member_number,
                        'error' => 'Guarantor not found or not active'
                    ]);
                    throw new \Exception('Invalid guarantor membership number. Please provide a valid active member number.');
                }
                
                DB::table('guarantors')->insert([
                    'client_id' => $client->id,
                    'guarantor_member_id' => $guarantorMember->id,
                    'relationship' => $this->guarantor_relationship,
                    'notes' => null,
                    'is_active' => true,
                    'guarantee_start_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Log::info('Guarantor record created', ['guarantor_id' => $guarantorMember->id]);
            }
            
            // Create approval request
            Log::info('Creating approval request');
            $approvalData = [
             
                'process_name' => 'new_member_registration',
                'process_description' => auth()->user()->name . ' has requested to register a new member: ' . 
                    ($this->membership_type === 'Individual' ? 
                        $this->first_name . ' ' . $this->last_name : 
                        $this->business_name),
                'approval_process_description' => 'New member registration approval required',
                'process_code' => 'MEMBER_REG',
                'process_id' => $client->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'team_id' => auth()->user()->current_team_id,
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => null
            ];
            
            $approval = approvals::create($approvalData);
            Log::info('Approval request created', ['approval_id' => $approval->id]);

            $institution = DB::table('institutions')->where('id',1)->first();


            $accountService = new AccountCreationService();

      

            $mandatorySharesAccount = $institution->mandatory_shares_account;
            $mandatorySavingsAccount = $institution->mandatory_savings_account;
            $mandatoryDepositsAccount = $institution->mandatory_deposits_account;

            //dd($this->client_number );

            $account_name = $this->membership_type === 'Individual' ?  $this->first_name . ' ' . $this->last_name : $this->business_name;


            $sharesAccount = $accountService->createAccount([
                'account_use' => 'external',
                'account_name' => 'MANDATORY SHARES: '.$this->first_name.' '.$this->middle_name.' '.$this->last_name,
                'type' => 'capital_accounts',
                'product_number' => '1000',
                'member_number' => $this->client_number,
                'branch_number' => auth()->user()->branch ?? '1'
            ], $mandatorySharesAccount);

            // Log before storing in session
            Log::info('Storing shares account in session', [
                'account_number' => $sharesAccount->account_number,
                'account_name' => $sharesAccount->account_name,
                'type' => $sharesAccount->type,
                'user_id' => auth()->id()
            ]);

            // Store shares account in session
            session()->put('saved_credit_account', $sharesAccount);

            // Log after storing in session
            Log::info('Shares account stored in session successfully', [
                'account_number' => $sharesAccount->account_number,
                'session_key' => 'saved_credit_account',
                'user_id' => auth()->id()
            ]);

            $savingsAccount = $accountService->createAccount([
                'account_use' => 'external',
                'account_name' => 'MANDATORY SAVINGS: '.$this->first_name.' '.$this->middle_name.' '.$this->last_name,
                'type' => 'liability_accounts',
                'product_number' => '2000',
                'member_number' => $this->client_number,
                'branch_number' => auth()->user()->branch ?? '1'
            ], $mandatorySavingsAccount);

            $depositsAccount = $accountService->createAccount([
                'account_use' => 'external',
                'account_name' => 'MANDATORY DEPOSITS: '.$this->first_name.' '.$this->middle_name.' '.$this->last_name,
                'type' => 'liability_accounts',
                'product_number' => '3000',
                'member_number' => $this->client_number,
                'branch_number' => auth()->user()->branch ?? '1'
            ], $mandatoryDepositsAccount);
            
            // Create bills for each service
            Log::info('Creating service bills', ['control_numbers_count' => count($this->generatedControlNumbers)]);
            foreach ($this->generatedControlNumbers as $control) {
                Log::info('Processing service bill', ['service_code' => $control['service_code']]);
                $service = DB::table('services')
                    ->where('code', $control['service_code'])
                    ->first();

                if ($service) {
                    $billingService = new BillingService();
                    $bill = $billingService->createBill(
                        $this->client_number,
                        $service->id,
                        $service->is_recurring,
                        $service->payment_mode,
                        $control['control_number'],
                        $service->lower_limit
                    );

                    Log::info('Service bill created', [
                        'service_id' => $service->id,
                        'control_number' => $control['control_number']
                    ]);
                } else {
                    Log::warning('Service not found', ['service_code' => $control['service_code']]);
                }
            }
            
            DB::commit();
            Log::info('Database transaction committed successfully');

            // Dispatch background job for notifications
            Log::info('Dispatching member notifications', [
                'client' => [
                    'id' => $client->id,
                    'name' => $client->first_name . ' ' . $client->last_name,
                    'email' => $client->email,
                    'phone' => $client->phone_number
                ],
                'guarantor' => $guarantorMember ? [
                    'id' => $guarantorMember->id,
                    'name' => $guarantorMember->first_name . ' ' . $guarantorMember->last_name,
                    'email' => $guarantorMember->email,
                    'phone' => $guarantorMember->phone_number
                ] : null,
                'control_numbers' => $this->generatedControlNumbers
            ]);

            $institution_id = DB::table('institutions')->where('id',1)->value('institution_id');
            $saccos = preg_replace('/[^0-9]/', '', $institution_id); // Remove non-numeric characters



            ////////////////LINK GENERATION////////////////////
            
            // Generate payment link for member registration fees
            try {
                Log::info('Generating payment link for member registration', [
                    'client_number' => $this->client_number,
                    'member_name' => $account_name
                ]);
                
                $paymentService = new PaymentLinkService();
                
                // Get all bills for this client
                $bills = DB::table('bills')
                    ->join('services', 'bills.service_id', '=', 'services.id')
                    ->where('bills.client_number', $this->client_number)
                    ->where('bills.status', '!=', 'PAID') // Only unpaid bills
                    ->whereNotNull('bills.id') // Ensure bill_id exists
                    ->select(
                        'bills.id as bill_id',
                        'bills.control_number',
                        'bills.amount_due as bill_amount',
                        'bills.payment_mode',
                        'services.code as service_code',
                        'services.name as service_name'
                    )
                    ->get();
                
                // Check if bills exist
                if ($bills->isEmpty()) {
                    Log::warning('No bills found for client', [
                        'client_number' => $this->client_number
                    ]);
                    $paymentUrl = null;
                } else {
                
                // Prepare payment items from bills
                $items = [];
                foreach ($bills as $bill) {
                    $items[] = [
                        'type' => 'service',
                        'product_service_reference' => (string) $bill->bill_id, // Use bill_id from the query and convert to string
                        'product_service_name' => $bill->service_name,
                        'amount' => $bill->bill_amount,
                        'is_required' => true,
                        'allow_partial' => $bill->payment_mode === 'partial'
                    ];
                }
                
                Log::info('Bills found for payment link', [
                    'client_number' => $this->client_number,
                    'bills_count' => count($bills),
                    'total_amount' => $bills->sum('bill_amount')
                ]);
                
                if (!empty($items)) {
                    $paymentData = [
                        'description' => 'MFI Member Registration - ' . $account_name,
                        'target' => 'individual',
                        'customer_reference' => $this->client_number,
                        'customer_name' => $account_name,
                        'customer_phone' => $this->phone_number,
                        'customer_email' => $this->email,
                        'expires_at' => now()->addDays(7)->toIso8601String(),
                        'items' => $items
                    ];
                    
                    $paymentResponse = $paymentService->generateUniversalPaymentLink($paymentData);
                    $paymentUrl = $paymentResponse['data']['payment_url'] ?? null;
                    
                    if ($paymentUrl) {
                        Log::info('Payment link generated successfully', [
                            'payment_url' => $paymentUrl,
                            'link_id' => $paymentResponse['data']['link_id'] ?? null,
                            'total_amount' => $paymentResponse['data']['total_amount'] ?? null
                        ]);
                        
                        // Store payment link in bills table for all bills included in this payment
                        $billIds = $bills->pluck('bill_id')->toArray();
                        DB::table('bills')->whereIn('id', $billIds)->update([
                            'payment_link' => $paymentUrl,
                            'payment_link_id' => $paymentResponse['data']['link_id'] ?? null,
                            'payment_link_generated_at' => now(),
                            'payment_link_items' => json_encode($paymentResponse['data']['items'] ?? [])
                        ]);
                        
                        // Set payment URL for notifications
                        $paymentUrl = $paymentUrl;
                    } else {
                        Log::warning('Payment link generation did not return URL', [
                            'response' => $paymentResponse
                        ]);
                        // Don't send any payment link if generation fails
                        $paymentUrl = null;
                    }
                } else {
                    Log::info('No payment items to generate link for', [
                        'client_number' => $this->client_number
                    ]);
                    // Don't send any payment link if no items
                    $paymentUrl = null;
                }
                }
            } catch (\Exception $e) {
                Log::error('Failed to generate payment link', [
                    'error' => $e->getMessage(),
                    'client_number' => $this->client_number
                ]);
                // Don't send any payment link if generation fails
                $paymentUrl = null;
            }








            ProcessMemberNotifications::dispatch($client, $this->generatedControlNumbers, $paymentUrl)
                ->onQueue('notifications');
            
            session()->flash('success', 'Member registration submitted successfully! Your member number is: ' . $this->client_number);
           $this->resetForm();
           $this->emit('refreshClientsList');
            
            Log::info('Member registration completed successfully', [
                'client_id' => $client->id,
                'client_number' => $this->client_number
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Member registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            session()->flash('error', 'Failed to save member: ' . $e->getMessage());
        }
    }

    public function addDocument()
    {
        $this->additionalDocuments[] = ['file' => null, 'description' => ''];
    }

    public function removeDocument($index)
    {
        unset($this->additionalDocuments[$index]);
        $this->additionalDocuments = array_values($this->additionalDocuments);
    }

    public function verifyMembership($membershipNumber)
    {
        Log::info('Verifying guarantor membership', [
            'membership_number' => $membershipNumber,
            'user_id' => auth()->id()
        ]);

        $verificationService = new MembershipVerificationService();
        $result = $verificationService->verifyMembership($membershipNumber);

        Log::info('Guarantor verification result', [
            'exists' => $result,
            
        ]);

        $this->guarantorVerification = $result['member'];
        $this->guarantorVerificationMessage = $result['message'];
        $this->guarantorVerificationStatus = $result['exists'] ? 'success' : 'error';

        if ($result['exists']) {
            $this->guarantor_member_number = $membershipNumber;
        }
    }

    protected function generateControlNumbers()
    {
        $billingService = new BillingService();
        
        // Get all required services in a single query
        $services = DB::table('services')
            ->whereIn('code', ['REG', 'SHC'])
            ->select('id', 'code', 'name', 'is_recurring', 'payment_mode', 'lower_limit')
            ->get()
            ->keyBy('code');

        $this->generatedControlNumbers = [];

        // Generate control numbers for each service
        foreach (['REG', 'SHC'] as $serviceCode) {
            $service = $services[$serviceCode];
            $controlNumber = $billingService->generateControlNumber(
                $this->client_number,
                $service->id,
                $service->is_recurring,
                $service->payment_mode
            );

            $this->generatedControlNumbers[] = [
                'service_code' => $service->code,
                'control_number' => $controlNumber,
                'amount' => $service->lower_limit
            ];
        }
    }

    // Add this method to handle real-time validation
    public function updated($propertyName)
    {
        // Skip validation for exit-related properties and other non-form properties
        $skipValidation = [
            'exitMemberNumber',
            'exitPhoneNumber',
            'exitMemberDetails',
            'exitReason',
            'exitNotes',
            'exitHistory',
            'search',
            'perPage',
            'selectedCategory',
            'selectedMembershipType',
            'selectedGender',
            'selectedMaritalStatus',
            'selectedStatus'
        ];
        
        // Only validate if property is not in skip list
        if (!in_array($propertyName, $skipValidation)) {
            $this->validateOnly($propertyName);
        }
    }


    /**
     * Check for duplicate member registration
     */
    protected function checkForDuplicates()
    {
        $isDuplicate = false;
        $message = '';

        // Check NIDA number
        if ($this->nida_number) {
            $existingNida = ClientsModel::where('nida_number', $this->nida_number)->first();
            if ($existingNida) {
                $isDuplicate = true;
                $message = "Member already exists with NIDA number: {$this->nida_number}. Name: {$existingNida->first_name} {$existingNida->last_name}";
            }
        }

        // Check phone number
        if (!$isDuplicate && $this->phone_number) {
            $existingPhone = ClientsModel::where('phone_number', $this->phone_number)->first();
            if ($existingPhone) {
                $isDuplicate = true;
                $message = "Member already exists with phone number: {$this->phone_number}. Name: {$existingPhone->first_name} {$existingPhone->last_name}";
            }
        }

        return [
            'isDuplicate' => $isDuplicate,
            'message' => $message
        ];
    }

    /**
     * Check for duplicates when updating (exclude current client)
     */
    protected function checkForDuplicatesOnUpdate($clientId)
    {
        $isDuplicate = false;
        $message = '';

        // Check NIDA number
        if ($this->nida_number) {
            $existingNida = ClientsModel::where('nida_number', $this->nida_number)
                ->where('id', '!=', $clientId)
                ->first();
            if ($existingNida) {
                $isDuplicate = true;
                $message = "Another member already exists with NIDA number: {$this->nida_number}. Name: {$existingNida->first_name} {$existingNida->last_name}";
            }
        }

        // Check phone number
        if (!$isDuplicate && $this->phone_number) {
            $existingPhone = ClientsModel::where('phone_number', $this->phone_number)
                ->where('id', '!=', $clientId)
                ->first();
            if ($existingPhone) {
                $isDuplicate = true;
                $message = "Another member already exists with phone number: {$this->phone_number}. Name: {$existingPhone->first_name} {$existingPhone->last_name}";
            }
        }

        return [
            'isDuplicate' => $isDuplicate,
            'message' => $message
        ];
    }

    /**
     * Member Exit Methods
     */
    public function searchMemberForExit()
    {
        $this->exitMemberDetails = null;
        
        if (empty($this->exitMemberNumber) && empty($this->exitPhoneNumber)) {
            session()->flash('error', 'Please enter either a member number or phone number');
            return;
        }
        
        $query = ClientsModel::query();
        
        if (!empty($this->exitMemberNumber)) {
            $query->where('client_number', $this->exitMemberNumber);
        }
        
        if (!empty($this->exitPhoneNumber)) {
            $query->orWhere('phone_number', 'like', '%' . $this->exitPhoneNumber . '%');
        }
        
        $member = $query->first();
        
        if (!$member) {
            session()->flash('error', 'Member not found');
            return;
        }
        
        // Get comprehensive member financial data
        $member = $this->calculateMemberExitData($member);
        
        $this->exitMemberDetails = $member;
        
        // Load exit history
        $this->loadExitHistory();
    }
    
    public function confirmExitRequest()
    {
        \Log::info('🔘 === CONFIRM EXIT REQUEST - BUTTON CLICKED ===', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->user()->id ?? null,
            'user_name' => auth()->user()->name ?? null,
        ]);

        // Validate before showing modal
        if (!$this->exitMemberDetails) {
            \Log::warning('❌ Validation failed: No member selected for exit');
            session()->flash('error', 'No member selected for exit');
            return;
        }

        \Log::info('✅ Member details exist', [
            'member_id' => $this->exitMemberDetails->id,
            'client_number' => $this->exitMemberDetails->client_number,
        ]);

        if (empty($this->exitReason)) {
            \Log::warning('❌ Validation failed: No exit reason provided', [
                'member_id' => $this->exitMemberDetails->id,
            ]);
            session()->flash('error', 'Please select an exit reason');
            return;
        }

        \Log::info('✅ Exit reason provided', [
            'exit_reason' => $this->exitReason,
        ]);

        // Check that settlement amount is zero
        $settlementAmount = $this->exitMemberDetails->exit_final_settlement ?? 0;

        \Log::info('🔍 Checking settlement amount', [
            'settlement_amount' => $settlementAmount,
        ]);

        if ($settlementAmount != 0) {
            \Log::warning('❌ Validation failed: Settlement amount is not zero', [
                'settlement_amount' => $settlementAmount,
                'member_id' => $this->exitMemberDetails->id,
            ]);
            session()->flash('error', 'Cannot process exit - settlement amount must be zero. Current settlement: TZS ' . number_format($settlementAmount, 2));
            return;
        }

        // Check for outstanding obligations
        \Log::info('🔍 Checking outstanding obligations', [
            'loan_balance' => $this->exitMemberDetails->exit_loan_balance,
            'unpaid_bills' => $this->exitMemberDetails->exit_unpaid_bills,
        ]);

        if ($this->exitMemberDetails->exit_loan_balance > 0) {
            \Log::warning('❌ Validation failed: Outstanding loan balance', [
                'loan_balance' => $this->exitMemberDetails->exit_loan_balance,
                'member_id' => $this->exitMemberDetails->id,
            ]);
            session()->flash('error', 'Cannot process exit. Outstanding loan balance: TZS ' . number_format($this->exitMemberDetails->exit_loan_balance, 2));
            return;
        }

        if ($this->exitMemberDetails->exit_unpaid_bills > 0) {
            \Log::warning('❌ Validation failed: Unpaid bills exist', [
                'unpaid_bills' => $this->exitMemberDetails->exit_unpaid_bills,
                'member_id' => $this->exitMemberDetails->id,
            ]);
            session()->flash('error', 'Cannot process exit. Unpaid bills: TZS ' . number_format($this->exitMemberDetails->exit_unpaid_bills, 2));
            return;
        }

        // All validations passed, show confirmation modal
        \Log::info('✅ All validations passed - showing confirmation modal', [
            'member_id' => $this->exitMemberDetails->id,
            'client_number' => $this->exitMemberDetails->client_number,
            'exit_reason' => $this->exitReason,
            'settlement_amount' => $settlementAmount,
        ]);

        $this->showExitConfirmModal = true;

        \Log::info('🎯 Confirmation modal opened successfully');
    }
    
    public function closeExitConfirmModal()
    {
        $this->showExitConfirmModal = false;
    }
    
    public function submitExitForApproval()
    {
        \Log::info('🚀 === SUBMIT EXIT FOR APPROVAL - START ===', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->user()->id ?? null,
            'user_name' => auth()->user()->name ?? null,
            'user_email' => auth()->user()->email ?? null,
            'member_details_exists' => !empty($this->exitMemberDetails),
            'exit_reason' => $this->exitReason ?? 'NOT SET',
            'exit_notes' => $this->exitNotes ?? 'NOT SET',
            'function' => __FUNCTION__,
            'file' => __FILE__,
            'line' => __LINE__,
        ]);

        \Log::info('🔒 Closing exit confirmation modal...');
        $this->closeExitConfirmModal();
        \Log::info('✅ Modal closed');

        // Validation 1: Check if member is selected
        \Log::info('🔍 VALIDATION 1: Checking if member is selected...');
        if (!$this->exitMemberDetails) {
            \Log::warning('❌ Validation 1 FAILED: No member selected for exit');
            session()->flash('error', 'No member selected for exit');
            return;
        }
        \Log::info('✅ Validation 1 PASSED: Member is selected', [
            'member_id' => $this->exitMemberDetails->id,
            'client_number' => $this->exitMemberDetails->client_number,
        ]);

        // Log complete member details
        \Log::info('📋 Complete Member Details:', [
            'id' => $this->exitMemberDetails->id,
            'client_number' => $this->exitMemberDetails->client_number,
            'first_name' => $this->exitMemberDetails->first_name,
            'middle_name' => $this->exitMemberDetails->middle_name ?? 'N/A',
            'last_name' => $this->exitMemberDetails->last_name,
            'phone_number' => $this->exitMemberDetails->phone_number ?? 'N/A',
            'email' => $this->exitMemberDetails->email ?? 'N/A',
            'status' => $this->exitMemberDetails->status ?? 'N/A',
            'exit_shares_balance' => $this->exitMemberDetails->exit_shares_balance ?? 'N/A',
            'exit_savings_balance' => $this->exitMemberDetails->exit_savings_balance ?? 'N/A',
            'exit_deposits_balance' => $this->exitMemberDetails->exit_deposits_balance ?? 'N/A',
            'exit_loan_balance' => $this->exitMemberDetails->exit_loan_balance ?? 'N/A',
            'exit_unpaid_bills' => $this->exitMemberDetails->exit_unpaid_bills ?? 'N/A',
            'exit_final_settlement' => $this->exitMemberDetails->exit_final_settlement ?? 'N/A',
        ]);

        // Validation 2: Check if exit reason is provided
        \Log::info('🔍 VALIDATION 2: Checking if exit reason is provided...');
        if (empty($this->exitReason)) {
            \Log::warning('❌ Validation 2 FAILED: Exit reason not provided', [
                'member_id' => $this->exitMemberDetails->id,
                'exit_reason_value' => $this->exitReason,
            ]);
            session()->flash('error', 'Please select an exit reason before submitting');
            return;
        }
        \Log::info('✅ Validation 2 PASSED: Exit reason provided', [
            'exit_reason' => $this->exitReason,
        ]);

        // Validation 3: Check settlement amount is zero
        \Log::info('🔍 VALIDATION 3: Checking settlement amount...');
        $settlementAmount = $this->exitMemberDetails->exit_final_settlement ?? 0;
        \Log::info('Settlement amount value:', [
            'raw_value' => $this->exitMemberDetails->exit_final_settlement,
            'parsed_value' => $settlementAmount,
            'is_zero' => $settlementAmount == 0,
            'data_type' => gettype($settlementAmount),
        ]);

        if ($settlementAmount != 0) {
            \Log::warning('❌ Validation 3 FAILED: Settlement amount is not zero', [
                'settlement_amount' => $settlementAmount,
                'member_id' => $this->exitMemberDetails->id,
            ]);
            session()->flash('error', 'Cannot submit exit request - settlement amount must be zero. Current settlement: TZS ' . number_format($settlementAmount, 2));
            return;
        }
        \Log::info('✅ Validation 3 PASSED: Settlement amount is zero');

        // Validation 4: Check for outstanding loans
        \Log::info('🔍 VALIDATION 4: Checking for outstanding loans...');
        $loanBalance = $this->exitMemberDetails->exit_loan_balance ?? 0;
        \Log::info('Loan balance value:', [
            'raw_value' => $this->exitMemberDetails->exit_loan_balance,
            'parsed_value' => $loanBalance,
            'has_loans' => $loanBalance > 0,
        ]);

        if ($loanBalance > 0) {
            \Log::warning('❌ Validation 4 FAILED: Outstanding loan balance exists', [
                'loan_balance' => $loanBalance,
                'member_id' => $this->exitMemberDetails->id,
            ]);
            session()->flash('error', 'Cannot submit exit request - member has outstanding loan balance: TZS ' . number_format($loanBalance, 2));
            return;
        }
        \Log::info('✅ Validation 4 PASSED: No outstanding loans');

        // Validation 5: Check for unpaid bills
        \Log::info('🔍 VALIDATION 5: Checking for unpaid bills...');
        $unpaidBills = $this->exitMemberDetails->exit_unpaid_bills ?? 0;
        \Log::info('Unpaid bills value:', [
            'raw_value' => $this->exitMemberDetails->exit_unpaid_bills,
            'parsed_value' => $unpaidBills,
            'has_unpaid_bills' => $unpaidBills > 0,
        ]);

        if ($unpaidBills > 0) {
            \Log::warning('❌ Validation 5 FAILED: Unpaid bills exist', [
                'unpaid_bills' => $unpaidBills,
                'member_id' => $this->exitMemberDetails->id,
            ]);
            session()->flash('error', 'Cannot submit exit request - member has unpaid bills: TZS ' . number_format($unpaidBills, 2));
            return;
        }
        \Log::info('✅ Validation 5 PASSED: No unpaid bills');

        \Log::info('🎉 ===== ALL 5 VALIDATIONS PASSED =====', [
            'member_id' => $this->exitMemberDetails->id,
            'client_number' => $this->exitMemberDetails->client_number,
            'member_name' => $this->exitMemberDetails->first_name . ' ' . $this->exitMemberDetails->last_name,
            'exit_final_settlement' => $settlementAmount,
            'exit_loan_balance' => $loanBalance,
            'exit_unpaid_bills' => $unpaidBills,
        ]);

        try {
            \Log::info('🔄 DATABASE OPERATION: Starting transaction...');
            DB::beginTransaction();
            \Log::info('✅ Transaction started successfully');

            // Build the comprehensive edit package
            \Log::info('📦 Building edit package...');
            $editPackage = [
                'member_id' => $this->exitMemberDetails->id,
                'client_number' => $this->exitMemberDetails->client_number,
                'member_name' => $this->exitMemberDetails->first_name . ' ' . $this->exitMemberDetails->last_name,
                'first_name' => $this->exitMemberDetails->first_name,
                'middle_name' => $this->exitMemberDetails->middle_name ?? '',
                'last_name' => $this->exitMemberDetails->last_name,
                'phone_number' => $this->exitMemberDetails->phone_number,
                'email' => $this->exitMemberDetails->email ?? '',
                'exit_reason' => $this->exitReason,
                'exit_notes' => $this->exitNotes ?? '',
                'exit_requested_by' => auth()->user()->name,
                'exit_requested_at' => now()->toDateTimeString(),

                // Financial Details
                'shares_balance' => $this->exitMemberDetails->exit_shares_balance ?? 0,
                'savings_balance' => $this->exitMemberDetails->exit_savings_balance ?? 0,
                'deposits_balance' => $this->exitMemberDetails->exit_deposits_balance ?? 0,
                'loan_balance' => $this->exitMemberDetails->exit_loan_balance ?? 0,
                'unpaid_bills' => $this->exitMemberDetails->exit_unpaid_bills ?? 0,
                'dividends' => $this->exitMemberDetails->exit_dividends ?? 0,
                'interest_on_savings' => $this->exitMemberDetails->exit_interest_on_savings ?? 0,

                // Totals
                'total_credits' => $this->exitMemberDetails->exit_total_credits ?? 0,
                'total_debits' => $this->exitMemberDetails->exit_total_debits ?? 0,
                'settlement_amount' => $settlementAmount,

                // Counts
                'accounts_count' => $this->exitMemberDetails->accounts_count ?? 0,
                'loans_count' => $this->exitMemberDetails->loans_count ?? 0,
                'unpaid_bills_count' => $this->exitMemberDetails->unpaid_bills_count ?? 0,
            ];
            \Log::info('✅ Edit package built successfully', [
                'total_fields' => count($editPackage),
                'edit_package' => $editPackage,
            ]);

            // Build the approval data array with all required fields
            \Log::info('📝 Building approval data array...');
            $approvalData = [
                'process_name' => 'Member Exit',
                'process_description' => auth()->user()->name . " has requested member exit for {$this->exitMemberDetails->first_name} {$this->exitMemberDetails->last_name} ({$this->exitMemberDetails->client_number})",
                'approval_process_description' => 'has approved member exit for ' . $this->exitMemberDetails->first_name . ' ' . $this->exitMemberDetails->last_name,
                'process_code' => 'MEMBER_EXIT',
                'process_id' => $this->exitMemberDetails->id,
                'process_status' => 'PENDING',
                'approval_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'approver_id' => null,
                'team_id' => auth()->user()->current_team_id ?? 1,
                'checker_level' => 1,
                'submitted_at' => now(),
                'approval_category' => 'MEMBER_MANAGEMENT',
                'edit_package' => $editPackage,
                'additional_data' => [
                    'exit_reason' => $this->exitReason,
                    'exit_notes' => $this->exitNotes ?? '',
                    'requested_by_user_id' => auth()->user()->id,
                    'requested_by_user_name' => auth()->user()->name,
                    'branch_id' => auth()->user()->branch ?? null,
                ],
            ];
            \Log::info('✅ Approval data array built successfully', [
                'total_fields' => count($approvalData),
                'approval_data' => $approvalData,
            ]);

            // Create approval record using Approval model
            \Log::info('💾 CRITICAL: About to create approval record in database...');
            \Log::info('Using Approval model: ' . \App\Models\Approval::class);

            $approval = \App\Models\Approval::create($approvalData);

            \Log::info('✅ SUCCESS: Approval record created in database!', [
                'approval_id' => $approval->id,
                'approval_object' => $approval->toArray(),
            ]);

            \Log::info('💾 CRITICAL: Committing database transaction...');
            DB::commit();
            \Log::info('✅ SUCCESS: Transaction committed to database!');

            \Log::info('📢 Setting success flash message...');
            session()->flash('success', 'Member exit request submitted for approval successfully. Approval ID: #' . $approval->id . '. The request will be reviewed by authorized personnel.');
            \Log::info('✅ Flash message set');

            // Save member details before clearing
            $finalMemberId = $this->exitMemberDetails->id;
            $finalClientNumber = $this->exitMemberDetails->client_number;
            $finalMemberName = $this->exitMemberDetails->first_name . ' ' . $this->exitMemberDetails->last_name;

            \Log::info('🔄 Resetting exit form (calling cancelMemberExit)...');
            $this->cancelMemberExit();
            \Log::info('✅ Exit form reset');

            \Log::info('🔄 Reloading exit history (calling loadExitHistory)...');
            $this->loadExitHistory();
            \Log::info('✅ Exit history reloaded');

            \Log::info('🎉 ===== SUBMIT EXIT FOR APPROVAL - COMPLETED SUCCESSFULLY ===== ', [
                'final_approval_id' => $approval->id,
                'final_member_id' => $finalMemberId,
                'final_client_number' => $finalClientNumber,
                'final_member_name' => $finalMemberName,
            ]);

        } catch (\Exception $e) {
            \Log::error('💥 EXCEPTION CAUGHT IN submitExitForApproval');
            \Log::error('Rolling back database transaction...');
            DB::rollBack();
            \Log::error('✅ Transaction rolled back');

            \Log::error('❌ ===== FAILED TO CREATE MEMBER EXIT APPROVAL ===== ', [
                'exception_class' => get_class($e),
                'member_id' => $this->exitMemberDetails->id ?? null,
                'client_number' => $this->exitMemberDetails->client_number ?? null,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'exit_reason' => $this->exitReason ?? 'NOT SET',
                'exit_notes' => $this->exitNotes ?? 'NOT SET',
                'user_id' => auth()->user()->id ?? null,
                'user_name' => auth()->user()->name ?? null,
            ]);

            session()->flash('error', 'Failed to submit exit request for approval: ' . $e->getMessage());

            \Log::error('📢 Error flash message set: ' . $e->getMessage());
        }

        \Log::info('🏁 === submitExitForApproval FUNCTION ENDED ===');
    }

    
    public function processMemberExit()
    {
        if (!$this->exitMemberDetails) {
            session()->flash('error', 'No member selected for exit');
            return;
        }
        
        if (empty($this->exitReason)) {
            session()->flash('error', 'Please select an exit reason');
            return;
        }
        
        // Check that settlement amount is zero
        $settlementAmount = $this->exitMemberDetails->exit_final_settlement ?? 0;
        
        if ($settlementAmount != 0) {
            session()->flash('error', 'Cannot process exit - settlement amount must be zero. Current settlement: TZS ' . number_format($settlementAmount, 2) . '. Please settle all outstanding amounts before processing exit.');
            return;
        }
        
        // Check for outstanding obligations
        $outstandingObligations = [];
        
        if ($this->exitMemberDetails->exit_loan_balance > 0) {
            $outstandingObligations[] = 'Outstanding loan balance: TZS ' . number_format($this->exitMemberDetails->exit_loan_balance, 2);
        }
        
        if ($this->exitMemberDetails->exit_unpaid_bills > 0) {
            $outstandingObligations[] = 'Unpaid bills: TZS ' . number_format($this->exitMemberDetails->exit_unpaid_bills, 2);
        }
        
        if (!empty($outstandingObligations)) {
            session()->flash('error', 'Cannot process exit - member has outstanding obligations: ' . implode(', ', $outstandingObligations));
            return;
        }
        
        DB::beginTransaction();
        
        try {
            // Use pre-calculated final settlement
            $settlementAmount = $this->exitMemberDetails->exit_final_settlement;
            
            // Update member status to EXITED
            ClientsModel::where('id', $this->exitMemberDetails->id)
                ->update([
                    'status' => 'EXITED',
                    'exit_date' => now(),
                    'exit_reason' => $this->exitReason,
                    'exit_notes' => $this->exitNotes,
                    'updated_at' => now()
                ]);
            
            // Create comprehensive exit record
            DB::table('member_exits')->insert([
                'client_number' => $this->exitMemberDetails->client_number,
                'member_name' => $this->exitMemberDetails->first_name . ' ' . $this->exitMemberDetails->last_name,
                'exit_date' => now(),
                'exit_reason' => $this->exitReason,
                'exit_notes' => $this->exitNotes,
                'shares_balance' => $this->exitMemberDetails->exit_shares_balance,
                'savings_balance' => $this->exitMemberDetails->exit_savings_balance,
                'deposits_balance' => $this->exitMemberDetails->exit_deposits_balance,
                'loan_balance' => $this->exitMemberDetails->exit_loan_balance,
                'unpaid_bills' => $this->exitMemberDetails->exit_unpaid_bills,
                'dividends' => $this->exitMemberDetails->exit_dividends,
                'interest_on_savings' => $this->exitMemberDetails->exit_interest_on_savings,
                'total_credits' => $this->exitMemberDetails->exit_total_credits,
                'total_debits' => $this->exitMemberDetails->exit_total_debits,
                'accounts_count' => $this->exitMemberDetails->accounts_count,
                'loans_count' => $this->exitMemberDetails->loans_count,
                'unpaid_bills_count' => $this->exitMemberDetails->unpaid_bills_count,
                'settlement_amount' => $settlementAmount,
                'processed_by' => auth()->user()->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Deactivate all member accounts
            DB::table('accounts')
                ->where('client_number', $this->exitMemberDetails->client_number)
                ->update(['status' => 'CLOSED', 'updated_at' => now()]);
            
            DB::commit();
            
            session()->flash('success', 'Member exit processed successfully. Settlement amount: TZS ' . number_format($settlementAmount, 2));
            
            // Reset form
            $this->cancelMemberExit();
            
            // Reload exit history
            $this->loadExitHistory();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Member exit processing failed', [
                'member_id' => $this->exitMemberDetails->id,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to process member exit: ' . $e->getMessage());
        }
    }

    
    public function cancelMemberExit()
    {
        $this->exitMemberNumber = null;
        $this->exitPhoneNumber = null;
        $this->exitMemberDetails = null;
        $this->exitReason = null;
        $this->exitNotes = null;
    }
    
    public function viewExitDetails($exitId)
    {
        $exit = DB::table('member_exits')->find($exitId);
        
        if ($exit) {
            session()->flash('info', 'Exit Details: ' . json_encode($exit));
        }
    }
    
    private function loadExitHistory()
    {
        $this->exitHistory = DB::table('member_exits')
            ->orderBy('exit_date', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Calculate comprehensive member exit data
     */
    private function calculateMemberExitData($member)
    {
        // 1. Account Balances
        $shares = DB::table('accounts')
            ->where('client_number', $member->client_number)
            ->where('product_number', '1000') // Share accounts
            ->whereIn('status', ['ACTIVE', 'PENDING'])
            ->sum(DB::raw('CAST(balance AS DECIMAL)'));
            
        $savings = DB::table('accounts')
            ->where('client_number', $member->client_number)
            ->where('product_number', '2000') // Savings accounts
            ->whereIn('status', ['ACTIVE', 'PENDING'])
            ->sum(DB::raw('CAST(balance AS DECIMAL)'));
            
        $deposits = DB::table('accounts')
            ->where('client_number', $member->client_number)
            ->where('product_number', '3000') // Deposit accounts
            ->whereIn('status', ['ACTIVE', 'PENDING'])
            ->sum(DB::raw('CAST(balance AS DECIMAL)'));

        // 2. Loan Balances (using linked account balances for accuracy)
        $loanBalance = DB::table('loans')
            ->join('accounts', 'loans.loan_account_number', '=', 'accounts.account_number')
            ->where('loans.client_number', $member->client_number)
            ->where('loans.status', 'ACTIVE')
            ->sum(DB::raw('CAST(accounts.balance AS DECIMAL)'));

        // 3. Unpaid Bills
        $unpaidBills = DB::table('bills')
            ->where('client_number', $member->client_number)
            ->where('status', '!=', 'PAID')
            ->sum('amount_due');

        // 4. Dividends
        $dividends = DB::table('dividends')
            ->where('member_id', $member->id)
            ->sum('amount');

        // 5. Interest on Savings
        $interestOnSavings = DB::table('interest_payables')
            ->where('member_id', $member->id)
            ->sum('interest_payable');

        // 6. Calculate Final Settlement
        $totalCredits = $shares + $savings + $deposits + $dividends + $interestOnSavings;
        $totalDebits = $loanBalance + $unpaidBills;
        $finalSettlement = $totalCredits - $totalDebits;

        // 7. Set all calculated values (using unique names to avoid relationship conflicts)
        $member->exit_shares_balance = $shares;
        $member->exit_savings_balance = $savings;
        $member->exit_deposits_balance = $deposits;
        $member->exit_loan_balance = $loanBalance;
        $member->exit_unpaid_bills = $unpaidBills;
        $member->exit_dividends = $dividends;
        $member->exit_interest_on_savings = $interestOnSavings;
        $member->exit_total_credits = $totalCredits;
        $member->exit_total_debits = $totalDebits;
        $member->exit_final_settlement = $finalSettlement;

        // 8. Additional details for display
        $member->accounts_count = DB::table('accounts')
            ->where('client_number', $member->client_number)
            ->whereIn('status', ['ACTIVE', 'PENDING'])
            ->count();

        $member->loans_count = DB::table('loans')
            ->where('client_number', $member->client_number)
            ->where('status', 'ACTIVE')
            ->count();

        $member->unpaid_bills_count = DB::table('bills')
            ->where('client_number', $member->client_number)
            ->where('status', '!=', 'PAID')
            ->count();

        return $member;
    }
    
    public function render()
    {
        // Get clients based on tab selection
        $clients = ClientsModel::query();
        
        // Apply filters based on tab
        if ($this->tab_id == '1') {
            // Active clients
            $clients = $clients->where('status', 'ACTIVE');
        } elseif ($this->tab_id == '2') {
            // Inactive clients
            $clients = $clients->where('status', '!=', 'ACTIVE');
        }
        
        // Apply search if needed
        if (!empty($this->search)) {
            $clients = $clients->where(function($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('membership_number', 'like', '%' . $this->search . '%')
                    ->orWhere('phone_number', 'like', '%' . $this->search . '%');
            });
        }
        
        $clients = $clients->paginate(10);
        
        // Pass permissions to the view
        return view('livewire.clients.clients', array_merge(
            $this->permissions,
            [
                'clients' => $clients,
                'permissions' => $this->permissions
            ]
        ));
    }
}
