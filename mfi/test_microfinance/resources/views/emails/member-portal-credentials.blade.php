<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your SACCO Members Portal Access Credentials</title>
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
        .credentials-box {
            background: #fff;
            border: 2px solid #3b82f6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .credential-item {
            margin: 10px 0;
            padding: 10px;
            background: #f3f4f6;
            border-radius: 5px;
        }
        .credential-label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }
        .credential-value {
            font-family: monospace;
            font-size: 16px;
            color: #1f2937;
            background: #fff;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }
        .password-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .security-notice {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .instructions {
            background: #dbeafe;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ isset($credentials['is_password_reset']) && $credentials['is_password_reset'] ? '🔄 Password Reset Complete' : '🎉 Your SACCO Portal Access is Ready!' }}</h1>
        <p>{{ isset($credentials['is_password_reset']) && $credentials['is_password_reset'] ? 'Your new password is ready to use' : 'Welcome to the digital banking experience' }}</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $member->getFullNameAttribute() }},</h2>
        
        @if(isset($credentials['is_password_reset']) && $credentials['is_password_reset'])
            <p>Your SACCO Members Portal password has been successfully reset. You can now access your account using the new credentials below.</p>
        @else
            <p>Great news! Your SACCO Members Portal access has been successfully activated by our team. You can now access your account information, view transactions, and manage your finances online 24/7.</p>
        @endif
        
        <div class="credentials-box">
            <h3 style="margin-top: 0; color: #1e40af;">🔐 Your Login Credentials</h3>
            
            <div class="credential-item">
                <div class="credential-label">Member Number:</div>
                <div class="credential-value">{{ $credentials['member_number'] }}</div>
            </div>
            
            <div class="credential-item">
                <div class="credential-label">Email Address:</div>
                <div class="credential-value">{{ $credentials['email'] }}</div>
            </div>
            
            <div class="credential-item">
                <div class="credential-label">Phone Number:</div>
                <div class="credential-value">{{ $credentials['phone'] }}</div>
            </div>
            
            <div class="password-box">
                <div class="credential-label">🔑 {{ isset($credentials['is_password_reset']) && $credentials['is_password_reset'] ? 'Your New Password:' : 'Your Temporary Password:' }}</div>
                <div class="credential-value" style="font-size: 18px; font-weight: bold; color: #b45309;">
                    {{ $credentials['password'] }}
                </div>
                <p style="margin: 10px 0 0 0; color: #92400e; font-size: 12px;">
                    <strong>Important:</strong> {{ isset($credentials['is_password_reset']) && $credentials['is_password_reset'] ? 'You will be required to change this password on your next login.' : 'Please change this password after your first login for security.' }}
                </p>
            </div>
        </div>
        
        <div class="instructions">
            <h3 style="margin-top: 0; color: #1e40af;">📝 How to Access Your Portal:</h3>
            <ol>
                <li><strong>Visit the portal:</strong> <a href="{{ $credentials['portal_url'] }}">{{ $credentials['portal_url'] }}</a></li>
                <li><strong>Login using:</strong> Your member number, phone, or email address</li>
                <li><strong>Enter password:</strong> Use the temporary password provided above</li>
                <li><strong>Change password:</strong> Update to a secure password of your choice</li>
                <li><strong>Explore:</strong> Access all your account information and services</li>
            </ol>
        </div>
        
        <div style="text-align: center;">
            <a href="{{ $credentials['portal_url'] }}" class="button">🚀 Access Your Portal Now</a>
        </div>
        
        <div class="instructions">
            <h3 style="margin-top: 0; color: #1e40af;">✨ What You Can Do in Your Portal:</h3>
            <ul>
                <li>📊 View all your account balances and statements</li>
                <li>💰 Check your savings, loans, and share information</li>
                <li>📋 Download account statements and reports</li>
                <li>🔄 View your transaction history</li>
                <li>📞 Update your contact information</li>
                <li>🔒 Manage your security settings</li>
                <li>📱 Access from any device, anytime</li>
            </ul>
        </div>
        
        <div class="security-notice">
            <h3 style="margin-top: 0; color: #dc2626;">🛡️ Important Security Information:</h3>
            <ul style="margin: 10px 0;">
                <li><strong>Keep your credentials secure</strong> - Never share your login details</li>
                <li><strong>Change your password</strong> immediately after first login</li>
                <li><strong>Use a strong password</strong> with letters, numbers, and symbols</li>
                <li><strong>Log out completely</strong> when using shared computers</li>
                <li><strong>Contact us immediately</strong> if you suspect unauthorized access</li>
                <li><strong>Verify the URL</strong> - Always ensure you're on the official portal</li>
            </ul>
        </div>
        
        <div style="background: #f0f9ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #0369a1;">📞 Need Help?</h3>
            <p style="margin-bottom: 10px;">Our support team is here to assist you:</p>
            <ul style="margin: 0;">
                <li><strong>Phone:</strong> Call us during business hours</li>
                <li><strong>Email:</strong> Send us your questions</li>
                <li><strong>Visit:</strong> Come to any of our branch locations</li>
                <li><strong>Portal Help:</strong> Use the help section in your portal</li>
            </ul>
        </div>
        
        <p><strong>Welcome to the future of SACCO banking!</strong></p>
        <p>We're excited to have you experience the convenience and security of our digital platform.</p>
        
        <p>Best regards,<br>
        <strong>SACCO Digital Banking Team</strong></p>
    </div>
    
    <div class="footer">
        <p><strong>This is an automated message.</strong> Please do not reply to this email.</p>
        <p>If you didn't request portal access, please contact us immediately.</p>
        <p>This email was sent to {{ $member->email }} on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p>&copy; {{ date('Y') }} SACCO. All rights reserved.</p>
    </div>
</body>
</html> 