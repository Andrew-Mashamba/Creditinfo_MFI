<?php

namespace App\Http\Livewire\Clients;

use App\Models\ClientsModel;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\BillingService;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessMemberNotifications;

class PendingApplications extends Component
{
    use WithPagination, WithFileUploads;

    // Pagination & Display
    public $perPage = 10;
    public $page = 1;
    public $totalRecords = 0;
    public $loading = false;
    public $showFilters = false;
    public $showColumnSelector = false;
    public $showExportOptions = false;
    public $showAdvancedFilters = false;
    public $showAdvancedColumns = false;
    public $generatedControlNumbers = [];

    // Search & Filter
    public $search = '';
    public $columnSearch = [];
    public $filters = [
        'status' => 'PENDING',
        'start_date' => '',
        'end_date' => '',
        'category' => '',
        'membership_type' => '',
        'gender' => '',
        'marital_status' => '',
        'nationality' => '',
        'country' => '',
        'region' => '',
        'district' => '',
        'education_level' => '',
        'employment' => '',
    ];

    // Sorting
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $multiSort = [];

    // Selection & Bulk Actions
    public $selected = [];
    public $selectAll = false;
    public $bulkAction = '';

    // Column Management
    public $columns = [
        // Primary Fields
        'account_number' => true,
        'client_number' => true,
        'member_number' => true,
        'branch_number' => true,
        
        // Personal Information
        'first_name' => true,
        'middle_name' => false,
        'last_name' => true,
        'date_of_birth' => false,
        'gender' => true,
        'marital_status' => false,
        'nationality' => false,
        'citizenship' => false,
        'country_of_birth' => false,
        'place_of_birth' => false,
        
        // Contact Information
        'phone_number' => true,
        'mobile_phone_number' => false,
        'contact_number' => false,
        'email' => true,
        'address' => false,
        'main_address' => false,
        'street' => false,
        'city' => false,
        'region' => false,
        'district' => false,
        'country' => false,
        'postal_code' => false,
        'building_number' => false,
        'number_of_building' => false,
        'ward' => false,
        
        // Business Information
        'business_name' => false,
        'trade_name' => false,
        'incorporation_number' => false,
        'registration_number' => false,
        'legal_form' => false,
        'establishment_date' => false,
        'registration_country' => false,
        'industry_sector' => false,
        
        // Financial Information
        'income_available' => false,
        'monthly_expenses' => false,
        'annual_income' => false,
        'basic_salary' => false,
        'gross_salary' => false,
        'tax_paid' => false,
        'pension' => false,
        'nhif' => false,
        'hisa' => false,
        'akiba' => false,
        'amana' => false,
        'amount' => false,
        
        // Identification
        'national_id' => false,
        'nida_number' => false,
        'tin_number' => false,
        'tax_identification_number' => false,
        'passport_number' => false,
        'driving_license_number' => false,
        'voters_id' => false,
        'custom_id_number_1' => false,
        'custom_id_number_2' => false,
        
        // Employment & Education
        'employment' => false,
        'occupation' => false,
        'employer_name' => false,
        'education' => false,
        'education_level' => false,
        
        // Family Information
        'number_of_spouse' => false,
        'number_of_children' => false,
        'dependent_count' => false,
        'present_surname' => false,
        'birth_surname' => false,
        
        // Guarantor Information
        'guarantor_first_name' => false,
        'guarantor_middle_name' => false,
        'guarantor_last_name' => false,
        'guarantor_phone' => false,
        'guarantor_email' => false,
        'guarantor_region' => false,
        'guarantor_ward' => false,
        'guarantor_district' => false,
        'guarantor_relationship' => false,
        'guarantor_membership_number' => false,
        
        // System Fields
        'status' => true,
        'membership_type' => true,
        'created_at' => true,
        'updated_at' => false,
    ];

    // Export
    public $exportFormat = 'csv';

    public $showViewModal = false;
    public $viewingMember = null;

    public $showAllDataModal = false;

    public $showEditModal = false;
    public $editingMember = null;
    public $photo;
    public $tempPhotoUrl;

    // Filter presets
    public $filterPresets = [
        'all' => 'All Pending Applications',
        'individual' => 'Individual Applications',
        'business' => 'Business Applications',
        'recent' => 'Recently Submitted',
        'old' => 'Long Pending',
    ];

    public $activeFilterPreset = 'all';

    // Approval specific modals
    public $showApprovalModal = false;
    public $showRejectionModal = false;
    public $approvingMember = null;
    public $rejectingMember = null;
    public $approvalNotes = '';
    public $rejectionReason = '';

    // Modal for block/activate/soft delete
    public $showActionModal = false;
    public $actionMember = null;
    public $actionType = null; // 'block', 'activate', 'delete'
    public $actionPassword = '';
    public $actionError = '';

    protected $listeners = [
        'refreshComponent' => '$refresh',
        'notify' => 'notify',
        'deleteMember' => 'showActionModal'
    ];

    public function mount()
    {
        $this->totalRecords = $this->getMembersQuery()->count();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->totalRecords = $this->getMembersQuery()->count();
    }

    public function updatedFilters()
    {
        $this->totalRecords = $this->getMembersQuery()->count();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleColumn($column)
    {
        if (isset($this->columns[$column])) {
            $this->columns[$column] = !$this->columns[$column];
        }
    }

    public function selectAll()
    {
        if ($this->selectAll) {
            $this->selected = $this->getMembersQuery()->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected()
    {
        $this->selectAll = false;
    }

    public function deleteSelected()
    {
        if (empty($this->selected)) {
            return;
        }

        ClientsModel::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        $this->totalRecords = $this->getMembersQuery()->count();
    }

    public function exportTable()
    {
        $this->loading = true;
        
        $members = $this->getMembersQuery()->get();
        $visibleColumns = array_keys(array_filter($this->columns));
        
        $headers = [];
        foreach ($visibleColumns as $column) {
            $headers[$column] = ucfirst(str_replace('_', ' ', $column));
        }

        switch ($this->exportFormat) {
            case 'csv':
                $this->exportToCsv($members, $visibleColumns, $headers);
                break;
            case 'excel':
                $this->exportToExcel($members, $visibleColumns, $headers);
                break;
            case 'pdf':
                $this->exportToPdf($members, $visibleColumns, $headers);
                break;
        }
        
        $this->loading = false;
    }

    private function exportToCsv($members, $visibleColumns, $headers)
    {
        $filename = 'pending_applications_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/public/exports/' . $filename);
        
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');
        
        // Write headers
        fputcsv($file, array_values($headers));
        
        // Write data
        foreach ($members as $member) {
            $row = [];
            foreach ($visibleColumns as $column) {
                $value = $member->$column;
                if ($column === 'created_at' || $column === 'updated_at') {
                    $value = $value ? $value->format('Y-m-d H:i:s') : '';
                }
                $row[] = $value;
            }
            fputcsv($file, $row);
        }
        
        fclose($file);
        
        return response()->download($filepath)->deleteFileAfterSend();
    }

    private function exportToExcel($members, $visibleColumns, $headers)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $col = 'A';
        foreach (array_values($headers) as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        
        // Set data
        $row = 2;
        foreach ($members as $member) {
            $col = 'A';
            foreach ($visibleColumns as $column) {
                $value = $member->$column;
                if ($column === 'created_at' || $column === 'updated_at') {
                    $value = $value ? $value->format('Y-m-d H:i:s') : '';
                }
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }
        
        $filename = 'pending_applications_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = storage_path('app/public/exports/' . $filename);
        
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return response()->download($filepath)->deleteFileAfterSend();
    }

    private function exportToPdf($members, $visibleColumns, $headers)
    {
        $data = [
            'members' => $members,
            'columns' => $visibleColumns,
            'headers' => $headers,
            'title' => 'Pending Applications Report'
        ];
        
        $pdf = PDF::loadView('exports.members-pdf', $data);
        
        $filename = 'pending_applications_' . date('Y-m-d_H-i-s') . '.pdf';
        
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    public function getMembersQuery()
    {
        $query = ClientsModel::where('status', 'PENDING');

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone_number', 'like', '%' . $this->search . '%')
                  ->orWhere('account_number', 'like', '%' . $this->search . '%')
                  ->orWhere('client_number', 'like', '%' . $this->search . '%')
                  ->orWhere('member_number', 'like', '%' . $this->search . '%');
            });
        }

        // Apply filters
        foreach ($this->filters as $key => $value) {
            if (!empty($value)) {
                switch ($key) {
                    case 'start_date':
                        $query->whereDate('created_at', '>=', $value);
                        break;
                    case 'end_date':
                        $query->whereDate('created_at', '<=', $value);
                        break;
                    case 'nationality':
                    case 'country':
                    case 'region':
                    case 'district':
                    case 'address':
                    case 'income_source':
                    case 'employer_name':
                    case 'business_name':
                        $query->where($key, 'like', '%' . $value . '%');
                        break;
                    default:
                        $query->where($key, $value);
                        break;
                }
            }
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query;
    }

    public function getMembers()
    {
        return $this->getMembersQuery()->paginate($this->perPage);
    }

    public function render()
    {
        $members = $this->getMembers();
        return view('livewire.clients.pending-applications', [
            'members' => $members
        ]);
    }

    public function viewMember($id)
    {
        $this->viewingMember = ClientsModel::find($id);
        $this->showViewModal = true;
    }

    public function closeModal()
    {
        $this->showViewModal = false;
        $this->viewingMember = null;
    }

    public function showAllData()
    {
        $this->showAllDataModal = true;
    }

    public function closeAllDataModal()
    {
        $this->showAllDataModal = false;
    }

    public function editMember($id)
    {
        $this->editingMember = ClientsModel::find($id);
        $this->showEditModal = true;
    }

    public function updatedPhoto()
    {
        if ($this->photo) {
            $this->tempPhotoUrl = $this->photo->temporaryUrl();
        }
    }

    public function saveMember()
    {
        $this->validate([
            'editingMember.first_name' => 'required|string|max:255',
            'editingMember.last_name' => 'required|string|max:255',
            'editingMember.email' => 'required|email|unique:clients,email,' . $this->editingMember->id,
            'editingMember.phone_number' => 'required|string|max:20',
        ]);

        if ($this->photo) {
            $filename = time() . '_' . $this->editingMember->first_name . '.' . $this->photo->getClientOriginalExtension();
            $path = $this->photo->storeAs('member_images', $filename, 'public');
            $this->editingMember->photo_url = $path;
        }

        $this->editingMember->save();
        
        $this->closeEditModal();
        $this->emit('notify', ['type' => 'success', 'message' => 'Application updated successfully!']);
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingMember = null;
        $this->photo = null;
        $this->tempPhotoUrl = null;
    }

    public function getFieldType($key)
    {
        $fieldTypes = [
            'first_name' => ['type' => 'text', 'required' => true],
            'last_name' => ['type' => 'text', 'required' => true],
            'email' => ['type' => 'email', 'required' => true],
            'phone_number' => ['type' => 'text', 'required' => true],
            'date_of_birth' => ['type' => 'date'],
            'gender' => ['type' => 'select', 'options' => ['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other']],
            'marital_status' => ['type' => 'select', 'options' => ['Single' => 'Single', 'Married' => 'Married', 'Divorced' => 'Divorced', 'Widowed' => 'Widowed']],
            'membership_type' => ['type' => 'select', 'options' => ['Individual' => 'Individual', 'Business' => 'Business'], 'disabled' => true],
            'status' => ['type' => 'select', 'options' => ['ACTIVE' => 'Active', 'PENDING' => 'Pending', 'BLOCKED' => 'Blocked'], 'disabled' => true],
            'address' => ['type' => 'textarea'],
        ];

        return $fieldTypes[$key] ?? ['type' => 'text', 'required' => false];
    }

    public function exportMemberToPDF()
    {
        if (!$this->viewingMember) {
            return;
        }

        $data = $this->organizeMemberDataForPDF($this->viewingMember);
        
        $pdf = PDF::loadView('exports.member-detail-pdf', [
            'member' => $this->viewingMember,
            'data' => $data,
            'title' => 'Application Details - ' . $this->viewingMember->first_name . ' ' . $this->viewingMember->last_name
        ]);
        
        $filename = 'application_' . $this->viewingMember->client_number . '_' . date('Y-m-d_H-i-s') . '.pdf';
        
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    private function organizeMemberDataForPDF($member)
    {
        $data = [
            'Personal Information' => [
                'First Name' => $member->first_name,
                'Middle Name' => $member->middle_name,
                'Last Name' => $member->last_name,
                'Date of Birth' => $member->date_of_birth,
                'Gender' => $member->gender,
                'Marital Status' => $member->marital_status,
                'Nationality' => $member->nationality,
                'Citizenship' => $member->citizenship,
            ],
            'Contact Information' => [
                'Email' => $member->email,
                'Phone Number' => $member->phone_number,
                'Mobile Phone' => $member->mobile_phone_number,
                'Address' => $member->address,
                'City' => $member->city,
                'Region' => $member->region,
                'District' => $member->district,
                'Country' => $member->country,
            ],
            'Application Information' => [
                'Client Number' => $member->client_number,
                'Account Number' => $member->account_number,
                'Member Number' => $member->member_number,
                'Membership Type' => $member->membership_type,
                'Status' => $member->status,
                'Application Date' => $member->created_at->format('M d, Y'),
            ],
            'Financial Information' => [
                'Income Available' => number_format($member->income_available ?? 0, 2),
                'Monthly Expenses' => number_format($member->monthly_expenses ?? 0, 2),
                'Annual Income' => number_format($member->annual_income ?? 0, 2),
                'TIN Number' => $member->tin_number,
            ],
            'Employment Information' => [
                'Employment Status' => $member->employment,
                'Occupation' => $member->occupation,
                'Employer Name' => $member->employer_name,
                'Education Level' => $member->education_level,
            ],
        ];

        // Add business information if it's a business member
        if ($member->membership_type === 'Business') {
            $data['Business Information'] = [
                'Business Name' => $member->business_name,
                'Trade Name' => $member->trade_name,
                'Incorporation Number' => $member->incorporation_number,
                'Registration Number' => $member->registration_number,
                'Legal Form' => $member->legal_form,
                'Establishment Date' => $member->establishment_date,
            ];
        }

        // Add guarantor information if available
        if ($member->guarantor_first_name || $member->guarantor_membership_number) {
            $data['Guarantor Information'] = [
                'Guarantor Name' => trim($member->guarantor_first_name . ' ' . $member->guarantor_last_name),
                'Guarantor Phone' => $member->guarantor_phone,
                'Guarantor Email' => $member->guarantor_email,
                'Relationship' => $member->guarantor_relationship,
                'Member Number' => $member->guarantor_membership_number,
            ];
        }

        return $data;
    }

    public function applyFilterPreset($preset)
    {
        $this->activeFilterPreset = $preset;
        
        switch ($preset) {
            case 'all':
                $this->filters = ['status' => 'PENDING'];
                break;
            case 'individual':
                $this->filters = ['status' => 'PENDING', 'membership_type' => 'Individual'];
                break;
            case 'business':
                $this->filters = ['status' => 'PENDING', 'membership_type' => 'Business'];
                break;
            case 'recent':
                $this->filters = ['status' => 'PENDING', 'start_date' => Carbon::now()->subDays(7)->format('Y-m-d')];
                break;
            case 'old':
                $this->filters = ['status' => 'PENDING', 'end_date' => Carbon::now()->subMonths(1)->format('Y-m-d')];
                break;
        }
        
        $this->totalRecords = $this->getMembersQuery()->count();
    }

    public function clearFilters()
    {
        $this->filters = ['status' => 'PENDING'];
        $this->activeFilterPreset = 'all';
        $this->totalRecords = $this->getMembersQuery()->count();
    }

    public function getActiveFiltersCount()
    {
        return count(array_filter($this->filters, function($value, $key) {
            return $key !== 'status' && !empty($value);
        }, ARRAY_FILTER_USE_BOTH));
    }

    public function getVisibleColumnsCount()
    {
        return count(array_filter($this->columns));
    }

    public function resetColumnsToDefault()
    {
        $this->columns = [
            'account_number' => true,
            'client_number' => true,
            'first_name' => true,
            'last_name' => true,
            'email' => true,
            'phone_number' => true,
            'status' => true,
            'membership_type' => true,
            'guarantor_membership_number' => true,
            'created_at' => true,
        ];
    }

    // Approval specific methods
    public function approveMember($id)
    {
        $this->approvingMember = ClientsModel::find($id);
        $this->showApprovalModal = true;
    }

    public function rejectMember($id)
    {
        $this->rejectingMember = ClientsModel::find($id);
        $this->showRejectionModal = true;
    }

    public function closeApprovalModal()
    {
        $this->showApprovalModal = false;
        $this->approvingMember = null;
        $this->approvalNotes = '';
    }

    public function closeRejectionModal()
    {
        $this->showRejectionModal = false;
        $this->rejectingMember = null;
        $this->rejectionReason = '';
    }

    protected function generateControlNumbers()
    {
        $billingService = new BillingService();

        // Get only SAV and DIP services (SHC bill already exists from registration)
        $services = DB::table('services')
            ->whereIn('code', ['SAV', 'DIP'])
            ->select('id', 'code', 'name', 'is_recurring', 'payment_mode', 'lower_limit')
            ->get()
            ->keyBy('code');

        $this->generatedControlNumbers = [];

        // Generate control numbers only for SAV and DIP
        foreach (['SAV', 'DIP'] as $serviceCode) {
            $service = $services[$serviceCode];
            $controlNumber = $billingService->generateControlNumber(
                $this->approvingMember->client_number,
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



    public function confirmApprove()
    {
        if ($this->approvingMember) {
            // Check if SAV and DIP bills already exist to prevent duplicates (SHC already exists from registration)
            $existingBills = DB::table('bills')
                ->where('client_number', $this->approvingMember->client_number)
                ->whereIn('service_id', function($query) {
                    $query->select('id')
                        ->from('services')
                        ->whereIn('code', ['SAV', 'DIP']);
                })
                ->exists();

            if ($existingBills) {
                Log::warning('SAV/DIP bills already exist for member, skipping bill creation', [
                    'client_number' => $this->approvingMember->client_number
                ]);

                // Just update status
                $this->approvingMember->update([
                    'status' => 'ACTIVE',
                ]);

                $this->closeApprovalModal();
                $this->totalRecords = $this->getMembersQuery()->count();
                $this->emit('notify', ['type' => 'success', 'message' => 'Application approved successfully! (Bills already exist)']);
                return;
            }

            // Update member status to ACTIVE
            $this->approvingMember->update([
                'status' => 'ACTIVE',
                //'approved_at' => now(),
                //'approved_by' => auth()->id(),
                //'approval_notes' => $this->approvalNotes
            ]);

            $client = $this->approvingMember;

            // Generate control numbers for SAV and DIP only
            $this->generateControlNumbers();

            // Create bills for SAV and DIP only (SHC already exists)
            Log::info('Creating SAV and DIP service bills for approved member', [
                'client_number' => $this->approvingMember->client_number,
                'control_numbers_count' => count($this->generatedControlNumbers)
            ]);

            foreach ($this->generatedControlNumbers as $control) {
                Log::info('Processing service bill', ['service_code' => $control['service_code']]);
                $service = DB::table('services')
                    ->where('code', $control['service_code'])
                    ->first();

                if ($service) {
                    $billingService = new BillingService();
                    $bill = $billingService->createBill(
                        $this->approvingMember->client_number,
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

            // Retrieve existing SHC bill from registration
            $shcService = DB::table('services')->where('code', 'SHC')->first();
            if ($shcService) {
                $shcBill = DB::table('bills')
                    ->where('client_number', $this->approvingMember->client_number)
                    ->where('service_id', $shcService->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($shcBill) {
                    // Add SHC bill to control numbers for notification
                    $this->generatedControlNumbers[] = [
                        'service_code' => 'SHC',
                        'control_number' => $shcBill->control_number,
                        'amount' => $shcBill->amount_due
                    ];

                    Log::info('Retrieved existing SHC bill for notification', [
                        'control_number' => $shcBill->control_number,
                        'amount' => $shcBill->amount_due
                    ]);
                } else {
                    Log::warning('SHC bill not found for member', [
                        'client_number' => $this->approvingMember->client_number
                    ]);
                }
            }

            $institution_id = DB::table('institutions')->where('id', 1)->value('institution_id');
            $saccos = preg_replace('/[^0-9]/', '', $institution_id); // Remove non-numeric characters

            // Send approval notification with all control numbers (SAV, DIP, SHC)
            ProcessMemberNotifications::dispatch($client, $this->generatedControlNumbers, env('PAYMENT_LINK') . '/' . $saccos . '/' . $this->approvingMember->client_number);

            $this->closeApprovalModal();
            $this->totalRecords = $this->getMembersQuery()->count();
            $this->emit('notify', ['type' => 'success', 'message' => 'Application approved successfully!']);
        }
    }

    public function confirmReject()
    {
        if ($this->rejectingMember) {
            $this->rejectingMember->update([
                'status' => 'REJECTED',
                'rejected_at' => now(),
                'rejected_by' => auth()->id(),
                'rejection_reason' => $this->rejectionReason
            ]);
            
            $this->closeRejectionModal();
            $this->totalRecords = $this->getMembersQuery()->count();
            $this->emit('notify', ['type' => 'success', 'message' => 'Application rejected successfully!']);
        }
    }

    public function toggleSelection($id)
    {
        $id = (string) $id;
        if (in_array($id, $this->selected)) {
            $this->selected = array_diff($this->selected, [$id]);
        } else {
            $this->selected[] = $id;
        }
    }

    public function toggleAllSelection()
    {
        if ($this->selectAll) {
            $this->selected = $this->getMembersQuery()->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selected = [];
        }
    }

    public function updateAllSelected()
    {
        $this->selectAll = count($this->selected) === $this->getMembersQuery()->count();
    }

    public function bulkApprove()
    {
        if (empty($this->selected)) {
            $this->emit('notify', ['type' => 'warning', 'message' => 'Please select applications to approve']);
            return;
        }

        ClientsModel::whereIn('id', $this->selected)->update([
            'status' => 'ACTIVE',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'approval_notes' => 'Bulk approved'
        ]);

        $this->selected = [];
        $this->selectAll = false;
        $this->totalRecords = $this->getMembersQuery()->count();
        $this->emit('notify', ['type' => 'success', 'message' => count($this->selected) . ' applications approved successfully!']);
    }

    public function bulkReject()
    {
        if (empty($this->selected)) {
            $this->emit('notify', ['type' => 'warning', 'message' => 'Please select applications to reject']);
            return;
        }

        ClientsModel::whereIn('id', $this->selected)->update([
            'status' => 'REJECTED',
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejection_reason' => 'Bulk rejected'
        ]);

        $this->selected = [];
        $this->selectAll = false;
        $this->totalRecords = $this->getMembersQuery()->count();
        $this->emit('notify', ['type' => 'success', 'message' => count($this->selected) . ' applications rejected successfully!']);
    }

    public function showActionModal($memberId)
    {
        $this->actionMember = ClientsModel::find($memberId);
        $this->showActionModal = true;
        $this->actionType = null;
        $this->actionPassword = '';
        $this->actionError = '';
    }

    public function setActionType($type)
    {
        $this->actionType = $type;
        $this->actionError = '';
    }

    public function confirmAction()
    {
        if (!$this->actionPassword) {
            $this->actionError = 'Password is required';
            return;
        }

        // Here you would typically verify the password against the authenticated user
        // For now, we'll just proceed with the action

        switch ($this->actionType) {
            case 'block':
                $this->actionMember->update(['status' => 'BLOCKED']);
                $this->emit('notify', ['type' => 'success', 'message' => 'Application blocked successfully']);
                break;
            case 'activate':
                $this->actionMember->update(['status' => 'ACTIVE']);
                $this->emit('notify', ['type' => 'success', 'message' => 'Application activated successfully']);
                break;
            case 'delete':
                $this->actionMember->delete();
                $this->emit('notify', ['type' => 'success', 'message' => 'Application deleted successfully']);
                break;
        }

        $this->closeActionModal();
        $this->totalRecords = $this->getMembersQuery()->count();
    }

    public function closeActionModal()
    {
        $this->showActionModal = false;
        $this->actionMember = null;
        $this->actionType = null;
        $this->actionPassword = '';
        $this->actionError = '';
    }

    public function notify($data)
    {
        // Handle notifications
        session()->flash('notification', $data);
    }
}
