<?php

namespace App\Http\Livewire\Approvals;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProcessCodeConfig;
use App\Models\Role;
use App\Models\Approval;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;

class ProcessCodeManager extends Component
{
    use WithPagination;
    public $processCode;
    public $processName;
    public $description;
    public $requiresFirstChecker = true;
    public $requiresSecondChecker = true;
    public $requiresApprover = true;
    public $firstCheckerRoles = [];
    public $secondCheckerRoles = [];
    public $approverRoles = [];
    public $minAmount;
    public $maxAmount;
    public $isActive = true;
    public $editingId;
    public $showForm = false;
    public $selectedCategory = 'all';
    public $searchTerm = '';
    public $selectedSection = 1; // default to dashboard
    
    // Dashboard stats
    public $dashboardTotalFlows = 0;
    public $dashboardActiveFlows = 0;
    public $dashboardInactiveFlows = 0;
    public $dashboardPendingCount = 0;
    public $dashboardApprovedToday = 0;
    public $dashboardRejectedToday = 0;
    public $dashboardRecentApprovals = [];
    public $pendingApprovals = [];
    public $categoryStats = [];
    public $approvalTrends = [];
    public $topApprovers = [];

    // Table sorting and pagination
    public $sortField = 'process_code';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Filtering
    public $filterStatus = 'all';
    public $filterRole = 'all';

    // Bulk actions
    public $selectedConfigs = [];
    public $selectAll = false;

    protected $rules = [
        'processCode' => 'required|string|max:50',
        'processName' => 'required|string|max:255',
        'description' => 'required|string',
        'requiresFirstChecker' => 'boolean',
        'requiresSecondChecker' => 'boolean',
        'requiresApprover' => 'boolean',
        'firstCheckerRoles' => 'nullable|array',
        'secondCheckerRoles' => 'nullable|array',
        'approverRoles' => 'nullable|array',
       
        'isActive' => 'boolean'
    ];

    protected $listeners = ['refreshProcessCodes' => '$refresh'];

    protected $queryString = [
        'sortField' => ['except' => 'process_code'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    protected $paginationTheme = 'tailwind';
    
    public function paginationView()
    {
        return 'vendor.livewire.tailwind';
    }

    public function getCategoriesProperty()
    {
        return [
            'all' => 'All Processes',
            'loan' => 'Loan Processing',
            'financial' => 'Financial Operations',
            'member' => 'Member Management',
            'expense' => 'Expense Management',
            'asset' => 'Asset Management',
            'hr' => 'Human Resources',
            'policy' => 'Policy and Compliance'
        ];
    }

    public function getFilteredConfigsProperty()
    {
        $query = ProcessCodeConfig::query();

        // Apply category filter
        if ($this->selectedCategory !== 'all') {
            $query->where(function($q) {
                switch ($this->selectedCategory) {
                    case 'loan':
                        $q->where('process_code', 'like', 'LOAN_%');
                        break;
                    case 'financial':
                        $q->whereIn('process_code', ['LARGE_WD', 'FIXED_DEP', 'FUND_TRANS']);
                        break;
                    case 'member':
                        $q->whereIn('process_code', ['NEW_MEM', 'SHARE_WD']);
                        break;
                    case 'expense':
                        $q->whereIn('process_code', ['PETTY_CASH', 'OP_EXP', 'CAP_EXP']);
                        break;
                    case 'asset':
                        $q->whereIn('process_code', ['ASSET_PUR', 'ASSET_DISP']);
                        break;
                    case 'hr':
                        $q->whereIn('process_code', ['STAFF_HIRE', 'SALARY_CHG']);
                        break;
                    case 'policy':
                        $q->whereIn('process_code', ['POLICY_CHG', 'INT_RATE']);
                        break;
                }
            });
        }

        // Apply status filter
        if ($this->filterStatus !== 'all') {
            $query->where('is_active', $this->filterStatus === 'active' ? 1 : 0);
        }

        // Apply role filter
        if ($this->filterRole !== 'all') {
            $roleId = (int)$this->filterRole;
            $query->where(function($q) use ($roleId) {
                $q->whereJsonContains('first_checker_roles', $roleId)
                  ->orWhereJsonContains('second_checker_roles', $roleId)
                  ->orWhereJsonContains('approver_roles', $roleId);
            });
        }

        // Apply search filter
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('process_code', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('process_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
            });
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        // Apply pagination
        return $query->paginate($this->perPage);
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingSortField()
    {
        $this->resetPage();
    }

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterRole()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->searchTerm = '';
        $this->selectedCategory = 'all';
        $this->filterStatus = 'all';
        $this->filterRole = 'all';
        $this->resetPage();
    }

    public function render()
    {
        $roles = Role::all();
        $configs = $this->filteredConfigs;
        $statuses = [
            'all' => 'All',
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];

        // Dashboard stats
        $this->dashboardTotalFlows = ProcessCodeConfig::count();
        $this->dashboardActiveFlows = ProcessCodeConfig::where('is_active', true)->count();
        $this->dashboardInactiveFlows = ProcessCodeConfig::where('is_active', false)->count();
        $this->dashboardPendingCount = Approval::where('process_status', 'PENDING')->count();
        $this->dashboardApprovedToday = Approval::where('process_status', 'APPROVED')
            ->whereDate('created_at', today())->count();
        $this->dashboardRejectedToday = Approval::where('process_status', 'REJECTED')
            ->whereDate('created_at', today())->count();
        
        $this->dashboardRecentApprovals = Approval::with(['user', 'processConfig'])
            ->orderBy('created_at', 'desc')->limit(5)->get();
        $this->pendingApprovals = Approval::with(['user', 'processConfig'])
            ->where('process_status', 'PENDING')
            ->orderBy('created_at', 'desc')->limit(20)->get();

        // Category statistics
        $this->categoryStats = [
            'loan' => ProcessCodeConfig::where('process_code', 'like', 'LOAN_%')->count(),
            'financial' => ProcessCodeConfig::whereIn('process_code', ['LARGE_WD', 'FIXED_DEP', 'FUND_TRANS'])->count(),
            'member' => ProcessCodeConfig::whereIn('process_code', ['NEW_MEM', 'SHARE_WD'])->count(),
            'expense' => ProcessCodeConfig::whereIn('process_code', ['PETTY_CASH', 'OP_EXP', 'CAP_EXP'])->count(),
            'asset' => ProcessCodeConfig::whereIn('process_code', ['ASSET_PUR', 'ASSET_DISP'])->count(),
            'hr' => ProcessCodeConfig::whereIn('process_code', ['STAFF_HIRE', 'SALARY_CHG'])->count(),
            'policy' => ProcessCodeConfig::whereIn('process_code', ['POLICY_CHG', 'INT_RATE'])->count(),
        ];

        // Approval trends (last 7 days)
        $this->approvalTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $this->approvalTrends[] = [
                'date' => $date->format('M d'),
                'approved' => Approval::where('process_status', 'APPROVED')->whereDate('created_at', $date)->count(),
                'pending' => Approval::where('process_status', 'PENDING')->whereDate('created_at', $date)->count(),
                'rejected' => Approval::where('process_status', 'REJECTED')->whereDate('created_at', $date)->count(),
            ];
        }

        // Top approvers this month
        $this->topApprovers = Approval::selectRaw('approver_id, COUNT(*) as approval_count')
            ->whereNotNull('approver_id')
            ->whereMonth('approved_at', now()->month)
            ->whereYear('approved_at', now()->year)
            ->groupBy('approver_id')
            ->orderByDesc('approval_count')
            ->limit(5)
            ->with('approver:id,name')
            ->get();

        return view('livewire.approvals.process-code-manager', [
            'configs' => $configs,
            'roles' => $roles,
            'categories' => $this->categories,
            'pendingCount' => $this->dashboardPendingCount,
            'recentApprovals' => $this->dashboardRecentApprovals,
            'pendingApprovals' => $this->pendingApprovals,
            'statuses' => $statuses,
            // Dashboard data
            'totalFlows' => $this->dashboardTotalFlows,
            'activeFlows' => $this->dashboardActiveFlows,
            'inactiveFlows' => $this->dashboardInactiveFlows,
            'approvedToday' => $this->dashboardApprovedToday,
            'rejectedToday' => $this->dashboardRejectedToday,
            'categoryStats' => $this->categoryStats,
            'approvalTrends' => $this->approvalTrends,
            'topApprovers' => $this->topApprovers,
        ]);
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset();
        $this->showForm = true;
    }

    public function edit($id)
    {
        $config = ProcessCodeConfig::findOrFail($id);
        $this->editingId = $id;
        $this->processCode = $config->process_code;
        $this->processName = $config->process_name;
        $this->description = $config->description;
        $this->requiresFirstChecker = $config->requires_first_checker;
        $this->requiresSecondChecker = $config->requires_second_checker;
        $this->requiresApprover = $config->requires_approver;
        $this->firstCheckerRoles = $config->first_checker_roles ?? [];
        $this->secondCheckerRoles = $config->second_checker_roles ?? [];
        $this->approverRoles = $config->approver_roles ?? [];
        $this->minAmount = $config->min_amount;
        $this->maxAmount = $config->max_amount;
        $this->isActive = $config->is_active;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'process_code' => $this->processCode,
            'process_name' => $this->processName,
            'description' => $this->description,
            'requires_first_checker' => $this->requiresFirstChecker,
            'requires_second_checker' => $this->requiresSecondChecker,
            'requires_approver' => $this->requiresApprover,
            'first_checker_roles' => $this->firstCheckerRoles,
            'second_checker_roles' => $this->secondCheckerRoles,
            'approver_roles' => $this->approverRoles,
            'min_amount' => $this->minAmount,
            'max_amount' => $this->maxAmount,
            'is_active' => $this->isActive
        ];

        if ($this->editingId) {
            ProcessCodeConfig::find($this->editingId)->update($data);
            $this->emit('notify', ['type' => 'success', 'message' => 'Process code configuration updated successfully']);
        } else {
            ProcessCodeConfig::create($data);
            $this->emit('notify', ['type' => 'success', 'message' => 'Process code configuration created successfully']);
        }

        $this->reset();
        $this->showForm = false;
    }

    public function delete($id)
    {
        ProcessCodeConfig::find($id)->delete();
        $this->emit('notify', ['type' => 'success', 'message' => 'Process code configuration deleted successfully']);
    }

    public function toggleStatus($id)
    {
        $config = ProcessCodeConfig::find($id);
        $config->is_active = !$config->is_active;
        $config->save();
        $this->emit('notify', ['type' => 'success', 'message' => 'Status updated successfully']);
    }

    public function updatedSelectedCategory()
    {
        $this->emit('refreshProcessCodes');
    }

    public function updatedSearchTerm()
    {
        $this->emit('refreshProcessCodes');
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedConfigs = $this->filteredConfigs->pluck('id')->toArray();
        } else {
            $this->selectedConfigs = [];
        }
    }

    public function updatedSelectedConfigs()
    {
        $this->selectAll = false;
    }

    public function bulkActivate()
    {
        if (empty($this->selectedConfigs)) return;
        ProcessCodeConfig::whereIn('id', $this->selectedConfigs)->update(['is_active' => true]);
        $this->emit('notify', ['type' => 'success', 'message' => 'Selected approval flows activated.']);
        $this->selectedConfigs = [];
        $this->selectAll = false;
    }

    public function bulkDeactivate()
    {
        if (empty($this->selectedConfigs)) return;
        ProcessCodeConfig::whereIn('id', $this->selectedConfigs)->update(['is_active' => false]);
        $this->emit('notify', ['type' => 'success', 'message' => 'Selected approval flows deactivated.']);
        $this->selectedConfigs = [];
        $this->selectAll = false;
    }

    public function bulkDelete()
    {
        if (empty($this->selectedConfigs)) return;
        ProcessCodeConfig::whereIn('id', $this->selectedConfigs)->delete();
        $this->emit('notify', ['type' => 'success', 'message' => 'Selected approval flows deleted.']);
        $this->selectedConfigs = [];
        $this->selectAll = false;
    }

    public function exportConfigs()
    {
        $configs = $this->filteredConfigs;
        $exportData = $configs->map(function($config) {
            return [
                'Process Code' => $config->process_code,
                'Process Name' => $config->process_name,
                'Description' => $config->description,
                'Min Amount' => $config->min_amount,
                'Max Amount' => $config->max_amount,
                'Active' => $config->is_active ? 'Yes' : 'No',
                'First Checker Roles' => collect($config->first_checker_roles)->map(function($id) { $role = \App\Models\Role::find($id); return $role ? $role->name : null; })->filter()->implode(', '),
                'Second Checker Roles' => collect($config->second_checker_roles)->map(function($id) { $role = \App\Models\Role::find($id); return $role ? $role->name : null; })->filter()->implode(', '),
                'Approver Roles' => collect($config->approver_roles)->map(function($id) { $role = \App\Models\Role::find($id); return $role ? $role->name : null; })->filter()->implode(', '),
            ];
        });
        $filename = 'approval_flows_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $callback = function() use ($exportData) {
            $out = fopen('php://output', 'w');
            if ($exportData->count()) {
                fputcsv($out, array_keys($exportData->first()));
                foreach ($exportData as $row) {
                    fputcsv($out, $row);
                }
            }
            fclose($out);
        };
        return Response::stream($callback, 200, $headers);
    }
}
