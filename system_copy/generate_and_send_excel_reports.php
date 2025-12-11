#!/usr/bin/env php
<?php

/**
 * Generate Excel Reports and Send via Email
 * Generates actual Excel reports with real data from the database
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Services\ScheduledReportDataService;
use App\Services\MonthlyReportDataService;
use App\Services\QuarterlyAnnualReportDataService;

// Configuration
$recipient = 'andrew.mashamba@nbc.co.tz';
$outputDir = __DIR__ . '/storage/app/scheduled_reports';

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  NBC SACCOS - EXCEL REPORTS GENERATOR\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Create output directory
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Initialize services
$dailyWeeklyService = new ScheduledReportDataService();
$monthlyService = new MonthlyReportDataService();
$quarterlyAnnualService = new QuarterlyAnnualReportDataService();

// Style definitions
function getHeaderStyle() {
    return [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D2240']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
    ];
}

function getSubHeaderStyle() {
    return [
        'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F0F8']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]]
    ];
}

function getTitleStyle() {
    return [
        'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0D2240']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ];
}

function getDataStyle() {
    return [
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
    ];
}

function getNumberStyle() {
    return [
        'numberFormat' => ['formatCode' => '#,##0.00'],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
    ];
}

/**
 * Convert nested array to flat rows for Excel
 */
function flattenData($data, $prefix = '') {
    $rows = [];
    foreach ($data as $key => $value) {
        $label = $prefix ? "$prefix - $key" : $key;
        $label = ucwords(str_replace('_', ' ', $label));

        if (is_array($value)) {
            if (isset($value[0])) {
                // Array of items
                if (is_array($value[0]) || is_object($value[0])) {
                    // Array of objects - will be handled separately
                    continue;
                } else {
                    // Simple array of scalars
                    $rows[] = [$label, implode(', ', $value)];
                }
            } else {
                // Nested associative array
                $rows = array_merge($rows, flattenData($value, $label));
            }
        } elseif (is_object($value)) {
            // Convert object to array and process
            $rows = array_merge($rows, flattenData((array)$value, $label));
        } else {
            $rows[] = [$label, $value];
        }
    }
    return $rows;
}

/**
 * Add a report to an Excel worksheet
 */
function addReportToSheet($spreadsheet, $sheetName, $reportData, $sheetIndex = null) {
    if ($sheetIndex === null || $sheetIndex === 0) {
        $sheet = $spreadsheet->getActiveSheet();
    } else {
        $sheet = $spreadsheet->createSheet();
    }

    // Clean sheet name (max 31 chars, no special chars)
    $cleanName = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $sheetName), 0, 31);
    $sheet->setTitle($cleanName);

    $row = 1;

    // Title
    $reportType = $reportData['report_type'] ?? $sheetName;
    $sheet->setCellValue('A' . $row, $reportType);
    $sheet->mergeCells('A' . $row . ':D' . $row);
    $sheet->getStyle('A' . $row)->applyFromArray(getTitleStyle());
    $row += 2;

    // Generated info
    $sheet->setCellValue('A' . $row, 'Generated At:');
    $sheet->setCellValue('B' . $row, $reportData['generated_at'] ?? date('Y-m-d H:i:s'));
    $row++;

    if (isset($reportData['report_month'])) {
        $sheet->setCellValue('A' . $row, 'Report Period:');
        $sheet->setCellValue('B' . $row, $reportData['report_month']);
        $row++;
    }

    if (isset($reportData['period'])) {
        $sheet->setCellValue('A' . $row, 'Period:');
        $sheet->setCellValue('B' . $row, $reportData['period']);
        $row++;
    }

    $row++;

    // Summary section
    $flatData = flattenData($reportData);

    // Headers
    $sheet->setCellValue('A' . $row, 'Metric');
    $sheet->setCellValue('B' . $row, 'Value');
    $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray(getHeaderStyle());
    $row++;

    foreach ($flatData as $item) {
        if (!in_array($item[0], ['Report Type', 'Generated At', 'Report Month', 'Report Period', 'Period'])) {
            $sheet->setCellValue('A' . $row, $item[0]);
            // Convert arrays/objects to string
            $cellValue = $item[1];
            if (is_array($cellValue) || is_object($cellValue)) {
                $cellValue = json_encode($cellValue);
            }
            $sheet->setCellValue('B' . $row, $cellValue);
            $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray(getDataStyle());
            $row++;
        }
    }

    // Check for array data (like lists of loans, members, etc.)
    foreach ($reportData as $key => $value) {
        if (is_array($value) && isset($value[0]) && is_array($value[0])) {
            $row += 2;
            $sectionTitle = ucwords(str_replace('_', ' ', $key));
            $sheet->setCellValue('A' . $row, $sectionTitle);
            $sheet->getStyle('A' . $row)->applyFromArray(getSubHeaderStyle());
            $row++;

            // Get headers from first item
            $headers = array_keys($value[0]);
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, ucwords(str_replace('_', ' ', $header)));
                $col++;
            }
            $lastCol = chr(ord('A') + count($headers) - 1);
            $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray(getHeaderStyle());
            $row++;

            // Data rows
            foreach ($value as $item) {
                $col = 'A';
                foreach ($item as $cellValue) {
                    // Convert arrays/objects to string
                    if (is_array($cellValue) || is_object($cellValue)) {
                        $cellValue = json_encode($cellValue);
                    }
                    $sheet->setCellValue($col . $row, $cellValue);
                    $col++;
                }
                $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray(getDataStyle());
                $row++;
            }
        }
    }

    // Auto-size columns
    foreach (range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    return $spreadsheet;
}

/**
 * Generate Excel file for a group of reports
 */
function generateExcelFile($reports, $filename, $outputDir) {
    $spreadsheet = new Spreadsheet();
    $sheetIndex = 0;

    foreach ($reports as $name => $data) {
        addReportToSheet($spreadsheet, $name, $data, $sheetIndex);
        $sheetIndex++;
    }

    $writer = new Xlsx($spreadsheet);
    $filepath = $outputDir . '/' . $filename;
    $writer->save($filepath);

    return $filepath;
}

$generatedFiles = [];

// ============================================
// DAILY REPORTS
// ============================================
echo "📊 Generating Daily Reports...\n";
$dailyReports = [
    'Daily Operations' => $dailyWeeklyService->generateDailyOperationsReport(),
    'Daily Arrears' => $dailyWeeklyService->generateDailyArrearsReport(),
    'Daily Cash Position' => $dailyWeeklyService->generateDailyCashPositionReport(),
    'Daily Disbursements' => $dailyWeeklyService->generateDailyDisbursementsReport(),
    'Daily Collections' => $dailyWeeklyService->generateDailyCollectionsReport(),
    'Daily Member Activity' => $dailyWeeklyService->generateDailyMemberActivityReport(),
    'Daily Loan Officer' => $dailyWeeklyService->generateDailyLoanOfficerPortfolioReport(),
    'Daily Recovery Action' => $dailyWeeklyService->generateDailyRecoveryActionReport(),
    'Daily GL Summary' => $dailyWeeklyService->generateDailyGLSummaryReport(),
    'Daily Large Trans' => $dailyWeeklyService->generateDailyLargeTransactionsReport(),
];
$dailyFile = generateExcelFile($dailyReports, 'NBC_SACCOS_Daily_Reports_' . date('Y-m-d') . '.xlsx', $outputDir);
$generatedFiles[] = ['path' => $dailyFile, 'name' => basename($dailyFile), 'category' => 'Daily Reports'];
echo "   ✓ Daily Reports generated\n";

// ============================================
// WEEKLY REPORTS
// ============================================
echo "📊 Generating Weekly Reports...\n";
$weeklyReports = [
    'Weekly Executive' => $dailyWeeklyService->generateWeeklyExecutiveSummaryReport(),
    'Weekly Arrears' => $dailyWeeklyService->generateWeeklyArrearsReport(),
    'Weekly Credit Comm' => $dailyWeeklyService->generateWeeklyCreditCommitteeReport(),
    'Weekly Collections' => $dailyWeeklyService->generateWeeklyCollectionsPerformanceReport(),
    'Weekly Risk Alerts' => $dailyWeeklyService->generateWeeklyRiskAlertsReport(),
    'Weekly Savings' => $dailyWeeklyService->generateWeeklySavingsMobilizationReport(),
    'Weekly Loan Officer' => $dailyWeeklyService->generateWeeklyLoanOfficerTargetsReport(),
    'Weekly Suspense' => $dailyWeeklyService->generateWeeklySuspenseAccountReport(),
];
$weeklyFile = generateExcelFile($weeklyReports, 'NBC_SACCOS_Weekly_Reports_' . date('Y-m-d') . '.xlsx', $outputDir);
$generatedFiles[] = ['path' => $weeklyFile, 'name' => basename($weeklyFile), 'category' => 'Weekly Reports'];
echo "   ✓ Weekly Reports generated\n";

// ============================================
// MONTHLY CREDIT REPORTS
// ============================================
echo "📊 Generating Monthly Credit Reports...\n";
$monthlyCreditReports = [
    'Loan Portfolio' => $monthlyService->generateMonthlyLoanPortfolioReport(),
    'Delinquency' => $monthlyService->generateMonthlyDelinquencyReport(),
    'NPL Classification' => $monthlyService->generateMonthlyNPLClassificationReport(),
    'Provision' => $monthlyService->generateMonthlyProvisionReport(),
    'Officer Performance' => $monthlyService->generateMonthlyLoanOfficerPerformanceReport(),
    'Product Analysis' => $monthlyService->generateMonthlyProductAnalysisReport(),
];
$monthlyCreditFile = generateExcelFile($monthlyCreditReports, 'NBC_SACCOS_Monthly_Credit_Reports_' . date('Y-m') . '.xlsx', $outputDir);
$generatedFiles[] = ['path' => $monthlyCreditFile, 'name' => basename($monthlyCreditFile), 'category' => 'Monthly Credit Reports'];
echo "   ✓ Monthly Credit Reports generated\n";

// ============================================
// MONTHLY FINANCE REPORTS
// ============================================
echo "📊 Generating Monthly Finance Reports...\n";
$monthlyFinanceReports = [
    'Trial Balance' => $monthlyService->generateMonthlyTrialBalanceReport(),
    'Income Statement' => $monthlyService->generateMonthlyIncomeStatementReport(),
    'Balance Sheet' => $monthlyService->generateMonthlyBalanceSheetReport(),
    'Cash Flow' => $monthlyService->generateMonthlyCashFlowReport(),
    'Budget Variance' => $monthlyService->generateMonthlyBudgetVarianceReport(),
    'Bank Reconciliation' => $monthlyService->generateMonthlyBankReconciliationReport(),
    'Interest Accrual' => $monthlyService->generateMonthlyInterestAccrualReport(),
    'Liquidity' => $monthlyService->generateMonthlyLiquidityReport(),
];
$monthlyFinanceFile = generateExcelFile($monthlyFinanceReports, 'NBC_SACCOS_Monthly_Finance_Reports_' . date('Y-m') . '.xlsx', $outputDir);
$generatedFiles[] = ['path' => $monthlyFinanceFile, 'name' => basename($monthlyFinanceFile), 'category' => 'Monthly Finance Reports'];
echo "   ✓ Monthly Finance Reports generated\n";

// ============================================
// MONTHLY MEMBERSHIP & RISK REPORTS
// ============================================
echo "📊 Generating Monthly Membership & Risk Reports...\n";
$monthlyMembershipRiskReports = [
    'Membership' => $quarterlyAnnualService->generateMonthlyMembershipReport(),
    'Savings Deposits' => $quarterlyAnnualService->generateMonthlySavingsDepositsReport(),
    'Share Capital' => $quarterlyAnnualService->generateMonthlyShareCapitalReport(),
    'Mandatory Savings' => $quarterlyAnnualService->generateMonthlyMandatorySavingsReport(),
    'Risk Assessment' => $quarterlyAnnualService->generateMonthlyRiskAssessmentReport(),
    'Concentration Risk' => $quarterlyAnnualService->generateMonthlyConcentrationRiskReport(),
    'AML Compliance' => $quarterlyAnnualService->generateMonthlyAMLComplianceReport(),
    'Write-off' => $quarterlyAnnualService->generateMonthlyWriteOffReport(),
];
$monthlyMembershipFile = generateExcelFile($monthlyMembershipRiskReports, 'NBC_SACCOS_Monthly_Membership_Risk_Reports_' . date('Y-m') . '.xlsx', $outputDir);
$generatedFiles[] = ['path' => $monthlyMembershipFile, 'name' => basename($monthlyMembershipFile), 'category' => 'Monthly Membership & Risk Reports'];
echo "   ✓ Monthly Membership & Risk Reports generated\n";

// ============================================
// QUARTERLY REPORTS
// ============================================
echo "📊 Generating Quarterly Reports...\n";
$quarterlyReports = [
    'BOT Regulatory' => $quarterlyAnnualService->generateQuarterlyBOTRegulatoryReport(),
    'Capital Adequacy' => $quarterlyAnnualService->generateQuarterlyCapitalAdequacyReport(),
    'Liquid Assets' => $quarterlyAnnualService->generateQuarterlyLiquidAssetsReport(),
    'Large Exposures' => $quarterlyAnnualService->generateQuarterlyLargeExposuresReport(),
    'NPL Return' => $quarterlyAnnualService->generateQuarterlyNPLReturnReport(),
    'TCDC Supervision' => $quarterlyAnnualService->generateQuarterlyTCDCSupervisionReport(),
    'TCDC Membership' => $quarterlyAnnualService->generateQuarterlyTCDCMembershipReport(),
    'Board Pack' => $quarterlyAnnualService->generateQuarterlyBoardPackReport(),
    'Supervisory' => $quarterlyAnnualService->generateQuarterlySupervisoryReport(),
    'Audit' => $quarterlyAnnualService->generateQuarterlyAuditReport(),
    'Risk Report' => $quarterlyAnnualService->generateQuarterlyRiskReport(),
    'Financial Statements' => $quarterlyAnnualService->generateQuarterlyFinancialStatementsReport(),
];
$quarterlyFile = generateExcelFile($quarterlyReports, 'NBC_SACCOS_Quarterly_Reports_Q' . ceil(date('n')/3) . '_' . date('Y') . '.xlsx', $outputDir);
$generatedFiles[] = ['path' => $quarterlyFile, 'name' => basename($quarterlyFile), 'category' => 'Quarterly Reports'];
echo "   ✓ Quarterly Reports generated\n";

// ============================================
// ANNUAL REPORTS
// ============================================
echo "📊 Generating Annual Reports...\n";
$annualReports = [
    'Financial Statements' => $quarterlyAnnualService->generateAnnualFinancialStatementsReport(),
    'AGM Pack' => $quarterlyAnnualService->generateAnnualAGMPackReport(),
    'BOT Return' => $quarterlyAnnualService->generateAnnualBOTReturnReport(),
    'TCDC Return' => $quarterlyAnnualService->generateAnnualTCDCReturnReport(),
];
$annualFile = generateExcelFile($annualReports, 'NBC_SACCOS_Annual_Reports_' . date('Y') . '.xlsx', $outputDir);
$generatedFiles[] = ['path' => $annualFile, 'name' => basename($annualFile), 'category' => 'Annual Reports'];
echo "   ✓ Annual Reports generated\n";

echo "\n📁 Generated " . count($generatedFiles) . " Excel files\n\n";

// ============================================
// SEND EMAILS
// ============================================
echo "📧 Sending emails to $recipient...\n\n";

// Send in batches to avoid large attachments
$batches = [
    [
        'subject' => 'NBC SACCOS Reports - Daily & Weekly Reports',
        'files' => array_filter($generatedFiles, fn($f) => in_array($f['category'], ['Daily Reports', 'Weekly Reports'])),
    ],
    [
        'subject' => 'NBC SACCOS Reports - Monthly Credit & Finance Reports',
        'files' => array_filter($generatedFiles, fn($f) => in_array($f['category'], ['Monthly Credit Reports', 'Monthly Finance Reports'])),
    ],
    [
        'subject' => 'NBC SACCOS Reports - Monthly Membership, Risk & Quarterly Reports',
        'files' => array_filter($generatedFiles, fn($f) => in_array($f['category'], ['Monthly Membership & Risk Reports', 'Quarterly Reports'])),
    ],
    [
        'subject' => 'NBC SACCOS Reports - Annual Reports',
        'files' => array_filter($generatedFiles, fn($f) => $f['category'] === 'Annual Reports'),
    ],
];

$emailsSent = 0;
foreach ($batches as $batch) {
    if (empty($batch['files'])) continue;

    try {
        Mail::send([], [], function (Message $message) use ($batch, $recipient) {
            $message->to($recipient)
                    ->subject($batch['subject']);

            foreach ($batch['files'] as $file) {
                $message->attach($file['path'], [
                    'as' => $file['name'],
                    'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ]);
            }

            $fileList = implode('</li><li>', array_map(fn($f) => "<strong>{$f['name']}</strong> - {$f['category']}", $batch['files']));

            $message->html('
                <html>
                <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                    <div style="max-width: 700px; margin: 0 auto; padding: 20px;">
                        <h2 style="color: #0d2240; border-bottom: 3px solid #0d2240; padding-bottom: 10px;">
                            ' . $batch['subject'] . '
                        </h2>

                        <p>Dear Team,</p>

                        <p>Please find attached the following Excel reports generated from the NBC SACCOS Core Banking System:</p>

                        <div style="background-color: #e8f0f8; border-left: 4px solid #0d2240; padding: 15px; margin: 20px 0;">
                            <h3 style="margin-top: 0; color: #0d2240;">📎 Attached Files</h3>
                            <ul><li>' . $fileList . '</li></ul>
                        </div>

                        <p>Each Excel file contains multiple worksheets with detailed report data.</p>

                        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em;">
                            <p>
                                <strong>Generated:</strong> ' . date('Y-m-d H:i:s') . '<br>
                                <strong>System:</strong> NBC SACCOS Core Banking Application
                            </p>
                        </div>
                    </div>
                </body>
                </html>
            ');
        });

        echo "   ✓ Sent: {$batch['subject']}\n";
        $emailsSent++;

        // Small delay between emails
        sleep(2);

    } catch (Exception $e) {
        echo "   ✗ Failed: {$batch['subject']} - " . $e->getMessage() . "\n";
    }
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  SUMMARY\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Excel Files Generated: " . count($generatedFiles) . "\n";
echo "  Emails Sent: $emailsSent\n";
echo "  Recipient: $recipient\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Files saved to: $outputDir\n";
foreach ($generatedFiles as $file) {
    $size = round(filesize($file['path']) / 1024, 1);
    echo "  • {$file['name']} ({$size} KB)\n";
}

echo "\n✅ Done!\n";
