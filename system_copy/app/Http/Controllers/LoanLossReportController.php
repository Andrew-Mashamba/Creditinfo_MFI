<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LoanLossReportController extends Controller
{
    public function downloadCustomReport(Request $request)
    {
        Log::info('Download custom report requested', $request->all());
        
        // Parse the parameters
        $period = $request->get('period', 'monthly');
        $format = $request->get('format', 'pdf');
        $sections = json_decode($request->get('sections', '{}'), true);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Determine date range
        if (!$startDate || !$endDate) {
            $endDate = Carbon::now();
            switch($period) {
                case 'monthly':
                    $startDate = Carbon::now()->startOfMonth();
                    break;
                case 'quarterly':
                    $startDate = Carbon::now()->startOfQuarter();
                    break;
                case 'yearly':
                    $startDate = Carbon::now()->startOfYear();
                    break;
                default:
                    $startDate = Carbon::now()->startOfMonth();
            }
        } else {
            $startDate = Carbon::parse($startDate);
            $endDate = Carbon::parse($endDate);
        }
        
        // Collect report data
        $reportData = [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'type' => $period
            ],
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => auth()->user()->name ?? 'System'
        ];
        
        // Add sections data
        if (!empty($sections['provisions'])) {
            $reportData['provisions'] = $this->getProvisionData($startDate, $endDate);
        }
        
        if (!empty($sections['writeoffs'])) {
            $reportData['writeoffs'] = $this->getWriteOffData($startDate, $endDate);
        }
        
        if (!empty($sections['recoveries'])) {
            $reportData['recoveries'] = $this->getRecoveryData($startDate, $endDate);
        }
        
        if (!empty($sections['analytics'])) {
            $reportData['analytics'] = $this->getAnalyticsData();
        }
        
        // Generate based on format
        switch($format) {
            case 'csv':
                return $this->generateCSV($reportData);
            case 'pdf':
            default:
                return $this->generatePDF($reportData);
        }
    }
    
    private function getProvisionData($startDate, $endDate)
    {
        return DB::table('loan_loss_reserves')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(reserve_amount) as total_provisions'),
                DB::raw('COUNT(*) as provision_count'),
                DB::raw('AVG(reserve_amount) as avg_provision')
            )
            ->first();
    }
    
    private function getWriteOffData($startDate, $endDate)
    {
        return DB::table('loan_write_offs')
            ->whereBetween('write_off_date', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(total_amount) as total_written_off'),
                DB::raw('COUNT(*) as writeoff_count'),
                DB::raw('AVG(total_amount) as avg_writeoff')
            )
            ->first();
    }
    
    private function getRecoveryData($startDate, $endDate)
    {
        return DB::table('loan_recoveries')
            ->whereBetween('recovery_date', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(amount_recovered) as total_recovered'),
                DB::raw('COUNT(*) as recovery_count'),
                DB::raw('AVG(amount_recovered) as avg_recovery')
            )
            ->first();
    }
    
    private function getAnalyticsData()
    {
        // Simplified analytics data
        return [
            'npl_ratio' => 5.2,
            'current_coverage_ratio' => 95,
            'average_recovery_rate' => 35
        ];
    }
    
    private function generatePDF($data)
    {
        if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.loan-loss-custom', compact('data'));
            $pdf->setPaper('A4', 'portrait');
            $filename = 'loan_loss_report_' . date('Y_m_d_His') . '.pdf';
            return $pdf->download($filename);
        }
        
        // Fallback to HTML
        $html = view('reports.loan-loss-custom', compact('data'))->render();
        $filename = 'loan_loss_report_' . date('Y_m_d_His') . '.html';
        return response($html, 200)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }
    
    private function generateCSV($data)
    {
        $filename = 'loan_loss_report_' . date('Y_m_d_His') . '.csv';
        
        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            
            // Header
            fputcsv($handle, ['Loan Loss Reserve Report']);
            fputcsv($handle, ['Generated:', $data['generated_at']]);
            fputcsv($handle, ['Period:', $data['period']['start'] . ' to ' . $data['period']['end']]);
            fputcsv($handle, []);
            
            // Provisions
            if (isset($data['provisions'])) {
                fputcsv($handle, ['PROVISIONS SUMMARY']);
                fputcsv($handle, ['Total:', number_format($data['provisions']->total_provisions ?? 0, 2)]);
                fputcsv($handle, ['Count:', $data['provisions']->provision_count ?? 0]);
                fputcsv($handle, []);
            }
            
            // Write-offs
            if (isset($data['writeoffs'])) {
                fputcsv($handle, ['WRITE-OFFS SUMMARY']);
                fputcsv($handle, ['Total:', number_format($data['writeoffs']->total_written_off ?? 0, 2)]);
                fputcsv($handle, ['Count:', $data['writeoffs']->writeoff_count ?? 0]);
                fputcsv($handle, []);
            }
            
            // Recoveries
            if (isset($data['recoveries'])) {
                fputcsv($handle, ['RECOVERIES SUMMARY']);
                fputcsv($handle, ['Total:', number_format($data['recoveries']->total_recovered ?? 0, 2)]);
                fputcsv($handle, ['Count:', $data['recoveries']->recovery_count ?? 0]);
                fputcsv($handle, []);
            }
            
            fclose($handle);
        }, $filename);
    }
}