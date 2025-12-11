<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Statement of Financial Position</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .report-date {
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background-color: #f3f4f6;
            padding: 8px;
            text-align: left;
            border: 1px solid #e5e7eb;
            font-weight: bold;
        }
        td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
        }
        .section-header {
            background-color: #dbeafe;
            font-weight: bold;
            font-size: 12px;
        }
        .category-header {
            background-color: #f9fafb;
            font-weight: bold;
            padding-left: 15px !important;
        }
        .account-row {
            padding-left: 30px !important;
        }
        .child-account {
            padding-left: 45px !important;
            font-size: 10px;
        }
        .total-row {
            background-color: #2563eb;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }
        .subtotal-row {
            background-color: #fef3c7;
            font-weight: bold;
        }
        .amount {
            text-align: right;
        }
        .negative {
            color: #dc2626;
        }
        .note-number {
            text-align: center;
            color: #2563eb;
            font-weight: bold;
        }
        .variance {
            text-align: right;
        }
        .variance-positive {
            color: #16a34a;
        }
        .variance-negative {
            color: #dc2626;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #666;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $companyName }}</div>
        <div class="report-title">STATEMENT OF FINANCIAL POSITION z</div>
        <div class="report-date">As at {{ $reportingDate }}</div>
        <div class="report-date">(All amounts in Tanzanian Shillings)</div>
    </div>

    <!-- Assets Section -->
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">ASSETS</th>
                <th style="width: 10%;">Note</th>
                @foreach($comparisonYears as $year)
                <th style="width: 20%;" class="amount">{{ $year }}</th>
                @endforeach
                <th style="width: 10%;" class="variance">Variance %</th>
            </tr>
        </thead>
        <tbody>
            <!-- Current Assets -->
            @if(count($assetsData['current'] ?? []) > 0)
            <tr class="section-header">
                <td colspan="{{ 3 + count($comparisonYears) }}">CURRENT ASSETS</td>
            </tr>
            @foreach($assetsData['current'] as $index => $asset)
            <tr>
                <td class="category-header">{{ $asset['account_name'] }}</td>
                <td class="note-number">{{ $index + 5 }}</td>
                @foreach($comparisonYears as $year)
                <td class="amount">
                    {{ number_format($asset['years'][$year] ?? 0, 2) }}
                </td>
                @endforeach
                <td class="variance">
                    @php
                        $variance = 0;
                        if(isset($asset['years'][$comparisonYears[1]]) && $asset['years'][$comparisonYears[1]] != 0) {
                            $variance = (($asset['years'][$comparisonYears[0]] ?? 0) - $asset['years'][$comparisonYears[1]]) / abs($asset['years'][$comparisonYears[1]]) * 100;
                        }
                    @endphp
                    <span class="{{ $variance >= 0 ? 'variance-positive' : 'variance-negative' }}">
                        {{ number_format($variance, 1) }}%
                    </span>
                </td>
            </tr>
            @endforeach
            @endif

            <!-- Non-Current Assets -->
            @if(count($assetsData['non_current'] ?? []) > 0)
            <tr class="section-header">
                <td colspan="{{ 3 + count($comparisonYears) }}">NON-CURRENT ASSETS</td>
            </tr>
            @foreach($assetsData['non_current'] as $index => $asset)
            <tr>
                <td class="category-header">{{ $asset['account_name'] }}</td>
                <td class="note-number">{{ count($assetsData['current']) + $index + 5 }}</td>
                @foreach($comparisonYears as $year)
                <td class="amount">
                    {{ number_format($asset['years'][$year] ?? 0, 2) }}
                </td>
                @endforeach
                <td class="variance">
                    @php
                        $variance = 0;
                        if(isset($asset['years'][$comparisonYears[1]]) && $asset['years'][$comparisonYears[1]] != 0) {
                            $variance = (($asset['years'][$comparisonYears[0]] ?? 0) - $asset['years'][$comparisonYears[1]]) / abs($asset['years'][$comparisonYears[1]]) * 100;
                        }
                    @endphp
                    <span class="{{ $variance >= 0 ? 'variance-positive' : 'variance-negative' }}">
                        {{ number_format($variance, 1) }}%
                    </span>
                </td>
            </tr>
            @endforeach
            @endif

            <!-- Total Assets -->
            <tr class="total-row">
                <td>TOTAL ASSETS</td>
                <td></td>
                @foreach($comparisonYears as $year)
                <td class="amount">
                    {{ number_format($assetsData['total'][$year] ?? 0, 2) }}
                </td>
                @endforeach
                <td class="variance">
                    @php
                        $variance = 0;
                        if(isset($assetsData['total'][$comparisonYears[1]]) && $assetsData['total'][$comparisonYears[1]] != 0) {
                            $variance = (($assetsData['total'][$comparisonYears[0]] ?? 0) - $assetsData['total'][$comparisonYears[1]]) / abs($assetsData['total'][$comparisonYears[1]]) * 100;
                        }
                    @endphp
                    {{ number_format($variance, 1) }}%
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Equity Section -->
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">EQUITY</th>
                <th style="width: 10%;">Note</th>
                @foreach($comparisonYears as $year)
                <th style="width: 20%;" class="amount">{{ $year }}</th>
                @endforeach
                <th style="width: 10%;" class="variance">Variance %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($equityData['current'] ?? [] as $index => $equity)
            <tr>
                <td class="category-header">{{ $equity['account_name'] }}</td>
                <td class="note-number">{{ $index + 7 }}</td>
                @foreach($comparisonYears as $year)
                <td class="amount">
                    {{ number_format($equity['years'][$year] ?? 0, 2) }}
                </td>
                @endforeach
                <td class="variance">
                    @php
                        $variance = 0;
                        if(isset($equity['years'][$comparisonYears[1]]) && $equity['years'][$comparisonYears[1]] != 0) {
                            $variance = (($equity['years'][$comparisonYears[0]] ?? 0) - $equity['years'][$comparisonYears[1]]) / abs($equity['years'][$comparisonYears[1]]) * 100;
                        }
                    @endphp
                    <span class="{{ $variance >= 0 ? 'variance-positive' : 'variance-negative' }}">
                        {{ number_format($variance, 1) }}%
                    </span>
                </td>
            </tr>
            @endforeach

            <!-- Total Equity -->
            <tr class="subtotal-row">
                <td>TOTAL EQUITY</td>
                <td></td>
                @foreach($comparisonYears as $year)
                <td class="amount">
                    {{ number_format($equityData['total'][$year] ?? 0, 2) }}
                </td>
                @endforeach
                <td class="variance">
                    @php
                        $variance = 0;
                        if(isset($equityData['total'][$comparisonYears[1]]) && $equityData['total'][$comparisonYears[1]] != 0) {
                            $variance = (($equityData['total'][$comparisonYears[0]] ?? 0) - $equityData['total'][$comparisonYears[1]]) / abs($equityData['total'][$comparisonYears[1]]) * 100;
                        }
                    @endphp
                    {{ number_format($variance, 1) }}%
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Liabilities Section -->
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">LIABILITIES</th>
                <th style="width: 10%;">Note</th>
                @foreach($comparisonYears as $year)
                <th style="width: 20%;" class="amount">{{ $year }}</th>
                @endforeach
                <th style="width: 10%;" class="variance">Variance %</th>
            </tr>
        </thead>
        <tbody>
            <!-- Current Liabilities -->
            @if(count($liabilitiesData['current'] ?? []) > 0)
            <tr class="section-header">
                <td colspan="{{ 3 + count($comparisonYears) }}">CURRENT LIABILITIES</td>
            </tr>
            @foreach($liabilitiesData['current'] as $index => $liability)
            <tr>
                <td class="category-header">{{ $liability['account_name'] }}</td>
                <td class="note-number">{{ $index + 6 }}</td>
                @foreach($comparisonYears as $year)
                <td class="amount">
                    {{ number_format($liability['years'][$year] ?? 0, 2) }}
                </td>
                @endforeach
                <td class="variance">
                    @php
                        $variance = 0;
                        if(isset($liability['years'][$comparisonYears[1]]) && $liability['years'][$comparisonYears[1]] != 0) {
                            $variance = (($liability['years'][$comparisonYears[0]] ?? 0) - $liability['years'][$comparisonYears[1]]) / abs($liability['years'][$comparisonYears[1]]) * 100;
                        }
                    @endphp
                    <span class="{{ $variance >= 0 ? 'variance-positive' : 'variance-negative' }}">
                        {{ number_format($variance, 1) }}%
                    </span>
                </td>
            </tr>
            @endforeach
            @endif

            <!-- Non-Current Liabilities -->
            @if(count($liabilitiesData['non_current'] ?? []) > 0)
            <tr class="section-header">
                <td colspan="{{ 3 + count($comparisonYears) }}">NON-CURRENT LIABILITIES</td>
            </tr>
            @foreach($liabilitiesData['non_current'] as $index => $liability)
            <tr>
                <td class="category-header">{{ $liability['account_name'] }}</td>
                <td class="note-number">{{ count($liabilitiesData['current']) + $index + 6 }}</td>
                @foreach($comparisonYears as $year)
                <td class="amount">
                    {{ number_format($liability['years'][$year] ?? 0, 2) }}
                </td>
                @endforeach
                <td class="variance">
                    @php
                        $variance = 0;
                        if(isset($liability['years'][$comparisonYears[1]]) && $liability['years'][$comparisonYears[1]] != 0) {
                            $variance = (($liability['years'][$comparisonYears[0]] ?? 0) - $liability['years'][$comparisonYears[1]]) / abs($liability['years'][$comparisonYears[1]]) * 100;
                        }
                    @endphp
                    <span class="{{ $variance >= 0 ? 'variance-positive' : 'variance-negative' }}">
                        {{ number_format($variance, 1) }}%
                    </span>
                </td>
            </tr>
            @endforeach
            @endif

            <!-- Total Liabilities -->
            <tr class="subtotal-row">
                <td>TOTAL LIABILITIES</td>
                <td></td>
                @foreach($comparisonYears as $year)
                <td class="amount">
                    {{ number_format($liabilitiesData['total'][$year] ?? 0, 2) }}
                </td>
                @endforeach
                <td class="variance">
                    @php
                        $variance = 0;
                        if(isset($liabilitiesData['total'][$comparisonYears[1]]) && $liabilitiesData['total'][$comparisonYears[1]] != 0) {
                            $variance = (($liabilitiesData['total'][$comparisonYears[0]] ?? 0) - $liabilitiesData['total'][$comparisonYears[1]]) / abs($liabilitiesData['total'][$comparisonYears[1]]) * 100;
                        }
                    @endphp
                    {{ number_format($variance, 1) }}%
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Total Equity and Liabilities -->
    <table>
        <tbody>
            <tr class="total-row">
                <td style="width: 40%;">TOTAL EQUITY AND LIABILITIES</td>
                <td style="width: 10%;"></td>
                @foreach($comparisonYears as $year)
                <td style="width: 20%;" class="amount">
                    {{ number_format($summaryData[$year]['total_liabilities_equity'] ?? 0, 2) }}
                </td>
                @endforeach
                <td style="width: 10%;" class="variance">
                    @php
                        $variance = 0;
                        if(isset($summaryData[$comparisonYears[1]]['total_liabilities_equity']) && $summaryData[$comparisonYears[1]]['total_liabilities_equity'] != 0) {
                            $variance = (($summaryData[$comparisonYears[0]]['total_liabilities_equity'] ?? 0) - $summaryData[$comparisonYears[1]]['total_liabilities_equity']) / abs($summaryData[$comparisonYears[1]]['total_liabilities_equity']) * 100;
                        }
                    @endphp
                    {{ number_format($variance, 1) }}%
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ date('d F Y H:i:s') }}</p>
        <p>{{ $companyName }} - Statement of Financial Position</p>
    </div>
</body>
</html>