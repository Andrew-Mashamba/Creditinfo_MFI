<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Payment Reminder</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .invoice-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details td {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .invoice-details td:first-child {
            font-weight: bold;
            color: #666;
            width: 40%;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
        }
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #dc3545;
            color: #721c24;
        }
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #17a2b8;
            color: #0c5460;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            font-weight: bold;
        }
        .btn-primary {
            background: #007bff;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-danger {
            background: #dc3545;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .reminder-type {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .pre-due {
            background: #e3f2fd;
            color: #1976d2;
        }
        .due-today {
            background: #fff3cd;
            color: #f57c00;
        }
        .overdue {
            background: #ffebee;
            color: #c62828;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $institution->name ?? 'MFI' }}</h1>
        <p style="margin: 0;">Invoice Payment Reminder</p>
    </div>

    <div class="content">
        <p>Dear {{ $customerName }},</p>

        @php
            $reminderClass = 'alert-info';
            $typeClass = 'pre-due';
            if ($reminderType == 'Due Date Reminder') {
                $reminderClass = 'alert-warning';
                $typeClass = 'due-today';
            } elseif (str_contains($reminderType, 'Overdue') || str_contains($reminderType, 'Final')) {
                $reminderClass = 'alert-danger';
                $typeClass = 'overdue';
            }
        @endphp

        <div class="reminder-type {{ $typeClass }}">
            {{ $reminderType }}
        </div>

        <div class="alert {{ $reminderClass }}">
            @if($daysOverdue > 0)
                <strong>Your invoice will be due in {{ $daysOverdue }} days.</strong>
            @elseif($daysOverdue == 0)
                <strong>Your invoice is due TODAY!</strong>
            @else
                <strong>Your invoice is {{ abs($daysOverdue) }} days OVERDUE!</strong>
            @endif
        </div>

        <p>This is a reminder regarding the following invoice:</p>

        <div class="invoice-details">
            <table>
                <tr>
                    <td>Invoice Number:</td>
                    <td><strong>{{ $invoice->invoice_number }}</strong></td>
                </tr>
                <tr>
                    <td>Invoice Date:</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td>Due Date:</td>
                    <td><strong>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</strong></td>
                </tr>
                <tr>
                    <td>Amount Due:</td>
                    <td style="font-size: 18px; color: #dc3545;">
                        <strong>{{ $invoice->currency ?? 'TZS' }} {{ number_format($invoice->balance, 2) }}</strong>
                    </td>
                </tr>
                @if(isset($invoice->control_number) && $invoice->control_number)
                <tr>
                    <td>Control Number:</td>
                    <td><strong style="color: #28a745;">{{ $invoice->control_number }}</strong></td>
                </tr>
                @endif
            </table>
        </div>

        @if($reminderType == 'Final Demand Notice')
            <div class="alert alert-danger">
                <strong>⚠️ FINAL NOTICE ⚠️</strong><br>
                This is our final reminder. Immediate payment is required to avoid further action.
            </div>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            @if(isset($paymentUrl) && $paymentUrl)
                <a href="{{ $paymentUrl }}" class="btn btn-success">Pay Now Online</a>
            @endif
            
            @if(isset($invoice->invoice_file_path) && $invoice->invoice_file_path)
                <p style="margin-top: 15px;">
                    <small>The invoice PDF is attached to this email for your reference.</small>
                </p>
            @endif
        </div>

        @if($daysOverdue < 0)
            <p style="color: #dc3545;">
                <strong>Important:</strong> To avoid additional late fees and potential service interruption, 
                please make your payment immediately.
            </p>
        @else
            <p>
                To avoid late fees, please ensure payment is made by the due date.
            </p>
        @endif

        <p>
            If you have already made this payment, please disregard this reminder. 
            If you have any questions or concerns, please contact us immediately.
        </p>

        <p>Thank you for your prompt attention to this matter.</p>

        <p>
            Best regards,<br>
            <strong>{{ $institution->name ?? 'MFI Team' }}</strong><br>
            @if(isset($institution->contact_phone))
                Phone: {{ $institution->contact_phone }}<br>
            @endif
            @if(isset($institution->email))
                Email: {{ $institution->email }}
            @endif
        </p>
    </div>

    <div class="footer">
        <p>
            This is an automated reminder from {{ $institution->name ?? 'MFI' }}.<br>
            Please do not reply to this email. For assistance, contact our support team.<br>
            © {{ date('Y') }} {{ $institution->name ?? 'MFI' }}. All rights reserved.
        </p>
    </div>
</body>
</html>