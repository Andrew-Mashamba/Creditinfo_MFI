<?php

namespace App\Http\Livewire\HR;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeeRequest;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RequestManagement extends Component
{
    use WithPagination;
    
    // Stats
    public $totalPending = 0;
    public $totalApproved = 0;
    public $totalRejected = 0;
    public $totalProcessing = 0;
    
    // Filters
    public $search = '';
    public $filterType = '';
    public $filterStatus = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    
    // Request details modal
    public $showDetailsModal = false;
    public $selectedRequest = null;
    public $rejectionReason = '';
    
    // Request counts by type
    public $requestCounts = [];

    public function mount()
    {
        $this->loadStatistics();
    }

    public function loadStatistics()
    {
        // Overall statistics
        $this->totalPending = EmployeeRequest::where('status', EmployeeRequest::STATUS_PENDING)->count();
        $this->totalApproved = EmployeeRequest::where('status', EmployeeRequest::STATUS_APPROVED)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $this->totalRejected = EmployeeRequest::where('status', EmployeeRequest::STATUS_REJECTED)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $this->totalProcessing = EmployeeRequest::where('status', EmployeeRequest::STATUS_PROCESSING)->count();
        
        // Counts by type
        $this->requestCounts = [
            'materials' => EmployeeRequest::where('type', EmployeeRequest::TYPE_MATERIALS)
                ->where('status', EmployeeRequest::STATUS_PENDING)->count(),
            'resignation' => EmployeeRequest::where('type', EmployeeRequest::TYPE_RESIGNATION)
                ->where('status', EmployeeRequest::STATUS_PENDING)->count(),
            'travel' => EmployeeRequest::where('type', EmployeeRequest::TYPE_TRAVEL)
                ->where('status', EmployeeRequest::STATUS_PENDING)->count(),
            'advance' => EmployeeRequest::where('type', EmployeeRequest::TYPE_ADVANCE)
                ->where('status', EmployeeRequest::STATUS_PENDING)->count(),
            'training' => EmployeeRequest::where('type', EmployeeRequest::TYPE_TRAINING)
                ->where('status', EmployeeRequest::STATUS_PENDING)->count(),
            'workshop' => EmployeeRequest::where('type', EmployeeRequest::TYPE_WORKSHOP)
                ->where('status', EmployeeRequest::STATUS_PENDING)->count(),
            'overtime' => EmployeeRequest::where('type', EmployeeRequest::TYPE_OVERTIME)
                ->where('status', EmployeeRequest::STATUS_PENDING)->count(),
            'payslip' => EmployeeRequest::where('type', EmployeeRequest::TYPE_PAYSLIP)
                ->where('status', EmployeeRequest::STATUS_PENDING)->count(),
            'hr_docs' => EmployeeRequest::where('type', EmployeeRequest::TYPE_HR_DOCS)
                ->where('status', EmployeeRequest::STATUS_PENDING)->count(),
            'general' => EmployeeRequest::where('type', EmployeeRequest::TYPE_GENERAL)
                ->where('status', EmployeeRequest::STATUS_PENDING)->count(),
        ];
    }

    public function getRequests()
    {
        $query = EmployeeRequest::with(['employee', 'approver'])
            ->when($this->search, function($q) {
                $q->whereHas('employee', function($query) {
                    $query->where('first_name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%')
                          ->orWhere('employee_number', 'like', '%' . $this->search . '%');
                })
                ->orWhere('subject', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterType, function($q) {
                $q->where('type', $this->filterType);
            })
            ->when($this->filterStatus, function($q) {
                $q->where('status', $this->filterStatus);
            })
            ->when($this->filterDateFrom, function($q) {
                $q->whereDate('created_at', '>=', $this->filterDateFrom);
            })
            ->when($this->filterDateTo, function($q) {
                $q->whereDate('created_at', '<=', $this->filterDateTo);
            })
            ->orderBy('created_at', 'desc');
            
        return $query->paginate(15);
    }

    public function viewDetails($requestId)
    {
        $this->selectedRequest = EmployeeRequest::with(['employee', 'approver'])->find($requestId);
        $this->showDetailsModal = true;
    }

    public function approveRequest($requestId)
    {
        $request = EmployeeRequest::find($requestId);
        
        if ($request && $request->status === EmployeeRequest::STATUS_PENDING) {
            // Special handling for different request types
            switch ($request->type) {
                case EmployeeRequest::TYPE_RESIGNATION:
                    // Mark employee as resigned
                    $employee = $request->employee;
                    if ($employee) {
                        $employee->employee_status = 'resigned';
                        $employee->resignation_date = Carbon::now();
                        $employee->save();
                    }
                    break;
                    
                case EmployeeRequest::TYPE_OVERTIME:
                    // Could create overtime record
                    // $this->createOvertimeRecord($request);
                    break;
                    
                case EmployeeRequest::TYPE_PAYSLIP:
                    // Generate payslip
                    // $this->generatePayslip($request);
                    $request->status = EmployeeRequest::STATUS_PROCESSING;
                    $request->save();
                    session()->flash('info', 'Payslip generation initiated. Status set to processing.');
                    $this->loadStatistics();
                    return;
                    
                case EmployeeRequest::TYPE_ADVANCE:
                    // Process salary advance
                    // $this->processSalaryAdvance($request);
                    break;
            }
            
            $request->update([
                'status' => EmployeeRequest::STATUS_APPROVED,
                'approver_id' => Auth::id(),
                'approved_at' => now()
            ]);
            
            session()->flash('success', 'Request approved successfully!');
            $this->loadStatistics();
            $this->closeDetailsModal();
        }
    }

    public function rejectRequest($requestId)
    {
        $this->validate([
            'rejectionReason' => 'required|min:10'
        ]);
        
        $request = EmployeeRequest::find($requestId);
        
        if ($request && $request->status === EmployeeRequest::STATUS_PENDING) {
            $request->update([
                'status' => EmployeeRequest::STATUS_REJECTED,
                'approver_id' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $this->rejectionReason
            ]);
            
            session()->flash('info', 'Request rejected.');
            $this->rejectionReason = '';
            $this->loadStatistics();
            $this->closeDetailsModal();
        }
    }

    public function markAsProcessing($requestId)
    {
        $request = EmployeeRequest::find($requestId);
        
        if ($request && in_array($request->status, [EmployeeRequest::STATUS_PENDING, EmployeeRequest::STATUS_APPROVED])) {
            $request->update([
                'status' => EmployeeRequest::STATUS_PROCESSING,
                'approver_id' => Auth::id()
            ]);
            
            session()->flash('success', 'Request marked as processing.');
            $this->loadStatistics();
        }
    }

    public function markAsCompleted($requestId)
    {
        $request = EmployeeRequest::find($requestId);
        
        if ($request && $request->status === EmployeeRequest::STATUS_PROCESSING) {
            $request->update([
                'status' => EmployeeRequest::STATUS_COMPLETED
            ]);
            
            session()->flash('success', 'Request marked as completed.');
            $this->loadStatistics();
        }
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedRequest = null;
        $this->rejectionReason = '';
    }

    public function render()
    {
        return view('livewire.h-r.request-management', [
            'requests' => $this->getRequests()
        ]);
    }
}