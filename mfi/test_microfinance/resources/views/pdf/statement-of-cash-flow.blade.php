<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statement of Cash Flow - {{ $institution->name ?? 'NBC SACCO' }}</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 25px;
            background: #fff;
            color: #111827;
            line-height: 1.5;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #1e40af;
        }
        .institution-name {
            font-size: 26px;
            font-weight: bold;
            color: #1e40af;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 20px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .period-info {
            font-size: 14px;
            color: #6b7280;
        }
        .currency {
            font-size: 12px;
            font-style: italic;
            color: #6b7280;
            margin-top: 6px;
        }

        /* Summary Grid - Using Table for reliable PDF support */
        .summary-grid {
            width: 100%;
            margin: 30px 0;
            border-collapse: separate;
            border-spacing: 15px;
        }
        .summary-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            min-height: 80px;
            width: 33.33%;
            vertical-align: middle;
        }

        .summary-label {
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 6px;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
        }
        .positive { color: #059669; }
        .negative { color: #dc2626; }

        /* Table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .details-table th, 
        .details-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .details-table th {
            background: #1f2937;
            color: #fff;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            text-align: left;
        }
        .details-table th.amount,
        .details-table td.amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        .sub-item {
            padding-left: 25px;
            color: #4b5563;
            font-size: 13px;
        }

        /* Section Headers */
        .section-header td {
            background: #1e40af;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13px;
        }

        /* Net Totals */
        .net-total td {
            font-weight: bold;
            background: #f3f4f6;
            border-top: 2px solid #374151;
        }
        .final-summary td {
            background: #111827;
            color: #fff;
            font-weight: bold;
            font-size: 14px;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="institution-name">{{ $institution->name ?? 'MFI Management System' }}</div>
        <div class="report-title">Statement of Cash Flow</div>
        <div class="period-info">
            For the period {{ $statementData['period']['start_date'] }} – {{ $statementData['period']['end_date'] }}
        </div>
        <div class="currency">(All amounts in Tanzanian Shillings)</div>
    </div>

    @if(isset($statementData) && !empty($statementData))
        <!-- Summary -->
        <table class="summary-grid">
            <tr>
                <td class="summary-card">
                    <div class="summary-label">Operating</div>
                    <div class="summary-value {{ $statementData['cash_flow_summary']['net_operating_cash_flow'] >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($statementData['cash_flow_summary']['net_operating_cash_flow'] ?? 0, 2) }}
                    </div>
                </td>
                <td class="summary-card">
                    <div class="summary-label">Investing</div>
                    <div class="summary-value {{ $statementData['cash_flow_summary']['net_investing_cash_flow'] >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($statementData['cash_flow_summary']['net_investing_cash_flow'] ?? 0, 2) }}
                    </div>
                </td>
                <td class="summary-card">
                    <div class="summary-label">Financing</div>
                    <div class="summary-value {{ $statementData['cash_flow_summary']['net_financing_cash_flow'] >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($statementData['cash_flow_summary']['net_financing_cash_flow'] ?? 0, 2) }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Details -->
        <table class="details-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="amount">Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                <!-- Operating -->
                <tr class="section-header"><td colspan="2">Operating Activities</td></tr>
                @foreach(($statementData['operating_activities']['income_details'] ?? []) as $income)
                    <tr><td class="sub-item">{{ $income['account_name'] }}</td><td class="amount positive">{{ number_format($income['amount'], 2) }}</td></tr>
                @endforeach
                @foreach(($statementData['operating_activities']['expense_details'] ?? []) as $expense)
                    <tr><td class="sub-item">{{ $expense['account_name'] }}</td><td class="amount negative">{{ number_format($expense['amount'], 2) }}</td></tr>
                @endforeach
                <tr class="net-total"><td>Net Cash from Operating</td>
                    <td class="amount {{ $statementData['operating_activities']['net_cash_flow'] >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($statementData['operating_activities']['net_cash_flow'], 2) }}
                    </td>
                </tr>

                <!-- Investing -->
                <tr class="section-header"><td colspan="2">Investing Activities</td></tr>
                @foreach(($statementData['investing_activities']['sale_details'] ?? []) as $sale)
                    <tr><td class="sub-item">Sale of {{ $sale['account_name'] }}</td><td class="amount positive">{{ number_format($sale['amount'], 2) }}</td></tr>
                @endforeach
                @foreach(($statementData['investing_activities']['purchase_details'] ?? []) as $purchase)
                    <tr><td class="sub-item">Purchase of {{ $purchase['account_name'] }}</td><td class="amount negative">{{ number_format($purchase['amount'], 2) }}</td></tr>
                @endforeach
                <tr class="net-total"><td>Net Cash from Investing</td>
                    <td class="amount {{ $statementData['investing_activities']['net_cash_flow'] >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($statementData['investing_activities']['net_cash_flow'], 2) }}
                    </td>
                </tr>

                <!-- Financing -->
                <tr class="section-header"><td colspan="2">Financing Activities</td></tr>
                @foreach(($statementData['financing_activities']['loan_proceed_details'] ?? []) as $proceed)
                    <tr><td class="sub-item">{{ $proceed['account_name'] }} Proceeds</td><td class="amount positive">{{ number_format($proceed['amount'], 2) }}</td></tr>
                @endforeach
                @foreach(($statementData['financing_activities']['capital_contribution_details'] ?? []) as $contribution)
                    <tr><td class="sub-item">{{ $contribution['account_name'] }} Contribution</td><td class="amount positive">{{ number_format($contribution['amount'], 2) }}</td></tr>
                @endforeach
                @foreach(($statementData['financing_activities']['loan_repayment_details'] ?? []) as $repayment)
                    <tr><td class="sub-item">{{ $repayment['account_name'] }} Repayment</td><td class="amount negative">{{ number_format($repayment['amount'], 2) }}</td></tr>
                @endforeach
                @foreach(($statementData['financing_activities']['capital_withdrawal_details'] ?? []) as $withdrawal)
                    <tr><td class="sub-item">{{ $withdrawal['account_name'] }} Withdrawal</td><td class="amount negative">{{ number_format($withdrawal['amount'], 2) }}</td></tr>
                @endforeach
                <tr class="net-total"><td>Net Cash from Financing</td>
                    <td class="amount {{ $statementData['financing_activities']['net_cash_flow'] >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($statementData['financing_activities']['net_cash_flow'], 2) }}
                    </td>
                </tr>

                <!-- Summary -->
                <tr class="final-summary"><td>Net Increase (Decrease) in Cash</td>
                    <td class="amount">{{ number_format($statementData['cash_flow_summary']['net_cash_flow'], 2) }}</td>
                </tr>
                <tr><td>Cash at Beginning of Period</td><td class="amount">{{ number_format($statementData['cash_flow_summary']['beginning_cash'], 2) }}</td></tr>
                <tr class="final-summary"><td>Cash at End of Period</td>
                    <td class="amount">{{ number_format($statementData['cash_flow_summary']['ending_cash'], 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            Generated on {{ now()->format('F d, Y \a\t g:i A') }}
        </div>
    @else
        <div style="text-align:center; padding:40px; color:#6b7280;">No cash flow data available for the selected period.</div>
    @endif
</body>
</html>
