<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Throwable;

/**
 * Copies past EasyOrders orders into SEA HUM: missing orders are created with their current status,
 * existing ones take the status EasyOrders has now. Staff are not notified for imported orders.
 */
class EasyOrdersImportService
{
    public function __construct(
        private readonly EasyOrdersApiClient       $easyOrdersApiClient,
        private readonly EasyOrdersWebhookService  $easyOrdersWebhookService,
    )
    {
    }

    /**
     * @param callable(string): void|null $onProgress
     * @return array{created: int, updated: int, unchanged: int, failed: int, unknown_statuses: array<string, int>}
     */
    public function importSince(Carbon $createdFrom, bool $isDryRun = false, ?callable $onProgress = null): array
    {
        $summary = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'failed' => 0, 'unknown_statuses' => []];
        $page = 1;

        do {
            $result = $this->easyOrdersApiClient->getOrdersPage(createdFrom: $createdFrom, page: $page);
            foreach ($result['orders'] as $easyOrder) {
                $error = null;
                try {
                    $outcome = $this->importOrder(easyOrder: $easyOrder, isDryRun: $isDryRun);
                } catch (Throwable $exception) {
                    $outcome = 'failed';
                    $error = $exception->getMessage();
                }
                $summary[$outcome]++;

                $status = strtolower((string)($easyOrder['status'] ?? ''));
                if ($this->easyOrdersWebhookService->getMappedOrderStatus(easyOrdersStatus: $status) === null
                    && !in_array($status, config('easy_orders.paid_statuses'), true)) {
                    $summary['unknown_statuses'][$status] = ($summary['unknown_statuses'][$status] ?? 0) + 1;
                }

                if ($onProgress) {
                    $onProgress(($easyOrder['id'] ?? '?') . ' ' . $status . ' → ' . $outcome . ($error ? ' (' . $error . ')' : ''));
                }
            }
            $page++;
        } while ($result['has_more'] && !empty($result['orders']));

        return $summary;
    }

    /**
     * @param array<string, mixed> $easyOrder
     * @return 'created'|'updated'|'unchanged'|'failed'
     */
    private function importOrder(array $easyOrder, bool $isDryRun): string
    {
        if (empty($easyOrder['id'])) {
            return 'failed';
        }

        $existingOrder = $this->easyOrdersWebhookService->findOrder(externalOrderId: (string)$easyOrder['id']);
        $status = strtolower((string)($easyOrder['status'] ?? ''));

        if ($existingOrder) {
            $mappedStatus = $this->easyOrdersWebhookService->getMappedOrderStatus(easyOrdersStatus: $status);
            $isPaid = in_array($status, config('easy_orders.paid_statuses'), true);
            $needsUpdate = ($mappedStatus !== null && $mappedStatus !== $existingOrder['order_status'])
                || ($isPaid && $existingOrder['payment_status'] !== 'paid');
            if (!$needsUpdate) {
                return 'unchanged';
            }
            if (!$isDryRun) {
                $this->easyOrdersWebhookService->updateOrderStatus(payload: ['order_id' => (string)$easyOrder['id'], 'new_status' => $status]);
            }

            return 'updated';
        }

        if ($isDryRun) {
            return 'created';
        }

        if (empty($easyOrder['cart_items'])) {
            $easyOrder = $this->easyOrdersApiClient->getOrder(orderId: (string)$easyOrder['id']);
        }
        if (empty($easyOrder['cart_items']) || empty($easyOrder['full_name'] ?? null)) {
            return 'failed';
        }

        $storedOrder = $this->easyOrdersWebhookService->storeOrder(payload: $easyOrder, notifyStaff: false);

        return $storedOrder ? 'created' : 'unchanged';
    }
}
