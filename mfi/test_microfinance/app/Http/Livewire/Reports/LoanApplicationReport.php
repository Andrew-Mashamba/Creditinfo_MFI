<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\BranchesModel;
use App\Models\LoanSubProduct;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Maatwebsite\Excel\Facades\Excel;

class LoanApplicationReport extends Component
{
    public $reportStartDate;
    public $reportEndDate;
    public $statusFilter = '';
    public $applications = [];
    public $totalApplications = 0;
    public $approvedApplications = 0;
    public $pendingApplications = 0;
    public $rejectedApplications = 0;
    public $totalApplicationAmount = 0;
    public $averageApplicationAmount = 0;
    public $approvalRate = 0;
    public $isExportingPdf = false;
    public $isExportingExcel = false;

    public function mount()
    {
        $this->reportStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->reportEndDate = Carbon::now()->format('Y-m-d');
        $this->loadApplications();
        $this->calculateSummary();
    }

    protected function rules()
    {
        return [
            'reportStartDate' => 'required|date|before_or_equal:reportEndDate',
            'reportEndDate' => 'required|date|after_or_equal:reportStartDate|before_or_equal:today',
            'statusFilter' => 'nullable|in:PENDING,APPROVED,REJECTED,ACTIVE,COMPLETED'
        ];
    }

    protected function messages()
    {
        return [
            'reportStartDate.required' => 'Start date is required.',
            'reportStartDate.before_or_equal' => 'Start date must be before or equal to end date.',
            'reportEndDate.required' => 'End date is required.',
            'reportEndDate.after_or_equal' => 'End date must be after or equal to start date.',
            'reportEndDate.before_or_equal' => 'End date cannot be in the future.',
            'statusFilter.in' => 'Invalid status filter selected.'
        ];
    }

    public function loadApplications()
    {
        try {
            // Validate input data
            $this->validate();

            $startDate = Carbon::parse($this->reportStartDate)->startOfDay();
            $endDate = Carbon::parse($this->reportEndDate)->endOfDay();

        // Build base query with proper relationships to avoid N+1 queries
        $query = LoansModel::query()
            ->with([
                'client:id,client_number,first_name,middle_name,last_name',
                'loanBranch:id,name',
                'loanProduct:product_id,sub_product_name'
            ])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        // Limit results to prevent performance issues with large datasets
        $this->applications = $query->limit(1000)->get()->map(function ($application) {
            // Get client name using relationship
            $application->client_name = $application->client 
                ? trim($application->client->first_name . ' ' . $application->client->middle_name . ' ' . $application->client->last_name)
                : 'N/A';

            // Get branch name using relationship
            $application->branch_name = $application->loanBranch ? $application->loanBranch->name : 'N/A';

            // Get loan product name using relationship
            $application->loan_product_name = $application->loanProduct ? $application->loanProduct->sub_product_name : 'N/A';

            // Format application date
            $application->application_date = $application->created_at ? $application->created_at->format('Y-m-d') : 'N/A';

            // Calculate processing days
            if ($application->created_at && $application->updated_at) {
                $application->processing_days = $application->created_at->diffInDays($application->updated_at);
            } else {
                $application->processing_days = null;
            }

            return $application;
        });

        } catch (\Exception $e) {
            Log::error('Error loading loan applications', [
                'error' => $e->getMessage(),
                'start_date' => $this->reportStartDate,
                'end_date' => $this->reportEndDate,
                'status_filter' => $this->statusFilter,
                'user_id' => auth()->id()
            ]);
            
            session()->flash('error', 'Error loading loan applications: ' . $e->getMessage());
            $this->applications = collect();
        }
    }

    public function calculateSummary()
    {
        try {
            $startDate = Carbon::parse($this->reportStartDate)->startOfDay();
            $endDate = Carbon::parse($this->reportEndDate)->endOfDay();

            // Build query for summary calculations
            $query = LoansModel::query()
                ->whereBetween('created_at', [$startDate, $endDate]);

            if (!empty($this->statusFilter)) {
                $query->where('status', $this->statusFilter);
            }

            // Get summary statistics efficiently
            $this->totalApplications = $query->count();
            $this->approvedApplications = $query->where('status', 'APPROVED')->count();
            $this->pendingApplications = $query->where('status', 'PENDING')->count();
            $this->rejectedApplications = $query->where('status', 'REJECTED')->count();
            $this->totalApplicationAmount = $query->sum(DB::raw('CAST(principle AS DECIMAL(15,2))'));
            $this->averageApplicationAmount = $this->totalApplications > 0 ? $this->totalApplicationAmount / $this->totalApplications : 0;
            $this->approvalRate = $this->totalApplications > 0 ? round(($this->approvedApplications / $this->totalApplications) * 100, 2) : 0;

        } catch (\Exception $e) {
            Log::error('Error calculating summary', [
                'error' => $e->getMessage(),
                'start_date' => $this->reportStartDate,
                'end_date' => $this->reportEndDate,
                'status_filter' => $this->statusFilter,
                'user_id' => auth()->id()
            ]);
            
            // Reset all values to 0 on error
            $this->totalApplications = 0;
            $this->approvedApplications = 0;
            $this->pendingApplications = 0;
            $this->rejectedApplications = 0;
            $this->totalApplicationAmount = 0;
            $this->averageApplicationAmount = 0;
            $this->approvalRate = 0;
        }
    }

    public function refreshData()
    {
        $this->loadApplications();
        $this->calculateSummary();
        
        session()->flash('success', 'Data refreshed successfully!');
    }

    public function exportPdf()
    {
        $this->isExportingPdf = true;
        
        try {
            // Validate input data
            $this->validate();
            
            // Get the data for export
            $startDate = Carbon::parse($this->reportStartDate)->startOfDay();
            $endDate = Carbon::parse($this->reportEndDate)->endOfDay();

            $query = LoansModel::query()
                ->with([
                    'client:id,client_number,first_name,middle_name,last_name',
                    'loanBranch:id,name',
                    'loanProduct:product_id,sub_product_name'
                ])
                ->whereBetween('created_at', [$startDate, $endDate]);

            if (!empty($this->statusFilter)) {
                $query->where('status', $this->statusFilter);
            }

            $applications = $query->limit(1000)->get()->map(function ($application) {
                $application->client_name = $application->client 
                    ? trim($application->client->first_name . ' ' . $application->client->middle_name . ' ' . $application->client->last_name)
                    : 'N/A';
                $application->branch_name = $application->loanBranch ? $application->loanBranch->name : 'N/A';
                $application->loan_product_name = $application->loanProduct ? $application->loanProduct->sub_product_name : 'N/A';
                $application->application_date = $application->created_at ? $application->created_at->format('Y-m-d') : 'N/A';
                $application->processing_days = $application->created_at && $application->updated_at 
                    ? $application->created_at->diffInDays($application->updated_at) 
                    : null;
                return $application;
            });

            // Generate PDF using Laravel's PDF package
            $pdf = PDF::loadView('pdf.loan-application-report', [
                'applications' => $applications,
                'startDate' => $this->reportStartDate,
                'endDate' => $this->reportEndDate,
                'statusFilter' => $this->statusFilter,
                'totalApplications' => $this->totalApplications,
                'approvedApplications' => $this->approvedApplications,
                'pendingApplications' => $this->pendingApplications,
                'rejectedApplications' => $this->rejectedApplications,
                'totalApplicationAmount' => $this->totalApplicationAmount,
                'averageApplicationAmount' => $this->averageApplicationAmount,
                'approvalRate' => $this->approvalRate,
            ]);

            $filename = 'loan-application-report-' . $this->reportStartDate . '-to-' . $this->reportEndDate . '.pdf';
            
            Log::info('Loan Application Report PDF exported', [
                'format' => 'pdf',
                'start_date' => $this->reportStartDate,
                'end_date' => $this->reportEndDate,
                'status_filter' => $this->statusFilter,
                'user_id' => auth()->id(),
                'record_count' => $applications->count()
            ]);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (Exception $e) {
            session()->flash('error', 'Error exporting PDF: ' . $e->getMessage());
            Log::error('Loan Application Report PDF export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isExportingPdf = false;
        }
    }

    public function exportExcel()
    {
        $this->isExportingExcel = true;
        
        try {
            // Validate input data
            $this->validate();
            
            // Get the data for export
            $startDate = Carbon::parse($this->reportStartDate)->startOfDay();
            $endDate = Carbon::parse($this->reportEndDate)->endOfDay();

            $query = LoansModel::query()
                ->with([
                    'client:id,client_number,first_name,middle_name,last_name',
                    'loanBranch:id,name',
                    'loanProduct:product_id,sub_product_name'
                ])
                ->whereBetween('created_at', [$startDate, $endDate]);

            if (!empty($this->statusFilter)) {
                $query->where('status', $this->statusFilter);
            }

            $applications = $query->limit(1000)->get()->map(function ($application) {
                $application->client_name = $application->client 
                    ? trim($application->client->first_name . ' ' . $application->client->middle_name . ' ' . $application->client->last_name)
                    : 'N/A';
                $application->branch_name = $application->loanBranch ? $application->loanBranch->name : 'N/A';
                $application->loan_product_name = $application->loanProduct ? $application->loanProduct->sub_product_name : 'N/A';
                $application->application_date = $application->created_at ? $application->created_at->format('Y-m-d') : 'N/A';
                $application->processing_days = $application->created_at && $application->updated_at 
                    ? $application->created_at->diffInDays($application->updated_at) 
                    : null;
                return $application;
            });

            // Generate Excel using Laravel Excel package
            $filename = 'loan-application-report-' . $this->reportStartDate . '-to-' . $this->reportEndDate . '.xlsx';
            
            Log::info('Loan Application Report Excel exported', [
                'format' => 'excel',
                'start_date' => $this->reportStartDate,
                'end_date' => $this->reportEndDate,
                'status_filter' => $this->statusFilter,
                'user_id' => auth()->id(),
                'record_count' => $applications->count()
            ]);

            return Excel::download(new \App\Exports\LoanApplicationReportExport($applications, [
                'startDate' => $this->reportStartDate,
                'endDate' => $this->reportEndDate,
                'statusFilter' => $this->statusFilter,
                'totalApplications' => $this->totalApplications,
                'approvedApplications' => $this->approvedApplications,
                'pendingApplications' => $this->pendingApplications,
                'rejectedApplications' => $this->rejectedApplications,
                'totalApplicationAmount' => $this->totalApplicationAmount,
                'averageApplicationAmount' => $this->averageApplicationAmount,
                'approvalRate' => $this->approvalRate,
            ]), $filename);

        } catch (Exception $e) {
            session()->flash('error', 'Error exporting Excel: ' . $e->getMessage());
            Log::error('Loan Application Report Excel export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isExportingExcel = false;
        }
    }

    public function render()
    {
        return view('livewire.reports.loan-application-report');
    }
}