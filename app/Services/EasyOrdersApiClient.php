<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Read-only client for the EasyOrders public API (https://public-api-docs.easy-orders.net).
 * The API allows 40 requests per minute, so throttled requests are retried after a pause.
 */
class EasyOrdersApiClient
{
    private const PAGE_SIZE = 50;

    public function isConfigured(): bool
    {
        return (string)config('easy_orders.api_key') !== '';
    }

    /**
     * Orders created on or after the given time, newest first.
     *
     * @return array{orders: array<int, array<string, mixed>>, has_more: bool}
     */
    public function getOrdersPage(Carbon $createdFrom, int $page): array
    {
        $body = $this->get(path: 'orders', query: [
            'filter' => 'created_at||gte||' . $createdFrom->toIso8601String(),
            'sort' => 'created_at,DESC',
            'limit' => self::PAGE_SIZE,
            'page' => $page,
            'join' => 'cart_items',
        ]);

        $orders = array_is_list($body) ? $body : ($body['data'] ?? []);
        $hasMore = isset($body['pageCount'])
            ? $page < (int)$body['pageCount']
            : count($orders) === self::PAGE_SIZE;

        return ['orders' => $orders, 'has_more' => $hasMore];
    }

    /**
     * @return array<string, mixed>
     */
    public function getOrder(string $orderId): array
    {
        return $this->get(path: 'orders/' . rawurlencode($orderId));
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function get(string $path, array $query = []): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('EASY_ORDERS_API_KEY is not set in .env');
        }

        try {
            $response = $this->request()
                ->retry(times: 3, sleepMilliseconds: 61000, when: fn ($exception) => $exception instanceof RequestException && $exception->response->status() === 429)
                ->get($path, $query);
        } catch (RequestException $exception) {
            throw new RuntimeException('EasyOrders API error ' . $exception->response->status() . ': ' . $exception->response->body(), previous: $exception);
        }

        return $response->json() ?? [];
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim((string)config('easy_orders.api_base_url'), '/'))
            ->withHeaders(['Api-Key' => (string)config('easy_orders.api_key')])
            ->acceptJson()
            ->timeout(30);
    }
}
