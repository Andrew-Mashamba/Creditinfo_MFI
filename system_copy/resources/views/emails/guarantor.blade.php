<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Guarantor Notification - NBC SACCOS</title>
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
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #003366;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
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
            <h2 style="color: #003366;">Guarantor Notification</h2>
            
            <p>Dear {{ $name }},</p>
            
            <p>You have been listed as a guarantor for {{ $memberName }} at NBC SACCOS. As a guarantor, you will be responsible for ensuring the member meets their financial obligations to the SACCOS.</p>
            
           
            <div class="contact-info">
                <h4 style="color: #003366;">Need Assistance?</h4>
                <p>Our dedicated support team is here to help you:</p>
                <ul>
                    <li>📞 Phone: +255 22 219 7000</li>
                    <li>📧 Email: support@nbcsaccos.co.tz</li>
                    <li>🏦 Visit any NBC Bank branch</li>
                </ul>
            </div>
            
            <p>Thank you for your support!</p>
            
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