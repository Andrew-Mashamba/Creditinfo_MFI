<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        .payment-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .payment-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .payment-details td {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .payment-details td:first-child {
            font-weight: bold;
            color: #666;
            width: 40%;
        }
        .success-badge {
            display: inline-block;
            background: #d4edda;
            color: #155724;
            padding: 10px 20px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
            margin: 20px 0;
        }
        .amount-paid {
            font-size: 24px;
            color: #28a745;
            font-weight: bold;
        }
        .balance-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .balance-zero {
            background: #d4edda;
            border: 1px solid #28a745;
            color: #155724;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $institution->name ?? 'MFI' }}</h1>
        <p style="margin: 0;">Payment Confirmation</p>
    </div>

    <div class="content">
        <p>Dear {{ $customerName }},</p>

        <div class="success-badge">
            ✅ Payment Successfully Received
        </div>

        <p>We confirm receipt of your payment for Invoice <strong>{{ $receivable->invoice_number }}</strong>.</p>

        <div class="payment-details">
            <table>
                <tr>
                    <td>Invoice Number:</td>
                    <td><strong>{{ $receivable->invoice_number }}</strong></td>
                </tr>
                <tr>
                    <td>Payment Date:</td>
                    <td>{{ \Carbon\Carbon::parse($paymentDate)->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td>Payment Method:</td>
                    <td>{{ ucwords(str_replace('_', ' ', $paymentMethod)) }}</td>
                </tr>
                @if($referenceNumber)
                <tr>
                    <td>Reference Number:</td>
                    <td><strong>{{ $referenceNumber }}</strong></td>
                </tr>
                @endif
                <tr>
                    <td>Amount Paid:</td>
                    <td class="amount-paid">
                        {{ $receivable->currency ?? 'TZS' }} {{ number_format($paymentAmount, 2) }}
                    </td>
                </tr>
                <tr>
                    <td>Original Invoice Amount:</td>
                    <td>{{ $receivable->currency ?? 'TZS' }} {{ number_format($receivable->amount, 2) }}</td>
                </tr>
                <tr>
                    <td>New Balance:</td>
                    <td style="font-size: 18px; {{ $newBalance <= 0 ? 'color: #28a745;' : 'color: #dc3545;' }}">
                        <strong>{{ $receivable->currency ?? 'TZS' }} {{ number_format($newBalance, 2) }}</strong>
                    </td>
                </tr>
            </table>
        </div>

        @if($newBalance > 0)
            <div class="balance-info">
                <strong>Outstanding Balance:</strong><br>
                You still have an outstanding balance of <strong>{{ $receivable->currency ?? 'TZS' }} {{ number_format($newBalance, 2) }}</strong> on this invoice.
                Please ensure the remaining amount is paid by the due date to avoid any late fees.
            </div>
        @else
            <div class="balance-info balance-zero">
                <strong>✅ Invoice Fully Paid</strong><br>
                This invoice has been fully paid. Thank you for your prompt payment!
            </div>
        @endif

        <p>
            This payment has been recorded in our system and your account has been updated accordingly.
            Please keep this email for your records.
        </p>

        <p>
            If you have any questions about this payment or your account, please don't hesitate to contact us.
        </p>

        <p>
            Thank you for your business!
        </p>

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
            This is an automated payment confirmation from {{ $institution->name ?? 'MFI' }}.<br>
            Transaction Reference: PAY-{{ str_pad($receivable->id, 6, '0', STR_PAD_LEFT) }}<br>
            © {{ date('Y') }} {{ $institution->name ?? 'MFI' }}. All rights reserved.
        </p>
    </div>
</body>
</html>