@component('mail::message')
# Welcome to NBC SACCOS System!

Dear {{ $name }},

Welcome to the NBC SACCOS System! Your account has been created successfully.

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
NBC SACCOS Team
@endcomponent 