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
        $validated = $request->validate(EasyOrdersOrderEditService::getValidationRules());

        $result = $easyOrdersOrderEditService->updateOrder(orderId: $id, data: $validated);

        $result['status'] ? ToastMagic::success($result['message']) : ToastMagic::error($result['message']);

        return back();
    }
}
