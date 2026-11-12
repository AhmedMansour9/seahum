<?php

namespace App\Services;

use App\Contracts\Repositories\DeliveryManTransactionRepositoryInterface;
use App\Contracts\Repositories\DeliveryManWalletRepositoryInterface;
use App\Contracts\Repositories\LoyaltyPointTransactionRepositoryInterface;
use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\OrderDetailsRewardsRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\OrderStatusHistoryRepositoryInterface;
use App\Enums\PaymentMethod;
use App\Events\OrderStatusEvent;
use App\Models\Order;
use App\Models\ReferralCustomer;
use App\Repositories\OrderTransactionRepository;
use App\Utils\OrderManager;

/**
 * Order status and payment status changes for system (non EasyOrders) orders,
 * shared by the admin panel and the staff screens of the delivery man app.
 */
class OrderStatusUpdateService
{
    public function __construct(
        private readonly OrderRepositoryInterface                   $orderRepo,
        private readonly OrderDetailRepositoryInterface             $orderDetailRepo,
        private readonly OrderDetailsRewardsRepositoryInterface     $orderDetailsRewardsRepo,
        private readonly LoyaltyPointTransactionRepositoryInterface $loyaltyPointTransactionRepo,
        private readonly DeliveryManWalletRepositoryInterface       $deliveryManWalletRepo,
        private readonly DeliveryManTransactionRepositoryInterface  $deliveryManTransactionRepo,
        private readonly OrderStatusHistoryRepositoryInterface      $orderStatusHistoryRepo,
        private readonly OrderTransactionRepository                 $orderTransactionRepo,
        private readonly DeliveryManTransactionService              $deliveryManTransactionService,
        private readonly DeliveryManWalletService                   $deliveryManWalletService,
        private readonly OrderStatusHistoryService                  $orderStatusHistoryService,
    )
    {
    }

    /**
     * @return array{status: int, message: string}
     */
    public function updateStatus(string|int $orderId, string $status, string|int $userId = 0, string $userType = 'admin'): array
    {
        $order = $this->orderRepo->getFirstWhere(params: ['id' => $orderId], relations: ['customer', 'seller.shop', 'deliveryMan', 'latestEditHistory']);

        $blockingMessage = $this->getStatusChangeBlockingMessage(order: $order, status: $status);
        if ($blockingMessage) {
            return ['status' => 0, 'message' => $blockingMessage];
        }

        $this->orderRepo->updateStockOnOrderStatusChange($orderId, $status);
        $this->orderRepo->update(id: $orderId, data: ['order_status' => $status]);
        if ($status == 'delivered') {
            $this->orderRepo->update(id: $orderId, data: ['payment_status' => 'paid', 'is_pause' => 0]);
            $this->orderDetailRepo->updateWhere(params: ['order_id' => $order['id']], data: ['delivery_status' => $status, 'payment_status' => 'paid']);
            $this->orderDetailRepo->updateWhere(params: ['order_id' => $order['id'], 'refund_started_at' => null], data: ['refund_started_at' => now()]);
        }
        event(new OrderStatusEvent(key: $status, type: 'customer', order: $order));
        if ($status == 'canceled') {
            event(new OrderStatusEvent(key: 'canceled', type: 'delivery_man', order: $order));
        }
        if ($order['seller_is'] == 'seller') {
            if ($status == 'canceled') {
                event(new OrderStatusEvent(key: 'canceled', type: 'seller', order: $order));
            } elseif ($status == 'delivered') {
                event(new OrderStatusEvent(key: 'delivered', type: 'seller', order: $order));
            }
        }

        $this->deliverLoyaltyPointReward(order: $order, status: $status);

        OrderManager::generateReferBonusForFirstOrder(orderId: $order['id']);

        if ($order['delivery_man_id'] && $status == 'delivered') {
            $this->updateDeliveryManWalletOnDelivered(order: $order);
        }

        $orderStatusHistoryData = $this->orderStatusHistoryService->getOrderHistoryData(orderId: $orderId, userId: $userId, userType: $userType, status: $status);
        $this->orderStatusHistoryRepo->add($orderStatusHistoryData);
        OrderManager::removeOldStatusHistory(orderId: $orderId, orderStatus: $status);

        $transaction = $this->orderTransactionRepo->getFirstWhere(params: ['order_id' => $order['id']]);
        if (isset($transaction) && $transaction['status'] == 'disburse') {
            return ['status' => 1, 'message' => translate('status_change_successfully')];
        }
        if ($status == 'delivered' && $order['seller_id'] != null) {
            $this->orderRepo->manageWalletOnOrderStatusChange(order: $order, receivedBy: 'admin');
        }
        if ($status == 'delivered') {
            $referredUser = ReferralCustomer::where('user_id', $order?->customer?->id)->first();
            if ($referredUser?->delivered_notify != 1) {
                event(new OrderStatusEvent(key: 'your_referred_customer_order_has_been_delivered', type: 'promoter', order: $order));
                ReferralCustomer::where('user_id', $order?->customer?->id)->update(['delivered_notify' => 1]);
            }
        }

        return ['status' => 1, 'message' => translate('status_change_successfully')];
    }

    /**
     * @return array{status: int, message: string}
     */
    public function updatePaymentStatus(string|int $orderId, string $paymentStatus): array
    {
        $order = $this->orderRepo->getFirstWhere(params: ['id' => $orderId]);

        if ($order['is_guest'] == '0' && !isset($order['customer'])) {
            return ['status' => 0, 'message' => translate('account_has_been_deleted_you_can_not_change_the_status')];
        }
        if ($order['payment_method'] == 'offline_payment' && $order['payment_status'] == 'unpaid') {
            return ['status' => 0, 'message' => translate('Please confirm the offline payment information before editing this order.')];
        }

        $this->orderRepo->update(id: $orderId, data: ['payment_status' => $paymentStatus]);

        return ['status' => 1, 'message' => translate('status_change_successfully')];
    }

    private function getStatusChangeBlockingMessage(Order $order, string $status): ?string
    {
        if (!$order['is_guest'] && !isset($order['customer'])) {
            return translate('account_has_been_deleted_you_can_not_change_the_status');
        }

        if ($order['edit_due_amount'] > 0 && $order?->latestEditHistory?->order_due_payment_method == 'cash_on_delivery' && $order?->latestEditHistory?->order_due_payment_status == 'unpaid' && $status == 'delivered') {
            return translate('Please mark as paid before delivered this order.');
        }

        if ($status == 'delivered') {
            foreach ($order['details'] as $orderDetail) {
                $productDetails = json_decode($orderDetail?->product_details ?? '', true);
                if (
                    $productDetails['product_type'] == 'digital' &&
                    (isset($productDetails['digital_product_type']) && $productDetails['digital_product_type'] == 'ready_after_sell') &&
                    is_null($orderDetail['digital_file_after_sell'])
                ) {
                    return translate('Please_upload_the_digital_product_files_first');
                }
            }
        }

        return null;
    }

    private function deliverLoyaltyPointReward(Order $order, string $status): void
    {
        $loyaltyPointStatus = getWebConfig(name: 'loyalty_point_status');
        $loyaltyPointEachOrder = getWebConfig(name: 'loyalty_point_for_each_order');
        $loyaltyPointEachOrder = !is_null($loyaltyPointEachOrder) ? $loyaltyPointEachOrder : $loyaltyPointStatus;
        $orderDetailsRewards = $this->orderDetailsRewardsRepo->getFirstWhere(params: ['order_id' => $order['id'], 'reward_type' => 'loyalty_point']);

        if ($orderDetailsRewards && $orderDetailsRewards['reward_delivered'] != 1 && $orderDetailsRewards['reward_amount'] > 0 && $loyaltyPointStatus == 1 && $loyaltyPointEachOrder == 1 && !$order['is_guest'] && $status == 'delivered') {
            $this->loyaltyPointTransactionRepo->addLoyaltyPointTransaction(userId: $order['customer_id'], reference: $order['id'], amount: usdToDefaultCurrency(amount: $order['order_amount'] - $order['shipping_cost']), transactionType: 'order_place');
            $this->orderDetailsRewardsRepo->update(id: $orderDetailsRewards['id'], data: ['reward_delivered' => 1]);
        }
    }

    private function updateDeliveryManWalletOnDelivered(Order $order): void
    {
        $deliverymanWallet = $this->deliveryManWalletRepo->getFirstWhere(params: ['delivery_man_id' => $order['delivery_man_id']]);
        $cashInHand = $order['payment_method'] == 'cash_on_delivery' ? $order['order_amount'] : 0;
        if (empty($deliverymanWallet)) {
            $deliverymanWalletData = $this->deliveryManWalletService->getDeliveryManData(id: $order['delivery_man_id'], deliverymanCharge: $order['deliveryman_charge'], cashInHand: $cashInHand);
            $this->deliveryManWalletRepo->add(data: $deliverymanWalletData);
        } else {
            $deliverymanWalletData = [
                'current_balance' => $deliverymanWallet['current_balance'] + $order['deliveryman_charge'] ?? 0,
                'cash_in_hand' => $deliverymanWallet['cash_in_hand'] + $cashInHand ?? 0,
            ];
            $this->deliveryManWalletRepo->updateWhere(params: ['delivery_man_id' => $order['delivery_man_id']], data: $deliverymanWalletData);
        }
        if ($order['deliveryman_charge']) {
            $deliveryManTransactionData = $this->deliveryManTransactionService->getDeliveryManTransactionData(amount: $order['deliveryman_charge'], addedBy: 'admin', id: $order['delivery_man_id'], transactionType: 'deliveryman_charge');
            $this->deliveryManTransactionRepo->add($deliveryManTransactionData);
        }
    }
}
