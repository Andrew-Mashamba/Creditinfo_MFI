<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loans to Insiders Report</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #1e40af;
            margin: 0 0 5px 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .header h2 {
            color: #6b7280;
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: normal;
        }
        
        .header .badge {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            padding: 10px 15px;
            background: #f8fafc;
            border-left: 4px solid #2563eb;
            font-size: 9px;
        }
        
        .summary-section {
            margin-bottom: 30px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .summary-header {
            background: #2563eb;
            color: white;
            padding: 12px 15px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
        }
        
        .summary-item {
            padding: 15px;
            text-align: center;
            border-right: 1px solid #e5e7eb;
        }
        
        .summary-item:last-child {
            border-right: none;
        }
        
        .summary-item h4 {
            margin: 0 0 5px 0;
            font-size: 10px;
            color: #6b7280;
            font-weight: bold;
        }
        
        .summary-item .value {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
        }
        
        .compliance-status {
            padding: 15px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
        }
        
        .compliance-compliant {
            background: #d1fae5;
            color: #065f46;
        }
        
        .compliance-non-compliant {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin: 25px 0 10px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border: 1px solid #d1d5db;
        }
        
        th {
            background: #374151;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
            border: 1px solid #374151;
        }
        
        td {
            padding: 6px;
            border: 1px solid #e5e7eb;
            font-size: 8px;
        }
        
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .status-active { 
            color: #059669; 
            font-weight: bold; 
        }
        .status-pending { 
            color: #d97706; 
            font-weight: bold; 
        }
        .status-completed { 
            color: #2563eb; 
            font-weight: bold; 
        }
        
        .arrears-current { 
            color: #059669; 
            font-weight: bold; 
        }
        .arrears-overdue { 
            color: #dc2626; 
            font-weight: bold; 
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .footer p {
            margin: 2px 0;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-style: italic;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Loans to Insiders Report</h1>
        <h2>BOT Required Monthly Report</h2>
        <div class="badge">Regulatory Compliance</div>
    </div>
    
    <div class="report-info">
        <div>Report Date: {{ $reportDate }}</div>
        <div>Generated on: {{ $generatedAt }} | Generated by: {{ $generatedBy }}</div>
    </div>
    
    {{-- Summary Section --}}
    <div class="summary-section">
        <div class="summary-header">
            Executive Summary
        </div>
        <div class="summary-grid">
            <div class="summary-item">
                <h4>Total Insider Loans</h4>
                <div class="value">{{ $insiderLoanCount }}</div>
            </div>
            <div class="summary-item">
                <h4>Total Loan Amount</h4>
                <div class="value">{{ number_format($totalInsiderLoanAmount, 0) }} TZS</div>
            </div>
            <div class="summary-item">
                <h4>Average Loan Amount</h4>
                <div class="value">{{ number_format($averageInsiderLoanAmount, 0) }} TZS</div>
            </div>
            <div class="summary-item">
                <h4>Maximum Limit</h4>
                <div class="value">{{ number_format($maximumInsiderLoanLimit, 0) }} TZS</div>
            </div>
        </div>
        
        <div class="compliance-status {{ $complianceStatus === 'COMPLIANT' ? 'compliance-compliant' : 'compliance-non-compliant' }}">
            Compliance Status: {{ $complianceStatus }}
        </div>
    </div>
    
    {{-- Insider Categories Breakdown --}}
    <div class="section-title">Insider Categories Breakdown</div>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Category</th>
                <th style="width: 15%;">Count</th>
                <th style="width: 30%;">Total Amount (TZS)</th>
                <th style="width: 30%;">Average Amount (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($insiderCategories as $category => $data)
            <tr>
                <td><strong>{{ ucfirst(str_replace('_', ' ', $category)) }}</strong></td>
                <td>{{ $data['count'] }}</td>
                <td>{{ number_format($data['total_amount'], 0) }}</td>
                <td>{{ number_format($data['average_amount'], 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    {{-- Insider Loans Details --}}
    @if(count($insiderLoans) > 0)
    <div class="page-break"></div>
    <div class="section-title">Insider Loans Details</div>
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 12%;">Loan ID</th>
                <th style="width: 15%;">Client Name</th>
                <th style="width: 12%;">Position</th>
                <th style="width: 10%;">Department</th>
                <th style="width: 12%;">Loan Amount</th>
                <th style="width: 12%;">Outstanding</th>
                <th style="width: 8%;">Interest %</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 7%;">Days Arrears</th>
            </tr>
        </thead>
        <tbody>
            @foreach($insiderLoans as $index => $loan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $loan['loan_id'] ?? 'N/A' }}</strong></td>
                <td>{{ $loan['client_name'] ?? 'N/A' }}</td>
                <td>{{ $loan['employee_position'] ?? 'N/A' }}</td>
                <td>{{ $loan['employee_department'] ?? 'N/A' }}</td>
                <td>{{ number_format($loan['loan_amount'], 0) }}</td>
                <td>{{ number_format($loan['outstanding_balance'], 0) }}</td>
                <td>{{ $loan['interest_rate'] }}%</td>
                <td class="status-{{ strtolower($loan['loan_status']) }}">
                    {{ $loan['loan_status'] }}
                </td>
                <td class="{{ $loan['days_in_arrears'] > 0 ? 'arrears-overdue' : 'arrears-current' }}">
                    {{ $loan['days_in_arrears'] > 0 ? $loan['days_in_arrears'] . ' days' : 'Current' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        No insider loans found.
    </div>
    @endif
    
    {{-- Related Party Loans --}}
    @if(count($relatedPartyLoans) > 0)
    <div class="page-break"></div>
    <div class="section-title">Related Party Loans</div>
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 12%;">Loan ID</th>
                <th style="width: 20%;">Client Name</th>
                <th style="width: 15%;">Relationship</th>
                <th style="width: 12%;">Loan Amount</th>
                <th style="width: 12%;">Outstanding</th>
                <th style="width: 8%;">Interest %</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 9%;">Days Arrears</th>
            </tr>
        </thead>
        <tbody>
            @foreach($relatedPartyLoans as $index => $loan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $loan['loan_id'] ?? 'N/A' }}</strong></td>
                <td>{{ $loan['client_name'] ?? 'N/A' }}</td>
                <td>{{ $loan['relationship_type'] ?? 'N/A' }}</td>
                <td>{{ number_format($loan['loan_amount'], 0) }}</td>
                <td>{{ number_format($loan['outstanding_balance'], 0) }}</td>
                <td>{{ $loan['interest_rate'] }}%</td>
                <td class="status-{{ strtolower($loan['loan_status']) }}">
                    {{ $loan['loan_status'] }}
                </td>
                <td class="{{ $loan['days_in_arrears'] > 0 ? 'arrears-overdue' : 'arrears-current' }}">
                    {{ $loan['days_in_arrears'] > 0 ? $loan['days_in_arrears'] . ' days' : 'Current' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    
    <div class="footer">
        <p>This report was generated automatically by the SACCOS Management System</p>
        <p>BOT Required Report - Loans to Insiders and Related Parties Disclosure</p>
        <p>For questions or clarifications, please contact the system administrator</p>
    </div>
</body>
</html>
