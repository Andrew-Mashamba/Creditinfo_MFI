<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Invoice</title>
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
        .btn-success {
            background: #28a745;
        }
        .btn-primary {
            background: #007bff;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .invoice-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            background: #e3f2fd;
            color: #1976d2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $institution->name ?? 'MFI' }}</h1>
        <p style="margin: 0;">New Invoice Generated</p>
    </div>

    <div class="content">
        <p>Dear {{ $customerName }},</p>

        <div class="invoice-badge">
            New Invoice
        </div>

        <div class="alert alert-info">
            <strong>A new invoice has been generated for your account.</strong>
        </div>

        <p>Please find the details of your invoice below:</p>

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
                    <td>Description:</td>
                    <td>{{ $invoice->description ?? 'Invoice Payment' }}</td>
                </tr>
                <tr>
                    <td>Amount Due:</td>
                    <td style="font-size: 18px; color: #28a745;">
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

        <div style="text-align: center; margin: 30px 0;">
            @if(isset($paymentUrl) && $paymentUrl)
                <a href="{{ $paymentUrl }}" class="btn btn-success">Pay Now Online</a>
            @endif
        </div>

        <p>
            The invoice PDF is attached to this email for your records. 
            Please keep it for your reference and make payment by the due date to avoid any late fees.
        </p>

        <p>
            If you have any questions about this invoice, please contact us immediately.
            We appreciate your business and prompt payment.
        </p>

        <p>Thank you for choosing our services.</p>

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
            This is an automated notification from {{ $institution->name ?? 'MFI' }}.<br>
            Please do not reply to this email. For assistance, contact our support team.<br>
            © {{ date('Y') }} {{ $institution->name ?? 'MFI' }}. All rights reserved.
        </p>
    </div>
</body>
</html>