<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Application Verification Code</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #2563eb;
            color: #ffffff;
            text-align: center;
            padding: 30px 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: normal;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2563eb;
        }
        .otp-box {
            background-color: #f3f4f6;
            border: 2px solid #2563eb;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #2563eb;
            letter-spacing: 8px;
            margin: 10px 0;
        }
        .otp-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 10px;
        }
        .message {
            font-size: 16px;
            color: #4b5563;
            margin-bottom: 20px;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
            color: #92400e;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 5px 0;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            margin: 20px 0;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            .content {
                padding: 30px 20px;
            }
            .otp-code {
                font-size: 28px;
                letter-spacing: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Loan Application Verification</h1>
        </div>
        
        <div class="content">
            <p class="greeting">Dear {{ $clientName ?? 'Valued Member' }},</p>
            
            <p class="message">
                You have requested to submit a loan application. To ensure the security of your application, 
                please use the verification code below:
            </p>
            
            <div class="otp-box">
                <p class="otp-label">Your Verification Code</p>
                <p class="otp-code">{{ $otp }}</p>
                <p class="otp-label">Valid for {{ $expiryMinutes ?? 5 }} minutes</p>
            </div>
            
            <p class="message">
                Enter this code in your loan application to proceed with the submission.
            </p>
            
            <div class="warning">
                <strong>Security Notice:</strong> This code is confidential. Do not share it with anyone. 
                Our staff will never ask you for this code over the phone or email.
            </div>
            
            <p class="message">
                If you did not request this verification code, please ignore this email and contact our 
                support team immediately.
            </p>
        </div>
        
        <div class="footer">
            <p><strong>{{ config('app.name', 'SACCOS') }}</strong></p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'SACCOS') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>