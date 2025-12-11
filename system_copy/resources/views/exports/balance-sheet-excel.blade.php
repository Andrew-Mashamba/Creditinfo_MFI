<table>
    <thead>
        <tr>
            <th colspan="2" style="text-align: center; font-weight: bold; font-size: 16px;">
                NBC SACCOS LTD
            </th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: center; font-weight: bold; font-size: 14px;">
                STATEMENT OF FINANCIAL POSITION x
            </th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: center;">
                As at {{ $period->end_date ?? date('Y-m-d') }}
            </th>
        </tr>
        <tr>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        {{-- ASSETS --}}
        <tr>
            <td style="font-weight: bold; background-color: #1e3a8a; color: white;">ASSETS</td>
            <td style="font-weight: bold; background-color: #1e3a8a; color: white;">Amount (TZS)</td>
        </tr>
        
        {{-- Current Assets --}}
        <tr>
            <td style="font-weight: bold; background-color: #dbeafe;">Current Assets</td>
            <td style="background-color: #dbeafe;"></td>
        </tr>
        @foreach($data['assets']['current'] ?? [] as $asset)
        <tr>
            <td style="padding-left: 20px;">{{ $asset['account_name'] ?? '' }}</td>
            <td style="text-align: right;">{{ number_format($asset['balance'] ?? 0, 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td style="font-weight: bold; padding-left: 10px;">Total Current Assets</td>
            <td style="text-align: right; font-weight: bold;">
                {{ number_format($data['assets']['current_total'] ?? 0, 2) }}
            </td>
        </tr>
        
        {{-- Non-Current Assets --}}
        <tr>
            <td style="font-weight: bold; background-color: #dbeafe;">Non-Current Assets</td>
            <td style="background-color: #dbeafe;"></td>
        </tr>
        @foreach($data['assets']['non_current'] ?? [] as $asset)
        <tr>
            <td style="padding-left: 20px;">{{ $asset['account_name'] ?? '' }}</td>
            <td style="text-align: right;">{{ number_format($asset['balance'] ?? 0, 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td style="font-weight: bold; padding-left: 10px;">Total Non-Current Assets</td>
            <td style="text-align: right; font-weight: bold;">
                {{ number_format($data['assets']['non_current_total'] ?? 0, 2) }}
            </td>
        </tr>
        
        {{-- Total Assets --}}
        <tr>
            <td style="font-weight: bold; background-color: #93c5fd;">TOTAL ASSETS</td>
            <td style="text-align: right; font-weight: bold; background-color: #93c5fd;">
                {{ number_format($data['total_assets'] ?? 0, 2) }}
            </td>
        </tr>
        
        <tr>
            <td></td>
            <td></td>
        </tr>
        
        {{-- LIABILITIES --}}
        <tr>
            <td style="font-weight: bold; background-color: #1e3a8a; color: white;">LIABILITIES</td>
            <td style="font-weight: bold; background-color: #1e3a8a; color: white;">Amount (TZS)</td>
        </tr>
        
        {{-- Current Liabilities --}}
        <tr>
            <td style="font-weight: bold; background-color: #dbeafe;">Current Liabilities</td>
            <td style="background-color: #dbeafe;"></td>
        </tr>
        @foreach($data['liabilities']['current'] ?? [] as $liability)
        <tr>
            <td style="padding-left: 20px;">{{ $liability['account_name'] ?? '' }}</td>
            <td style="text-align: right;">{{ number_format($liability['balance'] ?? 0, 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td style="font-weight: bold; padding-left: 10px;">Total Current Liabilities</td>
            <td style="text-align: right; font-weight: bold;">
                {{ number_format($data['liabilities']['current_total'] ?? 0, 2) }}
            </td>
        </tr>
        
        {{-- Non-Current Liabilities --}}
        <tr>
            <td style="font-weight: bold; background-color: #dbeafe;">Non-Current Liabilities</td>
            <td style="background-color: #dbeafe;"></td>
        </tr>
        @foreach($data['liabilities']['non_current'] ?? [] as $liability)
        <tr>
            <td style="padding-left: 20px;">{{ $liability['account_name'] ?? '' }}</td>
            <td style="text-align: right;">{{ number_format($liability['balance'] ?? 0, 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td style="font-weight: bold; padding-left: 10px;">Total Non-Current Liabilities</td>
            <td style="text-align: right; font-weight: bold;">
                {{ number_format($data['liabilities']['non_current_total'] ?? 0, 2) }}
            </td>
        </tr>
        
        {{-- Total Liabilities --}}
        <tr>
            <td style="font-weight: bold; background-color: #fecaca;">TOTAL LIABILITIES</td>
            <td style="text-align: right; font-weight: bold; background-color: #fecaca;">
                {{ number_format($data['liabilities']['total'] ?? 0, 2) }}
            </td>
        </tr>
        
        <tr>
            <td></td>
            <td></td>
        </tr>
        
        {{-- EQUITY --}}
        <tr>
            <td style="font-weight: bold; background-color: #1e3a8a; color: white;">EQUITY</td>
            <td style="font-weight: bold; background-color: #1e3a8a; color: white;">Amount (TZS)</td>
        </tr>
        @foreach($data['equity']['items'] ?? [] as $equity)
        <tr>
            <td style="padding-left: 10px;">{{ $equity['account_name'] ?? '' }}</td>
            <td style="text-align: right;">{{ number_format($equity['balance'] ?? 0, 2) }}</td>
        </tr>
        @endforeach
        
        {{-- Total Equity --}}
        <tr>
            <td style="font-weight: bold; background-color: #bbf7d0;">TOTAL EQUITY</td>
            <td style="text-align: right; font-weight: bold; background-color: #bbf7d0;">
                {{ number_format($data['equity']['total'] ?? 0, 2) }}
            </td>
        </tr>
        
        <tr>
            <td></td>
            <td></td>
        </tr>
        
        {{-- Total Liabilities and Equity --}}
        <tr>
            <td style="font-weight: bold; background-color: #93c5fd;">TOTAL LIABILITIES AND EQUITY</td>
            <td style="text-align: right; font-weight: bold; background-color: #93c5fd;">
                {{ number_format($data['total_liabilities_equity'] ?? 0, 2) }}
            </td>
        </tr>
        
        <tr>
            <td></td>
            <td></td>
        </tr>
        
        {{-- Balance Check --}}
        <tr>
            <td style="font-weight: bold;">Balance Check (Assets - Liabilities - Equity)</td>
            <td style="text-align: right; font-weight: bold; 
                @if(abs(($data['total_assets'] ?? 0) - ($data['total_liabilities_equity'] ?? 0)) < 0.01)
                    color: green;
                @else
                    color: red;
                @endif">
                {{ number_format(($data['total_assets'] ?? 0) - ($data['total_liabilities_equity'] ?? 0), 2) }}
            </td>
        </tr>
    </tbody>
</table>