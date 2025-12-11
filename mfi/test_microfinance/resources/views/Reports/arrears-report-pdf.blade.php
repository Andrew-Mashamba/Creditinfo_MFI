<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportName ?? 'Arrears Report' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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
            font-size: 22px;
            margin: 0 0 10px 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header h2 {
            color: #666;
            font-size: 14px;
            margin: 0 0 5px 0;
            font-weight: normal;
        }

        .header p {
            color: #999;
            font-size: 10px;
            margin: 5px 0;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #1F4E78;
            margin-bottom: 5px;
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
            font-size: 13px;
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
            padding: 10px 12px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }

        .summary-cell.label {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #366092;
            font-size: 10px;
        }

        .summary-cell.value {
            font-size: 14px;
            font-weight: bold;
        }

        .summary-cell.value.danger {
            color: #DC2626;
        }

        .summary-cell.value.warning {
            color: #F59E0B;
        }

        .summary-cell.value.success {
            color: #10B981;
        }

        .summary-cell.value.primary {
            color: #366092;
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
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2d5a87;
        }

        td {
            padding: 6px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e9ecef;
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

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .badge-primary {
            background-color: #cce5ff;
            color: #004085;
        }

        .risk-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .risk-low { background-color: #10B981; }
        .risk-medium { background-color: #F59E0B; }
        .risk-high { background-color: #EF4444; }
        .risk-critical { background-color: #7C2D12; }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        .page-break {
            page-break-before: always;
        }

        .info-box {
            background-color: #f0f7ff;
            border-left: 4px solid #366092;
            padding: 10px 15px;
            margin-bottom: 15px;
        }

        .info-box p {
            margin: 5px 0;
            font-size: 10px;
        }

        .totals-row {
            background-color: #e9ecef !important;
            font-weight: bold;
        }

        .totals-row td {
            border-top: 2px solid #366092;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">{{ $company ?? 'MFI Management System' }}</div>
        <h1>{{ $reportName ?? 'ARREARS REPORT' }}</h1>
        <h2>{{ $reportDescription ?? 'Loan Arrears Analysis' }}</h2>
        <p>Report Period: {{ $data['report_period'] ?? date('F Y') }}</p>
        <p>Generated on: {{ $data['generated_at'] ?? now()->format('F d, Y \a\t g:i A') }}</p>
        <p>All amounts in Tanzanian Shillings (TZS)</p>
    </div>

    <!-- Executive Summary -->
    <div class="section">
        <div class="section-title">Executive Summary</div>
        <div class="summary-grid">
            <div class="summary-row">
                @if(isset($data['summary']['total_loans_in_arrears']))
                <div class="summary-cell label">Total Loans in Arrears</div>
                @endif
                @if(isset($data['summary']['total_arrears_amount']))
                <div class="summary-cell label">Total Arrears Amount</div>
                @endif
                @if(isset($data['summary']['average_days_in_arrears']))
                <div class="summary-cell label">Avg. Days in Arrears</div>
                @endif
                @if(isset($data['summary']['par_ratio']))
                <div class="summary-cell label">PAR Ratio</div>
                @endif
                @if(isset($data['summary']['provision_required']))
                <div class="summary-cell label">Provision Required</div>
                @endif
            </div>
            <div class="summary-row">
                @if(isset($data['summary']['total_loans_in_arrears']))
                <div class="summary-cell value danger">{{ number_format($data['summary']['total_loans_in_arrears']) }}</div>
                @endif
                @if(isset($data['summary']['total_arrears_amount']))
                <div class="summary-cell value danger">{{ number_format($data['summary']['total_arrears_amount'], 2) }}</div>
                @endif
                @if(isset($data['summary']['average_days_in_arrears']))
                <div class="summary-cell value warning">{{ number_format($data['summary']['average_days_in_arrears'], 0) }} days</div>
                @endif
                @if(isset($data['summary']['par_ratio']))
                <div class="summary-cell value {{ $data['summary']['par_ratio'] > 5 ? 'danger' : ($data['summary']['par_ratio'] > 2 ? 'warning' : 'success') }}">
                    {{ number_format($data['summary']['par_ratio'], 2) }}%
                </div>
                @endif
                @if(isset($data['summary']['provision_required']))
                <div class="summary-cell value primary">{{ number_format($data['summary']['provision_required'], 2) }}</div>
                @endif
            </div>
        </div>

        @if(isset($data['details']['new_arrears_today']) || isset($data['details']['collections_today']))
        <div class="info-box">
            <p><strong>Today's Activity:</strong></p>
            @if(isset($data['details']['new_arrears_today']))
            <p>New Arrears: {{ number_format($data['details']['new_arrears_today']) }} loans</p>
            @endif
            @if(isset($data['details']['collections_today']))
            <p>Collections: TZS {{ number_format($data['details']['collections_today'], 2) }}</p>
            @endif
        </div>
        @endif
    </div>

    <!-- Risk Level Summary -->
    @if(isset($data['details']['risk_summary']) && count($data['details']['risk_summary']) > 0)
    <div class="section">
        <div class="section-title">Risk Level Summary</div>
        <table>
            <thead>
                <tr>
                    <th>Risk Level</th>
                    <th class="text-center">No. of Loans</th>
                    <th class="text-right">Total Arrears (TZS)</th>
                    <th class="text-right">Total Principal (TZS)</th>
                    <th class="text-center">% of Portfolio</th>
                </tr>
            </thead>
            <tbody>
                @php $totalCount = 0; $totalArrears = 0; $totalPrincipal = 0; @endphp
                @foreach($data['details']['risk_summary'] as $risk)
                @php
                    $totalCount += $risk['count'] ?? 0;
                    $totalArrears += $risk['total_arrears'] ?? 0;
                    $totalPrincipal += $risk['total_principal'] ?? 0;
                @endphp
                <tr>
                    <td>
                        @if(stripos($risk['risk_level'] ?? '', 'low') !== false)
                            <span class="risk-indicator risk-low"></span>
                        @elseif(stripos($risk['risk_level'] ?? '', 'medium') !== false)
                            <span class="risk-indicator risk-medium"></span>
                        @elseif(stripos($risk['risk_level'] ?? '', 'high') !== false)
                            <span class="risk-indicator risk-high"></span>
                        @else
                            <span class="risk-indicator risk-critical"></span>
                        @endif
                        {{ $risk['risk_level'] ?? 'Unknown' }}
                    </td>
                    <td class="text-center">{{ number_format($risk['count'] ?? 0) }}</td>
                    <td class="text-right">{{ number_format($risk['total_arrears'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($risk['total_principal'] ?? 0, 2) }}</td>
                    <td class="text-center">
                        @if($totalPrincipal > 0)
                            {{ number_format(($risk['total_principal'] ?? 0) / $totalPrincipal * 100, 1) }}%
                        @else
                            0%
                        @endif
                    </td>
                </tr>
                @endforeach
                <tr class="totals-row">
                    <td><strong>TOTAL</strong></td>
                    <td class="text-center"><strong>{{ number_format($totalCount) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalArrears, 2) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalPrincipal, 2) }}</strong></td>
                    <td class="text-center"><strong>100%</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Aging Analysis -->
    @if(isset($data['details']['aging_analysis']) && count($data['details']['aging_analysis']) > 0)
    <div class="section">
        <div class="section-title">Aging Analysis (Days Past Due)</div>
        <table>
            <thead>
                <tr>
                    <th>Age Bucket</th>
                    <th class="text-center">No. of Loans</th>
                    <th class="text-right">Arrears Amount (TZS)</th>
                    <th class="text-right">Principal Outstanding (TZS)</th>
                    <th class="text-center">Provision Rate</th>
                    <th class="text-right">Provision Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @php $totalProvision = 0; @endphp
                @foreach($data['details']['aging_analysis'] as $aging)
                @php $totalProvision += $aging['provision_amount'] ?? 0; @endphp
                <tr>
                    <td>
                        @if(($aging['days_from'] ?? 0) <= 30)
                            <span class="badge badge-success">{{ $aging['bucket'] ?? $aging['age_bucket'] ?? 'N/A' }}</span>
                        @elseif(($aging['days_from'] ?? 0) <= 60)
                            <span class="badge badge-warning">{{ $aging['bucket'] ?? $aging['age_bucket'] ?? 'N/A' }}</span>
                        @elseif(($aging['days_from'] ?? 0) <= 90)
                            <span class="badge badge-danger">{{ $aging['bucket'] ?? $aging['age_bucket'] ?? 'N/A' }}</span>
                        @else
                            <span class="badge badge-danger">{{ $aging['bucket'] ?? $aging['age_bucket'] ?? 'N/A' }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($aging['loan_count'] ?? $aging['count'] ?? 0) }}</td>
                    <td class="text-right">{{ number_format($aging['arrears_amount'] ?? $aging['amount'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($aging['principal_amount'] ?? $aging['principal'] ?? 0, 2) }}</td>
                    <td class="text-center">{{ number_format($aging['provision_rate'] ?? 0, 0) }}%</td>
                    <td class="text-right">{{ number_format($aging['provision_amount'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
                <tr class="totals-row">
                    <td colspan="5"><strong>TOTAL PROVISION REQUIRED</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalProvision, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Detailed Aging (if available) -->
    @if(isset($data['details']['detailed_aging']) && count($data['details']['detailed_aging']) > 0)
    <div class="section">
        <div class="section-title">Detailed Aging Analysis</div>
        <table>
            <thead>
                <tr>
                    <th>Bucket</th>
                    <th class="text-center">Loan Count</th>
                    <th class="text-right">Principal Amount (TZS)</th>
                    <th class="text-right">Arrears Amount (TZS)</th>
                    <th class="text-center">Provision Rate</th>
                    <th class="text-right">Provision Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['details']['detailed_aging'] as $aging)
                <tr>
                    <td>{{ $aging['bucket'] ?? 'N/A' }}</td>
                    <td class="text-center">{{ number_format($aging['loan_count'] ?? 0) }}</td>
                    <td class="text-right">{{ number_format($aging['principal_amount'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($aging['arrears_amount'] ?? 0, 2) }}</td>
                    <td class="text-center">{{ number_format($aging['provision_rate'] ?? 0, 0) }}%</td>
                    <td class="text-right">{{ number_format($aging['provision_amount'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Recovery Status -->
    @if(isset($data['details']['recovery_summary']) && count($data['details']['recovery_summary']) > 0)
    <div class="section">
        <div class="section-title">Recovery Status Summary</div>
        <table>
            <thead>
                <tr>
                    <th>Recovery Stage</th>
                    <th class="text-center">No. of Loans</th>
                    <th class="text-right">Total Arrears (TZS)</th>
                    <th class="text-right">Total Recovered (TZS)</th>
                    <th class="text-center">Recovery Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['details']['recovery_summary'] as $recovery)
                <tr>
                    <td>
                        @if($recovery['stage'] == 'Follow-up')
                            <span class="badge badge-info">{{ $recovery['stage'] }}</span>
                        @elseif($recovery['stage'] == 'Intensive Recovery')
                            <span class="badge badge-warning">{{ $recovery['stage'] }}</span>
                        @elseif($recovery['stage'] == 'Legal Notice')
                            <span class="badge badge-danger">{{ $recovery['stage'] }}</span>
                        @else
                            <span class="badge badge-danger">{{ $recovery['stage'] }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($recovery['count'] ?? 0) }}</td>
                    <td class="text-right">{{ number_format($recovery['total_arrears'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($recovery['total_recovered'] ?? 0, 2) }}</td>
                    <td class="text-center">
                        <span class="{{ ($recovery['recovery_rate'] ?? 0) >= 50 ? 'badge badge-success' : (($recovery['recovery_rate'] ?? 0) >= 25 ? 'badge badge-warning' : 'badge badge-danger') }}">
                            {{ number_format($recovery['recovery_rate'] ?? 0, 1) }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Top Arrears Loans -->
    @if(isset($data['details']['top_arrears']) && count($data['details']['top_arrears']) > 0)
    <div class="section page-break">
        <div class="section-title">Top Arrears Accounts</div>
        <table>
            <thead>
                <tr>
                    <th>Loan Reference</th>
                    <th>Member Name</th>
                    <th>Phone</th>
                    <th class="text-right">Principal (TZS)</th>
                    <th class="text-right">Arrears (TZS)</th>
                    <th class="text-center">Days in Arrears</th>
                    <th>Risk Level</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['details']['top_arrears'] as $loan)
                <tr>
                    <td>{{ $loan['loan_reference'] ?? $loan['loan_id'] ?? 'N/A' }}</td>
                    <td>{{ $loan['member_name'] ?? $loan['client_name'] ?? 'Unknown' }}</td>
                    <td>{{ $loan['member_phone'] ?? $loan['phone'] ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($loan['principle'] ?? $loan['principal'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($loan['arrears_amount'] ?? $loan['arrears'] ?? 0, 2) }}</td>
                    <td class="text-center">{{ number_format($loan['days_in_arrears'] ?? 0) }}</td>
                    <td>
                        @php $days = $loan['days_in_arrears'] ?? 0; @endphp
                        @if($days <= 30)
                            <span class="badge badge-success">Low Risk</span>
                        @elseif($days <= 60)
                            <span class="badge badge-warning">Medium Risk</span>
                        @elseif($days <= 90)
                            <span class="badge badge-danger">High Risk</span>
                        @else
                            <span class="badge badge-danger">Critical</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Recovery List -->
    @if(isset($data['details']['recovery_list']) && count($data['details']['recovery_list']) > 0)
    <div class="section page-break">
        <div class="section-title">Detailed Recovery List</div>
        <table>
            <thead>
                <tr>
                    <th>Loan Reference</th>
                    <th>Member Name</th>
                    <th>Phone</th>
                    <th class="text-right">Principal (TZS)</th>
                    <th class="text-right">Arrears (TZS)</th>
                    <th class="text-right">Amount Paid (TZS)</th>
                    <th class="text-center">Days</th>
                    <th>Recovery Stage</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($data['details']['recovery_list'], 0, 50) as $loan)
                <tr>
                    <td>{{ $loan['loan_reference'] ?? 'N/A' }}</td>
                    <td>{{ $loan['member_name'] ?? 'Unknown' }}</td>
                    <td>{{ $loan['member_phone'] ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($loan['principle'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($loan['arrears_amount'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($loan['amount_paid'] ?? 0, 2) }}</td>
                    <td class="text-center">{{ $loan['days_in_arrears'] ?? 0 }}</td>
                    <td>
                        @if(($loan['recovery_stage'] ?? '') == 'Follow-up')
                            <span class="badge badge-info">{{ $loan['recovery_stage'] }}</span>
                        @elseif(($loan['recovery_stage'] ?? '') == 'Intensive Recovery')
                            <span class="badge badge-warning">{{ $loan['recovery_stage'] }}</span>
                        @else
                            <span class="badge badge-danger">{{ $loan['recovery_stage'] ?? 'Unknown' }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if(count($data['details']['recovery_list']) > 50)
        <p class="text-center" style="font-style: italic; color: #666;">
            Showing first 50 of {{ number_format(count($data['details']['recovery_list'])) }} records.
            Export to Excel for complete list.
        </p>
        @endif
    </div>
    @endif

    <!-- Overall Recovery Statistics -->
    @if(isset($data['details']['overall_recovery']))
    <div class="section">
        <div class="section-title">Overall Recovery Statistics</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell label">Total in Recovery</div>
                <div class="summary-cell label">Total Arrears</div>
                <div class="summary-cell label">Total Recovered</div>
                <div class="summary-cell label">Recovery Rate</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell value primary">{{ number_format($data['details']['overall_recovery']['total_in_recovery'] ?? 0) }}</div>
                <div class="summary-cell value danger">{{ number_format($data['details']['overall_recovery']['total_arrears'] ?? 0, 2) }}</div>
                <div class="summary-cell value success">{{ number_format($data['details']['overall_recovery']['total_recovered'] ?? 0, 2) }}</div>
                <div class="summary-cell value {{ ($data['details']['overall_recovery']['recovery_rate'] ?? 0) >= 50 ? 'success' : 'warning' }}">
                    {{ number_format($data['details']['overall_recovery']['recovery_rate'] ?? 0, 1) }}%
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>{{ $company ?? 'MFI Management System' }}</strong></p>
        <p>This report was generated automatically by the Loan Management System</p>
        <p>For questions or clarifications, please contact the Credit/Risk Management Department</p>
        <p style="margin-top: 10px;">
            <strong>BoT Classification:</strong>
            <span class="risk-indicator risk-low"></span> Current (0-30 days, 0%) |
            <span class="risk-indicator risk-medium"></span> Watch (31-90 days, 10%) |
            <span class="risk-indicator risk-high"></span> Substandard (91-180 days, 30%) |
            <span class="risk-indicator risk-critical"></span> Doubtful/Loss (181+ days, 50-100%)
        </p>
        <p style="font-size: 8px; color: #999; margin-top: 15px;">
            Report ID: {{ uniqid('ARR-') }} | Generated: {{ now()->format('Y-m-d H:i:s') }}
        </p>
    </div>
</body>
</html>
