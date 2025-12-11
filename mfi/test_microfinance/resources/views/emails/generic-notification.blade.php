<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>MFI Management System Notification</title>
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
        .message {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MFI Management System Notification</h1>
        </div>
        
        <div class="content">
            <p>Dear <strong>{{ $memberName }}</strong>,</p>
            
            <div class="message">
                <p>{{ $message }}</p>
            </div>
            
            <p>If you have any questions or need assistance, please contact our support team:</p>
            <ul>
                <li>📞 Phone: +255 22 219 7000</li>
                <li>📧 Email: support@nbcsaccos.co.tz</li>
                <li>🏦 Visit any NBC Bank branch</li>
            </ul>
            
            <p>Thank you for choosing MFI Management System as your financial partner.</p>
            
            <p>Best regards,<br>
            <strong>MFI Management System Team</strong></p>
        </div>
        
        <div class="footer">
            <p>This email is confidential and intended for the recipient specified in the message only.</p>
            <p>© {{ date('Y') }} NBC Bank. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 