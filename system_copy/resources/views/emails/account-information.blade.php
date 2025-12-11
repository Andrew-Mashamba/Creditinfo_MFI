<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your NBC SACCOS Account Information</title>
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
        .account-details {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        .account-item {
            margin: 10px 0;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            background-color: #ffffff;
        }
        .account-item:last-child {
            border-bottom: none;
        }
        .account-number {
            font-size: 18px;
            font-weight: bold;
            color: #003366;
            letter-spacing: 1px;
            margin: 5px 0;
        }
        .account-type {
            color: #28a745;
            font-weight: bold;
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
            
            <p>Congratulations! Your membership has been approved. We are pleased to provide you with your account information.</p>
            
            <div class="account-details">
                <h3 style="color: #003366;">Your Account Information</h3>
                
                @if($sharesAccount)
                <div class="account-item">
                    <p><strong>Account Type:</strong> <span class="account-type">Shares Account</span></p>
                    <p><strong>Account Number:</strong></p>
                    <div class="account-number">{{ $sharesAccount->account_number }}</div>
                    <p><small>Use this account for your mandatory shares contribution</small></p>
                </div>
                @endif

                @if($savingsAccount)
                <div class="account-item">
                    <p><strong>Account Type:</strong> <span class="account-type">Savings Account</span></p>
                    <p><strong>Account Number:</strong></p>
                    <div class="account-number">{{ $savingsAccount->account_number }}</div>
                    <p><small>Use this account for your regular savings</small></p>
                </div>
                @endif

                @if($depositsAccount)
                <div class="account-item">
                    <p><strong>Account Type:</strong> <span class="account-type">Deposits Account</span></p>
                    <p><strong>Account Number:</strong></p>
                    <div class="account-number">{{ $depositsAccount->account_number }}</div>
                    <p><small>Use this account for your fixed deposits</small></p>
                </div>
                @endif
            </div>
            
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