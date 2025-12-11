<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduled Report - {{ $report->report_type }}</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .report-info {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .report-info h2 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 18px;
            font-weight: 600;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 14px;
            color: #2c3e50;
            font-weight: 500;
        }
        .summary-section {
            margin-bottom: 25px;
        }
        .summary-section h3 {
            color: #2c3e50;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        .summary-item {
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #667eea;
        }
        .summary-value {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .summary-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
        }
        .attachment-section {
            background-color: #e3f2fd;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .attachment-section h3 {
            color: #1976d2;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .attachment-info {
            display: flex;
            align-items: center;
            background-color: white;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }
        .attachment-icon {
            width: 40px;
            height: 40px;
            background-color: #1976d2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .attachment-icon svg {
            width: 20px;
            height: 20px;
            fill: white;
        }
        .attachment-details h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
            font-size: 14px;
            font-weight: 600;
        }
        .attachment-details p {
            margin: 0;
            color: #6c757d;
            font-size: 12px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 0;
            color: #6c757d;
            font-size: 12px;
        }
        .compliance-badges {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 15px;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-bot {
            background-color: #28a745;
            color: white;
        }
        .badge-ifrs {
            background-color: #007bff;
            color: white;
        }
        .badge-internal {
            background-color: #6c757d;
            color: white;
        }
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .compliance-badges {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📊 Scheduled Report Generated</h1>
            <p>Your requested financial report is ready for review</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Report Information -->
            <div class="report-info">
                <h2>📋 Report Details</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Report Type</span>
                        <span class="info-value">{{ $report->report_type }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Generated At</span>
                        <span class="info-value">{{ $generatedAt->format('M d, Y H:i:s') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Frequency</span>
                        <span class="info-value">{{ ucfirst($report->frequency) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">{{ ucfirst($report->status) }}</span>
                    </div>
                </div>

                <!-- Compliance Badges -->
                <div class="compliance-badges">
                    @if(isset($reportData['report_info']['compliance']))
                        @foreach($reportData['report_info']['compliance'] as $compliance)
                            <span class="badge badge-{{ strtolower($compliance) }}">{{ $compliance }}</span>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Report Summary -->
            @if(isset($reportData['totals']))
            <div class="summary-section">
                <h3>📈 Key Financial Metrics</h3>
                <div class="summary-grid">
                    @if(isset($reportData['totals']['total_assets']))
                    <div class="summary-item">
                        <div class="summary-value">TZS {{ number_format($reportData['totals']['total_assets']) }}</div>
                        <div class="summary-label">Total Assets</div>
                    </div>
                    @endif
                    
                    @if(isset($reportData['totals']['total_liabilities']))
                    <div class="summary-item">
                        <div class="summary-value">TZS {{ number_format($reportData['totals']['total_liabilities']) }}</div>
                        <div class="summary-label">Total Liabilities</div>
                    </div>
                    @endif
                    
                    @if(isset($reportData['totals']['total_equity']))
                    <div class="summary-item">
                        <div class="summary-value">TZS {{ number_format($reportData['totals']['total_equity']) }}</div>
                        <div class="summary-label">Total Equity</div>
                    </div>
                    @endif
                    
                    @if(isset($reportData['totals']['net_income']))
                    <div class="summary-item">
                        <div class="summary-value">TZS {{ number_format($reportData['totals']['net_income']) }}</div>
                        <div class="summary-label">Net Income</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Attachment Section -->
            <div class="attachment-section">
                <h3>📎 Report Attachment</h3>
                <div class="attachment-info">
                    <div class="attachment-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                    </div>
                    <div class="attachment-details">
                        <h4>{{ $report->report_type }} Report</h4>
                        <p>Generated on {{ $generatedAt->format('M d, Y \a\t H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            @if(isset($reportData['report_info']['description']))
            <div class="summary-section">
                <h3>ℹ️ Report Description</h3>
                <p style="color: #6c757d; font-size: 14px; line-height: 1.6;">
                    {{ $reportData['report_info']['description'] }}
                </p>
            </div>
            @endif

            <!-- Custom Message -->
            @if($report->email_message)
            <div class="summary-section">
                <h3>💬 Additional Notes</h3>
                <p style="color: #2c3e50; font-size: 14px; line-height: 1.6; background-color: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #667eea;">
                    {{ $report->email_message }}
                </p>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>MFI Core System</strong> - Professional Financial Reporting</p>
            <p>This is an automated report generated by the system. Please contact your system administrator for any questions.</p>
            <p style="margin-top: 10px; font-size: 11px; color: #adb5bd;">
                Generated on {{ $generatedAt->format('Y-m-d H:i:s') }} | 
                Report ID: {{ $report->id ?? 'N/A' }}
            </p>
        </div>
    </div>
</body>
</html> 