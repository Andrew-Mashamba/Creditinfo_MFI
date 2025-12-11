<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\loans_schedules;
use App\Exports\LoanScheduleReport;
use App\Exports\PortfolioAtRiskExport;
use App\Exports\PortfolioAtRiskPdfExport;
use Maatwebsite\Excel\Facades\Excel;

class PortifolioAtRisk extends Component
{
    public $selected = 10;
    public $loans = [];
    public $parRange = [1, 9]; // Default range for PAR 1-10 days

    public function visit($id){

        switch($id){

            case(10): 
                $this->parRange = [1, 9];
                break;

            case(30): 
                $this->parRange = [10, 29];
                break;

            case(40): 
                $this->parRange = [30, 89];
                break;

            case(50): 
                $this->parRange = [90, 9999]; // Above 90 days
                break;

        }

        $this->selected = $id;
        $this->loadLoans();

    }

    public $below10, $count10;
    public $below30, $count30;
    public $below60, $count60;
    public $below90, $count90;


     function summary(){
        $query =LoansModel::query()->where('status','ACTIVE');

        $this->below10=LoansModel::whereBetween('days_in_arrears',[1,9])->where('status','ACTIVE')->sum('principle');
        $this->count10=LoansModel::whereBetween('days_in_arrears',[1,9])->where('status','ACTIVE')->count();


      $this->below30=LoansModel::whereBetween('days_in_arrears',[10,29])->where('status','ACTIVE')->sum('principle');
      $this->count30=LoansModel::whereBetween('days_in_arrears',[10,29])->where('status','ACTIVE')->count();


      $this->below60=LoansModel::whereBetween('days_in_arrears',[30,89])->where('status','ACTIVE')->sum('principle');
      $this->count60=LoansModel::whereBetween('days_in_arrears',[30,89])->where('status','ACTIVE')->count();


      $this->below90=LoansModel::where('days_in_arrears','>=',90)->where('status','ACTIVE')->sum('principle');
      $this->count90=LoansModel::where('days_in_arrears','>=',90)->where('status','ACTIVE')->count();


     }

    public function loadLoans()
    {
        $query = LoansModel::query()->where('status', 'ACTIVE');

        if ($this->parRange[1] == 9999) {
            // For above 90 days
            $query->where('days_in_arrears', '>=', $this->parRange[0]);
        } else {
            $query->whereBetween('days_in_arrears', $this->parRange);
        }

        $this->loans = $query->get()->map(function ($loan) {
            // Get client name
            $client = ClientsModel::where('client_number', $loan->client_number)->first();
            $loan->client_name = $client ? trim($client->first_name . ' ' . $client->middle_name . ' ' . $client->last_name) : 'N/A';

            // Get start date (oldest installment)
            $startDate = loans_schedules::where('loan_id', $loan->id)->oldest()->value('installment_date');
            $loan->start_date = $startDate ? date('Y-m-d', strtotime($startDate)) : 'N/A';

            // Get due date (latest installment)
            $dueDate = loans_schedules::where('loan_id', $loan->id)->latest()->value('installment_date');
            $loan->due_date = $dueDate ? date('Y-m-d', strtotime($dueDate)) : 'N/A';

            // Calculate outstanding amount
            $scheduleQuery = loans_schedules::where('loan_id', $loan->id);
            $totalPrinciple = $scheduleQuery->sum('principle');
            $totalPayment = $scheduleQuery->sum('payment');
            $totalInterest = $scheduleQuery->sum('interest');
            
            $loan->outstanding_amount = $totalPrinciple - ($totalPayment ? $totalPayment - $totalInterest : 0);

            return $loan;
        });
    }

    public function downloadSchedule($loanId)
    {
        try {
            return Excel::download(new LoanScheduleReport($loanId), 'LoanScheduleReport.xlsx');
        } catch (\Exception $e) {
            session()->flash('error', 'Error downloading schedule: ' . $e->getMessage());
        }
    }

    public function exportToExcel()
    {
        try {
            $categoryNames = [
                10 => 'PAR_1_10_Days',
                30 => 'PAR_10_30_Days',
                40 => 'PAR_30_90_Days',
                50 => 'PAR_Above_90_Days'
            ];

            $filename = $categoryNames[$this->selected] . '_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            
            return Excel::download(
                new PortfolioAtRiskExport($this->parRange, $this->selected), 
                $filename
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Error exporting to Excel: ' . $e->getMessage());
        }
    }

    public function exportToPdf()
    {
        try {
            $categoryNames = [
                10 => 'PAR_1_10_Days',
                30 => 'PAR_10_30_Days',
                40 => 'PAR_30_90_Days',
                50 => 'PAR_Above_90_Days'
            ];

            $filename = $categoryNames[$this->selected] . '_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            
            $pdfExport = new PortfolioAtRiskPdfExport($this->parRange, $this->selected);
            $pdf = $pdfExport->generate();
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);
        } catch (\Exception $e) {
            session()->flash('error', 'Error exporting to PDF: ' . $e->getMessage());
        }
    }

    public function mount()
    {
        $this->loadLoans();
    }

    public function render()
    {
        $this->summary();
        return view('livewire.reports.portifolio-at-risk');
    }
}
