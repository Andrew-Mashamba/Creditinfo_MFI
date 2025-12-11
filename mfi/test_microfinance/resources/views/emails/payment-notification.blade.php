<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Notification</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .urgent {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .high {
            background-color: #ffc107;
            color: #333;
            padding: 10px 20px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #6c757d;
        }
        table {
            width: 100%;
            margin: 20px 0;
        }
        td {
            padding: 8px 0;
        }
        .label {
            font-weight: 600;
            color: #495057;
            width: 40%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0; color: #007bff;">MFI Payment Notification</h1>
        </div>

        @if($urgency === 'urgent')
            <div class="urgent">URGENT ACTION REQUIRED</div>
        @elseif($urgency === 'high')
            <div class="high">HIGH PRIORITY</div>
        @endif

        @if($type === 'upcoming')
            <h2>Upcoming Payment Due</h2>
            <p>This is a reminder that you have an upcoming payment due to the following vendor:</p>
        @elseif($type === 'overdue')
            <h2 style="color: #dc3545;">OVERDUE Payment Notice</h2>
            <p><strong>This payment is now {{ abs($daysUntilDue) }} days overdue.</strong> Please arrange for immediate payment to avoid service disruption.</p>
        @elseif($type === 'upcoming_receivable')
            <h2>Expected Payment from Customer</h2>
            <p>A payment is expected from a customer within the next {{ $daysUntilDue }} days:</p>
        @elseif($type === 'overdue_receivable')
            <h2 style="color: #dc3545;">OVERDUE Receivable</h2>
            <p><strong>This receivable is {{ abs($daysUntilDue) }} days overdue.</strong> Follow-up action may be required.</p>
        @endif

        <div class="info-box">
            <table>
                <tr>
                    <td class="label">{{ in_array($type, ['upcoming_receivable', 'overdue_receivable']) ? 'Customer:' : 'Vendor:' }}</td>
                    <td><strong>{{ $payment->vendor_name ?? $payment->customer_name ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Bill Number:</td>
                    <td>{{ $payment->bill_number ?? $payment->invoice_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Amount Due:</td>
                    <td class="amount">{{ number_format($payment->balance ?? $payment->amount, 2) }} {{ $payment->currency ?? 'TZS' }}</td>
                </tr>
                <tr>
                    <td class="label">Due Date:</td>
                    <td><strong>{{ \Carbon\Carbon::parse($payment->due_date)->format('d M Y') }}</strong></td>
                </tr>
                @if($isOverdue)
                <tr>
                    <td class="label">Days Overdue:</td>
                    <td style="color: #dc3545; font-weight: bold;">{{ abs($daysUntilDue) }} days</td>
                </tr>
                @else
                <tr>
                    <td class="label">Days Until Due:</td>
                    <td style="color: #28a745; font-weight: bold;">{{ $daysUntilDue }} days</td>
                </tr>
                @endif
            </table>
        </div>

        @if(isset($payment->description) && $payment->description)
        <div style="margin: 20px 0;">
            <strong>Description:</strong><br>
            {{ $payment->description }}
        </div>
        @endif

        @if(in_array($type, ['upcoming', 'overdue']))
            <h3>Vendor Banking Details:</h3>
            <table>
                @if(isset($payment->vendor_bank_name) && $payment->vendor_bank_name)
                <tr>
                    <td class="label">Bank Name:</td>
                    <td>{{ $payment->vendor_bank_name }}</td>
                </tr>
                @endif
                @if(isset($payment->vendor_bank_account_number) && $payment->vendor_bank_account_number)
                <tr>
                    <td class="label">Account Number:</td>
                    <td>{{ $payment->vendor_bank_account_number }}</td>
                </tr>
                @endif
                @if(isset($payment->vendor_bank_branch) && $payment->vendor_bank_branch)
                <tr>
                    <td class="label">Branch:</td>
                    <td>{{ $payment->vendor_bank_branch }}</td>
                </tr>
                @endif
                @if(isset($payment->vendor_swift_code) && $payment->vendor_swift_code)
                <tr>
                    <td class="label">SWIFT Code:</td>
                    <td>{{ $payment->vendor_swift_code }}</td>
                </tr>
                @endif
            </table>
        @endif

        <div style="margin-top: 30px; padding: 20px; background-color: #e7f3ff; border-radius: 4px;">
            <strong>Action Required:</strong>
            @if($type === 'upcoming')
                <p>Please ensure funds are available and arrange for payment by the due date.</p>
            @elseif($type === 'overdue')
                <p style="color: #dc3545;"><strong>Immediate payment is required.</strong> Please process this payment today to avoid any penalties or service disruptions.</p>
            @elseif($type === 'upcoming_receivable')
                <p>Monitor this receivable and follow up with the customer if payment is not received by the due date.</p>
            @elseif($type === 'overdue_receivable')
                <p style="color: #dc3545;"><strong>Follow-up action required.</strong> Please contact the customer immediately to arrange payment.</p>
            @endif
        </div>

        <div class="footer">
            <p>This is an automated notification from the MFI Management System.</p>
            <p>If you have any questions, please contact the accounting department.</p>
            <p style="color: #6c757d; font-size: 11px;">
                Generated on {{ now()->format('d M Y H:i') }}<br>
                MFI Core System © {{ date('Y') }}
            </p>
        </div>
    </div>
</body>
</html>