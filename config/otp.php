<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for OTP (One-Time Password)
    | functionality in the E-Halal BTECHenyo.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | OTP Login Toggle
    |--------------------------------------------------------------------------
    |
    | This option controls whether OTP login is enabled for the system.
    | When set to true, users will be required to use OTP for authentication.
    | When set to false, traditional password authentication will be used.
    |
    */

    'login_enabled' => env('OTP_LOGIN', false),

    /*
    |--------------------------------------------------------------------------
    | OTP Settings
    |--------------------------------------------------------------------------
    |
    | These options control various aspects of OTP functionality.
    |
    */

    'length' => env('OTP_LENGTH', 6),
    'expiry_minutes' => env('OTP_EXPIRY_MINUTES', 5),
    'max_attempts' => env('OTP_MAX_ATTEMPTS', 3),
    'rate_limit_per_hour' => env('OTP_RATE_LIMIT_PER_HOUR', 5),

    /*
    |--------------------------------------------------------------------------
    | OTP Channels
    |--------------------------------------------------------------------------
    |
    | Define which channels can be used for OTP delivery.
    | Available channels: 'email', 'sms'
    |
    */

    'channels' => [
        'email' => env('OTP_EMAIL_ENABLED', true),
        'sms' => env('OTP_SMS_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Email Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for OTP email delivery.
    |
    */

    'email' => [
        'template' => env('OTP_EMAIL_TEMPLATE', 'email-templates.otp-verification'),
        'subject' => env('OTP_EMAIL_SUBJECT', 'Your OTP Verification Code'),
        'from_name' => env('OTP_EMAIL_FROM_NAME', 'E-Halal BTECHenyo'),
        'from_email' => env('OTP_EMAIL_FROM_EMAIL', 'admin@ehalal.tech'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP SMS Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for OTP SMS delivery (if enabled).
    |
    */

    'sms' => [
        'provider' => env('OTP_SMS_PROVIDER', 'twilio'),
        'from_number' => env('OTP_SMS_FROM_NUMBER', '+1234567890'),
        'message_template' => env('OTP_SMS_MESSAGE', 'Your OTP code is: {code}. Valid for {minutes} minutes. Do not share this code.'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Settings
    |--------------------------------------------------------------------------
    |
    | Settings specific to development environment.
    |
    */

    'development' => [
        'always_use_test_otp' => env('OTP_ALWAYS_TEST', false),
        'test_otp_code' => env('OTP_TEST_CODE', '123456'),
        'skip_rate_limiting' => env('OTP_SKIP_RATE_LIMITING', false),
    ],
];
