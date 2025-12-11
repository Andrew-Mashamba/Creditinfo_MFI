<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to MFI Management System</title>
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
            padding: 30px 20px;
            background-color: #ffffff;
        }
        .control-number {
            background-color: #f5f5f5;
            border: 2px solid #003366;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .control-number-code {
            font-size: 32px;
            font-weight: bold;
            color: #003366;
            letter-spacing: 5px;
        }
        .account-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .account-info {
            margin: 10px 0;
            padding: 10px;
            background-color: #fff;
            border-radius: 3px;
        }
        .account-number {
            font-size: 16px;
            font-weight: bold;
            color: #003366;
        }
        .payment-options {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .payment-option {
            margin: 10px 0;
            padding: 10px;
            background-color: #fff;
            border-radius: 3px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #003366;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/nbc-logo.png') }}" alt="MFI Management System">
        </div>
        
        <div class="content">
            <h2>Welcome to MFI Management System, {{ $member->name }}!</h2>
            
            <p>We are delighted to have you as a new member of MFI Management System. Your application has been received and is pending payment of the control number.</p>
            
            <div class="control-number">
                <h3>Your Control Number</h3>
                <div class="control-number-code">{{ $controlNumber }}</div>
                <p>Please use this control number to make your payment</p>
            </div>

            <div class="account-details">
                <h3>Your Account Numbers</h3>
                @if($sharesAccount)
                <div class="account-info">
                    <strong>Shares Account:</strong>
                    <div class="account-number">{{ $sharesAccount->account_number }}</div>
                </div>
                @endif
                @if($savingsAccount)
                <div class="account-info">
                    <strong>Savings Account:</strong>
                    <div class="account-number">{{ $savingsAccount->account_number }}</div>
                </div>
                @endif
                @if($depositsAccount)
                <div class="account-info">
                    <strong>Deposits Account:</strong>
                    <div class="account-number">{{ $depositsAccount->account_number }}</div>
                </div>
                @endif
            </div>
            
            <div class="payment-options">
                <h3>Payment Options</h3>
                <div class="payment-option">
                    <strong>NBC Bank Branches</strong>
                    <p>Visit any NBC Bank branch and present your control number</p>
                </div>
                <div class="payment-option">
                    <strong>NBC Mobile Banking</strong>
                    <p>Use the control number in the mobile banking app</p>
                </div>
                <div class="payment-option">
                    <strong>NBC Internet Banking</strong>
                    <p>Log in to your internet banking and use the control number</p>
                </div>
            </div>
            
            <p>Once your payment is confirmed, your membership will be activated and you'll receive access to all member benefits.</p>
            
            <a href="{{ url('/payment-instructions') }}" class="button">View Detailed Payment Instructions</a>
            
            <p>If you have any questions, please contact our support team:</p>
            <ul>
                <li>Phone: +255 22 219 7000</li>
                <li>Email: support@nbc.co.tz</li>
            </ul>
        </div>
        
        <div class="footer">
            <p>National Bank of Commerce Limited (registered number 32700) is regulated by the Bank of Tanzania.</p>
            <p>© {{ date('Y') }} MFI Management System. All rights reserved.</p>
            <p>
                <small>
                    This is an automated message, please do not reply to this email.<br>
                    For security reasons, please keep your control number confidential.
                </small>
            </p>
        </div>
    </div>
</body>
</html> 