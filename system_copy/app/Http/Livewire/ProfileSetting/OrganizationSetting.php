<?php

namespace App\Http\Livewire\ProfileSetting;

use App\Models\approvals;

use App\Models\InstitutionsList;
use App\Models\Share;
use App\Models\SharesModel;
use App\Models\TeamUser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OrganizationSetting extends Component
{
    use WithFileUploads;

    // Public properties
    public $registration_fees;
    public $allocated_shares;
    public $available_shares;
    public $institution_name;
    public $region;
    public $wilaya;
    public $onboarding_process;
    public $settings_status;
    public $min_shares;
    public $summary_of_general_meeting;
    public $value_per_share;
    public $email;
    public $phone_number;
    public $inactivity;
    public $repayment_frequency;
    public $audit_financial_report;
    public $notes;
    public $chairperson_report;
    public $total_shares;
    public $startDate;
    public $revenue_and_expense;
    public $budget_approval_letter;
    public $openOne = false;
    public $openOne1 = false;
    public $openOne3 = false;
    public $openOne4 = false;
    public $openOne2 = false;
    public $member_category;
    public $categories;
    public $initial_shares2;
    public $repayment_date;
    public $initial_shares;
    public $petty_amount_limit;
    public $shares;
    public $institution;
    public $categoryDate = [];

    // Add the missing fileFields property
    public $fileFields = [
        'budget_approval_letter' => 'Budget Approval Letter',
        'summary_of_general_meeting' => 'Summary of General Meeting',
        'chairperson_report' => "Chairperson's Report",
        'audit_financial_report' => "External Auditor's Financial Report",
        'revenue_and_expense' => 'Revenue and Expense Analysis'
    ];

    // Validation rules
    protected $rules = [
        'institution.registration_fees' => 'required|numeric|min:0',
        'institution.region' => 'required|string|max:255',
        'institution.wilaya' => 'required|string|max:255',
        'institution.min_shares' => 'required|numeric|min:0',
        'institution.inactivity' => 'required|numeric|min:0',
        'institution.institution_name' => 'required|string|max:255',
        'institution.value_per_share' => 'required|numeric|min:0',
        'institution.manager_email' => 'required|email|max:255',
        'institution.phone_number' => 'required|string|max:20',
        'institution.settings_status' => 'nullable|boolean',
        'institution.startDate' => 'nullable|date',
        'institution.notes' => 'nullable|string|max:1000',
        'institution.initial_shares' => 'nullable|numeric|min:0',
        'institution.onboarding_process' => 'required|string|max:255',
        'institution.petty_amount_limit' => 'required|numeric|min:0',
    ];

    // Custom validation messages
    protected $messages = [
        'institution.registration_fees.required' => 'Registration fees are required',
        'institution.registration_fees.numeric' => 'Registration fees must be a number',
        'institution.region.required' => 'Region is required',
        'institution.wilaya.required' => 'Wilaya is required',
        'institution.min_shares.required' => 'Minimum shares are required',
        'institution.inactivity.required' => 'Inactivity period is required',
        'institution.institution_name.required' => 'Institution name is required',
        'institution.value_per_share.required' => 'Value per share is required',
        'institution.manager_email.required' => 'Manager email is required',
        'institution.manager_email.email' => 'Please enter a valid email address',
        'institution.phone_number.required' => 'Phone number is required',
        'institution.onboarding_process.required' => 'Onboarding process is required',
        'institution.petty_amount_limit.required' => 'Petty amount limit is required',
    ];

    public function mount()
    {
        try {
            // Initialize shares if they don't exist
            $commonShareTypes = [
                [
                    'type' => 'Ordinary Shares',
                    'summary' => 'Basic membership shares that give voting rights and dividend eligibility',
                    'value' => 1000
                ],
                [
                    'type' => 'Preference Shares',
                    'summary' => 'Shares with priority in dividend payments and capital repayment',
                    'value' => 2000
                ],
                [
                    'type' => 'Development Shares',
                    'summary' => 'Shares specifically for SACCO development and infrastructure',
                    'value' => 1500
                ],
                [
                    'type' => 'Bonus Shares',
                    'summary' => 'Additional shares issued as a bonus to existing shareholders',
                    'value' => 0
                ],
                [
                    'type' => 'Rights Shares',
                    'summary' => 'Shares offered to existing members at a preferential rate',
                    'value' => 1200
                ],
                [
                    'type' => 'Employee Shares',
                    'summary' => 'Shares allocated to SACCO employees as part of their benefits',
                    'value' => 800
                ],
                [
                    'type' => 'Special Purpose Shares',
                    'summary' => 'Shares created for specific SACCO projects or initiatives',
                    'value' => 500
                ]
            ];

            // Removed shares table references - shares functionality not needed
            $this->shares = [];
            $this->institution = \App\Models\InstitutionsList::findOrFail(1);
            
            if ($this->institution->startDate) {
                $startDate = \Carbon\Carbon::parse($this->institution->startDate);
                $this->startDate = $startDate->copy()->addYear()->endOfMonth();
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to load organization settings. Please try again.');
            Log::error('Error in OrganizationSetting mount: ' . $e->getMessage());
        }
    }

    public function saveDate($categoryId)
    {
        try {
        DB::table('member_categories')
            ->where('id', $categoryId)
                ->update(['repayment_date' => $this->categoryDate[$categoryId] ?? null]);

            session()->flash('message', 'Date updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update date. Please try again.');
        }
    }

    public function save()
    {
        try {
        // Removed shares table references - shares functionality not needed
        session()->flash('message', 'Settings saved successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update shares. Please try again.');
        }
    }

    public function institutionSetting()
{
    try {
        // Validate input
        $data = $this->validate();

        // Sanitize any arrays/objects to JSON strings
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $data[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        }

        // Find or create the institution with ID 1
        $institution = InstitutionsList::updateOrCreate(['id' => 1], $data);

        // Create an approval record
        approvals::create([
            'institution' => $institution->id,
            'process_name' => "editOrganizationSetting",
            'process_description' => 'Organization settings edited',
            'approval_process_description' => "Edit organization settings",
            'process_code' => '102',
            'process_id' => $institution->id,
            'approver_id' => Auth::id(),
            'approval_status' => 'APPROVED',
            'process_status' => 'Pending',
            'user_id' => Auth::id(),
            'team_id' => "",
            'edit_package' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        // Update local component data
        $this->institution = $institution;

        session()->flash('message', 'Settings saved successfully.');
        return redirect()->back();

    } catch (\Exception $e) {
        session()->flash('error', 'Failed to process settings: ' . $e->getMessage());
        return redirect()->back();
    }
}


    public function saveMemberCategory()
    {
        try {
        $this->validate([
            'member_category' => 'required|string|max:255',
        ]);

        DB::table('member_categories')->insert([
            'name' => $this->member_category,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->member_category = '';
            session()->flash('message', 'Member category added successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add member category. Please try again.');
        }
    }

    public function deleteCategory($id)
    {
        try {
        DB::table('member_categories')->where('id', $id)->delete();
            session()->flash('message', 'Category deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete category. Please try again.');
        }
    }

    public function store()
    {
        try {
            $file_name = '';
            $path = '';
            $file_id = '';
            $description = '';

            if ($this->budget_approval_letter) {
                $this->validate([
                    'budget_approval_letter' => 'mimes:pdf|max:1024',
                ]);
                $path = $this->budget_approval_letter->store('uploads');
                $file_name = $this->budget_approval_letter->getClientOriginalName();
                $file_id = 1;
                $description = "Budget approval letter";
            } elseif ($this->summary_of_general_meeting) {
            $this->validate([
                    'summary_of_general_meeting' => 'mimes:pdf|max:1024',
                ]);
                $path = $this->summary_of_general_meeting->store('uploads');
                $file_name = $this->summary_of_general_meeting->getClientOriginalName();
                $file_id = 2;
                $description = "Summary of the General Meeting signed by the Chairman of the Association";
            } elseif ($this->chairperson_report) {
      $this->validate([
                    'chairperson_report' => 'mimes:pdf|max:1024',
                ]);
                $path = $this->chairperson_report->store('uploads');
                $file_name = $this->chairperson_report->getClientOriginalName();
                $file_id = 3;
                $description = "Chairperson's Report";
            } elseif ($this->audit_financial_report) {
            $this->validate([
                    'audit_financial_report' => 'mimes:pdf|max:1024',
                ]);
                $path = $this->audit_financial_report->store('uploads');
                $file_name = $this->audit_financial_report->getClientOriginalName();
                $file_id = 4;
                $description = "External Auditor's Financial Report";
            } elseif ($this->revenue_and_expense) {
            $this->validate([
                    'revenue_and_expense' => 'mimes:pdf|max:1024',
                ]);
                $path = $this->revenue_and_expense->store('uploads');
                $file_name = $this->revenue_and_expense->getClientOriginalName();
                $file_id = 5;
                $description = "Supplementary vital documents for revenue and expense analysis";
            }

            if ($path) {
                DB::table('institution_files')->insert([
                    'institution_id' => 1,
                    'file_name' => $file_name,
                    'description' => $description,
                    'file_path' => $path,
                    'file_id' => $file_id,
                    'upload_date' => now(),
                ]);

                $this->reset([
                    'chairperson_report',
                    'budget_approval_letter',
                    'summary_of_general_meeting',
                    'audit_financial_report',
                    'revenue_and_expense'
                ]);

                session()->flash('message', 'File uploaded successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to upload file. Please try again.');
        }
    }

    public function deleteFile($id)
    {
        try {
            $file = DB::table('institution_files')->where('id', $id)->first();

            if ($file && Storage::exists($file->file_path)) {
                Storage::delete($file->file_path);
                DB::table('institution_files')->where('id', $id)->delete();
                session()->flash('message', 'File deleted successfully.');
            } else {
                session()->flash('error', 'File not found.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete file. Please try again.');
        }
    }

    public function download($id)
    {
        try {
            $file = DB::table('institution_files')->where('id', $id)->first();

            if ($file && Storage::exists($file->file_path)) {
                return response()->download(Storage::path($file->file_path));
            } else {
                session()->flash('error', 'File not found.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to download file. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.profile-setting.organization-setting', [
            'institution' => $this->institution,
            'shares' => $this->shares,
            'startDate' => $this->startDate,
            'categoryDate' => $this->categoryDate,
        ]);
    }

    // Toggle methods
    public function openCloseOne()
    {
        $this->openOne = !$this->openOne;
    }

    public function showMeetingSummary()
    {
        $this->openOne1 = !$this->openOne1;
    }

    public function showChairPerson()
    {
        $this->openOne2 = !$this->openOne2;
    }

    public function showAuditReport()
    {
        $this->openOne3 = !$this->openOne3;
    }

    public function showRevenueAndExpensesReport()
    {
        $this->openOne4 = !$this->openOne4;
    }
}
