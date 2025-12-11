@component('mail::message')
# Welcome to MFI Management System System!

Dear {{ $name }},

Welcome to the MFI Management System System! Your account has been created successfully.

## Your Login Credentials
- **Username:** {{ $email }}
- **Password:** {{ $password }}

@component('mail::button', ['url' => config('app.url')])
Login to System
@endcomponent

## Important Security Notice
For security reasons, please change your password after your first login.

If you have any questions or need assistance, please contact our support team.

Best regards,<br>
MFI Management System Team
@endcomponent 