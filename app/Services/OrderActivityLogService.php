<?php

namespace App\Services;

use App\Models\OrderActivityLog;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

/**
 * Records every change to an order's important fields together with who made it (admin, vendor,
 * delivery man or staff from the delivery app, customer, or the system) and from where.
 * Logging never breaks the order update that triggered it.
 */
class OrderActivityLogService
{
    /** Order columns whose changes are logged; ranking, read flags and timestamps are not. */
    public const TRACKED_FIELDS = [
        'order_status',
        'payment_status',
        'payment_method',
        'transaction_ref',
        'payment_note',
        'paid_amount',
        'order_amount',
        'edit_due_amount',
        'edit_return_amount',
        'discount_amount',
        'coupon_code',
        'extra_discount',
        'shipping_cost',
        'is_pause',
        'cause',
        'delivery_man_id',
        'deliveryman_charge',
        'expected_delivery_date',
        'scheduled_for',
        'order_note',
        'shipping_address_data',
        'billing_address_data',
        'governorate',
        'delivery_type',
        'delivery_service_name',
        'third_party_delivery_tracking_id',
        'verification_status',
    ];

    private const MAX_VALUE_LENGTH = 2000;

    /** @var array{actor_type: string, actor_id: ?int, actor_name: ?string, source: string, ip_address: ?string}|null */
    private ?array $actorForTesting = null;

    /**
     * @param  array<string, mixed>  $values  the columns being written
     * @return array<int, string>  tracked columns among them
     */
    public function trackedFieldsIn(array $values): array
    {
        return array_values(array_intersect(self::TRACKED_FIELDS, array_keys($values)));
    }

    /**
     * @param  iterable<int, object|array<string, mixed>>  $ordersBefore  rows read just before the update (id + tracked fields)
     * @param  array<string, mixed>  $values  the columns that were written
     */
    public function recordChanges(iterable $ordersBefore, array $values): void
    {
        $fields = $this->trackedFieldsIn($values);
        if (empty($fields)) {
            return;
        }

        try {
            $actor = $this->resolveActor();
            $now = now();
            $rows = [];
            foreach ($ordersBefore as $order) {
                foreach ($fields as $field) {
                    $oldValue = $this->normalize(data_get($order, $field));
                    $newValue = $this->normalize($values[$field]);
                    if ($oldValue === $newValue) {
                        continue;
                    }
                    $rows[] = [
                        'order_id' => (int) data_get($order, 'id'),
                        'field' => $field,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                        'created_at' => $now,
                    ] + $actor;
                }
            }

            if (! empty($rows)) {
                OrderActivityLog::query()->insert($rows);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * The order's items as "Name (variant) · qty × price", keyed by product (or name) and variant.
     *
     * @return array<string, string>
     */
    public function snapshotOrderItems(int $orderId): array
    {
        try {
            return OrderDetail::where('order_id', $orderId)->get()
                ->mapWithKeys(function (OrderDetail $detail): array {
                    $productDetails = is_array($detail['product_details']) ? $detail['product_details'] : (json_decode($detail['product_details'] ?? '', true) ?? []);
                    $name = $productDetails['name'] ?? '#'.$detail['product_id'];
                    $label = $name.(filled($detail['variant']) ? ' ('.$detail['variant'].')' : '');

                    return [($detail['product_id'] ?: $name).'|'.$detail['variant'] => $label.' · '.$this->normalize($detail['qty']).' × '.$this->normalize($detail['price'])];
                })
                ->all();
        } catch (Throwable $exception) {
            report($exception);

            return [];
        }
    }

    /**
     * Logs each item whose quantity or price changed, was added or was removed since $itemsBefore.
     *
     * @param  array<string, string>  $itemsBefore  from snapshotOrderItems() just before the edit
     */
    public function recordItemChanges(int $orderId, array $itemsBefore): void
    {
        try {
            $itemsAfter = $this->snapshotOrderItems(orderId: $orderId);
            $actor = $this->resolveActor();
            $now = now();
            $rows = [];
            foreach (array_unique([...array_keys($itemsBefore), ...array_keys($itemsAfter)]) as $itemKey) {
                $oldValue = $itemsBefore[$itemKey] ?? null;
                $newValue = $itemsAfter[$itemKey] ?? null;
                if ($oldValue === $newValue) {
                    continue;
                }
                $rows[] = [
                    'order_id' => $orderId,
                    'field' => 'order_item',
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'created_at' => $now,
                ] + $actor;
            }

            if (! empty($rows)) {
                OrderActivityLog::query()->insert($rows);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @param  array{actor_type: string, actor_id: ?int, actor_name: ?string, source: string, ip_address: ?string}|null  $actor
     */
    public function setActorForTesting(?array $actor): void
    {
        $this->actorForTesting = $actor;
    }

    /**
     * @return array{actor_type: string, actor_id: ?int, actor_name: ?string, source: string, ip_address: ?string}
     */
    public function resolveActor(): array
    {
        if ($this->actorForTesting) {
            return $this->actorForTesting;
        }

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return ['actor_type' => 'system', 'actor_id' => null, 'actor_name' => 'System (scheduled task)', 'source' => 'system', 'ip_address' => null];
        }

        $request = request();
        $source = $this->resolveSource();
        $ipAddress = $request->ip();

        if ($admin = Auth::guard('admin')->user()) {
            return ['actor_type' => 'admin', 'actor_id' => $admin->id, 'actor_name' => $admin->name ?? trim(($admin->f_name ?? '').' '.($admin->l_name ?? '')), 'source' => $source, 'ip_address' => $ipAddress];
        }

        if ($deliveryMan = $request->get('delivery_man')) {
            return ['actor_type' => 'delivery_man', 'actor_id' => data_get($deliveryMan, 'id'), 'actor_name' => trim(data_get($deliveryMan, 'f_name').' '.data_get($deliveryMan, 'l_name')), 'source' => $source, 'ip_address' => $ipAddress];
        }

        if ($vendor = Auth::guard('seller')->user() ?? $request->get('seller')) {
            return ['actor_type' => 'vendor', 'actor_id' => data_get($vendor, 'id'), 'actor_name' => trim(data_get($vendor, 'f_name').' '.data_get($vendor, 'l_name')), 'source' => $source, 'ip_address' => $ipAddress];
        }

        $customer = Auth::guard('customer')->user() ?? ($request->is('api/*') ? Auth::guard('api')->user() : null);
        if ($customer) {
            return ['actor_type' => 'customer', 'actor_id' => $customer->id, 'actor_name' => trim(($customer->f_name ?? '').' '.($customer->l_name ?? '')) ?: $customer->phone, 'source' => $source, 'ip_address' => $ipAddress];
        }

        return ['actor_type' => $source === 'system' ? 'system' : 'guest', 'actor_id' => null, 'actor_name' => null, 'source' => $source, 'ip_address' => $ipAddress];
    }

    private function resolveSource(): string
    {
        $request = request();

        return match (true) {
            $request->is('admin', 'admin/*') => 'admin_panel',
            $request->is('vendor', 'vendor/*', 'seller', 'seller/*') => 'vendor_panel',
            $request->is('api/v2/delivery-man', 'api/v2/delivery-man/*') => 'delivery_app',
            $request->is('api/v3/seller', 'api/v3/seller/*') => 'vendor_app',
            $request->is('api/*') => 'customer_app',
            default => 'website',
        };
    }

    private function normalize(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $value = (string) $value;
        if (is_numeric($value) && str_contains($value, '.')) {
            $value = rtrim(rtrim($value, '0'), '.');
        }

        return Str::limit($value, self::MAX_VALUE_LENGTH, '…');
    }
}
