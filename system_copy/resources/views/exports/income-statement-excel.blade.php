<table>
    <thead>
        <tr>
            <th colspan="2" style="text-align: center; font-weight: bold; font-size: 16px;">
                NBC SACCOS LTD
            </th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: center; font-weight: bold; font-size: 14px;">
                INCOME STATEMENT
            </th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: center;">
                For the year ended {{ $period->end_date ?? date('Y-m-d') }}
            </th>
        </tr>
        <tr>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        {{-- REVENUE --}}
        <tr>
            <td style="font-weight: bold; background-color: #10b981; color: white;">REVENUE</td>
            <td style="font-weight: bold; background-color: #10b981; color: white;">Amount (TZS)</td>
        </tr>
        
        @php
            $totalRevenue = 0;
        @endphp
        
        @foreach($data['revenue'] ?? [] as $revenue)
        <tr>
            <td style="padding-left: 20px;">{{ $revenue['account_name'] ?? '' }}</td>
            <td style="text-align: right;">{{ number_format($revenue['balance'] ?? 0, 2) }}</td>
        </tr>
        @php
            $totalRevenue += $revenue['balance'] ?? 0;
        @endphp
        @endforeach
        
        <tr>
            <td style="font-weight: bold; background-color: #d1fae5;">Total Revenue</td>
            <td style="text-align: right; font-weight: bold; background-color: #d1fae5;">
                {{ number_format($totalRevenue, 2) }}
            </td>
        </tr>
        
        <tr>
            <td></td>
            <td></td>
        </tr>
        
        {{-- EXPENSES --}}
        <tr>
            <td style="font-weight: bold; background-color: #ef4444; color: white;">EXPENSES</td>
            <td style="font-weight: bold; background-color: #ef4444; color: white;">Amount (TZS)</td>
        </tr>
        
        @php
            $totalExpenses = 0;
        @endphp
        
        {{-- Operating Expenses --}}
        @if(isset($data['operating_expenses']) && count($data['operating_expenses']) > 0)
        <tr>
            <td style="font-weight: bold; background-color: #fee2e2;">Operating Expenses</td>
            <td style="background-color: #fee2e2;"></td>
        </tr>
        @foreach($data['operating_expenses'] ?? [] as $expense)
        <tr>
            <td style="padding-left: 20px;">{{ $expense['account_name'] ?? '' }}</td>
            <td style="text-align: right;">{{ number_format($expense['balance'] ?? 0, 2) }}</td>
        </tr>
        @php
            $totalExpenses += $expense['balance'] ?? 0;
        @endphp
        @endforeach
        @endif
        
        {{-- Administrative Expenses --}}
        @if(isset($data['administrative_expenses']) && count($data['administrative_expenses']) > 0)
        <tr>
            <td style="font-weight: bold; background-color: #fee2e2;">Administrative Expenses</td>
            <td style="background-color: #fee2e2;"></td>
        </tr>
        @foreach($data['administrative_expenses'] ?? [] as $expense)
        <tr>
            <td style="padding-left: 20px;">{{ $expense['account_name'] ?? '' }}</td>
            <td style="text-align: right;">{{ number_format($expense['balance'] ?? 0, 2) }}</td>
        </tr>
        @php
            $totalExpenses += $expense['balance'] ?? 0;
        @endphp
        @endforeach
        @endif
        
        {{-- Other Expenses --}}
        @foreach($data['expenses'] ?? [] as $expense)
        <tr>
            <td style="padding-left: 20px;">{{ $expense['account_name'] ?? '' }}</td>
            <td style="text-align: right;">{{ number_format($expense['balance'] ?? 0, 2) }}</td>
        </tr>
        @php
            $totalExpenses += $expense['balance'] ?? 0;
        @endphp
        @endforeach
        
        <tr>
            <td style="font-weight: bold; background-color: #fecaca;">Total Expenses</td>
            <td style="text-align: right; font-weight: bold; background-color: #fecaca;">
                {{ number_format($totalExpenses, 2) }}
            </td>
        </tr>
        
        <tr>
            <td></td>
            <td></td>
        </tr>
        
        {{-- NET INCOME --}}
        @php
            $netIncome = $totalRevenue - $totalExpenses;
        @endphp
        <tr>
            <td style="font-weight: bold; font-size: 12px; 
                background-color: {{ $netIncome >= 0 ? '#10b981' : '#ef4444' }}; 
                color: white;">
                NET {{ $netIncome >= 0 ? 'INCOME' : 'LOSS' }}
            </td>
            <td style="text-align: right; font-weight: bold; font-size: 12px;
                background-color: {{ $netIncome >= 0 ? '#10b981' : '#ef4444' }}; 
                color: white;">
                {{ number_format(abs($netIncome), 2) }}
            </td>
        </tr>
        
        <tr>
            <td></td>
            <td></td>
        </tr>
        
        {{-- Performance Metrics --}}
        <tr>
            <td colspan="2" style="font-weight: bold; background-color: #f3f4f6;">Performance Metrics</td>
        </tr>
        @if($totalRevenue > 0)
        <tr>
            <td style="padding-left: 20px;">Gross Profit Margin</td>
            <td style="text-align: right;">{{ number_format(($netIncome / $totalRevenue) * 100, 2) }}%</td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">Operating Expense Ratio</td>
            <td style="text-align: right;">{{ number_format(($totalExpenses / $totalRevenue) * 100, 2) }}%</td>
        </tr>
        @endif
    </tbody>
</table>