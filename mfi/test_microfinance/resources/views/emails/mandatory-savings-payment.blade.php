<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mandatory Savings Payment - {{ $month }} {{ $year }}</title>
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
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #003366;
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px 20px;
        }
        .payment-details {
            background-color: #f8f9fa;
            border: 2px solid #003366;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .control-number {
            background-color: #003366;
            color: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 15px 0;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            margin: 15px 0;
        }
        .due-date {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 10px;
            margin: 15px 0;
            text-align: center;
        }
        .payment-method {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .payment-method h4 {
            color: #003366;
            margin-top: 0;
        }
        .payment-method ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .payment-method li {
            margin: 5px 0;
        }
        .important-note {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
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
            background-color: #e9ecef;
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
        .pay-now-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .pay-now-section h3 {
            color: white;
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        .pay-now-button {
            display: inline-block;
            background-color: #ffffff;
            color: #28a745;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .pay-now-button:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        .pay-now-link {
            color: white;
            font-size: 12px;
            margin-top: 15px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mandatory Savings Payment</h1>
            <p>{{ $month }} {{ $year }}</p>
        </div>
        
        <div class="content">
            <p>Dear <strong>{{ $memberName }}</strong>,</p>
            
            <p>Your mandatory savings payment for <strong>{{ $month }} {{ $year }}</strong> has been generated. Please find the payment details below:</p>
            
            <div class="payment-details">
                <h3 style="color: #003366; margin-top: 0;">Payment Information</h3>

                <p><strong>Account Number:</strong> {{ $accountNumber }}</p>
                <p><strong>Payment Period:</strong> {{ $month }} {{ $year }}</p>

                <div class="control-number">
                    Control Number: {{ $controlNumber }}
                </div>

                <div class="amount">
                    Amount: TZS {{ number_format($amount, 2) }}
                </div>

                <div class="due-date">
                    <strong>Due Date:</strong> {{ $dueDate->format('F j, Y') }}
                </div>
            </div>

            @if($paymentLink)
            <div class="pay-now-section">
                <h3>Quick & Easy Online Payment</h3>
                <a href="{{ $paymentLink }}" class="pay-now-button">PAY NOW</a>
                <p class="pay-now-link">Or copy this link: {{ $paymentLink }}</p>
            </div>
            @endif
            
            <div class="important-note">
                <h4 style="margin-top: 0; color: #0c5460;">Important Notes:</h4>
                <ul>
                    <li>Please use the control number above when making your payment</li>
                    <li>Payments made after the due date may incur late fees</li>
                    <li>Keep your payment receipt for reference</li>
                    <li>This is a mandatory payment required for all active members</li>
                </ul>
            </div>
            
            <h3 style="color: #003366;">Payment Methods</h3>
            
            @foreach($paymentInstructions as $method)
            <div class="payment-method">
                <h4>{{ $method['title'] }}</h4>
                <ol>
                    @foreach($method['steps'] as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
            </div>
            @endforeach
            
            <div class="contact-info">
                <h4 style="color: #003366; margin-top: 0;">Need Assistance?</h4>
                <p>Our support team is here to help you:</p>
                <ul>
                    <li>📞 Phone: +255 22 219 7000</li>
                    <li>📧 Email: support@nbcsaccos.co.tz</li>
                    <li>🏦 Visit any NBC Bank branch</li>
                    <li>💬 WhatsApp: +255 755 123 456</li>
                </ul>
            </div>
            
            <p>Thank you for your continued membership with MFI Management System. Your timely payments help us provide better services to all members.</p>
            
            <p>Best regards,<br>
            <strong>MFI Management System Team</strong></p>
        </div>
        
        <div class="footer">
            <p>This email is confidential and intended for the recipient specified in the message only. It is strictly forbidden to share any part of this message with any third party, without a written consent of the sender.</p>
            <p>© {{ date('Y') }} NBC Bank. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 