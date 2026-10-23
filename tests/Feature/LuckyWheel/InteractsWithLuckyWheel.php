<?php

namespace Tests\Feature\LuckyWheel;

use App\Enums\LuckyWheel\LuckyWheelRewardType;
use App\Enums\LuckyWheel\LuckyWheelSpinFrequency;
use App\Models\BusinessSetting;
use App\Models\LuckyWheelReward;
use App\Models\Translation;
use App\Models\User;
use App\Services\LuckyWheelService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait InteractsWithLuckyWheel
{
    private const LUCKY_WHEEL_MIGRATIONS = [
        'database/migrations/2026_10_23_100000_create_lucky_wheel_rewards_table.php',
        'database/migrations/2026_10_23_100001_create_lucky_wheel_spins_table.php',
        'database/migrations/2026_10_23_100002_create_lucky_wheel_won_gifts_table.php',
    ];

    private function createLuckyWheelTables(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->longText('value')->nullable();
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('f_name')->nullable();
            $table->string('email')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale');
            $table->string('key')->nullable();
            $table->text('value')->nullable();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->boolean('is_guest')->default(0);
            $table->string('order_status')->default('pending');
            $table->decimal('order_amount', 40, 20)->default(0);
            $table->decimal('shipping_cost', 40, 20)->default(0);
            $table->decimal('total_tax_amount', 40, 20)->default(0);
            $table->decimal('discount_amount', 40, 20)->default(0);
            $table->string('coupon_code')->nullable();
            $table->decimal('refer_and_earn_discount', 40, 20)->default(0);
            $table->timestamps();
        });
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->boolean('is_stock_decreased')->default(0);
            $table->decimal('price', 40, 20)->default(0);
            $table->integer('qty')->default(1);
            $table->decimal('discount', 40, 20)->default(0);
            $table->decimal('tax', 40, 20)->default(0);
        });
        Schema::create('storages', function (Blueprint $table) {
            $table->id();
            $table->string('data_type');
            $table->unsignedBigInteger('data_id');
            $table->string('key')->nullable();
            $table->string('value')->nullable();
        });
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('coupon_type');
        });

        $this->artisan('migrate', ['--path' => self::LUCKY_WHEEL_MIGRATIONS])->assertSuccessful();

        $this->setBusinessSetting(type: 'timezone', value: 'Africa/Cairo');
    }

    private function setBusinessSetting(string $type, string $value): void
    {
        BusinessSetting::updateOrInsert(['type' => $type], ['value' => $value]);
        clearWebConfigCacheKeys();
    }

    private function configureLuckyWheel(bool $isEnabled, LuckyWheelSpinFrequency $spinFrequency): void
    {
        $this->setBusinessSetting(type: LuckyWheelService::STATUS_SETTING, value: $isEnabled ? '1' : '0');
        $this->setBusinessSetting(type: LuckyWheelService::SPIN_FREQUENCY_SETTING, value: $spinFrequency->value);
    }

    private function createCustomer(string $email = 'buyer@gmail.com'): User
    {
        return User::create(['f_name' => 'Buyer', 'email' => $email]);
    }

    private function createReward(
        string $name,
        LuckyWheelRewardType $type = LuckyWheelRewardType::Gift,
        bool $isActive = true,
        int $displayOrder = 1,
        ?string $arabicName = null,
    ): LuckyWheelReward {
        $reward = LuckyWheelReward::create([
            'name' => $name,
            'type' => $type,
            'is_active' => $isActive,
            'display_order' => $displayOrder,
        ]);

        if ($arabicName) {
            Translation::insert([
                'translationable_type' => LuckyWheelReward::class,
                'translationable_id' => $reward->id,
                'locale' => 'eg',
                'key' => 'name',
                'value' => $arabicName,
            ]);
        }

        return $reward;
    }
}
