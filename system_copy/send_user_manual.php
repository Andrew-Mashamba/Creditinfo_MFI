#!/usr/bin/env php
<?php

/**
 * Send User Manual via Email
 * Script to email the NBC SACCOS User Manual to recipients
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

// Configuration
$pdfPath = __DIR__ . '/NBC_SACCOS_USER_MANUAL.pdf';
$txtPath = __DIR__ . '/NBC_SACCOS_USER_MANUAL.txt';
$recipient = 'andrew.mashamba@nbc.co.tz';
$subject = 'NBC SACCOS - User Manual (PDF & TXT)';

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  NBC SACCOS USER MANUAL - EMAIL SENDER\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Check if files exist
if (!file_exists($pdfPath)) {
    echo "❌ ERROR: PDF file not found at: $pdfPath\n";
    exit(1);
}

if (!file_exists($txtPath)) {
    echo "❌ ERROR: TXT file not found at: $txtPath\n";
    exit(1);
}

$pdfSize = filesize($pdfPath);
$txtSize = filesize($txtPath);
$pdfSizeKB = round($pdfSize / 1024, 1);
$txtSizeKB = round($txtSize / 1024, 1);

echo "📄 Files to send:\n";
echo "   • NBC_SACCOS_USER_MANUAL.pdf ({$pdfSizeKB} KB)\n";
echo "   • NBC_SACCOS_USER_MANUAL.txt ({$txtSizeKB} KB)\n";
echo "📧 Recipient: $recipient\n";
echo "📋 Subject: $subject\n\n";

echo "Sending email...\n";

try {
    Mail::send([], [], function (Message $message) use ($pdfPath, $txtPath, $recipient, $subject) {
        $message->to($recipient)
                ->subject($subject)
                ->attach($pdfPath, [
                    'as' => 'NBC_SACCOS_USER_MANUAL.pdf',
                    'mime' => 'application/pdf'
                ])
                ->attach($txtPath, [
                    'as' => 'NBC_SACCOS_USER_MANUAL.txt',
                    'mime' => 'text/plain'
                ])
                ->html('
                    <html>
                    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                        <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                            <h2 style="color: #0d2240; border-bottom: 3px solid #0d2240; padding-bottom: 10px;">
                                NBC SACCOS Core Banking System - User Manual
                            </h2>

                            <p>Dear Team,</p>

                            <p>Please find attached the <strong>NBC SACCOS User Manual</strong> in both PDF and TXT formats.</p>

                            <div style="background-color: #e8f0f8; border-left: 4px solid #0d2240; padding: 15px; margin: 20px 0;">
                                <h3 style="margin-top: 0; color: #0d2240;">📎 Attachments</h3>
                                <ul style="margin: 10px 0; padding-left: 20px;">
                                    <li><strong>NBC_SACCOS_USER_MANUAL.pdf</strong> - Professionally formatted PDF with styled headers and sections</li>
                                    <li><strong>NBC_SACCOS_USER_MANUAL.txt</strong> - Plain text version for reference</li>
                                </ul>
                            </div>

                            <div style="background-color: #f5f8fc; border-left: 4px solid #1a3a6e; padding: 15px; margin: 20px 0;">
                                <h3 style="margin-top: 0; color: #1a3a6e;">📚 Manual Contents (24 Modules)</h3>
                                <table style="width: 100%; font-size: 0.9em;">
                                    <tr>
                                        <td style="padding: 3px 10px;">1. Dashboard</td>
                                        <td style="padding: 3px 10px;">13. Reports Manager</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 10px;">2. Branches</td>
                                        <td style="padding: 3px 10px;">14. Billing</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 10px;">3. Members</td>
                                        <td style="padding: 3px 10px;">15. Expenses</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 10px;">4. Shares</td>
                                        <td style="padding: 3px 10px;">16. Budget Management</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 10px;">5. Savings</td>
                                        <td style="padding: 3px 10px;">17. Institution Settings</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 10px;">6. Deposits</td>
                                        <td style="padding: 3px 10px;">18. Transactions</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 10px;">7. Loans</td>
                                        <td style="padding: 3px 10px;">19. Self Services</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 10px;">8. Accounting</td>
                                        <td style="padding: 3px 10px;">20. Active Loans</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 10px;">9. Products Management</td>
                                        <td style="padding: 3px 10px;">21. Mobile & Web Portal</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 10px;">10. Payments</td>
                                        <td style="padding: 3px 10px;">22. Subscriptions</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 10px;">11. Human Resources</td>
                                        <td style="padding: 3px 10px;">23. Approvals</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 10px;">12. Reconciliation</td>
                                        <td style="padding: 3px 10px;">24. Users Manager</td>
                                    </tr>
                                </table>
                            </div>

                            <h3 style="color: #0d2240;">📖 Each Module Includes:</h3>
                            <ul style="line-height: 1.8;">
                                <li>✅ Description and overview</li>
                                <li>✅ Key features and functionality</li>
                                <li>✅ Sub-modules and sections</li>
                                <li>✅ Forms and data entry capabilities</li>
                                <li>✅ Step-by-step "How To Use" guides</li>
                                <li>✅ Available reports and exports</li>
                            </ul>

                            <h3 style="color: #0d2240;">📋 Appendices:</h3>
                            <ul style="line-height: 1.8;">
                                <li>Keyboard shortcuts</li>
                                <li>Common task checklists (Member Registration, Loan Processing, End of Day)</li>
                                <li>Support contacts</li>
                            </ul>

                            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em;">
                                <p>
                                    <strong>Generated:</strong> ' . date('Y-m-d H:i:s') . '<br>
                                    <strong>System:</strong> NBC SACCOS Core Banking Application<br>
                                    <strong>Version:</strong> 1.0
                                </p>
                            </div>
                        </div>
                    </body>
                    </html>
                ');
    });

    echo "\n✅ SUCCESS! Email sent successfully to $recipient\n\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    exit(0);

} catch (Exception $e) {
    echo "\n❌ ERROR: Failed to send email\n";
    echo "Error Message: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check SMTP settings in .env file\n";
    echo "2. Verify mail server is accessible\n";
    echo "3. Check firewall/network connectivity\n";
    echo "4. Review Laravel logs: storage/logs/laravel.log\n\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    exit(1);
}
