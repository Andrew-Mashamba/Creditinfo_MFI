<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportName }}</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header .subtitle {
            margin-top: 10px;
            font-size: 14px;
            opacity: 0.9;
        }
        .header .report-badge {
            display: inline-block;
            background-color: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 10px;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .description-box {
            background-color: #e8f4fd;
            border-left: 4px solid #1e3a5f;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 0 5px 5px 0;
        }
        .summary-section {
            margin-bottom: 30px;
        }
        .summary-section h3 {
            color: #1e3a5f;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .metric-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        .metric-value {
            font-size: 24px;
            font-weight: 700;
            color: #1e3a5f;
            margin-bottom: 5px;
        }
        .metric-value.positive {
            color: #28a745;
        }
        .metric-value.negative {
            color: #dc3545;
        }
        .metric-value.warning {
            color: #ffc107;
        }
        .metric-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .data-table th {
            background-color: #1e3a5f;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
        }
        .data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .data-table tr:hover {
            background-color: #e9ecef;
        }
        .amount {
            font-weight: bold;
            color: #1e3a5f;
            text-align: right;
        }
        .percentage {
            font-weight: bold;
        }
        .percentage.high {
            color: #dc3545;
        }
        .percentage.medium {
            color: #ffc107;
        }
        .percentage.low {
            color: #28a745;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-good {
            background-color: #d4edda;
            color: #155724;
        }
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .compliance-badges {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .compliance-badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .compliance-bot {
            background-color: #28a745;
            color: white;
        }
        .compliance-ifrs {
            background-color: #007bff;
            color: white;
        }
        .compliance-internal {
            background-color: #6c757d;
            color: white;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
        }
        .progress-good {
            background-color: #28a745;
        }
        .progress-warning {
            background-color: #ffc107;
        }
        .progress-danger {
            background-color: #dc3545;
        }
        .cta-section {
            text-align: center;
            margin: 30px 0;
        }
        .cta-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #1e3a5f;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 5px 0;
        }
        @media (max-width: 600px) {
            .metrics-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .compliance-badges {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $reportName }}</h1>
            <div class="subtitle">Generated: {{ $generatedAt->format('F d, Y \a\t H:i') }}</div>
            @if(isset($reportData['report_info']['compliance']))
            <div class="report-badge">
                @foreach($reportData['report_info']['compliance'] as $compliance)
                    {{ $compliance }}@if(!$loop->last) | @endif
                @endforeach
            </div>
            @endif
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Greeting -->
            <p class="greeting">Dear <strong>{{ $recipientName }}</strong>,</p>

            <!-- Description -->
            @if($reportDescription)
            <div class="description-box">
                {{ $reportDescription }}
            </div>
            @endif

            <!-- Report Period -->
            @if(isset($reportData['period']))
            <div class="summary-section">
                <p><strong>Report Period:</strong> {{ $reportData['period']['start'] }} to {{ $reportData['period']['end'] }}</p>
            </div>
            @elseif(isset($reportData['as_of_date']))
            <div class="summary-section">
                <p><strong>As of Date:</strong> {{ $reportData['as_of_date'] }}</p>
            </div>
            @endif

            <!-- Key Metrics Summary -->
            @if(isset($reportData['totals']) || isset($reportData['summary']) || isset($reportData['portfolio_summary']))
            <div class="summary-section">
                <h3>Key Metrics</h3>
                <div class="metrics-grid">
                    @if(isset($reportData['totals']['total_portfolio']))
                    <div class="metric-card">
                        <div class="metric-value">TZS {{ number_format($reportData['totals']['total_portfolio']) }}</div>
                        <div class="metric-label">Total Portfolio</div>
                    </div>
                    @endif

                    @if(isset($reportData['portfolio_summary']['total_outstanding']))
                    <div class="metric-card">
                        <div class="metric-value">TZS {{ number_format($reportData['portfolio_summary']['total_outstanding']) }}</div>
                        <div class="metric-label">Outstanding Balance</div>
                    </div>
                    @endif

                    @if(isset($reportData['portfolio_summary']['total_active_loans']))
                    <div class="metric-card">
                        <div class="metric-value">{{ number_format($reportData['portfolio_summary']['total_active_loans']) }}</div>
                        <div class="metric-label">Active Loans</div>
                    </div>
                    @endif

                    @if(isset($reportData['portfolio_summary']['par_ratio']) || isset($reportData['totals']['par_ratio']))
                    @php
                        $parRatio = $reportData['portfolio_summary']['par_ratio'] ?? $reportData['totals']['par_ratio'];
                        $parClass = $parRatio > 10 ? 'negative' : ($parRatio > 5 ? 'warning' : 'positive');
                    @endphp
                    <div class="metric-card">
                        <div class="metric-value {{ $parClass }}">{{ $parRatio }}%</div>
                        <div class="metric-label">PAR Ratio</div>
                    </div>
                    @endif

                    @if(isset($reportData['summary']['total_loans_in_arrears']))
                    <div class="metric-card">
                        <div class="metric-value negative">{{ number_format($reportData['summary']['total_loans_in_arrears']) }}</div>
                        <div class="metric-label">Loans in Arrears</div>
                    </div>
                    @endif

                    @if(isset($reportData['summary']['total_arrears_amount']))
                    <div class="metric-card">
                        <div class="metric-value negative">TZS {{ number_format($reportData['summary']['total_arrears_amount']) }}</div>
                        <div class="metric-label">Total Arrears</div>
                    </div>
                    @endif

                    @if(isset($reportData['totals']['net_income']))
                    <div class="metric-card">
                        <div class="metric-value {{ $reportData['totals']['net_income'] >= 0 ? 'positive' : 'negative' }}">
                            TZS {{ number_format($reportData['totals']['net_income']) }}
                        </div>
                        <div class="metric-label">Net Income</div>
                    </div>
                    @endif

                    @if(isset($reportData['summary']['profit_margin']))
                    <div class="metric-card">
                        <div class="metric-value {{ $reportData['summary']['profit_margin'] >= 0 ? 'positive' : 'negative' }}">
                            {{ $reportData['summary']['profit_margin'] }}%
                        </div>
                        <div class="metric-label">Profit Margin</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- PAR Metrics (Delinquency Reports) -->
            @if(isset($reportData['metrics']))
            <div class="summary-section">
                <h3>Portfolio at Risk Analysis</h3>
                <div class="metrics-grid">
                    @if(isset($reportData['metrics']['par_1']))
                    <div class="metric-card">
                        <div class="metric-value {{ $reportData['metrics']['par_1'] > 10 ? 'negative' : ($reportData['metrics']['par_1'] > 5 ? 'warning' : 'positive') }}">
                            {{ $reportData['metrics']['par_1'] }}%
                        </div>
                        <div class="metric-label">PAR > 1 Day</div>
                    </div>
                    @endif
                    @if(isset($reportData['metrics']['par_30']))
                    <div class="metric-card">
                        <div class="metric-value {{ $reportData['metrics']['par_30'] > 5 ? 'negative' : ($reportData['metrics']['par_30'] > 3 ? 'warning' : 'positive') }}">
                            {{ $reportData['metrics']['par_30'] }}%
                        </div>
                        <div class="metric-label">PAR > 30 Days</div>
                    </div>
                    @endif
                    @if(isset($reportData['metrics']['par_60']))
                    <div class="metric-card">
                        <div class="metric-value {{ $reportData['metrics']['par_60'] > 3 ? 'negative' : ($reportData['metrics']['par_60'] > 2 ? 'warning' : 'positive') }}">
                            {{ $reportData['metrics']['par_60'] }}%
                        </div>
                        <div class="metric-label">PAR > 60 Days</div>
                    </div>
                    @endif
                    @if(isset($reportData['metrics']['par_90']))
                    <div class="metric-card">
                        <div class="metric-value {{ $reportData['metrics']['par_90'] > 2 ? 'negative' : ($reportData['metrics']['par_90'] > 1 ? 'warning' : 'positive') }}">
                            {{ $reportData['metrics']['par_90'] }}%
                        </div>
                        <div class="metric-label">PAR > 90 Days</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Transactions Summary (Operations Reports) -->
            @if(isset($reportData['transactions']))
            <div class="summary-section">
                <h3>Transactions Summary</h3>
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-value">{{ number_format($reportData['transactions']['count']) }}</div>
                        <div class="metric-label">Total Transactions</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-value">TZS {{ number_format($reportData['transactions']['total_amount']) }}</div>
                        <div class="metric-label">Total Amount</div>
                    </div>
                </div>

                @if(isset($reportData['transactions']['by_type']) && count($reportData['transactions']['by_type']) > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Transaction Type</th>
                            <th>Count</th>
                            <th>Amount (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['transactions']['by_type'] as $type => $data)
                        <tr>
                            <td>{{ ucwords(str_replace('_', ' ', $type)) }}</td>
                            <td>{{ number_format($data['count']) }}</td>
                            <td class="amount">{{ number_format($data['amount']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            @endif

            <!-- New Loans & Disbursements -->
            @if(isset($reportData['new_loans']) || isset($reportData['disbursements']))
            <div class="summary-section">
                <h3>Loan Activity</h3>
                <div class="metrics-grid">
                    @if(isset($reportData['new_loans']))
                    <div class="metric-card">
                        <div class="metric-value">{{ number_format($reportData['new_loans']['count']) }}</div>
                        <div class="metric-label">New Applications</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-value">TZS {{ number_format($reportData['new_loans']['total_amount']) }}</div>
                        <div class="metric-label">Applied Amount</div>
                    </div>
                    @endif
                    @if(isset($reportData['disbursements']))
                    <div class="metric-card">
                        <div class="metric-value">{{ number_format($reportData['disbursements']['count']) }}</div>
                        <div class="metric-label">Disbursements</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-value">TZS {{ number_format($reportData['disbursements']['total_amount']) }}</div>
                        <div class="metric-label">Disbursed Amount</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Aging Analysis -->
            @if(isset($reportData['aging_analysis']) && count($reportData['aging_analysis']) > 0)
            <div class="summary-section">
                <h3>Aging Analysis</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Aging Bucket</th>
                            <th>Count</th>
                            <th>Arrears (TZS)</th>
                            <th>Outstanding (TZS)</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['aging_analysis'] as $bucket)
                        <tr>
                            <td>{{ $bucket['bucket'] }}</td>
                            <td>{{ number_format($bucket['count']) }}</td>
                            <td class="amount">{{ number_format($bucket['arrears_amount']) }}</td>
                            <td class="amount">{{ number_format($bucket['outstanding_balance']) }}</td>
                            <td class="percentage {{ $bucket['percentage'] > 30 ? 'high' : ($bucket['percentage'] > 15 ? 'medium' : 'low') }}">
                                {{ $bucket['percentage'] }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Risk Distribution -->
            @if(isset($reportData['risk_distribution']))
            <div class="summary-section">
                <h3>Risk Classification</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Classification</th>
                            <th>Count</th>
                            <th>Amount (TZS)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['risk_distribution'] as $classification => $data)
                        <tr>
                            <td>{{ ucfirst($classification) }}</td>
                            <td>{{ number_format($data['count']) }}</td>
                            <td class="amount">{{ number_format($data['amount']) }}</td>
                            <td>
                                @if($classification === 'performing')
                                <span class="status-badge status-good">Healthy</span>
                                @elseif($classification === 'watch')
                                <span class="status-badge status-warning">Monitor</span>
                                @else
                                <span class="status-badge status-danger">At Risk</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Top Defaulters -->
            @if(isset($reportData['top_defaulters']) && count($reportData['top_defaulters']) > 0)
            <div class="summary-section">
                <h3>Top Defaulters</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Loan ID</th>
                            <th>Client</th>
                            <th>Arrears (TZS)</th>
                            <th>Days</th>
                            <th>Outstanding (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['top_defaulters'] as $defaulter)
                        <tr>
                            <td>{{ $defaulter['loan_id'] }}</td>
                            <td>{{ $defaulter['client_name'] }}</td>
                            <td class="amount">{{ number_format($defaulter['arrears_amount']) }}</td>
                            <td class="percentage high">{{ $defaulter['days_in_arrears'] }}</td>
                            <td class="amount">{{ number_format($defaulter['outstanding_balance']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Trial Balance -->
            @if(isset($reportData['summary']['total_debits']) && isset($reportData['summary']['total_credits']))
            <div class="summary-section">
                <h3>Balance Summary</h3>
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-value">TZS {{ number_format($reportData['summary']['total_debits']) }}</div>
                        <div class="metric-label">Total Debits</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-value">TZS {{ number_format($reportData['summary']['total_credits']) }}</div>
                        <div class="metric-label">Total Credits</div>
                    </div>
                    @if(isset($reportData['summary']['balanced']))
                    <div class="metric-card">
                        <div class="metric-value {{ $reportData['summary']['balanced'] ? 'positive' : 'negative' }}">
                            {{ $reportData['summary']['balanced'] ? 'Balanced' : 'Unbalanced' }}
                        </div>
                        <div class="metric-label">Status</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Executive Summary Highlights -->
            @if(isset($reportData['highlights']))
            <div class="summary-section">
                <h3>Performance Highlights</h3>
                <div class="metrics-grid">
                    @if(isset($reportData['highlights']['transactions']))
                    <div class="metric-card">
                        <div class="metric-value">{{ number_format($reportData['highlights']['transactions']['count']) }}</div>
                        <div class="metric-label">Transactions</div>
                    </div>
                    @endif
                    @if(isset($reportData['highlights']['new_loans']))
                    <div class="metric-card">
                        <div class="metric-value">{{ number_format($reportData['highlights']['new_loans']['count']) }}</div>
                        <div class="metric-label">New Loans</div>
                    </div>
                    @endif
                    @if(isset($reportData['highlights']['disbursements']))
                    <div class="metric-card">
                        <div class="metric-value">TZS {{ number_format($reportData['highlights']['disbursements']['total_amount']) }}</div>
                        <div class="metric-label">Disbursed</div>
                    </div>
                    @endif
                    @if(isset($reportData['highlights']['portfolio']))
                    <div class="metric-card">
                        <div class="metric-value {{ $reportData['highlights']['portfolio']['par_ratio'] > 5 ? 'negative' : 'positive' }}">
                            {{ $reportData['highlights']['portfolio']['par_ratio'] }}%
                        </div>
                        <div class="metric-label">PAR Ratio</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Call to Action -->
            <div class="cta-section">
                <a href="{{ config('app.url') }}/reports" class="cta-button">View Full Report</a>
            </div>

            <!-- Signature -->
            <p style="margin-top: 30px;">
                Best regards,<br>
                <strong>MFI Management System System</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>MFI Management System - Automated Reporting System</strong></p>
            <p>This is an automated report generated by the system. For questions, contact your system administrator.</p>
            <p style="margin-top: 10px;">
                &copy; {{ date('Y') }} NBC Bank. All rights reserved. |
                Report ID: {{ $reportData['report_type'] ?? 'N/A' }} |
                Generated: {{ $generatedAt->format('Y-m-d H:i:s') }}
            </p>
        </div>
    </div>
</body>
</html>
