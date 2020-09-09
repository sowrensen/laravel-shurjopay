<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Server URL
    |--------------------------------------------------------------------------
    |
    | The server URL provided by ShurjoPay authority.
    |
    */
    'server_url' => env('SHURJOPAY_SERVER_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Merchant Username
    |--------------------------------------------------------------------------
    |
    | Your unique merchant username provided by ShurjoPay authority.
    |
    */
    'merchant_username' => env('MERCHANT_USERNAME', ''),

    /*
    |--------------------------------------------------------------------------
    | Merchant Password
    |--------------------------------------------------------------------------
    |
    | Your secret merchant password provided by ShurjoPay authority.
    |
    */
    'merchant_password' => env('MERCHANT_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Merchant Key Prefix
    |--------------------------------------------------------------------------
    |
    | Your chosen merchant key prefix authorized by ShurjoPay.
    |
    */
    'merchant_key_prefix' => env('MERCHANT_KEY_PREFIX', ''),
];

