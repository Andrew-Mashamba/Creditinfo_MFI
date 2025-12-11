<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Details - {{ $member->first_name }} {{ $member->last_name }}</title>
    <style nonce="{{ csp_nonce() }}">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }
        
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #2563eb;
            margin-bottom: 30px;
        }
        
        .institution-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }
        
        .member-info {
            font-size: 14px;
            color: #6b7280;
        }
        
        .generated-info {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 10px;
        }
        
        .member-photo {
            float: right;
            width: 80px;
            height: 80px;
            border: 2px solid #e5e7eb;
            border-radius: 50%;
            margin-left: 20px;
            margin-bottom: 20px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #9ca3af;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background: #f8fafc;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            border-left: 4px solid #2563eb;
            margin-bottom: 15px;
            border-radius: 0 4px 4px 0;
        }
        
        .data-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-row {
            display: table-row;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .data-row:nth-child(even) {
            background: #f9fafb;
        }
        
        .data-label {
            display: table-cell;
            width: 35%;
            padding: 8px 12px;
            font-weight: 600;
            color: #374151;
            border-right: 1px solid #e5e7eb;
        }
        
        .data-value {
            display: table-cell;
            width: 65%;
            padding: 8px 12px;
            color: #1f2937;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-data {
            color: #9ca3af;
            font-style: italic;
        }
        
        .status-active {
            color: #059669;
            font-weight: bold;
        }
        
        .status-inactive {
            color: #dc2626;
            font-weight: bold;
        }
        
        .status-pending {
            color: #d97706;
            font-weight: bold;
        }
        
        @media print {
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="institution-name">{{ $institution }}</div>
        <div class="report-title">Member Details Report</div>
        <div class="member-info">{{ $member->first_name }} {{ $member->last_name }} - {{ $member->client_number }}</div>
        <div class="generated-info">Generated on: {{ $generatedAt }}</div>
    </div>

    @if($member->profile_photo)
        <div class="member-photo">
            <img src="{{ storage_path('app/public/' . $member->profile_photo) }}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
        </div>
    @else
        <div class="member-photo">
            {{ strtoupper(substr($member->first_name,0,1).substr($member->last_name,0,1)) }}
        </div>
    @endif

    @foreach($memberData as $categoryName => $categoryData)
        <div class="section">
            <div class="section-title">{{ $categoryName }}</div>
            <div class="data-grid">
                @foreach($categoryData as $field => $value)
                    <div class="data-row">
                        <div class="data-label">{{ ucfirst(str_replace('_', ' ', $field)) }}</div>
                        <div class="data-value">
                            @if($field === 'status')
                                <span class="status-{{ strtolower($value) }}">{{ $value }}</span>
                            @elseif($field === 'date_of_birth')
                                {{ \Carbon\Carbon::parse($value)->format('d/m/Y') }}
                            @elseif($field === 'monthly_income' || $field === 'annual_income' || $field === 'basic_salary' || $field === 'gross_salary')
                                {{ number_format($value, 2) }} TZS
                            @elseif(is_array($value))
                                {{ json_encode($value) }}
                            @elseif($value === null || $value === '')
                                <span class="no-data">Not provided</span>
                            @else
                                {{ $value }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @if($member->accounts && count($member->accounts) > 0)
        <div class="section page-break">
            <div class="section-title">Account Information</div>
            <div class="data-grid">
                @foreach($member->accounts as $account)
                    <div class="data-row">
                        <div class="data-label">Account Number</div>
                        <div class="data-value">{{ $account->account_number }}</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Account Name</div>
                        <div class="data-value">{{ $account->account_name }}</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Balance</div>
                        <div class="data-value">{{ number_format($account->balance, 2) }} TZS</div>
                    </div>
                    @if($account->locked_amount > 0)
                        <div class="data-row">
                            <div class="data-label">Locked Amount</div>
                            <div class="data-value">{{ number_format($account->locked_amount, 2) }} TZS</div>
                        </div>
                    @endif
                    <div class="data-row" style="height: 20px; background: #f3f4f6;"></div>
                @endforeach
            </div>
        </div>
    @endif

    @if($member->loans && count($member->loans) > 0)
        <div class="section page-break">
            <div class="section-title">Loan Information</div>
            <div class="data-grid">
                @foreach($member->loans as $loan)
                    <div class="data-row">
                        <div class="data-label">Loan Number</div>
                        <div class="data-value">{{ $loan->loan_number }}</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Loan Type</div>
                        <div class="data-value">{{ $loan->loan_type }}</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Amount</div>
                        <div class="data-value">{{ number_format($loan->amount, 2) }} TZS</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Balance</div>
                        <div class="data-value">{{ number_format($loan->balance, 2) }} TZS</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Status</div>
                        <div class="data-value">
                            <span class="status-{{ strtolower($loan->status) }}">{{ $loan->status }}</span>
                        </div>
                    </div>
                    <div class="data-row" style="height: 20px; background: #f3f4f6;"></div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the {{ $institution }} system.</p>
        <p>For any queries, please contact the administration office.</p>
        <p>Page 1 of 1</p>
    </div>
</body>
</html> 