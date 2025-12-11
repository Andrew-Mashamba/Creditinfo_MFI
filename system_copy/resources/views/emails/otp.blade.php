<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NBC Tanzania - OTP Verification</title>
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
        .otp-box {
            background-color: #f5f5f5;
            border: 2px solid #003366;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #003366;
            letter-spacing: 5px;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
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
        .security-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/nbc-logo.png') }}" alt="NBC Tanzania">
        </div>
        
        <div class="content">
            <h2>Good day, {{ $name }}!</h2>
            
            <p>Thank you for using NBC Tanzania's online banking services. To ensure the security of your account, please use the following One-Time Password (OTP) to complete your verification:</p>
            
            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
                <p>This OTP will expire in 5 minutes</p>
            </div>
            
            <div class="security-notice">
                <strong>Security Notice:</strong>
                <ul>
                    <li>Never share this OTP with anyone</li>
                    <li>NBC Tanzania will never ask for your OTP via email, phone, or SMS</li>
                    <li>If you did not request this OTP, please contact our security team immediately</li>
                </ul>
            </div>
            
            <p>If you're having trouble, you can:</p>
            <ul>
                <li>Request a new OTP</li>
                <li>Contact our customer support at +255 22 219 7000</li>
                <li>Visit your nearest NBC branch</li>
            </ul>
            
         
        </div>
        
        <div class="footer">
            <p>National Bank of Commerce Limited (registered number 32700) is regulated by the Bank of Tanzania.</p>
            <p>© {{ date('Y') }} NBC Tanzania. All rights reserved.</p>
            <p>
                <small>
                    This is an automated message, please do not reply to this email.<br>
                    For security reasons, please delete this email after using the OTP.
                </small>
            </p>
        </div>
    </div>
</body>
</html>
