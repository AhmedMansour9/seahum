<?php

namespace Tests\Feature\LuckyWheel;

use App\Enums\LuckyWheel\LuckyWheelRewardType;
use App\Enums\LuckyWheel\LuckyWheelSpinFrequency;
use App\Exceptions\LuckyWheelSpinException;
use App\Models\LuckyWheelSpin;
use App\Models\LuckyWheelWonGift;
use App\Services\LuckyWheelService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LuckyWheelSpinApiTest extends TestCase
{
    use InteractsWithLuckyWheel;

    private const STATUS_ENDPOINT = '/api/v1/customer/lucky-wheel';
    private const SPIN_ENDPOINT = '/api/v1/customer/lucky-wheel/spin';
    private const GIFTS_ENDPOINT = '/api/v1/customer/lucky-wheel/gifts';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createLuckyWheelTables();
        Carbon::setTestNow(Carbon::parse('2026-10-23 12:00:00', 'Africa/Cairo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guest_cannot_view_or_spin_the_wheel(): void
    {
        $this->getJson(self::STATUS_ENDPOINT)->assertUnauthorized();
        $this->postJson(self::SPIN_ENDPOINT)->assertUnauthorized();
        $this->getJson(self::GIFTS_ENDPOINT)->assertUnauthorized();
    }

    public function test_disabled_wheel_cannot_be_spun(): void
    {
        $this->configureLuckyWheel(isEnabled: false, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->createReward(name: '50g Salmon');
        $customer = $this->createCustomer();

        $this->actingAs($customer, 'api')->getJson(self::STATUS_ENDPOINT)
            ->assertOk()
            ->assertJson(['is_enabled' => false, 'can_spin' => false, 'reason' => LuckyWheelSpinException::DISABLED]);

        $this->spinAs($customer)->assertForbidden()->assertJson(['reason' => LuckyWheelSpinException::DISABLED]);
        $this->assertSame(0, LuckyWheelSpin::count());
    }

    public function test_once_mode_hides_no_prize_and_only_awards_gifts(): void
    {
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Once);
        $this->createReward(name: '50g Salmon', displayOrder: 1);
        $this->createReward(name: '250g Shrimp', displayOrder: 2);
        $this->createReward(name: 'Try Again Tomorrow', type: LuckyWheelRewardType::NoPrize, displayOrder: 3);

        $status = $this->actingAs($this->createCustomer(), 'api')->getJson(self::STATUS_ENDPOINT)->assertOk();
        $this->assertSame(['50g Salmon', '250g Shrimp'], array_column($status->json('rewards'), 'name'));

        foreach (range(1, 20) as $customerNumber) {
            $this->spinAs($this->createCustomer(email: "customer{$customerNumber}@gmail.com"))
                ->assertOk()
                ->assertJson(['outcome_type' => LuckyWheelRewardType::Gift->value]);
        }
        $this->assertSame(0, LuckyWheelSpin::where('outcome_type', LuckyWheelRewardType::NoPrize->value)->count());
        $this->assertSame(20, LuckyWheelWonGift::count());
    }

    public function test_once_mode_spin_cannot_be_repeated_even_on_a_later_day(): void
    {
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Once);
        $this->createReward(name: '50g Salmon');
        $customer = $this->createCustomer();

        $this->spinAs($customer)->assertOk()->assertJsonPath('status.can_spin', false);
        $this->spinAs($customer)->assertForbidden()->assertJson(['reason' => LuckyWheelSpinException::ALREADY_SPUN]);

        Carbon::setTestNow(Carbon::parse('2026-11-30 12:00:00', 'Africa/Cairo'));
        $this->spinAs($customer)->assertForbidden()->assertJson(['reason' => LuckyWheelSpinException::ALREADY_SPUN]);
        $this->assertSame(1, LuckyWheelSpin::count());
    }

    public function test_once_mode_without_active_gifts_returns_an_error_without_consuming_the_spin(): void
    {
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Once);
        $this->createReward(name: 'Try Again Tomorrow', type: LuckyWheelRewardType::NoPrize);
        $this->createReward(name: 'Inactive gift', isActive: false);
        $customer = $this->createCustomer();

        $this->spinAs($customer)->assertForbidden()->assertJson(['reason' => LuckyWheelSpinException::NO_REWARDS_AVAILABLE]);
        $this->assertSame(0, LuckyWheelSpin::count());

        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->spinAs($customer)->assertOk()->assertJson(['outcome_type' => LuckyWheelRewardType::NoPrize->value]);
    }

    public function test_daily_mode_shows_all_active_outcomes_and_allows_one_spin_per_day(): void
    {
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->createReward(name: '50g Salmon', displayOrder: 1);
        $this->createReward(name: 'Try Again Tomorrow', type: LuckyWheelRewardType::NoPrize, displayOrder: 2);
        $customer = $this->createCustomer();

        $status = $this->actingAs($customer, 'api')->getJson(self::STATUS_ENDPOINT)->assertOk();
        $this->assertSame(['50g Salmon', 'Try Again Tomorrow'], array_column($status->json('rewards'), 'name'));

        $this->spinAs($customer)->assertOk()
            ->assertJsonPath('status.can_spin', false)
            ->assertJsonPath('status.has_spun_today', true)
            ->assertJsonPath('status.next_spin_at', '2026-10-24T00:00:00+03:00');
        $this->spinAs($customer)->assertForbidden()->assertJson(['reason' => LuckyWheelSpinException::ALREADY_SPUN_TODAY]);

        Carbon::setTestNow(Carbon::parse('2026-10-24 09:00:00', 'Africa/Cairo'));
        $this->spinAs($customer)->assertOk();
        $this->assertSame(2, LuckyWheelSpin::where('customer_id', $customer->id)->count());
    }

    public function test_daily_eligibility_resets_at_business_midnight_not_after_24_hours(): void
    {
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->createReward(name: '50g Salmon');
        $customer = $this->createCustomer();

        Carbon::setTestNow(Carbon::parse('2026-10-23 23:59:59', 'Africa/Cairo'));
        $this->spinAs($customer)->assertOk();

        Carbon::setTestNow(Carbon::parse('2026-10-24 00:00:00', 'Africa/Cairo'));
        $this->spinAs($customer)->assertOk();

        $this->assertSame(
            ['2026-10-23', '2026-10-24'],
            LuckyWheelSpin::orderBy('id')->pluck('spin_date')->all()
        );
    }

    public function test_no_prize_consumes_the_daily_spin_without_creating_a_gift(): void
    {
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->createReward(name: 'Try Again Tomorrow', type: LuckyWheelRewardType::NoPrize);
        $customer = $this->createCustomer();

        $this->spinAs($customer)->assertOk()->assertJson(['outcome_type' => LuckyWheelRewardType::NoPrize->value, 'won_gift' => null]);

        $this->spinAs($customer)->assertForbidden()->assertJson(['reason' => LuckyWheelSpinException::ALREADY_SPUN_TODAY]);
        $this->assertSame(0, LuckyWheelWonGift::count());
    }

    public function test_switching_frequency_keeps_history_and_applies_the_new_rule(): void
    {
        $this->createReward(name: '50g Salmon');
        $dailySpinner = $this->createCustomer(email: 'daily@gmail.com');
        $onceSpinner = $this->createCustomer(email: 'once@gmail.com');

        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->spinAs($dailySpinner)->assertOk();
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Once);
        $this->spinAs($onceSpinner)->assertOk();

        Carbon::setTestNow(Carbon::parse('2026-10-24 12:00:00', 'Africa/Cairo'));
        $this->spinAs($dailySpinner)->assertForbidden()->assertJson(['reason' => LuckyWheelSpinException::ALREADY_SPUN]);

        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->spinAs($onceSpinner)->assertOk();
        $this->assertSame(3, LuckyWheelSpin::count());
    }

    public function test_inactive_and_deleted_rewards_are_never_selected(): void
    {
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $winningReward = $this->createReward(name: '50g Salmon');
        $this->createReward(name: 'Inactive', isActive: false);
        $this->createReward(name: 'Deleted')->delete();

        foreach (range(1, 10) as $customerNumber) {
            $this->spinAs($this->createCustomer(email: "customer{$customerNumber}@gmail.com"))
                ->assertOk()
                ->assertJsonPath('reward.id', $winningReward->id)
                ->assertJsonPath('reward_index', 0);
        }
    }

    public function test_every_active_reward_can_be_picked_with_an_equal_share(): void
    {
        $this->createReward(name: '50g Salmon');
        $this->createReward(name: '250g Shrimp');
        $this->createReward(name: 'Try Again Tomorrow', type: LuckyWheelRewardType::NoPrize);
        $luckyWheelService = app(LuckyWheelService::class);
        $rewards = $luckyWheelService->getEligibleRewards(spinFrequency: LuckyWheelSpinFrequency::Daily);

        $this->assertSame([33.33, 33.33, 33.33], array_values($luckyWheelService->getSelectionProbabilities(rewards: $rewards)));

        $pickedNames = collect(range(1, 300))->map(fn () => $luckyWheelService->selectReward(rewards: $rewards)->name)->unique()->sort()->values()->all();
        $this->assertSame(['250g Shrimp', '50g Salmon', 'Try Again Tomorrow'], $pickedNames);
    }

    public function test_client_supplied_reward_and_probability_are_ignored(): void
    {
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $winningReward = $this->createReward(name: '50g Salmon');
        $inactiveReward = $this->createReward(name: 'Inactive', isActive: false);

        $this->actingAs($this->createCustomer(), 'api')
            ->postJson(self::SPIN_ENDPOINT, ['reward_id' => $inactiveReward->id, 'probability' => 100])
            ->assertOk()
            ->assertJsonPath('reward.id', $winningReward->id);
    }

    public function test_database_rejects_a_second_spin_for_the_same_customer_and_day(): void
    {
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->createReward(name: '50g Salmon');
        $customer = $this->createCustomer();
        $this->spinAs($customer)->assertOk();

        $this->expectException(UniqueConstraintViolationException::class);
        LuckyWheelSpin::create([
            'customer_id' => $customer->id,
            'outcome_type' => LuckyWheelRewardType::Gift,
            'spin_date' => '2026-10-23',
            'spin_frequency' => LuckyWheelSpinFrequency::Daily,
            'reward_snapshot' => [],
        ]);
    }

    public function test_reward_and_gift_names_follow_the_request_language(): void
    {
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->createReward(name: '50g Salmon', arabicName: '٥٠ جرام سمك سلمون');
        $customer = $this->createCustomer();

        $this->actingAs($customer, 'api')->postJson(self::SPIN_ENDPOINT, [], ['lang' => 'eg'])
            ->assertOk()
            ->assertJsonPath('reward.name', '٥٠ جرام سمك سلمون')
            ->assertJsonPath('won_gift.name', '٥٠ جرام سمك سلمون');

        $this->actingAs($customer, 'api')->getJson(self::GIFTS_ENDPOINT, ['lang' => 'en'])
            ->assertOk()
            ->assertJsonPath('gifts.0.name', '50g Salmon');
        $this->actingAs($customer, 'api')->getJson(self::STATUS_ENDPOINT, ['lang' => 'eg'])
            ->assertJsonPath('reason_message', 'لقد أدرت العجلة اليوم بالفعل. عد غدًا');
    }

    public function test_customers_only_see_their_own_gifts(): void
    {
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->createReward(name: '50g Salmon');
        $owner = $this->createCustomer(email: 'buyer@gmail.com');
        $otherCustomer = $this->createCustomer(email: 'seller@gmail.com');
        $this->spinAs($owner)->assertOk();

        $this->actingAs($otherCustomer, 'api')->getJson(self::GIFTS_ENDPOINT)->assertOk()->assertJson(['total_size' => 0, 'gifts' => []]);
        $this->actingAs($owner, 'api')->getJson(self::GIFTS_ENDPOINT.'?status=pending')
            ->assertOk()
            ->assertJsonPath('total_size', 1)
            ->assertJsonPath('gifts.0.status', 'pending');
    }

    private function spinAs($customer): TestResponse
    {
        return $this->actingAs($customer, 'api')->postJson(self::SPIN_ENDPOINT);
    }
}
