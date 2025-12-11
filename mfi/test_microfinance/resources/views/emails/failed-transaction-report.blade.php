<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Failed Transaction Report</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #dc3545;
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            margin-top: 10px;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .summary-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .summary-box h3 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .summary-stats {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .stat-item {
            text-align: center;
            padding: 10px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
        }
        .stat-label {
            font-size: 12px;
            color: #6c757d;
        }
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 13px;
        }
        .transaction-table th {
            background-color: #343a40;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
        }
        .transaction-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
        }
        .transaction-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .transaction-table tr:hover {
            background-color: #e9ecef;
        }
        .error-badge {
            background-color: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
        }
        .amount {
            font-weight: bold;
            color: #dc3545;
        }
        .reference {
            font-family: monospace;
            font-size: 12px;
            color: #495057;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
        .action-required {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #721c24;
        }
        .user-notice {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Failed Transaction Report</h1>
            <div class="subtitle">Generated: {{ $reportTime }}</div>
        </div>

        <div class="content">
            @if($recipientType === 'tech')
                <p>Dear <strong>Technical Support Team</strong>,</p>

                <div class="action-required">
                    <strong>Action Required:</strong> The following transaction(s) have failed and require investigation.
                </div>
            @else
                <p>Dear <strong>{{ $userName ?? 'Valued User' }}</strong>,</p>

                <div class="user-notice">
                    <strong>Notice:</strong> We regret to inform you that the following transaction(s) initiated by you were unsuccessful. Our technical team has been notified and is investigating the issue.
                </div>
            @endif

            <div class="summary-box">
                <h3>Summary</h3>
                <div class="summary-stats">
                    <div class="stat-item">
                        <div class="stat-value">{{ $transactionCount }}</div>
                        <div class="stat-label">Failed Transaction(s)</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">TZS {{ number_format($totalAmount, 2) }}</div>
                        <div class="stat-label">Total Amount</div>
                    </div>
                </div>
            </div>

            <h3>Transaction Details</h3>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Failed At</th>
                        @if($recipientType === 'tech')
                        <th>Error Code</th>
                        @endif
                        <th>Error Message</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $txn)
                    <tr>
                        <td class="reference">{{ $txn->reference ?? $txn->transaction_uuid }}</td>
                        <td>{{ $txn->type ?? 'N/A' }}</td>
                        <td class="amount">{{ $txn->currency ?? 'TZS' }} {{ number_format($txn->amount, 2) }}</td>
                        <td>{{ $txn->failed_at ? $txn->failed_at->format('d M Y H:i') : 'N/A' }}</td>
                        @if($recipientType === 'tech')
                        <td>
                            @if($txn->error_code)
                            <span class="error-badge">{{ $txn->error_code }}</span>
                            @else
                            -
                            @endif
                        </td>
                        @endif
                        <td>{{ Str::limit($txn->error_message ?? $txn->failure_reason ?? 'Unknown error', 60) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($recipientType === 'tech')
                <h3>Additional Information</h3>
                @foreach($transactions as $txn)
                <div style="background-color: #f8f9fa; padding: 15px; margin-bottom: 15px; border-radius: 5px; border-left: 4px solid #dc3545;">
                    <strong>Transaction: {{ $txn->reference }}</strong><br>
                    <small>
                        <strong>External System:</strong> {{ $txn->external_system ?? 'N/A' }}<br>
                        <strong>External Ref:</strong> {{ $txn->external_reference ?? 'N/A' }}<br>
                        <strong>External Status:</strong> {{ $txn->external_status_code ?? 'N/A' }} - {{ $txn->external_status_message ?? 'N/A' }}<br>
                        <strong>Account ID:</strong> {{ $txn->account_id ?? 'N/A' }}<br>
                        <strong>Initiated By:</strong> {{ $txn->initiated_by ?? 'System' }}<br>
                        <strong>Channel:</strong> {{ $txn->channel_code ?? $txn->source ?? 'N/A' }}<br>
                        @if($txn->error_details)
                        <strong>Error Details:</strong><br>
                        <pre style="background: #343a40; color: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 11px;">{{ is_array($txn->error_details) ? json_encode($txn->error_details, JSON_PRETTY_PRINT) : $txn->error_details }}</pre>
                        @endif
                    </small>
                </div>
                @endforeach

                <p><strong>Recommended Actions:</strong></p>
                <ol>
                    <li>Review the error codes and messages for each transaction</li>
                    <li>Check external system connectivity and status</li>
                    <li>Verify account balances and transaction limits</li>
                    <li>Contact external service provider if necessary</li>
                    <li>Update transaction status after resolution</li>
                </ol>
            @else
                <p>If you need immediate assistance, please contact our support team:</p>
                <ul>
                    <li>Phone: +255 22 219 7000</li>
                    <li>Email: support@nbcsaccos.co.tz</li>
                    <li>Visit any NBC Bank branch</li>
                </ul>

                <p>Please keep your transaction reference number(s) handy when contacting support.</p>
            @endif

            <p>Best regards,<br>
            <strong>MFI Management System System</strong></p>
        </div>

        <div class="footer">
            <p>This is an automated email from the MFI Management System transaction monitoring system.</p>
            <p>&copy; {{ date('Y') }} NBC Bank. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
