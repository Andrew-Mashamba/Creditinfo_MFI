<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your System Account Credentials - {{ config('app.name', 'SACCOS Management System') }}</title>
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
        .role-info {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Your System Account is Ready!</h1>
        <p>Welcome to {{ config('app.name', 'SACCOS Management System') }}</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->name }},</h2>
        
        <p>Your account has been successfully created in the {{ config('app.name', 'SACCOS Management System') }}. You now have access to the system and can begin using all available features.</p>
        
        <div class="credentials-box">
            <h3 style="margin-top: 0; color: #1e40af;">🔐 Your Login Credentials</h3>
            
            <div class="credential-item">
                <div class="credential-label">Email Address:</div>
                <div class="credential-value">{{ $user->email }}</div>
            </div>
            
            <div class="password-box">
                <div class="credential-label">🔑 Your Temporary Password:</div>
                <div class="credential-value" style="font-size: 18px; font-weight: bold; color: #b45309;">
                    {{ $password }}
                </div>
                <p style="margin: 10px 0 0 0; color: #92400e; font-size: 12px;">
                    <strong>Important:</strong> Please change this password after your first login for security.
                </p>
            </div>
        </div>

        @if($department || $role)
        <div class="role-info">
            <h3 style="margin-top: 0; color: #0369a1;">👤 Your Account Information</h3>
            @if($department)
            <div class="credential-item">
                <div class="credential-label">Department:</div>
                <div class="credential-value">{{ $department }}</div>
            </div>
            @endif
            @if($role)
            <div class="credential-item">
                <div class="credential-label">Role:</div>
                <div class="credential-value">{{ $role }}</div>
            </div>
            @endif
        </div>
        @endif
        
        <div class="instructions">
            <h3 style="margin-top: 0; color: #1e40af;">📝 How to Access the System:</h3>
            <ol>
                <li><strong>Visit the system:</strong> <a href="http://saccos-uat.intra.nbc.co.tz/">http://saccos-uat.intra.nbc.co.tz/</a></li>
                <li><strong>Enter your email:</strong> Use the email address provided above</li>
                <li><strong>Enter password:</strong> Use the temporary password provided above</li>
                <li><strong>Change password:</strong> Update to a secure password of your choice</li>
                <li><strong>Explore:</strong> Access all system features and functionalities</li>
            </ol>
        </div>

        <div style="text-align: center;">
            <a href="http://saccos-uat.intra.nbc.co.tz/" class="button">🚀 Login to System</a>
        </div>
        
        <div class="instructions">
            <h3 style="margin-top: 0; color: #1e40af;">✨ What You Can Do in the System:</h3>
            <ul>
                <li>📊 Access your dashboard and overview</li>
                <li>👥 Manage user accounts and permissions</li>
                <li>📋 View and manage system data</li>
                <li>📈 Generate reports and analytics</li>
                <li>⚙️ Configure system settings</li>
                <li>🔒 Manage security and access controls</li>
                <li>📱 Access from any device, anywhere</li>
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
                <li><strong>Verify the URL</strong> - Always ensure you're on the official system</li>
            </ul>
        </div>
        
        <div style="background: #f0f9ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #0369a1;">📞 Need Help?</h3>
            <p style="margin-bottom: 10px;">Our support team is here to assist you:</p>
            <ul style="margin: 0;">
                <li><strong>System Support:</strong> Contact your system administrator</li>
                <li><strong>Technical Issues:</strong> Reach out to the IT department</li>
                <li><strong>Training:</strong> Request system training if needed</li>
                <li><strong>Documentation:</strong> Check the system help section</li>
            </ul>
        </div>
        
        <p><strong>Welcome to the team!</strong></p>
        <p>We're excited to have you join us and look forward to your contributions to our organization.</p>
        
        <p>Best regards,<br>
        <strong>{{ config('app.name', 'SACCOS Management System') }} Team</strong></p>
    </div>
    
    <div class="footer">
        <p><strong>This is an automated message.</strong> Please do not reply to this email.</p>
        <p>If you didn't expect this account creation, please contact us immediately.</p>
        <p>This email was sent to {{ $user->email }} on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'SACCOS Management System') }}. All rights reserved.</p>
    </div>
</body>
</html>
