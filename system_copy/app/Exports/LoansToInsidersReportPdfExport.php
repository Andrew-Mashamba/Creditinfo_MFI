<?php

namespace App\Exports;

use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\Employee;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

class LoansToInsidersReportPdfExport
{
    protected $insiderLoans;
    protected $relatedPartyLoans;
    protected $insiderCategories;
    protected $reportDate;
    protected $totalInsiderLoanAmount;
    protected $insiderLoanCount;
    protected $averageInsiderLoanAmount;
    protected $complianceStatus;
    protected $maximumInsiderLoanLimit;

    public function __construct($insiderLoans, $relatedPartyLoans, $insiderCategories, $reportDate, $totalInsiderLoanAmount, $insiderLoanCount, $averageInsiderLoanAmount, $complianceStatus, $maximumInsiderLoanLimit)
    {
        $this->insiderLoans = $insiderLoans;
        $this->relatedPartyLoans = $relatedPartyLoans;
        $this->insiderCategories = $insiderCategories;
        $this->reportDate = $reportDate;
        $this->totalInsiderLoanAmount = $totalInsiderLoanAmount;
        $this->insiderLoanCount = $insiderLoanCount;
        $this->averageInsiderLoanAmount = $averageInsiderLoanAmount;
        $this->complianceStatus = $complianceStatus;
        $this->maximumInsiderLoanLimit = $maximumInsiderLoanLimit;
    }

    public function generate()
    {
        $data = [
            'insiderLoans' => $this->insiderLoans,
            'relatedPartyLoans' => $this->relatedPartyLoans,
            'insiderCategories' => $this->insiderCategories,
            'reportDate' => $this->reportDate,
            'totalInsiderLoanAmount' => $this->totalInsiderLoanAmount,
            'insiderLoanCount' => $this->insiderLoanCount,
            'averageInsiderLoanAmount' => $this->averageInsiderLoanAmount,
            'complianceStatus' => $this->complianceStatus,
            'maximumInsiderLoanLimit' => $this->maximumInsiderLoanLimit,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'generatedBy' => auth()->user()->name ?? 'System'
        ];

        $pdf = Pdf::loadView('pdf.loans-to-insiders-report', $data);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial',
            'dpi' => 150,
            'defaultPaperSize' => 'a4',
            'isPhpEnabled' => true
        ]);
        
        return $pdf;
    }
}
