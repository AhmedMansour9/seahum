<?php

namespace Database\Seeders;

use App\Enums\LuckyWheel\LuckyWheelRewardType;
use App\Enums\LuckyWheel\LuckyWheelSpinFrequency;
use App\Models\BusinessSetting;
use App\Models\LuckyWheelReward;
use App\Models\LuckyWheelSpin;
use App\Models\LuckyWheelWonGift;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use App\Services\LuckyWheelService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds one customer per lucky wheel branch (can spin, already spun, pending gifts,
 * gifts below/above the order minimum, attached/delivered gifts, deleted reward snapshot)
 * and prints every row with its login and the request that reaches it. Safe to re-run.
 */
class LuckyWheelScenarioSeeder extends Seeder
{
    private const PASSWORD = '12345678';
    private const SCENARIO_EMAIL_DOMAIN = '@seahum.test';
    private const ORDER_GROUP_PREFIX = 'lucky-wheel-scenario-';

    private LuckyWheelService $luckyWheelService;
    private Carbon $businessToday;
    private Product $orderProduct;
    private array $rows = [];
    private array $requests = [];

    public function run(): void
    {
        $this->call(LuckyWheelSeeder::class);
        $this->enableWheel();

        $this->luckyWheelService = app(LuckyWheelService::class);
        $this->businessToday = $this->luckyWheelService->getBusinessNow()->startOfDay();
        $this->orderProduct = Product::where(['added_by' => 'admin', 'status' => 1])->orderBy('id')->firstOrFail();

        $rewards = LuckyWheelReward::orderBy('display_order')->orderBy('id')->get();
        $salmonReward = $rewards->firstWhere('type', LuckyWheelRewardType::Gift);
        $giftRewards = $rewards->where('type', LuckyWheelRewardType::Gift)->values();
        $noPrizeReward = $rewards->firstWhere('type', LuckyWheelRewardType::NoPrize);

        $this->seedFreshSpinner();
        $this->seedSpunTodayWithNoPrize(noPrizeReward: $noPrizeReward);
        $this->seedPendingGift(reward: $salmonReward);
        $this->seedMultiplePendingGifts(giftRewards: $giftRewards);
        $this->seedGiftKeptAfterSmallOrder(reward: $salmonReward);
        $this->seedGiftAttachedToPendingOrder(reward: $giftRewards->last());
        $this->seedGiftOnDeliveredOrder(reward: $salmonReward);
        $this->seedRetiredRewardSnapshot();
        $this->seedInactiveReward();

        clearWebConfigCacheKeys();
        $this->printReport();
    }

    private function enableWheel(): void
    {
        BusinessSetting::updateOrInsert(['type' => LuckyWheelService::STATUS_SETTING], ['value' => '1', 'updated_at' => now()]);
        BusinessSetting::updateOrInsert(['type' => LuckyWheelService::SPIN_FREQUENCY_SETTING], ['value' => LuckyWheelSpinFrequency::Daily->value, 'updated_at' => now()]);
        clearWebConfigCacheKeys();
    }

    private function seedFreshSpinner(): void
    {
        $buyer = User::where('email', 'buyer@gmail.com')->first();
        if (!$buyer) {
            $this->command->warn('buyer@gmail.com not found — run StorefrontPagesScenarioSeeder first; scenario 1 skipped.');
            return;
        }

        $hasSpunToday = LuckyWheelSpin::where(['customer_id' => $buyer->id, 'spin_date' => $this->businessToday->toDateString()])->exists();
        $this->addRow(
            scenario: 'Can spin (daily, fresh today)',
            ids: "user={$buyer->id}",
            keyFields: $hasSpunToday ? 'already spun today → re-test tomorrow' : 'can_spin=true',
            login: 'buyer@gmail.com',
            reach: 'POST {{base_url}}/customer/lucky-wheel/spin · web /lucky-wheel',
            request: ['POST', '/customer/lucky-wheel/spin', null, $hasSpunToday ? '403 lucky_wheel_already_spun_today' : '200 outcome GIFT or NO_PRIZE'],
        );
    }

    private function seedSpunTodayWithNoPrize(LuckyWheelReward $noPrizeReward): void
    {
        $customer = $this->scenarioCustomer(key: 'spun-today', firstName: 'Spun Today');
        $spin = $this->seedSpin(customer: $customer, reward: $noPrizeReward, daysAgo: 0);
        $this->addRow(
            scenario: 'Already spun today (NO_PRIZE)',
            ids: "user={$customer->id} spin={$spin->id}",
            keyFields: 'outcome=NO_PRIZE, spin_date='.$spin->spin_date.', gifts=0',
            login: $customer->email,
            reach: 'POST {{base_url}}/customer/lucky-wheel/spin',
            request: ['POST', '/customer/lucky-wheel/spin', null, '403 lucky_wheel_already_spun_today'],
        );
    }

    private function seedPendingGift(LuckyWheelReward $reward): void
    {
        $customer = $this->scenarioCustomer(key: 'pending-gift', firstName: 'Pending Gift');
        $spin = $this->seedSpin(customer: $customer, reward: $reward, daysAgo: 1);
        $this->addRow(
            scenario: 'One pending gift, can spin today',
            ids: "user={$customer->id} gift={$spin->wonGift->id}",
            keyFields: 'gift=pending, won='.$spin->spin_date,
            login: $customer->email,
            reach: 'GET {{base_url}}/customer/lucky-wheel/checkout-summary',
            request: ['GET', '/customer/lucky-wheel/checkout-summary', null, '200 lucky_wheel.pending_gifts=1 (add cart items to see is_eligible)'],
        );
    }

    private function seedMultiplePendingGifts($giftRewards): void
    {
        $customer = $this->scenarioCustomer(key: 'multi-gifts', firstName: 'Multi Gifts');
        $giftIds = [];
        foreach ($giftRewards->take(3)->values() as $index => $reward) {
            $giftIds[] = $this->seedSpin(customer: $customer, reward: $reward, daysAgo: $index + 3)->wonGift->id;
        }
        $this->addRow(
            scenario: 'Three pending gifts (daily wins)',
            ids: "user={$customer->id} gifts=".implode(',', $giftIds),
            keyFields: 'gifts=3 pending',
            login: $customer->email,
            reach: 'GET {{base_url}}/customer/lucky-wheel/gifts?status=pending',
            request: ['GET', '/customer/lucky-wheel/gifts?status=pending&limit=10&offset=1', null, '200 total_size=3'],
        );
    }

    private function seedGiftKeptAfterSmallOrder(LuckyWheelReward $reward): void
    {
        $customer = $this->scenarioCustomer(key: 'below-minimum', firstName: 'Below Minimum');
        $gift = $this->seedSpin(customer: $customer, reward: $reward, daysAgo: 2)->wonGift;
        $order = $this->seedOrder(customer: $customer, key: 'below-minimum', itemPrice: 600, status: 'pending');
        $this->addRow(
            scenario: 'Order below minimum, gift stays pending',
            ids: "user={$customer->id} gift={$gift->id} order={$order->id}",
            keyFields: 'order subtotal=600 < min='.$this->luckyWheelService->getMinimumOrderAmount().', gift.order_id=null',
            login: $customer->email,
            reach: "GET {{base_url}}/customer/order/get-order-by-id?order_id={$order->id}",
            request: ['GET', "/customer/order/get-order-by-id?order_id={$order->id}", null, '200 lucky_wheel_gifts=[]'],
        );
    }

    private function seedGiftAttachedToPendingOrder(LuckyWheelReward $reward): void
    {
        $customer = $this->scenarioCustomer(key: 'attached-gift', firstName: 'Attached Gift');
        $gift = $this->seedSpin(customer: $customer, reward: $reward, daysAgo: 2)->wonGift;
        $order = $this->seedOrder(customer: $customer, key: 'attached-gift', itemPrice: 1200, status: 'pending');
        $gift->update(['order_id' => $order->id]);
        $this->addRow(
            scenario: 'Gift on pending order (cancel → released)',
            ids: "user={$customer->id} gift={$gift->id} order={$order->id}",
            keyFields: 'order=pending subtotal=1200, gift.order_id='.$order->id,
            login: $customer->email,
            reach: "GET {{base_url}}/order/cancel-order?order_id={$order->id} · admin /admin/orders/details/{$order->id}",
            request: ['GET', "/order/cancel-order?order_id={$order->id}", null, '200, then gift.order_id=null (pending again)'],
        );
    }

    private function seedGiftOnDeliveredOrder(LuckyWheelReward $reward): void
    {
        $customer = $this->scenarioCustomer(key: 'delivered-gift', firstName: 'Delivered Gift');
        $gift = $this->seedSpin(customer: $customer, reward: $reward, daysAgo: 6)->wonGift;
        $order = $this->seedOrder(customer: $customer, key: 'delivered-gift', itemPrice: 1500, status: 'delivered');
        $gift->update(['order_id' => $order->id]);
        $this->addRow(
            scenario: 'Gift on delivered order (kept)',
            ids: "user={$customer->id} gift={$gift->id} order={$order->id}",
            keyFields: 'order=delivered, gift.order_id='.$order->id,
            login: $customer->email,
            reach: "GET {{base_url}}/customer/order/get-order-by-id?order_id={$order->id} · web /account-order-details?id={$order->id}",
            request: ['GET', "/customer/order/get-order-by-id?order_id={$order->id}", null, '200 lucky_wheel_gifts[0].status=attached_to_order'],
        );
    }

    private function seedRetiredRewardSnapshot(): void
    {
        $retiredReward = LuckyWheelReward::withTrashed()
            ->whereIn('name', ['[Scenario] Retired 500g Hamour', '[Scenario] Renamed after award'])
            ->first()
            ?? LuckyWheelReward::create(['name' => '[Scenario] Retired 500g Hamour', 'type' => LuckyWheelRewardType::Gift, 'is_active' => false, 'display_order' => 99]);
        $customer = $this->scenarioCustomer(key: 'retired-reward', firstName: 'Retired Reward');
        $gift = $this->seedSpin(customer: $customer, reward: $retiredReward, daysAgo: 10)->wonGift;
        $retiredReward->update(['name' => '[Scenario] Renamed after award']);
        if (!$retiredReward->trashed()) {
            $retiredReward->delete();
        }
        $this->addRow(
            scenario: 'Gift from deleted+renamed reward',
            ids: "user={$customer->id} gift={$gift->id} reward={$retiredReward->id}",
            keyFields: 'reward soft-deleted, gift.name='.$gift->name,
            login: $customer->email,
            reach: 'GET {{base_url}}/customer/lucky-wheel/gifts',
            request: ['GET', '/customer/lucky-wheel/gifts?limit=10&offset=1', null, '200 gifts[0].name = original snapshot name'],
        );
    }

    private function seedInactiveReward(): void
    {
        $inactiveReward = LuckyWheelReward::firstOrCreate(
            ['name' => '[Scenario] Inactive 1kg Mussels'],
            ['type' => LuckyWheelRewardType::Gift, 'is_active' => false, 'display_order' => 98],
        );
        $this->addRow(
            scenario: 'Inactive reward (never on wheel)',
            ids: "reward={$inactiveReward->id}",
            keyFields: 'is_active=false',
            login: 'admin (dashboard)',
            reach: '/admin/lucky-wheel · GET {{base_url}}/customer/lucky-wheel',
            request: ['GET', '/customer/lucky-wheel', null, '200 rewards[] excludes this id'],
        );
    }

    private function scenarioCustomer(string $key, string $firstName): User
    {
        return User::updateOrCreate(
            ['email' => 'lw-'.$key.self::SCENARIO_EMAIL_DOMAIN],
            [
                'name' => $firstName.' Customer',
                'f_name' => $firstName,
                'l_name' => 'Customer',
                'phone' => '+97150'.str_pad((string)crc32($key) % 10000000, 7, '0', STR_PAD_LEFT),
                'password' => Hash::make(self::PASSWORD),
                'is_active' => 1,
                'is_phone_verified' => 1,
                'is_email_verified' => 1,
                'email_verified_at' => now(),
                'login_medium' => 'general',
            ],
        );
    }

    private function seedSpin(User $customer, LuckyWheelReward $reward, int $daysAgo): LuckyWheelSpin
    {
        $spinDay = $this->businessToday->copy()->subDays($daysAgo);
        $spin = LuckyWheelSpin::firstOrCreate(
            ['customer_id' => $customer->id, 'spin_date' => $spinDay->toDateString()],
            [
                'reward_id' => $reward->id,
                'outcome_type' => $reward->type,
                'spin_frequency' => LuckyWheelSpinFrequency::Daily,
                'reward_snapshot' => [
                    'id' => $reward->id,
                    'name' => $reward->name,
                    'name_translations' => $reward->getNameTranslations(),
                    'type' => $reward->type->value,
                    'image' => $reward->image,
                    'image_storage_type' => $reward->image_storage_type,
                ],
            ],
        );

        if ($reward->type === LuckyWheelRewardType::Gift) {
            LuckyWheelWonGift::firstOrCreate(
                ['spin_id' => $spin->id],
                [
                    'customer_id' => $customer->id,
                    'reward_id' => $reward->id,
                    'name' => $reward->name,
                    'name_translations' => $reward->getNameTranslations(),
                    'image' => $reward->image,
                    'image_storage_type' => $reward->image_storage_type,
                    'awarded_at' => $spinDay->copy()->setTime(12, 0),
                ],
            );
        }

        return $spin->load('wonGift');
    }

    private function seedOrder(User $customer, string $key, int $itemPrice, string $status): Order
    {
        $order = Order::updateOrCreate(
            ['order_group_id' => self::ORDER_GROUP_PREFIX.$key],
            [
                'customer_id' => $customer->id,
                'is_guest' => 0,
                'customer_type' => 'customer',
                'payment_status' => $status === 'delivered' ? 'paid' : 'unpaid',
                'order_status' => $status,
                'payment_method' => 'cash_on_delivery',
                'order_amount' => $itemPrice,
                'init_order_amount' => $itemPrice,
                'admin_commission' => 0,
                'total_tax_amount' => 0,
                'discount_amount' => 0,
                'shipping_cost' => 0,
                'is_shipping_free' => 0,
                'shipping_method_id' => 0,
                'shipping_type' => 'order_wise',
                'shipping_responsibility' => 'inhouse_shipping',
                'seller_id' => 1,
                'seller_is' => 'admin',
                'verification_code' => '123456',
                'order_type' => 'default_type',
                'checked' => 1,
                'order_note' => 'Lucky wheel scenario: '.$key,
            ],
        );

        OrderDetail::updateOrCreate(
            ['order_id' => $order->id, 'product_id' => $this->orderProduct->id],
            [
                'seller_id' => 1,
                'product_details' => json_encode($this->orderProduct->toArray()),
                'qty' => 1,
                'price' => $itemPrice,
                'tax' => 0,
                'discount' => 0,
                'tax_model' => 'exclude',
                'delivery_status' => $status,
                'payment_status' => $status === 'delivered' ? 'paid' : 'unpaid',
                'variant' => '',
                'variation' => json_encode([]),
                'discount_type' => 'discount_on_product',
                'is_stock_decreased' => 0,
                'refund_request' => 0,
            ],
        );

        return $order;
    }

    private function addRow(string $scenario, string $ids, string $keyFields, string $login, string $reach, array $request): void
    {
        $this->rows[] = [count($this->rows) + 1, $scenario, $ids, $keyFields, $login, $reach];
        $this->requests[] = $request;
    }

    private function printReport(): void
    {
        $this->command->newLine();
        $this->command->info('Lucky wheel scenarios (wheel enabled, DAILY, minimum order '.$this->luckyWheelService->getMinimumOrderAmount().', business date '.$this->businessToday->toDateString().')');
        $this->command->table(['#', 'Scenario', 'ID', 'Key fields', 'Login', 'How to reach it'], $this->rows);
        $this->command->comment('Password for every login above: '.self::PASSWORD);

        $this->command->newLine();
        $this->command->info('Postman requests (Authorization: Bearer {{token}} from POST {{base_url}}/auth/login of the row\'s login; header lang: en | eg)');
        foreach ($this->requests as $index => [$method, $path, $body, $expected]) {
            $this->command->line(sprintf('%d. %s {{base_url}}%s  →  expected %s', $index + 1, $method, $path, $expected));
            if ($body !== null) {
                $this->command->line(json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }
        }

        $this->command->newLine();
        $this->command->info('Counts');
        $this->command->table(['Rewards (active / inactive / deleted)', 'Spins (GIFT / NO_PRIZE)', 'Gifts (pending / attached)'], [[
            LuckyWheelReward::where('is_active', true)->count().' / '.LuckyWheelReward::where('is_active', false)->count().' / '.LuckyWheelReward::onlyTrashed()->count(),
            LuckyWheelSpin::where('outcome_type', LuckyWheelRewardType::Gift->value)->count().' / '.LuckyWheelSpin::where('outcome_type', LuckyWheelRewardType::NoPrize->value)->count(),
            LuckyWheelWonGift::whereNull('order_id')->count().' / '.LuckyWheelWonGift::whereNotNull('order_id')->count(),
        ]]);
        $this->command->comment('Re-run: php artisan db:seed --class=LuckyWheelScenarioSeeder');
    }
}
