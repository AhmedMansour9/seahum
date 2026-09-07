<?php

namespace App\Providers;

use App\Utils\Helpers;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class PaymentConfigProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        try {
            $data = BusinessSetting::where(['type' => 'paypal'])->first();
            $paypal = json_decode($data['value'], true);
            if ($paypal) {
                $mode = $paypal['environment']??'sandbox';
                if ($mode == 'live') {
                    $paypal_mode = "live";
                } else {
                    $paypal_mode = "sandbox";
                }

                $config = array(
                    'client_id' => $paypal['paypal_client_id'], // values : (local | production)
                    'secret' => $paypal['paypal_secret'],
                    'settings' => array(
                        'mode' => config('payment_overrides.paypal_mode') ?? $paypal_mode, //live||sandbox
                        'http.ConnectionTimeOut' => 30,
                        'log.LogEnabled' => true,
                        'log.FileName' => storage_path() . '/logs/paypal.log',
                        'log.LogLevel' => 'ERROR'
                    ),
                );
                Config::set('paypal', $config);
            }

            /*$data = BusinessSetting::where(['type' => 'ssl_commerz_payment'])->first();
            $ssl = json_decode($data['value'], true);
            if ($ssl) {
                $mode = $ssl['environment']??'sandbox';
                if ($mode == 'live') {
                    $url = "https://securepay.sslcommerz.com";
                    $host = false;
                } else {
                    $url = "https://sandbox.sslcommerz.com";
                    $host = true;
                }
                $config = array(
                    'projectPath' => config('payment_overrides.sslcommerz_project_path'),
                    'apiDomain' => config('payment_overrides.sslcommerz_api_domain_url') ?? $url,
                    'apiCredentials' => [
                        'store_id' => $ssl['store_id'],
                        'store_password' => $ssl['store_password'],
                    ],
                    'apiUrl' => [
                        'make_payment' => "/gwprocess/v4/api.php",
                        'transaction_status' => "/validator/api/merchantTransIDvalidationAPI.php",
                        'order_validate' => "/validator/api/validationserverAPI.php",
                        'refund_payment' => "/validator/api/merchantTransIDvalidationAPI.php",
                        'refund_status' => "/validator/api/merchantTransIDvalidationAPI.php",
                    ],
                    'connect_from_localhost' => config('payment_overrides.sslcommerz_is_localhost') ?? $host, // For Sandbox, use "true", For Live, use "false"
                    'success_url' => '/success',
                    'failed_url' => '/fail',
                    'cancel_url' => '/cancel',
                    'ipn_url' => '/ipn',
                );
                Config::set('sslcommerz', $config);
            }*/

            $data = BusinessSetting::where(['type' => 'razor_pay'])->first();
            $razor = json_decode($data['value'], true);
            if ($razor) {
                $config = array(
                    'razor_key' => config('payment_overrides.razor_key') ?? $razor['razor_key'],
                    'razor_secret' => config('payment_overrides.razor_secret') ?? $razor['razor_secret']
                );
                Config::set('razor', $config);
            }

            $data = BusinessSetting::where(['type' => 'paystack'])->first();
            $paystack = json_decode($data['value'], true);
            if ($paystack) {
                $config = array(
                    'publicKey' => config('payment_overrides.paystack_public_key') ?? $paystack['publicKey'],
                    'secretKey' => config('payment_overrides.paystack_secret_key') ?? $paystack['secretKey'],
                    'paymentUrl' => config('payment_overrides.paystack_payment_url') ?? $paystack['paymentUrl'],
                    'merchantEmail' => config('payment_overrides.paystack_merchant_email') ?? $paystack['merchantEmail'],
                );
                Config::set('paystack', $config);
            }

            $data = BusinessSetting::where(['type' => 'flutterwave'])->first();
            $flutterwave = json_decode($data['value'], true);
            if ($flutterwave) {
                $config = array(
                    'publicKey' => config('payment_overrides.flutterwave_public_key') ?? $flutterwave['public_key'], // values : (local | production)
                    'secretKey' => config('payment_overrides.flutterwave_secret_key') ?? $flutterwave['secret_key'],
                    'secretHash' => config('payment_overrides.flutterwave_secret_hash') ?? $flutterwave['hash'],
                );
                Config::set('flutterwave', $config);
            }


            //paytm
            $paytm = getWebConfig(name: 'paytm');
            if (isset($paytm)) {

                $PAYTM_STATUS_QUERY_NEW_URL='https://securegw-stage.paytm.in/merchant-status/getTxnStatus';
                $PAYTM_TXN_URL='https://securegw-stage.paytm.in/theia/processTransaction';
                $mode = $paytm['environment']??'sandbox';
                if ($mode == 'live') {
                    $PAYTM_STATUS_QUERY_NEW_URL='https://securegw.paytm.in/merchant-status/getTxnStatus';
                    $PAYTM_TXN_URL='https://securegw.paytm.in/theia/processTransaction';
                }

                $config = array(
                    'PAYTM_ENVIRONMENT' => ($mode=='live')?'PROD':'TEST',
                    'PAYTM_MERCHANT_KEY' => config('payment_overrides.paytm_merchant_key') ?? $paytm['paytm_merchant_key'],
                    'PAYTM_MERCHANT_MID' => config('payment_overrides.paytm_merchant_mid') ?? $paytm['paytm_merchant_mid'],
                    'PAYTM_MERCHANT_WEBSITE' => config('payment_overrides.paytm_merchant_website') ?? $paytm['paytm_merchant_website'],
                    'PAYTM_REFUND_URL' => config('payment_overrides.paytm_refund_url') ?? $paytm['paytm_refund_url'],
                    'PAYTM_STATUS_QUERY_URL' => config('payment_overrides.paytm_status_query_url') ?? $PAYTM_STATUS_QUERY_NEW_URL,
                    'PAYTM_STATUS_QUERY_NEW_URL' => config('payment_overrides.paytm_status_query_new_url') ?? $PAYTM_STATUS_QUERY_NEW_URL,
                    'PAYTM_TXN_URL' => config('payment_overrides.paytm_txn_url') ?? $PAYTM_TXN_URL,
                );

                Config::set('config_paytm', $config);
            }

        } catch (\Exception $ex) {

        }
    }
}
