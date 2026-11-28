<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Delivering an order no longer marks it paid. "Paid online" is its own action, and the
 * delivery app sends the customer's transfer screenshot with it.
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

    public function test_admin_and_easy_orders_delivering_keep_the_payment_status(): void
    {
        $service = file_get_contents(app_path('Services/OrderStatusUpdateService.php'));
        $start = strpos($service, "if (\$status == 'delivered') {");
        $deliveredBlock = substr($service, $start, strpos($service, 'event(new OrderStatusEvent', $start) - $start);
        $this->assertStringNotContainsString("'payment_status' => 'paid'", $deliveredBlock);

        $webhook = file_get_contents(app_path('Services/EasyOrdersWebhookService.php'));
        $start = strpos($webhook, "if (\$status === 'delivered') {");
        $this->assertNotFalse($start);
        $this->assertStringNotContainsString("'payment_status'", substr($webhook, $start, strpos($webhook, '}', $start) - $start));
    }

    public function test_the_delivery_man_marks_paid_only_with_a_transfer_screenshot(): void
    {
        $controller = $this->deliveryManController();
        $start = strpos($controller, 'public function markOrderPaid');
        $this->assertNotFalse($start);
        $endpoint = substr($controller, $start, strpos($controller, 'public function', $start + 10) - $start);

        $this->assertStringContainsString("'payment_proof' => 'required|image", $endpoint);
        $this->assertStringContainsString("translate('please_upload_the_transfer_screenshot')", $endpoint);
        $this->assertStringContainsString("'delivery_man_id' => \$deliveryMan['id']", $endpoint, 'Only the assigned delivery man can mark the order paid.');
        $this->assertStringContainsString('$this->orderPaymentProofService->store(', $endpoint);
        $this->assertStringContainsString("'payment_status' => 'paid'", $endpoint);

        $this->assertStringContainsString("Route::put('mark-order-paid', 'markOrderPaid');", file_get_contents(base_path('routes/rest_api/v2/api.php')));

        foreach (['en', 'eg'] as $locale) {
            $messages = include base_path("resources/lang/{$locale}/new-messages.php");
            $this->assertArrayHasKey('please_upload_the_transfer_screenshot', $messages);
            $this->assertArrayHasKey('transfer_screenshot', $messages);
        }
    }

    public function test_staff_marking_paid_keeps_the_transfer_screenshot(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/RestAPI/v2/delivery_man/StaffOrderController.php'));
        $start = strpos($controller, 'public function updatePaymentStatus');
        $endpoint = substr($controller, $start, strpos($controller, 'public function', $start + 10) - $start);

        $this->assertStringContainsString("'payment_proof' => 'nullable|image", $endpoint, 'Older app versions without the screenshot keep working.');
        $this->assertStringContainsString("uploadedByType: 'staff'", $endpoint);
    }

    public function test_admin_order_details_shows_the_transfer_screenshots(): void
    {
        $this->assertStringContainsString(
            "@include('admin-views.order.partials._payment-proofs', ['order' => \$order])",
            file_get_contents(resource_path('views/admin-views/order/order-details.blade.php')),
        );
        $this->assertStringContainsString('$order->paymentProofs', file_get_contents(resource_path('views/admin-views/order/partials/_payment-proofs.blade.php')));
    }

    public function test_admin_marking_a_delivered_order_paid_checks_the_referral_reward(): void
    {
        $service = file_get_contents(app_path('Services/OrderStatusUpdateService.php'));

        $this->assertStringContainsString("if (\$paymentStatus === 'paid' && \$order['payment_status'] !== 'paid' && \$order['order_status'] === 'delivered') {", $service);
        $this->assertStringContainsString('OrderManager::generateReferBonusForFirstOrder(orderId: $orderId);', $service);
    }
}
