<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Payment Summary</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
        }
        h1 {
            margin: 0;
            font-size: 28px;
        }
        .date {
            font-size: 14px;
            opacity: 0.9;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        .summary-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .summary-card h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #495057;
        }
        .metric {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }
        .metric-label {
            color: #6c757d;
        }
        .metric-value {
            font-weight: bold;
            color: #212529;
        }
        .amount {
            color: #007bff;
            font-size: 20px;
        }
        .urgent {
            color: #dc3545;
        }
        .warning {
            color: #ffc107;
        }
        .success {
            color: #28a745;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-overdue {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-upcoming {
            background-color: #fff3cd;
            color: #856404;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #6c757d;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Daily Payment Summary</h1>
            <div class="date">{{ \Carbon\Carbon::parse($summary['date'])->format('l, d F Y') }}</div>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <h3>📤 Payables Overview</h3>
                <div class="metric">
                    <span class="metric-label">Upcoming (7 days):</span>
                    <span class="metric-value">{{ $summary['upcoming_payables']->count ?? 0 }}</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Amount Due:</span>
                    <span class="metric-value amount">{{ number_format($summary['upcoming_payables']->total ?? 0, 2) }} TZS</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Overdue:</span>
                    <span class="metric-value urgent">{{ $summary['overdue_payables']->count ?? 0 }}</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Overdue Amount:</span>
                    <span class="metric-value urgent">{{ number_format($summary['overdue_payables']->total ?? 0, 2) }} TZS</span>
                </div>
            </div>

            <div class="summary-card">
                <h3>📥 Receivables Overview</h3>
                <div class="metric">
                    <span class="metric-label">Expected (7 days):</span>
                    <span class="metric-value">{{ $summary['upcoming_receivables']->count ?? 0 }}</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Expected Amount:</span>
                    <span class="metric-value amount">{{ number_format($summary['upcoming_receivables']->total ?? 0, 2) }} TZS</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Overdue:</span>
                    <span class="metric-value warning">{{ $summary['overdue_receivables']->count ?? 0 }}</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Overdue Amount:</span>
                    <span class="metric-value warning">{{ number_format($summary['overdue_receivables']->total ?? 0, 2) }} TZS</span>
                </div>
            </div>
        </div>

        @if(!empty($summary['upcoming_payables_list']) && count($summary['upcoming_payables_list']) > 0)
        <div style="margin: 40px 0;">
            <h2 style="color: #007bff; font-size: 20px;">📋 Upcoming Payables (Next 7 Days)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Bill #</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Days Until Due</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['upcoming_payables_list'] as $payable)
                    <tr>
                        <td><strong>{{ $payable->vendor_name }}</strong></td>
                        <td>{{ $payable->bill_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($payable->due_date)->format('d M Y') }}</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($payable->balance, 2) }}</td>
                        <td>
                            @php
                                $daysUntil = \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($payable->due_date), false);
                            @endphp
                            <span class="status-badge status-upcoming">{{ $daysUntil }} days</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(!empty($summary['overdue_payables_list']) && count($summary['overdue_payables_list']) > 0)
        <div style="margin: 40px 0;">
            <h2 style="color: #dc3545; font-size: 20px;">⚠️ Overdue Payables - URGENT</h2>
            <table>
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Bill #</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Days Overdue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['overdue_payables_list'] as $payable)
                    <tr>
                        <td><strong>{{ $payable->vendor_name }}</strong></td>
                        <td>{{ $payable->bill_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($payable->due_date)->format('d M Y') }}</td>
                        <td style="text-align: right; font-weight: bold; color: #dc3545;">{{ number_format($payable->balance, 2) }}</td>
                        <td>
                            @php
                                $daysOverdue = \Carbon\Carbon::parse($payable->due_date)->diffInDays(\Carbon\Carbon::today());
                            @endphp
                            <span class="status-badge status-overdue">{{ $daysOverdue }} days</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div style="background-color: #e7f3ff; padding: 20px; border-radius: 8px; margin: 30px 0;">
            <h3 style="margin: 0 0 10px 0; color: #004085;">📊 Summary & Actions Required</h3>
            
            @if(($summary['overdue_payables']->count ?? 0) > 0)
            <p style="color: #dc3545; font-weight: bold;">
                ⚠️ You have {{ $summary['overdue_payables']->count }} overdue payables totaling {{ number_format($summary['overdue_payables']->total, 2) }} TZS that require immediate attention.
            </p>
            @endif
            
            @if(($summary['upcoming_payables']->count ?? 0) > 0)
            <p>
                📅 There are {{ $summary['upcoming_payables']->count }} upcoming payments totaling {{ number_format($summary['upcoming_payables']->total, 2) }} TZS due within the next 7 days.
            </p>
            @endif
            
            @if(($summary['overdue_receivables']->count ?? 0) > 0)
            <p style="color: #856404;">
                💰 {{ $summary['overdue_receivables']->count }} customer payments totaling {{ number_format($summary['overdue_receivables']->total, 2) }} TZS are overdue and require follow-up.
            </p>
            @endif
        </div>

        <div class="footer">
            <p><strong>This is an automated daily summary from the MFI Management System</strong></p>
            <p>For detailed information, please log into the system or contact the accounting department.</p>
            <p style="color: #6c757d; font-size: 11px;">
                Generated on {{ now()->format('d M Y H:i') }} | MFI Core System © {{ date('Y') }}
            </p>
        </div>
    </div>
</body>
</html>