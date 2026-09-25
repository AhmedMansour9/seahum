<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Http\Controllers\RestAPI\v1\OrderController as ApiOrderController;
use App\Http\Controllers\Web\WebController;
use App\Http\Requests\Admin\BusinessSettingRequest;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Utils\OrderManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CardOnDeliveryPaymentMethodTest extends TestCase
{
    private const PAYMENT_OPTION_FIELDS = ['cash_on_delivery', 'card_on_delivery', 'digital_payment', 'offline_payment'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setCardOnDeliveryStatus(status: null);
    }

    public function test_card_on_delivery_is_disabled_when_the_setting_row_is_missing(): void
    {
        $this->assertFalse(OrderManager::isCardOnDeliveryEnabled());
    }

    public function test_card_on_delivery_follows_the_admin_setting(): void
    {
        $this->setCardOnDeliveryStatus(status: 1);
        $this->assertTrue(OrderManager::isCardOnDeliveryEnabled());

        $this->setCardOnDeliveryStatus(status: 0);
        $this->assertFalse(OrderManager::isCardOnDeliveryEnabled());
    }

    public function test_admin_can_save_card_on_delivery_as_the_only_payment_option(): void
    {
        $this->assertFalse($this->paymentOptionValidator(['card_on_delivery' => 1])->fails());
    }

    public function test_admin_must_keep_at_least_one_payment_option(): void
    {
        $this->assertTrue($this->paymentOptionValidator([])->errors()->has('card_on_delivery'));
    }

    public function test_web_checkout_rejects_card_on_delivery_when_disabled(): void
    {
        $request = Request::create('/checkout-complete', 'GET', ['payment_method' => PaymentMethod::CARD_ON_DELIVERY]);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = app(WebController::class)->getCashOnDeliveryCheckoutComplete($request);

        $this->assertSame(0, $response->getData(true)['status']);
    }

    public function test_web_checkout_rejects_unknown_payment_method(): void
    {
        $request = Request::create('/checkout-complete', 'GET', ['payment_method' => 'pay_by_wallet']);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = app(WebController::class)->getCashOnDeliveryCheckoutComplete($request);

        $this->assertSame(0, $response->getData(true)['status']);
    }

    public function test_api_place_order_rejects_card_on_delivery_when_disabled(): void
    {
        $response = app(ApiOrderController::class)->place_order(
            Request::create('/api/v1/customer/order/place', 'GET', ['payment_method' => PaymentMethod::CARD_ON_DELIVERY])
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertArrayHasKey('message', $response->getData(true));
    }

    public function test_api_place_order_rejects_unsupported_payment_method(): void
    {
        $response = app(ApiOrderController::class)->place_order(
            Request::create('/api/v1/customer/order/place', 'GET', ['payment_method' => 'pay_by_wallet'])
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('payment_method', $response->getData(true)['errors'][0]['code']);
    }

    public function test_card_on_delivery_order_is_not_held_as_a_pending_digital_transaction(): void
    {
        Schema::create('order_transactions', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('admin_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->decimal('pending_amount')->default(0);
        });

        $order = new Order(['id' => 1, 'order_amount' => 1000, 'payment_method' => PaymentMethod::CARD_ON_DELIVERY]);

        OrderManager::getAddOrderTransactionsOnGenerateOrder($order, ['payment_method' => PaymentMethod::CARD_ON_DELIVERY]);

        $this->assertDatabaseCount('order_transactions', 0);
        $this->assertDatabaseCount('admin_wallets', 0);
    }

    private function setCardOnDeliveryStatus(?int $status): void
    {
        $settings = is_null($status) ? collect() : collect([
            new BusinessSetting(['type' => 'card_on_delivery', 'value' => json_encode(['status' => $status])]),
        ]);

        Cache::put(CACHE_BUSINESS_SETTINGS_TABLE, $settings);
    }

    private function paymentOptionValidator(array $input): \Illuminate\Validation\Validator
    {
        return Validator::make($input, Arr::only((new BusinessSettingRequest)->rules(), self::PAYMENT_OPTION_FIELDS));
    }
}
