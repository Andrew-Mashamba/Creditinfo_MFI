<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\approvals;
use App\Models\LoansModel;
use App\Models\Transactions;
use App\Models\ClientsModel;
use App\Models\BranchesModel;
use App\Models\AccountsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MainReport;
use App\Exports\LoanSchedule;
use App\Exports\ContractData;
use App\Exports\ClientDetailsReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;

class ClientsDetailsReport extends Component
{
    public $client_type = "ALL";
    public $custome_client_number = '';
    public $branchFilter = '';
    public $statusFilter = '';
    public $members = [];
    public $branches = [];
    public $totalMembers = 0;
    public $activeMembers = 0;
    public $pendingMembers = 0;
    public $inactiveMembers = 0;
    public $totalBranches = 0;
    public $totalSavings = 0;
    public $membersWithLoans = 0;
    
    // Modal properties
    public $showMemberModal = false;
    public $selectedMember = null;



    public function mount()
    {
        $this->loadBranches();
        $this->loadMembers();
        $this->calculateSummary();
    }

    public function loadBranches()
    {
        $this->branches = BranchesModel::all();
    }

    public function loadMembers()
    {
        $query = ClientsModel::query();

        if ($this->client_type === 'MULTIPLE' && !empty($this->custome_client_number)) {
            // Handle multiple member selection
            $input = rtrim($this->custome_client_number, ',');
            $numbers = explode(',', $input);
            $memberNumbers = [];
            
            foreach ($numbers as $number) {
                $number = trim($number);
                $number = str_pad($number, 4, 0, STR_PAD_LEFT);
                $memberNumbers[] = $number;
            }
            
            $query->whereIn('client_number', $memberNumbers);
        } else {
            // Apply filters for all members
            if (!empty($this->branchFilter)) {
                $query->where('branch_id', $this->branchFilter);
            }
            
            if (!empty($this->statusFilter)) {
                $query->where('status', $this->statusFilter);
            }
        }

        $this->members = $query->get()->map(function ($member) {
            // Get branch name
            $branch = BranchesModel::find($member->branch_id);
            $member->branch_name = $branch ? $branch->name : 'N/A';

            // Create full name
            $member->full_name = trim($member->first_name . ' ' . $member->middle_name . ' ' . $member->last_name);

            // Format registration date
            $member->registration_date = $member->created_at ? $member->created_at->format('Y-m-d') : 'N/A';

            // Get savings balance (you might need to adjust this based on your accounts structure)
            $savingsAccount = AccountsModel::where('client_number', $member->client_number)
                ->where('product_number', '1000') // Assuming 1000 is savings product
                ->first();
            $member->savings_balance = $savingsAccount ? $savingsAccount->balance : 0;

            return $member;
        });
    }

    public function calculateSummary()
    {
        $this->totalMembers = ClientsModel::count();
        $this->activeMembers = ClientsModel::where('status', 'ACTIVE')->count();
        $this->pendingMembers = ClientsModel::where('status', 'PENDING')->count();
        $this->inactiveMembers = ClientsModel::where('status', 'INACTIVE')->count();
        $this->totalBranches = BranchesModel::count();
        
        // Calculate total savings
        $this->totalSavings = AccountsModel::where('product_number', '1000')->sum('balance');
        
        // Calculate members with loans
        $this->membersWithLoans = ClientsModel::whereHas('loans')->count();
    }

    public function viewMemberDetails($memberId)
    {
        $this->selectedMember = ClientsModel::find($memberId);
        if ($this->selectedMember) {
            // Get branch name
            $branch = BranchesModel::find($this->selectedMember->branch_id);
            $this->selectedMember->branch_name = $branch ? $branch->name : 'N/A';
            
            // Create full name
            $this->selectedMember->full_name = trim($this->selectedMember->first_name . ' ' . $this->selectedMember->middle_name . ' ' . $this->selectedMember->last_name);
            
            // Format registration date
            $this->selectedMember->registration_date = $this->selectedMember->created_at ? $this->selectedMember->created_at->format('Y-m-d') : 'N/A';
            
            // Get savings balance
            $savingsAccount = AccountsModel::where('client_number', $this->selectedMember->client_number)
                ->where('product_number', '1000')
                ->first();
            $this->selectedMember->savings_balance = $savingsAccount ? $savingsAccount->balance : 0;
            
            // Get loan information
            $loans = LoansModel::where('client_number', $this->selectedMember->client_number)->get();
            $this->selectedMember->loans = $loans;
            $this->selectedMember->total_loans = $loans->count();
            $this->selectedMember->active_loans = $loans->where('status', 'ACTIVE')->count();
            $this->selectedMember->total_loan_amount = $loans->sum('loan_amount');
            $this->selectedMember->outstanding_balance = $loans->where('status', 'ACTIVE')->sum('outstanding_balance');
            
            // Get recent transactions
            $recentTransactions = Transactions::where('client_number', $this->selectedMember->client_number)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            $this->selectedMember->recent_transactions = $recentTransactions;
            
            // Get all accounts
            $allAccounts = AccountsModel::where('client_number', $this->selectedMember->client_number)->get();
            $this->selectedMember->all_accounts = $allAccounts;
            $this->selectedMember->total_accounts = $allAccounts->count();
        }
        $this->showMemberModal = true;
    }

    public function closeMemberModal()
    {
        $this->showMemberModal = false;
        $this->selectedMember = null;
    }

    public function exportReport($format = 'pdf')
    {
        try {
            if ($format === 'pdf') {
                return $this->exportToPDF();
            } elseif ($format === 'excel') {
                return $this->exportToExcel();
            }
            
            session()->flash('error', 'Invalid export format specified.');
        } catch (Exception $e) {
            session()->flash('error', 'Error exporting report: ' . $e->getMessage());
            Log::error('Client Details Report export failed', [
                'error' => $e->getMessage(),
                'format' => $format,
                'user_id' => auth()->id()
            ]);
        }
    }

    private function exportToPDF()
    {
        try {
            // Prepare data for PDF
            $reportData = [
                'members' => $this->members,
                'summary' => [
                    'totalMembers' => $this->totalMembers,
                    'activeMembers' => $this->activeMembers,
                    'pendingMembers' => $this->pendingMembers,
                    'inactiveMembers' => $this->inactiveMembers,
                    'totalBranches' => $this->totalBranches,
                    'totalSavings' => $this->totalSavings,
                    'membersWithLoans' => $this->membersWithLoans,
                ],
                'filters' => [
                    'client_type' => $this->client_type,
                    'branch_filter' => $this->branchFilter,
                    'status_filter' => $this->statusFilter,
                    'custom_numbers' => $this->custome_client_number,
                ],
                'reportDate' => now()->format('Y-m-d H:i:s'),
                'generatedBy' => auth()->user()->name ?? 'System',
            ];

            $filename = 'client_details_report_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            
            // Generate PDF using DomPDF
            $pdf = Pdf::loadView('pdf.client-details-report', $reportData);
            
            // Set PDF options
            $pdf->setPaper('A4', 'landscape');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Arial'
            ]);
            
            Log::info('Client Details Report exported as PDF', [
                'format' => 'pdf',
                'client_type' => $this->client_type,
                'branch_filter' => $this->branchFilter,
                'status_filter' => $this->statusFilter,
                'user_id' => auth()->id()
            ]);
            
            // Download the PDF
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);
            
        } catch (Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    private function exportToExcel()
    {
        try {
            $filename = 'client_details_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            
            // Prepare data for Excel export
            $summary = [
                'totalMembers' => $this->totalMembers,
                'activeMembers' => $this->activeMembers,
                'pendingMembers' => $this->pendingMembers,
                'inactiveMembers' => $this->inactiveMembers,
                'totalBranches' => $this->totalBranches,
                'totalSavings' => $this->totalSavings,
                'membersWithLoans' => $this->membersWithLoans,
            ];
            
            $filters = [
                'client_type' => $this->client_type,
                'branch_filter' => $this->branchFilter,
                'status_filter' => $this->statusFilter,
                'custom_numbers' => $this->custome_client_number,
            ];
            
            Log::info('Client Details Report exported as Excel', [
                'format' => 'excel',
                'client_type' => $this->client_type,
                'branch_filter' => $this->branchFilter,
                'status_filter' => $this->statusFilter,
                'user_id' => auth()->id()
            ]);
            
            // Use the proper Excel export class
            return Excel::download(
                new \App\Exports\ClientDetailsReportExport($this->members, $summary, $filters),
                $filename
            );
            
        } catch (Exception $e) {
            Log::error('Error generating Excel: ' . $e->getMessage());
            throw $e;
        }
    }


    public function render()
    {
        return view('livewire.reports.clients-details-report');
    }
}
