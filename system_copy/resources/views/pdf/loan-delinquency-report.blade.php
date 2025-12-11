<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Delinquency Report</title>
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
        }
        
        .summary-cell.value.high-risk {
            color: #366092;
        }
        
        .summary-cell.value.medium-risk {
            color: #F59E0B;
        }
        
        .summary-cell.value.low-risk {
            color: #10B981;
        }
        
        .delinquency-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .delinquency-row {
            display: table-row;
        }
        
        .delinquency-cell {
            display: table-cell;
            padding: 8px 12px;
            border: 1px solid #ddd;
            width: 20%;
            text-align: center;
            vertical-align: middle;
        }
        
        .delinquency-cell.label {
            background-color: #fef2f2;
            font-weight: bold;
            color: #366092;
        }
        
        .delinquency-cell.value {
            font-size: 14px;
            font-weight: bold;
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
        
        .delinquency-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            min-width: 50px;
        }
        
        .delinquency-badge.low {
            background-color: #d4edda;
            color: #155724;
        }
        
        .delinquency-badge.medium {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .delinquency-badge.high {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .delinquency-badge.critical {
            background-color: #f5c6cb;
            color: #721c24;
        }
        
        .risk-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 4px;
        }
        
        .risk-indicator.low {
            background-color: #10B981;
        }
        
        .risk-indicator.medium {
            background-color: #F59E0B;
        }
        
        .risk-indicator.high {
            background-color: #366092;
        }
        
        .risk-indicator.critical {
            background-color: #7C2D12;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LOAN DELINQUENCY REPORT</h1>
        <h2>As at {{ $reportData['period']['end_date'] }}</h2>
        <p>(All amounts in Tanzanian Shillings)</p>
        <p>Generated on {{ $reportData['generated_at'] ? \Carbon\Carbon::parse($reportData['generated_at'])->format('F d, Y \a\t g:i A') : now()->format('F d, Y \a\t g:i A') }}</p>
        <p>Generated by: {{ $reportData['generated_by'] ?? 'System' }}</p>
    </div>

    <!-- Delinquency Summary -->
    <div class="section">
        <div class="section-title">Delinquency Summary</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell label">Total Delinquent Amount</div>
                <div class="summary-cell label">Total Loan Portfolio</div>
                <div class="summary-cell label">Delinquency Rate</div>
                <div class="summary-cell label">Delinquent Loans</div>
                <div class="summary-cell label">Current Loans</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell value high-risk">{{ number_format($reportData['delinquency_summary']['total_delinquent_amount'], 2) }}</div>
                <div class="summary-cell value">{{ number_format($reportData['delinquency_summary']['total_loan_portfolio'], 2) }}</div>
                <div class="summary-cell value {{ $reportData['delinquency_summary']['delinquency_rate'] > 5 ? 'high-risk' : ($reportData['delinquency_summary']['delinquency_rate'] > 2 ? 'medium-risk' : 'low-risk') }}">
                    {{ number_format($reportData['delinquency_summary']['delinquency_rate'], 2) }}%
                </div>
                <div class="summary-cell value high-risk">{{ number_format($reportData['delinquency_summary']['number_of_delinquent_loans']) }}</div>
                <div class="summary-cell value low-risk">{{ number_format($reportData['delinquency_summary']['current_loans']) }}</div>
            </div>
        </div>
    </div>

    <!-- Delinquency by Age -->
    <div class="section">
        <div class="section-title">Delinquency by Age</div>
        <div class="delinquency-grid">
            <div class="delinquency-row">
                <div class="delinquency-cell label">1-30 Days</div>
                <div class="delinquency-cell label">31-60 Days</div>
                <div class="delinquency-cell label">61-90 Days</div>
                <div class="delinquency-cell label">91-180 Days</div>
                <div class="delinquency-cell label">Over 180 Days</div>
            </div>
            <div class="delinquency-row">
                <div class="delinquency-cell value">{{ number_format($reportData['delinquency_by_age']['1-30 days'], 2) }}</div>
                <div class="delinquency-cell value">{{ number_format($reportData['delinquency_by_age']['31-60 days'], 2) }}</div>
                <div class="delinquency-cell value">{{ number_format($reportData['delinquency_by_age']['61-90 days'], 2) }}</div>
                <div class="delinquency-cell value">{{ number_format($reportData['delinquency_by_age']['91-180 days'], 2) }}</div>
                <div class="delinquency-cell value">{{ number_format($reportData['delinquency_by_age']['Over 180 days'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Detailed Delinquent Loans -->
    <div class="section page-break">
        <div class="section-title">Detailed Delinquent Loans</div>
        <table>
            <thead>
                <tr>
                    <th>Loan ID</th>
                    <th>Client Name</th>
                    <th>Phone</th>
                    <th>Outstanding Balance</th>
                    <th>Overdue Amount</th>
                    <th>Days Past Due</th>
                    <th>Status</th>
                    <th>Last Payment</th>
                    <th>Guarantor</th>
                    <th>Collateral</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['delinquent_loans'] as $loan)
                    <tr>
                        <td>{{ $loan['loan_id'] }}</td>
                        <td>{{ $loan['client_name'] ?: $loan['business_name'] }}</td>
                        <td>{{ $loan['client_phone'] ?: 'N/A' }}</td>
                        <td class="text-right">{{ number_format($loan['outstanding_balance'], 2) }}</td>
                        <td class="text-right">{{ number_format($loan['overdue_amount'], 2) }}</td>
                        <td class="text-center">
                            @if($loan['days_past_due'] <= 30)
                                <span class="delinquency-badge low">{{ $loan['days_past_due'] }} days</span>
                            @elseif($loan['days_past_due'] <= 60)
                                <span class="delinquency-badge medium">{{ $loan['days_past_due'] }} days</span>
                            @elseif($loan['days_past_due'] <= 90)
                                <span class="delinquency-badge high">{{ $loan['days_past_due'] }} days</span>
                            @else
                                <span class="delinquency-badge critical">{{ $loan['days_past_due'] }} days</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($loan['days_past_due'] <= 30)
                                <span class="delinquency-badge low">Low Risk</span>
                            @elseif($loan['days_past_due'] <= 60)
                                <span class="delinquency-badge medium">Medium Risk</span>
                            @else
                                <span class="delinquency-badge high">High Risk</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($loan['last_payment_date'])
                                {{ \Carbon\Carbon::parse($loan['last_payment_date'])->format('M d, Y') }}<br>
                                <small>{{ number_format($loan['last_payment_amount'], 2) }}</small>
                            @else
                                <span style="color: #366092;">No payments</span>
                            @endif
                        </td>
                        <td>
                            @if($loan['guarantor_name'])
                                {{ $loan['guarantor_name'] }}<br>
                                <small>{{ $loan['guarantor_phone'] ?: 'No phone' }}</small>
                            @else
                                <span style="color: #6B7280;">No guarantor</span>
                            @endif
                        </td>
                        <td>
                            @if($loan['collateral_type'])
                                {{ $loan['collateral_type'] }}<br>
                                <small>{{ $loan['collateral_value'] ? number_format($loan['collateral_value'], 2) . ' TZS' : 'No value' }}</small>
                            @else
                                <span style="color: #6B7280;">No collateral</span>
                            @endif
                        </td>
                        <td style="font-size: 8px;">{{ $loan['delinquency_reason'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Collection Actions Summary -->
    @if(!empty($reportData['delinquent_loans']))
    <div class="section">
        <div class="section-title">Collection Actions Summary</div>
        <table>
            <thead>
                <tr>
                    <th>Loan ID</th>
                    <th>Client Name</th>
                    <th>Days Past Due</th>
                    <th>Recent Actions</th>
                    <th>Next Action Required</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['delinquent_loans'] as $loan)
                    <tr>
                        <td>{{ $loan['loan_id'] }}</td>
                        <td>{{ $loan['client_name'] ?: $loan['business_name'] }}</td>
                        <td class="text-center">
                            @if($loan['days_past_due'] <= 30)
                                <span class="risk-indicator low"></span>{{ $loan['days_past_due'] }} days
                            @elseif($loan['days_past_due'] <= 60)
                                <span class="risk-indicator medium"></span>{{ $loan['days_past_due'] }} days
                            @elseif($loan['days_past_due'] <= 90)
                                <span class="risk-indicator high"></span>{{ $loan['days_past_due'] }} days
                            @else
                                <span class="risk-indicator critical"></span>{{ $loan['days_past_due'] }} days
                            @endif
                        </td>
                        <td>
                            @if(!empty($loan['collection_actions']))
                                @foreach(array_slice($loan['collection_actions'], 0, 2) as $action)
                                    <div style="font-size: 8px; margin-bottom: 2px;">
                                        <strong>{{ $action['type'] }}</strong> - {{ \Carbon\Carbon::parse($action['date'])->format('M d') }}
                                        <br><small>{{ $action['description'] }}</small>
                                    </div>
                                @endforeach
                            @else
                                <span style="color: #366092; font-size: 8px;">No actions recorded</span>
                            @endif
                        </td>
                        <td>
                            @if($loan['days_past_due'] <= 30)
                                <span style="color: #10B981; font-size: 8px;">Phone call reminder</span>
                            @elseif($loan['days_past_due'] <= 60)
                                <span style="color: #F59E0B; font-size: 8px;">Written notice</span>
                            @elseif($loan['days_past_due'] <= 90)
                                <span style="color: #366092; font-size: 8px;">Field visit required</span>
                            @else
                                <span style="color: #7C2D12; font-size: 8px;">Legal action needed</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This report was generated automatically by the Loan Management System</p>
        <p>For questions or clarifications, please contact the Risk Management Department</p>
        <p><strong>Risk Levels:</strong> 
            <span class="risk-indicator low"></span> Low Risk (1-30 days) | 
            <span class="risk-indicator medium"></span> Medium Risk (31-60 days) | 
            <span class="risk-indicator high"></span> High Risk (61-90 days) | 
            <span class="risk-indicator critical"></span> Critical Risk (90+ days)
        </p>
    </div>
</body>
</html>
