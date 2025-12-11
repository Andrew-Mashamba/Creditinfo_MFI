<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Income Statement - {{ $selectedYear }}</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #000;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 16px;
            font-weight: normal;
            color: #555;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .section-header {
            background-color: #e0e0e0;
            font-weight: bold;
            font-size: 13px;
        }
        
        .total-row {
            background-color: #fffacd;
            font-weight: bold;
        }
        
        .grand-total {
            background-color: #90ee90;
            font-weight: bold;
            font-size: 13px;
        }
        
        .amount {
            text-align: right;
        }
        
        .note-column {
            text-align: center;
            width: 50px;
        }
        
        .negative {
            color: #d00;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #999;
            font-size: 10px;
            color: #666;
        }
        
        .footer p {
            margin: 3px 0;
        }
        
        @page {
            margin: 0.5in;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $organizationName }}</h1>
        <h2>INCOME STATEMENT </h2>
        <p>For the Year Ended December 31, {{ $selectedYear }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 50%;">PARTICULARS</th>
                <th class="note-column">Note</th>
                <th style="width: 20%;">{{ $comparisonYears[0] }}</th>
                <th style="width: 20%;">{{ $comparisonYears[1] }}</th>
            </tr>
        </thead>
        <tbody>
            <!-- INCOME SECTION -->
            <tr>
                <td colspan="4" class="section-header">INCOME</td>
            </tr>
            
            @php $noteNumber = 1; @endphp
            
            <tr>
                <td>Interest Income on Loans</td>
                <td class="note-column">{{ $noteNumber++ }}</td>
                <td class="amount">{{ number_format($interestIncome[$comparisonYears[0]], 2) }}</td>
                <td class="amount">{{ number_format($interestIncome[$comparisonYears[1]], 2) }}</td>
            </tr>
            
            <tr>
                <td>Less: Interest on Savings</td>
                <td class="note-column">{{ $noteNumber++ }}</td>
                <td class="amount negative">({{ number_format($interestOnSavings[$comparisonYears[0]], 2) }})</td>
                <td class="amount negative">({{ number_format($interestOnSavings[$comparisonYears[1]], 2) }})</td>
            </tr>
            
            <tr>
                <td>Other Income</td>
                <td class="note-column">{{ $noteNumber++ }}</td>
                <td class="amount">{{ number_format($otherIncome[$comparisonYears[0]], 2) }}</td>
                <td class="amount">{{ number_format($otherIncome[$comparisonYears[1]], 2) }}</td>
            </tr>
            
            <tr class="total-row">
                <td><strong>TOTAL INCOME</strong></td>
                <td class="note-column"></td>
                <td class="amount"><strong>{{ number_format($totalIncome[$comparisonYears[0]], 2) }}</strong></td>
                <td class="amount"><strong>{{ number_format($totalIncome[$comparisonYears[1]], 2) }}</strong></td>
            </tr>
            
            <!-- Blank row -->
            <tr>
                <td colspan="4" style="border: none; height: 10px;"></td>
            </tr>
            
            <!-- EXPENSES SECTION -->
            <tr>
                <td colspan="4" class="section-header">OPERATING EXPENSES</td>
            </tr>
            
            <tr>
                <td>Administrative Expenses</td>
                <td class="note-column">{{ $noteNumber++ }}</td>
                <td class="amount">{{ number_format($administrativeExpenses[$comparisonYears[0]], 2) }}</td>
                <td class="amount">{{ number_format($administrativeExpenses[$comparisonYears[1]], 2) }}</td>
            </tr>
            
            <tr>
                <td>Personnel Expenses</td>
                <td class="note-column">{{ $noteNumber++ }}</td>
                <td class="amount">{{ number_format($personnelExpenses[$comparisonYears[0]], 2) }}</td>
                <td class="amount">{{ number_format($personnelExpenses[$comparisonYears[1]], 2) }}</td>
            </tr>
            
            <tr>
                <td>Operating Expenses</td>
                <td class="note-column">{{ $noteNumber++ }}</td>
                <td class="amount">{{ number_format($operatingExpenses[$comparisonYears[0]], 2) }}</td>
                <td class="amount">{{ number_format($operatingExpenses[$comparisonYears[1]], 2) }}</td>
            </tr>
            
            <tr class="total-row">
                <td><strong>TOTAL OPERATING EXPENSES</strong></td>
                <td class="note-column"></td>
                <td class="amount"><strong>{{ number_format($totalExpenses[$comparisonYears[0]], 2) }}</strong></td>
                <td class="amount"><strong>{{ number_format($totalExpenses[$comparisonYears[1]], 2) }}</strong></td>
            </tr>
            
            <!-- Blank row -->
            <tr>
                <td colspan="4" style="border: none; height: 10px;"></td>
            </tr>
            
            <!-- PROFIT BEFORE TAX -->
            <tr class="total-row">
                <td><strong>SURPLUS/(DEFICIT) BEFORE TAX</strong></td>
                <td class="note-column"></td>
                <td class="amount {{ $profitBeforeTax[$comparisonYears[0]] < 0 ? 'negative' : '' }}">
                    <strong>{{ number_format($profitBeforeTax[$comparisonYears[0]], 2) }}</strong>
                </td>
                <td class="amount {{ $profitBeforeTax[$comparisonYears[1]] < 0 ? 'negative' : '' }}">
                    <strong>{{ number_format($profitBeforeTax[$comparisonYears[1]], 2) }}</strong>
                </td>
            </tr>
            
            <!-- TAX -->
            <tr>
                <td>Less: Tax Expense (30%)</td>
                <td class="note-column">{{ $noteNumber++ }}</td>
                <td class="amount">{{ number_format($taxExpense[$comparisonYears[0]], 2) }}</td>
                <td class="amount">{{ number_format($taxExpense[$comparisonYears[1]], 2) }}</td>
            </tr>
            
            <!-- NET PROFIT -->
            <tr class="grand-total">
                <td><strong>NET SURPLUS/(DEFICIT) FOR THE YEAR</strong></td>
                <td class="note-column"></td>
                <td class="amount {{ $netProfit[$comparisonYears[0]] < 0 ? 'negative' : '' }}">
                    <strong>{{ number_format($netProfit[$comparisonYears[0]], 2) }}</strong>
                </td>
                <td class="amount {{ $netProfit[$comparisonYears[1]] < 0 ? 'negative' : '' }}">
                    <strong>{{ number_format($netProfit[$comparisonYears[1]], 2) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div class="footer">
        <p><strong>Notes:</strong> The accompanying notes form an integral part of these financial statements.</p>
        <p>Generated on {{ date('F d, Y H:i:s') }} | SACCOS Core System</p>
    </div>
</body>
</html>