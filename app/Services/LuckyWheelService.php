<?php

namespace App\Services;

use App\Enums\LuckyWheel\LuckyWheelRewardType;
use App\Enums\LuckyWheel\LuckyWheelSpinFrequency;
use App\Exceptions\LuckyWheelSpinException;
use App\Models\Coupon;
use App\Models\LuckyWheelReward;
use App\Models\LuckyWheelSpin;
use App\Models\LuckyWheelWonGift;
use App\Models\Order;
use App\Models\User;
use App\Utils\OrderManager;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LuckyWheelService
{
    public const STATUS_SETTING = 'lucky_wheel_status';
    public const SPIN_FREQUENCY_SETTING = 'lucky_wheel_spin_frequency';
    public const MINIMUM_ORDER_AMOUNT_SETTING = 'lucky_wheel_minimum_order_amount';

    private const MONEY_SCALE = 2;

    /**
     * Order statuses that end an order before fulfillment, giving its gifts back to the customer.
     */
    public const GIFT_RELEASING_ORDER_STATUSES = ['canceled', 'failed'];

    public function isEnabled(): bool
    {
        return (int)getWebConfig(name: self::STATUS_SETTING) === 1;
    }

    public function getSpinFrequency(): LuckyWheelSpinFrequency
    {
        return LuckyWheelSpinFrequency::tryFrom((string)getWebConfig(name: self::SPIN_FREQUENCY_SETTING)) ?? LuckyWheelSpinFrequency::Daily;
    }

    /**
     * Minimum gift-qualifying subtotal in the base currency, as a decimal string.
     */
    public function getMinimumOrderAmount(): string
    {
        $configuredAmount = getWebConfig(name: self::MINIMUM_ORDER_AMOUNT_SETTING);
        return $this->toMoney(amount: is_numeric($configuredAmount) ? $configuredAmount : config('lucky_wheel.default_minimum_order_amount'));
    }

    /**
     * Merchandise subtotal that counts toward the gift minimum: item prices after product, coupon and
     * referral discounts. Shipping, taxes, free-delivery coupons and the gifts themselves never count.
     */
    public function calculateGiftQualifyingSubtotal(float|int|string $itemSubtotal, float|int|string $productDiscount, float|int|string $couponDiscount, float|int|string $referralDiscount): string
    {
        $qualifyingSubtotal = bcsub($this->toMoney(amount: $itemSubtotal), $this->toMoney(amount: $productDiscount), self::MONEY_SCALE);
        $qualifyingSubtotal = bcsub($qualifyingSubtotal, $this->toMoney(amount: $couponDiscount), self::MONEY_SCALE);
        $qualifyingSubtotal = bcsub($qualifyingSubtotal, $this->toMoney(amount: $referralDiscount), self::MONEY_SCALE);
        return bccomp($qualifyingSubtotal, '0', self::MONEY_SCALE) < 0 ? $this->toMoney(amount: 0) : $qualifyingSubtotal;
    }

    /**
     * The authoritative subtotal of a placed checkout, reusing the order summary used across order accounting.
     */
    public function getOrdersGiftQualifyingSubtotal(Collection $orders): string
    {
        $freeDeliveryCouponCodes = Coupon::whereIn('code', $orders->pluck('coupon_code')->filter()->unique())
            ->where('coupon_type', 'free_delivery')
            ->pluck('code')
            ->all();

        return $orders->reduce(function (string $total, Order $order) use ($freeDeliveryCouponCodes) {
            $orderSummary = OrderManager::getOrderTotalAndSubTotalAmountSummary($order);
            $orderSubtotal = $this->calculateGiftQualifyingSubtotal(
                itemSubtotal: $orderSummary['subtotal'],
                productDiscount: $orderSummary['total_discount_on_product'],
                couponDiscount: in_array($order['coupon_code'], $freeDeliveryCouponCodes, true) ? 0 : ($order['discount_amount'] ?? 0),
                referralDiscount: $order['refer_and_earn_discount'] ?? 0,
            );
            return bcadd($total, $orderSubtotal, self::MONEY_SCALE);
        }, $this->toMoney(amount: 0));
    }

    /**
     * @return array{minimum_order_amount: float, current_order_subtotal: float, is_eligible: bool, remaining_amount: float}
     */
    public function getGiftEligibility(string $qualifyingSubtotal): array
    {
        $minimumOrderAmount = $this->getMinimumOrderAmount();
        $isEligible = bccomp($qualifyingSubtotal, $minimumOrderAmount, self::MONEY_SCALE) >= 0;

        return [
            'minimum_order_amount' => (float)$minimumOrderAmount,
            'current_order_subtotal' => (float)$qualifyingSubtotal,
            'is_eligible' => $isEligible,
            'remaining_amount' => $isEligible ? 0.0 : (float)bcsub($minimumOrderAmount, $qualifyingSubtotal, self::MONEY_SCALE),
        ];
    }

    /**
     * Checkout preview of the gift minimum, or null when the customer has no pending gifts.
     *
     * @return array{minimum_order_amount: float, current_order_subtotal: float, is_eligible: bool, remaining_amount: float, pending_gifts: Collection}|null
     */
    public function getCheckoutGiftSummary(int $customerId, float|int|string $itemSubtotal, float|int|string $productDiscount, float|int|string $couponDiscount, float|int|string $referralDiscount): ?array
    {
        $pendingGifts = $this->getPendingGifts(customerId: $customerId);
        if ($pendingGifts->isEmpty()) {
            return null;
        }

        $qualifyingSubtotal = $this->calculateGiftQualifyingSubtotal(
            itemSubtotal: $itemSubtotal,
            productDiscount: $productDiscount,
            couponDiscount: $couponDiscount,
            referralDiscount: $referralDiscount,
        );
        return [...$this->getGiftEligibility(qualifyingSubtotal: $qualifyingSubtotal), 'pending_gifts' => $pendingGifts];
    }

    public function getBusinessNow(): Carbon
    {
        return Carbon::now(getWebConfig(name: 'timezone') ?: config('app.timezone'));
    }

    /**
     * The single source of wheel outcomes: rendered on the wheel, shown to the admin and used for selection.
     */
    public function getEligibleRewards(?LuckyWheelSpinFrequency $spinFrequency = null): Collection
    {
        $spinFrequency ??= $this->getSpinFrequency();

        return LuckyWheelReward::active()
            ->with('translations')
            ->when($spinFrequency === LuckyWheelSpinFrequency::Once, function ($query) {
                return $query->where('type', LuckyWheelRewardType::Gift->value);
            })
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array{is_enabled: bool, spin_frequency: string, can_spin: bool, has_spun_today: bool, has_ever_spun: bool, next_spin_at: string|null, reason: string|null, rewards: Collection}
     */
    public function getCustomerStatus(int $customerId): array
    {
        $isEnabled = $this->isEnabled();
        $spinFrequency = $this->getSpinFrequency();
        $businessNow = $this->getBusinessNow();
        $rewards = $this->getEligibleRewards(spinFrequency: $spinFrequency);

        $hasEverSpun = LuckyWheelSpin::where('customer_id', $customerId)->exists();
        $hasSpunToday = $hasEverSpun && LuckyWheelSpin::where(['customer_id' => $customerId, 'spin_date' => $businessNow->toDateString()])->exists();

        $reason = match (true) {
            !$isEnabled => LuckyWheelSpinException::DISABLED,
            $spinFrequency === LuckyWheelSpinFrequency::Once && $hasEverSpun => LuckyWheelSpinException::ALREADY_SPUN,
            $spinFrequency === LuckyWheelSpinFrequency::Daily && $hasSpunToday => LuckyWheelSpinException::ALREADY_SPUN_TODAY,
            $rewards->isEmpty() => LuckyWheelSpinException::NO_REWARDS_AVAILABLE,
            default => null,
        };

        $nextSpinAt = $isEnabled && $reason === LuckyWheelSpinException::ALREADY_SPUN_TODAY
            ? $businessNow->copy()->addDay()->startOfDay()->toIso8601String()
            : null;

        return [
            'is_enabled' => $isEnabled,
            'spin_frequency' => $spinFrequency->value,
            'can_spin' => $reason === null,
            'has_spun_today' => $hasSpunToday,
            'has_ever_spun' => $hasEverSpun,
            'next_spin_at' => $nextSpinAt,
            'reason' => $reason,
            'rewards' => $rewards,
        ];
    }

    /**
     * @throws LuckyWheelSpinException
     */
    public function spin(int $customerId): LuckyWheelSpin
    {
        if (!$this->isEnabled()) {
            throw new LuckyWheelSpinException(LuckyWheelSpinException::DISABLED);
        }

        $spinFrequency = $this->getSpinFrequency();
        $spinDate = $this->getBusinessNow()->toDateString();

        try {
            return DB::transaction(function () use ($customerId, $spinFrequency, $spinDate) {
                User::whereKey($customerId)->lockForUpdate()->first();

                $previousSpins = LuckyWheelSpin::where('customer_id', $customerId);
                if ($spinFrequency === LuckyWheelSpinFrequency::Once && $previousSpins->exists()) {
                    throw new LuckyWheelSpinException(LuckyWheelSpinException::ALREADY_SPUN);
                }
                if ($previousSpins->where('spin_date', $spinDate)->exists()) {
                    throw new LuckyWheelSpinException(LuckyWheelSpinException::ALREADY_SPUN_TODAY);
                }

                $reward = $this->selectReward(rewards: $this->getEligibleRewards(spinFrequency: $spinFrequency));
                $rewardSnapshot = [
                    'id' => $reward->id,
                    'name' => $reward->name,
                    'name_translations' => $reward->getNameTranslations(),
                    'type' => $reward->type->value,
                    'image' => $reward->image,
                    'image_storage_type' => $reward->image_storage_type,
                ];

                $spin = LuckyWheelSpin::create([
                    'customer_id' => $customerId,
                    'reward_id' => $reward->id,
                    'outcome_type' => $reward->type,
                    'spin_date' => $spinDate,
                    'spin_frequency' => $spinFrequency,
                    'reward_snapshot' => $rewardSnapshot,
                ]);

                if ($reward->type === LuckyWheelRewardType::Gift) {
                    $spin->setRelation('wonGift', LuckyWheelWonGift::create([
                        'customer_id' => $customerId,
                        'spin_id' => $spin->id,
                        'reward_id' => $reward->id,
                        'name' => $rewardSnapshot['name'],
                        'name_translations' => $rewardSnapshot['name_translations'],
                        'image' => $reward->image,
                        'image_storage_type' => $reward->image_storage_type,
                        'awarded_at' => now(),
                    ]));
                } else {
                    $spin->setRelation('wonGift', null);
                }

                return $spin->setRelation('reward', $reward);
            });
        } catch (UniqueConstraintViolationException) {
            throw new LuckyWheelSpinException(LuckyWheelSpinException::ALREADY_SPUN_TODAY);
        }
    }

    /**
     * @throws LuckyWheelSpinException
     */
    public function selectReward(Collection $rewards): LuckyWheelReward
    {
        if ($rewards->isEmpty()) {
            throw new LuckyWheelSpinException(LuckyWheelSpinException::NO_REWARDS_AVAILABLE);
        }

        return $rewards->random();
    }

    public function getPendingGifts(int $customerId): Collection
    {
        return LuckyWheelWonGift::where('customer_id', $customerId)->pending()->orderBy('id')->get();
    }

    /**
     * Attaches pending gifts to the first order of a placed checkout when the checkout meets the minimum.
     * Otherwise the gifts stay pending for a later qualifying order.
     *
     * @param array<int, int|string> $orderIds
     */
    public function attachPendingGiftsToQualifyingOrders(int $customerId, array $orderIds): int
    {
        if (empty($orderIds) || !LuckyWheelWonGift::where('customer_id', $customerId)->pending()->exists()) {
            return 0;
        }

        $orders = Order::with('details')->whereIn('id', $orderIds)->get();
        if (!$this->getGiftEligibility(qualifyingSubtotal: $this->getOrdersGiftQualifyingSubtotal(orders: $orders))['is_eligible']) {
            return 0;
        }

        return DB::transaction(fn () => $this->attachPendingGiftsToOrder(customerId: $customerId, orderId: (int)reset($orderIds)));
    }

    /**
     * A single conditional update, so a retried or concurrent checkout can never attach the same gift twice.
     */
    public function attachPendingGiftsToOrder(int $customerId, int $orderId): int
    {
        return LuckyWheelWonGift::where('customer_id', $customerId)->pending()->update(['order_id' => $orderId]);
    }

    private function toMoney(float|int|string $amount): string
    {
        return number_format((float)$amount, self::MONEY_SCALE, '.', '');
    }

    public function releaseGiftsOnOrderStatusChange(int $orderId, string $orderStatus): int
    {
        if (!in_array($orderStatus, self::GIFT_RELEASING_ORDER_STATUSES, true)) {
            return 0;
        }
        return LuckyWheelWonGift::where('order_id', $orderId)->update(['order_id' => null]);
    }
}
