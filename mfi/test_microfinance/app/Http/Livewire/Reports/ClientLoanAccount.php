<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\BranchesModel;
use App\Models\AccountsModel;
use App\Models\LoanSubProduct;
use App\Models\loans_schedules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientLoanAccountExport;

class ClientLoanAccount extends Component
{
    public $selectedClient = '';
    public $clientNumber = '';
    public $startDate;
    public $endDate;
    public $clients = [];
    public $clientLoans = [];
    public $selectedLoan = null;
    public $loanAccountDetails = [];
    public $loanSchedules = [];
    public $totalLoanAmount = 0;
    public $totalPaidAmount = 0;
    public $outstandingBalance = 0;
    public $nextPaymentDate = null;
    public $nextPaymentAmount = 0;
    public $daysInArrears = 0;
    public $showLoanDetails = false;
    
    // Export loading states
    public $isExportingPdf = false;
    public $isExportingExcel = false;

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->clientLoans = collect([]);
        $this->loadClients();
    }

    public function loadClients()
    {
        $this->clients = ClientsModel::whereHas('loans')->get()->map(function ($client) {
            $client->full_name = trim($client->first_name . ' ' . $client->middle_name . ' ' . $client->last_name);
            return $client;
        });
    }

    public function updatedSelectedClient()
    {
        if ($this->selectedClient) {
            $client = ClientsModel::find($this->selectedClient);
            $this->clientNumber = $client->client_number;
            $this->loadClientLoans();
        }
    }

    public function updatedClientNumber()
    {
        if ($this->clientNumber) {
            $this->loadClientLoans();
        }
    }

    public function loadClientLoans()
    {
        if (empty($this->clientNumber)) {
            $this->clientLoans = collect([]);
            return;
        }

        $this->clientLoans = LoansModel::where('client_number', $this->clientNumber)
            ->get()
            ->map(function ($loan) {
                // Get client name
                $client = ClientsModel::where('client_number', $loan->client_number)->first();
                $loan->client_name = $client ? trim($client->first_name . ' ' . $client->middle_name . ' ' . $client->last_name) : 'N/A';

                // Get branch name
                $branch = BranchesModel::find($loan->branch_id);
                $loan->branch_name = $branch ? $branch->name : 'N/A';

                // Get loan product name
                $product = LoanSubProduct::where('product_id', $loan->loan_sub_product)->first();
                $loan->product_name = $product ? $product->product_name : 'N/A';

                // Calculate outstanding balance
                $totalPaid = loans_schedules::where('loan_id', $loan->id)->sum('payment');
                $loan->outstanding_balance = $loan->principle - ($totalPaid ?: 0);

                // Get days in arrears
                $loan->days_in_arrears = loans_schedules::where('loan_id', $loan->id)
                    ->where('days_in_arrears', '>', 0)
                    ->max('days_in_arrears') ?: 0;

                // Get next payment date
                $nextSchedule = loans_schedules::where('loan_id', $loan->id)
                    ->where('payment', 0)
                    ->orWhereNull('payment')
                    ->orderBy('installment_date')
                    ->first();
                $loan->next_payment_date = $nextSchedule ? $nextSchedule->installment_date : null;
                $loan->next_payment_amount = $nextSchedule ? $nextSchedule->installment : 0;

                return $loan;
            });
    }

    public function viewLoanDetails($loanId)
    {
        $this->selectedLoan = LoansModel::find($loanId);
        if ($this->selectedLoan) {
            $this->loadLoanAccountDetails();
            $this->loadLoanSchedules();
            $this->calculateLoanSummary();
            $this->showLoanDetails = true;
        }
    }

    public function loadLoanAccountDetails()
    {
        if (!$this->selectedLoan) return;

        // Get account details
        $account = AccountsModel::where('account_number', $this->selectedLoan->loan_account_number)->first();
        $this->loanAccountDetails = $account ? $account->toArray() : [];

        // Get client details
        $client = ClientsModel::where('client_number', $this->selectedLoan->client_number)->first();
        if ($client) {
            $this->loanAccountDetails['client_name'] = trim($client->first_name . ' ' . $client->middle_name . ' ' . $client->last_name);
            $this->loanAccountDetails['client_phone'] = $client->phone_number;
            $this->loanAccountDetails['client_email'] = $client->email;
        }

        // Get branch details
        $branch = BranchesModel::find($this->selectedLoan->branch_id);
        if ($branch) {
            $this->loanAccountDetails['branch_name'] = $branch->name;
        }

        // Get product details
        $product = LoanSubProduct::where('product_id', $this->selectedLoan->loan_sub_product)->first();
        if ($product) {
            $this->loanAccountDetails['product_name'] = $product->product_name;
        }
    }

    public function loadLoanSchedules()
    {
        if (!$this->selectedLoan) return;

        $this->loanSchedules = loans_schedules::where('loan_id', $this->selectedLoan->id)
            ->orderBy('installment_date')
            ->get()
            ->map(function ($schedule) {
                $schedule->payment_status = $schedule->payment > 0 ? 'Paid' : 'Pending';
                $schedule->is_overdue = $schedule->days_in_arrears > 0;
                return $schedule;
            });
    }

    public function calculateLoanSummary()
    {
        if (!$this->selectedLoan) return;

        $this->totalLoanAmount = $this->selectedLoan->principle;
        $this->totalPaidAmount = loans_schedules::where('loan_id', $this->selectedLoan->id)->sum('payment');
        $this->outstandingBalance = $this->totalLoanAmount - $this->totalPaidAmount;

        // Get next payment details
        $nextSchedule = loans_schedules::where('loan_id', $this->selectedLoan->id)
            ->where(function ($query) {
                $query->where('payment', 0)->orWhereNull('payment');
            })
            ->orderBy('installment_date')
            ->first();

        $this->nextPaymentDate = $nextSchedule ? $nextSchedule->installment_date : null;
        $this->nextPaymentAmount = $nextSchedule ? $nextSchedule->installment : 0;

        // Calculate days in arrears
        $this->daysInArrears = loans_schedules::where('loan_id', $this->selectedLoan->id)
            ->where('days_in_arrears', '>', 0)
            ->max('days_in_arrears') ?: 0;
    }

    public function closeLoanDetails()
    {
        $this->showLoanDetails = false;
        $this->selectedLoan = null;
        $this->loanAccountDetails = [];
        $this->loanSchedules = [];
    }

    public function exportReport($format = 'pdf')
    {
        if ($format === 'pdf') {
            return $this->exportToPdf();
        } elseif ($format === 'excel') {
            return $this->exportToExcel();
        }
    }

    public function exportToPdf()
    {
        $this->isExportingPdf = true;
        
        try {
            // Validate user permissions for export
            if (!auth()->check()) {
                throw new Exception('User authentication required for export');
            }

            // Validate that we have data to export
            if (empty($this->clientLoans) || $this->clientLoans->isEmpty()) {
                throw new Exception('No loan account data available for export. Please select a member and ensure there is data.');
            }

            // Prepare summary data
            $summary = [
                'totalLoans' => $this->clientLoans->count(),
                'totalLoanAmount' => $this->clientLoans->sum('principle'),
                'totalOutstanding' => $this->clientLoans->sum('outstanding_balance')
            ];

            $filename = 'member_loan_account_' . $this->clientNumber . '_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            
            Log::info('Client Loan Account Report exported as PDF', [
                'format' => 'pdf',
                'client_number' => $this->clientNumber,
                'user_id' => auth()->id(),
                'record_count' => $this->clientLoans->count()
            ]);
            
            // Generate PDF using DomPDF
            $pdf = Pdf::loadView('pdf.client-loan-account-report', [
                'clientLoans' => $this->clientLoans,
                'summary' => $summary,
                'clientNumber' => $this->clientNumber
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
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
            
        } catch (Exception $e) {
            session()->flash('error', 'Error exporting PDF: ' . $e->getMessage());
            Log::error('Client Loan Account Report PDF export failed', [
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

            // Validate that we have data to export
            if (empty($this->clientLoans) || $this->clientLoans->isEmpty()) {
                throw new Exception('No loan account data available for export. Please select a member and ensure there is data.');
            }

            // Prepare summary data
            $summary = [
                'totalLoans' => $this->clientLoans->count(),
                'totalLoanAmount' => $this->clientLoans->sum('principle'),
                'totalOutstanding' => $this->clientLoans->sum('outstanding_balance')
            ];

            $filename = 'member_loan_account_' . $this->clientNumber . '_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            
            Log::info('Client Loan Account Report exported as Excel', [
                'format' => 'excel',
                'client_number' => $this->clientNumber,
                'user_id' => auth()->id(),
                'record_count' => $this->clientLoans->count()
            ]);
            
            // Use the Excel export class
            return Excel::download(
                new ClientLoanAccountExport($this->clientLoans, $summary, $this->clientNumber),
                $filename,
                \Maatwebsite\Excel\Excel::XLSX
            );
            
        } catch (Exception $e) {
            session()->flash('error', 'Error exporting Excel: ' . $e->getMessage());
            Log::error('Client Loan Account Report Excel export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isExportingExcel = false;
        }
    }

    public function render()
    {
        return view('livewire.reports.client-loan-account');
    }
}
