<?php

/*
|--------------------------------------------------------------------------
| Payment Gateway .env Overrides
|--------------------------------------------------------------------------
|
| Gateway credentials live in the database and are applied by
| App\Providers\PaymentConfigProvider. These optional .env values take
| precedence over the database when present, which is why every entry
| defaults to null: a null value means "no override, use the database".
|
*/

return [

    'paypal_mode' => env('PAYPAL_MODE'),

    'razor_key' => env('RAZOR_KEY'),
    'razor_secret' => env('RAZOR_SECRET'),

    'paystack_public_key' => env('PAYSTACK_PUBLIC_KEY'),
    'paystack_secret_key' => env('PAYSTACK_SECRET_KEY'),
    'paystack_payment_url' => env('PAYSTACK_PAYMENT_URL'),
    'paystack_merchant_email' => env('MERCHANT_EMAIL'),

    'flutterwave_public_key' => env('FLW_PUBLIC_KEY'),
    'flutterwave_secret_key' => env('FLW_SECRET_KEY'),
    'flutterwave_secret_hash' => env('FLW_SECRET_HASH'),

    'paytm_merchant_key' => env('PAYTM_MERCHANT_KEY'),
    'paytm_merchant_mid' => env('PAYTM_MERCHANT_MID'),
    'paytm_merchant_website' => env('PAYTM_MERCHANT_WEBSITE'),
    'paytm_refund_url' => env('PAYTM_REFUND_URL'),
    'paytm_status_query_url' => env('PAYTM_STATUS_QUERY_URL'),
    'paytm_status_query_new_url' => env('PAYTM_STATUS_QUERY_NEW_URL'),
    'paytm_txn_url' => env('PAYTM_TXN_URL'),

    'sslcommerz_project_path' => env('PROJECT_PATH'),
    'sslcommerz_api_domain_url' => env('API_DOMAIN_URL'),
    'sslcommerz_is_localhost' => env('IS_LOCALHOST'),

];
