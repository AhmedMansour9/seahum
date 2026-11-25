<?php

namespace App\Services;

use App\Models\Order;

/**
 * Data for the Meta (Facebook) Pixel shopping events on the storefront. The pixel itself is
 * configured in admin (Third party > Analytics > Meta Pixel); without it no event is sent.
 */
class MetaPixelService
{
    /** Orders older than this are never reported again, so an old link cannot re-send a purchase. */
    private const PURCHASE_REPORT_WINDOW_HOURS = 24;

    public function getCurrencyCode(): string
    {
        return getCurrencyCode() ?: 'EGP';
    }

    /**
     * @param  array<int, int|string>|null  $orderIds
     * @return array{event_id: string, data: array{value: float, currency: string, content_type: string, content_ids: array<int, string>, num_items: int}}|null
     */
    public function getPurchaseEvent(?array $orderIds): ?array
    {
        $orderIds = array_values(array_filter((array) $orderIds, 'is_numeric'));
        if (empty($orderIds)) {
            return null;
        }

        $orders = Order::with('details:id,order_id,product_id,qty')
            ->whereIn('id', $orderIds)
            ->where('created_at', '>=', now()->subHours(self::PURCHASE_REPORT_WINDOW_HOURS))
            ->get(['id', 'order_amount']);
        if ($orders->isEmpty()) {
            return null;
        }

        $orderDetails = $orders->flatMap(fn (Order $order) => $order->details);

        return [
            'event_id' => 'order-'.$orders->pluck('id')->sort()->implode('-'),
            'data' => [
                'value' => round((float) $orders->sum('order_amount'), 2),
                'currency' => $this->getCurrencyCode(),
                'content_type' => 'product',
                'content_ids' => $orderDetails->pluck('product_id')->unique()->map(fn ($productId) => (string) $productId)->values()->all(),
                'num_items' => (int) $orderDetails->sum('qty'),
            ],
        ];
    }
}
