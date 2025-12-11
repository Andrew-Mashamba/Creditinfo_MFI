<?php

namespace App\Http\Livewire\Clients;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ClientsModel;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class AllMembers extends Component
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

    // Search & Filter
    public $search = '';
    public $columnSearch = [];
    public $filters = [
        'status' => '',
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
        'member_number' => false,
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
    // public $exportFormat = 'csv';

    public $showViewModal = false;
    public $viewingMember = null;

    public $showAllDataModal = false;

    public $showEditModal = false;
    public $editingMember = [];
    public $editingMemberId = null;
    public $photo;
    public $tempPhotoUrl;

    // Filter presets
    public $filterPresets = [
        'all' => 'All Members',
        'active' => 'Active Members',
        'pending' => 'Pending Members',
        'individual' => 'Individual Members',
        'business' => 'Business Members',
    ];

    public $activeFilterPreset = 'all';

    // Modal for block/activate/soft delete
    public $showActionModal = false;
    public $actionMember = null;
    public $actionType = null; // 'block', 'activate', 'delete'
    public $actionPassword = '';
    public $actionError = '';

    // protected $queryString = [
    //     'search' => ['except' => ''],
    //     'sortField' => ['except' => 'created_at'],
    //     'sortDirection' => ['except' => 'desc'],
    //     'page' => ['except' => 1],
    //     'perPage' => ['except' => 10],
    // ];

    protected $listeners = [
        'refreshTable' => '$refresh',
        'exportTable' => 'exportTable',
        'deleteSelected' => 'deleteSelected',
        'closeModal',
        'deleteMember' => 'showActionModal',
    ];

    protected $fieldTypes = [
        'client_number' => ['type' => 'text', 'required' => true],
        'first_name' => ['type' => 'text', 'required' => true],
        'last_name' => ['type' => 'text', 'required' => true],
        'phone_number' => ['type' => 'tel', 'required' => true, 'disabled' => true],
        'email' => ['type' => 'email', 'required' => false, 'disabled' => true],
        'account_number' => ['type' => 'text', 'required' => false, 'disabled' => true],
        'date_of_birth' => ['type' => 'date', 'required' => true],
        'gender' => ['type' => 'select', 'options' => ['MALE' => 'Male', 'FEMALE' => 'Female'], 'required' => true],
        'marital_status' => ['type' => 'select', 'options' => ['SINGLE' => 'Single', 'MARRIED' => 'Married', 'DIVORCED' => 'Divorced', 'WIDOWED' => 'Widowed'], 'required' => true],
        'nationality' => ['type' => 'text', 'required' => true],
        'address' => ['type' => 'textarea', 'required' => true],
        'membership_type' => ['type' => 'select', 'options' => ['ORDINARY' => 'Ordinary', 'ASSOCIATE' => 'Associate'], 'required' => true],
        'member_category' => ['type' => 'select', 'options' => ['INDIVIDUAL' => 'Individual', 'GROUP' => 'Group', 'CORPORATE' => 'Corporate'], 'required' => true],
    ];

    protected $rules = [
        'editingMember.current_team_id' => 'nullable|integer',
        'editingMember.account_number' => 'nullable|integer',
        'editingMember.client_number' => 'required|string|max:50',
        'editingMember.first_name' => 'required|string|max:100',
        'editingMember.middle_name' => 'nullable|string|max:100',
        'editingMember.last_name' => 'required|string|max:100',
        'editingMember.branch' => 'nullable|string|max:100',
        'editingMember.registering_officer' => 'nullable|string|max:100',
        'editingMember.loan_officer' => 'nullable|string|max:100',
        'editingMember.approving_officer' => 'nullable|string|max:100',
        'editingMember.membership_type' => 'required|string|max:50',
        'editingMember.incorporation_number' => 'nullable|string|max:50',
        'editingMember.phone_number' => 'required|string|max:20',
        'editingMember.mobile_phone_number' => 'nullable|string|max:20',
        'editingMember.email' => 'nullable|email|max:100',
        'editingMember.place_of_birth' => 'nullable|string|max:100',
        'editingMember.marital_status' => 'required|string|max:20',
        'editingMember.registration_date' => 'nullable|date',
        'editingMember.address' => 'required|string|max:255',
        'editingMember.notes' => 'nullable|string|max:255',
        'editingMember.profile_photo_path' => 'nullable|string|max:255',
        'editingMember.branch_id' => 'required|exists:branches,id',        
        'editingMember.next_of_kin_name' => 'nullable|string|max:100',
        'editingMember.next_of_kin_phone' => 'nullable|string|max:20',
        'editingMember.tin_number' => 'nullable|string|max:50',
        'editingMember.nida_number' => 'nullable|string|max:50',
        'editingMember.ref_number' => 'nullable|string|max:50',
        'editingMember.shares_ref_number' => 'nullable|string|max:50',
        'editingMember.nationality' => 'nullable|string|max:100',
        'editingMember.full_name' => 'nullable|string|max:255',
        'editingMember.amount' => 'nullable|numeric|between:0,999999999.99',
        'editingMember.national_id' => 'nullable|string|max:50',
        'editingMember.client_id' => 'nullable|string|max:50',
        'editingMember.customer_code' => 'nullable|string|max:50',
        'editingMember.present_surname' => 'nullable|string|max:100',
        'editingMember.birth_surname' => 'nullable|string|max:100',
        'editingMember.number_of_spouse' => 'nullable|integer|min:0',
        'editingMember.number_of_children' => 'nullable|integer|min:0',
        'editingMember.classification_of_individual' => 'nullable|string|max:50',
        'editingMember.gender' => 'required|string|max:10',
        'editingMember.date_of_birth' => 'required|date',
        'editingMember.country_of_birth' => 'nullable|string|max:100',
        'editingMember.fate_status' => 'nullable|string|max:50',
        'editingMember.social_status' => 'nullable|string|max:50',
        'editingMember.residency' => 'nullable|string|max:50',
        'editingMember.citizenship' => 'nullable|string|max:100',
        'editingMember.employment' => 'nullable|string|max:100',
        'editingMember.employer_name' => 'nullable|string|max:100',
        'editingMember.education' => 'nullable|string|max:100',
        'editingMember.business_name' => 'nullable|string|max:100',
        'editingMember.income_available' => 'nullable|numeric|between:0,999999999.99',
        'editingMember.monthly_expenses' => 'nullable|numeric|between:0,999999999.99',
        'editingMember.negative_status_of_individual' => 'nullable|string|max:50',
        'editingMember.tax_identification_number' => 'nullable|string|max:50',
        'editingMember.passport_number' => 'nullable|string|max:50',
        'editingMember.passport_issuer_country' => 'nullable|string|max:100',
        'editingMember.driving_license_number' => 'nullable|string|max:50',
        'editingMember.voters_id' => 'nullable|string|max:50',
        'editingMember.custom_id_number_1' => 'nullable|string|max:50',
        'editingMember.custom_id_number_2' => 'nullable|string|max:50',
        'editingMember.main_address' => 'nullable|string|max:255',
        'editingMember.street' => 'nullable|string|max:100',
        'editingMember.number_of_building' => 'nullable|string|max:20',
        'editingMember.postal_code' => 'nullable|string|max:20',
        'editingMember.region' => 'nullable|string|max:100',
        'editingMember.district' => 'nullable|string|max:100',
        'editingMember.country' => 'nullable|string|max:100',
        'editingMember.mobile_phone' => 'nullable|string|max:20',
        'editingMember.fixed_line' => 'nullable|string|max:20',
        'editingMember.trade_name' => 'nullable|string|max:100',
        'editingMember.legal_form' => 'nullable|string|max:50',
        'editingMember.establishment_date' => 'nullable|date',
        'editingMember.registration_country' => 'nullable|string|max:100',
        'editingMember.industry_sector' => 'nullable|string|max:100',
        'editingMember.registration_number' => 'nullable|string|max:50',
        'editingMember.middle_names' => 'nullable|string|max:100',
        'editingMember.member_number' => 'nullable|string|max:50',
        'editingMember.contact_number' => 'nullable|string|max:20',
        'editingMember.occupation' => 'nullable|string|max:100',
        'editingMember.education_level' => 'nullable|string|max:100',
        'editingMember.dependent_count' => 'nullable|integer|min:0',
        'editingMember.annual_income' => 'nullable|numeric|between:0,999999999.99',
        'editingMember.city' => 'nullable|string|max:100',
        'editingMember.status' => 'nullable|string|max:50',
        'editingMember.religion' => 'nullable|string|max:50',
        'editingMember.building_number' => 'nullable|string|max:20',
        'editingMember.ward' => 'nullable|string|max:100',
        'editingMember.accept_terms' => 'nullable|boolean',
        'editingMember.application_type' => 'nullable|string|max:50',
        'editingMember.guarantor_region' => 'nullable|string|max:100',
        'editingMember.guarantor_ward' => 'nullable|string|max:100',
        'editingMember.guarantor_district' => 'nullable|string|max:100',
        'editingMember.guarantor_relationship' => 'nullable|string|max:100',
        'editingMember.guarantor_membership_number' => 'nullable|string|max:50',
        'editingMember.guarantor_full_name' => 'nullable|string|max:100',
        'editingMember.guarantor_email' => 'nullable|string|max:100',
        'editingMember.barua' => 'nullable|string|max:10',
        'editingMember.uthibitisho' => 'nullable|string|max:10',
        'editingMember.hisa' => 'nullable|numeric|between:0,999999999.99',
        'editingMember.akiba' => 'nullable|numeric|between:0,999999999.99',
        'editingMember.amana' => 'nullable|numeric|between:0,999999999.99',
        'editingMember.guarantor_first_name' => 'nullable|string|max:100',
        'editingMember.guarantor_middle_name' => 'nullable|string|max:100',
        'editingMember.guarantor_last_name' => 'nullable|string|max:100',
        'editingMember.income_source' => 'nullable|string|max:100',
        'editingMember.share_payment_status' => 'nullable|string|max:50',
        'editingMember.basic_salary' => 'nullable|numeric|between:0,999999999.99',
        'editingMember.gross_salary' => 'nullable|numeric|between:0,999999999.99',
        'editingMember.tax_paid' => 'nullable|numeric|between:0,999999999.99',
        'editingMember.pension' => 'nullable|numeric|between:0,999999999.99',
        'editingMember.nhif' => 'nullable|numeric|between:0,999999999.99',
        'editingMember.workers_union_etc' => 'nullable|string|max:50',
        'editingMember.member_category' => 'nullable|string|max:50',
        'editingMember.guarantor_phone' => 'nullable|string|max:20',
        'editingMember.id_type' => 'nullable|string|max:50',
        'photo' => 'nullable|image|max:1024', // Max 1MB
    ];

    public function mount()
    {
        $this->totalRecords = ClientsModel::count();
    }

    public function updatingSearch()
    {
        $this->loading = true;
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->loading = true;
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->loading = false;
    }

    public function updatedFilters()
    {
        $this->loading = false;
    }

    public function sortBy($field)
    {
        // Validate that the field exists in visible columns
        if (!isset($this->columns[$field]) || !$this->columns[$field]) {
            return;
        }

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
        $this->selectAll = !$this->selectAll;
        if ($this->selectAll) {
            $this->selected = $this->getMembersQuery()->pluck('clients.id')->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected()
    {
        $this->selectAll = count($this->selected) === $this->getMembersQuery()->count();
    }

    public function deleteSelected()
    {
        if (empty($this->selected)) {
            return;
        }

        ClientsModel::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        $this->dispatchBrowserEvent('notify', ['message' => 'Selected members deleted successfully']);
    }

    public function exportTable()
    {
        try {
            // Get all members without pagination for export
            $members = $this->getMembersQuery()->get();
            
            // For Excel export, include ALL fields from the clients table
            return $this->exportAllDataToExcel($members);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Export failed: ' . $e->getMessage()
            ]);
        }
    }

    private function exportAllDataToExcel($members)
    {
        $filename = 'all_members_data_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // Create a new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Define all possible fields from clients table
        $allFields = [
            // Primary Information
            'id', 'client_number', 'member_number', 'account_number', 'membership_type', 'category', 'status',
            
            // Personal Information
            'first_name', 'middle_name', 'last_name', 'date_of_birth', 'gender', 'marital_status', 
            'nationality', 'citizenship', 'country_of_birth', 'place_of_birth',
            
            // Contact Information
            'phone_number', 'mobile_phone_number', 'contact_number', 'email', 'address', 'main_address',
            'street', 'city', 'region', 'district', 'country', 'postal_code', 'building_number',
            'number_of_building', 'ward',
            
            // Business Information
            'business_name', 'trade_name', 'incorporation_number', 'registration_number', 'legal_form',
            'establishment_date', 'registration_country', 'industry_sector',
            
            // Financial Information
            'income_available', 'monthly_expenses', 'annual_income', 'basic_salary', 'gross_salary',
            'tax_paid', 'pension', 'nhif', 'hisa', 'akiba', 'amana', 'amount',
            
            // Identification
            'national_id', 'nida_number', 'tin_number', 'tax_identification_number', 'passport_number',
            'driving_license_number', 'voters_id', 'custom_id_number_1', 'custom_id_number_2',
            
            // Employment & Education
            'employment', 'occupation', 'employer_name', 'education', 'education_level',
            
            // Family Information
            'number_of_spouse', 'number_of_children', 'dependent_count', 'present_surname', 'birth_surname',
            
            // Guarantor Information
            'guarantor_first_name', 'guarantor_middle_name', 'guarantor_last_name', 'guarantor_phone',
            'guarantor_email', 'guarantor_region', 'guarantor_ward', 'guarantor_district',
            'guarantor_relationship', 'guarantor_membership_number',
            
            // Branch Information
            'branch_number', 'branch_name', 'branch_id',
            
            // System Fields
            'created_at', 'updated_at', 'deleted_at', 'institution_id'
        ];
        
        // Create headers
        $headers = array_map(function($field) {
            return ucwords(str_replace('_', ' ', $field));
        }, $allFields);
        
        // Set headers
        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }
        
        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);
        
        // Set data
        $row = 2;
        foreach ($members as $member) {
            $col = 1;
            foreach ($allFields as $field) {
                $value = $member->$field ?? '';
                
                // Format dates
                if ($value instanceof \DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }
                
                // Format decimal numbers
                if (is_numeric($value) && (
                    strpos($field, 'amount') !== false || 
                    strpos($field, 'salary') !== false || 
                    strpos($field, 'income') !== false || 
                    strpos($field, 'expenses') !== false ||
                    strpos($field, 'hisa') !== false ||
                    strpos($field, 'akiba') !== false ||
                    strpos($field, 'amana') !== false ||
                    strpos($field, 'pension') !== false ||
                    strpos($field, 'nhif') !== false ||
                    strpos($field, 'tax_paid') !== false
                )) {
                    $value = number_format($value, 2);
                }
                
                // Handle null values
                if ($value === null) {
                    $value = '';
                }
                
                $sheet->setCellValueByColumnAndRow($col, $row, $value);
                $col++;
            }
            $row++;
        }
        
        // Auto-size columns
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }
        
        // Add borders to all data
        $dataRange = 'A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow();
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Create Excel file in memory
        $writer = new Xlsx($spreadsheet);
        
        // Return the file as a download response
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function getMembersQuery()
    {
        $query = ClientsModel::query()
            ->leftJoin('branches', 'clients.branch_id', '=', 'branches.id')
            ->select('clients.*', 'branches.branch_number', 'branches.name as branch_name');

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            
            // Get all searchable text columns from clients table
            $searchableColumns = [
                'clients.account_number',
                'clients.first_name',
                'clients.middle_name',
                'clients.middle_names',
                'clients.last_name',
                'clients.full_name',
                'clients.branch',
                'clients.registering_officer',
                'clients.loan_officer',
                'clients.approving_officer',
                'clients.membership_type',
                'clients.incorporation_number',
                'clients.phone_number',
                'clients.mobile_phone_number',
                'clients.mobile_phone',
                'clients.fixed_line',
                'clients.contact_number',
                'clients.email',
                'clients.place_of_birth',
                'clients.marital_status',
                'clients.client_number',
                'clients.member_number',
                'clients.address',
                'clients.main_address',
                'clients.street',
                'clients.building_number',
                'clients.number_of_building',
                'clients.postal_code',
                'clients.city',
                'clients.region',
                'clients.district',
                'clients.ward',
                'clients.country',
                'clients.notes',
                'clients.client_status',
                'clients.status',
                'clients.next_of_kin_name',
                'clients.next_of_kin_phone',
                'clients.tin_number',
                'clients.nida_number',
                'clients.ref_number',
                'clients.shares_ref_number',
                'clients.nationarity',
                'clients.nationality',
                'clients.citizenship',
                'clients.national_id',
                'clients.client_id',
                'clients.customer_code',
                'clients.employee_id',
                'clients.present_surname',
                'clients.birth_surname',
                'clients.classification_of_individual',
                'clients.gender',
                'clients.country_of_birth',
                'clients.fate_status',
                'clients.social_status',
                'clients.residency',
                'clients.religion',
                'clients.employment',
                'clients.employer_name',
                'clients.occupation',
                'clients.education',
                'clients.education_level',
                'clients.income_source',
                'clients.business_name',
                'clients.trade_name',
                'clients.legal_form',
                'clients.registration_country',
                'clients.industry_sector',
                'clients.registration_number',
                'clients.negative_status_of_individual',
                'clients.tax_identification_number',
                'clients.passport_number',
                'clients.passport_issuer_country',
                'clients.driving_license_number',
                'clients.voters_id',
                'clients.custom_id_number_1',
                'clients.custom_id_number_2',
                'clients.application_type',
                'clients.guarantor_first_name',
                'clients.guarantor_middle_name',
                'clients.guarantor_last_name',
                'clients.guarantor_full_name',
                'clients.guarantor_email',
                'clients.guarantor_phone',
                'clients.guarantor_relationship',
                'clients.guarantor_membership_number',
                'clients.guarantor_region',
                'clients.guarantor_ward',
                'clients.guarantor_district',
                'clients.barua',
                'clients.uthibitisho',
                'clients.share_payment_status',
                'clients.member_category',
                'clients.member_group',
                'clients.id_type',
                'clients.exit_reason',
                'clients.exit_notes',
                'clients.payment_link',
                'branches.branch_number',
                'branches.name'
            ];
            
            $query->where(function($q) use ($searchTerm, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    // Use ILIKE for case-insensitive search (PostgreSQL)
                    $q->orWhereRaw("CAST($column AS TEXT) ILIKE ?", [$searchTerm]);
                }
            });
        }

        if ($this->filters['status']) {
            $query->where('clients.status', $this->filters['status']);
        }

        if ($this->filters['start_date'] && $this->filters['end_date']) {
            $startDate = Carbon::parse($this->filters['start_date'])->startOfDay();
            $endDate = Carbon::parse($this->filters['end_date'])->endOfDay();
            $query->whereBetween('clients.created_at', [$startDate, $endDate]);
        }

        if ($this->filters['category']) {
            $query->where('clients.category', $this->filters['category']);
        }

        if ($this->filters['membership_type']) {
            $query->where('clients.membership_type', $this->filters['membership_type']);
        }

        if ($this->filters['gender']) {
            $query->where('clients.gender', $this->filters['gender']);
        }

        if ($this->filters['marital_status']) {
            $query->where('clients.marital_status', $this->filters['marital_status']);
        }

        if ($this->filters['nationality']) {
            $query->where('clients.nationality', $this->filters['nationality']);
        }

        if ($this->filters['country']) {
            $query->where('clients.country', $this->filters['country']);
        }

        if ($this->filters['region']) {
            $query->where('clients.region', $this->filters['region']);
        }

        if ($this->filters['district']) {
            $query->where('clients.district', $this->filters['district']);
        }

        if ($this->filters['education_level']) {
            $query->where('clients.education_level', $this->filters['education_level']);
        }

        if ($this->filters['employment']) {
            $query->where('clients.employment', $this->filters['employment']);
        }

        if ($this->filters['status']) {
            $query->where('clients.status', $this->filters['status']);
        }

        // Apply multi-column sorting
        foreach ($this->multiSort as $field => $direction) {
            $query->orderBy($field, $direction);
        }

        // Apply primary sort
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
        $this->totalRecords = $members->total();
        
        return view('livewire.clients.all-members', [
            'members' => $members,
            'totalRecords' => $this->totalRecords,
        ]);
    }

    public function viewMember($id)
    {
        $this->viewingMember = ClientsModel::with([
            'loans.schedules', 
            'loans.loanAccount',
            'loans.loanProduct',
            'bills.service',
            'dividends',
            'interestPayables',
            // 'accounts' => function($query) {
            //     $query->with('parentAccount');
            // }
        ])->find($id);
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
        $member = ClientsModel::find($id);

        if ($member) {
            $this->editingMemberId = $id;
            $this->editingMember = $member->toArray();
            $this->tempPhotoUrl = $member->photo_url;
            $this->showEditModal = true;
        }
    }

    public function updatedPhoto()
    {
        $this->validateOnly('photo');
        $this->tempPhotoUrl = $this->photo->temporaryUrl();
    }

    public function saveMember()
    {
        \Log::info('=== SAVE MEMBER STARTED ===', [
            'editing_member_id' => $this->editingMemberId,
            'editing_member_data' => $this->editingMember,
            'show_edit_modal' => $this->showEditModal,
        ]);

        try {
            \Log::info('Before validation');
            $this->validate();
            \Log::info('After validation - passed');

            DB::beginTransaction();
            \Log::info('Transaction started');

            // Find the member
            $member = ClientsModel::find($this->editingMemberId);
            \Log::info('Member lookup', [
                'member_id' => $this->editingMemberId,
                'member_found' => $member ? 'yes' : 'no'
            ]);

            if (!$member) {
                throw new \Exception('Member not found');
            }

            // Handle photo upload
            if ($this->photo) {
                \Log::info('Processing photo upload');
                // Delete old photo if exists
                if ($member->photo_url) {
                    Storage::delete($member->photo_url);
                }

                // Store new photo
                $path = $this->photo->store('member-photos', 'public');
                $this->editingMember['photo_url'] = $path;
                \Log::info('Photo uploaded', ['path' => $path]);
            }

            // Update member with array data (excluding timestamps and relations)
            $updateData = collect($this->editingMember)
                ->except(['id', 'created_at', 'updated_at', 'deleted_at', 'bills', 'loans', 'accounts'])
                ->filter(function ($value) {
                    return $value !== null; // Keep null values as they may be intentional
                })
                ->toArray();

            \Log::info('Update data prepared', [
                'fields_count' => count($updateData),
                'fields' => array_keys($updateData)
            ]);

            $member->update($updateData);
            \Log::info('Member updated successfully');

            DB::commit();
            \Log::info('Transaction committed');

            $this->closeEditModal();
            \Log::info('Modal closed');

            session()->flash('message', 'Member updated successfully!');

            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Member updated successfully!'
            ]);

            // Refresh the table
            $this->emit('refreshTable');

            \Log::info('=== SAVE MEMBER COMPLETED SUCCESSFULLY ===');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('=== VALIDATION FAILED ===', [
                'member_id' => $this->editingMemberId,
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);

            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Validation failed: ' . collect($e->errors())->flatten()->first()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('=== ERROR UPDATING MEMBER ===', [
                'member_id' => $this->editingMemberId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error updating member: ' . $e->getMessage()
            ]);
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingMember = [];
        $this->editingMemberId = null;
        $this->photo = null;
        $this->tempPhotoUrl = null;
        $this->resetValidation();
    }

    public function getFieldType($key)
    {
        return $this->fieldTypes[$key] ?? ['type' => 'text', 'required' => false];
    }

    public function exportMemberToPDF()
    {
        try {
            if (!$this->viewingMember) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => 'No member selected for export'
                ]);
                return;
            }

            // Organize member data into categories
            $memberData = $this->organizeMemberDataForPDF($this->viewingMember);

            $pdf = PDF::loadView('pdf.member-details', [
                'member' => $this->viewingMember,
                'memberData' => $memberData,
                'generatedAt' => now()->format('Y-m-d H:i:s'),
                'institution' => config('app.name', 'Financial Institution')
            ]);

            $filename = 'member_details_' . $this->viewingMember->client_number . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);

        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error generating PDF: ' . $e->getMessage()
            ]);
        }
    }

    private function organizeMemberDataForPDF($member)
    {
        $memberArray = $member->toArray();
        
        // Define field categories
        $categories = [
            'Personal Information' => [
                'first_name', 'middle_name', 'last_name', 'date_of_birth', 
                'gender', 'marital_status', 'nationality', 'citizenship',
                'national_id', 'nida_number', 'passport_number', 'driving_license'
            ],
            'Contact Information' => [
                'email', 'phone_number', 'mobile_phone_number', 'contact_number',
                'address', 'city', 'region', 'district', 'country', 'postal_code'
            ],
            'Employment & Financial' => [
                'employment', 'employer_name', 'job_title', 'income_source',
                'monthly_income', 'tin_number', 'business_name', 'trade_name',
                'business_address', 'business_phone', 'business_email'
            ],
            'Membership & System' => [
                'client_number', 'member_number', 'account_number', 'membership_type',
                'category', 'status', 'branch_number', 'branch_name',
                'guarantor_member_number', 'education_level'
            ]
        ];

        // Exclude fields that shouldn't be in PDF
        $excludedFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'branch_id', 'bills', 'loans', 'accounts', 'photo_url', 'institution_id'];

        $organizedData = [];
        
        // Organize data by categories
        foreach ($categories as $categoryName => $fields) {
            $categoryData = [];
            foreach ($fields as $field) {
                if (isset($memberArray[$field]) && $memberArray[$field] !== null) {
                    $categoryData[$field] = $memberArray[$field];
                }
            }
            if (!empty($categoryData)) {
                $organizedData[$categoryName] = $categoryData;
            }
        }

        // Add remaining fields to "Additional Information"
        $allCategorizedFields = array_merge(...array_values($categories));
        $additionalFields = [];
        
        foreach ($memberArray as $key => $value) {
            if (!in_array($key, $excludedFields) && 
                !in_array($key, $allCategorizedFields) && 
                $value !== null) {
                $additionalFields[$key] = $value;
            }
        }
        
        if (!empty($additionalFields)) {
            $organizedData['Additional Information'] = $additionalFields;
        }

        return $organizedData;
    }

    public function applyFilterPreset($preset)
    {
        $this->loading = true;
        $this->activeFilterPreset = $preset;
        
        switch ($preset) {
            case 'all':
                $this->filters = array_fill_keys(array_keys($this->filters), '');
                break;
            case 'active':
                $this->filters = array_fill_keys(array_keys($this->filters), '');
                $this->filters['status'] = 'ACTIVE';
                break;
            case 'pending':
                $this->filters = array_fill_keys(array_keys($this->filters), '');
                $this->filters['status'] = 'PENDING';
                break;
            case 'individual':
                $this->filters = array_fill_keys(array_keys($this->filters), '');
                $this->filters['membership_type'] = 'Individual';
                break;
            case 'business':
                $this->filters = array_fill_keys(array_keys($this->filters), '');
                $this->filters['membership_type'] = 'Business';
                break;
        }
        
        $this->resetPage();
        $this->loading = false;
    }

    public function clearFilters()
    {
        $this->loading = true;
        $this->filters = array_fill_keys(array_keys($this->filters), '');
        $this->activeFilterPreset = 'all';
        $this->resetPage();
        $this->loading = false;
    }

    public function getActiveFiltersCount()
    {
        return collect($this->filters)->filter()->count();
    }

    public function getVisibleColumnsCount()
    {
        return collect($this->columns)->filter()->count();
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
            'created_at' => true,
        ];
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
        $this->validate([
            'actionPassword' => 'required|string',
        ]);
        // Check password
        if (!\Hash::check($this->actionPassword, auth()->user()->password)) {
            $this->actionError = 'Incorrect password.';
            return;
        }
        // Approval workflow with edit package
        try {
            $processName = '';
            $processDescription = '';
            $processCode = '';
            $newStatus = '';
            
            switch ($this->actionType) {
                case 'block':
                    $processName = 'Block Member';
                    $processDescription = auth()->user()->name . ' has requested to block member: ' . $this->actionMember->getFullNameAttribute();
                    $processCode = 'CLIENT_BLACKLIST';
                    $newStatus = 'BLOCKED';
                    break;
                case 'activate':
                    $processName = 'Activate Member';
                    $processDescription = auth()->user()->name . ' has requested to activate member: ' . $this->actionMember->getFullNameAttribute();
                    $processCode = 'CLIENT_REG';
                    $newStatus = 'ACTIVE';
                    break;
                case 'delete':
                    $processName = 'Soft Delete Member';
                    $processDescription = auth()->user()->name . ' has requested to soft delete member: ' . $this->actionMember->getFullNameAttribute();
                    $processCode = 'CLIENT_BLACKLIST'; // Using CLIENT_BLACKLIST as fallback
                    $newStatus = 'DELETED';
                    break;
            }
            
            // Create edit package with old and new values
            $editPackage = [
                'status' => [
                    'old' => $this->actionMember->status,
                    'new' => $newStatus
                ]
            ];
            
            // Create approval using the existing pattern
            \App\Models\approvals::create([
                // 'institution_id' => auth()->user()->institution_id ?? '',
                'process_name' => $processName,
                'process_description' => $processDescription,
                'approval_process_description' => 'Member status change approval required',
                'process_code' => $processCode,
                'process_id' => $this->actionMember->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->id(),
                'team_id' => auth()->user()->current_team_id ?? '',
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => json_encode($editPackage)
            ]);
            
            $this->showActionModal = false;
            session()->flash('notification', [
                'type' => 'success',
                'message' => 'Approval request generated for ' . $this->actionType . ' action.'
            ]);
        } catch (\Exception $e) {
            $this->actionError = 'Failed to generate approval request: ' . $e->getMessage();
        }
    }

    public function closeActionModal()
    {
        $this->showActionModal = false;
        $this->actionMember = null;
        $this->actionType = null;
        $this->actionPassword = '';
        $this->actionError = '';
    }
}
