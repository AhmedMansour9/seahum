<?php

namespace App\Http\Controllers\RestAPI\v2\delivery_man;

use App\Enums\OrderSource;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DeliveryAppStaffOrderCreateService;
use App\Services\DeliveryAppStaffOrderEditService;
use App\Services\DeliveryAppStaffService;
use App\Services\DeliveryManAssignmentService;
use App\Services\EasyOrdersOrderEditService;
use App\Services\EasyOrdersWebhookService;
use App\Services\OrderStatusUpdateService;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Order screens for admin and customer service accounts of the delivery man app.
 */
class StaffOrderController extends Controller
{
    public function __construct(
        private readonly DeliveryAppStaffService      $deliveryAppStaffService,
        private readonly OrderStatusUpdateService     $orderStatusUpdateService,
        private readonly EasyOrdersWebhookService     $easyOrdersWebhookService,
        private readonly DeliveryManAssignmentService $deliveryManAssignmentService,
    )
    {
    }

    public function getOrders(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => ['nullable', Rule::in(getOrderStatusList())],
            'order_source' => ['nullable', Rule::in(array_column(OrderSource::cases(), 'value'))],
            'date_type' => 'nullable|in:today',
            'postponed' => 'nullable|boolean',
            'governorates' => 'nullable|boolean',
            'delivery_man_id' => 'nullable|integer',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
            'search' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $limit = (int)($request['limit'] ?? 20);
        $offset = (int)($request['offset'] ?? 1);
        $filters = $request->only(['status', 'search', 'order_source', 'date_type', 'delivery_man_id', 'date_from', 'date_to', 'postponed', 'governorates']);
        $orders = $this->deliveryAppStaffService->getOrders(filters: $filters, limit: $limit, offset: $offset);

        return response()->json([
            'total_size' => $orders->total(),
            'limit' => $limit,
            'offset' => $offset,
            'status_counts' => $this->deliveryAppStaffService->getStatusCounts(filters: $filters),
            'postponed_count' => $this->deliveryAppStaffService->getPostponedCount(filters: $filters),
            'governorates_count' => $this->deliveryAppStaffService->getGovernoratesCount(filters: $filters),
            'orders' => $orders->getCollection()->map(fn (Order $order) => $this->deliveryAppStaffService->formatOrderListItem(order: $order))->values(),
        ]);
    }

    public function getOrderDetails(Request $request): JsonResponse
    {
        $staff = $request['delivery_man'];
        $order = $this->deliveryAppStaffService->getOrderDetails(
            orderId: (int)$request['order_id'],
            deliveryManId: $staff->app_role->isStaff() ? null : $staff['id'],
        );
        if (!$order) {
            return $this->orderNotFoundResponse();
        }

        return response()->json($order);
    }

    public function updateScheduledFor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'scheduled_for' => 'nullable|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        if (!Order::where(['id' => $request['order_id'], 'order_type' => 'default_type'])->exists()) {
            return $this->orderNotFoundResponse();
        }

        $this->deliveryAppStaffService->updateScheduledFor(orderId: $request['order_id'], scheduledFor: $request['scheduled_for']);

        return response()->json(['status' => 1, 'message' => translate('successfully_updated')]);
    }

    public function updateOrderStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'status' => ['required', Rule::in(getOrderStatusList())],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $order = Order::where(['id' => $request['order_id'], 'order_type' => 'default_type'])->first();
        if (!$order) {
            return $this->orderNotFoundResponse();
        }

        $staff = $request['delivery_man'];
        if ($order['order_source'] === OrderSource::EasyOrders) {
            $this->easyOrdersWebhookService->updateAdminOrderStatus(
                orderId: $order['id'],
                status: $request['status'],
                adminId: $staff['id'],
                userType: 'delivery_man',
            );

            return response()->json(['status' => 1, 'message' => translate('status_change_successfully')]);
        }

        if ($order['order_status'] === $request['status']) {
            return response()->json(['status' => 1, 'message' => translate('status_change_successfully')]);
        }

        return response()->json($this->orderStatusUpdateService->updateStatus(
            orderId: $order['id'],
            status: $request['status'],
            userId: $staff['id'],
            userType: 'delivery_man',
        ));
    }

    public function updateEasyOrdersOrder(Request $request, EasyOrdersOrderEditService $easyOrdersOrderEditService): JsonResponse
    {
        $validator = Validator::make($request->all(), ['order_id' => 'required|integer'] + EasyOrdersOrderEditService::getValidationRules());
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $result = $easyOrdersOrderEditService->updateOrder(orderId: $request['order_id'], data: $validator->validated());
        if (!$result['status']) {
            return response()->json(['errors' => [['code' => 'order_id', 'message' => $result['message']]]], 403);
        }

        return response()->json(['status' => 1, 'message' => $result['message']]);
    }

    public function createOrder(Request $request, DeliveryAppStaffOrderCreateService $deliveryAppStaffOrderCreateService): JsonResponse
    {
        $validator = Validator::make($request->all(), DeliveryAppStaffOrderCreateService::getValidationRules());
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $result = $deliveryAppStaffOrderCreateService->createOrder(data: $validator->validated(), staff: $request['delivery_man']);
        if (!$result['status']) {
            return response()->json(['errors' => [['code' => 'products', 'message' => $result['message']]]], 403);
        }

        return response()->json(['status' => 1, 'message' => $result['message'], 'order_id' => $result['order_id']]);
    }

    public function getEditProducts(Request $request, DeliveryAppStaffOrderEditService $deliveryAppStaffOrderEditService): JsonResponse
    {
        $validator = Validator::make($request->all(), ['search' => 'nullable|string|max:100']);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        return response()->json($deliveryAppStaffOrderEditService->searchProducts(search: $request['search']));
    }

    public function previewOrderEdit(Request $request, DeliveryAppStaffOrderEditService $deliveryAppStaffOrderEditService): JsonResponse
    {
        $validator = Validator::make($request->all(), ['order_id' => 'required|integer'] + DeliveryAppStaffOrderEditService::getProductsValidationRules());
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $result = $deliveryAppStaffOrderEditService->previewProducts(
            orderId: $request['order_id'],
            products: $request['products'],
            staff: $request['delivery_man'],
            shippingCost: $request['shipping_cost'],
        );

        return $this->orderEditResponse(result: $result);
    }

    public function updateOrder(Request $request, DeliveryAppStaffOrderEditService $deliveryAppStaffOrderEditService): JsonResponse
    {
        $validator = Validator::make($request->all(), DeliveryAppStaffOrderEditService::getValidationRules());
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $result = $deliveryAppStaffOrderEditService->updateOrder(
            orderId: $request['order_id'],
            data: $validator->validated(),
            staff: $request['delivery_man'],
        );

        return $this->orderEditResponse(result: $result);
    }

    public function updatePaymentStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'payment_status' => 'required|in:paid,unpaid',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $order = Order::where(['id' => $request['order_id'], 'order_type' => 'default_type'])->first();
        if (!$order) {
            return $this->orderNotFoundResponse();
        }

        if ($order['order_source'] === OrderSource::EasyOrders) {
            $this->easyOrdersWebhookService->updateAdminOrderPaymentStatus(orderId: $order['id'], paymentStatus: $request['payment_status']);

            return response()->json(['status' => 1, 'message' => translate('status_change_successfully')]);
        }

        return response()->json($this->orderStatusUpdateService->updatePaymentStatus(
            orderId: $order['id'],
            paymentStatus: $request['payment_status'],
        ));
    }

    public function assignDeliveryMan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'delivery_man_id' => 'present|nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $order = Order::where(['id' => $request['order_id'], 'order_type' => 'default_type'])->first();
        if (!$order) {
            return $this->orderNotFoundResponse();
        }

        if (blank($request['delivery_man_id'])) {
            if ($order['order_status'] === 'delivered') {
                return response()->json(['errors' => [['code' => 'order', 'message' => translate('This_order_can_no_longer_be_edited')]]], 403);
            }
            $this->deliveryManAssignmentService->unassignDeliveryMan(orderId: $order['id']);

            return response()->json(['status' => 1, 'message' => translate('Delivery_man_removed_successfully')]);
        }

        if (!$this->deliveryAppStaffService->isAssignableDeliveryMan(deliveryManId: $request['delivery_man_id'])) {
            return response()->json([
                'errors' => [['code' => 'delivery_man_id', 'message' => translate('Delivery_man_not_found')]],
            ], 404);
        }

        $this->deliveryManAssignmentService->assignDeliveryMan(orderId: $request['order_id'], deliveryManId: $request['delivery_man_id']);

        return response()->json(['status' => 1, 'message' => translate('Delivery_man_assigned_successfully')]);
    }

    public function getDeliveryMen(): JsonResponse
    {
        return response()->json($this->deliveryAppStaffService->getDeliveryMen());
    }

    public function getDashboardStats(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $dateFrom = Carbon::parse($request['date_from'] ?? $request['date_to'] ?? 'today');
        $dateTo = Carbon::parse($request['date_to'] ?? $request['date_from'] ?? 'today');

        return response()->json($this->deliveryAppStaffService->getStats(dateFrom: $dateFrom, dateTo: $dateTo));
    }

    /**
     * @param array{status: bool, message: string} $result
     */
    private function orderEditResponse(array $result): JsonResponse
    {
        if (!$result['status']) {
            return response()->json(['errors' => [['code' => 'order', 'message' => $result['message']]]], 403);
        }

        return response()->json([...$result, 'status' => 1]);
    }

    private function orderNotFoundResponse(): JsonResponse
    {
        return response()->json([
            'errors' => [['code' => 'order_id', 'message' => translate('Order_not_found')]],
        ], 404);
    }
}
