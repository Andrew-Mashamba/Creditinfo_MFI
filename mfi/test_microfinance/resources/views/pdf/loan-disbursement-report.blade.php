<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Disbursement Report</title>
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
            width: 20%;
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
            color: #10B981;
        }
        
        .type-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .type-row {
            display: table-row;
        }
        
        .type-cell {
            display: table-cell;
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }
        
        .type-cell.label {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #366092;
            text-align: left;
        }
        
        .type-cell.value {
            font-size: 12px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
        }
        
        th {
            background-color: #366092;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        
        td {
            padding: 4px;
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
        
        .disbursement-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            min-width: 50px;
        }
        
        .disbursement-badge.cash {
            background-color: #d4edda;
            color: #155724;
        }
        
        .disbursement-badge.bank {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .disbursement-badge.mobile {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .amount-highlight {
            background-color: #e8f5e8;
            font-weight: bold;
        }
        
        .trend-chart {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .trend-row {
            display: table-row;
        }
        
        .trend-cell {
            display: table-cell;
            padding: 4px 8px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
        }
        
        .trend-cell.date {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #366092;
        }
        
        .trend-cell.amount {
            background-color: #e8f5e8;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LOAN DISBURSEMENT REPORT</h1>
        <h2>For the period {{ $reportData['period']['start_date'] }} to {{ $reportData['period']['end_date'] }}</h2>
        <p>(All amounts in Tanzanian Shillings)</p>
        <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }}</p>
        <p>Generated by: {{ auth()->user()->name ?? 'System' }}</p>
    </div>

    <!-- Disbursement Summary -->
    <div class="section">
        <div class="section-title">Disbursement Summary</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell label">Total Disbursed</div>
                <div class="summary-cell label">Number of Disbursements</div>
                <div class="summary-cell label">Average Disbursement</div>
                <div class="summary-cell label">Largest Disbursement</div>
                <div class="summary-cell label">Smallest Disbursement</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell value amount-highlight">{{ number_format($reportData['disbursement_summary']['total_disbursed'], 2) }}</div>
                <div class="summary-cell value">{{ number_format($reportData['disbursement_summary']['number_of_disbursements']) }}</div>
                <div class="summary-cell value">{{ number_format($reportData['disbursement_summary']['average_disbursement'], 2) }}</div>
                <div class="summary-cell value">{{ number_format($reportData['disbursement_summary']['largest_disbursement'], 2) }}</div>
                <div class="summary-cell value">{{ number_format($reportData['disbursement_summary']['smallest_disbursement'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Disbursements by Type -->
    @if(!empty($reportData['disbursements_by_type']))
    <div class="section">
        <div class="section-title">Disbursements by Loan Type</div>
        <table>
            <thead>
                <tr>
                    <th>Loan Type</th>
                    <th>Number of Disbursements</th>
                    <th>Total Amount (TZS)</th>
                    <th>Average Amount (TZS)</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['disbursements_by_type'] as $type => $data)
                    <tr>
                        <td class="text-bold">{{ $type }}</td>
                        <td class="text-center">{{ number_format($data['count']) }}</td>
                        <td class="text-right amount-highlight">{{ number_format($data['amount'], 2) }}</td>
                        <td class="text-right">{{ number_format($data['amount'] / $data['count'], 2) }}</td>
                        <td class="text-center">
                            {{ $reportData['disbursement_summary']['total_disbursed'] > 0 ? number_format(($data['amount'] / $reportData['disbursement_summary']['total_disbursed']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Daily Disbursement Trend -->
    @if(!empty($reportData['daily_trend']))
    <div class="section">
        <div class="section-title">Daily Disbursement Trend</div>
        <div class="trend-chart">
            <div class="trend-row">
                <div class="trend-cell date">Date</div>
                <div class="trend-cell date">Count</div>
                <div class="trend-cell date">Amount (TZS)</div>
                <div class="trend-cell date">Date</div>
                <div class="trend-cell date">Count</div>
                <div class="trend-cell date">Amount (TZS)</div>
            </div>
            @foreach(array_chunk($reportData['daily_trend'], 2) as $chunk)
                <div class="trend-row">
                    @foreach($chunk as $trend)
                        <div class="trend-cell">{{ $trend['date'] }}</div>
                        <div class="trend-cell">{{ $trend['count'] }}</div>
                        <div class="trend-cell amount">{{ number_format($trend['amount'], 2) }}</div>
                    @endforeach
                    @if(count($chunk) == 1)
                        <div class="trend-cell">-</div>
                        <div class="trend-cell">-</div>
                        <div class="trend-cell">-</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Detailed Disbursements -->
    <div class="section page-break">
        <div class="section-title">Detailed Disbursements</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Loan ID</th>
                    <th>Client Number</th>
                    <th>Business Name</th>
                    <th>Amount (TZS)</th>
                    <th>Loan Type</th>
                    <th>Interest Rate (%)</th>
                    <th>Method</th>
                    <th>Net Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['disbursements'] as $disbursement)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($disbursement->disbursement_date)->format('M d, Y') }}</td>
                        <td class="text-bold">{{ $disbursement->loan_id }}</td>
                        <td>{{ $disbursement->client_number }}</td>
                        <td>{{ $disbursement->business_name }}</td>
                        <td class="text-right amount-highlight">{{ number_format($disbursement->principle, 2) }}</td>
                        <td>{{ $disbursement->loan_type_2 }}</td>
                        <td class="text-center">{{ number_format($disbursement->interest, 2) }}</td>
                        <td class="text-center">
                            @if(isset($disbursement->disbursement_method))
                                @if(strtolower($disbursement->disbursement_method) == 'cash')
                                    <span class="disbursement-badge cash">Cash</span>
                                @elseif(strpos(strtolower($disbursement->disbursement_method), 'bank') !== false)
                                    <span class="disbursement-badge bank">Bank</span>
                                @elseif(strpos(strtolower($disbursement->disbursement_method), 'mobile') !== false)
                                    <span class="disbursement-badge mobile">Mobile</span>
                                @else
                                    <span class="disbursement-badge cash">{{ $disbursement->disbursement_method }}</span>
                                @endif
                            @else
                                <span class="disbursement-badge cash">Cash</span>
                            @endif
                        </td>
                        <td class="text-right">
                            {{ number_format($disbursement->net_disbursement_amount ?? $disbursement->principle, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Summary Statistics -->
    <div class="section">
        <div class="section-title">Summary Statistics</div>
        <div class="type-grid">
            <div class="type-row">
                <div class="type-cell label">Total Disbursements</div>
                <div class="type-cell value">{{ number_format($reportData['disbursement_summary']['number_of_disbursements']) }}</div>
                <div class="type-cell label">Total Amount Disbursed</div>
                <div class="type-cell value amount-highlight">{{ number_format($reportData['disbursement_summary']['total_disbursed'], 2) }} TZS</div>
            </div>
            <div class="type-row">
                <div class="type-cell label">Average Disbursement Size</div>
                <div class="type-cell value">{{ number_format($reportData['disbursement_summary']['average_disbursement'], 2) }} TZS</div>
                <div class="type-cell label">Largest Single Disbursement</div>
                <div class="type-cell value">{{ number_format($reportData['disbursement_summary']['largest_disbursement'], 2) }} TZS</div>
            </div>
            <div class="type-row">
                <div class="type-cell label">Smallest Single Disbursement</div>
                <div class="type-cell value">{{ number_format($reportData['disbursement_summary']['smallest_disbursement'], 2) }} TZS</div>
                <div class="type-cell label">Report Period</div>
                <div class="type-cell value">{{ $reportData['period']['start_date'] }} to {{ $reportData['period']['end_date'] }}</div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This report was generated automatically by the Loan Management System</p>
        <p>For questions or clarifications, please contact the Loan Operations Department</p>
        <p><strong>Note:</strong> All amounts are in Tanzanian Shillings (TZS)</p>
    </div>
</body>
</html>
