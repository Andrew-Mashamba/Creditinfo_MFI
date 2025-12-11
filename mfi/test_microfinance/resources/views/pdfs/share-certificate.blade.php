<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Share Certificate</title>
    <style nonce="{{ csp_nonce() }}">
        @page {
            margin: 15px;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .certificate-container {
            border: 8px double #2563eb;
            padding: 15px;
            margin: 5px;
            background: #fff;
        }

        .certificate-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }

        .certificate-title {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .certificate-subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 3px;
        }

        .certificate-body {
            margin: 10px 0;
            padding: 10px;
        }

        .member-info {
            text-align: center;
            margin: 12px 0;
            font-size: 13px;
        }

        .member-name {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
            margin: 8px 0;
            text-transform: uppercase;
        }

        .certificate-text {
            font-size: 13px;
            text-align: center;
            margin: 10px 0;
            line-height: 1.4;
        }

        .shares-table {
            width: 100%;
            margin: 12px 0;
            border-collapse: collapse;
        }

        .shares-table th {
            background-color: #2563eb;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        .shares-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }

        .shares-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .total-row {
            font-weight: bold;
            background-color: #dbeafe !important;
            border-top: 2px solid #2563eb;
        }

        .certificate-footer {
            margin-top: 20px;
            display: table;
            width: 100%;
        }

        .signature-section {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 8px;
        }

        .signature-line {
            border-top: 1.5px solid #333;
            margin-top: 30px;
            padding-top: 6px;
            font-weight: bold;
            font-size: 11px;
        }

        .certificate-id {
            text-align: center;
            margin-top: 12px;
            font-size: 10px;
            color: #666;
        }

        .issue-date {
            text-align: center;
            margin-top: 8px;
            font-size: 12px;
            font-weight: bold;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(37, 99, 235, 0.04);
            z-index: -1;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="watermark">SHARE CERTIFICATE</div>

    <div class="certificate-container">
        <div class="certificate-header">
            <div style="font-size: 16px; font-weight: bold; color: #1e40af; margin-bottom: 8px; text-transform: uppercase;">
                {{ $data['institution']['name'] }}
            </div>
            @if($data['institution']['address'])
            <div style="font-size: 10px; color: #666; margin-bottom: 5px;">
                {{ $data['institution']['address'] }}
            </div>
            @endif
            @if($data['institution']['phone'] || $data['institution']['email'])
            <div style="font-size: 10px; color: #666; margin-bottom: 10px;">
                @if($data['institution']['phone'])
                    Tel: {{ $data['institution']['phone'] }}
                @endif
                @if($data['institution']['phone'] && $data['institution']['email'])
                    |
                @endif
                @if($data['institution']['email'])
                    Email: {{ $data['institution']['email'] }}
                @endif
            </div>
            @endif
            <div class="certificate-title">Share Certificate</div>
            <div class="certificate-subtitle">This is to Certify That</div>
        </div>

        <div class="certificate-body">
            <div class="member-info">
                <div class="member-name">{{ $data['member']['full_name'] }}</div>
                <div>Client Number: <strong>{{ $data['member']['client_number'] }}</strong></div>
            </div>

            <div class="certificate-text">
                is the registered holder of the following shares:
            </div>

            <table class="shares-table">
                <thead>
                    <tr>
                        <th>Share Account Number</th>
                        <th>Product Name</th>
                        <th style="text-align: right;">Number of Shares</th>
                        <th style="text-align: right;">Nominal Price (TZS)</th>
                        <th style="text-align: right;">Total Value (TZS)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['shares'] as $share)
                    <tr>
                        <td>{{ $share['share_account_number'] }}</td>
                        <td>{{ $share['product_name'] }}</td>
                        <td style="text-align: right;">{{ number_format($share['shares']) }}</td>
                        <td style="text-align: right;">{{ number_format($share['nominal_price'], 2) }}</td>
                        <td style="text-align: right;">{{ number_format($share['value'], 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2"><strong>TOTAL</strong></td>
                        <td style="text-align: right;"><strong>{{ number_format($data['total_shares']) }}</strong></td>
                        <td></td>
                        <td style="text-align: right;"><strong>{{ number_format($data['total_value'], 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>

            <div class="certificate-text">
                Subject to the terms and conditions of the organization's constitution and regulations.
            </div>
        </div>

        <div class="certificate-footer">
            <div class="signature-section">
                <div class="signature-line">
                    Chairman/CEO
                </div>
            </div>
            <div class="signature-section">
                <div class="signature-line">
                    Secretary
                </div>
            </div>
        </div>

        <div class="issue-date">
            Issue Date: {{ $data['issue_date'] }}
        </div>

        <div class="certificate-id">
            Certificate ID: {{ $data['certificate_id'] }}
        </div>
    </div>
</body>
</html>
