<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Details Report</title>
    <style nonce="{{ csp_nonce() }}">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            background: #fff;
        }
        
        .header {
            text-align: center;
            padding: 15px 0;
            border-bottom: 2px solid #1e40af;
            margin-bottom: 20px;
        }
        
        .institution-name {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }
        
        .report-info {
            font-size: 10px;
            color: #6b7280;
        }
        
        .summary-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .summary-title {
            background: #f8fafc;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
            border-left: 4px solid #2563eb;
            margin-bottom: 10px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-cell {
            display: table-cell;
            width: 25%;
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
            background: #f9fafb;
        }
        
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
        }
        
        .summary-label {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
        }
        
        .filters-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .filters-title {
            background: #f0f9ff;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: bold;
            color: #0369a1;
            border-left: 4px solid #0ea5e9;
            margin-bottom: 8px;
        }
        
        .filters-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .filters-row {
            display: table-row;
        }
        
        .filters-cell {
            display: table-cell;
            width: 50%;
            padding: 4px 8px;
            border: 1px solid #e5e7eb;
            font-size: 9px;
        }
        
        .filters-label {
            font-weight: bold;
            color: #374151;
        }
        
        .filters-value {
            color: #6b7280;
        }
        
        .table-section {
            margin-bottom: 20px;
        }
        
        .table-title {
            background: #f8fafc;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
            border-left: 4px solid #2563eb;
            margin-bottom: 10px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        
        .data-table th {
            background: #f3f4f6;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            color: #374151;
            border: 1px solid #d1d5db;
            font-size: 8px;
        }
        
        .data-table td {
            padding: 4px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }
        
        .data-table tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .data-table tr:hover {
            background: #f3f4f6;
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
        
        .status-suspended {
            color: #7c2d12;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-data {
            color: #9ca3af;
            font-style: italic;
        }
        
        .currency {
            text-align: right;
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
        <div class="institution-name">Financial Institution</div>
        <div class="report-title">Member Details Report</div>
        <div class="report-info">
            Generated on: {{ $reportDate }} | Generated by: {{ $generatedBy }}
        </div>
    </div>

    {{-- Summary Section --}}
    <div class="summary-section">
        <div class="summary-title">Report Summary</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-value">{{ $summary['totalMembers'] }}</div>
                    <div class="summary-label">Total Members</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ $summary['activeMembers'] }}</div>
                    <div class="summary-label">Active Members</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ $summary['pendingMembers'] }}</div>
                    <div class="summary-label">Pending Members</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ $summary['inactiveMembers'] }}</div>
                    <div class="summary-label">Inactive Members</div>
                </div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-value">{{ $summary['totalBranches'] }}</div>
                    <div class="summary-label">Total Branches</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($summary['totalSavings'], 2) }}</div>
                    <div class="summary-label">Total Savings (TZS)</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ $summary['membersWithLoans'] }}</div>
                    <div class="summary-label">Members with Loans</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ count($members) }}</div>
                    <div class="summary-label">Records in Report</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="filters-section">
        <div class="filters-title">Report Filters Applied</div>
        <div class="filters-grid">
            <div class="filters-row">
                <div class="filters-cell">
                    <span class="filters-label">Member Selection:</span>
                    <span class="filters-value">{{ $filters['client_type'] === 'ALL' ? 'All Members' : 'Selected Members' }}</span>
                </div>
                <div class="filters-cell">
                    <span class="filters-label">Branch Filter:</span>
                    <span class="filters-value">{{ $filters['branch_filter'] ?: 'All Branches' }}</span>
                </div>
            </div>
            <div class="filters-row">
                <div class="filters-cell">
                    <span class="filters-label">Status Filter:</span>
                    <span class="filters-value">{{ $filters['status_filter'] ?: 'All Statuses' }}</span>
                </div>
                <div class="filters-cell">
                    <span class="filters-label">Custom Numbers:</span>
                    <span class="filters-value">{{ $filters['custom_numbers'] ?: 'None' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Members Table --}}
    <div class="table-section">
        <div class="table-title">Member Details ({{ count($members) }} records)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 8%;">Member #</th>
                    <th style="width: 15%;">Full Name</th>
                    <th style="width: 8%;">NIDA</th>
                    <th style="width: 6%;">Gender</th>
                    <th style="width: 10%;">Phone</th>
                    <th style="width: 12%;">Email</th>
                    <th style="width: 8%;">Branch</th>
                    <th style="width: 6%;">Status</th>
                    <th style="width: 8%;">Reg. Date</th>
                    <th style="width: 10%;">Savings Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $index => $member)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $member->client_number ?? 'N/A' }}</td>
                        <td>
                            <div style="font-weight: bold;">{{ $member->full_name ?? 'N/A' }}</div>
                            @if($member->nida_number)
                                <div style="font-size: 7px; color: #6b7280;">NIDA: {{ $member->nida_number }}</div>
                            @endif
                        </td>
                        <td>{{ $member->nida_number ?? 'N/A' }}</td>
                        <td>{{ $member->gender ?? 'N/A' }}</td>
                        <td>
                            <div>{{ $member->phone_number ?? 'N/A' }}</div>
                            @if($member->mobile_phone_number && $member->mobile_phone_number !== $member->phone_number)
                                <div style="font-size: 7px; color: #6b7280;">{{ $member->mobile_phone_number }}</div>
                            @endif
                        </td>
                        <td>{{ $member->email ?? 'N/A' }}</td>
                        <td>{{ $member->branch_name ?? 'N/A' }}</td>
                        <td>
                            <span class="status-{{ strtolower($member->status ?? 'unknown') }}">
                                {{ $member->status ?? 'N/A' }}
                            </span>
                        </td>
                        <td>{{ $member->registration_date ?? 'N/A' }}</td>
                        <td class="currency">{{ number_format($member->savings_balance ?? 0, 2) }} TZS</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 20px; color: #9ca3af;">
                            No members found for the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Additional Information --}}
    @if(count($members) > 0)
        <div class="summary-section">
            <div class="summary-title">Report Totals</div>
            <div class="summary-grid">
                <div class="summary-row">
                    <div class="summary-cell">
                        <div class="summary-value">{{ count($members) }}</div>
                        <div class="summary-label">Records in Report</div>
                    </div>
                    <div class="summary-cell">
                        <div class="summary-value">{{ number_format($members->sum('savings_balance'), 2) }}</div>
                        <div class="summary-label">Total Savings (TZS)</div>
                    </div>
                    <div class="summary-cell">
                        <div class="summary-value">{{ $members->where('status', 'ACTIVE')->count() }}</div>
                        <div class="summary-label">Active in Report</div>
                    </div>
                    <div class="summary-cell">
                        <div class="summary-value">{{ $members->where('status', 'PENDING')->count() }}</div>
                        <div class="summary-label">Pending in Report</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Financial Institution system.</p>
        <p>For any queries, please contact the administration office.</p>
        <p>Report generated on {{ $reportDate }} | Page 1 of 1</p>
    </div>
</body>
</html>
