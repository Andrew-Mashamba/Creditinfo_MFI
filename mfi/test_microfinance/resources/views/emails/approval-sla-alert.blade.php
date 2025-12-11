<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Approval SLA Alert</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        .header-warning {
            background-color: #ffc107;
            color: #333;
        }
        .header-breach {
            background-color: #dc3545;
        }
        .header-escalation {
            background-color: #6f42c1;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            margin-top: 10px;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .alert-box {
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
        }
        .alert-breach {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .alert-escalation {
            background-color: #e7d8f6;
            border: 1px solid #6f42c1;
            color: #4a2c7a;
        }
        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .summary-stats {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            text-align: center;
        }
        .stat-item {
            padding: 10px 15px;
        }
        .stat-value {
            font-size: 28px;
            font-weight: bold;
        }
        .stat-value-warning {
            color: #ffc107;
        }
        .stat-value-breach {
            color: #dc3545;
        }
        .stat-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
        }
        .approval-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 13px;
        }
        .approval-table th {
            background-color: #343a40;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
        }
        .approval-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
        }
        .approval-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-warning {
            background-color: #ffc107;
            color: #333;
        }
        .status-breach {
            background-color: #dc3545;
            color: white;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
        }
        .progress-ok {
            background-color: #28a745;
        }
        .progress-warning {
            background-color: #ffc107;
        }
        .progress-breach {
            background-color: #dc3545;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
        .action-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .time-elapsed {
            font-family: monospace;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header header-{{ $alertType }}">
            <h1>
                @if($alertType === 'warning')
                    SLA Warning Alert
                @elseif($alertType === 'breach')
                    SLA Breach Alert
                @else
                    SLA Escalation Alert
                @endif
            </h1>
            <div class="subtitle">Generated: {{ $reportTime }}</div>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $recipientName }}</strong>,</p>

            @if($alertType === 'warning')
            <div class="alert-box alert-warning">
                <strong>Warning:</strong> The following approval request(s) are approaching their SLA deadline. Please take action to avoid SLA breach.
            </div>
            @elseif($alertType === 'breach')
            <div class="alert-box alert-breach">
                <strong>Urgent Action Required:</strong> The following approval request(s) have exceeded their SLA deadline. Immediate action is required.
            </div>
            @else
            <div class="alert-box alert-escalation">
                <strong>Escalation Notice:</strong> The following approval request(s) have been escalated due to continued SLA breach. Management attention is required.
            </div>
            @endif

            <div class="summary-box">
                <h3 style="margin-top: 0;">Summary</h3>
                <div class="summary-stats">
                    <div class="stat-item">
                        <div class="stat-value">{{ $summary['total'] }}</div>
                        <div class="stat-label">Total Approvals</div>
                    </div>
                    @if($summary['warnings'] > 0)
                    <div class="stat-item">
                        <div class="stat-value stat-value-warning">{{ $summary['warnings'] }}</div>
                        <div class="stat-label">At Warning</div>
                    </div>
                    @endif
                    @if($summary['breached'] > 0)
                    <div class="stat-item">
                        <div class="stat-value stat-value-breach">{{ $summary['breached'] }}</div>
                        <div class="stat-label">Breached</div>
                    </div>
                    @endif
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($summary['oldest_hours'], 1) }}h</div>
                        <div class="stat-label">Oldest Wait Time</div>
                    </div>
                </div>
            </div>

            <h3>Approval Details</h3>
            <table class="approval-table">
                <thead>
                    <tr>
                        <th>Process</th>
                        <th>Description</th>
                        <th>Current Level</th>
                        <th>Time Waiting</th>
                        <th>SLA Limit</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($approvals as $approval)
                    @php
                        $hoursWaiting = $approval->getHoursAtCurrentLevel();
                        $slaLimit = $approval->getSlaHoursLimit() ?? 0;
                        $percentage = $approval->getSlaPercentageUsed();
                        $slaStatus = $approval->getSlaStatus();
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $approval->process_name }}</strong><br>
                            <small style="color: #6c757d;">{{ $approval->process_code }}</small>
                        </td>
                        <td>{{ Str::limit($approval->process_description, 50) }}</td>
                        <td>{{ $approval->getCurrentLevelName() }}</td>
                        <td>
                            <span class="time-elapsed">{{ number_format($hoursWaiting, 1) }}h</span>
                            <div class="progress-bar" style="margin-top: 5px;">
                                <div class="progress-fill progress-{{ $slaStatus }}" style="width: {{ min($percentage, 100) }}%;"></div>
                            </div>
                        </td>
                        <td>{{ $slaLimit }}h</td>
                        <td>
                            <span class="status-badge status-{{ $slaStatus }}">
                                {{ strtoupper($slaStatus) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($recipientType === 'checker')
            <p><strong>Action Required:</strong> Please review and process the pending approval(s) listed above as soon as possible.</p>
            @elseif($recipientType === 'supervisor')
            <p><strong>Supervisor Notice:</strong> Your team member has pending approval(s) that require attention. Please follow up to ensure timely processing.</p>
            @else
            <p><strong>Management Action:</strong> These approval(s) have been escalated and require management intervention to resolve the SLA breaches.</p>
            @endif

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/approvals" class="action-btn">View All Pending Approvals</a>
            </div>

            <p style="margin-top: 30px;">Best regards,<br>
            <strong>MFI Management System System</strong></p>
        </div>

        <div class="footer">
            <p>This is an automated SLA monitoring alert from the MFI Management System approval system.</p>
            <p>&copy; {{ date('Y') }} NBC Bank. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
