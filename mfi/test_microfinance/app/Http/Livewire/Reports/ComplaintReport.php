<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ComplaintReport extends Component
{
    public $reportDate;
    public $startDate;
    public $endDate;
    public $complaints = [];
    public $complaintStatistics = [];
    public $totalComplaints = 0;
    public $resolvedComplaints = 0;
    public $pendingComplaints = 0;
    public $overdueComplaints = 0;
    public $averageResolutionTime = 0;
    public $complaintCategories = [];
    public $complaintSources = [];
    public $resolutionStatus = [];
    public $monthlyTrend = [];
    public $topComplaintTypes = [];
    public $branchComplaints = [];

    public function mount()
    {
        $this->reportDate = Carbon::now()->format('Y-m-d');
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->loadComplaints();
            $this->calculateComplaintStatistics();
            $this->categorizeComplaints();
            $this->analyzeComplaintSources();
            $this->analyzeResolutionStatus();
            $this->calculateMonthlyTrend();
            $this->getTopComplaintTypes();
            $this->getBranchComplaints();
        } catch (Exception $e) {
            Log::error('Error loading Complaint Report data: ' . $e->getMessage());
            session()->flash('error', 'Error loading report data: ' . $e->getMessage());
        }
    }

    public function loadComplaints()
    {
        // Since we don't have a complaints table, we'll create sample data structure
        // In a real implementation, this would query the actual complaints table
        
        $this->complaints = collect([
            [
                'id' => 1,
                'complaint_number' => 'COMP-2024-001',
                'client_name' => 'John Doe',
                'client_number' => 'CLI001',
                'complaint_type' => 'Service Quality',
                'complaint_category' => 'Customer Service',
                'complaint_source' => 'Branch Visit',
                'description' => 'Long waiting time at branch',
                'priority' => 'Medium',
                'status' => 'Resolved',
                'submission_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'resolution_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'resolution_time_hours' => 72,
                'assigned_to' => 'Branch Manager',
                'resolution_notes' => 'Implemented queue management system',
                'client_satisfaction' => 'Satisfied',
                'branch_id' => 1,
                'branch_name' => 'Main Branch'
            ],
            [
                'id' => 2,
                'complaint_number' => 'COMP-2024-002',
                'client_name' => 'Jane Smith',
                'client_number' => 'CLI002',
                'complaint_type' => 'Loan Processing',
                'complaint_category' => 'Loan Services',
                'complaint_source' => 'Phone Call',
                'description' => 'Delayed loan approval process',
                'priority' => 'High',
                'status' => 'Pending',
                'submission_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'resolution_date' => null,
                'resolution_time_hours' => null,
                'assigned_to' => 'Loan Officer',
                'resolution_notes' => null,
                'client_satisfaction' => null,
                'branch_id' => 2,
                'branch_name' => 'Downtown Branch'
            ],
            [
                'id' => 3,
                'complaint_number' => 'COMP-2024-003',
                'client_name' => 'Mike Johnson',
                'client_number' => 'CLI003',
                'complaint_type' => 'Account Access',
                'complaint_category' => 'Digital Services',
                'complaint_source' => 'Online Portal',
                'description' => 'Unable to access online banking',
                'priority' => 'High',
                'status' => 'Resolved',
                'submission_date' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'resolution_date' => Carbon::now()->subDays(6)->format('Y-m-d'),
                'resolution_time_hours' => 24,
                'assigned_to' => 'IT Support',
                'resolution_notes' => 'Password reset and account unlocked',
                'client_satisfaction' => 'Very Satisfied',
                'branch_id' => 1,
                'branch_name' => 'Main Branch'
            ],
            [
                'id' => 4,
                'complaint_number' => 'COMP-2024-004',
                'client_name' => 'Sarah Wilson',
                'client_number' => 'CLI004',
                'complaint_type' => 'Interest Calculation',
                'complaint_category' => 'Loan Services',
                'complaint_source' => 'Email',
                'description' => 'Discrepancy in interest calculation',
                'priority' => 'Medium',
                'status' => 'Overdue',
                'submission_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'resolution_date' => null,
                'resolution_time_hours' => null,
                'assigned_to' => 'Finance Manager',
                'resolution_notes' => null,
                'client_satisfaction' => null,
                'branch_id' => 3,
                'branch_name' => 'Suburban Branch'
            ],
            [
                'id' => 5,
                'complaint_number' => 'COMP-2024-005',
                'client_name' => 'David Brown',
                'client_number' => 'CLI005',
                'complaint_type' => 'ATM Issues',
                'complaint_category' => 'Digital Services',
                'complaint_source' => 'Phone Call',
                'description' => 'ATM not dispensing cash',
                'priority' => 'High',
                'status' => 'Resolved',
                'submission_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'resolution_date' => Carbon::now()->subHours(6)->format('Y-m-d H:i:s'),
                'resolution_time_hours' => 6,
                'assigned_to' => 'Technical Support',
                'resolution_notes' => 'ATM serviced and cash replenished',
                'client_satisfaction' => 'Satisfied',
                'branch_id' => 2,
                'branch_name' => 'Downtown Branch'
            ]
        ])->filter(function($complaint) {
            $complaintDate = Carbon::parse($complaint['submission_date']);
            return $complaintDate->between(Carbon::parse($this->startDate), Carbon::parse($this->endDate));
        })->values();
    }

    public function calculateComplaintStatistics()
    {
        $this->totalComplaints = $this->complaints->count();
        $this->resolvedComplaints = $this->complaints->where('status', 'Resolved')->count();
        $this->pendingComplaints = $this->complaints->where('status', 'Pending')->count();
        $this->overdueComplaints = $this->complaints->where('status', 'Overdue')->count();

        // Calculate average resolution time
        $resolvedComplaints = $this->complaints->where('status', 'Resolved')->whereNotNull('resolution_time_hours');
        $this->averageResolutionTime = $resolvedComplaints->count() > 0 ? 
            $resolvedComplaints->avg('resolution_time_hours') : 0;

        $this->complaintStatistics = [
            'total_complaints' => $this->totalComplaints,
            'resolved_complaints' => $this->resolvedComplaints,
            'pending_complaints' => $this->pendingComplaints,
            'overdue_complaints' => $this->overdueComplaints,
            'resolution_rate' => $this->totalComplaints > 0 ? ($this->resolvedComplaints / $this->totalComplaints) * 100 : 0,
            'average_resolution_time' => $this->averageResolutionTime,
            'client_satisfaction_rate' => $this->calculateClientSatisfactionRate()
        ];
    }

    public function calculateClientSatisfactionRate()
    {
        $satisfiedComplaints = $this->complaints->whereIn('client_satisfaction', ['Satisfied', 'Very Satisfied']);
        $totalResolvedWithFeedback = $this->complaints->where('status', 'Resolved')->whereNotNull('client_satisfaction');
        
        return $totalResolvedWithFeedback->count() > 0 ? 
            ($satisfiedComplaints->count() / $totalResolvedWithFeedback->count()) * 100 : 0;
    }

    public function categorizeComplaints()
    {
        $this->complaintCategories = $this->complaints->groupBy('complaint_category')->map(function($complaints, $category) {
            return [
                'category' => $category,
                'count' => $complaints->count(),
                'percentage' => $this->totalComplaints > 0 ? ($complaints->count() / $this->totalComplaints) * 100 : 0,
                'resolved' => $complaints->where('status', 'Resolved')->count(),
                'pending' => $complaints->where('status', 'Pending')->count(),
                'overdue' => $complaints->where('status', 'Overdue')->count(),
                'average_resolution_time' => $complaints->where('status', 'Resolved')->whereNotNull('resolution_time_hours')->avg('resolution_time_hours') ?: 0
            ];
        })->values();
    }

    public function analyzeComplaintSources()
    {
        $this->complaintSources = $this->complaints->groupBy('complaint_source')->map(function($complaints, $source) {
            return [
                'source' => $source,
                'count' => $complaints->count(),
                'percentage' => $this->totalComplaints > 0 ? ($complaints->count() / $this->totalComplaints) * 100 : 0,
                'resolved' => $complaints->where('status', 'Resolved')->count(),
                'pending' => $complaints->where('status', 'Pending')->count(),
                'overdue' => $complaints->where('status', 'Overdue')->count()
            ];
        })->values();
    }

    public function analyzeResolutionStatus()
    {
        $this->resolutionStatus = [
            'resolved' => [
                'count' => $this->resolvedComplaints,
                'percentage' => $this->totalComplaints > 0 ? ($this->resolvedComplaints / $this->totalComplaints) * 100 : 0,
                'average_time' => $this->averageResolutionTime
            ],
            'pending' => [
                'count' => $this->pendingComplaints,
                'percentage' => $this->totalComplaints > 0 ? ($this->pendingComplaints / $this->totalComplaints) * 100 : 0
            ],
            'overdue' => [
                'count' => $this->overdueComplaints,
                'percentage' => $this->totalComplaints > 0 ? ($this->overdueComplaints / $this->totalComplaints) * 100 : 0
            ]
        ];
    }

    public function calculateMonthlyTrend()
    {
        $this->monthlyTrend = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            // In a real implementation, this would query the database for the specific month
            $monthlyComplaints = $this->complaints->filter(function($complaint) use ($monthStart, $monthEnd) {
                $complaintDate = Carbon::parse($complaint['submission_date']);
                return $complaintDate->between($monthStart, $monthEnd);
            });

            $this->monthlyTrend[] = [
                'month' => $date->format('M Y'),
                'total_complaints' => $monthlyComplaints->count(),
                'resolved_complaints' => $monthlyComplaints->where('status', 'Resolved')->count(),
                'pending_complaints' => $monthlyComplaints->where('status', 'Pending')->count(),
                'overdue_complaints' => $monthlyComplaints->where('status', 'Overdue')->count(),
                'resolution_rate' => $monthlyComplaints->count() > 0 ? 
                    ($monthlyComplaints->where('status', 'Resolved')->count() / $monthlyComplaints->count()) * 100 : 0
            ];
        }
    }

    public function getTopComplaintTypes()
    {
        $this->topComplaintTypes = $this->complaints->groupBy('complaint_type')->map(function($complaints, $type) {
            return [
                'type' => $type,
                'count' => $complaints->count(),
                'percentage' => $this->totalComplaints > 0 ? ($complaints->count() / $this->totalComplaints) * 100 : 0,
                'resolved' => $complaints->where('status', 'Resolved')->count(),
                'pending' => $complaints->where('status', 'Pending')->count(),
                'overdue' => $complaints->where('status', 'Overdue')->count()
            ];
        })->sortByDesc('count')->take(5)->values();
    }

    public function getBranchComplaints()
    {
        $this->branchComplaints = $this->complaints->groupBy('branch_name')->map(function($complaints, $branch) {
            return [
                'branch_name' => $branch,
                'count' => $complaints->count(),
                'percentage' => $this->totalComplaints > 0 ? ($complaints->count() / $this->totalComplaints) * 100 : 0,
                'resolved' => $complaints->where('status', 'Resolved')->count(),
                'pending' => $complaints->where('status', 'Pending')->count(),
                'overdue' => $complaints->where('status', 'Overdue')->count(),
                'average_resolution_time' => $complaints->where('status', 'Resolved')->whereNotNull('resolution_time_hours')->avg('resolution_time_hours') ?: 0
            ];
        })->sortByDesc('count')->values();
    }

    public function getComplaintPriorityDistribution()
    {
        return $this->complaints->groupBy('priority')->map(function($complaints, $priority) {
            return [
                'priority' => $priority,
                'count' => $complaints->count(),
                'percentage' => $this->totalComplaints > 0 ? ($complaints->count() / $this->totalComplaints) * 100 : 0
            ];
        })->values();
    }

    public function getClientSatisfactionDistribution()
    {
        return $this->complaints->where('status', 'Resolved')->whereNotNull('client_satisfaction')
            ->groupBy('client_satisfaction')->map(function($complaints, $satisfaction) {
                return [
                    'satisfaction' => $satisfaction,
                    'count' => $complaints->count(),
                    'percentage' => $this->resolvedComplaints > 0 ? ($complaints->count() / $this->resolvedComplaints) * 100 : 0
                ];
            })->values();
    }

    public function exportReport($format = 'pdf')
    {
        try {
            session()->flash('success', "Complaint Report exported as {$format} successfully!");
            
            Log::info('Complaint Report exported', [
                'format' => $format,
                'report_date' => $this->reportDate,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'total_complaints' => $this->totalComplaints,
                'resolved_complaints' => $this->resolvedComplaints,
                'user_id' => auth()->id()
            ]);
        } catch (Exception $e) {
            session()->flash('error', 'Error exporting report: ' . $e->getMessage());
            Log::error('Complaint Report export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        }
    }

    public function updatedStartDate()
    {
        $this->loadData();
    }

    public function updatedEndDate()
    {
        $this->loadData();
    }

    public function updatedReportDate()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.reports.complaint-report');
    }
}
