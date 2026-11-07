<?php

namespace App\Services;

use App\Contracts\Repositories\DeliveryManTransactionRepositoryInterface;
use App\Contracts\Repositories\DeliveryManWalletRepositoryInterface;
use App\Contracts\Repositories\LoyaltyPointTransactionRepositoryInterface;
use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\OrderDetailsRewardsRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\OrderStatusHistoryRepositoryInterface;
use App\Events\OrderStatusEvent;
use App\Models\Order;
use App\Models\ReferralCustomer;
use App\Repositories\OrderTransactionRepository;
use App\Utils\OrderManager;
use Illuminate\Support\Facades\DB;

class OrderStatusLifecycleService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly DeliveryManTransactionRepositoryInterface $deliveryManTransactionRepository,
        private readonly DeliveryManWalletRepositoryInterface $deliveryManWalletRepository,
        private readonly DeliveryManTransactionService $deliveryManTransactionService,
        private readonly DeliveryManWalletService $deliveryManWalletService,
        private readonly OrderDetailRepositoryInterface $orderDetailRepository,
        private readonly OrderDetailsRewardsRepositoryInterface $orderDetailsRewardsRepository,
        private readonly LoyaltyPointTransactionRepositoryInterface $loyaltyPointTransactionRepository,
        private readonly OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository,
        private readonly OrderStatusHistoryService $orderStatusHistoryService,
        private readonly OrderTransactionRepository $orderTransactionRepository,
    ) {
    }

    public function updateStatus(
        Order $order,
        string $status,
        string $userType = 'admin',
        string|int $userId = 0,
        string $receivedBy = 'admin',
    ): void {
        DB::transaction(fn () => $this->applyStatus(
            order: $order,
            status: $status,
            userType: $userType,
            userId: $userId,
            receivedBy: $receivedBy,
        ));
    }

    private function applyStatus(
        Order $order,
        string $status,
        string $userType,
        string|int $userId,
        string $receivedBy,
    ): void {
        $order->loadMissing(['customer', 'seller.shop', 'deliveryMan', 'latestEditHistory', 'details']);

        $this->orderRepository->updateStockOnOrderStatusChange(orderId: $order['id'], status: $status);
        $this->orderRepository->update(id: $order['id'], data: ['order_status' => $status]);
        $this->orderDetailRepository->updateWhere(
            params: ['order_id' => $order['id']],
            data: ['delivery_status' => $status],
        );

        if ($status === 'delivered') {
            $this->orderRepository->update(id: $order['id'], data: ['payment_status' => 'paid', 'is_pause' => 0]);
            $this->orderDetailRepository->updateWhere(
                params: ['order_id' => $order['id']],
                data: ['payment_status' => 'paid'],
            );
            $this->orderDetailRepository->updateWhere(
                params: ['order_id' => $order['id'], 'refund_started_at' => null],
                data: ['refund_started_at' => now()],
            );
        }

        event(new OrderStatusEvent(key: $status, type: 'customer', order: $order));
        if ($status === 'canceled') {
            event(new OrderStatusEvent(key: 'canceled', type: 'delivery_man', order: $order));
        }
        if ($order['seller_is'] === 'seller') {
            if ($status === 'canceled') {
                event(new OrderStatusEvent(key: 'canceled', type: 'seller', order: $order));
            } elseif ($status === 'delivered') {
                event(new OrderStatusEvent(key: 'delivered', type: 'seller', order: $order));
            }
        }

        if (!$order['is_guest'] && $status === 'delivered') {
            $loyaltyPointStatus = getWebConfig(name: 'loyalty_point_status');
            $loyaltyPointEachOrder = getWebConfig(name: 'loyalty_point_for_each_order');
            $loyaltyPointEachOrder = !is_null($loyaltyPointEachOrder) ? $loyaltyPointEachOrder : $loyaltyPointStatus;
            $orderDetailsRewards = $this->orderDetailsRewardsRepository->getFirstWhere(params: [
                'order_id' => $order['id'],
                'reward_type' => 'loyalty_point',
            ]);

            if ($orderDetailsRewards && $orderDetailsRewards['reward_delivered'] != 1 && $orderDetailsRewards['reward_amount'] > 0 && $loyaltyPointStatus == 1 && $loyaltyPointEachOrder == 1) {
                $this->loyaltyPointTransactionRepository->addLoyaltyPointTransaction(
                    userId: $order['customer_id'],
                    reference: $order['id'],
                    amount: usdToDefaultCurrency(amount: $order['order_amount'] - $order['shipping_cost']),
                    transactionType: 'order_place',
                );
                $this->orderDetailsRewardsRepository->update(id: $orderDetailsRewards['id'], data: ['reward_delivered' => 1]);
            }
        }

        if (!$order['is_guest']) {
            OrderManager::generateReferBonusForFirstOrder(orderId: $order['id']);
        }

        if ($order['delivery_man_id'] && $status === 'delivered') {
            $deliveryManWallet = $this->deliveryManWalletRepository->getFirstWhere(params: [
                'delivery_man_id' => $order['delivery_man_id'],
            ]);
            $cashInHand = $order['payment_method'] === 'cash_on_delivery' ? $order['order_amount'] : 0;
            if (empty($deliveryManWallet)) {
                $deliveryManWalletData = $this->deliveryManWalletService->getDeliveryManData(
                    id: $order['delivery_man_id'],
                    deliverymanCharge: $order['deliveryman_charge'],
                    cashInHand: $cashInHand,
                );
                $this->deliveryManWalletRepository->add(data: $deliveryManWalletData);
            } else {
                $deliveryManWalletData = [
                    'current_balance' => $deliveryManWallet['current_balance'] + $order['deliveryman_charge'],
                    'cash_in_hand' => $deliveryManWallet['cash_in_hand'] + $cashInHand,
                ];
                $this->deliveryManWalletRepository->updateWhere(
                    params: ['delivery_man_id' => $order['delivery_man_id']],
                    data: $deliveryManWalletData,
                );
            }
            if ($order['deliveryman_charge']) {
                $deliveryManTransactionData = $this->deliveryManTransactionService->getDeliveryManTransactionData(
                    amount: $order['deliveryman_charge'],
                    addedBy: 'admin',
                    id: $order['delivery_man_id'],
                    transactionType: 'deliveryman_charge',
                );
                $this->deliveryManTransactionRepository->add($deliveryManTransactionData);
            }
        }

        $orderStatusHistoryData = $this->orderStatusHistoryService->getOrderHistoryData(
            orderId: $order['id'],
            userId: $userId,
            userType: $userType,
            status: $status,
        );
        $this->orderStatusHistoryRepository->add($orderStatusHistoryData);
        OrderManager::removeOldStatusHistory(orderId: $order['id'], orderStatus: $status);

        $transaction = $this->orderTransactionRepository->getFirstWhere(params: ['order_id' => $order['id']]);
        if (isset($transaction) && $transaction['status'] === 'disburse') {
            return;
        }
        if ($status === 'delivered' && $order['seller_id'] !== null) {
            $this->orderRepository->manageWalletOnOrderStatusChange(order: $order, receivedBy: $receivedBy);
        }
        if ($status === 'delivered' && $order['customer_id'] !== null) {
            $referredUser = ReferralCustomer::where('user_id', $order['customer_id'])->first();
            if ($referredUser?->delivered_notify != 1) {
                event(new OrderStatusEvent(key: 'your_referred_customer_order_has_been_delivered', type: 'promoter', order: $order));
                ReferralCustomer::where('user_id', $order['customer_id'])->update(['delivered_notify' => 1]);
            }
        }
    }
}
