<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Statement</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .info-box h3 {
            font-size: 14px;
            color: #333;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .transactions-table th {
            background-color: #f1f5f9;
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .transactions-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .transactions-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Account Statement</h1>
        <p>Generated on: {{ now()->format('Y-M-d H:i:s') }}</p>
    </div>

    @foreach ($accounts as $account)
    <div class="info-section">
        <div class="info-grid">
            <div class="info-box">
                <h3>Account Information</h3>
                <div class="info-row">
                    <span>Account Name:</span>
                    <span>{{ $account->account_name }}</span>
                </div>
                <div class="info-row">
                    <span>Account Number:</span>
                    <span>{{ $account->account_number }}</span>
                </div>
                <div class="info-row">
                    <span>Start Date:</span>
                    <span>{{ $account->created_at ? $account->created_at->format('Y-m-d') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span>Category:</span>
                    <span>{{ ucwords(str_replace('_', ' ', $account->type ?? 'Unknown')) }}</span>
                </div>
            </div>

            <div class="info-box">
                <h3>Financial Summary</h3>
                <div class="info-row">
                    <span>Currency:</span>
                    <span>TZS</span>
                </div>
                <div class="info-row">
                    <span>Total Credits:</span>
                    <span>{{ number_format($transactions->sum('credit'), 2) }}</span>
                </div>
                <div class="info-row">
                    <span>Total Debits:</span>
                    <span>{{ number_format($transactions->sum('debit'), 2) }}</span>
                </div>
                <div class="info-row">
                    <span>Current Balance:</span>
                    <span>{{ number_format($account->balance ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <h2>Transaction History</h2>
    <table class="transactions-table">
        <thead>
            <tr>
                <th>S/N</th>
                <th>Date</th>
                <th>Reference</th>
                <th>Narration</th>
                <th>Credit</th>
                <th>Debit</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @php
                $runningBalance = $account->balance ?? 0;
            @endphp
            @foreach ($transactions as $transaction)
            @php
                $credit = (float)($transaction->credit ?? 0);
                $debit = (float)($transaction->debit ?? 0);
                $runningBalance = $runningBalance - $credit + $debit;
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                <td>{{ $transaction->reference_number ?? 'N/A' }}</td>
                <td>{{ $transaction->narration ?? 'No description' }}</td>
                <td>{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
                <td>{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                <td>{{ number_format($runningBalance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
        @if($account->branch_number)
            <p>Branch: {{ DB::table('branches')->where('id', $account->branch_number)->value('name') ?? 'N/A' }}</p>
        @endif
    </div>
    @endforeach
</body>
</html> 