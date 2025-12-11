<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComplaintReportForTheMonthEnded extends Component
{
    public $startDate;
    public $endDate;
    public $complaints;
    public $totalComplaints;
    public $resolvedComplaints;
    public $pendingComplaints;
    public $averageResolutionTime;

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        // Fetch complaints data
        $this->complaints = DB::table('complaints')
            ->select(
                'complaints.*',
                'clients.first_name',
                'clients.last_name',
                'complaint_categories.name as category_name',
                'complaint_statuses.name as status_name'
            )
            ->leftJoin('clients', 'complaints.client_id', '=', 'clients.id')
            ->leftJoin('complaint_categories', 'complaints.category_id', '=', 'complaint_categories.id')
            ->leftJoin('complaint_statuses', 'complaints.status_id', '=', 'complaint_statuses.id')
            ->whereBetween('complaints.created_at', [$this->startDate, $this->endDate])
            ->orderBy('complaints.created_at', 'desc')
            ->get();

        // Calculate statistics
        $this->totalComplaints = $this->complaints->count();
        $this->resolvedComplaints = $this->complaints->where('status_id', 2)->count(); // Assuming 2 is the ID for resolved status
        $this->pendingComplaints = $this->complaints->where('status_id', 1)->count(); // Assuming 1 is the ID for pending status

        // Calculate average resolution time
        $resolvedComplaints = $this->complaints->where('status_id', 2);
        if ($resolvedComplaints->count() > 0) {
            $totalResolutionTime = $resolvedComplaints->sum(function ($complaint) {
                return Carbon::parse($complaint->created_at)->diffInDays($complaint->resolved_at);
            });
            $this->averageResolutionTime = round($totalResolutionTime / $resolvedComplaints->count(), 1);
        } else {
            $this->averageResolutionTime = 0;
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

    public function render()
    {
        return view('livewire.reports.complaint-report-for-the-month-ended');
    }
} 