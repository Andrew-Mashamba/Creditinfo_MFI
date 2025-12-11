<?php

return [
    'gateway_url' => env('GEPG_GATEWAY_URL', 'https://nbc-gateway-uat.intra.nbc.co.tz'),
    'channel_id' => env('GEPG_CHANNEL_ID', 'YOUR_CHANNEL_ID'),
    'channel_name' => env('GEPG_CHANNEL_NAME', 'YOUR_CHANNEL_NAME'),
    'auth_token' => env('GEPG_AUTH_TOKEN', 'YOUR_AUTH_TOKEN'),
    'default_account_no' => env('GEPG_DEFAULT_ACCOUNT_NO', 'DEFAULT_ACCOUNT'),
    'private_key_path' => storage_path('app/keys/private_key.pem'),
    'public_key_path' => storage_path('app/keys/public_key.pem'),
    'verify_ssl' => env('GEPG_GATEWAY_VERIFY_SSL', true),
];