<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Delivery men no longer mark orders as paid, neither by delivering them nor through the
 * "payment received" call the delivery app sends before delivering; admin confirms payments.
 */
class DeliveryManKeepsPaymentStatusTest extends TestCase
{
    private function deliveryManController(): string
    {
        return file_get_contents(app_path('Http/Controllers/RestAPI/v2/delivery_man/DeliveryManController.php'));
    }

    public function test_marking_an_order_delivered_keeps_its_payment_status(): void
    {
        $controller = $this->deliveryManController();
        $start = strpos($controller, "if (isset(\$deliveryMan['id']) && \$request['status'] == 'delivered') {");
        $this->assertNotFalse($start, 'The delivered branch of update_order_status moved.');
        $deliveredBlock = substr($controller, $start, strpos($controller, 'DeliverymanWallet::where', $start) - $start);

        $this->assertStringNotContainsString("'payment_status' => 'paid'", $deliveredBlock);
        $this->assertStringNotContainsString("'order_due_payment_status' => 'paid'", $deliveredBlock);
        $this->assertStringContainsString("'edit_due_amount' => 0,", $deliveredBlock, 'The edited amount still joins the order total.');
    }

    public function test_the_delivery_app_payment_call_no_longer_changes_the_payment(): void
    {
        $controller = $this->deliveryManController();
        $start = strpos($controller, 'public function order_payment_status_update');
        $paymentEndpoint = substr($controller, $start, strpos($controller, 'public function', $start + 10) - $start);

        $this->assertStringNotContainsString('->save()', $paymentEndpoint);
        $this->assertStringNotContainsString('->update(', $paymentEndpoint);
        $this->assertStringContainsString("translate('payment_status_is_confirmed_by_admin')], 200)", $paymentEndpoint, 'The app keeps getting success so its delivery flow goes on.');

        foreach (['en', 'eg'] as $locale) {
            $this->assertArrayHasKey('payment_status_is_confirmed_by_admin', include base_path("resources/lang/{$locale}/new-messages.php"));
        }
    }

    public function test_admin_marking_a_delivered_order_paid_checks_the_referral_reward(): void
    {
        $service = file_get_contents(app_path('Services/OrderStatusUpdateService.php'));

        $this->assertStringContainsString("if (\$paymentStatus === 'paid' && \$order['payment_status'] !== 'paid' && \$order['order_status'] === 'delivered') {", $service);
        $this->assertStringContainsString('OrderManager::generateReferBonusForFirstOrder(orderId: $orderId);', $service);
    }
}
