<?php

namespace App\Http\Controllers\Admin\Order;

use App\Services\EasyOrdersOrderEditService;
use App\Services\EasyOrdersWebhookService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EasyOrdersOrderController
{
    public function __construct(
        private readonly EasyOrdersWebhookService $easyOrdersWebhookService,
    ) {
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'order_status' => 'required|string|in:' . implode(',', getOrderStatusList()),
        ]);

        $order = $this->easyOrdersWebhookService->updateAdminOrderStatus(
            orderId: $validated['id'],
            status: $validated['order_status'],
            adminId: auth('admin')->id(),
        );

        if (!$order) {
            return response()->json([
                'status' => 0,
                'message' => translate('Order_not_found'),
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => translate('status_change_successfully'),
        ]);
    }

    public function updatePaymentStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'payment_status' => 'required|string|in:paid,unpaid',
        ]);

        $order = $this->easyOrdersWebhookService->updateAdminOrderPaymentStatus(
            orderId: $validated['id'],
            paymentStatus: $validated['payment_status'],
        );

        if (!$order) {
            return response()->json([
                'status' => 0,
                'message' => translate('Order_not_found'),
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => translate('status_change_successfully'),
        ]);
    }

    public function update(Request $request, string|int $id, EasyOrdersOrderEditService $easyOrdersOrderEditService): RedirectResponse
    {
        $validated = $request->validate([
            'contact_person_name' => 'required|string|max:191',
            'phone' => 'required|string|max:30',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'order_note' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.remove' => 'nullable|boolean',
            'new_items' => 'nullable|array',
            'new_items.*.name' => 'nullable|string|max:191',
            'new_items.*.qty' => 'nullable|required_with:new_items.*.name|integer|min:1',
            'new_items.*.price' => 'nullable|required_with:new_items.*.name|numeric|min:0',
        ]);

        $result = $easyOrdersOrderEditService->updateOrder(orderId: $id, data: $validated);

        $result['status'] ? ToastMagic::success($result['message']) : ToastMagic::error($result['message']);

        return back();
    }
}
