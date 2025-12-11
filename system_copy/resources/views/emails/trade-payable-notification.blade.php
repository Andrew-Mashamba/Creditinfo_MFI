<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Notification - {{ $bill_number }}</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 20px 0;
        }
        .bill-details {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .bill-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .bill-details td {
            padding: 8px 0;
            vertical-align: top;
        }
        .bill-details td:first-child {
            font-weight: 600;
            color: #666;
            width: 40%;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
            text-align: center;
            padding: 15px;
            background-color: #f0f3ff;
            border-radius: 8px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: 600;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 20px 0;
            border-radius: 4px;
            color: #856404;
        }
        .info {
            background-color: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 10px;
            margin: 20px 0;
            border-radius: 4px;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $institution_name }}</h1>
            <p style="margin: 5px 0; opacity: 0.9;">Payment Commitment Notification</p>
        </div>
        
        <div class="content">
            <p>Dear <strong>{{ $vendor_name }}</strong>,</p>
            
            <p>We hope this email finds you well. This is to confirm that we have received and acknowledged your invoice, and we are committed to making payment as detailed below.</p>
            
            <div class="bill-details">
                <table>
                    <tr>
                        <td>Our Reference Number:</td>
                        <td><strong>{{ $bill_number }}</strong></td>
                    </tr>
                    <tr>
                        <td>Services/Goods Received:</td>
                        <td>{{ $description ?? 'Services/Goods Provided' }}</td>
                    </tr>
                    <tr>
                        <td>Payment Scheduled Date:</td>
                        <td><strong style="color: #28a745;">{{ $due_date }}</strong></td>
                    </tr>
                </table>
            </div>
            
            <div class="amount">
                Committed Payment Amount: {{ $currency ?? 'TZS' }} {{ $amount }}
            </div>
            
            @if(isset($payable_type))
                @if($payable_type == 'installment')
                    <div class="info">
                        <strong>Payment Type:</strong> Installment Payment<br>
                        @if(isset($installment_details))
                            Installment {{ $installment_details['current'] }} of {{ $installment_details['total'] }}
                        @endif
                    </div>
                @elseif($payable_type == 'subscription')
                    <div class="info">
                        <strong>Payment Type:</strong> Recurring Subscription<br>
                        Frequency: {{ ucfirst($recurring_frequency ?? 'Monthly') }}
                    </div>
                @endif
            @endif
            
            <div class="info">
                <strong>Payment Commitment:</strong> We confirm that payment will be processed on or before the scheduled date mentioned above. This serves as our formal commitment to settle the outstanding amount.
            </div>
            
            <h3 style="color: #333; margin-top: 30px;">Our Payment Will Be Made From:</h3>
            <div class="bill-details">
                <table>
                    <tr>
                        <td>Organization:</td>
                        <td>{{ $institution_name }}</td>
                    </tr>
                    <tr>
                        <td>Payment Reference:</td>
                        <td><strong>{{ $bill_number }}</strong></td>
                    </tr>
                    <tr>
                        <td>Payment Method:</td>
                        <td>{{ $payment_method ?? 'Bank Transfer' }}</td>
                    </tr>
                </table>
            </div>
            
            <p style="margin-top: 30px;">This notification serves as confirmation that we have acknowledged receipt of your invoice and have scheduled it for payment. You will receive payment on or before the scheduled date.</p>
            
            <p>If you have any questions regarding this payment commitment, please don't hesitate to contact our accounts payable department.</p>
            
            <p>Thank you for your services and continued partnership.</p>
            
            <p style="margin-top: 30px;">
                Best regards,<br>
                <strong>Accounts Department</strong><br>
                {{ $institution_name }}
            </p>
        </div>
        
        <div class="footer">
            <p>This is an automated notification from {{ $institution_name }}.</p>
            <p>Please do not reply to this email. For inquiries, contact our support team.</p>
            @if(isset($institution_email))
                <p>Email: {{ $institution_email }} | Phone: {{ $institution_phone ?? '' }}</p>
            @endif
            <p style="margin-top: 15px; color: #999;">© {{ date('Y') }} {{ $institution_name }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>