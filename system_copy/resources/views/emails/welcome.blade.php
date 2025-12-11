<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to NBC SACCOS</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #003366;
            padding: 20px;
            text-align: center;
        }
        .header img {
            max-width: 200px;
            height: auto;
        }
        .content {
            padding: 20px;
            background-color: #ffffff;
        }
        .payment-details {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        .payment-item {
            margin: 10px 0;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            background-color: #ffffff;
        }
        .payment-item:last-child {
            border-bottom: none;
        }
        .control-number {
            font-weight: bold;
            color: #003366;
        }
        .amount {
            font-weight: bold;
            color: #28a745;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
        .contact-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .contact-info ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .contact-info li {
            margin: 5px 0;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #003366;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/nbc.png') }}" alt="NBC Bank Logo">
        </div>
        
        <div class="content">
            <h2 style="color: #003366;">Welcome to NBC SACCOS!</h2>
            
            <p>Dear {{ $name }},</p>
            
            <p>We are delighted to welcome you to NBC SACCOS, your trusted financial partner. Thank you for choosing us for your financial needs.</p>
            
            <div class="payment-details">
                <h3 style="color: #003366;">Payment Details</h3>
                @foreach($controlNumbers as $control)
                <div class="payment-item">
                    <p><strong>Service:</strong> {{ $control['service_name'] }} ({{ $control['service_code'] }})</p>
                    <p><strong>Control Number:</strong> <span class="control-number">{{ $control['control_number'] }}</span></p>
                    <p><strong>Amount to Pay:</strong> <span class="amount">TZS {{ number_format($control['amount'], 2) }}</span></p>
                </div>
                @endforeach
            </div>
            
            <p>To complete your registration, please make your payment using the control numbers above at any NBC Bank branch or through our digital banking channels.</p>
            
            @if($paymentLink)
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $paymentLink }}" class="button" style="display: inline-block; padding: 12px 24px; background-color: #003366; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    Pay with Mobile Phone
                </a>
            </div>
            @endif
            
            <div class="contact-info">
                <h4 style="color: #003366;">Need Assistance?</h4>
                <p>Our dedicated support team is here to help you:</p>
                <ul>
                    <li>📞 Phone: +255 22 219 7000</li>
                    <li>📧 Email: support@nbcsaccos.co.tz</li>
                    <li>🏦 Visit any NBC Bank branch</li>
                </ul>
            </div>
            
            <p>Thank you for choosing NBC SACCOS as your financial partner. We look forward to serving you!</p>
            
            <p>Best regards,<br>
            <strong>NBC SACCOS Team</strong></p>
        </div>
        
        <div class="footer">
            <p>This email is confidential and intended for the recipient specified in the message only. It is strictly forbidden to share any part of this message with any third party, without a written consent of the sender.</p>
            <p>© {{ date('Y') }} NBC Bank. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 