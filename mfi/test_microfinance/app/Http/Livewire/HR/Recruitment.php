<?php

namespace App\Http\Livewire\HR;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\JobPosting;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\Onboarding;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Illuminate\Support\Collection;
use Livewire\TemporaryUploadedFile;
use App\Traits\HasFileUploads;
use App\Http\Livewire\Components\FileUploader;
use App\Models\Branch;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\ClientsModel;
use App\Services\MemberNumberGeneratorService;
use App\Services\AccountCreationService;
use App\Services\BillingService;
use Illuminate\Support\Facades\Mail;
use App\Mail\ControlNumberGenerated;
use App\Notifications\NewMemberWelcomeNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Models\Approval;

class Recruitment extends Component
{
    use WithPagination;
    use WithFileUploads;
    use HasFileUploads;

    // Search and filter properties
    public $search = '';
    public $filterStatus = '';
    public $filterDepartment = '';

    // Tab State
    public $activeTab = 'job-postings';

    // Common properties
    public $email = '';
    public $phone = '';
    public $notes = '';
    public $salary = '';

    // Job Posting properties
    public $showJobModal = false;
    public $editingJobId = null;
    public $jobTitle = '';
    public $department = '';
    public $location = '';
    public $jobType = '';
    public $description = '';
    public $requirements = '';
    public $jobStatus = 'open';

    // Applicant properties
    public $showApplicantModal = false;
    public $editingApplicantId = null;
    public $name = '';
    public $resume = '';
    public $coverLetter = '';
    public $jobPostingId = '';
    public $applicantStatus = 'new';

    // Interview properties
    public $showInterviewModal = false;
    public $editingInterviewId = null;
    public $applicantId = '';
    public $interviewDate = '';
    public $interviewTime = '';
    public $interviewType = '';
    public $interviewer = '';
    public $interviewStatus = 'scheduled';

    // Onboarding properties
    public $showOnboardingModal = false;
    public $editingOnboardingId = null;
    public $firstName = '';
    public $middleName = '';
    public $lastName = '';
    public $dateOfBirth = '';
    public $gender = '';
    public $maritalStatus = '';
    public $street = '';
    public $role = '';
    public $employmentType = '';
    public $nextOfKinName = '';
    public $nextOfKinPhone = '';
    public $nidaNumber = '';
    public $tinNumber = '';
    public $startDate = '';
    public $onboardingStatus = 'pending';
    public $branchId = 1; // Default branch ID

    // File upload properties
    public $cv = null;
    public $nationalId = null;
    public $passportPhoto = null;
    public $employmentContract = null;
    public $bankAccountDetails = null;

    // Dashboard Data
    public $departmentData = [];
    public $jobPostingsCount = 0;
    public $applicantsCount = 0;
    public $interviewsCount = 0;
    public $onboardingsCount = 0;

    // Stepper control
    public $currentStep = 0;
    public $totalSteps = 9;

    // Step 0 - Personal Information
    public $dob;
    public $nationality;
    public $nida;
    public $tin;
    public $physicalAddress;
    public $postalAddress;
    public $emergencyContactName;
    public $emergencyContactRelationship;
    public $emergencyContactPhone;

    // Step 1 - Educational & Professional Documents
    public $certificates = [];
    public $profCerts = [];
    public $transcripts = [];
    public $license;

    // Step 2 - Employment History & References
    public $employmentLetters = [];
    public $recommendationLetters = [];
    public $referees = [];
    public $reasonForLeaving;

    // Step 3 - Regulatory and Compliance
    public $policeClearance;
    public $crbReport;
    public $nonConflictDeclaration;
    public $amlDeclaration;
    public $confidentialityOath;
    public $fitAndProperDeclaration;
    public $declarationOfAssets;

    // Step 4 - HR & Employment Contracts
    public $jobDescription;
    public $termsAndConditions;
    public $codeOfConduct;
    public $employeeHandbook;

    // Step 5 - Payroll & Compensation
    public $taxDeclaration;
    public $nhifDetails;
    public $nssfDetails;

    // Step 6 - SACCOS Documentation
    public $saccosApplication;
    public $saccosRules;
    public $saccosAgreement;

    // Step 7 - Supporting Files & Identification
    public $passportPhotos = [];
    public $drivingLicense;
    public $personalInfoForm;

    // Step 8 - IT & Operational Setup
    public $emailCreated = false;
    public $systemAccess = false;
    public $systemAccessGranted = false;
    public $workstationId = '';
    public $idBadge = false;
    public $assignedEquipment = [];

    // Collections for dropdowns
    public $departments;
    public $roles;
    public $branches;
    public $managers;

    // New fields
    public $department_id = null;
    public $role_id;
    public $branch_id;
    public $reporting_manager_id;
    public $employment_type;
    public $start_date;

    // File Upload Listeners
    protected $listeners = [
        'files-uploaded' => 'handleFilesUploaded',
        'file-removed' => 'handleFileRemoved'
    ];

    // Add property for generated control numbers
    public $generatedControlNumbers = [];

    // Add missing properties
    public $emergencyContactEmail;
    public $paymentFrequency;
    public $nssfNumber;
    public $nssfRate;
    public $nhifNumber;
    public $nhifRate;
    public $workersCompensation;
    public $lifeInsurance;
    public $taxCategory;
    public $payeRate;
    public $basicSalary;
    public $grossSalary;
    public $taxPaid;
    public $pension;
    public $nhif;
    public $educationLevel;
    public $city;
    public $region;
    public $district;
    public $ward;
    public $postalCode;

    protected $rules = [
        // Job Posting Rules
        'jobTitle' => 'required|min:3|max:255',
        'department' => 'required|exists:departments,id',
        'location' => 'required|max:255',
        'jobType' => 'required|in:full-time,part-time,contract,internship',
        'description' => 'required|min:50',
        'requirements' => 'required|min:50',
        'salary' => 'required|numeric|min:0',
        'jobStatus' => 'required|in:open,closed,draft',

        // Applicant Rules
        'name' => 'required|min:3|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
        'resume' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        'coverLetter' => 'nullable|max:1000',
        'jobPostingId' => 'required|exists:job_postings,id',
        'applicantStatus' => 'required|in:new,reviewing,shortlisted,rejected,hired',

        // Interview Rules
        'applicantId' => 'required|exists:applicants,id',
        'interviewDate' => 'required|date|after_or_equal:today',
        'interviewTime' => 'required|date_format:H:i',
        'interviewType' => 'required|in:phone,video,in-person',
        'interviewer' => 'required|exists:employees,id',
        'notes' => 'nullable|max:1000',
        'interviewStatus' => 'required|in:scheduled,completed,cancelled',

        // Onboarding Rules - Personal Information
        'firstName' => 'required|string|max:100',
        'middleName' => 'nullable|string|max:100',
        'lastName' => 'required|string|max:100',
        'dateOfBirth' => 'required|date|before:-18 years',
        'nationality' => 'required|string|max:100',
        'nida' => 'required|string|max:30|unique:employees,nida_number',
        'tin' => 'required|string|max:30|unique:employees,tin_number',
        'physicalAddress' => 'required|string|max:255',
        'postalAddress' => 'required|string|max:255',
        'emergencyContactName' => 'required|string|max:255',
        'emergencyContactRelationship' => 'required|string|max:100',
        'emergencyContactPhone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',

        // Educational & Professional Documents
        'cv' => 'required|file|mimes:pdf,doc,docx|max:10240',
        'certificates.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'profCerts.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'transcripts.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'license' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',

        // Employment History & References
        'employmentLetters.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'recommendationLetters.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'reasonForLeaving' => 'required|string|max:1000',

        // Regulatory and Compliance
        'policeClearance' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'crbReport' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'nonConflictDeclaration' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'amlDeclaration' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'confidentialityOath' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'fitAndProperDeclaration' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'declarationOfAssets' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',

        // HR & Employment Contracts
        'employmentContract' => 'required|file|mimes:pdf|max:5120',
        'jobDescription' => 'required|file|mimes:pdf|max:5120',
        'termsAndConditions' => 'required|file|mimes:pdf|max:5120',
        'codeOfConduct' => 'required|file|mimes:pdf|max:5120',
        'employeeHandbook' => 'required|file|mimes:pdf|max:5120',

        // Payroll & Compensation
        'bankAccountDetails' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'taxDeclaration' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'nhifDetails' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'nssfDetails' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',

        // SACCOS Documentation
        'saccosApplication' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'saccosRules' => 'required|file|mimes:pdf,doc,docx|max:10240',
        'saccosAgreement' => 'required|file|mimes:pdf,doc,docx|max:10240',

        // Supporting Files & Identification
        'passportPhotos.*' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        'nationalId' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'drivingLicense' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'personalInfoForm' => 'required|file|mimes:pdf,doc,docx|max:10240',

        // IT & Operational Setup
        'workstationId' => 'required|string|max:50',
        'assignedEquipment' => 'required|array',
        'assignedEquipment.*.name' => 'required|string|max:255',
        'assignedEquipment.*.serial_number' => 'required|string|max:100',
        'assignedEquipment.*.condition' => 'required|string|in:new,used,refurbished',
        'emailCreated' => 'required|boolean',
        'systemAccess' => 'required|boolean',
        'idBadge' => 'required|boolean',

        // New fields
        'department_code' => 'required|exists:departments,department_code',
        'role_id' => 'required|exists:roles,id',
        'branch_id' => 'required|exists:branches,id',
        'reporting_manager_id' => 'required|exists:employees,id',
        'employment_type' => 'required|in:full_time,part_time,contract,internship',
        'start_date' => 'required|date|after_or_equal:today',
    ];

    protected $messages = [
        'cv.required' => 'Please upload your CV/Resume',
        'nationalId.required' => 'Please upload your National ID',
        'passportPhoto.required' => 'Please upload your passport photo',
        'employmentContract.required' => 'Please upload the employment contract',
        'bankAccountDetails.required' => 'Please upload your bank account details',
        'workstationId.required' => 'Please provide a workstation ID',
    ];

    public function boot(FileUploadService $fileUploadService)
    {
        $this->bootHasFileUploads($fileUploadService);
    }

    public function mount()
    {
        $this->loadDashboardData();
        $this->assignedEquipment = [];
        $this->systemAccessGranted = false;
        $this->departments = Department::active()->get();
        $this->roles = collect(); // Initialize as empty collection
        $this->branches = Branch::all();
        
        // Get potential reporting managers (Department Heads and Managers)
        $this->managers = Employee::whereRaw('EXISTS (
            SELECT 1 FROM users 
            WHERE CAST(users."employeeId" AS BIGINT) = employees.id
            AND EXISTS (
                SELECT 1 FROM roles 
                INNER JOIN user_roles ON roles.id = user_roles.role_id 
                WHERE users.id = user_roles.user_id 
                AND (roles.name LIKE \'%Head\' OR roles.name LIKE \'%Manager\')
            )
        )')->get();
    }

    public function updatedDepartmentId($value)
    {
        if ($value) {
            $this->roles = Role::where('department_id', $value)
                ->where('is_system_role', false)
                ->orderBy('level')
                ->get();
                
            // Update managers list to show only managers from the selected department
            $this->managers = Employee::whereRaw('EXISTS (
                SELECT 1 FROM users 
                WHERE CAST(users."employeeId" AS BIGINT) = employees.id
                AND EXISTS (
                    SELECT 1 FROM roles 
                    INNER JOIN user_roles ON roles.id = user_roles.role_id 
                    WHERE users.id = user_roles.user_id 
                    AND roles.department_id = ?
                    AND (roles.name LIKE \'%Head\' OR roles.name LIKE \'%Manager\')
                )
            )', [$value])->get();
        } else {
            $this->roles = collect();
            $this->managers = collect();
        }
        $this->role_id = null; // Reset role selection when department changes
        $this->reporting_manager_id = null; // Reset reporting manager when department changes
    }

    public function loadDashboardData()
    {
        // Load department data
        $this->departmentData = DB::table('departments')
            ->select('departments.department_name', DB::raw('COUNT(employees.id) as employee_count'))
            ->leftJoin('employees', 'employees.department_id', '=', 'departments.id')
            ->groupBy('departments.id', 'departments.department_name')
            ->get()
            ->map(function ($dept) {
                return [
                    'name' => $dept->department_name,
                    'count' => $dept->employee_count
                ];
            });

        // Load recruitment data
        $this->jobPostingsCount = JobPosting::count();
        $this->applicantsCount = Applicant::count();
        $this->interviewsCount = Interview::count();
        $this->onboardingsCount = Onboarding::count();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // Job Posting Methods
    public function openJobModal()
    {
        $this->resetJobForm();
        $this->showJobModal = true;
    }

    public function closeJobModal()
    {
        $this->showJobModal = false;
        $this->resetJobForm();
    }

    public function resetJobForm()
    {
        $this->jobTitle = '';
        $this->department = '';
        $this->location = '';
        $this->jobType = '';
        $this->description = '';
        $this->requirements = '';
        $this->salary = '';
        $this->jobStatus = 'open';
        $this->editingJobId = null;
    }

    protected function handleFileUpload($field, $path)
    {
        if ($field === 'assignedEquipment') {
            return $this->assignedEquipment;
        }

        $file = $this->$field;
        if (!$file) {
            return null;
        }
        return $this->uploadFile($file, $path);
    }

    protected function handleMultipleFileUpload($field, $path)
    {
        if ($field === 'assignedEquipment') {
            return $this->assignedEquipment;
        }

        $files = $this->$field;
        if (empty($files)) {
            return [];
        }
        return $this->uploadMultipleFiles($files, $path);
    }

    protected function deleteFile($path)
    {
        return $this->fileUploadService->deleteFile($path);
    }

    protected function deleteMultipleFiles($paths)
    {
        $this->fileUploadService->deleteMultipleFiles($paths);
    }

    public function removeFile($field, $index = null)
    {
        if ($field === 'assignedEquipment') {
            if (isset($this->assignedEquipment[$index])) {
                unset($this->assignedEquipment[$index]);
                $this->assignedEquipment = array_values($this->assignedEquipment);
            }
            return;
        }

        if ($index !== null) {
            // Handle multiple file removal
            $files = $this->$field;
            if (isset($files[$index])) {
                unset($files[$index]);
                $this->$field = array_values($files);
            }
        } else {
            // Handle single file removal
            $this->$field = null;
        }
    }

    public function createJobPosting()
    {
        $this->validate([
            'jobTitle' => 'required|min:3|max:255',
            'department' => 'required|exists:departments,id',
            'location' => 'required|max:255',
            'jobType' => 'required|in:full-time,part-time,contract,internship',
            'description' => 'required|min:50',
            'requirements' => 'required|min:50',
            'salary' => 'required|numeric|min:0',
            'jobStatus' => 'required|in:open,closed,draft',
        ]);

        JobPosting::create([
            'job_title' => $this->jobTitle,
            'department_id' => $this->department,
            'location' => $this->location,
            'job_type' => $this->jobType,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'salary' => $this->salary,
            'status' => $this->jobStatus,
            'created_by' => auth()->id(),
        ]);

        $this->closeJobModal();
        session()->flash('message', 'Job posting created successfully.');
    }

    public function editJobPosting($id)
    {
        $job = JobPosting::findOrFail($id);
        $this->editingJobId = $id;
        $this->jobTitle = $job->job_title;
        $this->department = $job->department;
        $this->location = $job->location;
        $this->jobType = $job->job_type;
        $this->description = $job->description;
        $this->requirements = $job->requirements;
        $this->salary = $job->salary;
        $this->jobStatus = $job->status;
        $this->showJobModal = true;
    }

    public function updateJobPosting()
    {
        $this->validate([
            'jobTitle' => 'required|min:3',
            'department' => 'required',
            'location' => 'required',
            'jobType' => 'required',
            'description' => 'required',
            'requirements' => 'required',
            'salary' => 'required|numeric',
            'jobStatus' => 'required|in:open,closed,draft',
        ]);

        $job = JobPosting::findOrFail($this->editingJobId);
        $job->update([
            'job_title' => $this->jobTitle,
            'department' => $this->department,
            'location' => $this->location,
            'job_type' => $this->jobType,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'salary' => $this->salary,
            'status' => $this->jobStatus,
        ]);

        $this->closeJobModal();
        session()->flash('message', 'Job posting updated successfully.');
    }

    public function deleteJobPosting($id)
    {
        JobPosting::findOrFail($id)->delete();
        session()->flash('message', 'Job posting deleted successfully.');
    }

    // Applicant Methods
    public function openApplicantModal()
    {
        $this->resetApplicantForm();
        $this->showApplicantModal = true;
    }

    public function closeApplicantModal()
    {
        $this->showApplicantModal = false;
        $this->resetApplicantForm();
    }

    public function resetApplicantForm()
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->resume = null;
        $this->coverLetter = '';
        $this->jobPostingId = '';
        $this->applicantStatus = 'new';
        $this->editingApplicantId = null;
    }

    public function createApplicant()
    {
        $this->validate([
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'coverLetter' => 'nullable|max:1000',
            'jobPostingId' => 'required|exists:job_postings,id',
        ]);

        $resumePath = $this->handleFileUpload($this->resume, 'applicants/resumes');

        Applicant::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'resume_path' => $resumePath,
            'cover_letter' => $this->coverLetter,
            'job_posting_id' => $this->jobPostingId,
            'status' => 'new',
            'created_by' => auth()->id(),
        ]);

        $this->closeApplicantModal();
        session()->flash('message', 'Applicant added successfully.');
    }

    public function editApplicant($id)
    {
        $applicant = Applicant::findOrFail($id);
        $this->editingApplicantId = $id;
        $this->name = $applicant->name;
        $this->email = $applicant->email;
        $this->phone = $applicant->phone;
        $this->coverLetter = $applicant->cover_letter;
        $this->jobPostingId = $applicant->job_posting_id;
        $this->applicantStatus = $applicant->status;
        $this->showApplicantModal = true;
    }

    public function updateApplicant()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email',
            'phone' => 'required',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'coverLetter' => 'nullable',
            'jobPostingId' => 'required|exists:job_postings,id',
            'applicantStatus' => 'required|in:new,reviewing,shortlisted,rejected,hired',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'cover_letter' => $this->coverLetter,
            'job_posting_id' => $this->jobPostingId,
            'status' => $this->applicantStatus,
        ];

        if ($this->resume) {
            $data['resume_path'] = $this->resume->store('resumes', 'public');
        }

        $applicant = Applicant::findOrFail($this->editingApplicantId);
        $applicant->update($data);

        $this->closeApplicantModal();
        session()->flash('message', 'Applicant updated successfully.');
    }

    public function deleteApplicant($id)
    {
        Applicant::findOrFail($id)->delete();
        session()->flash('message', 'Applicant deleted successfully.');
    }

    // Interview Methods
    public function openInterviewModal()
    {
        $this->resetInterviewForm();
        $this->showInterviewModal = true;
    }

    public function closeInterviewModal()
    {
        $this->showInterviewModal = false;
        $this->resetInterviewForm();
    }

    public function resetInterviewForm()
    {
        $this->applicantId = '';
        $this->interviewDate = '';
        $this->interviewTime = '';
        $this->interviewType = '';
        $this->interviewer = '';
        $this->notes = '';
        $this->interviewStatus = 'scheduled';
        $this->editingInterviewId = null;
    }

    public function createInterview()
    {
        $this->validate([
            'applicantId' => 'required|exists:applicants,id',
            'interviewDate' => 'required|date|after_or_equal:today',
            'interviewTime' => 'required|date_format:H:i',
            'interviewType' => 'required|in:phone,video,in-person',
            'interviewer' => 'required|exists:employees,id',
            'notes' => 'nullable|max:1000',
        ]);

        Interview::create([
            'applicant_id' => $this->applicantId,
            'interview_date' => $this->interviewDate,
            'interview_time' => $this->interviewTime,
            'interview_type' => $this->interviewType,
            'interviewer_id' => $this->interviewer,
            'notes' => $this->notes,
            'status' => 'scheduled',
            'created_by' => auth()->id(),
        ]);

        $this->closeInterviewModal();
        session()->flash('message', 'Interview scheduled successfully.');
    }

    public function editInterview($id)
    {
        $interview = Interview::findOrFail($id);
        $this->editingInterviewId = $id;
        $this->applicantId = $interview->applicant_id;
        $this->interviewDate = $interview->interview_date;
        $this->interviewTime = $interview->interview_time;
        $this->interviewType = $interview->interview_type;
        $this->interviewer = $interview->interviewer;
        $this->notes = $interview->notes;
        $this->interviewStatus = $interview->status;
        $this->showInterviewModal = true;
    }

    public function updateInterview()
    {
        $this->validate([
            'applicantId' => 'required|exists:applicants,id',
            'interviewDate' => 'required|date',
            'interviewTime' => 'required',
            'interviewType' => 'required|in:phone,video,in-person',
            'interviewer' => 'required',
            'notes' => 'nullable',
            'interviewStatus' => 'required|in:scheduled,completed,cancelled',
        ]);

        $interview = Interview::findOrFail($this->editingInterviewId);
        $interview->update([
            'applicant_id' => $this->applicantId,
            'interview_date' => $this->interviewDate,
            'interview_time' => $this->interviewTime,
            'interview_type' => $this->interviewType,
            'interviewer_id' => $this->interviewer,
            'notes' => $this->notes,
            'status' => $this->interviewStatus,
        ]);

        $this->closeInterviewModal();
        session()->flash('message', 'Interview updated successfully.');
    }

    public function deleteInterview($id)
    {
        Interview::findOrFail($id)->delete();
        session()->flash('message', 'Interview deleted successfully.');
    }

    // Onboarding Methods
    public function openOnboardingModal()
    {
        $this->resetOnboardingForm();
        $this->showOnboardingModal = true;
    }

    public function closeOnboardingModal()
    {
        $this->showOnboardingModal = false;
        $this->resetOnboardingForm();
    }

    private function resetOnboardingForm()
    {
        $this->editingOnboardingId = null;
        $this->firstName = '';
        $this->middleName = '';
        $this->lastName = '';
        $this->dateOfBirth = '';
        $this->gender = '';
        $this->maritalStatus = '';
        $this->email = '';
        $this->phone = '';
        $this->street = '';
        $this->department = '';
        $this->role = '';
        $this->employmentType = '';
        $this->salary = '';
        $this->nextOfKinName = '';
        $this->nextOfKinPhone = '';
        $this->nidaNumber = '';
        $this->tinNumber = '';
        $this->startDate = '';
        $this->onboardingStatus = 'pending';
        $this->notes = '';
        $this->workstationId = '';
        $this->assignedEquipment = [];
        $this->emailCreated = false;
        $this->systemAccess = false;
        $this->idBadge = false;
    }

    protected function generateSecurePassword($length = 12)
    {
        $numbers = '0123456789';
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        // Ensure at least 4 numbers
        $password = substr(str_shuffle($numbers), 0, 4);
        
        // Fill the rest with random letters
        $password .= substr(str_shuffle($letters), 0, $length - 4);
        
        // Shuffle the final password
        return str_shuffle($password);
    }

    public function createOnboarding()
    {
        try {
            DB::beginTransaction();
            
            Log::info('Starting onboarding process', [
                'employee_data' => [
                    'name' => $this->firstName . ' ' . $this->middleName . ' ' . $this->lastName,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'dateOfBirth' => $this->dateOfBirth,
                    'gender' => $this->gender,
                    'department_id' => $this->department_id,
                    'role_id' => $this->role_id,
                    'reporting_manager_id' => $this->reporting_manager_id,
                    'joining_date' => $this->start_date,
                    'salary' => $this->salary,
                    'status' => 'active'
                ]
            ]);

            // Create employee record
            $employee = Employee::create([
                'first_name' => $this->firstName,
                'middle_name' => $this->middleName,
                'last_name' => $this->lastName,
                'email' => $this->email,
                'phone' => $this->phone,
                'date_of_birth' => !empty($this->dateOfBirth) ? $this->dateOfBirth : null,
                'gender' => $this->gender,
                'marital_status' => $this->maritalStatus,
                'nationality' => $this->nationality,
                'address' => $this->physicalAddress,
                'street' => $this->street,
                'city' => $this->city,
                'region' => $this->region,
                'district' => $this->district,
                'ward' => $this->ward,
                'postal_code' => $this->postalCode,
                'emergency_contact_name' => $this->emergencyContactName,
                'emergency_contact_relationship' => $this->emergencyContactRelationship,
                'emergency_contact_phone' => $this->emergencyContactPhone,
                'emergency_contact_email' => $this->emergencyContactEmail,
                'department_id' => $this->department_id,
                'role_id' => $this->role_id,
                'branch_id' => $this->branch_id,
                'reporting_manager_id' => $this->reporting_manager_id,
                'employment_type' => $this->employment_type,
                'hire_date' => empty($this->start_date) ? null : $this->start_date,
                'basic_salary' => $this->basicSalary,
                'gross_salary' => $this->grossSalary,
                'payment_frequency' => $this->paymentFrequency,
                'employee_status' => 'active',
                'nssf_number' => $this->nssfNumber,
                'nssf_rate' => $this->nssfRate,
                'nhif_number' => $this->nhifNumber,
                'nhif_rate' => $this->nhifRate,
                'workers_compensation' => $this->workersCompensation,
                'life_insurance' => 1,
                'tax_category' => $this->taxCategory,
                'paye_rate' => $this->payeRate,
                'tax_paid' => $this->taxPaid,
                'pension' => $this->pension,
                'nhif' => $this->nhif,
                'tin_number' => $this->tin,
                'nida_number' => $this->nida,
                'education_level' => $this->educationLevel,
                'institution_id' => auth()->user()->institution_id,
                'approval_stage' => 'PENDING'
            ]);

            // Register employee as SACCO member
            $memberNumber = app(MemberNumberGeneratorService::class)->generate();
            
            // Create client record
            $client = ClientsModel::create([
                'member_number' => $memberNumber,
                'account_number' => $memberNumber,
                'first_name' => $this->firstName,
                'middle_name' => $this->middleName,
                'last_name' => $this->lastName,
                'email' => $this->email,
                'phone_number' => $this->phone,
                'mobile_phone_number' => $this->phone,
                'client_status' => 'ACTIVE',
                'membership_type' => 'EMPLOYEE',
                'marital_status' => $this->maritalStatus,
                'gender' => $this->gender,
                'date_of_birth' => !empty($this->dateOfBirth) ? $this->dateOfBirth : null,
                'nationality' => $this->nationality,
                'tin_number' => $this->tinNumber,
                'nida_number' => $this->nidaNumber,
                'address' => $this->physicalAddress,
                'main_address' => $this->physicalAddress,
                'street' => $this->street,
                'city' => $this->city,
                'region' => $this->region,
                'district' => $this->district,
                'ward' => $this->ward,
                'postal_code' => $this->postalCode,
                'next_of_kin_name' => $this->nextOfKinName,
                'next_of_kin_phone' => $this->nextOfKinPhone,
                'employment' => $this->employmentType,
                'employer_name' => config('app.name'),
                'occupation' => $this->jobTitle,
                'education_level' => $this->educationLevel,
                'basic_salary' => $this->basicSalary,
                'gross_salary' => $this->grossSalary,
                'tax_paid' => $this->taxPaid,
                'pension' => $this->pension,
                'nhif' => $this->nhif,
                'registration_date' => now(),
                'branch_id' => $this->branchId,
                'employee_id' => $employee->id
            ]);

            // Create user account
            $password = $this->generateSecurePassword();
            $user = User::create([
                'name' => $this->firstName . ' ' . $this->middleName . ' ' . $this->lastName,
                'email' => $this->email,
                'password' => Hash::make($password),
                'employeeId' => $employee->id,
                'department_code' => $employee->department_code,
                'branch' => $employee->branch_code,
                //'institution_id' => auth()->user()->institution_id,
                'password_changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Associate the models
            $employee->update([
                'user_id' => $user->id,
                'client_id' => $client->id
            ]);

            $client->update([
                'user_id' => $user->id
            ]);

            $user->update([
                'client_id' => $client->id
            ]);

            // Update approval request
            $approvalData = [
                'institution' => $client->id,
                'process_name' => 'new_member_registration',
                'process_description' => auth()->user()->name . ' has requested to register a new member: ' . 
                    $this->firstName . ' ' . $this->lastName,
                'approval_process_description' => 'New member registration approval required',
                'process_code' => 'MEMBER_REG',
                'process_id' => $client->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'team_id' => auth()->user()->current_team_id,
                'approver_id' => auth()->user()->id,
                'approval_status' => 'PENDING',
                'edit_package' => null
            ];

            $approval = Approval::create($approvalData);

            // Create mandatory accounts
            $accountService = new AccountCreationService();
            $institution = DB::table('institutions')->where('id', 1)->first();
            $branch = null;
            //$branch_number = $this->branchId;
            $branch_number = '01';

            $mandatorySharesAccount = $institution->mandatory_shares_account;
            $mandatorySavingsAccount = $institution->mandatory_savings_account;
            $mandatoryDepositsAccount = $institution->mandatory_deposits_account;

            $sharesAccount = $accountService->createAccount([
                'account_use' => 'external',
                'account_name' => $client->first_name . ' ' . $client->last_name,
                'type' => 'capital_accounts',
                'product_number' => '1000',
                'member_number' => $client->member_number,
                'branch_number' => $branch_number
            ], $mandatorySharesAccount);

            $savingsAccount = $accountService->createAccount([
                'account_use' => 'external',
                'account_name' => $client->first_name . ' ' . $client->last_name,
                'type' => 'savings_accounts',
                'product_number' => '2000',
                'member_number' => $client->member_number,
                'branch_number' => $branch_number
            ], $mandatorySavingsAccount);

            $depositsAccount = $accountService->createAccount([
                'account_use' => 'external',
                'account_name' => $client->first_name . ' ' . $client->last_name,
                'type' => 'deposits_accounts',
                'product_number' => '3000',
                'member_number' => $client->member_number,
                'branch_number' => $branch_number
            ], $mandatoryDepositsAccount);

            // Generate control numbers for mandatory services
            $billingService = new BillingService();
            
            // Get all required services in a single query
            $services = DB::table('services')
                ->whereIn('code', ['REG', 'SHC'])
                ->select('id', 'code', 'name', 'is_recurring', 'payment_mode', 'lower_limit')
                ->get()
                ->keyBy('code');

            $controlNumbers = [];

            // Generate control numbers for each service
            foreach (['REG', 'SHC'] as $serviceCode) {
                $service = $services[$serviceCode];
                $controlNumber = $billingService->generateControlNumber(
                    $client->member_number,
                    $service->id,
                    $service->is_recurring,
                    $service->payment_mode
                );

                $controlNumbers[] = [
                    'service_code' => $service->code,
                    'control_number' => $controlNumber,
                    'amount' => $service->lower_limit
                ];
            }

            // Create bills for each service
            foreach ($controlNumbers as $control) {
                $service = DB::table('services')
                    ->where('code', $control['service_code'])
                    ->first();

                if ($service) {
                    $bill = $billingService->createBill(
                        $client->member_number,
                        $service->id,
                        $service->is_recurring,
                        $service->payment_mode,
                        $control['control_number'],
                        $service->lower_limit
                    );
                }
            }

            // Send welcome email with control numbers and login credentials
            $client->notify(new NewMemberWelcomeNotification(
                $client,
                $controlNumbers,
                $sharesAccount,
                $savingsAccount,
                $depositsAccount,
                $password
            ));

            DB::commit();
            
            Log::info('Onboarding process completed successfully', [
                'employee_id' => $employee->id,
                'member_number' => $memberNumber,
                'control_number' => $controlNumbers
            ]);

            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Employee onboarded successfully!'
            ]);

            $this->resetOnboardingForm();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Onboarding process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Failed to onboard employee: ' . $e->getMessage()
            ]);
        }
    }

    public function editOnboarding($id)
    {
        $onboarding = Onboarding::findOrFail($id);
        $employee = Employee::where('id', $onboarding->applicant_id)->first();

        $this->editingOnboardingId = $id;
        $this->firstName = $employee->first_name;
        $this->middleName = $employee->middle_name;
        $this->lastName = $employee->last_name;
        $this->dateOfBirth = $employee->date_of_birth;
        $this->gender = $employee->gender;
        $this->maritalStatus = $employee->marital_status;
        $this->email = $employee->email;
        $this->phone = $employee->phone;
        $this->street = $employee->street;
        $this->department = $employee->department;
        $this->role = Role::where('name', $employee->job_title)->first()->id;
        $this->employmentType = $employee->Employment_type;
        $this->salary = $employee->salary;
        $this->nextOfKinName = $employee->next_of_kin_name;
        $this->nextOfKinPhone = $employee->next_of_kin_phone;
        $this->nidaNumber = $employee->nida_number;
        $this->tinNumber = $employee->tin_number;
        $this->startDate = $onboarding->start_date;
        $this->onboardingStatus = $onboarding->status;
        $this->notes = $onboarding->notes;
        $this->workstationId = $onboarding->workstation_id;
        $this->assignedEquipment = $onboarding->assigned_equipment;
        $this->emailCreated = $onboarding->email_created;
        $this->systemAccess = $onboarding->system_access;
        $this->idBadge = $onboarding->id_badge;

        $this->showOnboardingModal = true;
    }

    public function updateOnboarding()
    {
        $this->validate();

        $onboarding = Onboarding::findOrFail($this->editingOnboardingId);
        $employee = Employee::where('id', $onboarding->applicant_id)->first();

        // Update employee record
        $employee->update([
            'first_name' => $this->firstName,
            'middle_name' => $this->middleName,
            'last_name' => $this->lastName,
            'date_of_birth' => $this->dateOfBirth,
            'gender' => $this->gender,
            'marital_status' => $this->maritalStatus,
            'email' => $this->email,
            'phone' => $this->phone,
            'street' => $this->street,
            'department' => $this->department,
            'job_title' => Role::find($this->role)->name,
            'hire_date' => $this->startDate,
            'salary' => $this->salary,
            'Employment_type' => $this->employmentType,
            'next_of_kin_name' => $this->nextOfKinName,
            'next_of_kin_phone' => $this->nextOfKinPhone,
            'nida_number' => $this->nidaNumber,
            'tin_number' => $this->tinNumber,
            'notes' => $this->notes,
        ]);

        // Update onboarding record
        $onboarding->update([
            'start_date' => $this->startDate,
            'status' => $this->onboardingStatus,
            'notes' => $this->notes,
            'workstation_id' => $this->workstationId,
            'assigned_equipment' => $this->assignedEquipment,
            'email_created' => $this->emailCreated,
            'system_access' => $this->systemAccess,
            'id_badge' => $this->idBadge,
        ]);

        $this->closeOnboardingModal();
        session()->flash('message', 'Onboarding process updated successfully.');
    }

    public function deleteOnboarding($id)
    {
        $onboarding = Onboarding::findOrFail($id);
        $onboarding->delete();
        session()->flash('message', 'Onboarding process deleted successfully.');
    }

    // Stepper navigation methods
    public function nextStep()
    {
        $this->validateStep();
        if ($this->currentStep < $this->totalSteps - 1) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 0) {
            $this->currentStep--;
        }
    }

    protected function validateStep()
    {
        $rules = [];

        switch ($this->currentStep) {
            case 0: // Personal Information
                $rules = [
                    'firstName' => 'nullable|string|max:100',
                    'middleName' => 'nullable|string|max:100',
                    'lastName' => 'nullable|string|max:100',
                    'dob' => 'nullable|date|before:-18 years',
                    'nationality' => 'nullable|string|max:100',
                    'nida' => 'nullable|string|max:30|unique:employees,nida_number',
                    'tin' => 'nullable|string|max:30|unique:employees,tin_number',
                    'physicalAddress' => 'nullable|string|max:255',
                    'postalAddress' => 'nullable|string|max:255',
                    'emergencyContactName' => 'nullable|string|max:255',
                    'emergencyContactRelationship' => 'nullable|string|max:100',
                    'emergencyContactPhone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
                ];
                break;

            case 1: // Educational & Professional Documents
                $rules = [
                    'cv' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                    'certificates.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'profCerts.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'transcripts.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'license' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                ];
                break;

            case 2: // Employment History & References
                $rules = [
                    'employmentLetters.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'recommendationLetters.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'reasonForLeaving' => 'nullable|string|max:1000',
                ];
                break;

            case 3: // Regulatory and Compliance
                $rules = [
                    'policeClearance' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'crbReport' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'nonConflictDeclaration' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'amlDeclaration' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'confidentialityOath' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'fitAndProperDeclaration' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'declarationOfAssets' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                ];
                break;

            case 4: // HR & Employment Contracts
                $rules = [
                    'employmentContract' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                    'jobDescription' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                    'termsAndConditions' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                    'codeOfConduct' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                    'employeeHandbook' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                ];
                break;

            case 5: // Payroll & Compensation
                $rules = [
                    'bankAccountDetails' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'taxDeclaration' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'nhifDetails' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'nssfDetails' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                ];
                break;

            case 6: // SACCOS Documentation
                $rules = [
                    'saccosApplication' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'saccosRules' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                    'saccosAgreement' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                ];
                break;

            case 7: // Supporting Files & Identification
                $rules = [
                    'passportPhotos.*' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
                    'nationalId' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'drivingLicense' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'personalInfoForm' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                ];
                break;

            case 8: // IT & Operational Setup
                $rules = [
                    'workstationId' => 'nullable|string|max:50',
                    'assignedEquipment' => 'nullable|array',
                    'assignedEquipment.*.name' => 'nullable|string|max:255',
                    'assignedEquipment.*.serial_number' => 'nullable|string|max:100',
                    'assignedEquipment.*.condition' => 'nullable|string|in:new,used,refurbished',
                    'emailCreated' => 'nullable|boolean',
                    'systemAccess' => 'nullable|boolean',
                    'idBadge' => 'nullable|boolean',
                ];
                break;
        }

        $this->validate($rules);
    }

    protected function resetForm()
    {
        $this->reset([
            'firstName', 'middleName', 'lastName', 'dob', 'nationality', 'nida', 'tin',
            'physicalAddress', 'postalAddress', 'emergencyContactName', 'emergencyContactRelationship',
            'emergencyContactPhone', 'cv', 'certificates', 'profCerts', 'transcripts', 'license',
            'employmentLetters', 'recommendationLetters', 'referees', 'reasonForLeaving', 'policeClearance',
            'crbReport', 'nonConflictDeclaration', 'amlDeclaration', 'confidentialityOath', 'fitAndProperDeclaration',
            'declarationOfAssets', 'employmentContract', 'jobDescription', 'termsAndConditions', 'codeOfConduct',
            'employeeHandbook', 'bankAccountDetails', 'taxDeclaration', 'nhifDetails', 'nssfDetails', 'saccosApplication',
            'saccosRules', 'saccosAgreement', 'passportPhotos', 'nationalId', 'drivingLicense', 'personalInfoForm',
            'workstationId', 'emailCreated', 'systemAccess', 'idBadge'
        ]);

        $this->assignedEquipment = [];
        $this->currentStep = 1;
    }

    public function addEquipment()
    {
        $this->assignedEquipment[] = [
            'name' => '',
            'serial_number' => '',
            'condition' => 'new'
        ];
    }

    public function removeEquipment($index)
    {
        if (isset($this->assignedEquipment[$index])) {
            unset($this->assignedEquipment[$index]);
            $this->assignedEquipment = array_values($this->assignedEquipment);
        }
    }

    public function handleFilesUploaded($data)
    {
        $field = $data['name'];
        $files = $data['files'];
        
        if ($field === 'assignedEquipment') {
            $this->assignedEquipment = $files;
            return;
        }

        if (is_array($files)) {
            $this->$field = $files;
        } else {
            $this->$field = [$files];
        }
    }

    public function handleFileRemoved($data)
    {
        $field = $data['name'];
        $index = $data['index'];
        
        if ($field === 'assignedEquipment') {
            if (isset($this->assignedEquipment[$index])) {
                unset($this->assignedEquipment[$index]);
                $this->assignedEquipment = array_values($this->assignedEquipment);
            }
            return;
        }

        if (is_array($this->$field)) {
            if (isset($this->$field[$index])) {
                unset($this->$field[$index]);
                $this->$field = array_values($this->$field);
            }
        } else {
            $this->$field = null;
        }
    }

    public function render()
    {
        $jobPostings = JobPosting::query()
            ->when($this->search, function($query) {
                $query->where('job_title', 'like', '%' . $this->search . '%')
                    ->orWhere('department', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatus, function($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterDepartment, function($query) {
                $query->where('department', $this->filterDepartment);
            })
            ->latest()
            ->paginate(10);

        $applicants = Applicant::with('jobPosting')
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatus, function($query) {
                $query->where('status', $this->filterStatus);
            })
            ->latest()
            ->paginate(10);

        $interviews = Interview::with(['applicant', 'interviewer'])
            ->when($this->search, function($query) {
                $query->whereHas('applicant', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, function($query) {
                $query->where('status', $this->filterStatus);
            })
            ->latest()
            ->paginate(10);

        $onboardings = Onboarding::query()
            ->with(['applicant', 'jobPosting'])
            ->when($this->search, function($query) {
                $query->whereHas('applicant', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('jobPosting', function($q) {
                    $q->where('job_title', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, function($query) {
                $query->where('status', $this->filterStatus);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.h-r.recruitment', [
            'jobPostings' => $jobPostings,
            'applicants' => $applicants,
            'interviews' => $interviews,
            'onboardings' => $onboardings,
            'departments' => Department::all(),
            'roles' => Role::all(),
            'employees' => Employee::all(),
        ]);
    }
}
