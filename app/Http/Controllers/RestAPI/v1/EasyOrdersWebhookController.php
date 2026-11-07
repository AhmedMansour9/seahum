<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Services\EasyOrdersWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EasyOrdersWebhookController extends Controller
{
    private const STATUS_UPDATE_EVENT = 'order-status-update';

    public function __construct(
        private readonly EasyOrdersWebhookService $easyOrdersWebhookService,
    )
    {
    }

    public function handle(Request $request): JsonResponse
    {
        if (!$this->easyOrdersWebhookService->isValidSecret(secret: $request->header('secret'))) {
            return response()->json(['message' => 'Invalid webhook secret'], 401);
        }

        if ($request->input('event_type') === self::STATUS_UPDATE_EVENT) {
            return $this->updateOrderStatus(request: $request);
        }

        return $this->storeOrder(request: $request);
    }

    private function storeOrder(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'id' => 'required',
            'status' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'cost' => 'nullable|numeric',
            'shipping_cost' => 'nullable|numeric',
            'total_cost' => 'required|numeric',
            'full_name' => 'required|string',
            'phone' => 'required|string',
            'government' => 'nullable|string',
            'address' => 'nullable|string',
            'cart_items' => 'required|array|min:1',
            'cart_items.*.price' => 'required|numeric',
            'cart_items.*.quantity' => 'required|integer|min:1',
            'cart_items.*.product_id' => 'nullable',
            'cart_items.*.variant_id' => 'nullable',
            'cart_items.*.product' => 'nullable|array',
            'cart_items.*.variant' => 'nullable|array',
        ]);

        $order = $this->easyOrdersWebhookService->storeOrder(payload: $payload);

        return response()->json(['order_id' => $order['id']], 200);
    }

    private function updateOrderStatus(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'order_id' => 'required',
            'new_status' => 'required|string',
            'payment_ref_id' => 'nullable|string',
        ]);

        $order = $this->easyOrdersWebhookService->updateOrderStatus(payload: $payload);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json(['order_id' => $order['id']], 200);
    }
}
