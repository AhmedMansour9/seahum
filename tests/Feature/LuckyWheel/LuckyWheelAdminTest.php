<?php

namespace Tests\Feature\LuckyWheel;

use App\Enums\LuckyWheel\LuckyWheelRewardType;
use App\Enums\LuckyWheel\LuckyWheelSpinFrequency;
use App\Http\Middleware\ActivationCheckMiddleware;
use App\Models\Admin;
use App\Models\BusinessSetting;
use App\Models\LuckyWheelReward;
use App\Models\LuckyWheelWonGift;
use App\Models\Translation;
use App\Services\LuckyWheelService;
use Database\Seeders\LuckyWheelSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LuckyWheelAdminTest extends TestCase
{
    use InteractsWithLuckyWheel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ActivationCheckMiddleware::class);
        $this->createLuckyWheelTables();
        $this->createAdminTables();
        $this->setBusinessSetting(type: 'pnc_language', value: '["en","eg"]');
        Storage::fake('public');
    }

    public function test_admin_enables_disables_and_switches_frequency(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.lucky-wheel.settings'), ['is_enabled' => 1, 'spin_frequency' => LuckyWheelSpinFrequency::Once->value, 'minimum_order_amount' => '1000'])
            ->assertRedirect(route('admin.lucky-wheel.index'));
        $this->assertSame([true, LuckyWheelSpinFrequency::Once], $this->currentSettings());

        $this->actingAs($admin, 'admin')
            ->post(route('admin.lucky-wheel.settings'), ['spin_frequency' => LuckyWheelSpinFrequency::Daily->value, 'minimum_order_amount' => '1000']);
        $this->assertSame([false, LuckyWheelSpinFrequency::Daily], $this->currentSettings());

        $this->actingAs($admin, 'admin')
            ->post(route('admin.lucky-wheel.settings'), ['is_enabled' => 1, 'spin_frequency' => 'WEEKLY', 'minimum_order_amount' => '1000'])
            ->assertSessionHasErrors('spin_frequency');
        $this->assertSame([false, LuckyWheelSpinFrequency::Daily], $this->currentSettings());
    }

    public function test_only_admins_with_marketing_access_can_manage_the_wheel(): void
    {
        $this->post(route('admin.lucky-wheel.settings'), ['is_enabled' => 1, 'spin_frequency' => 'ONCE'])->assertNotFound();

        $this->actingAs($this->createCustomer(), 'customer')
            ->post(route('admin.lucky-wheel.reward.delete'), ['id' => 1])
            ->assertNotFound();

        $this->actingAs($this->createAdmin(adminRoleId: 2, moduleAccess: ['orders']), 'admin')
            ->post(route('admin.lucky-wheel.settings'), ['is_enabled' => 1, 'spin_frequency' => 'ONCE'])
            ->assertRedirect(route('admin.dashboard.index'));

        $this->assertSame(0, BusinessSetting::where('type', LuckyWheelService::STATUS_SETTING)->count());
    }

    public function test_admin_creates_a_bilingual_reward_with_an_image(): void
    {
        $this->actingAs($this->createAdmin(), 'admin')
            ->post(route('admin.lucky-wheel.reward.add'), $this->rewardPayload(image: UploadedFile::fake()->image('salmon.png')))
            ->assertRedirect(route('admin.lucky-wheel.index'));

        $reward = LuckyWheelReward::with('translations')->sole();
        $this->assertSame(['en' => '50g Salmon', 'eg' => '٥٠ جرام سمك سلمون'], $reward->getNameTranslations());
        $this->assertSame([LuckyWheelRewardType::Gift, 3, true], [$reward->type, $reward->display_order, $reward->is_active]);
        Storage::disk('public')->assertExists('lucky-wheel/'.$reward->image);
    }

    public function test_reward_validation_rejects_unknown_types_and_missing_names(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')->post(route('admin.lucky-wheel.reward.add'), [...$this->rewardPayload(), 'type' => 'JACKPOT'])
            ->assertSessionHasErrors('type');
        $this->actingAs($admin, 'admin')->post(route('admin.lucky-wheel.reward.add'), [...$this->rewardPayload(), 'name' => ['', 'اسم']])
            ->assertSessionHasErrors();

        $this->assertSame(0, LuckyWheelReward::count());
    }

    public function test_replacing_an_image_keeps_the_old_file_while_a_won_gift_uses_it(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin, 'admin')->post(route('admin.lucky-wheel.reward.add'), $this->rewardPayload(image: UploadedFile::fake()->image('first.png')));
        $reward = LuckyWheelReward::sole();
        $awardedImage = $reward->image;
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        app(LuckyWheelService::class)->spin(customerId: $this->createCustomer()->id);

        $this->actingAs($admin, 'admin')->post(route('admin.lucky-wheel.reward.update', ['id' => $reward->id]), $this->rewardPayload(name: '60g Salmon', image: UploadedFile::fake()->image('second.png')));
        $replacedImage = $reward->fresh()->image;
        $this->actingAs($admin, 'admin')->post(route('admin.lucky-wheel.reward.update', ['id' => $reward->id]), $this->rewardPayload(name: '70g Salmon', image: UploadedFile::fake()->image('third.png')));

        Storage::disk('public')->assertExists('lucky-wheel/'.$awardedImage);
        Storage::disk('public')->assertMissing('lucky-wheel/'.$replacedImage);
        Storage::disk('public')->assertExists('lucky-wheel/'.$reward->fresh()->image);
        $this->assertSame(['50g Salmon', $awardedImage], [LuckyWheelWonGift::sole()->name, LuckyWheelWonGift::sole()->image]);
        $this->assertSame('70g Salmon', $reward->fresh()->name);
    }

    public function test_admin_toggles_and_soft_deletes_rewards_without_touching_won_gifts(): void
    {
        $admin = $this->createAdmin();
        $reward = $this->createReward(name: '50g Salmon');
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        app(LuckyWheelService::class)->spin(customerId: $this->createCustomer()->id);

        $this->actingAs($admin, 'admin')->postJson(route('admin.lucky-wheel.reward.status'), ['id' => $reward->id])->assertOk();
        $this->assertFalse($reward->fresh()->is_active);
        $this->assertTrue(app(LuckyWheelService::class)->getEligibleRewards()->isEmpty());

        $this->actingAs($admin, 'admin')->postJson(route('admin.lucky-wheel.reward.status'), ['id' => $reward->id, 'status' => 1])->assertOk();
        $this->assertTrue($reward->fresh()->is_active);

        $this->actingAs($admin, 'admin')->post(route('admin.lucky-wheel.reward.delete'), ['id' => $reward->id])->assertRedirect();
        $this->assertSoftDeleted($reward);
        $this->assertTrue(app(LuckyWheelService::class)->getEligibleRewards()->isEmpty());
        $this->assertSame($reward->id, LuckyWheelWonGift::sole()->reward_id);
    }

    public function test_seeder_is_idempotent_and_never_overwrites_admin_changes(): void
    {
        $this->seed(LuckyWheelSeeder::class);

        $luckyWheelService = app(LuckyWheelService::class);
        $this->assertSame([false, LuckyWheelSpinFrequency::Daily], $this->currentSettings());
        $this->assertSame(5, LuckyWheelReward::count());
        $this->assertSame([20.0, 20.0, 20.0, 20.0, 20.0], array_values($luckyWheelService->getSelectionProbabilities(rewards: $luckyWheelService->getEligibleRewards(spinFrequency: LuckyWheelSpinFrequency::Daily))));
        $this->assertSame([25.0, 25.0, 25.0, 25.0], array_values($luckyWheelService->getSelectionProbabilities(rewards: $luckyWheelService->getEligibleRewards(spinFrequency: LuckyWheelSpinFrequency::Once))));
        $this->assertSame(
            ['50g Salmon', '100g Salmon Trimmings', '200g Minced Salmon', '250g Shrimp', 'Try Again Tomorrow'],
            $luckyWheelService->getEligibleRewards(spinFrequency: LuckyWheelSpinFrequency::Daily)->pluck('name')->all()
        );
        $this->assertSame('حاول مرة أخرى غدًا', LuckyWheelReward::where('name', 'Try Again Tomorrow')->sole()->getNameTranslations()['eg']);

        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Once);
        LuckyWheelReward::where('name', '50g Salmon')->update(['name' => 'Admin renamed', 'display_order' => 9]);
        LuckyWheelReward::where('name', '250g Shrimp')->delete();

        $this->seed(LuckyWheelSeeder::class);

        $this->assertSame([true, LuckyWheelSpinFrequency::Once], $this->currentSettings());
        $this->assertSame(5, LuckyWheelReward::withTrashed()->count());
        $this->assertSame(1, LuckyWheelReward::onlyTrashed()->count());
        $this->assertSame(['name' => 'Admin renamed', 'display_order' => 9], LuckyWheelReward::where('display_order', 9)->first(['name', 'display_order'])->only(['name', 'display_order']));
        $this->assertSame(5, Translation::where('translationable_type', LuckyWheelReward::class)->count());
    }

    private function currentSettings(): array
    {
        clearWebConfigCacheKeys();
        $luckyWheelService = app(LuckyWheelService::class);
        return [$luckyWheelService->isEnabled(), $luckyWheelService->getSpinFrequency()];
    }

    private function rewardPayload(string $name = '50g Salmon', ?UploadedFile $image = null): array
    {
        return array_filter([
            'lang' => ['en', 'eg'],
            'name' => [$name, '٥٠ جرام سمك سلمون'],
            'type' => LuckyWheelRewardType::Gift->value,
            'display_order' => 3,
            'is_active' => 1,
            'image' => $image,
        ], fn ($value) => $value !== null);
    }

    private function createAdmin(int $adminRoleId = 1, array $moduleAccess = []): Admin
    {
        DB::table('admin_roles')->insert(['id' => $adminRoleId, 'name' => 'Role', 'module_access' => json_encode($moduleAccess), 'status' => 1]);
        return Admin::forceCreate(['name' => 'Admin', 'email' => 'admin@gmail.com', 'admin_role_id' => $adminRoleId, 'status' => 1]);
    }

    private function createAdminTables(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('admin_role_id')->default(1);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('module_access')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }
}
