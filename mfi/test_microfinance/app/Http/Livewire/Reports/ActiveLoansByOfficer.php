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
use App\Models\Employee;
use App\Exports\ActiveLoansByOfficerExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ActiveLoansByOfficer extends Component
{
    public $selectedOfficer = '';
    public $loans = [];
    public $officers = [];
    public $totalLoans = 0;
    public $totalLoanAmount = 0;
    public $overdueLoans = 0;
    public $activeOfficers = 0;
    public $officerLoans = 0;
    public $officerLoanAmount = 0;
    public $officerOverdueLoans = 0;
    public $isExportingPdf = false;
    public $isExportingExcel = false;

    public function mount()
    {
        $this->loadOfficers();
        $this->loadLoans();
        $this->calculateSummary();
    }

    public function loadOfficers()
    {
        $this->officers = Employee::whereHas('loans')->get();
    }

    public function loadLoans()
    {
        $query = LoansModel::query()->where('status', 'ACTIVE');

        if (!empty($this->selectedOfficer)) {
            $query->where('supervisor_id', $this->selectedOfficer);
        }

        $this->loans = $query->get()->map(function ($loan) {
            // Get member name
            $member = ClientsModel::find($loan->client_id);
            $loan->member_name = $member ? trim($member->first_name . ' ' . $member->middle_name . ' ' . $member->last_name) : 'N/A';

            // Get guarantor name
            $guarantor = ClientsModel::where('client_number', $loan->guarantor)->first();
            $loan->guarantor_name = $guarantor ? trim($guarantor->first_name . ' ' . $guarantor->middle_name . ' ' . $guarantor->last_name) : 'N/A';

            // Get branch name
            $branch = BranchesModel::find($loan->branch_id);
            $loan->branch_name = $branch ? $branch->name : 'N/A';

            // Get officer name
            $officer = Employee::find($loan->supervisor_id);
            $loan->officer_name = $officer ? trim($officer->first_name . ' ' . $officer->middle_name . ' ' . $officer->last_name) : 'N/A';

            return $loan;
        });
    }

    public function loadOfficerData()
    {
        $this->loadLoans();
        $this->calculateOfficerSummary();
    }

    public function calculateSummary()
    {
        $this->totalLoans = LoansModel::where('status', 'ACTIVE')->count();
        $this->totalLoanAmount = LoansModel::where('status', 'ACTIVE')->sum('principle');
        $this->overdueLoans = LoansModel::where('status', 'ACTIVE')->where('days_in_arrears', '>', 0)->count();
        $this->activeOfficers = Employee::whereHas('loans', function($query) {
            $query->where('status', 'ACTIVE');
        })->count();
    }

    public function calculateOfficerSummary()
    {
        if (empty($this->selectedOfficer)) {
            $this->officerLoans = 0;
            $this->officerLoanAmount = 0;
            $this->officerOverdueLoans = 0;
            return;
        }

        $this->officerLoans = LoansModel::where('status', 'ACTIVE')->where('supervisor_id', $this->selectedOfficer)->count();
        $this->officerLoanAmount = LoansModel::where('status', 'ACTIVE')->where('supervisor_id', $this->selectedOfficer)->sum('principle');
        $this->officerOverdueLoans = LoansModel::where('status', 'ACTIVE')->where('supervisor_id', $this->selectedOfficer)->where('days_in_arrears', '>', 0)->count();
    }

    public function refreshData()
    {
        $this->loadOfficers();
        $this->loadLoans();
        $this->calculateSummary();
        $this->calculateOfficerSummary();
        
        session()->flash('success', 'Data refreshed successfully!');
    }

    public function exportReport($format = 'pdf')
    {
        if ($format === 'pdf') {
            return $this->exportToPDF();
        } elseif ($format === 'excel') {
            return $this->exportToExcel();
        }
        
        session()->flash('error', 'Invalid export format specified.');
    }

    public function exportToPDF()
    {
        $this->isExportingPdf = true;

        try {
            // Validate user permissions for export
            if (!auth()->check()) {
                throw new Exception('User authentication required for export');
            }

            // Ensure we have current data
            $this->loadLoans();
            $this->calculateSummary();
            $this->calculateOfficerSummary();

            // Validate report data exists
            if (empty($this->loans)) {
                throw new Exception('No loan data available for export. Please ensure there are active loans in the system.');
            }

            $filename = 'active_loans_by_officer_report_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            
            // Get officer name if filtered
            $officerName = null;
            if (!empty($this->selectedOfficer)) {
                $officer = Employee::find($this->selectedOfficer);
                $officerName = $officer ? trim($officer->first_name . ' ' . $officer->middle_name . ' ' . $officer->last_name) : null;
            }
            
            Log::info('Active Loans by Officer Report exported as PDF', [
                'format' => 'pdf',
                'user_id' => auth()->id(),
                'loans_count' => count($this->loans),
                'officer_id' => $this->selectedOfficer,
                'officer_name' => $officerName
            ]);
            
            session()->flash('success', 'Active Loans by Officer Report exported as PDF successfully!');
            
            // Generate and download PDF
            return $this->generatePDFDownload($filename, $officerName);
            
        } catch (Exception $e) {
            session()->flash('error', 'Error exporting PDF: ' . $e->getMessage());
            Log::error('Active Loans by Officer Report PDF export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isExportingPdf = false;
        }
    }

    public function exportToExcel()
    {
        $this->isExportingExcel = true;

        try {
            // Validate user permissions for export
            if (!auth()->check()) {
                throw new Exception('User authentication required for export');
            }

            // Ensure we have current data
            $this->loadLoans();
            $this->calculateSummary();
            $this->calculateOfficerSummary();

            // Validate report data exists
            if (empty($this->loans)) {
                throw new Exception('No loan data available for export. Please ensure there are active loans in the system.');
            }

            $filename = 'active_loans_by_officer_report_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            
            // Get officer name if filtered
            $officerName = null;
            if (!empty($this->selectedOfficer)) {
                $officer = Employee::find($this->selectedOfficer);
                $officerName = $officer ? trim($officer->first_name . ' ' . $officer->middle_name . ' ' . $officer->last_name) : null;
            }
            
            // Prepare summary data
            $summary = [
                'totalLoans' => $this->totalLoans,
                'totalLoanAmount' => $this->totalLoanAmount,
                'overdueLoans' => $this->overdueLoans,
                'activeOfficers' => $this->activeOfficers,
                'officerLoans' => $this->officerLoans,
                'officerLoanAmount' => $this->officerLoanAmount,
                'officerOverdueLoans' => $this->officerOverdueLoans,
            ];
            
            Log::info('Active Loans by Officer Report exported as Excel', [
                'format' => 'excel',
                'user_id' => auth()->id(),
                'loans_count' => count($this->loans),
                'officer_id' => $this->selectedOfficer,
                'officer_name' => $officerName
            ]);
            
            session()->flash('success', 'Active Loans by Officer Report exported as Excel successfully!');
            
            // Generate and download Excel
            return $this->generateExcelDownload($filename, $summary, $officerName);
            
        } catch (Exception $e) {
            session()->flash('error', 'Error exporting Excel: ' . $e->getMessage());
            Log::error('Active Loans by Officer Report Excel export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isExportingExcel = false;
        }
    }

    private function generatePDFDownload($filename, $officerName = null)
    {
        // Prepare summary data
        $summary = [
            'totalLoans' => $this->totalLoans,
            'totalLoanAmount' => $this->totalLoanAmount,
            'overdueLoans' => $this->overdueLoans,
            'activeOfficers' => $this->activeOfficers,
            'officerLoans' => $this->officerLoans,
            'officerLoanAmount' => $this->officerLoanAmount,
            'officerOverdueLoans' => $this->officerOverdueLoans,
        ];
        
        // Generate PDF using DomPDF with the dedicated view
        $pdf = PDF::loadView('pdf.active-loans-by-officer-report', [
            'loans' => $this->loans,
            'summary' => $summary,
            'selectedOfficer' => $this->selectedOfficer,
            'officerName' => $officerName
        ]);
        
        // Set PDF options
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial'
        ]);
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    private function generateExcelDownload($filename, $summary, $officerName = null)
    {
        // Download the Excel file using the dedicated export class
        return Excel::download(
            new ActiveLoansByOfficerExport($this->loans, $summary, $this->selectedOfficer, $officerName),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }


    public function render()
    {
        return view('livewire.reports.active-loans-by-officer');
    }
}
