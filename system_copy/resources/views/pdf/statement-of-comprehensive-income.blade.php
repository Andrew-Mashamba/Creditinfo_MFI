<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Statement of Comprehensive Income</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 20px;
            color: #333;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .header h2 {
            font-size: 16px;
            color: #666;
            margin: 5px 0;
            font-weight: normal;
        }
        .header .compliance {
            font-size: 10px;
            color: #888;
            margin-top: 10px;
        }
        .period-info {
            text-align: center;
            margin-bottom: 25px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        .financial-statement {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .financial-statement th {
            background-color: #e9ecef;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
            font-size: 12px;
        }
        .financial-statement td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .financial-statement tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .section-header {
            background-color: #d1ecf1 !important;
            font-weight: bold;
            font-size: 13px;
            color: #0c5460;
        }
        .account-row {
            font-size: 11px;
        }
        .account-name {
            padding-left: 15px;
        }
        .total-row {
            background-color: #fff3cd !important;
            font-weight: bold;
            border-top: 2px solid #333;
        }
        .grand-total-row {
            background-color: #d4edda !important;
            font-weight: bold;
            font-size: 12px;
            border-top: 3px solid #333;
            border-bottom: 3px solid #333;
        }
        .net-income-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .performance-summary {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
        .performance-summary.profitable {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .performance-summary.loss {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        .summary-box {
            background-color: #f1f5f9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .summary-box h3 {
            font-size: 12px;
            color: #333;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
        }
        .amount {
            text-align: right;
            font-family: monospace;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>STATEMENT OF COMPREHENSIVE INCOME Z</h1>
        <h2>For the period from {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}</h2>
        <div class="compliance">
            BOT Regulatory Requirements Compliant | Prepared in accordance with IFRS
        </div>
    </div>

    <div class="period-info">
        <strong>Reporting Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }} | 
        <strong>Currency:</strong> {{ $currency }} | 
        <strong>Generated:</strong> {{ $reportDate }}
    </div>

    <!-- Financial Summary -->
    <div class="summary-grid">
        <div class="summary-box">
            <h3>Income Statement Summary</h3>
            <div class="summary-row">
                <span>Total Revenue:</span>
                <span class="amount">{{ $currency }} {{ number_format($totalIncome, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Total Expenses:</span>
                <span class="amount">{{ $currency }} {{ number_format($totalExpenses, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Net Income:</span>
                <span class="amount">{{ $currency }} {{ number_format($netIncome, 2) }}</span>
            </div>
        </div>
        
        <div class="summary-box">
            <h3>Performance Metrics</h3>
            <div class="summary-row">
                <span>Profit Margin:</span>
                <span class="amount">{{ $totalIncome > 0 ? number_format(($netIncome / $totalIncome) * 100, 1) . '%' : 'N/A' }}</span>
            </div>
            <div class="summary-row">
                <span>Expense Ratio:</span>
                <span class="amount">{{ $totalIncome > 0 ? number_format(($totalExpenses / $totalIncome) * 100, 1) . '%' : 'N/A' }}</span>
            </div>
            <div class="summary-row">
                <span>Performance:</span>
                <span class="amount">{{ $netIncome > 0 ? 'Profitable' : 'Loss' }}</span>
            </div>
        </div>
    </div>

    <!-- Main Financial Statement -->
    <table class="financial-statement">
        <thead>
            <tr>
                <th style="width: 60%;">Account Description</th>
                <th style="width: 40%;" class="amount">Amount ({{ $currency }})</th>
            </tr>
        </thead>
        <tbody>
            <!-- REVENUE SECTION -->
            <tr class="section-header">
                <td colspan="2">REVENUE</td>
            </tr>
            @forelse($income as $categoryCode => $category)
                <tr class="account-row">
                    <td class="account-name"><strong>{{ $category['name'] }}</strong></td>
                    <td class="amount"></td>
                </tr>
                @foreach($category['accounts'] as $account)
                    <tr class="account-row">
                        <td class="account-name" style="padding-left: 30px;">{{ is_object($account) ? $account->account_name : $account['account_name'] }}</td>
                        <td class="amount">{{ number_format(is_object($account) ? $account->current_balance : $account['current_balance'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="account-row">
                    <td class="account-name" style="padding-left: 15px;"><strong>Subtotal</strong></td>
                    <td class="amount"><strong>{{ number_format($category['subtotal'], 2) }}</strong></td>
                </tr>
                <tr style="height: 5px;"><td colspan="2"></td></tr>
            @empty
                <tr class="account-row">
                    <td class="account-name">No revenue accounts found</td>
                    <td class="amount">0.00</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td><strong>TOTAL REVENUE</strong></td>
                <td class="amount"><strong>{{ number_format($totalIncome, 2) }}</strong></td>
            </tr>

            <!-- EXPENSES SECTION -->
            <tr style="height: 15px;"><td colspan="2"></td></tr>
            <tr class="section-header">
                <td colspan="2">EXPENSES</td>
            </tr>
            @forelse($expenses as $categoryCode => $category)
                <tr class="account-row">
                    <td class="account-name"><strong>{{ $category['name'] }}</strong></td>
                    <td class="amount"></td>
                </tr>
                @foreach($category['accounts'] as $account)
                    <tr class="account-row">
                        <td class="account-name" style="padding-left: 30px;">{{ is_object($account) ? $account->account_name : $account['account_name'] }}</td>
                        <td class="amount">{{ number_format(is_object($account) ? $account->current_balance : $account['current_balance'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="account-row">
                    <td class="account-name" style="padding-left: 15px;"><strong>Subtotal</strong></td>
                    <td class="amount"><strong>{{ number_format($category['subtotal'], 2) }}</strong></td>
                </tr>
                <tr style="height: 5px;"><td colspan="2"></td></tr>
            @empty
                <tr class="account-row">
                    <td class="account-name">No expense accounts found</td>
                    <td class="amount">0.00</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td><strong>TOTAL EXPENSES</strong></td>
                <td class="amount"><strong>{{ number_format($totalExpenses, 2) }}</strong></td>
            </tr>

            <!-- NET INCOME SECTION -->
            <tr style="height: 15px;"><td colspan="2"></td></tr>
            <tr class="grand-total-row">
                <td><strong>NET INCOME (LOSS)</strong></td>
                <td class="amount"><strong>{{ number_format($netIncome, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Performance Summary -->
    <div class="performance-summary {{ $netIncome > 0 ? 'profitable' : 'loss' }}">
        @if($netIncome > 0)
            <strong>✓ PROFITABLE PERIOD</strong><br>
            Net Income of {{ $currency }} {{ number_format($netIncome, 2) }}
        @else
            <strong>⚠ LOSS PERIOD</strong><br>
            Net Loss of {{ $currency }} {{ number_format(abs($netIncome), 2) }}
        @endif
    </div>

    <div class="footer">
        <div class="footer-grid">
            <div>
                <strong>Prepared by:</strong><br>
                NBC SACCOS<br>
                Financial Reporting System
            </div>
            <div>
                <strong>Report ID:</strong> 38<br>
                <strong>Standard:</strong> IFRS<br>
                <strong>Compliance:</strong> BOT Regulatory
            </div>
            <div>
                <strong>Generated:</strong> {{ $reportDate }}<br>
                <strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M Y') }}<br>
                <strong>Status:</strong> Verified
            </div>
        </div>
        <hr style="margin: 15px 0;">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Statement of Comprehensive Income prepared in accordance with International Financial Reporting Standards (IFRS) and Bank of Tanzania regulatory requirements.</p>
    </div>
</body>
</html>