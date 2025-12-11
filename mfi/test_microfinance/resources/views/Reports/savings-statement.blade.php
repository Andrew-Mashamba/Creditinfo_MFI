<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings Account Statement</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        .account-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .account-details, .statement-details {
            flex: 1;
        }
        .account-details h3, .statement-details h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #2c3e50;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            width: 120px;
        }
        .info-value {
            flex: 1;
        }
        .summary {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #2c3e50;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
        }
        .summary-label {
            font-weight: bold;
        }
        .summary-value {
            text-align: right;
        }
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .transactions-table th {
            background-color: #2c3e50;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
        }
        .transactions-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        .transactions-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .credit {
            color: #27ae60;
            font-weight: bold;
        }
        .debit {
            color: #e74c3c;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>MFI CORE SYSTEM</h1>
        <p>Savings Account Statement</p>
        <p>Generated on: {{ $generatedAt->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="account-info">
        <div class="account-details">
            <h3>Account Information</h3>
            <div class="info-row">
                <span class="info-label">Account Number:</span>
                <span class="info-value">{{ $account->account_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Account Name:</span>
                <span class="info-value">{{ $account->account_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Member Name:</span>
                <span class="info-value">{{ $account->client->first_name ?? '' }} {{ $account->client->last_name ?? '' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Member Number:</span>
                <span class="info-value">{{ $account->client_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Product:</span>
                <span class="info-value">{{ $account->shareProduct->product_name ?? 'Savings Account' }}</span>
            </div>
        </div>
        
        <div class="statement-details">
            <h3>Statement Period</h3>
            <div class="info-row">
                <span class="info-label">From Date:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">To Date:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Generated By:</span>
                <span class="info-value">{{ $generatedBy }}</span>
            </div>
        </div>
    </div>

    <div class="summary">
        <h3>Statement Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <span class="summary-label">Opening Balance:</span>
                <span class="summary-value">TZS {{ number_format($openingBalance, 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Credits:</span>
                <span class="summary-value credit">TZS {{ number_format($totalCredits, 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Debits:</span>
                <span class="summary-value debit">TZS {{ number_format($totalDebits, 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Closing Balance:</span>
                <span class="summary-value">TZS {{ number_format($closingBalance, 2) }}</span>
            </div>
        </div>
    </div>

    <h3>Transaction History</h3>
    @if($transactions->count() > 0)
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                @php $runningBalance = $openingBalance; @endphp
                @foreach($transactions as $transaction)
                    @php 
                        $runningBalance += ($transaction->credit ?? 0) - ($transaction->debit ?? 0);
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y') }}</td>
                        <td>{{ $transaction->reference_number ?? 'N/A' }}</td>
                        <td>{{ $transaction->narration ?? 'Transaction' }}</td>
                        <td class="debit">{{ $transaction->debit > 0 ? 'TZS ' . number_format($transaction->debit, 2) : '-' }}</td>
                        <td class="credit">{{ $transaction->credit > 0 ? 'TZS ' . number_format($transaction->credit, 2) : '-' }}</td>
                        <td>TZS {{ number_format($runningBalance, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #666; font-style: italic;">No transactions found for this period.</p>
    @endif

    <div class="footer">
        <div class="footer-grid">
            <div>
                <p><strong>Important Notes:</strong></p>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    <li>This statement is computer generated</li>
                    <li>Please report any discrepancies within 30 days</li>
                    <li>For queries, contact your branch office</li>
                </ul>
            </div>
            <div>
                <p><strong>Contact Information:</strong></p>
                <p>Email: info@saccos.com<br>
                Phone: +255 22 1234567<br>
                Address: P.O. Box 123, Dar es Salaam</p>
            </div>
        </div>
    </div>
</body>
</html>
