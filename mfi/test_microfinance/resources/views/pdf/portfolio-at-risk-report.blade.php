<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio at Risk Report</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1E40AF;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #1E40AF;
            margin: 0;
            font-size: 24px;
        }
        
        .header h2 {
            color: #6B7280;
            margin: 5px 0 0 0;
            font-size: 16px;
            font-weight: normal;
        }
        
        
        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 10px;
            color: #6B7280;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border: 1px solid #1E40AF;
        }
        
        th {
            background-color: #1E40AF;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            border: 1px solid #1E40AF;
        }
        
        td {
            padding: 6px 4px;
            border: 1px solid #E5E7EB;
            font-size: 8px;
        }
        
        tr:nth-child(even) {
            background-color: #F9FAFB;
        }
        
        .risk-normal { color: #059669; font-weight: bold; }
        .risk-watch { color: #D97706; font-weight: bold; }
        .risk-substandard { color: #DC2626; font-weight: bold; }
        .risk-doubtful { color: #7C2D12; font-weight: bold; }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #6B7280;
            border-top: 1px solid #E5E7EB;
            padding-top: 10px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6B7280;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Portfolio at Risk Report</h1>
        <h2>{{ $categoryName }}</h2>
    </div>
    
    <div class="report-info">
        <div>Generated on: {{ $generatedAt }}</div>
        <div>Total Loans: {{ $totalLoans }} | Total Outstanding: {{ number_format($totalOutstanding, 2) }} TZS</div>
    </div>
    
    
    @if($loans->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 20%;">Client Name</th>
                <th style="width: 12%;">Client Number</th>
                <th style="width: 12%;">Loan Amount</th>
                <th style="width: 10%;">Start Date</th>
                <th style="width: 10%;">Due Date</th>
                <th style="width: 10%;">Interest</th>
                <th style="width: 12%;">Outstanding</th>
                <th style="width: 8%;">Days Arrears</th>
                <th style="width: 8%;">Risk Category</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $index => $loan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $loan->client_name ?? 'N/A' }}</td>
                <td>{{ $loan->client_number ?? 'N/A' }}</td>
                <td>{{ number_format($loan->principle, 2) }}</td>
                <td>{{ $loan->start_date ?? 'N/A' }}</td>
                <td>{{ $loan->due_date ?? 'N/A' }}</td>
                <td>{{ number_format($loan->interest, 2) }}</td>
                <td>{{ number_format($loan->outstanding_amount, 2) }}</td>
                <td>{{ $loan->days_in_arrears }}</td>
                <td class="risk-{{ strtolower(str_replace(['/', ' '], ['', '-'], $loan->risk_category)) }}">
                    {{ $loan->risk_category }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        No loans found for the selected criteria.
    </div>
    @endif
    
    <div class="footer">
        <p>This report was generated automatically by the MFI Management System</p>
        <p>For questions or clarifications, please contact the system administrator</p>
    </div>
</body>
</html>
