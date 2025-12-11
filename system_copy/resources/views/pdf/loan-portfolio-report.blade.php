<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Portfolio Report</title>
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
            margin-bottom: 30px;
            border-bottom: 2px solid #366092;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #366092;
            font-size: 24px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        .header h2 {
            color: #666;
            font-size: 16px;
            margin: 0 0 5px 0;
            font-weight: normal;
        }
        
        .header p {
            color: #999;
            font-size: 10px;
            margin: 0;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background-color: #366092;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-cell {
            display: table-cell;
            padding: 8px 12px;
            border: 1px solid #ddd;
            width: 25%;
            text-align: center;
            vertical-align: middle;
        }
        
        .summary-cell.label {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #366092;
        }
        
        .summary-cell.value {
            font-size: 14px;
            font-weight: bold;
        }
        
        .metrics-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .metrics-row {
            display: table-row;
        }
        
        .metrics-cell {
            display: table-cell;
            padding: 8px 12px;
            border: 1px solid #ddd;
            width: 25%;
            text-align: center;
            vertical-align: middle;
        }
        
        .metrics-cell.label {
            background-color: #e8f4fd;
            font-weight: bold;
            color: #366092;
        }
        
        .metrics-cell.value {
            font-size: 14px;
            font-weight: bold;
        }
        
        .risk-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .risk-row {
            display: table-row;
        }
        
        .risk-cell {
            display: table-cell;
            padding: 8px 12px;
            border: 1px solid #ddd;
            width: 50%;
            vertical-align: top;
        }
        
        .risk-cell h4 {
            margin: 0 0 10px 0;
            color: #366092;
            font-size: 13px;
            font-weight: bold;
        }
        
        .risk-item {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px solid #eee;
        }
        
        .risk-item:last-child {
            border-bottom: none;
        }
        
        .risk-item .label {
            font-weight: bold;
        }
        
        .risk-item .value {
            font-weight: bold;
        }
        
        .risk-item .value.high-risk {
            color: #dc3545;
        }
        
        .risk-item .value.medium-risk {
            color: #fd7e14;
        }
        
        .risk-item .value.low-risk {
            color: #28a745;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        
        th {
            background-color: #4472C4;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        
        td {
            padding: 6px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-bold {
            font-weight: bold;
        }
        
        .trend-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .trend-row {
            display: table-row;
        }
        
        .trend-cell {
            display: table-cell;
            padding: 8px 12px;
            border: 1px solid #ddd;
            width: 33.33%;
            text-align: center;
            vertical-align: middle;
        }
        
        .trend-cell.label {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #366092;
        }
        
        .trend-cell.value {
            font-size: 14px;
            font-weight: bold;
        }
        
        .trend-cell.value.positive {
            color: #28a745;
        }
        
        .trend-cell.value.negative {
            color: #dc3545;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .risk-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            min-width: 60px;
        }
        
        .risk-badge.low {
            background-color: #d4edda;
            color: #155724;
        }
        
        .risk-badge.medium {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .risk-badge.high {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .risk-badge.critical {
            background-color: #f5c6cb;
            color: #721c24;
        }
        
        .delinquency-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            min-width: 60px;
        }
        
        .delinquency-badge.current {
            background-color: #d4edda;
            color: #155724;
        }
        
        .delinquency-badge.overdue {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LOAN PORTFOLIO REPORT</h1>
        <h2>As at {{ $reportData['period']['end_date'] }}</h2>
        <p>(All amounts in Tanzanian Shillings)</p>
        <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }}</p>
    </div>

    <!-- Portfolio Summary -->
    <div class="section">
        <div class="section-title">Portfolio Summary</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell label">Total Portfolio</div>
                <div class="summary-cell label">Number of Loans</div>
                <div class="summary-cell label">Average Loan Size</div>
                <div class="summary-cell label">Largest Loan</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell value">{{ number_format($reportData['portfolio_summary']['total_portfolio'], 2) }}</div>
                <div class="summary-cell value">{{ number_format($reportData['portfolio_summary']['number_of_loans']) }}</div>
                <div class="summary-cell value">{{ number_format($reportData['portfolio_summary']['average_loan_size'], 2) }}</div>
                <div class="summary-cell value">{{ number_format($reportData['portfolio_summary']['largest_loan'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Financial Metrics -->
    <div class="section">
        <div class="section-title">Financial Metrics</div>
        <div class="metrics-grid">
            <div class="metrics-row">
                <div class="metrics-cell label">Interest Income</div>
                <div class="metrics-cell label">Portfolio Yield</div>
                <div class="metrics-cell label">Avg Interest Rate</div>
                <div class="metrics-cell label">Provision for Losses</div>
            </div>
            <div class="metrics-row">
                <div class="metrics-cell value">{{ number_format($reportData['financial_metrics']['total_interest_income'], 2) }}</div>
                <div class="metrics-cell value">{{ number_format($reportData['financial_metrics']['portfolio_yield'], 2) }}%</div>
                <div class="metrics-cell value">{{ number_format($reportData['financial_metrics']['average_interest_rate'], 2) }}%</div>
                <div class="metrics-cell value">{{ number_format($reportData['financial_metrics']['provision_for_losses'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Risk Analysis -->
    <div class="section">
        <div class="section-title">Risk Analysis</div>
        <div class="risk-grid">
            <div class="risk-row">
                <div class="risk-cell">
                    <h4>Portfolio at Risk</h4>
                    <div class="risk-item">
                        <span class="label">Total Portfolio at Risk:</span>
                        <span class="value high-risk">{{ number_format($reportData['risk_analysis']['portfolio_at_risk'], 2) }}</span>
                    </div>
                    <div class="risk-item">
                        <span class="label">PAR Ratio:</span>
                        <span class="value high-risk">{{ number_format($reportData['risk_analysis']['portfolio_at_risk_ratio'], 2) }}%</span>
                    </div>
                    <div class="risk-item">
                        <span class="label">NPL Ratio:</span>
                        <span class="value high-risk">{{ number_format($reportData['risk_analysis']['non_performing_loan_ratio'], 2) }}%</span>
                    </div>
                </div>
                <div class="risk-cell">
                    <h4>Risk Distribution</h4>
                    <div class="risk-item">
                        <span class="label">Low Risk:</span>
                        <span class="value low-risk">{{ number_format($reportData['risk_analysis']['risk_distribution']['low_risk']['amount'], 2) }}</span>
                    </div>
                    <div class="risk-item">
                        <span class="label">Medium Risk:</span>
                        <span class="value medium-risk">{{ number_format($reportData['risk_analysis']['risk_distribution']['medium_risk']['amount'], 2) }}</span>
                    </div>
                    <div class="risk-item">
                        <span class="label">High Risk:</span>
                        <span class="value high-risk">{{ number_format($reportData['risk_analysis']['risk_distribution']['high_risk']['amount'], 2) }}</span>
                    </div>
                    <div class="risk-item">
                        <span class="label">Critical Risk:</span>
                        <span class="value high-risk">{{ number_format($reportData['risk_analysis']['risk_distribution']['critical_risk']['amount'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delinquency Analysis -->
    <div class="section">
        <div class="section-title">Delinquency Analysis</div>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-right">Amount</th>
                    <th class="text-center">Count</th>
                    <th class="text-center">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['risk_analysis']['delinquency_buckets'] as $category => $data)
                    <tr>
                        <td>
                            @if($category === 'current')
                                <span class="delinquency-badge current">Current</span>
                            @elseif($category === '1-30_days')
                                <span class="delinquency-badge overdue">1-30 Days</span>
                            @elseif($category === '31-60_days')
                                <span class="delinquency-badge overdue">31-60 Days</span>
                            @elseif($category === '61-90_days')
                                <span class="delinquency-badge overdue">61-90 Days</span>
                            @elseif($category === '91-180_days')
                                <span class="delinquency-badge overdue">91-180 Days</span>
                            @else
                                <span class="delinquency-badge overdue">Over 180 Days</span>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($data['amount'], 2) }}</td>
                        <td class="text-center">{{ number_format($data['count']) }}</td>
                        <td class="text-center">
                            {{ $reportData['portfolio_summary']['total_portfolio'] > 0 ? number_format(($data['amount'] / $reportData['portfolio_summary']['total_portfolio']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Trend Analysis -->
    <div class="section">
        <div class="section-title">Trend Analysis</div>
        <div class="trend-grid">
            <div class="trend-row">
                <div class="trend-cell label">Month-over-Month Growth</div>
                <div class="trend-cell label">Year-over-Year Growth</div>
                <div class="trend-cell label">Current Portfolio</div>
            </div>
            <div class="trend-row">
                <div class="trend-cell value {{ $reportData['trend_analysis']['month_over_month_growth'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $reportData['trend_analysis']['month_over_month_growth'] >= 0 ? '+' : '' }}{{ number_format($reportData['trend_analysis']['month_over_month_growth'], 2) }}%
                </div>
                <div class="trend-cell value {{ $reportData['trend_analysis']['year_over_year_growth'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $reportData['trend_analysis']['year_over_year_growth'] >= 0 ? '+' : '' }}{{ number_format($reportData['trend_analysis']['year_over_year_growth'], 2) }}%
                </div>
                <div class="trend-cell value">{{ number_format($reportData['trend_analysis']['current_portfolio'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Portfolio by Type -->
    <div class="section">
        <div class="section-title">Portfolio by Loan Type</div>
        <table>
            <thead>
                <tr>
                    <th>Loan Type</th>
                    <th class="text-right">Amount</th>
                    <th class="text-center">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['portfolio_by_type'] as $type => $amount)
                    <tr>
                        <td>{{ $type }}</td>
                        <td class="text-right">{{ number_format($amount, 2) }}</td>
                        <td class="text-center">
                            {{ $reportData['portfolio_summary']['total_portfolio'] > 0 ? number_format(($amount / $reportData['portfolio_summary']['total_portfolio']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Detailed Loan Portfolio -->
    <div class="section page-break">
        <div class="section-title">Detailed Loan Portfolio</div>
        <table>
            <thead>
                <tr>
                    <th>Loan ID</th>
                    <th>Client Number</th>
                    <th>Business Name</th>
                    <th>Category</th>
                    <th class="text-right">Outstanding Balance</th>
                    <th class="text-center">Days Past Due</th>
                    <th class="text-center">Risk Level</th>
                    <th class="text-center">Interest Rate</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['loan_details'] as $loan)
                    <tr>
                        <td>{{ $loan['loan_id'] }}</td>
                        <td>{{ $loan['client_number'] }}</td>
                        <td>{{ $loan['business_name'] }}</td>
                        <td>{{ $loan['category'] }}</td>
                        <td class="text-right">{{ number_format($loan['outstanding_balance'], 2) }}</td>
                        <td class="text-center">
                            @if($loan['days_past_due'] > 0)
                                <span class="delinquency-badge overdue">{{ $loan['days_past_due'] }} days</span>
                            @else
                                <span class="delinquency-badge current">Current</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($loan['risk_level'] === 'Low Risk')
                                <span class="risk-badge low">Low Risk</span>
                            @elseif($loan['risk_level'] === 'Medium Risk')
                                <span class="risk-badge medium">Medium Risk</span>
                            @elseif($loan['risk_level'] === 'High Risk')
                                <span class="risk-badge high">High Risk</span>
                            @else
                                <span class="risk-badge critical">Critical Risk</span>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($loan['interest_rate'], 2) }}%</td>
                        <td>{{ $loan['status'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This report was generated automatically by the Loan Management System</p>
        <p>For questions or clarifications, please contact the Risk Management Department</p>
    </div>
</body>
</html>
