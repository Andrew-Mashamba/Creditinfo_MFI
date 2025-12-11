<?php

namespace App\Exports;

use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\loans_schedules;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

class PortfolioAtRiskPdfExport
{
    protected $parRange;
    protected $selectedCategory;
    public function __construct($parRange, $selectedCategory)
    {
        $this->parRange = $parRange;
        $this->selectedCategory = $selectedCategory;
    }

    public function generate()
    {
        $loans = $this->getLoansData();
        $categoryNames = [
            10 => 'PAR 1-10 Days',
            30 => 'PAR 10-30 Days',
            40 => 'PAR 30-90 Days',
            50 => 'PAR Above 90 Days'
        ];

        $data = [
            'loans' => $loans,
            'categoryName' => $categoryNames[$this->selectedCategory] ?? 'Portfolio at Risk',
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'totalLoans' => $loans->count(),
            'totalOutstanding' => $loans->sum('outstanding_amount')
        ];

        $pdf = Pdf::loadView('pdf.portfolio-at-risk-report', $data);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial'
        ]);
        
        return $pdf;
    }

    protected function getLoansData()
    {
        $query = LoansModel::query()->where('status', 'ACTIVE');

        if ($this->parRange[1] == 9999) {
            // For above 90 days
            $query->where('days_in_arrears', '>=', $this->parRange[0]);
        } else {
            $query->whereBetween('days_in_arrears', $this->parRange);
        }

        return $query->get()->map(function ($loan) {
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

            // Determine risk category
            if ($loan->days_in_arrears >= 90) {
                $loan->risk_category = 'DOUBTFUL/LOSS';
            } elseif ($loan->days_in_arrears >= 30) {
                $loan->risk_category = 'SUBSTANDARD';
            } elseif ($loan->days_in_arrears >= 10) {
                $loan->risk_category = 'WATCH';
            } else {
                $loan->risk_category = 'NORMAL';
            }

            return $loan;
        });
    }
}
