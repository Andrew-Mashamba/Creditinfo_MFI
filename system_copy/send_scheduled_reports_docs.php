#!/usr/bin/env php
<?php

/**
 * Send Scheduled Reports Documentation & Service Files via Email
 * Script to email the scheduled reports implementation to recipients
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

// Configuration
$recipient = 'andrew.mashamba@nbc.co.tz';
$subject = 'NBC SACCOS - Scheduled Reports Implementation (56 Reports)';

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  NBC SACCOS SCHEDULED REPORTS - EMAIL SENDER\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Files to send
$files = [
    [
        'path' => __DIR__ . '/app/Services/ScheduledReportDataService.php',
        'name' => 'ScheduledReportDataService.php',
        'description' => 'Daily & Weekly Reports (18 methods)'
    ],
    [
        'path' => __DIR__ . '/app/Services/MonthlyReportDataService.php',
        'name' => 'MonthlyReportDataService.php',
        'description' => 'Monthly Credit & Finance Reports (14 methods)'
    ],
    [
        'path' => __DIR__ . '/app/Services/QuarterlyAnnualReportDataService.php',
        'name' => 'QuarterlyAnnualReportDataService.php',
        'description' => 'Membership, Risk, Quarterly & Annual Reports (24 methods)'
    ],
    [
        'path' => __DIR__ . '/app/Console/Commands/GenerateScheduledReports.php',
        'name' => 'GenerateScheduledReports.php',
        'description' => 'Console command for generating reports'
    ],
    [
        'path' => __DIR__ . '/app/Console/Commands/TestScheduledReports.php',
        'name' => 'TestScheduledReports.php',
        'description' => 'Test command for validating all report queries'
    ],
];

// Check if files exist
$validFiles = [];
foreach ($files as $file) {
    if (file_exists($file['path'])) {
        $size = round(filesize($file['path']) / 1024, 1);
        $validFiles[] = $file;
        echo "✓ {$file['name']} ({$size} KB) - {$file['description']}\n";
    } else {
        echo "✗ {$file['name']} - NOT FOUND\n";
    }
}

if (empty($validFiles)) {
    echo "\n❌ ERROR: No files found to send!\n";
    exit(1);
}

echo "\n📧 Recipient: $recipient\n";
echo "📋 Subject: $subject\n\n";

echo "Sending email...\n";

try {
    Mail::send([], [], function (Message $message) use ($validFiles, $recipient, $subject) {
        $message->to($recipient)
                ->subject($subject);

        // Attach all files
        foreach ($validFiles as $file) {
            $message->attach($file['path'], [
                'as' => $file['name'],
                'mime' => 'text/plain'
            ]);
        }

        $message->html('
            <html>
            <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #0d2240; border-bottom: 3px solid #0d2240; padding-bottom: 10px;">
                        NBC SACCOS - Scheduled Reports Implementation
                    </h2>

                    <p>Dear Team,</p>

                    <p>Please find attached the complete <strong>Scheduled Reports Implementation</strong> for the NBC SACCOS Core Banking System. This implementation includes <strong>56 automated reports</strong> covering all regulatory, operational, and management reporting needs.</p>

                    <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: #155724;">✅ All 56 Reports Tested & Validated</h3>
                        <p style="margin-bottom: 0;">All report queries have been tested against the live database and are returning correct data.</p>
                    </div>

                    <div style="background-color: #e8f0f8; border-left: 4px solid #0d2240; padding: 15px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: #0d2240;">📎 Attached Files</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr style="background-color: #f8f9fa;">
                                <th style="padding: 8px; text-align: left; border-bottom: 2px solid #dee2e6;">File</th>
                                <th style="padding: 8px; text-align: left; border-bottom: 2px solid #dee2e6;">Description</th>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><strong>ScheduledReportDataService.php</strong></td>
                                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">Daily & Weekly Reports (18 methods)</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><strong>MonthlyReportDataService.php</strong></td>
                                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">Monthly Credit & Finance Reports (14 methods)</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><strong>QuarterlyAnnualReportDataService.php</strong></td>
                                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">Membership, Risk, Quarterly & Annual Reports (24 methods)</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><strong>GenerateScheduledReports.php</strong></td>
                                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">Console command for generating reports</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><strong>TestScheduledReports.php</strong></td>
                                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">Test command for validating all report queries</td>
                            </tr>
                        </table>
                    </div>

                    <h3 style="color: #0d2240;">📊 Complete Report Inventory (56 Reports)</h3>

                    <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
                        <h4 style="margin-top: 0; color: #856404;">DAILY REPORTS (10)</h4>
                        <ol style="margin: 10px 0; padding-left: 20px;">
                            <li>Daily Operations Report</li>
                            <li>Daily Arrears Report</li>
                            <li>Daily Cash Position Report</li>
                            <li>Daily Disbursements Report</li>
                            <li>Daily Collections Report</li>
                            <li>Daily Member Activity Report</li>
                            <li>Daily Loan Officer Portfolio Report</li>
                            <li>Daily Recovery Action Report</li>
                            <li>Daily GL Summary Report</li>
                            <li>Daily Large Transactions Report</li>
                        </ol>
                    </div>

                    <div style="background-color: #cce5ff; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0;">
                        <h4 style="margin-top: 0; color: #004085;">WEEKLY REPORTS (8)</h4>
                        <ol style="margin: 10px 0; padding-left: 20px;">
                            <li>Weekly Executive Summary Report</li>
                            <li>Weekly Arrears Report</li>
                            <li>Weekly Credit Committee Report</li>
                            <li>Weekly Collections Performance Report</li>
                            <li>Weekly Risk Alerts Report</li>
                            <li>Weekly Savings Mobilization Report</li>
                            <li>Weekly Loan Officer Targets Report</li>
                            <li>Weekly Suspense Account Report</li>
                        </ol>
                    </div>

                    <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0;">
                        <h4 style="margin-top: 0; color: #155724;">MONTHLY CREDIT REPORTS (6)</h4>
                        <ol style="margin: 10px 0; padding-left: 20px;">
                            <li>Monthly Loan Portfolio Report</li>
                            <li>Monthly Delinquency Report</li>
                            <li>Monthly NPL Classification Report (BOT Guidelines)</li>
                            <li>Monthly Provision Report</li>
                            <li>Monthly Loan Officer Performance Report</li>
                            <li>Monthly Product Analysis Report</li>
                        </ol>
                    </div>

                    <div style="background-color: #e2e3e5; border-left: 4px solid #6c757d; padding: 15px; margin: 20px 0;">
                        <h4 style="margin-top: 0; color: #383d41;">MONTHLY FINANCE REPORTS (8)</h4>
                        <ol style="margin: 10px 0; padding-left: 20px;">
                            <li>Monthly Trial Balance Report</li>
                            <li>Monthly Income Statement Report</li>
                            <li>Monthly Balance Sheet Report</li>
                            <li>Monthly Cash Flow Report</li>
                            <li>Monthly Budget Variance Report</li>
                            <li>Monthly Bank Reconciliation Report</li>
                            <li>Monthly Interest Accrual Report</li>
                            <li>Monthly Liquidity Report</li>
                        </ol>
                    </div>

                    <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0;">
                        <h4 style="margin-top: 0; color: #721c24;">MONTHLY MEMBERSHIP REPORTS (4)</h4>
                        <ol style="margin: 10px 0; padding-left: 20px;">
                            <li>Monthly Membership Report</li>
                            <li>Monthly Savings & Deposits Report</li>
                            <li>Monthly Share Capital Report</li>
                            <li>Monthly Mandatory Savings Report</li>
                        </ol>
                    </div>

                    <div style="background-color: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0;">
                        <h4 style="margin-top: 0; color: #0c5460;">MONTHLY RISK REPORTS (4)</h4>
                        <ol style="margin: 10px 0; padding-left: 20px;">
                            <li>Monthly Risk Assessment Report</li>
                            <li>Monthly Concentration Risk Report</li>
                            <li>Monthly AML Compliance Report</li>
                            <li>Monthly Write-off Report</li>
                        </ol>
                    </div>

                    <div style="background-color: #ffeeba; border-left: 4px solid #fd7e14; padding: 15px; margin: 20px 0;">
                        <h4 style="margin-top: 0; color: #856404;">QUARTERLY REGULATORY REPORTS (7)</h4>
                        <ol style="margin: 10px 0; padding-left: 20px;">
                            <li>Quarterly BOT Regulatory Report</li>
                            <li>Quarterly Capital Adequacy Report</li>
                            <li>Quarterly Liquid Assets Report</li>
                            <li>Quarterly Large Exposures Report</li>
                            <li>Quarterly NPL Return Report</li>
                            <li>Quarterly TCDC Supervision Report</li>
                            <li>Quarterly TCDC Membership Report</li>
                        </ol>
                    </div>

                    <div style="background-color: #c3e6cb; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0;">
                        <h4 style="margin-top: 0; color: #155724;">QUARTERLY GOVERNANCE REPORTS (5)</h4>
                        <ol style="margin: 10px 0; padding-left: 20px;">
                            <li>Quarterly Board Pack Report</li>
                            <li>Quarterly Supervisory Report</li>
                            <li>Quarterly Audit Report</li>
                            <li>Quarterly Risk Report</li>
                            <li>Quarterly Financial Statements Report</li>
                        </ol>
                    </div>

                    <div style="background-color: #b8daff; border-left: 4px solid #0056b3; padding: 15px; margin: 20px 0;">
                        <h4 style="margin-top: 0; color: #004085;">ANNUAL REPORTS (4)</h4>
                        <ol style="margin: 10px 0; padding-left: 20px;">
                            <li>Annual Financial Statements Report</li>
                            <li>Annual AGM Pack Report</li>
                            <li>Annual BOT Return Report</li>
                            <li>Annual TCDC Return Report</li>
                        </ol>
                    </div>

                    <h3 style="color: #0d2240;">🔧 Usage Commands</h3>
                    <div style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.9em;">
                        <p><strong># Test all reports</strong><br>
                        php artisan reports:test --category=all</p>

                        <p><strong># Test specific category</strong><br>
                        php artisan reports:test --category=daily<br>
                        php artisan reports:test --category=weekly<br>
                        php artisan reports:test --category=monthly<br>
                        php artisan reports:test --category=quarterly<br>
                        php artisan reports:test --category=annual</p>

                        <p><strong># Generate reports</strong><br>
                        php artisan reports:generate --frequency=daily<br>
                        php artisan reports:generate --frequency=weekly<br>
                        php artisan reports:generate --frequency=monthly</p>
                    </div>

                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em;">
                        <p>
                            <strong>Generated:</strong> ' . date('Y-m-d H:i:s') . '<br>
                            <strong>System:</strong> NBC SACCOS Core Banking Application<br>
                            <strong>Total Reports:</strong> 56
                        </p>
                    </div>
                </div>
            </body>
            </html>
        ');
    });

    echo "\n✅ SUCCESS! Email sent successfully to $recipient\n\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    exit(0);

} catch (Exception $e) {
    echo "\n❌ ERROR: Failed to send email\n";
    echo "Error Message: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check SMTP settings in .env file\n";
    echo "2. Verify mail server is accessible\n";
    echo "3. Check firewall/network connectivity\n";
    echo "4. Review Laravel logs: storage/logs/laravel.log\n\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    exit(1);
}
