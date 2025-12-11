<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to SACCO Members Portal</title>
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
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8fafc;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .button {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .highlight {
            background: #dbeafe;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to SACCO Members Portal!</h1>
        <p>Your portal access has been successfully activated</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $member->getFullNameAttribute() }},</h2>
        
        <p>Welcome to the SACCO Members Portal! Your portal account has been successfully created and you can now access your SACCO services online.</p>
        
        <div class="highlight">
            <h3>What you can do with your portal account:</h3>
            <ul>
                <li>View your account balances and statements</li>
                <li>Check your loan status and payment schedules</li>
                <li>Monitor your shares and dividends</li>
                <li>View recent transactions</li>
                <li>Download account statements</li>
                <li>Update your contact information</li>
            </ul>
        </div>
        
        <p><strong>Portal Access Details:</strong></p>
        <ul>
            <li><strong>Member Number:</strong> {{ $member->client_number }}</li>
            <li><strong>Portal URL:</strong> <a href="{{ $portal_url }}">{{ $portal_url }}</a></li>
        </ul>
        
        <p>You can log in using your member number, phone number, or email address along with the password you created during registration.</p>
        
        <div style="text-align: center;">
            <a href="{{ $portal_url }}" class="button">Access Your Portal</a>
        </div>
        
        <div class="highlight">
            <h3>Security Tips:</h3>
            <ul>
                <li>Keep your password secure and don't share it with anyone</li>
                <li>Log out when using shared computers</li>
                <li>Contact us immediately if you suspect unauthorized access</li>
                <li>Regularly update your contact information</li>
            </ul>
        </div>
        
        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
        
        <p>Thank you for choosing our SACCO!</p>
        
        <p>Best regards,<br>
        <strong>SACCO Management Team</strong></p>
    </div>
    
    <div class="footer">
        <p>This email was sent to {{ $member->email }}. If you didn't register for portal access, please contact us immediately.</p>
        <p>&copy; {{ date('Y') }} SACCO. All rights reserved.</p>
    </div>
</body>
</html> 