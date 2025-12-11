<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Statements - {{ $year }}</title>
    <style nonce="{{ csp_nonce() }}">
        @page {
            margin: 20mm;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin: 0;
            color: #1e3a8a;
        }
        
        .header h2 {
            font-size: 14pt;
            font-weight: normal;
            margin: 5px 0;
        }
        
        .header p {
            font-size: 10pt;
            color: #666;
            margin: 5px 0;
        }
        
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 10px;
            padding: 5px;
            background-color: #f0f4f8;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            padding: 5px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        th {
            background-color: #1e3a8a;
            color: white;
            font-weight: bold;
        }
        
        .category-header {
            background-color: #dbeafe;
            font-weight: bold;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f3f4f6;
        }
        
        .grand-total {
            font-weight: bold;
            background-color: #93c5fd;
        }
        
        .amount {
            text-align: right;
        }
        
        .indent-1 {
            padding-left: 20px;
        }
        
        .indent-2 {
            padding-left: 40px;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        
        .balance-warning {
            color: red;
            font-weight: bold;
            padding: 10px;
            background-color: #fee;
            border: 1px solid red;
            margin: 10px 0;
        }
        
        .balance-ok {
            color: green;
            font-weight: bold;
            padding: 10px;
            background-color: #efe;
            border: 1px solid green;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $company_name }}</h1>
        <h2>INTEGRATED FINANCIAL STATEMENTS</h2>
        <p>For the year ended 31 December {{ $year }}</p>
    </div>

    {{-- Statement of Financial Position --}}
    <div class="section">
        <div class="section-title">STATEMENT OF FINANCIAL POSITION y</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 70%;">Description</th>
                    <th style="width: 30%;" class="amount">Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                {{-- Assets --}}
                <tr>
                    <td colspan="2" class="category-header">ASSETS</td>
                </tr>
                
                {{-- Current Assets --}}
                <tr>
                    <td class="indent-1"><strong>Current Assets</strong></td>
                    <td></td>
                </tr>
                @foreach($balance_sheet['assets']['current'] ?? [] as $asset)
                <tr>
                    <td class="indent-2">{{ $asset['account_name'] ?? '' }}</td>
                    <td class="amount">{{ number_format($asset['balance'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td class="indent-1">Total Current Assets</td>
                    <td class="amount">{{ number_format($balance_sheet['assets']['current_total'] ?? 0, 2) }}</td>
                </tr>
                
                {{-- Non-Current Assets --}}
                <tr>
                    <td class="indent-1"><strong>Non-Current Assets</strong></td>
                    <td></td>
                </tr>
                @foreach($balance_sheet['assets']['non_current'] ?? [] as $asset)
                <tr>
                    <td class="indent-2">{{ $asset['account_name'] ?? '' }}</td>
                    <td class="amount">{{ number_format($asset['balance'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td class="indent-1">Total Non-Current Assets</td>
                    <td class="amount">{{ number_format($balance_sheet['assets']['non_current_total'] ?? 0, 2) }}</td>
                </tr>
                
                <tr class="grand-total">
                    <td>TOTAL ASSETS</td>
                    <td class="amount">{{ number_format($balance_sheet['total_assets'] ?? 0, 2) }}</td>
                </tr>
                
                {{-- Liabilities --}}
                <tr>
                    <td colspan="2" class="category-header">LIABILITIES</td>
                </tr>
                
                {{-- Current Liabilities --}}
                <tr>
                    <td class="indent-1"><strong>Current Liabilities</strong></td>
                    <td></td>
                </tr>
                @foreach($balance_sheet['liabilities']['current'] ?? [] as $liability)
                <tr>
                    <td class="indent-2">{{ $liability['account_name'] ?? '' }}</td>
                    <td class="amount">{{ number_format($liability['balance'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td class="indent-1">Total Current Liabilities</td>
                    <td class="amount">{{ number_format($balance_sheet['liabilities']['current_total'] ?? 0, 2) }}</td>
                </tr>
                
                {{-- Non-Current Liabilities --}}
                <tr>
                    <td class="indent-1"><strong>Non-Current Liabilities</strong></td>
                    <td></td>
                </tr>
                @foreach($balance_sheet['liabilities']['non_current'] ?? [] as $liability)
                <tr>
                    <td class="indent-2">{{ $liability['account_name'] ?? '' }}</td>
                    <td class="amount">{{ number_format($liability['balance'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td class="indent-1">Total Non-Current Liabilities</td>
                    <td class="amount">{{ number_format($balance_sheet['liabilities']['non_current_total'] ?? 0, 2) }}</td>
                </tr>
                
                <tr class="grand-total">
                    <td>TOTAL LIABILITIES</td>
                    <td class="amount">{{ number_format($balance_sheet['liabilities']['total'] ?? 0, 2) }}</td>
                </tr>
                
                {{-- Equity --}}
                <tr>
                    <td colspan="2" class="category-header">EQUITY</td>
                </tr>
                @foreach($balance_sheet['equity']['items'] ?? [] as $equity)
                <tr>
                    <td class="indent-1">{{ $equity['account_name'] ?? '' }}</td>
                    <td class="amount">{{ number_format($equity['balance'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
                
                <tr class="grand-total">
                    <td>TOTAL EQUITY</td>
                    <td class="amount">{{ number_format($balance_sheet['equity']['total'] ?? 0, 2) }}</td>
                </tr>
                
                <tr class="grand-total">
                    <td>TOTAL LIABILITIES AND EQUITY</td>
                    <td class="amount">{{ number_format($balance_sheet['total_liabilities_equity'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>
        
        {{-- Balance Check --}}
        @php
            $difference = ($balance_sheet['total_assets'] ?? 0) - ($balance_sheet['total_liabilities_equity'] ?? 0);
        @endphp
        @if(abs($difference) < 0.01)
            <div class="balance-ok">
                ✓ Balance Sheet is balanced
            </div>
        @else
            <div class="balance-warning">
                ⚠ Balance Sheet is not balanced. Difference: {{ number_format($difference, 2) }} TZS
            </div>
        @endif
    </div>

    {{-- Page Break --}}
    <div class="page-break"></div>

    {{-- Income Statement --}}
    @if(isset($income_statement))
    <div class="section">
        <div class="section-title">INCOME STATEMENT</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 70%;">Description</th>
                    <th style="width: 30%;" class="amount">Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                {{-- Revenue --}}
                <tr>
                    <td colspan="2" class="category-header">REVENUE</td>
                </tr>
                @foreach($income_statement['revenue'] ?? [] as $revenue)
                <tr>
                    <td class="indent-1">{{ $revenue['account_name'] ?? '' }}</td>
                    <td class="amount">{{ number_format($revenue['amount'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td>Total Revenue</td>
                    <td class="amount">{{ number_format($income_statement['total_revenue'] ?? 0, 2) }}</td>
                </tr>
                
                {{-- Expenses --}}
                <tr>
                    <td colspan="2" class="category-header">EXPENSES</td>
                </tr>
                @foreach($income_statement['expenses'] ?? [] as $expense)
                <tr>
                    <td class="indent-1">{{ $expense['account_name'] ?? '' }}</td>
                    <td class="amount">{{ number_format($expense['amount'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td>Total Expenses</td>
                    <td class="amount">{{ number_format($income_statement['total_expenses'] ?? 0, 2) }}</td>
                </tr>
                
                <tr class="grand-total">
                    <td>NET INCOME (LOSS)</td>
                    <td class="amount">{{ number_format($income_statement['net_income'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    {{-- Financial Ratios --}}
    @if(!empty($ratios))
    <div class="section">
        <div class="section-title">FINANCIAL RATIOS</div>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Ratio</th>
                    <th class="amount">Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ratios as $category => $categoryRatios)
                    @foreach($categoryRatios as $ratio)
                    <tr>
                        <td>{{ ucfirst($category) }}</td>
                        <td>{{ $ratio['ratio_name'] }}</td>
                        <td class="amount">{{ number_format($ratio['value'] ?? 0, 2) }}%</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        Generated on {{ date('d M Y H:i:s') }} | Page <span class="pagenum"></span>
    </div>
</body>
</html>