<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VisitorLogbook extends Component
{
    use WithPagination;

    // Modal properties
    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $selectedVisitor = null;

    // Form properties
    public $visitorName = '';
    public $visitorPhone = '';
    public $visitorEmail = '';
    public $visitorIdNumber = '';
    public $visitorOrganization = '';
    public $purposeOfVisit = '';
    public $personToSee = '';
    public $department = '';
    public $visitDate = '';
    public $timeIn = '';
    public $timeOut = '';
    public $vehicleNumber = '';
    public $vehicleType = '';
    public $escortRequired = false;
    public $escortName = '';
    public $status = 'in';
    public $notes = '';

    // Search and filter properties
    public $searchTerm = '';
    public $selectedMonth = '';
    public $selectedYear = '';
    public $selectedDepartment = '';
    public $selectedStatus = '';
    public $selectedPurpose = '';

    // Options arrays
    public $departments = [
        'management' => 'Management',
        'finance' => 'Finance',
        'operations' => 'Operations',
        'hr' => 'Human Resources',
        'it' => 'Information Technology',
        'marketing' => 'Marketing',
        'legal' => 'Legal',
        'compliance' => 'Compliance',
        'customer_service' => 'Customer Service',
        'security' => 'Security',
        'maintenance' => 'Maintenance',
        'other' => 'Other'
    ];

    public $purposes = [
        'meeting' => 'Meeting',
        'consultation' => 'Consultation',
        'delivery' => 'Delivery',
        'maintenance' => 'Maintenance',
        'inspection' => 'Inspection',
        'training' => 'Training',
        'interview' => 'Interview',
        'audit' => 'Audit',
        'emergency' => 'Emergency',
        'social_visit' => 'Social Visit',
        'other' => 'Other'
    ];

    public $statuses = [
        'in' => 'Checked In',
        'out' => 'Checked Out',
        'pending' => 'Pending'
    ];

    public $vehicleTypes = [
        'car' => 'Car',
        'motorcycle' => 'Motorcycle',
        'truck' => 'Truck',
        'van' => 'Van',
        'bus' => 'Bus',
        'bicycle' => 'Bicycle',
        'other' => 'Other'
    ];

    protected $rules = [
        'visitorName' => 'required|min:2|max:255',
        'visitorPhone' => 'required|min:10|max:20',
        'visitorEmail' => 'nullable|email|max:255',
        'visitorIdNumber' => 'required|min:5|max:50',
        'visitorOrganization' => 'required|min:2|max:255',
        'purposeOfVisit' => 'required',
        'personToSee' => 'required|min:2|max:255',
        'department' => 'required',
        'visitDate' => 'required|date',
        'timeIn' => 'required',
        'timeOut' => 'nullable',
        'vehicleNumber' => 'nullable|max:20',
        'vehicleType' => 'nullable',
        'escortRequired' => 'boolean',
        'escortName' => 'nullable|max:255',
        'status' => 'required|in:in,out,pending',
        'notes' => 'nullable|max:1000'
    ];

    public function mount()
    {
        // Set default values
        $this->visitDate = now()->format('Y-m-d');
        $this->timeIn = now()->format('H:i');
        $this->selectedMonth = now()->format('m');
        $this->selectedYear = now()->format('Y');
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetForm();
    }

    public function openEditModal($visitorId)
    {
        $this->selectedVisitor = $this->getVisitorById($visitorId);
        if ($this->selectedVisitor) {
            $this->visitorName = $this->selectedVisitor['visitor_name'];
            $this->visitorPhone = $this->selectedVisitor['visitor_phone'];
            $this->visitorEmail = $this->selectedVisitor['visitor_email'] ?? '';
            $this->visitorIdNumber = $this->selectedVisitor['visitor_id_number'];
            $this->visitorOrganization = $this->selectedVisitor['visitor_organization'];
            $this->purposeOfVisit = $this->selectedVisitor['purpose_of_visit'];
            $this->personToSee = $this->selectedVisitor['person_to_see'];
            $this->department = $this->selectedVisitor['department'];
            $this->visitDate = $this->selectedVisitor['visit_date'];
            $this->timeIn = $this->selectedVisitor['time_in'];
            $this->timeOut = $this->selectedVisitor['time_out'] ?? '';
            $this->vehicleNumber = $this->selectedVisitor['vehicle_number'] ?? '';
            $this->vehicleType = $this->selectedVisitor['vehicle_type'] ?? '';
            $this->escortRequired = $this->selectedVisitor['escort_required'] ?? false;
            $this->escortName = $this->selectedVisitor['escort_name'] ?? '';
            $this->status = $this->selectedVisitor['status'];
            $this->notes = $this->selectedVisitor['notes'] ?? '';
        }
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->selectedVisitor = null;
        $this->resetForm();
    }

    public function openViewModal($visitorId)
    {
        $this->selectedVisitor = $this->getVisitorById($visitorId);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedVisitor = null;
    }

    public function openDeleteModal($visitorId)
    {
        $this->selectedVisitor = $this->getVisitorById($visitorId);
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedVisitor = null;
    }

    public function resetForm()
    {
        $this->visitorName = '';
        $this->visitorPhone = '';
        $this->visitorEmail = '';
        $this->visitorIdNumber = '';
        $this->visitorOrganization = '';
        $this->purposeOfVisit = '';
        $this->personToSee = '';
        $this->department = '';
        $this->visitDate = now()->format('Y-m-d');
        $this->timeIn = now()->format('H:i');
        $this->timeOut = '';
        $this->vehicleNumber = '';
        $this->vehicleType = '';
        $this->escortRequired = false;
        $this->escortName = '';
        $this->status = 'in';
        $this->notes = '';
        $this->resetValidation();
    }

    public function addVisitor()
    {
        $this->validate();

        try {
            $visitorData = [
                'id' => Str::uuid(),
                'visitor_name' => $this->visitorName,
                'visitor_phone' => $this->visitorPhone,
                'visitor_email' => $this->visitorEmail,
                'visitor_id_number' => $this->visitorIdNumber,
                'visitor_organization' => $this->visitorOrganization,
                'purpose_of_visit' => $this->purposeOfVisit,
                'person_to_see' => $this->personToSee,
                'department' => $this->department,
                'visit_date' => $this->visitDate,
                'time_in' => $this->timeIn,
                'time_out' => $this->timeOut,
                'vehicle_number' => $this->vehicleNumber,
                'vehicle_type' => $this->vehicleType,
                'escort_required' => $this->escortRequired,
                'escort_name' => $this->escortName,
                'status' => $this->status,
                'notes' => $this->notes,
                'created_at' => now()->toISOString(),
                'created_by' => auth()->id() ?? 'system'
            ];

            $this->saveVisitorData($visitorData);

            session()->flash('success', 'Visitor logged successfully!');
            $this->closeAddModal();

        } catch (\Exception $e) {
            session()->flash('error', 'Error logging visitor: ' . $e->getMessage());
        }
    }

    public function updateVisitor()
    {
        $this->validate();

        try {
            if ($this->selectedVisitor) {
                $this->updateVisitorData($this->selectedVisitor['id'], [
                    'visitor_name' => $this->visitorName,
                    'visitor_phone' => $this->visitorPhone,
                    'visitor_email' => $this->visitorEmail,
                    'visitor_id_number' => $this->visitorIdNumber,
                    'visitor_organization' => $this->visitorOrganization,
                    'purpose_of_visit' => $this->purposeOfVisit,
                    'person_to_see' => $this->personToSee,
                    'department' => $this->department,
                    'visit_date' => $this->visitDate,
                    'time_in' => $this->timeIn,
                    'time_out' => $this->timeOut,
                    'vehicle_number' => $this->vehicleNumber,
                    'vehicle_type' => $this->vehicleType,
                    'escort_required' => $this->escortRequired,
                    'escort_name' => $this->escortName,
                    'status' => $this->status,
                    'notes' => $this->notes,
                    'updated_at' => now()->toISOString(),
                    'updated_by' => auth()->id() ?? 'system'
                ]);

                session()->flash('success', 'Visitor information updated successfully!');
                $this->closeEditModal();
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating visitor: ' . $e->getMessage());
        }
    }

    public function checkOutVisitor($visitorId)
    {
        try {
            $this->updateVisitorData($visitorId, [
                'status' => 'out',
                'time_out' => now()->format('H:i'),
                'updated_at' => now()->toISOString(),
                'updated_by' => auth()->id() ?? 'system'
            ]);

            session()->flash('success', 'Visitor checked out successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error checking out visitor: ' . $e->getMessage());
        }
    }

    public function deleteVisitor()
    {
        try {
            if ($this->selectedVisitor) {
                $this->removeVisitorData($this->selectedVisitor['id']);
                session()->flash('success', 'Visitor record deleted successfully!');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting visitor: ' . $e->getMessage());
        }

        $this->closeDeleteModal();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedSelectedMonth()
    {
        $this->resetPage();
    }

    public function updatedSelectedYear()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatedSelectedPurpose()
    {
        $this->resetPage();
    }

    private function saveVisitorData($visitorData)
    {
        $visitorsFile = storage_path('app/visitors_data.json');
        $visitors = [];
        
        if (File::exists($visitorsFile)) {
            $visitors = json_decode(File::get($visitorsFile), true) ?? [];
        }
        
        $visitors[] = $visitorData;
        File::put($visitorsFile, json_encode($visitors, JSON_PRETTY_PRINT));
    }

    private function getVisitorsData()
    {
        $visitorsFile = storage_path('app/visitors_data.json');
        
        if (File::exists($visitorsFile)) {
            $visitors = json_decode(File::get($visitorsFile), true) ?? [];
            
            // Filter by search term
            if (!empty($this->searchTerm)) {
                $visitors = array_filter($visitors, function($visitor) {
                    return stripos($visitor['visitor_name'], $this->searchTerm) !== false ||
                           stripos($visitor['visitor_phone'], $this->searchTerm) !== false ||
                           stripos($visitor['visitor_id_number'], $this->searchTerm) !== false ||
                           stripos($visitor['visitor_organization'], $this->searchTerm) !== false ||
                           stripos($visitor['person_to_see'], $this->searchTerm) !== false ||
                           stripos($visitor['notes'], $this->searchTerm) !== false;
                });
            }
            
            // Filter by month and year
            if (!empty($this->selectedMonth) && !empty($this->selectedYear)) {
                $visitors = array_filter($visitors, function($visitor) {
                    $visitDate = Carbon::parse($visitor['visit_date']);
                    return $visitDate->format('m') === $this->selectedMonth && 
                           $visitDate->format('Y') === $this->selectedYear;
                });
            }
            
            // Filter by department
            if (!empty($this->selectedDepartment)) {
                $visitors = array_filter($visitors, function($visitor) {
                    return $visitor['department'] === $this->selectedDepartment;
                });
            }
            
            // Filter by status
            if (!empty($this->selectedStatus)) {
                $visitors = array_filter($visitors, function($visitor) {
                    return $visitor['status'] === $this->selectedStatus;
                });
            }
            
            // Filter by purpose
            if (!empty($this->selectedPurpose)) {
                $visitors = array_filter($visitors, function($visitor) {
                    return $visitor['purpose_of_visit'] === $this->selectedPurpose;
                });
            }
            
            // Sort by visit date and time (newest first)
            usort($visitors, function($a, $b) {
                $dateA = Carbon::parse($a['visit_date'] . ' ' . $a['time_in']);
                $dateB = Carbon::parse($b['visit_date'] . ' ' . $b['time_in']);
                return $dateB->timestamp - $dateA->timestamp;
            });
            
            return $visitors;
        }
        
        return [];
    }

    private function getVisitorById($visitorId)
    {
        $visitors = $this->getVisitorsData();
        foreach ($visitors as $visitor) {
            if ($visitor['id'] === $visitorId) {
                return $visitor;
            }
        }
        return null;
    }

    private function updateVisitorData($visitorId, $updateData)
    {
        $visitorsFile = storage_path('app/visitors_data.json');
        
        if (File::exists($visitorsFile)) {
            $visitors = json_decode(File::get($visitorsFile), true) ?? [];
            
            foreach ($visitors as &$visitor) {
                if ($visitor['id'] === $visitorId) {
                    $visitor = array_merge($visitor, $updateData);
                    break;
                }
            }
            
            File::put($visitorsFile, json_encode($visitors, JSON_PRETTY_PRINT));
        }
    }

    private function removeVisitorData($visitorId)
    {
        $visitorsFile = storage_path('app/visitors_data.json');
        
        if (File::exists($visitorsFile)) {
            $visitors = json_decode(File::get($visitorsFile), true) ?? [];
            $visitors = array_filter($visitors, function($visitor) use ($visitorId) {
                return $visitor['id'] !== $visitorId;
            });
            File::put($visitorsFile, json_encode(array_values($visitors), JSON_PRETTY_PRINT));
        }
    }

    public function getMonths()
    {
        return [
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December'
        ];
    }

    public function getYears()
    {
        $currentYear = now()->year;
        $years = [];
        for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
            $years[$i] = $i;
        }
        return $years;
    }

    public function getStatistics()
    {
        $visitors = $this->getVisitorsData();
        $totalVisitors = count($visitors);
        $checkedIn = count(array_filter($visitors, fn($v) => $v['status'] === 'in'));
        $checkedOut = count(array_filter($visitors, fn($v) => $v['status'] === 'out'));
        $pending = count(array_filter($visitors, fn($v) => $v['status'] === 'pending'));

        return [
            'total' => $totalVisitors,
            'checked_in' => $checkedIn,
            'checked_out' => $checkedOut,
            'pending' => $pending
        ];
    }

    public function render()
    {
        $visitors = $this->getVisitorsData();
        
        // Simple pagination for the array
        $perPage = 15;
        $currentPage = $this->page;
        $offset = ($currentPage - 1) * $perPage;
        $paginatedVisitors = array_slice($visitors, $offset, $perPage);
        
        $statistics = $this->getStatistics();
        
        return view('livewire.dashboard.visitor-logbook', [
            'visitors' => $paginatedVisitors,
            'totalVisitors' => count($visitors),
            'totalPages' => ceil(count($visitors) / $perPage),
            'statistics' => $statistics,
            'months' => $this->getMonths(),
            'years' => $this->getYears()
        ]);
    }
}
