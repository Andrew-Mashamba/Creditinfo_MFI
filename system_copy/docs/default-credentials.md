# SACCOS Core System - Default Credentials

## Updated Default Password
As of 2025-09-07, all default users have been updated to use a unified password for easier development and testing.

## Default User Accounts

### 1. System Administrator
- **Email**: jane.doe@example.com
- **Password**: 1234567890
- **Role**: System Administrator
- **Department**: GOV
- **Status**: Active

### 2. Institution Administrator (Primary)
- **Email**: andrew.s.mashamba@gmail.com
- **Password**: 1234567890
- **Role**: Institution Administrator
- **Department**: ICT
- **Status**: Active

### 3. Institution Administrator (Secondary)
- **Email**: admin@saccos.co.tz
- **Password**: 1234567890
- **Role**: Institution Administrator
- **Department**: IT
- **Status**: Active

## Password Hash Information
- **Plain Text**: 1234567890
- **BCrypt Hash**: `$2y$10$TyRcJsQ01zrX4AQmwMTF6.r/lTQbgSTdh5bWXi3kDlGLYU61apKTq`
- **Hash Algorithm**: BCrypt with cost factor 10

## Files Updated
The following seeder files have been updated with the new password:

1. **UsersSeeder.php**
   - Updated hash for existing users
   - Added comment indicating password value

2. **VerifySetupSeeder.php**
   - Updated bcrypt() calls to use '1234567890'
   - Added comment for clarity

## Security Notice
⚠️ **IMPORTANT**: These credentials are for development and testing purposes only.

### For Production Environments:
1. **NEVER** use these default credentials
2. Generate strong, unique passwords for each user
3. Enforce password policies requiring:
   - Minimum 12 characters
   - Mixed case letters
   - Numbers and special characters
   - Regular password rotation

### Password Policy Recommendations:
```php
// Example password policy for production
[
    'min_length' => 12,
    'require_uppercase' => true,
    'require_lowercase' => true,
    'require_numbers' => true,
    'require_special_chars' => true,
    'password_expiry_days' => 90,
    'password_history' => 5, // Prevent reuse of last 5 passwords
    'max_attempts' => 5,
    'lockout_duration' => 30, // minutes
]
```

## Testing the Credentials

### Via Web Interface:
1. Navigate to your application URL
2. Click on Login
3. Enter email and password
4. Submit

### Via Artisan Tinker:
```php
php artisan tinker
>>> use Illuminate\Support\Facades\Hash;
>>> $password = '1234567890';
>>> $hash = '$2y$10$TyRcJsQ01zrX4AQmwMTF6.r/lTQbgSTdh5bWXi3kDlGLYU61apKTq';
>>> Hash::check($password, $hash);
// Should return: true
```

### Via Database Query:
```sql
-- Check users and their roles
SELECT u.email, u.name, r.name as role_name
FROM users u
LEFT JOIN user_roles ur ON u.id = ur.user_id
LEFT JOIN roles r ON ur.role_id = r.id
WHERE u.status = 'active';
```

## Resetting Passwords

### For Individual User:
```php
php artisan tinker
>>> $user = User::find(1);
>>> $user->password = Hash::make('new_password_here');
>>> $user->save();
```

### For All Users (Development Only):
```php
php artisan tinker
>>> User::query()->update(['password' => Hash::make('1234567890')]);
```

## Troubleshooting

### Password Not Working:
1. Check if user exists: `User::where('email', 'email@example.com')->exists()`
2. Verify user is active: `User::where('email', 'email@example.com')->first()->status`
3. Reset password if needed (see above)

### Account Locked:
1. Check failed login attempts
2. Reset attempt counter
3. Verify account status

### Role Not Assigned:
1. Run VerifySetupSeeder: `php artisan db:seed --class=VerifySetupSeeder`
2. This will automatically assign roles to users without them

## Fresh Installation
When running fresh migrations and seeders:
```bash
php artisan migrate:fresh --seed
```

All users will be created with the password: **1234567890**

---
*Last Updated: 2025-09-07*
*Version: 1.0*
*Security Level: Development/Testing Only*