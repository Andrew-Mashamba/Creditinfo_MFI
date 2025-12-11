<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Maatwebsite\Excel\Facades\Excel;

class LoanDisbursementReport extends Component
{
    public $reportStartDate;
    public $reportEndDate;
    public $reportData = null;
    public $isGenerating = false;
    public $isExportingPdf = false;
    public $isExportingExcel = false;
    public $errorMessage = '';
    public $successMessage = '';

    public function mount()
    {
        $this->reportStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->reportEndDate = Carbon::now()->format('Y-m-d');
    }

    public function generateReport()
    {
        $this->isGenerating = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $this->reportData = $this->getReportData();
            $this->successMessage = 'Loan Disbursement Report generated successfully!';
            
            Log::info('Loan Disbursement Report generated', [
                'start_date' => $this->reportStartDate,
                'end_date' => $this->reportEndDate,
                'user_id' => auth()->id()
            ]);
        } catch (Exception $e) {
            $this->errorMessage = 'Error generating report: ' . $e->getMessage();
            Log::error('Loan Disbursement Report generation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isGenerating = false;
        }
    }

    public function getReportData()
    {
        $startDate = Carbon::parse($this->reportStartDate)->startOfDay();
        $endDate = Carbon::parse($this->reportEndDate)->endOfDay();

        // Get loan disbursements from loans table with additional fields
        $disbursements = DB::table('loans')
            ->where('status', 'APPROVED')
            ->where('disbursement_date', '>=', $startDate)
            ->where('disbursement_date', '<=', $endDate)
            ->whereNotNull('disbursement_date')
            ->orderBy('disbursement_date', 'desc')
            ->get();

        // Convert string amounts to float for calculations
        $totalDisbursed = $disbursements->sum(function($disbursement) {
            return (float) $disbursement->principle;
        });
        $numberOfDisbursements = $disbursements->count();
        $averageDisbursement = $numberOfDisbursements > 0 ? $totalDisbursed / $numberOfDisbursements : 0;

        // Group disbursements by loan type
        $disbursementsByType = [];
        $disbursementsByDate = [];

        foreach ($disbursements as $disbursement) {
            $loanType = $this->getLoanType($disbursement->loan_type_2);
            
            if (!isset($disbursementsByType[$loanType])) {
                $disbursementsByType[$loanType] = [
                    'count' => 0,
                    'amount' => 0,
                ];
            }
            $disbursementsByType[$loanType]['count']++;
            $disbursementsByType[$loanType]['amount'] += (float) $disbursement->principle;

            // Group by date
            $date = Carbon::parse($disbursement->disbursement_date)->format('Y-m-d');
            if (!isset($disbursementsByDate[$date])) {
                $disbursementsByDate[$date] = [
                    'count' => 0,
                    'amount' => 0,
                ];
            }
            $disbursementsByDate[$date]['count']++;
            $disbursementsByDate[$date]['amount'] += (float) $disbursement->principle;
        }

        // Get daily disbursement trend
        $dailyTrend = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $dailyTrend[] = [
                'date' => $currentDate->format('M d'),
                'amount' => $disbursementsByDate[$dateStr]['amount'] ?? 0,
                'count' => $disbursementsByDate[$dateStr]['count'] ?? 0,
            ];
            $currentDate->addDay();
        }

        return [
            'period' => [
                'start_date' => $startDate->format('F d, Y'),
                'end_date' => $endDate->format('F d, Y'),
                'start_date_short' => $startDate->format('M d, Y'),
                'end_date_short' => $endDate->format('M d, Y'),
            ],
            'disbursement_summary' => [
                'total_disbursed' => $totalDisbursed,
                'number_of_disbursements' => $numberOfDisbursements,
                'average_disbursement' => $averageDisbursement,
                'largest_disbursement' => $disbursements->max(function($disbursement) {
                    return (float) $disbursement->principle;
                }) ?? 0,
                'smallest_disbursement' => $disbursements->min(function($disbursement) {
                    return (float) $disbursement->principle;
                }) ?? 0,
            ],
            'disbursements' => $disbursements,
            'disbursements_by_type' => $disbursementsByType,
            'daily_trend' => $dailyTrend,
        ];
    }

    private function getLoanType($loanType)
    {
        if (!$loanType) {
            return 'Other Loans';
        }
        
        $name = strtolower($loanType);
        
        if (strpos($name, 'personal') !== false) {
            return 'Personal Loans';
        } elseif (strpos($name, 'business') !== false || strpos($name, 'commercial') !== false) {
            return 'Business Loans';
        } elseif (strpos($name, 'agriculture') !== false || strpos($name, 'agricultural') !== false) {
            return 'Agricultural Loans';
        } elseif (strpos($name, 'education') !== false || strpos($name, 'school') !== false) {
            return 'Education Loans';
        } elseif (strpos($name, 'short') !== false || strpos($name, 'working') !== false) {
            return 'Short-term Loans';
        } elseif (strpos($name, 'long') !== false || strpos($name, 'term') !== false) {
            return 'Long-term Loans';
        } else {
            return 'Other Loans';
        }
    }

    public function exportToPDF()
    {
        $this->isExportingPdf = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            // Validate user permissions for export
            if (!auth()->check()) {
                throw new Exception('User authentication required for export');
            }

            if (!$this->reportData) {
                $this->reportData = $this->getReportData();
            }

            // Validate report data exists
            if (empty($this->reportData)) {
                throw new Exception('No report data available for export. Please generate the report first.');
            }

            $filename = 'loan_disbursement_report_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            
            Log::info('Loan Disbursement Report exported as PDF', [
                'format' => 'pdf',
                'user_id' => auth()->id(),
                'disbursements_count' => count($this->reportData['disbursements']),
                'report_date' => $this->reportEndDate
            ]);
            
            $this->successMessage = 'Loan Disbursement Report exported as PDF successfully!';
            
            // Generate and download PDF
            return $this->generatePDFDownload($filename);
            
        } catch (Exception $e) {
            $this->errorMessage = 'Error exporting PDF: ' . $e->getMessage();
            Log::error('Loan Disbursement Report PDF export failed', [
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
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            // Validate user permissions for export
            if (!auth()->check()) {
                throw new Exception('User authentication required for export');
            }

            if (!$this->reportData) {
                $this->reportData = $this->getReportData();
            }

            // Validate report data exists
            if (empty($this->reportData)) {
                throw new Exception('No report data available for export. Please generate the report first.');
            }

            $filename = 'loan_disbursement_report_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            
            Log::info('Loan Disbursement Report exported as Excel', [
                'format' => 'excel',
                'user_id' => auth()->id(),
                'disbursements_count' => count($this->reportData['disbursements']),
                'report_date' => $this->reportEndDate
            ]);
            
            $this->successMessage = 'Loan Disbursement Report exported as Excel successfully!';
            
            // Generate and download Excel
            return $this->generateExcelDownload($filename);
            
        } catch (Exception $e) {
            $this->errorMessage = 'Error exporting Excel: ' . $e->getMessage();
            Log::error('Loan Disbursement Report Excel export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isExportingExcel = false;
        }
    }

    private function generatePDFDownload($filename)
    {
        // Generate PDF using DomPDF with the dedicated view
        $pdf = PDF::loadView('pdf.loan-disbursement-report', [
            'reportData' => $this->reportData,
            'reportDate' => $this->reportEndDate
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

    private function generateExcelDownload($filename)
    {
        // Download the Excel file using the dedicated export class
        return Excel::download(
            new \App\Exports\LoanDisbursementReportExport($this->reportData, $this->reportEndDate),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function render()
    {
        return view('livewire.reports.loan-disbursement-report');
    }
}