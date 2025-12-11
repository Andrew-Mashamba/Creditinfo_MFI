<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loan Application Report</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #7f8c8d;
        }
        .report-info {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px;
            margin-bottom: 30px;
        }
        .summary-table td {
            width: 14.28%;
            padding: 12px 8px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #e0e0e0;
            background-color: #ffffff;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
        }
        .summary-card {
            position: relative;
        }
        .summary-card h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #2c3e50;
            font-weight: bold;
            line-height: 1.2;
        }
        .summary-card p {
            margin: 0;
            color: #6c757d;
            font-size: 9px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.1;
        }
        .summary-icon {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            opacity: 0.3;
        }
        .summary-section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #007bff;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .status-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .status-active { background-color: #d1ecf1; color: #0c5460; }
        .status-completed { background-color: #e2e3e5; color: #383d41; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Loan Application Report</h1>
        <p>Summary of loan applications received and processed</p>
    </div>

    <div class="report-info">
        <strong>Report Period:</strong> {{ $startDate }} to {{ $endDate }}<br>
        @if($statusFilter)
            <strong>Status Filter:</strong> {{ $statusFilter }}<br>
        @endif
        <strong>Generated On:</strong> {{ now()->format('Y-m-d H:i:s') }}<br>
        <strong>Total Records:</strong> {{ $applications->count() }}
    </div>

    <table class="summary-table">
        <tr>
            <td style="border-top: 4px solid #007bff; background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);">
                <div class="summary-card">
                    <h4>{{ $totalApplications }}</h4>
                    <p>Total Applications</p>
                </div>
            </td>
            <td style="border-top: 4px solid #28a745; background: linear-gradient(135deg, #f8fff9 0%, #ffffff 100%);">
                <div class="summary-card">
                    <h4>{{ $approvedApplications }}</h4>
                    <p>Approved Applications</p>
                </div>
            </td>
            <td style="border-top: 4px solid #ffc107; background: linear-gradient(135deg, #fffdf8 0%, #ffffff 100%);">
                <div class="summary-card">                    
                    <h4>{{ $pendingApplications }}</h4>
                    <p>Pending Applications</p>
                </div>
            </td>
            <td style="border-top: 4px solid #dc3545; background: linear-gradient(135deg, #fff8f8 0%, #ffffff 100%);">
                <div class="summary-card">                
                    <h4>{{ $rejectedApplications }}</h4>
                    <p>Rejected Applications</p>
                </div>
            </td>
            <td style="border-top: 4px solid #6f42c1; background: linear-gradient(135deg, #faf8ff 0%, #ffffff 100%);">
                <div class="summary-card">
                    <h4>{{ number_format($totalApplicationAmount, 2) }}</h4>
                    <p>Total Amount (TZS)</p>
                </div>
            </td>
            <td style="border-top: 4px solid #20c997; background: linear-gradient(135deg, #f8fffe 0%, #ffffff 100%);">
                <div class="summary-card">
                    <h4>{{ number_format($averageApplicationAmount, 2) }}</h4>
                    <p>Average Amount (TZS)</p>
                </div>
            </td>
            <td style="border-top: 4px solid #fd7e14; background: linear-gradient(135deg, #fff8f0 0%, #ffffff 100%);">
                <div class="summary-card">
                    <h4>{{ $approvalRate }}%</h4>
                    <p>Approval Rate</p>
                </div>
            </td>
        </tr>
    </table>

    @if($applications->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Application Date</th>
                    <th>Client Name</th>
                    <th>Client Number</th>
                    <th>Loan ID</th>
                    <th>Loan Product</th>
                    <th>Amount (TZS)</th>
                    <th>Interest Rate (%)</th>
                    <th>Term (Months)</th>
                    <th>Branch</th>
                    <th>Status</th>
                    <th>Processing Days</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $index => $application)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $application->application_date ?? 'N/A' }}</td>
                        <td>{{ $application->client_name ?? 'N/A' }}</td>
                        <td>{{ $application->client_number ?? 'N/A' }}</td>
                        <td>{{ $application->loan_id ?? 'N/A' }}</td>
                        <td>{{ $application->loan_product_name ?? 'N/A' }}</td>
                        <td>{{ number_format((float)$application->principle, 2) }}</td>
                        <td>{{ number_format((float)$application->interest, 2) }}</td>
                        <td>{{ $application->tenure ?? 'N/A' }}</td>
                        <td>{{ $application->branch_name ?? 'N/A' }}</td>
                        <td>
                            <span class="status-badge status-{{ strtolower($application->status ?? 'unknown') }}">
                                {{ $application->status ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            @if($application->processing_days !== null)
                                {{ $application->processing_days }} days
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #6c757d;">
            <p>No loan applications found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Loan Management System</p>
        <p>For questions or support, please contact the system administrator</p>
    </div>
</body>
</html>
